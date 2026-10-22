USE zona_web_lara;
SET NAMES utf8mb4;

-- ============================================================
-- MIGRACIÓN v4.4 — Permisos por acción (RBAC granular)
-- ============================================================
--
-- Qué hace
-- --------
-- 1. Añade 32 permisos compuestos con el formato "base:verbo" (43 en total).
--    Cada verbo acota una acción de escritura dentro de un módulo:
--    por ejemplo, "gestion_clientes:crear" permite registrar clientes
--    pero no editarlos ni eliminarlos.
--
-- 2. Asigna esos 32 permisos al rol Administrador (roles.id = 1), de modo
--    que la matriz de permisos refleje el acceso completo del sistema.
--
-- Por qué es idempotente
-- ----------------------
-- La tabla "permisos" no tiene índice único sobre la columna "permisos",
-- así que INSERT IGNORE no evita duplicados. Por eso cada inserción se hace
-- con INSERT ... SELECT ... WHERE NOT EXISTS, que sí es idempotente.
-- La asignación a roles usa la misma técnica.
--
-- Compatibilidad
-- --------------
-- Los roles que no tienen verbos asignados heredan el acceso completo del
-- módulo (comportamiento idéntico al de la versión 4.3). La instalación
-- no se rompe al aplicar esta migración.
--
-- Requisito previo
-- -----------------
-- src/app/core/Permisos.php (v4.4) debe existir en el despliegue: es la
-- clase que interpreta estos permisos.

-- ============================================================
-- 1. PERMISOS DE ACCIÓN ("base:verbo")
-- ============================================================

-- Inventario
INSERT INTO permisos (permisos) SELECT 'gestion_productos:crear' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_productos:crear');
INSERT INTO permisos (permisos) SELECT 'gestion_productos:editar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_productos:editar');
INSERT INTO permisos (permisos) SELECT 'gestion_productos:eliminar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_productos:eliminar');
INSERT INTO permisos (permisos) SELECT 'gestion_productos:categoria' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_productos:categoria');

-- Ventas (POS)
INSERT INTO permisos (permisos) SELECT 'gestion_ventas:registrar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_ventas:registrar');

-- Clientes
INSERT INTO permisos (permisos) SELECT 'gestion_clientes:crear' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_clientes:crear');
INSERT INTO permisos (permisos) SELECT 'gestion_clientes:editar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_clientes:editar');
INSERT INTO permisos (permisos) SELECT 'gestion_clientes:eliminar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_clientes:eliminar');

-- Órdenes de compra
INSERT INTO permisos (permisos) SELECT 'gestion_abastecimiento:crear' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_abastecimiento:crear');
INSERT INTO permisos (permisos) SELECT 'gestion_abastecimiento:editar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_abastecimiento:editar');
INSERT INTO permisos (permisos) SELECT 'gestion_abastecimiento:eliminar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_abastecimiento:eliminar');
INSERT INTO permisos (permisos) SELECT 'gestion_abastecimiento:linea' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_abastecimiento:linea');

-- Gestión de proveedores
INSERT INTO permisos (permisos) SELECT 'gestion_proveedores:crear' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_proveedores:crear');
INSERT INTO permisos (permisos) SELECT 'gestion_proveedores:editar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_proveedores:editar');
INSERT INTO permisos (permisos) SELECT 'gestion_proveedores:eliminar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_proveedores:eliminar');

-- Cybercafé
INSERT INTO permisos (permisos) SELECT 'gestion_ciber:iniciar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_ciber:iniciar');
INSERT INTO permisos (permisos) SELECT 'gestion_ciber:finalizar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_ciber:finalizar');
INSERT INTO permisos (permisos) SELECT 'gestion_ciber:pc' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_ciber:pc');

-- Activos fijos
INSERT INTO permisos (permisos) SELECT 'gestion_activos:crear' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_activos:crear');
INSERT INTO permisos (permisos) SELECT 'gestion_activos:editar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_activos:editar');
INSERT INTO permisos (permisos) SELECT 'gestion_activos:eliminar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_activos:eliminar');
INSERT INTO permisos (permisos) SELECT 'gestion_activos:estado' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_activos:estado');

-- Asesoría legal
INSERT INTO permisos (permisos) SELECT 'gestion_asesoria:crear' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_asesoria:crear');

-- Usuarios
INSERT INTO permisos (permisos) SELECT 'gestion_usuarios:crear' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_usuarios:crear');
INSERT INTO permisos (permisos) SELECT 'gestion_usuarios:editar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_usuarios:editar');
INSERT INTO permisos (permisos) SELECT 'gestion_usuarios:eliminar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_usuarios:eliminar');
INSERT INTO permisos (permisos) SELECT 'gestion_usuarios:estado' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_usuarios:estado');
INSERT INTO permisos (permisos) SELECT 'gestion_usuarios:password' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'gestion_usuarios:password');

-- Roles y permisos
INSERT INTO permisos (permisos) SELECT 'configuracion:rol' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'configuracion:rol');
INSERT INTO permisos (permisos) SELECT 'configuracion:permisos' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'configuracion:permisos');
INSERT INTO permisos (permisos) SELECT 'configuracion:asignacion' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'configuracion:asignacion');

-- Reportes
INSERT INTO permisos (permisos) SELECT 'ver_reportes:exportar' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE permisos = 'ver_reportes:exportar');

-- ============================================================
-- 2. MATRIZ DE PERMISOS DEL ROL ADMINISTRADOR (roles.id = 1)
-- ============================================================
INSERT INTO permisos_rol (fk_rol, fk_permiso)
SELECT 1, p.id
FROM permisos p
WHERE p.permisos IN (
    'gestion_productos:crear',
    'gestion_productos:editar',
    'gestion_productos:eliminar',
    'gestion_productos:categoria',
    'gestion_ventas:registrar',
    'gestion_clientes:crear',
    'gestion_clientes:editar',
    'gestion_clientes:eliminar',
    'gestion_abastecimiento:crear',
    'gestion_abastecimiento:editar',
    'gestion_abastecimiento:eliminar',
    'gestion_abastecimiento:linea',
    'gestion_proveedores:crear',
    'gestion_proveedores:editar',
    'gestion_proveedores:eliminar',
    'gestion_ciber:iniciar',
    'gestion_ciber:finalizar',
    'gestion_ciber:pc',
    'gestion_activos:crear',
    'gestion_activos:editar',
    'gestion_activos:eliminar',
    'gestion_activos:estado',
    'gestion_asesoria:crear',
    'gestion_usuarios:crear',
    'gestion_usuarios:editar',
    'gestion_usuarios:eliminar',
    'gestion_usuarios:estado',
    'gestion_usuarios:password',
    'configuracion:rol',
    'configuracion:permisos',
    'configuracion:asignacion',
    'ver_reportes:exportar'
)
AND NOT EXISTS (
    SELECT 1 FROM permisos_rol pr
    WHERE pr.fk_rol = 1 AND pr.fk_permiso = p.id
);

-- ============================================================
-- 3. VERIFICACIÓN (opcional: descomentar para auditar)
-- ============================================================
-- SELECT COUNT(*) AS permisos_totales FROM permisos;
-- SELECT COUNT(*) AS verbos FROM permisos WHERE permisos LIKE '%:%';
-- SELECT r.nombre_rol, COUNT(*) AS permisos
--   FROM permisos_rol pr JOIN roles r ON r.id = pr.fk_rol
--   WHERE r.id = 1 GROUP BY r.nombre_rol;
