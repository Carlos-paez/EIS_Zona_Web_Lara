CREATE DATABASE IF NOT EXISTS zona_web_lara;
USE zona_web_lara;

-- 1. Tablas Maestras / Independientes
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol VARCHAR(50) NOT NULL
);

CREATE TABLE permisos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    permisos VARCHAR(100) NOT NULL
);

CREATE TABLE categoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_categoria VARCHAR(100) NOT NULL
);

CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    direccion TEXT,
    telefono VARCHAR(20)
);

CREATE TABLE proveedores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rif VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100),
    email VARCHAR(100),
    telefono VARCHAR(20)
);

CREATE TABLE status_seguimiento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    status VARCHAR(50) NOT NULL
);

CREATE TABLE tipo_asesoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo VARCHAR(100) NOT NULL,
    permitido BOOLEAN
);

CREATE TABLE tarifas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tarifa_hora DECIMAL(10,2),
    precio_tiempo DECIMAL(10,2)
);

CREATE TABLE tipo_activo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_tipo VARCHAR(50) -- "activa" parece ser un estado o tipo aquí [3]
);

-- 2. Tablas con dependencias (Llaves Foráneas)

CREATE TABLE rol_usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fk_rol INT,
    rol VARCHAR(50), -- Atributo específico del diagrama [1]
    FOREIGN KEY (fk_rol) REFERENCES roles(id)
);

CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    user_name VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255),
    email VARCHAR(100),
    estatus VARCHAR(20),
    fk_rol_usuario INT,
    FOREIGN KEY (fk_rol_usuario) REFERENCES rol_usuarios(id)
);

CREATE TABLE permisos_rol (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fk_rol INT,
    fk_permiso INT,
    FOREIGN KEY (fk_rol) REFERENCES roles(id),
    FOREIGN KEY (fk_permiso) REFERENCES permisos(id)
);

CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) UNIQUE,
    nombre VARCHAR(100),
    descripcion TEXT,
    stock INT,
    stock_minimo INT,
    precio_compra DECIMAL(10,2),
    precio_venta DECIMAL(10,2),
    fecha_creacion DATE, -- El diagrama desglosa día/mes/año [1]
    fecha_actualizacion DATE,
    fk_categoria INT,
    FOREIGN KEY (fk_categoria) REFERENCES categoria(id)
);

CREATE TABLE orden_de_venta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_de_orden VARCHAR(50),
    fecha DATE,
    fk_usuario INT,
    fk_cliente INT,
    FOREIGN KEY (fk_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (fk_cliente) REFERENCES clientes(id)
);

CREATE TABLE lineas_venta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cantidad INT,
    precio DECIMAL(10,2),
    fk_orden INT,
    fk_producto INT,
    FOREIGN KEY (fk_orden) REFERENCES orden_de_venta(id),
    FOREIGN KEY (fk_producto) REFERENCES productos(id)
);

CREATE TABLE orden_abastecimiento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_de_orden VARCHAR(50),
    fecha DATE,
    fk_proveedor INT,
    fk_status INT,
    FOREIGN KEY (fk_proveedor) REFERENCES proveedores(id),
    FOREIGN KEY (fk_status) REFERENCES status_seguimiento(id)
);

CREATE TABLE lineas_abastecimiento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cantidad INT,
    precio DECIMAL(10,2),
    fk_orden_abastecimiento INT,
    fk_producto INT,
    FOREIGN KEY (fk_orden_abastecimiento) REFERENCES orden_abastecimiento(id),
    FOREIGN KEY (fk_producto) REFERENCES productos(id)
);

CREATE TABLE asesoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    documento VARCHAR(100),
    descripcion TEXT,
    fecha DATE,
    fk_cliente INT,
    fk_tipo_asesoria INT,
    FOREIGN KEY (fk_cliente) REFERENCES clientes(id),
    FOREIGN KEY (fk_tipo_asesoria) REFERENCES tipo_asesoria(id)
);

CREATE TABLE sesion_ciber (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tiempo_uso VARCHAR(50),
    fk_cliente INT,
    fk_tarifa INT,
    FOREIGN KEY (fk_cliente) REFERENCES clientes(id),
    FOREIGN KEY (fk_tarifa) REFERENCES tarifas(id)
);

CREATE TABLE movimientos_inventario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    producto_id INT NOT NULL,
    tipo VARCHAR(20) NOT NULL COMMENT 'entrada o salida',
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    usuario_id INT,
    motivo VARCHAR(255) DEFAULT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca VARCHAR(100),
    descripcion TEXT,
    is_ciber BOOLEAN,
    activa BOOLEAN DEFAULT TRUE, -- Atributo "activa" identificado en el nodo [3]
    fk_tipo_activo INT,
    fk_usuario_usa INT, -- Relación "usa" con usuarios [3]
    FOREIGN KEY (fk_tipo_activo) REFERENCES tipo_activo(id),
    FOREIGN KEY (fk_usuario_usa) REFERENCES usuarios(id)
);
