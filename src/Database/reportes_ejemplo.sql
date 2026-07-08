-- ============================================================
-- REPORTES DE EJEMPLO — EIS Zona Web Lara (ZWL)
-- Base de datos: zona_web_lara
-- ============================================================

-- ============================================================
-- 1. REPORTES DE INVENTARIO / PRODUCTOS
-- ============================================================

-- 1.1. Valor total del inventario (costo vs venta)
SELECT
    COUNT(*)                                  AS total_productos,
    SUM(stock)                                AS unidades_totales,
    SUM(stock * precio_compra)                AS valor_costo_total,
    SUM(stock * precio_venta)                 AS valor_venta_total,
    SUM(stock * (precio_venta - precio_compra)) AS ganancia_potencial
FROM productos;

-- 1.2. Productos con stock crítico (bajo o agotado)
SELECT
    p.id,
    p.codigo,
    p.nombre,
    p.stock,
    p.stock_minimo,
    c.nombre_categoria                    AS categoria,
    p.precio_compra,
    p.precio_venta,
    CASE
        WHEN p.stock <= 0                 THEN 'SIN STOCK'
        WHEN p.stock <= p.stock_minimo    THEN 'CRÍTICO'
        ELSE 'OK'
    END                                   AS estado_stock
FROM productos p
LEFT JOIN categoria c ON p.fk_categoria = c.id
WHERE p.stock <= p.stock_minimo
ORDER BY p.stock ASC;

-- 1.3. Productos por categoría (cantidad y valor)
SELECT
    c.id                                   AS id_categoria,
    c.nombre_categoria                     AS categoria,
    COUNT(p.id)                            AS cantidad_productos,
    SUM(p.stock)                           AS unidades_totales,
    SUM(p.stock * p.precio_venta)          AS valor_total_venta
FROM categoria c
LEFT JOIN productos p ON p.fk_categoria = c.id
GROUP BY c.id, c.nombre_categoria
ORDER BY cantidad_productos DESC;

-- 1.4. Top productos más vendidos (por cantidad)
SELECT
    p.id,
    p.codigo,
    p.nombre,
    c.nombre_categoria                    AS categoria,
    SUM(lv.cantidad)                      AS unidades_vendidas,
    SUM(lv.cantidad * lv.precio)          AS total_generado,
    COUNT(DISTINCT lv.fk_orden)           AS veces_vendido
FROM lineas_venta lv
JOIN productos p ON lv.fk_producto = p.id
LEFT JOIN categoria c ON p.fk_categoria = c.id
GROUP BY p.id, p.codigo, p.nombre, c.nombre_categoria
ORDER BY unidades_vendidas DESC
LIMIT 20;

-- 1.5. Productos que nunca se han vendido (rotación cero)
SELECT
    p.id,
    p.codigo,
    p.nombre,
    p.stock,
    c.nombre_categoria                    AS categoria,
    p.precio_venta,
    p.fecha_creacion
FROM productos p
LEFT JOIN categoria c ON p.fk_categoria = c.id
WHERE p.id NOT IN (SELECT DISTINCT fk_producto FROM lineas_venta)
ORDER BY p.nombre;


-- ============================================================
-- 2. REPORTES DE VENTAS
-- ============================================================

-- 2.1. Resumen global de ventas
SELECT
    COUNT(DISTINCT ov.id)                 AS total_ordenes,
    COUNT(DISTINCT lv.fk_producto)        AS productos_distintos_vendidos,
    SUM(lv.cantidad)                      AS unidades_vendidas,
    SUM(lv.cantidad * lv.precio)          AS monto_total_ventas,
    AVG(lv.cantidad * lv.precio)          AS ticket_promedio
FROM orden_de_venta ov
JOIN lineas_venta lv ON lv.fk_orden = ov.id;

-- 2.2. Ventas por día (últimos 30 días)
SELECT
    ov.fecha,
    COUNT(DISTINCT ov.id)                 AS cantidad_ordenes,
    SUM(lv.cantidad)                      AS unidades_vendidas,
    SUM(lv.cantidad * lv.precio)          AS monto_total,
    ROUND(AVG(lv.cantidad * lv.precio), 2) AS ticket_promedio
FROM orden_de_venta ov
JOIN lineas_venta lv ON lv.fk_orden = ov.id
WHERE ov.fecha >= CURDATE() - INTERVAL 30 DAY
GROUP BY ov.fecha
ORDER BY ov.fecha DESC;

