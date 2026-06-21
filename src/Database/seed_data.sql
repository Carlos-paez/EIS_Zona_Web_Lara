USE zona_web_lara;

-- ============================================================
-- ROLES
-- ============================================================
INSERT INTO roles (nombre_rol) VALUES
('Administrador'),
('Vendedor'),
('Almacenista'),
('Asesor'),
('Soporte Técnico');

-- ============================================================
-- PERMISOS
-- ============================================================
INSERT INTO permisos (permisos) VALUES
('gestion_usuarios'),
('gestion_productos'),
('gestion_ventas'),
('gestion_abastecimiento'),
('gestion_asesoria'),
('gestion_activos'),
('gestion_clientes'),
('gestion_proveedores'),
('ver_reportes'),
('configuracion');

-- ============================================================
-- CATEGORIA
-- ============================================================
INSERT INTO categoria (nombre_categoria) VALUES
('Laptops'),
('PC de Escritorio'),
('Monitores'),
('Teclados'),
('Mouse'),
('Audífonos'),
('Componentes Internos'),
('Redes'),
('Impresoras'),
('Accesorios'),
('Papelería'),
('Juguetería'),
('Bisutería');

-- ============================================================
-- CLIENTES
-- ============================================================
INSERT INTO clientes (cedula, nombre, apellido, direccion, telefono) VALUES
('V-12345678', 'Carlos', 'González', 'Av. Libertador, Caracas', '0412-1234567'),
('V-23456789', 'María', 'Rodríguez', 'Calle Sucre, Maracaibo', '0414-2345678'),
('V-34567890', 'Pedro', 'Martínez', 'Urb. Las Mercedes, Valencia', '0424-3456789'),
('V-45678901', 'Ana', 'López', 'Av. Bolívar, Barquisimeto', '0416-4567890'),
('J-56789012', 'Comercial XYZ, C.A.', 'S/N', 'Zona Industrial, San Cristóbal', '0276-5678901'),
('V-67890123', 'Luis', 'Pérez', 'Calle 5, Mérida', '0412-6789012'),
('V-78901234', 'Sofía', 'Díaz', 'Av. Principal, Puerto Ordaz', '0414-7890123'),
('J-89012345', 'Inversiones ABC, C.A.', 'S/N', 'Centro Empresarial, Los Teques', '0212-8901234'),
('E-90123456', 'Roberto', 'Sánchez', 'Calle 10, San Felipe', '0426-9012345'),
('V-01234567', 'Daniela', 'Torres', 'Urb. El Paraíso, Maracay', '0412-0123456');

-- ============================================================
-- PROVEEDORES
-- ============================================================
INSERT INTO proveedores (rif, nombre, email, telefono) VALUES
('J-12345678-9', 'TecnoSuministros C.A.', 'ventas@tecnosuministros.com', '0212-1234567'),
('J-23456789-0', 'CompuMundo C.A.', 'info@compumundo.com', '0241-2345678'),
('J-34567890-1', 'DataRed Express', 'contacto@datared.com', '0261-3456789'),
('V-45678901-2', 'Luis Perdomo Electrónica', 'luisperdomo@email.com', '0414-4567890'),
('J-56789012-3', 'Inversiones Tecnológicas Zulia', 'ventas@itzulia.com', '0261-5678901'),
('J-67890123-4', 'Sumtec C.A.', 'pedidos@sumtec.com', '0212-6789012'),
('J-78901234-5', 'Papelería y Suministros C.A.', 'ventas@papisum.com', '0212-7890123'),
('J-89012345-6', 'Juguettos C.A.', 'pedidos@juguettos.com', '0241-8901234'),
('V-90123456-7', 'Bisutería Fashion C.A.', 'info@bisfashion.com', '0412-9012345');

-- ============================================================
-- STATUS_SEGUIMIENTO
-- ============================================================
INSERT INTO status_seguimiento (status) VALUES
('Pendiente'),
('Aprobado'),
('En Tránsito'),
('Recibido Parcial'),
('Recibido Completo'),
('Cancelado');

