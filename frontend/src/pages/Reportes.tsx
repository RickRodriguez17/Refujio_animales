import { useEffect, useState } from "react";
import { api } from "../api/client";
import type { ResumenReporte } from "../types";

interface Stat {
  label: string;
  value: string | number;
  hint?: string;
  color: string;
}

export function ReportesPage() {
  const [data, setData] = useState<ResumenReporte | null>(null);

  useEffect(() => {
    api.get<ResumenReporte>("/api/reportes/resumen").then((r) => setData(r.data));
  }, []);

  if (!data) return <div>Cargando reporte...</div>;

  const stats: Stat[] = [
    {
      label: "Animales en el refugio",
      value: data.total_animales,
      hint: `${data.animales_disponibles} disponibles, ${data.animales_en_tratamiento} en tratamiento`,
      color: "bg-refugio-rojo",
    },
    {
      label: "Animales adoptados",
      value: data.animales_adoptados,
      color: "bg-emerald-600",
    },
    {
      label: "Adopciones (total)",
      value: data.total_adopciones,
      hint: `${data.adopciones_pendientes} pendientes, ${data.adopciones_aprobadas} aprobadas`,
      color: "bg-amber-500",
    },
    {
      label: "Donaciones recibidas",
      value: data.total_donaciones,
      hint: `Total Bs ${data.monto_total_donado.toFixed(2)}`,
      color: "bg-blue-600",
    },
    {
      label: "Voluntarios",
      value: data.total_voluntarios,
      hint: `${data.total_actividades} actividades · ${data.horas_voluntariado} hrs`,
      color: "bg-purple-600",
    },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-slate-800">Reportes</h1>
        <p className="text-sm text-slate-500">
          Resumen general del refugio en tiempo real.
        </p>
      </div>

      <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {stats.map((s) => (
          <div key={s.label} className="card p-5">
            <div className={`h-1 rounded ${s.color} mb-3`} />
            <div className="text-sm text-slate-500">{s.label}</div>
            <div className="text-3xl font-bold text-slate-800">{s.value}</div>
            {s.hint && (
              <div className="text-xs text-slate-500 mt-1">{s.hint}</div>
            )}
          </div>
        ))}
      </div>

      <section className="card p-6">
        <h2 className="font-semibold text-slate-700 mb-2">Indicadores</h2>
        <div className="grid sm:grid-cols-2 gap-3 text-sm">
          <div className="flex justify-between border-b py-1">
            <span>Total de animales</span>
            <b>{data.total_animales}</b>
          </div>
          <div className="flex justify-between border-b py-1">
            <span>Animales disponibles</span>
            <b>{data.animales_disponibles}</b>
          </div>
          <div className="flex justify-between border-b py-1">
            <span>Animales en tratamiento</span>
            <b>{data.animales_en_tratamiento}</b>
          </div>
          <div className="flex justify-between border-b py-1">
            <span>Animales adoptados</span>
            <b>{data.animales_adoptados}</b>
          </div>
          <div className="flex justify-between border-b py-1">
            <span>Adopciones totales</span>
            <b>{data.total_adopciones}</b>
          </div>
          <div className="flex justify-between border-b py-1">
            <span>Adopciones pendientes</span>
            <b>{data.adopciones_pendientes}</b>
          </div>
          <div className="flex justify-between border-b py-1">
            <span>Donaciones</span>
            <b>{data.total_donaciones}</b>
          </div>
          <div className="flex justify-between border-b py-1">
            <span>Monto total donado</span>
            <b>Bs {data.monto_total_donado.toFixed(2)}</b>
          </div>
          <div className="flex justify-between border-b py-1">
            <span>Voluntarios registrados</span>
            <b>{data.total_voluntarios}</b>
          </div>
          <div className="flex justify-between border-b py-1">
            <span>Horas de voluntariado</span>
            <b>{data.horas_voluntariado} hrs</b>
          </div>
        </div>
      </section>
    </div>
  );
}
