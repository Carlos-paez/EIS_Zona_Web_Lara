USE zwl;

-- Insert usuarios
INSERT INTO usuarios (nombre, email) VALUES
('Juan Pérez', 'juan.perez@email.com'),
('María García', 'maria.garcia@email.com'),
('Carlos López', 'carlos.lopez@email.com'),
('Ana Martínez', 'ana.martinez@email.com'),
('Luis Rodríguez', 'luis.rodriguez@email.com');

-- Insert proveedores
INSERT INTO proveedores (nombre, contacto, email, telefono) VALUES
('TechSupply S.A.', 'Roberto Díaz', 'contacto@techsupply.com', '555-0101'),
('Oficina Total', 'Carmen Ruiz', 'ventas@oficinatotal.com', '555-0102'),
('Insumos Cyber', 'Miguel Torres', 'info@insumoscyber.com', '555-0103'),
('Licencias Pro', 'Sofia Vargas', 'sofia@licenciaspro.com', '555-0104');

-- Insert productos
INSERT INTO productos (codigo, codigo_barras, nombre, marca, categoria, descripcion, stock, stock_minimo, ubicacion, unidad_medida, costo_compra, precio_venta, iva, margen_ganancia, permite_descuento, estado, estado_venta) VALUES
('MOU-001', NULL, 'Mouse Inalámbrico Logitech', 'Logitech', 'Accesorios', 'Mouse inalámbrico 2.4GHz', 30, 5, 'Estante A-01', 'Unidades', 15.00, 25.50, 16.00, 70.00, TRUE, 'OK', 'Activo'),
('TEC-001', NULL, 'Teclado Mecánico RGB', 'Logitech', 'Accesorios', 'Teclado mecánico con iluminación RGB', 15, 5, 'Estante A-02', 'Unidades', 50.00, 85.00, 16.00, 70.00, TRUE, 'OK', 'Activo'),
('MON-001', NULL, 'Monitor LED 24"', 'Samsung', 'Monitores', 'Monitor LED 24 pulgadas Full HD', 8, 3, 'Estante B-01', 'Unidades', 180.00, 250.00, 16.00, 38.89, TRUE, 'OK', 'Activo'),
('CAB-001', NULL, 'Cable HDMI 2m', 'Generic', 'Cables', 'Cable HDMI 2 metros alta velocidad', 50, 10, 'Estante B-02', 'Unidades', 5.00, 12.00, 16.00, 140.00, TRUE, 'OK', 'Activo'),
('SIL-001', NULL, 'Silla Ergonómica', 'Generic', 'Muebles', 'Silla ergonómica de oficina', 4, 5, 'Estante C-01', 'Unidades', 200.00, 320.00, 16.00, 60.00, TRUE, 'Crítico', 'Activo'),
('PAP-001', NULL, 'Papel Bond A4 (500 hojas)', 'Bond', 'Papelería', 'Papel bond tamaño carta 500 hojas', 100, 20, 'Estante C-02', 'Unidades', 4.00, 8.50, 16.00, 112.50, TRUE, 'OK', 'Activo'),
('TON-001', NULL, 'Tóner HP 85A', 'HP', 'Insumos', 'Tóner compatible con impresoras HP', 2, 5, 'Estante D-01', 'Unidades', 35.00, 65.00, 16.00, 85.71, TRUE, 'Crítico', 'Activo'),
('SSD-001', NULL, 'Disco SSD 500GB', 'Kingston', 'Almacenamiento', 'Disco sólido 500GB SATA III', 0, 3, 'Estante D-02', 'Unidades', 50.00, 80.00, 16.00, 60.00, TRUE, 'Sin stock', 'Activo'),
('RAM-001', NULL, 'Memoria RAM 8GB DDR4', 'Kingston', 'Componentes', 'Memoria RAM 8GB DDR4 2400MHz', 20, 5, 'Estante D-03', 'Unidades', 25.00, 45.00, 16.00, 80.00, TRUE, 'OK', 'Activo'),
('LAP-001', NULL, 'Laptop Gamer MSI', 'MSI', 'Computadoras', 'Laptop para gaming con tarjeta dedicada', 3, 2, 'Estante E-01', 'Unidades', 900.00, 1200.00, 16.00, 33.33, TRUE, 'Crítico', 'Activo');

-- Insert activos
INSERT INTO activos (nombre, tipo, estado, fecha_adquisicion, fecha_vencimiento) VALUES
('Laptop Dell Latitude', 'Equipos', 'Activo', '2024-01-15', NULL),
('Taladro Bosch', 'Herramientas', 'Activo', '2023-06-10', NULL),
('Licencia Windows 10 Pro', 'Licencias', 'Activo', '2024-02-01', '2026-02-01'),
('Impresora HP LaserJet', 'Equipos', 'Mantenimiento', '2023-09-20', NULL),
('Licencia Adobe Photoshop', 'Licencias', 'Vencida', '2023-01-10', '2025-01-10'),
('Kit Destornilladores', 'Herramientas', 'Activo', '2024-03-05', NULL);

