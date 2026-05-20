# Documentación del Diseño Lógico de Base de Datos - Sistema ZWL v2.0

## 1. Introducción

El diseño lógico representa la estructura detallada de la base de datos del Sistema ZWL. Esta versión 2.0 implementa normalización completa, tablas puente (M:N), eliminación de redundancias, seguridad por roles, autenticación segura con bcrypt, procedimientos almacenados transaccionales, triggers para automatización de reglas de negocio y eventos programados.

## 2. Esquema de la Base de Datos

**Nombre**: `zwl`
**Motor**: MySQL 8.0+ / MariaDB 10.3+ (InnoDB)
**Juego de caracteres**: `utf8mb4`
**Cotejamiento**: `utf8mb4_unicode_ci`

## 3. Diagrama de Relaciones (Simplificado)

```
roles ──< usuarios >──< ventas >──< detalle_ventas >──> productos
         │                │                                  │
         │                └── tipos_pago                     │
         │                                                   │
         ├──< solicitudes >──< detalle_solicitudes >──>──────┤
         │       │                                           │
         │       └──> proveedores ──< producto_proveedor >───┘
         │
         ├──< sesiones_cyber >──> estaciones_cyber
         │       │                    │
         │       └──> tarifas_cyber ──┘
         │
         ├──< movimientos_stock >──> productos
         │
         ├──< asesorias
         │
         └──< activos >──> tipos_activo

categorias ──< productos >── marcas
```

## 4. Especificación de Tablas

### 4.1 Tablas de Catálogo (Lookup Tables)

#### `roles`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | TINYINT UNSIGNED | NO | PK | Identificador |
| nombre | VARCHAR(30) | NO | UNI | Nombre del rol |
| descripcion | VARCHAR(150) | SI | - | Descripción |
| created_at | TIMESTAMP | SI | - | Fecha creación |

#### `categorias`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | SMALLINT UNSIGNED | NO | PK | Identificador |
| nombre | VARCHAR(50) | NO | UNI | Nombre categoría |
| descripcion | VARCHAR(200) | SI | - | Descripción |
| activa | BOOLEAN | SI | - | Está activa |
| created_at | TIMESTAMP | SI | - | Fecha creación |

#### `marcas`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | SMALLINT UNSIGNED | NO | PK | Identificador |
| nombre | VARCHAR(100) | NO | UNI | Nombre marca |
| descripcion | VARCHAR(200) | SI | - | Descripción |
| created_at | TIMESTAMP | SI | - | Fecha creación |

#### `tipos_activo`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | TINYINT UNSIGNED | NO | PK | Identificador |
| nombre | VARCHAR(50) | NO | UNI | Tipo de activo |
| descripcion | VARCHAR(200) | SI | - | Descripción |
| created_at | TIMESTAMP | SI | - | Fecha creación |

#### `tarifas_cyber`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | SMALLINT UNSIGNED | NO | PK | Identificador |
| nombre | VARCHAR(50) | NO | UNI | Tarifa (Gaming, Oficina, Premium, Estudiante) |
| precio_por_hora | DECIMAL(8,2) | NO | - | Precio por hora |
| precio_por_minuto | DECIMAL(6,2) | SI | - | Precio fraccionado |
| tiempo_minimo | INT UNSIGNED | SI | - | Minutos mínimos por sesión (defecto 30) |
| activa | BOOLEAN | SI | - | Tarifa activa |
| created_at | TIMESTAMP | SI | - | Fecha creación |
| updated_at | TIMESTAMP | SI | - | Fecha actualización |

#### `tipos_pago`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | TINYINT UNSIGNED | NO | PK | Identificador |
| nombre | VARCHAR(30) | NO | UNI | Efectivo, Transferencia, Punto, Mixto, Crédito |
| created_at | TIMESTAMP | SI | - | Fecha creación |

---

### 4.2 Tablas Principales

