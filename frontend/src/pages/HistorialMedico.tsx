import { useEffect, useState, type FormEvent } from "react";
import { api } from "../api/client";
import type { Animal, HistorialMedico, TipoHistorial } from "../types";

const TIPOS: { value: TipoHistorial; label: string }[] = [
  { value: "consulta", label: "Consulta" },
  { value: "tratamiento", label: "Tratamiento" },
  { value: "vacuna", label: "Vacuna" },
  { value: "cirugia", label: "Cirugía" },
];

export function HistorialMedicoPage() {
  const [items, setItems] = useState<HistorialMedico[]>([]);
  const [animales, setAnimales] = useState<Animal[]>([]);
  const [open, setOpen] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [filtro, setFiltro] = useState<number | "">("");
  const [form, setForm] = useState({
    animal_id: 0,
    tipo: "consulta" as TipoHistorial,
    diagnostico: "",
    tratamiento: "",
    vacuna: "",
    observaciones: "",
  });

  async function load() {
    const params: Record<string, string> = {};
    if (filtro) params.animal_id = String(filtro);
    const { data } = await api.get<HistorialMedico[]>("/api/historial-medico", {
      params,
    });
    setItems(data);
  }

  useEffect(() => {
    api.get<Animal[]>("/api/animales").then((r) => setAnimales(r.data));
  }, []);

  useEffect(() => {
    load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filtro]);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError(null);
    if (!form.animal_id) {
      setError("Debes seleccionar un animal");
      return;
    }
    try {
      await api.post("/api/historial-medico", form);
      setOpen(false);
      setForm({
        animal_id: 0,
        tipo: "consulta",
        diagnostico: "",
        tratamiento: "",
        vacuna: "",
        observaciones: "",
      });
      load();
    } catch (err: any) {
      setError(err?.response?.data?.detail || "Error al registrar");
    }
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Historial médico</h1>
          <p className="text-sm text-slate-500">
            Registro de consultas, tratamientos y vacunas.
          </p>
        </div>
        <button
          className="btn-primary"
          onClick={() => {
            setOpen(true);
            setError(null);
          }}
        >
          + Registrar atención médica
        </button>
      </div>

      <div className="card p-4 flex items-end gap-3">
        <div className="flex-1">
          <label className="label">Filtrar por animal</label>
          <select
            className="input"
            value={filtro}
            onChange={(e) =>
              setFiltro(e.target.value ? Number(e.target.value) : "")
            }
          >
            <option value="">Todos los animales</option>
            {animales.map((a) => (
              <option key={a.id} value={a.id}>
                {a.nombre} ({a.especie})
              </option>
            ))}
          </select>
        </div>
      </div>

      {items.length === 0 ? (
        <div className="card p-10 text-center text-slate-500">
          No hay registros médicos.
        </div>
      ) : (
        <div className="card overflow-x-auto">
          <table className="min-w-full text-sm">
            <thead className="bg-refugio-crema text-left text-slate-600">
              <tr>
                <th className="px-3 py-2">Fecha</th>
                <th className="px-3 py-2">Animal</th>
                <th className="px-3 py-2">Tipo</th>
                <th className="px-3 py-2">Diagnóstico / Tratamiento</th>
                <th className="px-3 py-2">Vacuna</th>
                <th className="px-3 py-2">Veterinario</th>
                <th className="px-3 py-2">Observaciones</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-200">
              {items.map((h) => (
                <tr key={h.id}>
                  <td className="px-3 py-2 text-xs text-slate-500">
                    {new Date(h.fecha).toLocaleString()}
                  </td>
                  <td className="px-3 py-2">{h.animal?.nombre || `#${h.animal_id}`}</td>
                  <td className="px-3 py-2 capitalize">{h.tipo}</td>
                  <td className="px-3 py-2">
                    {h.diagnostico && <div>{h.diagnostico}</div>}
                    {h.tratamiento && (
                      <div className="text-xs text-slate-500">
                        Tratamiento: {h.tratamiento}
                      </div>
                    )}
                  </td>
                  <td className="px-3 py-2">{h.vacuna || "—"}</td>
                  <td className="px-3 py-2">{h.veterinario?.nombre || "—"}</td>
                  <td className="px-3 py-2 text-slate-600 max-w-[260px]">
                    {h.observaciones || "—"}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {open && (
        <div className="fixed inset-0 z-30 bg-black/40 flex items-center justify-center p-4">
          <form
            onSubmit={onSubmit}
            className="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 space-y-3"
          >
            <h2 className="text-lg font-bold">Registrar atención médica</h2>
            <div>
              <label className="label">Animal *</label>
              <select
                className="input"
                value={form.animal_id || ""}
                onChange={(e) =>
                  setForm({ ...form, animal_id: Number(e.target.value) })
                }
                required
              >
                <option value="">Seleccionar...</option>
                {animales.map((a) => (
                  <option key={a.id} value={a.id}>
                    {a.nombre} ({a.especie})
                  </option>
                ))}
              </select>
            </div>
            <div>
              <label className="label">Tipo</label>
              <select
                className="input"
                value={form.tipo}
                onChange={(e) =>
                  setForm({ ...form, tipo: e.target.value as TipoHistorial })
                }
              >
                {TIPOS.map((t) => (
                  <option key={t.value} value={t.value}>
                    {t.label}
                  </option>
                ))}
              </select>
            </div>
            {form.tipo !== "vacuna" && (
              <>
                <div>
                  <label className="label">Diagnóstico</label>
                  <input
                    className="input"
                    value={form.diagnostico}
                    onChange={(e) =>
                      setForm({ ...form, diagnostico: e.target.value })
                    }
                  />
                </div>
                <div>
                  <label className="label">Tratamiento</label>
                  <input
                    className="input"
                    value={form.tratamiento}
                    onChange={(e) =>
                      setForm({ ...form, tratamiento: e.target.value })
                    }
                  />
                </div>
              </>
            )}
            {form.tipo === "vacuna" && (
              <div>
                <label className="label">Vacuna aplicada</label>
                <input
                  className="input"
                  value={form.vacuna}
                  onChange={(e) =>
                    setForm({ ...form, vacuna: e.target.value })
                  }
                />
              </div>
            )}
            <div>
              <label className="label">Observaciones</label>
              <textarea
                className="input"
                rows={2}
                value={form.observaciones}
                onChange={(e) =>
                  setForm({ ...form, observaciones: e.target.value })
                }
              />
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
