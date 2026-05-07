"""Caso de uso 3.0 - Gestión de Salud Animal (Diagrama FDO).

Casos de uso del Veterinario:
- Registrar Consulta Médica
- Registrar Tratamiento
- Registrar Vacunación
- Ver Historial Médico
"""

from typing import List, Optional

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session, joinedload

from app.auth import get_current_user, require_roles
from app.database import get_db
from app.models import Animal, HistorialMedico, RolUsuario, Usuario
from app.schemas import HistorialCreate, HistorialOut

router = APIRouter(prefix="/api/historial-medico", tags=["Historial Médico"])


veterinario_only = require_roles(RolUsuario.VETERINARIO, RolUsuario.ADMINISTRADOR)


@router.get("", response_model=List[HistorialOut])
def listar_historial(
    animal_id: Optional[int] = None,
    db: Session = Depends(get_db),
    current: Usuario = Depends(get_current_user),
):
    if current.rol not in (RolUsuario.VETERINARIO, RolUsuario.ADMINISTRADOR):
        raise HTTPException(status_code=403, detail="Sin permisos")
    query = db.query(HistorialMedico).options(
        joinedload(HistorialMedico.animal), joinedload(HistorialMedico.veterinario)
    )
    if animal_id:
        query = query.filter(HistorialMedico.animal_id == animal_id)
    return query.order_by(HistorialMedico.fecha.desc()).all()


@router.post("", response_model=HistorialOut, status_code=status.HTTP_201_CREATED)
def registrar_historial(
    payload: HistorialCreate,
    db: Session = Depends(get_db),
    current: Usuario = Depends(veterinario_only),
):
    animal = db.query(Animal).filter(Animal.id == payload.animal_id).first()
    if not animal:
        raise HTTPException(status_code=404, detail="Animal no encontrado")

    historial = HistorialMedico(
        animal_id=payload.animal_id,
        veterinario_id=current.id,
        tipo=payload.tipo,
        diagnostico=payload.diagnostico,
        tratamiento=payload.tratamiento,
        vacuna=payload.vacuna,
        observaciones=payload.observaciones,
    )
    db.add(historial)
    db.commit()
    db.refresh(historial)
    return historial


@router.get("/animal/{animal_id}", response_model=List[HistorialOut])
def historial_por_animal(
    animal_id: int,
    db: Session = Depends(get_db),
    current: Usuario = Depends(get_current_user),
):
    """Ver Historial Médico de un animal específico."""

    return (
        db.query(HistorialMedico)
        .options(joinedload(HistorialMedico.veterinario))
        .filter(HistorialMedico.animal_id == animal_id)
        .order_by(HistorialMedico.fecha.desc())
        .all()
    )
