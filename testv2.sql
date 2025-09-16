SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `apertura` (
  `id` int(11) NOT NULL,
  `id_periodo` int(11) NOT NULL,
  `id_relacion_cuestionario_programa` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `asignacion` (
  `id` int(11) NOT NULL,
  `id_apertura` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `campus` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `cuestionario` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tiempo_limite` time NOT NULL DEFAULT '00:40:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `docente` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `identificacion` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `programa_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `estudiante` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `identificacion` varchar(50) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `intento_cuestionario` (
  `id` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_apertura` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  `completado` tinyint(1) NOT NULL DEFAULT 0,
  `puntaje_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `nivel` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `puntaje_maximo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `opcion_respuesta` (
  `id` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `texto_opcion` text NOT NULL,
  `opcion_correcta` tinyint(1) NOT NULL,
  `orden` int(11) NOT NULL,
  `imagen_opcion` mediumblob DEFAULT NULL,
  `nombre_imagen_opcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `periodo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `preguntas` (
  `id` int(11) NOT NULL,
  `id_cuestionario` int(11) NOT NULL,
  `texto_pregunta` text NOT NULL,
  `orden_pregunta` int(11) NOT NULL,
  `peso_pregunta` decimal(5,2) NOT NULL,
  `imagen_pregunta` mediumblob DEFAULT NULL,
  `nombre_imagen_pregunta` varchar(255) DEFAULT NULL,
  `orientacion` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `programa` (
  `id` int(11) NOT NULL,
  `id_nivel` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `id_campus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `progreso_cuestionarios_intentos` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `cuestionario_id` int(11) NOT NULL,
  `tiempo_total` time NOT NULL,
  `tiempo_guardado` time NOT NULL,
  `pregunta_opcion_guardado` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`pregunta_opcion_guardado`)),
  `fecha_realizado` datetime NOT NULL,
  `fecha_inicio` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `raes_modulos` (
  `courseID` int(11) DEFAULT 0,
  `cuestionario_id` int(11) NOT NULL DEFAULT 0,
  `abreviatura` varchar(10) NOT NULL,
  `descripcion` varchar(400) NOT NULL,
  `programa_id` int(11) NOT NULL DEFAULT 0,
  `id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `raes_modulos_indicadores` (
  `moduloID` varchar(10) NOT NULL,
  `puntaje_min` decimal(10,2) DEFAULT NULL,
  `puntaje_max` decimal(10,2) DEFAULT NULL,
  `nivel` varchar(4) NOT NULL,
  `indicadores` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `relacion_cuestionario_programa` (
  `id` int(11) NOT NULL,
  `id_cuestionario` int(11) NOT NULL,
  `id_programa` int(11) DEFAULT NULL,
  `id_docente` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `relacion_programa_estudiante` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `programa_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `respuesta_estudiante` (
  `id` int(11) NOT NULL,
  `id_intento` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `id_opcion_seleccionada` int(11) DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

CREATE TABLE `sesion_estudiante` (
  `id` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `jwt_jti` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_ultima_actividad` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;


ALTER TABLE `apertura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_periodo` (`id_periodo`,`id_relacion_cuestionario_programa`),
  ADD KEY `id_relacion_cuestionario_programa` (`id_relacion_cuestionario_programa`);

ALTER TABLE `asignacion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_apertura` (`id_apertura`,`id_estudiante`),
  ADD KEY `id_estudiante` (`id_estudiante`);

ALTER TABLE `campus`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cuestionario`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `docente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `programa_id` (`programa_id`);

ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `intento_cuestionario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `id_apertura` (`id_apertura`);

ALTER TABLE `nivel`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `opcion_respuesta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pregunta` (`id_pregunta`);

ALTER TABLE `periodo`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cuestionario` (`id_cuestionario`);

ALTER TABLE `programa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nivel` (`id_nivel`),
  ADD KEY `id_campus` (`id_campus`);

ALTER TABLE `progreso_cuestionarios_intentos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `raes_modulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `raes_modulos_ibfk_1` (`programa_id`);

ALTER TABLE `raes_modulos_indicadores`
  ADD PRIMARY KEY (`moduloID`,`nivel`);

ALTER TABLE `relacion_cuestionario_programa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_cuestionario` (`id_cuestionario`,`id_programa`),
  ADD KEY `id_programa` (`id_programa`),
  ADD KEY `id_docente` (`id_docente`);

ALTER TABLE `relacion_programa_estudiante`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `respuesta_estudiante`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pregunta` (`id_pregunta`),
  ADD KEY `id_opcion_seleccionada` (`id_opcion_seleccionada`),
  ADD KEY `id_intento` (`id_intento`);

ALTER TABLE `sesion_estudiante`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_jwt_jti` (`jwt_jti`),
  ADD UNIQUE KEY `idx_session_token` (`session_token`) USING BTREE,
  ADD KEY `idx_estudiante_activa` (`id_estudiante`,`activa`),
  ADD KEY `idx_fecha_actividad` (`fecha_ultima_actividad`,`activa`),
  ADD KEY `unique_student_session` (`id_estudiante`) USING BTREE;


ALTER TABLE `apertura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `asignacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `campus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `cuestionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `docente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `intento_cuestionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `nivel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `opcion_respuesta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `periodo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `programa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `progreso_cuestionarios_intentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `relacion_cuestionario_programa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `relacion_programa_estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `respuesta_estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sesion_estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `apertura`
  ADD CONSTRAINT `apertura_ibfk_1` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `apertura_ibfk_2` FOREIGN KEY (`id_relacion_cuestionario_programa`) REFERENCES `relacion_cuestionario_programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `asignacion`
  ADD CONSTRAINT `asignacion_ibfk_1` FOREIGN KEY (`id_apertura`) REFERENCES `apertura` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asignacion_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `docente`
  ADD CONSTRAINT `docente_ibfk_1` FOREIGN KEY (`programa_id`) REFERENCES `programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `estudiante`
  ADD CONSTRAINT `estudiante_ibfk_1` FOREIGN KEY (`id_programa`) REFERENCES `programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `programa_id` FOREIGN KEY (`id_programa`) REFERENCES `programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `intento_cuestionario`
  ADD CONSTRAINT `intento_cuestionario_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `intento_cuestionario_ibfk_2` FOREIGN KEY (`id_apertura`) REFERENCES `apertura` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `opcion_respuesta`
  ADD CONSTRAINT `opcion_respuesta_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`id_cuestionario`) REFERENCES `cuestionario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `programa`
  ADD CONSTRAINT `programa_ibfk_1` FOREIGN KEY (`id_nivel`) REFERENCES `nivel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `programa_ibfk_2` FOREIGN KEY (`id_campus`) REFERENCES `campus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `raes_modulos`
  ADD CONSTRAINT `raes_modulos_ibfk_1` FOREIGN KEY (`programa_id`) REFERENCES `programa` (`id`) ON UPDATE CASCADE;

ALTER TABLE `raes_modulos_indicadores`
  ADD CONSTRAINT `MODU_INDI` FOREIGN KEY (`moduloID`) REFERENCES `raes_modulos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `relacion_cuestionario_programa`
  ADD CONSTRAINT `relacion_cuestionario_programa_ibfk_1` FOREIGN KEY (`id_cuestionario`) REFERENCES `cuestionario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relacion_cuestionario_programa_ibfk_2` FOREIGN KEY (`id_programa`) REFERENCES `programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relacion_cuestionario_programa_ibfk_3` FOREIGN KEY (`id_docente`) REFERENCES `docente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `relacion_programa_estudiante`
  ADD CONSTRAINT `relacion_programa_estudiante_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiante` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relacion_programa_estudiante_ibfk_2` FOREIGN KEY (`programa_id`) REFERENCES `programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `respuesta_estudiante`
  ADD CONSTRAINT `respuesta_estudiante_ibfk_1` FOREIGN KEY (`id_intento`) REFERENCES `intento_cuestionario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `respuesta_estudiante_ibfk_2` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `respuesta_estudiante_ibfk_3` FOREIGN KEY (`id_opcion_seleccionada`) REFERENCES `opcion_respuesta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sesion_estudiante`
  ADD CONSTRAINT `sesion_estudiante_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
