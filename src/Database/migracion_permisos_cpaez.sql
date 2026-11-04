USE zona_web_lara;
SET NAMES utf8mb4;

-- ============================================================
-- Asignación de permisos totales al usuario cpaez
-- ============================================================
-- Objetivo:
--   Que el usuario cpaez (Carlos Páez) tenga todos los permisos
--   existentes en la base de datos.
--
-- Estrategia:
--   1. Se le asigna el rol Administrador (roles.id = 1), que la
--      aplicación trata además como superusuario.
--   2. Se garantiza que ese rol tenga TODOS los permisos activos
--      registrados en la tabla "permisos".
--
-- Es idempotente: puede ejecutarse varias veces sin duplicar filas.
-- ============================================================

-- 1. Asignar el rol Administrador al usuario cpaez.
UPDATE usuarios
SET fk_rol_usuario = 1
WHERE user_name = 'cpaez';

-- 2. Asegurar que el rol Administrador tenga todos los permisos activos.
INSERT INTO permisos_rol (fk_rol, fk_permiso)
SELECT 1, p.id
FROM permisos p
WHERE p.activo = 1
  AND NOT EXISTS (
      SELECT 1
      FROM permisos_rol pr
      WHERE pr.fk_rol = 1
        AND pr.fk_permiso = p.id
  );

-- 3. Verificación rápida (opcional).
SELECT u.user_name, u.fk_rol_usuario, r.nombre_rol, COUNT(pr.fk_permiso) AS total_permisos
FROM usuarios u
JOIN roles r ON r.id = u.fk_rol_usuario
LEFT JOIN permisos_rol pr ON pr.fk_rol = r.id
WHERE u.user_name = 'cpaez'
GROUP BY u.user_name, u.fk_rol_usuario, r.nombre_rol;
