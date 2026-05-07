import { Link, NavLink, Outlet, useNavigate } from "react-router-dom";
import { useAuth } from "../auth/AuthContext";
import { Logo } from "./Logo";
import type { Rol } from "../types";

interface MenuItem {
  to: string;
  label: string;
  roles: Rol[] | "all";
}

const MENU: MenuItem[] = [
  { to: "/dashboard", label: "Inicio", roles: "all" },
  { to: "/animales", label: "Animales", roles: "all" },
  {
    to: "/adopciones",
    label: "Adopciones",
    roles: ["administrador", "veterinario", "adoptante"],
  },
  {
    to: "/historial-medico",
    label: "Historial Médico",
    roles: ["administrador", "veterinario"],
  },
  {
    to: "/donaciones",
    label: "Donaciones",
    roles: ["administrador", "donante"],
  },
  {
    to: "/voluntariado",
    label: "Voluntariado",
    roles: ["administrador", "voluntario"],
  },
  { to: "/usuarios", label: "Usuarios", roles: ["administrador"] },
  { to: "/reportes", label: "Reportes", roles: ["administrador"] },
];

const ROLE_LABELS: Record<Rol, string> = {
  administrador: "Administrador",
  veterinario: "Veterinario",
  adoptante: "Adoptante",
  donante: "Donante",
  voluntario: "Voluntario",
};

export function Layout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  if (!user) return null;

  const items = MENU.filter(
    (m) => m.roles === "all" || m.roles.includes(user.rol)
  );

  return (
    <div className="min-h-full flex flex-col">
      <header className="bg-white border-b border-slate-200 sticky top-0 z-20">
        <div className="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
          <Link to="/dashboard">
            <Logo />
          </Link>
          <div className="flex items-center gap-3">
            <div className="text-right hidden sm:block">
              <div className="text-sm font-medium text-slate-700">
                {user.nombre}
              </div>
              <div className="text-xs text-slate-500">
                {ROLE_LABELS[user.rol]}
              </div>
            </div>
            <button
              className="btn-outline"
              onClick={() => {
                logout();
                navigate("/login");
              }}
            >
              Cerrar sesión
            </button>
          </div>
        </div>
        <nav className="border-t border-slate-100 bg-refugio-crema">
          <div className="max-w-7xl mx-auto px-2 flex gap-1 overflow-x-auto">
            {items.map((it) => (
              <NavLink
                key={it.to}
                to={it.to}
                className={({ isActive }) =>
                  `whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 -mb-px ${
                    isActive
                      ? "border-refugio-rojo text-refugio-rojo"
                      : "border-transparent text-slate-600 hover:text-refugio-rojo"
                  }`
                }
              >
                {it.label}
              </NavLink>
            ))}
          </div>
        </nav>
      </header>
      <main className="flex-1 max-w-7xl w-full mx-auto px-4 py-6">
        <Outlet />
      </main>
      <footer className="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        Refugio Amor de 4 Patas — Sistema de Gestión · Cada vida importa, cada
        acción cuenta.
      </footer>
    </div>
  );
}
