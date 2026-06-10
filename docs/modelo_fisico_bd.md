# Modelo Físico de la Base de Datos — Zona Web Lara (ZWL v2.2)

## General

| Propiedad | Valor |
|-----------|-------|
| Motor | MySQL 8.0+ / MariaDB 10.3+ |
| Engine | InnoDB |
| Charset | `utf8mb4` |
| Collation | `utf8mb4_unicode_ci` |
| Nombre BD | `zwl` |
| Archivo DDL | `src/Database/estructura.sql` |
| Archivo datos prueba | `src/Database/datos_prueba.sql` |

---

## Diagrama Entidad-Relación

```
roles 1──< usuarios >──< ventas >──< detalle_ventas >──> productos
         │                 │                              │
         │                 └── clientes                    │
         │                                                 │
         ├──< solicitudes >──< detalle_solicitudes >──>────┤
         │       │                                         │
         │       └──> proveedores ──< producto_proveedor >─┘
         │
         ├──< sesiones_cyber >──> estaciones_cyber >── tarifas_cyber
         │       │                 │
         │       └──> clientes ────┘
         │
         ├──< bitacora_movimientos_stock >──> productos
         │
         ├──< usuario_asesoria >──< asesorias >── clientes_asesorias
         │
         └──< activos >──> tipos_activo

subcategorias 1──< categorias 1──< productos >── 0..1 modelos >── marcas
```

---

## Tablas Catálogo

### `roles`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | TINYINT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| nombre | VARCHAR(30) | NO | UNIQUE | | |
| descripcion | VARCHAR(150) | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**Índices**: PRIMARY (`id`), UNIQUE (`nombre`)

---

### `subcategorias`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | SMALLINT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| nombre | VARCHAR(50) | NO | UNIQUE | | |
| descripcion | VARCHAR(200) | SÍ | | NULL | |
| activa | BOOLEAN | SÍ | | TRUE | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**Índices**: PRIMARY (`id`), UNIQUE (`nombre`)

---

### `categorias`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | SMALLINT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| subcategoria_id | SMALLINT UNSIGNED | SÍ | FK | NULL | |
| nombre | VARCHAR(50) | NO | | | |
| descripcion | VARCHAR(200) | SÍ | | NULL | |
| activa | BOOLEAN | SÍ | | TRUE | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**Índices**: PRIMARY (`id`), UNIQUE `idx_categoria_nombre` (`subcategoria_id`, `nombre`)

**FK**: `fk_categorias_subcategoria` → `subcategorias(id)` ON DELETE RESTRICT

---

### `marcas`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | SMALLINT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| nombre | VARCHAR(100) | NO | UNIQUE | | |
| descripcion | VARCHAR(200) | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**Índices**: PRIMARY (`id`), UNIQUE (`nombre`)

---

### `modelos`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| marca_id | SMALLINT UNSIGNED | NO | FK | | |
| nombre | VARCHAR(100) | NO | | | |
| descripcion | VARCHAR(200) | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**Índices**: PRIMARY (`id`), UNIQUE `idx_modelo_nombre` (`marca_id`, `nombre`)

**FK**: `fk_modelos_marca` → `marcas(id)` ON DELETE RESTRICT

---

### `tipos_activo`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | TINYINT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| nombre | VARCHAR(50) | NO | UNIQUE | | |
| descripcion | VARCHAR(200) | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**Índices**: PRIMARY (`id`), UNIQUE (`nombre`)

---

### `tarifas_cyber`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | SMALLINT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| nombre | VARCHAR(50) | NO | UNIQUE | | |
| precio_por_hora | DECIMAL(8,2) | NO | | 0.00 | |
| tiempo_minimo | INT UNSIGNED | SÍ | | 30 | Minutos mínimos por sesión |
| activa | BOOLEAN | SÍ | | TRUE | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), UNIQUE (`nombre`)

---

## Tablas Maestras

### `usuarios`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| username | VARCHAR(30) | NO | UNIQUE | | |
| password_hash | VARCHAR(255) | NO | | | |
| nombre | VARCHAR(100) | NO | | | |
| email | VARCHAR(100) | NO | UNIQUE | | |
| telefono | VARCHAR(20) | SÍ | | NULL | |
| activo | BOOLEAN | SÍ | | TRUE | |
| rol_id | TINYINT UNSIGNED | NO | FK | 2 | |
| ultimo_acceso | DATETIME | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), UNIQUE (`username`), UNIQUE (`email`), INDEX `idx_usuarios_rol` (`rol_id`)

**FK**: `fk_usuarios_rol` → `roles(id)` ON DELETE RESTRICT

---

### `clientes`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| cedula_rif | VARCHAR(20) | NO | UNIQUE | | |
| nombre | VARCHAR(150) | NO | | | |
| telefono | VARCHAR(20) | SÍ | | NULL | |
| email | VARCHAR(100) | SÍ | | NULL | |
| direccion | TEXT | SÍ | | NULL | |
| activo | BOOLEAN | SÍ | | TRUE | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), UNIQUE (`cedula_rif`)

