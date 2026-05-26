CREATE DATABASE IF NOT EXISTS zwl;
USE zwl;

-- =========================
-- USUARIOS
-- =========================

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    usuario VARCHAR(50) UNIQUE,
    password VARCHAR(100),
    rol VARCHAR(50),
    telefono VARCHAR(30),
    correo VARCHAR(100),
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- CLIENTES
-- =========================

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    cedula VARCHAR(30),
    telefono VARCHAR(30),
    correo VARCHAR(100),
    direccion TEXT,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- PROVEEDORES
-- =========================

CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    telefono VARCHAR(30),
    correo VARCHAR(100),
    direccion TEXT,
    proveedor_principal BOOLEAN DEFAULT FALSE
);

-- =========================
-- PRODUCTOS
-- =========================

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    codigo VARCHAR(50),
    nombre VARCHAR(100),

    categoria VARCHAR(100),
    subcategoria VARCHAR(100),

    marca VARCHAR(100),
    modelo VARCHAR(100),

    descripcion TEXT,

    cantidad INT DEFAULT 0,
    stock_minimo INT DEFAULT 5,

    precio_compra DECIMAL(10,2),
    precio_venta DECIMAL(10,2),

    proveedor_id INT,

    estado VARCHAR(30) DEFAULT 'activo',

    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (proveedor_id)
    REFERENCES proveedores(id)
);

-- =========================
-- MOVIMIENTOS INVENTARIO
-- =========================

CREATE TABLE inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,

    producto_id INT,
    usuario_id INT,

    tipo VARCHAR(50),
    cantidad INT,

    observacion TEXT,

    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (producto_id)
    REFERENCES productos(id),

    FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id)
);

-- =========================
-- VENTAS
-- =========================

CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,

    cliente_id INT,
    usuario_id INT,

    subtotal DECIMAL(10,2),
    total DECIMAL(10,2),

    tipo_pago VARCHAR(50),

    estado VARCHAR(50) DEFAULT 'completada',

    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cliente_id)
    REFERENCES clientes(id),

    FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id)
);

CREATE TABLE detalle_ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,

    venta_id INT,
    producto_id INT,

    cantidad INT,
    precio DECIMAL(10,2),
    subtotal DECIMAL(10,2),

    FOREIGN KEY (venta_id)
    REFERENCES ventas(id),

    FOREIGN KEY (producto_id)
    REFERENCES productos(id)
);

-- =========================
-- SOLICITUDES
-- =========================

CREATE TABLE solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,

    cliente_id INT,
    usuario_id INT,

    descripcion TEXT,

    tiempo_entrega INT,

    estado VARCHAR(50) DEFAULT 'pendiente',

    observaciones TEXT,

    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cliente_id)
    REFERENCES clientes(id),

    FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id)
);

-- =========================
-- CYBER
-- =========================

CREATE TABLE sesiones_cyber (
    id INT AUTO_INCREMENT PRIMARY KEY,

    cliente_id INT,
    usuario_id INT,

    computadora VARCHAR(50),

    hora_inicio DATETIME,
    hora_fin DATETIME,

    minutos INT,

    costo_hora DECIMAL(10,2),
    subtotal DECIMAL(10,2),
    total DECIMAL(10,2),

    estado VARCHAR(50) DEFAULT 'activa',

    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cliente_id)
    REFERENCES clientes(id),

    FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id)
);

-- =========================
-- ASESORIAS
-- =========================

CREATE TABLE asesorias (
    id INT AUTO_INCREMENT PRIMARY KEY,

    cliente_id INT,
    usuario_id INT,

    tema VARCHAR(200),
    descripcion TEXT,

    telefono VARCHAR(30),
    correo VARCHAR(100),

    costo DECIMAL(10,2),

    estado VARCHAR(50) DEFAULT 'pendiente',

    fecha_inicio DATETIME,
    fecha_fin DATETIME,

    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (cliente_id)
    REFERENCES clientes(id),

    FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id)
);

-- =========================
-- TRIGGER SIMPLE INVENTARIO
-- =========================

DELIMITER $$

CREATE TRIGGER descontar_stock
AFTER INSERT ON detalle_ventas
FOR EACH ROW
BEGIN

    UPDATE productos
    SET cantidad = cantidad - NEW.cantidad
    WHERE id = NEW.producto_id;

END$$

DELIMITER ;
