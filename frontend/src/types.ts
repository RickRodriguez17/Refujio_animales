export type Rol =
  | "administrador"
  | "veterinario"
  | "adoptante"
  | "donante"
  | "voluntario";

export interface Usuario {
  id: number;
  nombre: string;
  email: string;
  rol: Rol;
  telefono?: string | null;
  direccion?: string | null;
  activo: boolean;
  creado_en: string;
}

export type EstadoAnimal =
  | "disponible"
  | "en_tratamiento"
  | "adoptado"
  | "no_disponible";

export interface Animal {
  id: number;
  nombre: string;
  especie: string;
  raza?: string | null;
  edad?: number | null;
  sexo: "macho" | "hembra";
  tamano?: string | null;
  color?: string | null;
  descripcion?: string | null;
  foto_url?: string | null;
  estado: EstadoAnimal;
  fecha_ingreso: string;
}

export type EstadoAdopcion =
  | "pendiente"
  | "en_revision"
  | "aprobada"
  | "rechazada"
  | "completada";

export interface Adopcion {
  id: number;
  animal_id: number;
  adoptante_id: number;
  fecha_solicitud: string;
  fecha_resolucion?: string | null;
  estado: EstadoAdopcion;
  motivo?: string | null;
  documentos?: string | null;
  observaciones?: string | null;
  animal?: Animal;
  adoptante?: Usuario;
}

export type TipoHistorial = "consulta" | "tratamiento" | "vacuna" | "cirugia";

export interface HistorialMedico {
  id: number;
  animal_id: number;
  veterinario_id: number;
  fecha: string;
  tipo: TipoHistorial;
  diagnostico?: string | null;
  tratamiento?: string | null;
  vacuna?: string | null;
  observaciones?: string | null;
  animal?: Animal;
  veterinario?: Usuario;
}

export type TipoDonacion =
  | "dinero"
  | "alimento"
  | "medicinas"
  | "insumos"
  | "otro";

export interface Donacion {
  id: number;
  donante_id: number;
  tipo: TipoDonacion;
  monto?: number | null;
  descripcion?: string | null;
  comprobante?: string | null;
  fecha: string;
  donante?: Usuario;
}

export interface Actividad {
  id: number;
  titulo: string;
  descripcion?: string | null;
  fecha: string;
  horas_estimadas: number;
  cupos: number;
  creada_en: string;
  inscritos: number;
}

export interface Inscripcion {
  id: number;
  voluntario_id: number;
  actividad_id: number;
  horas_registradas: number;
  creada_en: string;
  actividad?: Actividad;
  voluntario?: Usuario;
}

export interface ResumenReporte {
  total_animales: number;
  animales_disponibles: number;
  animales_adoptados: number;
  animales_en_tratamiento: number;
  total_adopciones: number;
  adopciones_pendientes: number;
  adopciones_aprobadas: number;
  total_donaciones: number;
  monto_total_donado: number;
  total_voluntarios: number;
  total_actividades: number;
  horas_voluntariado: number;
}
