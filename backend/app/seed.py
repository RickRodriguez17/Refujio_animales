"""Datos de ejemplo para el Sistema de Gestión del Refugio."""

from datetime import datetime, timedelta

from sqlalchemy.orm import Session

from app.auth import hash_password
from app.database import Base, SessionLocal, engine
from app.models import (
    ActividadVoluntariado,
    Adopcion,
    Animal,
    Donacion,
    EstadoAdopcion,
    EstadoAnimal,
    HistorialMedico,
    InscripcionVoluntariado,
    RolUsuario,
    Sexo,
    TipoDonacion,
    TipoHistorial,
    Usuario,
)


def init_db() -> None:
    Base.metadata.create_all(bind=engine)


def seed_demo_data(db: Session) -> None:
    if db.query(Usuario).count() > 0:
        return

    usuarios = [
        Usuario(
            nombre="Edwin Aguilar",
            email="admin@refugio.bo",
            password_hash=hash_password("admin123"),
            rol=RolUsuario.ADMINISTRADOR,
            telefono="+591 70000001",
            direccion="La Paz, Bolivia",
        ),
        Usuario(
            nombre="Dra. Ana Bustillos",
            email="vet@refugio.bo",
            password_hash=hash_password("vet123"),
            rol=RolUsuario.VETERINARIO,
            telefono="+591 70000002",
        ),
        Usuario(
            nombre="Ricardo Rodríguez",
            email="adoptante@refugio.bo",
            password_hash=hash_password("adopta123"),
            rol=RolUsuario.ADOPTANTE,
            telefono="+591 70000003",
            direccion="Av. 6 de Agosto, La Paz",
        ),
        Usuario(
            nombre="Mauricio Burgoa",
            email="donante@refugio.bo",
            password_hash=hash_password("dona123"),
            rol=RolUsuario.DONANTE,
            telefono="+591 70000004",
        ),
        Usuario(
            nombre="Cristofer Molina",
            email="voluntario@refugio.bo",
            password_hash=hash_password("volun123"),
            rol=RolUsuario.VOLUNTARIO,
            telefono="+591 70000005",
        ),
    ]
    db.add_all(usuarios)
    db.commit()
    for u in usuarios:
        db.refresh(u)

    admin, vet, adoptante, donante, voluntario = usuarios

    animales = [
        Animal(
            nombre="Firulais",
            especie="Perro",
            raza="Mestizo",
            edad=3,
            sexo=Sexo.MACHO,
            tamano="Mediano",
            color="Marrón",
            descripcion="Perro juguetón rescatado de la calle.",
            foto_url=(
                "https://images.unsplash.com/photo-1517849845537-4d257902454a"
                "?auto=format&fit=crop&w=800&q=80"
            ),
            estado=EstadoAnimal.DISPONIBLE,
        ),
        Animal(
            nombre="Luna",
            especie="Gato",
            raza="Doméstica",
            edad=2,
            sexo=Sexo.HEMBRA,
            tamano="Pequeño",
            color="Blanco y negro",
            descripcion="Gata cariñosa, ideal para familia.",
            foto_url=(
                "https://images.unsplash.com/photo-1518791841217-8f162f1e1131"
                "?auto=format&fit=crop&w=800&q=80"
            ),
            estado=EstadoAnimal.DISPONIBLE,
        ),
        Animal(
            nombre="Rocky",
            especie="Perro",
            raza="Labrador",
            edad=5,
            sexo=Sexo.MACHO,
            tamano="Grande",
            color="Negro",
            descripcion="Tranquilo y obediente, en tratamiento veterinario.",
            foto_url=(
                "https://images.unsplash.com/photo-1543466835-00a7907e9de1"
                "?auto=format&fit=crop&w=800&q=80"
            ),
            estado=EstadoAnimal.EN_TRATAMIENTO,
        ),
        Animal(
            nombre="Manchas",
            especie="Perro",
            raza="Dálmata",
            edad=1,
            sexo=Sexo.HEMBRA,
            tamano="Mediano",
            color="Blanco con manchas negras",
            descripcion="Cachorra muy activa, busca un hogar con espacio.",
            foto_url=(
                "https://images.unsplash.com/photo-1583337130417-3346a1be7dee"
                "?auto=format&fit=crop&w=800&q=80"
            ),
            estado=EstadoAnimal.DISPONIBLE,
        ),
        Animal(
            nombre="Michi",
            especie="Gato",
            raza="Siamés",
            edad=4,
            sexo=Sexo.MACHO,
            tamano="Pequeño",
            color="Crema",
            descripcion="Gato adoptado recientemente.",
            estado=EstadoAnimal.ADOPTADO,
        ),
    ]
    db.add_all(animales)
    db.commit()
    for a in animales:
        db.refresh(a)

    db.add_all(
        [
            HistorialMedico(
                animal_id=animales[2].id,
                veterinario_id=vet.id,
                tipo=TipoHistorial.CONSULTA,
                diagnostico="Dermatitis leve",
                tratamiento="Aplicar shampoo medicado por 14 días",
                observaciones="Control en 2 semanas",
                fecha=datetime.utcnow() - timedelta(days=10),
            ),
            HistorialMedico(
                animal_id=animales[0].id,
                veterinario_id=vet.id,
                tipo=TipoHistorial.VACUNA,
                vacuna="Antirrábica",
                observaciones="Vacuna anual aplicada",
                fecha=datetime.utcnow() - timedelta(days=5),
            ),
        ]
    )

    db.add(
        Adopcion(
            animal_id=animales[1].id,
            adoptante_id=adoptante.id,
            estado=EstadoAdopcion.PENDIENTE,
            motivo="Quiero darle un hogar a Luna; ya tengo experiencia con gatos.",
            documentos="cedula.pdf, comprobante_domicilio.pdf",
        )
    )

    db.add_all(
        [
            Donacion(
                donante_id=donante.id,
                tipo=TipoDonacion.DINERO,
                monto=200.0,
                descripcion="Aporte mensual",
                comprobante="COMP-INICIAL01",
            ),
            Donacion(
                donante_id=donante.id,
                tipo=TipoDonacion.ALIMENTO,
                monto=None,
                descripcion="2 sacos de croquetas (15 kg c/u)",
                comprobante="COMP-INICIAL02",
            ),
        ]
    )

    actividad = ActividadVoluntariado(
        titulo="Jornada de limpieza del refugio",
        descripcion=(
            "Ayudaremos a limpiar las jaulas, alimentar a los animales y pasearlos."
        ),
        fecha=datetime.utcnow() + timedelta(days=7),
        horas_estimadas=4,
        cupos=15,
    )
    db.add(actividad)
    db.commit()
    db.refresh(actividad)

    db.add(
        InscripcionVoluntariado(
            voluntario_id=voluntario.id,
            actividad_id=actividad.id,
            horas_registradas=0,
        )
    )

    db.commit()


def main() -> None:
    init_db()
    db = SessionLocal()
    try:
        seed_demo_data(db)
        print("Datos de demostración cargados.")
    finally:
        db.close()


if __name__ == "__main__":
    main()
