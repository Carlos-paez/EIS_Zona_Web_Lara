USE zona_web_lara;

-- ============================================================
-- ROLES
-- ============================================================
INSERT INTO roles (nombre_rol) VALUES
('Administrador'),
('Vendedor'),
('Almacenista'),
('Asesor'),
('Soporte Técnico'),
('Gerente General'),
('Contador'),
('Recursos Humanos');


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
('configuracion'),
('gestion_nomina'),
('gestion_contabilidad'),
('auditoria'),
('gestion_marketing'),
('gestion_compras');

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
-- CLIENTE_ASESORIA
-- ============================================================
INSERT INTO cliente_asesoria (fk_cliente, email, rif, tipo) VALUES
(1, 'carlosg@email.com', 'V-12345678', 'civil'),
(3, 'pedrom@email.com', 'V-34567890', 'civil'),
(5, 'comercialxyz@email.com', 'J-56789012', 'juridico'),
(2, 'mariar@email.com', 'V-23456789', 'civil'),
(8, 'inversionesabc@email.com', 'J-89012345', 'juridico');

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
(5, 'Soporte Técnico'),
(6, 'Gerente General'),
(7, 'Contador'),
(8, 'Recursos Humanos');

-- ============================================================
-- USUARIOS
-- ============================================================
INSERT INTO usuarios (nombre, apellido, user_name, password_hash, email, estatus, fk_rol_usuario) VALUES
('Admin', 'Principal', 'admin', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'admin@zonaweb.com', '1', 1),
('Juan', 'Peralta', 'jperalta', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'jperalta@zonaweb.com', '1', 2),
('María', 'Fernández', 'mfernandez', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'mfernandez@zonaweb.com', '1', 2),
('Carlos', 'Rivas', 'crivas', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'crivas@zonaweb.com', '1', 3),
('Ana', 'Mendoza', 'amendoza', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'amendoza@zonaweb.com', '1', 4),
('Pedro', 'García', 'pgarcia', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'pgarcia@zonaweb.com', '1', 5);

-- ============================================================
-- PERMISOS_ROL (Administrador tiene todos los permisos)
-- ============================================================
INSERT INTO permisos_rol (fk_rol, fk_permiso) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5),
(1, 6), (1, 7), (1, 8), (1, 9), (1, 10),
(1, 11), (1, 12), (1, 13), (1, 14), (1, 15),
(2, 3), (2, 7), (2, 9),
(3, 2), (3, 4), (3, 9),
(4, 5), (4, 7), (4, 9),
(5, 6), (5, 9),
(6, 1), (6, 2), (6, 3), (6, 7), (6, 8),
(6, 9), (6, 10), (6, 14), (6, 15),
(7, 9), (7, 12), (7, 13),
(8, 1), (8, 9), (8, 11);

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
INSERT INTO asesoria (documento, descripcion, fecha, fk_cliente_asesoria, fk_tipo_asesoria) VALUES
('ASES-001', 'Asesoría para instalación de servidor en red local', '2026-06-02', 1, 2),
('ASES-002', 'Diagnóstico y reparación de PC de escritorio', '2026-06-04', 2, 4),
('ASES-003', 'Configuración de software contable', '2026-06-07', 3, 3),
('ASES-004', 'Asesoría para migración a Office 365', '2026-06-09', 4, 3),
('ASES-005', 'Revisión de equipos para renovación de parque tecnológico', '2026-06-11', 5, 1);

-- ============================================================
-- ACTIVOS
-- ============================================================
INSERT INTO activos (marca, descripcion, is_ciber, activa, fk_tipo_activo) VALUES
('HP', 'PC Escritorio HP ProDesk 400 G5', TRUE, TRUE, 1),
('Dell', 'Laptop Dell Latitude 5490', FALSE, TRUE, 2),
('Samsung', 'Monitor Samsung 24" Curvo', TRUE, TRUE, 3),
('HP', 'Impresora HP LaserJet M404', FALSE, TRUE, 4),
('Dell', 'Servidor Dell PowerEdge T340', FALSE, TRUE, 5),
('TP-Link', 'Router TP-Link Archer C80', TRUE, TRUE, 6),
('Cisco', 'Switch Cisco Catalyst 2960', TRUE, TRUE, 7),
('APC', 'UPS APC Back-UPS 1500VA', TRUE, TRUE, 8),
('Lenovo', 'PC Escritorio Lenovo ThinkCentre', TRUE, TRUE, 1),
('LG', 'Monitor LG 27" 4K', FALSE, TRUE, 3);

