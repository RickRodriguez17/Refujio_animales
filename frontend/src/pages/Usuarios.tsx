import { useEffect, useState, type FormEvent } from "react";
import { api } from "../api/client";
import type { Rol, Usuario } from "../types";

const ROLES: { value: Rol; label: string }[] = [
  { value: "administrador", label: "Administrador" },
  { value: "veterinario", label: "Veterinario" },
  { value: "adoptante", label: "Adoptante" },
  { value: "donante", label: "Donante" },
  { value: "voluntario", label: "Voluntario" },
];

interface FormData {
  id?: number;
  nombre: string;
  email: string;
  password: string;
  rol: Rol;
  telefono: string;
  direccion: string;
  activo: boolean;
}

const VACIO: FormData = {
  nombre: "",
  email: "",
  password: "",
  rol: "adoptante",
  telefono: "",
  direccion: "",
  activo: true,
};

export function UsuariosPage() {
  const [items, setItems] = useState<Usuario[]>([]);
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState<FormData>(VACIO);
  const [error, setError] = useState<string | null>(null);

  async function load() {
    const { data } = await api.get<Usuario[]>("/api/usuarios");
    setItems(data);
  }
  useEffect(() => {
    load();
  }, []);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError(null);
    try {
      if (form.id) {
        const payload: any = {
          nombre: form.nombre,
          telefono: form.telefono,
          direccion: form.direccion,
          rol: form.rol,
          activo: form.activo,
        };
        if (form.password) payload.password = form.password;
        await api.put(`/api/usuarios/${form.id}`, payload);
      } else {
        await api.post("/api/usuarios", {
          nombre: form.nombre,
          email: form.email,
          password: form.password,
          rol: form.rol,
          telefono: form.telefono,
          direccion: form.direccion,
        });
      }
      setOpen(false);
      setForm(VACIO);
      load();
    } catch (err: any) {
      setError(err?.response?.data?.detail || "Error al guardar usuario");
    }
  }

  async function eliminar(id: number) {
    if (!confirm("¿Eliminar este usuario?")) return;
    await api.delete(`/api/usuarios/${id}`);
    load();
  }

  return (
    <div className="space-y-4">
      <div className="flex items-end justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Usuarios</h1>
          <p className="text-sm text-slate-500">
            Gestionar los usuarios del sistema.
          </p>
        </div>
        <button
          className="btn-primary"
          onClick={() => {
            setForm(VACIO);
            setOpen(true);
          }}
        >
          + Nuevo usuario
        </button>
      </div>

      <div className="card overflow-x-auto">
        <table className="min-w-full text-sm">
          <thead className="bg-refugio-crema text-left text-slate-600">
            <tr>
              <th className="px-3 py-2">#</th>
              <th className="px-3 py-2">Nombre</th>
              <th className="px-3 py-2">Email</th>
              <th className="px-3 py-2">Rol</th>
              <th className="px-3 py-2">Activo</th>
              <th className="px-3 py-2"></th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-200">
            {items.map((u) => (
              <tr key={u.id}>
                <td className="px-3 py-2 font-medium">{u.id}</td>
                <td className="px-3 py-2">{u.nombre}</td>
                <td className="px-3 py-2">{u.email}</td>
                <td className="px-3 py-2 capitalize">{u.rol}</td>
                <td className="px-3 py-2">{u.activo ? "Sí" : "No"}</td>
                <td className="px-3 py-2 flex gap-1">
                  <button
                    className="btn-secondary text-xs"
                    onClick={() => {
                      setForm({
                        id: u.id,
                        nombre: u.nombre,
                        email: u.email,
                        password: "",
                        rol: u.rol,
                        telefono: u.telefono || "",
                        direccion: u.direccion || "",
                        activo: u.activo,
                      });
                      setOpen(true);
                    }}
                  >
                    Editar
                  </button>
                  <button
                    className="btn-danger text-xs"
                    onClick={() => eliminar(u.id)}
                  >
                    Eliminar
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {open && (
        <div className="fixed inset-0 z-30 bg-black/40 flex items-center justify-center p-4">
          <form
            onSubmit={onSubmit}
            className="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 space-y-3"
          >
            <h2 className="text-lg font-bold">
              {form.id ? "Editar usuario" : "Nuevo usuario"}
            </h2>
            <div className="grid grid-cols-2 gap-3">
              <div className="col-span-2">
                <label className="label">Nombre *</label>
                <input
                  className="input"
                  required
                  value={form.nombre}
                  onChange={(e) =>
                    setForm({ ...form, nombre: e.target.value })
                  }
                />
              </div>
              <div className="col-span-2">
                <label className="label">Email *</label>
                <input
                  type="email"
                  className="input"
                  required
                  disabled={!!form.id}
                  value={form.email}
                  onChange={(e) => setForm({ ...form, email: e.target.value })}
                />
              </div>
              <div className="col-span-2">
                <label className="label">
                  {form.id ? "Nueva contraseña (opcional)" : "Contraseña *"}
                </label>
                <input
                  type="password"
                  className="input"
                  required={!form.id}
                  value={form.password}
                  onChange={(e) =>
                    setForm({ ...form, password: e.target.value })
                  }
                />
              </div>
              <div>
                <label className="label">Rol *</label>
                <select
                  className="input"
                  value={form.rol}
                  onChange={(e) =>
                    setForm({ ...form, rol: e.target.value as Rol })
                  }
                >
                  {ROLES.map((r) => (
                    <option key={r.value} value={r.value}>
                      {r.label}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="label">Teléfono</label>
                <input
                  className="input"
                  value={form.telefono}
                  onChange={(e) =>
                    setForm({ ...form, telefono: e.target.value })
                  }
                />
              </div>
              <div className="col-span-2">
                <label className="label">Dirección</label>
                <input
                  className="input"
                  value={form.direccion}
                  onChange={(e) =>
                    setForm({ ...form, direccion: e.target.value })
                  }
                />
              </div>
              {form.id && (
                <label className="col-span-2 flex items-center gap-2 text-sm">
                  <input
                    type="checkbox"
                    checked={form.activo}
                    onChange={(e) =>
                      setForm({ ...form, activo: e.target.checked })
                    }
                  />
                  Usuario activo
                </label>
              )}
            </div>
            {error && (
              <div className="text-sm text-red-600 bg-red-50 border border-red-200 rounded p-2">
                {error}
              </div>
            )}
            <div className="flex justify-end gap-2">
              <button
                type="button"
                className="btn-outline"
                onClick={() => setOpen(false)}
              >
                Cancelar
              </button>
              <button type="submit" className="btn-primary">
                Guardar
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