-- 2.3. Ventas por mes
SELECT
    YEAR(ov.fecha)                        AS anio,
    MONTH(ov.fecha)                       AS mes,
    DATE_FORMAT(ov.fecha, '%Y-%m')        AS periodo,
    COUNT(DISTINCT ov.id)                 AS cantidad_ordenes,
    SUM(lv.cantidad)                      AS unidades_vendidas,
    SUM(lv.cantidad * lv.precio)          AS monto_total
FROM orden_de_venta ov
JOIN lineas_venta lv ON lv.fk_orden = ov.id
GROUP BY anio, mes, periodo
ORDER BY anio DESC, mes DESC;

-- 2.4. Ventas por cliente (mejores clientes)
SELECT
    cl.id                                 AS id_cliente,
    cl.cedula,
    CONCAT(cl.nombre, ' ', cl.apellido)   AS cliente,
    COUNT(DISTINCT ov.id)                 AS compras_realizadas,
    SUM(lv.cantidad)                      AS unidades_compradas,
    SUM(lv.cantidad * lv.precio)          AS total_gastado,
    AVG(lv.cantidad * lv.precio)          AS gasto_promedio_por_compra
FROM clientes cl
JOIN orden_de_venta ov ON ov.fk_cliente = cl.id
JOIN lineas_venta lv ON lv.fk_orden = ov.id
GROUP BY cl.id, cl.cedula, cliente
ORDER BY total_gastado DESC
LIMIT 20;

-- 2.5. Ventas por usuario (empleado que registró)
SELECT
    u.id                                  AS id_usuario,
    u.user_name,
    CONCAT(u.nombre, ' ', u.apellido)     AS empleado,
    COUNT(DISTINCT ov.id)                 AS ordenes_registradas,
    SUM(lv.cantidad * lv.precio)          AS monto_generado
FROM usuarios u
JOIN orden_de_venta ov ON ov.fk_usuario = u.id
JOIN lineas_venta lv ON lv.fk_orden = ov.id
GROUP BY u.id, u.user_name, empleado
ORDER BY monto_generado DESC;


-- ============================================================
-- 3. REPORTES DE PROVEEDORES Y ABASTECIMIENTO
-- ============================================================

-- 3.1. Resumen de órdenes de abastecimiento por estado
SELECT
    ss.status                             AS estado,
    COUNT(oa.id)                          AS cantidad_ordenes,
    SUM(la.cantidad)                      AS unidades_solicitadas,
    SUM(la.cantidad * la.precio)          AS monto_total
FROM orden_abastecimiento oa
LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
LEFT JOIN lineas_abastecimiento la ON la.fk_orden_abastecimiento = oa.id
GROUP BY ss.status
ORDER BY cantidad_ordenes DESC;

-- 3.2. Órdenes de abastecimiento con detalle completo
SELECT
    oa.id                                 AS orden_id,
    oa.numero_de_orden,
    oa.fecha,
    pv.nombre                             AS proveedor,
    pv.rif,
    ss.status                             AS estado,
    COUNT(DISTINCT la.id)                 AS lineas,
    SUM(la.cantidad)                      AS total_unidades,
    SUM(la.cantidad * la.precio)          AS monto_total
FROM orden_abastecimiento oa
JOIN proveedores pv ON oa.fk_proveedor = pv.id
LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
LEFT JOIN lineas_abastecimiento la ON la.fk_orden_abastecimiento = oa.id
GROUP BY oa.id, oa.numero_de_orden, oa.fecha, pv.nombre, pv.rif, ss.status
ORDER BY oa.fecha DESC;

-- 3.3. Productos más solicitados a proveedores
SELECT
    p.id,
    p.codigo,
    p.nombre,
    c.nombre_categoria                    AS categoria,
    COUNT(DISTINCT la.fk_orden_abastecimiento) AS veces_ordenado,
    SUM(la.cantidad)                      AS unidades_solicitadas,
    AVG(la.precio)                        AS precio_promedio_compra
FROM lineas_abastecimiento la
JOIN productos p ON la.fk_producto = p.id
LEFT JOIN categoria c ON p.fk_categoria = c.id
GROUP BY p.id, p.codigo, p.nombre, c.nombre_categoria
ORDER BY unidades_solicitadas DESC
LIMIT 20;

-- 3.4. Gasto total por proveedor
SELECT
    pv.id                                 AS id_proveedor,
    pv.nombre                             AS proveedor,
    pv.rif,
    COUNT(DISTINCT oa.id)                 AS ordenes_realizadas,
    SUM(la.cantidad * la.precio)          AS total_gastado,
    AVG(la.cantidad * la.precio)          AS promedio_por_orden