-- Insert estaciones_cyber
INSERT INTO estaciones_cyber (nombre, estado, tipo) VALUES
('PC-01', 'Disponible', 'Gaming'),
('PC-02', 'Ocupada', 'Gaming'),
('PC-03', 'Disponible', 'Oficina'),
('PC-04', 'Mantenimiento', 'Gaming'),
('PC-05', 'Disponible', 'Oficina'),
('PC-06', 'Ocupada', 'Gaming');

-- Insert ventas
INSERT INTO ventas (fecha, total, usuario_id, estado) VALUES
('2025-04-15 10:30:00', 110.50, 1, 'completada'),
('2025-04-16 14:20:00', 250.00, 2, 'completada'),
('2025-04-17 09:15:00', 37.00, 1, 'completada'),
('2025-04-18 16:45:00', 85.00, 3, 'pendiente'),
('2025-04-19 11:00:00', 130.00, NULL, 'completada'),
('2025-04-20 13:30:00', 25.50, 4, 'cancelada');

-- Insert detalle_ventas
INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 2, 25.50, 51.00),
(1, 6, 7, 8.50, 59.50),
(2, 3, 1, 250.00, 250.00),
(3, 4, 3, 12.00, 36.00),
(3, 6, 1, 8.50, 8.50),
(4, 2, 1, 85.00, 85.00),
(5, 9, 2, 45.00, 90.00),
(5, 4, 2, 12.00, 24.00),
(5, 6, 2, 8.00, 16.00),
(6, 1, 1, 25.50, 25.50);

-- Insert solicitudes
INSERT INTO solicitudes (codigo, proveedor_id, fecha, estado, usuario_id) VALUES
('SOL-001', 1, '2025-04-10', 'Recibida', 1),
('SOL-002', 2, '2025-04-12', 'Pendiente', 2),
('SOL-003', 3, '2025-04-14', 'Pendiente', 1),
('SOL-004', 1, '2025-04-16', 'Cancelada', 3),
('SOL-005', 4, '2025-04-18', 'Recibida', 2);

-- Insert sesiones_cyber
INSERT INTO sesiones_cyber (estacion_id, usuario_id, hora_inicio, hora_fin, duracion_minutos, costo) VALUES
(1, 1, '2025-04-20 08:00:00', '2025-04-20 10:30:00', 150, 15.00),
(2, 3, '2025-04-20 09:00:00', NULL, NULL, NULL),
(3, 2, '2025-04-20 10:15:00', '2025-04-20 12:15:00', 120, 12.00),
(5, 4, '2025-04-20 11:00:00', '2025-04-20 13:45:00', 165, 16.50),
(6, 5, '2025-04-20 08:30:00', NULL, NULL, NULL),
(2, 1, '2025-04-20 14:00:00', '2025-04-20 16:00:00', 120, 12.00);

-- Insert movimientos_stock
INSERT INTO movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, fecha, usuario_id, motivo) VALUES
(1, 'entrada', 20, 10, 30, '2025-04-01 08:00:00', 1, 'Compra inicial'),
(2, 'entrada', 15, 0, 15, '2025-04-01 09:00:00', 1, 'Compra inicial'),
(3, 'entrada', 8, 0, 8, '2025-04-02 10:00:00', 2, 'Compra inicial'),
(5, 'entrada', 10, 0, 10, '2025-04-02 11:00:00', 2, 'Compra inicial'),
(5, 'salida', 6, 10, 4, '2025-04-15 15:00:00', 3, 'Venta directa'),
(7, 'entrada', 5, 0, 5, '2025-04-03 08:30:00', 1, 'Compra inicial'),
(7, 'salida', 3, 5, 2, '2025-04-18 10:00:00', 2, 'Uso interno'),
(8, 'entrada', 10, 0, 10, '2025-04-03 09:00:00', 1, 'Compra inicial'),
(8, 'salida', 10, 10, 0, '2025-04-10 14:00:00', 3, 'Venta agotada'),
(9, 'entrada', 20, 0, 20, '2025-04-04 08:00:00', 2, 'Compra inicial'),
(10, 'entrada', 5, 0, 5, '2025-04-04 10:00:00', 2, 'Compra inicial'),
(10, 'salida', 2, 5, 3, '2025-04-19 11:30:00', 1, 'Venta'),
(4, 'ajuste', 50, 0, 50, '2025-04-05 08:00:00', 1, 'Ajuste de inventario inicial');
