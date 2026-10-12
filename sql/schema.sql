-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 12-10-2026 a las 22:43:12
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
-- Base de datos: `appjoteca`
--
CREATE DATABASE IF NOT EXISTS `appjoteca` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `appjoteca`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autores`
--
-- Creación: 26-09-2026 a las 23:59:32
--

CREATE TABLE `autores` (
  `id_autor` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bibliotecas`
--
-- Creación: 18-09-2026 a las 05:13:06
--

CREATE TABLE `bibliotecas` (
  `id_biblioteca` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colecciones`
--
-- Creación: 18-09-2026 a las 05:15:25
--

CREATE TABLE `colecciones` (
  `id_coleccion` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `id_biblioteca` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--
-- Creación: 25-09-2026 a las 18:14:24
--

CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `tipo_comentario` varchar(50) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `correo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dewey`
--
-- Creación: 18-09-2026 a las 05:13:47
--

CREATE TABLE `dewey` (
  `id_dewey` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `codigo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editoriales`
--
-- Creación: 26-09-2026 a las 23:56:41
--

CREATE TABLE `editoriales` (
  `id_editorial` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejemplares`
--
-- Creación: 30-08-2026 a las 23:39:15
--

CREATE TABLE `ejemplares` (
  `id_ejemplar` int(11) NOT NULL,
  `fk_id_libro_ejemplar` int(11) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `id_coleccion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--
-- Creación: 21-09-2026 a las 03:10:47
--

CREATE TABLE `historial` (
  `id_historial` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `detalle` varchar(255) DEFAULT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `idioma`
--
-- Creación: 26-09-2026 a las 22:24:53
--

CREATE TABLE `idioma` (
  `id_idioma` int(11) NOT NULL,
  `nombre_idioma` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `insignias`
--
-- Creación: 12-10-2026 a las 19:40:51
-- Última actualización: 12-10-2026 a las 19:40:51
--

CREATE TABLE `insignias` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `icono` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `insignias`
--

INSERT INTO `insignias` (`id`, `codigo`, `nombre`, `descripcion`, `icono`) VALUES
(1, 'primera_pagina', 'Primera Página', 'Realizaste tu primer préstamo.', 'auto_stories'),
(2, 'lector_novato', 'Lector Novato', 'Devolviste 3 libros.', 'menu_book'),
(3, 'lector_intermedio', 'Lector Intermedio', 'Devolviste 10 libros.', 'library_books'),
(4, 'lector_avanzado', 'Lector Avanzado', 'Devolviste 25 libros.', 'local_library'),
(5, 'lector_experto', 'Lector Experto', 'Devolviste 50 libros.', 'school'),
(6, 'lector_constante', 'Lector Constante', 'Leíste durante 3 meses consecutivos.', 'calendar_month'),
(7, 'explorador', 'Explorador', 'Leíste libros de 3 categorías.', 'explore'),
(8, 'polimata', 'Polímata', 'Leíste libros de 5 categorías.', 'psychology'),
(9, 'meta_cumplida', 'Meta Cumplida', 'Completaste una meta personal.', 'flag'),
(10, 'cumplidor_de_metas', 'Cumplidor de Metas', 'Completaste 3 metas personales.', 'military_tech'),
(11, 'devolutor_puntual', 'Devolutor Puntual', 'Devolviste 5 libros a tiempo.', 'schedule'),
(12, 'super_puntual', 'Súper Puntual', 'Devolviste 15 libros a tiempo.', 'verified'),
(13, 'veloz', 'Veloz', 'Devolviste 3 libros en un mismo mes.', 'bolt'),
(14, 'maratonista', 'Maratonista', 'Devolviste 5 libros en un mismo mes.', 'directions_run'),
(15, 'cientifico', 'Científico', 'Leíste 5 libros de Ciencias.', 'science'),
(16, 'literato', 'Literato', 'Leíste 5 libros de Literatura.', 'menu_book'),
(17, 'historiador', 'Historiador', 'Leíste 5 libros de Historia.', 'history_edu'),
(18, 'filosofo', 'Filósofo', 'Leíste 5 libros de Filosofía.', 'lightbulb');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--
-- Creación: 26-09-2026 a las 21:04:08
--

CREATE TABLE `libros` (
  `id_libro` int(11) NOT NULL,
  `id_editorial` int(11) DEFAULT NULL,
  `id_materia` int(11) DEFAULT NULL,
  `id_tipo_material` int(11) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `titulo` varchar(50) DEFAULT NULL,
  `edicion` varchar(30) DEFAULT NULL,
  `ciudad` varchar(30) DEFAULT NULL,
  `publicacion_year` year(4) DEFAULT NULL,
  `serie` varchar(20) DEFAULT NULL,
  `volumen` int(8) DEFAULT NULL,
  `portada` varchar(120) DEFAULT NULL,
  `sinopsis` text DEFAULT NULL,
  `idioma` int(11) DEFAULT NULL,
  `numero_paginas` int(11) DEFAULT NULL,
  `fecha_registro_libro` datetime DEFAULT NULL,
  `fecha_eliminacion_libro` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libro_autor`
--
-- Creación: 26-09-2026 a las 23:59:49
--

CREATE TABLE `libro_autor` (
  `id_detalle` int(11) NOT NULL,
  `id_libro` int(11) DEFAULT NULL,
  `id_autor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lista_deseos`
--
-- Creación: 12-10-2026 a las 19:40:51
-- Última actualización: 12-10-2026 a las 19:51:50
--

CREATE TABLE `lista_deseos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--
-- Creación: 18-09-2026 a las 05:15:51
--

CREATE TABLE `materias` (
  `id_materia` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `id_dewey` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metas`
--
-- Creación: 12-10-2026 a las 19:40:51
-- Última actualización: 12-10-2026 a las 19:51:22
--

CREATE TABLE `metas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `cantidad_objetivo` int(11) NOT NULL,
  `cantidad_actual` int(11) NOT NULL DEFAULT 0,
  `fecha_limite` date DEFAULT NULL,
  `completada` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_docente`
--
-- Creación: 12-10-2026 a las 19:40:51
-- Última actualización: 12-10-2026 a las 20:36:00
--

CREATE TABLE `notas_docente` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `contenido` text NOT NULL,
  `fecha_recordatorio` date DEFAULT NULL,
  `es_importante` tinyint(1) NOT NULL DEFAULT 0,
  `recordatorio_enviado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--
-- Creación: 07-10-2026 a las 21:18:44
-- Última actualización: 12-10-2026 a las 18:45:56
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `mensaje` varchar(255) DEFAULT NULL,
  `url_accion` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion_usuario`
--
-- Creación: 07-10-2026 a las 21:18:21
-- Última actualización: 12-10-2026 a las 19:55:18
--

CREATE TABLE `notificacion_usuario` (
  `id_detalle` int(11) NOT NULL,
  `id_notificacion` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--
-- Creación: 27-09-2026 a las 01:04:38
--

CREATE TABLE `prestamos` (
  `id_prestamo` int(11) NOT NULL,
  `id_ejemplar` int(11) DEFAULT NULL,
  `id_reserva` int(11) DEFAULT NULL,
  `fecha_prestamo` date DEFAULT NULL,
  `fecha_devolucion_prevista` date DEFAULT NULL,
  `fecha_devolucion_real` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `observaciones` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rate_limits`
--
-- Creación: 10-10-2026 a las 17:22:16
-- Última actualización: 12-10-2026 a las 20:40:31
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 1,
  `reset_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--
-- Creación: 11-10-2026 a las 00:43:24
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `fk_id_usuario_reserva` int(11) DEFAULT NULL,
  `fk_id_libro_reserva` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `fecha_reserva` date DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `fecha_devolucion_estimada` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `observacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--
-- Creación: 30-08-2026 a las 20:39:24
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`) VALUES
(1, 'estudiante'),
(2, 'profesor'),
(3, 'bibliotecario'),
(4, 'administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_cambio`
--
-- Creación: 12-10-2026 a las 00:52:30
-- Última actualización: 12-10-2026 a las 19:01:45
--

CREATE TABLE `solicitudes_cambio` (
  `id_solicitud` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('email','nombre','documento','eliminacion') NOT NULL,
  `valor_actual` varchar(255) DEFAULT NULL,
  `valor_nuevo` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_resolucion` datetime DEFAULT NULL,
  `id_admin_resuelve` int(11) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `archivo_nuevo` varchar(512) DEFAULT NULL,
  `archivo_mime` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_materiales`
--
-- Creación: 18-09-2026 a las 05:14:44
--

CREATE TABLE `tipos_materiales` (
  `id_tipo_material` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--
-- Creación: 11-10-2026 a las 21:16:15
-- Última actualización: 12-10-2026 a las 19:02:45
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `nombre_apellido` varchar(100) DEFAULT NULL,
  `nombre_usuario` varchar(30) DEFAULT NULL,
  `nombre_usuario_anterior` varchar(30) DEFAULT NULL,
  `fecha_liberacion_nombre` datetime DEFAULT NULL,
  `correo_institucional` varchar(60) DEFAULT NULL,
  `documento` varchar(25) DEFAULT NULL,
  `foto_documento` varchar(512) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `foto_perfil` varchar(50) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `estado` varchar(10) DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `ultimo_cambio_nombre` datetime DEFAULT NULL,
  `remember_token` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_insignias`
--
-- Creación: 12-10-2026 a las 19:40:51
--

CREATE TABLE `usuario_insignias` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_insignia` int(11) NOT NULL,
  `fecha_obtencion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id_autor`);

--
-- Indices de la tabla `bibliotecas`
--
ALTER TABLE `bibliotecas`
  ADD PRIMARY KEY (`id_biblioteca`);

--
-- Indices de la tabla `colecciones`
--
ALTER TABLE `colecciones`
  ADD PRIMARY KEY (`id_coleccion`),
  ADD KEY `id_biblioteca` (`id_biblioteca`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id_comentario`);

--
-- Indices de la tabla `dewey`
--
ALTER TABLE `dewey`
  ADD PRIMARY KEY (`id_dewey`);

--
-- Indices de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  ADD PRIMARY KEY (`id_editorial`);

--
-- Indices de la tabla `ejemplares`
--
ALTER TABLE `ejemplares`
  ADD PRIMARY KEY (`id_ejemplar`),
  ADD KEY `fk_id_libro_ejemplar` (`fk_id_libro_ejemplar`),
  ADD KEY `id_coleccion` (`id_coleccion`);

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `idioma`
--
ALTER TABLE `idioma`
  ADD PRIMARY KEY (`id_idioma`);

--
-- Indices de la tabla `insignias`
--
ALTER TABLE `insignias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_insignias_codigo` (`codigo`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id_libro`),
  ADD KEY `id_editorial` (`id_editorial`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_tipo_material` (`id_tipo_material`),
  ADD KEY `id_idioma_libro` (`idioma`);

--
-- Indices de la tabla `libro_autor`
--
ALTER TABLE `libro_autor`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_libro` (`id_libro`),
  ADD KEY `id_autor` (`id_autor`);

--
-- Indices de la tabla `lista_deseos`
--
ALTER TABLE `lista_deseos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_lista_deseos_usuario_libro` (`id_usuario`,`id_libro`),
  ADD KEY `idx_lista_deseos_libro` (`id_libro`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id_materia`),
  ADD KEY `id_dewey` (`id_dewey`);

--
-- Indices de la tabla `metas`
--
ALTER TABLE `metas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_metas_usuario` (`id_usuario`,`completada`);

--
-- Indices de la tabla `notas_docente`
--
ALTER TABLE `notas_docente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notas_recordatorios` (`fecha_recordatorio`,`recordatorio_enviado`),
  ADD KEY `idx_notas_usuario` (`id_usuario`,`es_importante`,`fecha_creacion`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`);

--
-- Indices de la tabla `notificacion_usuario`
--
ALTER TABLE `notificacion_usuario`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_notificacion` (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id_prestamo`),
  ADD KEY `id_ejemplar` (`id_ejemplar`),
  ADD KEY `id_reserva` (`id_reserva`);

--
-- Indices de la tabla `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifier` (`identifier`),
  ADD KEY `idx_reset_time` (`reset_time`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `fk_id_usuario_reserva` (`fk_id_usuario_reserva`),
  ADD KEY `fk_id_libro_reserva` (`fk_id_libro_reserva`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `solicitudes_cambio`
--
ALTER TABLE `solicitudes_cambio`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `fk_solicitud_admin` (`id_admin_resuelve`);

--
-- Indices de la tabla `tipos_materiales`
--
ALTER TABLE `tipos_materiales`
  ADD PRIMARY KEY (`id_tipo_material`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`) USING BTREE,
  ADD UNIQUE KEY `uq_usuarios_correo` (`correo_institucional`),
  ADD UNIQUE KEY `uq_usuarios_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `uq_usuarios_documento` (`documento`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `usuario_insignias`
--
ALTER TABLE `usuario_insignias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_insignia` (`id_usuario`,`id_insignia`),
  ADD KEY `fk_usuario_insignias_insignia` (`id_insignia`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autores`
--
ALTER TABLE `autores`
  MODIFY `id_autor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bibliotecas`
--
ALTER TABLE `bibliotecas`
  MODIFY `id_biblioteca` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `colecciones`
--
ALTER TABLE `colecciones`
  MODIFY `id_coleccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `dewey`
--
ALTER TABLE `dewey`
  MODIFY `id_dewey` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  MODIFY `id_editorial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ejemplares`
--
ALTER TABLE `ejemplares`
  MODIFY `id_ejemplar` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `idioma`
--
ALTER TABLE `idioma`
  MODIFY `id_idioma` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `insignias`
--
ALTER TABLE `insignias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id_libro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `libro_autor`
--
ALTER TABLE `libro_autor`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lista_deseos`
--
ALTER TABLE `lista_deseos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `metas`
--
ALTER TABLE `metas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas_docente`
--
ALTER TABLE `notas_docente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificacion_usuario`
--
ALTER TABLE `notificacion_usuario`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id_prestamo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `solicitudes_cambio`
--
ALTER TABLE `solicitudes_cambio`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipos_materiales`
--
ALTER TABLE `tipos_materiales`
  MODIFY `id_tipo_material` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario_insignias`
--
ALTER TABLE `usuario_insignias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `colecciones`
--
ALTER TABLE `colecciones`
  ADD CONSTRAINT `id_biblioteca` FOREIGN KEY (`id_biblioteca`) REFERENCES `bibliotecas` (`id_biblioteca`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `ejemplares`
--
ALTER TABLE `ejemplares`
  ADD CONSTRAINT `fk_id_libro_ejemplar` FOREIGN KEY (`fk_id_libro_ejemplar`) REFERENCES `libros` (`id_libro`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `id_coleccion` FOREIGN KEY (`id_coleccion`) REFERENCES `colecciones` (`id_coleccion`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `id_editorial` FOREIGN KEY (`id_editorial`) REFERENCES `editoriales` (`id_editorial`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `id_idioma_libro` FOREIGN KEY (`idioma`) REFERENCES `idioma` (`id_idioma`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `id_materia` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `id_tipo_material` FOREIGN KEY (`id_tipo_material`) REFERENCES `tipos_materiales` (`id_tipo_material`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `libro_autor`
--
ALTER TABLE `libro_autor`
  ADD CONSTRAINT `id_autor` FOREIGN KEY (`id_autor`) REFERENCES `autores` (`id_autor`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `id_libro` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `lista_deseos`
--
ALTER TABLE `lista_deseos`
  ADD CONSTRAINT `fk_lista_deseos_libro` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lista_deseos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `materias`
--
ALTER TABLE `materias`
  ADD CONSTRAINT `id_dewey` FOREIGN KEY (`id_dewey`) REFERENCES `dewey` (`id_dewey`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `metas`
--
ALTER TABLE `metas`
  ADD CONSTRAINT `fk_metas_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notas_docente`
--
ALTER TABLE `notas_docente`
  ADD CONSTRAINT `fk_notas_docente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificacion_usuario`
--
ALTER TABLE `notificacion_usuario`
  ADD CONSTRAINT `fk_notif_usuario_notif` FOREIGN KEY (`id_notificacion`) REFERENCES `notificaciones` (`id_notificacion`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_notif_usuario_user` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `id_ejemplar` FOREIGN KEY (`id_ejemplar`) REFERENCES `ejemplares` (`id_ejemplar`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `id_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id_reserva`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_id_libro_reserva` FOREIGN KEY (`fk_id_libro_reserva`) REFERENCES `libros` (`id_libro`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_id_usuario_reserva` FOREIGN KEY (`fk_id_usuario_reserva`) REFERENCES `usuarios` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `solicitudes_cambio`
--
ALTER TABLE `solicitudes_cambio`
  ADD CONSTRAINT `fk_solicitud_admin` FOREIGN KEY (`id_admin_resuelve`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_solicitud_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `id_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `usuario_insignias`
--
ALTER TABLE `usuario_insignias`
  ADD CONSTRAINT `fk_usuario_insignias_insignia` FOREIGN KEY (`id_insignia`) REFERENCES `insignias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuario_insignias_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
