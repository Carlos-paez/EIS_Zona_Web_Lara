# Documentación del Diseño Conceptual de Base de Datos - Sistema ZWL v2.0

## 1. Introducción

El sistema ZWL es una aplicación de gestión integral diseñada para administrar múltiples aspectos de un negocio que incluye ventas, inventario, proveedores, activos fijos, control de cybercafé y asesorías legales. Este documento describe el diseño conceptual de la base de datos que soporta todas estas funcionalidades.

## 2. Diagrama Entidad-Relación (Conceptual)

```
roles ──< usuarios >───< ventas >───< detalle_ventas >──> productos >── categorias
         │                 │                                    │           │
         │                 └── tipos_pago                       │           └── marcas
         │                                                      │
         ├──< solicitudes >──< detalle_solicitudes >──>─────────┤
         │       │                                              │
         │       └──> proveedores ──< producto_proveedor >──────┘
         │
         ├──< sesiones_cyber >──> estaciones_cyber
         │       │                    │
         │       └──> tarifas_cyber ──┘
         │
         ├──< movimientos_stock >──> productos
         │
         ├──< asesorias
         │
         ├──< activos >──> tipos_activo
         │
         └─── roles (FK: rol_id)
```

## 3. Entidades y Atributos

### 3.1 roles
**Descripción**: Catálogo de roles para control de acceso al sistema.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | TINYINT UNSIGNED | PK, AUTO_INCREMENT | Identificador único del rol |
| nombre | VARCHAR(30) | NOT NULL, UNIQUE | Nombre del rol (Administrador, Vendedor, Cyber, Asesor, Consultor) |
| descripcion | VARCHAR(150) | NULL | Descripción del rol |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### 3.2 categorias
**Descripción**: Catálogo de categorías para clasificar productos.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | SMALLINT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| nombre | VARCHAR(50) | NOT NULL, UNIQUE | Nombre de la categoría |
| descripcion | VARCHAR(200) | NULL | Descripción |
| activa | BOOLEAN | DEFAULT TRUE | Está activa |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### 3.3 marcas
**Descripción**: Catálogo de marcas de productos.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | SMALLINT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| nombre | VARCHAR(100) | NOT NULL, UNIQUE | Nombre de la marca |
| descripcion | VARCHAR(200) | NULL | Descripción |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### 3.4 tipos_activo
**Descripción**: Catálogo de tipos de activos fijos.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | TINYINT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| nombre | VARCHAR(50) | NOT NULL, UNIQUE | Tipo (Equipos, Herramientas, Licencias, Mobiliario, Vehículos) |
| descripcion | VARCHAR(200) | NULL | Descripción |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### 3.5 tarifas_cyber
**Descripción**: Tarifas de precios para estaciones de cybercafé.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | SMALLINT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| nombre | VARCHAR(50) | NOT NULL, UNIQUE | Nombre de la tarifa (Gaming, Oficina, Premium) |
| precio_por_hora | DECIMAL(8,2) | NOT NULL | Precio por hora |
| precio_por_minuto | DECIMAL(6,2) | NULL | Precio fraccionado por minuto |
| tiempo_minimo | INT UNSIGNED | DEFAULT 30 | Minutos mínimos por sesión |
| activa | BOOLEAN | DEFAULT TRUE | Tarifa activa |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de actualización |

### 3.6 tipos_pago
**Descripción**: Catálogo de métodos de pago.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | TINYINT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| nombre | VARCHAR(30) | NOT NULL, UNIQUE | Efectivo, Transferencia, Punto, Mixto, Crédito |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### 3.7 usuarios
**Descripción**: Representa a los usuarios del sistema con autenticación segura y roles.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único del usuario |
| username | VARCHAR(30) | NOT NULL, UNIQUE | Nombre de usuario para login |
| password_hash | VARCHAR(255) | NOT NULL | Hash bcrypt de la contraseña |
| nombre | VARCHAR(100) | NOT NULL | Nombre completo del usuario |
| email | VARCHAR(100) | NOT NULL, UNIQUE | Correo electrónico único |
| telefono | VARCHAR(20) | NULL | Teléfono de contacto |
| activo | BOOLEAN | DEFAULT TRUE | Usuario activo en el sistema |
| rol_id | TINYINT UNSIGNED | NOT NULL, FK → roles(id) | Rol del usuario |
| ultimo_acceso | DATETIME | NULL | Fecha y hora del último login |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de última actualización |

