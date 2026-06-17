# Documentación del Módulo de Inventario

## Índice

1. [Descripción General](#1-descripción-general)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Estructura de Archivos](#3-estructura-de-archivos)
4. [Explicación Detallada del Modelo](#4-explicación-detallada-del-modelo)
   - [4.1 CRUD de Productos](#41-crud-de-productos)
   - [4.2 KPIs de Inventario](#42-kpis-de-inventario)
   - [4.3 Catálogos](#43-catálogos)
   - [4.4 Movimientos de Stock](#44-movimientos-de-stock)
5. [El Controlador (API JSON)](#5-el-controlador-api-json)
6. [La Vista (Interfaz de Usuario)](#6-la-vista-interfaz-de-usuario)
7. [JavaScript (Cliente AJAX)](#7-javascript-cliente-ajax)
8. [Seguridad](#8-seguridad)
9. [Base de Datos](#9-base-de-datos)
10. [Preguntas Frecuentes del Profesor y Cómo Defender el Código](#10-preguntas-frecuentes-del-profesor-y-cómo-defender-el-código)

---

## 1. Descripción General

El módulo de inventario del sistema **ZWL (Zona Web Lara)** está implementado con el modelo **POO**:

| Archivo | Enfoque | Ubicación |
|---------|---------|-----------|
| `inventario.php` | **POO (Programación Orientada a Objetos)** — clase con namespace | `src/app/Models/inventario.php` |

> 📝 **Novedad:** El archivo ha sido completamente comentado **línea por línea** en español. Cada sentencia SQL, cada método, cada parámetro y cada estructura de control tiene su comentario explicativo, lo que facilita el estudio del código para fines académicos.

**¿Qué hace este módulo?** Proporciona las funciones necesarias para que las páginas web puedan:

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
    │   └── InventarioController.php ← ★ Controlador AJAX (clase, 11 acciones)
    ├── Models/
    │   └── inventario.php           ← ★ Modelo POO
    │   ├── crud_users.php           ← Modelo de usuarios
    │   └── crud_asesorias.php       ← Modelo de asesorías
    ├── Views/
    │   └── inventario.php           ← Página del inventario
    └── core/
        └── router.php               ← Enrutador (rutea AJAX al controlador)
src/Public/js/
    └── app.inventario.js            ★ JavaScript de inventario (AJAX)


---

## 2. Arquitectura del Sistema

### 2.1 Patrón MVC (Modelo-Vista-Controlador)

La aplicación usa una arquitectura **MVC simple y artesanal** (sin framework completo, aunque usa Composer con PSR-4):

| Capa | Qué hace | Archivo |
|------|----------|---------|
| **Modelo** | Clase POO con métodos, type hints y `$this->db` | `Models/inventario.php` |
| **Vista** | Muestra la interfaz al usuario (usa el modelo POO) | `Views/inventario.php` |
| **Controlador** | Clase que procesa peticiones AJAX con `match()` | `Controllers/InventarioController.php` |
| **JavaScript** | Interactividad frontend vía AJAX | `Public/js/app.inventario.js` |
| **Enrutador** | Desvía peticiones AJAX al controlador | `core/router.php` |

### 2.2 Implementación POO

El modelo está implementado con POO (`inventario.php`):

- Usa **namespace** `App\Models` y extiende una clase base `Model`.
- Tiene **type hints** en todos los parámetros y retornos (`int`, `string`, `float`, `bool`, `array`).
- Accede a la BD mediante `$this->db` heredado de la clase `Model`.
- El **controlador** también es POO: clase `InventarioController` en `App\Controllers` que instancia `new inventario()` y usa `match()` (PHP 8).

### 2.3 ¿Por qué no se usó un framework como Laravel?

En segundo año de ingeniería es importante **aprender los fundamentos** antes de usar herramientas que hacen todo automáticamente. Las ventajas de hacerlo así son:

1. **Aprendes SQL de verdad** — no dependes de que Eloquent (Laravel) genere las consultas por ti.
2. **Entiendes cómo funciona PDO** — la capa oficial de PHP para bases de datos.
3. **El código es transparente** — no hay magia, ves exactamente lo que hace cada línea.

---

## 3. Estructura de Archivos

### 3.1 Modelo POO: `inventario.php`

```
namespace App\Models;
class inventario extends Model
│
├── Propiedades
│   └── $db (heredada de Model)
│
├── SECCIÓN 1: CRUD Productos    ← 6 métodos
│   ├── crearProducto(...): bool           → INSERT
│   ├── obtenerProductos(): array          → SELECT con JOIN
│   ├── obtenerProductoPorId(int): array|false → SELECT con filtro
│   ├── buscarProductos(string): array     → SELECT con LIKE
│   ├── actualizarProducto(...): bool      → UPDATE
│   └── eliminarProducto(int): bool        → DELETE
│
├── SECCIÓN 2: KPIs              ← 4 métodos
│   ├── totalProductos(): int              → COUNT(*)
│   ├── stockCritico(): int                → COUNT con filtro
│   ├── stockBajo(): int                   → COUNT con filtro
│   └── valorTotalInventario(): float      → SUM(stock * precio)
│
├── SECCIÓN 3: Catálogos         ← 4 métodos
│   ├── obtenerCategorias(): array
│   ├── obtenerSubcategorias(): array
│   ├── obtenerMarcas(): array
│   └── obtenerModelos(): array
│
└── SECCIÓN 4: Movimientos       ← 3 métodos
    ├── registrarEntrada(...): bool         → UPDATE stock + INSERT bitácora
    ├── registrarSalida(...): bool          → UPDATE stock + INSERT bitácora
    └── obtenerMovimientos(int): array      → SELECT histórico
```

---

## 4. Explicación Detallada del Modelo

> A continuación se explica el modelo **POO** (`inventario.php`), que es la implementación actual del módulo de inventario.
>
> 🔍 **Importante:** El archivo contiene comentarios exhaustivos **línea por línea** directamente en el código fuente. Se recomienda revisar `src/app/Models/inventario.php` para ver la explicación detallada de cada sentencia SQL, parámetro y estructura de control.

### 4.1 CRUD de Productos

#### `crearProducto(string $codigo, string $nombre, int $categoria_id, int $stock, int $stock_minimo, float $costo_compra, float $precio_venta): bool`

**Qué hace:** Inserta un producto nuevo en la tabla `productos`.

```php
public function crearProducto(string $codigo, string $nombre, int $categoria_id, int $stock, int $stock_minimo, float $costo_compra, float $precio_venta): bool
{
    $sql = "INSERT INTO productos (codigo, nombre, categoria_id, stock, stock_minimo, costo_compra, precio_venta) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta]);
}
```

**Explicación:**
1. Recibe los datos del producto con type hints estrictos (`string`, `int`, `float`).
2. `$sql` contiene la consulta SQL. Los `?` son placeholders (marcadores de posición).
3. `prepare()` envía la consulta a MySQL para que la compile sin ejecutarla.
4. `execute()` reemplaza los `?` con los valores reales y ejecuta. Retorna `bool`.

**¿Por qué usar `?` en lugar de poner las variables directamente?**
Para evitar **inyección SQL**. Si un usuario malintencionado escribe `'; DROP TABLE productos; --` en el campo del código, con `?` MySQL lo trata como texto, no como código.

---

#### `obtenerProductos(): array`

**Qué hace:** Trae todos los productos activos con el nombre de su categoría.

```php
public function obtenerProductos(): array
{
    $stmt = $this->db->query("SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.activo = TRUE ORDER BY p.nombre");
    return $stmt->fetchAll();
}
```

**Puntos clave del SQL:**
- **LEFT JOIN** asegura que si un producto no tiene categoría asignada, igual aparece en la lista.
- **WHERE activo = TRUE** filtra solo los productos activos (no eliminados).
- **ORDER BY p.nombre** los ordena alfabéticamente.

---

#### `obtenerProductoPorId(int $id): array|false`

**Qué hace:** Busca un producto específico por su ID.

```php
public function obtenerProductoPorId(int $id): array|false
{
    $sql = "SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}
```

Se usa `prepare()` + `execute([$id])` porque el ID llega desde la URL o un formulario y no debemos confiar en datos externos.

**Diferencia entre `fetch()` y `fetchAll()`:**
- `fetch()` devuelve **una sola fila**. Sirve cuando esperamos un solo resultado (como buscar por ID).
- `fetchAll()` devuelve **todas las filas**. Sirve para listados.

---

#### `buscarProductos(string $termino): array`

**Qué hace:** Busca productos cuyo nombre o código contengan el texto que el usuario escribió.

```php
public function buscarProductos(string $termino): array
{
    $sql = "SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.activo = TRUE AND (p.nombre LIKE ? OR p.codigo LIKE ?) ORDER BY p.nombre";
    $stmt = $this->db->prepare($sql);
    $buscar = "%$termino%";
    $stmt->execute([$buscar, $buscar]);
    return $stmt->fetchAll();
}
```

El `%` es un comodín de SQL. `%mouse%` significa: "cualquier texto que contenga 'mouse' en cualquier posición". Así, si el usuario escribe "mouse", encuentra "Mouse Inalámbrico" y también "Almohadilla para mouse".

---

#### `actualizarProducto(int $id, string $codigo, string $nombre, int $categoria_id, int $stock, int $stock_minimo, float $costo_compra, float $precio_venta): bool`

**Qué hace:** Cambia los datos de un producto existente.

```php
public function actualizarProducto(int $id, string $codigo, string $nombre, int $categoria_id, int $stock, int $stock_minimo, float $costo_compra, float $precio_venta): bool
{
    $sql = "UPDATE productos SET codigo = ?, nombre = ?, categoria_id = ?, stock = ?, stock_minimo = ?, costo_compra = ?, precio_venta = ? WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $id]);
}
```

Usa `UPDATE ... SET ... WHERE id = ?`. El `WHERE` es fundamental para no actualizar TODOS los productos de la tabla. Si olvidamos el `WHERE`, todos los productos quedarían con los mismos datos.

---

#### `eliminarProducto(int $id): bool`

**Qué hace:** Borra un producto de la base de datos.

```php
public function eliminarProducto(int $id): bool
{
    $sql = "DELETE FROM productos WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$id]);
}
```

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

Estos métodos son muy simples porque solo traen datos de tablas pequeñas que se usan para llenar los combos/selects de los formularios.

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

#### `registrarEntrada(int $producto_id, int $cantidad, int $usuario_id, string $motivo): bool`

**Qué hace:** Cuando llega mercancía nueva, suma al stock y lo anota en la bitácora.

**Paso a paso:**
1. Busca el producto para saber cuánto stock tiene actualmente.
2. Si el producto no existe, termina y devuelve `false`.
3. Calcula el nuevo stock: `stock_anterior + cantidad`.
4. Actualiza el stock en la tabla `productos`.
5. Registra el movimiento en la bitácora con tipo `'entrada'`.

#### `registrarSalida(int $producto_id, int $cantidad, int $usuario_id, string $motivo): bool`

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

#### `obtenerMovimientos(int $producto_id): array`

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

## 5. El Controlador (API JSON)

### `InventarioController.php`

**Ubicación:** `src/app/Controllers/InventarioController.php`

Actúa como **puente entre el JavaScript y el modelo**. Cuando `app.inventario.js` hace una petición AJAX, el router detecta `?pagina=inventario&action=accion` y carga este controlador.

El controlador está implementado como una **clase** con namespace `App\Controllers` que instancia el modelo POO:

```php
namespace App\Controllers;
use App\Models\inventario;

class InventarioController
{
    private inventario $model;

    public function __construct()
    {
        $this->model = new inventario();
    }
    // ...
}
```

### Las 11 acciones que maneja

| Acción | Método HTTP | Descripción | Respuesta |
|--------|-------------|-------------|-----------|
| `listar` | GET | Obtiene todos los productos activos | `{success, data: [...]}` |
| `kpis` | GET | Calcula total, crítico, bajo y valor | `{success, data: {total, critico, bajo, valor}}` |
| `categorias` | GET | Obtiene lista de categorías (para selects) | `{success, data: [...]}` |
| `detalle&id=X` | GET | Obtiene un producto por su ID | `{success, data: {...}}` |
| `movimientos&id=X` | GET | Historial de movimientos de un producto | `{success, data: [...]}` |
| `buscar` | POST | Busca productos por nombre o código | `{success, data: [...]}` |
| `crear` | POST | Crea un producto nuevo | `{success, message}` |
| `actualizar` | POST | Actualiza un producto existente | `{success, message}` |
| `eliminar` | POST | Elimina un producto | `{success, message}` |
| `entrada` | POST | Registra entrada de stock | `{success, message}` |
| `salida` | POST | Registra salida de stock | `{success, message}` |

> **Novedad:** La acción `categorias` es nueva respecto a versiones anteriores y permite cargar las categorías dinámicamente sin recargar la página.

### Estructura del código

```php
public function handle(): void
{
    header('Content-Type: application/json');
    $action = $_GET['action'] ?? '';

    try {
        match ($action) {                  // PHP 8: match en lugar de switch
            'listar'      => $this->listar(),
            'kpis'        => $this->kpis(),
            'categorias'  => $this->categorias(),
            'detalle'     => $this->detalle(),
            'movimientos' => $this->movimientos(),
            'buscar'      => $this->buscar(),
            'crear'       => $this->crear(),
            'actualizar'  => $this->actualizar(),
            'eliminar'    => $this->eliminar(),
            'entrada'     => $this->entrada(),
            'salida'      => $this->salida(),
            default       => $this->json(false, null, 'Acción no válida'),
        };
    } catch (\PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error de BD: ' . $e->getMessage()]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
    }
}
```

**Diferencias con la versión anterior (procedural):**
- Usa **`match()`** de PHP 8 en lugar de `switch`.
- Los casos llaman a **métodos privados** (`$this->listar()`) en lugar de ejecutar código inline.
- Incluye un **manejador de excepciones** con try-catch separado para `PDOException` y `Exception`.
- Tiene un método auxiliar `json()` para construir respuestas uniformes.
- Incluye validación de datos antes de llamar al modelo.

---

## 6. La Vista (Interfaz de Usuario)

### `inventario.php`

**Ubicación:** `src/app/Views/inventario.php`

Actualmente la vista usa el **modelo POO** directamente para cargar los datos iniciales:

```php
use App\Models\inventario;
$inventarioModel = new inventario();

$productos = $inventarioModel->obtenerProductos();
$totalP    = $inventarioModel->totalProductos();
$critico   = $inventarioModel->stockCritico();
$bajo      = $inventarioModel->stockBajo();
$valor     = $inventarioModel->valorTotalInventario();
$categorias = $inventarioModel->obtenerCategorias();
```

### Secciones de la página:

```
┌──────────────────────────────────────────────────────────────┐
│  KPI 1 (Total)  │  KPI 2 (Crítico)  │  KPI 3 (Bajo)  │  KPI 4 (Valor)  │
├──────────────────────────────────────────────────────────────┤
│  [🔍 Buscar producto...]  [Estado: ▼]  [➕ Nuevo Producto]  │
├──────────────────────────────────────────────────────────────┤
│  Lista de Productos                                   12 prod.│
├────────┬────────┬────────┬──────────┬──────────┬──────────────┤
│Producto│  ID    │ Precio │  Stock   │ Estado   │    Acción     │
├────────┼────────┼────────┼──────────┼──────────┼──────────────┤
│ 🟢Mouse│ M-001  │ $15.00 │ ██░░ 12  │ OK       │[📋][✏️][➕][➖][🗑️]│
│ 🔴Tecla│ T-002  │ $25.00 │ ██░░  2  │ Crítico  │[📋][✏️][➕][➖][🗑️]│
│ 🔴Monit│ M-003  │$120.00 │ ░░░░  0  │ Sin stock│[📋][✏️][➕][➖][🗑️]│
└────────┴────────┴────────┴──────────┴──────────┴──────────────┘
```

### Modales incluidos:

1. **Modal de producto** → formulario para crear o editar (con campos: código, nombre, categoría, stock, stock mínimo, costo, precio).
2. **Modal de movimientos** → tabla con historial de entradas/salidas (fecha, tipo, cantidad, stock anterior, stock nuevo, usuario, motivo).
3. **Modal de stock** → formulario de entrada o salida (cantidad, motivo), con botón dinámico según el tipo.

---

## 7. JavaScript (Cliente AJAX)

### `app.inventario.js`

**Ubicación:** `src/Public/js/app.inventario.js`

Corre en el navegador y proporciona interactividad completa sin recargar la página. Usa **jQuery** y se comunica con el controlador mediante AJAX.

### Funciones principales

| Función | ¿Qué hace? | ¿Cuándo se ejecuta? |
|---------|------------|---------------------|
| `refrescarKPI()` | Actualiza las 4 tarjetas de indicadores vía AJAX | Después de cualquier cambio (crear, editar, eliminar, entrada, salida) |
| `refrescarTabla()` | Recarga toda la tabla de productos desde el servidor | Después de cualquier cambio |
| `aplicarFiltro()` | Filtra filas visibles por texto y estado (lado cliente) | Al escribir en búsqueda o cambiar filtro |
| `abrirModalProducto(titulo, datos)` | Abre modal de producto (vacío para nuevo o precargado para editar) | Botón "Nuevo" o "Editar" |
| `abrirModalMovimientos(id, nombre)` | Abre modal con historial de movimientos vía AJAX | Botón de movimientos |
| `abrirModalStock(tipo, id, nombre)` | Abre modal de entrada/salida de stock | Botón "Entrada" o "Salida" |

### Detalles de implementación

- **URL base:** `var API = '?pagina=inventario&action=';`
- **Toast:** Usa `EIS.toast()` (función global del sistema) en lugar de `M.toast()` de Materialize.
- **Tooltips:** Se reinician después de cada recarga de tabla con `$('.tooltipped').tooltip();`.
- **Debounce:** El filtro de búsqueda usa `debounce(..., 300)` para no filtrar en cada tecla.
- **XSS prevention:** Usa `$('<span>').text(valor).html()` para escapar texto antes de insertarlo en el HTML.
- **Botones por fila:** Cada producto tiene 5 botones: movimientos (gris), editar (índigo), entrada (verde), salida (naranja), eliminar (rojo).

### Flujo de una petición (ej: crear producto)

```
Usuario hace clic en "Guardar" (nuevo producto)
  → app.inventario.js captura el submit del formulario
  → Previene el envío tradicional (e.preventDefault())
  → Determina si es crear o actualizar (según si hay ID)
  → Serializa el formulario con $(this).serialize()
  → Envía POST a ?pagina=inventario&action=crear (o actualizar)
  → router.php detecta action y carga InventarioController
  → Controlador valida datos y llama a $model->crearProducto()
  → Modelo POO ejecuta INSERT en MySQL
  → Controlador devuelve JSON {success: true, message: "..."}
  → JavaScript recibe respuesta:
      → Muestra toast verde con EIS.toast()
      → Cierra el modal
      → Llama a refrescarTabla() y refrescarKPI()
```

---

## 8. Seguridad

### 8.1 Inyección SQL

El ataque más común a bases de datos. Consiste en escribir código SQL malicioso en los campos de entrada.

**Ejemplo de ataque:**
Si el código hiciera:
```php
$sql = "SELECT * FROM productos WHERE id = " . $_GET['id'];
```
Un atacante podría poner en la URL: `?id=1; DROP TABLE productos;--`

**Cómo lo evitamos:** Usando **sentencias preparadas** con placeholders `?` en **todas** las funciones:
```php
// Versión procedural
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);

// Versión POO
$stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
```

PDO separa la estructura SQL de los datos, por lo que cualquier texto malicioso se trata como texto, no como código.

### 8.2 Configuración de PDO

En `Config/database.php`:
```php
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
PDO::ATTR_EMULATE_PREPARES => false
```

- `ERRMODE_EXCEPTION`: Si hay un error SQL, lanza una excepción (permite manejarlo con try-catch).
- `FETCH_ASSOC`: Los resultados se devuelven como arreglos asociativos (`$fila['nombre']`).
- `EMULATE_PREPARES => false`: Usa prepared statements reales de MySQL, no emulados por PHP.

### 8.3 Parámetro `$pdo` vs `$this->db`

**Modelo procedural:** Todas las funciones reciben `$pdo` como parámetro en lugar de usar `global $pdo`. Esto es mejor porque:
- La función no depende de una variable global que podría haber sido cambiada.
- Se puede pasar una conexión diferente (útil para pruebas).
- Es más fácil de entender qué necesita la función.

**Modelo POO:** La conexión se asigna en el constructor de la clase base `Model` y se accede mediante `$this->db`.

### 8.4 XSS (Cross-Site Scripting)

Tanto la vista como el JavaScript protegen contra XSS:
- **PHP:** `htmlspecialchars($texto)` escapa caracteres HTML (`<`, `>`, `"`, `&`) antes de imprimir datos del usuario.
- **JavaScript:** `$('<span>').text(valor).html()` crea un elemento temporal con jQuery, asigna el texto de forma segura, y luego obtiene el HTML escapado.

---

## 9. Base de Datos

### 9.1 Tablas que usa el modelo

| Tabla | Tipo | Descripción |
|-------|------|-------------|
| `productos` | Principal | Guarda todos los productos del inventario |
| `categorias` | Catálogo | Clasificación de productos |
| `subcategorias` | Catálogo | Agrupación de categorías |
| `marcas` | Catálogo | Marcas de los productos |
| `modelos` | Catálogo | Modelos específicos de cada marca |
| `bitacora_movimientos_stock` | Auditoría | Historial de entradas y salidas |
| `usuarios` | Relacionada | Usuarios del sistema (para saber quién hizo cada movimiento) |

### 9.2 Estructura de la tabla `productos`

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

### 9.3 Normalización

Las tablas están en **3ra Forma Normal (3FN)**:
- No hay datos repetidos (el nombre de la categoría está una vez en `categorias`, no en cada producto).
- Cada tabla tiene una clave primaria.
- Las relaciones se hacen con llaves foráneas (`categoria_id` → `categorias.id`).

---

## 10. Preguntas Frecuentes del Profesor y Cómo Defender el Código

### Pregunta 1: "¿Qué modelo usa el módulo de inventario?"

> Actualmente el módulo usa el modelo **`inventario.php`** (POO), una clase con namespace `App\Models`, type hints, herencia de `Model` y comentarios línea por línea en español.

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

### Pregunta 5: "¿Por qué `registrarEntrada` y `registrarSalida` son métodos separados?"

> Porque tienen lógica diferente:
> - `registrarEntrada()` **suma** la cantidad al stock.
> - `registrarSalida()` **resta** la cantidad y además **verifica** que el stock no quede negativo.
>
> Si las unificáramos en un solo método, tendríamos que pasar un parámetro extra para indicar si es entrada o salida, y el código sería más difícil de entender.

---

### Pregunta 6: "¿Qué retorna cada método del modelo?"

> Los métodos de escritura (`crearProducto`, `actualizarProducto`, `eliminarProducto`, `registrarEntrada`, `registrarSalida`) devuelven `bool`: `true` si la operación fue exitosa, `false` si falló.
>
> Los métodos de lectura devuelven `array` (lista de resultados) o `array|false` (un solo resultado o `false` si no existe).

---

### Pregunta 7: "¿Cómo se conecta el modelo POO a la base de datos?"

> La clase `inventario` extiende la clase base `Model` (namespace `App\Core\Model`). El constructor de `Model` establece la conexión PDO y la asigna a `$this->db`. Así todas las subclases heredan la conexión sin tener que incluir `database.php` manualmente.

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
> $productos = $modelo->obtenerProductos(($pagina-1) * 50, 50);
> ```

---

### Pregunta 10: "¿Por qué `valorTotalInventario()` usa un operador ternario?"

> Porque `SUM()` en SQL devuelve `NULL` si no hay productos que coincidan con el `WHERE`. Si devolviéramos `NULL` a la vista, podría mostrar un error o quedar en blanco. El ternario `$fila['total'] ? $fila['total'] : 0` garantiza que siempre devolvemos un número, aunque sea cero.

---

### Pregunta 11: "¿Qué ventajas tiene el controlador como clase?"

> 1. **Organización:** Cada acción es un método privado con nombre descriptivo (`$this->listar()`, `$this->crear()`).
> 2. **PHP 8 `match()`:** Más seguro que `switch` porque no necesita `break` y es una expresión.
> 3. **Manejo de errores:** Try-catch separado para errores de BD y errores generales.
> 4. **Método auxiliar `json()`:** Construye respuestas uniformes, evitando repetir el mismo código en cada acción.
> 5. **Type hints:** Los tipos de datos están declarados, lo que ayuda a detectar errores antes de ejecutar.

---

### Pregunta 12: "¿Cómo se protege la vista y el JavaScript contra XSS?"

> **En PHP (vista):** `htmlspecialchars($p['nombre'])` convierte caracteres especiales HTML en entidades, evitando que un nombre como `<script>alert('xss')</script>` se ejecute como código.
>
> **En JavaScript:** `$('<span>').text(p.nombre).html()` crea un elemento temporal, asigna el texto de forma segura con `.text()` (que escapa automáticamente), y luego obtiene el HTML escapado con `.html()`.
