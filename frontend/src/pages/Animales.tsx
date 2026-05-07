import { useEffect, useState, type FormEvent } from "react";
import { Link } from "react-router-dom";
import { api } from "../api/client";
import { useAuth } from "../auth/AuthContext";
import { EstadoAnimalBadge } from "../components/EstadoBadge";
import type { Animal, EstadoAnimal } from "../types";

const ESTADOS: { value: EstadoAnimal | ""; label: string }[] = [
  { value: "", label: "Todos los estados" },
  { value: "disponible", label: "Disponible" },
  { value: "en_tratamiento", label: "En tratamiento" },
  { value: "adoptado", label: "Adoptado" },
  { value: "no_disponible", label: "No disponible" },
];

const VACIO: Partial<Animal> = {
  nombre: "",
  especie: "Perro",
  raza: "",
  edad: 0,
  sexo: "macho",
  tamano: "",
  color: "",
  descripcion: "",
  foto_url: "",
  estado: "disponible",
};

export function AnimalesPage() {
  const { hasRole } = useAuth();
  const isAdmin = hasRole("administrador");
  const [items, setItems] = useState<Animal[]>([]);
  const [estado, setEstado] = useState<string>("");
  const [busqueda, setBusqueda] = useState("");
  const [open, setOpen] = useState(false);
  const [edit, setEdit] = useState<Partial<Animal> | null>(null);
  const [error, setError] = useState<string | null>(null);

  async function load() {
    const params: Record<string, string> = {};
    if (estado) params.estado = estado;
    if (busqueda) params.busqueda = busqueda;
    const { data } = await api.get<Animal[]>("/api/animales", { params });
    setItems(data);
  }

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [estado]);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    if (!edit) return;
    setError(null);
    try {
      if (edit.id) {
        await api.put(`/api/animales/${edit.id}`, edit);
      } else {
        await api.post("/api/animales", edit);
      }
      setOpen(false);
      setEdit(null);
      load();
    } catch (err: any) {
      setError(err?.response?.data?.detail || "Error al guardar");
    }
  }

  async function onDelete(id: number) {
    if (!confirm("¿Eliminar este animal?")) return;
    await api.delete(`/api/animales/${id}`);
    load();
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Animales</h1>
          <p className="text-sm text-slate-500">
            Listado de animales del refugio.
          </p>
        </div>
        {isAdmin && (
          <button
            className="btn-primary"
            onClick={() => {
              setEdit({ ...VACIO });
              setOpen(true);
            }}
          >
            + Registrar animal
          </button>
        )}
      </div>

      <div className="card p-4 flex flex-wrap gap-3 items-end">
        <div className="flex-1 min-w-[200px]">
          <label className="label">Buscar</label>
          <input
            className="input"
            placeholder="Nombre o raza..."
            value={busqueda}
            onChange={(e) => setBusqueda(e.target.value)}
            onKeyDown={(e) => e.key === "Enter" && load()}
          />
        </div>
        <div>
          <label className="label">Estado</label>
          <select
            className="input"
            value={estado}
            onChange={(e) => setEstado(e.target.value)}
          >
            {ESTADOS.map((o) => (
              <option key={o.value} value={o.value}>
                {o.label}
              </option>
            ))}
          </select>
        </div>
        <button className="btn-outline" onClick={load}>
          Buscar
        </button>
      </div>

      <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {items.map((a) => (
          <div key={a.id} className="card overflow-hidden">
            {a.foto_url ? (
              <img
                src={a.foto_url}
                alt={a.nombre}
                className="h-44 w-full object-cover"
                onError={(e) => {
                  (e.currentTarget as HTMLImageElement).style.display = "none";
                }}
              />
            ) : (
              <div className="h-44 w-full bg-refugio-amarillo/40 flex items-center justify-center text-6xl">
                🐾
              </div>
            )}
            <div className="p-4">
              <div className="flex items-start justify-between gap-2">
                <div>
                  <div className="font-semibold text-slate-800">{a.nombre}</div>
                  <div className="text-xs text-slate-500">
                    {a.especie} · {a.raza || "Mestizo"} ·{" "}
                    {a.sexo === "macho" ? "Macho" : "Hembra"}
                  </div>
                </div>
                <EstadoAnimalBadge estado={a.estado} />
              </div>
              <p className="text-sm text-slate-600 mt-2 line-clamp-2">
                {a.descripcion || "Sin descripción."}
              </p>
              <div className="flex flex-wrap gap-2 mt-3">
                <Link
                  to={`/animales/${a.id}`}
                  className="btn-outline text-xs"
                >
                  Ver detalle
                </Link>
                {isAdmin && (
                  <>
                    <button
                      className="btn-secondary text-xs"
                      onClick={() => {
                        setEdit({ ...a });
                        setOpen(true);
                      }}
                    >
                      Editar
                    </button>
                    <button
                      className="btn-danger text-xs"
                      onClick={() => onDelete(a.id)}
                    >
                      Eliminar
                    </button>
                  </>
                )}
              </div>
            </div>
          </div>
        ))}
        {items.length === 0 && (
          <div className="col-span-full text-center text-slate-500 py-10">
            No hay animales con esos filtros.
          </div>
        )}
      </div>

      {open && edit && (
        <div className="fixed inset-0 z-30 bg-black/40 flex items-center justify-center p-4">
          <form
            onSubmit={onSubmit}
            className="bg-white rounded-xl shadow-lg w-full max-w-2xl p-6 space-y-3 max-h-[90vh] overflow-y-auto"
          >
            <h2 className="text-lg font-bold">
              {edit.id ? "Editar animal" : "Registrar animal"}
            </h2>
            <div className="grid sm:grid-cols-2 gap-3">
              <div>
                <label className="label">Nombre *</label>
                <input
                  className="input"
                  required
                  value={edit.nombre || ""}
                  onChange={(e) =>
                    setEdit({ ...edit, nombre: e.target.value })
                  }
                />
              </div>
              <div>
                <label className="label">Especie *</label>
                <input
                  className="input"
                  required
                  value={edit.especie || ""}
                  onChange={(e) =>
                    setEdit({ ...edit, especie: e.target.value })
                  }
                />
              </div>
              <div>
                <label className="label">Raza</label>
                <input
                  className="input"
                  value={edit.raza || ""}
                  onChange={(e) => setEdit({ ...edit, raza: e.target.value })}
                />
              </div>
              <div>
                <label className="label">Edad (años)</label>
                <input
                  type="number"
                  min={0}
                  className="input"
                  value={edit.edad ?? 0}
                  onChange={(e) =>
                    setEdit({ ...edit, edad: parseInt(e.target.value || "0") })
                  }
                />
              </div>
              <div>
                <label className="label">Sexo</label>
                <select
                  className="input"
                  value={edit.sexo || "macho"}
                  onChange={(e) =>
                    setEdit({ ...edit, sexo: e.target.value as "macho" | "hembra" })
                  }
                >
                  <option value="macho">Macho</option>
                  <option value="hembra">Hembra</option>
                </select>
              </div>
              <div>
                <label className="label">Tamaño</label>
                <input
                  className="input"
                  value={edit.tamano || ""}
                  onChange={(e) =>
                    setEdit({ ...edit, tamano: e.target.value })
                  }
                />
              </div>
              <div>
                <label className="label">Color</label>
                <input
                  className="input"
                  value={edit.color || ""}
                  onChange={(e) => setEdit({ ...edit, color: e.target.value })}
                />
              </div>
              <div>
                <label className="label">Estado</label>
                <select
                  className="input"
                  value={edit.estado || "disponible"}
                  onChange={(e) =>
                    setEdit({ ...edit, estado: e.target.value as EstadoAnimal })
                  }
                >
                  <option value="disponible">Disponible</option>
                  <option value="en_tratamiento">En tratamiento</option>
                  <option value="adoptado">Adoptado</option>
                  <option value="no_disponible">No disponible</option>
                </select>
              </div>
              <div className="sm:col-span-2">
                <label className="label">URL de foto</label>
                <input
                  className="input"
                  value={edit.foto_url || ""}
                  onChange={(e) =>
                    setEdit({ ...edit, foto_url: e.target.value })
                  }
                />
              </div>
              <div className="sm:col-span-2">
                <label className="label">Descripción</label>
                <textarea
                  className="input"
                  rows={3}
                  value={edit.descripcion || ""}
                  onChange={(e) =>
                    setEdit({ ...edit, descripcion: e.target.value })
                  }
                />
              </div>
            </div>
            {error && (
              <div className="text-sm text-red-600 bg-red-50 border border-red-200 rounded p-2">
                {error}
              </div>
            )}
            <div className="flex justify-end gap-2 pt-2">
              <button
                type="button"
                className="btn-outline"
                onClick={() => {
                  setOpen(false);
                  setEdit(null);
                }}
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