#### `usuarios`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador único |
| username | VARCHAR(30) | NO | UNI | Nombre de usuario para login |
| password_hash | VARCHAR(255) | NO | - | Hash bcrypt de la contraseña |
| nombre | VARCHAR(100) | NO | - | Nombre completo |
| email | VARCHAR(100) | NO | UNI | Correo electrónico |
| telefono | VARCHAR(20) | SI | - | Teléfono |
| activo | BOOLEAN | SI | - | Usuario activo |
| rol_id | TINYINT UNSIGNED | NO | FK | Rol del usuario |
| ultimo_acceso | DATETIME | SI | - | Último login |
| created_at | TIMESTAMP | SI | - | Fecha creación |
| updated_at | TIMESTAMP | SI | - | Fecha actualización |

**Características**: Autenticación segura (bcrypt), roles para permisos, control de acceso con registro de último acceso.

---

#### `productos`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador único |
| codigo | VARCHAR(50) | NO | UNI | SKU del producto |
| codigo_barras | VARCHAR(100) | SI | UNI | Código de barras |
| nombre | VARCHAR(150) | NO | - | Nombre del producto |
| descripcion | TEXT | SI | - | Descripción detallada |
| categoria_id | SMALLINT UNSIGNED | NO | FK | Categoría normalizada |
| marca_id | SMALLINT UNSIGNED | SI | FK | Marca normalizada |
| unidad_medida | ENUM | SI | - | Unidades, Kg, Litros, Metros, Packs |
| stock | INT | NO | - | Stock actual |
| stock_minimo | INT | NO | - | Punto de reorden |
| ubicacion | VARCHAR(100) | SI | - | Ubicación física |
| costo_compra | DECIMAL(12,2) | NO | - | Costo de adquisición |
| precio_venta | DECIMAL(12,2) | NO | - | Precio de venta |
| iva | DECIMAL(5,2) | SI | - | % IVA (defecto 16.00) |
| permite_descuento | BOOLEAN | SI | - | Permite descuento |
| estado_venta | ENUM | SI | - | Activo/Inactivo para ventas |
| activo | BOOLEAN | SI | - | Producto activo |
| created_at | TIMESTAMP | SI | - | Fecha creación |
| updated_at | TIMESTAMP | SI | - | Fecha actualización |

**Características**: Categorías y marcas normalizadas en tablas separadas. `estado` del stock es calculado vía vista `v_productos_stock`. Se eliminó columna redundante de estado stock. UNIQUE INDEX en `codigo_barras`.

---

#### `proveedores`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador |
| nombre | VARCHAR(150) | NO | - | Nombre empresa |
| rif | VARCHAR(20) | SI | UNI | Registro fiscal |
| tipo_documento | ENUM(J,V,E,G) | SI | - | Tipo RIF |
| contacto | VARCHAR(100) | SI | - | Persona contacto |
| email | VARCHAR(100) | SI | - | Correo |
| telefono | VARCHAR(20) | SI | - | Teléfono |
| direccion | TEXT | SI | - | Dirección fiscal |
| activo | BOOLEAN | SI | - | Proveedor activo |
| created_at | TIMESTAMP | SI | - | Fecha creación |
| updated_at | TIMESTAMP | SI | - | Fecha actualización |

**Características**: RIF único, dirección fiscal, campo activo para baja lógica.

---

#### `ventas`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador |
| fecha | DATETIME | NO | - | Fecha de venta |
| usuario_id | INT UNSIGNED | SI | FK | Vendedor |
| cliente_nombre | VARCHAR(150) | SI | - | Cliente (venta directa) |
| cliente_cedula | VARCHAR(20) | SI | - | Cédula cliente |
| tipo_pago_id | TINYINT UNSIGNED | SI | FK | Forma de pago |
| subtotal | DECIMAL(12,2) | NO | - | Suma productos sin IVA |
| descuento | DECIMAL(12,2) | NO | - | Descuento global |
| iva_total | DECIMAL(12,2) | NO | - | Total IVA |
| total | DECIMAL(12,2) | NO | - | Total final |
| estado | ENUM | NO | - | completada/pendiente/cancelada/reembolsada |
| notas | TEXT | SI | - | Notas adicionales |
| created_at | TIMESTAMP | SI | - | Fecha creación |
| updated_at | TIMESTAMP | SI | - | Fecha actualización |

