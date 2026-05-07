"""Punto de entrada del Sistema de Gestión del Refugio Amor de 4 Patas."""

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.database import SessionLocal
from app.routers import (
    adopciones,
    animales,
    auth,
    donaciones,
    historial_medico,
    reportes,
    usuarios,
    voluntariado,
)
from app.seed import init_db, seed_demo_data

app = FastAPI(
    title="Sistema de Gestión - Refugio Amor de 4 Patas",
    description=(
        "API del sistema de gestión del refugio de animales. "
        "Implementa los casos de uso descritos en el informe: gestión de "
        "animales, adopciones, salud animal, donaciones y voluntariado."
    ),
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.on_event("startup")
def on_startup() -> None:
    init_db()
    db = SessionLocal()
    try:
        seed_demo_data(db)
    finally:
        db.close()


@app.get("/")
def root():
    return {
        "nombre": "Refugio Amor de 4 Patas - API",
        "version": "1.0.0",
        "docs": "/docs",
    }


@app.get("/api/health")
def health():
    return {"status": "ok"}


app.include_router(auth.router)
app.include_router(usuarios.router)
app.include_router(animales.router)
app.include_router(adopciones.router)
app.include_router(historial_medico.router)
app.include_router(donaciones.router)
app.include_router(voluntariado.router)
app.include_router(reportes.router)
