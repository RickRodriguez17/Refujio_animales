import { useEffect, useState, type FormEvent } from "react";
import { api } from "../api/client";
import { useAuth } from "../auth/AuthContext";
import type { Actividad, Inscripcion } from "../types";

export function VoluntariadoPage() {
  const { hasRole } = useAuth();
  const isAdmin = hasRole("administrador");
  const isVol = hasRole("voluntario");
  const [actividades, setActividades] = useState<Actividad[]>([]);
  const [inscripciones, setInscripciones] = useState<Inscripcion[]>([]);
  const [open, setOpen] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [form, setForm] = useState({
    titulo: "",
    descripcion: "",
    fecha: "",
    horas_estimadas: 2,
    cupos: 10,
  });

  async function load() {
    const [a, i] = await Promise.all([
      api.get<Actividad[]>("/api/voluntariado/actividades"),
      api.get<Inscripcion[]>("/api/voluntariado/inscripciones").catch(() => ({ data: [] as Inscripcion[] })),
    ]);
    setActividades(a.data);
    setInscripciones(i.data);
  }
  useEffect(() => {
    load();
  }, []);

  async function crearActividad(e: FormEvent) {
    e.preventDefault();
    setError(null);
    try {
      await api.post("/api/voluntariado/actividades", {
        ...form,
        fecha: new Date(form.fecha).toISOString(),
      });
      setOpen(false);
      setForm({
        titulo: "",
        descripcion: "",
        fecha: "",
        horas_estimadas: 2,
        cupos: 10,
      });
      load();
    } catch (err: any) {
      setError(err?.response?.data?.detail || "Error al crear actividad");
    }
  }

  async function inscribirse(actividad_id: number) {
    try {
      await api.post("/api/voluntariado/inscripciones", { actividad_id });
      load();
    } catch (err: any) {
      alert(err?.response?.data?.detail || "Error al inscribirse");
    }
  }

  async function registrarHoras(insc: Inscripcion) {
    const horas = prompt(
      "Horas trabajadas en la actividad:",
      String(insc.horas_registradas || 0)
    );
    if (horas === null) return;
    const n = parseInt(horas, 10);
    if (isNaN(n) || n < 0) return alert("Horas inválidas");
    await api.put(`/api/voluntariado/inscripciones/${insc.id}`, {
      horas_registradas: n,
    });
    load();
  }

  async function eliminarActividad(id: number) {
    if (!confirm("¿Eliminar esta actividad?")) return;
    await api.delete(`/api/voluntariado/actividades/${id}`);
    load();
  }

  const inscritoEn = (id: number) =>
    inscripciones.find((i) => i.actividad_id === id);

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Voluntariado</h1>
          <p className="text-sm text-slate-500">
            Actividades disponibles para los voluntarios del refugio.
          </p>
        </div>
        {isAdmin && (
          <button className="btn-primary" onClick={() => setOpen(true)}>
            + Nueva actividad
          </button>
        )}
      </div>

      <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {actividades.map((a) => {
          const insc = inscritoEn(a.id);
          const lleno = a.inscritos >= a.cupos;
          return (
            <div key={a.id} className="card p-5">
              <div className="font-semibold text-slate-800">{a.titulo}</div>
              <div className="text-xs text-slate-500 mb-2">
                {new Date(a.fecha).toLocaleString()} · {a.horas_estimadas} hrs
              </div>
              <p className="text-sm text-slate-600 mb-3">
                {a.descripcion || "Sin descripción."}
              </p>
              <div className="text-xs text-slate-500 mb-3">
                Inscritos: <b>{a.inscritos}</b> / {a.cupos}
              </div>
              <div className="flex flex-wrap gap-2">
                {isVol && !insc && (
                  <button
                    className="btn-primary text-xs"
                    disabled={lleno}
                    onClick={() => inscribirse(a.id)}
                  >
                    {lleno ? "Sin cupos" : "Inscribirme"}
                  </button>
                )}
                {isVol && insc && (
                  <button
                    className="btn-secondary text-xs"
                    onClick={() => registrarHoras(insc)}
                  >
                    Registrar horas ({insc.horas_registradas} hrs)
                  </button>
                )}
                {isAdmin && (
                  <button
                    className="btn-danger text-xs"
                    onClick={() => eliminarActividad(a.id)}
                  >
                    Eliminar
                  </button>
                )}
              </div>
            </div>
          );
        })}
        {actividades.length === 0 && (
          <div className="col-span-full text-center text-slate-500 py-10">
            No hay actividades disponibles.
          </div>
        )}
      </div>

      {(isAdmin || isVol) && inscripciones.length > 0 && (
        <section className="card p-5">
          <h2 className="font-semibold text-slate-700 mb-3">
            {isAdmin ? "Inscripciones registradas" : "Mis horas de voluntariado"}
          </h2>
          <table className="min-w-full text-sm">
            <thead className="text-left text-slate-500">
              <tr>
                <th className="py-1">Actividad</th>
                {isAdmin && <th>Voluntario</th>}
                <th>Horas</th>
                <th>Registrado</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-200">
              {inscripciones.map((i) => (
                <tr key={i.id}>
                  <td className="py-2">{i.actividad?.titulo || `#${i.actividad_id}`}</td>
                  {isAdmin && (
                    <td>{i.voluntario?.nombre || `#${i.voluntario_id}`}</td>
                  )}
                  <td>{i.horas_registradas}</td>
                  <td className="text-xs text-slate-500">
                    {new Date(i.creada_en).toLocaleDateString()}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </section>
      )}

      {open && (
        <div className="fixed inset-0 z-30 bg-black/40 flex items-center justify-center p-4">
          <form
            onSubmit={crearActividad}
            className="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 space-y-3"
          >
            <h2 className="text-lg font-bold">Nueva actividad</h2>
            <div>
              <label className="label">Título *</label>
              <input
                className="input"
                required
                value={form.titulo}
                onChange={(e) => setForm({ ...form, titulo: e.target.value })}
              />
            </div>
            <div>
              <label className="label">Fecha y hora *</label>
              <input
                type="datetime-local"
                className="input"
                required
                value={form.fecha}
                onChange={(e) => setForm({ ...form, fecha: e.target.value })}
              />
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="label">Horas estimadas</label>
                <input
                  type="number"
                  min={1}
                  className="input"
                  value={form.horas_estimadas}
                  onChange={(e) =>
                    setForm({
                      ...form,
                      horas_estimadas: parseInt(e.target.value || "1"),
                    })
                  }
                />
              </div>
              <div>
                <label className="label">Cupos</label>
                <input
                  type="number"
                  min={1}
                  className="input"
                  value={form.cupos}
                  onChange={(e) =>
                    setForm({
                      ...form,
                      cupos: parseInt(e.target.value || "1"),
                    })
                  }
                />
              </div>
            </div>
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
                Crear
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
