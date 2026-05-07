import { useEffect, useState, type FormEvent } from "react";
import { api } from "../api/client";
import { useAuth } from "../auth/AuthContext";
import type { Donacion, TipoDonacion } from "../types";

const TIPOS: { value: TipoDonacion; label: string }[] = [
  { value: "dinero", label: "Dinero" },
  { value: "alimento", label: "Alimento" },
  { value: "medicinas", label: "Medicinas" },
  { value: "insumos", label: "Insumos" },
  { value: "otro", label: "Otro" },
];

export function DonacionesPage() {
  const { hasRole } = useAuth();
  const puedeDonar = hasRole("donante", "administrador");
  const [items, setItems] = useState<Donacion[]>([]);
  const [open, setOpen] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [comprobanteRecibido, setComprobanteRecibido] = useState<string | null>(null);
  const [form, setForm] = useState({
    tipo: "dinero" as TipoDonacion,
    monto: 0 as number | undefined,
    descripcion: "",
  });

  async function load() {
    const { data } = await api.get<Donacion[]>("/api/donaciones");
    setItems(data);
  }
  useEffect(() => {
    load();
  }, []);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError(null);
    try {
      const payload = {
        tipo: form.tipo,
        monto: form.tipo === "dinero" ? form.monto : null,
        descripcion: form.descripcion,
      };
      const { data } = await api.post<Donacion>("/api/donaciones", payload);
      setOpen(false);
      setComprobanteRecibido(data.comprobante || null);
      setForm({ tipo: "dinero", monto: 0, descripcion: "" });
      load();
    } catch (err: any) {
      setError(err?.response?.data?.detail || "Error al donar");
    }
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Donaciones</h1>
          <p className="text-sm text-slate-500">
            {hasRole("administrador")
              ? "Donaciones recibidas por el refugio."
              : "Apoya al refugio con tu donación."}
          </p>
        </div>
        {puedeDonar && (
          <button className="btn-primary" onClick={() => setOpen(true)}>
            + Realizar donación
          </button>
        )}
      </div>

      {comprobanteRecibido && (
        <div className="card p-4 bg-emerald-50 border-emerald-200">
          <div className="font-semibold text-emerald-700">
            ¡Gracias por tu donación!
          </div>
          <div className="text-sm">
            Tu comprobante:{" "}
            <code className="bg-white px-2 py-1 rounded border">
              {comprobanteRecibido}
            </code>
          </div>
        </div>
      )}

      {items.length === 0 ? (
        <div className="card p-10 text-center text-slate-500">
          No hay donaciones registradas.
        </div>
      ) : (
        <div className="card overflow-x-auto">
          <table className="min-w-full text-sm">
            <thead className="bg-refugio-crema text-left text-slate-600">
              <tr>
                <th className="px-3 py-2">Comprobante</th>
                <th className="px-3 py-2">Tipo</th>
                <th className="px-3 py-2">Monto</th>
                <th className="px-3 py-2">Descripción</th>
                {hasRole("administrador") && (
                  <th className="px-3 py-2">Donante</th>
                )}
                <th className="px-3 py-2">Fecha</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-200">
              {items.map((d) => (
                <tr key={d.id}>
                  <td className="px-3 py-2 font-mono text-xs">
                    {d.comprobante}
                  </td>
                  <td className="px-3 py-2 capitalize">{d.tipo}</td>
                  <td className="px-3 py-2">
                    {d.monto ? `Bs ${d.monto.toFixed(2)}` : "—"}
                  </td>
                  <td className="px-3 py-2">{d.descripcion || "—"}</td>
                  {hasRole("administrador") && (
                    <td className="px-3 py-2">{d.donante?.nombre || "—"}</td>
                  )}
                  <td className="px-3 py-2 text-xs text-slate-500">
                    {new Date(d.fecha).toLocaleString()}
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
            className="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-3"
          >
            <h2 className="text-lg font-bold">Realizar donación</h2>
            <div>
              <label className="label">Tipo de donación *</label>
              <select
                className="input"
                value={form.tipo}
                onChange={(e) =>
                  setForm({
                    ...form,
                    tipo: e.target.value as TipoDonacion,
                  })
                }
              >
                {TIPOS.map((t) => (
                  <option key={t.value} value={t.value}>
                    {t.label}
                  </option>
                ))}
              </select>
            </div>
            {form.tipo === "dinero" && (
              <div>
                <label className="label">Monto (Bs) *</label>
                <input
                  type="number"
                  step="0.01"
                  min={0}
                  className="input"
                  value={form.monto ?? 0}
                  onChange={(e) =>
                    setForm({
                      ...form,
                      monto: parseFloat(e.target.value || "0"),
                    })
                  }
                  required
                />
              </div>
            )}
            <div>
              <label className="label">Descripción</label>
              <textarea
                className="input"
                rows={3}
                value={form.descripcion}
                onChange={(e) =>
                  setForm({ ...form, descripcion: e.target.value })
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
                Donar
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