**Características**: Datos del cliente, tipo de pago normalizado, subtotal e IVA desglosados, estado "reembolsada". El trigger `trg_actualizar_totales_venta` actualiza subtotal y total automáticamente al insertar detalles.

---

#### `detalle_ventas` (Bridge: ventas M:N productos)
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador |
| venta_id | INT UNSIGNED | NO | FK | Venta relacionada |
| producto_id | INT UNSIGNED | NO | FK | Producto vendido |
| cantidad | INT UNSIGNED | NO | - | Cantidad |
| precio_unitario | DECIMAL(12,2) | NO | - | Precio al momento |
| iva_unitario | DECIMAL(12,2) | NO | - | IVA del producto |
| descuento | DECIMAL(12,2) | NO | - | Descuento línea |
| subtotal | DECIMAL(12,2) | NO | - | (precio - desc) * cantidad |
| created_at | TIMESTAMP | SI | - | Fecha creación |

**Características**: IVA y descuento por línea para facturación detallada.

---

#### `solicitudes`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador |
| codigo | VARCHAR(20) | NO | UNI | Código único (ej. SOL-2026-0001) |
| proveedor_id | INT UNSIGNED | NO | FK | Proveedor |
| fecha | DATE | NO | - | Fecha solicitud |
| fecha_estimada_entrega | DATE | SI | - | ETA |
| tipo_pago_id | TINYINT UNSIGNED | SI | FK | Forma de pago |
| subtotal | DECIMAL(12,2) | NO | - | Subtotal |
| iva_total | DECIMAL(12,2) | NO | - | IVA |
| total | DECIMAL(12,2) | NO | - | Total |
| estado | ENUM | NO | - | Pendiente/Aprobada/Enviada/Recibida/Cancelada |
| usuario_id | INT UNSIGNED | SI | FK | Creador |
| notas | TEXT | SI | - | Notas |
| created_at | TIMESTAMP | SI | - | Fecha creación |
| updated_at | TIMESTAMP | SI | - | Fecha actualización |

**Características**: Estados expandidos para seguimiento completo, totales económicos, fecha estimada de entrega.

---

#### `detalle_solicitudes` (Bridge: solicitudes M:N productos)
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador |
| solicitud_id | INT UNSIGNED | NO | FK | Solicitud |
| producto_id | INT UNSIGNED | NO | FK | Producto solicitado |
| cantidad_solicitada | INT UNSIGNED | NO | - | Cantidad pedida |
| cantidad_recibida | INT UNSIGNED | SI | - | Cantidad recibida (recepción parcial) |
| precio_unitario_estimado | DECIMAL(12,2) | NO | - | Precio estimado |
| subtotal | DECIMAL(12,2) | NO | - | Subtotal línea |
| created_at | TIMESTAMP | SI | - | Fecha creación |

**Características**: Permite recepciones parciales y saber exactamente qué productos pidió cada solicitud.

---

#### `producto_proveedor` (Bridge: productos M:N proveedores)
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| producto_id | INT UNSIGNED | NO | PK/FK | Producto |
| proveedor_id | INT UNSIGNED | NO | PK/FK | Proveedor |
| codigo_proveedor | VARCHAR(50) | SI | - | SKU del proveedor |
| precio_compra | DECIMAL(12,2) | SI | - | Precio específico |
| tiempo_entrega_dias | SMALLINT UNSIGNED | SI | - | Tiempo de entrega |
| es_proveedor_principal | BOOLEAN | SI | - | Proveedor por defecto |
| created_at | TIMESTAMP | SI | - | Fecha creación |

**Características**: Un producto puede tener múltiples proveedores con diferentes precios y tiempos de entrega. Clave compuesta (M:N real).

---