-- ============================================================
-- SESION_CIBER
-- ============================================================
INSERT INTO sesion_ciber (tiempo_uso, fk_cliente, fk_tarifa, fk_activo) VALUES
('01:30:00', 1, 3, NULL),
('00:45:00', 3, 2, NULL),
('02:00:00', 4, 4, NULL),
('01:00:00', 6, 2, NULL),
('00:30:00', 7, 1, NULL),
('03:00:00', 9, 5, NULL),
('00:50:00', 11, 1, 1),
('02:30:00', 12, 4, 3),
('01:15:00', 14, 2, 6),
('00:20:00', 16, 1, NULL),
('04:00:00', 18, 5, 9),
('01:45:00', 20, 3, 7),
('00:35:00', 22, 1, 3),
('02:15:00', 24, 4, 1),
('01:10:00', 25, 2, NULL),
('00:55:00', 5, 2, 6);

-- ============================================================
-- MÁS CLIENTES
-- ============================================================
INSERT INTO clientes (cedula, nombre, apellido, direccion, telefono) VALUES
('V-11111111', 'Jorge', 'Ramírez', 'Av. Las Américas, San Cristóbal', '0412-1111111'),
('V-22222222', 'Laura', 'Mendoza', 'Calle 7, Cabudare', '0414-2222222'),
('V-33333333', 'Andrés', 'Castillo', 'Urb. San José, Barinas', '0424-3333333'),
('V-44444444', 'Valentina', 'Rojas', 'Av. Libertador, Maturín', '0416-4444444'),
('J-55555555', 'Distribuidora del Sur C.A.', 'S/N', 'Zona Comercial, Ciudad Bolívar', '0285-5555555'),
('V-66666666', 'Diego', 'Morales', 'Calle 12, Coro', '0412-6666666'),
('V-77777777', 'Camila', 'Herrera', 'Urb. Villa Jardín, Barcelona', '0414-7777777'),
('E-88888888', 'Miguel', 'Álvarez', 'Av. Principal, Cumaná', '0426-8888888'),
('J-99999999', 'Comercial del Centro C.A.', 'S/N', 'Centro Comercial Gigante, Maracay', '0243-9999999'),
('V-10101010', 'Gabriela', 'Medina', 'Calle 3, Punto Fijo', '0412-1010101'),
('V-11121314', 'Fernando', 'Moreno', 'Av. Bolívar, Guanare', '0414-1112131'),
('E-12131415', 'Sara', 'Cruz', 'Urb. Las Flores, Acarigua', '0426-1213141'),
('J-13141516', 'Inversiones Miranda S.A.', 'S/N', 'Zona Industrial, Los Teques', '0212-1314151'),
('V-14151617', 'Ricardo', 'Peña', 'Calle 8, Tucupita', '0416-1415161'),
('V-15161718', 'Andrea', 'Flores', 'Av. Intercomunal, Puerto La Cruz', '0412-1516171');

-- ============================================================
-- MÁS CLIENTE_ASESORIA
-- ============================================================
INSERT INTO cliente_asesoria (fk_cliente, email, rif, tipo) VALUES
(11, 'jorger@email.com', 'V-11111111', 'civil'),
(13, 'andresc@email.com', 'V-33333333', 'civil'),
(15, 'distribuidorasur@email.com', 'J-55555555', 'juridico'),
(17, 'camilah@email.com', 'V-77777777', 'civil'),
(20, 'gabrielam@email.com', 'V-10101010', 'civil'),
(23, 'inversionesmiranda@email.com', 'J-13141516', 'juridico'),
(14, 'valentinar@email.com', 'V-44444444', 'civil'),
(19, 'comercialcentro@email.com', 'J-99999999', 'juridico'),
(25, 'andreaf@email.com', 'V-15161718', 'civil'),
(12, 'lauram@email.com', 'V-22222222', 'civil');

