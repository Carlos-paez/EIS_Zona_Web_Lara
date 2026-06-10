-- ============================================================
-- DATA DE PRUEBA (INSERTS) - Sistema ZWL v2.1
-- ============================================================
USE zwl;

-- Deshabilitar temporalmente llaves foráneas para una inserción limpia y segura
SET FOREIGN_KEY_CHECKS = 0;

-- Limpieza previa de tablas para evitar duplicados si se vuelve a ejecutar
TRUNCATE TABLE usuario_asesoria;
TRUNCATE TABLE asesorias;
TRUNCATE TABLE clientes_asesorias;
TRUNCATE TABLE sesiones_cyber;
TRUNCATE TABLE estaciones_cyber;
TRUNCATE TABLE bitacora_movimientos_stock;
TRUNCATE TABLE detalle_solicitudes;
TRUNCATE TABLE solicitudes;
TRUNCATE TABLE detalle_ventas;
TRUNCATE TABLE ventas;
TRUNCATE TABLE producto_proveedor;
TRUNCATE TABLE activos;
TRUNCATE TABLE productos;
TRUNCATE TABLE proveedores;
TRUNCATE TABLE clientes;
TRUNCATE TABLE usuarios;
TRUNCATE TABLE tarifas_cyber;
TRUNCATE TABLE tipos_activo;
TRUNCATE TABLE modelos;
TRUNCATE TABLE marcas;
TRUNCATE TABLE categorias;
TRUNCATE TABLE subcategorias;
TRUNCATE TABLE roles;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. MODULO DE CONFIGURACIÓN Y CATÁLOGOS (Lookup Tables)
-- ============================================================

-- Roles de usuario
INSERT INTO roles (id, nombre, descripcion) VALUES
(1, 'Administrador', 'Acceso total al sistema, configuraciones y reportes financieros.'),
(2, 'Operador', 'Atención al cliente en cybercafé, facturación de productos y apertura de sesiones.'),
(3, 'Asesor Legal', 'Gestión exclusiva de expedientes jurídicos y asignación de asesorías.');

-- Subcategorías (Nivel superior según la jerarquía solicitada)
INSERT INTO subcategorias (id, nombre, descripcion) VALUES
(1, 'Componentes de PC', 'Hardware interno y piezas de ensamblaje para computadoras.'),
(2, 'Periféricos', 'Dispositivos de entrada y salida de datos.'),
(3, 'Consumibles', 'Materiales de oficina y papelería indispensables.'),
(4, 'Servicios Digitales', 'Servicios de red, impresiones y navegación.');

-- Categorías (Se relacionan con Subcategorías)
INSERT INTO categorias (id, subcategoria_id, nombre, descripcion) VALUES
(1, 1, 'Almacenamiento SSD', 'Discos de estado sólido de alta velocidad (M.2, SATA).'),
(2, 1, 'Memorias RAM', 'Módulos de memoria para laptops y PCs de escritorio.'),
(3, 2, 'Teclados y Ratones', 'Accesorios de control periférico cableados e inalámbricos.'),
(4, 3, 'Papelería e Impresión', 'Hojas, tintas y servicios de copiado.'),
(5, 4, 'Tiempo de Cybercafé', 'Uso de estaciones de computación o consolas.');

-- Marcas
INSERT INTO marcas (id, nombre, descripcion) VALUES
(1, 'Kingston', 'Memorias y soluciones de almacenamiento.'),
(2, 'Logitech', 'Periféricos y accesorios de alta durabilidad.'),
(3, 'HP', 'Equipos de computación e insumos de impresión.'),
(4, 'Corsair', 'Componentes de alto rendimiento y gaming.');

-- Modelos (Se relacionan con Marcas)
INSERT INTO modelos (id, marca_id, nombre, descripcion) VALUES
(1, 1, 'A400 480GB', 'SSD SATA de 2.5 pulgadas.'),
(2, 1, 'Fury Beast DDR4 8GB', 'Módulo de memoria RAM de 3200MHz.'),
(3, 2, 'G203 Lightsync', 'Ratón cableado enfocado en gaming y oficina.'),
(4, 2, 'K120 USB', 'Teclado estándar resistente a salpicaduras.'),
(5, 3, 'LaserJet Pro M15w', 'Insumos relacionados con la línea de impresión láser.'),
(6, 4, 'Vengeance LPX 16GB', 'Kit de memoria RAM de alto rendimiento.');

