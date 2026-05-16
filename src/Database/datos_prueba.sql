-- ============================================================
-- DATOS DE PRUEBA - Sistema ZWL v2.0
-- ============================================================

USE zwl;

-- ============================================================
-- CATÁLOGOS
-- ============================================================

INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Acceso total al sistema'),
('Vendedor', 'Gestión de ventas e inventario'),
('Cyber', 'Acceso solo al módulo de cybercafé'),
('Asesor', 'Acceso al módulo de asesorías legales'),
('Consultor', 'Acceso solo de lectura');

INSERT INTO tipos_pago (nombre) VALUES
('Efectivo'), ('Transferencia'), ('Punto de Venta'), ('Mixto'), ('Crédito');

INSERT INTO categorias (nombre, descripcion) VALUES
('Accesorios', 'Periféricos y accesorios de computación'),
('Monitores', 'Pantallas y monitores'),
('Cables', 'Cables y adaptadores'),
('Muebles', 'Muebles de oficina y ergonomía'),
('Papelería', 'Artículos de oficina y papel'),
('Insumos', 'Tóner, tintas y consumibles'),
('Almacenamiento', 'Discos duros, SSD, memorias USB'),
('Componentes', 'Partes internas de computadoras'),
('Computadoras', 'Equipos completos');

INSERT INTO marcas (nombre, descripcion) VALUES
('Logitech', 'Periféricos y accesorios'),
('Samsung', 'Electrónica y monitores'),
('HP', 'Impresoras y suministros'),
('Kingston', 'Memorias y almacenamiento'),
('MSI', 'Hardware gaming y laptops'),
('Bond', 'Artículos de papelería'),
('Generic', 'Productos genéricos');

INSERT INTO tipos_activo (nombre, descripcion) VALUES
('Equipos', 'Computadoras, laptops, servidores'),
('Herramientas', 'Herramientas manuales y eléctricas'),
('Licencias', 'Licencias de software'),
('Mobiliario', 'Escritorios, sillas, estanterías'),
('Vehículos', 'Vehículos de la empresa');

INSERT INTO tarifas_cyber (nombre, precio_por_hora, tiempo_minimo, activa) VALUES
('Gaming', 8.00, 60, TRUE),
('Oficina', 5.00, 30, TRUE),
('Premium', 12.00, 60, TRUE),
('Estudiante', 3.50, 30, TRUE);

-- ============================================================
-- USUARIOS (password_hash: bcrypt de '123456')
-- ============================================================