-- ============================================================
-- MÁS PROVEEDORES
-- ============================================================
INSERT INTO proveedores (rif, nombre, email, telefono) VALUES
('J-11111111-1', 'Distribuidora Elektron C.A.', 'ventas@elektron.com', '0241-1111111'),
('J-22222222-2', 'TecnoMundo C.A.', 'info@tecnomundo.com', '0212-2222222'),
('V-33333333-3', 'Carlos Briceño Suministros', 'cbriceno@email.com', '0414-3333333'),
('J-44444444-4', 'PapelTech C.A.', 'pedidos@papeltech.com', '0261-4444444'),
('J-55555555-5', 'Juguetería La Volanta C.A.', 'ventas@lavolanta.com', '0241-5555555'),
('V-66666666-6', 'Bisutería y Accesorios D''Lujo', 'info@dlujo.com', '0412-6666666'),
('J-77777777-7', 'Redes y Comunicaciones C.A.', 'ventas@redesycom.com', '0212-7777777'),
('J-88888888-8', 'Suministros Industriales C.A.', 'pedidos@sumindca.com', '0261-8888888'),
('V-99999999-9', 'María Delgado Repuestos', 'madelgado@email.com', '0416-9999999'),
('J-00000000-1', 'LogiTech Solutions C.A.', 'logitech@logitechsol.com', '0212-0000000'),
('J-12121212-1', 'Componentes y Partes C.A.', 'ventas@compypar.com', '0241-1212121'),
('V-34343434-3', 'Pedro Linares Electrónica', 'plinares@email.com', '0414-3434343');

-- ============================================================
-- MÁS TARIFAS
-- ============================================================
INSERT INTO tarifas (tarifa_hora, precio_tiempo) VALUES
(6.00, 12.00),
(8.00, 15.00),
(10.00, 18.00);

-- ============================================================
-- MÁS TIPO_ACTIVO
-- ============================================================
INSERT INTO tipo_activo (nombre_tipo) VALUES
('Tablet'),
('Escáner'),
('Firewall'),
('Cámara IP');

-- ============================================================
-- MÁS USUARIOS
-- ============================================================
INSERT INTO usuarios (nombre, apellido, user_name, password_hash, email, estatus, fk_rol_usuario) VALUES
('Luis', 'Marcano', 'lmarcano', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'lmarcano@zonaweb.com', '1', 2),
('Carmen', 'Suárez', 'csuarez', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'csuarez@zonaweb.com', '1', 3),
('José', 'Blanco', 'jblanco', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'jblanco@zonaweb.com', '1', 4),
('Rosa', 'Márquez', 'rmarquez', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'rmarquez@zonaweb.com', '1', 5),
('David', 'Contreras', 'dcontreras', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'dcontreras@zonaweb.com', '1', 2),
('Elena', 'Vásquez', 'evasquez', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'evasquez@zonaweb.com', '1', 6),
('Francisco', 'Torrealba', 'ftorrealba', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'ftorrealba@zonaweb.com', '1', 7),
('Isabel', 'Cedeño', 'icedeno', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'icedeno@zonaweb.com', '1', 8),
('Gabriel', 'Paredes', 'gparedes', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'gparedes@zonaweb.com', '1', 2),
('Alejandra', 'Urbina', 'aurbina', '$2y$10$bca1qYoXF3KYxsot7bVU1O3GbR5/4KAyStTHFUrK1tJL4wiD4zimm', 'aurbina@zonaweb.com', '1', 3);

