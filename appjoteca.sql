-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 11-10-2026 a las 06:44:00
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
-- Última actualización: 10-10-2026 a las 23:57:50
--

CREATE TABLE `autores` (
  `id_autor` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `autores`
--

INSERT INTO `autores` (`id_autor`, `nombre`) VALUES
(1, 'Gabriel García Márquez'),
(2, 'Karen M. McManus'),
(3, 'Miguel de Cervantes');

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
-- Volcado de datos para la tabla `colecciones`
--

INSERT INTO `colecciones` (`id_coleccion`, `nombre`, `id_biblioteca`) VALUES
(2, 'Colección General', 1);

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

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id_comentario`, `nombre`, `tipo_comentario`, `comentario`, `correo`) VALUES
(2, 'Simoncito', 'problema', 'Las reservaciones están muy lentas', 'simon.montoya@iemanueljbetancur.edu.co');

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
-- Volcado de datos para la tabla `dewey`
--

INSERT INTO `dewey` (`id_dewey`, `nombre`, `codigo`) VALUES
(1, 'Lengua', '400');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editoriales`
--
-- Creación: 26-09-2026 a las 23:56:41
-- Última actualización: 10-10-2026 a las 23:57:50
--

CREATE TABLE `editoriales` (
  `id_editorial` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `editoriales`
--

INSERT INTO `editoriales` (`id_editorial`, `nombre`) VALUES
(1, 'Penguin'),
(2, 'Matle'),
(3, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejemplares`
--
-- Creación: 30-08-2026 a las 23:39:15
-- Última actualización: 11-10-2026 a las 01:34:15
--

CREATE TABLE `ejemplares` (
  `id_ejemplar` int(11) NOT NULL,
  `fk_id_libro_ejemplar` int(11) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `id_coleccion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `ejemplares`
--

INSERT INTO `ejemplares` (`id_ejemplar`, `fk_id_libro_ejemplar`, `estado`, `id_coleccion`) VALUES
(1, 5, 'Disponible', 2),
(2, 5, 'Disponible', 2),
(3, 6, 'Prestado', 2),
(4, 6, 'Prestado', 2),
(5, 6, 'Disponible', 2),
(6, 6, 'Prestado', 2),
(7, 6, 'Prestado', 2),
(8, 7, 'Disponible', 2),
(9, 7, 'Disponible', 2),
(10, 7, 'Disponible', 2),
(11, 7, 'Disponible', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--
-- Creación: 21-09-2026 a las 03:10:47
-- Última actualización: 11-10-2026 a las 01:34:15
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
-- Volcado de datos para la tabla `historial`
--

INSERT INTO `historial` (`id_historial`, `id_usuario`, `tipo`, `accion`, `detalle`, `referencia`, `fecha`) VALUES
(1, 5, 'reserva', 'Solicitud de reserva', 'Reserva #1 · Libro #5', NULL, '2026-10-07 18:40:11'),
(2, 5, 'reserva', 'Reserva aceptada', 'Reserva #1 · Préstamo #1 generado', NULL, '2026-10-07 18:43:21'),
(3, 5, 'devolucion', 'Devolución registrada', 'Préstamo #1 · El amor en los tiempos del cólera', NULL, '2026-10-07 18:49:23'),
(4, 5, 'reserva', 'Solicitud de reserva', 'Reserva #2 · Libro #5', NULL, '2026-10-07 19:19:51'),
(5, 5, 'reserva', 'Reserva rechazada', 'Reserva #2 · Rechazada por bibliotecario', NULL, '2026-10-07 19:25:24'),
(6, 5, 'reserva', 'Solicitud de reserva', 'Reserva #3 · «El amor en los tiempos del cólera» · Cantidad: 2', NULL, '2026-10-10 18:26:56'),
(7, 5, 'reserva', 'Reserva aceptada', 'Reserva #3 · «El amor en los tiempos del cólera» · 2 ejemplares · Préstamo(s) #2, #3', NULL, '2026-10-10 18:30:34'),
(8, 5, 'devolucion', 'Devolución registrada', 'Préstamo #2 · El amor en los tiempos del cólera', NULL, '2026-10-10 18:33:45'),
(9, 5, 'devolucion', 'Devolución registrada', 'Préstamo #3 · El amor en los tiempos del cólera', NULL, '2026-10-10 18:33:47'),
(10, 5, 'reserva', 'Solicitud de reserva', 'Reserva #4 · «One of us is lying» · Cantidad: 4', NULL, '2026-10-10 18:49:08'),
(11, 5, 'reserva', 'Reserva aceptada', 'Reserva #4 · «One of us is lying» · 4 ejemplares · Préstamo(s) #4, #5, #6, #7', NULL, '2026-10-10 18:50:04'),
(12, 5, 'devolucion', 'Devoluciones registradas', '4 ejemplares · Préstamo(s) #4, #5, #6, #7 · One of us is lying', NULL, '2026-10-10 18:50:31'),
(13, 5, 'reserva', 'Solicitud de reserva', 'Reserva #5 · «One of us is lying» · Cantidad: 3', NULL, '2026-10-10 19:01:11'),
(14, 5, 'reserva', 'Reserva aceptada', 'Reserva #5 · «One of us is lying» · 3 ejemplares · Préstamo(s) #8, #9, #10', NULL, '2026-10-10 19:01:59'),
(15, 5, 'reserva', 'Solicitud de reserva', 'Reserva #6 · «El ingenioso hidalgo Don Quijote de la Mancha» · Cantidad: 3', NULL, '2026-10-10 19:21:02'),
(16, 5, 'reserva', 'Reserva aceptada', 'Reserva #6 · «El ingenioso hidalgo Don Quijote de la Mancha» · 3 ejemplares · Préstamo(s) #11, #12, #13', NULL, '2026-10-10 19:23:20'),
(17, 5, 'reserva', 'Solicitud de reserva', 'Reserva #7 · «One of us is lying» · Cantidad: 2', NULL, '2026-10-10 19:46:58'),
(18, 5, 'reserva', 'Reserva cancelada', 'Reserva #7 cancelada por el usuario', NULL, '2026-10-10 19:47:25'),
(19, 5, 'reserva', 'Solicitud de reserva', 'Reserva #8 · «One of us is lying» · Cantidad: 2', NULL, '2026-10-10 19:48:00'),
(20, 5, 'reserva', 'Reserva aceptada', 'Reserva #8 · «One of us is lying» · 2 ejemplares · Préstamo(s) #14, #15', NULL, '2026-10-10 19:49:21'),
(21, 5, 'devolucion', 'Devoluciones registradas', '3 ejemplares · Préstamo(s) #11, #12, #13 · El ingenioso hidalgo Don Quijote de la Mancha', NULL, '2026-10-10 19:50:06'),
(22, 5, 'devolucion', 'Devoluciones registradas', '3 ejemplares · Préstamo(s) #8, #9, #10 · One of us is lying', NULL, '2026-10-10 19:50:11'),
(23, 5, 'reserva', 'Solicitud de reserva', 'Reserva #9 · «One of us is lying» · Cantidad: 1', NULL, '2026-10-10 19:51:40'),
(24, 5, 'reserva', 'Reserva cancelada', 'Reserva #9 cancelada por el usuario', NULL, '2026-10-10 19:56:24'),
(25, 5, 'reserva', 'Solicitud de reserva', 'Reserva #11 · «One of us is lying» · Cantidad: 2', NULL, '2026-10-10 20:05:28'),
(26, 5, 'reserva', 'Reserva cancelada', 'Reserva #11 cancelada por el usuario', NULL, '2026-10-10 20:05:43'),
(27, 5, 'reserva', 'Solicitud de reserva', 'Reserva #12 · «One of us is lying» · Cantidad: 2', NULL, '2026-10-10 20:05:53'),
(28, 5, 'reserva', 'Reserva aceptada', 'Reserva #12 · «One of us is lying» · 2 ejemplares · Préstamo(s) #16, #17', NULL, '2026-10-10 20:34:15');

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

--
-- Volcado de datos para la tabla `idioma`
--

INSERT INTO `idioma` (`id_idioma`, `nombre_idioma`) VALUES
(1, 'Español');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--
-- Creación: 26-09-2026 a las 21:04:08
-- Última actualización: 10-10-2026 a las 23:58:11
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

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id_libro`, `id_editorial`, `id_materia`, `id_tipo_material`, `isbn`, `titulo`, `edicion`, `ciudad`, `publicacion_year`, `serie`, `volumen`, `portada`, `sinopsis`, `idioma`, `numero_paginas`, `fecha_registro_libro`, `fecha_eliminacion_libro`) VALUES
(1, NULL, NULL, NULL, '1142424', 'Cien años de soledad', 'liter', 'Medellín', '2026', '1', 2, NULL, NULL, NULL, NULL, NULL, '2026-09-26 19:06:35'),
(2, NULL, NULL, NULL, '1313141', 'Veinte años sin tí', 'po', 'medellín', '2005', '2', 3, NULL, NULL, NULL, NULL, NULL, '2026-09-26 19:06:42'),
(5, 1, 2, 1, '93573987593', 'El amor en los tiempos del cólera', '1', 'Medellín', '2009', '1', 1, 'uploads/books/portadas/portada_6ab85e9bd6b3e9.86719671.jpg', 'Era inevitable el olor de las almendras amargas le recordaba siempre el destino de los amores contrarios. El doctor Juvenal Urbino lo percibió desde que entro en la casa todavía en penumbras, adonde había acudido de urgencia a ocuparse de un caso que para el había dejado de ser urgente desde hacia hace muchos años.', 1, 520, '2026-09-26 19:08:59', NULL),
(6, NULL, 2, 1, '353443546464', 'One of us is lying', '1', 'Uniate', '2023', '1', 2, 'uploads/books/portadas/portada_3836109317c910a2efd706fb77599f61.jpg', '#1 NEW YORK TIMES BESTSELLER • NAMED ONE OF THE TEN BEST BOOKS OF THE YEAR BY ENTERTAINMENT WEEKLY • BUZZFEED • POPCRUSH “Pretty Little Liars meets The Breakfast Club” (Entertainment Weekly) in this addictive mystery about what happens when five strangers walk into detention and only four walk out alive. Pay close attention and you might solve this. On Monday afternoon, five students at Bayview High walk into detention. Bronwyn, the brain, is Yale-bound and never breaks a rule. Addy, the beauty, is the picture-perfect homecoming princess. Nate, the criminal, is already on probation for dealing. Cooper, the athlete, is the all-star baseball pitcher. And Simon, the outcast, is the creator of Bayview High’s notorious gossip app. Only, Simon never makes it out of that classroom. Before the end of detention Simon\'s dead. And according to investigators, his death wasn’t an accident. On Monday, he died. But on Tuesday, he’d planned to post juicy reveals about all four of his high-profile classmates, which makes all four of them suspects in his murder. Or are they the perfect patsies for a killer who’s still on the loose? Everyone has secrets, right? What really matters is how far you would go to protect them. All the secrets of the Bayview Four will be revealed in the TV series now streaming on NBC\'s Peacock! And don’t miss the #1 New York Times bestselling sequel, One of Us is Next!', 1, 310, '2026-10-10 13:43:01', NULL),
(7, NULL, 1, 1, '2424242424', 'El ingenioso hidalgo Don Quijote de la Mancha', '1', 'Madrid', '1975', '2', 3, 'uploads/books/portadas/portada_44f8dbc375f3c534cf151d747d855599.jpg', 'La novela más loca, divertida y humana de todos los tiempos narra las aventuras de Alonso Quijano, un hidalgo de mediana edad que, tras leer demasiados libros de caballería, pierde la razón y decide convertirse en el caballero andante Don Quijote de la Mancha. Junto a su escudero Sancho Panza, emprende salidas en busca de aventuras, confundiéndolas con la realidad (como molinos de viento que cree que son gigantes) y buscando siempre defender los ideales de caballería en un mundo que ya no los comprende. La obra es una parodia de los libros de caballerías de la época y explora temas como la locura, la realidad superada por la imaginación y la crítica social de la España de la época. La presente edición incluye, -al final de cada parte para no entorpecer la lectura-, las mejores notas de los grandes cervantistas como Clemencín, Rodríguez Marín, Schevill, Mendizábal, Martín de Riquer o Luis Andrés Murillo.', 1, 320, '2026-10-10 18:57:50', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libro_autor`
--
-- Creación: 26-09-2026 a las 23:59:49
-- Última actualización: 10-10-2026 a las 23:58:11
--

CREATE TABLE `libro_autor` (
  `id_detalle` int(11) NOT NULL,
  `id_libro` int(11) DEFAULT NULL,
  `id_autor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `libro_autor`
--

INSERT INTO `libro_autor` (`id_detalle`, `id_libro`, `id_autor`) VALUES
(3, 5, 1),
(9, 6, 2),
(12, 7, 3);

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
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id_materia`, `nombre`, `id_dewey`) VALUES
(1, 'Lenguaje y Linguística', 1),
(2, 'Lenguaje y lengua', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--
-- Creación: 07-10-2026 a las 21:18:44
-- Última actualización: 11-10-2026 a las 01:34:15
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `mensaje` varchar(255) DEFAULT NULL,
  `url_accion` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `mensaje`, `url_accion`, `fecha_creacion`) VALUES
(1, 'Nueva solicitud de cuenta: Matías Ruiz López (matias.ruiz). Pendiente de aprobación.', '/Appjoteca/dashboards/Administrador/index.php', '2026-10-07 17:12:51'),
(2, 'Nueva solicitud de cuenta: sianuro (sianuroa.adwew). Pendiente de aprobación.', '/Appjoteca/dashboards/Administrador/index.php', '2026-10-07 18:30:32'),
(3, 'Nueva solicitud de reserva #1', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-07 18:40:11'),
(4, 'Tu reserva #1 fue aceptada. Ya puedes reclamar el libro.', '/Appjoteca/pages/history/historial.php', '2026-10-07 18:43:21'),
(5, 'Tu préstamo del libro «El amor en los tiempos del cólera» fue marcado como devuelto.', '/Appjoteca/pages/history/historial.php', '2026-10-07 18:49:23'),
(6, 'Nueva solicitud de reserva #2', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-07 19:19:51'),
(7, 'Tu reserva #2 fue rechazada.', '/Appjoteca/pages/history/historial.php', '2026-10-07 19:25:24'),
(8, 'Nueva solicitud de reserva #3 · «El amor en los tiempos del cólera» (2 ejemplares)', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-10 18:26:56'),
(9, 'Tu reserva #3 del libro «El amor en los tiempos del cólera» fue aceptada (2 ejemplares). Ya puedes reclamarlo.', '/Appjoteca/pages/history/historial.php', '2026-10-10 18:30:34'),
(10, 'Tu préstamo del libro «El amor en los tiempos del cólera» fue marcado como devuelto.', '/Appjoteca/pages/history/historial.php', '2026-10-10 18:33:45'),
(11, 'Tu préstamo del libro «El amor en los tiempos del cólera» fue marcado como devuelto.', '/Appjoteca/pages/history/historial.php', '2026-10-10 18:33:47'),
(12, 'Nueva solicitud de reserva #4 · «One of us is lying» (4 ejemplares)', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-10 18:49:08'),
(13, 'Tu reserva #4 del libro «One of us is lying» fue aceptada (4 ejemplares). Ya puedes reclamarlo.', '/Appjoteca/pages/history/historial.php', '2026-10-10 18:50:04'),
(14, 'Se registró la devolución de 4 ejemplares del libro «One of us is lying».', '/Appjoteca/pages/history/historial.php', '2026-10-10 18:50:31'),
(15, 'Nueva solicitud de reserva #5 · «One of us is lying» (3 ejemplares)', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-10 19:01:11'),
(16, 'Tu reserva #5 del libro «One of us is lying» fue aceptada (3 ejemplares). Ya puedes reclamarlo.', '/Appjoteca/pages/history/historial.php', '2026-10-10 19:01:59'),
(17, 'Nueva solicitud de reserva #6 · «El ingenioso hidalgo Don Quijote de la Mancha» (3 ejemplares)', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-10 19:21:02'),
(18, 'Tu reserva #6 del libro «El ingenioso hidalgo Don Quijote de la Mancha» fue aceptada (3 ejemplares). Ya puedes reclamarlo.', '/Appjoteca/pages/history/historial.php', '2026-10-10 19:23:20'),
(19, 'Nueva solicitud de reserva #7 · «One of us is lying» (2 ejemplares)', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-10 19:46:58'),
(20, 'Nueva solicitud de reserva #8 · «One of us is lying» (2 ejemplares)', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-10 19:48:00'),
(21, 'Tu reserva #8 del libro «One of us is lying» fue aceptada (2 ejemplares). Ya puedes reclamarlo.', '/Appjoteca/pages/history/historial.php', '2026-10-10 19:49:21'),
(22, 'Se registró la devolución de 3 ejemplares del libro «El ingenioso hidalgo Don Quijote de la Mancha».', '/Appjoteca/pages/history/historial.php', '2026-10-10 19:50:06'),
(23, 'Se registró la devolución de 3 ejemplares del libro «One of us is lying».', '/Appjoteca/pages/history/historial.php', '2026-10-10 19:50:11'),
(24, 'Nueva solicitud de reserva #9 · «One of us is lying» (1 ejemplar)', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-10 19:51:40'),
(25, 'Nueva solicitud de reserva #11 · «One of us is lying» (2 ejemplares)', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-10 20:05:28'),
(26, 'Nueva solicitud de reserva #12 · «One of us is lying» (2 ejemplares)', '/Appjoteca/dashboards/bibliotecario/pages/reservaciones/reservaciones.php', '2026-10-10 20:05:53'),
(27, 'Tu reserva #12 del libro «One of us is lying» fue aceptada (2 ejemplares). Ya puedes reclamarlo.', '/Appjoteca/pages/history/historial.php', '2026-10-10 20:34:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion_usuario`
--
-- Creación: 07-10-2026 a las 21:18:21
-- Última actualización: 11-10-2026 a las 01:36:13
--

CREATE TABLE `notificacion_usuario` (
  `id_detalle` int(11) NOT NULL,
  `id_notificacion` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `notificacion_usuario`
--

INSERT INTO `notificacion_usuario` (`id_detalle`, `id_notificacion`, `id_usuario`, `leida`) VALUES
(3, 2, 12, 1),
(6, 5, 5, 1),
(7, 6, 7, 1),
(8, 7, 5, 1),
(9, 8, 7, 1),
(10, 9, 5, 0),
(11, 10, 5, 0),
(12, 11, 5, 0),
(13, 12, 7, 1),
(14, 13, 5, 0),
(15, 14, 5, 0),
(16, 15, 7, 1),
(17, 16, 5, 0),
(18, 17, 7, 1),
(19, 18, 5, 0),
(20, 19, 7, 1),
(21, 20, 7, 1),
(22, 21, 5, 0),
(23, 22, 5, 0),
(24, 23, 5, 0),
(25, 24, 7, 1),
(26, 25, 7, 1),
(27, 26, 7, 1),
(28, 27, 5, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--
-- Creación: 27-09-2026 a las 01:04:38
-- Última actualización: 11-10-2026 a las 01:34:15
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
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id_prestamo`, `id_ejemplar`, `id_reserva`, `fecha_prestamo`, `fecha_devolucion_prevista`, `fecha_devolucion_real`, `estado`, `observaciones`) VALUES
(1, 1, 1, '2026-10-07', '2026-10-22', '2026-10-07', 'devuelto', NULL),
(2, 1, 3, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(3, 2, 3, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(4, 3, 4, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(5, 4, 4, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(6, 5, 4, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(7, 6, 4, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(8, 3, 5, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(9, 4, 5, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(10, 5, 5, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(11, 8, 6, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(12, 9, 6, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(13, 10, 6, '2026-10-10', '2026-10-25', '2026-10-10', 'devuelto', NULL),
(14, 6, 8, '2026-10-10', '2026-10-23', NULL, 'activo', NULL),
(15, 7, 8, '2026-10-10', '2026-10-23', NULL, 'activo', NULL),
(16, 3, 12, '2026-10-10', '2026-10-21', NULL, 'activo', NULL),
(17, 4, 12, '2026-10-10', '2026-10-21', NULL, 'activo', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rate_limits`
--
-- Creación: 10-10-2026 a las 17:22:16
-- Última actualización: 11-10-2026 a las 02:06:10
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 1,
  `reset_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `identifier`, `attempts`, `reset_time`) VALUES
(50, 'login:127.0.0.1', 2, 1791685182);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--
-- Creación: 11-10-2026 a las 00:43:24
-- Última actualización: 11-10-2026 a las 01:34:15
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

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id_reserva`, `fk_id_usuario_reserva`, `fk_id_libro_reserva`, `cantidad`, `fecha_reserva`, `fecha_limite`, `fecha_devolucion_estimada`, `estado`, `observacion`) VALUES
(1, 5, 5, 1, '2026-10-07', '2026-10-09', NULL, 'aceptada', 'Hoasldla'),
(2, 5, 5, 1, '2026-10-07', '2026-10-09', NULL, 'rechazada', 'Rechazada por bibliotecario'),
(3, 5, 5, 2, '2026-10-10', '2026-10-12', NULL, 'aceptada', NULL),
(4, 5, 6, 4, '2026-10-10', '2026-10-12', NULL, 'aceptada', 'kkadha'),
(5, 5, 6, 3, '2026-10-10', '2026-10-12', NULL, 'aceptada', 'ljlj'),
(6, 5, 7, 3, '2026-10-10', '2026-10-12', NULL, 'aceptada', '<script src=\"reservacion.php\">'),
(7, 5, 6, 2, '2026-10-10', '2026-10-12', '2026-10-23', 'cancelada', '897'),
(8, 5, 6, 2, '2026-10-10', '2026-10-12', '2026-10-23', 'aceptada', 'mbm'),
(9, 5, 6, 1, '2026-10-10', '2026-10-12', '2026-10-16', 'cancelada', '98'),
(11, 5, 6, 2, '2026-10-10', '2026-10-12', '2026-10-20', 'cancelada', 'ryr'),
(12, 5, 6, 2, '2026-10-10', '2026-10-12', '2026-10-21', 'aceptada', '44');

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
-- Creación: 27-09-2026 a las 18:42:44
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
  `comentario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes_cambio`
--

INSERT INTO `solicitudes_cambio` (`id_solicitud`, `id_usuario`, `tipo`, `valor_actual`, `valor_nuevo`, `estado`, `fecha_solicitud`, `fecha_resolucion`, `id_admin_resuelve`, `comentario`) VALUES
(1, 7, 'nombre', 'simonmontoya', 'Simon Montoya', 'pendiente', '2026-09-27 22:09:02', NULL, NULL, NULL);

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
-- Volcado de datos para la tabla `tipos_materiales`
--

INSERT INTO `tipos_materiales` (`id_tipo_material`, `nombre`) VALUES
(1, 'Libro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--
-- Creación: 27-09-2026 a las 18:43:47
-- Última actualización: 10-10-2026 a las 23:09:09
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

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre_apellido`, `nombre_usuario`, `nombre_usuario_anterior`, `fecha_liberacion_nombre`, `correo_institucional`, `documento`, `foto_documento`, `password`, `foto_perfil`, `biografia`, `estado`, `fecha_registro`, `ultimo_cambio_nombre`, `remember_token`) VALUES
(4, 1, 'Simón Montoya Soto', 'simon.ms', NULL, NULL, 'simon.montoya@iemanueljbetancur.edu.co', '1186463034', NULL, '$2y$10$eBMwcJQfbGzWtqPWl8Eh0O3cPioBvrjb579/pBmeaZ0x8jRi769lq', NULL, NULL, '1', '2026-08-30', NULL, NULL),
(5, 1, 'simoncito', 'simon.mo', NULL, NULL, 'simon.el@iemanueljbetancur.edu.co', '244242424242', NULL, '$2y$10$wWtZKyazb/24Ro/EG71m2OD6SqGhJuYYT1UycYJbYmUddz05GVtai', 'uploads/profiles/photos/profile_5_1788745607.jpg', NULL, NULL, '2026-08-30', '2026-09-06 21:44:13', NULL),
(7, 3, 'simonmontoya', 'simon.bibliotecario', NULL, NULL, 'simon.biblioteca@iemanueljbetancur.edu.co', '11864630345', NULL, '$2y$10$bT14geo/yIKlEE114AAL0e19WAy0pessoyQc.iw.f9.nTvyeQnIni', 'uploads/profiles/photos/profile_7_1791673749.png', NULL, '1', '2026-09-04', NULL, NULL),
(8, 2, 'simon profesor', 'simon.profesor', NULL, NULL, 'simon.profesor@iemanueljbetancur.edu.co', '1187472934', 'uploads/profiles/doc_82034dc7cf179fa01ae324707a3ff48e.pdf', '$2y$10$Fugnc824oVYRylQVb8Xjhu6Jqsqo9nFUeixxOW4sUWnz8y4uOr862', NULL, NULL, '1', '2026-09-11', NULL, NULL),
(10, 1, 'afaasda', 'adad', NULL, NULL, 'simon@manueljbetancur.edu.co', '12947242424242', 'uploads/profiles/documents/doc_9a559adefe006b7034675051afe5ffd6.png', '$2y$10$7EwuK6BqkpoR8bhJceFuIuXU1iatFAhq9C2gZzc8nzliHG9riejZS', NULL, NULL, '1', '2026-09-14', NULL, NULL),
(12, 4, 'simon admin', 'simon.admin', NULL, NULL, 'simon.admin@iemanueljbetancur.edu.co', '11864343434', NULL, '$2y$10$eQKNEGyQu90oV4uRhxF.1eIucesyzRYwy4dZJTOc7QSelSivQpIEe', NULL, NULL, '1', '2026-09-21', NULL, NULL),
(13, NULL, 'Matías Ruiz López', 'matias.ruiz', NULL, NULL, 'matias.ruiz@iemanueljbetancur.edu.co', '24242424242', 'uploads/profiles/documents/doc_c75a7b7078c353cfbbde4f5076fa2a80.pdf', '$2y$10$7el8Eb.rhOdAkzKbXCA3sOEr2Hm3ZiC97YAi5HqHNa7UYSAXCp.qu', NULL, NULL, '1', '2026-10-07', NULL, NULL),
(15, 1, 'sianuro', 'sianuroa.adwew', NULL, NULL, 'sianuro@iemanueljbetancur.edu.co', '1186463032', 'uploads/profiles/documents/doc_5dcf6fbf121e3cc518d4777dbf671ab1.pdf', '$2y$10$.NPl8r.xkj213GrC/tcxm.gRN6dnMND7Ztgyt5BRDoHUD/HvBwAN6', NULL, NULL, 'pendiente', '2026-10-07', NULL, NULL);

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
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autores`
--
ALTER TABLE `autores`
  MODIFY `id_autor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `bibliotecas`
--
ALTER TABLE `bibliotecas`
  MODIFY `id_biblioteca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `colecciones`
--
ALTER TABLE `colecciones`
  MODIFY `id_coleccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `dewey`
--
ALTER TABLE `dewey`
  MODIFY `id_dewey` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  MODIFY `id_editorial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ejemplares`
--
ALTER TABLE `ejemplares`
  MODIFY `id_ejemplar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `idioma`
--
ALTER TABLE `idioma`
  MODIFY `id_idioma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id_libro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `libro_autor`
--
ALTER TABLE `libro_autor`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `notificacion_usuario`
--
ALTER TABLE `notificacion_usuario`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id_prestamo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `solicitudes_cambio`
--
ALTER TABLE `solicitudes_cambio`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipos_materiales`
--
ALTER TABLE `tipos_materiales`
  MODIFY `id_tipo_material` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