### 3.8 productos
**Descripción**: Catálogo de productos disponibles para venta e inventario.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único del producto |
| codigo | VARCHAR(50) | NOT NULL, UNIQUE | SKU interno del producto |
| codigo_barras | VARCHAR(100) | NULL, UNIQUE | Código EAN/UPC para escáner |
| nombre | VARCHAR(150) | NOT NULL | Nombre descriptivo del producto |
| descripcion | TEXT | NULL | Descripción detallada |
| categoria_id | SMALLINT UNSIGNED | NOT NULL, FK → categorias(id) | Categoría del producto |
| marca_id | SMALLINT UNSIGNED | NULL, FK → marcas(id) | Marca del producto |
| unidad_medida | ENUM | DEFAULT 'Unidades' | Unidad de medida |
| stock | INT | NOT NULL, DEFAULT 0 | Cantidad disponible en inventario |
| stock_minimo | INT | NOT NULL, DEFAULT 5 | Cantidad mínima antes de alerta |
| ubicacion | VARCHAR(100) | NULL | Ubicación física en almacén |
| costo_compra | DECIMAL(12,2) | NOT NULL | Costo de adquisición |
| precio_venta | DECIMAL(12,2) | NOT NULL | Precio de venta |
| iva | DECIMAL(5,2) | DEFAULT 16.00 | Porcentaje de IVA |
| permite_descuento | BOOLEAN | DEFAULT TRUE | Permite aplicar descuentos |
| estado_venta | ENUM | DEFAULT 'Activo' | Estado para ventas (Activo/Inactivo) |
| activo | BOOLEAN | DEFAULT TRUE | Producto activo en el catálogo |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de última actualización |

### 3.9 proveedores
**Descripción**: Entidades que suministran productos al negocio.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único del proveedor |
| nombre | VARCHAR(150) | NOT NULL | Nombre de la empresa proveedora |
| rif | VARCHAR(20) | NULL, UNIQUE | Registro de Información Fiscal |
| tipo_documento | ENUM | DEFAULT 'J' | Tipo de RIF (J, V, E, G) |
| contacto | VARCHAR(100) | NULL | Persona de contacto |
| email | VARCHAR(100) | NULL | Correo electrónico de contacto |
| telefono | VARCHAR(20) | NULL | Teléfono de contacto |
| direccion | TEXT | NULL | Dirección fiscal |
| activo | BOOLEAN | DEFAULT TRUE | Proveedor activo |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de última actualización |

### 3.10 producto_proveedor
**Descripción**: Relación muchos a muchos entre productos y proveedores.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| producto_id | INT UNSIGNED | PK, FK → productos(id) ON DELETE CASCADE | Producto |
| proveedor_id | INT UNSIGNED | PK, FK → proveedores(id) ON DELETE CASCADE | Proveedor |
| codigo_proveedor | VARCHAR(50) | NULL | SKU del producto según el proveedor |
| precio_compra | DECIMAL(12,2) | NULL | Precio específico del proveedor |
| tiempo_entrega_dias | SMALLINT UNSIGNED | NULL | Tiempo estimado de entrega |
| es_proveedor_principal | BOOLEAN | DEFAULT FALSE | Proveedor por defecto |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### 3.11 ventas
**Descripción**: Registro de transacciones de ventas realizadas en el sistema.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único de la venta |
| fecha | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Fecha y hora de la venta |
| usuario_id | INT UNSIGNED | NULL, FK → usuarios(id) ON DELETE SET NULL | Usuario que realizó la venta |
| cliente_nombre | VARCHAR(150) | NULL | Nombre del cliente |
| cliente_cedula | VARCHAR(20) | NULL | Cédula de identidad del cliente |
| tipo_pago_id | TINYINT UNSIGNED | NULL, FK → tipos_pago(id) ON DELETE SET NULL | Método de pago |
| subtotal | DECIMAL(12,2) | NOT NULL | Suma de productos antes de impuestos |
| descuento | DECIMAL(12,2) | NOT NULL, DEFAULT 0.00 | Descuento global aplicado |
| iva_total | DECIMAL(12,2) | NOT NULL, DEFAULT 0.00 | Total de IVA |
| total | DECIMAL(12,2) | NOT NULL, DEFAULT 0.00 | Monto total de la venta |
| estado | ENUM | DEFAULT 'completada' | completada, pendiente, cancelada, reembolsada |
| notas | TEXT | NULL | Notas adicionales |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de última actualización |

