import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from "react";
import { api } from "../api/client";
import type { Rol, Usuario } from "../types";

interface AuthState {
  user: Usuario | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
  registro: (data: RegistroInput) => Promise<void>;
  hasRole: (...roles: Rol[]) => boolean;
}

export interface RegistroInput {
  nombre: string;
  email: string;
  password: string;
  rol: Rol;
  telefono?: string;
  direccion?: string;
}

const AuthContext = createContext<AuthState | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<Usuario | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const stored = localStorage.getItem("refugio_user");
    if (stored) {
      try {
        setUser(JSON.parse(stored));
      } catch {
        localStorage.removeItem("refugio_user");
      }
    }
    setLoading(false);
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const { data } = await api.post("/api/auth/login", { email, password });
    localStorage.setItem("refugio_token", data.access_token);
    localStorage.setItem("refugio_user", JSON.stringify(data.user));
    setUser(data.user);
  }, []);

  const registro = useCallback(async (payload: RegistroInput) => {
    await api.post("/api/auth/registro", payload);
    await login(payload.email, payload.password);
  }, [login]);

  const logout = useCallback(() => {
    localStorage.removeItem("refugio_token");
    localStorage.removeItem("refugio_user");
    setUser(null);
  }, []);

  const hasRole = useCallback(
    (...roles: Rol[]) => !!user && roles.includes(user.rol),
    [user]
  );

  const value = useMemo(
    () => ({ user, loading, login, logout, registro, hasRole }),
    [user, loading, login, logout, registro, hasRole]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth debe usarse dentro de AuthProvider");
  return ctx;
}
