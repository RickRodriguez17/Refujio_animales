import { useEffect, useState, type FormEvent } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import { api } from "../api/client";
import { useAuth } from "../auth/AuthContext";
import { EstadoAnimalBadge } from "../components/EstadoBadge";
import type { Animal, HistorialMedico } from "../types";

export function AnimalDetallePage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user, hasRole } = useAuth();
  const [animal, setAnimal] = useState<Animal | null>(null);
  const [historial, setHistorial] = useState<HistorialMedico[]>([]);
  const [open, setOpen] = useState(false);
  const [motivo, setMotivo] = useState("");
  const [documentos, setDocumentos] = useState("");
  const [msg, setMsg] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    api.get<Animal>(`/api/animales/${id}`).then((r) => setAnimal(r.data));
    if (user && (user.rol === "veterinario" || user.rol === "administrador")) {
      api
        .get<HistorialMedico[]>(`/api/historial-medico/animal/${id}`)
        .then((r) => setHistorial(r.data));
    }
  }, [id, user]);

  async function solicitarAdopcion(e: FormEvent) {
    e.preventDefault();
    setError(null);
    setMsg(null);
    try {
      await api.post("/api/adopciones", {
        animal_id: Number(id),
        motivo,
        documentos,
      });
      setMsg("¡Solicitud enviada! Puedes ver su estado en Mis Adopciones.");
      setOpen(false);
      setTimeout(() => navigate("/adopciones"), 1500);
    } catch (err: any) {
      setError(err?.response?.data?.detail || "Error al solicitar adopción");
    }
  }

  if (!animal) return <div>Cargando...</div>;

  return (
    <div className="space-y-6">
      <Link to="/animales" className="text-sm text-refugio-rojo hover:underline">
        ← Volver
      </Link>

      <div className="card overflow-hidden grid md:grid-cols-2">
        {animal.foto_url ? (
          <img
            src={animal.foto_url}
            alt={animal.nombre}
            className="w-full h-64 md:h-full object-cover"
          />
        ) : (
          <div className="w-full h-64 md:h-full bg-refugio-amarillo/40 flex items-center justify-center text-8xl">
            🐾
          </div>
        )}
        <div className="p-6">
          <div className="flex items-center gap-2 justify-between">
            <h1 className="text-2xl font-bold text-slate-800">
              {animal.nombre}
            </h1>
            <EstadoAnimalBadge estado={animal.estado} />
          </div>
          <div className="text-sm text-slate-500 mb-4">
            {animal.especie} · {animal.raza || "Mestizo"} ·{" "}
            {animal.sexo === "macho" ? "Macho" : "Hembra"}
          </div>
          <dl className="grid grid-cols-2 gap-3 text-sm">
            <div>
              <dt className="text-slate-500">Edad</dt>
              <dd className="font-medium">
                {animal.edad ?? "-"} {animal.edad === 1 ? "año" : "años"}
              </dd>
            </div>
            <div>
              <dt className="text-slate-500">Tamaño</dt>
              <dd className="font-medium">{animal.tamano || "-"}</dd>
            </div>
            <div>
              <dt className="text-slate-500">Color</dt>
              <dd className="font-medium">{animal.color || "-"}</dd>
            </div>
            <div>
              <dt className="text-slate-500">Ingreso</dt>
              <dd className="font-medium">
                {new Date(animal.fecha_ingreso).toLocaleDateString()}
              </dd>
            </div>
          </dl>
          <p className="text-sm text-slate-600 mt-4">
            {animal.descripcion || "Sin descripción."}
          </p>

          {hasRole("adoptante") && animal.estado === "disponible" && (
            <button
              className="btn-primary mt-5"
              onClick={() => setOpen(true)}
            >
              Solicitar adopción
            </button>
          )}
          {!user && (
            <Link to="/login" className="btn-primary mt-5 inline-block">
              Inicia sesión para adoptar
            </Link>
          )}
          {msg && (
            <div className="mt-3 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded p-2">
              {msg}
            </div>
          )}
        </div>
      </div>

      {hasRole("administrador", "veterinario") && (
        <section className="card p-6">
          <h2 className="text-lg font-semibold text-slate-700 mb-3">
            Historial médico
          </h2>
          {historial.length === 0 ? (
            <p className="text-sm text-slate-500">
              Aún no se ha registrado historial médico.
            </p>
          ) : (
            <ul className="divide-y divide-slate-200">
              {historial.map((h) => (
                <li key={h.id} className="py-3 text-sm">
                  <div className="flex items-center justify-between">
                    <span className="font-medium capitalize">{h.tipo}</span>
                    <span className="text-xs text-slate-500">
                      {new Date(h.fecha).toLocaleString()}
                    </span>
                  </div>
                  {h.diagnostico && (
                    <div>
                      <span className="text-slate-500">Diagnóstico:</span>{" "}
                      {h.diagnostico}
                    </div>
                  )}
                  {h.tratamiento && (
                    <div>
                      <span className="text-slate-500">Tratamiento:</span>{" "}
                      {h.tratamiento}
                    </div>
                  )}
                  {h.vacuna && (
                    <div>
                      <span className="text-slate-500">Vacuna:</span> {h.vacuna}
                    </div>
                  )}
                  {h.observaciones && (
                    <div className="text-slate-600">
                      {h.observaciones}
                    </div>
                  )}
                </li>
              ))}
            </ul>
          )}
        </section>
      )}

      {open && (
        <div className="fixed inset-0 z-30 bg-black/40 flex items-center justify-center p-4">
          <form
            onSubmit={solicitarAdopcion}
            className="bg-white rounded-xl shadow-lg w-full max-w-lg p-6 space-y-3"
          >
            <h2 className="text-lg font-bold">Solicitar adopción</h2>
            <p className="text-sm text-slate-500">
              Cuéntanos por qué quieres adoptar a <b>{animal.nombre}</b>.
            </p>
            <div>
              <label className="label">Motivo *</label>
              <textarea
                className="input"
                rows={3}
                value={motivo}
                required
                onChange={(e) => setMotivo(e.target.value)}
              />
            </div>
            <div>
              <label className="label">Documentos (referencias)</label>
              <input
                className="input"
                placeholder="cedula.pdf, comprobante_domicilio.pdf"
                value={documentos}
                onChange={(e) => setDocumentos(e.target.value)}
              />
              <p className="text-xs text-slate-500 mt-1">
                Lista los documentos adjuntados (caso de uso «Adjuntar
                documentos»).
              </p>
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
                Enviar solicitud
              </button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