### 3.12 detalle_ventas
**Descripción**: Detalle de productos incluidos en cada venta (relación muchos a muchos entre ventas y productos).

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único del detalle |
| venta_id | INT UNSIGNED | NOT NULL, FK → ventas(id) ON DELETE CASCADE | Venta a la que pertenece el detalle |
| producto_id | INT UNSIGNED | NOT NULL, FK → productos(id) ON DELETE RESTRICT | Producto vendido |
| cantidad | INT UNSIGNED | NOT NULL, DEFAULT 1 | Cantidad vendida |
| precio_unitario | DECIMAL(12,2) | NOT NULL | Precio unitario al momento de la venta |
| iva_unitario | DECIMAL(12,2) | NOT NULL, DEFAULT 0.00 | IVA por unidad |
| descuento | DECIMAL(12,2) | NOT NULL, DEFAULT 0.00 | Descuento por línea |
| subtotal | DECIMAL(12,2) | NOT NULL | (precio_unitario - descuento) * cantidad |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### 3.13 solicitudes
**Descripción**: Solicitudes de pedidos o reabastecimiento enviadas a proveedores.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único de la solicitud |
| codigo | VARCHAR(20) | NOT NULL, UNIQUE | Código único (ej. SOL-2026-0001) |
| proveedor_id | INT UNSIGNED | NOT NULL, FK → proveedores(id) ON DELETE RESTRICT | Proveedor destino |
| fecha | DATE | NOT NULL | Fecha de la solicitud |
| fecha_estimada_entrega | DATE | NULL | Fecha estimada de recepción |
| tipo_pago_id | TINYINT UNSIGNED | NULL, FK → tipos_pago(id) ON DELETE SET NULL | Forma de pago acordada |
| subtotal | DECIMAL(12,2) | NOT NULL | Subtotal de la solicitud |
| iva_total | DECIMAL(12,2) | NOT NULL | Total IVA |
| total | DECIMAL(12,2) | NOT NULL | Total de la solicitud |
| estado | ENUM | DEFAULT 'Pendiente' | Pendiente, Aprobada, Enviada, Recibida, Cancelada |
| usuario_id | INT UNSIGNED | NULL, FK → usuarios(id) ON DELETE SET NULL | Usuario que creó la solicitud |
| notas | TEXT | NULL | Notas adicionales |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de última actualización |

