-- ============================================================
-- EIS Zona Web Lara (ZWL) — Esquema de base de datos
-- Motor: InnoDB / MySQL 8.0.16+ / utf8mb4
-- ============================================================
--
-- Este script crea la base de datos desde cero e incluye:
--
--   1) BORRADO LÓGICO (soft delete) desde el primer momento:
--      todas las tablas de dominio tienen la columna `activo`:
--          activo = 1  -> registro visible y operativo
--          activo = 0  -> registro "eliminado" (se conserva en la BD)
--
--      Las tablas de detalle/mapeo (lineas_venta,
--      lineas_abastecimiento y permisos_rol) se siguen borrando
--      físicamente porque no son entidades de dominio.
--
--   2) VALIDACIONES DE INTEGRIDAD:
--      - NOT NULL en campos obligatorios.
--      - UNIQUE en claves naturales (no repetir).
--      - CHECK para longitudes mínimas/máximas, rangos numéricos,
--        valores booleanos y formatos coherentes con la aplicación.
--
-- Las relaciones, claves foráneas y acciones ON DELETE/ON UPDATE
-- se mantienen iguales al diseño original.
--
-- Requiere MySQL 8.0.16 o superior (las restricciones CHECK se
-- hacen efectivas a partir de esa versión).
-- ============================================================

CREATE DATABASE IF NOT EXISTS zona_web_lara
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE zona_web_lara;

SET NAMES utf8mb4;

