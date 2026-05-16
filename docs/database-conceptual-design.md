# Documentación del Diseño Conceptual de Base de Datos - Sistema ZWL

## 1. Introducción

El sistema ZWL es una aplicación de gestión integral diseñada para administrar múltiples aspectos de un negocio que incluye ventas, inventario, proveedores, activos fijos y control de cybercafé. Este documento describe el diseño conceptual de la base de datos que soporta todas estas funcionalidades.

## 2. Diagrama Entidad-Relación (Conceptual)

```
+---------------+       +----------------+       +------------------+
|   USUARIOS    |------>|     VENTAS     |<------|    CLIENTES      |
+---------------+       +----------------+       +------------------+
| PK: id        |       | PK: id         |       | (opcional)       |
|    nombre     |       | FK: usuario_id |       +------------------+
|    email      |       |    fecha       |
+---------------+       |    total       |
        |               |    estado      |
        |               +----------------+
        |                       |
        v                       v
+----------------+       +------------------+
| SESIONES_CYBER |       | DETALLE_VENTAS   |
+----------------+       +------------------+
| PK: id         |       | PK: id           |
| FK: usuario_id |       | FK: venta_id     |
| FK: estacion_id|       | FK: producto_id  |
| hora_inicio    |       |    cantidad      |
| hora_fin       |       |    precio_unitario|
| costo          |       |    subtotal      |
+----------------+       +------------------+
        ^
        |
+----------------+
| ESTACIONES_    |
| CYBER          |
+----------------+
| PK: id         |
|    nombre      |
|    estado      |
+----------------+

+----------------+       +------------------+
|   PROVEEDORES  |------>|   SOLICITUDES    |
+----------------+       +------------------+
| PK: id         |       | PK: id           |
|    nombre      |       | FK: proveedor_id |
|    contacto    |       | FK: usuario_id   |
|    email       |       |    fecha         |
|    telefono    |       |    estado        |
+----------------+       +------------------+

+----------------+       +------------------+
|   PRODUCTOS    |------>| MOVIMIENTOS_STOCK|
+----------------+       +------------------+
| PK: id         |       | PK: id           |
|    nombre      |       | FK: producto_id  |
|    categoria   |       | FK: usuario_id   |
|    precio      |       |    tipo          |
|    stock       |       |    cantidad      |
|    stock_minimo|       |    stock_anterior|
|    estado      |       |    stock_nuevo   |
+----------------+       +------------------+

+----------------+
|    ACTIVOS     |
+----------------+
| PK: id         |
|    nombre      |
|    tipo        |
|    estado      |
|    fecha_adq.  |
|    fecha_venc. |
+----------------+
```

## 3. Entidades y Atributos

### 3.1 USUARIOS
**Descripción**: Representa a los usuarios del sistema que pueden realizar ventas, solicitudes y movimientos de inventario.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identificador único del usuario |
| nombre | VARCHAR(100) | NOT NULL | Nombre completo del usuario |
| email | VARCHAR(100) | NOT NULL, UNIQUE | Correo electrónico único |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación del registro |
| updated_at | TIMESTAMP | AUTO UPDATE | Fecha de última actualización |

### 3.2 PRODUCTOS
**Descripción**: Catálogo de productos disponibles para venta e inventario.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identificador único del producto |
| nombre | VARCHAR(150) | NOT NULL | Nombre descriptivo del producto |
| categoria | VARCHAR(50) | NOT NULL | Categoría del producto (Periféricos, Pantallas, etc.) |
| precio | DECIMAL(10,2) | NOT NULL, DEFAULT 0.00 | Precio de venta unitario |
| stock | INT | NOT NULL, DEFAULT 0 | Cantidad disponible en inventario |
| stock_minimo | INT | NOT NULL, DEFAULT 5 | Cantidad mínima antes de alerta |
| estado | ENUM | DEFAULT 'OK' | Estado del stock: OK, Crítico, Sin stock |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | AUTO UPDATE | Fecha de última actualización |

