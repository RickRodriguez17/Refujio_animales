-- =====================================================================
--  Sistema de Gestion para un Refugio de Animales (Huellitas de Amor)
-- =====================================================================
--  Importa este archivo UNA SOLA VEZ desde phpMyAdmin (boton "Importar")
--  o desde la consola con:
--      mysql -u root -p < db.sql
--
--  Crea la base de datos `refujio_de_animales`, todas las tablas y
--  carga datos de demostracion (5 usuarios, 5 animales, 1 adopcion,
--  1 atencion medica, 2 donaciones, 1 actividad de voluntariado).
--
--  Las contrase&ntilde;as ya estan hasheadas con bcrypt
--  (password_hash(PASSWORD_BCRYPT)). Si vuelves a importar el archivo
--  se borran los datos previos y se vuelven a sembrar (es idempotente).
--
--  Usuarios demo:
--     admin@refugio.bo      / admin123
--     vet@refugio.bo        / vet123
--     adoptante@refugio.bo  / adopta123
--     donante@refugio.bo    / dona123
--     voluntario@refugio.bo / volun123
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `refujio_de_animales`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_spanish_ci;

USE `refujio_de_animales`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `inscripciones`;
DROP TABLE IF EXISTS `actividades`;
DROP TABLE IF EXISTS `donaciones`;
DROP TABLE IF EXISTS `historial_medico`;
DROP TABLE IF EXISTS `adopciones`;
DROP TABLE IF EXISTS `animales`;
DROP TABLE IF EXISTS `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
--  Usuarios (admin, veterinario, adoptante, donante, voluntario)
-- ---------------------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `rol` ENUM('admin','veterinario','adoptante','donante','voluntario') NOT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `direccion` VARCHAR(200) DEFAULT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Animales del refugio
-- ---------------------------------------------------------------------
CREATE TABLE `animales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(80) NOT NULL,
  `especie` VARCHAR(40) NOT NULL,
  `raza` VARCHAR(80) DEFAULT NULL,
  `edad` INT DEFAULT 0,
  `sexo` ENUM('macho','hembra') DEFAULT NULL,
  `descripcion` TEXT,
  `estado` ENUM('disponible','adoptado','en_tratamiento','no_disponible') NOT NULL DEFAULT 'disponible',
  `fecha_ingreso` DATE DEFAULT NULL,
  `foto` VARCHAR(255) DEFAULT NULL,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Adopciones (solicitudes hechas por adoptantes)
-- ---------------------------------------------------------------------
CREATE TABLE `adopciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `animal_id` INT NOT NULL,
  `adoptante_id` INT NOT NULL,
  `motivo` TEXT,
  `documentos` VARCHAR(500) DEFAULT NULL,
  `estado` ENUM('pendiente','aprobada','rechazada','completada') NOT NULL DEFAULT 'pendiente',
  `observaciones` TEXT,
  `fecha_solicitud` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_resolucion` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_adop_animal` FOREIGN KEY (`animal_id`)    REFERENCES `animales`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_adop_user`   FOREIGN KEY (`adoptante_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Historial medico (consultas, vacunas, tratamientos, cirugias)
-- ---------------------------------------------------------------------
CREATE TABLE `historial_medico` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `animal_id` INT NOT NULL,
  `veterinario_id` INT NOT NULL,
  `tipo` ENUM('consulta','vacuna','tratamiento','cirugia') NOT NULL,
  `diagnostico` VARCHAR(255) DEFAULT NULL,
  `vacuna` VARCHAR(100) DEFAULT NULL,
  `tratamiento` VARCHAR(255) DEFAULT NULL,
  `observaciones` TEXT,
  `fecha` DATE NOT NULL,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_hm_animal` FOREIGN KEY (`animal_id`)      REFERENCES `animales`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hm_vet`    FOREIGN KEY (`veterinario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Donaciones (dinero, alimento, medicina, otro)
-- ---------------------------------------------------------------------
CREATE TABLE `donaciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `donante_id` INT NOT NULL,
  `tipo` ENUM('dinero','alimento','medicina','otro') NOT NULL,
  `monto` DECIMAL(10,2) DEFAULT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  `comprobante` VARCHAR(40) UNIQUE NOT NULL,
  `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_don_user` FOREIGN KEY (`donante_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
--  Actividades de voluntariado + inscripciones
-- ---------------------------------------------------------------------
CREATE TABLE `actividades` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(150) NOT NULL,
  `descripcion` TEXT,
  `fecha` DATETIME NOT NULL,
  `duracion_horas` INT NOT NULL DEFAULT 0,
  `cupo_maximo` INT NOT NULL DEFAULT 10,
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `inscripciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `actividad_id` INT NOT NULL,
  `voluntario_id` INT NOT NULL,
  `horas_realizadas` INT NOT NULL DEFAULT 0,
  `inscrito_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unq_act_vol` (`actividad_id`, `voluntario_id`),
  CONSTRAINT `fk_ins_act`  FOREIGN KEY (`actividad_id`)  REFERENCES `actividades`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ins_user` FOREIGN KEY (`voluntario_id`) REFERENCES `usuarios`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
--  DATOS DE DEMOSTRACION
-- =====================================================================

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `telefono`, `direccion`) VALUES
  (1, 'Edwin Aguilar',      'admin@refugio.bo',      '$2y$10$yVDTDPqdgJMHqSboUXCFveV02eG2ZzwuYO36jAo7a2G0Yj5TCLywO', 'admin',       '70000001', 'La Paz'),
  (2, 'Dra. Ana Bustillos', 'vet@refugio.bo',        '$2y$10$INqB./KShwoBy/RbPW19xuwIIbzqVbltzaZ.QZZdhnd28Z1oxPfOK', 'veterinario', '70000002', 'La Paz'),
  (3, 'Ricardo Rodriguez',  'adoptante@refugio.bo',  '$2y$10$5sOq.sd6aynyceNHeW1jGOc/A0.v2sV6O1yq/TAdK.05wycQA5Wdi', 'adoptante',   '70000003', 'La Paz'),
  (4, 'Mauricio Burgoa',    'donante@refugio.bo',    '$2y$10$rTF3sgQHZHwFqiG93DxWt.AhmakG/tH0n2WM4pmMsfYv8rI8WAONe', 'donante',     '70000004', 'La Paz'),
  (5, 'Cristofer Molina',   'voluntario@refugio.bo', '$2y$10$MSXHM8oZgC2fvo4TVnOqP.LOyFsk/4.0K0ZHEc.FRuyIhNaJcwZWq', 'voluntario',  '70000005', 'La Paz');

