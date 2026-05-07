"""Caso de uso 1.0 - Gestión de Animales (Diagrama FDO)."""

from typing import List, Optional

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy.orm import Session

from app.auth import get_current_user, require_roles
from app.database import get_db
from app.models import Animal, EstadoAnimal, RolUsuario, Usuario
from app.schemas import AnimalCreate, AnimalOut, AnimalUpdate

router = APIRouter(prefix="/api/animales", tags=["Animales"])


admin_only = require_roles(RolUsuario.ADMINISTRADOR)


@router.get("", response_model=List[AnimalOut])
def listar_animales(
    estado: Optional[EstadoAnimal] = None,
    especie: Optional[str] = None,
    busqueda: Optional[str] = Query(None, description="Buscar por nombre o raza"),
    db: Session = Depends(get_db),
):
    """Caso de uso: Buscar Animales Disponibles / Listar Animales (público)."""

    query = db.query(Animal)
    if estado:
        query = query.filter(Animal.estado == estado)
    if especie:
        query = query.filter(Animal.especie.ilike(f"%{especie}%"))
    if busqueda:
        like = f"%{busqueda}%"
        query = query.filter((Animal.nombre.ilike(like)) | (Animal.raza.ilike(like)))
    return query.order_by(Animal.fecha_ingreso.desc()).all()


@router.get("/{animal_id}", response_model=AnimalOut)
def obtener_animal(animal_id: int, db: Session = Depends(get_db)):
    """Caso de uso: Ver Detalle de Animal (público)."""

    animal = db.query(Animal).filter(Animal.id == animal_id).first()
    if not animal:
        raise HTTPException(status_code=404, detail="Animal no encontrado")
    return animal


@router.post("", response_model=AnimalOut, status_code=status.HTTP_201_CREATED)
def crear_animal(
    payload: AnimalCreate,
    db: Session = Depends(get_db),
    _: Usuario = Depends(admin_only),
):
    """Caso de uso: Registrar Animal (Administrador)."""

    animal = Animal(**payload.model_dump())
    db.add(animal)
    db.commit()
    db.refresh(animal)
    return animal


@router.put("/{animal_id}", response_model=AnimalOut)
def actualizar_animal(
    animal_id: int,
    payload: AnimalUpdate,
    db: Session = Depends(get_db),
    _: Usuario = Depends(admin_only),
):
    animal = db.query(Animal).filter(Animal.id == animal_id).first()
    if not animal:
        raise HTTPException(status_code=404, detail="Animal no encontrado")
    data = payload.model_dump(exclude_none=True)
    for k, v in data.items():
        setattr(animal, k, v)
    db.commit()
    db.refresh(animal)
    return animal


@router.delete("/{animal_id}", status_code=status.HTTP_204_NO_CONTENT)
def eliminar_animal(
    animal_id: int,
    db: Session = Depends(get_db),
    _: Usuario = Depends(admin_only),
):
    animal = db.query(Animal).filter(Animal.id == animal_id).first()
    if not animal:
        raise HTTPException(status_code=404, detail="Animal no encontrado")
    db.delete(animal)
    db.commit()
    return None
