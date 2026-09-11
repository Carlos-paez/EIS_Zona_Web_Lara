-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-09-2026 a las 17:33:22
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
  `id` int(11) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `is_ciber` tinyint(1) NOT NULL DEFAULT 0,
  `activa` tinyint(1) DEFAULT 1,
  `fk_tipo_activo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `activos`
--

INSERT INTO `activos` (`id`, `marca`, `descripcion`, `is_ciber`, `activa`, `fk_tipo_activo`) VALUES
(1, 'HP', 'PC Escritorio HP ProDesk 400 G5', 1, 1, 1),
(2, 'Dell', 'Laptop Dell Latitude 5490', 0, 1, 2),
(3, 'Samsung', 'Monitor Samsung 24\" Curvo', 1, 1, 3),
(4, 'HP', 'Impresora HP LaserJet M404', 0, 1, 4),
(5, 'Dell', 'Servidor Dell PowerEdge T340', 0, 1, 5),
(6, 'TP-Link', 'Router TP-Link Archer C80', 1, 1, 6),
(7, 'Cisco', 'Switch Cisco Catalyst 2960', 1, 1, 7),
(8, 'APC', 'UPS APC Back-UPS 1500VA', 1, 1, 8),
(9, 'Lenovo', 'PC Escritorio Lenovo ThinkCentre', 1, 1, 1),
(10, 'LG', 'Monitor LG 27\" 4K', 0, 1, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asesoria`
--

CREATE TABLE `asesoria` (
  `id` int(11) NOT NULL,
  `documento` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `fk_cliente_asesoria` int(11) DEFAULT NULL,
  `fk_tipo_asesoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `asesoria`
--

INSERT INTO `asesoria` (`id`, `documento`, `descripcion`, `fecha`, `fk_cliente_asesoria`, `fk_tipo_asesoria`) VALUES
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
  `id` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id`, `nombre_categoria`) VALUES
(1, 'Laptops'),
(2, 'PC de Escritorio'),
(3, 'Monitores'),
(4, 'Teclados'),
(5, 'Mouse'),
(6, 'Audífonos'),
(7, 'Componentes Internos'),
(8, 'Redes'),
(9, 'Impresoras'),
(10, 'Accesorios'),
(11, 'Papelería'),
(12, 'Juguetería'),
(13, 'Bisutería'),
(14, 'Laptops'),
(15, 'PC de Escritorio'),
(16, 'Monitores'),
(17, 'Teclados'),
(18, 'Mouse'),
(19, 'Audífonos'),
(20, 'Componentes Internos'),
(21, 'Redes'),
(22, 'Impresoras'),
(23, 'Accesorios'),
(24, 'Papelería'),
(25, 'Juguetería'),
(26, 'Bisutería');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `direccion` text NOT NULL,
  `telefono` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `cedula`, `nombre`, `apellido`, `direccion`, `telefono`) VALUES
(1, 'V-12345678', 'Carlos', 'González', 'Av. Libertador, Caracas', '0412-1234567'),
(2, 'V-23456789', 'María', 'Rodríguez', 'Calle Sucre, Maracaibo', '0414-2345678'),
(3, 'V-34567890', 'Pedro', 'Martínez', 'Urb. Las Mercedes, Valencia', '0424-3456789'),
(4, 'V-45678901', 'Ana', 'López', 'Av. Bolívar, Barquisimeto', '0416-4567890'),
(5, 'J-56789012', 'Comercial XYZ, C.A.', 'S/N', 'Zona Industrial, San Cristóbal', '0276-5678901'),
(6, 'V-67890123', 'Luis', 'Pérez', 'Calle 5, Mérida', '0412-6789012'),
(7, 'V-78901234', 'Sofía', 'Díaz', 'Av. Principal, Puerto Ordaz', '0414-7890123'),
(8, 'J-89012345', 'Inversiones ABC, C.A.', 'S/N', 'Centro Empresarial, Los Teques', '0212-8901234'),
(9, 'E-90123456', 'Roberto', 'Sánchez', 'Calle 10, San Felipe', '0426-9012345'),
(10, 'V-01234567', 'Daniela', 'Torres', 'Urb. El Paraíso, Maracay', '0412-0123456');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente_asesoria`
--

CREATE TABLE `cliente_asesoria` (
  `id` int(11) NOT NULL,
  `fk_cliente` int(11) DEFAULT NULL,
  `email` varchar(80) NOT NULL DEFAULT 'N/A',
  `rif` varchar(50) DEFAULT 'N/A',
  `tipo` varchar(80) DEFAULT 'civil'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `cliente_asesoria`
--

INSERT INTO `cliente_asesoria` (`id`, `fk_cliente`, `email`, `rif`, `tipo`) VALUES
(1, 1, 'carlosg@email.com', 'V-12345678', 'civil'),
(2, 3, 'pedrom@email.com', 'V-34567890', 'civil'),
(3, 5, 'comercialxyz@email.com', 'J-56789012', 'comercial'),
(4, 2, 'mariar@email.com', 'V-23456789', 'civil'),
(5, 8, 'inversionesabc@email.com', 'J-89012345', 'comercial');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineas_abastecimiento`
--