---

### `clientes_asesorias`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| cedula | VARCHAR(20) | NO | UNIQUE | | |
| nombre | VARCHAR(150) | NO | | | |
| email | VARCHAR(100) | SÍ | | NULL | |
| telefono | VARCHAR(20) | SÍ | | NULL | |
| direccion | TEXT | SÍ | | NULL | |
| notas_expediente | TEXT | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), UNIQUE (`cedula`)

---

### `proveedores`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| nombre | VARCHAR(150) | NO | | | |
| rif | VARCHAR(20) | SÍ | UNIQUE | NULL | |
| tipo_documento | ENUM('J','V','E','G') | SÍ | | 'J' | |
| contacto | VARCHAR(100) | SÍ | | NULL | |
| email | VARCHAR(100) | SÍ | | NULL | |
| telefono | VARCHAR(20) | SÍ | | NULL | |
| direccion | TEXT | SÍ | | NULL | |
| es_proveedor_principal | BOOLEAN | SÍ | | FALSE | |
| activo | BOOLEAN | SÍ | | TRUE | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), UNIQUE (`rif`)

---

### `productos`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| codigo | VARCHAR(50) | NO | UNIQUE | | |
| codigo_barras | VARCHAR(100) | SÍ | UNIQUE | NULL | |
| nombre | VARCHAR(150) | NO | | | |
| descripcion | TEXT | SÍ | | NULL | |
| categoria_id | SMALLINT UNSIGNED | NO | FK | | |
| modelo_id | INT UNSIGNED | SÍ | FK | NULL | |
| unidad_medida | ENUM('Unidades','Kg','Litros','Metros','Packs') | SÍ | | 'Unidades' | |
| stock | INT | NO | | 0 | |
| stock_minimo | INT | NO | | 5 | |
| ubicacion | VARCHAR(100) | SÍ | | NULL | |
| costo_compra | DECIMAL(12,2) | NO | | 0.00 | |
| precio_venta | DECIMAL(12,2) | NO | | 0.00 | |
| permite_descuento | BOOLEAN | SÍ | | TRUE | |
| estado_venta | ENUM('Activo','Inactivo') | SÍ | | 'Activo' | |
| activo | BOOLEAN | SÍ | | TRUE | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), UNIQUE (`codigo`), UNIQUE `idx_productos_barras` (`codigo_barras`), INDEX `idx_productos_categoria` (`categoria_id`), INDEX `idx_productos_modelo` (`modelo_id`)

**FK**:
- `fk_productos_categoria` → `categorias(id)` ON DELETE RESTRICT
- `fk_productos_modelo` → `modelos(id)` ON DELETE SET NULL

---

### `activos`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| nombre | VARCHAR(150) | NO | | | |
| descripcion | TEXT | SÍ | | NULL | |
| tipo_activo_id | TINYINT UNSIGNED | NO | FK | | |
| estado | ENUM('Activo','Mantenimiento','Vencida','Baja') | SÍ | | 'Activo' | |
| ubicacion | VARCHAR(100) | SÍ | | NULL | |
| valor_adquisicion | DECIMAL(12,2) | SÍ | | NULL | |
| fecha_adquisicion | DATE | SÍ | | NULL | |
| fecha_vencimiento | DATE | SÍ | | NULL | |
| responsable_id | INT UNSIGNED | SÍ | FK | NULL | |
| notas | TEXT | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), INDEX `idx_activos_tipo` (`tipo_activo_id`)

**FK**:
- `fk_activos_tipo_activo` → `tipos_activo(id)` ON DELETE RESTRICT
- `fk_activos_responsable` → `usuarios(id)` ON DELETE SET NULL

---

### `estaciones_cyber`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| nombre | VARCHAR(50) | NO | UNIQUE | | |
| estado | ENUM('Disponible','Ocupada','Mantenimiento') | SÍ | | 'Disponible' | |
| tarifa_id | SMALLINT UNSIGNED | NO | FK | | |
| especificaciones | VARCHAR(255) | SÍ | | NULL | |
| ip_local | VARCHAR(15) | SÍ | | NULL | |
| mac_address | VARCHAR(17) | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), UNIQUE (`nombre`)

**FK**: `fk_estaciones_tarifa` → `tarifas_cyber(id)` ON DELETE RESTRICT

---

## Tablas Transaccionales

### `ventas`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| fecha | DATETIME | NO | | CURRENT_TIMESTAMP | |
| usuario_id | INT UNSIGNED | SÍ | FK | NULL | |
| cliente_id | INT UNSIGNED | SÍ | FK | NULL | |
| subtotal | DECIMAL(12,2) | NO | | 0.00 | |
| descuento | DECIMAL(12,2) | NO | | 0.00 | |
| total | DECIMAL(12,2) | NO | | 0.00 | |
| estado | ENUM('completada','pendiente','cancelada','reembolsada') | SÍ | | 'completada' | |
| notas | TEXT | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), INDEX `idx_ventas_fecha` (`fecha`)

