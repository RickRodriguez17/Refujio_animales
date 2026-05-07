import { Navigate, Route, Routes } from "react-router-dom";
import { Layout } from "./components/Layout";
import { ProtectedRoute } from "./components/ProtectedRoute";
import { AdopcionesPage } from "./pages/Adopciones";
import { AnimalDetallePage } from "./pages/AnimalDetalle";
import { AnimalesPage } from "./pages/Animales";
import { DashboardPage } from "./pages/Dashboard";
import { DonacionesPage } from "./pages/Donaciones";
import { HistorialMedicoPage } from "./pages/HistorialMedico";
import { LoginPage } from "./pages/Login";
import { RegistroPage } from "./pages/Registro";
import { ReportesPage } from "./pages/Reportes";
import { UsuariosPage } from "./pages/Usuarios";
import { VoluntariadoPage } from "./pages/Voluntariado";

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/registro" element={<RegistroPage />} />

      <Route
        element={
          <ProtectedRoute>
            <Layout />
          </ProtectedRoute>
        }
      >
        <Route path="/dashboard" element={<DashboardPage />} />
        <Route path="/animales" element={<AnimalesPage />} />
        <Route path="/animales/:id" element={<AnimalDetallePage />} />
        <Route
          path="/adopciones"
          element={
            <ProtectedRoute roles={["administrador", "veterinario", "adoptante"]}>
              <AdopcionesPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/historial-medico"
          element={
            <ProtectedRoute roles={["administrador", "veterinario"]}>
              <HistorialMedicoPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/donaciones"
          element={
            <ProtectedRoute roles={["administrador", "donante"]}>
              <DonacionesPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/voluntariado"
          element={
            <ProtectedRoute roles={["administrador", "voluntario"]}>
              <VoluntariadoPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/usuarios"
          element={
            <ProtectedRoute roles={["administrador"]}>
              <UsuariosPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/reportes"
          element={
            <ProtectedRoute roles={["administrador"]}>
              <ReportesPage />
            </ProtectedRoute>
          }
        />
      </Route>

      <Route path="/" element={<Navigate to="/dashboard" replace />} />
      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  );
}