### 3.14 detalle_solicitudes
**Descripción**: Detalle de productos incluidos en cada solicitud de compra.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| solicitud_id | INT UNSIGNED | NOT NULL, FK → solicitudes(id) ON DELETE CASCADE | Solicitud relacionada |
| producto_id | INT UNSIGNED | NOT NULL, FK → productos(id) ON DELETE RESTRICT | Producto solicitado |
| cantidad_solicitada | INT UNSIGNED | NOT NULL, DEFAULT 1 | Cantidad pedida |
| cantidad_recibida | INT UNSIGNED | NULL | Cantidad recibida (para recepciones parciales) |
| precio_unitario_estimado | DECIMAL(12,2) | NOT NULL | Precio estimado por unidad |
| subtotal | DECIMAL(12,2) | NOT NULL | Subtotal de la línea |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### 3.15 activos
**Descripción**: Activos fijos de la empresa (equipos, herramientas, licencias, muebles).

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único del activo |
| nombre | VARCHAR(150) | NOT NULL | Nombre o descripción del activo |
| descripcion | TEXT | NULL | Descripción detallada |
| tipo_activo_id | TINYINT UNSIGNED | NOT NULL, FK → tipos_activo(id) ON DELETE RESTRICT | Tipo de activo normalizado |
| estado | ENUM | DEFAULT 'Activo' | Activo, Mantenimiento, Vencida, Baja |
| ubicacion | VARCHAR(100) | NULL | Ubicación física |
| valor_adquisicion | DECIMAL(12,2) | NULL | Valor de compra del activo |
| fecha_adquisicion | DATE | NULL | Fecha de compra o adquisición |
| fecha_vencimiento | DATE | NULL | Fecha de vencimiento (para licencias) |
| responsable_id | INT UNSIGNED | NULL, FK → usuarios(id) ON DELETE SET NULL | Custodio del activo |
| notas | TEXT | NULL | Notas adicionales |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de última actualización |

### 3.16 estaciones_cyber
**Descripción**: Computadoras o estaciones de trabajo en el cybercafé.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único de la estación |
| nombre | VARCHAR(50) | NOT NULL, UNIQUE | Identificador de la estación (ej. PC-01) |
| estado | ENUM | DEFAULT 'Disponible' | Disponible, Ocupada, Mantenimiento |
| tarifa_id | SMALLINT UNSIGNED | NOT NULL, FK → tarifas_cyber(id) ON DELETE RESTRICT | Tarifa asociada |
| especificaciones | VARCHAR(255) | NULL | Especificaciones técnicas (RAM, CPU, GPU) |
| ip_local | VARCHAR(15) | NULL | Dirección IP local |
| mac_address | VARCHAR(17) | NULL | Dirección MAC |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de última actualización |

### 3.17 sesiones_cyber
**Descripción**: Registro de sesiones de uso de estaciones en el cybercafé.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único de la sesión |
| estacion_id | INT UNSIGNED | NOT NULL, FK → estaciones_cyber(id) ON DELETE RESTRICT | Estación utilizada |
| usuario_id | INT UNSIGNED | NULL, FK → usuarios(id) ON DELETE SET NULL | Operador que registró la sesión |
| cliente_nombre | VARCHAR(100) | NULL | Nombre del cliente (walk-in) |
| tarifa_id | SMALLINT UNSIGNED | NOT NULL, FK → tarifas_cyber(id) ON DELETE RESTRICT | Tarifa aplicada |
| hora_inicio | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Hora de inicio de sesión |
| hora_fin | DATETIME | NULL | Hora de fin de sesión |
| costo_total | DECIMAL(10,2) | NULL | Costo total calculado de la sesión |
| estado | ENUM | DEFAULT 'activa' | activa, cerrada, interrumpida |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de última actualización |

### 3.18 movimientos_stock
**Descripción**: Historial de todos los movimientos de inventario (entradas, salidas, ajustes).

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Identificador único (BIGINT para alto volumen) |
| producto_id | INT UNSIGNED | NOT NULL, FK → productos(id) ON DELETE RESTRICT | Producto afectado |
| tipo | ENUM | NOT NULL | entrada, salida, ajuste |
| cantidad | INT | NOT NULL | Cantidad movida (positiva para entradas, negativa para salidas) |
| stock_anterior | INT | NOT NULL | Stock antes del movimiento |
| stock_nuevo | INT | NOT NULL | Stock después del movimiento |
| precio_unitario | DECIMAL(12,2) | NULL | Precio unitario al momento del movimiento |
| costo_total | DECIMAL(12,2) | NULL | Costo total del lote |
| fecha | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Fecha del movimiento |
| usuario_id | INT UNSIGNED | NULL, FK → usuarios(id) ON DELETE SET NULL | Usuario que realizó el movimiento |
| referencia_tipo | VARCHAR(30) | NULL | Entidad origen (venta, solicitud, ajuste_precio) |
| referencia_id | INT UNSIGNED | NULL | ID de la entidad origen |
| motivo | VARCHAR(255) | NULL | Razón del movimiento |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### 3.19 asesorias
**Descripción**: Registro de casos de asesoría legal o consultoría.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT UNSIGNED | PK, AUTO_INCREMENT | Identificador único |
| ciudadano | VARCHAR(150) | NOT NULL | Nombre del ciudadano o cliente |
| cedula | VARCHAR(20) | NOT NULL | Cédula de identidad |
| documento | VARCHAR(50) | NULL | Documento asociado al caso |
| descripcion | TEXT | NOT NULL | Descripción detallada del caso |
| estado | ENUM | DEFAULT 'Pendiente' | Pendiente, En Proceso, Finalizada, Archivada |
| usuario_id | INT UNSIGNED | NULL, FK → usuarios(id) ON DELETE SET NULL | Usuario que registró el caso |
| fecha_registro | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Fecha de registro del caso |
| fecha_cierre | DATETIME | NULL | Fecha de cierre del caso |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | ON UPDATE CURRENT_TIMESTAMP | Fecha de última actualización |