#### `activos`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador |
| nombre | VARCHAR(150) | NO | - | Nombre del activo |
| descripcion | TEXT | SI | - | Descripción |
| tipo_activo_id | TINYINT UNSIGNED | NO | FK | Tipo normalizado |
| estado | ENUM | NO | - | Activo/Mantenimiento/Vencida/Baja |
| ubicacion | VARCHAR(100) | SI | - | Ubicación física |
| valor_adquisicion | DECIMAL(12,2) | SI | - | Valor de compra |
| fecha_adquisicion | DATE | SI | - | Fecha de compra |
| fecha_vencimiento | DATE | SI | - | Vencimiento (licencias) |
| responsable_id | INT UNSIGNED | SI | FK | Custodio del activo |
| notas | TEXT | SI | - | Notas |
| created_at | TIMESTAMP | SI | - | Fecha creación |
| updated_at | TIMESTAMP | SI | - | Fecha actualización |

**Características**: Tipos normalizados, responsable asignado, ubicación, valor de adquisición para control contable.

---

#### `estaciones_cyber`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador |
| nombre | VARCHAR(50) | NO | UNI | PC-01, PC-02... |
| estado | ENUM | NO | - | Disponible/Ocupada/Mantenimiento |
| tarifa_id | SMALLINT UNSIGNED | NO | FK | Tarifa asociada |
| especificaciones | VARCHAR(255) | SI | - | RAM, CPU, GPU |
| ip_local | VARCHAR(15) | SI | - | Dirección IP |
| mac_address | VARCHAR(17) | SI | - | Dirección MAC |
| created_at | TIMESTAMP | SI | - | Fecha creación |
| updated_at | TIMESTAMP | SI | - | Fecha actualización |

**Características**: Cada estación tiene una tarifa asociada (Gaming, Oficina, Premium, etc.), especificaciones técnicas para inventario, y datos de red.

---

#### `sesiones_cyber`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador |
| estacion_id | INT UNSIGNED | NO | FK | Estación usada |
| usuario_id | INT UNSIGNED | SI | FK | Operador |
| cliente_nombre | VARCHAR(100) | SI | - | Nombre del cliente |
| tarifa_id | SMALLINT UNSIGNED | NO | FK | Tarifa aplicada |
| hora_inicio | DATETIME | NO | - | Inicio sesión |
| hora_fin | DATETIME | SI | - | Fin sesión |
| costo_total | DECIMAL(10,2) | SI | - | Costo calculado |
| estado | ENUM | NO | - | activa/cerrada/interrumpida |
| created_at | TIMESTAMP | SI | - | Fecha creación |
| updated_at | TIMESTAMP | SI | - | Fecha actualización |

**Características**: `minutos_consumidos` eliminado (calculable vía TIMESTAMPDIFF). Tarifa referenciada para cálculo automático de costos mediante procedimiento `sp_cerrar_sesion_cyber`.

---

#### `movimientos_stock`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | BIGINT UNSIGNED | NO | PK | Identificador (BIGINT para alto volumen) |
| producto_id | INT UNSIGNED | NO | FK | Producto afectado |
| tipo | ENUM | NO | - | entrada/salida/ajuste |
| cantidad | INT | NO | - | Cantidad (+/-) |
| stock_anterior | INT | NO | - | Stock previo |
| stock_nuevo | INT | NO | - | Stock resultante |
| precio_unitario | DECIMAL(12,2) | SI | - | Precio al momento |
| costo_total | DECIMAL(12,2) | SI | - | Costo total del lote |
| fecha | DATETIME | NO | - | Fecha del movimiento |
| usuario_id | INT UNSIGNED | SI | FK | Responsable |
| referencia_tipo | VARCHAR(30) | SI | - | Entidad origen (venta, solicitud, ajuste_precio) |
| referencia_id | INT UNSIGNED | SI | - | ID de la entidad origen |
| motivo | VARCHAR(255) | SI | - | Razón del movimiento |
| created_at | TIMESTAMP | SI | - | Fecha creación |

