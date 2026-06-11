# Documentación del Modelo de Inventario — `crud_inventario.php`

## Índice

1. [Descripción General](#1-descripción-general)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Estructura del Archivo](#3-estructura-del-archivo)
4. [Explicación Detallada de Cada Sección](#4-explicación-detallada-de-cada-sección)
   - [4.1 CRUD de Productos](#41-crud-de-productos)
   - [4.2 KPIs de Inventario](#42-kpis-de-inventario)
   - [4.3 Catálogos](#43-catálogos)
   - [4.4 Movimientos de Stock](#44-movimientos-de-stock)
5. [Seguridad](#5-seguridad)
6. [Base de Datos](#6-base-de-datos)
7. [Preguntas Frecuentes del Profesor y Cómo Defender el Código](#7-preguntas-frecuentes-del-profesor-y-cómo-defender-el-código)

---

## 1. Descripción General

El archivo `crud_inventario.php` es el **modelo** del módulo de inventario del sistema **ZWL (Zona Web Lara)**. Una aplicación web hecha en PHP para gestionar un negocio.

**¿Qué hace este archivo?** Proporciona las funciones necesarias para que las páginas web puedan:

- **Crear, leer, actualizar y eliminar** productos del inventario.
- **Calcular indicadores** como total de productos, stock crítico, stock bajo y valor total.
- **Gestionar catálogos** de categorías, subcategorías, marcas y modelos.
- **Registrar entradas y salidas** de mercancía con una bitácora de auditoría.

### Ubicación en el proyecto

```
src/
├── Config/
│   └── database.php                ← Conexión a MySQL (crea $pdo)
├── Database/
│   ├── estructura.sql              ← SQL de las tablas
│   └── datos_prueba.sql            ← Datos para probar
└── app/
    ├── Controllers/
    │   └── inventarioController.php ← ★ Controlador AJAX (10 acciones)
    ├── Models/
    │   ├── crud_users.php           ← Modelo de usuarios
    │   ├── crud_asesorias.php       ← Modelo de asesorías
    │   └── crud_inventario.php      ← ★ Modelo de inventario
    ├── Views/
    │   └── inventario.php           ← Página del inventario
    └── core/
        └── router.php               ← Enrutador (rutea AJAX al controlador)
src/Public/js/
    └── app.inventario.js            ← ★ JavaScript de inventario (AJAX)
```

---

## 2. Arquitectura del Sistema

### 2.1 Patrón MVC (Modelo-Vista-Controlador)

La aplicación usa una arquitectura **MVC simple y artesanal** (sin framework):

| Capa | Qué hace | Archivo |
|------|----------|---------|
| **Modelo** | Se conecta a la BD y trae/guarda datos | `crud_inventario.php` |
| **Vista** | Muestra la interfaz al usuario | `Views/inventario.php` |
| **Controlador** | Procesa peticiones AJAX y coordina datos | `Controllers/inventarioController.php` |
| **JavaScript** | Interactividad frontend vía AJAX | `Public/js/app.inventario.js` |
| **Enrutador** | Desvía peticiones AJAX al controlador | `core/router.php` |

### 2.2 ¿Por qué no se usó un framework como Laravel?

En segundo año de ingeniería es importante **aprender los fundamentos** antes de usar herramientas que hacen todo automáticamente. Las ventajas de hacerlo así son:

1. **Aprendes SQL de verdad** — no dependes de que Eloquent (Laravel) genere las consultas por ti.
2. **Entiendes cómo funciona PDO** — la capa oficial de PHP para bases de datos.
3. **El código es transparente** — no hay magia, ves exactamente lo que hace cada línea.

---

## 3. Estructura del Archivo

```
crud_inventario.php
│
├── [require] database.php       ← Trae la conexión a MySQL
│
├── SECCIÓN 1: CRUD Productos    ← 6 funciones
│   ├── crearProducto()          → INSERT
│   ├── obtenerProductos()       → SELECT con JOIN
│   ├── obtenerProductoPorId()   → SELECT con filtro
│   ├── buscarProductos()        → SELECT con LIKE
│   ├── actualizarProducto()     → UPDATE
│   └── eliminarProducto()       → DELETE
│
├── SECCIÓN 2: KPIs              ← 4 funciones
│   ├── totalProductos()         → COUNT(*)
│   ├── stockCritico()           → COUNT con filtro
│   ├── stockBajo()             → COUNT con filtro
│   └── valorTotalInventario()   → SUM(stock * precio)
│
├── SECCIÓN 3: Catálogos         ← 4 funciones
│   ├── obtenerCategorias()
│   ├── obtenerSubcategorias()
│   ├── obtenerMarcas()
│   └── obtenerModelos()
│
└── SECCIÓN 4: Movimientos       ← 3 funciones
    ├── registrarEntrada()       → UPDATE stock + INSERT bitácora
    ├── registrarSalida()        → UPDATE stock + INSERT bitácora
    └── obtenerMovimientos()     → SELECT histórico
```

---

## 4. Explicación Detallada de Cada Sección

### 4.1 CRUD de Productos

#### `crearProducto($pdo, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta)`

**Qué hace:** Inserta un producto nuevo en la tabla `productos`.

```php
function crearProducto($pdo, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta) {
    $sql = "INSERT INTO productos (codigo, nombre, categoria_id, stock, stock_minimo, costo_compra, precio_venta) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta]);
    return $stmt;
}
```

**Explicación línea por línea:**
1. La función recibe la conexión `$pdo` (para hablar con la BD) y los datos del producto.
2. `$sql` contiene la consulta SQL. Los `?` son placeholders (marcadores de posición).
3. `prepare()` envía la consulta a MySQL para que la compile sin ejecutarla.
4. `execute()` mete los valores reales en los `?` y ejecuta la consulta.

**¿Por qué usar `?` en lugar de poner las variables directamente?**
Para evitar **inyección SQL**. Si un usuario malintencionado escribe `'; DROP TABLE productos; --` en el campo del código, con `?` MySQL lo trata como texto, no como código. Si concatenáramos strings, se ejecutaría como SQL y se borraría la tabla.

---

#### `obtenerProductos($pdo)`

**Qué hace:** Trae todos los productos activos con el nombre de su categoría.

```sql
SELECT p.*, c.nombre AS categoria
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
WHERE p.activo = TRUE
ORDER BY p.nombre
```

**Puntos clave:**
- **LEFT JOIN** asegura que si un producto no tiene categoría asignada, igual aparece en la lista.
- **WHERE activo = TRUE** filtra solo los productos activos (no eliminados).
- **ORDER BY p.nombre** los ordena alfabéticamente.

---

#### `obtenerProductoPorId($pdo, $id)`

**Qué hace:** Busca un producto específico por su ID.

Se usa `prepare()` + `execute([$id])` porque el ID llega desde la URL o un formulario y no debemos confiar en datos externos.

**Diferencia entre `fetch()` y `fetchAll()`:**
- `fetch()` devuelve **una sola fila**. Sirve cuando esperamos un solo resultado (como buscar por ID).
- `fetchAll()` devuelve **todas las filas**. Sirve para listados.

---

#### `buscarProductos($pdo, $termino)`

**Qué hace:** Busca productos cuyo nombre o código contengan el texto que el usuario escribió.

```php
$buscar = "%$termino%";
$stmt->execute([$buscar, $buscar]);
```

El `%` es un comodín de SQL. `%mouse%` significa: "cualquier texto que contenga 'mouse' en cualquier posición". Así, si el usuario escribe "mouse", encuentra "Mouse Inalámbrico" y también "Almohadilla para mouse".

---

#### `actualizarProducto($pdo, $id, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta)`

**Qué hace:** Cambia los datos de un producto existente.

Usa `UPDATE ... SET ... WHERE id = ?`. El `WHERE` es fundamental para no actualizar TODOS los productos de la tabla. Si olvidamos el `WHERE`, todos los productos quedarían con los mismos datos.

---

#### `eliminarProducto($pdo, $id)`

**Qué hace:** Borra un producto de la base de datos.

```sql
DELETE FROM productos WHERE id = ?
```

Esta es una **eliminación física**: el producto desaparece de la tabla. Esto tiene una desventaja: si el producto aparecía en ventas antiguas, esas ventas pierden la referencia al producto. Una forma más segura sería desactivarlo (soft delete: `UPDATE activo = FALSE`), pero para un proyecto de segundo año el DELETE directo es aceptable.

---

### 4.2 KPIs de Inventario

**KPI** significa *Key Performance Indicator* (Indicador Clave de Rendimiento). Son números que resumen cómo está el inventario.

| Indicador | Función | SQL que ejecuta |
|-----------|---------|-----------------|
| **Total de productos** | `totalProductos()` | `COUNT(*) WHERE activo = TRUE` |
| **Stock crítico** (sin stock) | `stockCritico()` | `COUNT(*) WHERE activo = TRUE AND stock <= 0` |
| **Stock bajo** (menos del mínimo) | `stockBajo()` | `COUNT(*) WHERE activo = TRUE AND stock > 0 AND stock <= stock_minimo` |
| **Valor total** del inventario | `valorTotalInventario()` | `SUM(stock * precio_venta) WHERE activo = TRUE` |

**Detalle de `valorTotalInventario()`:**
```php
return $fila['total'] ? $fila['total'] : 0;
```
Esta línea es un **operador ternario**. Significa: "si `$fila['total']` tiene un valor, devuélvelo; si no, devuelve 0". Se usa porque `SUM()` devuelve `NULL` cuando no hay productos, y si devolviéramos `NULL` la página podría mostrar un error o quedar en blanco.

---

### 4.3 Catálogos (Categorías, Subcategorías, Marcas, Modelos)

Estas funciones son muy simples porque solo traen datos de tablas pequeñas que se usan para llenar los combos/selects de los formularios.

```
Ejemplo de jerarquía:
  Subcategoría: "Informática"
    └── Categoría: "Periféricos", "Pantallas", "Almacenamiento"
  Marca: "Logitech"
    └── Modelo: "G203", "G403", "Pro X"
```

Todas usan `SELECT *` que trae todas las columnas de la tabla. En tablas pequeñas esto no es problema, pero en tablas grandes es mejor especificar solo las columnas que necesitamos.

---

### 4.4 Movimientos de Stock

Esta sección lleva el **control de todo lo que entra y sale** del inventario. Cada movimiento queda registrado en una bitácora (tabla `bitacora_movimientos_stock`) para poder auditar después qué pasó.

#### `registrarEntrada($pdo, $producto_id, $cantidad, $usuario_id, $motivo)`

**Qué hace:** Cuando llega mercancía nueva, suma al stock y lo anota en la bitácora.

**Paso a paso:**
1. Busca el producto para saber cuánto stock tiene actualmente.
2. Si el producto no existe, termina y devuelve `false`.
3. Calcula el nuevo stock: `stock_anterior + cantidad`.
4. Actualiza el stock en la tabla `productos`.
5. Registra el movimiento en la bitácora con tipo `'entrada'`.

#### `registrarSalida($pdo, $producto_id, $cantidad, $usuario_id, $motivo)`

**Qué hace:** Cuando se vende o usa un producto, resta del stock y lo anota.

**Diferencia clave con registrarEntrada:**
- En lugar de sumar, **resta**: `stock_nuevo = stock_anterior - cantidad`
- **Valida que el stock no quede negativo:** `if ($stock_nuevo < 0) return false;`

**¿Por qué no dejar que el stock quede en negativo?**
Porque no se puede vender algo que no existe. Si el stock llega a -3, significa que debemos 3 unidades, lo cual no tiene sentido en un inventario real.

**Problema de esta implementación (para tener en cuenta):**

Si dos usuarios venden el mismo producto al mismo tiempo:
1. Usuario A lee stock = 10, resta 1, calcula 9.
2. Usuario B lee stock = 10 (porque A aún no guardó), resta 1, calcula 9.
3. Ambos guardan stock = 9. **Se perdió 1 unidad.**

En un sistema real esto se soluciona con transacciones y bloqueos (`FOR UPDATE`), pero para un proyecto de segundo año esta implementación simple es aceptable.

#### `obtenerMovimientos($pdo, $producto_id)`

**Qué hace:** Muestra el historial de movimientos de un producto.

```sql
SELECT b.*, u.nombre AS usuario
FROM bitacora_movimientos_stock b
LEFT JOIN usuarios u ON b.usuario_id = u.id
WHERE b.producto_id = ?
ORDER BY b.fecha DESC
```

**LEFT JOIN con usuarios** para mostrar el nombre de la persona que hizo el movimiento.
**ORDER BY b.fecha DESC** para mostrar los más recientes primero.

---

## 5. Seguridad

### 5.1 Inyección SQL

El ataque más común a bases de datos. Consiste en escribir código SQL malicioso en los campos de entrada.

**Ejemplo de ataque:**
Si el código hiciera:
```php
$sql = "SELECT * FROM productos WHERE id = " . $_GET['id'];
```
Un atacante podría poner en la URL: `?id=1; DROP TABLE productos;--`

**Cómo lo evitamos:** Usando **sentencias preparadas** con placeholders `?` en **todas** las funciones:
```php
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
```

PDO separa la estructura SQL de los datos, por lo que cualquier texto malicioso se trata como texto, no como código.

### 5.2 Configuración de PDO

En `Config/database.php`:
```php
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
PDO::ATTR_EMULATE_PREPARES => false
```

- `ERRMODE_EXCEPTION`: Si hay un error SQL, lanza una excepción (permite manejarlo con try-catch).
- `FETCH_ASSOC`: Los resultados se devuelven como arreglos asociativos (`$fila['nombre']`).
- `EMULATE_PREPARES => false`: Usa prepared statements reales de MySQL, no emulados por PHP.

### 5.3 Parámetro `$pdo`

Todas las funciones reciben `$pdo` como parámetro en lugar de usar `global $pdo`. Esto es mejor porque:
- La función no depende de una variable global que podría haber sido cambiada.
- Se puede pasar una conexión diferente (útil para pruebas).
- Es más fácil de entender qué necesita la función.

---

## 6. Base de Datos

### 6.1 Tablas que usa el modelo

| Tabla | Tipo | Descripción |
|-------|------|-------------|
| `productos` | Principal | Guarda todos los productos del inventario |
| `categorias` | Catálogo | Clasificación de productos |
| `subcategorias` | Catálogo | Agrupación de categorías |
| `marcas` | Catálogo | Marcas de los productos |
| `modelos` | Catálogo | Modelos específicos de cada marca |
| `bitacora_movimientos_stock` | Auditoría | Historial de entradas y salidas |
| `usuarios` | Relacionada | Usuarios del sistema (para saber quién hizo cada movimiento) |

### 6.2 Estructura de la tabla `productos`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT (auto_increment) | Identificador único |
| `codigo` | VARCHAR(50) | Código del producto (ej: "TEC-001") |
| `nombre` | VARCHAR(150) | Nombre del producto |
| `categoria_id` | INT | Categoría a la que pertenece |
| `stock` | INT | Cantidad en inventario |
| `stock_minimo` | INT | Mínimo antes de alertar |
| `costo_compra` | DECIMAL(12,2) | Precio de compra |
| `precio_venta` | DECIMAL(12,2) | Precio de venta |
| `activo` | BOOLEAN | Si está activo o fue eliminado |

### 6.3 Normalización

Las tablas están en **3ra Forma Normal (3FN)**:
- No hay datos repetidos (el nombre de la categoría está una vez en `categorias`, no en cada producto).
- Cada tabla tiene una clave primaria.
- Las relaciones se hacen con llaves foráneas (`categoria_id` → `categorias.id`).

---

## 7. Preguntas Frecuentes del Profesor y Cómo Defender el Código

### Pregunta 1: "¿Por qué no usaste programación orientada a objetos?"

> Elegí usar **funciones** en lugar de clases porque es más simple y directo. El proyecto no necesita herencia ni polimorfismo. Cada función hace una tarea específica y se entiende fácilmente. Además, el archivo `composer.json` ya tiene configurado el namespace `App\Models` con PSR-4, así que en el futuro se podría migrar a clases sin problemas.

---

### Pregunta 2: "¿Qué son los `?` en las consultas SQL?"

> Son **placeholders** o marcadores de posición. Se usan en las **sentencias preparadas** de PDO. La consulta se envía a MySQL con los `?` y luego se envían los valores por separado con `execute()`. Esto evita la **inyección SQL**, que es cuando un atacante escribe código SQL malicioso en un campo de texto. Con los placeholders, cualquier cosa que escriba el usuario se trata como texto, no como código.

---

### Pregunta 3: "¿Qué diferencia hay entre `query()` y `prepare()`?"

> `query()` se usa para consultas **sin parámetros variables**, como `obtenerProductos()` que siempre trae todos los productos.
>
> `prepare()` + `execute()` se usa para consultas con **datos que vienen del usuario**, como `obtenerProductoPorId($id)`. Primero se prepara la estructura SQL y luego se pasan los valores, lo que es más seguro.

---

### Pregunta 4: "¿Qué puede salir mal si dos usuarios venden al mismo tiempo?"

> Si dos usuarios venden el mismo producto simultáneamente, puede ocurrir una **condición de carrera**. Por ejemplo:
> 1. Usuario A lee stock = 10, resta 1, calcula 9.
> 2. Usuario B lee stock = 10 (antes de que A guarde), resta 1, calcula 9.
> 3. Ambos guardan stock = 9.
>
> **Resultado:** Debería ser 8 pero quedó 9. Se perdió 1 unidad.
>
> En un sistema real esto se resuelve con **transacciones** (`BEGIN TRANSACTION` / `COMMIT`) y **bloqueo de filas** (`SELECT ... FOR UPDATE`). Para un proyecto de segundo año, la implementación actual es aceptable porque la probabilidad de que dos usuarios vendan el mismo producto exactamente al mismo tiempo es baja en un negocio pequeño.

---

### Pregunta 5: "¿Por qué `registrarEntrada` y `registrarSalida` son funciones separadas?"

> Porque tienen lógica diferente:
> - `registrarEntrada()` **suma** la cantidad al stock.
> - `registrarSalida()` **resta** la cantidad y además **verifica** que el stock no quede negativo.
>
> Si las unificáramos en una sola función, tendríamos que pasar un parámetro extra para indicar si es entrada o salida, y el código sería más difícil de entender.

---

### Pregunta 6: "¿Por qué algunas funciones usan `return $stmt` y otras `return true`?"

> Las funciones del CRUD básico (`crearProducto`, `actualizarProducto`, `eliminarProducto`) devuelven el objeto `$stmt`, que se puede evaluar como `true` o `false`.
>
> Las funciones de movimientos (`registrarEntrada`, `registrarSalida`) devuelven `true` o `false` explícitamente, porque tienen múltiples consultas y necesitamos controlar el flujo (por ejemplo, si el producto no existe, devolvemos `false` sin ejecutar el resto).

---

### Pregunta 7: "¿Por qué usas `require_once` al inicio?"

> `require_once` incluye el archivo `database.php` que contiene la conexión a MySQL. El `_once` evita que se incluya más de una vez, lo que causaría un error porque la variable `$pdo` ya estaría definida.

---

### Pregunta 8: "En `buscarProductos`, el `%` se concatena directamente con `$termino`. ¿No es peligroso?"

> Sí y no. El `%` se concatena con `$termino` **antes** de pasarlo a `execute()`, pero `$termino` no se inyecta directamente en el SQL. Pasa por el placeholder `?`, así que MySQL lo trata como un valor, no como código. Aun así, es mejor práctica usar `CONCAT('%', ?, '%')` dentro del SQL, pero para el nivel del proyecto esto funciona correctamente.

---

### Pregunta 9: "¿Cómo agregarías paginación a `obtenerProductos()`?"

> Agregando dos parámetros: `$offset` (desde dónde empezar) y `$limite` (cuántos traer). La consulta quedaría:
>
> ```sql
> SELECT ... ORDER BY p.nombre LIMIT ? OFFSET ?
> ```
>
> Y se usaría así:
> ```php
> $pagina = $_GET['pagina'] ?? 1;
> $productos = obtenerProductos($pdo, ($pagina-1) * 50, 50);
> ```

---

### Pregunta 10: "¿Por qué `valorTotalInventario()` usa un operador ternario?"

> Porque `SUM()` en SQL devuelve `NULL` si no hay productos que coincidan con el `WHERE`. Si devolviéramos `NULL` a la vista, podría mostrar un error o quedar en blanco. El ternario `$fila['total'] ? $fila['total'] : 0` garantiza que siempre devolvemos un número, aunque sea cero.

---

## 8. Arquitectura Completa del Módulo

### El Controlador (`inventarioController.php`)

Actúa como **puente entre el JavaScript y el modelo**. Cuando `app.inventario.js` hace una petición AJAX, el router detecta `?pagina=inventario&action=accion` y carga este controlador.

**Las 10 acciones que maneja:**

| Acción | Método | Descripción |
|--------|--------|-------------|
| `listar` | GET | Obtiene todos los productos activos |
| `kpis` | GET | Calcula total, crítico, bajo y valor |
| `detalle&id=X` | GET | Obtiene un producto por su ID |
| `movimientos&id=X` | GET | Historial de movimientos de un producto |
| `buscar` | POST | Busca productos por nombre o código |
| `crear` | POST | Crea un producto nuevo |
| `actualizar` | POST | Actualiza un producto existente |
| `eliminar` | POST | Elimina un producto |
| `entrada` | POST | Registra entrada de stock |
| `salida` | POST | Registra salida de stock |

Todas las acciones devuelven JSON con la estructura `{success: true/false, data/message/error}`.

### El JavaScript (`app.inventario.js`)

Corre en el navegador y proporciona la interactividad del módulo:

- **refrescarKPI()** — Actualiza las 4 tarjetas de indicadores vía AJAX
- **refrescarTabla()** — Recarga la tabla de productos sin recargar la página
- **aplicarFiltro()** — Filtra por texto y estado en el lado del cliente
- **abrirModalProducto()** — Abre el modal de crear/editar producto
- **abrirModalMovimientos()** — Abre el modal con historial de movimientos
- **abrirModalStock()** — Abre el modal de entrada/salida de stock

Todas las operaciones CRUD se realizan mediante peticiones AJAX a `?pagina=inventario&action=...`.

### Flujo completo de una petición

```
Usuario hace clic en "Guardar" (nuevo producto)
  → app.inventario.js captura el submit
  → Envía POST a ?pagina=inventario&action=crear
  → router.php detecta action y carga inventarioController.php
  → Controlador valida datos y llama a crearProducto() (crud_inventario.php)
  → Modelo ejecuta INSERT en MySQL
  → Controlador devuelve JSON {success: true}
  → JavaScript recibe respuesta, muestra toast, refresca tabla y KPIs
```