-- Tipos de Activos
INSERT INTO tipos_activo (id, nombre, descripcion) VALUES
(1, 'Infraestructura y Redes', 'Servidores, routers, cableado estructurado y switches.'),
(2, 'Mobiliario', 'Escritorios, sillas ergonómicas y estanterías.'),
(3, 'Equipos de Computación', 'PCs de clientes, laptops administrativas y monitores.'),
(4, 'Licencias de Software', 'Sistemas operativos, antivirus y licencias de ERP.');

-- Tarifas del Cybercafé (Manejo de costo por hora y tiempo mínimo)
INSERT INTO tarifas_cyber (id, nombre, precio_por_hora, tiempo_minimo) VALUES
(1, 'Zona Gaming', 2.50, 30),      -- $2.50 la hora, mínimo 30 minutos ($1.25)
(2, 'Uso Oficina / Estudio', 1.50, 15), -- $1.50 la hora, mínimo 15 minutos ($0.375)
(3, 'Impresiones y Consultas', 1.00, 10); -- $1.00 la hora, mínimo 10 minutos ($0.166)

-- ============================================================
-- 2. TABLAS MAESTRAS PRINCIPALES
-- ============================================================

-- Usuarios del Sistema (Contraseñas de prueba en hash simulado)
INSERT INTO usuarios (id, username, password_hash, nombre, email, telefono, rol_id) VALUES
(1, 'carlos_admin', '$2y$10$xyz123ADMINISTRADORhash', 'Carlos Páez', 'carlos.paez@zwl.com', '0412-5551122', 1),
(2, 'felix_operador', '$2y$10$abc456OPERADORhash', 'Felix Tapia', 'felix.tapia@zwl.com', '0414-5553344', 2),
(3, 'jesus_asesor', '$2y$10$jkl789ASESORhash', 'Jesús Torrealba', 'jesus.t@zwl.com', '0416-5555566', 3);

-- Clientes Generales (Módulo Comercial / Cybercafé)
INSERT INTO clientes (id, cedula_rif, nombre, telefono, email, direccion) VALUES
(1, 'V-12345678', 'Juan Almarza', '0424-5112233', 'juan.almarza@mail.com', 'Barquisimeto, Centro, Calle 25'),
(2, 'V-87654321', 'Jeisson Terán', '0412-6334455', 'jeisson.t@mail.com', 'Barquisimeto, Av. Venezuela con Morán'),
(3, 'J-44556677-1', 'Zona Web Lara C.A.', '0251-2541122', 'contacto@zwlara.com', 'Zona Industrial II, Barquisimeto');

-- Clientes para Asesorías Legales (Con campos extendidos de contacto e historial)
INSERT INTO clientes_asesorias (id, cedula, nombre, email, telefono, direccion, notas_expediente) VALUES
(1, 'V-11222333', 'María Linares', 'maria.linares@asesoria.com', '0426-7119988', 'Pavia, Sector Las Veritas, Calle Principal', 'Caso de regularización de linderos de propiedad privada rural.'),
(2, 'V-44555666', 'Máyela Cadevilla', 'mayela.c@asesoria.com', '0414-8114422', 'Urb. Las Trinitarias, Este de Barquisimeto', 'Constitución y registro de actas de asamblea para firma comercial.');

-- Proveedores (El flag es_proveedor_principal se maneja aquí directamente)
INSERT INTO proveedores (id, nombre, rif, tipo_documento, contacto, email, telefono, es_proveedor_principal) VALUES
(1, 'Mayorista Tech Lara', 'J-31122334-0', 'J', 'Alejandro Ramos', 'ventas@techlara.com', '0251-5114477', TRUE),
(2, 'Insumos Globales Occidente', 'J-41155667-0', 'J', 'Laura Méndez', 'contacto@insumosglobales.com', '0251-6228899', FALSE);

