import type { EstadoAdopcion, EstadoAnimal } from "../types";

const ANIMAL_COLORS: Record<EstadoAnimal, string> = {
  disponible: "bg-emerald-100 text-emerald-700",
  en_tratamiento: "bg-amber-100 text-amber-700",
  adoptado: "bg-slate-200 text-slate-700",
  no_disponible: "bg-slate-200 text-slate-600",
};

const ANIMAL_LABELS: Record<EstadoAnimal, string> = {
  disponible: "Disponible",
  en_tratamiento: "En tratamiento",
  adoptado: "Adoptado",
  no_disponible: "No disponible",
};

const ADOP_COLORS: Record<EstadoAdopcion, string> = {
  pendiente: "bg-yellow-100 text-yellow-700",
  en_revision: "bg-blue-100 text-blue-700",
  aprobada: "bg-emerald-100 text-emerald-700",
  rechazada: "bg-red-100 text-red-700",
  completada: "bg-indigo-100 text-indigo-700",
};

const ADOP_LABELS: Record<EstadoAdopcion, string> = {
  pendiente: "Pendiente",
  en_revision: "En revisión",
  aprobada: "Aprobada",
  rechazada: "Rechazada",
  completada: "Completada",
};

export function EstadoAnimalBadge({ estado }: { estado: EstadoAnimal }) {
  return (
    <span className={`badge ${ANIMAL_COLORS[estado]}`}>
      {ANIMAL_LABELS[estado]}
    </span>
  );
}

export function EstadoAdopcionBadge({ estado }: { estado: EstadoAdopcion }) {
  return (
    <span className={`badge ${ADOP_COLORS[estado]}`}>{ADOP_LABELS[estado]}</span>
  );
}
