"""Caso de uso: Gestionar Usuarios (Administrador)."""

from typing import List

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from app.auth import hash_password, require_roles
from app.database import get_db
from app.models import RolUsuario, Usuario
from app.schemas import UsuarioCreate, UsuarioOut, UsuarioUpdate

router = APIRouter(prefix="/api/usuarios", tags=["Usuarios"])


admin_only = require_roles(RolUsuario.ADMINISTRADOR)


@router.get("", response_model=List[UsuarioOut])
def listar_usuarios(
    db: Session = Depends(get_db), _: Usuario = Depends(admin_only)
):
    return db.query(Usuario).order_by(Usuario.id.desc()).all()


@router.post("", response_model=UsuarioOut, status_code=status.HTTP_201_CREATED)
def crear_usuario(
    payload: UsuarioCreate,
    db: Session = Depends(get_db),
    _: Usuario = Depends(admin_only),
):
    if db.query(Usuario).filter(Usuario.email == payload.email).first():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Ya existe un usuario con ese email",
        )
    user = Usuario(
        nombre=payload.nombre,
        email=payload.email,
        password_hash=hash_password(payload.password),
        rol=payload.rol,
        telefono=payload.telefono,
        direccion=payload.direccion,
    )
    db.add(user)
    db.commit()
    db.refresh(user)
    return user


@router.put("/{usuario_id}", response_model=UsuarioOut)
def actualizar_usuario(
    usuario_id: int,
    payload: UsuarioUpdate,
    db: Session = Depends(get_db),
    _: Usuario = Depends(admin_only),
):
    user = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not user:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")

    if payload.nombre is not None:
        user.nombre = payload.nombre
    if payload.telefono is not None:
        user.telefono = payload.telefono
    if payload.direccion is not None:
        user.direccion = payload.direccion
    if payload.rol is not None:
        user.rol = payload.rol
    if payload.activo is not None:
        user.activo = 1 if payload.activo else 0
    if payload.password:
        user.password_hash = hash_password(payload.password)

    db.commit()
    db.refresh(user)
    return user


@router.delete("/{usuario_id}", status_code=status.HTTP_204_NO_CONTENT)
def eliminar_usuario(
    usuario_id: int,
    db: Session = Depends(get_db),
    current: Usuario = Depends(admin_only),
):
    if usuario_id == current.id:
        raise HTTPException(status_code=400, detail="No puede eliminar su propio usuario")
    user = db.query(Usuario).filter(Usuario.id == usuario_id).first()
    if not user:
        raise HTTPException(status_code=404, detail="Usuario no encontrado")
    db.delete(user)
    db.commit()
    return None
