"""Casos de uso del Voluntario:
- Ver Actividades Disponibles
- Inscribirse en Actividad
- Registrar Horas de Voluntariado
"""

from typing import List

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session, joinedload

from app.auth import get_current_user, require_roles
from app.database import get_db
from app.models import (
    ActividadVoluntariado,
    InscripcionVoluntariado,
    RolUsuario,
    Usuario,
)
from app.schemas import (
    ActividadCreate,
    ActividadOut,
    InscripcionCreate,
    InscripcionOut,
    InscripcionUpdate,
)

router = APIRouter(prefix="/api/voluntariado", tags=["Voluntariado"])


admin_only = require_roles(RolUsuario.ADMINISTRADOR)


def _con_inscritos(actividad: ActividadVoluntariado) -> dict:
    return {
        "id": actividad.id,
        "titulo": actividad.titulo,
        "descripcion": actividad.descripcion,
        "fecha": actividad.fecha,
        "horas_estimadas": actividad.horas_estimadas,
        "cupos": actividad.cupos,
        "creada_en": actividad.creada_en,
        "inscritos": len(actividad.inscripciones),
    }


@router.get("/actividades", response_model=List[ActividadOut])
def listar_actividades(db: Session = Depends(get_db)):
    actividades = (
        db.query(ActividadVoluntariado)
        .options(joinedload(ActividadVoluntariado.inscripciones))
        .order_by(ActividadVoluntariado.fecha.desc())
        .all()
    )
    return [_con_inscritos(a) for a in actividades]


@router.post(
    "/actividades", response_model=ActividadOut, status_code=status.HTTP_201_CREATED
)
def crear_actividad(
    payload: ActividadCreate,
    db: Session = Depends(get_db),
    _: Usuario = Depends(admin_only),
):
    actividad = ActividadVoluntariado(**payload.model_dump())
    db.add(actividad)
    db.commit()
    db.refresh(actividad)
    return _con_inscritos(actividad)


@router.delete("/actividades/{actividad_id}", status_code=status.HTTP_204_NO_CONTENT)
def eliminar_actividad(
    actividad_id: int,
    db: Session = Depends(get_db),
    _: Usuario = Depends(admin_only),
):
    actividad = (
        db.query(ActividadVoluntariado)
        .filter(ActividadVoluntariado.id == actividad_id)
        .first()
    )
    if not actividad:
        raise HTTPException(status_code=404, detail="Actividad no encontrada")
    db.delete(actividad)
    db.commit()


@router.post(
    "/inscripciones", response_model=InscripcionOut, status_code=status.HTTP_201_CREATED
)
def inscribirse(
    payload: InscripcionCreate,
    db: Session = Depends(get_db),
    current: Usuario = Depends(require_roles(RolUsuario.VOLUNTARIO)),
):
    actividad = (
        db.query(ActividadVoluntariado)
        .filter(ActividadVoluntariado.id == payload.actividad_id)
        .first()
    )
    if not actividad:
        raise HTTPException(status_code=404, detail="Actividad no encontrada")
    inscritos = (
        db.query(InscripcionVoluntariado)
        .filter(InscripcionVoluntariado.actividad_id == payload.actividad_id)
        .count()
    )
    if inscritos >= actividad.cupos:
        raise HTTPException(status_code=400, detail="No hay cupos disponibles")

    existente = (
        db.query(InscripcionVoluntariado)
        .filter(
            InscripcionVoluntariado.actividad_id == payload.actividad_id,
            InscripcionVoluntariado.voluntario_id == current.id,
        )
        .first()
    )
    if existente:
        raise HTTPException(status_code=400, detail="Ya está inscrito en esta actividad")

    inscripcion = InscripcionVoluntariado(
        voluntario_id=current.id,
        actividad_id=payload.actividad_id,
    )
    db.add(inscripcion)
    db.commit()
    db.refresh(inscripcion)
    return inscripcion


@router.get("/inscripciones", response_model=List[InscripcionOut])
def listar_inscripciones(
    db: Session = Depends(get_db),
    current: Usuario = Depends(get_current_user),
):
    query = db.query(InscripcionVoluntariado).options(
        joinedload(InscripcionVoluntariado.actividad),
        joinedload(InscripcionVoluntariado.voluntario),
    )
    if current.rol == RolUsuario.VOLUNTARIO:
        query = query.filter(InscripcionVoluntariado.voluntario_id == current.id)
    elif current.rol != RolUsuario.ADMINISTRADOR:
        raise HTTPException(status_code=403, detail="Sin permisos")
    return query.order_by(InscripcionVoluntariado.creada_en.desc()).all()


@router.put("/inscripciones/{inscripcion_id}", response_model=InscripcionOut)
def registrar_horas(
    inscripcion_id: int,
    payload: InscripcionUpdate,
    db: Session = Depends(get_db),
    current: Usuario = Depends(get_current_user),
):
    """Caso de uso: Registrar Horas de Voluntariado."""

    inscripcion = (
        db.query(InscripcionVoluntariado)
        .filter(InscripcionVoluntariado.id == inscripcion_id)
        .first()
    )
    if not inscripcion:
        raise HTTPException(status_code=404, detail="Inscripción no encontrada")

    if (
        current.rol == RolUsuario.VOLUNTARIO
        and inscripcion.voluntario_id != current.id
    ):
        raise HTTPException(status_code=403, detail="Sin permisos sobre esta inscripción")
    if current.rol not in (RolUsuario.VOLUNTARIO, RolUsuario.ADMINISTRADOR):
        raise HTTPException(status_code=403, detail="Sin permisos")

    inscripcion.horas_registradas = payload.horas_registradas
    db.commit()
    db.refresh(inscripcion)
    return inscripcion
