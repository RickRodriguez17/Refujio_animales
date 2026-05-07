"""Caso de uso 2.0 - Gestión de Adopciones (Diagrama FDO)."""

from datetime import datetime
from typing import List

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session, joinedload

from app.auth import get_current_user, require_roles
from app.database import get_db
from app.models import (
    Adopcion,
    Animal,
    EstadoAdopcion,
    EstadoAnimal,
    RolUsuario,
    Usuario,
)
from app.schemas import AdopcionCreate, AdopcionOut, AdopcionUpdate

router = APIRouter(prefix="/api/adopciones", tags=["Adopciones"])


@router.get("", response_model=List[AdopcionOut])
def listar_adopciones(
    db: Session = Depends(get_db),
    current: Usuario = Depends(get_current_user),
):
    """Administrador ve todas. Adoptante ve solo las suyas."""

    query = db.query(Adopcion).options(
        joinedload(Adopcion.animal), joinedload(Adopcion.adoptante)
    )
    if current.rol == RolUsuario.ADOPTANTE:
        query = query.filter(Adopcion.adoptante_id == current.id)
    elif current.rol not in (RolUsuario.ADMINISTRADOR, RolUsuario.VETERINARIO):
        raise HTTPException(status_code=403, detail="Sin permisos para ver adopciones")
    return query.order_by(Adopcion.fecha_solicitud.desc()).all()


@router.post("", response_model=AdopcionOut, status_code=status.HTTP_201_CREATED)
def solicitar_adopcion(
    payload: AdopcionCreate,
    db: Session = Depends(get_db),
    current: Usuario = Depends(require_roles(RolUsuario.ADOPTANTE)),
):
    """Caso de uso: Solicitar Adopción (Adoptante) -- include Adjuntar Documentos."""

    animal = db.query(Animal).filter(Animal.id == payload.animal_id).first()
    if not animal:
        raise HTTPException(status_code=404, detail="Animal no encontrado")
    if animal.estado != EstadoAnimal.DISPONIBLE:
        raise HTTPException(
            status_code=400, detail="El animal no está disponible para adopción"
        )

    existente = (
        db.query(Adopcion)
        .filter(
            Adopcion.animal_id == animal.id,
            Adopcion.adoptante_id == current.id,
            Adopcion.estado.in_([EstadoAdopcion.PENDIENTE, EstadoAdopcion.EN_REVISION]),
        )
        .first()
    )
    if existente:
        raise HTTPException(
            status_code=400, detail="Ya tiene una solicitud activa para este animal"
        )

    adopcion = Adopcion(
        animal_id=payload.animal_id,
        adoptante_id=current.id,
        motivo=payload.motivo,
        documentos=payload.documentos,
    )
    db.add(adopcion)
    db.commit()
    db.refresh(adopcion)
    return adopcion


@router.put("/{adopcion_id}", response_model=AdopcionOut)
def actualizar_estado_adopcion(
    adopcion_id: int,
    payload: AdopcionUpdate,
    db: Session = Depends(get_db),
    _: Usuario = Depends(require_roles(RolUsuario.ADMINISTRADOR)),
):
    """Caso de uso: Aprobar / Rechazar / Completar Adopción (Administrador)."""

    adopcion = db.query(Adopcion).filter(Adopcion.id == adopcion_id).first()
    if not adopcion:
        raise HTTPException(status_code=404, detail="Adopción no encontrada")

    adopcion.estado = payload.estado
    if payload.observaciones is not None:
        adopcion.observaciones = payload.observaciones
    if payload.estado in (
        EstadoAdopcion.APROBADA,
        EstadoAdopcion.RECHAZADA,
        EstadoAdopcion.COMPLETADA,
    ):
        adopcion.fecha_resolucion = datetime.utcnow()

    if payload.estado == EstadoAdopcion.COMPLETADA:
        animal = db.query(Animal).filter(Animal.id == adopcion.animal_id).first()
        if animal:
            animal.estado = EstadoAnimal.ADOPTADO
    elif payload.estado == EstadoAdopcion.APROBADA:
        animal = db.query(Animal).filter(Animal.id == adopcion.animal_id).first()
        if animal:
            animal.estado = EstadoAnimal.NO_DISPONIBLE

    db.commit()
    db.refresh(adopcion)
    return adopcion


@router.get("/{adopcion_id}", response_model=AdopcionOut)
def detalle_adopcion(
    adopcion_id: int,
    db: Session = Depends(get_db),
    current: Usuario = Depends(get_current_user),
):
    adopcion = (
        db.query(Adopcion)
        .options(joinedload(Adopcion.animal), joinedload(Adopcion.adoptante))
        .filter(Adopcion.id == adopcion_id)
        .first()
    )
    if not adopcion:
        raise HTTPException(status_code=404, detail="Adopción no encontrada")
    if (
        current.rol == RolUsuario.ADOPTANTE
        and adopcion.adoptante_id != current.id
    ):
        raise HTTPException(status_code=403, detail="Sin permisos sobre esta adopción")
    return adopcion
