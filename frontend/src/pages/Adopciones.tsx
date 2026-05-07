import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { api } from "../api/client";
import { useAuth } from "../auth/AuthContext";
import { EstadoAdopcionBadge } from "../components/EstadoBadge";
import type { Adopcion, EstadoAdopcion } from "../types";

const ACCIONES_ADMIN: { value: EstadoAdopcion; label: string; cls: string }[] = [
  { value: "en_revision", label: "Marcar en revisión", cls: "btn-outline" },
  { value: "aprobada", label: "Aprobar", cls: "btn-primary" },
  { value: "completada", label: "Completar", cls: "btn-secondary" },
  { value: "rechazada", label: "Rechazar", cls: "btn-danger" },
];

export function AdopcionesPage() {
  const { hasRole } = useAuth();
  const isAdmin = hasRole("administrador");
  const [items, setItems] = useState<Adopcion[]>([]);

  async function load() {
    const { data } = await api.get<Adopcion[]>("/api/adopciones");
    setItems(data);
  }

  useEffect(() => {
    load();
  }, []);

  async function actualizar(id: number, estado: EstadoAdopcion) {
    const obs =
      prompt(`Observaciones para la solicitud (opcional):`, "") || undefined;
    await api.put(`/api/adopciones/${id}`, { estado, observaciones: obs });
    load();
  }

  return (
    <div className="space-y-4">
      <div>
        <h1 className="text-2xl font-bold text-slate-800">
          {isAdmin ? "Adopciones" : "Mis solicitudes de adopción"}
        </h1>
        <p className="text-sm text-slate-500">
          {isAdmin
            ? "Gestiona las solicitudes de adopción del refugio."
            : "Revisa el estado de tus solicitudes."}
        </p>
      </div>

      {items.length === 0 ? (
        <div className="card p-10 text-center text-slate-500">
          No hay solicitudes registradas.{" "}
          {!isAdmin && (
            <Link className="text-refugio-rojo font-medium" to="/animales">
              Buscar animales
            </Link>
          )}
        </div>
      ) : (
        <div className="card overflow-x-auto">
          <table className="min-w-full text-sm">
            <thead className="bg-refugio-crema text-left text-slate-600">
              <tr>
                <th className="px-3 py-2">#</th>
                <th className="px-3 py-2">Animal</th>
                {isAdmin && <th className="px-3 py-2">Adoptante</th>}
                <th className="px-3 py-2">Solicitud</th>
                <th className="px-3 py-2">Estado</th>
                <th className="px-3 py-2">Motivo</th>
                <th className="px-3 py-2">Acciones</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-200">
              {items.map((a) => (
                <tr key={a.id}>
                  <td className="px-3 py-2 font-medium">{a.id}</td>
                  <td className="px-3 py-2">
                    {a.animal ? (
                      <Link
                        to={`/animales/${a.animal_id}`}
                        className="text-refugio-rojo hover:underline"
                      >
                        {a.animal.nombre}
                      </Link>
                    ) : (
                      `#${a.animal_id}`
                    )}
                  </td>
                  {isAdmin && (
                    <td className="px-3 py-2">
                      {a.adoptante?.nombre || `Usuario #${a.adoptante_id}`}
                    </td>
                  )}
                  <td className="px-3 py-2 text-xs text-slate-500">
                    {new Date(a.fecha_solicitud).toLocaleDateString()}
                  </td>
                  <td className="px-3 py-2">
                    <EstadoAdopcionBadge estado={a.estado} />
                  </td>
                  <td className="px-3 py-2 max-w-[260px] truncate" title={a.motivo || ""}>
                    {a.motivo || "—"}
                  </td>
                  <td className="px-3 py-2">
                    {isAdmin ? (
                      <div className="flex flex-wrap gap-1">
                        {ACCIONES_ADMIN.map((acc) => (
                          <button
                            key={acc.value}
                            className={`${acc.cls} text-xs`}
                            onClick={() => actualizar(a.id, acc.value)}
                          >
                            {acc.label}
                          </button>
                        ))}
                      </div>
                    ) : (
                      <span className="text-xs text-slate-500">
                        {a.observaciones || "Pendiente de revisión"}
                      </span>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
