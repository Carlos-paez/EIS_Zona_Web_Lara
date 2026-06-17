# Diccionario de Datos — Sistema ZWL (Zona Web Lara)

**Base de datos:** `zwl`  
**Motor:** MySQL 8.0+ / MariaDB 10.3+ (InnoDB)  
**Charset:** `utf8mb4` — `utf8mb4_unicode_ci`  
**Total de tablas:** 21 | **Vistas:** 3 | **Procedimientos:** 2 | **Triggers:** 2  
**Última actualización:** Junio 2026

---

## Tablas de Catálogo / Soporte (Lookup Tables)

---

### `roles`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del rol | Serial | | NOT NULL |
| nombre | Nombre del rol (Administrador, Operador, Asesor Legal) | Caracter variable | 30 | NOT NULL |
| descripcion | Descripción opcional del rol | Caracter variable | 150 | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** No tiene

---

### `subcategorias`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único de la subcategoría | Serial | | NOT NULL |
| nombre | Nombre de la subcategoría (Componentes de PC, Periféricos, Consumibles) | Caracter variable | 50 | NOT NULL |
| descripcion | Descripción opcional | Caracter variable | 200 | NULL |
| activa | Indica si la subcategoría está activa (borrado lógico) | Booleano | | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** No tiene

---

### `categorias`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único de la categoría | Serial | | NOT NULL |
| subcategoria_id | Identificador de la subcategoría a la que pertenece | Entero | | NULL |
| nombre | Nombre de la categoría | Caracter variable | 50 | NOT NULL |
| descripcion | Descripción opcional | Caracter variable | 200 | NULL |
| activa | Indica si la categoría está activa (borrado lógico) | Booleano | | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla subcategorias mediante `subcategoria_id`

---

### `marcas`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único de la marca | Serial | | NOT NULL |
| nombre | Nombre de la marca (Logitech, HP, Samsung) | Caracter variable | 100 | NOT NULL |
| descripcion | Descripción opcional de la marca | Caracter variable | 200 | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** No tiene

---

### `modelos`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del modelo | Serial | | NOT NULL |
| marca_id | Identificador de la marca a la que pertenece | Entero | | NOT NULL |
| nombre | Nombre del modelo (G203, Pro X, Pavilion) | Caracter variable | 100 | NOT NULL |
| descripcion | Descripción opcional del modelo | Caracter variable | 200 | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla marcas mediante `marca_id`

---

### `tipos_activo`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del tipo de activo | Serial | | NOT NULL |
| nombre | Nombre del tipo (Equipo, Licencia, Herramienta, Infraestructura) | Caracter variable | 50 | NOT NULL |
| descripcion | Descripción opcional del tipo de activo | Caracter variable | 200 | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** No tiene

---

### `tarifas_cyber`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único de la tarifa | Serial | | NOT NULL |
| nombre | Nombre de la tarifa (Gaming, Oficina, Premium) | Caracter variable | 50 | NOT NULL |
| precio_por_hora | Precio por hora de uso de la estación | Decimal | 8,2 | NOT NULL |
| tiempo_minimo | Minutos mínimos de cobro por sesión | Entero | | NULL |
| activa | Indica si la tarifa está activa | Booleano | | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** No tiene

---

## Tablas Maestras

---

### `usuarios`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del usuario | Serial | | NOT NULL |
| username | Nombre de usuario para inicio de sesión | Caracter variable | 30 | NOT NULL |
| password_hash | Hash de la contraseña (bcrypt) | Caracter variable | 255 | NOT NULL |
| nombre | Nombre completo del usuario | Caracter variable | 100 | NOT NULL |
| email | Correo electrónico del usuario | Caracter variable | 100 | NOT NULL |
| telefono | Número de teléfono de contacto | Caracter variable | 20 | NULL |
| activo | Indica si el usuario está activo (borrado lógico) | Booleano | | NULL |
| rol_id | Identificador del rol asignado al usuario | Entero | | NOT NULL |
| ultimo_acceso | Fecha y hora del último inicio de sesión | Fecha/Hora | | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla roles mediante `rol_id`

---