-- ============================================================
-- ROLES
-- ============================================================
CREATE TABLE roles
(
    id          INT          NOT NULL AUTO_INCREMENT,
    nombre_rol  VARCHAR(50)  NOT NULL,
    descripcion VARCHAR(500)          DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    activo      TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_nombre_rol (nombre_rol),
    CONSTRAINT chk_roles_nombre_rol
        CHECK (CHAR_LENGTH(TRIM(nombre_rol)) BETWEEN 2 AND 50),
    CONSTRAINT chk_roles_descripcion
        CHECK (descripcion IS NULL OR CHAR_LENGTH(descripcion) <= 500),
    CONSTRAINT chk_roles_activo
        CHECK (activo IN (0, 1))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- PERMISOS
-- ============================================================
CREATE TABLE permisos
(
    id       INT          NOT NULL AUTO_INCREMENT,
    permisos VARCHAR(100) NOT NULL,
    activo   TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_permisos_permisos (permisos),
    CONSTRAINT chk_permisos_nombre
        CHECK (CHAR_LENGTH(TRIM(permisos)) BETWEEN 1 AND 100),
    CONSTRAINT chk_permisos_activo
        CHECK (activo IN (0, 1))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- CATEGORÍA
-- ============================================================
CREATE TABLE categoria
(
    id               INT          NOT NULL AUTO_INCREMENT,
    nombre_categoria VARCHAR(100) NOT NULL,
    activo           TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categoria_nombre_categoria (nombre_categoria),
    CONSTRAINT chk_categoria_nombre
        CHECK (CHAR_LENGTH(TRIM(nombre_categoria)) BETWEEN 2 AND 100),
    CONSTRAINT chk_categoria_activo
        CHECK (activo IN (0, 1))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- CLIENTES
-- ============================================================
CREATE TABLE clientes
(
    id        INT          NOT NULL AUTO_INCREMENT,
    cedula    VARCHAR(20)  NOT NULL,
    nombre    VARCHAR(100) NOT NULL,
    apellido  VARCHAR(100) NOT NULL,
    direccion TEXT         NOT NULL,
    telefono  VARCHAR(20)  NOT NULL,
    activo    TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clientes_cedula (cedula),
    CONSTRAINT chk_clientes_cedula
        CHECK (CHAR_LENGTH(TRIM(cedula)) BETWEEN 5 AND 20),
    CONSTRAINT chk_clientes_nombre
        CHECK (CHAR_LENGTH(TRIM(nombre)) BETWEEN 2 AND 100),
    CONSTRAINT chk_clientes_apellido
        CHECK (CHAR_LENGTH(apellido) <= 100),
    CONSTRAINT chk_clientes_direccion
        CHECK (CHAR_LENGTH(direccion) <= 500),
    CONSTRAINT chk_clientes_telefono
        CHECK (CHAR_LENGTH(telefono) <= 20),
    CONSTRAINT chk_clientes_activo
        CHECK (activo IN (0, 1))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- CLIENTE_ASESORIA
-- ============================================================
CREATE TABLE cliente_asesoria
(
    id         INT         NOT NULL AUTO_INCREMENT,
    fk_cliente INT         NOT NULL,
    email      VARCHAR(80) NOT NULL DEFAULT 'N/A',
    rif        VARCHAR(50) NOT NULL DEFAULT 'N/A',
    tipo       VARCHAR(80) NOT NULL DEFAULT 'civil',
    activo     TINYINT(1)  NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cliente_asesoria_fk_cliente (fk_cliente),
    CONSTRAINT chk_cliente_asesoria_email
        CHECK (CHAR_LENGTH(email) <= 80),
    CONSTRAINT chk_cliente_asesoria_rif
        CHECK (CHAR_LENGTH(rif) <= 50),
    CONSTRAINT chk_cliente_asesoria_tipo
        CHECK (CHAR_LENGTH(tipo) <= 80),
    CONSTRAINT chk_cliente_asesoria_activo
        CHECK (activo IN (0, 1)),
    CONSTRAINT fk_cliente_asesoria_cliente
        FOREIGN KEY (fk_cliente) REFERENCES clientes (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- PROVEEDORES
-- ============================================================
CREATE TABLE proveedores
(
    id       INT          NOT NULL AUTO_INCREMENT,
    rif      VARCHAR(20)  NOT NULL,
    nombre   VARCHAR(100) NOT NULL,
    email    VARCHAR(100) NOT NULL,
    telefono VARCHAR(20)  NOT NULL,
    activo   TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_proveedores_rif (rif),
    CONSTRAINT chk_proveedores_rif
        CHECK (CHAR_LENGTH(TRIM(rif)) BETWEEN 5 AND 20),
    CONSTRAINT chk_proveedores_nombre
        CHECK (CHAR_LENGTH(TRIM(nombre)) BETWEEN 2 AND 100),
    CONSTRAINT chk_proveedores_email
        CHECK (email = '' OR (CHAR_LENGTH(email) <= 100 AND email LIKE '%_@_%._%')),
    CONSTRAINT chk_proveedores_telefono
        CHECK (telefono = '' OR CHAR_LENGTH(telefono) BETWEEN 10 AND 20),
    CONSTRAINT chk_proveedores_activo
        CHECK (activo IN (0, 1))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- STATUS_SEGUIMIENTO
-- ============================================================
CREATE TABLE status_seguimiento
(
    id     INT         NOT NULL AUTO_INCREMENT,
    status VARCHAR(50) NOT NULL,
    activo TINYINT(1)  NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_status_seguimiento_status (status),
    CONSTRAINT chk_status_seguimiento_status
        CHECK (CHAR_LENGTH(TRIM(status)) BETWEEN 2 AND 50),
    CONSTRAINT chk_status_seguimiento_activo
        CHECK (activo IN (0, 1))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- TIPO_ASESORIA
-- ============================================================
CREATE TABLE tipo_asesoria
(
    id        INT          NOT NULL AUTO_INCREMENT,
    tipo      VARCHAR(100) NOT NULL,
    permitido BOOLEAN      NOT NULL DEFAULT FALSE,
    activo    TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tipo_asesoria_tipo (tipo),
    CONSTRAINT chk_tipo_asesoria_tipo
        CHECK (CHAR_LENGTH(TRIM(tipo)) BETWEEN 2 AND 100),
    CONSTRAINT chk_tipo_asesoria_permitido
        CHECK (permitido IN (0, 1)),
    CONSTRAINT chk_tipo_asesoria_activo
        CHECK (activo IN (0, 1))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- TARIFAS
-- ============================================================
CREATE TABLE tarifas
(
    id            INT            NOT NULL AUTO_INCREMENT,
    tarifa_hora   DECIMAL(10, 2) NOT NULL,
    precio_tiempo DECIMAL(10, 2) NOT NULL,
    activo        TINYINT(1)     NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tarifas_tarifa_precio (tarifa_hora, precio_tiempo),
    CONSTRAINT chk_tarifas_tarifa_hora
        CHECK (tarifa_hora > 0),
    CONSTRAINT chk_tarifas_precio_tiempo
        CHECK (precio_tiempo > 0),
    CONSTRAINT chk_tarifas_activo
        CHECK (activo IN (0, 1))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- TIPO_ACTIVO
-- ============================================================
CREATE TABLE tipo_activo
(
    id          INT         NOT NULL AUTO_INCREMENT,
    nombre_tipo VARCHAR(50) NOT NULL,
    activo      TINYINT(1)  NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tipo_activo_nombre_tipo (nombre_tipo),
    CONSTRAINT chk_tipo_activo_nombre
        CHECK (CHAR_LENGTH(TRIM(nombre_tipo)) BETWEEN 2 AND 50),
    CONSTRAINT chk_tipo_activo_activo
        CHECK (activo IN (0, 1))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- ROL_USUARIOS
-- ============================================================
CREATE TABLE rol_usuarios
(
    id     INT        NOT NULL AUTO_INCREMENT,
    fk_rol INT        NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rol_usuarios_fk_rol (fk_rol),
    CONSTRAINT chk_rol_usuarios_activo
        CHECK (activo IN (0, 1)),
    CONSTRAINT fk_rol_usuarios_rol
        FOREIGN KEY (fk_rol) REFERENCES roles (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- USUARIOS
-- ============================================================
CREATE TABLE usuarios
(
    id             INT          NOT NULL AUTO_INCREMENT,
    nombre         VARCHAR(100) NOT NULL,
    apellido       VARCHAR(100) NOT NULL,
    user_name      VARCHAR(50)  NOT NULL,
    password_hash  VARCHAR(255) NOT NULL,
    email          VARCHAR(100)          DEFAULT 'zonaweblara@gmail.com',
    estatus        VARCHAR(20)  NOT NULL DEFAULT '0',
    activo         TINYINT(1)   NOT NULL DEFAULT 1,
    fk_rol_usuario INT                   DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_user_name (user_name),
    CONSTRAINT chk_usuarios_user_name
        CHECK (CHAR_LENGTH(TRIM(user_name)) BETWEEN 3 AND 50),
    CONSTRAINT chk_usuarios_password_hash
        CHECK (CHAR_LENGTH(password_hash) BETWEEN 20 AND 255),
    CONSTRAINT chk_usuarios_email
        CHECK (email IS NULL OR CHAR_LENGTH(email) <= 100),
    CONSTRAINT chk_usuarios_estatus
        CHECK (estatus IN ('0', '1', 'activo', 'inactivo')),
    CONSTRAINT chk_usuarios_activo
        CHECK (activo IN (0, 1)),
    CONSTRAINT fk_usuarios_rol_usuario
        FOREIGN KEY (fk_rol_usuario) REFERENCES rol_usuarios (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- PERMISOS_ROL (detalle/mapeo: borrado físico)
-- ============================================================
CREATE TABLE permisos_rol
(
    id         INT NOT NULL AUTO_INCREMENT,
    fk_rol     INT NOT NULL,
    fk_permiso INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_permisos_rol_rol_permiso (fk_rol, fk_permiso),
    CONSTRAINT fk_permisos_rol_rol
        FOREIGN KEY (fk_rol) REFERENCES roles (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT fk_permisos_rol_permiso
        FOREIGN KEY (fk_permiso) REFERENCES permisos (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- PRODUCTOS
-- ============================================================
CREATE TABLE productos
(
    id                  INT            NOT NULL AUTO_INCREMENT,
    codigo              VARCHAR(50)    NOT NULL,
    nombre              VARCHAR(100)   NOT NULL,
    descripcion         TEXT                    DEFAULT NULL,
    stock               INT UNSIGNED   NOT NULL,
    stock_minimo        INT UNSIGNED   NOT NULL,
    precio_compra       DECIMAL(10, 2) NOT NULL,
    precio_venta        DECIMAL(10, 2) NOT NULL,
    fecha_creacion      DATE           NOT NULL,
    fecha_actualizacion DATE           NOT NULL,
    fk_categoria        INT            NOT NULL,
    activo              TINYINT(1)     NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_productos_codigo (codigo),
    CONSTRAINT chk_productos_codigo
        CHECK (CHAR_LENGTH(TRIM(codigo)) BETWEEN 1 AND 50),
    CONSTRAINT chk_productos_nombre
        CHECK (CHAR_LENGTH(TRIM(nombre)) BETWEEN 2 AND 100),
    CONSTRAINT chk_productos_descripcion
        CHECK (descripcion IS NULL OR CHAR_LENGTH(descripcion) <= 1000),
    CONSTRAINT chk_productos_stock_minimo
        CHECK (stock_minimo >= 1),
    CONSTRAINT chk_productos_precio_compra
        CHECK (precio_compra >= 0),
    CONSTRAINT chk_productos_precio_venta
        CHECK (precio_venta > 0),
    CONSTRAINT chk_productos_fechas
        CHECK (fecha_actualizacion >= fecha_creacion),
    CONSTRAINT chk_productos_activo
        CHECK (activo IN (0, 1)),
    CONSTRAINT fk_productos_categoria
        FOREIGN KEY (fk_categoria) REFERENCES categoria (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- ORDEN_DE_VENTA
-- ============================================================
CREATE TABLE orden_de_venta
(
    id              INT         NOT NULL AUTO_INCREMENT,
    numero_de_orden VARCHAR(50) NOT NULL,
    fecha           DATE        NOT NULL,
    fk_usuario      INT         NOT NULL,
    fk_cliente      INT         NOT NULL,
    activo          TINYINT(1)  NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orden_de_venta_numero (numero_de_orden),
    CONSTRAINT chk_orden_de_venta_numero
        CHECK (CHAR_LENGTH(TRIM(numero_de_orden)) BETWEEN 1 AND 50),
    CONSTRAINT chk_orden_de_venta_activo
        CHECK (activo IN (0, 1)),
    CONSTRAINT fk_orden_de_venta_usuario
        FOREIGN KEY (fk_usuario) REFERENCES usuarios (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_orden_de_venta_cliente
        FOREIGN KEY (fk_cliente) REFERENCES clientes (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- LINEAS_VENTA (detalle: borrado físico)
-- ============================================================
CREATE TABLE lineas_venta
(
    id          INT            NOT NULL AUTO_INCREMENT,
    cantidad    INT UNSIGNED   NOT NULL,
    precio      DECIMAL(10, 2) NOT NULL,
    fk_orden    INT            NOT NULL,
    fk_producto INT            NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT chk_lineas_venta_cantidad
        CHECK (cantidad BETWEEN 1 AND 99999),
    CONSTRAINT chk_lineas_venta_precio
        CHECK (precio >= 0),
    CONSTRAINT fk_lineas_venta_orden
        FOREIGN KEY (fk_orden) REFERENCES orden_de_venta (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT fk_lineas_venta_producto
        FOREIGN KEY (fk_producto) REFERENCES productos (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- ORDEN_ABASTECIMIENTO
-- ============================================================
CREATE TABLE orden_abastecimiento
(
    id              INT         NOT NULL AUTO_INCREMENT,
    numero_de_orden VARCHAR(50) NOT NULL,
    fecha           DATE        NOT NULL,
    fk_proveedor    INT         NOT NULL,
    fk_status       INT         NOT NULL,
    activo          TINYINT(1)  NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_orden_abastecimiento_numero (numero_de_orden),
    CONSTRAINT chk_orden_abastecimiento_numero
        CHECK (CHAR_LENGTH(TRIM(numero_de_orden)) BETWEEN 1 AND 50),
    CONSTRAINT chk_orden_abastecimiento_activo
        CHECK (activo IN (0, 1)),
    CONSTRAINT fk_orden_abastecimiento_proveedor
        FOREIGN KEY (fk_proveedor) REFERENCES proveedores (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_orden_abastecimiento_status
        FOREIGN KEY (fk_status) REFERENCES status_seguimiento (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- LINEAS_ABASTECIMIENTO (detalle: borrado físico)
-- ============================================================
CREATE TABLE lineas_abastecimiento
(
    id                      INT            NOT NULL AUTO_INCREMENT,
    cantidad                INT UNSIGNED   NOT NULL,
    precio                  DECIMAL(10, 2) NOT NULL,
    fk_orden_abastecimiento INT            NOT NULL,
    fk_producto             INT            NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT chk_lineas_abastecimiento_cantidad
        CHECK (cantidad BETWEEN 1 AND 99999),
    CONSTRAINT chk_lineas_abastecimiento_precio
        CHECK (precio >= 0),
    CONSTRAINT fk_lineas_abastecimiento_orden
        FOREIGN KEY (fk_orden_abastecimiento) REFERENCES orden_abastecimiento (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT fk_lineas_abastecimiento_producto
        FOREIGN KEY (fk_producto) REFERENCES productos (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- ASESORIA
-- ============================================================
CREATE TABLE asesoria
(
    id                  INT          NOT NULL AUTO_INCREMENT,
    documento           VARCHAR(100) NOT NULL,
    descripcion         TEXT         NOT NULL,
    fecha               DATE         NOT NULL,
    fk_cliente_asesoria INT          NOT NULL,
    fk_tipo_asesoria    INT                   DEFAULT NULL,
    activo              TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    CONSTRAINT chk_asesoria_documento
        CHECK (CHAR_LENGTH(TRIM(documento)) BETWEEN 1 AND 100),
    CONSTRAINT chk_asesoria_descripcion
        CHECK (CHAR_LENGTH(descripcion) <= 1000),
    CONSTRAINT chk_asesoria_activo
        CHECK (activo IN (0, 1)),
    CONSTRAINT fk_asesoria_cliente_asesoria
        FOREIGN KEY (fk_cliente_asesoria) REFERENCES cliente_asesoria (id)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
    CONSTRAINT fk_asesoria_tipo_asesoria
        FOREIGN KEY (fk_tipo_asesoria) REFERENCES tipo_asesoria (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- ACTIVOS
-- ============================================================
CREATE TABLE activos
(
    id             INT          NOT NULL AUTO_INCREMENT,
    marca          VARCHAR(100) NOT NULL,
    descripcion    TEXT         NOT NULL,
    is_ciber       BOOLEAN      NOT NULL DEFAULT FALSE,
    activa         BOOLEAN      NOT NULL DEFAULT TRUE,
    activo         TINYINT(1)   NOT NULL DEFAULT 1,
    fk_tipo_activo INT          NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_activos_marca_descripcion (marca, descripcion(255)),
    CONSTRAINT chk_activos_marca
        CHECK (CHAR_LENGTH(TRIM(marca)) BETWEEN 2 AND 100),
    CONSTRAINT chk_activos_descripcion
        CHECK (CHAR_LENGTH(descripcion) <= 1000),
    CONSTRAINT chk_activos_is_ciber
        CHECK (is_ciber IN (0, 1)),
    CONSTRAINT chk_activos_activa
        CHECK (activa IN (0, 1)),
    CONSTRAINT chk_activos_activo
        CHECK (activo IN (0, 1)),
    CONSTRAINT fk_activos_tipo_activo
        FOREIGN KEY (fk_tipo_activo) REFERENCES tipo_activo (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;

-- ============================================================
-- SESION_CIBER
-- ============================================================
CREATE TABLE sesion_ciber
(
    id         INT         NOT NULL AUTO_INCREMENT,
    tiempo_uso VARCHAR(9)  NOT NULL,
    finalizada TINYINT(1)  NOT NULL DEFAULT 0,
    fk_cliente INT         NOT NULL,
    fk_tarifa  INT         NOT NULL,
    fk_activo  INT                  DEFAULT NULL,
    activo     TINYINT(1)  NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    CONSTRAINT chk_sesion_ciber_tiempo_uso
        CHECK (tiempo_uso REGEXP '^[0-9]{1,3}:[0-5][0-9]:[0-5][0-9]$'),
    CONSTRAINT chk_sesion_ciber_finalizada
        CHECK (finalizada IN (0, 1)),
    CONSTRAINT chk_sesion_ciber_activo
        CHECK (activo IN (0, 1)),
    CONSTRAINT fk_sesion_ciber_cliente
        FOREIGN KEY (fk_cliente) REFERENCES clientes (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_sesion_ciber_tarifa
        FOREIGN KEY (fk_tarifa) REFERENCES tarifas (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
    CONSTRAINT fk_sesion_ciber_activo
        FOREIGN KEY (fk_activo) REFERENCES activos (id)
            ON DELETE RESTRICT
            ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;
