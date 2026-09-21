-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 21-09-2026 a las 19:21:53
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
-- Creación: 30-08-2026 a las 23:33:38
--

CREATE TABLE `autores` (
  `id_autor` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- RELACIONES PARA LA TABLA `autores`:
--

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

--
-- RELACIONES PARA LA TABLA `bibliotecas`:
--

--
-- Volcado de datos para la tabla `bibliotecas`
--

INSERT INTO `bibliotecas` (`id_biblioteca`, `nombre`) VALUES
(1, 'I.E Manuel J. Betancur');

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

--
-- RELACIONES PARA LA TABLA `colecciones`:
--   `id_biblioteca`
--       `bibliotecas` -> `id_biblioteca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--
-- Creación: 14-09-2026 a las 03:52:02
--

CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `tipo_comentario` varchar(50) DEFAULT NULL,
  `comentario` varchar(1000) DEFAULT NULL,
  `correo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- RELACIONES PARA LA TABLA `comentarios`:
--

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

--
-- RELACIONES PARA LA TABLA `dewey`:
--

--
-- Volcado de datos para la tabla `dewey`
--

INSERT INTO `dewey` (`id_dewey`, `nombre`, `codigo`) VALUES
(1, 'Lengua', '400');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editoriales`
--
-- Creación: 30-08-2026 a las 23:20:48
--

CREATE TABLE `editoriales` (
  `id_editorial` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- RELACIONES PARA LA TABLA `editoriales`:
--

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

--
-- RELACIONES PARA LA TABLA `ejemplares`:
--   `fk_id_libro_ejemplar`
--       `libros` -> `id_libro`
--   `id_coleccion`
--       `colecciones` -> `id_coleccion`
--

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

--
-- RELACIONES PARA LA TABLA `historial`:
--   `id_usuario`
--       `usuarios` -> `id_usuario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--
-- Creación: 18-09-2026 a las 19:01:01
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
  `portada` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- RELACIONES PARA LA TABLA `libros`:
--   `id_editorial`
--       `editoriales` -> `id_editorial`
--   `id_materia`
--       `materias` -> `id_materia`
--   `id_tipo_material`
--       `tipos_materiales` -> `id_tipo_material`
--

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id_libro`, `id_editorial`, `id_materia`, `id_tipo_material`, `isbn`, `titulo`, `edicion`, `ciudad`, `publicacion_year`, `serie`, `volumen`, `portada`) VALUES
(1, NULL, NULL, NULL, '1142424', 'Cien años de soledad', 'liter', 'Medellín', '2026', '1', 2, NULL),
(2, NULL, NULL, NULL, '1313141', 'Veinte años sin tí', 'po', 'medellín', '2005', '2', 3, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libro_autor`
--
-- Creación: 30-08-2026 a las 23:34:36
--

CREATE TABLE `libro_autor` (
  `id_detalle` int(11) NOT NULL,
  `id_libro` int(11) DEFAULT NULL,
  `id_autor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- RELACIONES PARA LA TABLA `libro_autor`:
--   `id_autor`
--       `autores` -> `id_autor`
--   `id_libro`
--       `libros` -> `id_libro`
--

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

--
-- RELACIONES PARA LA TABLA `materias`:
--   `id_dewey`
--       `dewey` -> `id_dewey`
--

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id_materia`, `nombre`, `id_dewey`) VALUES
(1, 'Lenguaje y Linguística', 1),
(2, 'Lenguaje y lengua', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--
-- Creación: 21-09-2026 a las 03:10:47
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `mensaje` varchar(255) DEFAULT NULL,
  `url_accion` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- RELACIONES PARA LA TABLA `notificaciones`:
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion_usuario`
--
-- Creación: 21-09-2026 a las 03:10:47
--

CREATE TABLE `notificacion_usuario` (
  `id_detalle` int(11) NOT NULL,
  `id_notificacion` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- RELACIONES PARA LA TABLA `notificacion_usuario`:
--   `id_notificacion`
--       `notificaciones` -> `id_notificacion`
--   `id_usuario`
--       `usuarios` -> `id_usuario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--
-- Creación: 30-08-2026 a las 23:46:40
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

--
-- RELACIONES PARA LA TABLA `prestamos`:
--   `id_ejemplar`
--       `ejemplares` -> `id_ejemplar`
--   `id_reserva`
--       `reservas` -> `id_reserva`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--
-- Creación: 30-08-2026 a las 23:43:42
--

CREATE TABLE `reservas` (
  `id_reserva` int(11) NOT NULL,
  `fk_id_usuario_reserva` int(11) DEFAULT NULL,
  `fk_id_libro_reserva` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `fecha_reserva` date DEFAULT NULL,
  `fecha_limite` date DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `observacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- RELACIONES PARA LA TABLA `reservas`:
--   `fk_id_libro_reserva`
--       `libros` -> `id_libro`
--   `fk_id_usuario_reserva`
--       `usuarios` -> `id_usuario`
--

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
-- RELACIONES PARA LA TABLA `roles`:
--

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
-- Estructura de tabla para la tabla `tipos_materiales`
--
-- Creación: 18-09-2026 a las 05:14:44
--

CREATE TABLE `tipos_materiales` (
  `id_tipo_material` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- RELACIONES PARA LA TABLA `tipos_materiales`:
--

--
-- Volcado de datos para la tabla `tipos_materiales`
--

INSERT INTO `tipos_materiales` (`id_tipo_material`, `nombre`) VALUES
(1, 'Libro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--
-- Creación: 21-09-2026 a las 00:52:42
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `nombre_apellido` varchar(100) DEFAULT NULL,
  `nombre_usuario` varchar(30) DEFAULT NULL,
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

--
-- RELACIONES PARA LA TABLA `usuarios`:
--   `id_rol`
--       `roles` -> `id_rol`
--

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre_apellido`, `nombre_usuario`, `correo_institucional`, `documento`, `foto_documento`, `password`, `foto_perfil`, `biografia`, `estado`, `fecha_registro`, `ultimo_cambio_nombre`, `remember_token`) VALUES
(4, 1, 'Simón Montoya Soto', 'simon.ms', 'simon.montoya@iemanueljbetancur.edu.co', '1186463034', NULL, '$2y$10$eBMwcJQfbGzWtqPWl8Eh0O3cPioBvrjb579/pBmeaZ0x8jRi769lq', NULL, NULL, '1', '2026-08-30', NULL, NULL),
(5, 1, 'simoncito', 'simon.mo', 'simon.el@iemanueljbetancur.edu.co', '244242424242', NULL, '$2y$10$wWtZKyazb/24Ro/EG71m2OD6SqGhJuYYT1UycYJbYmUddz05GVtai', 'uploads/profiles/photos/profile_5_1788745607.jpg', NULL, NULL, '2026-08-30', '2026-09-06 21:44:13', NULL),
(7, 3, 'simonmontoya', 'simon.bibliotecario', 'simon.biblioteca@iemanueljbetancur.edu.co', '11864630345', NULL, '$2y$10$bT14geo/yIKlEE114AAL0e19WAy0pessoyQc.iw.f9.nTvyeQnIni', NULL, NULL, '1', '2026-09-04', NULL, NULL),
(8, 2, 'simon profesor', 'simon.profesor', 'simon.profesor@iemanueljbetancur.edu.co', '1187472934', 'uploads/profiles/doc_82034dc7cf179fa01ae324707a3ff48e.pdf', '$2y$10$Fugnc824oVYRylQVb8Xjhu6Jqsqo9nFUeixxOW4sUWnz8y4uOr862', NULL, NULL, '1', '2026-09-11', NULL, NULL),
(10, 1, 'afaasda', 'adad', 'simon@manueljbetancur.edu.co', '12947242424242', 'uploads/profiles/documents/doc_9a559adefe006b7034675051afe5ffd6.png', '$2y$10$7EwuK6BqkpoR8bhJceFuIuXU1iatFAhq9C2gZzc8nzliHG9riejZS', NULL, NULL, '1', '2026-09-14', NULL, NULL);

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
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id_libro`),
  ADD KEY `id_editorial` (`id_editorial`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_tipo_material` (`id_tipo_material`);

--
-- Indices de la tabla `libro_autor`
--
ALTER TABLE `libro_autor`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_libro` (`id_libro`),
  ADD KEY `id_autor` (`id_autor`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id_materia`),
  ADD KEY `id_dewey` (`id_dewey`);

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
-- Indices de la tabla `tipos_materiales`
--
ALTER TABLE `tipos_materiales`
  ADD PRIMARY KEY (`id_tipo_material`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`) USING BTREE,
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bibliotecas`
--
ALTER TABLE `bibliotecas`
  MODIFY `id_biblioteca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `colecciones`
--
ALTER TABLE `colecciones`
  MODIFY `id_coleccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `dewey`
--
ALTER TABLE `dewey`
  MODIFY `id_dewey` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id_libro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipos_materiales`
--
ALTER TABLE `tipos_materiales`
  MODIFY `id_tipo_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  ADD CONSTRAINT `id_materia` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `id_tipo_material` FOREIGN KEY (`id_tipo_material`) REFERENCES `tipos_materiales` (`id_tipo_material`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `libro_autor`
--
ALTER TABLE `libro_autor`
  ADD CONSTRAINT `id_autor` FOREIGN KEY (`id_autor`) REFERENCES `autores` (`id_autor`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `id_libro` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id_libro`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `materias`
--
ALTER TABLE `materias`
  ADD CONSTRAINT `id_dewey` FOREIGN KEY (`id_dewey`) REFERENCES `dewey` (`id_dewey`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `id_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
