# Backend - Sistema de Gestión Refugio Amor de 4 Patas

API REST construida con **FastAPI**, **SQLAlchemy** y **SQLite**, fiel al
informe del proyecto y a los siguientes diagramas:

- Diagrama de Contexto
- Diagrama FDO (Flujo de Datos): procesos 1.0 a 4.0 y almacenes D1–D4
- Diagrama de Casos de Uso

## Requisitos

- Python 3.11+
- pip

## Instalación

```bash
cd backend
python -m venv .venv
source .venv/bin/activate           # Windows: .venv\Scripts\activate
pip install -r requirements.txt
cp .env.example .env
```

## Ejecutar

```bash
uvicorn app.main:app --reload
```

La API quedará disponible en http://localhost:8000 y la documentación
interactiva (Swagger) en http://localhost:8000/docs.

Al primer arranque se crea la base SQLite `refugio.db` y se cargan **datos
de demostración** (usuarios de cada rol, animales, una adopción pendiente,
donaciones y una actividad de voluntariado).

## Usuarios demo

| Rol           | Email                    | Contraseña |
|---------------|--------------------------|------------|
| Administrador | admin@refugio.bo         | admin123   |
| Veterinario   | vet@refugio.bo           | vet123     |
| Adoptante     | adoptante@refugio.bo     | adopta123  |
| Donante       | donante@refugio.bo       | dona123    |
| Voluntario    | voluntario@refugio.bo    | volun123   |

## Estructura

```
backend/
├── app/
│   ├── main.py            # FastAPI app + CORS + montaje de routers
│   ├── config.py          # Settings (lee .env)
│   ├── database.py        # SQLAlchemy engine + sesión
│   ├── models.py          # Tablas (D1-D4 + soporte)
│   ├── schemas.py         # Pydantic v2 schemas
│   ├── auth.py            # Hashing + JWT + dependencias
│   ├── seed.py            # Datos de demostración
│   └── routers/
│       ├── auth.py             # Iniciar / Cerrar sesión, /me, registro
│       ├── usuarios.py         # Gestionar Usuarios (Admin)
│       ├── animales.py         # 1.0 Gestión de Animales
│       ├── adopciones.py       # 2.0 Gestión de Adopciones
│       ├── historial_medico.py # 3.0 Gestión de Salud Animal
│       ├── donaciones.py       # 4.0 Gestión de Donaciones
│       ├── voluntariado.py     # Actividades + inscripciones + horas
│       └── reportes.py         # Generar Reportes (Admin)
└── requirements.txt
```

## Endpoints principales

- `POST /api/auth/login` — Iniciar Sesión
- `POST /api/auth/registro` — Registro público (Adoptante / Donante / Voluntario)
- `GET  /api/auth/me` — Validar credenciales del token actual
- `GET/POST/PUT/DELETE /api/animales` — Gestionar Animales
- `GET/POST/PUT /api/adopciones` — Solicitar / Aprobar / Rechazar adopciones
- `GET/POST /api/historial-medico` — Consultas, tratamientos, vacunas
- `GET/POST /api/donaciones` — Realizar donaciones, comprobante
- `GET/POST /api/voluntariado/actividades` — Actividades y cupos
- `POST/PUT /api/voluntariado/inscripciones` — Inscribirse + registrar horas
- `GET /api/reportes/resumen` — Reporte general (Administrador)