**Características**: ID migrado a BIGINT (escala). Precio/costo por movimiento para valoración de inventario. Referencia polimórfica para trazabilidad completa (saber qué venta o solicitud originó cada movimiento). El procedimiento `sp_registrar_movimiento_stock` maneja las inserciones de forma transaccional con validación de stock negativo.

---

#### `asesorias`
| Columna | Tipo | Nulo | Clave | Descripción |
|---------|------|------|-------|-------------|
| id | INT UNSIGNED | NO | PK | Identificador |
| ciudadano | VARCHAR(150) | NO | - | Nombre del ciudadano |
| cedula | VARCHAR(20) | NO | - | Cédula de identidad |
| documento | VARCHAR(50) | SI | - | Documento asociado |
| descripcion | TEXT | NO | - | Descripción del caso |
| estado | ENUM | NO | - | Pendiente/En Proceso/Finalizada/Archivada |
| usuario_id | INT UNSIGNED | SI | FK | Usuario que registró |
| fecha_registro | DATETIME | NO | - | Fecha de registro |
| fecha_cierre | DATETIME | SI | - | Fecha de cierre |
| created_at | TIMESTAMP | SI | - | Fecha creación |
| updated_at | TIMESTAMP | SI | - | Fecha actualización |

**Características**: Tabla con FK a usuarios, estados expandidos, fecha de cierre, índices de búsqueda por cédula y estado.

---

## 5. Tablas Puente (Bridge Tables)

| Tabla Puente | Entidad A | Entidad B | Cardinalidad | Beneficio |
|-------------|-----------|-----------|--------------|-----------|
| `detalle_ventas` | ventas | productos | M:N | Productos en cada venta (con precio, IVA, descuento) |
| `detalle_solicitudes` | solicitudes | productos | M:N | Productos en cada solicitud de compra (con recepción parcial) |
| `producto_proveedor` | productos | proveedores | M:N | Múltiples proveedores por producto (con precios diferenciados) |

## 6. Vistas

| Vista | Descripción |
|-------|-------------|
| `v_productos_stock` | Vista de productos con estado de stock calculado (OK, Crítico, Sin stock) e información de categoría y marca |
| `v_ventas_diarias` | Agregación de ventas diarias: total ventas, monto total, descuentos, ticket promedio |
| `v_sesiones_activas` | Sesiones de cyber activas con minutos transcurridos y costo estimado en tiempo real |

## 7. Objetos de Base de Datos

| Objeto | Tipo | Descripción |
|--------|------|-------------|
| `fn_estado_stock` | FUNCTION | Calcula estado del stock (OK, Crítico, Sin stock) dados stock actual y mínimo |
| `sp_registrar_movimiento_stock` | PROCEDURE | Registro transaccional de movimiento de stock con bloqueo pesimista (FOR UPDATE), validación de stock negativo y rollback automático |
| `sp_cerrar_sesion_cyber` | PROCEDURE | Cierre de sesión cyber con cálculo de costo y liberación de estación |
| `trg_actualizar_totales_venta` | TRIGGER | AFTER INSERT en detalle_ventas: actualiza subtotal y total en ventas |
| `trg_auditar_precio_producto` | TRIGGER | BEFORE UPDATE en productos: registra movimiento de stock al cambiar precio |
| `ev_vencer_licencias` | EVENT | Ejecución diaria: cambia estado de licencias vencidas a 'Vencida' |

## 8. Índices y Rendimiento