**FK**:
- `fk_ventas_usuario` → `usuarios(id)` ON DELETE SET NULL
- `fk_ventas_cliente` → `clientes(id)` ON DELETE RESTRICT

**Trigger**: `trg_actualizar_totales_venta` — AFTER INSERT en `detalle_ventas`, recalcula subtotal y total sumando los detalles.

---

### `detalle_ventas`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| venta_id | INT UNSIGNED | NO | FK | | |
| producto_id | INT UNSIGNED | NO | FK | | |
| cantidad | INT UNSIGNED | NO | | 1 | |
| precio_unitario | DECIMAL(12,2) | NO | | | |
| descuento | DECIMAL(12,2) | NO | | 0.00 | |
| subtotal | DECIMAL(12,2) | NO | | | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**FK**:
- `fk_dv_venta` → `ventas(id)` ON DELETE CASCADE
- `fk_dv_producto` → `productos(id)` ON DELETE RESTRICT

---

### `solicitudes`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| codigo | VARCHAR(20) | NO | UNIQUE | | |
| proveedor_id | INT UNSIGNED | NO | FK | | |
| fecha | DATE | NO | | (CURRENT_DATE) | |
| fecha_estimada_entrega | DATE | SÍ | | NULL | |
| tiempo_entrega_dias | SMALLINT UNSIGNED | SÍ | | NULL | |
| subtotal | DECIMAL(12,2) | NO | | 0.00 | |
| total | DECIMAL(12,2) | NO | | 0.00 | |
| estado | ENUM('Pendiente','Aprobada','Enviada','Recibida','Cancelada') | SÍ | | 'Pendiente' | |
| usuario_id | INT UNSIGNED | SÍ | FK | NULL | |
| notas | TEXT | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), UNIQUE (`codigo`)

**FK**:
- `fk_solicitudes_proveedor` → `proveedores(id)` ON DELETE RESTRICT
- `fk_solicitudes_usuario` → `usuarios(id)` ON DELETE SET NULL

---

### `detalle_solicitudes`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| solicitud_id | INT UNSIGNED | NO | FK | | |
| producto_id | INT UNSIGNED | NO | FK | | |
| cantidad_solicitada | INT UNSIGNED | NO | | 1 | |
| cantidad_recibida | INT UNSIGNED | SÍ | | NULL | |
| precio_unitario_estimado | DECIMAL(12,2) | NO | | 0.00 | |
| subtotal | DECIMAL(12,2) | NO | | 0.00 | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**FK**:
- `fk_ds_solicitud` → `solicitudes(id)` ON DELETE CASCADE
- `fk_ds_producto` → `productos(id)` ON DELETE RESTRICT

---

### `sesiones_cyber`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| estacion_id | INT UNSIGNED | NO | FK | | |
| usuario_id | INT UNSIGNED | SÍ | FK | NULL | |
| cliente_id | INT UNSIGNED | SÍ | FK | NULL | |
| hora_inicio | DATETIME | NO | | CURRENT_TIMESTAMP | |
| hora_fin | DATETIME | SÍ | | NULL | |
| costo_total | DECIMAL(10,2) | SÍ | | NULL | |
| estado | ENUM('activa','cerrada','interrumpida') | SÍ | | 'activa' | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**FK**:
- `fk_sesiones_estacion` → `estaciones_cyber(id)` ON DELETE RESTRICT
- `fk_sesiones_usuario` → `usuarios(id)` ON DELETE SET NULL
- `fk_sesiones_cliente` → `clientes(id)` ON DELETE RESTRICT

**SP**: `sp_cerrar_sesion_cyber` — calcula duración, aplica tarifa mínima, actualiza costo y libera estación.

---

### `asesorias`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | INT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| cliente_asesoria_id | INT UNSIGNED | NO | FK | | |
| documento | VARCHAR(50) | SÍ | | NULL | Nro. de Expediente o Visado |
| descripcion | TEXT | NO | | | |
| estado | ENUM('Pendiente','En Proceso','Finalizada','Archivada') | SÍ | | 'Pendiente' | |
| fecha_registro | DATETIME | NO | | CURRENT_TIMESTAMP | |
| fecha_cierre | DATETIME | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP ON UPDATE | |

**Índices**: PRIMARY (`id`), INDEX `idx_asesorias_cliente_asesoria` (`cliente_asesoria_id`), INDEX `idx_asesorias_estado` (`estado`)

**FK**: `fk_asesorias_cliente_asesoria` → `clientes_asesorias(id)` ON DELETE RESTRICT

---

### `bitacora_movimientos_stock`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| id | BIGINT UNSIGNED | NO | PK | | AUTO_INCREMENT |
| producto_id | INT UNSIGNED | NO | FK | | |
| tipo | ENUM('entrada','salida','ajuste') | NO | | | |
| cantidad | INT | NO | | | |
| stock_anterior | INT | NO | | | |
| stock_nuevo | INT | NO | | | |
| precio_unitario | DECIMAL(12,2) | SÍ | | NULL | |
| costo_total | DECIMAL(12,2) | SÍ | | NULL | |
| fecha | DATETIME | NO | | CURRENT_TIMESTAMP | |
| usuario_id | INT UNSIGNED | SÍ | FK | NULL | |
| referencia_tipo | VARCHAR(30) | SÍ | | NULL | |
| referencia_id | INT UNSIGNED | SÍ | | NULL | |
| motivo | VARCHAR(255) | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**Índices**: PRIMARY (`id`), INDEX `idx_bms_producto` (`producto_id`), INDEX `idx_bms_fecha` (`fecha`)