### `clientes`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del cliente | Serial | | NOT NULL |
| cedula_rif | Cédula o RIF del cliente (V-12345678, J-12345678-0) | Caracter variable | 20 | NOT NULL |
| nombre | Nombre o razón social del cliente | Caracter variable | 150 | NOT NULL |
| telefono | Número de teléfono de contacto | Caracter variable | 20 | NULL |
| email | Correo electrónico del cliente | Caracter variable | 100 | NULL |
| direccion | Dirección física del cliente | Caracter variable | Ilimitado (TEXT) | NULL |
| activo | Indica si el cliente está activo (borrado lógico) | Booleano | | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** No tiene

---

### `clientes_asesorias`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del cliente de asesoría legal | Serial | | NOT NULL |
| cedula | Cédula de identidad del cliente legal | Caracter variable | 20 | NOT NULL |
| nombre | Nombre completo del cliente legal | Caracter variable | 150 | NOT NULL |
| email | Correo electrónico del cliente legal | Caracter variable | 100 | NULL |
| telefono | Número de teléfono de contacto | Caracter variable | 20 | NULL |
| direccion | Dirección física del cliente legal | Caracter variable | Ilimitado (TEXT) | NULL |
| notas_expediente | Notas e información legal del expediente del cliente | Caracter variable | Ilimitado (TEXT) | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** No tiene

---

### `proveedores`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del proveedor | Serial | | NOT NULL |
| nombre | Nombre o razón social del proveedor | Caracter variable | 150 | NOT NULL |
| rif | RIF del proveedor | Caracter variable | 20 | NULL |
| tipo_documento | Tipo de documento: J (Jurídico), V (Venezolano), E (Extranjero), G (Gubernamental) | Caracter variable (ENUM) | 1 | NULL |
| contacto | Nombre de la persona de contacto | Caracter variable | 100 | NULL |
| email | Correo electrónico del proveedor | Caracter variable | 100 | NULL |
| telefono | Número de teléfono del proveedor | Caracter variable | 20 | NULL |
| direccion | Dirección física del proveedor | Caracter variable | Ilimitado (TEXT) | NULL |
| es_proveedor_principal | Indica si es proveedor principal (marcado como preferido) | Booleano | | NULL |
| activo | Indica si el proveedor está activo (borrado lógico) | Booleano | | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** No tiene

---

### `productos`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del producto | Serial | | NOT NULL |
| codigo | SKU o código interno del producto | Caracter variable | 50 | NOT NULL |
| codigo_barras | Código de barras del producto para lectura por escáner | Caracter variable | 100 | NULL |
| nombre | Nombre del producto | Caracter variable | 150 | NOT NULL |
| descripcion | Descripción detallada del producto | Caracter variable | Ilimitado (TEXT) | NULL |
| categoria_id | Identificador de la categoría del producto | Entero | | NOT NULL |
| modelo_id | Identificador del modelo del producto | Entero | | NULL |
| unidad_medida | Unidad de medida: Unidades, Kg, Litros, Metros, Packs | Caracter variable (ENUM) | 9 | NULL |
| stock | Cantidad actual en inventario | Entero | | NOT NULL |
| stock_minimo | Cantidad mínima para alerta de reposición | Entero | | NOT NULL |
| ubicacion | Ubicación física en el almacén | Caracter variable | 100 | NULL |
| costo_compra | Precio de compra o costo de adquisición | Decimal | 12,2 | NOT NULL |
| precio_venta | Precio de venta al público | Decimal | 12,2 | NOT NULL |
| permite_descuento | Indica si el producto puede tener descuento | Booleano | | NULL |
| estado_venta | Estado de venta: Activo o Inactivo | Caracter variable (ENUM) | 8 | NULL |
| activo | Indica si el producto está activo (borrado lógico) | Booleano | | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla categorias mediante `categoria_id`; Relación con la tabla modelos mediante `modelo_id`

---