INSERT INTO usuarios (username, password_hash, nombre, email, telefono, rol_id) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Sistema', 'admin@zwl.local', '0412-0000001', 1),
('vendedor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 'juan.perez@email.com', '0412-0000002', 2),
('vendedor2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María García', 'maria.garcia@email.com', '0412-0000003', 2),
('cyber1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos López', 'carlos.lopez@email.com', '0412-0000004', 3),
('asesor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Martínez', 'ana.martinez@email.com', '0412-0000005', 4);

-- ============================================================
-- PROVEEDORES
-- ============================================================

INSERT INTO proveedores (nombre, rif, tipo_documento, contacto, email, telefono, direccion) VALUES
('TechSupply S.A.', 'J-12345678-9', 'J', 'Roberto Díaz', 'contacto@techsupply.com', '555-0101', 'Av. Principal, Edif. TechSupply, Piso 2'),
('Oficina Total C.A.', 'J-23456789-0', 'J', 'Carmen Ruiz', 'ventas@oficinatotal.com', '555-0102', 'Calle Los Mangos, Centro Of. Local 5'),
('Insumos Cyber 3000', 'J-34567890-1', 'J', 'Miguel Torres', 'info@insumoscyber.com', '555-0103', 'Urb. Industrial, Galpón 12'),
('Licencias Pro', 'V-12345678', 'V', 'Sofía Vargas', 'sofia@licenciaspro.com', '555-0104', 'CC CTro, Nivel 3, Of. 301'),
('Distribuidora XYZ', 'J-45678901-2', 'J', 'Pedro Rojas', 'pedro@distxyz.com', '555-0105', 'Zona Industrial Sur, Calle 7');

-- ============================================================
-- PRODUCTOS
-- ============================================================

INSERT INTO productos (codigo, codigo_barras, nombre, descripcion, categoria_id, marca_id, unidad_medida, stock, stock_minimo, ubicacion, costo_compra, precio_venta, iva, permite_descuento, estado_venta) VALUES
('MOU-001', '7501234567891', 'Mouse Inalámbrico Logitech', 'Mouse inalámbrico 2.4GHz con receptor USB', 1, 1, 'Unidades', 30, 5, 'Estante A-01', 15.00, 25.50, 16.00, TRUE, 'Activo'),
('TEC-001', '7501234567892', 'Teclado Mecánico RGB', 'Teclado mecánico con iluminación RGB personalizable', 1, 1, 'Unidades', 15, 5, 'Estante A-02', 50.00, 85.00, 16.00, TRUE, 'Activo'),
('MON-001', '7501234567893', 'Monitor LED 24" Samsung', 'Monitor LED 24 pulgadas Full HD 75Hz', 2, 2, 'Unidades', 8, 3, 'Estante B-01', 180.00, 250.00, 16.00, TRUE, 'Activo'),
('CAB-001', '7501234567894', 'Cable HDMI 2m', 'Cable HDMI 2 metros alta velocidad 4K', 3, 7, 'Unidades', 50, 10, 'Estante B-02', 5.00, 12.00, 16.00, TRUE, 'Activo'),
('SIL-001', '7501234567895', 'Silla Ergonómica', 'Silla ergonómica de oficina con soporte lumbar', 4, 7, 'Unidades', 4, 5, 'Estante C-01', 200.00, 320.00, 16.00, TRUE, 'Activo'),
('PAP-001', '7501234567896', 'Papel Bond A4 (500 hojas)', 'Papel bond tamaño carta 500 hojas 75g/m²', 5, 6, 'Unidades', 100, 20, 'Estante C-02', 4.00, 8.50, 16.00, TRUE, 'Activo'),
('TON-001', '7501234567897', 'Tóner HP 85A', 'Tóner compatible con impresoras HP LaserJet', 6, 3, 'Unidades', 2, 5, 'Estante D-01', 35.00, 65.00, 16.00, TRUE, 'Activo'),
('SSD-001', '7501234567898', 'Disco SSD 500GB Kingston', 'Disco sólido 500GB SATA III 2.5"', 7, 4, 'Unidades', 0, 3, 'Estante D-02', 50.00, 80.00, 16.00, TRUE, 'Activo'),
('RAM-001', '7501234567899', 'Memoria RAM 8GB DDR4', 'Memoria RAM 8GB DDR4 2400MHz', 8, 4, 'Unidades', 20, 5, 'Estante D-03', 25.00, 45.00, 16.00, TRUE, 'Activo'),
('LAP-001', '7501234567900', 'Laptop Gamer MSI Katana', 'Laptop gaming 15.6" i7 16GB RTX 3060', 9, 5, 'Unidades', 3, 2, 'Estante E-01', 900.00, 1200.00, 16.00, FALSE, 'Activo');

-- ============================================================
-- TABLA PUENTE: Producto-Proveedor
-- ============================================================

INSERT INTO producto_proveedor (producto_id, proveedor_id, codigo_proveedor, precio_compra, tiempo_entrega_dias, es_proveedor_principal) VALUES
(1, 1, 'MOU-LOG-001', 12.50, 3, TRUE),
(1, 3, 'INS-MOU-001', 14.00, 5, FALSE),
(2, 1, 'TEC-LOG-001', 45.00, 3, TRUE),
(3, 2, 'MON-SAM-001', 165.00, 7, TRUE),
(4, 3, 'CAB-HDM-001', 3.50, 2, TRUE),
(5, 2, 'SIL-ERG-001', 180.00, 10, TRUE),
(6, 2, 'PAP-BON-001', 3.20, 2, TRUE),
(7, 3, 'TON-HP-001', 28.00, 4, TRUE),
(8, 4, 'SSD-KIN-001', 42.00, 5, TRUE),
(9, 4, 'RAM-KIN-001', 20.00, 3, TRUE),
(10, 5, 'LAP-MSI-001', 850.00, 15, TRUE);

-- ============================================================
-- ACTIVOS FIJOS
-- ============================================================

INSERT INTO activos (nombre, descripcion, tipo_activo_id, estado, ubicacion, valor_adquisicion, fecha_adquisicion, fecha_vencimiento, responsable_id) VALUES
('Laptop Dell Latitude 3420', 'Laptop corporativa i5 16GB SSD 512GB', 1, 'Activo', 'Oficina Principal', 8500.00, '2024-01-15', NULL, 1),
('Taladro Bosch 18V', 'Taladro inalámbrico con batería de litio', 2, 'Activo', 'Taller', 2500.00, '2023-06-10', NULL, NULL),
('Licencia Windows 10 Pro', 'Licencia digital OEM', 3, 'Activo', 'Oficina Principal', 350.00, '2024-02-01', '2026-02-01', 1),
('Impresora HP LaserJet Pro', 'Impresora láser multifuncional', 1, 'Mantenimiento', 'Oficina Admin', 4200.00, '2023-09-20', NULL, 2),
('Licencia Adobe Creative Suite', 'Suite de diseño gráfico anual', 3, 'Vencida', 'Oficina Diseño', 1800.00, '2023-01-10', '2025-01-10', 3),
('Kit Destornilladores Precision', 'Juego de 32 piezas para electrónica', 2, 'Activo', 'Taller', 450.00, '2024-03-05', NULL, NULL),
('Servidor HP ProLiant DL380', 'Servidor corporativo 2U', 1, 'Activo', 'Cuarto Servidores', 35000.00, '2024-06-01', NULL, 1),
('Escritorio Ergonómico', 'Escritorio eléctrico ajustable', 4, 'Activo', 'Oficina Principal', 5500.00, '2024-08-15', NULL, NULL);

-- ============================================================
-- ESTACIONES CYBER
-- ============================================================

INSERT INTO estaciones_cyber (nombre, estado, tarifa_id, especificaciones, ip_local, mac_address) VALUES
('PC-01', 'Disponible', 1, 'i5-12400F / 16GB RAM / RTX 3060 / SSD 512GB', '192.168.1.10', 'AA:BB:CC:DD:01:01'),
('PC-02', 'Ocupada', 1, 'i5-12400F / 16GB RAM / RTX 3060 / SSD 512GB', '192.168.1.11', 'AA:BB:CC:DD:01:02'),
('PC-03', 'Disponible', 2, 'i3-12100 / 8GB RAM / SSD 256GB', '192.168.1.12', 'AA:BB:CC:DD:01:03'),
('PC-04', 'Mantenimiento', 3, 'i7-12700K / 32GB RAM / RTX 4080 / SSD 1TB', '192.168.1.13', 'AA:BB:CC:DD:01:04'),
('PC-05', 'Disponible', 2, 'i3-12100 / 8GB RAM / SSD 256GB', '192.168.1.14', 'AA:BB:CC:DD:01:05'),
('PC-06', 'Ocupada', 1, 'i5-12400F / 16GB RAM / RTX 3060 / SSD 512GB', '192.168.1.15', 'AA:BB:CC:DD:01:06');

-- ============================================================
-- VENTAS
-- ============================================================

INSERT INTO ventas (fecha, usuario_id, cliente_nombre, cliente_cedula, tipo_pago_id, subtotal, descuento, iva_total, total, estado) VALUES
('2025-04-15 10:30:00', 2, 'Luis Rodríguez', 'V-12345678', 1, 95.26, 0.00, 15.24, 110.50, 'completada'),
('2025-04-16 14:20:00', 3, 'Pedro Gómez', 'V-87654321', 2, 215.52, 0.00, 34.48, 250.00, 'completada'),
('2025-04-17 09:15:00', 2, 'Marta Sánchez', 'V-11223344', 1, 31.90, 0.00, 5.10, 37.00, 'completada'),
('2025-04-18 16:45:00', 3, 'José Contreras', 'E-55667788', 3, 73.28, 0.00, 11.72, 85.00, 'pendiente'),
('2025-04-19 11:00:00', NULL, 'Cliente Anónimo', NULL, 1, 112.07, 0.00, 17.93, 130.00, 'completada'),
('2025-04-20 13:30:00', 4, 'Test Cancelada', 'V-99887766', 1, 21.98, 0.00, 3.52, 25.50, 'cancelada');

-- ============================================================
-- DETALLE DE VENTAS
-- ============================================================

INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, iva_unitario, descuento, subtotal) VALUES
(1, 1, 2, 25.50, 3.52, 0.00, 51.00),
(1, 6, 7, 8.50, 1.17, 0.00, 59.50),
(2, 3, 1, 250.00, 34.48, 0.00, 250.00),
(3, 4, 3, 12.00, 1.66, 0.00, 36.00),
(3, 6, 1, 8.50, 1.17, 0.00, 8.50),
(4, 2, 1, 85.00, 11.72, 0.00, 85.00),
(5, 9, 2, 45.00, 6.21, 0.00, 90.00),
(5, 4, 2, 12.00, 1.66, 0.00, 24.00),
(5, 6, 2, 8.00, 1.10, 0.00, 16.00),
(6, 1, 1, 25.50, 3.52, 0.00, 25.50);

