"""Casos de uso: Iniciar Sesión, Cerrar Sesión, Validar Credenciales."""

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from app.auth import crear_token, get_current_user, hash_password, verify_password
from app.database import get_db
from app.models import RolUsuario, Usuario
from app.schemas import LoginInput, TokenOut, UsuarioCreate, UsuarioOut

router = APIRouter(prefix="/api/auth", tags=["Autenticación"])


@router.post("/login", response_model=TokenOut)
def login(payload: LoginInput, db: Session = Depends(get_db)):
    user = db.query(Usuario).filter(Usuario.email == payload.email).first()
    if not user or not verify_password(payload.password, user.password_hash):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Email o contraseña incorrectos",
        )
    if not user.activo:
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Usuario inactivo")

    token = crear_token({"sub": str(user.id), "rol": user.rol.value})
    return {"access_token": token, "token_type": "bearer", "user": user}


@router.post("/registro", response_model=UsuarioOut, status_code=status.HTTP_201_CREATED)
def registro_publico(payload: UsuarioCreate, db: Session = Depends(get_db)):
    """Auto-registro público: solo permite roles Adoptante / Donante / Voluntario.

    El registro de Administradores y Veterinarios lo realiza un Administrador.
    """

    if payload.rol in (RolUsuario.ADMINISTRADOR, RolUsuario.VETERINARIO):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Este rol solo puede ser creado por un administrador",
        )

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


@router.get("/me", response_model=UsuarioOut)
def me(current_user: Usuario = Depends(get_current_user)):
    return current_user