-- Productos (La cantidad/stock reside en esta tabla, se relaciona con Categorías y Modelos)
INSERT INTO productos (id, codigo, codigo_barras, nombre, descripcion, categoria_id, modelo_id, unidad_medida, stock, stock_minimo, ubicacion, costo_compra, precio_venta) VALUES
(1, 'SSD-KIN-480', '740617263450', 'SSD Kingston A400 480GB', 'Disco sólido interno de 2.5 pulgadas SATA III.', 1, 1, 'Unidades', 15, 5, 'Pasillo A - Estante 2', 22.00, 35.00),
(2, 'RAM-KIN-8GB', '740617311731', 'Memoria RAM Kingston Fury Beast 8GB', 'Módulo DDR4 a 3200MHz con disipador.', 2, 2, 'Unidades', 8, 3, 'Pasillo A - Estante 1', 18.00, 28.00),
(3, 'MOU-LOG-G203', '097855155940', 'Ratón Gaming Logitech G203', 'Ratón óptico con iluminación RGB Lightsync USB.', 3, 3, 'Unidades', 12, 4, 'Pasillo B - Vitrina 1', 15.00, 25.00),
(4, 'KEY-LOG-K120', '097855061241', 'Teclado Estándar Logitech K120', 'Teclado cableado USB en español, resistente al agua.', 3, 4, 'Unidades', 20, 5, 'Pasillo B - Caja Central', 7.50, 12.00),
(5, 'IMP-HOJA-A4', '750100412345', 'Resma de Hojas Blancas Carta/A4', 'Paquete de 500 hojas blancas de 75g.', 4, NULL, 'Packs', 50, 10, 'Depósito Trasero - Stand 1', 3.50, 5.50);

-- Activos Físicos de la Empresa (Relacionados con tipos_activo y usuarios responsables)
INSERT INTO activos (id, nombre, descripcion, tipo_activo_id, estado, ubicacion, valor_adquisicion, fecha_adquisicion, responsable_id) VALUES
(1, 'Router Balanceador Mikrotik RB3011', 'Router principal de administración de ancho de banda del cyber.', 1, 'Activo', 'Rack del Servidor Principal', 180.00, '2025-01-15', 1),
(2, 'Estación de Trabajo Cyber PC-01', 'PC de clientes: Ryzen 5, 16GB RAM, SSD 480GB.', 3, 'Activo', 'Módulo Central Cyber - Cubículo 1', 450.00, '2025-03-20', 2),
(3, 'Estación de Trabajo Cyber PC-02', 'PC de clientes: Ryzen 5, 16GB RAM, SSD 480GB.', 3, 'Mantenimiento', 'Módulo Central Cyber - Cubículo 2', 450.00, '2025-03-20', 2),
(4, 'Silla Ejecutiva Ergonómica Negra', 'Silla ajustable de red del puesto administrativo.', 2, 'Activo', 'Oficina de Administración', 95.00, '2025-02-10', 1);

-- ============================================================
-- 3. TABLAS PUENTE (Relaciones M:N)
-- ============================================================

-- Catálogo Cruzado de Producto-Proveedor
INSERT INTO producto_proveedor (producto_id, proveedor_id, codigo_proveedor, precio_compra, tiempo_entrega_dias) VALUES
(1, 1, 'PROV-SSD480-K', 21.00, 3),
(1, 2, 'GLOBAL-SSD-480', 22.50, 5),
(2, 1, 'PROV-RAM8GB-K', 17.50, 3),
(3, 2, 'GLOBAL-MOU-G203', 14.80, 4),
(4, 1, 'PROV-TEC-K120', 7.00, 2);

-- ============================================================
-- 4. MOVIMIENTOS TRANSACCIONALES DE PRUEBA
-- ============================================================

-- Ventas Realizadas (Campos calculados netos, sin IVA)
INSERT INTO ventas (id, fecha, usuario_id, cliente_id, subtotal, descuento, total, estado) VALUES
(1, '2026-05-25 10:30:00', 2, 1, 35.00, 0.00, 35.00, 'completada'),
(2, '2026-05-26 14:15:00', 2, 2, 53.00, 3.00, 50.00, 'completada');

-- Detalle de las Ventas correspondientes
INSERT INTO detalle_ventas (id, venta_id, producto_id, cantidad, precio_unitario, descuento, subtotal) VALUES
(1, 1, 1, 1, 35.00, 0.00, 35.00), -- 1 SSD de $35
(2, 2, 2, 1, 28.00, 0.00, 28.00), -- 1 RAM de $28
(3, 2, 3, 1, 25.00, 3.00, 22.00); -- 1 Mouse de $25 con desc de $3

