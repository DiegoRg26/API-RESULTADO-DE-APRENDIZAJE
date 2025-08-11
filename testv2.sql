-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-08-2025 a las 08:21:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `testv2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apertura`
--

CREATE TABLE `apertura` (
  `id` int(11) NOT NULL,
  `id_periodo` int(11) NOT NULL,
  `id_relacion_cuestionario_programa` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `apertura`
--

INSERT INTO `apertura` (`id`, `id_periodo`, `id_relacion_cuestionario_programa`, `activo`) VALUES
(1, 2, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion`
--

CREATE TABLE `asignacion` (
  `id` int(11) NOT NULL,
  `id_apertura` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `asignacion`
--

INSERT INTO `asignacion` (`id`, `id_apertura`, `id_estudiante`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `campus`
--

CREATE TABLE `campus` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `campus`
--

INSERT INTO `campus` (`id`, `nombre`) VALUES
(1, 'Cartagena'),
(2, 'Barranquilla');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuestionario`
--

CREATE TABLE `cuestionario` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tiempo_limite` time NOT NULL DEFAULT '00:40:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `cuestionario`
--

INSERT INTO `cuestionario` (`id`, `titulo`, `descripcion`, `tiempo_limite`) VALUES
(1, 'Cuestionario de matemáticas', 'Evaluación de testeo', '00:25:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente`
--

CREATE TABLE `docente` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `identificacion` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `programa_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `docente`
--

INSERT INTO `docente` (`id`, `nombre`, `email`, `identificacion`, `password`, `fecha_registro`, `programa_id`) VALUES
(1, 'ADMIN', 'admin', '1', '$2y$10$kVBhr6Cx0QyHyje9QrDTg.L67NYliD2.sldu3tyfh7g98.hYncW1O', '2025-07-07 14:55:32', NULL),
(3, 'Jose Gracia', 'jose@email.com', '1008254632', '$2y$10$GxLsAcMN5VTd0Ofl9ImDDe/YtMrcJ3CNVqWkZaX5ANaEXJ3azTyoi', '2025-07-08 16:30:03', 10),
(4, 'Arnulfo Ramirez', 'aram@email.com', '1007565841', '$2y$10$5vqeTyZZOMH5rqQMKDoWf.4PbsrzWKMfUZ2EAKVa1jriDhSwbGzya', '2025-07-08 16:36:17', NULL),
(5, 'Alvaro Guzman', 'alvag@email.com', '45485868', '$2y$10$0ijGEKo3p2sQT2K8cI2cFuKJ9myiLrrPChg.3RWcyi4KPZkIURhRy', '2025-07-10 13:34:57', NULL),
(6, 'Jesus Campos', 'jCampos@email.com', '26113021', '$2y$10$5vqeTyZZOMH5rqQMKDoWf.4PbsrzWKMfUZ2EAKVa1jriDhSwbGzya', '2025-07-14 21:28:01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante`
--

CREATE TABLE `estudiante` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `identificacion` varchar(50) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `id_programa` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `estudiante`
--

INSERT INTO `estudiante` (`id`, `email`, `identificacion`, `nombre`, `id_programa`, `estado`) VALUES
(1, 'diego@email.com', '1007260262', 'Diego Román', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intento_cuestionario`
--

CREATE TABLE `intento_cuestionario` (
  `id` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_apertura` int(11) NOT NULL,
  `fecha_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  `completado` tinyint(1) NOT NULL DEFAULT 0,
  `puntaje_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `intento_cuestionario`
--

INSERT INTO `intento_cuestionario` (`id`, `id_estudiante`, `id_apertura`, `fecha_inicio`, `fecha_fin`, `completado`, `puntaje_total`) VALUES
(2, 1, 1, '2025-08-01 10:31:37', '2025-08-01 10:51:37', 1, 150.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nivel`
--

CREATE TABLE `nivel` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `puntaje_maximo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `nivel`
--

INSERT INTO `nivel` (`id`, `nombre`, `puntaje_maximo`) VALUES
(1, 'Tecnico', 100),
(2, 'Tecnologo', 200),
(3, 'Profesional', 300),
(4, 'Especializacion', 300);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opcion_respuesta`
--

CREATE TABLE `opcion_respuesta` (
  `id` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `texto_opcion` text NOT NULL,
  `opcion_correcta` tinyint(1) NOT NULL,
  `orden` int(11) NOT NULL,
  `imagen_opcion` mediumblob DEFAULT NULL,
  `nombre_imagen_opcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `opcion_respuesta`
--

INSERT INTO `opcion_respuesta` (`id`, `id_pregunta`, `texto_opcion`, `opcion_correcta`, `orden`, `imagen_opcion`, `nombre_imagen_opcion`) VALUES
(1, 1, 'París', 1, 0, NULL, NULL),
(2, 1, 'Londres', 0, 1, NULL, NULL),
(3, 1, 'Berlín', 0, 2, NULL, NULL),
(4, 1, 'Bayunca', 0, 3, NULL, NULL),
(5, 2, '3', 0, 0, NULL, NULL),
(6, 2, '4', 1, 1, NULL, NULL),
(7, 2, '5', 0, 2, NULL, NULL),
(8, 2, '9', 0, 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodo`
--

CREATE TABLE `periodo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `periodo`
--

INSERT INTO `periodo` (`id`, `nombre`, `fecha_inicio`, `fecha_fin`, `activo`) VALUES
(1, '2025-01', '2025-07-16', '2025-08-04', 1),
(2, '2025-02', '2025-07-17', '2025-08-14', 1),
(3, '2025-03', '2025-07-23', '2025-07-31', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

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

--
-- Volcado de datos para la tabla `preguntas`
--

INSERT INTO `preguntas` (`id`, `id_cuestionario`, `texto_pregunta`, `orden_pregunta`, `peso_pregunta`, `imagen_pregunta`, `nombre_imagen_pregunta`, `orientacion`) VALUES
(1, 1, '¿Cuál es la capital de Francia?', 0, 150.00, NULL, NULL, 1),
(2, 1, '¿Cuánto es 2 + 2?', 1, 150.00, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programa`
--

CREATE TABLE `programa` (
  `id` int(11) NOT NULL,
  `id_nivel` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `id_campus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `programa`
--

INSERT INTO `programa` (`id`, `id_nivel`, `nombre`, `id_campus`) VALUES
(1, 3, 'Medicina', 1),
(2, 1, 'Técnico en Sistemas', 1),
(3, 2, 'Tecnólogo en Sistemas', 1),
(4, 3, 'Ingeniería de Sistemas', 1),
(5, 1, 'Técnico en Contabilidad', 2),
(6, 2, 'Tecnólogo en Contabilidad y Finanzas', 2),
(7, 3, 'Contaduría Pública', 2),
(8, 1, 'Técnico en Desarrollo de Software', 1),
(9, 2, 'Tecnólogo en Desarrollo de Software', 2),
(10, 3, 'Ingeniería de Software', 1),
(11, 1, 'Técnico en Electrónica', 2),
(12, 2, 'Tecnólogo en Electrónica Industrial', 1),
(13, 3, 'Ingeniería Electrónica', 2),
(14, 1, 'Técnico en Logística', 1),
(15, 2, 'Tecnólogo en Gestión Logística', 2),
(16, 3, 'Administración Logística', 1),
(17, 1, 'Técnico en Gestión Ambiental', 2),
(18, 2, 'Tecnólogo en Saneamiento Ambiental', 1),
(19, 3, 'Ingeniería Ambiental', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `relacion_cuestionario_programa`
--

CREATE TABLE `relacion_cuestionario_programa` (
  `id` int(11) NOT NULL,
  `id_cuestionario` int(11) NOT NULL,
  `id_programa` int(11) NOT NULL,
  `id_docente` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `relacion_cuestionario_programa`
--

INSERT INTO `relacion_cuestionario_programa` (`id`, `id_cuestionario`, `id_programa`, `id_docente`, `activo`) VALUES
(1, 1, 1, 6, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuesta_estudiante`
--

CREATE TABLE `respuesta_estudiante` (
  `id` int(11) NOT NULL,
  `id_intento` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `id_opcion_seleccionada` int(11) DEFAULT NULL,
  `fecha_respuesta` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `respuesta_estudiante`
--

INSERT INTO `respuesta_estudiante` (`id`, `id_intento`, `id_pregunta`, `id_opcion_seleccionada`, `fecha_respuesta`) VALUES
(3, 2, 1, 1, '2025-08-01 10:51:37'),
(4, 2, 2, 5, '2025-08-01 10:51:37');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `apertura`
--
ALTER TABLE `apertura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_periodo` (`id_periodo`,`id_relacion_cuestionario_programa`),
  ADD KEY `id_relacion_cuestionario_programa` (`id_relacion_cuestionario_programa`);

--
-- Indices de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_apertura` (`id_apertura`,`id_estudiante`),
  ADD KEY `id_estudiante` (`id_estudiante`);

--
-- Indices de la tabla `campus`
--
ALTER TABLE `campus`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cuestionario`
--
ALTER TABLE `cuestionario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `docente`
--
ALTER TABLE `docente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `programa_id` (`programa_id`);

--
-- Indices de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identificacion` (`identificacion`,`id_programa`),
  ADD KEY `estudiante_ibfk_1` (`id_programa`);

--
-- Indices de la tabla `intento_cuestionario`
--
ALTER TABLE `intento_cuestionario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `id_apertura` (`id_apertura`);

--
-- Indices de la tabla `nivel`
--
ALTER TABLE `nivel`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `opcion_respuesta`
--
ALTER TABLE `opcion_respuesta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pregunta` (`id_pregunta`);

--
-- Indices de la tabla `periodo`
--
ALTER TABLE `periodo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cuestionario` (`id_cuestionario`);

--
-- Indices de la tabla `programa`
--
ALTER TABLE `programa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_nivel` (`id_nivel`),
  ADD KEY `id_campus` (`id_campus`);

--
-- Indices de la tabla `relacion_cuestionario_programa`
--
ALTER TABLE `relacion_cuestionario_programa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_cuestionario` (`id_cuestionario`,`id_programa`),
  ADD KEY `id_programa` (`id_programa`),
  ADD KEY `id_docente` (`id_docente`);

--
-- Indices de la tabla `respuesta_estudiante`
--
ALTER TABLE `respuesta_estudiante`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pregunta` (`id_pregunta`),
  ADD KEY `id_opcion_seleccionada` (`id_opcion_seleccionada`),
  ADD KEY `id_intento` (`id_intento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `apertura`
--
ALTER TABLE `apertura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `campus`
--
ALTER TABLE `campus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cuestionario`
--
ALTER TABLE `cuestionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `docente`
--
ALTER TABLE `docente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `intento_cuestionario`
--
ALTER TABLE `intento_cuestionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `nivel`
--
ALTER TABLE `nivel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `opcion_respuesta`
--
ALTER TABLE `opcion_respuesta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `periodo`
--
ALTER TABLE `periodo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `programa`
--
ALTER TABLE `programa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `relacion_cuestionario_programa`
--
ALTER TABLE `relacion_cuestionario_programa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `respuesta_estudiante`
--
ALTER TABLE `respuesta_estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `apertura`
--
ALTER TABLE `apertura`
  ADD CONSTRAINT `apertura_ibfk_1` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `apertura_ibfk_2` FOREIGN KEY (`id_relacion_cuestionario_programa`) REFERENCES `relacion_cuestionario_programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `asignacion`
--
ALTER TABLE `asignacion`
  ADD CONSTRAINT `asignacion_ibfk_1` FOREIGN KEY (`id_apertura`) REFERENCES `apertura` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asignacion_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `docente`
--
ALTER TABLE `docente`
  ADD CONSTRAINT `docente_ibfk_1` FOREIGN KEY (`programa_id`) REFERENCES `programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD CONSTRAINT `estudiante_ibfk_1` FOREIGN KEY (`id_programa`) REFERENCES `programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `programa_id` FOREIGN KEY (`id_programa`) REFERENCES `programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `intento_cuestionario`
--
ALTER TABLE `intento_cuestionario`
  ADD CONSTRAINT `intento_cuestionario_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiante` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `intento_cuestionario_ibfk_2` FOREIGN KEY (`id_apertura`) REFERENCES `apertura` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `opcion_respuesta`
--
ALTER TABLE `opcion_respuesta`
  ADD CONSTRAINT `opcion_respuesta_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`id_cuestionario`) REFERENCES `cuestionario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `programa`
--
ALTER TABLE `programa`
  ADD CONSTRAINT `programa_ibfk_1` FOREIGN KEY (`id_nivel`) REFERENCES `nivel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `programa_ibfk_2` FOREIGN KEY (`id_campus`) REFERENCES `campus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `relacion_cuestionario_programa`
--
ALTER TABLE `relacion_cuestionario_programa`
  ADD CONSTRAINT `relacion_cuestionario_programa_ibfk_1` FOREIGN KEY (`id_cuestionario`) REFERENCES `cuestionario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relacion_cuestionario_programa_ibfk_2` FOREIGN KEY (`id_programa`) REFERENCES `programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relacion_cuestionario_programa_ibfk_3` FOREIGN KEY (`id_docente`) REFERENCES `docente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `respuesta_estudiante`
--
ALTER TABLE `respuesta_estudiante`
  ADD CONSTRAINT `respuesta_estudiante_ibfk_1` FOREIGN KEY (`id_intento`) REFERENCES `intento_cuestionario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `respuesta_estudiante_ibfk_2` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `respuesta_estudiante_ibfk_3` FOREIGN KEY (`id_opcion_seleccionada`) REFERENCES `opcion_respuesta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