-- ============================================================
-- TIPO_ASESORIA
-- ============================================================
INSERT INTO tipo_asesoria (tipo, permitido) VALUES
('Documentos no controlados', TRUE),
('Asesoría documental', TRUE),
('Asesoría general', TRUE),
('Asesoría sobre tramites generales', TRUE),
('Asesoría sobre tramites digitales', TRUE),
('Asesoría Penal', FALSE);

-- ============================================================
-- TARIFAS
-- ============================================================
INSERT INTO tarifas (tarifa_hora, precio_tiempo) VALUES
(2.50, 5.00),
(3.00, 6.00),
(4.00, 8.00),
(5.00, 10.00),
(1.50, 3.00);

-- ============================================================
-- TIPO_ACTIVO
-- ============================================================
INSERT INTO tipo_activo (nombre_tipo) VALUES
('PC Escritorio'),
('Laptop'),
('Monitor'),
('Impresora'),
('Servidor'),
('Router'),
('Switch'),
('UPS');

-- ============================================================
-- ROL_USUARIOS
-- ============================================================
INSERT INTO rol_usuarios (fk_rol, rol) VALUES
(1, 'Administrador'),
(2, 'Vendedor'),
(3, 'Almacenista'),
(4, 'Asesor'),
(5, 'Soporte Técnico');

-- ============================================================
-- USUARIOS
-- ============================================================
INSERT INTO usuarios (nombre, apellido, user_name, password_hash, email, estatus, fk_rol_usuario) VALUES
('Admin', 'Principal', 'admin', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'admin@zonaweb.com', 'activo', 1),
('Juan', 'Peralta', 'jperalta', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'jperalta@zonaweb.com', 'activo', 2),
('María', 'Fernández', 'mfernandez', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'mfernandez@zonaweb.com', 'activo', 2),
('Carlos', 'Rivas', 'crivas', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'crivas@zonaweb.com', 'activo', 3),
('Ana', 'Mendoza', 'amendoza', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'amendoza@zonaweb.com', 'activo', 4),
('Pedro', 'García', 'pgarcia', '$2y$10$kTT14tkjYsPzfwqMamoF9.67Kh1M5YJAH9a3xcs6dCCk7nXYReEF.', 'pgarcia@zonaweb.com', 'activo', 5);

-- ============================================================
-- PERMISOS_ROL (Administrador tiene todos los permisos)
-- ============================================================
INSERT INTO permisos_rol (fk_rol, fk_permiso) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5),
(1, 6), (1, 7), (1, 8), (1, 9), (1, 10),
(2, 3), (2, 7), (2, 9),
(3, 2), (3, 4), (3, 9),
(4, 5), (4, 7), (4, 9),
(5, 6), (5, 9);