FROM proveedores pv
LEFT JOIN orden_abastecimiento oa ON oa.fk_proveedor = pv.id
LEFT JOIN lineas_abastecimiento la ON la.fk_orden_abastecimiento = oa.id
GROUP BY pv.id, pv.nombre, pv.rif
ORDER BY total_gastado DESC;

-- 3.5. Tiempo promedio de gestión por estado
SELECT
    ss.status                             AS estado,
    COUNT(oa.id)                          AS ordenes,
    MIN(oa.fecha)                         AS primera_orden,
    MAX(oa.fecha)                         AS ultima_orden
FROM orden_abastecimiento oa
LEFT JOIN status_seguimiento ss ON oa.fk_status = ss.id
GROUP BY ss.status;


-- ============================================================
-- 4. REPORTES DE ASESORÍA LEGAL
-- ============================================================

-- 4.1. Resumen de asesorías por tipo de documento
SELECT
    ta.id                                 AS id_tipo,
    ta.tipo                               AS tipo_documento,
    COUNT(a.id)                           AS cantidad_casos,
    SUM(CASE WHEN ta.permitido = 1 THEN 1 ELSE 0 END) AS permitidos
FROM tipo_asesoria ta
LEFT JOIN asesoria a ON a.fk_tipo_asesoria = ta.id
GROUP BY ta.id, ta.tipo
ORDER BY cantidad_casos DESC;

-- 4.2. Asesorías con datos completos del cliente
SELECT
    a.id                                  AS caso_id,
    a.documento,
    a.descripcion,
    a.fecha,
    CONCAT(cl.nombre, ' ', cl.apellido)   AS ciudadano,
    cl.cedula,
    cl.telefono,
    ca.email                              AS email_contacto,
    ca.rif                                AS rif_contacto,
    ta.tipo                               AS tipo_documento
FROM asesoria a
JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
JOIN clientes cl ON ca.fk_cliente = cl.id
JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
ORDER BY a.fecha DESC;

-- 4.3. Clientes con más asesorías registradas
SELECT
    cl.id                                 AS id_cliente,
    CONCAT(cl.nombre, ' ', cl.apellido)   AS ciudadano,
    cl.cedula,
    COUNT(a.id)                           AS total_asesorias,
    MIN(a.fecha)                          AS primera_asesoria,
    MAX(a.fecha)                          AS ultima_asesoria
FROM clientes cl
JOIN cliente_asesoria ca ON ca.fk_cliente = cl.id
JOIN asesoria a ON a.fk_cliente_asesoria = ca.id
GROUP BY cl.id, ciudadano, cl.cedula
ORDER BY total_asesorias DESC;

-- 4.4. Asesorías por mes
SELECT
    YEAR(a.fecha)                         AS anio,
    MONTH(a.fecha)                        AS mes,
    DATE_FORMAT(a.fecha, '%Y-%m')         AS periodo,
    COUNT(a.id)                           AS casos_ingresados
FROM asesoria a
GROUP BY anio, mes, periodo
ORDER BY anio DESC, mes DESC;


-- ============================================================
-- 5. REPORTES DE CIBERCAFÉ
-- ============================================================

-- 5.1. Sesiones por cliente
SELECT
    CONCAT(cl.nombre, ' ', cl.apellido)   AS cliente,
    cl.cedula,
    COUNT(sc.id)                          AS sesiones_realizadas,
    SUM(CAST(SUBSTRING_INDEX(sc.tiempo_uso, ':', 1) AS UNSIGNED) * 60 +
        CAST(SUBSTRING_INDEX(sc.tiempo_uso, ':', -1) AS UNSIGNED)) AS minutos_totales,
    SUM(ta.precio_tiempo)                 AS total_gastado
FROM sesion_ciber sc
JOIN clientes cl ON sc.fk_cliente = cl.id
JOIN tarifas ta ON sc.fk_tarifa = ta.id
GROUP BY cl.id, cliente, cl.cedula
ORDER BY sesiones_realizadas DESC;

-- 5.2. Uso de estaciones (activos tipo ciber)
SELECT
    a.id                                  AS estacion_id,
    a.marca,
    a.descripcion,
    COUNT(sc.id)                          AS sesiones_registradas,
    SUM(CAST(SUBSTRING_INDEX(sc.tiempo_uso, ':', 1) AS UNSIGNED) * 60 +
        CAST(SUBSTRING_INDEX(sc.tiempo_uso, ':', -1) AS UNSIGNED)) AS minutos_totales
