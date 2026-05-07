import { Navigate } from "react-router-dom";
import type { ReactNode } from "react";
import { useAuth } from "../auth/AuthContext";
import type { Rol } from "../types";

export function ProtectedRoute({
  children,
  roles,
}: {
  children: ReactNode;
  roles?: Rol[];
}) {
  const { user, loading } = useAuth();
  if (loading) return null;
  if (!user) return <Navigate to="/login" replace />;
  if (roles && !roles.includes(user.rol)) {
    return <Navigate to="/dashboard" replace />;
  }
  return <>{children}</>;
}
