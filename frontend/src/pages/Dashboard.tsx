import { Link } from "react-router-dom";
import { useAuth } from "../auth/AuthContext";

const QUICK_LINKS: Record<string, { to: string; label: string; desc: string }[]> = {
  administrador: [
    { to: "/animales", label: "Animales", desc: "Registrar y gestionar animales rescatados." },
    { to: "/adopciones", label: "Adopciones", desc: "Aprobar o rechazar solicitudes." },
    { to: "/donaciones", label: "Donaciones", desc: "Controlar las donaciones recibidas." },
    { to: "/usuarios", label: "Usuarios", desc: "Gestionar usuarios del sistema." },
    { to: "/reportes", label: "Reportes", desc: "Resumen general del refugio." },
    { to: "/voluntariado", label: "Voluntariado", desc: "Crear actividades para voluntarios." },
  ],
  veterinario: [
    { to: "/animales", label: "Animales", desc: "Consultar animales del refugio." },
    {
      to: "/historial-medico",
      label: "Historial Médico",
      desc: "Registrar consultas, tratamientos y vacunas.",
    },
  ],
  adoptante: [
    { to: "/animales", label: "Buscar animales", desc: "Encuentra a tu nuevo amigo." },
    { to: "/adopciones", label: "Mis solicitudes", desc: "Sigue el estado de tus adopciones." },
  ],
  donante: [
    { to: "/donaciones", label: "Realizar donación", desc: "Apoya al refugio." },
  ],
  voluntario: [
    { to: "/voluntariado", label: "Actividades", desc: "Inscríbete y registra tus horas." },
  ],
};

export function DashboardPage() {
  const { user } = useAuth();
  if (!user) return null;
  const links = QUICK_LINKS[user.rol] || [];

  return (
    <div className="space-y-6">
      <div className="card p-6 bg-gradient-to-r from-refugio-rojo to-refugio-rojoDark text-white">
        <h1 className="text-2xl font-bold">¡Hola, {user.nombre}!</h1>
        <p className="opacity-90 mt-1">
          Bienvenido al sistema de gestión del{" "}
          <span className="font-semibold">Refugio Amor de 4 Patas</span>.
        </p>
      </div>

      <section>
        <h2 className="text-lg font-semibold text-slate-700 mb-3">
          Accesos rápidos
        </h2>
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {links.map((l) => (
            <Link
              key={l.to}
              to={l.to}
              className="card p-5 hover:shadow-md hover:-translate-y-0.5 transition"
            >
              <div className="font-semibold text-refugio-rojo">{l.label}</div>
              <div className="text-sm text-slate-600 mt-1">{l.desc}</div>
            </Link>
          ))}
        </div>
      </section>

      <section className="card p-6">
        <h2 className="text-lg font-semibold text-slate-700">Sobre el refugio</h2>
        <div className="grid md:grid-cols-3 gap-4 mt-3">
          <div>
            <div className="font-semibold text-slate-700">Misión</div>
            <p className="text-sm text-slate-600">
              Brindar protección, cuidado y una segunda oportunidad a animales
              abandonados, promoviendo la adopción responsable y la
              concientización social.
            </p>
          </div>
          <div>
            <div className="font-semibold text-slate-700">Visión</div>
            <p className="text-sm text-slate-600">
              Ser un refugio reconocido a nivel local por su compromiso con el
              bienestar animal y su impacto positivo en la sociedad.
            </p>
          </div>
          <div>
            <div className="font-semibold text-slate-700">Objetivos</div>
            <ul className="text-sm text-slate-600 list-disc list-inside">
              <li>Registrar animales rescatados</li>
              <li>Controlar adopciones</li>
              <li>Gestionar donaciones</li>
              <li>Administrar voluntarios</li>
            </ul>
          </div>
        </div>
      </section>
    </div>
  );
}
