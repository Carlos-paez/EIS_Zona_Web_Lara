CREATE DATABASE IF NOT EXISTS zona_web_lara;
USE zona_web_lara;

-- 1. Tablas Maestras / Independientes
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol VARCHAR(50) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permisos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    permisos VARCHAR(100) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_categoria VARCHAR(100) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    direccion TEXT NOT NULL,
    telefono VARCHAR(20) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE proveedores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rif VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE status_seguimiento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    status VARCHAR(50) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tipo_asesoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo VARCHAR(100) NOT NULL,
    permitido BOOLEAN NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tarifas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tarifa_hora DECIMAL(10,2) NOT NULL,
    precio_tiempo DECIMAL(10,2) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tipo_activo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_tipo VARCHAR(50) NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tablas con dependencias (Llaves Foráneas)

CREATE TABLE rol_usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fk_rol INT NOT NULL,
    rol VARCHAR(50) NOT NULL,
    FOREIGN KEY (fk_rol) REFERENCES roles(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    user_name VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    estatus VARCHAR(20) NOT NULL,
    fk_rol_usuario INT NOT NULL,
    FOREIGN KEY (fk_rol_usuario) REFERENCES rol_usuarios(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permisos_rol (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fk_rol INT NOT NULL,
    fk_permiso INT NOT NULL,
    FOREIGN KEY (fk_rol) REFERENCES roles(id),
    FOREIGN KEY (fk_permiso) REFERENCES permisos(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    stock INT NOT NULL,
    stock_minimo INT NOT NULL,
    precio_compra DECIMAL(10,2) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    fecha_creacion DATE NOT NULL,
    fecha_actualizacion DATE NOT NULL,
    fk_categoria INT NOT NULL,
    FOREIGN KEY (fk_categoria) REFERENCES categoria(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orden_de_venta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_de_orden VARCHAR(50) NOT NULL,
    fecha DATE NOT NULL,
    fk_usuario INT NOT NULL,
    fk_cliente INT NOT NULL,
    FOREIGN KEY (fk_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (fk_cliente) REFERENCES clientes(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lineas_venta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    fk_orden INT NOT NULL,
    fk_producto INT NOT NULL,
    FOREIGN KEY (fk_orden) REFERENCES orden_de_venta(id),
    FOREIGN KEY (fk_producto) REFERENCES productos(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orden_abastecimiento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_de_orden VARCHAR(50) NOT NULL,
    fecha DATE NOT NULL,
    fk_proveedor INT NOT NULL,
    fk_status INT NOT NULL,
    FOREIGN KEY (fk_proveedor) REFERENCES proveedores(id),
    FOREIGN KEY (fk_status) REFERENCES status_seguimiento(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lineas_abastecimiento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    fk_orden_abastecimiento INT NOT NULL,
    fk_producto INT NOT NULL,
    FOREIGN KEY (fk_orden_abastecimiento) REFERENCES orden_abastecimiento(id),
    FOREIGN KEY (fk_producto) REFERENCES productos(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE asesoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    documento VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha DATE NOT NULL,
    fk_cliente INT NOT NULL,
    fk_tipo_asesoria INT NOT NULL,
    FOREIGN KEY (fk_cliente) REFERENCES clientes(id),
    FOREIGN KEY (fk_tipo_asesoria) REFERENCES tipo_asesoria(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sesion_ciber (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tiempo_uso VARCHAR(50) NOT NULL,
    fk_cliente INT NOT NULL,
    fk_tarifa INT NOT NULL,
    FOREIGN KEY (fk_cliente) REFERENCES clientes(id),
    FOREIGN KEY (fk_tarifa) REFERENCES tarifas(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE movimientos_inventario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    producto_id INT NOT NULL,
    tipo VARCHAR(20) NOT NULL COMMENT 'entrada o salida',
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    usuario_id INT,
    motivo VARCHAR(255) DEFAULT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca VARCHAR(100),
    descripcion TEXT NOT NULL,
    is_ciber BOOLEAN NOT NULL,
    activa BOOLEAN DEFAULT TRUE NOT NULL, -- Atributo "activa" identificado en el nodo [3]
    fk_tipo_activo INT NOT NULL,
    fk_usuario_usa INT NOT NULL, -- Relación "usa" con usuarios [3]
    FOREIGN KEY (fk_tipo_activo) REFERENCES tipo_activo(id),
    FOREIGN KEY (fk_usuario_usa) REFERENCES usuarios(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