**FK**:
- `fk_bms_producto` → `productos(id)` ON DELETE RESTRICT
- `fk_bms_usuario` → `usuarios(id)` ON DELETE SET NULL

**SP**: `sp_registrar_movimiento_stock` — transaccional, bloquea producto con `FOR UPDATE`, valida stock, actualiza y audita.

**Trigger**: `trg_auditar_precio_producto` — BEFORE UPDATE en `productos`, si cambia `precio_venta` registra en la bitácora.

---

## Tablas Pivote (M:N)

### `producto_proveedor`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| producto_id | INT UNSIGNED | NO | PK, FK | | |
| proveedor_id | INT UNSIGNED | NO | PK, FK | | |
| codigo_proveedor | VARCHAR(50) | SÍ | | NULL | |
| precio_compra | DECIMAL(12,2) | SÍ | | NULL | |
| tiempo_entrega_dias | SMALLINT UNSIGNED | SÍ | | NULL | |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**PK compuesta**: (`producto_id`, `proveedor_id`)

**FK**:
- `fk_pp_producto` → `productos(id)` ON DELETE CASCADE
- `fk_pp_proveedor` → `proveedores(id)` ON DELETE CASCADE

---

### `usuario_asesoria`

| Columna | Tipo | Nulo | Llave | Defecto | Extra |
|---------|------|------|-------|---------|-------|
| usuario_id | INT UNSIGNED | NO | PK, FK | | |
| asesoria_id | INT UNSIGNED | NO | PK, FK | | |
| rol_en_asesoria | VARCHAR(50) | SÍ | | 'Asesor Principal' | Ej: Consultor, Gestor, Auditor |
| created_at | TIMESTAMP | SÍ | | CURRENT_TIMESTAMP | |

**PK compuesta**: (`usuario_id`, `asesoria_id`)

**FK**:
- `fk_ua_usuario` → `usuarios(id)` ON DELETE CASCADE
- `fk_ua_asesoria` → `asesorias(id)` ON DELETE CASCADE

---

## Vistas

### `v_productos_stock`

```sql
SELECT
    p.id, p.codigo, p.codigo_barras, p.nombre,
    c.nombre AS categoria, sub.nombre AS subcategoria,
    m.nombre AS marca, modl.nombre AS modelo,
    p.stock, p.stock_minimo, p.ubicacion, p.precio_venta,
    CASE
        WHEN p.stock <= 0 THEN 'Sin stock'
        WHEN p.stock <= p.stock_minimo THEN 'Critico'
        ELSE 'OK'
    END AS estado_stock,
    p.estado_venta, p.activo
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN subcategorias sub ON c.subcategoria_id = sub.id
LEFT JOIN modelos modl ON p.modelo_id = modl.id
LEFT JOIN marcas m ON modl.marca_id = m.id;
```

### `v_ventas_diarias`

```sql
SELECT
    DATE(v.fecha) AS dia,
    COUNT(*) AS total_ventas,
    SUM(v.total) AS monto_total,
    SUM(v.descuento) AS descuentos_total,
    AVG(v.total) AS ticket_promedio
FROM ventas v
WHERE v.estado = 'completada'
GROUP BY DATE(v.fecha)
ORDER BY dia DESC;
```

### `v_sesiones_activas`

```sql
SELECT
    s.id, e.nombre AS estacion,
    t.nombre AS tarifa, t.precio_por_hora, t.tiempo_minimo,
    s.hora_inicio, cl.nombre AS cliente_nombre,
    u.nombre AS usuario_registra,
    TIMESTAMPDIFF(MINUTE, s.hora_inicio, NOW()) AS minutos_transcurridos,
    ROUND(
        CASE
            WHEN TIMESTAMPDIFF(MINUTE, s.hora_inicio, NOW()) <= t.tiempo_minimo
                THEN (t.tiempo_minimo / 60.0) * t.precio_por_hora
            ELSE (TIMESTAMPDIFF(MINUTE, s.hora_inicio, NOW()) / 60.0) * t.precio_por_hora
        END, 2
    ) AS costo_estimado
FROM sesiones_cyber s
INNER JOIN estaciones_cyber e ON s.estacion_id = e.id
LEFT JOIN tarifas_cyber t ON e.tarifa_id = t.id
LEFT JOIN clientes cl ON s.cliente_id = cl.id
LEFT JOIN usuarios u ON s.usuario_id = u.id
WHERE s.estado = 'activa';
```

---

## Procedimientos Almacenados