### `activos`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del activo fijo | Serial | | NOT NULL |
| nombre | Nombre del activo fijo | Caracter variable | 150 | NOT NULL |
| descripcion | Descripción detallada del activo | Caracter variable | Ilimitado (TEXT) | NULL |
| tipo_activo_id | Identificador del tipo de activo | Entero | | NOT NULL |
| estado | Estado del activo: Activo, Mantenimiento, Vencida, Baja | Caracter variable (ENUM) | 12 | NULL |
| ubicacion | Ubicación física del activo | Caracter variable | 100 | NULL |
| valor_adquisicion | Valor de compra o adquisición del activo | Decimal | 12,2 | NULL |
| fecha_adquisicion | Fecha de compra o adquisición | Fecha | | NULL |
| fecha_vencimiento | Fecha de vencimiento (licencias, garantías) | Fecha | | NULL |
| responsable_id | Identificador del usuario responsable del activo | Entero | | NULL |
| notas | Notas u observaciones adicionales | Caracter variable | Ilimitado (TEXT) | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla tipos_activo mediante `tipo_activo_id`; Relación con la tabla usuarios mediante `responsable_id`

---

### `estaciones_cyber`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único de la estación | Serial | | NOT NULL |
| nombre | Nombre o identificador de la estación (PC-01, Zona Gamers #3) | Caracter variable | 50 | NOT NULL |
| estado | Estado actual: Disponible, Ocupada, Mantenimiento | Caracter variable (ENUM) | 12 | NULL |
| tarifa_id | Identificador de la tarifa asignada a la estación | Entero | | NOT NULL |
| especificaciones | Especificaciones técnicas (RAM, CPU, GPU) | Caracter variable | 255 | NULL |
| ip_local | Dirección IP local de la estación | Caracter variable | 15 | NULL |
| mac_address | Dirección MAC de la estación (AA:BB:CC:DD:EE:FF) | Caracter variable | 17 | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla tarifas_cyber mediante `tarifa_id`

---

## Tablas Transaccionales

---

### `ventas`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único de la venta | Serial | | NOT NULL |
| fecha | Fecha y hora de la venta | Fecha/Hora | | NOT NULL |
| usuario_id | Identificador del usuario que realizó la venta | Entero | | NULL |
| cliente_id | Identificador del cliente que realizó la compra | Entero | | NULL |
| subtotal | Subtotal de la venta (suma de detalles) | Decimal | 12,2 | NOT NULL |
| descuento | Descuento global aplicado a la venta | Decimal | 12,2 | NOT NULL |
| total | Total de la venta (subtotal - descuento) | Decimal | 12,2 | NOT NULL |
| estado | Estado: completada, pendiente, cancelada, reembolsada | Caracter variable (ENUM) | 11 | NULL |
| notas | Notas u observaciones de la venta | Caracter variable | Ilimitado (TEXT) | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla usuarios mediante `usuario_id`; Relación con la tabla clientes mediante `cliente_id`

---

### `detalle_ventas`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del detalle de venta | Serial | | NOT NULL |
| venta_id | Identificador de la venta a la que pertenece | Entero | | NOT NULL |
| producto_id | Identificador del producto vendido | Entero | | NOT NULL |
| cantidad | Cantidad vendida del producto | Entero | | NOT NULL |
| precio_unitario | Precio unitario al momento de la venta (precio congelado) | Decimal | 12,2 | NOT NULL |
| descuento | Descuento aplicado a este producto | Decimal | 12,2 | NOT NULL |
| subtotal | Subtotal del detalle (cantidad × precio_unitario - descuento) | Decimal | 12,2 | NOT NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla ventas mediante `venta_id`; Relación con la tabla productos mediante `producto_id`

---

### `solicitudes`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único de la solicitud | Serial | | NOT NULL |
| codigo | Código visible de la solicitud (SOL-2024-001) | Caracter variable | 20 | NOT NULL |
| proveedor_id | Identificador del proveedor solicitado | Entero | | NOT NULL |
| fecha | Fecha de la solicitud | Fecha | | NOT NULL |
| fecha_estimada_entrega | Fecha estimada de entrega del pedido | Fecha | | NULL |
| tiempo_entrega_dias | Tiempo estimado de entrega en días | Entero | | NULL |
| subtotal | Subtotal de la solicitud | Decimal | 12,2 | NOT NULL |
| total | Total de la solicitud | Decimal | 12,2 | NOT NULL |
| estado | Estado: Pendiente, Aprobada, Enviada, Recibida, Cancelada | Caracter variable (ENUM) | 10 | NULL |
| usuario_id | Identificador del usuario que creó la solicitud | Entero | | NULL |
| notas | Notas u observaciones de la solicitud | Caracter variable | Ilimitado (TEXT) | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla proveedores mediante `proveedor_id`; Relación con la tabla usuarios mediante `usuario_id`

---

### `detalle_solicitudes`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del detalle de solicitud | Serial | | NOT NULL |
| solicitud_id | Identificador de la solicitud a la que pertenece | Entero | | NOT NULL |
| producto_id | Identificador del producto solicitado | Entero | | NOT NULL |
| cantidad_solicitada | Cantidad solicitada del producto | Entero | | NOT NULL |
| cantidad_recibida | Cantidad realmente recibida (soporta recepción parcial) | Entero | | NULL |
| precio_unitario_estimado | Precio unitario estimado al momento de la solicitud | Decimal | 12,2 | NOT NULL |
| subtotal | Subtotal del detalle | Decimal | 12,2 | NOT NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla solicitudes mediante `solicitud_id`; Relación con la tabla productos mediante `producto_id`

---

### `sesiones_cyber`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único de la sesión cyber | Serial | | NOT NULL |
| estacion_id | Identificador de la estación utilizada | Entero | | NOT NULL |
| usuario_id | Identificador del usuario que registró la sesión | Entero | | NULL |
| cliente_id | Identificador del cliente que usa la estación | Entero | | NULL |
| hora_inicio | Fecha y hora de inicio de la sesión | Fecha/Hora | | NOT NULL |
| hora_fin | Fecha y hora de fin de la sesión (calculado por SP) | Fecha/Hora | | NULL |
| costo_total | Costo total calculado según tarifa y tiempo mínimo | Decimal | 10,2 | NULL |
| estado | Estado: activa, cerrada, interrumpida | Caracter variable (ENUM) | 11 | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla estaciones_cyber mediante `estacion_id`; Relación con la tabla usuarios mediante `usuario_id`; Relación con la tabla clientes mediante `cliente_id`

---

### `asesorias`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único de la asesoría legal | Serial | | NOT NULL |
| cliente_asesoria_id | Identificador del cliente legal asociado | Entero | | NOT NULL |
| documento | Número de expediente o visado del caso | Caracter variable | 50 | NULL |
| descripcion | Descripción detallada del caso legal | Caracter variable | Ilimitado (TEXT) | NOT NULL |
| estado | Estado: Pendiente, En Proceso, Finalizada, Archivada | Caracter variable (ENUM) | 11 | NULL |
| fecha_registro | Fecha y hora de registro del caso | Fecha/Hora | | NOT NULL |
| fecha_cierre | Fecha y hora de cierre del caso | Fecha/Hora | | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |
| updated_at | Fecha y hora de la última modificación | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla clientes_asesorias mediante `cliente_asesoria_id`

---

### `bitacora_movimientos_stock`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| id | Identificador único del movimiento (BIGINT para alta capacidad) | Serial (BIGINT) | | NOT NULL |
| producto_id | Identificador del producto afectado | Entero | | NOT NULL |
| tipo | Tipo de movimiento: entrada, salida, ajuste | Caracter variable (ENUM) | 7 | NOT NULL |
| cantidad | Cantidad del movimiento (negativo para salidas) | Entero | | NOT NULL |
| stock_anterior | Stock del producto antes del movimiento | Entero | | NOT NULL |
| stock_nuevo | Stock del producto después del movimiento | Entero | | NOT NULL |
| precio_unitario | Precio unitario al momento del movimiento | Decimal | 12,2 | NULL |
| costo_total | Costo total del movimiento | Decimal | 12,2 | NULL |
| fecha | Fecha y hora del movimiento | Fecha/Hora | | NOT NULL |
| usuario_id | Identificador del usuario que realizó el movimiento | Entero | | NULL |
| referencia_tipo | Tipo de referencia origen (venta, solicitud, ajuste_precio) | Caracter variable | 30 | NULL |
| referencia_id | Identificador de la referencia origen | Entero | | NULL |
| motivo | Motivo o razón del movimiento | Caracter variable | 255 | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** id  
**CLAVE FORÁNEA:** Relación con la tabla productos mediante `producto_id`; Relación con la tabla usuarios mediante `usuario_id`

---

## Tablas Puente (Relaciones M:N)

---

### `producto_proveedor`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| producto_id | Identificador del producto (parte de PK compuesta) | Entero | | NOT NULL |
| proveedor_id | Identificador del proveedor (parte de PK compuesta) | Entero | | NOT NULL |
| codigo_proveedor | SKU o código que usa el proveedor para este producto | Caracter variable | 50 | NULL |
| precio_compra | Precio de compra negociado con este proveedor | Decimal | 12,2 | NULL |
| tiempo_entrega_dias | Tiempo de entrega estimado para este producto-proveedor | Entero | | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** (producto_id, proveedor_id) — Compuesta  
**CLAVE FORÁNEA:** Relación con la tabla productos mediante `producto_id`; Relación con la tabla proveedores mediante `proveedor_id`

---

### `usuario_asesoria`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | LONGITUD | REQUERIDO |
|-------|-------------|---------------|----------|-----------|
| usuario_id | Identificador del usuario asignado (parte de PK compuesta) | Entero | | NOT NULL |
| asesoria_id | Identificador de la asesoría asignada (parte de PK compuesta) | Entero | | NOT NULL |
| rol_en_asesoria | Rol del usuario en esta asesoría (Asesor Principal, Consultor, Gestor, Auditor) | Caracter variable | 50 | NULL |
| created_at | Fecha y hora de creación del registro | TimeStamp | | NULL |

**CLAVE PRIMARIA:** (usuario_id, asesoria_id) — Compuesta  
**CLAVE FORÁNEA:** Relación con la tabla usuarios mediante `usuario_id`; Relación con la tabla asesorias mediante `asesoria_id`

---

## Vistas del Sistema

---

### `v_productos_stock`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | ORIGEN |
|-------|-------------|---------------|--------|
| id | Identificador único del producto | Entero | productos.id |
| codigo | SKU o código interno del producto | Caracter variable | productos.codigo |
| codigo_barras | Código de barras del producto | Caracter variable | productos.codigo_barras |
| nombre | Nombre del producto | Caracter variable | productos.nombre |
| categoria | Nombre de la categoría del producto | Caracter variable | categorias.nombre |
| subcategoria | Nombre de la subcategoría del producto | Caracter variable | subcategorias.nombre |
| marca | Nombre de la marca del producto | Caracter variable | marcas.nombre |
| modelo | Nombre del modelo del producto | Caracter variable | modelos.nombre |
| stock | Cantidad actual en inventario | Entero | productos.stock |
| stock_minimo | Cantidad mínima para alerta de reposición | Entero | productos.stock_minimo |
| ubicacion | Ubicación física en el almacén | Caracter variable | productos.ubicacion |
| precio_venta | Precio de venta al público | Decimal | productos.precio_venta |
| estado_stock | Estado del stock: Sin stock, Crítico, OK | Caracter variable | Calculado (CASE) |
| estado_venta | Estado de venta: Activo o Inactivo | Caracter variable | productos.estado_venta |
| activo | Indica si el producto está activo | Booleano | productos.activo |

**Vista que consolida:** productos + categorias + subcategorias + modelos + marcas

---

### `v_ventas_diarias`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | ORIGEN |
|-------|-------------|---------------|--------|
| dia | Fecha de la venta (sin hora) | Fecha | DATE(ventas.fecha) |
| total_ventas | Cantidad de ventas realizadas en ese día | Entero | COUNT(*) |
| monto_total | Suma total de montos vendidos en el día | Decimal | SUM(ventas.total) |
| descuentos_total | Suma total de descuentos aplicados en el día | Decimal | SUM(ventas.descuento) |
| ticket_promedio | Valor promedio por venta en el día | Decimal | AVG(ventas.total) |

**Vista que consolida:** ventas (filtro: estado = 'completada', agrupado por día)

---

### `v_sesiones_activas`

| CAMPO | DESCRIPCION | TIPO DE CAMPO | ORIGEN |
|-------|-------------|---------------|--------|
| id | Identificador de la sesión activa | Entero | sesiones_cyber.id |
| estacion | Nombre de la estación en uso | Caracter variable | estaciones_cyber.nombre |
| tarifa | Nombre de la tarifa aplicada | Caracter variable | tarifas_cyber.nombre |
| precio_por_hora | Precio por hora de la tarifa | Decimal | tarifas_cyber.precio_por_hora |
| tiempo_minimo | Minutos mínimos de cobro | Entero | tarifas_cyber.tiempo_minimo |
| hora_inicio | Hora de inicio de la sesión | Fecha/Hora | sesiones_cyber.hora_inicio |
| cliente_nombre | Nombre del cliente en la sesión | Caracter variable | clientes.nombre |
| usuario_registra | Nombre del usuario que registró la sesión | Caracter variable | usuarios.nombre |
| minutos_transcurridos | Minutos transcurridos desde el inicio | Entero | TIMESTAMPDIFF |
| costo_estimado | Costo estimado según tarifa aplicada | Decimal | Calculado con regla de tiempo mínimo |

**Vista que consolida:** sesiones_cyber + estaciones_cyber + tarifas_cyber + clientes + usuarios (filtro: estado = 'activa')

---

## Procedimientos Almacenados

| Nombre | Descripción | Parámetros |
|--------|-------------|------------|
| `sp_registrar_movimiento_stock` | Registra un movimiento de stock de forma transaccional. Bloquea el producto con `FOR UPDATE`, valida que el stock no quede negativo, actualiza `productos.stock` e inserta el registro en `bitacora_movimientos_stock` | p_producto_id, p_tipo (entrada/salida/ajuste), p_cantidad, p_usuario_id, p_motivo, p_referencia_tipo, p_referencia_id |
| `sp_cerrar_sesion_cyber` | Cierra una sesión de cybercafé. Calcula la duración, aplica la regla de tiempo mínimo de la tarifa, actualiza `sesiones_cyber` con hora_fin, costo_total y estado='cerrada', y libera la estación a 'Disponible' | p_sesion_id |

---

## Triggers (Disparadores)

| Nombre | Evento | Tabla | Acción |
|--------|--------|-------|--------|
| `trg_actualizar_totales_venta` | AFTER INSERT | detalle_ventas | Recalcula automáticamente subtotal y total en la cabecera de `ventas` sumando los subtotales de los detalles insertados |
| `trg_auditar_precio_producto` | BEFORE UPDATE | productos | Si cambia el `precio_venta`, registra automáticamente el cambio en `bitacora_movimientos_stock` con referencia_tipo='ajuste_precio' |

---

## Resumen de Relaciones (Claves Foráneas)

| Tabla | Columna FK | Tabla Referencia | Columna Ref | Regla ON DELETE |
|-------|-----------|-----------------|-------------|-----------------|
| usuarios | rol_id | roles | id | RESTRICT |
| categorias | subcategoria_id | subcategorias | id | RESTRICT |
| modelos | marca_id | marcas | id | RESTRICT |
| productos | categoria_id | categorias | id | RESTRICT |
| productos | modelo_id | modelos | id | SET NULL |
| activos | tipo_activo_id | tipos_activo | id | RESTRICT |
| activos | responsable_id | usuarios | id | SET NULL |
| estaciones_cyber | tarifa_id | tarifas_cyber | id | RESTRICT |
| ventas | usuario_id | usuarios | id | SET NULL |
| ventas | cliente_id | clientes | id | RESTRICT |
| detalle_ventas | venta_id | ventas | id | CASCADE |
| detalle_ventas | producto_id | productos | id | RESTRICT |
| solicitudes | proveedor_id | proveedores | id | RESTRICT |
| solicitudes | usuario_id | usuarios | id | SET NULL |
| detalle_solicitudes | solicitud_id | solicitudes | id | CASCADE |
| detalle_solicitudes | producto_id | productos | id | RESTRICT |
| sesiones_cyber | estacion_id | estaciones_cyber | id | RESTRICT |
| sesiones_cyber | usuario_id | usuarios | id | SET NULL |
| sesiones_cyber | cliente_id | clientes | id | RESTRICT |
| asesorias | cliente_asesoria_id | clientes_asesorias | id | RESTRICT |
| bitacora_movimientos_stock | producto_id | productos | id | RESTRICT |
| bitacora_movimientos_stock | usuario_id | usuarios | id | SET NULL |
| producto_proveedor | producto_id | productos | id | CASCADE |
| producto_proveedor | proveedor_id | proveedores | id | CASCADE |
| usuario_asesoria | usuario_id | usuarios | id | CASCADE |
| usuario_asesoria | asesoria_id | asesorias | id | CASCADE |

---

*Documentación generada a partir del esquema SQL en `src/Database/estructura.sql`*