### 8.1 Índices por Tabla
| Índice | Tabla | Columnas | Propósito |
|--------|-------|----------|-----------|
| idx_usuarios_rol | usuarios | rol_id | Filtro por rol |
| idx_productos_barras | productos | codigo_barras (UNIQUE) | Búsqueda por escáner |
| idx_productos_categoria | productos | categoria_id | Filtro por categoría |
| idx_productos_marca | productos | marca_id | Filtro por marca |
| idx_pp_proveedor | producto_proveedor | proveedor_id | Búsqueda inversa |
| idx_ventas_fecha | ventas | fecha | Reportes por fecha |
| idx_ventas_usuario | ventas | usuario_id | Ventas por usuario |
| idx_ventas_estado | ventas | estado | Filtro de ventas activas |
| idx_dv_venta | detalle_ventas | venta_id | Detalle de venta |
| idx_dv_producto | detalle_ventas | producto_id | Historial de producto |
| idx_solicitudes_proveedor | solicitudes | proveedor_id | Solicitudes por proveedor |
| idx_solicitudes_fecha | solicitudes | fecha | Reportes por fecha |
| idx_solicitudes_estado | solicitudes | estado | Filtro por estado |
| idx_ds_solicitud | detalle_solicitudes | solicitud_id | Detalle de solicitud |
| idx_ds_producto | detalle_solicitudes | producto_id | Historial de producto |
| idx_activos_tipo | activos | tipo_activo_id | Filtro por tipo |
| idx_activos_estado | activos | estado | Filtro por estado |
| idx_sc_estacion | sesiones_cyber | estacion_id | Sesiones por estación |
| idx_sc_activas | sesiones_cyber | estado | Sesiones activas |
| idx_sc_fecha | sesiones_cyber | hora_inicio | Reportes por fecha |
| idx_asesorias_estado | asesorias | estado | Filtro de asesorías |
| idx_asesorias_cedula | asesorias | cedula | Búsqueda por cédula |
| idx_asesorias_fecha | asesorias | fecha_registro | Reportes por fecha |
| idx_ms_producto | movimientos_stock | producto_id | Historial de producto |
| idx_ms_fecha | movimientos_stock | fecha | Reportes por fecha |
| idx_ms_referencia | movimientos_stock | referencia_tipo, referencia_id | Trazabilidad |

## 9. Archivos del Schema

| Archivo | Descripción |
|---------|-------------|
| `src/Database/estructura.sql` | Schema completo v2.0 (tablas, vistas, funciones, procedimientos, eventos, triggers) |
| `src/Database/datos_prueba.sql` | Datos de prueba para el schema v2.0 |
| `src/Config/database.php` | Configuración de conexión PDO |
| `src/app/Models/crud_users.php` | CRUD de usuarios con autenticación y roles |
| `src/app/Models/crud_asesorias.php` | CRUD de asesorías con FK a usuarios y búsqueda |

## 10. Integridad Referencial

| Tabla | FK | Referencia | Política DELETE |
|-------|----|------------|-----------------|
| usuarios | rol_id | roles(id) | RESTRICT |
| productos | categoria_id | categorias(id) | RESTRICT |
| productos | marca_id | marcas(id) | SET NULL |
| producto_proveedor | producto_id | productos(id) | CASCADE |
| producto_proveedor | proveedor_id | proveedores(id) | CASCADE |
| ventas | usuario_id | usuarios(id) | SET NULL |
| ventas | tipo_pago_id | tipos_pago(id) | SET NULL |
| detalle_ventas | venta_id | ventas(id) | CASCADE |
| detalle_ventas | producto_id | productos(id) | RESTRICT |
| solicitudes | proveedor_id | proveedores(id) | RESTRICT |
| solicitudes | tipo_pago_id | tipos_pago(id) | SET NULL |
| solicitudes | usuario_id | usuarios(id) | SET NULL |
| detalle_solicitudes | solicitud_id | solicitudes(id) | CASCADE |
| detalle_solicitudes | producto_id | productos(id) | RESTRICT |
| activos | tipo_activo_id | tipos_activo(id) | RESTRICT |
| activos | responsable_id | usuarios(id) | SET NULL |
| estaciones_cyber | tarifa_id | tarifas_cyber(id) | RESTRICT |
| sesiones_cyber | estacion_id | estaciones_cyber(id) | RESTRICT |
| sesiones_cyber | usuario_id | usuarios(id) | SET NULL |
| sesiones_cyber | tarifa_id | tarifas_cyber(id) | RESTRICT |
| movimientos_stock | producto_id | productos(id) | RESTRICT |
| movimientos_stock | usuario_id | usuarios(id) | SET NULL |
| asesorias | usuario_id | usuarios(id) | SET NULL |