### 3.3 VENTAS
**Descripción**: Registro de transacciones de ventas realizadas en el sistema.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identificador único de la venta |
| fecha | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Fecha y hora de la venta |
| total | DECIMAL(10,2) | NOT NULL, DEFAULT 0.00 | Monto total de la venta |
| usuario_id | INT | FK, NULL | Usuario que realizó la venta |
| estado | ENUM | DEFAULT 'completada' | Estado: completada, pendiente, cancelada |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | AUTO UPDATE | Fecha de última actualización |

### 3.4 DETALLE_VENTAS
**Descripción**: Detalle de productos incluidos en cada venta (relación muchos a muchos entre ventas y productos).

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identificador único del detalle |
| venta_id | INT | FK, NOT NULL | Venta a la que pertenece el detalle |
| producto_id | INT | FK, NOT NULL | Producto vendido |
| cantidad | INT | NOT NULL, DEFAULT 1 | Cantidad vendida |
| precio_unitario | DECIMAL(10,2) | NOT NULL | Precio unitario al momento de la venta |
| subtotal | DECIMAL(10,2) | NOT NULL | Cantidad * Precio unitario |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

### 3.5 PROVEEDORES
**Descripción**: Entidades que suministran productos al negocio.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identificador único del proveedor |
| nombre | VARCHAR(150) | NOT NULL | Nombre de la empresa proveedora |
| contacto | VARCHAR(100) | NULL | Persona de contacto |
| email | VARCHAR(100) | NULL | Correo electrónico de contacto |
| telefono | VARCHAR(20) | NULL | Teléfono de contacto |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | AUTO UPDATE | Fecha de última actualización |

### 3.6 SOLICITUDES
**Descripción**: Solicitudes de pedidos o reabastecimiento enviadas a proveedores.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identificador único de la solicitud |
| codigo | VARCHAR(20) | NOT NULL, UNIQUE | Código único (ej. SOL-089) |
| proveedor_id | INT | FK, NOT NULL | Proveedor al que se envía la solicitud |
| fecha | DATE | NOT NULL | Fecha de la solicitud |
| estado | ENUM | DEFAULT 'Pendiente' | Estado: Pendiente, Recibida, Cancelada |
| usuario_id | INT | FK, NULL | Usuario que realizó la solicitud |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | AUTO UPDATE | Fecha de última actualización |

### 3.7 ACTIVOS
**Descripción**: Activos fijos de la empresa (equipos, herramientas, licencias).

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identificador único del activo |
| nombre | VARCHAR(150) | NOT NULL | Nombre o descripción del activo |
| tipo | ENUM | NOT NULL | Tipo: Equipos, Herramientas, Licencias |
| estado | ENUM | DEFAULT 'Activo' | Estado: Activo, Mantenimiento, Vencida |
| fecha_adquisicion | DATE | NULL | Fecha de compra o adquisición |
| fecha_vencimiento | DATE | NULL | Fecha de vencimiento (para licencias) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | AUTO UPDATE | Fecha de última actualización |

### 3.8 ESTACIONES_CYBER
**Descripción**: Computadoras o estaciones de trabajo en el cybercafé.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identificador único de la estación |
| nombre | VARCHAR(50) | NOT NULL, UNIQUE | Identificador de la estación (ej. PC-01) |
| estado | ENUM | DEFAULT 'Disponible' | Estado: Disponible, Ocupada, Mantenimiento |
| tipo | VARCHAR(50) | NULL | Tipo de estación (Gaming, Oficina, etc.) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | AUTO UPDATE | Fecha de última actualización |