| Nombre | Descripción |
|--------|-------------|
| `sp_registrar_movimiento_stock` | Transaccional. Bloquea producto, valida stock no negativo, actualiza `productos.stock` e inserta en `bitacora_movimientos_stock`. |
| `sp_cerrar_sesion_cyber` | Calcula duración de sesión, aplica tiempo mínimo de tarifa, actualiza `sesiones_cyber` y libera estación a 'Disponible'. |

---

## Triggers

| Nombre | Evento | Tabla | Acción |
|--------|--------|-------|--------|
| `trg_actualizar_totales_venta` | AFTER INSERT | `detalle_ventas` | Recalcula `subtotal` y `total` en la `ventas` padre |
| `trg_auditar_precio_producto` | BEFORE UPDATE | `productos` | Si cambia `precio_venta`, registra en `bitacora_movimientos_stock` |

---

## Resumen de Llaves Foráneas

| Tabla | Columna FK | Tabla Referencia | Columna Ref | ON DELETE |
|-------|-----------|-----------------|------------|-----------|
| usuarios | rol_id | roles | id | RESTRICT |
| categorias | subcategoria_id | subcategorias | id | RESTRICT |
| modelos | marca_id | marcas | id | RESTRICT |
| productos | categoria_id | categorias | id | RESTRICT |
| productos | modelo_id | modelos | id | SET NULL |
| activos | tipo_activo_id | tipos_activo | id | RESTRICT |
| activos | responsable_id | usuarios | id | SET NULL |
| estaciones_cyber | tarifa_id | tarifas_cyber | id | RESTRICT |
| sesiones_cyber | estacion_id | estaciones_cyber | id | RESTRICT |
| sesiones_cyber | usuario_id | usuarios | id | SET NULL |
| sesiones_cyber | cliente_id | clientes | id | RESTRICT |
| ventas | usuario_id | usuarios | id | SET NULL |
| ventas | cliente_id | clientes | id | RESTRICT |
| detalle_ventas | venta_id | ventas | id | CASCADE |
| detalle_ventas | producto_id | productos | id | RESTRICT |
| solicitudes | proveedor_id | proveedores | id | RESTRICT |
| solicitudes | usuario_id | usuarios | id | SET NULL |
| detalle_solicitudes | solicitud_id | solicitudes | id | CASCADE |
| detalle_solicitudes | producto_id | productos | id | RESTRICT |
| producto_proveedor | producto_id | productos | id | CASCADE |
| producto_proveedor | proveedor_id | proveedores | id | CASCADE |
| bitacora_movimientos_stock | producto_id | productos | id | RESTRICT |
| bitacora_movimientos_stock | usuario_id | usuarios | id | SET NULL |
| asesorias | cliente_asesoria_id | clientes_asesorias | id | RESTRICT |
| usuario_asesoria | usuario_id | usuarios | id | CASCADE |
| usuario_asesoria | asesoria_id | asesorias | id | CASCADE |

---

## Convenciones y Patrones

- **19 tablas**, **3 vistas**, **2 procedimientos**, **2 triggers**
- **Borrado lógico**: tablas maestras tienen columna `activo` BOOLEAN
- **Timestamps auditables**: `created_at` en todas las tablas; `updated_at` con `ON UPDATE CURRENT_TIMESTAMP` en las transaccionales
- **Pivotes con PK compuesta**: `producto_proveedor` y `usuario_asesoria`
- **Referencia polimórfica**: `bitacora_movimientos_stock` usa `referencia_tipo` + `referencia_id` para rastrear origen del movimiento
- **Restricción `RESTRICT`**: evita borrados en cascada no deseados en tablas maestras
- **Cascada en pivotes y detalles**: `CASCADE` en tablas intermedias y líneas de detalle




-- ============================================================
-- Scrip SQL de la base de datos
-- ============================================================

CREATE DATABASE IF NOT EXISTS zwl_V2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zwl_V2;

-- SET de configuración para asegurar compatibilidad de caracteres
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TABLAS DE CATÁLOGO / SOPORTE (Lookup Tables)
-- ============================================================

