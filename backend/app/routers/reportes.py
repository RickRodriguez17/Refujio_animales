"""Caso de uso: Generar Reportes (Administrador)."""

from fastapi import APIRouter, Depends
from sqlalchemy import func
from sqlalchemy.orm import Session

from app.auth import require_roles
from app.database import get_db
from app.models import (
    Adopcion,
    Animal,
    Donacion,
    EstadoAdopcion,
    EstadoAnimal,
    InscripcionVoluntariado,
    ActividadVoluntariado,
    RolUsuario,
    Usuario,
)
from app.schemas import ResumenReporte

router = APIRouter(prefix="/api/reportes", tags=["Reportes"])


@router.get("/resumen", response_model=ResumenReporte)
def resumen_general(
    db: Session = Depends(get_db),
    _: Usuario = Depends(require_roles(RolUsuario.ADMINISTRADOR)),
):
    total_animales = db.query(Animal).count()
    disponibles = db.query(Animal).filter(Animal.estado == EstadoAnimal.DISPONIBLE).count()
    adoptados = db.query(Animal).filter(Animal.estado == EstadoAnimal.ADOPTADO).count()
    en_tratamiento = (
        db.query(Animal).filter(Animal.estado == EstadoAnimal.EN_TRATAMIENTO).count()
    )

    total_adopciones = db.query(Adopcion).count()
    pendientes = (
        db.query(Adopcion).filter(Adopcion.estado == EstadoAdopcion.PENDIENTE).count()
    )
    aprobadas = (
        db.query(Adopcion)
        .filter(
            Adopcion.estado.in_(
                [EstadoAdopcion.APROBADA, EstadoAdopcion.COMPLETADA]
            )
        )
        .count()
    )

    total_donaciones = db.query(Donacion).count()
    monto_total = db.query(func.coalesce(func.sum(Donacion.monto), 0.0)).scalar() or 0.0

    total_voluntarios = (
        db.query(Usuario).filter(Usuario.rol == RolUsuario.VOLUNTARIO).count()
    )
    total_actividades = db.query(ActividadVoluntariado).count()
    horas = (
        db.query(func.coalesce(func.sum(InscripcionVoluntariado.horas_registradas), 0)).scalar()
        or 0
    )

    return ResumenReporte(
        total_animales=total_animales,
        animales_disponibles=disponibles,
        animales_adoptados=adoptados,
        animales_en_tratamiento=en_tratamiento,
        total_adopciones=total_adopciones,
        adopciones_pendientes=pendientes,
        adopciones_aprobadas=aprobadas,
        total_donaciones=total_donaciones,
        monto_total_donado=float(monto_total),
        total_voluntarios=total_voluntarios,
        total_actividades=total_actividades,
        horas_voluntariado=int(horas),
    )
