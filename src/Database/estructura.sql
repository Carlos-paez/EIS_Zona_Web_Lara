CREATE DATABASE IF NOT EXISTS zona_web_lara;
USE zona_web_lara;

CREATE TABLE roles
(
    id         INT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol VARCHAR(50) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE permisos
(
    id       INT PRIMARY KEY AUTO_INCREMENT,
    permisos VARCHAR(100) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE categoria
(
    id               INT PRIMARY KEY AUTO_INCREMENT,
    nombre_categoria VARCHAR(100) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE clientes
(
    id        INT PRIMARY KEY AUTO_INCREMENT,
    cedula    VARCHAR(20) UNIQUE NOT NULL,
    nombre    VARCHAR(100)       not null,
    apellido  VARCHAR(100)       not null,
    direccion TEXT               not null,
    telefono  VARCHAR(20)        not null
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE cliente_asesoria
(
    id               INT PRIMARY KEY AUTO_INCREMENT,
    fk_cliente       int,
    email            VARCHAR(80) not null default 'N/A',
    rif              varchar(50)          default 'N/A',
    tipo             varchar(80)          default 'civil',
    foreign key (fk_cliente) references clientes (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE proveedores
(
    id       INT PRIMARY KEY AUTO_INCREMENT,
    rif      VARCHAR(20) UNIQUE NOT NULL,
    nombre   VARCHAR(100)       not null,
    email    VARCHAR(100)       not null,
    telefono VARCHAR(20)        not null
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE status_seguimiento
(
    id     INT PRIMARY KEY AUTO_INCREMENT,
    status VARCHAR(50) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE tipo_asesoria
(
    id        INT PRIMARY KEY AUTO_INCREMENT,
    tipo      VARCHAR(100) NOT NULL,
    permitido BOOLEAN      not null default false
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE tarifas
(
    id            INT PRIMARY KEY AUTO_INCREMENT,
    tarifa_hora   DECIMAL(10, 2) not null,
    precio_tiempo DECIMAL(10, 2) not null
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE tipo_activo
(
    id          INT PRIMARY KEY AUTO_INCREMENT,
    nombre_tipo VARCHAR(50) not null
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;


CREATE TABLE rol_usuarios
(
    id     INT PRIMARY KEY AUTO_INCREMENT,
    fk_rol INT,
    rol    VARCHAR(50) not null,
    FOREIGN KEY (fk_rol) REFERENCES roles (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE usuarios
(
    id             INT PRIMARY KEY AUTO_INCREMENT,
    nombre         VARCHAR(100)       not null,
    apellido       VARCHAR(100)       not null,
    user_name      VARCHAR(50) UNIQUE not null,
    password_hash  VARCHAR(255)       not null,
    email          VARCHAR(100)                default 'zonaweblara@gmail.com',
    estatus        VARCHAR(20)        not null default '0',
    fk_rol_usuario INT,
    FOREIGN KEY (fk_rol_usuario) REFERENCES rol_usuarios (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE permisos_rol
(
    id         INT PRIMARY KEY AUTO_INCREMENT,
    fk_rol     INT,
    fk_permiso INT,
    FOREIGN KEY (fk_rol) REFERENCES roles (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (fk_permiso) REFERENCES permisos (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE productos
(
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    codigo              VARCHAR(50) UNIQUE,
    nombre              VARCHAR(100)   not null,
    descripcion         TEXT,
    stock               INT            not null,
    stock_minimo        INT            not null,
    precio_compra       DECIMAL(10, 2) not null,
    precio_venta        DECIMAL(10, 2) not null,
    fecha_creacion      DATE           not null,
    fecha_actualizacion DATE           not null,
    fk_categoria        INT,
    FOREIGN KEY (fk_categoria) REFERENCES categoria (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE orden_de_venta
(
    id              INT PRIMARY KEY AUTO_INCREMENT,
    numero_de_orden VARCHAR(50) not null,
    fecha           DATE        not null,
    fk_usuario      INT,
    fk_cliente      INT,
    FOREIGN KEY (fk_usuario) REFERENCES usuarios (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (fk_cliente) REFERENCES clientes (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE lineas_venta
(
    id          INT PRIMARY KEY AUTO_INCREMENT,
    cantidad    INT            not null,
    precio      DECIMAL(10, 2) not null,
    fk_orden    INT,
    fk_producto INT,
    FOREIGN KEY (fk_orden) REFERENCES orden_de_venta (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (fk_producto) REFERENCES productos (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE orden_abastecimiento
(
    id              INT PRIMARY KEY AUTO_INCREMENT,
    numero_de_orden VARCHAR(50) not null,
    fecha           DATE        not null,
    fk_proveedor    INT,
    fk_status       INT,
    FOREIGN KEY (fk_proveedor) REFERENCES proveedores (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (fk_status) REFERENCES status_seguimiento (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE lineas_abastecimiento
(
    id                      INT PRIMARY KEY AUTO_INCREMENT,
    cantidad                INT            not null,
    precio                  DECIMAL(10, 2) not null,
    fk_orden_abastecimiento INT,
    fk_producto             INT,
    FOREIGN KEY (fk_orden_abastecimiento) REFERENCES orden_abastecimiento (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (fk_producto) REFERENCES productos (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE asesoria
(
    id                  INT PRIMARY KEY AUTO_INCREMENT,
    documento           VARCHAR(100) not null,
    descripcion         TEXT         not null,
    fecha               DATE         not null,
    fk_cliente_asesoria INT,
    fk_tipo_asesoria    INT,
    FOREIGN KEY (fk_cliente_asesoria) REFERENCES cliente_asesoria (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (fk_tipo_asesoria) REFERENCES tipo_asesoria (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE activos
(
    id             INT PRIMARY KEY AUTO_INCREMENT,
    marca          VARCHAR(100) not null,
    descripcion    TEXT         not null,
    is_ciber       BOOLEAN      not null default false,
    activa         BOOLEAN               DEFAULT TRUE,
    fk_tipo_activo INT,
    FOREIGN KEY (fk_tipo_activo) REFERENCES tipo_activo (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

CREATE TABLE sesion_ciber
(
    id         INT PRIMARY KEY AUTO_INCREMENT,
    tiempo_uso VARCHAR(50) not null,
    finalizada TINYINT(1) not null default 0,
    fk_cliente INT,
    fk_tarifa  INT,
    fk_activo  INT,
    FOREIGN KEY (fk_cliente) REFERENCES clientes (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (fk_tarifa) REFERENCES tarifas (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (fk_activo) REFERENCES activos (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;