-- ============================================================
-- PRODUCTOS
-- ============================================================
INSERT INTO productos (codigo, nombre, descripcion, stock, stock_minimo, precio_compra, precio_venta, fecha_creacion, fecha_actualizacion, fk_categoria) VALUES
('LAP-001', 'Laptop HP Pavilion 15', 'Intel Core i5, 8GB RAM, 512GB SSD, 15.6"', 15, 3, 450.00, 650.00, '2026-01-10', '2026-06-01', 1),
('LAP-002', 'Laptop Lenovo ThinkPad X1', 'Intel Core i7, 16GB RAM, 512GB SSD, 14"', 8, 2, 850.00, 1200.00, '2026-01-15', '2026-05-28', 1),
('PCD-001', 'PC Desktop Dell OptiPlex', 'Intel Core i5, 8GB RAM, 1TB HDD', 10, 3, 350.00, 520.00, '2026-02-01', '2026-06-10', 2),
('PCD-002', 'PC Desktop Armada Gamer', 'Ryzen 7, 32GB RAM, 1TB SSD, RTX 4060', 5, 1, 1200.00, 1800.00, '2026-02-20', '2026-06-15', 2),
('MON-001', 'Monitor Samsung 24"', 'Full HD 1920x1080, IPS Panel', 20, 5, 120.00, 180.00, '2026-01-05', '2026-06-12', 3),
('MON-002', 'Monitor LG UltraWide 29"', '2560x1080, IPS, 75Hz', 12, 3, 200.00, 310.00, '2026-03-01', '2026-06-10', 3),
('TEC-001', 'Teclado Mecánico Redragon', 'Switch Red, RGB, Español', 30, 10, 25.00, 45.00, '2026-01-20', '2026-06-05', 4),
('TEC-002', 'Teclado Inalámbrico Logitech', 'Compacto, Batería recargable', 25, 8, 30.00, 55.00, '2026-02-10', '2026-06-08', 4),
('MOU-001', 'Mouse Gamer Logitech G203', '8000 DPI, RGB, 6 botones', 40, 10, 20.00, 38.00, '2026-01-15', '2026-06-03', 5),
('MOU-002', 'Mouse Inalámbrico Microsoft', 'Ergonómico, Bluetooth', 35, 10, 18.00, 32.00, '2026-02-01', '2026-06-02', 5),
('AUD-001', 'Audífonos HyperX Cloud II', 'Diadema, 7.1 surround, USB', 18, 5, 55.00, 90.00, '2026-03-05', '2026-06-10', 6),
('AUD-002', 'Audífonos Sony WH-1000XM5', 'Cancelación de ruido, Bluetooth', 10, 3, 220.00, 350.00, '2026-04-01', '2026-06-11', 6),
('COM-001', 'SSD Kingston 480GB', 'SATA III, 2.5"', 50, 15, 40.00, 65.00, '2026-01-10', '2026-06-01', 7),
('COM-002', 'RAM Corsair Vengeance 16GB DDR4', '3200MHz, CL16', 40, 10, 45.00, 75.00, '2026-01-20', '2026-06-05', 7),
('COM-003', 'Fuente EVGA 600W 80+', 'ATX, 80 Plus White', 20, 5, 50.00, 85.00, '2026-02-15', '2026-06-07', 7),
('RED-001', 'Router TP-Link Archer AX10', 'WiFi 6, Doble Banda', 15, 4, 55.00, 90.00, '2026-03-01', '2026-06-09', 8),
('RED-002', 'Switch Cisco 24 Puertos', 'Gigabit, Managed', 6, 2, 180.00, 280.00, '2026-03-10', '2026-06-12', 8),
('IMP-001', 'Impresora HP LaserJet Pro', 'Monocromática, WiFi, Dúplex', 10, 3, 150.00, 250.00, '2026-02-20', '2026-06-08', 9),
('IMP-002', 'Impresora Epson L3250', 'Multifuncional, Sistema continuo', 12, 4, 130.00, 210.00, '2026-03-15', '2026-06-10', 9),
('ACC-001', 'Hub USB 4 Puertos', 'USB 3.0, Aluminio', 60, 20, 8.00, 15.00, '2026-01-05', '2026-06-01', 10),
-- Papelería (categoría 11)
('PAP-001', 'Resma Papel Bond Carta', 'Papel bond 75g, 500 hojas', 100, 20, 3.50, 6.00, '2026-01-10', '2026-06-01', 11),
('PAP-002', 'Resma Papel Bond Oficio', 'Papel bond 75g, 500 hojas', 80, 15, 4.00, 7.00, '2026-01-10', '2026-06-01', 11),
('PAP-003', 'Lápiz HB N°2', 'Caja x 12 unidades, grafito', 120, 30, 1.50, 3.00, '2026-01-15', '2026-05-20', 11),
('PAP-004', 'Bolígrafo Azul', 'Caja x 12, punta fina', 150, 30, 2.00, 4.00, '2026-01-15', '2026-05-20', 11),
('PAP-005', 'Bolígrafo Negro', 'Caja x 12, punta fina', 150, 30, 2.00, 4.00, '2026-01-15', '2026-05-20', 11),
('PAP-006', 'Marcador Pizarra', 'Caja x 4 colores', 60, 15, 2.50, 5.00, '2026-02-01', '2026-05-25', 11),
('PAP-007', 'Cuaderno Universitario', '100 hojas, cosido, rayado', 90, 20, 1.80, 3.50, '2026-02-05', '2026-06-02', 11),
('PAP-008', 'Carpeta Archivo', 'Carpeta colgante, kraft', 70, 15, 1.20, 2.50, '2026-02-10', '2026-06-03', 11),
('PAP-009', 'Tijeras Escolares', 'Acero inoxidable, punta roma', 50, 10, 1.00, 2.20, '2026-02-15', '2026-06-05', 11),
('PAP-010', 'Pegamento en Barra', 'Barra 21g, x 12 unidades', 80, 20, 3.00, 5.50, '2026-02-20', '2026-06-05', 11),
('PAP-011', 'Grapadora Oficina', 'Metálica, capacidad 20 hojas', 40, 10, 4.00, 8.00, '2026-03-01', '2026-06-07', 11),
('PAP-012', 'Caja Clip Mariposa', 'Caja x 100 unidades', 100, 20, 0.80, 1.80, '2026-03-05', '2026-06-08', 11),
('PAP-013', 'Cinta Adhesiva Transparente', 'Rollos x 6, 48mm x 50m', 60, 15, 3.50, 7.00, '2026-03-10', '2026-06-08', 11),
('PAP-014', 'Folder Manila', 'Carta, con bolsillo', 100, 25, 0.50, 1.20, '2026-03-15', '2026-06-10', 11),
('PAP-015', 'Tóner HP 85A', 'Negro, original HP', 25, 5, 45.00, 75.00, '2026-04-01', '2026-06-10', 11),
-- Juguetería (categoría 12)
('JUG-001', 'Muñeca Barbie', 'Vestido de moda, accesorios incluidos', 30, 8, 8.00, 18.00, '2026-01-20', '2026-06-01', 12),
('JUG-002', 'Carro Hot Wheels', 'Pack x 5, escala 1:64', 60, 15, 3.00, 7.00, '2026-01-20', '2026-06-01', 12),
('JUG-003', 'Pelota de Fútbol', 'Tamaño 5, cuero sintético', 25, 5, 6.00, 14.00, '2026-02-01', '2026-06-02', 12),
('JUG-004', 'Lego Clásico', 'Bloques 500 piezas', 20, 5, 20.00, 40.00, '2026-02-10', '2026-06-03', 12),
('JUG-005', 'Rompecabezas 1000 piezas', 'Paisaje, impresión de alta calidad', 18, 5, 6.00, 15.00, '2026-02-15', '2026-06-05', 12),
('JUG-006', 'Juego de Mesa Monopoly', 'Edición clásica', 15, 4, 12.00, 28.00, '2026-03-01', '2026-06-05', 12),
('JUG-007', 'Pelota de Basketball', 'Tamaño 7, caucho', 20, 5, 7.00, 16.00, '2026-03-05', '2026-06-07', 12),
('JUG-008', 'Trompo de Madera', 'Tradicional, 10cm, cuerda incluida', 40, 10, 1.50, 4.00, '2026-03-10', '2026-06-08', 12),
('JUG-009', 'Peluche Oso 30cm', 'Hipolergénico, suave', 25, 6, 5.00, 12.00, '2026-03-15', '2026-06-10', 12),
('JUG-010', 'Pistola de Agua', '500ml, automática', 35, 8, 2.50, 6.00, '2026-03-20', '2026-06-10', 12),
('JUG-011', 'Set de Plastilina', '12 colores, 24 barras', 30, 8, 3.00, 7.50, '2026-04-01', '2026-06-11', 12),
('JUG-012', 'Dominó Clásico', '28 fichas, maletín metálico', 25, 6, 3.00, 7.00, '2026-04-05', '2026-06-11', 12),
-- Bisutería (categoría 13)
('BIS-001', 'Collar Acero Quirúrgico', 'Cadena 50cm + dije brillante', 40, 10, 3.00, 8.00, '2026-01-25', '2026-06-01', 13),
('BIS-002', 'Pulsera Mostacilla', 'Elástica, varios colores', 60, 15, 1.00, 3.00, '2026-01-25', '2026-06-01', 13),
('BIS-003', 'Aros Argolla Dorada', 'Acero bañado en oro, 2cm', 50, 12, 2.00, 5.50, '2026-02-05', '2026-06-02', 13),
('BIS-004', 'Anillo Ajustable', 'Varios diseños, talla única', 70, 20, 1.50, 4.00, '2026-02-10', '2026-06-03', 13),
('BIS-005', 'Gargantilla Plateada', 'Acero inoxidable, brillantes incrustados', 35, 8, 4.00, 10.00, '2026-02-15', '2026-06-05', 13),
('BIS-006', 'Set de Bisutería 3 piezas', 'Collar + pulsera + aros, elegante', 25, 6, 6.00, 15.00, '2026-03-01', '2026-06-05', 13),
('BIS-007', 'Tobillera Plateada', 'Cadena fina ajustable', 45, 10, 1.50, 4.00, '2026-03-10', '2026-06-07', 13),
('BIS-008', 'Piercing Nariz', 'Acero quirúrgico, brillante', 80, 20, 0.80, 2.00, '2026-03-15', '2026-06-08', 13),
('BIS-009', 'Reloj Analógico Mujer', 'Pulso metálico, brillantes', 20, 5, 10.00, 25.00, '2026-03-20', '2026-06-10', 13),
('BIS-010', 'Collar de Perlas', 'Perlas cultivadas, cierre dorado', 15, 4, 12.00, 30.00, '2026-04-01', '2026-06-10', 13),
('BIS-011', 'Manilla Cuero', 'Trenzada, cierre metálico', 40, 10, 2.00, 5.00, '2026-04-05', '2026-06-11', 13),
('BIS-012', 'Broche para Cabello', 'Cristal brillante, pinza metálica', 55, 15, 1.20, 3.50, '2026-04-10', '2026-06-11', 13);

