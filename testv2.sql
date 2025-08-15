-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-08-2025 a las 11:19:39
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion`
--

CREATE TABLE `asignacion` (
  `id` int(11) NOT NULL,
  `id_apertura` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

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
(1, 'Cuestionario de matemáticas', 'Evaluación de testeo', '00:25:00'),
(2, 'Test Jose', '[DESCRIPCION] del testeo de creacion', '00:40:00'),
(3, 'Test N°2 for API RESTful', 'Disclaimer, this description is only for test.', '00:40:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `desempeno_indicadores`
--

CREATE TABLE `desempeno_indicadores` (
  `nivelID` int(11) NOT NULL,
  `puntaje_min` decimal(10,2) DEFAULT NULL,
  `puntaje_max` decimal(10,2) DEFAULT NULL,
  `nivel` varchar(4) DEFAULT NULL,
  `indicadores` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `desempeno_indicadores`
--

INSERT INTO `desempeno_indicadores` (`nivelID`, `puntaje_min`, `puntaje_max`, `nivel`, `indicadores`) VALUES
(211, 0.00, 83.00, '1', ' - Define conceptos clave como modelos matemáticos-físicos, álgebra, lógica computacional, algoritmos, probabilidad y estadística.\r\n - Explica con sus propias palabras métodos de recolección y sistematización de datos (ej:  muestreo, encuestas).\r\n - Identifica las características de problemas probabilísticos y estadísticos en contextos profesionales.\r\n - Describe la relación entre modelos matemáticos y la toma de decisiones en ingeniería.'),
(211, 84.00, 104.00, '2', ' - Utiliza fórmulas algebraicas, lógica booleana o algoritmos para procesar datos en escenarios simulados.\r\n - Implementa técnicas básicas de analítica (ej.: medidas de tendencia central, regresión lineal) en conjuntos de datos.\r\n - Aplica modelos probabilísticos (ej.: distribuciones binomial o normal) para predecir resultados.\r\n - Ejecuta procedimientos de sistematización de datos mediante herramientas como hojas de cálculo o lenguajes de programación (Python, R).'),
(211, 105.00, 132.00, '3', ' - Compara críticamente modelos matemáticos para determinar su idoneidad en problemas específicos (ej.: decisión entre modelo determinista vs. estocástico).\r\n - Evalúa la calidad de los datos recolectados y propone métodos para minimizar sesgos.\r\n - Interpreta resultados estadísticos (ej.: intervalos de confianza, pruebas de hipótesis) para sustentar decisiones.\r\n - Valora la eficiencia de algoritmos en términos de complejidad computacional para soluciones escalables.'),
(211, 133.00, 200.00, '4', ' - Diseña métodos personalizados de recolección y análisis de datos adaptados a fenómenos profesionales (ej.: algoritmo para predecir fallos en redes).\r\n - Construye modelos matemático-físicos innovadores que integren álgebra, lógica y probabilidad para resolver problemas complejos.\r\n - Propone soluciones basadas en analítica avanzada (ej.: aprendizaje automático) para optimizar procesos.\r\n - Genera informes técnicos que vinculen resultados analíticos con la toma de decisiones estratégicas.'),
(212, 0.00, 83.00, '1', '- Identifica las áreas fundamentales de conocimiento de la ingeniería de sistemas (ej.: HUMANÍSTICA, INVESTIGACIÓN).'),
(212, 84.00, 104.00, '2', '- Explica con sus palabras la relación entre las áreas de la ingeniería y su impacto en la APLICADA Y GESTIÓN DE PROYECTOS.'),
(212, 105.00, 132.00, '3', '- Utiliza herramientas/metodologías de ingeniería (ej.: diseño de software, gestión de proyectos).'),
(212, 133.00, 200.00, '4', '- Analiza críticamente el impacto social y ambiental de proyectos de ingeniería existentes.'),
(213, 0.00, 83.00, '1', '- Define conceptos clave: software, algoritmos, estructuras de datos, paradigmas de programación.'),
(213, 84.00, 104.00, '2', '- Explica cómo las estructuras de datos (árboles, grafos, tablas hash) optimizan el procesamiento de información.'),
(213, 105.00, 132.00, '3', '- Implementa algoritmos eficientes (ordenamiento, búsqueda, grafos) en un lenguaje de programación.'),
(213, 133.00, 200.00, '4', '- Analiza la escalabilidad y mantenibilidad de un sistema de software existente, identificando oportunidades de mejora.'),
(214, 0.00, 83.00, '1', '- Define conceptos clave: infraestructura TI, redes (LAN/WAN), telecomunicaciones (VoIP, etc.).'),
(214, 84.00, 104.00, '2', '- Explica la arquitectura de redes y su impacto en el desempeño organizacional (latencia, ancho de banda, disponibilidad).'),
(214, 105.00, 132.00, '3', '- Configura infraestructura de redes (VLANs, VPNs) y dispositivos (Cisco, Huawei) usando herramientas especializadas.'),
(214, 133.00, 200.00, '4', '- Analiza la escalabilidad de infraestructuras TI frente a demandas organizacionales (ej.: crecimiento de usuarios, servicios en la nube).'),
(215, 0.00, 83.00, '1', 'Reconoce componentes electrónicos fundamentales (sensores, actuadores, etc.)'),
(215, 84.00, 104.00, '2', 'Explica el flujo de señales en sistemas de control (analógico ? digital ? información)'),
(215, 105.00, 132.00, '3', 'Diseña circuitos electrónicos para condiciones operativas específicas'),
(215, 133.00, 200.00, '4', 'Diagnostica problemas en sistemas automatizados usando análisis de señales'),
(141, 0.00, 125.00, '1', ' - Define conceptos clave como modelos matemáticos-físicos, álgebra, lógica computacional, algoritmos, probabilidad y estadística.\r\n - Explica con sus propias palabras métodos de recolección y sistematización de datos (ej:  muestreo, encuestas).\r\n - Identifica las características de problemas probabilísticos y estadísticos en contextos profesionales.\r\n - Describe la relación entre modelos matemáticos y la toma de decisiones en ingeniería.'),
(141, 126.00, 156.00, '2', ' - Utiliza fórmulas algebraicas, lógica booleana o algoritmos para procesar datos en escenarios simulados.\r\n - Implementa técnicas básicas de analítica (ej.: medidas de tendencia central, regresión lineal) en conjuntos de datos.\r\n - Aplica modelos probabilísticos (ej.: distribuciones binomial o normal) para predecir resultados.\r\n - Ejecuta procedimientos de sistematización de datos mediante herramientas como hojas de cálculo o lenguajes de programación (Python, R).'),
(141, 157.00, 198.00, '3', ' - Compara críticamente modelos matemáticos para determinar su idoneidad en problemas específicos (ej.: decisión entre modelo determinista vs. estocástico).\r\n - Evalúa la calidad de los datos recolectados y propone métodos para minimizar sesgos.\r\n - Interpreta resultados estadísticos (ej.: intervalos de confianza, pruebas de hipótesis) para sustentar decisiones.\r\n - Valora la eficiencia de algoritmos en términos de complejidad computacional para soluciones escalables.'),
(141, 199.00, 300.00, '4', ' - Diseña métodos personalizados de recolección y análisis de datos adaptados a fenómenos profesionales (ej.: algoritmo para predecir fallos en redes).\r\n - Construye modelos matemático-físicos innovadores que integren álgebra, lógica y probabilidad para resolver problemas complejos.\r\n - Propone soluciones basadas en analítica avanzada (ej.: aprendizaje automático) para optimizar procesos.\r\n - Genera informes técnicos que vinculen resultados analíticos con la toma de decisiones estratégicas.'),
(142, 0.00, 125.00, '1', '- Identifica las áreas fundamentales de conocimiento de la ingeniería de sistemas (ej.: HUMANÍSTICA, INVESTIGACIÓN).'),
(142, 126.00, 156.00, '2', '- Explica con sus palabras la relación entre las áreas de la ingeniería y su impacto en la APLICADA Y GESTIÓN DE PROYECTOS.'),
(142, 157.00, 198.00, '3', '- Utiliza herramientas/metodologías de ingeniería (ej.: diseño de software, gestión de proyectos).'),
(142, 199.00, 300.00, '4', '- Analiza críticamente el impacto social y ambiental de proyectos de ingeniería existentes.'),
(143, 0.00, 125.00, '1', '- Define conceptos clave: software, algoritmos, estructuras de datos, paradigmas de programación.'),
(143, 126.00, 156.00, '2', '- Explica cómo las estructuras de datos (árboles, grafos, tablas hash) optimizan el procesamiento de información.'),
(143, 157.00, 198.00, '3', '- Implementa algoritmos eficientes (ordenamiento, búsqueda, grafos) en un lenguaje de programación.'),
(143, 199.00, 300.00, '4', '- Analiza la escalabilidad y mantenibilidad de un sistema de software existente, identificando oportunidades de mejora.'),
(144, 0.00, 125.00, '1', '- Define conceptos clave: infraestructura TI, redes (LAN/WAN), telecomunicaciones (VoIP, etc.).'),
(144, 126.00, 156.00, '2', '- Explica la arquitectura de redes y su impacto en el desempeño organizacional (latencia, ancho de banda, disponibilidad).'),
(144, 157.00, 198.00, '3', '- Configura infraestructura de redes (VLANs, VPNs) y dispositivos (Cisco, Huawei) usando herramientas especializadas.'),
(144, 199.00, 300.00, '4', '- Analiza la escalabilidad de infraestructuras TI frente a demandas organizacionales (ej.: crecimiento de usuarios, servicios en la nube).'),
(111, 0.00, 124.00, '1', '* Recorda las etapas del desarrollo embriológico craneofacial y características histológicas del esmalte, dentina y pulpa.                                                                                                                                                                                                        * Enumera los componentes estructurales de dientes, encías, lengua, estructuras orales y músculos de la masticación y su inervación                                                                                                                                                                                           * Recuerda los mecanismos básicos de la digestión, masticación, funciones de la saliva y sus componentes.'),
(111, 125.00, 156.00, '2', '* Explica la relación entre la anatomía y la función del sistema estomatognático y describe cómo los procesos biológicos influyen en la salud oral.                                                                                                                                                           * Interpreta los mecanismos de defensa de la cavidad oral y explica la relación entre pH salival y salud dental.\r\n* Explica la importancia de la vascularización e inervación oral e interpreta los signos y síntomas básicos de alteraciones funcionales.'),
(111, 157.00, 198.00, '3', '* Aplica los conocimientos anatómicos para localizar estructuras en modelos simulados y utiliza principios fisiológicos para explicar síntomas específicos de las patologias.                                                                                                                          * Ejecuta técnicas de exploración clínica sistematizada y aplica métodos de diagnóstico por imagen para identificar estructuras dentofaciales.\r\n* Aplica principios embriológicos para explicar malformaciones y utiliza conocimientos de fisiología para interpretar pruebas funcionales.'),
(111, 199.00, 300.00, '4', '* Examina las interacciones entre diferentes sistemas que afectan la salud oral e identifica patrones de normalidad y anormalidad en estructuras orales.                                                                                                                                                         * Analiza la literatura científica sobre procesos morfofuncionales, comparando diferentes teorías sobre desarrollo y función del sistema estomatognático.                                                                                                                                                     * Sintetiza información de múltiples fuentes para crear marcos conceptuales, evaluando críticamente metodologías de investigación en ciencias básicas odontológicas                                                                                             '),
(112, 0.00, 124.00, '1', '* Identifica los determinantes sociales de la salud en contextos comunitarios específicos.                                             * Reconoce los lineamientos nacionales e internacionales en promoción y prevención en salud oral.                                                       * Enumera las estrategias históricas y actuales de salud pública implementadas en Colombia. \r\n* Recuerda conceptos fundamentales sobre salud colectiva, epidemiología y políticas públicas.'),
(112, 125.00, 156.00, '2', '* Explica la importancia de la promoción y prevención en la transformación social y la equidad en salud oral.\r\n* Interpreta indicadores epidemiológicos y datos sociales para caracterizar una comunidad.\r\n* Describe la relación entre las condiciones sociales y los perfiles de riesgo en salud bucal.\r\n* Resume el impacto de políticas públicas y programas internacionales en el ámbito local.\r\n'),
(112, 157.00, 198.00, '3', '* Diseña y ejecuta proyectos de intervención en salud oral basados en diagnósticos previos.\r\n* Aplica protocolos y guías en actividades de promoción y prevención con enfoque diferencial y territorial.\r\n* Utiliza herramientas de investigación (encuestas, entrevistas, análisis de datos) para sustentar decisiones.\r\n* Implementa estrategias educativas en salud oral adaptadas a diversas poblaciones vulnerables.'),
(112, 199.00, 300.00, '4', '* Analiza críticamente la relación entre políticas públicas y condiciones reales de salud oral en la comunidad.\r\n* Evalúa el impacto de intervenciones en promoción y prevención desde indicadores de calidad de vida.\r\n* Diferencia entre enfoques biomédicos y socio-sanitarios al abordar los problemas de salud pública oral.\r\n*Contrasta evidencias científicas y experiencias comunitarias para orientar la toma de decisiones en salud colectiva.'),
(112, 0.00, 124.00, '1', '* Identifica los determinantes sociales de la salud en contextos comunitarios específicos.                                             * Reconoce los lineamientos nacionales e internacionales en promoción y prevención en salud oral.                                                       * Enumera las estrategias históricas y actuales de salud pública implementadas en Colombia. \r\n* Recuerda conceptos fundamentales sobre salud colectiva, epidemiología y políticas públicas.'),
(112, 125.00, 156.00, '2', '* Explica la importancia de la promoción y prevención en la transformación social y la equidad en salud oral.\r\n* Interpreta indicadores epidemiológicos y datos sociales para caracterizar una comunidad.\r\n* Describe la relación entre las condiciones sociales y los perfiles de riesgo en salud bucal.\r\n* Resume el impacto de políticas públicas y programas internacionales en el ámbito local.\r\n'),
(112, 157.00, 198.00, '3', '* Diseña y ejecuta proyectos de intervención en salud oral basados en diagnósticos previos.\r\n* Aplica protocolos y guías en actividades de promoción y prevención con enfoque diferencial y territorial.\r\n* Utiliza herramientas de investigación (encuestas, entrevistas, análisis de datos) para sustentar decisiones.\r\n* Implementa estrategias educativas en salud oral adaptadas a diversas poblaciones vulnerables.'),
(112, 199.00, 300.00, '4', '* Analiza críticamente la relación entre políticas públicas y condiciones reales de salud oral en la comunidad.\r\n* Evalúa el impacto de intervenciones en promoción y prevención desde indicadores de calidad de vida.\r\n* Diferencia entre enfoques biomédicos y socio-sanitarios al abordar los problemas de salud pública oral.\r\n*Contrasta evidencias científicas y experiencias comunitarias para orientar la toma de decisiones en salud colectiva.'),
(113, 0.00, 124.00, '1', '* Identifica estructuras anatómicas relevantes para el diagnóstico clínico y radiográfico.\r\n* Enumera signos y síntomas asociados a las patologías buco-dentales más frecuentes.\r\n* Reconoce los protocolos básicos para la recolección de historia clínica y examen físico.\r\n* Recuerda las bases fisiopatológicas de las enfermedades orales para su correcta clasificación.'),
(113, 125.00, 156.00, '2', '* Explica la relación entre factores etiológicos y la manifestación clínica de patologías buco-dentales.\r\n* Interpreta radiografías y otros estudios auxiliares para apoyar el diagnóstico bucales.\r\n* Clasifica los casos clínicos según su complejidad y área clínica. \r\n* Describe los elementos que influyen en el pronóstico de un caso odontológico.'),
(113, 157.00, 198.00, '3', '* Realiza la historia clínica completa integrando hallazgos subjetivos y objetivos.\r\n* Emite diagnósticos diferenciales y definitivos en pacientes de diversas edades.\r\n* Elabora planes de tratamiento individualizados considerando el área clínica correspondiente.\r\n* Aplica protocolos clínicos y guías de práctica en la toma de decisiones terapéuticas.'),
(113, 199.00, 300.00, '4', '* Analiza integralmente los datos clínicos, radiográficos y de laboratorio para determinar pronóstico del caso clinico.\r\n* Diferencia entre patologías con manifestaciones clínicas similares para evitar errores diagnósticos.\r\n* Prioriza tratamientos según urgencia, riesgo, pronóstico y necesidades del paciente.\r\n* Examina la evolución clínica del paciente para ajustar el plan de tratamiento si es necesario.'),
(114, 0.00, 124.00, '1', '* Identifica los principios éticos fundamentales del ejercicio odontológico.\r\n* Reconoce los derechos y deberes del paciente según la legislación vigente.\r\n* Recuerda los fundamentos del juramento hipocrático y el código deontológico odontológico.\r\n* Enumera valores humanísticos como el respeto, la empatía, la solidaridad y la responsabilidad social.'),
(114, 125.00, 156.00, '2', '* Explica la importancia de la comunicación empática con el paciente como parte del acto clínico.\r\n* Justifica decisiones clínicas considerando principios éticos y el bienestar del paciente.\r\n* Interpreta dilemas éticos comunes en la práctica odontológica y sugiere alternativas coherentes.\r\n* Describe el papel del odontólogo como agente transformador en contextos sociales vulnerables.'),
(114, 157.00, 198.00, '3', '* Establece una relación de confianza con los pacientes basada en el respeto y la confidencialidad.\r\n* Aplica el consentimiento informado en todos los procedimientos, explicando riesgos, beneficios y alternativas.\r\n* Toma decisiones clínicas con base en la equidad y el respeto a la dignidad humana, incluso con recursos limitados.\r\n* Actúa con honestidad y responsabilidad en el manejo de errores, conflictos o situaciones adversas.'),
(114, 199.00, 300.00, '4', '* Analiza dilemas éticos complejos en la atención odontológica y plantea soluciones fundamentadas.\r\n* Diferencia entre conductas profesionales y no profesionales en el ámbito clínico y académico.\r\n* Reflexiona sobre sus propias actitudes y prejuicios, identificando áreas de mejora personal.\r\n* Evalúa cómo los determinantes sociales afectan la equidad en el acceso y la atención en salud oral, y propone estrategias para mitigarlos.'),
(131, 0.00, 125.00, '1', 'Reconoce las diversas teorías y concepciones\r\n jurídicas y sociojurídicas del Derecho.\r\nInterpreta la diferentes manifestaciones\r\n jurídicas y sociojurídicas \r\ndel Derecho dentro del contexto Social \r\neconómio y Político'),
(131, 126.00, 156.00, '2', 'Aplica los fundamentos teorico-practicos del \r\nsistema Jurídico Nacional e internacional\r\n\r\nResuelve los problemas jurídicos y sociojurídicos del Derecho, \r\npresentes en el sistema jurídico colombiano mediante la aplicación de diversas teorías y concepciones jurídicas y sociojurídicas'),
(131, 157.00, 198.00, '3', 'Analiza dentro del conjunto de procedimientos inmersos en el sistema jurídico Colombiano \r\ncual es el proceso idóneo para la solución de la problemática presente \r\n\r\nCompara en pro de lograr una solucion justa cual es la acción o medio de control \r\nprocedente de acuerdo a la guía del Derecho objeto de conflicto.'),
(131, 199.00, 300.00, '4', 'Propone soluciones jurídicas fundamentadas frente a situaciones propias del contexto colombiano.\r\nEvalúa críticamente la efectividad de las normas legales y procedimientos jurídicos \r\nen la resolución de problemas sociales, económicos y políticos.'),
(132, 0.00, 125.00, '1', 'Reconoce los diferentes instrumentos legales aplicables dentro de los diferentes escenarios de la abogacía\r\nInterpreta los fundamentos constitucionales y legales del ejercicio de la abogacía en los diferentes escenarios jurídicos mediados por las TIC del Sistema Procesal Colombiano.'),
(132, 126.00, 156.00, '2', 'Aplica sus competencias profesionales en los diferentes escenarios jurídicos y Socio- juridicos del Sistema juridico nacional e internacional,  a traves del uso de los instrumentos legales.\r\n\r\nResuelve conflictos jurídicos y socio-jurÍdicos, fundamentandose en los instrumentos legales disponibles y en el uso de las TIC'),
(132, 157.00, 198.00, '3', 'Analiza las acciones o medios de control más procedentes para la gestión social, económica, política y ambiental mediante el uso de las TIC dentro de los conflictos jurídicos y socio-humanísticos presentes en el Estado Colombiano.\r\n\r\nCompara distintos escenarios jurídicos según su eficacia en la gestión de conflictos mediados por las TIC '),
(132, 199.00, 300.00, '4', 'Propone soluciones idóneas y conducentes para la gestión jurídica y social de los conflictos, fundamentadas en el uso de TIC y en la normativa vigente. \r\nEvalúa  los intrumentos legales aplicables en la gestion social, economica, politica y ambiental como medios de accion dentro de la intervencion juiridica y socio juridica en el sistema de justicia colombiano mediado por las TIC'),
(133, 0.00, 125.00, '1', 'Reconoce los diversos mecanismos alternativos de solución de conflictos\r\nDescribe los diversos mecanismos alternativos de solución de conflictos \r\nComprende de manera ética y conforme a los principios que rigen la profesión, los mecanismos alternativos de solución de conflictos '),
(133, 126.00, 156.00, '2', 'Desarrolla estrategias de solucion de conflictos aplicando mecanismos alternos adecuados a los contextos administrativos y judicial, de manera etica y conforme a los principios profesionales.\r\n\r\nUtiliza de forma pertinente los diferentes mecanismos alternativos de solución de conflictos, demostrando conocimiento, dominio, y actuación ética en diversos escenarios de desempeño.\r\n\r\nImplementa mecanismos alternativos de solución de conflictos en casos concretos, garantizando su correcta aplicación dentro de los principios de la profesión.\r\n\r\nAplica los mecanismos alternativos de solución de conflictos en los contextos administrativo y judicial, de manera ética y conforme a los principios que rigen la profesión.\r\n\r\nEjecuta procedimientos de solución de conflictos mediante mecanismos alternativos, asegurando su adecuada puesta en practica en los ambitos administrativos y judicial.\r\n\r\n\r\n'),
(133, 157.00, 198.00, '3', 'Compara los distintos mecanismos alternativos de solución de conflictos, identificando sus ventajas, limitaciones, y aplicación en contextos administrativos y judiciuales, bajo principios éticos.\r\n\r\nAnaliza la pertinencia y efectividad del mecanismo alternativo de solución de conflicto a ser aplicado.\r\n\r\nInterpreta los principios legales y éticos que rigen los mecanismos alternativos de solución de conflictos, para su aplicación correcta en los diferentes contextos administrativos y judiciales.\r\n\r\nValora la importancia del uso ético y responsable de los mecanismos alternativos de solución de conflictos, como herramienta efectiva para la resolución pacífica en los distintos contextos.'),
(133, 199.00, 300.00, '4', 'Evalúa de manera crítica la aplicabilidad y efectividad de los divesos mecanismos alternos de solución de conflictos en distintos contextos administrativos y judiciales, considerando los principíos éticos y profesionales.\r\n\r\nPropone soluciones viables a conflictos específicos mediante el uso adecuado de los mecanismos alternos de solución de conflictos , fundamentando su elección con base en criterios, legales, técnicos y éticos.\r\n\r\nDiseña estrategias integrales de solución de conflictos que integren mecanismos alternativos ajustados a casos particulares.\r\n\r\nArgumenta con solidez jurídica y ética la selección y aplicación de mecanismos alternativos de solución de conflictos.'),
(134, 0.00, 125.00, '1', 'Reconoce los principios del metodo científico y sus etapas fundamentales, identificando su importancia en la formulación de proyectos de investigación jurídica y sociojurídica.\r\n\r\nDescribe las carcaterísticas, procesos y componentes esenciales de los proyectos de investigación formativa, jurídica y sociojurídica, de los diferentes contextos y problemáticas.\r\n\r\nDefine problemas jurídicos y sociojurídicos susceptibles de ser abordados mediante proyectos de investigación, formulando objetivos claros y pertinentes para su desarrollo.'),
(134, 126.00, 156.00, '2', 'Utiliza de manera adecuada herramientas y tecnicas metodologicas, para la formulación de proyectos de investigación jurídico y sociojurídico.\r\n\r\nAplica correctamente los pasos del metodo cientifico en la estructuración y desarrollo de investigación jurídica y sociojurídica.\r\n\r\nImplementa actividades propias de los proyectos de investigación jurídica y sociojurídica, siguiendo criterios tecnicos, metodologicos y éticos.'),
(134, 157.00, 198.00, '3', 'Compara diferentes enfoques y metodologias de investigación jurídica y sociojuridica, identificando sus ventajas y limitaciones para dar respuesta a necesidades concretas.\r\n\r\nAnaliza la pertinencia, calidad y viabilidad de los proyectos de investigacióbn juridica y sociojuridca, en relacion con las provbelmnaticas y contextos.\r\n\r\nInterpreta de manera argumentada, los resultados de investigación juridica y sociojurídica, valorando su impacto en la resolucion de necesidades juridcas reales.\r\n\r\nValora la importancia del uso del metodo cientifico, y la investigación como herramientas fundamentales para la intervención y gestión de problemáticas jurídicas y sociojurídicas.\r\n\r\n'),
(134, 199.00, 300.00, '4', 'Evalúa de manera crítica el impacto y la viabilidad de proyectos de investigación jurídica y sociojurídica, considerando las necesidades del contexto.\r\n\r\nPropone soluciones innovadoras a problematicas juridicas y sociojuridicas, mediante proyectos de investigación que respondan a las necesidades del contexto.\r\n\r\nDiseña proyectos de investigación juridica y sociojuridica, con estructura metodologica solida, fundamentados en el analisis critico de las problemáticas orientados a la gestión y solución.'),
(135, 0.00, 125.00, '1', 'Reconoce los principios, normas y fundamentos jurídicos básicos que permiten abordar conflictos o problemas jurídicos y sociojurídicos.\r\n\r\nDescribe las características y elementos escenciales de los hechos jurídicos, asi como las fuentes aplicable spara la adecuada resolucion de conflictos.'),
(135, 126.00, 156.00, '2', 'Define conceptos, categorias jurídicas y sociojurídicas relevantes para la comprensión y analisis de porblemas en distintos contextos legales.\r\n\r\nUtiliza de manera adecuada las normas, principios y fundamentos, jurpidicos para abordar conflictos jurídicos y sociojurídicos concretos.\r\n\r\nImplementa estrategias jurídicas básicas orientadas a la resolución de conflictos y la toma de decisiones fundamentadas en el marco legal correspondiente.\r\n\r\nAplica correctamente los procedimientos y herramientas jurídicas para establecer la relación entre los hechos y las fuentes del derechos en la solución de problemas.'),
(135, 157.00, 198.00, '3', 'Compara diferentes normas, doctrina y soluciones jurídicas posibles para la resolución de conflictos, valorando su pertinencia, frente a los hechos presentados.\r\n\r\nInterpreta de manera critica las normas juridicas, los hechos, y las fuentes del derecho para construir soluciones coherentes y fundamentadas a probkemas jurpidicos y sociojurídicos.\r\n\r\nAnaliza integralmente las soluciones jurídicas y sociojurídicas estableciendo relaciones claras entre los hechos, las normas aplicables, y las posibles soluciones.'),
(135, 199.00, 300.00, '4', 'Evalúa de manera crítica la pertinencia, eficacia, y consecuencias jurídicas de diferentes alternativas para la solución de conflictos o problemas jurídicos y sociojurídicos.\r\n\r\nPropone soluciones juridicas innovadoras y viables a partir del analisis integral de los hechos, las normas, y las fuentes del derecho, contribuyendo a la toma de decisiones acertadas.\r\n\r\nDiseña estrategias jurídicas que articulan correctamente los fundamentos del derecho con la realidad juridica y sociojurídica, aplicando los principios de la ética profesional.'),
(291, 0.00, 125.00, '1', 'Identifica teorías fundamentales del desarrollo infantil propuestas por diversos autores. \r\nInfiere estrategia pedagógicas en relación con la comprensión del conocimiento sobre el desarrollo de los niños y niñas.\r\nEnuncia principios pedagógicos relacionados con la formación integral del niño y la niña.\r\nExplica la relación entre las características del desarrollo infantil y los factores contextuales que influyen en los procesos de aprendizaje.\r\n\r\n'),
(291, 126.00, 156.00, '2', 'Planea actividades pedagógicas utilizando principios y teorías del desarrollo infantil.\r\nSelecciona estrategias pedagógicas de aula que integren aspectos del desarrollo cognitivo, emocional y social de los estudiantes.'),
(291, 157.00, 198.00, '3', 'Analiza la coherencia entre una estrategia pedagógica implementada y teorías del desarrollo y aprendizaje infantil.\r\nCompara distintas teorías sobre el desarrollo y el aprendizaje en términos de su contribución a la formación integral de los niños y niñas.'),
(291, 199.00, 300.00, '4', 'Evalúa la efectividad de estrategias pedagógicas para promover el desarrolloy el aprendizaje de niños y niñas.\r\nJustifica la elección de determinadas estrategias pedagógicas a partir de evidencias sobre el desarrollo y aprendizaje infantil.\r\nElabora propuestas pedagógicas innovadoras basadas en teorías del desarrollo y aprendizaje infantil.\r\nAdapta un proyecto educativo que articule diversas dimensiones del desarrollo del niño con prácticas pedagógicas contextualizadas.'),
(292, 0.00, 125.00, '1', 'Identifica los principales componentes de un programa o proyecto educativo para la primera infancia en contextos diversos.\r\nDescribe las características del desarrollo infantil que fundamentan programas y proyectos educativos en entornos familiares, comunitarios y hospitalarios.\r\nExplica la relación entre las necesidades del contexto y el diseño de proyectos y programas educativos para la primera infancia.\r\nInfiere programas y royectos pedagógico a partir del análisisis de los contextos familiares, comunitarios y hospitalarios en donde se desenvuelve el niño.  '),
(292, 126.00, 156.00, '2', 'Emplea estrategias pedagógicas pertinentes en programas y proyectos eduactivos según  la población infantil y el tipo de contexto (comunitarios, familiares, hospitalarios y otros).\r\nElabora una propuesta educativa ajustada a un contexto familiar, comunitario o hospitalario  específico.'),
(292, 157.00, 198.00, '3', 'Diferencia las necesidades educativas de la primera infancia según el tipo de contexto en el que se desarrolla un proyecto o programa.\r\nEstructura un programa o proyecto educativo en coherencia con el entorno donde se ubica el niño (Familiar, hospitalario, comunitario u otros)'),
(292, 199.00, 300.00, '4', 'Justifica la selección de estrategias pedagógicas utilizadas en un programa educativo para la primera infancia en diferentes contextos.\r\nValora la coherencia entre los componentes de un proyecto o programa educativo y las condiciones del contexto.\r\nDiseña un programa o proyecto educativo integral y contextualizado, dirigido a la atención de la primera infancia en uno o más escenarios específicos (comunitario, familiar, hospitalario u otros).\r\nConstruye una guía metodológica para la elaboración de programas y proyectos educativos que atiendan diferentes tipos de contextos.'),
(293, 0.00, 125.00, '1', 'Reconoce las fuentes oficiales de información sobre políticas de infancia a nivel nacional e internacional.\r\nIdentifica los problemas más frecuentes que afectan a la población infantil en lo local, regionales, nacional e internacional.\r\nExplica la relación entre los problemas sociales identificados y las necesidades educativas de la población infantil.\r\nInterpreta datos y descripciones de problemáticas que vive la infancia en los contextos locales, nacionales e internacionales para proponer proyectos de intervenciaón interdisciplinaria.'),
(293, 126.00, 156.00, '2', 'Diseña instrumentos para el diagnóstico de necesidades en poblaciones infantiles de una comunidad específica que sirvan de información para el diseño de proyectos interdisciplinarios.\r\nAplica herramientas básicas de diagnóstico participativo o de recolección de información para caracterizar una problemática infantil en un contexto específico.'),
(293, 157.00, 198.00, '3', 'Descompone un problema educativo de la primera infancia en sus dimensiones pedagógicas, sociales y políticas para ser abordado interdisciplinariamente.\r\nExamina de forma crítica una situación o necesidad que afecta a la infancia, considerando variables sociales, educativas y culturales y su relación con el contexto local o global.\r\n\r\n'),
(293, 199.00, 300.00, '4', 'Valora la pertinencia y viabilidad de una propuesta de intervención interdisciplinar con base en criterios técnicos, éticos y contextuales.\r\nJustifica la elección de un enfoque interdisciplinar para abordar una problemática identificada en el contexto nacional o internacional.\r\nElabora un proyecto de investigación e intervención educativa que integre saberes de diversas disciplinas para atender una problemática infantil específica.\r\nFormula una propuesta innovadora de intervención educativa con enfoque territorial y participación comunitaria.'),
(294, 0.00, 125.00, '1', 'Describe las principales políticas públicas educativas vigentes en Colombia (y otros contextos) relacionadas con la atención a la primera infancia\r\nIdentifica los organismos e instituciones responsables de formular políticas para la atención integral de la infancia..\r\nExplica los objetivos de una política pública educativa orientada a la atención infantil.\r\nInfiere estrategias pedagógicas de atención a la primera infancia partiendo de la política pública. '),
(294, 126.00, 156.00, '2', 'Usa los lineamientos de las políticas públicas en situaciones educativas reales en contextos comunitarios, escolares o institucionales.\r\nUtiliza criterios normativos para orientar propuestas pedagógicas coherentes con las políticas públicas vigentes.'),
(294, 157.00, 198.00, '3', 'Analiza las implicaciones sociales, pedagógicas y culturales de una política pública específica sobre la atención a la primera infancia en un contexto determinado.\r\nExamina la coherencia entre las políticas públicas y las necesidades reales de la infancia en diferentes contextos.'),
(294, 199.00, 300.00, '4', 'Valora el grado de implementación de una política pública educativa en un entorno territorial determinado.\r\nJustifica su postura frente a la pertinencia de una política pública en relación con los derechos de la infancia.\r\nPlantea propuestas de mejora o ajustes a políticas públicas existentes, integrando criterios pedagógicos, éticos y contextuales.\r\nFormula recomendaciones para la elaboración de políticas públicas inclusivas y pertinentes para la infancia.'),
(181, 0.00, 125.00, '1', 'Identifica las características fundamentales del garantismo penal según Ferrajoli.\r\n\r\nEnumera los derechos fundamentales laborales consagrados en la Constitución.\r\n\r\nCita los principios rectores del derecho laboral colombiano.\r\n\r\nReconoce las garantías procesales esenciales en materia penal.\r\n\r\nMenciona las fuentes primarias del derecho administrativo colombiano.                           Explica la diferencia entre el derecho público y el derecho privado en contextos prácticos.\r\n\r\nInterpreta los principios del garantismo penal en casos reales o hipotéticos.\r\n\r\nResume los elementos esenciales del Estado Social de Derecho.\r\n\r\nRelaciona la ética profesional con actuaciones concretas del abogado.\r\n\r\nEjemplifica situaciones comunes de vulneración de derechos laborales.               '),
(181, 126.00, 156.00, '2', 'Redacta documentos judiciales aplicando normas y técnicas procesales adecuadas.\r\n\r\nAplica mecanismos constitucionales para la protección de derechos fundamentales.\r\n\r\nImplementa estrategias jurídicas de defensa basadas en el garantismo penal.\r\n\r\nUtiliza jurisprudencia relevante para sustentar argumentos legales en casos laborales.\r\n\r\nEmplea herramientas tecnológicas para gestionar procesos jurídicos de forma eficiente.'),
(181, 157.00, 198.00, '3', 'Analiza la coherencia entre normas sustanciales y procedimientos aplicables.\r\n\r\nDescompone casos complejos para identificar los elementos jurídicos relevantes.\r\n\r\nDistingue entre errores de fondo y vicios procesales en actuaciones judiciales.\r\n\r\nEvalúa precedentes jurisprudenciales para extraer su razón decisoria (ratio decidendi).\r\n\r\nIdentifica patrones sistemáticos de vulneración de derechos laborales.'),
(181, 199.00, 300.00, '4', 'Valora la eficacia real de las garantías procesales en situaciones jurídicas específicas.\r\n\r\nCritica con fundamentos decisiones judiciales que afecten derechos fundamentales.\r\n\r\nJuzga la pertinencia de estrategias procesales empleadas en litigios.\r\n\r\nEvalúa el impacto de reformas legales en la protección de derechos laborales.\r\n\r\nVerifica el cumplimiento de los estándares éticos en la actuación profesional.\r\nDiseña estrategias procesales innovadoras respetando el marco constitucional.\r\n\r\nConstruye argumentos jurídicos originales con base en doctrina y jurisprudencia.\r\n\r\nFormula teorías del caso coherentes e integrales en litigios penales o laborales.\r\n\r\nDesarrolla protocolos éticos de actuación en escenarios jurídicos reales o simulados.\r\n\r\nGenera soluciones alternativas para resolver conflictos laborales complejos.'),
(182, 0.00, 125.00, '1', 'Identifica los mecanismos alternativos de solución de conflictos (MASC) reconocidos por la legislación colombiana.\r\n\r\nEnumera las etapas de los procesos de mediación, conciliación y arbitraje.\r\n\r\nReconoce los elementos esenciales para la formación y validez de un contrato.\r\n\r\nMenciona las principales causales de nulidad y rescisión contractual.\r\n\r\nCita los requisitos procesales básicos para interponer una demanda civil.\r\nExplica las diferencias jurídicas y funcionales entre arbitraje, mediación y conciliación.\r\n\r\nInterpreta cláusulas contractuales que puedan generar ambigüedad o conflicto.\r\n\r\nRelaciona el decoro profesional con la conducta ética en la gestión de controversias.\r\n\r\nEjemplifica casos de responsabilidad civil extracontractual en conflictos privados.\r\n\r\nDistingue entre obligaciones civiles simples, solidarias y mancomunadas.\r\n'),
(182, 126.00, 156.00, '2', 'Redacta cláusulas contractuales que prevengan o canalicen controversias mediante MASC.\r\n\r\nAplica estrategias de conciliación preprocesal para la solución de conflictos patrimoniales.\r\n\r\nUtiliza técnicas de negociación colaborativa en simulaciones jurídicas.\r\n\r\nEjecuta audiencias de conciliación respetando los principios de imparcialidad y confidencialidad.\r\n\r\nEmplea jurisprudencia civil relevante para sustentar posiciones procesales en controversias privadas.'),
(182, 157.00, 198.00, '3', 'Analiza la viabilidad de los distintos mecanismos hetero y autocompositivos para resolver un caso específico.\r\n\r\nDescompone un conflicto complejo para identificar los intereses jurídicos, económicos y personales en juego.\r\n\r\nContrasta los efectos jurídicos de la conciliación y del laudo arbitral frente a una sentencia judicial.\r\n\r\nEvalúa la fuerza probatoria de distintos medios en procesos civiles.\r\n\r\nDiagnostica barreras jurídicas, personales o estructurales que obstaculizan la resolución efectiva del conflicto.'),
(182, 199.00, 300.00, '4', 'Valora la eficacia jurídica y práctica de los acuerdos conciliatorios en casos específicos.\r\n\r\nJuzga la pertinencia del uso del arbitraje o la vía judicial según el tipo de controversia.\r\n\r\nEvalúa el cumplimiento de estándares éticos y de decoro profesional en la gestión del conflicto.\r\n\r\nCritica constructivamente estrategias procesales adoptadas en controversias civiles.\r\n\r\nVerifica el cumplimiento y validez jurídica de acuerdos extrajudiciales.                     Diseña protocolos de actuación para gestionar controversias privadas a través de mecanismos autocompositivos.\r\n\r\nConstruye propuestas de solución jurídica innovadoras que integren aspectos técnicos y humanos del conflicto.\r\n\r\nElabora modelos contractuales que incluyan cláusulas preventivas y escalonadas de resolución de conflictos.\r\n\r\nGenera estrategias integrales de abordaje para conflictos familiares, comerciales o patrimoniales.\r\n\r\nFormula teorías del caso para litigios complejos que consideren vías jurisdiccionales y extrajudiciales.          '),
(183, 0.00, 125.00, '1', 'Identifica las etapas del método científico aplicadas a investigaciones jurídicas y sociojurídicas.\r\n\r\nEnumera los tipos de investigación jurídica (dogmática, empírica y comparada) con base en su finalidad.\r\n\r\nMenciona las fuentes primarias y secundarias utilizadas en investigación jurídica.\r\n\r\nReconoce las técnicas básicas de recolección de datos en contextos jurídicos y sociales.\r\n\r\nCita las normas académicas para la presentación de trabajos investigativos (APA, Chicago, etc.).\r\nExplica la diferencia entre investigación jurídica pura y aplicada, ilustrando su aplicación en problemas reales.\r\n\r\nInterpreta datos cuantitativos y cualitativos en investigaciones sociojurídicas.\r\n\r\nRelaciona problemas jurídicos con su correspondiente marco normativo y jurisprudencial.\r\n\r\nClasifica metodologías de investigación según el enfoque epistemológico adoptado.\r\n\r\nResume hallazgos relevantes de investigaciones previas para contextualizar su propio trabajo.\r\n'),
(183, 126.00, 156.00, '2', 'Redacta protocolos de investigación estructurados y coherentes con estándares académicos.\r\n\r\nAplica técnicas de entrevista y encuesta para recolectar información en contextos sociojurídicos.\r\n\r\nEmplea software especializado para el análisis de datos legales y sociales (SPSS, NVivo, etc.).\r\n\r\nDesarrolla búsquedas avanzadas en bases de datos jurídicas y científicas.\r\n\r\nUtiliza marcos normativos para analizar críticamente casos reales durante el proceso investigativo.\r\n'),
(183, 157.00, 198.00, '3', 'Analiza la coherencia entre problema de investigación, objetivos, hipótesis y metodología.\r\n\r\nDescompone problemas jurídicos complejos en variables que puedan ser operacionalizadas.\r\n\r\nContrasta posturas doctrinarias, jurisprudenciales o normativas sobre un fenómeno jurídico determinado.\r\n\r\nEvalúa la validez y confiabilidad de instrumentos utilizados en investigaciones sociojurídicas.\r\n\r\nDiagnostica vacíos teóricos o metodológicos en investigaciones jurídicas existentes.'),
(183, 199.00, 300.00, '4', 'Valora la pertinencia social y jurídica del problema abordado en la investigación.\r\n\r\nCritica con fundamentos metodológicos investigaciones existentes en el campo jurídico.\r\n\r\nJuzga la rigurosidad científica del diseño metodológico y del análisis de datos.\r\n\r\nEvalúa la factibilidad y el impacto potencial de las propuestas jurídicas planteadas.\r\n\r\nVerifica el cumplimiento de los principios éticos en todo el proceso investigativo.     Diseña proyectos de investigación jurídica o sociojurídica que respondan a problemáticas reales del contexto.\r\n\r\nConstruye propuestas de solución jurídicas innovadoras fundamentadas en evidencia empírica.\r\n\r\nElabora instrumentos de recolección de datos válidos, confiables y pertinentes.\r\n\r\nIntegra enfoques interdisciplinarios en la formulación de propuestas de intervención jurídica.\r\n\r\nProduce documentos académicos que contribuyan al conocimiento jurídico y a la transformación social.\r\n            '),
(184, 0.00, 125.00, '1', 'Identifica los 17 Objetivos de Desarrollo Sostenible (ODS) y sus finalidades principales.\r\n\r\nEnumera las poblaciones reconocidas constitucionalmente como sujetos de especial protección.\r\n\r\nCita los tratados internacionales de derechos humanos ratificados por Colombia.\r\n\r\nReconoce los principios fundamentales del Estado Social de Derecho.\r\n\r\nMenciona los mecanismos de protección nacional e internacional de los derechos humanos.                                                                                                                    Explica la interdependencia entre derechos civiles, políticos, económicos, sociales y culturales.\r\n\r\nInterpreta los indicadores de cumplimiento de los ODS en contextos locales.\r\n\r\nRelaciona el concepto de desarrollo sostenible con la justicia social y ambiental.\r\n\r\nDistingue entre situaciones de equidad e igualdad en el acceso efectivo a derechos.\r\n\r\nResume los fundamentos de la convivencia ciudadana en una democracia participativa.\r\n'),
(184, 126.00, 156.00, '2', 'Redacta acciones de tutela para proteger derechos fundamentales vulnerados.\r\n\r\nAplica enfoques diferenciales en el análisis y atención jurídica de casos de discriminación.\r\n\r\nEmplea mecanismos de participación ciudadana para promover el cumplimiento de derechos.\r\n\r\nDesarrolla estrategias educativas sobre derechos humanos en comunidades específicas.\r\n\r\nUtiliza lenguaje inclusivo y accesible en la elaboración de documentos jurídicos.'),
(184, 157.00, 198.00, '3', 'Analiza el impacto de políticas públicas en poblaciones históricamente excluidas.\r\n\r\nDistingue entre violaciones sistemáticas y hechos aislados de vulneración de derechos.\r\n\r\nExamina barreras de acceso a la justicia para comunidades marginadas.\r\n\r\nContrasta distintos modelos de responsabilidad social empresarial a nivel nacional e internacional.\r\n\r\nDiagnostica causas estructurales de conflictos sociales en entornos territoriales específicos.'),
(184, 199.00, 300.00, '4', 'Valora el cumplimiento de estándares internacionales en la protección de derechos humanos en Colombia.\r\n\r\nCritica políticas públicas desde un enfoque de derechos e inclusión.\r\n\r\nJuzga la pertinencia de medidas de acción afirmativa para poblaciones vulnerables.\r\n\r\nEvalúa el impacto social de proyectos de desarrollo económico con enfoque territorial.\r\n\r\nVerifica la efectividad de mecanismos de participación ciudadana en la toma de decisiones públicas.\r\nDiseña políticas públicas inclusivas fundamentadas en el enfoque de derechos humanos.\r\n\r\nConstruye estrategias de educación jurídica transformadora con enfoque interseccional.\r\n\r\nElabora protocolos de atención diferencial para poblaciones en situación de vulnerabilidad.\r\n\r\nDesarrolla programas de responsabilidad social jurídica con impacto en comunidades locales.\r\n\r\nPropone reformas normativas orientadas a fortalecer la protección y promoción de los derechos humanos.'),
(601, 0.00, 125.00, '1', '•	Identifica factores de riesgo en salud vinculados al entorno familiar, comunitario y laboral.\r\n•	Reconoce elementos clave de las políticas públicas en salud y su relación con los ODS.\r\n•	Describe determinantes biopsicosociales y culturales que influyen en el perfil de riesgo de las personas.'),
(601, 126.00, 156.00, '2', '•	Utiliza guías de valoración para aplicar instrumentos como escalas de riesgo, fichas de contexto social y mapas comunitarios.\r\n•	Implementa procesos básicos de planeación en intervenciones educativas orientadas a estilos de vida saludables.\r\n•	Aplica referentes normativos y técnicos (como resolución 3280 o planes territoriales) en escenarios simulados.'),
(601, 157.00, 198.00, '3', '•	Analiza información epidemiológica y datos comunitarios para identificar riesgos prioritarios de salud.\r\n•	Contrasta diversos modelos de atención y programas de prevención para determinar su aplicabilidad local.\r\n•	Examina barreras sociales, políticas y culturales que limitan el impacto de intervenciones planificadas.'),
(601, 199.00, 300.00, '4', '•	Diseña planes de intervención comunitaria o institucional que integran análisis de riesgos y políticas públicas.\r\n•	Formula estrategias de promoción y prevención alineadas con los ODS y normas vigentes.\r\n•	Evalúa la pertinencia de las acciones propuestas mediante criterios de sostenibilidad, equidad y eficacia.\r\n•	Justifica sus decisiones planificadas con evidencia científica, enfoque ético y lectura contextual del entorno. '),
(602, 0.00, 125.00, '1', '•	Identifica los principios del cuidado humanizado en escenarios clínicos y comunitarios.\r\n•	Reconoce normativas vigentes que rigen el ejercicio profesional del cuidado (ej. Resolución 3280, guías de práctica clínica).\r\n•	Describe avances tecnológicos relevantes para el monitoreo, valoración y comunicación en el contexto asistencial.'),
(602, 126.00, 156.00, '2', '•	Aplica protocolos institucionales de cuidado integral a través de simulaciones y escenarios reales.\r\n•	Utiliza instrumentos tecnológicos como historiales clínicos electrónicos, dispositivos de monitoreo o apps de autocuidado.\r\n•	Implementa estrategias comunicativas empáticas, adaptadas a las condiciones físicas, emocionales y culturales del paciente.'),
(602, 157.00, 198.00, '3', '•	Analiza necesidades de cuidado de pacientes con alteraciones de salud considerando sus dimensiones biopsicosociales.\r\n•	Contrasta diferentes tipos de evidencia (revisiones sistemáticas, protocolos, consensos) para seleccionar la más pertinente en la atención.\r\n•	Examina la integración de tecnología en el proceso de cuidado en función de la seguridad y humanización.'),
(602, 199.00, 300.00, '4', '•	Diseña planes de cuidado integrales y humanizados sustentados en evidencia y normativas vigentes.\r\n•	Evalúa el impacto de intervenciones utilizando indicadores clínicos, tecnológicos y percepciones del paciente.\r\n•	Propone mejoras en el proceso de atención para fortalecer la humanización y seguridad del paciente.\r\n•	Formula criterios éticos y técnicos para la toma de decisiones ante dilemas clínicos complejos. '),
(603, 0.00, 125.00, '1', '•	Identifica las fases del proceso administrativo en salud: planeación, organización, control y evaluación.\r\n•	Reconoce los marcos legales y normativos que regulan la gestión del cuidado (ej. Resolución 3280, normas territoriales).\r\n•	Describe el rol del profesional de Enfermería en equipos multidisciplinarios dentro de la gestión del cuidado.');
INSERT INTO `desempeno_indicadores` (`nivelID`, `puntaje_min`, `puntaje_max`, `nivel`, `indicadores`) VALUES
(603, 126.00, 156.00, '2', '•	Aplica herramientas de planeación estratégica en casos simulados y escenarios reales (cronogramas, matriz FODA, mapa de actores).\r\n•	Coordina acciones de atención integral en jornadas comunitarias o ejercicios de simulación clínica.\r\n•	Utiliza instrumentos de gestión (checklists, matrices de seguimiento, indicadores) para controlar procesos asistenciales.'),
(603, 157.00, 198.00, '3', '•	Analiza indicadores de salud y desempeño para ajustar las fases del cuidado gestionado.\r\n•	Evalúa las fortalezas, debilidades y oportunidades de proyectos comunitarios a partir de criterios de calidad.\r\n•	Identifica variables contextuales que afectan la implementación de procesos de atención integral (culturales, sociales, institucionales).'),
(603, 199.00, 300.00, '4', '•	Diseña propuestas de gestión integral del cuidado alineadas con políticas públicas, normativas institucionales y necesidades de la comunidad.\r\n•	Formula planes de acción basados en evidencia y datos epidemiológicos para coordinar servicios de atención.\r\n•	Evalúa el impacto de procesos gestionados mediante indicadores de efectividad, satisfacción y cobertura.\r\n•	Propone soluciones innovadoras que optimizan el ciclo de gestión, con enfoque ético, seguro y participativo. '),
(604, 0.00, 125.00, '1', '•	Identifica conceptos clave que conforman el cuidado interdisciplinario: sociología, nutrición, psicología, trabajo social, salud ambiental.\r\n•	Reconoce el papel y las funciones de cada disciplina dentro de un equipo de salud comunitaria.\r\n•	Describe el concepto de red conceptual y su importancia en el abordaje integral del cuidado.'),
(604, 126.00, 156.00, '2', '•	Aplica los marcos conceptuales de distintas disciplinas en el análisis de un problema comunitario de salud.\r\n•	Utiliza herramientas integradas (como mapas de actores, matrices de problematización o fichas socioculturales) para diseñar propuestas con enfoque interdisciplinario.\r\n•	Participa activamente en ejercicios colaborativos donde se simula la articulación de saberes para la construcción de soluciones comunitarias.'),
(604, 157.00, 198.00, '3', '•	Analiza las relaciones entre los factores biopsicosociales y culturales que inciden en un problema de salud comunitaria.\r\n•	Evalúa cómo la articulación interdisciplinaria influye en la calidad del cuidado brindado.\r\n•	Descompone casos clínicos y comunitarios complejos identificando el aporte de cada disciplina en la atención.'),
(604, 199.00, 300.00, '4', '•	Diseña propuestas integrales de intervención comunitaria articulando saberes interdisciplinarios con justificación conceptual.\r\n•	Formula soluciones innovadoras que vinculan el enfoque enfermero con aportes complementarios de otras áreas.\r\n•	Evalúa el impacto de las acciones interdisciplinarias mediante criterios de equidad, pertinencia sociocultural y sostenibilidad.\r\n•	Propone y argumenta el uso de redes conceptuales colaborativas en proyectos de salud comunitaria institucionales. '),
(605, 0.00, 125.00, '1', '•	Identifica conceptos clave relacionados con modelos de atención, autocuidado, empoderamiento y estrategias de promoción y prevención.\r\n•	Reconoce tipos de investigación aplicados en Enfermería que aportan evidencia sobre modelos innovadores.\r\n•	Describe elementos esenciales de estrategias educativas en salud basadas en evidencia.'),
(605, 126.00, 156.00, '2', '•	Aplica hallazgos de estudios actuales sobre modelos de atención para orientar prácticas clínicas o comunitarias.\r\n•	Diseña materiales educativos (folletos, guías, infografías) que promuevan el autocuidado en pacientes con base en evidencia científica.\r\n•	Utiliza tecnologías digitales y redes sociales para difundir estrategias preventivas derivadas de investigaciones.'),
(605, 157.00, 198.00, '3', '•	Analiza estudios recientes sobre nuevos modelos de atención para identificar resultados aplicables a poblaciones específicas.\r\n•	Evalúa la coherencia entre prácticas tradicionales y evidencias emergentes sobre empoderamiento y autocuidado.\r\n•	Identifica barreras culturales, sociales o institucionales que limitan la implementación de estrategias innovadoras.'),
(605, 199.00, 300.00, '4', '•	Diseña propuestas integradas de promoción y prevención sustentadas en evidencia científica y adaptadas a contextos reales.\r\n•	Formula planes de intervención que empoderen al paciente desde una perspectiva ética, humanizada y basada en resultados de investigación.\r\n•	Justifica la elección de un modelo de atención innovador a través del análisis crítico de estudios comparativos.\r\n•	Evalúa el impacto de las estrategias implementadas mediante indicadores de autocuidado, adherencia y satisfacción del paciente. '),
(606, 0.00, 125.00, '1', '•	Identifica los determinantes de la salud física, mental y emocional en diferentes etapas de la vida.\r\n•	Reconoce los principales factores de riesgo y protección en el contexto territorial.\r\n•	Describe principios básicos de promoción de la salud y prevención de la enfermedad.'),
(606, 126.00, 156.00, '2', '•	Aplica guías educativas y contenidos validados para fomentar hábitos saludables en población infantil, adulta y mayor.\r\n•	Implementa sesiones educativas comunitarias utilizando recursos visuales, tecnológicos o narrativos.\r\n•	Coordina actividades grupales en instituciones o barrios que promuevan la salud física, mental y emocional.'),
(606, 157.00, 198.00, '3', '•	Analiza indicadores epidemiológicos locales para detectar necesidades y priorizar acciones preventivas.\r\n•	Evalúa los patrones de salud y enfermedad en comunidades vulnerables para orientar sus intervenciones.\r\n•	Identifica barreras socioculturales o económicas que impiden la adopción de prácticas saludables.'),
(606, 199.00, 300.00, '4', '•	Diseña proyectos integrales de educación en salud que articulan promoción, prevención y participación comunitaria.\r\n•	Propone estrategias creativas que respondan a las necesidades territoriales con enfoque intercultural y de ciclo vital.\r\n•	Evalúa el impacto de las intervenciones realizadas mediante indicadores de cambio conductual, percepción y bienestar.\r\n•	Justifica la implementación de programas de salud comunitaria desde marcos éticos, normativos y contextuales. '),
(201, 0.00, 125.00, '1', 'o Identifica los diferentes procesos administrativos, financieros, humanos, tecnológicos y organizacionales que operan dentro de una organización.\r\no Reconoce las herramientas digitales clave utilizadas en la gestión de organizaciones y los enfoques sistémicos relevantes.\r\no Comprende la relación entre la gestión eficiente de procesos y la generación de valor, la calidad, la sostenibilidad y la competitividad.\r\no Explica la importancia de la gestión administrativa en las organizaciones.'),
(201, 126.00, 156.00, '2', 'o Utiliza herramientas digitales para gestionar y optimizar procesos administrativos específicos, como la gestión de recursos humanos o la gestión de calidad.\r\no Aplica un enfoque sistémico para identificar y resolver problemas relacionados con la integración de procesos organizacionales.\r\no Implementa estrategias para mejorar la calidad, la sostenibilidad o la competitividad en un entorno organizacional simulado.\r\no Demuestra el uso de herramientas digitales para el análisis de datos y la toma de decisiones en la gestión de procesos.'),
(201, 157.00, 198.00, '3', 'o Examina críticamente la estructura y el funcionamiento de diferentes organizaciones, identificando sus fortalezas y debilidades en términos de gestión de procesos.\r\no Analiza las causas y consecuencias de los problemas que surgen en la gestión de procesos administrativos, humanos, de calidad, tecnológicos y organizacionales.\r\no Compara y contrasta diferentes herramientas digitales y enfoques sistémicos, evaluando su idoneidad para diferentes contextos organizacionales.\r\no Evalúa el impacto de la gestión administrativa en las organizaciones.'),
(201, 199.00, 300.00, '4', 'o Evalúa la eficacia de las estrategias implementadas para mejorar la calidad, la sostenibilidad y la competitividad de una organización.\r\no Juzga las prácticas de gestión de procesos existentes, proponiendo mejoras basadas en criterios de eficiencia, sostenibilidad y responsabilidad social.\r\no Desarrolla un plan estratégico integral para la gestión de una organización, que tenga en cuenta la generación de valor, la calidad, la sostenibilidad y la competitividad.\r\no Propone soluciones creativas para los desafíos que enfrentan las organizaciones en un entorno empresarial dinámico y competitivo.'),
(202, 0.00, 125.00, '1', 'o Identifica los diferentes estados financieros (Balance General, Estado de Resultados, Estado de Flujo de Efectivo, Estado de Cambios en el Patrimonio).\r\no Reconoce las principales normas contables nacionales e internacionales (NIC, NIIF) y las empresariales (Laboral, Comercial y Tributaria)\r\no Explica los principales indicadores económicos (PIB, inflación, desempleo).\r\no Comprende el impacto de las decisiones económicas y financieras empresariales en la sostenibilidad, la legalidad y la responsabilidad social.'),
(202, 126.00, 156.00, '2', 'o Resuelve problemas mediante la aplicación de conocimientos económicos y financieros, utilizando técnicas previamente adquiridas.\r\no Aplica las normas contables para la elaboración de estados financieros básicos.\r\no Calcula e interpreta ratios financieros para evaluar la situación económica y financiera de una empresa.\r\no Utiliza informes financieros sencillos para la toma de decisiones.'),
(202, 157.00, 198.00, '3', 'o Analiza los estados financieros de una empresa para identificar sus fortalezas y debilidades financieras.\r\no Compara el desempeño financiero de diferentes empresas del mismo sector.\r\no Evalúa el impacto de los factores macroeconómicos en la situación financiera de una empresa.\r\no Identifica posibles riesgos financieros y propone medidas para mitigarlos.'),
(202, 199.00, 300.00, '4', 'o Crea diagnósticos financieros de la empresa y propone alternativas estratégicas para elevar la rentabilidad de la organización. \r\no Genera información económica y financiera con miras a generar estrategias sostenibles y sustentables de los diferentes modelos de empresas o negocios.\r\no Analiza la información contable y financiera de la empresa para la toma de decisiones gerenciales.\r\no Toma decisiones de inversión, financiamiento y gestión de recursos financieros en la empresa.'),
(203, 126.00, 156.00, '2', 'o Aplica los principios de gestión operativa para mejorar los procesos productivos.\r\no Resuelve problemas sencillos relacionados con la logística y la cadena de suministro.\r\no Implementa estrategias de marketing en simulaciones de entornos empresariales.\r\no Utiliza técnicas de gestión para optimizar los recursos en un contexto empresarial dado.'),
(203, 157.00, 198.00, '3', 'o Analiza los datos de marketing para comprender el comportamiento del consumidor.\r\no Evalúa las diferentes opciones estratégicas para resolver los problemas empresariales.\r\no Compara y contrasta los procesos comerciales, logísticos y productivos en diferentes contextos.\r\no Reorganiza la información disponible para identificar patrones y tendencias en el mercado.'),
(203, 199.00, 300.00, '4', 'o Gestiona estrategias de marketing relevantes del contexto al momento de crear, innovar o mejorar un producto o servicio a través de investigación de mercados y comportamiento del consumidor para determinar decisiones pertinentes para la empresa.\r\no Evalúa métodos estadísticos y cuantitativos en los procesos productivos de la organización para sintetizar problemas operacionales con el apoyo de las herramientas tecnológicas.\r\no Propone estrategias logísticas para optimizar los procesos de gestión comercial y productiva en las empresas en el contexto nacional e internacional.\r\no Emplea las políticas comerciales colombianas para realizar operaciones mercantiles en el contexto internacional.'),
(203, 0.00, 125.00, '1', 'o Identifica los conceptos clave de los procesos comerciales, logísticos y productivos.\r\no Interpreta las herramientas de marketing y gestión operativa.\r\no Aplica los principios de gestión operativa para mejorar los procesos productivos.\r\no Resuelve problemas sencillos relacionados con la logística y la cadena de suministro.\r\no Implementa estrategias de marketing en simulaciones de entornos empresariales.'),
(204, 0.00, 125.00, '1', 'o Conoce los conceptos de eficiencia en el uso de recursos, tecnologías emergentes, análisis del entorno, trabajo colaborativo, mejoramiento continuo y desarrollo sostenible.\r\no Reconoce ejemplos de proyectos administrativos o empresariales exitosos que incorporan elementos claves deescenarios estrategicos y prospectivos.\r\no Identifica las etapas del proceso de formulación e implementación de proyectos.\r\no Conoce las diferentes tecnologías emergentes relevantes para la administración de empresas.'),
(204, 126.00, 156.00, '2', 'o Aplicar los principios de la gestión eficiente de recursos en la planificación de un proyecto.\r\no Utilizar herramientas de análisis del entorno (por ejemplo, análisis PESTEL, FODA) para identificar oportunidades y amenazas.\r\no Demostrar cómo las tecnologías emergentes pueden ser integradas en un proyecto administrativo o empresarial.\r\no Resolver problemas simulados relacionados con la implementación de proyectos sostenibles.'),
(204, 157.00, 198.00, '3', 'o Analiza críticamente diferentes escenarios estratégicos y prospectivos, identificando sus fortalezas y debilidades.\r\no Desglosa un proyecto administrativo o empresarial en sus componentes clave y evaluar su interdependencia.\r\no Identifica las causas y consecuencias de problemas que surgen durante la implementación de un proyecto.\r\no Evalua el impacto ambiental y social de un proyecto, proponiendo mejoras para aumentar su sostenibilidad.'),
(204, 199.00, 300.00, '4', 'o EvalÚa la viabilidad de un proyecto administrativo o empresarial, considerando factores económicos, sociales y ambientales.\r\no Justifica la selección de determinadas estrategias y tecnologías en función de criterios de eficiencia, sostenibilidad y competitividad.\r\no Diseña escenarios estratégicos y prospectivos originales que aborden desafíos específicos del entorno empresarial.\r\no Crea un modelo de negocio que sea a la vez rentable, socialmente responsable y ambientalmente sostenible.'),
(101, 0.00, 125.00, '1\r\n', 'Reconoce los fundamentos fisiopatológicos, signos clínicos y conceptos esenciales de enfermedades prevalentes en medicina interna.'),
(101, 126.00, 156.00, '2\r\n', 'Aplica el conocimiento médico para interpretar síntomas y seleccionar respuestas clínicas apropiadas ante escenarios comunes en medicina interna.'),
(101, 157.00, 198.00, '3\r\n', 'Analiza críticamente casos clínicos integrando información clínica, paraclínica y farmacológica para establecer hipótesis diagnósticas fundamentadas.'),
(101, 199.00, 300.00, '4\r\n', 'Evalúa alternativas terapéuticas y toma decisiones clínicas complejas con base en principios éticos, evidencia científica y razonamiento médico integral.'),
(102, 0.00, 125.00, '1\r\n', 'Reconoce conceptos fundamentales relacionados con la estructura familiar, funciones, tipologías, modelos de atención y herramientas básicas utilizadas en medicina familiar.'),
(102, 126.00, 156.00, '2\r\n', 'Aplica herramientas e instrumentos propios de la medicina familiar para identificar dinámicas intrafamiliares, etapas del ciclo vital y situaciones de riesgo que afectan la salud de sus miembros.'),
(102, 157.00, 198.00, '3\r\n', 'Analiza integralmente la funcionalidad familiar, los determinantes sociales y los procesos de adaptación de las familias ante eventos de salud-enfermedad, para orientar decisiones clínicas.'),
(102, 199.00, 300.00, '4\r\n', 'Evalúa críticamente casos clínicos complejos desde el enfoque biopsicosocial, proponiendo intervenciones fundamentadas en el entorno familiar, comunitario y en el respeto por la diversidad.'),
(103, 0.00, 125.00, '1\r\n', 'Reconoce conceptos fundamentales de salud pública, indicadores epidemiológicos, niveles de atención, estrategias de promoción, prevención y atención integral, así como principios del modelo de Atención Primaria en Salud.'),
(103, 126.00, 156.00, '2\r\n', 'Aplica herramientas operativas y modelos de intervención para el diseño, ejecución y seguimiento de programas de salud pública con enfoque diferencial y comunitario.'),
(103, 157.00, 198.00, '3\r\n', 'Analiza críticamente problemáticas de salud colectiva, determinantes sociales, contextos locales y datos epidemiológicos para formular propuestas de intervención integradas y contextualizadas.'),
(103, 199.00, 300.00, '4\r\n', 'Evalúa el impacto de intervenciones en salud pública y propone acciones innovadoras que articulen participación comunitaria, políticas públicas, gestión sanitaria y mejora continua en salud.'),
(104, 0.00, 125.00, '1\r\n', 'Reconoce mecanismos de acción, efectos adversos, usos terapéuticos y contraindicaciones de medicamentos empleados en medicina interna, así como fundamentos fisiopatológicos de enfermedades comunes.'),
(104, 126.00, 156.00, '2\r\n', 'Aplica criterios clínico-farmacológicos para seleccionar intervenciones terapéuticas adecuadas frente a diversos cuadros clínicos, considerando antecedentes, comorbilidades y parámetros diagnósticos.'),
(104, 157.00, 198.00, '3\r\n', 'Analiza escenarios clínicos complejos evaluando la interacción entre diagnósticos, tratamientos y evolución del paciente para establecer decisiones razonadas y ajustadas a la evidencia.'),
(104, 199.00, 300.00, '4\r\n', 'Evalúa críticamente casos clínicos integrando farmacología, especialidades médicas y juicio clínico para tomar decisiones terapéuticas seguras, eficaces y centradas en el paciente.'),
(105, 0.00, 125.00, '1\r\n', 'Reconoce los conceptos, niveles y estrategias fundamentales de prevención en salud, así como los determinantes sociales que influyen en el proceso salud-enfermedad.'),
(105, 126.00, 156.00, '2\r\n', 'Aplica intervenciones preventivas según el ciclo vital y los niveles de atención, ajustadas a las características de grupos poblacionales específicos.'),
(105, 157.00, 198.00, '3\r\n', 'Analiza críticamente situaciones clínicas y contextos epidemiológicos para proponer medidas de prevención acordes a las necesidades individuales y colectivas.'),
(105, 199.00, 300.00, '4\r\n', 'Evalúa la pertinencia y efectividad de estrategias preventivas integrales, formulando propuestas que promuevan el bienestar, el autocuidado y la salud pública.'),
(106, 0.00, 125.00, '1\r\n', 'Reconoce principios fundamentales de la bioética, niveles de prevención, legislación en salud y elementos básicos de la entrevista clínica y la comunicación médico-paciente.'),
(106, 126.00, 156.00, '2\r\n', 'Aplica criterios éticos, jurídicos y comunicacionales para la toma de decisiones clínicas respetando la autonomía del paciente, el consentimiento informado y la confidencialidad.'),
(106, 157.00, 198.00, '3\r\n', 'Analiza situaciones clínicas complejas con implicaciones éticas y legales, integrando normativa vigente, principios bioéticos y juicio clínico para sustentar sus decisiones.'),
(106, 199.00, 300.00, '4\r\n', 'Evalúa críticamente escenarios asistenciales y propone estrategias éticas, legales y comunicativas que promueven el respeto a la dignidad humana, la seguridad del paciente y la justicia en salud.'),
(107, 0.00, 125.00, '1', 'Reconoce principios básicos de comunicación médico-paciente, ética clínica, normativas de investigación y características fisiológicas y clínicas del paciente geriátrico.'),
(107, 126.00, 156.00, '2\r\n', 'Aplica principios éticos, epidemiológicos y clínicos en situaciones prácticas relacionadas con la atención al adulto mayor, promoción de la salud y manejo de condiciones crónicas prevalentes.'),
(107, 157.00, 198.00, '3\r\n', 'Analiza integralmente casos clínicos y situaciones gerontológicas evaluando factores de riesgo, decisiones médicas complejas y herramientas de valoración funcional para la toma de decisiones fundamentadas.'),
(107, 199.00, 300.00, '4\r\n', 'Evalúa escenarios clínicos y éticos en pacientes adultos mayores, proponiendo intervenciones centradas en la dignidad, funcionalidad y calidad de vida, respetando principios bioéticos y normativos.'),
(108, 0.00, 125.00, '1\r\n', 'Reconoce los componentes fundamentales de un artículo científico, conceptos básicos de estadística y herramientas esenciales para la búsqueda y comprensión de literatura biomédica.'),
(108, 126.00, 156.00, '2\r\n', 'Aplica criterios metodológicos, herramientas de análisis de información y bases de datos científicas para desarrollar estrategias de búsqueda, selección y comprensión de literatura relevante.'),
(108, 157.00, 198.00, '3\r\n', 'Analiza críticamente textos científicos, metodologías de investigación, resultados estadísticos y evidencia empírica para sustentar argumentos académicos.'),
(108, 199.00, 300.00, '4\r\n', 'Evalúa la calidad, relevancia y aplicabilidad de estudios científicos, proponiendo preguntas de investigación y estrategias metodológicas coherentes con el problema planteado.'),
(109, 0.00, 125.00, '1\r\n ', 'Reconoce los conceptos básicos, normativas legales, definiciones y funciones esenciales de la salud ocupacional, así como los principios de ergonomía, prevención de riesgos laborales y protección del trabajador.'),
(109, 126.00, 156.00, '2\r\n', 'Aplica herramientas, normativas y medidas de prevención para identificar factores de riesgo físicos, químicos, biológicos y psicosociales, y proponer acciones inmediatas en contextos reales de trabajo.'),
(109, 157.00, 198.00, '3\r\n', 'Analiza críticamente eventos laborales y condiciones del entorno, considerando la evidencia normativa, epidemiológica y técnica para priorizar intervenciones de salud ocupacional.'),
(109, 199.00, 300.00, '4\r\n', 'Evalúa programas de intervención en salud ocupacional y propone estrategias innovadoras que integran acciones correctivas, participación del personal y mejora continua en ambientes laborales.'),
(281, 0.00, 125.00, '1', '- Define conceptos clave como inclusión, diversidad y equidad educativa\r\n  - Identifica las características de problemas educativos y sociales en torno a las diversidades\r\n  - Describe la relación entre las teorias de la diversidad y los enfoques diferenciales desde lo cultural, social y ético'),
(281, 126.00, 156.00, '2', '- Utiliza conceptos de la diversidad e inclusión para la solución de problemas en los contextos educativos\r\n  - Implementa técnicas que identifican situaciones y problematicas en contextos educativos'),
(281, 157.00, 198.00, '3', '- Compara críticamente modelos teóricos de la educación y diversidad rescpeto a las situaciones problema del contexto educativo \r\n  - Evalúa problemáticas en el contexto educativo y propone métodos para minimizar obstaculos hacia la inclusión educativa\r\n  - Interpreta resultados cualitativos para sustentar decisiones desde una perspectiva cultural, social, ética, estética y normativa\r\n'),
(281, 199.00, 300.00, '4', '- Diseña métodos personalizados de recolección y análisis de datos adaptados a fenómenos de la inclusión y diversidad desde una perspectiva social, cultural y ética\r\n  - Propone soluciones basadas en en análisis de las diversidad y la inclusión aplicando teorías que las sustentan\r\n  - Genera informes técnicos sobre inclusión y diversidad que vinculan resultados analíticos con la toma de decisiones estratégicas desde una perspectiva social, cultural y ética'),
(282, 0.00, 125.00, '1', '- Define conceptos normativos sobre inclusión, diversidad y equidad educativa\r\n  - Identifica las características de problemas educativos y sociales desde las politicas y normativa de la educación, inclusión y diversidad\r\n  - Describe la relación entre las normativas sobre diversidad y la aplicabilidad de las políticas educativas'),
(282, 126.00, 156.00, '2', '- Utiliza conceptos de la diversidad e inclusión para la solución de problemas en los contextos educativos\r\n- Implementa técnicas que identifican situaciones y problematicas en contextos educativos                      - Aplica las normativas sobre diversidad ylas políticas educativas a estudios de casos sobre diversidad e inclusión'),
(282, 157.00, 198.00, '3', '- Compara críticamente las póliticas y normativas en educación y diversidad rescpeto a las situaciones problema del contexto educativo \r\n  - Evalúa problemáticas en el contexto educativo y propone procesos normartivos para minimizar obstaculos hacia la inclusión educativa\r\n\r\n'),
(282, 199.00, 300.00, '4', '- Diseña recomendaciones a politicas que traten los fenómenos de la inclusión y diversidad desde una perspectiva social, cultural y ética\r\n  - Propone soluciones normativas basadas en en análisis de las diversidad y la inclusión aplicando teorías que las sustentan\r\n  - Genera informes técnicos sobre politicas educativas relacionadas con la inclusión y diversidad que vinculan resultados analíticos con la toma de decisiones estratégicas'),
(283, 0.00, 125.00, '1', '- Define conceptos como intervención socioeducativa en la nclusión, diversidad y equidad educativa\r\n  - Identifica antecedentes y lecciones aprendidas de intervenciones socioeducativas y tendencias innovadoras de la educación\r\n'),
(283, 126.00, 156.00, '2', '- Utiliza conceptos de la diversidad e inclusión para la solución de problemas en los contextos educativos\r\n- Implementa técnicas que identifican situaciones y problematicas en contextos educativos                      - Aplica las teorias sobre la intervención socioeducativa sobre diversidad y la inclusión a estudios de casos sobre diversidad e inclusión'),
(283, 157.00, 198.00, '3', '- Compara críticamente las póliticas y normativas en educación y diversidad rescpeto a las situaciones problema del contexto educativo \r\n- Evalúa problemáticas en el contexto educativo y propone procesos normartivos para minimizar obstaculos hacia la inclusión educativa\r\n- Interpreta resultados cualitativos y cuantitativos desde investigaciones de la linea en educación y diversidad '),
(283, 199.00, 300.00, '4', '- Diseña propuesta de intervención socioeducativa que traten los fenómenos de la inclusión y diversidad desde una perspectiva social, cultural y ética\r\n- Propone soluciones innovadoras basadas en en análisis de las diversidad y la inclusión educativa\r\n- Genera informes técnicos sobre intervenciones socioeducativas relacionadas con la inclusión y diversidad que vinculan resultados analíticos con la toma de decisiones estratégicas'),
(284, 0.00, 125.00, '1', '- Define conceptos de metodología de la investigación en torno a la inclusión, diversidad educativa\r\n- Identifica antecedentes investigativos sobre educación, diversidad e inclusión\r\n- Describe la relación entre las teorias y variables cualitativas y cuantitativas sobre inclusión educativa y diversidad'),
(284, 126.00, 156.00, '2', '- Implementa técnicas de recolección de datos cualitativos y cuantitativos en torno a la educación inclusiva                                                                                                                            - Aplica las teorias sobre la intervención socioeducativa sobre diversidad y la inclusión en investigaciones cientificas y académicas\r\n- Interpreta resultados cualitativos y cuantitativos desde investigaciones de la linea en educación y diversidad \"\"'),
(284, 157.00, 198.00, '3', '- Compara críticamente las modelos metodológicvos desde la inter y multidisciplina para abordar situaciones problema de la inclusión educativa y las diversidades \r\n- Evalúa cuali y cuantitativamente problemáticas en el contexto educativo desde la inclusión educativa\r\n\r\n'),
(284, 199.00, 300.00, '4', '- Diseña propuesta de investigación socioeducativa que traten los fenómenos de la inclusión y diversidad desde una perspectiva social, cultural y ética\r\n- Genera informes investigativos sobre problemáticas relacionadas con la inclusión y diversidad que vinculan resultados analíticos con la toma de decisiones estratégicas'),
(285, 0.00, 125.00, '1', '- Define conceptos de metodología de la investigación en torno a la inclusión, diversidad educativa\r\n- Identifica antecedentes investigativos sobre educación, diversidad e inclusión\r\n- Describe la relación entre las teorias y variables cualitativas y cuantitativas sobre inclusión educativa y diversidad'),
(285, 126.00, 156.00, '2', '- Implementa técnicas de recolección de datos cualitativos y cuantitativos en torno a la educación inclusiva                                                                                                                            - Aplica las teorias sobre la intervención socioeducativa sobre diversidad y la inclusión en investigaciones cientificas y académicas\r\n- Interpreta resultados cualitativos y cuantitativos desde investigaciones de la linea en educación y diversidad \"\"'),
(285, 157.00, 198.00, '3', '- Compara críticamente las modelos metodológicvos desde la inter y multidisciplina para abordar situaciones problema de la inclusión educativa y las diversidades \r\n- Evalúa cuali y cuantitativamente problemáticas en el contexto educativo desde la inclusión educativa\r\n\r\n'),
(285, 199.00, 300.00, '4', '- Diseña propuesta de investigación socioeducativa que traten los fenómenos de la inclusión y diversidad desde una perspectiva social, cultural y ética\r\n- Genera informes investigativos sobre problemáticas relacionadas con la inclusión y diversidad que vinculan resultados analíticos con la toma de decisiones estratégicas'),
(261, 0.00, 125.00, '1', '- Reconoce los enfoques históricos, epistemológicos y teóricos fundamentales que han estructurado el campo del Trabajo Social.\r\n- Describe eventos y corrientes teóricas que han marcado hitos en la evolución disciplinar del Trabajo Social.\r\n- Interpreta en sus propias palabras las principales relaciones entre teorías sociales y las prácticas profesionales del Trabajo Social.'),
(261, 126.00, 156.00, '2', '- Aplica conocimientos teóricos para explicar situaciones históricas relevantes del desarrollo del Trabajo Social en contextos nacionales o regionales.\r\n- Utiliza conceptos epistemológicos para sustentar intervenciones o decisiones dentro del campo profesional del Trabajo Social.\r\n- Emplea marcos teóricos clásicos del Trabajo Social en la comprensión de fenómenos sociales contemporáneos.'),
(261, 157.00, 198.00, '3', ' Analiza críticamente las influencias de distintas corrientes epistemológicas en los modelos de intervención del Trabajo Social.\r\n- Examina casos o contextos socioprofesionales identificando cómo se expresan las diferentes perspectivas históricas y teóricas de la profesión.\r\n- Reorganiza y contrasta marcos teóricos del Trabajo Social a partir de su coherencia, aplicabilidad y actualidad en diversos escenarios de intervención.'),
(261, 199.00, 300.00, '4', '- Evalúa la validez, vigencia y aplicabilidad de teorías y enfoques epistemológicos en la intervención social desde una mirada crítica y fundamentada.\r\n- Formula propuestas de mejora disciplinar o profesional sustentadas en una integración de enfoques históricos, epistemológicos y teóricos del Trabajo Social.\r\n- Diseña modelos analíticos o documentos técnicos que articulen las perspectivas del Trabajo Social con soluciones innovadoras a problemáticas sociales complejas.'),
(262, 0.00, 125.00, '1', '- Reconoce los principios básicos teóricos, metodológicos y éticos que sustentan la intervención social en sus distintos niveles.\r\n- Describe acciones y estrategias generales utilizadas en procesos de intervención con individuos, familias y comunidades.\r\n- Interpreta el papel del Trabajo Social en la intervención en distintos contextos sociales, a partir del análisis de situaciones guiadas..'),
(262, 126.00, 156.00, '2', '- Aplica técnicas básicas de intervención social en casos simulados o reales bajo supervisión, considerando los niveles individual, familiar o comunitario.\r\n- Utiliza procedimientos metodológicos y herramientas de Trabajo Social de forma pertinente para abordar problemáticas sociales identificadas.\r\n- Desarrolla acciones de intervención social articulando teoría y práctica con enfoque ético en contextos institucionales.'),
(262, 157.00, 198.00, '3', '- Analiza situaciones sociales complejas identificando niveles de intervención adecuados (individual, grupal, comunitario u organizacional) y los enfoques pertinentes.\r\n- Examina críticamente los efectos de las estrategias de intervención implementadas, reconociendo tensiones éticas, técnicas y metodológicas.\r\n- Reorganiza planes o proyectos de intervención integrando componentes teóricos, instrumentales y contextuales para mejorar su efectividad.'),
(262, 199.00, 300.00, '4', ' Evalúa procesos de intervención social considerando criterios técnicos, éticos y metodológicos en función de su impacto en los sujetos y territorios.\r\n- Diseña estrategias de intervención social innovadoras y contextualizadas, integrando diferentes niveles de análisis y participación.\r\n- Formula modelos de intervención complejos y adaptativos sustentados en marcos teóricos, experiencias previas y diagnóstico participativo.'),
(263, 0.00, 125.00, '1', '- Reconoce los principios básicos de la planeación social y los fundamentos de la justicia social, equidad y derechos humanos en el contexto del Trabajo Social.\r\n- Describe las fases y componentes fundamentales de un plan, programa o proyecto social con enfoque participativo.\r\n- Interpreta la función social del Trabajo Social en la formulación de propuestas dirigidas a poblaciones en condición de vulnerabilidad.'),
(263, 126.00, 156.00, '2', '- Aplica metodologías participativas básicas para la formulación e implementación de programas sociales orientados a poblaciones en riesgo o exclusión.\r\n- Desarrolla acciones estratégicas dentro de proyectos sociales incorporando criterios de justicia social y defensa de derechos.\r\n- Implementa instrumentos de diagnóstico, planificación o evaluación en experiencias comunitarias o institucionales con orientación a la transformación social.'),
(263, 157.00, 198.00, '3', '- Analiza problemáticas sociales complejas con enfoque de derechos, reconociendo necesidades y capacidades de los sujetos para diseñar propuestas pertinentes.\r\n- Examina críticamente planes y programas existentes, identificando vacíos éticos, técnicos o de participación comunitaria.\r\n- Reorganiza procesos de intervención social considerando principios de corresponsabilidad, inclusión social y sostenibilidad.'),
(263, 199.00, 300.00, '4', '- Evalúa la eficacia, pertinencia y equidad de planes, programas y proyectos en función de su impacto en la garantía de derechos de poblaciones vulnerables.\r\n- Diseña propuestas innovadoras de intervención social con enfoque participativo, sustentadas en marcos de justicia social y defensa de derechos.\r\n- Formula modelos de gestión social que integran actores institucionales y comunitarios para la transformación estructural de situaciones de desigualdad.'),
(264, 0.00, 125.00, '1', '- Reconoce los componentes fundamentales del proceso investigativo en el campo de las ciencias sociales (problema, objetivos, metodología, etc.).\r\n- Describe distintas problemáticas sociales propias del contexto local, regional o nacional que pueden ser objeto de investigación.\r\n- Interpreta los elementos básicos de un proyecto de investigación social a partir de guías o ejemplos estructurados.'),
(264, 126.00, 156.00, '2', '- Aplica estructuras metodológicas básicas en la formulación de propuestas de investigación relacionadas con problemáticas sociales específicas.\r\n- Diseña preguntas de investigación pertinentes y objetivos coherentes con las realidades sociales del entorno.\r\n- Emplea instrumentos de recolección de información apropiados al enfoque y problema investigativo definido.'),
(264, 157.00, 198.00, '3', '- Analiza problemáticas sociales desde una perspectiva crítica, identificando su complejidad y relevancia para el contexto investigativo.\r\n- Examina la coherencia interna entre problema, objetivos, marco teórico y metodología de proyectos de investigación en Trabajo Social.\r\n- Reorganiza diseños de investigación para mejorar su pertinencia, rigor metodológico y alcance interpretativo.'),
(264, 199.00, 300.00, '4', '- Evalúa proyectos de investigación social considerando criterios de coherencia lógica, relevancia social, rigor ético y factibilidad.\r\n- Diseña proyectos de investigación social innovadores que articulan teoría, método y compromiso con la transformación de realidades sociales.\r\n- Formula propuestas investigativas con proyección local y/o global, basadas en el análisis contextual y las necesidades de los sujetos sociales.'),
(192, 0.00, 125.00, '1', 'Identifica la Justicia social y los derechos humanos como principios del Trabajo Social; Reconoce la importancia de la articulación con otras disciplinas para fortalecer la intervención social; Asocia los conceptos de ecología, desarrollo sostenible, medio ambiente con los problemas sociales del contexto; Distingue prácticas pedagógicas que pueden usarse en la intervención social. Distingue las problemáticas sociales en la actualidad; Contrasta situaciones sociales reales a la luz de teorías sociológicas, psicológicas o antropológicas relevantes.'),
(192, 0.00, 125.00, '1', 'Identifica la Justicia social y los derechos humanos como principios del Trabajo Social; Reconoce la importancia de la articulación con otras disciplinas para fortalecer la intervención social; Asocia los conceptos de ecología, desarrollo sostenible, medio ambiente con los problemas sociales del contexto; Distingue prácticas pedagógicas que pueden usarse en la intervención social. Distingue las problemáticas sociales en la actualidad; Contrasta situaciones sociales reales a la luz de teorías sociológicas, psicológicas o antropológicas relevantes.'),
(192, 126.00, 156.00, '2', 'Organiza equipos de trabajo interdisciplinario para abordar problemáticas sociales en contextos intersectoriales; Aplica estrategias de articulación institucional para facilitar procesos de intervención social conjunta; Demuestra habilidades para coordinar acciones entre actores de diferentes sectores en el marco de proyectos.'),
(192, 157.00, 198.00, '3', 'Analiza problemáticas sociales complejas integrando enfoques interdisciplinares e interinstitucionales para gestionar procesos sociales; Investiga políticas, prácticas y dinámicas territoriales para diseñar proyectos de intervención que promuevan justicia social y Derechos Humanos; Contrasta enfoques y estrategias de intervención para deducir acciones sostenibles que articulen lo comunitario con lo institucional; Identifica actores y recursos clave para proponer intervenciones que fomenten la paz, la inclusión y el desarrollo territorial.'),
(192, 199.00, 300.00, '4', 'Analiza críticamente las condiciones socioeconómicas, culturales y políticas de una comunidad para justificar la pertinencia de un proyecto de intervención social emancipatorio y con pedagogía social para las colectividades; Evalúa críticamente un proyecto de intervención social considerando su coherencia con principios de justicia social, paz, derechos humanos y fundado en la acción sin daño; Diseña proyectos y estrategias de intervención en contextos comunitarios específicos, basado en principios éticos; Formula desde la praxis profesional, estrategias de acción situadas, contextualizadas y fundamentadas teóricamente, que promuevan procesos de transformación social en diversos niveles de intervención (individual, grupal, comunitario).'),
(193, 0.00, 125.00, '1', 'Identifica los elementos de un proyecto de gestión social; Organiza estrategias y metodologías que promueven la participación en el territorio; Distingue los proyectos de gestión e intervención social, de los de investigación, diagnóstico y de emprendimiento; Explica las etapas del diseño, implemetación y evaluación de prigramas y proyectos sociales'),
(193, 126.00, 156.00, '2', 'Utiliza diversas metodologías para la formulación, diseño y ejecución de programas y proyectos sociales; Emplea metodologías participativas para la construcción de diagnósticos comunitarios en contextos de intervención social; Demuestra habilidades en el diseño de proyectos sociales utilizando enfoques metodológicos pertinentes a las realidades del entorno; Ilustra mediante mapas conceptuales o cronogramas, el proceso de planificación y ejecución de un proyecto social con enfoque participativo; Selecciona herramientas metodológicas adecuadas para evaluar la efectividad de programas sociales en distintos contextos comunitarios; Aplica esquemas metodológicos operativos que integren el análisis de actores, recursos y contexto social.'),
(193, 157.00, 198.00, '3', 'Examina los componentes de proyectos sociales, identificando elementos clave que favorecen la participación de los sujetos para el desarrollo social; Categoriza las metodologías aplicadas en programas sociales, comparando su efectividad para las dinámicas del individuo, grupo y comunidad; Identifica  los  resultados y procesos de ejecución de proyectos sociales, infiriendo mejoras para optimizar la participación y el impacto social.'),
(193, 199.00, 300.00, '4', 'Analiza críticamente el contexto social y territorial en el que se inserta un proyecto, reconociendo factores estructurales, culturales y políticos que inciden en la participación de los sujetos; Diseña programas o proyectos sociales coherentes y viables, que integren metodologías dsiciplinares, participativas y respondan a problemáticas reales del entorno; Reconoce el territorio a traves de mapeo de actores, la planificacion y diseño de proyectos de intervencion social, valorando su impacto en el desarrollo social; Explica las etapas y componentes esenciales de un proyecto disciplinar, diferenciando entre procesos de diagnóstico, formulación, ejecución y evaluación.'),
(191, 0.00, 125.00, '1', 'Identifica las políticas que impactan en el contexto social; Describe el papel del trabajador (a) social en el campo de bienestar social y sus posibilidades de participación en las políticas sociales; Comprende la importancia de la historia y los contextos sociales para el desarrollo de políticas sociales; '),
(191, 126.00, 156.00, '2', 'Demuestra dominio de argumentos éticos y técnicos al participar en debates sobre políticas sociales; Desarrolla intervenciones orales organizadas que relacionen los principios del Trabajo Social con políticas sociales específicas del entorno local; Selecciona argumentos relevantes que reflejan el pensamiento crítico, el compromiso social y la identidad profesional del trabajador social nuñista; Interpreta los lineamientos de políticas sociales en función de su impacto en los sujetos sociales y su coherencia con los valores institucionales.'),
(191, 157.00, 198.00, '3', 'Analiza problemáticas sociales del contexto a partir de evidencias empíricas y teóricas, para sustentar su participación argumentada en debates sobre políticas sociales coherentes con los valores institucionales; Compara los diferentes enfoques de intervención social presentes en políticas públicas, identificando sus implicaciones éticas, sociales y territoriales desde una mirada crítica del trabajo social; Conecta y organiza saberes disciplinares con las necesidades del contexto local articulándole a propuestas de formulación e implementación de políticas sociales que promuevan el bienestar colectivo; Distingue los elementos clave de políticas sociales existentes, proponiendo alternativas que respondan a las dinámicas territoriales y los principios institucionales, mediante la participación activa en debates interdisciplinarios.'),
(191, 199.00, 300.00, '4', 'Analiza críticamente experiencias históricas y actuales de políticas sociales y politicas publicas desde una perspectiva territorial; Relaciona teorías y marcos normativos del Trabajo Social con la formulación de políticas sociales y politicas publicas; Argumenta su postura en los debates sobre políticas sociales, empleando fuentes teóricas pertinentes y disciplinares; Participa en la construcción de politicas publicas y sociales en el territorio, en clave de la Justicia social, equidad y solidaridad.'),
(192, 0.00, 125.00, '1', 'Identifica la Justicia social y los derechos humanos como principios del Trabajo Social; Reconoce la importancia de la articulación con otras disciplinas para fortalecer la intervención social; Asocia los conceptos de ecología, desarrollo sostenible, medio ambiente con los problemas sociales del contexto; Distingue prácticas pedagógicas que pueden usarse en la intervención social. Distingue las problemáticas sociales en la actualidad; Contrasta situaciones sociales reales a la luz de teorías sociológicas, psicológicas o antropológicas relevantes.'),
(192, 126.00, 156.00, '2', 'Organiza equipos de trabajo interdisciplinario para abordar problemáticas sociales en contextos intersectoriales; Aplica estrategias de articulación institucional para facilitar procesos de intervención social conjunta; Demuestra habilidades para coordinar acciones entre actores de diferentes sectores en el marco de proyectos.'),
(192, 157.00, 198.00, '3', 'Analiza problemáticas sociales complejas integrando enfoques interdisciplinares e interinstitucionales para gestionar procesos sociales; Investiga políticas, prácticas y dinámicas territoriales para diseñar proyectos de intervención que promuevan justicia social y Derechos Humanos; Contrasta enfoques y estrategias de intervención para deducir acciones sostenibles que articulen lo comunitario con lo institucional; Identifica actores y recursos clave para proponer intervenciones que fomenten la paz, la inclusión y el desarrollo territorial.'),
(192, 199.00, 300.00, '4', 'Analiza críticamente las condiciones socioeconómicas, culturales y políticas de una comunidad para justificar la pertinencia de un proyecto de intervención social emancipatorio y con pedagogía social para las colectividades; Evalúa críticamente un proyecto de intervención social considerando su coherencia con principios de justicia social, paz, derechos humanos y fundado en la acción sin daño; Diseña proyectos y estrategias de intervención en contextos comunitarios específicos, basado en principios éticos; Formula desde la praxis profesional, estrategias de acción situadas, contextualizadas y fundamentadas teóricamente, que promuevan procesos de transformación social en diversos niveles de intervención (individual, grupal, comunitario).'),
(193, 0.00, 125.00, '1', 'Identifica los elementos de un proyecto de gestión social; Organiza estrategias y metodologías que promueven la participación en el territorio; Distingue los proyectos de gestión e intervención social, de los de investigación, diagnóstico y de emprendimiento; Explica las etapas del diseño, implemetación y evaluación de prigramas y proyectos sociales'),
(193, 126.00, 156.00, '2', 'Utiliza diversas metodologías para la formulación, diseño y ejecución de programas y proyectos sociales; Emplea metodologías participativas para la construcción de diagnósticos comunitarios en contextos de intervención social; Demuestra habilidades en el diseño de proyectos sociales utilizando enfoques metodológicos pertinentes a las realidades del entorno; Ilustra mediante mapas conceptuales o cronogramas, el proceso de planificación y ejecución de un proyecto social con enfoque participativo; Selecciona herramientas metodológicas adecuadas para evaluar la efectividad de programas sociales en distintos contextos comunitarios; Aplica esquemas metodológicos operativos que integren el análisis de actores, recursos y contexto social.'),
(193, 157.00, 198.00, '3', 'Examina los componentes de proyectos sociales, identificando elementos clave que favorecen la participación de los sujetos para el desarrollo social; Categoriza las metodologías aplicadas en programas sociales, comparando su efectividad para las dinámicas del individuo, grupo y comunidad; Identifica  los  resultados y procesos de ejecución de proyectos sociales, infiriendo mejoras para optimizar la participación y el impacto social.'),
(193, 199.00, 300.00, '4', 'Analiza críticamente el contexto social y territorial en el que se inserta un proyecto, reconociendo factores estructurales, culturales y políticos que inciden en la participación de los sujetos; Diseña programas o proyectos sociales coherentes y viables, que integren metodologías dsiciplinares, participativas y respondan a problemáticas reales del entorno; Reconoce el territorio a traves de mapeo de actores, la planificacion y diseño de proyectos de intervencion social, valorando su impacto en el desarrollo social; Explica las etapas y componentes esenciales de un proyecto disciplinar, diferenciando entre procesos de diagnóstico, formulación, ejecución y evaluación.'),
(194, 0.00, 125.00, '1', 'Identifica los elementos del proceso investigativo;  Asocia las técnicas de recolección de la información según el enfoque cualitativo o cuantitativo en la investigación social; Enuncia estrategias y metodologías propias del Trabajo Social que pueden usarse en los proyectos de investigación e intervención social;'),
(194, 126.00, 156.00, '2', 'Aplica teorías del Trabajo Social en la formulación de proyectos de intervención orientados a problemáticas sociales específicas; Organiza las fases del proyecto de investigación o intervención considerando los métodos y técnicas propias de la disciplina; Selecciona enfoques metodológicos pertinentes para abordar situaciones sociales complejas identificadas en el diagnóstico comunitario; Utiliza técnicas de recolección de la información y análisis unitario para justificar la pertinencia del enfoque adoptado en la planificación.');
INSERT INTO `desempeno_indicadores` (`nivelID`, `puntaje_min`, `puntaje_max`, `nivel`, `indicadores`) VALUES
(194, 157.00, 198.00, '3', 'Analiza y clasifica problemas sociales, relacionándolos con teorías y métodos del trabajo social; Investiga y valora técnicas , métodos para diseñar intervenciones adecuadas a las necesidades sociales; Organiza y conecta información sociocultural y datos para deducir hipótesis y planificar proyectos efectivos.'),
(194, 199.00, 300.00, '4', 'Evalúa críticamente el nivel de participación logrado por los sujetos en el proyecto, identificando Debilidades, Fortalezas, Oportunidades y Amenazas del proceso de intervención; Compila experiencia del proyecto  de intervencion social basándose en evidencia y desde la praxis profesional, fundamentadas en teoría del Trabajo Social y con criterios éticos; Propone propuestas de intervencion social innovadoras que integran enfoques interdisciplinarios y participativos, con enfoque de derechos humanos y justicia social; Formula proyectos der intervención e investigación social, orientadas a generar transformación social desde una perspectiva participativa, transformadora y liberadora.'),
(195, 0.00, 125.00, '1', 'Reconoce el aporte de las Ciencias Sociales al Trabajo Social; Describe los fundamentos teóricos, metodológicos de la intervención en Trabajo Social;  Distingue los métodos de intervención en Trabajo Social, así como los enfoques y precursores/as; Distingue las estrategias y metodologías propicias de acuerdo con los grupos de intervención ya sea individuo, familia, comunidad o grupo; Reconoce el compromiso ético de la población para con los sujetos de su intervención '),
(195, 126.00, 156.00, '2', 'Aplica marcos conceptuales de las Ciencias Sociales en la estructuración de propuestas de intervención social. Demuestra el uso adecuado de conceptos disciplinares en la interpretación de problemáticas sociales concretas; Utiliza modelos conceptuales para ilustrar la relación entre diagnóstico, planificación y acción social transformadora. Desarrolla propuestas que vinculan problemas sociales identificados con referentes teóricos pertinentes al nivel individual, grupal o comunitario; Adapta modelos teóricos a las características particulares de las comunidades o colectivos intervenidos; Muestra cómo los enfoques conceptuales sustentan las decisiones metodológicas y estratégicas de su proyecto.'),
(195, 157.00, 198.00, '3', 'Analiza y compara los  marcos teóricos de las ciencias sociales para identificar su aporte en intervenciones sociales holísticas; Distingue los fundamentos y elementos teóricos disciplinares   para diseñar intervenciones según problemáticas sociales específicas.'),
(195, 199.00, 300.00, '4', ' Integra los marcos conceptuales y fundamentales de la disciplina con las características de las problemáticas sociales específicas de individuos, familias, grupos y comunidades; Construye un marco conceptual sólido que articule teoría, enfoque metodológico y contexto/realidad social en propuestas transformadoras y holisticos para los grupos o territorios; Diseña propuestas de intervención que reflejan una comprensión crítica de la realidad social desde un enfoque interdisciplinario y ético-politico como respuesta a las problematicas sociales, con enfoque de Derechos humanos.'),
(901, 0.00, 125.00, '1', 'Reconoce los fundamentos, protocolos y normativas relacionados con la instrumentación quirúrgica, el manejo de equipos, técnicas asépticas y normas de bioseguridad, comprendiendo su importancia para la prevención de riesgos biológicos y la seguridad en el entorno quirúrgico.'),
(901, 126.00, 156.00, '2', 'Aplica correctamente protocolos de instrumentación, manejo de materiales y normas de bioseguridad en contextos simulados o reales. Utiliza técnicas y recursos propios del campo quirúrgico para prevenir riesgos biológicos y asegurar la integridad del paciente y del equipo.'),
(901, 157.00, 198.00, '3', 'Analiza situaciones clínicas que comprometen la bioseguridad, la asepsia o el protocolo quirúrgico, identificando causas, consecuencias y factores de riesgo. Interpreta datos clínicos y reorganiza información relevante para comprender y abordar los problemas detectados en la práctica.'),
(901, 199.00, 300.00, '4', 'Evalúa críticamente el cumplimiento de protocolos de instrumentación y bioseguridad en procedimientos quirúrgicos, emitiendo juicios basados en criterios técnicos. Propone mejoras o estrategias para prevenir riesgos biológicos y optimizar los procesos quirúrgicos y de esterilización.'),
(902, 0.00, 125.00, '1', 'Reconoce funciones básicas relacionadas con la coordinación de salas de cirugía, centrales de esterilización y consultorios.  Identifica conceptos fundamentales de gestión clínica, administrativa y financiera, y describe información en tablas o gráficos simples.'),
(902, 126.00, 156.00, '2', 'Aplica procedimientos operativos y administrativos en la organización de salas quirúrgicas y centrales de esterilización. Gestionando dispositivos, recursos y talento humano siguiendo protocolos establecidos para apoyar la gestión clínica, operativa y logística eficiente del servicio.'),
(902, 157.00, 198.00, '3', 'Analiza eventos administrativos, logísticos o clínicos que afectan la coordinación de las áreas bajo su cargo. Identifica causas, consecuencias y propone mejoras en la distribución de recursos, manejo del talento humano o procesos de esterilización'),
(902, 199.00, 300.00, '4', 'Evalúa el desempeño de los procesos clínicos, administrativos y financieros en salas de cirugía, centrales de esterilización o consultorios, emitiendo juicios con base en indicadores y criterios de calidad. Propone soluciones para optimizar el funcionamiento del área, diseñando estrategias de mejora o nuevos modelos de gestión.'),
(903, 0.00, 125.00, '1', 'Reconoce problemas básicos del entorno profesional y comunitario relacionados con la salud. Identifica conceptos clave sobre investigación,   trabajo   interdisciplinario   e   innovación.   Interpreta información simple en gráficos, tablas o textos y la comunica en sus propias palabras'),
(903, 126.00, 156.00, '2', 'Aplica conocimientos técnicos y científicos adquiridos para colaborar en actividades interdisciplinarias básicas, utilizando técnicas y herramientas de investigación. Participando en la recolección de datos y en el desarrollo inicial de propuestas que respondan a necesidades identificadas en su entorno'),
(903, 157.00, 198.00, '3', 'Analiza críticamente situaciones problemáticas del entorno profesional y comunitario, identificando causas, consecuencias y actores clave. Propone enfoques colaborativos que articulen la instrumentación quirúrgica con otras disciplinas, reorganizando la información obtenida.'),
(903, 199.00, 300.00, '4', 'Evalúa de forma argumentada los problemas del entorno, integrando múltiples perspectivas disciplinarias. Diseña soluciones innovadoras, estrategias o tecnologías fundamentadas en evidencia científica y contexto social, generando nuevos aportes a la práctica profesional y al conocimiento en salud.'),
(904, 0.00, 125.00, '1', 'Reconoce las funciones, características técnicas y normativas de los dispositivos, equipos médico-quirúrgicos y técnicas empleadas en procedimientos especializados, así como los conceptos básicos sobre comercialización y emprendimiento en salud.'),
(904, 126.00, 156.00, '2', 'Aplica técnicas de manejo adecuadas de los diferentes dispositivos y equipos médico-quirúrgicos en situaciones simuladas o reales, utiliza técnicas específicas en el apoyo quirúrgico y desarrolla propuestas de comercialización en el ámbito de la salud.'),
(904, 157.00, 198.00, '3', 'Analiza el contexto clínico y comercial para seleccionar de forma pertinente los dispositivos, equipos y técnicas según el procedimiento quirúrgico; identificando oportunidades, riesgos y aspectos logísticos o técnicos que puedan afectan la prestación de los servicios en salud.'),
(904, 199.00, 300.00, '4', 'Evalúa el uso de dispositivos y  estrategias de comercialización en contextos quirúrgicos o empresariales y Propone ideas de mejora, combinando criterios técnicos, comerciales y normativos para responder a las necesidades del sector.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `desempeno_nivel`
--

CREATE TABLE `desempeno_nivel` (
  `cuestionarioID` int(11) DEFAULT 0,
  `abreviatura` varchar(4) NOT NULL,
  `programa_ID` int(11) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `desempeno_nivel`
--

INSERT INTO `desempeno_nivel` (`cuestionarioID`, `abreviatura`, `programa_ID`, `id`) VALUES
(0, 'RA1', 10, 101),
(0, 'RA2', 10, 102),
(0, 'RA3', 10, 103),
(0, 'RA4', 10, 104),
(0, 'RA5', 10, 105),
(0, 'RA6', 10, 106),
(0, 'RA7', 10, 107),
(0, 'RA8', 10, 108),
(0, 'RA9', 10, 109),
(0, 'RA1', 11, 111),
(0, 'RA2', 11, 112),
(0, 'RA3', 11, 113),
(0, 'RA4', 11, 114),
(0, 'RA1', 13, 131),
(0, 'RA2', 13, 132),
(0, 'RA3', 13, 133),
(0, 'RA4', 13, 134),
(0, 'RA5', 13, 135),
(0, 'RA1', 14, 141),
(0, 'RA2', 14, 142),
(0, 'RA3', 14, 143),
(0, 'RA4', 14, 144),
(0, 'RA1', 18, 181),
(0, 'RA2', 18, 182),
(0, 'RA3', 18, 183),
(0, 'RA4', 18, 184),
(0, 'RA1', 19, 191),
(0, 'RA2', 19, 192),
(0, 'RA3', 19, 193),
(0, 'RA4', 19, 194),
(0, 'RA5', 19, 195),
(0, 'RA1', 2, 201),
(0, 'RA2', 2, 202),
(0, 'RA3', 2, 203),
(0, 'RA4', 2, 204),
(0, 'RA1', 21, 211),
(0, 'RA2', 21, 212),
(0, 'RA3', 21, 213),
(0, 'RA4', 21, 214),
(0, 'RA5', 21, 215),
(0, 'RA1', 26, 261),
(0, 'RA2', 26, 262),
(0, 'RA3', 26, 263),
(0, 'RA4', 26, 264),
(0, 'RA1', 28, 281),
(0, 'RA2', 28, 282),
(0, 'RA3', 28, 283),
(0, 'RA4', 28, 284),
(0, 'RA5', 28, 285),
(0, 'RA1', 29, 291),
(0, 'RA2', 29, 292),
(0, 'RA3', 29, 293),
(0, 'RA4', 29, 294),
(0, 'RA1', 6, 601),
(0, 'RA2', 6, 602),
(0, 'RA3', 6, 603),
(0, 'RA4', 6, 604),
(0, 'RA5', 6, 605),
(0, 'RA6', 6, 606),
(0, 'RA1', 9, 901),
(0, 'RA2', 9, 902),
(0, 'RA3', 9, 903),
(0, 'RA4', 9, 904);

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
(2, 'Arnulfo Ramirez', 'aram@email.com', '1007565841', '$2y$10$5vqeTyZZOMH5rqQMKDoWf.4PbsrzWKMfUZ2EAKVa1jriDhSwbGzya', '2025-07-08 16:36:17', NULL),
(3, 'Alvaro Guzman', 'alvag@email.com', '45485868', '$2y$10$0ijGEKo3p2sQT2K8cI2cFuKJ9myiLrrPChg.3RWcyi4KPZkIURhRy', '2025-07-10 13:34:57', NULL),
(4, 'Jose Gracia', 'jose@email.com', '123456987', '$2y$10$YDOheuM5b7TyYlSwtKoA.e9bt2zONBKoyjh1oHlEZ2LvzJDcGhc1W', '2025-08-13 19:13:38', 30),
(5, 'Jesus Campos', 'jCampos@email.com', '951753850', '$2y$10$VVODeIaP3SsXU3k85NY6COgzwVPkX1TNgD24di.U1zn4iM0D2ttpy', '2025-08-13 19:42:30', 10);

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
(8, 2, '9', 0, 3, NULL, NULL),
(9, 3, '1', 1, 0, NULL, NULL),
(10, 3, '2', 0, 1, NULL, NULL),
(11, 3, '3', 0, 2, NULL, NULL),
(12, 3, '4', 0, 3, NULL, NULL),
(13, 4, 'Océano Atlántico', 0, 0, NULL, NULL),
(14, 4, 'Océano Índico', 0, 1, NULL, NULL),
(15, 4, 'Océano Pacífico', 1, 2, NULL, NULL),
(16, 4, 'Océano Ártico', 0, 3, NULL, NULL),
(17, 5, 'Neil Armstrong', 0, 0, NULL, NULL),
(18, 5, 'Yuri Gagarin', 1, 1, NULL, NULL),
(19, 5, 'Alan Shepard', 0, 2, NULL, NULL),
(20, 5, 'John Glenn', 0, 3, NULL, NULL);

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
(2, 1, '¿Cuánto es 2 + 2?', 1, 150.00, NULL, NULL, 1),
(3, 2, 'Marque 1', 0, 300.00, NULL, NULL, 1),
(4, 3, '¿Cuál es el océano más grande y profundo del mundo?', 0, 150.00, NULL, NULL, 1),
(5, 3, '¿Quién fue el primer ser humano en viajar al espacio?', 1, 150.00, NULL, NULL, 1);

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
(2, 3, 'ADMINISTRACIÓN DE EMPRESAS', 1),
(3, 3, 'BACTERIOLOGÍA', 1),
(6, 3, 'ENFERMERÍA', 1),
(9, 3, 'INSTRUMENTACIÓN QUIRÚRGICA', 1),
(10, 3, 'MEDICINA', 1),
(11, 3, 'ODONTOLOGÍA', 1),
(12, 3, 'CONTADURÍA PÚBLICA', 1),
(13, 3, 'DERECHO', 1),
(14, 3, 'Ingeniería de Sistemas', 1),
(15, 2, 'TECNOLOGÍA EN ATENCIÓN PREHOSPITALARIA', 1),
(17, 2, 'TECNOLOGÍA EN MECÁNICA DENTAL', 1),
(18, 3, 'DERECHO | BARRANQUILLA', 2),
(19, 3, 'TRABAJO SOCIAL', 1),
(21, 2, 'TECNÓLOGIA EN SISTEMAS DE INFORMACIÓN Y DE SOFTWARE', 1),
(22, 2, 'TECNOLOGÍA EN CONTABILIDAD SISTEMATIZADA', 1),
(26, 3, 'TRABAJO SOCIAL', 2),
(27, 3, 'ENFERMERÍA', 2),
(28, 4, 'ESPECIALIZACIÓN EN EDUCACIÓN Y DIVERSIDAD', 1),
(29, 3, 'LICENCIATURA EN EDUCACIÓN INFANTIL', 1),
(30, 3, 'INGENIERÍA DE SOFTWARE', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `relacion_cuestionario_programa`
--

CREATE TABLE `relacion_cuestionario_programa` (
  `id` int(11) NOT NULL,
  `id_cuestionario` int(11) NOT NULL,
  `id_programa` int(11) DEFAULT NULL,
  `id_docente` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `relacion_cuestionario_programa`
--

INSERT INTO `relacion_cuestionario_programa` (`id`, `id_cuestionario`, `id_programa`, `id_docente`, `activo`) VALUES
(1, 1, 30, 4, 1),
(2, 2, 30, 4, 1),
(3, 3, 30, 4, 1);

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
-- Indices de la tabla `desempeno_indicadores`
--
ALTER TABLE `desempeno_indicadores`
  ADD KEY `indicador_nivel` (`nivelID`);

--
-- Indices de la tabla `desempeno_nivel`
--
ALTER TABLE `desempeno_nivel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unico` (`abreviatura`,`programa_ID`),
  ADD KEY `programa` (`programa_ID`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `campus`
--
ALTER TABLE `campus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cuestionario`
--
ALTER TABLE `cuestionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `docente`
--
ALTER TABLE `docente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `intento_cuestionario`
--
ALTER TABLE `intento_cuestionario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nivel`
--
ALTER TABLE `nivel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `opcion_respuesta`
--
ALTER TABLE `opcion_respuesta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `periodo`
--
ALTER TABLE `periodo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `programa`
--
ALTER TABLE `programa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `relacion_cuestionario_programa`
--
ALTER TABLE `relacion_cuestionario_programa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `respuesta_estudiante`
--
ALTER TABLE `respuesta_estudiante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Filtros para la tabla `desempeno_indicadores`
--
ALTER TABLE `desempeno_indicadores`
  ADD CONSTRAINT `indicador_nivel` FOREIGN KEY (`nivelID`) REFERENCES `desempeno_nivel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `desempeno_nivel`
--
ALTER TABLE `desempeno_nivel`
  ADD CONSTRAINT `programa` FOREIGN KEY (`programa_ID`) REFERENCES `programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