INSERT INTO `animales` (`id`, `nombre`, `especie`, `raza`, `edad`, `sexo`, `descripcion`, `estado`, `fecha_ingreso`) VALUES
  (1, 'Manchas',  'perro', 'Mestizo',       2, 'macho',  'Perro juguet&oacute;n rescatado del centro.',           'disponible',     CURDATE()),
  (2, 'Luna',     'gato',  'Siames',        3, 'hembra', 'Gata cari&ntilde;osa, le gusta dormir al sol.',         'disponible',     CURDATE()),
  (3, 'Rocky',    'perro', 'Labrador',      5, 'macho',  'En tratamiento por dermatitis leve.',                   'en_tratamiento', CURDATE()),
  (4, 'Firulais', 'perro', 'Pastor Aleman', 4, 'macho',  'Vacunado y desparasitado, buen guardian.',              'disponible',     CURDATE()),
  (5, 'Michi',    'gato',  'Mestizo',       1, 'macho',  'Gato adoptado recientemente, ya tiene hogar.',          'adoptado',       CURDATE());

-- 1 solicitud de adopcion pendiente: el adoptante quiere a Luna.
INSERT INTO `adopciones` (`animal_id`, `adoptante_id`, `motivo`, `estado`) VALUES
  (2, 3, 'Quiero darle un buen hogar a Luna.', 'pendiente');

-- 1 atencion medica: la veterinaria revisa a Rocky.
INSERT INTO `historial_medico` (`animal_id`, `veterinario_id`, `tipo`, `diagnostico`, `observaciones`, `fecha`) VALUES
  (3, 2, 'consulta', 'Dermatitis leve', 'Aplicar pomada cada 12 hrs por 7 dias.', CURDATE());

-- 2 donaciones del donante demo.
INSERT INTO `donaciones` (`donante_id`, `tipo`, `monto`, `descripcion`, `comprobante`) VALUES
  (4, 'dinero',   200.00, 'Aporte mensual',                    'COMP-INICIAL01'),
  (4, 'alimento', NULL,   '2 sacos de croquetas (15 kg c/u)',  'COMP-INICIAL02');

-- 1 actividad de voluntariado dentro de 7 dias + voluntario inscrito.
INSERT INTO `actividades` (`id`, `titulo`, `descripcion`, `fecha`, `duracion_horas`, `cupo_maximo`) VALUES
  (1, 'Jornada de limpieza del refugio',
      'Ayudaremos a limpiar las jaulas, alimentar a los animales y pasearlos.',
      DATE_ADD(NOW(), INTERVAL 7 DAY),
      4, 15);

INSERT INTO `inscripciones` (`actividad_id`, `voluntario_id`, `horas_realizadas`) VALUES
  (1, 5, 0);
