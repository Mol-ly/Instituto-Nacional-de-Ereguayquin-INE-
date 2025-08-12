-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-08-2025 a las 00:32:03
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `b_instituto`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_materias`
--

CREATE TABLE `asistencia_materias` (
  `ID_ASITENCIA` int(11) NOT NULL,
  `ID_ESTUDIANTE` int(7) NOT NULL,
  `ID_MATERIA` int(11) NOT NULL,
  `FECHA` date NOT NULL,
  `ASISTENCIA` enum('PRESENTE','AUSENTE','JUSTIFICADO','DIA FESTIVO') NOT NULL,
  `DOCENTE` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia_materias`
--

INSERT INTO `asistencia_materias` (`ID_ASITENCIA`, `ID_ESTUDIANTE`, `ID_MATERIA`, `FECHA`, `ASISTENCIA`, `DOCENTE`) VALUES
(5, 3214329, 3, '2025-08-12', 'PRESENTE', 'OLGA ORALIA ALVARADO VILLALTA'),
(6, 3214329, 3, '2025-08-12', 'PRESENTE', 'OLGA ORALIA ALVARADO VILLALTA'),
(7, 3214329, 3, '2025-08-14', 'PRESENTE', 'OLGA ORALIA ALVARADO VILLALTA'),
(8, 3214329, 3, '2025-08-13', 'PRESENTE', 'OLGA ORALIA ALVARADO VILLALTA'),
(9, 10001004, 3, '2025-08-13', 'PRESENTE', 'OLGA ORALIA ALVARADO VILLALTA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_modulos`
--

CREATE TABLE `asistencia_modulos` (
  `ID_ASISTENCIA` int(11) NOT NULL,
  `ID_ESTUDIANTE` int(7) NOT NULL,
  `ID_MODULO` int(11) NOT NULL,
  `FECHA` date NOT NULL,
  `ASISTENCIA` enum('PRESENTE','AUSENTE','JUSTIFICADO','DIA FESTIVO') NOT NULL,
  `DOCENTE` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes_modulo`
--

CREATE TABLE `estudiantes_modulo` (
  `ID` int(11) NOT NULL,
  `ID_ESTUDIANTE` int(7) NOT NULL,
  `ID_MODULO` int(11) NOT NULL,
  `DOCENTE` varchar(100) NOT NULL,
  `ROL` enum('ESTUDIANTE') NOT NULL,
  `ANO_ACADEMICO` enum('1° AÑO','2° AÑO','3° AÑO') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes_modulo`
--

INSERT INTO `estudiantes_modulo` (`ID`, `ID_ESTUDIANTE`, `ID_MODULO`, `DOCENTE`, `ROL`, `ANO_ACADEMICO`) VALUES
(1, 2009873, 1, 'JUAN MATEO FLORES AR', 'ESTUDIANTE', '1° AÑO'),
(2, 10001004, 2, 'JUAN MATEO FLORES ARQUÍMEDEZ', 'ESTUDIANTE', '1° AÑO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudintes_materia`
--

CREATE TABLE `estudintes_materia` (
  `ID` int(11) NOT NULL,
  `ID_ESTUDIANTE` int(7) NOT NULL,
  `MATERIA` varchar(40) NOT NULL,
  `DOCENTE` varchar(40) NOT NULL,
  `ROL` enum('ESTUDIANTE') NOT NULL,
  `ANO_ACADEMICO` enum('1 AÑO','2 AÑO') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudintes_materia`
--

INSERT INTO `estudintes_materia` (`ID`, `ID_ESTUDIANTE`, `MATERIA`, `DOCENTE`, `ROL`, `ANO_ACADEMICO`) VALUES
(1, 3214329, 'LENGUAJE Y LITERATURA', 'OLGA ORALIA ALVARADO', 'ESTUDIANTE', '1 AÑO'),
(2, 3214329, 'CIENCIA NATURALES', 'ANDREA ESMERALDA FIN', 'ESTUDIANTE', '1 AÑO'),
(3, 3214329, 'ESTUDIO SOCIALES', 'MARLON FERRUFINO ANT', 'ESTUDIANTE', '1 AÑO'),
(4, 3214329, 'MATEMÁTICAS', 'MARIA ENCARNACIÓN CO', 'ESTUDIANTE', '1 AÑO'),
(5, 10001004, 'LENGUAJE Y LITERATURA', 'OLGA ORALIA ALVARADO', 'ESTUDIANTE', '1 AÑO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia`
--

CREATE TABLE `materia` (
  `ID_MATERIA` int(11) NOT NULL,
  `MATERIA` varchar(40) NOT NULL,
  `DOCENTE` varchar(40) NOT NULL,
  `LOGRO` varchar(500) NOT NULL,
  `HORARIO` datetime NOT NULL,
  `ESTADO` enum('ACTIVO','INACTIVO') NOT NULL,
  `TIPO` enum('MATERIA','SEMINARIO') DEFAULT 'MATERIA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materia`
--

INSERT INTO `materia` (`ID_MATERIA`, `MATERIA`, `DOCENTE`, `LOGRO`, `HORARIO`, `ESTADO`, `TIPO`) VALUES
(1, 'CIENCIA NATURALES', 'ANDREA ESMERALDA FINCA CARMELO', 'Aplicación del método científico, investigación, análisis.', '2025-08-13 09:58:00', 'ACTIVO', 'MATERIA'),
(2, 'ESTUDIO SOCIALES', 'MARLON FERRUFINO ANTIGUO VAQUÉZ', 'CONOCER TUS RAICES, CULTURAS Y COSTUMBRES DE EL SALVADOR', '2025-08-07 17:23:59', 'ACTIVO', 'MATERIA'),
(3, 'LENGUAJE Y LITERATURA', 'OLGA ORALIA ALVARADO VILLALTA', 'Niveles de comprensión lectora: literal, inferencial y crítico.', '2025-01-31 14:39:55', 'ACTIVO', 'MATERIA'),
(4, 'MATEMÁTICAS', 'MARIA ENCARNACIÓN COLORADO AMARGO', 'Habilidades de cálculo, representación y resolución de problemas.', '2025-01-31 14:39:55', 'ACTIVO', 'MATERIA'),
(5, 'MUCI', 'MANUELA RAQUEL QUINOA QUINOA ', 'El estudiante valora la cultural, participa en actividades que fomenten la identidad nacional y ejerce prácticas ciudadanas responsables respetando normas, símbolos patrios y principios democráticos.', '2025-01-31 14:49:22', 'ACTIVO', 'MATERIA'),
(6, 'OV', 'RAUL ALBERTO QUINTANILLA CARJARO', 'El estudiante desarrolla habilidades socioemocionales, hábitos saludables y valores éticos, fortaleciendo su resiliencia, autoestima y capacidad para tomar decisiones responsables que mejoren su calidad de vida y la de su comunidad.', '2025-01-31 14:49:22', 'ACTIVO', 'MATERIA'),
(7, 'SEMINARIO ', 'XIOMARA CARMEN JUSTICIA CORAZÓN', 'El estudiante identifica, formula y desarrolla proyectos de investigación aplicando métodos científicos, técnicas de recolección y análisis de datos, presentando resultados con rigor académico y ética.', '2025-01-31 14:49:22', 'ACTIVO', 'SEMINARIO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulo`
--

CREATE TABLE `modulo` (
  `ID_MODULO` int(11) NOT NULL,
  `MODULO` varchar(100) NOT NULL,
  `NOMBRE_MODULO` varchar(300) NOT NULL,
  `DOCENTE` varchar(100) NOT NULL,
  `TIPO` enum('MODULO') DEFAULT 'MODULO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modulo`
--

INSERT INTO `modulo` (`ID_MODULO`, `MODULO`, `NOMBRE_MODULO`, `DOCENTE`, `TIPO`) VALUES
(1, 'BTVAC 1.0', 'Orientación del estudiante al proceso educativo, ambiente de aula y centros de aprendizaje', 'JUAN MATEO FLORES ARQUÍMEDEZ', 'MODULO'),
(2, 'BTVAC 1.1', 'Clasificación, codificación, registro y archivo de documentos contables', 'JUAN MATEO FLORES ARQUÍMEDEZ', 'MODULO'),
(3, 'BTVAC 1.2', 'Manejo de documentos y registro de operaciones contables', 'JUAN MATEO FLORES ARQUÍMEDEZ', 'MODULO'),
(4, 'BTVAC 1.3', 'Registro de operaciones en el libro diario y mayor', 'JUAN MATEO FLORES ARQUÍMEDEZ', 'MODULO'),
(5, 'BTVAC 1.4', 'Registro y control de operaciones comerciales', 'JUAN MATEO FLORES ARQUÍMEDEZ', 'MODULO'),
(6, 'BTVAC 1.5', 'Interpretación de la información financiera', 'JUAN MATEO FLORES ARQUÍMEDEZ', 'MODULO'),
(7, 'BTVAC 1.6', 'Registro y control de operaciones en efectivo', 'JUAN MATEO FLORES ARQUÍMEDEZ', 'MODULO'),
(8, 'BTVAC 1.7', 'Elaboración de estados financieros', 'JUAN MATEO FLORES ARQUÍMEDEZ', 'MODULO'),
(9, 'BTVAC 1.8', 'Atención y servicio al cliente', 'JUAN MATEO FLORES ARQUÍMEDEZ', 'MODULO'),
(10, 'BTVAC 2.0', 'Orientación del estudiante al proceso educativo y centros de aprendizaje', 'MARÍA FERNANDA MARQUÉZ CONDADO', 'MODULO'),
(11, 'BTVAC 2.1', 'Cálculo de costos y presupuestos', 'MARÍA FERNANDA MARQUÉZ CONDADO', 'MODULO'),
(12, 'BTVAC 2.2', 'Registro de operaciones de activos', 'MARÍA FERNANDA MARQUÉZ CONDADO', 'MODULO'),
(13, 'BTVAC 2.3', 'Registro de operaciones de pasivos', 'MARÍA FERNANDA MARQUÉZ CONDADO', 'MODULO'),
(14, 'BTVAC 2.4', 'Registro de operaciones de patrimonio', 'MARÍA FERNANDA MARQUÉZ CONDADO', 'MODULO'),
(15, 'BTVAC 2.5', 'Control de inventarios', 'MARÍA FERNANDA MARQUÉZ CONDADO', 'MODULO'),
(16, 'BTVAC 2.6', 'Registro y control de operaciones bancarias', 'MARÍA FERNANDA MARQUÉZ CONDADO', 'MODULO'),
(17, 'BTVAC 2.7', 'Elaboración y análisis de estados financieros', 'MARÍA FERNANDA MARQUÉZ CONDADO', 'MODULO'),
(18, 'BTVAC 2.8', 'Gestión de recursos humanos', 'MARÍA FERNANDA MARQUÉZ CONDADO', 'MODULO'),
(19, 'BTVAC 3.0', 'Orientación del estudiante al proceso educativo, práctica empresarial y centros de aprendizaje', 'JUAN PABLO VILLAMAL FUENTES', 'MODULO'),
(20, 'BTVAC 3.1', 'Registro y control de operaciones de importación y exportación', 'JUAN PABLO VILLAMAL FUENTES', 'MODULO'),
(21, 'BTVAC 3.2', 'Registro y control de operaciones con organismos financieros', 'JUAN PABLO VILLAMAL FUENTES', 'MODULO'),
(22, 'BTVAC 3.3', 'Registro y control de operaciones de financiamiento', 'JUAN PABLO VILLAMAL FUENTES', 'MODULO'),
(23, 'BTVAC 3.4', 'Registro y control de operaciones de inversión', 'JUAN PABLO VILLAMAL FUENTES', 'MODULO'),
(24, 'BTVAC 3.5', 'Interpretación de estados financieros consolidados', 'JUAN PABLO VILLAMAL FUENTES', 'MODULO'),
(25, 'BTVAC 3.6', 'Aplicación de principios de auditoría', 'JUAN PABLO VILLAMAL FUENTES', 'MODULO'),
(26, 'BTVAC 3.7', 'Elaboración de informes contables y financieros', 'JUAN PABLO VILLAMAL FUENTES', 'MODULO'),
(27, 'BTVAC 3.8', 'Planificación y gestión empresarial', 'JUAN PABLO VILLAMAL FUENTES', 'MODULO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `ID_NOTA` int(10) NOT NULL,
  `NIE` int(7) NOT NULL,
  `MATERIA` varchar(40) NOT NULL,
  `PERIODO` int(2) DEFAULT NULL,
  `NOTA` decimal(5,2) DEFAULT NULL,
  `MODULO` varchar(30) DEFAULT NULL,
  `NOTA_MODULO` decimal(5,2) DEFAULT NULL,
  `ESTADO` varchar(50) DEFAULT NULL,
  `DOCENTE` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notas`
--

INSERT INTO `notas` (`ID_NOTA`, `NIE`, `MATERIA`, `PERIODO`, `NOTA`, `MODULO`, `NOTA_MODULO`, `ESTADO`, `DOCENTE`) VALUES
(19, 3214329, 'LENGUAJE Y LITERATURA', 1, 0.04, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(20, 3214329, 'LENGUAJE Y LITERATURA', 1, 0.04, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(21, 3214329, 'LENGUAJE Y LITERATURA', 1, 0.04, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(22, 3214329, 'LENGUAJE Y LITERATURA', 1, 0.04, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(23, 3214329, 'LENGUAJE Y LITERATURA', 2, 0.01, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(24, 3214329, 'LENGUAJE Y LITERATURA', 2, 0.01, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(25, 3214329, 'LENGUAJE Y LITERATURA', 2, 0.01, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(26, 3214329, 'LENGUAJE Y LITERATURA', 3, 0.01, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(27, 3214329, 'LENGUAJE Y LITERATURA', 3, 0.01, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(28, 3214329, 'LENGUAJE Y LITERATURA', 3, 0.01, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(29, 3214329, 'LENGUAJE Y LITERATURA', 3, 0.01, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(30, 3214329, 'LENGUAJE Y LITERATURA', 1, 0.07, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(31, 3214329, 'LENGUAJE Y LITERATURA', 1, 10.00, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA'),
(32, 10001004, 'LENGUAJE Y LITERATURA', 1, 9.00, NULL, NULL, NULL, 'OLGA ORALIA ALVARADO VILLALTA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_registrado`
--

CREATE TABLE `personal_registrado` (
  `NIE` int(7) NOT NULL,
  `NOMBRE` varchar(100) NOT NULL,
  `APELLIDO` varchar(100) NOT NULL,
  `CORREO` varchar(40) NOT NULL,
  `CONTRASEÑA` varchar(10) NOT NULL,
  `ROL` enum('ADMINISTRADOR','DOCENTE','ESTUDIANTE') DEFAULT NULL,
  `FECHA_DE_NACIMIENTO` date DEFAULT NULL,
  `DIRECCION` varchar(100) DEFAULT NULL,
  `TELEFONO` int(50) DEFAULT NULL,
  `ESPECIALIDAD` varchar(80) DEFAULT NULL,
  `ANO_ACADEMICO` enum('1° AÑO',' 2° AÑO','3° AÑO') DEFAULT NULL,
  `EDAD` int(15) NOT NULL,
  `TURNO` varchar(50) NOT NULL,
  `ESTADO` varchar(40) NOT NULL,
  `SECCION` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personal_registrado`
--

INSERT INTO `personal_registrado` (`NIE`, `NOMBRE`, `APELLIDO`, `CORREO`, `CONTRASEÑA`, `ROL`, `FECHA_DE_NACIMIENTO`, `DIRECCION`, `TELEFONO`, `ESPECIALIDAD`, `ANO_ACADEMICO`, `EDAD`, `TURNO`, `ESTADO`, `SECCION`) VALUES
(2009873, 'ANDREA ESMERALDA', 'FINCA CARMELO', '2009873@clases.edu.sv', 'ouuhhg7', 'DOCENTE', '1999-08-26', 'Usulután, Berlín. ', 72247892, NULL, '1° AÑO', 26, 'VESPERTINO', 'ACTIVO', 'A'),
(2088345, 'MARLON FERRUFINO', 'ANTIGUO VAQUÉZ', '2088345@clases.edu.sv', 'obuydg', 'DOCENTE', '1999-08-19', 'Usulután, La poza.', 78234567, NULL, '1° AÑO', 26, 'VESPERTINO', 'ACTIVO', 'A'),
(2117827, 'MARIA ENCARNACIÓN', 'COLORADO AMARGO', '2117827@clases.edu.sv', 'poiyb', 'DOCENTE', '1999-08-26', 'Usulután, Puerto el Triunfo.', 78234578, NULL, '1° AÑO', 26, 'VESPERTINO', 'ACTIVO', 'A'),
(2130725, 'MANUELA RAQUEL', 'QUINOA QUINOA ', '2130725@clases.edu.sv', 'okh8', 'DOCENTE', '1999-08-13', 'Usulután, Santa Elena.', NULL, NULL, '1° AÑO', 27, 'VESPERTINO', 'ACTIVO', 'A'),
(2134567, 'RAUL ALBERTO', 'QUINTANILLA CARJARO', '2134567@clases.edu.sv', 'jguojnko', 'DOCENTE', '1999-08-01', 'Usuluta, Usulután', 73452435, NULL, '1° AÑO', 26, 'VESPERTINO', 'ACTIVO', 'A'),
(2500924, 'JUAN MATEO', 'FLORES ARQUÍMEDEZ', '2500924@clases.edu.sv', 'pogu4&', 'DOCENTE', '1997-03-04', 'PASAJE COLORADO ', 77345678, 'ADMINISTRATIVO CONTABLE', '1° AÑO', 28, 'VESPERTINO', 'ACTIVO', NULL),
(2501231, 'MARÍA FERNANDA ', 'MARQUÉZ CONDADO', '2501231@clases.edu.sv', 'asxzdw', 'DOCENTE', '1990-05-01', 'FINAL 8A. CALLE PONIENTE AL COSTADO SUR DEL SEGURO SOCIAL.', 67892345, 'ADMINISTRATIVO CONTABLE3', ' 2° AÑO', 34, 'MATUTINO', 'ACTIVO', NULL),
(2501345, 'JUAN PABLO ', 'VILLAMAL FUENTES', '2501345@clases.edu.sv', 'qwerty6', 'DOCENTE', '1985-08-01', 'FINAL 8A. CLLE PONIENTE AL COSTADO SUR DE AGROSERVICIO.', 67892345, 'ADMINISTRATIVO CONTABLE3', '3° AÑO', 34, 'VESPERTINO', 'ACTIVO', NULL),
(2501356, 'SOFÍA YANETH', 'MARQUINA SERRANO', '2501356@clases.edu.sv', '0987', 'ADMINISTRADOR', '1995-08-17', 'USULUTÁN, USULUTÁN CENTRO', 23640942, 'NINGUNA', NULL, 30, 'VESPERTINO', 'ACTIVO', NULL),
(2514343, 'OLGA ORALIA', 'ALVARADO VILLALTA', '2514343@clases.edu.sv', '1234', 'DOCENTE', '1995-01-31', 'USULUTÁN, ERGUAYQUÍN', 75089803, 'BACHILLERATO GENERAL', '1° AÑO', 25, 'VESPERTINO', 'ACTIVO', NULL),
(2998356, 'XIOMARA CARMEN', 'JUSTICIA CORAZÓN', '2998356@clases.edu.sv', 'hjbhJ', 'DOCENTE', '1998-08-12', 'Usulután, Usulután', 7863467, NULL, '1° AÑO', 27, 'VESPERTINO', 'ACTIVO', 'A'),
(3214329, 'JOSÉ ALFONSO', 'QUINTEROS VELIS', '3214329@clases.edu.sv', '2345', 'ESTUDIANTE', '2007-08-07', 'USULUTÁN, SANTA ELENA', 53059783, 'BACHILLERATO GENERAL', '1° AÑO', 17, 'VESPERTINO', 'ACTIVO', 'A'),
(10001001, 'Juan', 'Pérez', '10001001@clases.edu.sv', '1234', 'ESTUDIANTE', '1995-06-15', 'Calle 123, Ciudad', 12345678, 'Bachillerato General', '1° AÑO', 22, 'VESPERTINO', 'ACTIVO', 'A'),
(10001002, 'María', 'Gómez', '10001002@clases.edu.sv', '1234', 'ESTUDIANTE', '1997-11-20', 'Avenida 456, Ciudad', 87654321, 'Bachillerato Técnico Vocacional en Contaduría', '', 20, 'VESPERTINO', 'ACTIVO', 'B'),
(10001003, 'Pedro', 'Sánchez', '10001003@clases.edu.sv', '1234', 'ESTUDIANTE', '1996-03-08', 'Calle 789, Ciudad', 45678901, 'Bachillerato General', '3° AÑO', 21, 'VESPERTINO', 'ACTIVO', 'C'),
(10001004, 'Ana', 'Rodríguez', '10001004@clases.edu.sv', '1234', 'ESTUDIANTE', '1998-09-12', 'Avenida 159, Ciudad', 23456789, 'Bachillerato Técnico Vocacional en Contaduría', '1° AÑO', 19, 'VESPERTINO', 'ACTIVO', 'A'),
(10001005, 'Luis', 'Hernández', '10001005@clases.edu.sv', '1234', 'ESTUDIANTE', '1997-04-25', 'Calle 753, Ciudad', 67890123, 'Bachillerato General', '', 20, 'MATUTINO', 'ACTIVO', 'B'),
(10001006, 'Sofía', 'Martínez', '10001006@clases.edu.sv', '1234', 'ESTUDIANTE', '1999-08-01', 'Avenida 789, Ciudad', 34567890, 'Bachillerato Técnico Vocacional en Contaduría', '1° AÑO', 18, 'VESPERTINO', 'ACTIVO', 'C'),
(10001007, 'Carlos', 'Flores', '10001007@clases.edu.sv', '1234', 'ESTUDIANTE', '1996-11-15', 'Calle 456, Ciudad', 78901234, 'Bachillerato General', '3° AÑO', 21, 'VESPERTINO', 'ACTIVO', 'A'),
(10001008, 'Lucía', 'Ramírez', '10001008@clases.edu.sv', '1234', 'ESTUDIANTE', '1998-02-28', 'Avenida 159, Ciudad', 45678901, 'Bachillerato Técnico Vocacional en Contaduría', '', 19, 'MATUTINO', 'ACTIVO', 'B'),
(10001009, 'Javier', 'Castillo', '10001009@clases.edu.sv', '1234', 'ESTUDIANTE', '1997-05-10', 'Calle 753, Ciudad', 12345678, 'Bachillerato General', '1° AÑO', 20, 'VESPERTINO', 'ACTIVO', 'C'),
(10001010, 'Gabriela', 'Morales', '10001010@clases.edu.sv', '1234', 'ESTUDIANTE', '1998-12-05', 'Avenida 456, Ciudad', 76543219, 'Bachillerato Técnico Vocacional en Contaduría', '1° AÑO', 19, 'VESPERTINO', 'ACTIVO', 'A');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencia_materias`
--
ALTER TABLE `asistencia_materias`
  ADD PRIMARY KEY (`ID_ASITENCIA`),
  ADD KEY `ID_ESTUDIANTE` (`ID_ESTUDIANTE`),
  ADD KEY `ID_MATERIA` (`ID_MATERIA`),
  ADD KEY `DOCENTE` (`DOCENTE`);

--
-- Indices de la tabla `asistencia_modulos`
--
ALTER TABLE `asistencia_modulos`
  ADD KEY `ID_ESTUDIANTE` (`ID_ESTUDIANTE`),
  ADD KEY `ID_MATERIA` (`ID_MODULO`),
  ADD KEY `DOCENTE` (`DOCENTE`);

--
-- Indices de la tabla `estudiantes_modulo`
--
ALTER TABLE `estudiantes_modulo`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `ID_ESTUDIANTE` (`ID_ESTUDIANTE`),
  ADD KEY `ID_MODULO` (`ID_MODULO`),
  ADD KEY `ROL` (`ROL`),
  ADD KEY `ANO_ACADEMICO` (`ANO_ACADEMICO`),
  ADD KEY `DOCENTE` (`DOCENTE`);

--
-- Indices de la tabla `estudintes_materia`
--
ALTER TABLE `estudintes_materia`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_ESTUDIANTE` (`ID_ESTUDIANTE`) USING BTREE,
  ADD KEY `ANO_ACADEMICO` (`ANO_ACADEMICO`),
  ADD KEY `MATERIA` (`MATERIA`),
  ADD KEY `DOCENTE` (`DOCENTE`),
  ADD KEY `ROL` (`ROL`);

--
-- Indices de la tabla `materia`
--
ALTER TABLE `materia`
  ADD PRIMARY KEY (`ID_MATERIA`),
  ADD UNIQUE KEY `MATERIA` (`MATERIA`) USING BTREE,
  ADD KEY `DOCENTE` (`DOCENTE`) USING BTREE;

--
-- Indices de la tabla `modulo`
--
ALTER TABLE `modulo`
  ADD PRIMARY KEY (`ID_MODULO`),
  ADD UNIQUE KEY `MODULO` (`MODULO`) USING BTREE,
  ADD KEY `DOCENTE` (`DOCENTE`) USING BTREE;

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`ID_NOTA`),
  ADD KEY `NIE` (`NIE`,`DOCENTE`) USING BTREE,
  ADD KEY `MODULO` (`MODULO`) USING BTREE,
  ADD KEY `MATERIA_2` (`MATERIA`) USING BTREE,
  ADD KEY `MATERIA` (`MATERIA`) USING BTREE;

--
-- Indices de la tabla `personal_registrado`
--
ALTER TABLE `personal_registrado`
  ADD PRIMARY KEY (`NIE`),
  ADD KEY `NOMBRE` (`NOMBRE`) USING BTREE,
  ADD KEY `ROL` (`ROL`),
  ADD KEY `ANO_ACADEMICO` (`ANO_ACADEMICO`),
  ADD KEY `APELLIDO` (`APELLIDO`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencia_materias`
--
ALTER TABLE `asistencia_materias`
  MODIFY `ID_ASITENCIA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `estudiantes_modulo`
--
ALTER TABLE `estudiantes_modulo`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estudintes_materia`
--
ALTER TABLE `estudintes_materia`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `materia`
--
ALTER TABLE `materia`
  MODIFY `ID_MATERIA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `modulo`
--
ALTER TABLE `modulo`
  MODIFY `ID_MODULO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `ID_NOTA` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencia_materias`
--
ALTER TABLE `asistencia_materias`
  ADD CONSTRAINT `asistencia_materias_ibfk_1` FOREIGN KEY (`ID_ESTUDIANTE`) REFERENCES `personal_registrado` (`NIE`),
  ADD CONSTRAINT `asistencia_materias_ibfk_2` FOREIGN KEY (`ID_MATERIA`) REFERENCES `materia` (`ID_MATERIA`),
  ADD CONSTRAINT `asistencia_materias_ibfk_3` FOREIGN KEY (`DOCENTE`) REFERENCES `materia` (`DOCENTE`);

--
-- Filtros para la tabla `asistencia_modulos`
--
ALTER TABLE `asistencia_modulos`
  ADD CONSTRAINT `asistencia_modulos_ibfk_1` FOREIGN KEY (`ID_ESTUDIANTE`) REFERENCES `personal_registrado` (`NIE`),
  ADD CONSTRAINT `asistencia_modulos_ibfk_2` FOREIGN KEY (`ID_MODULO`) REFERENCES `modulo` (`ID_MODULO`),
  ADD CONSTRAINT `asistencia_modulos_ibfk_3` FOREIGN KEY (`DOCENTE`) REFERENCES `modulo` (`DOCENTE`);

--
-- Filtros para la tabla `estudiantes_modulo`
--
ALTER TABLE `estudiantes_modulo`
  ADD CONSTRAINT `estudiantes_modulo_ibfk_1` FOREIGN KEY (`ID_ESTUDIANTE`) REFERENCES `personal_registrado` (`NIE`),
  ADD CONSTRAINT `estudiantes_modulo_ibfk_2` FOREIGN KEY (`ID_MODULO`) REFERENCES `modulo` (`ID_MODULO`),
  ADD CONSTRAINT `estudiantes_modulo_ibfk_3` FOREIGN KEY (`ROL`) REFERENCES `personal_registrado` (`ROL`);

--
-- Filtros para la tabla `estudintes_materia`
--
ALTER TABLE `estudintes_materia`
  ADD CONSTRAINT `estudintes_materia_ibfk_1` FOREIGN KEY (`ID_ESTUDIANTE`) REFERENCES `personal_registrado` (`NIE`),
  ADD CONSTRAINT `estudintes_materia_ibfk_2` FOREIGN KEY (`MATERIA`) REFERENCES `materia` (`MATERIA`),
  ADD CONSTRAINT `estudintes_materia_ibfk_3` FOREIGN KEY (`ROL`) REFERENCES `personal_registrado` (`ROL`);

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`NIE`) REFERENCES `personal_registrado` (`NIE`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`MATERIA`) REFERENCES `materia` (`MATERIA`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notas_ibfk_3` FOREIGN KEY (`MODULO`) REFERENCES `modulo` (`MODULO`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
