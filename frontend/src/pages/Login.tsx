import { useState, type FormEvent } from "react";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../auth/AuthContext";
import { Logo } from "../components/Logo";

const DEMO = [
  { rol: "Administrador", email: "admin@refugio.bo", pwd: "admin123" },
  { rol: "Veterinario", email: "vet@refugio.bo", pwd: "vet123" },
  { rol: "Adoptante", email: "adoptante@refugio.bo", pwd: "adopta123" },
  { rol: "Donante", email: "donante@refugio.bo", pwd: "dona123" },
  { rol: "Voluntario", email: "voluntario@refugio.bo", pwd: "volun123" },
];

export function LoginPage() {
  const { login } = useAuth();
  const navigate = useNavigate();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      await login(email, password);
      navigate("/dashboard");
    } catch (err: any) {
      setError(err?.response?.data?.detail || "No se pudo iniciar sesión");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center px-4 py-10 bg-gradient-to-br from-refugio-crema to-amber-50">
      <div className="w-full max-w-4xl grid md:grid-cols-2 gap-6">
        <div className="card p-8">
          <Logo size={48} />
          <h1 className="mt-6 text-2xl font-bold text-slate-800">
            Iniciar sesión
          </h1>
          <p className="text-sm text-slate-500 mb-6">
            Sistema de Gestión del Refugio de Animales
          </p>
          <form onSubmit={onSubmit} className="space-y-4">
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
                required
              />
            </div>
            {error && (
              <div className="text-sm text-red-600 bg-red-50 border border-red-200 rounded p-2">
                {error}
              </div>
            )}
            <button
              type="submit"
              className="btn-primary w-full"
              disabled={loading}
            >
              {loading ? "Ingresando..." : "Ingresar"}
            </button>
          </form>
          <p className="mt-4 text-sm text-slate-500">
            ¿No tienes cuenta?{" "}
            <Link
              to="/registro"
              className="text-refugio-rojo font-medium hover:underline"
            >
              Regístrate
            </Link>
          </p>
        </div>

        <div className="card p-8 bg-refugio-rojo text-white">
          <h2 className="text-lg font-bold">Cuentas de demostración</h2>
          <p className="text-sm opacity-90 mb-4">
            Prueba cada rol del sistema con estos usuarios precargados:
          </p>
          <ul className="space-y-2 text-sm">
            {DEMO.map((d) => (
              <li
                key={d.email}
                className="bg-white/10 rounded-md p-3 flex items-center justify-between gap-2"
              >
                <div>
                  <div className="font-semibold">{d.rol}</div>
                  <div className="opacity-90 text-xs">{d.email}</div>
                  <div className="opacity-75 text-xs">
                    Contraseña: <code>{d.pwd}</code>
                  </div>
                </div>
                <button
                  className="text-xs bg-white text-refugio-rojo px-2 py-1 rounded font-semibold hover:bg-refugio-amarillo"
                  onClick={() => {
                    setEmail(d.email);
                    setPassword(d.pwd);
                  }}
                >
                  Usar
                </button>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  );
}
