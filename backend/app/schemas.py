"""Esquemas Pydantic para validación de entrada / salida de la API."""

from datetime import datetime
from typing import Optional

from pydantic import BaseModel, ConfigDict, EmailStr, Field

from app.models import (
    EstadoAdopcion,
    EstadoAnimal,
    RolUsuario,
    Sexo,
    TipoDonacion,
    TipoHistorial,
)


# ---------- Autenticación ----------


class LoginInput(BaseModel):
    email: EmailStr
    password: str


class TokenOut(BaseModel):
    access_token: str
    token_type: str = "bearer"
    user: "UsuarioOut"


# ---------- Usuarios ----------


class UsuarioBase(BaseModel):
    nombre: str = Field(min_length=2, max_length=120)
    email: EmailStr
    rol: RolUsuario = RolUsuario.ADOPTANTE
    telefono: Optional[str] = None
    direccion: Optional[str] = None


class UsuarioCreate(UsuarioBase):
    password: str = Field(min_length=4, max_length=128)


class UsuarioUpdate(BaseModel):
    nombre: Optional[str] = None
    telefono: Optional[str] = None
    direccion: Optional[str] = None
    rol: Optional[RolUsuario] = None
    activo: Optional[bool] = None
    password: Optional[str] = None


class UsuarioOut(UsuarioBase):
    id: int
    activo: bool
    creado_en: datetime

    model_config = ConfigDict(from_attributes=True)


# ---------- Animales ----------


class AnimalBase(BaseModel):
    nombre: str
    especie: str
    raza: Optional[str] = None
    edad: Optional[int] = None
    sexo: Sexo = Sexo.MACHO
    tamano: Optional[str] = None
    color: Optional[str] = None
    descripcion: Optional[str] = None
    foto_url: Optional[str] = None
    estado: EstadoAnimal = EstadoAnimal.DISPONIBLE


class AnimalCreate(AnimalBase):
    pass


class AnimalUpdate(BaseModel):
    nombre: Optional[str] = None
    especie: Optional[str] = None
    raza: Optional[str] = None
    edad: Optional[int] = None
    sexo: Optional[Sexo] = None
    tamano: Optional[str] = None
    color: Optional[str] = None
    descripcion: Optional[str] = None
    foto_url: Optional[str] = None
    estado: Optional[EstadoAnimal] = None


class AnimalOut(AnimalBase):
    id: int
    fecha_ingreso: datetime

    model_config = ConfigDict(from_attributes=True)


# ---------- Adopciones ----------


class AdopcionCreate(BaseModel):
    animal_id: int
    motivo: Optional[str] = None
    documentos: Optional[str] = None


class AdopcionUpdate(BaseModel):
    estado: EstadoAdopcion
    observaciones: Optional[str] = None


class AdopcionOut(BaseModel):
    id: int
    animal_id: int
    adoptante_id: int
    fecha_solicitud: datetime
    fecha_resolucion: Optional[datetime] = None
    estado: EstadoAdopcion
    motivo: Optional[str] = None
    documentos: Optional[str] = None
    observaciones: Optional[str] = None
    animal: Optional[AnimalOut] = None
    adoptante: Optional[UsuarioOut] = None

    model_config = ConfigDict(from_attributes=True)


# ---------- Historial Médico ----------


class HistorialCreate(BaseModel):
    animal_id: int
    tipo: TipoHistorial = TipoHistorial.CONSULTA
    diagnostico: Optional[str] = None
    tratamiento: Optional[str] = None
    vacuna: Optional[str] = None
    observaciones: Optional[str] = None


class HistorialOut(BaseModel):
    id: int
    animal_id: int
    veterinario_id: int
    fecha: datetime
    tipo: TipoHistorial
    diagnostico: Optional[str] = None
    tratamiento: Optional[str] = None
    vacuna: Optional[str] = None
    observaciones: Optional[str] = None
    animal: Optional[AnimalOut] = None
    veterinario: Optional[UsuarioOut] = None

    model_config = ConfigDict(from_attributes=True)


# ---------- Donaciones ----------


class DonacionCreate(BaseModel):
    tipo: TipoDonacion = TipoDonacion.DINERO
    monto: Optional[float] = None
    descripcion: Optional[str] = None


class DonacionOut(BaseModel):
    id: int
    donante_id: int
    tipo: TipoDonacion
    monto: Optional[float] = None
    descripcion: Optional[str] = None
    comprobante: Optional[str] = None
    fecha: datetime
    donante: Optional[UsuarioOut] = None

    model_config = ConfigDict(from_attributes=True)


# ---------- Voluntariado ----------


class ActividadCreate(BaseModel):
    titulo: str
    descripcion: Optional[str] = None
    fecha: datetime
    horas_estimadas: int = 2
    cupos: int = 10


class ActividadOut(BaseModel):
    id: int
    titulo: str
    descripcion: Optional[str] = None
    fecha: datetime
    horas_estimadas: int
    cupos: int
    creada_en: datetime
    inscritos: int = 0

    model_config = ConfigDict(from_attributes=True)


class InscripcionCreate(BaseModel):
    actividad_id: int


class InscripcionUpdate(BaseModel):
    horas_registradas: int = Field(ge=0)


class InscripcionOut(BaseModel):
    id: int
    voluntario_id: int
    actividad_id: int
    horas_registradas: int
    creada_en: datetime
    actividad: Optional[ActividadOut] = None
    voluntario: Optional[UsuarioOut] = None

    model_config = ConfigDict(from_attributes=True)


# ---------- Reportes ----------


class ResumenReporte(BaseModel):
    total_animales: int
    animales_disponibles: int
    animales_adoptados: int
    animales_en_tratamiento: int
    total_adopciones: int
    adopciones_pendientes: int
    adopciones_aprobadas: int
    total_donaciones: int
    monto_total_donado: float
    total_voluntarios: int
    total_actividades: int
    horas_voluntariado: int


TokenOut.model_rebuild()