### 3.9 SESIONES_CYBER
**Descripción**: Registro de sesiones de uso de estaciones en el cybercafé.

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identificador único de la sesión |
| estacion_id | INT | FK, NOT NULL | Estación utilizada |
| usuario_id | INT | FK, NULL | Usuario que usa la estación (opcional) |
| hora_inicio | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Hora de inicio de sesión |
| hora_fin | DATETIME | NULL | Hora de fin de sesión |
| duracion_minutos | INT | NULL | Duración calculada en minutos |
| costo | DECIMAL(10,2) | NULL | Costo de la sesión |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |
| updated_at | TIMESTAMP | AUTO UPDATE | Fecha de última actualización |

### 3.10 MOVIMIENTOS_STOCK
**Descripción**: Historial de todos los movimientos de inventario (entradas, salidas, ajustes).

| Atributo | Tipo | Restricciones | Descripción |
|----------|------|---------------|-------------|
| id | INT | PK, AUTO_INCREMENT | Identificador único del movimiento |
| producto_id | INT | FK, NOT NULL | Producto afectado |
| tipo | ENUM | NOT NULL | Tipo: entrada, salida, ajuste |
| cantidad | INT | NOT NULL | Cantidad movida (positiva o negativa) |
| stock_anterior | INT | NOT NULL | Stock antes del movimiento |
| stock_nuevo | INT | NOT NULL | Stock después del movimiento |
| fecha | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Fecha del movimiento |
| usuario_id | INT | FK, NULL | Usuario que realizó el movimiento |
| motivo | VARCHAR(255) | NULL | Razón del movimiento |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Fecha de creación |

## 4. Relaciones y Cardinalidad

### 4.1 Relación USUARIOS - VENTAS
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un usuario puede realizar múltiples ventas, pero una venta es realizada por un solo usuario.
- **Implementación**: `ventas.usuario_id` → `usuarios.id`

### 4.2 Relación VENTAS - DETALLE_VENTAS
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Una venta puede tener múltiples productos en su detalle.
- **Implementación**: `detalle_ventas.venta_id` → `ventas.id`

### 4.3 Relación PRODUCTOS - DETALLE_VENTAS
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un producto puede aparecer en múltiples detalles de ventas.
- **Implementación**: `detalle_ventas.producto_id` → `productos.id`

### 4.4 Relación PROVEEDORES - SOLICITUDES
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un proveedor puede recibir múltiples solicitudes.
- **Implementación**: `solicitudes.proveedor_id` → `proveedores.id`

### 4.5 Relación USUARIOS - SOLICITUDES
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un usuario puede crear múltiples solicitudes.
- **Implementación**: `solicitudes.usuario_id` → `usuarios.id`

### 4.6 Relación ESTACIONES_CYBER - SESIONES_CYBER
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Una estación puede tener múltiples sesiones históricas.
- **Implementación**: `sesiones_cyber.estacion_id` → `estaciones_cyber.id`

### 4.7 Relación USUARIOS - SESIONES_CYBER
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un usuario puede tener múltiples sesiones de cyber.
- **Implementación**: `sesiones_cyber.usuario_id` → `usuarios.id`

### 4.8 Relación PRODUCTOS - MOVIMIENTOS_STOCK
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un producto puede tener múltiples movimientos de stock.
- **Implementación**: `movimientos_stock.producto_id` → `productos.id`

### 4.9 Relación USUARIOS - MOVIMIENTOS_STOCK
- **Cardinalidad**: Uno a Muchos (1:N)
- **Descripción**: Un usuario puede realizar múltiples movimientos de inventario.
- **Implementación**: `movimientos_stock.usuario_id` → `usuarios.id`

## 5. Reglas de Negocio

### 5.1 Gestión de Inventario
1. El stock de un producto no puede ser negativo (se valida en la aplicación).
2. Cuando el stock es menor al stock mínimo, el estado del producto cambia a 'Crítico'.
3. Cuando el stock es 0, el estado cambia a 'Sin stock'.
4. Todo movimiento de stock debe registrarse en la tabla `movimientos_stock` para auditoría.

