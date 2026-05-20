# Diagrama Entidad-Relación — Sistema ZWL (EIS_Zona_Web_Lara)

```mermaid
erDiagram
    %% ==================== CATALOGOS ====================
    roles {
        tinyint id PK
        varchar nombre UK
        varchar descripcion
        timestamp created_at
    }

    categorias {
        smallint id PK
        varchar nombre UK
        varchar descripcion
        boolean activa
        timestamp created_at
    }

    marcas {
        smallint id PK
        varchar nombre UK
        varchar descripcion
        timestamp created_at
    }

    tipos_activo {
        tinyint id PK
        varchar nombre UK
        varchar descripcion
        timestamp created_at
    }

    tarifas_cyber {
        smallint id PK
        varchar nombre UK
        decimal precio_por_hora
        decimal precio_por_minuto
        int tiempo_minimo
        boolean activa
        timestamp created_at
        timestamp updated_at
    }

    tipos_pago {
        tinyint id PK
        varchar nombre UK
        timestamp created_at
    }

    %% ==================== TABLAS PRINCIPALES ====================

    usuarios {
        int id PK
        varchar username UK
        varchar password_hash
        varchar nombre
        varchar email UK
        varchar telefono
        boolean activo
        tinyint rol_id FK
        datetime ultimo_acceso
        timestamp created_at
        timestamp updated_at
    }

    productos {
        int id PK
        varchar codigo UK
        varchar codigo_barras UK
        varchar nombre
        text descripcion
        smallint categoria_id FK
        smallint marca_id FK
        enum unidad_medida
        int stock
        int stock_minimo
        varchar ubicacion
        decimal costo_compra
        decimal precio_venta
        decimal iva
        boolean permite_descuento
        enum estado_venta
        boolean activo
        timestamp created_at
        timestamp updated_at
    }

    proveedores {
        int id PK
        varchar nombre
        varchar rif UK
        enum tipo_documento
        varchar contacto
        varchar email
        varchar telefono
        text direccion
        boolean activo
        timestamp created_at
        timestamp updated_at
    }

    producto_proveedor {
        int producto_id PK, FK
        int proveedor_id PK, FK
        varchar codigo_proveedor
        decimal precio_compra
        smallint tiempo_entrega_dias
        boolean es_proveedor_principal
        timestamp created_at
    }

    ventas {
        int id PK
        datetime fecha
        int usuario_id FK
        varchar cliente_nombre
        varchar cliente_cedula
        tinyint tipo_pago_id FK
        decimal subtotal
        decimal descuento
        decimal iva_total
        decimal total
        enum estado
        text notas
        timestamp created_at
        timestamp updated_at
    }

    detalle_ventas {
        int id PK
        int venta_id FK
        int producto_id FK
        int cantidad
        decimal precio_unitario
        decimal iva_unitario
        decimal descuento
        decimal subtotal
        timestamp created_at
    }

    solicitudes {
        int id PK
        varchar codigo UK
        int proveedor_id FK
        date fecha
        date fecha_estimada_entrega
        tinyint tipo_pago_id FK
        decimal subtotal
        decimal iva_total
        decimal total
        enum estado
        int usuario_id FK
        text notas
        timestamp created_at
        timestamp updated_at
    }

    detalle_solicitudes {
        int id PK
        int solicitud_id FK
        int producto_id FK
        int cantidad_solicitada
        int cantidad_recibida
        decimal precio_unitario_estimado
        decimal subtotal
        timestamp created_at
    }

    activos {
        int id PK
        varchar nombre
        text descripcion
        tinyint tipo_activo_id FK
        enum estado
        varchar ubicacion
        decimal valor_adquisicion
        date fecha_adquisicion
        date fecha_vencimiento
        int responsable_id FK
        text notas
        timestamp created_at
        timestamp updated_at
    }

    estaciones_cyber {
        int id PK
        varchar nombre UK
        enum estado
        smallint tarifa_id FK
        varchar especificaciones
        varchar ip_local
        varchar mac_address
        timestamp created_at
        timestamp updated_at
    }

    sesiones_cyber {
        int id PK
        int estacion_id FK
        int usuario_id FK
        varchar cliente_nombre
        smallint tarifa_id FK
        datetime hora_inicio
        datetime hora_fin
        decimal costo_total
        enum estado
        timestamp created_at
        timestamp updated_at
    }

    movimientos_stock {
        bigint id PK
        int producto_id FK
        enum tipo
        int cantidad
        int stock_anterior
        int stock_nuevo
        decimal precio_unitario
        decimal costo_total
        datetime fecha
        int usuario_id FK
        varchar referencia_tipo
        int referencia_id
        varchar motivo
        timestamp created_at
    }

    asesorias {
        int id PK
        varchar ciudadano
        varchar cedula
        varchar documento
        text descripcion
        enum estado
        int usuario_id FK
        datetime fecha_registro
        datetime fecha_cierre
        timestamp created_at
        timestamp updated_at
    }

    %% ==================== RELACIONES 1:N ====================

    roles ||--o{ usuarios : "tiene"
    categorias ||--o{ productos : "clasifica"
    marcas ||--o{ productos : "fabrica"
    tipos_activo ||--o{ activos : "categoriza"
    tipos_pago ||--o{ ventas : "registra_pago"
    tipos_pago ||--o{ solicitudes : "registra_pago"
    tarifas_cyber ||--o{ estaciones_cyber : "aplica"
    tarifas_cyber ||--o{ sesiones_cyber : "aplica"
    estaciones_cyber ||--o{ sesiones_cyber : "genera"
    usuarios ||--o{ ventas : "realiza"
    usuarios ||--o{ solicitudes : "crea"
    usuarios ||--o{ sesiones_cyber : "atiende"
    usuarios ||--o{ movimientos_stock : "registra"
    usuarios ||--o{ asesorias : "atiende"
    usuarios ||--o{ activos : "custodia"
    proveedores ||--o{ solicitudes : "recibe"

    %% ==================== RELACIONES M:N ====================

    ventas ||--o{ detalle_ventas : "contiene"
    detalle_ventas }o--|| productos : "incluye"

    solicitudes ||--o{ detalle_solicitudes : "contiene"
    detalle_solicitudes }o--|| productos : "incluye"

    productos ||--o{ producto_proveedor : "suministra"
    proveedores ||--o{ producto_proveedor : "suministra"

    productos ||--o{ movimientos_stock : "afecta"
```