-- ============================================================
-- MÁS PRODUCTOS (Tecnología adicional)
-- ============================================================
INSERT INTO productos (codigo, nombre, descripcion, stock, stock_minimo, precio_compra, precio_venta, fecha_creacion, fecha_actualizacion, fk_categoria) VALUES
('LAP-003', 'Laptop ASUS VivoBook 15', 'Intel Core i3, 8GB RAM, 256GB SSD, 15.6"', 12, 3, 380.00, 550.00, '2026-03-01', '2026-06-15', 1),
('LAP-004', 'Laptop MacBook Air M2', 'Apple M2, 8GB RAM, 256GB SSD, 13.6"', 6, 2, 950.00, 1450.00, '2026-03-10', '2026-06-16', 1),
('LAP-005', 'Laptop Dell Inspiron 16', 'Intel Core i7, 16GB RAM, 1TB SSD, 16"', 7, 2, 780.00, 1100.00, '2026-04-01', '2026-06-15', 1),
('PCD-003', 'PC Desktop HP Pavilion', 'Intel Core i3, 8GB RAM, 256GB SSD', 8, 2, 280.00, 420.00, '2026-04-05', '2026-06-14', 2),
('PCD-004', 'PC Desktop Gamer ASUS', 'Ryzen 5, 16GB RAM, 512GB SSD, RTX 3050', 4, 1, 850.00, 1350.00, '2026-04-10', '2026-06-16', 2),
('MON-003', 'Monitor Acer 21.5"', 'Full HD, VA Panel, 60Hz', 25, 8, 85.00, 130.00, '2026-04-01', '2026-06-13', 3),
('MON-004', 'Monitor Dell 27" 4K', '3840x2160, IPS, USB-C', 8, 2, 350.00, 520.00, '2026-04-15', '2026-06-15', 3),
('TEC-003', 'Teclado Gamer HyperX', 'Switch Red, RGB, Teclado numérico', 22, 6, 40.00, 70.00, '2026-04-05', '2026-06-12', 4),
('MOU-003', 'Mouse Pad XXL', '800x300mm, Superficie suave', 50, 15, 5.00, 12.00, '2026-04-01', '2026-06-10', 5),
('AUD-003', 'Audífonos Redragon Pandora', 'Gamer, RGB, 7.1 virtual', 25, 8, 30.00, 55.00, '2026-04-10', '2026-06-14', 6),
('COM-004', 'Disco Duro Externo 1TB', 'USB 3.0, Portátil', 30, 8, 35.00, 60.00, '2026-04-01', '2026-06-13', 7),
('COM-005', 'Tarjeta Gráfica GTX 1650', '4GB GDDR6, OC Edition', 8, 2, 180.00, 290.00, '2026-04-05', '2026-06-15', 7),
('RED-003', 'Access Point TP-Link EAP225', 'WiFi 5, PoE, Gigabit', 12, 3, 45.00, 75.00, '2026-04-10', '2026-06-14', 8),
('IMP-003', 'Impresora Multifuncional Canon', 'Inyección, WiFi, Escáner', 10, 3, 80.00, 140.00, '2026-04-15', '2026-06-15', 9),
('ACC-002', 'Cable HDMI 2m', 'Cobre, 4K@60Hz, Blindado', 100, 30, 2.00, 5.00, '2026-04-01', '2026-06-10', 10),
('ACC-003', 'Webcam Logitech C920', '1080p, Micrófono integrado', 15, 4, 40.00, 70.00, '2026-04-10', '2026-06-14', 10);

-- ============================================================
-- MÁS PRODUCTOS (Papelería adicional)
-- ============================================================
INSERT INTO productos (codigo, nombre, descripcion, stock, stock_minimo, precio_compra, precio_venta, fecha_creacion, fecha_actualizacion, fk_categoria) VALUES
('PAP-016', 'Saca Grapas Metálico', 'Acero, ergonómico', 45, 10, 1.00, 2.50, '2026-04-05', '2026-06-12', 11),
('PAP-017', 'Perforadora 3 Agujeros', 'Metálica, capacidad 30 hojas', 25, 5, 3.50, 7.00, '2026-04-10', '2026-06-13', 11),
('PAP-018', 'Block Notas Adhesivas', 'Pack x 5 colores, 200 hojas c/u', 70, 15, 2.50, 5.00, '2026-04-15', '2026-06-14', 11),
('PAP-019', 'Sobre Manila Carta', 'Pack x 50, papel kraft', 80, 20, 2.00, 4.50, '2026-04-20', '2026-06-15', 11),
('PAP-020', 'Corrector Líquido', '21ml, secado rápido', 60, 15, 1.20, 2.80, '2026-04-25', '2026-06-15', 11);