## 4. Relaciones y Cardinalidad

### 4.1 Relación roles - usuarios
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un rol puede tener múltiples usuarios asignados, pero un usuario tiene un solo rol.
- **Implementación**: `usuarios.rol_id` → `roles.id` (ON DELETE RESTRICT)

### 4.2 Relación categorias - productos
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Una categoría puede contener múltiples productos.
- **Implementación**: `productos.categoria_id` → `categorias.id` (ON DELETE RESTRICT)

### 4.3 Relación marcas - productos
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Una marca puede tener múltiples productos asociados.
- **Implementación**: `productos.marca_id` → `marcas.id` (ON DELETE SET NULL)

### 4.4 Relación usuarios - ventas
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un usuario puede realizar múltiples ventas, pero una venta es realizada por un solo usuario.
- **Implementación**: `ventas.usuario_id` → `usuarios.id` (ON DELETE SET NULL)

### 4.5 Relación tipos_pago - ventas
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un tipo de pago puede estar en múltiples ventas.
- **Implementación**: `ventas.tipo_pago_id` → `tipos_pago.id` (ON DELETE SET NULL)

### 4.6 Relación ventas - detalle_ventas
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Una venta puede tener múltiples productos en su detalle.
- **Implementación**: `detalle_ventas.venta_id` → `ventas.id` (ON DELETE CASCADE)

### 4.7 Relación productos - detalle_ventas
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un producto puede aparecer en múltiples detalles de ventas.
- **Implementación**: `detalle_ventas.producto_id` → `productos.id` (ON DELETE RESTRICT)

### 4.8 Relación proveedores - solicitudes
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un proveedor puede recibir múltiples solicitudes.
- **Implementación**: `solicitudes.proveedor_id` → `proveedores.id` (ON DELETE RESTRICT)

### 4.9 Relación tipos_pago - solicitudes
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un tipo de pago puede estar en múltiples solicitudes.
- **Implementación**: `solicitudes.tipo_pago_id` → `tipos_pago.id` (ON DELETE SET NULL)

### 4.10 Relación usuarios - solicitudes
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un usuario puede crear múltiples solicitudes.
- **Implementación**: `solicitudes.usuario_id` → `usuarios.id` (ON DELETE SET NULL)

### 4.11 Relación solicitudes - detalle_solicitudes
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Una solicitud puede contener múltiples productos.
- **Implementación**: `detalle_solicitudes.solicitud_id` → `solicitudes.id` (ON DELETE CASCADE)

### 4.12 Relación productos - detalle_solicitudes
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un producto puede aparecer en múltiples solicitudes.
- **Implementación**: `detalle_solicitudes.producto_id` → `productos.id` (ON DELETE RESTRICT)

### 4.13 Relación productos - producto_proveedor
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un producto puede ser suministrado por múltiples proveedores.
- **Implementación**: `producto_proveedor.producto_id` → `productos.id` (ON DELETE CASCADE)