-- ============================================================
-- SOLICITUDES A PROVEEDORES
-- ============================================================

INSERT INTO solicitudes (codigo, proveedor_id, fecha, fecha_estimada_entrega, tipo_pago_id, subtotal, iva_total, total, estado, usuario_id) VALUES
('SOL-2026-0001', 1, '2025-04-10', '2025-04-17', 2, 500.00, 80.00, 580.00, 'Recibida', 1),
('SOL-2026-0002', 2, '2025-04-12', '2025-04-22', 1, 320.00, 51.20, 371.20, 'Pendiente', 2),
('SOL-2026-0003', 3, '2025-04-14', '2025-04-18', 2, 150.00, 24.00, 174.00, 'Pendiente', 1),
('SOL-2026-0004', 1, '2025-04-16', NULL, 3, 200.00, 32.00, 232.00, 'Cancelada', 3),
('SOL-2026-0005', 4, '2025-04-18', '2025-04-28', 2, 420.00, 67.20, 487.20, 'Recibida', 2);

-- ============================================================
-- DETALLE DE SOLICITUDES (Bridge Table)
-- ============================================================

INSERT INTO detalle_solicitudes (solicitud_id, producto_id, cantidad_solicitada, cantidad_recibida, precio_unitario_estimado, subtotal) VALUES
(1, 1, 20, 20, 12.50, 250.00),
(1, 2, 5, 5, 45.00, 225.00),
(1, 4, 10, NULL, 3.50, 35.00),
(2, 5, 2, NULL, 120.00, 240.00),
(2, 6, 20, NULL, 4.00, 80.00),
(3, 8, 5, NULL, 28.00, 140.00),
(3, 4, 5, NULL, 3.00, 15.00),
(4, 9, 10, NULL, 20.00, 200.00),
(5, 10, 1, 1, 420.00, 420.00);