-- ============================================================
-- ORDEN_DE_VENTA
-- ============================================================
INSERT INTO orden_de_venta (numero_de_orden, fecha, fk_usuario, fk_cliente) VALUES
('VTA-0001', '2026-06-01', 2, 1),
('VTA-0002', '2026-06-03', 3, 2),
('VTA-0003', '2026-06-05', 2, 3),
('VTA-0004', '2026-06-08', 3, 5),
('VTA-0005', '2026-06-10', 2, 4),
('VTA-0006', '2026-06-12', 3, 6),
('VTA-0007', '2026-06-13', 2, 7),
('VTA-0008', '2026-06-14', 3, 8),
('VTA-0009', '2026-06-15', 2, 9),
('VTA-0010', '2026-06-16', 3, 10);

-- ============================================================
-- LINEAS_VENTA
-- ============================================================
INSERT INTO lineas_venta (cantidad, precio, fk_orden, fk_producto) VALUES
(1, 650.00, 1, 1),
(1, 45.00, 1, 7),
(2, 180.00, 2, 5),
(1, 90.00, 2, 11),
(1, 1200.00, 3, 2),
(1, 38.00, 3, 9),
(3, 65.00, 4, 13),
(5, 15.00, 4, 20),
(1, 520.00, 5, 3),
(1, 75.00, 5, 14),
(1, 350.00, 6, 12),
(1, 90.00, 6, 16),
-- Ventas de Papelería
(5, 6.00, 7, 21),
(10, 4.00, 7, 24),
(3, 8.00, 7, 26),
(8, 3.50, 8, 27),
(4, 5.50, 8, 30),
(6, 1.80, 8, 32),
-- Ventas de Juguetería
(2, 18.00, 9, 36),
(3, 7.00, 9, 37),
(1, 40.00, 9, 39),
(1, 28.00, 10, 41),
(2, 12.00, 10, 44),
-- Ventas de Bisutería
(3, 8.00, 7, 48),
(5, 3.00, 8, 49),
(2, 10.00, 9, 52),
(1, 25.00, 10, 56);