-- ============================================================
-- MÁS PRODUCTOS (Juguetería adicional)
-- ============================================================
INSERT INTO productos (codigo, nombre, descripcion, stock, stock_minimo, precio_compra, precio_venta, fecha_creacion, fecha_actualizacion, fk_categoria) VALUES
('JUG-013', 'Yo-Yo Profesional', 'Aluminio, rodamiento sellado', 35, 8, 2.00, 5.00, '2026-04-05', '2026-06-12', 12),
('JUG-014', 'Set de Dinosaurios', '6 figuras, 10-15cm c/u', 25, 6, 5.00, 12.00, '2026-04-10', '2026-06-13', 12),
('JUG-015', 'Avión de Juguete', 'Plástico, luces LED, sonido', 20, 5, 4.00, 10.00, '2026-04-15', '2026-06-14', 12),
('JUG-016', 'Burbujas Gigantes', 'Frasco 500ml, varita incluida', 40, 10, 1.50, 3.50, '2026-04-20', '2026-06-15', 12);

-- ============================================================
-- MÁS PRODUCTOS (Bisutería adicional)
-- ============================================================
INSERT INTO productos (codigo, nombre, descripcion, stock, stock_minimo, precio_compra, precio_venta, fecha_creacion, fecha_actualizacion, fk_categoria) VALUES
('BIS-013', 'Pulsera Hilo Encerado', 'Nudo marinero, cierre ajustable', 50, 12, 1.00, 3.00, '2026-04-05', '2026-06-12', 13),
('BIS-014', 'Collar Colgante Hoja', 'Acero inoxidable, cadena 45cm', 30, 8, 3.50, 9.00, '2026-04-10', '2026-06-13', 13),
('BIS-015', 'Aros Aro Grande', 'Dorado, 5cm diámetro', 35, 10, 2.50, 6.00, '2026-04-15', '2026-06-14', 13),
('BIS-016', 'Set Maquillaje Infantil', 'Maletín 12 piezas, hipoalergénico', 20, 5, 5.00, 12.00, '2026-04-20', '2026-06-15', 13);

-- ============================================================
-- MÁS ÓRDENES DE VENTA
-- ============================================================
INSERT INTO orden_de_venta (numero_de_orden, fecha, fk_usuario, fk_cliente) VALUES
('VTA-0011', '2026-06-17', 2, 11),
('VTA-0012', '2026-06-18', 3, 12),
('VTA-0013', '2026-06-19', 7, 13),
('VTA-0014', '2026-06-20', 2, 14),
('VTA-0015', '2026-06-21', 3, 15),
('VTA-0016', '2026-06-22', 7, 16),
('VTA-0017', '2026-06-23', 2, 17),
('VTA-0018', '2026-06-24', 3, 18),
('VTA-0019', '2026-06-25', 7, 19),
('VTA-0020', '2026-06-26', 2, 20),
('VTA-0021', '2026-06-17', 8, 21),
('VTA-0022', '2026-06-18', 9, 22),
('VTA-0023', '2026-06-19', 10, 23),
('VTA-0024', '2026-06-20', 8, 24),
('VTA-0025', '2026-06-21', 9, 25),
('VTA-0026', '2026-06-22', 10, 1),
('VTA-0027', '2026-06-23', 8, 3),
('VTA-0028', '2026-06-24', 9, 5),
('VTA-0029', '2026-06-25', 10, 7),
('VTA-0030', '2026-06-26', 11, 9);

