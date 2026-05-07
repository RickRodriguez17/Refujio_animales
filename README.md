# Refugio Amor de 4 Patas — Sistema de Gestión

Sistema web full-stack para la gestión integral del **Refugio de Animales "Amor de 4 Patas"**, construido a partir del informe y los diagramas (contexto, FDO y casos de uso) provistos.

> *“Cada vida importa, cada acción cuenta”.*

## Stack tecnológico

| Capa       | Tecnología                                                                 |
|------------|----------------------------------------------------------------------------|
| Backend    | **FastAPI** (Python 3.11) + SQLAlchemy + SQLite + JWT (python-jose) + bcrypt |
| Frontend   | **React 18** + TypeScript + Vite + Tailwind CSS + React Router            |
| Auth       | JSON Web Tokens (Bearer) con bcrypt para contraseñas                       |

## Estructura del repositorio

```
Refujio_animales/
├── backend/        # API FastAPI
│   ├── app/
│   │   ├── main.py
│   │   ├── models.py
│   │   ├── schemas.py
│   │   ├── auth.py
│   │   ├── seed.py
│   │   └── routers/
│   ├── requirements.txt
│   └── README.md
├── frontend/       # SPA React + Vite + Tailwind
│   ├── src/
│   │   ├── pages/
│   │   ├── components/
│   │   └── auth/
│   └── package.json
└── docs/diagramas/ # Diagramas extraídos del informe
```

## Roles del sistema (según diagrama de casos de uso)

| Rol           | Casos de uso principales                                              |
|---------------|-----------------------------------------------------------------------|
| Administrador | Gestionar usuarios, animales, adopciones, donaciones, voluntariado, reportes |
| Veterinario   | Consultar animales, registrar consultas / tratamientos / vacunas       |
| Adoptante     | Buscar animales, solicitar adopción y adjuntar documentos              |
| Donante       | Registrar donaciones (dinero / alimento / medicinas / insumos) y obtener comprobante |
| Voluntario    | Inscribirse a actividades y registrar horas voluntariado              |

## Arranque rápido (modo desarrollo)

### 1. Backend

```bash
cd backend
python3 -m venv .venv
source .venv/bin/activate          # Linux / macOS
# .venv\Scripts\activate            # Windows
pip install -r requirements.txt
cp .env.example .env
uvicorn app.main:app --reload
```

API disponible en <http://localhost:8000>. Documentación interactiva en <http://localhost:8000/docs>.

La base de datos SQLite (`refugio.db`) se crea y se siembra con datos de demostración la primera vez que arranca.

### 2. Frontend

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

Aplicación en <http://localhost:5173>.

## Usuarios de demostración (cargados por seed)

| Rol           | Email                       | Contraseña |
|---------------|-----------------------------|------------|
| Administrador | `admin@refugio.bo`          | `admin123` |
| Veterinario   | `vet@refugio.bo`            | `vet123`   |
| Adoptante     | `adoptante@refugio.bo`      | `adopta123`|
| Donante       | `donante@refugio.bo`        | `dona123`  |
| Voluntario    | `voluntario@refugio.bo`     | `volun123` |

> Cambia las contraseñas en producción.

## Diagramas

Los diagramas extraídos del informe se encuentran en [`docs/diagramas/`](docs/diagramas):

| Archivo                       | Descripción                                  |
|-------------------------------|----------------------------------------------|
| `00_logo.png`                 | Logo institucional “Refugio Amor de 4 Patas” |
| `01_organigrama.png`          | Organigrama del refugio                      |
| `02_diagrama_contexto.png`    | Diagrama de contexto del sistema             |
| `03_dfd_nivel0.png` / `04`    | Diagrama de Flujo de Datos (Nivel 0/1)       |
| `05_diagrama_entidades.png`   | Diagrama relacional / FDO                    |
| `06_casos_de_uso.png`         | Casos de uso por actor                       |

## Mapa de funcionalidades vs. diagramas

* **Diagrama de contexto** → Endpoints REST públicos para registro / login y consulta de animales; flujo bidireccional con cada actor desde el frontend.
* **DFD nivel 0/1** → Procesos `Animales`, `Adopciones`, `Historial Médico`, `Donaciones`, `Voluntariado`, `Reportes`; cada uno con su router en `backend/app/routers/`.
* **Diagrama relacional (FDO)** → Modelos SQLAlchemy en `backend/app/models.py` (Usuario, Animal, Adopcion, HistorialMedico, Donacion, Actividad, Inscripcion).
* **Casos de uso** → Páginas y rutas protegidas por rol en el frontend; permisos enforced en el backend con `Depends(require_roles(...))`.

## Scripts útiles

| Comando                                        | Descripción                       |
|------------------------------------------------|-----------------------------------|
| `cd backend && uvicorn app.main:app --reload`  | Arranca API en modo desarrollo    |
| `cd frontend && npm run dev`                   | Arranca SPA en modo desarrollo    |
| `cd frontend && npm run build`                 | Compila SPA para producción       |
| `cd frontend && npm run lint`                  | Type-check con TypeScript         |

## Licencia

Proyecto académico — uso libre con fines educativos.