-- ============================================================
-- ORDEN_ABASTECIMIENTO
-- ============================================================
INSERT INTO orden_abastecimiento (numero_de_orden, fecha, fk_proveedor, fk_status) VALUES
('OC-0001', '2026-05-20', 1, 5),
('OC-0002', '2026-05-25', 2, 5),
('OC-0003', '2026-06-01', 3, 3),
('OC-0004', '2026-06-05', 4, 2),
('OC-0005', '2026-06-10', 1, 1),
('OC-0006', '2026-06-12', 7, 5),
('OC-0007', '2026-06-14', 8, 3),
('OC-0008', '2026-06-16', 9, 2);

-- ============================================================
-- LINEAS_ABASTECIMIENTO
-- ============================================================
INSERT INTO lineas_abastecimiento (cantidad, precio, fk_orden_abastecimiento, fk_producto) VALUES
(10, 450.00, 1, 1),
(10, 120.00, 1, 5),
(20, 25.00, 2, 7),
(30, 20.00, 2, 9),
(15, 55.00, 3, 11),
(30, 40.00, 3, 13),
(10, 55.00, 4, 16),
(15, 8.00, 5, 20),
(10, 150.00, 5, 18),
-- Abastecimiento Papelería
(50, 3.50, 6, 21),
(80, 2.00, 6, 24),
(40, 2.50, 6, 26),
(60, 1.80, 6, 27),
(30, 4.00, 7, 31),
(50, 0.50, 7, 34),
-- Abastecimiento Juguetería
(25, 8.00, 7, 36),
(40, 3.00, 7, 37),
(15, 12.00, 8, 41),
(20, 5.00, 8, 44),
-- Abastecimiento Bisutería
(30, 3.00, 8, 48),
(50, 1.50, 8, 49),
(20, 6.00, 8, 52),
(15, 10.00, 8, 56);

