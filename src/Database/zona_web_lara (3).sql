-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-06-2026 a las 03:13:10
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
-- Base de datos: `zona_web_lara`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activos`
--

CREATE TABLE `activos` (
  `id_activo` int(11) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `is_ciber` tinyint(1) DEFAULT 0,
  `activa` tinyint(1) DEFAULT 1,
  `fk_tipo_activo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `activos`
--

INSERT INTO `activos` (`id_activo`, `marca`, `descripcion`, `is_ciber`, `activa`, `fk_tipo_activo`) VALUES
(1, 'HP', 'PC Escritorio HP ProDesk 400 G5', 1, 1, 1),
(2, 'Dell', 'Laptop Dell Latitude 5490', 0, 1, 2),
(3, 'Samsung', 'Monitor Samsung 24\" Curvo', 1, 1, 3),
(4, 'HP', 'Impresora HP LaserJet M404', 0, 1, 4),
(5, 'Dell', 'Servidor Dell PowerEdge T340', 0, 1, 5),
(6, 'TP-Link', 'Router TP-Link Archer C80', 1, 1, 6),
(7, 'Cisco', 'Switch Cisco Catalyst 2960', 1, 1, 7),
(8, 'APC', 'UPS APC Back-UPS 1500VA', 1, 1, 8),
(9, 'Lenovo', 'PC Escritorio Lenovo ThinkCentre', 1, 1, 1),
(10, 'LG', 'Monitor LG 27\" 4K', 0, 1, 3),
(11, 'Alienware', 'PC Gaming Aurora R15 - Intel i9, RTX 4080, 32GB RAM', 1, 1, 9),
(12, 'MSI', 'PC Gaming Infinite S3 - Intel i7, RTX 4060, 16GB RAM', 1, 1, 9),
(13, 'Asus', 'PC Gaming ROG Strix - Intel i7, RTX 4070, 16GB RAM', 1, 1, 9),
(14, 'Apple', 'iMac 24\" M3 - 8-core GPU, 16GB RAM, 512GB SSD', 1, 1, 11),
(15, 'Dell', 'PC Premium XPS 8960 - Intel i9, RTX 4080, 32GB RAM', 1, 1, 11),
(16, 'HP', 'PC Escritorio HP ProDesk 400 G5 - Intel i5, 8GB RAM', 1, 1, 1),
(17, 'Dell', 'PC Escritorio Dell OptiPlex 7020 - Intel i5, 8GB RAM', 1, 1, 1),
(18, 'Lenovo', 'PC Escritorio Lenovo ThinkCentre M720 - Intel i5, 8GB RAM', 1, 1, 1),
(19, 'HP', 'PC Gaming HP Omen 45L - Intel i9, RTX 4080, 32GB RAM', 1, 1, 2),
(20, 'Alienware', 'PC Gaming Aurora R15 - Intel i9, RTX 4080, 32GB RAM', 1, 1, 2),
(21, 'MSI', 'PC Gaming Infinite S3 - Intel i7, RTX 4060, 16GB RAM', 1, 1, 2),
(22, 'Asus', 'PC Gaming ROG Strix - Intel i7, RTX 4070, 16GB RAM', 1, 1, 2),
(23, 'Apple', 'iMac 24\" M3 - 16GB RAM, 512GB SSD', 1, 1, 4),
(24, 'Dell', 'PC Premium XPS 8960 - Intel i9, RTX 4080, 32GB RAM', 1, 1, 4),
(25, 'HP', 'PC Oficina ProDesk 400 G6 - Intel i5, 8GB RAM', 1, 1, 3),
(26, 'Dell', 'PC Oficina OptiPlex 3050 - Intel i3, 4GB RAM', 1, 1, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asesoria`
--

CREATE TABLE `asesoria` (
  `id_asesoria` int(11) NOT NULL,
  `documento` varchar(20) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `fk_cliente_asesoria` int(11) NOT NULL,
  `fk_tipo_asesoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asesoria`
--

INSERT INTO `asesoria` (`id_asesoria`, `documento`, `descripcion`, `fecha`, `fk_cliente_asesoria`, `fk_tipo_asesoria`) VALUES
(1, 'ASES-001', 'Asesoría para instalación de servidor en red local', '2026-06-02', 1, 2),
(2, 'ASES-002', 'Diagnóstico y reparación de PC de escritorio', '2026-06-04', 2, 4),
(3, 'ASES-003', 'Configuración de software contable', '2026-06-07', 3, 3),
(4, 'ASES-004', 'Asesoría para migración a Office 365', '2026-06-09', 4, 3),
(5, 'ASES-005', 'Revisión de equipos para renovación de parque tecnológico', '2026-06-11', 5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre_categoria`) VALUES
(10, 'Accesorios'),
(6, 'Audífonos'),
(13, 'Bisutería'),
(7, 'Componentes Internos'),
(9, 'Impresoras'),
(12, 'Juguetería'),
(1, 'Laptops'),
(3, 'Monitores'),
(5, 'Mouse'),
(11, 'Papelería'),
(2, 'PC de Escritorio'),
(8, 'Redes'),
(4, 'Teclados');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `cedula`, `nombre`, `apellido`, `direccion`, `telefono`, `email`) VALUES
(1, 'V-12345678', 'Carlos', 'González', 'Av. Libertador, Caracas', '0412-1234567', NULL),
(2, 'V-23456789', 'María', 'Rodríguez', 'Calle Sucre, Maracaibo', '0414-2345678', NULL),
(3, 'V-34567890', 'Pedro', 'Martínez', 'Urb. Las Mercedes, Valencia', '0424-3456789', NULL),
(4, 'V-45678901', 'Ana', 'López', 'Av. Bolívar, Barquisimeto', '0416-4567890', NULL),
(5, 'J-56789012', 'Comercial XYZ, C.A.', 'S/N', 'Zona Industrial, San Cristóbal', '0276-5678901', NULL),
(6, 'V-67890123', 'Luis', 'Pérez', 'Calle 5, Mérida', '0412-6789012', NULL),
(7, 'V-78901234', 'Sofía', 'Díaz', 'Av. Principal, Puerto Ordaz', '0414-7890123', NULL),
(8, 'J-89012345', 'Inversiones ABC, C.A.', 'S/N', 'Centro Empresarial, Los Teques', '0212-8901234', NULL),
(9, 'E-90123456', 'Roberto', 'Sánchez', 'Calle 10, San Felipe', '0426-9012345', NULL),
(10, 'V-01234567', 'Daniela', 'Torres', 'Urb. El Paraíso, Maracay', '0412-0123456', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_asesoria`
--

CREATE TABLE `cliente_asesoria` (
  `id_cliente_asesoria` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cliente_asesoria`
--

INSERT INTO `cliente_asesoria` (`id_cliente_asesoria`, `cedula`, `nombre`, `apellido`, `direccion`, `telefono`, `email`) VALUES
(1, 'V-12345678', 'Carlos', 'González', 'Av. Libertador, Caracas', '0412-1234567', 'carlosg@email.com'),
(2, 'V-34567890', 'Pedro', 'Martínez', 'Urb. Las Mercedes, Valencia', '0424-3456789', 'pedrom@email.com'),
(3, 'J-56789012', 'Comercial XYZ, C.A.', 'S/N', 'Zona Industrial, San Cristóbal', '0276-5678901', 'comercialxyz@email.com'),
(4, 'V-23456789', 'María', 'Rodríguez', 'Calle Sucre, Maracaibo', '0414-2345678', 'mariar@email.com'),
(5, 'J-89012345', 'Inversiones ABC, C.A.', 'S/N', 'Centro Empresarial, Los Teques', '0212-8901234', 'inversionesabc@email.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineas_abastecimiento`
--

CREATE TABLE `lineas_abastecimiento` (
  `id_linea_abastecimiento` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fk_orden_abastecimiento` int(11) NOT NULL,
  `fk_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineas_venta`
--

CREATE TABLE `lineas_venta` (
  `id_linea_venta` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fk_orden` int(11) NOT NULL,
  `fk_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_abastecimiento`
--

CREATE TABLE `orden_abastecimiento` (
  `id_orden_abastecimiento` int(11) NOT NULL,
  `numero_de_orden` varchar(20) NOT NULL,
  `fecha` date NOT NULL,
  `fk_proveedor` int(11) NOT NULL,
  `fk_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `orden_abastecimiento`
--

INSERT INTO `orden_abastecimiento` (`id_orden_abastecimiento`, `numero_de_orden`, `fecha`, `fk_proveedor`, `fk_status`) VALUES
(1, 'OC-0001', '2026-05-20', 1, 5),
(2, 'OC-0002', '2026-05-25', 2, 5),
(3, 'OC-0003', '2026-06-01', 3, 3),
(4, 'OC-0004', '2026-06-05', 4, 2),
(5, 'OC-0005', '2026-06-10', 1, 1),
(6, 'OC-0006', '2026-06-12', 7, 5),
(7, 'OC-0007', '2026-06-14', 8, 3),
(8, 'OC-0008', '2026-06-16', 9, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_de_venta`
--

CREATE TABLE `orden_de_venta` (
  `id_orden` int(11) NOT NULL,
  `numero_de_orden` varchar(20) NOT NULL,
  `fecha` date NOT NULL,
  `fk_usuario` int(11) NOT NULL,
  `fk_cliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id_permiso` int(11) NOT NULL,
  `permisos` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id_permiso`, `permisos`) VALUES
(10, 'configuracion'),
(4, 'gestion_abastecimiento'),
(6, 'gestion_activos'),
(5, 'gestion_asesoria'),
(7, 'gestion_clientes'),
(2, 'gestion_productos'),
(8, 'gestion_proveedores'),
(1, 'gestion_usuarios'),
(3, 'gestion_ventas'),
(9, 'ver_reportes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_rol`
--

CREATE TABLE `permisos_rol` (
  `id_permiso_rol` int(11) NOT NULL,
  `fk_rol` int(11) NOT NULL,
  `fk_permiso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permisos_rol`
--

INSERT INTO `permisos_rol` (`id_permiso_rol`, `fk_rol`, `fk_permiso`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9),
(10, 1, 10),
(11, 2, 3),
(12, 2, 7),
(13, 2, 9),
(14, 3, 2),
(15, 3, 4),
(16, 3, 9),
(17, 4, 5),
(18, 4, 7),
(19, 4, 9),
(20, 5, 6),
(21, 5, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 5,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `fecha_creacion` date NOT NULL,
  `fecha_actualizacion` date DEFAULT NULL,
  `fk_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `codigo`, `nombre`, `descripcion`, `stock`, `stock_minimo`, `precio_compra`, `precio_venta`, `fecha_creacion`, `fecha_actualizacion`, `fk_categoria`) VALUES
(1, 'LAP-001', 'Laptop HP Pavilion 15', 'Intel Core i5, 8GB RAM, 512GB SSD, 15.6\"', 15, 3, 450.00, 650.00, '2026-01-10', '2026-06-01', 1),
(2, 'LAP-002', 'Laptop Lenovo ThinkPad X1', 'Intel Core i7, 16GB RAM, 512GB SSD, 14\"', 8, 2, 850.00, 1200.00, '2026-01-15', '2026-05-28', 1),
(3, 'PCD-001', 'PC Desktop Dell OptiPlex', 'Intel Core i5, 8GB RAM, 1TB HDD', 10, 3, 350.00, 520.00, '2026-02-01', '2026-06-10', 2),
(4, 'PCD-002', 'PC Desktop Armada Gamer', 'Ryzen 7, 32GB RAM, 1TB SSD, RTX 4060', 5, 1, 1200.00, 1800.00, '2026-02-20', '2026-06-15', 2),
(5, 'MON-001', 'Monitor Samsung 24\"', 'Full HD 1920x1080, IPS Panel', 20, 5, 120.00, 180.00, '2026-01-05', '2026-06-12', 3),
(6, 'MON-002', 'Monitor LG UltraWide 29\"', '2560x1080, IPS, 75Hz', 12, 3, 200.00, 310.00, '2026-03-01', '2026-06-10', 3),
(7, 'TEC-001', 'Teclado Mecánico Redragon', 'Switch Red, RGB, Español', 30, 10, 25.00, 45.00, '2026-01-20', '2026-06-05', 4),
(8, 'TEC-002', 'Teclado Inalámbrico Logitech', 'Compacto, Batería recargable', 25, 8, 30.00, 55.00, '2026-02-10', '2026-06-08', 4),
(9, 'MOU-001', 'Mouse Gamer Logitech G203', '8000 DPI, RGB, 6 botones', 40, 10, 20.00, 38.00, '2026-01-15', '2026-06-03', 5),
(10, 'MOU-002', 'Mouse Inalámbrico Microsoft', 'Ergonómico, Bluetooth', 35, 10, 18.00, 32.00, '2026-02-01', '2026-06-02', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL,
  `rif` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id_proveedor`, `rif`, `nombre`, `email`, `telefono`) VALUES
(1, 'J-12345678-9', 'TecnoSuministros C.A.', 'ventas@tecnosuministros.com', '0212-1234567'),
(2, 'J-23456789-0', 'CompuMundo C.A.', 'info@compumundo.com', '0241-2345678'),
(3, 'J-34567890-1', 'DataRed Express', 'contacto@datared.com', '0261-3456789'),
(4, 'V-45678901-2', 'Luis Perdomo Electrónica', 'luisperdomo@email.com', '0414-4567890'),
(5, 'J-56789012-3', 'Inversiones Tecnológicas Zulia', 'ventas@itzulia.com', '0261-5678901'),
(6, 'J-67890123-4', 'Sumtec C.A.', 'pedidos@sumtec.com', '0212-6789012'),
(7, 'J-78901234-5', 'Papelería y Suministros C.A.', 'ventas@papisum.com', '0212-7890123'),
(8, 'J-89012345-6', 'Juguettos C.A.', 'pedidos@juguettos.com', '0241-8901234'),
(9, 'V-90123456-7', 'Bisutería Fashion C.A.', 'info@bisfashion.com', '0412-9012345');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'Administrador'),
(3, 'Almacenista'),
(4, 'Asesor'),
(5, 'Soporte Técnico'),
(2, 'Vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_usuarios`
--

CREATE TABLE `rol_usuarios` (
  `id_rol_usuario` int(11) NOT NULL,
  `fk_rol` int(11) NOT NULL,
  `rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol_usuarios`
--

INSERT INTO `rol_usuarios` (`id_rol_usuario`, `fk_rol`, `rol`) VALUES
(1, 1, 'Administrador'),
(2, 2, 'Vendedor'),
(3, 3, 'Almacenista'),
(4, 4, 'Asesor'),
(5, 5, 'Soporte Técnico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesion_ciber`
--

CREATE TABLE `sesion_ciber` (
  `id_sesion` int(11) NOT NULL,
  `tiempo_uso` time NOT NULL,
  `fk_cliente` int(11) NOT NULL,
  `fk_tarifa` int(11) NOT NULL,
  `fk_activo` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sesion_ciber`
--

INSERT INTO `sesion_ciber` (`id_sesion`, `tiempo_uso`, `fk_cliente`, `fk_tarifa`, `fk_activo`, `created_at`) VALUES
(1, '01:30:00', 1, 3, 1, '2026-06-24 23:23:49'),
(2, '00:45:00', 3, 2, 2, '2026-06-24 23:23:49'),
(3, '02:00:00', 4, 4, 3, '2026-06-24 23:23:49'),
(4, '01:00:00', 6, 2, 4, '2026-06-24 23:23:49'),
(5, '00:30:00', 7, 1, 5, '2026-06-24 23:23:49'),
(6, '03:00:00', 9, 5, 6, '2026-06-24 23:23:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status_seguimiento`
--

CREATE TABLE `status_seguimiento` (
  `id_status` int(11) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `status_seguimiento`
--

INSERT INTO `status_seguimiento` (`id_status`, `status`) VALUES
(2, 'Aprobado'),
(6, 'Cancelado'),
(3, 'En Tránsito'),
(1, 'Pendiente'),
(5, 'Recibido Completo'),
(4, 'Recibido Parcial');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id_tarifa` int(11) NOT NULL,
  `tarifa_hora` decimal(10,2) NOT NULL,
  `precio_tiempo` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarifas`
--

INSERT INTO `tarifas` (`id_tarifa`, `tarifa_hora`, `precio_tiempo`) VALUES
(1, 1.50, 3.00),
(2, 2.50, 5.00),
(3, 3.00, 6.00),
(4, 4.00, 8.00),
(5, 5.00, 10.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_activo`
--

CREATE TABLE `tipo_activo` (
  `id_tipo_activo` int(11) NOT NULL,
  `nombre_tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_activo`
--

INSERT INTO `tipo_activo` (`id_tipo_activo`, `nombre_tipo`) VALUES
(4, 'Impresora'),
(51, 'Impresora.'),
(2, 'Laptop'),
(49, 'Laptop.'),
(3, 'Monitor'),
(50, 'Monitor.'),
(1, 'PC Escritorio'),
(45, 'PC Escritorio.'),
(9, 'PC Gaming'),
(46, 'PC Gaming.'),
(10, 'PC Oficina'),
(47, 'PC Oficina.'),
(11, 'PC Premium'),
(48, 'PC Premium.'),
(6, 'Router'),
(53, 'Router.'),
(5, 'Servidor'),
(52, 'Servidor.'),
(7, 'Switch'),
(54, 'Switch.'),
(8, 'UPS'),
(55, 'UPS.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_asesoria`
--

CREATE TABLE `tipo_asesoria` (
  `id_tipo_asesoria` int(11) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `permitido` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_asesoria`
--

INSERT INTO `tipo_asesoria` (`id_tipo_asesoria`, `tipo`, `permitido`) VALUES
(1, 'Documentos no controlados', 1),
(2, 'Asesoría documental', 1),
(3, 'Asesoría general', 1),
(4, 'Asesoría sobre tramites generales', 1),
(5, 'Asesoría sobre tramites digitales', 1),
(6, 'Asesoría Penal', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `estatus` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fk_rol_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `user_name`, `password_hash`, `email`, `estatus`, `fecha_creacion`, `fk_rol_usuario`) VALUES
(1, 'Admin', 'Principal', 'admin', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'admin@zonaweb.com', 'activo', '2026-06-24 23:23:49', 1),
(2, 'Juan', 'Peralta', 'jperalta', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'jperalta@zonaweb.com', 'activo', '2026-06-24 23:23:49', 2),
(3, 'María', 'Fernández', 'mfernandez', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'mfernandez@zonaweb.com', 'activo', '2026-06-24 23:23:49', 2),
(4, 'Carlos', 'Rivas', 'crivas', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'crivas@zonaweb.com', 'activo', '2026-06-24 23:23:49', 3),
(5, 'Ana', 'Mendoza', 'amendoza', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'amendoza@zonaweb.com', 'activo', '2026-06-24 23:23:49', 4),
(6, 'Pedro', 'García', 'pgarcia', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'pgarcia@zonaweb.com', 'activo', '2026-06-24 23:23:49', 5);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activos`
--
ALTER TABLE `activos`
  ADD PRIMARY KEY (`id_activo`),
  ADD KEY `fk_tipo_activo` (`fk_tipo_activo`);

--
-- Indices de la tabla `asesoria`
--
ALTER TABLE `asesoria`
  ADD PRIMARY KEY (`id_asesoria`),
  ADD UNIQUE KEY `documento` (`documento`),
  ADD KEY `fk_cliente_asesoria` (`fk_cliente_asesoria`),
  ADD KEY `fk_tipo_asesoria` (`fk_tipo_asesoria`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `cliente_asesoria`
--
ALTER TABLE `cliente_asesoria`
  ADD PRIMARY KEY (`id_cliente_asesoria`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `lineas_abastecimiento`
--
ALTER TABLE `lineas_abastecimiento`
  ADD PRIMARY KEY (`id_linea_abastecimiento`),
  ADD KEY `fk_orden_abastecimiento` (`fk_orden_abastecimiento`),
  ADD KEY `fk_producto` (`fk_producto`);

--
-- Indices de la tabla `lineas_venta`
--
ALTER TABLE `lineas_venta`
  ADD PRIMARY KEY (`id_linea_venta`),
  ADD KEY `fk_orden` (`fk_orden`),
  ADD KEY `fk_producto` (`fk_producto`);

--
-- Indices de la tabla `orden_abastecimiento`
--
ALTER TABLE `orden_abastecimiento`
  ADD PRIMARY KEY (`id_orden_abastecimiento`),
  ADD UNIQUE KEY `numero_de_orden` (`numero_de_orden`),
  ADD KEY `fk_proveedor` (`fk_proveedor`),
  ADD KEY `fk_status` (`fk_status`);

--
-- Indices de la tabla `orden_de_venta`
--
ALTER TABLE `orden_de_venta`
  ADD PRIMARY KEY (`id_orden`),
  ADD UNIQUE KEY `numero_de_orden` (`numero_de_orden`),
  ADD KEY `fk_usuario` (`fk_usuario`),
  ADD KEY `fk_cliente` (`fk_cliente`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permiso`),
  ADD UNIQUE KEY `permisos` (`permisos`);

--
-- Indices de la tabla `permisos_rol`
--
ALTER TABLE `permisos_rol`
  ADD PRIMARY KEY (`id_permiso_rol`),
  ADD UNIQUE KEY `unique_permiso_rol` (`fk_rol`,`fk_permiso`),
  ADD KEY `fk_permiso` (`fk_permiso`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `fk_categoria` (`fk_categoria`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `rif` (`rif`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `rol_usuarios`
--
ALTER TABLE `rol_usuarios`
  ADD PRIMARY KEY (`id_rol_usuario`),
  ADD KEY `fk_rol` (`fk_rol`);

--
-- Indices de la tabla `sesion_ciber`
--
ALTER TABLE `sesion_ciber`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `fk_cliente` (`fk_cliente`),
  ADD KEY `fk_tarifa` (`fk_tarifa`),
  ADD KEY `fk_activo` (`fk_activo`);

--
-- Indices de la tabla `status_seguimiento`
--
ALTER TABLE `status_seguimiento`
  ADD PRIMARY KEY (`id_status`),
  ADD UNIQUE KEY `status` (`status`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id_tarifa`);

--
-- Indices de la tabla `tipo_activo`
--
ALTER TABLE `tipo_activo`
  ADD PRIMARY KEY (`id_tipo_activo`),
  ADD UNIQUE KEY `nombre_tipo` (`nombre_tipo`);

--
-- Indices de la tabla `tipo_asesoria`
--
ALTER TABLE `tipo_asesoria`
  ADD PRIMARY KEY (`id_tipo_asesoria`),
  ADD UNIQUE KEY `tipo` (`tipo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `user_name` (`user_name`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_rol_usuario` (`fk_rol_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activos`
--
ALTER TABLE `activos`
  MODIFY `id_activo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `asesoria`
--
ALTER TABLE `asesoria`
  MODIFY `id_asesoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `cliente_asesoria`
--
ALTER TABLE `cliente_asesoria`
  MODIFY `id_cliente_asesoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `lineas_abastecimiento`
--
ALTER TABLE `lineas_abastecimiento`
  MODIFY `id_linea_abastecimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lineas_venta`
--
ALTER TABLE `lineas_venta`
  MODIFY `id_linea_venta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `orden_abastecimiento`
--
ALTER TABLE `orden_abastecimiento`
  MODIFY `id_orden_abastecimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `orden_de_venta`
--
ALTER TABLE `orden_de_venta`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `permisos_rol`
--
ALTER TABLE `permisos_rol`
  MODIFY `id_permiso_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `rol_usuarios`
--
ALTER TABLE `rol_usuarios`
  MODIFY `id_rol_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `sesion_ciber`
--
ALTER TABLE `sesion_ciber`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `status_seguimiento`
--
ALTER TABLE `status_seguimiento`
  MODIFY `id_status` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id_tarifa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipo_activo`
--
ALTER TABLE `tipo_activo`
  MODIFY `id_tipo_activo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `tipo_asesoria`
--
ALTER TABLE `tipo_asesoria`
  MODIFY `id_tipo_asesoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activos`
--
ALTER TABLE `activos`
  ADD CONSTRAINT `activos_ibfk_1` FOREIGN KEY (`fk_tipo_activo`) REFERENCES `tipo_activo` (`id_tipo_activo`);

--
-- Filtros para la tabla `asesoria`
--
ALTER TABLE `asesoria`
  ADD CONSTRAINT `asesoria_ibfk_1` FOREIGN KEY (`fk_cliente_asesoria`) REFERENCES `cliente_asesoria` (`id_cliente_asesoria`),
  ADD CONSTRAINT `asesoria_ibfk_2` FOREIGN KEY (`fk_tipo_asesoria`) REFERENCES `tipo_asesoria` (`id_tipo_asesoria`);

--
-- Filtros para la tabla `lineas_abastecimiento`
--
ALTER TABLE `lineas_abastecimiento`
  ADD CONSTRAINT `lineas_abastecimiento_ibfk_1` FOREIGN KEY (`fk_orden_abastecimiento`) REFERENCES `orden_abastecimiento` (`id_orden_abastecimiento`) ON DELETE CASCADE,
  ADD CONSTRAINT `lineas_abastecimiento_ibfk_2` FOREIGN KEY (`fk_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `lineas_venta`
--
ALTER TABLE `lineas_venta`
  ADD CONSTRAINT `lineas_venta_ibfk_1` FOREIGN KEY (`fk_orden`) REFERENCES `orden_de_venta` (`id_orden`) ON DELETE CASCADE,
  ADD CONSTRAINT `lineas_venta_ibfk_2` FOREIGN KEY (`fk_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `orden_abastecimiento`
--
ALTER TABLE `orden_abastecimiento`
  ADD CONSTRAINT `orden_abastecimiento_ibfk_1` FOREIGN KEY (`fk_proveedor`) REFERENCES `proveedores` (`id_proveedor`),
  ADD CONSTRAINT `orden_abastecimiento_ibfk_2` FOREIGN KEY (`fk_status`) REFERENCES `status_seguimiento` (`id_status`);

--
-- Filtros para la tabla `orden_de_venta`
--
ALTER TABLE `orden_de_venta`
  ADD CONSTRAINT `orden_de_venta_ibfk_1` FOREIGN KEY (`fk_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `orden_de_venta_ibfk_2` FOREIGN KEY (`fk_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `permisos_rol`
--
ALTER TABLE `permisos_rol`
  ADD CONSTRAINT `permisos_rol_ibfk_1` FOREIGN KEY (`fk_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE,
  ADD CONSTRAINT `permisos_rol_ibfk_2` FOREIGN KEY (`fk_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`fk_categoria`) REFERENCES `categoria` (`id_categoria`);

--
-- Filtros para la tabla `rol_usuarios`
--
ALTER TABLE `rol_usuarios`
  ADD CONSTRAINT `rol_usuarios_ibfk_1` FOREIGN KEY (`fk_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesion_ciber`
--
ALTER TABLE `sesion_ciber`
  ADD CONSTRAINT `sesion_ciber_ibfk_1` FOREIGN KEY (`fk_cliente`) REFERENCES `clientes` (`id_cliente`),
  ADD CONSTRAINT `sesion_ciber_ibfk_2` FOREIGN KEY (`fk_tarifa`) REFERENCES `tarifas` (`id_tarifa`),
  ADD CONSTRAINT `sesion_ciber_ibfk_3` FOREIGN KEY (`fk_activo`) REFERENCES `activos` (`id_activo`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`fk_rol_usuario`) REFERENCES `rol_usuarios` (`id_rol_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