## Leyenda

| Símbolo | Significado                    |
|---------|--------------------------------|
| `||--o{` | Uno a Muchos (1:N)            |
| `}o--||` | Muchos a Uno (N:1)            |
| `||--o{` | También usado como 1 a Muchos |
| `PK`     | _Primary Key_ (Clave Primaria) |
| `FK`     | _Foreign Key_ (Clave Foránea)  |
| `UK`     | _Unique Key_ (Clave Única)     |

## Resumen de tablas

| # | Tabla | Tipo | Descripción |
|---|-------|------|-------------|
| 1 | `roles` | Catálogo | Roles de usuario (Admin, Vendedor, Cyber, Asesor, Consultor) |
| 2 | `categorias` | Catálogo | Categorías de productos |
| 3 | `marcas` | Catálogo | Marcas de productos |
| 4 | `tipos_activo` | Catálogo | Tipos de activos fijos |
| 5 | `tarifas_cyber` | Catálogo | Tarifas del cyber (Gaming, Oficina, Premium, Estudiante) |
| 6 | `tipos_pago` | Catálogo | Formas de pago |
| 7 | `usuarios` | Maestra | Usuarios del sistema |
| 8 | `productos` | Maestra | Productos e inventario |
| 9 | `proveedores` | Maestra | Proveedores |
| 10 | `producto_proveedor` | Puente | Relación M:N productos ↔ proveedores |
| 11 | `ventas` | Transaccional | Cabecera de ventas |
| 12 | `detalle_ventas` | Puente | Líneas de detalle de venta (M:N ventas ↔ productos) |
| 13 | `solicitudes` | Transaccional | Solicitudes de compra a proveedores |
| 14 | `detalle_solicitudes` | Puente | Líneas de solicitud (M:N solicitudes ↔ productos) |
| 15 | `activos` | Maestra | Activos fijos de la empresa |
| 16 | `estaciones_cyber` | Maestra | Estaciones/PCS del cyber |
| 17 | `sesiones_cyber` | Transaccional | Sesiones de cyber café |
| 18 | `movimientos_stock` | Transaccional | Histórico de movimientos de inventario (BIGINT) |
| 19 | `asesorias` | Transaccional | Casos de asesoría legal/técnica |
