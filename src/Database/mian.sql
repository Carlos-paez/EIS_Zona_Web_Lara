CREATE DATABASE IF NOT EXISTS zwl CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zwl;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE COMMENT 'SKU o código de producto único',
    codigo_barras VARCHAR(100) NULL COMMENT 'Código de barras para escaneo rápido',
    nombre VARCHAR(150) NOT NULL COMMENT 'Nombre comercial del artículo',
    marca VARCHAR(100) NULL COMMENT 'Marca del producto',
    categoria VARCHAR(50) NOT NULL COMMENT 'Categoría o departamento',
    descripcion TEXT NULL COMMENT 'Descripción breve o detalles técnicos',
    stock INT NOT NULL DEFAULT 0 COMMENT 'Stock actual disponible',
    stock_minimo INT NOT NULL DEFAULT 5 COMMENT 'Punto de reorden (stock mínimo)',
    ubicacion VARCHAR(100) NULL COMMENT 'Ubicación física (pasillo, estante, bodega)',
    unidad_medida ENUM('Unidades', 'Kg', 'Litros', 'Metros', 'Packs') DEFAULT 'Unidades' COMMENT 'Unidad de medida',
    costo_compra DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo de adquisición',
    precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio de venta al público (PVP)',
    iva DECIMAL(5,2) DEFAULT 16.00 COMMENT 'Porcentaje de IVA aplicable',
    margen_ganancia DECIMAL(5,2) NULL COMMENT 'Porcentaje de utilidad sobre el costo',
    permite_descuento BOOLEAN DEFAULT TRUE COMMENT 'Indica si el producto permite descuentos',
    estado ENUM('OK', 'Crítico', 'Sin stock') DEFAULT 'OK' COMMENT 'Estado de inventario',
    estado_venta ENUM('Activo', 'Inactivo') DEFAULT 'Activo' COMMENT 'Estado para ventas (activo/inactivo)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    usuario_id INT NULL,
    estado ENUM('completada', 'pendiente', 'cancelada') DEFAULT 'completada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS detalle_ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    contacto VARCHAR(100) NULL,
    email VARCHAR(100) NULL,
    telefono VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    proveedor_id INT NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('Pendiente', 'Recibida', 'Cancelada') DEFAULT 'Pendiente',
    usuario_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS activos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    tipo ENUM('Equipos', 'Herramientas', 'Licencias') NOT NULL,
    estado ENUM('Activo', 'Mantenimiento', 'Vencida') DEFAULT 'Activo',
    fecha_adquisicion DATE NULL,
    fecha_vencimiento DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS estaciones_cyber (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    estado ENUM('Disponible', 'Ocupada', 'Mantenimiento') DEFAULT 'Disponible',
    tipo VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sesiones_cyber (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estacion_id INT NOT NULL,
    usuario_id INT NULL,
    hora_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    hora_fin DATETIME NULL,
    duracion_minutos INT NULL,
    costo DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estacion_id) REFERENCES estaciones_cyber(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS movimientos_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    tipo ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NULL,
    motivo VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_productos_categoria ON productos(categoria);
CREATE INDEX idx_ventas_fecha ON ventas(fecha);
CREATE INDEX idx_ventas_usuario ON ventas(usuario_id);
CREATE INDEX idx_detalle_ventas_venta ON detalle_ventas(venta_id);
CREATE INDEX idx_detalle_ventas_producto ON detalle_ventas(producto_id);
CREATE INDEX idx_solicitudes_proveedor ON solicitudes(proveedor_id);
CREATE INDEX idx_solicitudes_fecha ON solicitudes(fecha);
CREATE INDEX idx_sesiones_estacion ON sesiones_cyber(estacion_id);
CREATE INDEX idx_movimientos_producto ON movimientos_stock(producto_id);
CREATE INDEX idx_movimientos_fecha ON movimientos_stock(fecha);
