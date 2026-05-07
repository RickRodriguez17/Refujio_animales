-- Esquema MySQL del sistema "Huellitas de Amor"
-- Para crear todo desde cero, abre install.php desde el navegador.

CREATE DATABASE IF NOT EXISTS DB_REFUJIO
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_spanish_ci;
USE DB_REFUJIO;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','veterinario','adoptante','donante','voluntario') NOT NULL,
  telefono VARCHAR(20),
  direccion VARCHAR(200),
  activo TINYINT(1) DEFAULT 1,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS animales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  especie VARCHAR(40) NOT NULL,
  raza VARCHAR(80),
  edad INT,
  sexo ENUM('macho','hembra'),
  descripcion TEXT,
  estado ENUM('disponible','adoptado','en_tratamiento','no_disponible') NOT NULL DEFAULT 'disponible',
  fecha_ingreso DATE,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS adopciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  animal_id INT NOT NULL,
  adoptante_id INT NOT NULL,
  motivo TEXT,
  documentos VARCHAR(500),
  estado ENUM('pendiente','aprobada','rechazada','completada') NOT NULL DEFAULT 'pendiente',
  observaciones TEXT,
  fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
  fecha_resolucion DATETIME NULL,
  CONSTRAINT fk_adop_animal FOREIGN KEY (animal_id) REFERENCES animales(id) ON DELETE CASCADE,
  CONSTRAINT fk_adop_user   FOREIGN KEY (adoptante_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS historial_medico (
  id INT AUTO_INCREMENT PRIMARY KEY,
  animal_id INT NOT NULL,
  veterinario_id INT NOT NULL,
  tipo ENUM('consulta','vacuna','tratamiento','cirugia') NOT NULL,
  diagnostico VARCHAR(255),
  vacuna VARCHAR(100),
  tratamiento VARCHAR(255),
  observaciones TEXT,
  fecha DATE NOT NULL,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_hm_animal FOREIGN KEY (animal_id) REFERENCES animales(id) ON DELETE CASCADE,
  CONSTRAINT fk_hm_vet    FOREIGN KEY (veterinario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS donaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  donante_id INT NOT NULL,
  tipo ENUM('dinero','alimento','medicina','otro') NOT NULL,
  monto DECIMAL(10,2) NULL,
  descripcion VARCHAR(255),
  comprobante VARCHAR(40) UNIQUE NOT NULL,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_don_user FOREIGN KEY (donante_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS actividades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  descripcion TEXT,
  fecha DATETIME NOT NULL,
  duracion_horas INT DEFAULT 0,
  cupo_maximo INT DEFAULT 10,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inscripciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  actividad_id INT NOT NULL,
  voluntario_id INT NOT NULL,
  horas_realizadas INT NOT NULL DEFAULT 0,
  inscrito_en DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unq_act_vol (actividad_id, voluntario_id),
  CONSTRAINT fk_ins_act FOREIGN KEY (actividad_id) REFERENCES actividades(id) ON DELETE CASCADE,
  CONSTRAINT fk_ins_user FOREIGN KEY (voluntario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