-- ============================================================
-- SESIONES CYBER
-- ============================================================

INSERT INTO sesiones_cyber (estacion_id, usuario_id, cliente_nombre, tarifa_id, hora_inicio, hora_fin, costo_total, estado) VALUES
(1, 2, 'Luis Rodríguez', 1, '2025-04-20 08:00:00', '2025-04-20 10:30:00', 20.00, 'cerrada'),
(2, 3, 'Carlos López', 1, '2025-04-20 09:00:00', NULL, NULL, 'activa'),
(3, 2, 'María Pérez', 2, '2025-04-20 10:15:00', '2025-04-20 12:15:00', 10.00, 'cerrada'),
(5, 4, 'José Contreras', 2, '2025-04-20 11:00:00', '2025-04-20 13:45:00', 13.75, 'cerrada'),
(6, 3, 'Ana Martínez', 1, '2025-04-20 08:30:00', NULL, NULL, 'activa'),
(2, 2, 'Pedro Gómez', 1, '2025-04-20 14:00:00', '2025-04-20 16:00:00', 16.00, 'cerrada');

-- ============================================================
-- MOVIMIENTOS DE STOCK
-- ============================================================

INSERT INTO movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, precio_unitario, costo_total, fecha, usuario_id, referencia_tipo, referencia_id, motivo) VALUES
(1, 'entrada', 20, 10, 30, 15.00, 300.00, '2025-04-01 08:00:00', 1, 'solicitud', 1, 'Recepción SOL-2026-0001'),
(2, 'entrada', 15, 0, 15, 50.00, 750.00, '2025-04-01 09:00:00', 1, 'solicitud', 1, 'Recepción SOL-2026-0001'),
(3, 'entrada', 8, 0, 8, 180.00, 1440.00, '2025-04-02 10:00:00', 2, NULL, NULL, 'Compra inicial'),
(5, 'entrada', 10, 0, 10, 200.00, 2000.00, '2025-04-02 11:00:00', 2, NULL, NULL, 'Compra inicial'),
(5, 'salida', -6, 10, 4, 200.00, -1200.00, '2025-04-15 15:00:00', 3, 'venta', 2, 'Venta #2'),
(7, 'entrada', 5, 0, 5, 35.00, 175.00, '2025-04-03 08:30:00', 1, NULL, NULL, 'Compra inicial'),
(7, 'salida', -3, 5, 2, 35.00, -105.00, '2025-04-18 10:00:00', 2, 'ajuste', NULL, 'Uso interno'),
(8, 'entrada', 10, 0, 10, 50.00, 500.00, '2025-04-03 09:00:00', 1, NULL, NULL, 'Compra inicial'),
(8, 'salida', -10, 10, 0, 50.00, -500.00, '2025-04-10 14:00:00', 3, 'venta', 4, 'Venta #4'),
(9, 'entrada', 20, 0, 20, 25.00, 500.00, '2025-04-04 08:00:00', 2, NULL, NULL, 'Compra inicial'),
(10, 'entrada', 5, 0, 5, 900.00, 4500.00, '2025-04-04 10:00:00', 2, NULL, NULL, 'Compra inicial'),
(10, 'salida', -2, 5, 3, 900.00, -1800.00, '2025-04-19 11:30:00', 1, 'venta', 5, 'Venta #5'),
(4, 'ajuste', 50, 0, 50, 5.00, 250.00, '2025-04-05 08:00:00', 1, 'ajuste', NULL, 'Ajuste inventario inicial');

-- ============================================================
-- ASESORÍAS
-- ============================================================

INSERT INTO asesorias (ciudadano, cedula, documento, descripcion, estado, usuario_id, fecha_registro) VALUES
('María Fernanda Torres', 'V-12345678', 'DNI-2025-001', 'Asesoría sobre constitución de empresa mercantil tipo S.A.', 'Finalizada', 5, '2025-03-10 09:00:00'),
('José Antonio López', 'V-23456789', 'DNI-2025-002', 'Consulta sobre registro de propiedad intelectual de software', 'En Proceso', 5, '2025-03-15 10:30:00'),
('Carmen Elena Rivas', 'E-34567890', 'DNI-2025-003', 'Asesoría laboral sobre contratación de personal extranjero', 'Pendiente', 5, '2025-04-01 14:00:00'),
('Roberto Andrés Silva', 'V-45678901', 'DNI-2025-004', 'Revisión de contrato de arrendamiento comercial', 'Finalizada', 5, '2025-04-05 11:00:00'),
('Laura Valentina Méndez', 'V-56789012', 'DNI-2025-005', 'Asesoría fiscal sobre declaración de ISLR persona jurídica', 'Pendiente', NULL, '2025-04-10 15:45:00');