-- ============================================================
-- MÁS LINEAS_VENTA
-- ============================================================
INSERT INTO lineas_venta (cantidad, precio, fk_orden, fk_producto) VALUES
-- VTA-0011: Cliente 11
(1, 550.00, 11, 60),
(2, 130.00, 11, 64),
(1, 55.00, 11, 68),
-- VTA-0012: Cliente 12
(1, 1100.00, 12, 62),
(1, 70.00, 12, 67),
(2, 12.00, 12, 69),
-- VTA-0013: Cliente 13
(3, 6.00, 13, 21),
(5, 4.00, 13, 24),
(2, 7.00, 13, 26),
(10, 1.20, 13, 34),
-- VTA-0014: Cliente 14
(1, 1450.00, 14, 61),
(1, 75.00, 14, 70),
-- VTA-0015: Cliente 15 (Jurídico)
(5, 650.00, 15, 1),
(3, 180.00, 15, 5),
(10, 15.00, 15, 20),
(8, 45.00, 15, 7),
-- VTA-0016: Cliente 16
(1, 520.00, 16, 64),
(2, 60.00, 16, 70),
(3, 12.00, 16, 83),
-- VTA-0017: Cliente 17
(2, 18.00, 17, 36),
(1, 40.00, 17, 39),
(1, 28.00, 17, 41),
(3, 16.00, 17, 42),
-- VTA-0018: Cliente 18
(1, 8.00, 18, 48),
(3, 5.50, 18, 50),
(2, 10.00, 18, 52),
(1, 30.00, 18, 57),
-- VTA-0019: Cliente 19 (Jurídico)
(20, 5.00, 19, 73),
(15, 2.80, 19, 75),
(10, 7.00, 19, 34),
-- VTA-0020: Cliente 20
(1, 14.00, 20, 38),
(1, 10.00, 20, 84),
(2, 6.00, 20, 88),
(3, 3.00, 20, 89),
-- VTA-0021: Cliente 21
(2, 550.00, 21, 60),
(1, 420.00, 21, 63),
(4, 38.00, 21, 9),
-- VTA-0022: Cliente 22
(1, 1350.00, 22, 65),
(2, 55.00, 22, 68),
(1, 70.00, 22, 72),
-- VTA-0023: Cliente 23 (Jurídico)
(3, 520.00, 23, 66),
(8, 75.00, 23, 70),
(15, 5.00, 23, 74),
(5, 60.00, 23, 71),
-- VTA-0024: Cliente 24
(2, 7.00, 24, 37),
(1, 15.00, 24, 40),
(4, 3.50, 24, 88),
(2, 5.00, 24, 43),
-- VTA-0025: Cliente 25
(1, 8.00, 25, 48),
(1, 25.00, 25, 56),
(3, 9.00, 25, 90),
(2, 12.00, 25, 92),
-- VTA-0026: Cliente 1
(1, 650.00, 26, 1),
(2, 32.00, 26, 10),
(1, 90.00, 26, 11),
-- VTA-0027: Cliente 3
(2, 180.00, 27, 5),
(1, 310.00, 27, 6),
(3, 45.00, 27, 7),
-- VTA-0028: Cliente 5
(10, 6.00, 28, 21),
(20, 4.00, 28, 24),
(5, 8.00, 28, 31),
(8, 5.50, 28, 30),
-- VTA-0029: Cliente 7
(1, 18.00, 29, 36),
(2, 14.00, 29, 38),
(1, 28.00, 29, 41),
(3, 12.00, 29, 44),
-- VTA-0030: Cliente 9
(2, 30.00, 30, 57),
(4, 5.00, 30, 88),
(1, 15.00, 30, 53),
(5, 3.50, 30, 89);

-- ============================================================
-- MÁS ÓRDENES DE ABASTECIMIENTO
-- ============================================================
INSERT INTO orden_abastecimiento (numero_de_orden, fecha, fk_proveedor, fk_status) VALUES
('OC-0009', '2026-06-18', 2, 2),
('OC-0010', '2026-06-19', 5, 1),
('OC-0011', '2026-06-20', 10, 4),
('OC-0012', '2026-06-21', 6, 3),
('OC-0013', '2026-06-22', 11, 2),
('OC-0014', '2026-06-23', 12, 1),
('OC-0015', '2026-06-24', 4, 5),
('OC-0016', '2026-06-25', 8, 5),
('OC-0017', '2026-06-26', 3, 3),
('OC-0018', '2026-06-27', 7, 2),
('OC-0019', '2026-06-28', 10, 1),
('OC-0020', '2026-06-29', 9, 4);

