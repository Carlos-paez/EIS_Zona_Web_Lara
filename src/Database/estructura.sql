-- ============================================================
-- ESTRUCTURA DE BASE DE DATOS - Sistema ZWL v2.0
-- Optimizada, normalizada con tablas puente
-- Motor: MySQL 8.0+ / MariaDB 10.3+
-- ============================================================



--Tablas Puente (M:N):
--detalle_ventas — ventas ↔ productos
--detalle_solicitudes — solicitudes ↔ productos
--producto_proveedor — productos ↔ proveedores





--Módulo Transaccional:
--ventas + detalle_ventas — módulo de ventas
--solicitudes + detalle_solicitudes — módulo de compras/solicitudes
--movimientos_stock — módulo de inventario (auditoría)
--sesiones_cyber — módulo de cybercafé
--asesorias — módulo de asesorías
--Catálogos (soporte): roles, categorias, marcas, tipos_activo, tarifas_cyber, tipos_pago
--Maestros (soporte): usuarios, productos, proveedores, activos, estaciones_cyber





CREATE DATABASE IF NOT EXISTS zwl CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zwl;

-- ============================================================
-- TABLAS DE CATÁLOGO (Lookup Tables)
-- ============================================================

CREATE TABLE IF NOT EXISTS roles (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE,
    descripcion VARCHAR(150) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categorias (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NULL,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS marcas (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tipos_activo (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tarifas_cyber (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE COMMENT 'Ej: Gaming, Oficina, Premium',
    precio_por_hora DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    precio_por_minuto DECIMAL(6,2) NULL,
    tiempo_minimo INT UNSIGNED DEFAULT 30 COMMENT 'Minutos mínimos por sesión',
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tipos_pago (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE COMMENT 'Efectivo, Transferencia, Punto, Mixto',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLAS PRINCIPALES
-- ============================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE COMMENT 'Nombre de usuario para login',
    password_hash VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt de la contraseña',
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20) NULL,
    activo BOOLEAN DEFAULT TRUE,
    rol_id TINYINT UNSIGNED NOT NULL DEFAULT 2,
    ultimo_acceso DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_usuarios_rol ON usuarios(rol_id);

CREATE TABLE IF NOT EXISTS proveedores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    rif VARCHAR(20) NULL UNIQUE COMMENT 'Registro de Información Fiscal',
    tipo_documento ENUM('J','V','E','G') DEFAULT 'J',
    contacto VARCHAR(100) NULL,
    email VARCHAR(100) NULL,
    telefono VARCHAR(20) NULL,
    direccion TEXT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE COMMENT 'SKU interno',
    codigo_barras VARCHAR(100) NULL COMMENT 'Código EAN/UPC para escáner',
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    categoria_id SMALLINT UNSIGNED NOT NULL,
    marca_id SMALLINT UNSIGNED NULL,
    unidad_medida ENUM('Unidades', 'Kg', 'Litros', 'Metros', 'Packs') DEFAULT 'Unidades',
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    ubicacion VARCHAR(100) NULL,
    costo_compra DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    precio_venta DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    iva DECIMAL(5,2) DEFAULT 16.00,
    permite_descuento BOOLEAN DEFAULT TRUE,
    estado_venta ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
    FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE UNIQUE INDEX idx_productos_barras ON productos(codigo_barras);
CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_marca ON productos(marca_id);

CREATE TABLE IF NOT EXISTS producto_proveedor (
    producto_id INT UNSIGNED NOT NULL,
    proveedor_id INT UNSIGNED NOT NULL,
    codigo_proveedor VARCHAR(50) NULL,
    precio_compra DECIMAL(12,2) NULL,
    tiempo_entrega_dias SMALLINT UNSIGNED NULL,
    es_proveedor_principal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (producto_id, proveedor_id),
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_pp_proveedor ON producto_proveedor(proveedor_id);

CREATE TABLE IF NOT EXISTS ventas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT UNSIGNED NULL,
    cliente_nombre VARCHAR(150) NULL,
    cliente_cedula VARCHAR(20) NULL,
    tipo_pago_id TINYINT UNSIGNED NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    descuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    iva_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estado ENUM('completada', 'pendiente', 'cancelada', 'reembolsada') DEFAULT 'completada',
    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (tipo_pago_id) REFERENCES tipos_pago(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_ventas_fecha ON ventas(fecha);
CREATE INDEX idx_ventas_usuario ON ventas(usuario_id);
CREATE INDEX idx_ventas_estado ON ventas(estado);

CREATE TABLE IF NOT EXISTS detalle_ventas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venta_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(12,2) NOT NULL,
    iva_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    descuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_dv_venta ON detalle_ventas(venta_id);
CREATE INDEX idx_dv_producto ON detalle_ventas(producto_id);

CREATE TABLE IF NOT EXISTS solicitudes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Ej: SOL-2026-0001',
    proveedor_id INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
    fecha_estimada_entrega DATE NULL,
    tipo_pago_id TINYINT UNSIGNED NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    iva_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estado ENUM('Pendiente', 'Aprobada', 'Enviada', 'Recibida', 'Cancelada') DEFAULT 'Pendiente',
    usuario_id INT UNSIGNED NULL,
    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE RESTRICT,
    FOREIGN KEY (tipo_pago_id) REFERENCES tipos_pago(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_solicitudes_proveedor ON solicitudes(proveedor_id);
CREATE INDEX idx_solicitudes_fecha ON solicitudes(fecha);
CREATE INDEX idx_solicitudes_estado ON solicitudes(estado);

CREATE TABLE IF NOT EXISTS detalle_solicitudes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad_solicitada INT UNSIGNED NOT NULL DEFAULT 1,
    cantidad_recibida INT UNSIGNED NULL COMMENT 'Actualizado al recibir parcialmente',
    precio_unitario_estimado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_ds_solicitud ON detalle_solicitudes(solicitud_id);
CREATE INDEX idx_ds_producto ON detalle_solicitudes(producto_id);

CREATE TABLE IF NOT EXISTS activos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    tipo_activo_id TINYINT UNSIGNED NOT NULL,
    estado ENUM('Activo', 'Mantenimiento', 'Vencida', 'Baja') DEFAULT 'Activo',
    ubicacion VARCHAR(100) NULL,
    valor_adquisicion DECIMAL(12,2) NULL,
    fecha_adquisicion DATE NULL,
    fecha_vencimiento DATE NULL,
    responsable_id INT UNSIGNED NULL,
    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_activo_id) REFERENCES tipos_activo(id) ON DELETE RESTRICT,
    FOREIGN KEY (responsable_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_activos_tipo ON activos(tipo_activo_id);
CREATE INDEX idx_activos_estado ON activos(estado);

CREATE TABLE IF NOT EXISTS estaciones_cyber (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    estado ENUM('Disponible', 'Ocupada', 'Mantenimiento') DEFAULT 'Disponible',
    tarifa_id SMALLINT UNSIGNED NOT NULL,
    especificaciones VARCHAR(255) NULL,
    ip_local VARCHAR(15) NULL,
    mac_address VARCHAR(17) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tarifa_id) REFERENCES tarifas_cyber(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sesiones_cyber (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estacion_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    cliente_nombre VARCHAR(100) NULL,
    tarifa_id SMALLINT UNSIGNED NOT NULL,
    hora_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    hora_fin DATETIME NULL,
    costo_total DECIMAL(10,2) NULL,
    estado ENUM('activa', 'cerrada', 'interrumpida') DEFAULT 'activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estacion_id) REFERENCES estaciones_cyber(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (tarifa_id) REFERENCES tarifas_cyber(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_sc_estacion ON sesiones_cyber(estacion_id);
CREATE INDEX idx_sc_activas ON sesiones_cyber(estado);
CREATE INDEX idx_sc_fecha ON sesiones_cyber(hora_inicio);

CREATE TABLE IF NOT EXISTS movimientos_stock (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id INT UNSIGNED NOT NULL,
    tipo ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    precio_unitario DECIMAL(12,2) NULL,
    costo_total DECIMAL(12,2) NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT UNSIGNED NULL,
    referencia_tipo VARCHAR(30) NULL COMMENT 'Entidad origen: venta, solicitud, ajuste',
    referencia_id INT UNSIGNED NULL,
    motivo VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_ms_producto ON movimientos_stock(producto_id);
CREATE INDEX idx_ms_fecha ON movimientos_stock(fecha);
CREATE INDEX idx_ms_referencia ON movimientos_stock(referencia_tipo, referencia_id);

CREATE TABLE IF NOT EXISTS asesorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ciudadano VARCHAR(150) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    documento VARCHAR(50) NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('Pendiente', 'En Proceso', 'Finalizada', 'Archivada') DEFAULT 'Pendiente',
    usuario_id INT UNSIGNED NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_asesorias_estado ON asesorias(estado);
CREATE INDEX idx_asesorias_cedula ON asesorias(cedula);
CREATE INDEX idx_asesorias_fecha ON asesorias(fecha_registro);

-- ============================================================
-- VISTAS
-- ============================================================

CREATE OR REPLACE VIEW v_productos_stock AS
SELECT
    p.id, p.codigo, p.codigo_barras, p.nombre,
    c.nombre AS categoria, m.nombre AS marca,
    p.stock, p.stock_minimo, p.ubicacion, p.precio_venta,
    CASE
        WHEN p.stock <= 0 THEN 'Sin stock'
        WHEN p.stock <= p.stock_minimo THEN 'Crítico'
        ELSE 'OK'
    END AS estado_stock,
    p.estado_venta, p.activo
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN marcas m ON p.marca_id = m.id;

CREATE OR REPLACE VIEW v_ventas_diarias AS
SELECT
    DATE(v.fecha) AS dia,
    COUNT(*) AS total_ventas,
    SUM(v.total) AS monto_total,
    SUM(v.descuento) AS descuentos_total,
    AVG(v.total) AS ticket_promedio
FROM ventas v
WHERE v.estado = 'completada'
GROUP BY DATE(v.fecha)
ORDER BY dia DESC;

CREATE OR REPLACE VIEW v_sesiones_activas AS
SELECT
    s.id, e.nombre AS estacion,
    t.nombre AS tarifa, t.precio_por_hora,
    s.hora_inicio, s.cliente_nombre,
    u.nombre AS usuario_registra,
    TIMESTAMPDIFF(MINUTE, s.hora_inicio, NOW()) AS minutos_transcurridos,
    ROUND(TIMESTAMPDIFF(MINUTE, s.hora_inicio, NOW()) / 60.0 * t.precio_por_hora, 2) AS costo_estimado
FROM sesiones_cyber s
INNER JOIN estaciones_cyber e ON s.estacion_id = e.id
INNER JOIN tarifas_cyber t ON s.tarifa_id = t.id
LEFT JOIN usuarios u ON s.usuario_id = u.id
WHERE s.estado = 'activa';

-- ============================================================
-- FUNCIÓN: Calcular estado del stock
-- ============================================================

DELIMITER //
CREATE FUNCTION fn_estado_stock(p_stock INT, p_stock_minimo INT)
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    IF p_stock <= 0 THEN
        RETURN 'Sin stock';
    ELSEIF p_stock <= p_stock_minimo THEN
        RETURN 'Crítico';
    ELSE
        RETURN 'OK';
    END IF;
END //

-- ============================================================
-- PROCEDIMIENTO: Registrar movimiento de stock (transaccional)
-- ============================================================

CREATE PROCEDURE sp_registrar_movimiento_stock(
    IN p_producto_id INT UNSIGNED,
    IN p_tipo ENUM('entrada', 'salida', 'ajuste'),
    IN p_cantidad INT,
    IN p_usuario_id INT UNSIGNED,
    IN p_motivo VARCHAR(255),
    IN p_referencia_tipo VARCHAR(30),
    IN p_referencia_id INT UNSIGNED
)
BEGIN
    DECLARE v_stock_actual INT;
    DECLARE v_stock_nuevo INT;
    DECLARE v_cantidad_efectiva INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT stock INTO v_stock_actual
    FROM productos
    WHERE id = p_producto_id
    FOR UPDATE;

    IF p_tipo = 'salida' THEN
        SET v_cantidad_efectiva = -ABS(p_cantidad);
    ELSE
        SET v_cantidad_efectiva = ABS(p_cantidad);
    END IF;

    SET v_stock_nuevo = v_stock_actual + v_cantidad_efectiva;

    IF v_stock_nuevo < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock no puede ser negativo';
    END IF;

    UPDATE productos SET stock = v_stock_nuevo WHERE id = p_producto_id;

    INSERT INTO movimientos_stock (
        producto_id, tipo, cantidad, stock_anterior, stock_nuevo,
        usuario_id, motivo, referencia_tipo, referencia_id
    ) VALUES (
        p_producto_id, p_tipo, v_cantidad_efectiva,
        v_stock_actual, v_stock_nuevo,
        p_usuario_id, p_motivo, p_referencia_tipo, p_referencia_id
    );

    COMMIT;
END //

-- ============================================================
-- PROCEDIMIENTO: Cerrar sesión de cybercafé
-- ============================================================

CREATE PROCEDURE sp_cerrar_sesion_cyber(
    IN p_sesion_id INT UNSIGNED
)
BEGIN
    DECLARE v_tarifa_precio DECIMAL(8,2);
    DECLARE v_minutos INT;
    DECLARE v_costo DECIMAL(10,2);

    SELECT t.precio_por_hora, TIMESTAMPDIFF(MINUTE, s.hora_inicio, NOW())
    INTO v_tarifa_precio, v_minutos
    FROM sesiones_cyber s
    INNER JOIN tarifas_cyber t ON s.tarifa_id = t.id
    WHERE s.id = p_sesion_id;

    SET v_costo = ROUND((v_minutos / 60.0) * v_tarifa_precio, 2);

    UPDATE sesiones_cyber SET
        hora_fin = NOW(), costo_total = v_costo, estado = 'cerrada'
    WHERE id = p_sesion_id;

    UPDATE estaciones_cyber SET estado = 'Disponible'
    WHERE id = (SELECT estacion_id FROM sesiones_cyber WHERE id = p_sesion_id);
END //

-- ============================================================
-- EVENTO: Vencimiento automático de licencias
-- ============================================================

CREATE EVENT ev_vencer_licencias
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
    UPDATE activos
    SET estado = 'Vencida'
    WHERE tipo_activo_id = (SELECT id FROM tipos_activo WHERE nombre = 'Licencias' LIMIT 1)
      AND fecha_vencimiento IS NOT NULL
      AND fecha_vencimiento < CURRENT_DATE
      AND estado != 'Vencida' //

-- ============================================================
-- TRIGGER: Actualizar totales de venta al insertar detalle
-- ============================================================

CREATE TRIGGER trg_actualizar_totales_venta
AFTER INSERT ON detalle_ventas
FOR EACH ROW
BEGIN
    UPDATE ventas v
    SET
        v.subtotal = (SELECT COALESCE(SUM(subtotal), 0) FROM detalle_ventas WHERE venta_id = NEW.venta_id),
        v.total = (SELECT COALESCE(SUM(subtotal), 0) FROM detalle_ventas WHERE venta_id = NEW.venta_id)
    WHERE v.id = NEW.venta_id;
END //

-- ============================================================
-- TRIGGER: Auditar cambios de precio en productos
-- ============================================================

CREATE TRIGGER trg_auditar_precio_producto
BEFORE UPDATE ON productos
FOR EACH ROW
BEGIN
    IF OLD.precio_venta != NEW.precio_venta THEN
        INSERT INTO movimientos_stock (
            producto_id, tipo, cantidad, stock_anterior, stock_nuevo,
            usuario_id, motivo, referencia_tipo
        ) VALUES (
            NEW.id, 'ajuste', 0, OLD.stock, NEW.stock,
            NULL, CONCAT('Cambio de precio: ', OLD.precio_venta, ' -> ', NEW.precio_venta),
            'ajuste_precio'
        );
    END IF;
END //

DELIMITER ;
