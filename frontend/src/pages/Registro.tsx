import { useState, type FormEvent } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../auth/AuthContext";
import { Logo } from "../components/Logo";
import type { Rol } from "../types";

export function RegistroPage() {
  const { registro } = useAuth();
  const navigate = useNavigate();
  const [nombre, setNombre] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [telefono, setTelefono] = useState("");
  const [direccion, setDireccion] = useState("");
  const [rol, setRol] = useState<Rol>("adoptante");
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      await registro({ nombre, email, password, telefono, direccion, rol });
      navigate("/dashboard");
    } catch (err: any) {
      setError(err?.response?.data?.detail || "No se pudo registrar");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center px-4 py-10 bg-gradient-to-br from-refugio-crema to-amber-50">
      <div className="w-full max-w-lg card p-8">
        <Logo size={48} />
        <h1 className="mt-6 text-2xl font-bold text-slate-800">Crear cuenta</h1>
        <p className="text-sm text-slate-500 mb-6">
          Regístrate como adoptante, donante o voluntario.
        </p>
        <form onSubmit={onSubmit} className="space-y-4">
          <div>
            <label className="label">Nombre completo</label>
            <input
              className="input"
              value={nombre}
              onChange={(e) => setNombre(e.target.value)}
              required
            />
          </div>
          <div>
            <label className="label">Email</label>
            <input
              type="email"
              className="input"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>
          <div>
            <label className="label">Contraseña</label>
            <input
              type="password"
              className="input"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              minLength={4}
              required
            />
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="label">Teléfono</label>
              <input
                className="input"
                value={telefono}
                onChange={(e) => setTelefono(e.target.value)}
              />
            </div>
            <div>
              <label className="label">Rol</label>
              <select
                className="input"
                value={rol}
                onChange={(e) => setRol(e.target.value as Rol)}
              >
                <option value="adoptante">Adoptante</option>
                <option value="donante">Donante</option>
                <option value="voluntario">Voluntario</option>
              </select>
            </div>
          </div>
          <div>
            <label className="label">Dirección</label>
            <input
              className="input"
              value={direccion}
              onChange={(e) => setDireccion(e.target.value)}
            />
          </div>
          {error && (
            <div className="text-sm text-red-600 bg-red-50 border border-red-200 rounded p-2">
              {error}
            </div>
          )}
          <button className="btn-primary w-full" disabled={loading}>
            {loading ? "Creando cuenta..." : "Crear cuenta"}
          </button>
        </form>
        <p className="mt-4 text-sm text-slate-500">
          ¿Ya tienes cuenta?{" "}
          <Link to="/login" className="text-refugio-rojo font-medium">
            Inicia sesión
          </Link>
        </p>
      </div>
    </div>
  );
}
