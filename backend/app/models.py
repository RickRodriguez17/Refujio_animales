"""Modelos SQLAlchemy del Sistema de Gestión del Refugio de Animales.

Las tablas reflejan los almacenes de datos del Diagrama FDO:
- D1 ANIMALES        -> animales
- D2 ADOPCIONES      -> adopciones
- D3 HISTORIAL MÉDICO -> historial_medico
- D4 DONACIONES      -> donaciones

Más las tablas de soporte: usuarios, actividades_voluntariado e
inscripciones_voluntariado, según los casos de uso del informe.
"""

from datetime import datetime
from enum import Enum

from sqlalchemy import (
    Column,
    DateTime,
    Enum as SAEnum,
    Float,
    ForeignKey,
    Integer,
    String,
    Text,
)
from sqlalchemy.orm import relationship

from app.database import Base


class RolUsuario(str, Enum):
    """Roles definidos en el Diagrama de Casos de Uso."""

    ADMINISTRADOR = "administrador"
    VETERINARIO = "veterinario"
    ADOPTANTE = "adoptante"
    DONANTE = "donante"
    VOLUNTARIO = "voluntario"


class EstadoAnimal(str, Enum):
    DISPONIBLE = "disponible"
    EN_TRATAMIENTO = "en_tratamiento"
    ADOPTADO = "adoptado"
    NO_DISPONIBLE = "no_disponible"


class Sexo(str, Enum):
    MACHO = "macho"
    HEMBRA = "hembra"


class EstadoAdopcion(str, Enum):
    PENDIENTE = "pendiente"
    EN_REVISION = "en_revision"
    APROBADA = "aprobada"
    RECHAZADA = "rechazada"
    COMPLETADA = "completada"


class TipoHistorial(str, Enum):
    CONSULTA = "consulta"
    TRATAMIENTO = "tratamiento"
    VACUNA = "vacuna"
    CIRUGIA = "cirugia"


class TipoDonacion(str, Enum):
    DINERO = "dinero"
    ALIMENTO = "alimento"
    MEDICINAS = "medicinas"
    INSUMOS = "insumos"
    OTRO = "otro"


class Usuario(Base):
    __tablename__ = "usuarios"

    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(120), nullable=False)
    email = Column(String(120), unique=True, index=True, nullable=False)
    password_hash = Column(String(255), nullable=False)
    rol = Column(SAEnum(RolUsuario), nullable=False, default=RolUsuario.ADOPTANTE)
    telefono = Column(String(30), nullable=True)
    direccion = Column(String(255), nullable=True)
    activo = Column(Integer, default=1, nullable=False)
    creado_en = Column(DateTime, default=datetime.utcnow, nullable=False)

    adopciones = relationship("Adopcion", back_populates="adoptante", cascade="all, delete")
    historiales = relationship("HistorialMedico", back_populates="veterinario")
    donaciones = relationship("Donacion", back_populates="donante", cascade="all, delete")
    inscripciones = relationship(
        "InscripcionVoluntariado", back_populates="voluntario", cascade="all, delete"
    )


class Animal(Base):
    """D1 - ANIMALES."""

    __tablename__ = "animales"

    id = Column(Integer, primary_key=True, index=True)
    nombre = Column(String(80), nullable=False)
    especie = Column(String(40), nullable=False)
    raza = Column(String(80), nullable=True)
    edad = Column(Integer, nullable=True)
    sexo = Column(SAEnum(Sexo), nullable=False, default=Sexo.MACHO)
    tamano = Column(String(40), nullable=True)
    color = Column(String(60), nullable=True)
    descripcion = Column(Text, nullable=True)
    foto_url = Column(String(500), nullable=True)
    estado = Column(SAEnum(EstadoAnimal), nullable=False, default=EstadoAnimal.DISPONIBLE)
    fecha_ingreso = Column(DateTime, default=datetime.utcnow, nullable=False)

    adopciones = relationship("Adopcion", back_populates="animal", cascade="all, delete")
    historiales = relationship(
        "HistorialMedico", back_populates="animal", cascade="all, delete"
    )


