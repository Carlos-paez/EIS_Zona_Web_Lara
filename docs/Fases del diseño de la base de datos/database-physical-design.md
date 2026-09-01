# Documentación del Diseño Físico de Base de Datos - Sistema ZWL

> **Actualizado (Agosto 2026):** Documento de diseño histórico. La base de datos final implementada contiene **21 tablas** y todos los módulos están conectados a ella.

## 1. Introducción
El diseño físico define la implementación del esquema lógico en el sistema gestor de bases de datos (MySQL 8.0+, motor InnoDB), especificando la organización de archivos, estructuras de almacenamiento, índices físicos, parámetros de configuración y políticas de respaldo para el sistema ZWL.

---

## 2. Configuración del Motor de Almacenamiento
Todas las tablas de la base de datos `zwl` utilizan el motor **InnoDB**, seleccionado por:
- Soporte nativo para transacciones ACID
- Integridad referencial (claves foráneas)
- Bloqueo a nivel de fila (mejor concurrencia)
- Recuperación ante fallos mediante redo/undo logs

---

## 3. Organización Física de Archivos
### 3.1 Estructura de Tablespaces
InnoDB utiliza una configuración **file-per-table** (habilitada por defecto en MySQL 8.0+), donde cada tabla tiene su propio archivo de tablespace:
- Formato: `nombre_tabla.ibd`
- Ubicación: `[datadir]/zwl/` (por defecto en Windows: `C:\ProgramData\MySQL\MySQL Server 8.0\Data\zwl\`)

### 3.2 Archivos del Sistema InnoDB
| Archivo | Descripción | Ubicación |
|---------|-------------|-----------|
| `ibdata1` | System tablespace (datos de diccionario, undo logs) | Directorio de datos de MySQL |
| `ib_logfile0`, `ib_logfile1` | Redo logs (registro de cambios para recuperación) | Directorio de datos de MySQL |
| `ibtmp1` | Temporary tablespace | Directorio de datos de MySQL |

### 3.3 Archivos por Tabla (`zwl`)
```
[datadir]\zwl\
├── roles.ibd
├── categorias.ibd
├── marcas.ibd
├── tipos_activo.ibd
├── tarifas_cyber.ibd
├── tipos_pago.ibd
├── usuarios.ibd
├── productos.ibd
├── proveedores.ibd
├── producto_proveedor.ibd
├── ventas.ibd
├── detalle_ventas.ibd
├── solicitudes.ibd
├── detalle_solicitudes.ibd
├── activos.ibd
├── estaciones_cyber.ibd
├── sesiones_cyber.ibd
├── movimientos_stock.ibd
├── asesorias.ibd
└── (Las vistas no generan archivos .ibd, son tablas virtuales)
```

---

## 4. Almacenamiento Físico de Tipos de Datos
InnoDB almacena los datos con los siguientes tamaños:

| Tipo de Dato | Tamaño de Almacenamiento | Notas Físicas |
|--------------|--------------------------|---------------|
| `TINYINT UNSIGNED` | 1 byte | Entero 0-255 |
| `SMALLINT UNSIGNED` | 2 bytes | Entero 0-65535 |
| `INT UNSIGNED` | 4 bytes | Entero 0-4294967295 |
| `BIGINT UNSIGNED` | 8 bytes | Entero 0-18446744073709551615 |
| `DECIMAL(12,2)` | Variable (máx 6 bytes) | Almacenado como bytes binarios, 2 dígitos por byte |
| `DECIMAL(8,2)` | Variable (máx 4 bytes) | Almacenado como bytes binarios |
| `DECIMAL(5,2)` | Variable (máx 3 bytes) | Almacenado como bytes binarios |
| `VARCHAR(n)` | 1-2 bytes de longitud + n bytes (utf8mb4: 1-4 bytes por carácter) | Longitud indica el número de bytes usados |
| `ENUM('a','b','c')` | 1-2 bytes | Almacena un entero que mapea a la posición del valor |
| `BOOLEAN` | 1 byte | Almacenado como TINYINT(1) |
| `DATE` | 3 bytes | Formato: YYYYMMDD |
| `DATETIME` | 5-8 bytes | Formato: YYYYMMDDHHMMSS + microsegundos |
| `TIMESTAMP` | 4 bytes | Almacena segundos desde la época Unix (1970-01-01) |
| `TEXT` | L + 2 bytes (L < 65536) | Almacenado fuera de página si excede 768 bytes |

---

## 5. Estructura Física de Índices
InnoDB utiliza árboles **B+ Tree** para todos los índices:

### 5.1 Índice Clustered (Primario)
- La clave primaria de cada tabla es un índice clustered: los datos de la fila se almacenan físicamente junto a las claves del índice.
- Ejemplo: Para `usuarios.ibd`, el árbol B+ de la PK `id` tiene las columnas completas en las hojas.

### 5.2 Índices Secundarios (No Clustered)
- Almacenan solo los valores de las columnas del índice y la clave primaria de la fila correspondiente.
- Para acceder a los datos completos, InnoDB usa la PK almacenada en el índice secundario para buscar en el índice clustered.

### 5.3 Detalle de Índices Físicos
| Tabla | Índice | Tipo | Columnas | Estructura |
|-------|--------|------|----------|------------|
| roles | PRIMARY | Clustered | id | B+ Tree |
| roles | nombre | Secondary UNIQUE | nombre | B+ Tree |
| categorias | PRIMARY | Clustered | id | B+ Tree |
| categorias | nombre | Secondary UNIQUE | nombre | B+ Tree |
| marcas | PRIMARY | Clustered | id | B+ Tree |
| marcas | nombre | Secondary UNIQUE | nombre | B+ Tree |
| tipos_activo | PRIMARY | Clustered | id | B+ Tree |
| tipos_activo | nombre | Secondary UNIQUE | nombre | B+ Tree |
| tarifas_cyber | PRIMARY | Clustered | id | B+ Tree |
| tarifas_cyber | nombre | Secondary UNIQUE | nombre | B+ Tree |
| tipos_pago | PRIMARY | Clustered | id | B+ Tree |
| tipos_pago | nombre | Secondary UNIQUE | nombre | B+ Tree |
| usuarios | PRIMARY | Clustered | id | B+ Tree |
| usuarios | username | Secondary UNIQUE | username | B+ Tree |
| usuarios | email | Secondary UNIQUE | email | B+ Tree |
| usuarios | idx_usuarios_rol | Secondary | rol_id | B+ Tree |
| productos | PRIMARY | Clustered | id | B+ Tree |
| productos | codigo | Secondary UNIQUE | codigo | B+ Tree |
| productos | idx_productos_barras | Secondary UNIQUE | codigo_barras | B+ Tree |
| productos | idx_productos_categoria | Secondary | categoria_id | B+ Tree |
| productos | idx_productos_marca | Secondary | marca_id | B+ Tree |
| proveedores | PRIMARY | Clustered | id | B+ Tree |
| proveedores | rif | Secondary UNIQUE | rif | B+ Tree |
| producto_proveedor | PRIMARY | Clustered (compuesta) | producto_id, proveedor_id | B+ Tree |
| producto_proveedor | idx_pp_proveedor | Secondary | proveedor_id | B+ Tree |
| ventas | PRIMARY | Clustered | id | B+ Tree |
| ventas | idx_ventas_fecha | Secondary | fecha | B+ Tree |
| ventas | idx_ventas_usuario | Secondary | usuario_id | B+ Tree |
| ventas | idx_ventas_estado | Secondary | estado | B+ Tree |
| detalle_ventas | PRIMARY | Clustered | id | B+ Tree |
| detalle_ventas | idx_dv_venta | Secondary | venta_id | B+ Tree |
| detalle_ventas | idx_dv_producto | Secondary | producto_id | B+ Tree |
| solicitudes | PRIMARY | Clustered | id | B+ Tree |
| solicitudes | codigo | Secondary UNIQUE | codigo | B+ Tree |
| solicitudes | idx_solicitudes_proveedor | Secondary | proveedor_id | B+ Tree |
| solicitudes | idx_solicitudes_fecha | Secondary | fecha | B+ Tree |
| solicitudes | idx_solicitudes_estado | Secondary | estado | B+ Tree |
| detalle_solicitudes | PRIMARY | Clustered | id | B+ Tree |
| detalle_solicitudes | idx_ds_solicitud | Secondary | solicitud_id | B+ Tree |
| detalle_solicitudes | idx_ds_producto | Secondary | producto_id | B+ Tree |
| activos | PRIMARY | Clustered | id | B+ Tree |
| activos | idx_activos_tipo | Secondary | tipo_activo_id | B+ Tree |
| activos | idx_activos_estado | Secondary | estado | B+ Tree |
| estaciones_cyber | PRIMARY | Clustered | id | B+ Tree |
| estaciones_cyber | nombre | Secondary UNIQUE | nombre | B+ Tree |
| sesiones_cyber | PRIMARY | Clustered | id | B+ Tree |
| sesiones_cyber | idx_sc_estacion | Secondary | estacion_id | B+ Tree |
| sesiones_cyber | idx_sc_activas | Secondary | estado | B+ Tree |
| sesiones_cyber | idx_sc_fecha | Secondary | hora_inicio | B+ Tree |
| movimientos_stock | PRIMARY | Clustered | id | B+ Tree |
| movimientos_stock | idx_ms_producto | Secondary | producto_id | B+ Tree |
| movimientos_stock | idx_ms_fecha | Secondary | fecha | B+ Tree |
| movimientos_stock | idx_ms_referencia | Secondary | referencia_tipo, referencia_id | B+ Tree |
| asesorias | PRIMARY | Clustered | id | B+ Tree |
| asesorias | idx_asesorias_estado | Secondary | estado | B+ Tree |
| asesorias | idx_asesorias_cedula | Secondary | cedula | B+ Tree |
| asesorias | idx_asesorias_fecha | Secondary | fecha_registro | B+ Tree |

---

## 6. Estrategia de Particionamiento Físico (Recomendado)

Para tablas con alto volumen de inserciones (crecimiento histórico), se recomienda particionamiento por rango:

### 6.1 Tabla `movimientos_stock`
- Particionamiento por `RANGE` en la columna `fecha` (tipo DATETIME)
- Cada partición almacena datos de un trimestre calendario

```sql
ALTER TABLE movimientos_stock
PARTITION BY RANGE (YEAR(fecha) * 100 + MONTH(fecha)) (
    PARTITION p202601 VALUES LESS THAN (202602),
    PARTITION p202602 VALUES LESS THAN (202603),
    PARTITION p202603 VALUES LESS THAN (202604),
    PARTITION p202604 VALUES LESS THAN (202605),
    PARTITION p202605 VALUES LESS THAN (202606),
    PARTITION p_max VALUES LESS THAN MAXVALUE
);
```

Cada partición genera un archivo `.ibd` separado: `movimientos_stock#P#p202601.ibd`

### 6.2 Tabla `sesiones_cyber`
- Particionamiento por `RANGE` en `hora_inicio` para sesiones antiguas
- Las particiones de sesiones cerradas se pueden archivar o mover a almacenamiento en frío

### 6.3 Tabla `detalle_ventas`
- Particionamiento por `RANGE` en `venta_id` o mediante referencia a la fecha de la venta (JOIN con ventas)

---

## 7. Parámetros de Configuración MySQL (Physical Tuning)

Ajustes recomendados en `my.ini` (Windows) para optimizar el rendimiento físico de la base `zwl`:

```ini
[mysqld]
# InnoDB Buffer Pool: 70-80% de RAM disponible (ej. 4GB para servidor con 6GB RAM)
innodb_buffer_pool_size = 4G
# Tamaño de los redo logs (256MB para cargas de escritura moderadas)
innodb_log_file_size = 256M
# Número de archivos de log (2 por defecto)
innodb_log_files_in_group = 2
# Método de flush de datos (O_DIRECT para evitar doble cacheo en Windows)
innodb_flush_method = O_DIRECT
# Tamaño de página (16KB por defecto)
innodb_page_size = 16K
# Habilitar recolección de estadísticas para índices
innodb_stats_persistent = ON
# Máximo de conexiones simultáneas
max_connections = 100
# Tamaño máximo de paquetes (para consultas con muchos detalles)
max_allowed_packet = 64M
# Timeout de conexión
wait_timeout = 600
```

---

## 8. Respaldo y Recuperación Física

### 8.1 Respaldo Físico (Hot Backup)
Se recomienda **Percona XtraBackup** para respaldos físicos en caliente (sin detener el servicio):
- Copia los archivos `.ibd`, redo logs y archivos de sistema
- Permite recuperación rápida (solo copiar archivos de vuelta al directorio de datos)

### 8.2 Respaldo Lógico (Complementario)
`mysqldump` para respaldos lógicos (SQL), útil para migraciones entre versiones:

```bash
# Respaldo completo de la base zwl
"C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe" -u root zwl > zwl_backup.sql

# Respaldo solo esquema (sin datos)
"C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe" -u root --no-data zwl > zwl_schema.sql

# Respaldo solo datos
"C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqldump.exe" -u root --no-create-info zwl > zwl_data.sql
```

### 8.3 Recuperación ante Fallos
InnoDB usa los redo logs (`ib_logfile*`) para recuperar transacciones confirmadas no volcadas al disco tras un reinicio inesperado. El procedimiento `sp_registrar_movimiento_stock` incluye manejo de errores con ROLLBACK para mantener la consistencia transaccional.

---

## 9. Seguridad a Nivel Físico

1. **Permisos de archivos**: El directorio de datos de MySQL debe tener permisos restringidos (solo la cuenta del servicio MySQL y administradores)
2. **Encriptación en reposo**: Habilitar encriptación de tablespaces InnoDB (MySQL 8.0+):
   ```sql
   ALTER TABLE usuarios ENCRYPTION = 'Y';
   ```
3. **Binary logs**: Encriptar registros binarios para auditoría:
   ```ini
   [mysqld]
   binlog_encryption = ON
   ```
4. **Credenciales**: Las contraseñas se almacenan como hash bcrypt (`VARCHAR(255)`) — 60 caracteres fijos + sal. No se almacenan en texto plano.

---

## 10. Monitoreo de Métricas Físicas

Métricas clave para supervisar el rendimiento del diseño físico:

- **Buffer pool hit rate**: Debe ser > 99% (consultar `SHOW ENGINE INNODB STATUS;`)
- **Disk I/O**: Latencia de lectura/escritura en el directorio de datos
- **Índices no utilizados**: Identificar con `sys.schema_unused_indexes`
- **Crecimiento de tablas**: Monitorear tamaño de `movimientos_stock.ibd` (es la tabla de mayor crecimiento por ser de auditoría)
- **Fragmentación de índices**: `SELECT * FROM information_schema.INNODB_METRICS WHERE NAME LIKE '%index%';`

---

## 11. Conclusión

El diseño físico de ZWL está optimizado para el motor InnoDB en entornos Windows (Laragon/XAMPP/WAMP), con 19 tablas y 3 vistas que generan estructuras de almacenamiento eficientes. Los 38 índices secundarios B+ Tree (13 de unicidad + 25 de búsqueda), más los 19 índices clustered de las claves primarias, garantizan acceso rápido a los datos más consultados, cubriendo todas las claves foráneas y columnas de filtrado frecuente. La configuración recomendada de InnoDB Buffer Pool (4GB) y redo logs (256MB) es adecuada para cargas de trabajo transaccionales moderadas. La estrategia de particionamiento para tablas históricas como `movimientos_stock` (BIGINT, ~48 bytes por fila) garantiza que el rendimiento no se degrade con el tiempo. Los procedimientos almacenados transaccionales con bloqueo pesimista (FOR UPDATE) y rollback automático mantienen la integridad de los datos a nivel físico.