CREATE TABLE IF NOT EXISTS roles (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE,
    descripcion VARCHAR(150) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subcategorias (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NULL,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subcategoria_id SMALLINT UNSIGNED NULL,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(200) NULL,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_categorias_subcategoria FOREIGN KEY (subcategoria_id) 
        REFERENCES subcategorias(id) ON DELETE RESTRICT,
    UNIQUE KEY idx_categoria_nombre (subcategoria_id, nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marcas (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS modelos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    marca_id SMALLINT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(200) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_modelos_marca FOREIGN KEY (marca_id) 
        REFERENCES marcas(id) ON DELETE RESTRICT,
    UNIQUE KEY idx_modelo_nombre (marca_id, nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tipos_activo (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tarifas_cyber (
    id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE COMMENT 'Ej: Gaming, Oficina, Premium',
    precio_por_hora DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    tiempo_minimo INT UNSIGNED DEFAULT 30 COMMENT 'Minutos mínimos por sesión de cobro',
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. TABLAS MAESTRAS PRINCIPALES
-- ============================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20) NULL,
    activo BOOLEAN DEFAULT TRUE,
    rol_id TINYINT UNSIGNED NOT NULL DEFAULT 2,
    ultimo_acceso DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_usuarios_rol ON usuarios(rol_id);

CREATE TABLE IF NOT EXISTS clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula_rif VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    direccion TEXT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clientes_asesorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(100) NULL,
    telefono VARCHAR(20) NULL,
    direccion TEXT NULL,
    notas_expediente TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proveedores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    rif VARCHAR(20) NULL UNIQUE,
    tipo_documento ENUM('J','V','E','G') DEFAULT 'J',
    contacto VARCHAR(100) NULL,
    email VARCHAR(100) NULL,
    telefono VARCHAR(20) NULL,
    direccion TEXT NULL,
    es_proveedor_principal BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    codigo_barras VARCHAR(100) NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    categoria_id SMALLINT UNSIGNED NOT NULL,
    modelo_id INT UNSIGNED NULL,
    unidad_medida ENUM('Unidades', 'Kg', 'Litros', 'Metros', 'Packs') DEFAULT 'Unidades',
    stock INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    ubicacion VARCHAR(100) NULL,
    costo_compra DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    precio_venta DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    permite_descuento BOOLEAN DEFAULT TRUE,
    estado_venta ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
    CONSTRAINT fk_productos_modelo FOREIGN KEY (modelo_id) REFERENCES modelos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE UNIQUE INDEX idx_productos_barras ON productos(codigo_barras);
CREATE INDEX idx_productos_categoria ON productos(categoria_id);
CREATE INDEX idx_productos_modelo ON productos(modelo_id);

CREATE TABLE IF NOT EXISTS activos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    tipo_activo_id TINYINT UNSIGNED NOT NULL,
    estado ENUM('Activo', 'Mantenimiento', 'Vencida', 'Baja') DEFAULT 'Activo',
    ubicacion VARCHAR(100) NULL,
    valor_adquisicion DECIMAL(12,2) NULL,
    fecha_adquisicion DATE NULL,
    fecha_vencimiento DATE NULL,
    responsable_id INT UNSIGNED NULL,
    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_activos_tipo_activo FOREIGN KEY (tipo_activo_id) REFERENCES tipos_activo(id) ON DELETE RESTRICT,
    CONSTRAINT fk_activos_responsable FOREIGN KEY (responsable_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_activos_tipo ON activos(tipo_activo_id);

-- ============================================================
-- 3. TABLAS PUENTE (Relaciones M:N)
-- ============================================================

CREATE TABLE IF NOT EXISTS producto_proveedor (
    producto_id INT UNSIGNED NOT NULL,
    proveedor_id INT UNSIGNED NOT NULL,
    codigo_proveedor VARCHAR(50) NULL,
    precio_compra DECIMAL(12,2) NULL,
    tiempo_entrega_dias SMALLINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (producto_id, proveedor_id),
    CONSTRAINT fk_pp_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    CONSTRAINT fk_pp_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. MÓDULO TRANSACCIONAL Y BITÁCORAS
-- ============================================================

CREATE TABLE IF NOT EXISTS ventas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT UNSIGNED NULL,
    cliente_id INT UNSIGNED NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    descuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estado ENUM('completada', 'pendiente', 'cancelada', 'reembolsada') DEFAULT 'completada',
    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ventas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_ventas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_ventas_fecha ON ventas(fecha);

CREATE TABLE IF NOT EXISTS detalle_ventas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venta_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(12,2) NOT NULL,
    descuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dv_venta FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    CONSTRAINT fk_dv_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitudes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    proveedor_id INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
    fecha_estimada_entrega DATE NULL,
    tiempo_entrega_dias SMALLINT UNSIGNED NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estado ENUM('Pendiente', 'Aprobada', 'Enviada', 'Recibida', 'Cancelada') DEFAULT 'Pendiente',
    usuario_id INT UNSIGNED NULL,
    notas TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitudes_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE RESTRICT,
    CONSTRAINT fk_solicitudes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detalle_solicitudes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad_solicitada INT UNSIGNED NOT NULL DEFAULT 1,
    cantidad_recibida INT UNSIGNED NULL,
    precio_unitario_estimado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ds_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
    CONSTRAINT fk_ds_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bitacora_movimientos_stock (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id INT UNSIGNED NOT NULL,
    tipo ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad INT NOT NULL,
    stock_anterior INT NOT NULL,
    stock_nuevo INT NOT NULL,
    precio_unitario DECIMAL(12,2) NULL,
    costo_total DECIMAL(12,2) NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT UNSIGNED NULL,
    referencia_tipo VARCHAR(30) NULL,
    referencia_id INT UNSIGNED NULL,
    motivo VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bms_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT,
    CONSTRAINT fk_bms_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_bms_producto ON bitacora_movimientos_stock(producto_id);
CREATE INDEX idx_bms_fecha ON bitacora_movimientos_stock(fecha);

-- ============================================================
-- 5. MÓDULO CYBERCAFÉ
-- ============================================================

CREATE TABLE IF NOT EXISTS estaciones_cyber (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    estado ENUM('Disponible', 'Ocupada', 'Mantenimiento') DEFAULT 'Disponible',
    tarifa_id SMALLINT UNSIGNED NOT NULL,
    especificaciones VARCHAR(255) NULL,
    ip_local VARCHAR(15) NULL,
    mac_address VARCHAR(17) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_estaciones_tarifa FOREIGN KEY (tarifa_id) REFERENCES tarifas_cyber(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sesiones_cyber (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estacion_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    cliente_id INT UNSIGNED NULL,
    hora_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    hora_fin DATETIME NULL,
    costo_total DECIMAL(10,2) NULL,
    estado ENUM('activa', 'cerrada', 'interrumpida') DEFAULT 'activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sesiones_estacion FOREIGN KEY (estacion_id) REFERENCES estaciones_cyber(id) ON DELETE RESTRICT,
    CONSTRAINT fk_sesiones_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_sesiones_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. MÓDULO ASESORÍAS LEGALES
-- ============================================================

CREATE TABLE IF NOT EXISTS asesorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_asesoria_id INT UNSIGNED NOT NULL,
    documento VARCHAR(50) NULL COMMENT 'Ej: Nro de Expediente o Visado',
    descripcion TEXT NOT NULL,
    estado ENUM('Pendiente', 'En Proceso', 'Finalizada', 'Archivada') DEFAULT 'Pendiente',
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_asesorias_cliente_asesoria FOREIGN KEY (cliente_asesoria_id) REFERENCES clientes_asesorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_asesorias_cliente_asesoria ON asesorias(cliente_asesoria_id);
CREATE INDEX idx_asesorias_estado ON asesorias(estado);

CREATE TABLE IF NOT EXISTS usuario_asesoria (
    usuario_id INT UNSIGNED NOT NULL,
    asesoria_id INT UNSIGNED NOT NULL,
    rol_en_asesoria VARCHAR(50) DEFAULT 'Asesor Principal' COMMENT 'Ej: Consultor, Gestor, Auditor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, asesoria_id),
    CONSTRAINT fk_ua_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_ua_asesoria FOREIGN KEY (asesoria_id) REFERENCES asesorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Habilitamos las llaves foráneas antes de crear las vistas y procedimientos
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 7. VISTAS DEL SISTEMA
-- ============================================================

CREATE OR REPLACE VIEW v_productos_stock AS
SELECT
    p.id, p.codigo, p.codigo_barras, p.nombre,
    c.nombre AS categoria, sub.nombre AS subcategoria,
    m.nombre AS marca, modl.nombre AS modelo,
    p.stock, p.stock_minimo, p.ubicacion, p.precio_venta,
    CASE
        WHEN p.stock <= 0 THEN 'Sin stock'
        WHEN p.stock <= p.stock_minimo THEN 'Crítico'
        ELSE 'OK'
    END AS estado_stock,
    p.estado_venta, p.activo
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
LEFT JOIN subcategorias sub ON c.subcategoria_id = sub.id
LEFT JOIN modelos modl ON p.modelo_id = modl.id
LEFT JOIN marcas m ON modl.marca_id = m.id;

CREATE OR REPLACE VIEW v_ventas_diarias AS
SELECT
    DATE(v.fecha) AS dia,
    COUNT(*) AS total_ventas,
    SUM(v.total) AS monto_total,
    SUM(v.descuento) AS descuentos_total,
    AVG(v.total) AS ticket_promedio
FROM ventas v
WHERE v.estado = 'completada'
GROUP BY DATE(v.fecha)
ORDER BY dia DESC;

CREATE OR REPLACE VIEW v_sesiones_activas AS
SELECT
    s.id, e.nombre AS estacion,
    t.nombre AS tarifa, t.precio_por_hora, t.tiempo_minimo,
    s.hora_inicio, cl.nombre AS cliente_nombre,
    u.nombre AS usuario_registra,
    TIMESTAMPDIFF(MINUTE, s.hora_inicio, NOW()) AS minutos_transcurridos,
    ROUND(
        CASE 
            WHEN TIMESTAMPDIFF(MINUTE, s.hora_inicio, NOW()) <= t.tiempo_minimo THEN (t.tiempo_minimo / 60.0) * t.precio_por_hora
            ELSE (TIMESTAMPDIFF(MINUTE, s.hora_inicio, NOW()) / 60.0) * t.precio_por_hora
        END, 2
    ) AS costo_estimado
FROM sesiones_cyber s
INNER JOIN estaciones_cyber e ON s.estacion_id = e.id
LEFT JOIN tarifas_cyber t ON e.tarifa_id = t.id
LEFT JOIN clientes cl ON s.cliente_id = cl.id
LEFT JOIN usuarios u ON s.usuario_id = u.id
WHERE s.estado = 'activa';

-- ============================================================
-- 8. PROCEDIMIENTOS ALMACENADOS Y DESENCADENADORES (TRIGGERS)
-- ============================================================

DELIMITER //

-- PROCEDIMIENTO: Registrar movimiento directamente en la tabla productos e historial en bitácora
CREATE PROCEDURE sp_registrar_movimiento_stock(
    IN p_producto_id INT UNSIGNED,
    IN p_tipo ENUM('entrada', 'salida', 'ajuste'),
    IN p_cantidad INT,
    IN p_usuario_id INT UNSIGNED,
    IN p_motivo VARCHAR(255),
    IN p_referencia_tipo VARCHAR(30),
    IN p_referencia_id INT UNSIGNED
)
BEGIN
    DECLARE v_stock_actual INT DEFAULT 0;
    DECLARE v_stock_nuevo INT;
    DECLARE v_cantidad_efectiva INT;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- Bloqueo seguro y lectura directa desde la tabla productos
    SELECT stock INTO v_stock_actual
    FROM productos
    WHERE id = p_producto_id
    FOR UPDATE;

    IF p_tipo = 'salida' THEN
        SET v_cantidad_efectiva = -ABS(p_cantidad);
    ELSE
        SET v_cantidad_efectiva = ABS(p_cantidad);
    END IF;

    SET v_stock_nuevo = v_stock_actual + v_cantidad_efectiva;

    IF v_stock_nuevo < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: El inventario del producto no puede quedar en negativo';
    END IF;

    -- Modificación de cantidad directa en la tabla productos
    UPDATE productos SET stock = v_stock_nuevo WHERE id = p_producto_id;

    -- Envío de datos a la bitácora histórica
    INSERT INTO bitacora_movimientos_stock (
        producto_id, tipo, cantidad, stock_anterior, stock_nuevo,
        usuario_id, motivo, referencia_tipo, referencia_id
    ) VALUES (
        p_producto_id, p_tipo, v_cantidad_efectiva,
        v_stock_actual, v_stock_nuevo,
        p_usuario_id, p_motivo, p_referencia_tipo, p_referencia_id
    );

    COMMIT;
END //

-- PROCEDIMIENTO: Cierre automatizado de sesión cyber basándose en tiempo mínimo y precio por hora
CREATE PROCEDURE sp_cerrar_sesion_cyber(
    IN p_sesion_id INT UNSIGNED
)
BEGIN
    DECLARE v_tarifa_precio DECIMAL(8,2);
    DECLARE v_tiempo_minimo INT;
    DECLARE v_minutos INT;
    DECLARE v_costo DECIMAL(10,2);

    SELECT t.precio_por_hora, t.tiempo_minimo, TIMESTAMPDIFF(MINUTE, s.hora_inicio, NOW())
    INTO v_tarifa_precio, v_tiempo_minimo, v_minutos
    FROM sesiones_cyber s
    INNER JOIN estaciones_cyber e ON s.estacion_id = e.id
    INNER JOIN tarifas_cyber t ON e.tarifa_id = t.id
    WHERE s.id = p_sesion_id;

    -- Validación del tiempo mínimo configurado en la tarifa
    IF v_minutos < v_tiempo_minimo THEN
        SET v_minutos = v_tiempo_minimo;
    END IF;

    SET v_costo = ROUND((v_minutos / 60.0) * v_tarifa_precio, 2);

    UPDATE sesiones_cyber SET
        hora_fin = NOW(), costo_total = v_costo, estado = 'cerrada'
    WHERE id = p_sesion_id;

    UPDATE estaciones_cyber SET estado = 'Disponible'
    WHERE id = (SELECT estacion_id FROM sesiones_cyber WHERE id = p_sesion_id);
END //

-- TRIGGER: Actualizar totales de la cabecera de venta de forma automática (Sin IVA)
CREATE TRIGGER trg_actualizar_totales_venta
AFTER INSERT ON detalle_ventas
FOR EACH ROW
BEGIN
    UPDATE ventas v
    SET
        v.subtotal = (SELECT COALESCE(SUM(subtotal), 0) FROM detalle_ventas WHERE venta_id = NEW.venta_id),
        v.total = (SELECT COALESCE(SUM(subtotal), 0) FROM detalle_ventas WHERE venta_id = NEW.venta_id)
    WHERE v.id = NEW.venta_id;
END //

-- TRIGGER: Auditar cambios manuales de precio directamente en la bitácora de stock
CREATE TRIGGER trg_auditar_precio_producto
BEFORE UPDATE ON productos
FOR EACH ROW
BEGIN
    IF OLD.precio_venta != NEW.precio_venta THEN
        INSERT INTO bitacora_movimientos_stock (
            producto_id, tipo, cantidad, stock_anterior, stock_nuevo,
            usuario_id, motivo, referencia_tipo
        ) VALUES (
            NEW.id, 'ajuste', 0, OLD.stock, NEW.stock,
            NULL, CONCAT('Cambio de precio: ', OLD.precio_venta, ' -> ', NEW.precio_venta),
            'ajuste_precio'
        );
    END IF;
END //

DELIMITER ;