### 4.14 Relación proveedores - producto_proveedor
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un proveedor puede suministrar múltiples productos.
- **Implementación**: `producto_proveedor.proveedor_id` → `proveedores.id` (ON DELETE CASCADE)

### 4.15 Relación tipos_activo - activos
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un tipo de activo puede tener múltiples activos asociados.
- **Implementación**: `activos.tipo_activo_id` → `tipos_activo.id` (ON DELETE RESTRICT)

### 4.16 Relación usuarios - activos (responsable)
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un usuario puede ser responsable de múltiples activos.
- **Implementación**: `activos.responsable_id` → `usuarios.id` (ON DELETE SET NULL)

### 4.17 Relación tarifas_cyber - estaciones_cyber
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Una tarifa puede estar asociada a múltiples estaciones.
- **Implementación**: `estaciones_cyber.tarifa_id` → `tarifas_cyber.id` (ON DELETE RESTRICT)

### 4.18 Relación estaciones_cyber - sesiones_cyber
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Una estación puede tener múltiples sesiones históricas.
- **Implementación**: `sesiones_cyber.estacion_id` → `estaciones_cyber.id` (ON DELETE RESTRICT)

### 4.19 Relación tarifas_cyber - sesiones_cyber
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Una tarifa puede estar en múltiples sesiones.
- **Implementación**: `sesiones_cyber.tarifa_id` → `tarifas_cyber.id` (ON DELETE RESTRICT)

### 4.20 Relación usuarios - sesiones_cyber
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un usuario puede registrar múltiples sesiones de cyber.
- **Implementación**: `sesiones_cyber.usuario_id` → `usuarios.id` (ON DELETE SET NULL)

### 4.21 Relación productos - movimientos_stock
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un producto puede tener múltiples movimientos de stock.
- **Implementación**: `movimientos_stock.producto_id` → `productos.id` (ON DELETE RESTRICT)

### 4.22 Relación usuarios - movimientos_stock
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un usuario puede realizar múltiples movimientos de inventario.
- **Implementación**: `movimientos_stock.usuario_id` → `usuarios.id` (ON DELETE SET NULL)

### 4.23 Relación usuarios - asesorias
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un usuario puede registrar múltiples casos de asesoría.
- **Implementación**: `asesorias.usuario_id` → `usuarios.id` (ON DELETE SET NULL)

## 5. Reglas de Negocio

### 5.1 Gestión de Inventario
1. El stock de un producto no puede ser negativo (validado en el procedimiento `sp_registrar_movimiento_stock`).
2. El estado del stock (OK, Crítico, Sin stock) se calcula dinámicamente mediante la función `fn_estado_stock()` y la vista `v_productos_stock`.
3. Todo movimiento de stock debe registrarse en la tabla `movimientos_stock` para auditoría (entradas, salidas, ajustes).
4. Los movimientos de stock se realizan de forma transaccional mediante el procedimiento `sp_registrar_movimiento_stock`, que incluye bloqueo pesimista (FOR UPDATE) y rollback automático en caso de error.

### 5.2 Ventas
1. Al registrar una venta se debe:
   - Crear el registro en `ventas`
   - Insertar los detalles en `detalle_ventas`
   - El trigger `trg_actualizar_totales_venta` actualiza automáticamente subtotal y total de la venta.
2. Una venta no puede existir sin al menos un detalle (integridad referencial vía FK).
3. El IVA y descuento se registran por línea de detalle para facturación detallada.
4. Los cambios de precio en productos se auditan automáticamente mediante el trigger `trg_auditar_precio_producto`.

### 5.3 Cybercafé
1. Una estación solo puede tener una sesión activa a la vez.
2. Al finalizar una sesión, se debe calcular la duración y el costo mediante el procedimiento `sp_cerrar_sesion_cyber`.
3. El estado de la estación se actualiza automáticamente al cerrar la sesión.
4. Las tarifas son parametrizables por estación (Gaming, Oficina, Premium, etc.).