-- Solicitudes a Proveedores (Maneja Cantidad e incluye tiempo_entrega_dias de la solicitud)
INSERT INTO solicitudes (id, codigo, proveedor_id, fecha, fecha_estimada_entrega, tiempo_entrega_dias, subtotal, total, estado, usuario_id) VALUES
(1, 'SOL-2026-001', 1, '2026-05-20', '2026-05-23', 3, 295.00, 295.00, 'Recibida', 1);

-- Detalle de Solicitudes (Cantidades de compra pedidas a proveedores)
INSERT INTO detalle_solicitudes (id, solicitud_id, producto_id, cantidad_solicitada, cantidad_recibida, precio_unitario_estimado, subtotal) VALUES
(1, 1, 1, 10, 10, 22.00, 220.00),
(2, 1, 4, 10, 10, 7.50, 75.00);

-- Historial Manual/Inicial de Auditoría en la Bitácora de Movimientos
INSERT INTO bitacora_movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, precio_unitario, costo_total, usuario_id, referencia_tipo, referencia_id, motivo) VALUES
(1, 'entrada', 15, 0, 15, 22.00, 330.00, 1, 'carga_inicial', NULL, 'Carga inicial de inventario en apertura del sistema.'),
(2, 'entrada', 8, 0, 8, 18.00, 144.00, 1, 'carga_inicial', NULL, 'Carga inicial de stock.'),
(3, 'entrada', 12, 0, 12, 15.00, 180.00, 1, 'carga_inicial', NULL, 'Inventario inicial automatizado.');

-- ============================================================
-- 5. MOVIMIENTOS DEL MODULO CYBERCAFÉ
-- ============================================================

-- Apertura de Estaciones Asociadas a Tarifas
INSERT INTO estaciones_cyber (id, nombre, estado, tarifa_id, ip_local, mac_address) VALUES
(1, 'PC-CLIENTE-01', 'Ocupada', 2, '192.168.1.51', '00:1A:2B:3C:4D:5E'),
(2, 'PC-CLIENTE-02', 'Disponible', 2, '192.168.1.52', '00:1A:2B:3C:4D:5F'),
(3, 'PC-GAMING-03', 'Disponible', 1, '192.168.1.60', '00:1A:2B:88:99:AA');

-- Sesiones de Uso del Cyber (Relacionadas con Clientes generales y operadores)
INSERT INTO sesiones_cyber (id, estacion_id, usuario_id, cliente_id, hora_inicio, hora_fin, costo_total, estado) VALUES
(1, '1', 2, 1, '2026-05-27 20:30:00', NULL, NULL, 'activa'), -- Sesión abierta corriendo en tiempo real
(2, '2', 2, 2, '2026-05-27 18:00:00', '2026-05-27 19:30:00', 2.25, 'cerrada'); -- 1 hora y media en tarifa oficina ($1.50 * 1.5)

-- ============================================================
-- 6. ASESORÍAS LEGALES Y ASIGNACIÓN PUENTE
-- ============================================================

-- Casos de Asesorías en Curso (Relacionado con la tabla de clientes de asesoría jurídica)
INSERT INTO asesorias (id, cliente_asesoria_id, documento, descripcion, estado, fecha_registro) VALUES
(1, 1, 'EXP-2026-09', 'Revisión técnica de documentos ejidales del sector Pavia para adjudicación definitiva.', 'En Proceso', '2026-05-10 09:00:00'),
(2, 2, 'REF-9982-A', 'Redacción de estatutos comerciales para constitución de firma personal de servicios.', 'Pendiente', '2026-05-24 11:30:00');

-- Registro en la Tabla Puente (usuario_asesoria) vinculando personal con los casos
INSERT INTO usuario_asesoria (usuario_id, asesoria_id, rol_en_asesoria) VALUES
(3, 1, 'Abogado Gestor Principal'), -- Jesús Torrealba lleva el caso de María Linares
(1, 1, 'Supervisor de Operación'),   -- Carlos Páez supervisa el proceso técnico
(3, 2, 'Consultor Redactor');       -- Jesús lleva la firma comercial de Máyela