FROM activos a
LEFT JOIN sesion_ciber sc ON sc.fk_activo = a.id
WHERE a.is_ciber = 1
GROUP BY a.id, a.marca, a.descripcion
ORDER BY sesiones_registradas DESC;

-- 5.3. Tarifas más usadas
SELECT
    ta.id                                 AS id_tarifa,
    ta.tarifa_hora,
    ta.precio_tiempo,
    COUNT(sc.id)                          AS veces_usada,
    SUM(CAST(SUBSTRING_INDEX(sc.tiempo_uso, ':', 1) AS UNSIGNED) * 60 +
        CAST(SUBSTRING_INDEX(sc.tiempo_uso, ':', -1) AS UNSIGNED)) AS minutos_acumulados
FROM tarifas ta
LEFT JOIN sesion_ciber sc ON sc.fk_tarifa = ta.id
GROUP BY ta.id, ta.tarifa_hora, ta.precio_tiempo
ORDER BY veces_usada DESC;


-- ============================================================
-- 6. REPORTES DE USUARIOS / ROLES
-- ============================================================

-- 6.1. Usuarios con su rol
SELECT
    u.id                                  AS id_usuario,
    u.user_name,
    CONCAT(u.nombre, ' ', u.apellido)     AS nombre_completo,
    u.email,
    u.estatus,
    r.nombre_rol                          AS rol,
    ru.rol                                AS rol_detalle
FROM usuarios u
JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
JOIN roles r ON ru.fk_rol = r.id
ORDER BY u.nombre;

-- 6.2. Cantidad de usuarios por rol
SELECT
    r.nombre_rol                          AS rol,
    COUNT(u.id)                           AS cantidad_usuarios
FROM roles r
LEFT JOIN rol_usuarios ru ON ru.fk_rol = r.id
LEFT JOIN usuarios u ON u.fk_rol_usuario = ru.id
GROUP BY r.id, r.nombre_rol
ORDER BY cantidad_usuarios DESC;

-- 6.3. Permisos asignados a cada rol
SELECT
    r.id                                  AS id_rol,
    r.nombre_rol                          AS rol,
    GROUP_CONCAT(p.permisos ORDER BY p.permisos SEPARATOR ', ') AS permisos_asignados
FROM roles r
LEFT JOIN permisos_rol pr ON pr.fk_rol = r.id
LEFT JOIN permisos p ON pr.fk_permiso = p.id
GROUP BY r.id, r.nombre_rol
ORDER BY r.nombre_rol;

-- 6.4. Actividad de usuarios (ventas registradas)
SELECT
    u.id                                  AS id_usuario,
    u.user_name,
    CONCAT(u.nombre, ' ', u.apellido)     AS empleado,
    COUNT(ov.id)                          AS ventas_registradas,
    MIN(ov.fecha)                         AS primera_venta,
    MAX(ov.fecha)                         AS ultima_venta
FROM usuarios u
LEFT JOIN orden_de_venta ov ON ov.fk_usuario = u.id
GROUP BY u.id, u.user_name, empleado
ORDER BY ventas_registradas DESC;


-- ============================================================
-- 7. REPORTES DE ACTIVOS FIJOS
-- ============================================================

-- 7.1. Activos por tipo
SELECT
    ta.id                                 AS id_tipo,
    ta.nombre_tipo                        AS tipo_activo,
    COUNT(a.id)                           AS cantidad,
    SUM(CASE WHEN a.activa = 1 THEN 1 ELSE 0 END) AS activos,
    SUM(CASE WHEN a.activa = 0 THEN 1 ELSE 0 END) AS inactivos
FROM tipo_activo ta
LEFT JOIN activos a ON a.fk_tipo_activo = ta.id
GROUP BY ta.id, ta.nombre_tipo
ORDER BY cantidad DESC;

-- 7.2. Activos con detalle
SELECT
    a.id                                  AS id_activo,
    ta.nombre_tipo                        AS tipo,
    a.marca,
    a.descripcion,
    CASE WHEN a.is_ciber = 1 THEN 'Sí' ELSE 'No' END AS es_cibercafe,
    CASE WHEN a.activa = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado
FROM activos a
JOIN tipo_activo ta ON a.fk_tipo_activo = ta.id
ORDER BY ta.nombre_tipo, a.marca;


-- ============================================================
-- 8. REPORTES TRANSVERSALES / KPIS
-- ============================================================