-- ============================================================
-- MÁS LINEAS_ABASTECIMIENTO
-- ============================================================
INSERT INTO lineas_abastecimiento (cantidad, precio, fk_orden_abastecimiento, fk_producto) VALUES
(8, 380.00, 9, 60),
(10, 85.00, 9, 64),
(15, 40.00, 10, 67),
(20, 5.00, 10, 69),
(25, 8.00, 11, 36),
(30, 3.00, 11, 37),
(12, 2.00, 11, 74),
(10, 3.50, 12, 90),
(15, 2.50, 12, 88),
(8, 5.00, 12, 92),
(20, 1.20, 13, 75),
(30, 3.50, 13, 34),
(15, 2.00, 13, 73),
(5, 850.00, 14, 65),
(10, 35.00, 14, 71),
(8, 40.00, 15, 72),
(12, 45.00, 15, 16),
(6, 180.00, 16, 17),
(20, 25.00, 16, 7),
(15, 30.00, 17, 8),
(10, 55.00, 17, 11),
(12, 280.00, 18, 63),
(8, 180.00, 18, 15),
(15, 950.00, 19, 61),
(20, 350.00, 19, 66),
(10, 130.00, 20, 19),
(25, 40.00, 20, 20),
(30, 5.00, 20, 74);

-- ============================================================
-- MÁS ASESORIA
-- ============================================================
INSERT INTO asesoria (documento, descripcion, fecha, fk_cliente_asesoria, fk_tipo_asesoria) VALUES
('ASES-006', 'Asesoría para implementación de red WiFi corporativa', '2026-06-14', 6, 1),
('ASES-007', 'Capacitación en uso de herramientas digitales para facturación', '2026-06-16', 7, 5),
('ASES-008', 'Diagnóstico de seguridad informática y recomendaciones', '2026-06-18', 8, 3),
('ASES-009', 'Asesoría para registro de marca y propiedad intelectual', '2026-06-20', 9, 4),
('ASES-010', 'Configuración de servidor NAS y backups automatizados', '2026-06-22', 10, 2),
('ASES-011', 'Asesoría legal para constitución de empresa tecnológica', '2026-06-24', 3, 2),
('ASES-012', 'Optimización de base de datos MySQL para tienda en línea', '2026-06-26', 4, 3),
('ASES-013', 'Migración de sistema contable a plataforma cloud', '2026-06-28', 5, 5),
('ASES-014', 'Asesoría en ciberseguridad para protección de datos sensibles', '2026-06-30', 8, 3),
('ASES-015', 'Revisión y actualización de infraestructura tecnológica', '2026-07-02', 6, 1);

-- ============================================================
-- MÁS ACTIVOS
-- ============================================================
INSERT INTO activos (marca, descripcion, is_ciber, activa, fk_tipo_activo) VALUES
('HP', 'Laptop HP Pavilion 15 Gaming', FALSE, TRUE, 2),
('Lenovo', 'Tablet Lenovo Tab M10', TRUE, TRUE, 9),
('Epson', 'Escáner Epson Perfection V39', FALSE, TRUE, 10),
('Fortinet', 'Firewall FortiGate 60F', TRUE, TRUE, 11),
('Hikvision', 'Cámara IP Hikvision 4MP', TRUE, TRUE, 12),
('Dell', 'Monitor Dell 22" Profesional', TRUE, TRUE, 3),
('Cisco', 'Access Point Cisco Aironet', TRUE, TRUE, 6),
('APC', 'UPS APC Smart-UPS 1000VA', TRUE, TRUE, 8),
('HP', 'Impresora HP OfficeJet Pro', FALSE, TRUE, 4),
('Apple', 'iMac 24" M1 All-in-One', FALSE, TRUE, 1),
('Samsung', 'Monitor Samsung 32" Curvo 4K', TRUE, TRUE, 3),
('Synology', 'Servidor NAS Synology DS220+', FALSE, TRUE, 5),
('TP-Link', 'Router TP-Link Deco X60 Mesh', TRUE, TRUE, 6),
('HP', 'PC Desktop HP EliteDesk 800', TRUE, TRUE, 1);
