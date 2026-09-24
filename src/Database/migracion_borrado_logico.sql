-- ============================================================
-- Migración: borrado lógico (soft delete)
-- Agrega la columna `activo` a las tablas de dominio que hoy
-- se borran físicamente con DELETE.
--
--   activo = 1  -> registro visible y operativo
--   activo = 0  -> registro "eliminado" (oculto de los listados,
--                  pero conservado en la BD por integridad/historial)
--
-- NOTA: Ejecutar UNA sola vez sobre una BD existente.
-- Las instalaciones nuevas ya incluyen estas columnas en
-- estructura.sql (docker-entrypoint-initdb.d).
-- ============================================================

USE zona_web_lara;

ALTER TABLE roles
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER created_at;

ALTER TABLE categoria
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER nombre_categoria;

ALTER TABLE clientes
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER telefono;

ALTER TABLE proveedores
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER telefono;

ALTER TABLE usuarios
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER estatus;

ALTER TABLE productos
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER fk_categoria;

ALTER TABLE asesoria
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER fk_tipo_asesoria;

-- activos ya cuenta con la columna `activa` (estado operativo de la
-- máquina); `activo` se usa como bandera de borrado lógico.
ALTER TABLE activos
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER activa;

ALTER TABLE orden_abastecimiento
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER fk_status;

-- NOTA: las tablas detalle (lineas_abastecimiento, permisos_rol) se
-- siguen borrando físicamente a propósito: son filas de detalle/mapeo,
-- no entidades de dominio.