-- ============================================================
-- ASESORIA
-- ============================================================
INSERT INTO asesoria (documento, descripcion, fecha, fk_cliente, fk_tipo_asesoria) VALUES
('ASES-001', 'Asesoría para instalación de servidor en red local', '2026-06-02', 1, 2),
('ASES-002', 'Diagnóstico y reparación de PC de escritorio', '2026-06-04', 3, 4),
('ASES-003', 'Configuración de software contable', '2026-06-07', 5, 3),
('ASES-004', 'Asesoría para migración a Office 365', '2026-06-09', 2, 3),
('ASES-005', 'Revisión de equipos para renovación de parque tecnológico', '2026-06-11', 8, 1);

-- ============================================================
-- SESION_CIBER
-- ============================================================
INSERT INTO sesion_ciber (tiempo_uso, fk_cliente, fk_tarifa) VALUES
('01:30:00', 1, 3),
('00:45:00', 3, 2),
('02:00:00', 4, 4),
('01:00:00', 6, 2),
('00:30:00', 7, 1),
('03:00:00', 9, 5);

-- ============================================================
-- ACTIVOS
-- ============================================================
INSERT INTO activos (marca, descripcion, is_ciber, activa, fk_tipo_activo, fk_usuario_usa) VALUES
('HP', 'PC Escritorio HP ProDesk 400 G5', TRUE, TRUE, 1, 1),
('Dell', 'Laptop Dell Latitude 5490', FALSE, TRUE, 2, 2),
('Samsung', 'Monitor Samsung 24" Curvo', TRUE, TRUE, 3, 3),
('HP', 'Impresora HP LaserJet M404', FALSE, TRUE, 4, 4),
('Dell', 'Servidor Dell PowerEdge T340', FALSE, TRUE, 5, 1),
('TP-Link', 'Router TP-Link Archer C80', TRUE, TRUE, 6, 4),
('Cisco', 'Switch Cisco Catalyst 2960', TRUE, TRUE, 7, 1),
('APC', 'UPS APC Back-UPS 1500VA', TRUE, TRUE, 8, 2),
('Lenovo', 'PC Escritorio Lenovo ThinkCentre', TRUE, TRUE, 1, 3),
('LG', 'Monitor LG 27" 4K', FALSE, TRUE, 3, 2);