class Adopcion(Base):
    """D2 - ADOPCIONES."""

    __tablename__ = "adopciones"

    id = Column(Integer, primary_key=True, index=True)
    animal_id = Column(Integer, ForeignKey("animales.id"), nullable=False)
    adoptante_id = Column(Integer, ForeignKey("usuarios.id"), nullable=False)
    fecha_solicitud = Column(DateTime, default=datetime.utcnow, nullable=False)
    fecha_resolucion = Column(DateTime, nullable=True)
    estado = Column(SAEnum(EstadoAdopcion), nullable=False, default=EstadoAdopcion.PENDIENTE)
    motivo = Column(Text, nullable=True)
    documentos = Column(Text, nullable=True)  # nombres / referencias de documentos
    observaciones = Column(Text, nullable=True)

    animal = relationship("Animal", back_populates="adopciones")
    adoptante = relationship("Usuario", back_populates="adopciones")


class HistorialMedico(Base):
    """D3 - HISTORIAL MÉDICO."""

    __tablename__ = "historial_medico"

    id = Column(Integer, primary_key=True, index=True)
    animal_id = Column(Integer, ForeignKey("animales.id"), nullable=False)
    veterinario_id = Column(Integer, ForeignKey("usuarios.id"), nullable=False)
    fecha = Column(DateTime, default=datetime.utcnow, nullable=False)
    tipo = Column(SAEnum(TipoHistorial), nullable=False, default=TipoHistorial.CONSULTA)
    diagnostico = Column(Text, nullable=True)
    tratamiento = Column(Text, nullable=True)
    vacuna = Column(String(120), nullable=True)
    observaciones = Column(Text, nullable=True)

    animal = relationship("Animal", back_populates="historiales")
    veterinario = relationship("Usuario", back_populates="historiales")


class Donacion(Base):
    """D4 - DONACIONES."""

    __tablename__ = "donaciones"

    id = Column(Integer, primary_key=True, index=True)
    donante_id = Column(Integer, ForeignKey("usuarios.id"), nullable=False)
    tipo = Column(SAEnum(TipoDonacion), nullable=False, default=TipoDonacion.DINERO)
    monto = Column(Float, nullable=True)
    descripcion = Column(Text, nullable=True)
    comprobante = Column(String(120), nullable=True)
    fecha = Column(DateTime, default=datetime.utcnow, nullable=False)

    donante = relationship("Usuario", back_populates="donaciones")


class ActividadVoluntariado(Base):
    __tablename__ = "actividades_voluntariado"

    id = Column(Integer, primary_key=True, index=True)
    titulo = Column(String(120), nullable=False)
    descripcion = Column(Text, nullable=True)
    fecha = Column(DateTime, nullable=False)
    horas_estimadas = Column(Integer, nullable=False, default=2)
    cupos = Column(Integer, nullable=False, default=10)
    creada_en = Column(DateTime, default=datetime.utcnow, nullable=False)

    inscripciones = relationship(
        "InscripcionVoluntariado", back_populates="actividad", cascade="all, delete"
    )


class InscripcionVoluntariado(Base):
    __tablename__ = "inscripciones_voluntariado"

    id = Column(Integer, primary_key=True, index=True)
    voluntario_id = Column(Integer, ForeignKey("usuarios.id"), nullable=False)
    actividad_id = Column(Integer, ForeignKey("actividades_voluntariado.id"), nullable=False)
    horas_registradas = Column(Integer, nullable=False, default=0)
    creada_en = Column(DateTime, default=datetime.utcnow, nullable=False)

    voluntario = relationship("Usuario", back_populates="inscripciones")
    actividad = relationship("ActividadVoluntariado", back_populates="inscripciones")