### 5.4 Solicitudes a Proveedores
1. El código de solicitud debe ser único (ej. `SOL-2026-0001`).
2. Una solicitud puede tener múltiples estados: Pendiente, Aprobada, Enviada, Recibida, Cancelada.
3. Al recibir una solicitud, se puede actualizar `cantidad_recibida` para recepciones parciales.
4. Un producto puede tener múltiples proveedores con diferentes precios y tiempos de entrega (tabla `producto_proveedor`).

### 5.5 Activos
1. Las licencias son los únicos activos con fecha de vencimiento.
2. Los activos vencidos cambian automáticamente su estado a 'Vencida' mediante el evento programado `ev_vencer_licencias` (se ejecuta diariamente).
3. Cada activo tiene un responsable asignado (usuario del sistema).

### 5.6 Asesorías
1. Cada caso de asesoría tiene un ciudadano asociado con número de cédula.
2. Los estados permiten seguimiento: Pendiente, En Proceso, Finalizada, Archivada.
3. Se registra fecha de inicio y fecha de cierre para cada caso.

### 5.7 Autenticación y Seguridad
1. Las contraseñas se almacenan usando hash bcrypt (password_hash VARCHAR(255)).
2. Cada usuario tiene un rol asignado que define sus permisos en el sistema.
3. Se registra el último acceso de cada usuario (`ultimo_acceso`).

## 6. Índices y Optimización

Se han creado índices en las columnas más utilizadas para consultas:

| Índice | Tabla | Columnas | Propósito |
|--------|-------|----------|-----------|
| idx_usuarios_rol | usuarios | rol_id | Filtro por rol |
| idx_productos_barras | productos | codigo_barras (UNIQUE) | Búsqueda por escáner |
| idx_productos_categoria | productos | categoria_id | Filtro por categoría |
| idx_productos_marca | productos | marca_id | Filtro por marca |
| idx_pp_proveedor | producto_proveedor | proveedor_id | Búsqueda inversa proveedor |
| idx_ventas_fecha | ventas | fecha | Consultas por rango de fechas |
| idx_ventas_usuario | ventas | usuario_id | Consultas de ventas por usuario |
| idx_ventas_estado | ventas | estado | Filtro de ventas activas |
| idx_dv_venta | detalle_ventas | venta_id | Acceso rápido a detalles de una venta |
| idx_dv_producto | detalle_ventas | producto_id | Historial de ventas por producto |
| idx_solicitudes_proveedor | solicitudes | proveedor_id | Consultas por proveedor |
| idx_solicitudes_fecha | solicitudes | fecha | Consultas por rango de fechas |
| idx_solicitudes_estado | solicitudes | estado | Filtro por estado |
| idx_ds_solicitud | detalle_solicitudes | solicitud_id | Detalle de una solicitud |
| idx_ds_producto | detalle_solicitudes | producto_id | Historial de producto en solicitudes |
| idx_activos_tipo | activos | tipo_activo_id | Filtro por tipo de activo |
| idx_activos_estado | activos | estado | Filtro por estado |
| idx_sc_estacion | sesiones_cyber | estacion_id | Consultas de sesiones por estación |
| idx_sc_activas | sesiones_cyber | estado | Sesiones activas |
| idx_sc_fecha | sesiones_cyber | hora_inicio | Reportes por fecha |
| idx_asesorias_estado | asesorias | estado | Filtro de asesorías |
| idx_asesorias_cedula | asesorias | cedula | Búsqueda por cédula |
| idx_asesorias_fecha | asesorias | fecha_registro | Reportes por fecha de registro |
| idx_ms_producto | movimientos_stock | producto_id | Historial de movimientos por producto |
| idx_ms_fecha | movimientos_stock | fecha | Consultas por rango de fechas |
| idx_ms_referencia | movimientos_stock | referencia_tipo, referencia_id | Trazabilidad de origen |

## 7. Integridad Referencial

Las claves foráneas están configuradas con las siguientes políticas:

- **ON DELETE SET NULL**: Para relaciones donde se quiere mantener el historial (ventas, solicitudes, sesiones, movimientos, asesorías). Si se elimina el usuario, las referencias se ponen a NULL.
- **ON DELETE CASCADE**: Para tablas puente y detalles (detalle_ventas, detalle_solicitudes, producto_proveedor). Si se elimina el padre, se eliminan los hijos automáticamente.
- **ON DELETE RESTRICT**: Para integridad crítica (productos en ventas, movimientos). No se puede eliminar un producto que tenga movimientos o ventas asociadas.

## 8. Consideraciones de Diseño

1. **Normalización**: La base de datos está en Tercera Forma Normal (3NF). Catálogos como categorías, marcas, tipos de activo, roles, tarifas y tipos de pago están normalizados en tablas separadas.
2. **Escalabilidad**: El diseño permite agregar nuevos campos o entidades sin afectar la estructura existente. IDs UNSIGNED para mayor capacidad. Movimientos_stock usa BIGINT para alto volumen.
3. **Auditoría**: Todas las tablas tienen campos `created_at` y `updated_at` para seguimiento temporal. Movimientos_stock tiene trazabilidad polimórfica (referencia_tipo + referencia_id).
4. **Transaccionalidad**: Los procedimientos almacenados usan transacciones con bloqueo pesimista (FOR UPDATE) y rollback automático en errores.
5. **Vistas computadas**: El estado del stock se calcula dinámicamente mediante vistas en lugar de almacenar columnas redundantes.
6. **Eventos programados**: La expiración de licencias se maneja automáticamente mediante un evento diario.

## 9. Diagrama de Esquema Lógico (Notación Crow's Foot)

```
[roles] 1 ──────< [usuarios] >───1
                                 │
                                 ├───< [ventas] >─── [tipos_pago]
                                 │      1
                                 │      │
                                 │      └───< [detalle_ventas] >─── [productos]
                                 │
                                 ├───< [solicitudes] >─── [proveedores]
                                 │      1                   │
                                 │      │                   │
                                 │      └───< [detalle_solicitudes] >───┐
                                 │                                      │
                                 │                              [producto_proveedor]
                                 │                                      │
                                 ├───< [movimientos_stock] >─── [productos]
                                 │
                                 ├───< [sesiones_cyber] >─── [estaciones_cyber]
                                 │      1                       │
                                 │      │                       │
                                 │      └─── [tarifas_cyber] ────┘
                                 │
                                 ├───< [asesorias]
                                 │
                                 └───< [activos] >─── [tipos_activo]

[categorias] 1 ──< [productos] >── 0..1 [marcas]
```

## 10. Funciones, Procedimientos, Triggers y Eventos

| Objeto | Tipo | Descripción |
|--------|------|-------------|
| fn_estado_stock | FUNCTION | Determina el estado del stock (OK, Crítico, Sin stock) basado en stock actual y stock mínimo |
| sp_registrar_movimiento_stock | PROCEDURE | Registra un movimiento de stock de forma transaccional con bloqueo pesimista y rollback |
| sp_cerrar_sesion_cyber | PROCEDURE | Cierra una sesión de cybercafé, calcula costo total y libera la estación |
| trg_actualizar_totales_venta | TRIGGER (AFTER INSERT) | Actualiza subtotal y total en la tabla ventas al insertar un detalle |
| trg_auditar_precio_producto | TRIGGER (BEFORE UPDATE) | Registra un movimiento de stock cuando cambia el precio de un producto |
| ev_vencer_licencias | EVENT | Evento diario que cambia automáticamente el estado de licencias vencidas |

## 11. Conclusiones

El diseño conceptual de la base de datos ZWL v2.0 proporciona una estructura sólida, normalizada y flexible para soportar todas las funcionalidades del sistema. Las mejoras respecto a la versión anterior incluyen catálogos normalizados, autenticación segura con bcrypt y roles, tablas puente para relaciones M:N, procedimientos transaccionales con integridad garantizada, triggers para automatización de reglas de negocio, y eventos programados para tareas de mantenimiento. Los índices estratégicos y las vistas computadas aseguran rendimiento óptimo sin redundancia de datos.