### 5.2 Ventas
1. Al completar una venta, se debe:
   - Crear el registro en `ventas`
   - Crear los detalles en `detalle_ventas`
   - Actualizar el stock en `productos`
   - Registrar el movimiento en `movimientos_stock`
2. Una venta no puede existir sin al menos un detalle.
3. El total de la venta debe ser la suma de los subtotales de los detalles.

### 5.3 Cybercafé
1. Una estación solo puede tener una sesión activa a la vez.
2. Al finalizar una sesión, se debe calcular la duración y el costo.
3. El estado de la estación debe actualizarse automáticamente.

### 5.4 Solicitudes a Proveedores
1. El código de solicitud debe ser único y seguir el formato SOL-XXX.
2. Una solicitud pendiente puede ser cancelada o marcada como recibida.
3. Al recibir una solicitud, se debe actualizar el stock de los productos correspondientes.

### 5.5 Activos
1. Las licencias son los únicos activos que requieren fecha de vencimiento.
2. Los activos vencidos deben cambiar automáticamente su estado a 'Vencida'.

## 6. Índices y Optimización

Se han creado índices en las columnas más utilizadas para consultas:

- `productos(categoria)`: Búsqueda rápida por categoría
- `ventas(fecha)`: Consultas de ventas por rango de fechas
- `ventas(usuario_id)`: Consultas de ventas por usuario
- `detalle_ventas(venta_id)`: Acceso rápido a detalles de una venta
- `detalle_ventas(producto_id)`: Consultas de historial de ventas por producto
- `solicitudes(proveedor_id)`: Consultas de solicitudes por proveedor
- `solicitudes(fecha)`: Consultas por rango de fechas
- `sesiones_cyber(estacion_id)`: Consultas de sesiones por estación
- `movimientos_stock(producto_id)`: Consultas de historial por producto
- `movimientos_stock(fecha)`: Consultas por rango de fechas

## 7. Integridad Referencial

Las claves foráneas están configuradas con las siguientes políticas:

- **ON DELETE SET NULL**: Para relaciones donde se quiere mantener el historial (ventas, solicitudes, sesiones, movimientos). Si se elimina el usuario, las referencias se ponen a NULL.
- **ON DELETE CASCADE**: Para detalles de ventas. Si se elimina una venta, se eliminan sus detalles automáticamente.
- **ON DELETE RESTRICT**: Para productos en detalles de ventas y movimientos. No se puede eliminar un producto que tenga historial.

## 8. Consideraciones de Diseño

1. **Normalización**: La base de datos está en Tercera Forma Normal (3NF), evitando redundancia de datos.
2. **Escalabilidad**: El diseño permite agregar nuevos campos o entidades sin afectar la estructura existente.
3. **Auditoría**: Todas las tablas importantes tienen campos `created_at` y `updated_at` para seguimiento temporal.
4. **Flexibilidad**: Algunos campos permiten NULL para adaptarse a diferentes escenarios (ej. ventas anónimas, sesiones de cyber sin usuario registrado).

## 9. Diagrama de Esquema Lógico (Notación Crow's Foot)

```
[USUARIOS] 1 ----------< VENTAS >---------- 0..* [CLIENTES]
    1                                           (opcional)
    |
    |
    |----------< SOLICITUDES >---------- [PROVEEDORES]
    |                                       1
    |                                        
    |----------< SESIONES_CYBER >---------- [ESTACIONES_CYBER]
    |                                       1
    |
    |----------< MOVIMIENTOS_STOCK >----------+
                                              |
[PRODUCTOS] 1 ----------< DETALLE_VENTAS >----+
                    ^        |
                    |        |
                    +--------+
              [VENTAS]
```

## 10. Conclusiones

El diseño conceptual de la base de datos ZWL proporciona una estructura sólida y flexible para soportar todas las funcionalidades del sistema. La normalización adecuada, el uso de claves foráneas con políticas de integridad referencial, y la inclusión de índices estratégicos aseguran un rendimiento óptimo y mantenimiento de la integridad de los datos.