-- 8.1. Dashboard: indicadores clave
SELECT
    (SELECT COUNT(*) FROM clientes)               AS total_clientes,
    (SELECT COUNT(*) FROM productos)              AS total_productos,
    (SELECT COUNT(*) FROM proveedores)            AS total_proveedores,
    (SELECT COUNT(*) FROM orden_de_venta)         AS total_ventas,
    (SELECT COUNT(*) FROM orden_abastecimiento)   AS total_ordenes_compra,
    (SELECT COUNT(*) FROM asesoria)               AS total_asesorias,
    (SELECT COUNT(*) FROM usuarios)               AS total_usuarios,
    (SELECT SUM(stock) FROM productos)            AS unidades_en_inventario,
    (SELECT SUM(stock * precio_venta) FROM productos) AS valor_inventario;

-- 8.2. Movimiento del mes actual
SELECT
    'Ventas'                              AS tipo_movimiento,
    COUNT(DISTINCT ov.id)                 AS cantidad,
    SUM(lv.cantidad * lv.precio)          AS monto
FROM orden_de_venta ov
JOIN lineas_venta lv ON lv.fk_orden = ov.id
WHERE MONTH(ov.fecha) = MONTH(CURDATE())
  AND YEAR(ov.fecha) = YEAR(CURDATE())
UNION ALL
SELECT
    'Abastecimientos',
    COUNT(DISTINCT oa.id),
    SUM(la.cantidad * la.precio)
FROM orden_abastecimiento oa
JOIN lineas_abastecimiento la ON la.fk_orden_abastecimiento = oa.id
WHERE MONTH(oa.fecha) = MONTH(CURDATE())
  AND YEAR(oa.fecha) = YEAR(CURDATE())
UNION ALL
SELECT
    'Asesorías',
    COUNT(*),
    NULL
FROM asesoria
WHERE MONTH(fecha) = MONTH(CURDATE())
  AND YEAR(fecha) = YEAR(CURDATE());

-- 8.3. Comparativa año actual vs año anterior (ventas mensuales)
SELECT
    YEAR(ov.fecha)                        AS anio,
    MONTH(ov.fecha)                       AS mes,
    DATE_FORMAT(ov.fecha, '%Y-%m')        AS periodo,
    COUNT(DISTINCT ov.id)                 AS ordenes,
    SUM(lv.cantidad * lv.precio)           AS monto
FROM orden_de_venta ov
JOIN lineas_venta lv ON lv.fk_orden = ov.id
WHERE YEAR(ov.fecha) IN (YEAR(CURDATE()), YEAR(CURDATE()) - 1)
GROUP BY anio, mes, periodo
ORDER BY anio DESC, mes DESC;

-- 8.4. Ranking general: top 10 productos por ingresos
SELECT
    p.id,
    p.codigo,
    p.nombre,
    c.nombre_categoria                    AS categoria,
    SUM(lv.cantidad)                      AS unidades_vendidas,
    SUM(lv.cantidad * lv.precio)          AS ingresos_totales,
    ROUND(SUM(lv.cantidad * lv.precio) * 100.0 /
        (SELECT SUM(lv2.cantidad * lv2.precio) FROM lineas_venta lv2), 2) AS porcentaje_ingresos
FROM lineas_venta lv
JOIN productos p ON lv.fk_producto = p.id
LEFT JOIN categoria c ON p.fk_categoria = c.id
GROUP BY p.id, p.codigo, p.nombre, c.nombre_categoria
ORDER BY ingresos_totales DESC
LIMIT 10;

-- 8.5. Órdenes de venta con resumen (para listado / exportación)
SELECT
    ov.id                                 AS orden_id,
    ov.numero_de_orden,
    ov.fecha,
    CONCAT(cl.nombre, ' ', cl.apellido)   AS cliente,
    cl.cedula,
    CONCAT(us.nombre, ' ', us.apellido)   AS registrado_por,
    COUNT(lv.id)                          AS lineas,
    SUM(lv.cantidad)                      AS unidades,
    SUM(lv.cantidad * lv.precio)          AS total_orden
FROM orden_de_venta ov
JOIN clientes cl ON ov.fk_cliente = cl.id
JOIN usuarios us ON ov.fk_usuario = us.id
JOIN lineas_venta lv ON lv.fk_orden = ov.id
GROUP BY ov.id, ov.numero_de_orden, ov.fecha, cliente, cl.cedula, registrado_por
ORDER BY ov.fecha DESC;