CREATE TABLE `lineas_abastecimiento` (
  `id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fk_orden_abastecimiento` int(11) DEFAULT NULL,
  `fk_producto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `lineas_abastecimiento`
--

INSERT INTO `lineas_abastecimiento` (`id`, `cantidad`, `precio`, `fk_orden_abastecimiento`, `fk_producto`) VALUES
(1, 10, 450.00, 1, 1),
(2, 10, 120.00, 1, 5),
(3, 20, 25.00, 2, 7),
(4, 30, 20.00, 2, 9),
(5, 15, 55.00, 3, 11),
(6, 30, 40.00, 3, 13),
(7, 10, 55.00, 4, 16),
(8, 15, 8.00, 5, 20),
(9, 10, 150.00, 5, 18),
(10, 50, 3.50, 6, 21),
(11, 80, 2.00, 6, 24),
(12, 40, 2.50, 6, 26),
(13, 60, 1.80, 6, 27),
(14, 30, 4.00, 7, 31),
(15, 50, 0.50, 7, 34),
(16, 25, 8.00, 7, 36),
(17, 40, 3.00, 7, 37),
(18, 15, 12.00, 8, 41),
(19, 20, 5.00, 8, 44),
(20, 30, 3.00, 8, 48),
(21, 50, 1.50, 8, 49),
(22, 20, 6.00, 8, 52),
(23, 15, 10.00, 8, 56);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineas_venta`
--

CREATE TABLE `lineas_venta` (
  `id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fk_orden` int(11) DEFAULT NULL,
  `fk_producto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `lineas_venta`
--

INSERT INTO `lineas_venta` (`id`, `cantidad`, `precio`, `fk_orden`, `fk_producto`) VALUES
(1, 1, 650.00, 1, 1),
(2, 1, 45.00, 1, 7),
(3, 2, 180.00, 2, 5),
(4, 1, 90.00, 2, 11),
(5, 1, 1200.00, 3, 2),
(6, 1, 38.00, 3, 9),
(7, 3, 65.00, 4, 13),
(8, 5, 15.00, 4, 20),
(9, 1, 520.00, 5, 3),
(10, 1, 75.00, 5, 14),
(11, 1, 350.00, 6, 12),
(12, 1, 90.00, 6, 16),
(13, 5, 6.00, 7, 21),
(14, 10, 4.00, 7, 24),
(15, 3, 8.00, 7, 26),
(16, 8, 3.50, 8, 27),
(17, 4, 5.50, 8, 30),
(18, 6, 1.80, 8, 32),
(19, 2, 18.00, 9, 36),
(20, 3, 7.00, 9, 37),
(21, 1, 40.00, 9, 39),
(22, 1, 28.00, 10, 41),
(23, 2, 12.00, 10, 44),
(24, 3, 8.00, 7, 48),
(25, 5, 3.00, 8, 49),
(26, 2, 10.00, 9, 52),
(27, 1, 25.00, 10, 56);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_abastecimiento`
--

CREATE TABLE `orden_abastecimiento` (
  `id` int(11) NOT NULL,
  `numero_de_orden` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `fk_proveedor` int(11) DEFAULT NULL,
  `fk_status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `orden_abastecimiento`
--

INSERT INTO `orden_abastecimiento` (`id`, `numero_de_orden`, `fecha`, `fk_proveedor`, `fk_status`) VALUES
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
  `id` int(11) NOT NULL,
  `numero_de_orden` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `fk_usuario` int(11) DEFAULT NULL,
  `fk_cliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `orden_de_venta`
--

INSERT INTO `orden_de_venta` (`id`, `numero_de_orden`, `fecha`, `fk_usuario`, `fk_cliente`) VALUES
(1, 'VTA-0001', '2026-06-01', 2, 1),
(2, 'VTA-0002', '2026-06-03', 3, 2),
(3, 'VTA-0003', '2026-06-05', 2, 3),
(4, 'VTA-0004', '2026-06-08', 3, 5),
(5, 'VTA-0005', '2026-06-10', 2, 4),
(6, 'VTA-0006', '2026-06-12', 3, 6),
(7, 'VTA-0007', '2026-06-13', 2, 7),
(8, 'VTA-0008', '2026-06-14', 3, 8),
(9, 'VTA-0009', '2026-06-15', 2, 9),
(10, 'VTA-0010', '2026-06-16', 3, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `permisos` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `permisos`) VALUES
(1, 'gestion_usuarios'),
(2, 'gestion_productos'),
(3, 'gestion_ventas'),
(4, 'gestion_abastecimiento'),
(5, 'gestion_asesoria'),
(6, 'gestion_activos'),
(7, 'gestion_clientes'),
(8, 'gestion_proveedores'),
(9, 'ver_reportes'),
(10, 'configuracion'),
(11, 'gestion_usuarios'),
(12, 'gestion_productos'),
(13, 'gestion_ventas'),
(14, 'gestion_abastecimiento'),
(15, 'gestion_asesoria'),
(16, 'gestion_activos'),
(17, 'gestion_clientes'),
(18, 'gestion_proveedores'),
(19, 'ver_reportes'),
(20, 'configuracion');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_rol`
--

CREATE TABLE `permisos_rol` (
  `id` int(11) NOT NULL,
  `fk_rol` int(11) DEFAULT NULL,
  `fk_permiso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `permisos_rol`
--

INSERT INTO `permisos_rol` (`id`, `fk_rol`, `fk_permiso`) VALUES
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
  `id` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `stock` int(11) NOT NULL,
  `stock_minimo` int(11) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `fecha_creacion` date NOT NULL,
  `fecha_actualizacion` date NOT NULL,
  `fk_categoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `stock`, `stock_minimo`, `precio_compra`, `precio_venta`, `fecha_creacion`, `fecha_actualizacion`, `fk_categoria`) VALUES
(1, 'LAP-001', 'Laptop HP Pavilion 15', 'Intel Core i5, 8GB RAM, 512GB SSD, 15.6\"', 15, 3, 450.00, 650.00, '2026-01-10', '2026-06-01', 1),
(2, 'LAP-002', 'Laptop Lenovo ThinkPad X1', 'Intel Core i7, 16GB RAM, 512GB SSD, 14\"', 8, 2, 850.00, 1200.00, '2026-01-15', '2026-05-28', 1),
(3, 'PCD-001', 'PC Desktop Dell OptiPlex', 'Intel Core i5, 8GB RAM, 1TB HDD', 10, 3, 350.00, 520.00, '2026-02-01', '2026-06-10', 2),
(4, 'PCD-002', 'PC Desktop Armada Gamer', 'Ryzen 7, 32GB RAM, 1TB SSD, RTX 4060', 5, 1, 1200.00, 1800.00, '2026-02-20', '2026-06-15', 2),
(5, 'MON-001', 'Monitor Samsung 24\"', 'Full HD 1920x1080, IPS Panel', 20, 5, 120.00, 180.00, '2026-01-05', '2026-06-12', 3),
(6, 'MON-002', 'Monitor LG UltraWide 29\"', '2560x1080, IPS, 75Hz', 12, 3, 200.00, 310.00, '2026-03-01', '2026-06-10', 3),
(7, 'TEC-001', 'Teclado Mecánico Redragon', 'Switch Red, RGB, Español', 30, 10, 25.00, 45.00, '2026-01-20', '2026-06-05', 4),
(8, 'TEC-002', 'Teclado Inalámbrico Logitech', 'Compacto, Batería recargable', 25, 8, 30.00, 55.00, '2026-02-10', '2026-06-08', 4),
(9, 'MOU-001', 'Mouse Gamer Logitech G203', '8000 DPI, RGB, 6 botones', 40, 10, 20.00, 38.00, '2026-01-15', '2026-06-03', 5),
(10, 'MOU-002', 'Mouse Inalámbrico Microsoft', 'Ergonómico, Bluetooth', 35, 10, 18.00, 32.00, '2026-02-01', '2026-06-02', 5),
(11, 'AUD-001', 'Audífonos HyperX Cloud II', 'Diadema, 7.1 surround, USB', 18, 5, 55.00, 90.00, '2026-03-05', '2026-06-10', 6),
(12, 'AUD-002', 'Audífonos Sony WH-1000XM5', 'Cancelación de ruido, Bluetooth', 10, 3, 220.00, 350.00, '2026-04-01', '2026-06-11', 6),
(13, 'COM-001', 'SSD Kingston 480GB', 'SATA III, 2.5\"', 50, 15, 40.00, 65.00, '2026-01-10', '2026-06-01', 7),
(14, 'COM-002', 'RAM Corsair Vengeance 16GB DDR4', '3200MHz, CL16', 40, 10, 45.00, 75.00, '2026-01-20', '2026-06-05', 7),
(15, 'COM-003', 'Fuente EVGA 600W 80+', 'ATX, 80 Plus White', 20, 5, 50.00, 85.00, '2026-02-15', '2026-06-07', 7),
(16, 'RED-001', 'Router TP-Link Archer AX10', 'WiFi 6, Doble Banda', 15, 4, 55.00, 90.00, '2026-03-01', '2026-06-09', 8),
(17, 'RED-002', 'Switch Cisco 24 Puertos', 'Gigabit, Managed', 6, 2, 180.00, 280.00, '2026-03-10', '2026-06-12', 8),
(18, 'IMP-001', 'Impresora HP LaserJet Pro', 'Monocromática, WiFi, Dúplex', 10, 3, 150.00, 250.00, '2026-02-20', '2026-06-08', 9),
(19, 'IMP-002', 'Impresora Epson L3250', 'Multifuncional, Sistema continuo', 12, 4, 130.00, 210.00, '2026-03-15', '2026-06-10', 9),
(20, 'ACC-001', 'Hub USB 4 Puertos', 'USB 3.0, Aluminio', 60, 20, 8.00, 15.00, '2026-01-05', '2026-06-01', 10),
(21, 'PAP-001', 'Resma Papel Bond Carta', 'Papel bond 75g, 500 hojas', 100, 20, 3.50, 6.00, '2026-01-10', '2026-06-01', 11),
(22, 'PAP-002', 'Resma Papel Bond Oficio', 'Papel bond 75g, 500 hojas', 80, 15, 4.00, 7.00, '2026-01-10', '2026-06-01', 11),
(23, 'PAP-003', 'Lápiz HB N°2', 'Caja x 12 unidades, grafito', 120, 30, 1.50, 3.00, '2026-01-15', '2026-05-20', 11),
(24, 'PAP-004', 'Bolígrafo Azul', 'Caja x 12, punta fina', 150, 30, 2.00, 4.00, '2026-01-15', '2026-05-20', 11),
(25, 'PAP-005', 'Bolígrafo Negro', 'Caja x 12, punta fina', 150, 30, 2.00, 4.00, '2026-01-15', '2026-05-20', 11),
(26, 'PAP-006', 'Marcador Pizarra', 'Caja x 4 colores', 60, 15, 2.50, 5.00, '2026-02-01', '2026-05-25', 11),
(27, 'PAP-007', 'Cuaderno Universitario', '100 hojas, cosido, rayado', 90, 20, 1.80, 3.50, '2026-02-05', '2026-06-02', 11),
(28, 'PAP-008', 'Carpeta Archivo', 'Carpeta colgante, kraft', 70, 15, 1.20, 2.50, '2026-02-10', '2026-06-03', 11),
(29, 'PAP-009', 'Tijeras Escolares', 'Acero inoxidable, punta roma', 50, 10, 1.00, 2.20, '2026-02-15', '2026-06-05', 11),
(30, 'PAP-010', 'Pegamento en Barra', 'Barra 21g, x 12 unidades', 80, 20, 3.00, 5.50, '2026-02-20', '2026-06-05', 11),
(31, 'PAP-011', 'Grapadora Oficina', 'Metálica, capacidad 20 hojas', 40, 10, 4.00, 8.00, '2026-03-01', '2026-06-07', 11),
(32, 'PAP-012', 'Caja Clip Mariposa', 'Caja x 100 unidades', 100, 20, 0.80, 1.80, '2026-03-05', '2026-06-08', 11),
(33, 'PAP-013', 'Cinta Adhesiva Transparente', 'Rollos x 6, 48mm x 50m', 60, 15, 3.50, 7.00, '2026-03-10', '2026-06-08', 11),
(34, 'PAP-014', 'Folder Manila', 'Carta, con bolsillo', 100, 25, 0.50, 1.20, '2026-03-15', '2026-06-10', 11),
(35, 'PAP-015', 'Tóner HP 85A', 'Negro, original HP', 25, 5, 45.00, 75.00, '2026-04-01', '2026-06-10', 11),
(36, 'JUG-001', 'Muñeca Barbie', 'Vestido de moda, accesorios incluidos', 30, 8, 8.00, 18.00, '2026-01-20', '2026-06-01', 12),
(37, 'JUG-002', 'Carro Hot Wheels', 'Pack x 5, escala 1:64', 60, 15, 3.00, 7.00, '2026-01-20', '2026-06-01', 12),
(38, 'JUG-003', 'Pelota de Fútbol', 'Tamaño 5, cuero sintético', 25, 5, 6.00, 14.00, '2026-02-01', '2026-06-02', 12),
(39, 'JUG-004', 'Lego Clásico', 'Bloques 500 piezas', 20, 5, 20.00, 40.00, '2026-02-10', '2026-06-03', 12),
(40, 'JUG-005', 'Rompecabezas 1000 piezas', 'Paisaje, impresión de alta calidad', 18, 5, 6.00, 15.00, '2026-02-15', '2026-06-05', 12),
(41, 'JUG-006', 'Juego de Mesa Monopoly', 'Edición clásica', 15, 4, 12.00, 28.00, '2026-03-01', '2026-06-05', 12),
(42, 'JUG-007', 'Pelota de Basketball', 'Tamaño 7, caucho', 20, 5, 7.00, 16.00, '2026-03-05', '2026-06-07', 12),
(43, 'JUG-008', 'Trompo de Madera', 'Tradicional, 10cm, cuerda incluida', 40, 10, 1.50, 4.00, '2026-03-10', '2026-06-08', 12),
(44, 'JUG-009', 'Peluche Oso 30cm', 'Hipolergénico, suave', 25, 6, 5.00, 12.00, '2026-03-15', '2026-06-10', 12),
(45, 'JUG-010', 'Pistola de Agua', '500ml, automática', 35, 8, 2.50, 6.00, '2026-03-20', '2026-06-10', 12),
(46, 'JUG-011', 'Set de Plastilina', '12 colores, 24 barras', 30, 8, 3.00, 7.50, '2026-04-01', '2026-06-11', 12),
(47, 'JUG-012', 'Dominó Clásico', '28 fichas, maletín metálico', 25, 6, 3.00, 7.00, '2026-04-05', '2026-06-11', 12),
(48, 'BIS-001', 'Collar Acero Quirúrgico', 'Cadena 50cm + dije brillante', 40, 10, 3.00, 8.00, '2026-01-25', '2026-06-01', 13),
(49, 'BIS-002', 'Pulsera Mostacilla', 'Elástica, varios colores', 60, 15, 1.00, 3.00, '2026-01-25', '2026-06-01', 13),
(50, 'BIS-003', 'Aros Argolla Dorada', 'Acero bañado en oro, 2cm', 50, 12, 2.00, 5.50, '2026-02-05', '2026-06-02', 13),
(51, 'BIS-004', 'Anillo Ajustable', 'Varios diseños, talla única', 70, 20, 1.50, 4.00, '2026-02-10', '2026-06-03', 13),
(52, 'BIS-005', 'Gargantilla Plateada', 'Acero inoxidable, brillantes incrustados', 35, 8, 4.00, 10.00, '2026-02-15', '2026-06-05', 13),
(53, 'BIS-006', 'Set de Bisutería 3 piezas', 'Collar + pulsera + aros, elegante', 25, 6, 6.00, 15.00, '2026-03-01', '2026-06-05', 13),
(54, 'BIS-007', 'Tobillera Plateada', 'Cadena fina ajustable', 45, 10, 1.50, 4.00, '2026-03-10', '2026-06-07', 13),
(55, 'BIS-008', 'Piercing Nariz', 'Acero quirúrgico, brillante', 80, 20, 0.80, 2.00, '2026-03-15', '2026-06-08', 13),
(56, 'BIS-009', 'Reloj Analógico Mujer', 'Pulso metálico, brillantes', 20, 5, 10.00, 25.00, '2026-03-20', '2026-06-10', 13),
(57, 'BIS-010', 'Collar de Perlas', 'Perlas cultivadas, cierre dorado', 15, 4, 12.00, 30.00, '2026-04-01', '2026-06-10', 13),
(58, 'BIS-011', 'Manilla Cuero', 'Trenzada, cierre metálico', 40, 10, 2.00, 5.00, '2026-04-05', '2026-06-11', 13),
(59, 'BIS-012', 'Broche para Cabello', 'Cristal brillante, pinza metálica', 55, 15, 1.20, 3.50, '2026-04-10', '2026-06-11', 13);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `rif` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `rif`, `nombre`, `email`, `telefono`) VALUES
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
  `id` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre_rol`, `descripcion`, `created_at`) VALUES
(1, 'Administrador', NULL, '2026-09-08 15:32:37'),
(2, 'Vendedor', NULL, '2026-09-08 15:32:37'),
(3, 'Almacenista', NULL, '2026-09-08 15:32:37'),
(4, 'Asesor', NULL, '2026-09-08 15:32:37'),
(5, 'Soporte Técnico', NULL, '2026-09-08 15:32:37'),
(6, 'Administrador', NULL, '2026-09-08 15:32:45'),
(7, 'Vendedor', NULL, '2026-09-08 15:32:45'),
(8, 'Almacenista', NULL, '2026-09-08 15:32:45'),
(9, 'Asesor', NULL, '2026-09-08 15:32:45'),
(10, 'Soporte Técnico', NULL, '2026-09-08 15:32:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_usuarios`
--

CREATE TABLE `rol_usuarios` (
  `id` int(11) NOT NULL,
  `fk_rol` int(11) DEFAULT NULL,
  `rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `rol_usuarios`
--

INSERT INTO `rol_usuarios` (`id`, `fk_rol`, `rol`) VALUES
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
  `id` int(11) NOT NULL,
  `tiempo_uso` varchar(50) NOT NULL,
  `finalizada` tinyint(1) NOT NULL DEFAULT 0,
  `fk_cliente` int(11) DEFAULT NULL,
  `fk_tarifa` int(11) DEFAULT NULL,
  `fk_activo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `sesion_ciber`
--

INSERT INTO `sesion_ciber` (`id`, `tiempo_uso`, `finalizada`, `fk_cliente`, `fk_tarifa`, `fk_activo`) VALUES
(1, '01:30:00', 0, 1, 3, NULL),
(2, '00:45:00', 0, 3, 2, NULL),
(3, '02:00:00', 0, 4, 4, NULL),
(4, '01:00:00', 0, 6, 2, NULL),
(5, '00:30:00', 0, 7, 1, NULL),
(6, '03:00:00', 0, 9, 5, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status_seguimiento`
--

CREATE TABLE `status_seguimiento` (
  `id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `status_seguimiento`
--

INSERT INTO `status_seguimiento` (`id`, `status`) VALUES
(1, 'Pendiente'),
(2, 'Aprobado'),
(3, 'En Tránsito'),
(4, 'Recibido Parcial'),
(5, 'Recibido Completo'),
(6, 'Cancelado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id` int(11) NOT NULL,
  `tarifa_hora` decimal(10,2) NOT NULL,
  `precio_tiempo` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tarifas`
--

INSERT INTO `tarifas` (`id`, `tarifa_hora`, `precio_tiempo`) VALUES
(1, 2.50, 5.00),
(2, 3.00, 6.00),
(3, 4.00, 8.00),
(4, 5.00, 10.00),
(5, 1.50, 3.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_activo`
--

CREATE TABLE `tipo_activo` (
  `id` int(11) NOT NULL,
  `nombre_tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tipo_activo`
--

INSERT INTO `tipo_activo` (`id`, `nombre_tipo`) VALUES
(1, 'PC Escritorio'),
(2, 'Laptop'),
(3, 'Monitor'),
(4, 'Impresora'),
(5, 'Servidor'),
(6, 'Router'),
(7, 'Switch'),
(8, 'UPS');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_asesoria`
--

CREATE TABLE `tipo_asesoria` (
  `id` int(11) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `permitido` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tipo_asesoria`
--

INSERT INTO `tipo_asesoria` (`id`, `tipo`, `permitido`) VALUES
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
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT 'zonaweblara@gmail.com',
  `estatus` varchar(20) NOT NULL DEFAULT '0',
  `fk_rol_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `user_name`, `password_hash`, `email`, `estatus`, `fk_rol_usuario`) VALUES
(1, 'Admin', 'Principal', 'admin', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'admin@zonaweb.com', '1', 1),
(2, 'Juan', 'Peralta', 'jperalta', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'jperalta@zonaweb.com', '1', 2),
(3, 'María', 'Fernández', 'mfernandez', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'mfernandez@zonaweb.com', '1', 2),
(4, 'Carlos', 'Rivas', 'crivas', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'crivas@zonaweb.com', '1', 3),
(5, 'Ana', 'Mendoza', 'amendoza', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'amendoza@zonaweb.com', '1', 4),
(6, 'Pedro', 'García', 'pgarcia', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'pgarcia@zonaweb.com', '1', 5);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activos`
--
ALTER TABLE `activos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tipo_activo` (`fk_tipo_activo`);

--
-- Indices de la tabla `asesoria`
--
ALTER TABLE `asesoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cliente_asesoria` (`fk_cliente_asesoria`),
  ADD KEY `fk_tipo_asesoria` (`fk_tipo_asesoria`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `cliente_asesoria`
--
ALTER TABLE `cliente_asesoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cliente` (`fk_cliente`);

--
-- Indices de la tabla `lineas_abastecimiento`
--
ALTER TABLE `lineas_abastecimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orden_abastecimiento` (`fk_orden_abastecimiento`),
  ADD KEY `fk_producto` (`fk_producto`);

--
-- Indices de la tabla `lineas_venta`
--
ALTER TABLE `lineas_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orden` (`fk_orden`),
  ADD KEY `fk_producto` (`fk_producto`);

--
-- Indices de la tabla `orden_abastecimiento`
--
ALTER TABLE `orden_abastecimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proveedor` (`fk_proveedor`),
  ADD KEY `fk_status` (`fk_status`);

--
-- Indices de la tabla `orden_de_venta`
--
ALTER TABLE `orden_de_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuario` (`fk_usuario`),
  ADD KEY `fk_cliente` (`fk_cliente`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `permisos_rol`
--
ALTER TABLE `permisos_rol`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rol` (`fk_rol`),
  ADD KEY `fk_permiso` (`fk_permiso`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `fk_categoria` (`fk_categoria`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rif` (`rif`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rol_usuarios`
--
ALTER TABLE `rol_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rol` (`fk_rol`);

--
-- Indices de la tabla `sesion_ciber`
--
ALTER TABLE `sesion_ciber`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cliente` (`fk_cliente`),
  ADD KEY `fk_tarifa` (`fk_tarifa`),
  ADD KEY `fk_activo` (`fk_activo`);

--
-- Indices de la tabla `status_seguimiento`
--
ALTER TABLE `status_seguimiento`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_activo`
--
ALTER TABLE `tipo_activo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_asesoria`
--
ALTER TABLE `tipo_asesoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_name` (`user_name`),
  ADD KEY `fk_rol_usuario` (`fk_rol_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activos`
--
ALTER TABLE `activos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `asesoria`
--
ALTER TABLE `asesoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=521;

--
-- AUTO_INCREMENT de la tabla `cliente_asesoria`
--
ALTER TABLE `cliente_asesoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT de la tabla `lineas_abastecimiento`
--
ALTER TABLE `lineas_abastecimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `lineas_venta`
--
ALTER TABLE `lineas_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `orden_abastecimiento`
--
ALTER TABLE `orden_abastecimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `orden_de_venta`
--
ALTER TABLE `orden_de_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `permisos_rol`
--
ALTER TABLE `permisos_rol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=272;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `rol_usuarios`
--
ALTER TABLE `rol_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `sesion_ciber`
--
ALTER TABLE `sesion_ciber`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `status_seguimiento`
--
ALTER TABLE `status_seguimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tipo_activo`
--
ALTER TABLE `tipo_activo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `tipo_asesoria`
--
ALTER TABLE `tipo_asesoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activos`
--
ALTER TABLE `activos`
  ADD CONSTRAINT `activos_ibfk_1` FOREIGN KEY (`fk_tipo_activo`) REFERENCES `tipo_activo` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `asesoria`
--
ALTER TABLE `asesoria`
  ADD CONSTRAINT `asesoria_ibfk_1` FOREIGN KEY (`fk_cliente_asesoria`) REFERENCES `cliente_asesoria` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asesoria_ibfk_2` FOREIGN KEY (`fk_tipo_asesoria`) REFERENCES `tipo_asesoria` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `cliente_asesoria`
--
ALTER TABLE `cliente_asesoria`
  ADD CONSTRAINT `cliente_asesoria_ibfk_1` FOREIGN KEY (`fk_cliente`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineas_abastecimiento`
--
ALTER TABLE `lineas_abastecimiento`
  ADD CONSTRAINT `lineas_abastecimiento_ibfk_1` FOREIGN KEY (`fk_orden_abastecimiento`) REFERENCES `orden_abastecimiento` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lineas_abastecimiento_ibfk_2` FOREIGN KEY (`fk_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineas_venta`
--
ALTER TABLE `lineas_venta`
  ADD CONSTRAINT `lineas_venta_ibfk_1` FOREIGN KEY (`fk_orden`) REFERENCES `orden_de_venta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lineas_venta_ibfk_2` FOREIGN KEY (`fk_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `orden_abastecimiento`
--
ALTER TABLE `orden_abastecimiento`
  ADD CONSTRAINT `orden_abastecimiento_ibfk_1` FOREIGN KEY (`fk_proveedor`) REFERENCES `proveedores` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `orden_abastecimiento_ibfk_2` FOREIGN KEY (`fk_status`) REFERENCES `status_seguimiento` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `orden_de_venta`
--
ALTER TABLE `orden_de_venta`
  ADD CONSTRAINT `orden_de_venta_ibfk_1` FOREIGN KEY (`fk_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `orden_de_venta_ibfk_2` FOREIGN KEY (`fk_cliente`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `permisos_rol`
--
ALTER TABLE `permisos_rol`
  ADD CONSTRAINT `permisos_rol_ibfk_1` FOREIGN KEY (`fk_rol`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permisos_rol_ibfk_2` FOREIGN KEY (`fk_permiso`) REFERENCES `permisos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`fk_categoria`) REFERENCES `categoria` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `rol_usuarios`
--
ALTER TABLE `rol_usuarios`
  ADD CONSTRAINT `rol_usuarios_ibfk_1` FOREIGN KEY (`fk_rol`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesion_ciber`
--
ALTER TABLE `sesion_ciber`
  ADD CONSTRAINT `sesion_ciber_ibfk_1` FOREIGN KEY (`fk_cliente`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sesion_ciber_ibfk_2` FOREIGN KEY (`fk_tarifa`) REFERENCES `tarifas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `sesion_ciber_ibfk_3` FOREIGN KEY (`fk_activo`) REFERENCES `activos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`fk_rol_usuario`) REFERENCES `rol_usuarios` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
