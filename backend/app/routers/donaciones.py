"""Caso de uso 4.0 - Gestión de Donaciones (Diagrama FDO).

Casos del Donante:
- Realizar Donación  -- include Seleccionar Tipo de Donación
- Obtener Comprobante de Donación
"""

import uuid
from typing import List

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session, joinedload

from app.auth import get_current_user, require_roles
from app.database import get_db
from app.models import Donacion, RolUsuario, Usuario
from app.schemas import DonacionCreate, DonacionOut

router = APIRouter(prefix="/api/donaciones", tags=["Donaciones"])


@router.get("", response_model=List[DonacionOut])
def listar_donaciones(
    db: Session = Depends(get_db),
    current: Usuario = Depends(get_current_user),
):
    query = db.query(Donacion).options(joinedload(Donacion.donante))
    if current.rol == RolUsuario.DONANTE:
        query = query.filter(Donacion.donante_id == current.id)
    elif current.rol != RolUsuario.ADMINISTRADOR:
        raise HTTPException(status_code=403, detail="Sin permisos")
    return query.order_by(Donacion.fecha.desc()).all()


@router.post("", response_model=DonacionOut, status_code=status.HTTP_201_CREATED)
def realizar_donacion(
    payload: DonacionCreate,
    db: Session = Depends(get_db),
    current: Usuario = Depends(require_roles(RolUsuario.DONANTE, RolUsuario.ADMINISTRADOR)),
):
    comprobante = f"COMP-{uuid.uuid4().hex[:10].upper()}"
    donacion = Donacion(
        donante_id=current.id,
        tipo=payload.tipo,
        monto=payload.monto,
        descripcion=payload.descripcion,
        comprobante=comprobante,
    )
    db.add(donacion)
    db.commit()
    db.refresh(donacion)
    return donacion


@router.get("/{donacion_id}", response_model=DonacionOut)
def detalle_donacion(
    donacion_id: int,
    db: Session = Depends(get_db),
    current: Usuario = Depends(get_current_user),
):
    """Caso de uso: Obtener Comprobante de Donación."""

    donacion = (
        db.query(Donacion)
        .options(joinedload(Donacion.donante))
        .filter(Donacion.id == donacion_id)
        .first()
    )
    if not donacion:
        raise HTTPException(status_code=404, detail="Donación no encontrada")
    if (
        current.rol == RolUsuario.DONANTE
        and donacion.donante_id != current.id
    ):
        raise HTTPException(status_code=403, detail="Sin permisos sobre esta donación")
    return donacion
