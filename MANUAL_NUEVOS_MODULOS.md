# Manual: Cómo Agregar Nuevos Módulos (Rutas) a EIS System

> **Versión:** 1.0  
> **Última actualización:** Septiembre 2026

---

## Índice

1. [Arquitectura General](#1-arquitectura-general)
2. [Estructura de Directorios](#2-estructura-de-directorios)
3. [Flujo de una Petición](#3-flujo-de-una-petición)
4. [Pasos para Agregar un Nuevo Módulo](#4-pasos-para-agregar-un-nuevo-módulo)
   - [Paso 1: Crear la Tabla en la Base de Datos](#paso-1-crear-la-tabla-en-la-base-de-datos)
   - [Paso 2: Crear el Modelo](#paso-2-crear-el-modelo)
   - [Paso 3: Crear el Controlador](#paso-3-crear-el-controlador)
   - [Paso 4: Crear la Vista](#paso-4-crear-la-vista)
   - [Paso 5: Registrar en el Router](#paso-5-registrar-en-el-router)
   - [Paso 6: Agregar al Menú Sidebar](#paso-6-agregar-al-menú-sidebar)
   - [Paso 7: Agregar Scripts JS (Opcional)](#paso-7-agregar-scripts-js-opcional)
   - [Paso 8: Agregar Headers Extra (Opcional)](#paso-8-agregar-headers-extra-opcional)
5. [Referencia Rápida](#5-referencia-rápida)
6. [Ejemplo Completo: Módulo "Facturación"](#6-ejemplo-completo-módulo-facturación)
7. [Convenciones y Buenas Prácticas](#7-convenciones-y-buenas-prácticas)

---

## 1. Arquitectura General

EIS System utiliza un **framework custom propio** basado en el patrón **Front Controller + MVC**. No es Laravel, CodeIgniter ni ningún framework externo.

| Componente | Detalle |
|---|---|
| **Framework** | Custom (framework propio) |
| **Patrón** | Front Controller + MVC |
| **Autoloading** | Composer PSR-4 (`App\ => src/app/`) |
| **Enrutamiento** | Query string (`?pagina=X`) con reescritura Apache |
| **Despacho AJAX** | `?pagina=X&action=Y` → controlador → `handle()` → JSON |
| **Base de datos** | MySQL vía PDO singleton (`Database::getConnection()`) |
| **Modelo base** | `App\Core\Model` (validación, sanitización, conexión) |
| **Layout** | Master template único (`template/layout.php`) con inyección de vista |
| **Seguridad** | CSRF token por sesión, validación en modelos y controladores |
| **UI** | Materialize CSS + jQuery + DataTables |

### Archivos Clave

| Archivo | Función |
|---|---|
| `src/index.php` | Front Controller (punto de entrada único) |
| `src/app/core/router.php` | Enrutador principal con mapa de rutas |
| `src/app/template/layout.php` | Layout maestro (sidebar + nav + contenido) |
| `src/app/core/Model.php` | Modelo abstracto base |
| `src/app/core/Validator.php` | Validaciones reutilizables |
| `src/app/core/Database.php` | Conexión PDO singleton |

---

## 2. Estructura de Directorios

```
EIS_Zona_Web_Lara/
├── .htaccess                          # Reescritura raíz → src/
├── composer.json                      # Autoloading PSR-4
├── vendor/                            # Composer autoload
│
└── src/                               # Document root de Apache
    ├── .htaccess                      # Reescritura: /X → index.php?pagina=X
    ├── index.php                      # Front Controller
    │
    └── app/
        ├── Controllers/               # Controladores (uno por módulo)
        │   ├── ActivoController.php
        │   ├── AsesoriaController.php
        │   ├── AuthController.php
        │   ├── CiberController.php
        │   ├── ClienteController.php
        │   ├── DashboardController.php
        │   ├── InventarioController.php
        │   ├── ProveedorController.php
        │   ├── ProveedorGestionController.php
        │   ├── ReporteController.php
        │   ├── RolController.php
        │   ├── UsuarioController.php
        │   └── VentaController.php
        │
        ├── Models/                    # Modelos (uno por módulo)
        │   ├── Activo.php
        │   ├── Asesoria.php
        │   ├── CiberControl.php
        │   ├── CiberModel.php
        │   ├── Cliente.php
        │   ├── Dashboard.php
        │   ├── Inventario.php
        │   ├── Proveedor.php
        │   ├── ProveedorGestion.php
        │   ├── Reporte.php
        │   ├── Rol.php
        │   ├── Usuario.php
        │   └── Venta.php
        │
        ├── Views/                     # Vistas PHP (una por página)
        │   ├── login.php
        │   ├── login_validate.php
        │   ├── dashboard.php
        │   ├── inventario.php
        │   ├── ventas.php
        │   ├── clientes.php
        │   ├── proveedores.php
        │   ├── proveedores-gestion.php
        │   ├── ciberControl.php
        │   ├── reportes.php
        │   ├── activos.php
        │   ├── asesorias.php
        │   ├── usuarios.php
        │   ├── roles.php
        │   └── menu.php
        │
        ├── template/
        │   └── layout.php             # Layout maestro
        │
        └── core/                      # Framework base
            ├── router.php             # Enrutador principal
            ├── Database.php           # Conexión PDO singleton
            ├── Model.php              # Modelo abstracto base
            ├── Validator.php          # Validaciones reutilizables
            ├── Logger.php             # Logging a archivo
            ├── Exporter.php           # Exportación de datos
            └── PdfBuilder.php         # Generación de PDFs
```

---

## 3. Flujo de una Petición

```
1. Usuario accede a: /clientes  (o ?pagina=clientes)
       │
       ▼
2. .htaccess reescribe → src/index.php?pagina=clientes
       │
       ▼
3. index.php: carga Composer autoload, crea Router, llama $router->handle()
       │
       ▼
4. Router::resolvePagina() → extrae y valida "clientes" de $_GET['pagina']
       │
       ▼
5. ¿Es página pública? → SÍ → renderiza vista directamente (sin layout)
                          NO → continuar
       │
       ▼
6. ¿Hay $_GET['action']? → SÍ → dispatchAction() → instanciar controlador → llamar $controller->handle()
                            NO → render() → incluir layout.php → dentro inyectar la vista
```

### Despacho AJAX

Las peticiones AJAX usan la estructura `?pagina=clientes&action=listar`:

```
GET /?pagina=clientes&action=listar   → ClienteController->handle() → listar() → JSON
POST /?pagina=clientes&action=crear   → ClienteController->handle() → crear()  → JSON
POST /?pagina=clientes&action=eliminar → ClienteController->handle() → eliminar() → JSON
```

### Renderizado de Vistas

Las páginas protegidas se renderizan a través del layout maestro (`template/layout.php`). Las variables se inyectan antes del `require`:

| Variable | Tipo | Descripción |
|---|---|---|
| `$contentView` | `string` | Ruta absoluta al archivo .php de la vista |
| `$pageTitle` | `string` | Título de la página (para el `<title>` y navbar) |
| `$headerExtra` | `string` | HTML de headers extra opcionales (chips, badges) |
| `$pagina` | `string` | Nombre de la página actual (para sidebar active) |

---

## 4. Pasos para Agregar un Nuevo Módulo

Para agregar un módulo llamado por ejemplo **"Facturación"**, se deben seguir estos 8 pasos:

---

### Paso 1: Crear la Tabla en la Base de Datos

Crear la tabla en MySQL antes de crear el modelo:

```sql
CREATE TABLE facturacion (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id  INT NOT NULL,
    fecha       DATE NOT NULL,
    total       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado      ENUM('pendiente', 'pagada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### Paso 2: Crear el Modelo

**Ubicación:** `src/app/Models/Facturacion.php`

El modelo extiende `App\Core\Model` y contiene:
- Propiedades privadas con getters/setters
- Validación en los setters
- Métodos de consulta SQL (CRUD)
- Uso de `$this->db` (PDO) para todas las consultas

```php
<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Facturacion extends Model
{
    private int $id = 0;
    private int $clienteId = 0;
    private string $fecha = '';
    private float $total = 0.0;
    private string $estado = 'pendiente';

    private const MIN_DESCRIPCION = 2;
    private const MAX_DESCRIPCION = 255;
    private const MAX_TOTAL = 99999999.99;
    private const ESTADOS_VALIDOS = ['pendiente', 'pagada', 'cancelada'];

    // ── Getters / Setters con validación ──

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $this->sanitizeInt($id);
    }

    public function getClienteId(): int
    {
        return $this->clienteId;
    }

    public function setClienteId(int $clienteId): void
    {
        $this->clienteId = $this->sanitizeInt($clienteId);
        $this->validatePositive((float)$this->clienteId, 'cliente_id');
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function setFecha(string $fecha): void
    {
        $this->validateFecha($fecha, 'fecha');
        $this->fecha = $fecha;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): void
    {
        $this->validatePositive($total, 'total');
        $this->validateMax($total, 'total', self::MAX_TOTAL);
        $this->total = $total;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): void
    {
        $this->validateEnLista($estado, 'estado', self::ESTADOS_VALIDOS);
        $this->estado = $estado;
    }

    // ── Métodos de consulta ──

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            "SELECT f.*, c.nombre, c.apellido
             FROM facturacion f
             JOIN clientes c ON c.id = f.cliente_id
             ORDER BY f.fecha DESC"
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare(
            "SELECT f.*, c.nombre, c.apellido
             FROM facturacion f
             JOIN clientes c ON c.id = f.cliente_id
             WHERE f.id = ?"
        );
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function crear(int $clienteId, string $fecha, float $total, string $estado): bool
    {
        $this->setClienteId($clienteId);
        $this->setFecha($fecha);
        $this->setTotal($total);
        $this->setEstado($estado);

        $stmt = $this->db->prepare(
            "INSERT INTO facturacion (cliente_id, fecha, total, estado) VALUES (?, ?, ?, ?)"
        );
        $stmt->bindParam(1, $this->clienteId, PDO::PARAM_INT);
        $stmt->bindParam(2, $this->fecha, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->total);
        $stmt->bindParam(4, $this->estado, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function actualizar(int $id, int $clienteId, string $fecha, float $total, string $estado): bool
    {
        $this->setId($id);
        $this->setClienteId($clienteId);
        $this->setFecha($fecha);
        $this->setTotal($total);
        $this->setEstado($estado);

        $stmt = $this->db->prepare(
            "UPDATE facturacion SET cliente_id = ?, fecha = ?, total = ?, estado = ? WHERE id = ?"
        );
        $stmt->bindParam(1, $this->clienteId, PDO::PARAM_INT);
        $stmt->bindParam(2, $this->fecha, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->total);
        $stmt->bindParam(4, $this->estado, PDO::PARAM_STR);
        $stmt->bindParam(5, $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminar(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("DELETE FROM facturacion WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function totalRegistros(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM facturacion");
        return (int)$stmt->fetch()['total'];
    }
}
```

#### Métodos heredados de `App\Core\Model` disponibles

| Método | Descripción |
|---|---|
| `sanitizeString(string)` | `htmlspecialchars(trim($input))` |
| `sanitizeInt(mixed)` | `filter_var($input, FILTER_VALIDATE_INT)` |
| `sanitizeFloat(mixed)` | `filter_var($input, FILTER_VALIDATE_FLOAT)` |
| `validateNotEmpty($value, $field)` | Lanza `InvalidArgumentException` si está vacío |
| `validateMinLength($value, $field, $min)` | Mínimo de caracteres |
| `validateLength($value, $field, $max)` | Máximo de caracteres |
| `validatePattern($value, $pattern, $msg)` | Validación con regex |
| `validatePositive($value, $field)` | Mayor a 0 |
| `validateMax($value, $field, $max)` | No exceder máximo |
| `validateRango($value, $field, $min, $max)` | Entre min y max |
| `validateEnLista($value, $field, $allowed)` | Debe estar en la lista |
| `validateFecha($fecha, $field)` | Formato YYYY-MM-DD válido |
| `validateTelefono($tel, $field)` | Formato de teléfono válido |
| `validateEmail($email)` | Retorna `bool` |
| `validarLibre($texto, $campo, $min, $max)` | Texto libre con patrón |
| `validarSinControl($texto, $campo)` | Sin caracteres de control |
| `existeEnTabla($tabla, $id)` | Verificar existencia por ID |

---

### Paso 3: Crear el Controlador

**Ubicación:** `src/app/Controllers/FacturacionController.php`

El controlador:
- Instancia el modelo en el constructor
- Implementa `handle()` como punto de entrada
- Usa `match` para despachar acciones AJAX
- Retorna JSON con `Content-Type: application/json`
- Valida CSRF en operaciones de escritura (POST)
- Usa `Validator` para validar datos de entrada
- Captura excepciones y retorna errores JSON

```php
<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\Validator;
use App\Models\Facturacion;

class FacturacionController
{
    private Facturacion $model;

    public function __construct()
    {
        $this->model = new Facturacion();
    }

    public function getModel(): Facturacion
    {
        return $this->model;
    }

    public function setModel(Facturacion $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'listar'     => $this->listar(),
                'detalle'    => $this->detalle(),
                'crear'      => $this->crear(),
                'actualizar' => $this->actualizar(),
                'eliminar'   => $this->eliminar(),
                'kpis'       => $this->kpis(),
                default      => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            Logger::error($e, 'Facturación - consulta SQL');
            $msg = $e->getMessage();
            if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
                echo json_encode(['success' => false, 'error' => 'No se puede eliminar: el registro tiene dependencias.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
            }
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            Logger::error($e, 'Facturación');
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }

    private function listar(): void
    {
        $datos = $this->model->obtenerTodos();
        echo json_encode(['success' => true, 'data' => $datos]);
    }

    private function kpis(): void
    {
        echo json_encode([
            'success' => true,
            'data' => [
                'total' => $this->model->totalRegistros(),
            ]
        ]);
    }

    private function detalle(): void
    {
        $id = Validator::id($_GET['id'] ?? null, 'ID del registro');
        $registro = $this->model->obtenerPorId($id);
        if ($registro) {
            echo json_encode(['success' => true, 'data' => $registro]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Registro no encontrado']);
        }
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $errores = Validator::requeridos($_POST, [
            'cliente_id' => 'cliente',
            'fecha'      => 'fecha',
            'total'      => 'total',
        ]);
        if ($errores) {
            echo json_encode(['success' => false, 'error' => 'Completa todos los campos obligatorios.', 'fieldErrors' => $errores]);
            return;
        }

        $clienteId = Validator::id($_POST['cliente_id'] ?? null, 'ID del cliente');
        $fecha     = Validator::texto($_POST['fecha'] ?? null, 'fecha', ['required' => true, 'pattern' => '/^\d{4}-\d{2}-\d{2}$/']);
        $total     = (float)($_POST['total'] ?? 0);
        $estado    = Validator::texto($_POST['estado'] ?? 'pendiente', 'estado', ['required' => true]);

        $resultado = $this->model->crear($clienteId, $fecha, $total, $estado);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Factura creada exitosamente']
                : ['success' => false, 'error' => 'Error al crear la factura']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $errores = Validator::requeridos($_POST, [
            'id'         => 'ID del registro',
            'cliente_id' => 'cliente',
            'fecha'      => 'fecha',
            'total'      => 'total',
        ]);
        if ($errores) {
            echo json_encode(['success' => false, 'error' => 'Completa todos los campos obligatorios.', 'fieldErrors' => $errores]);
            return;
        }

        $id         = Validator::id($_POST['id'] ?? null, 'ID del registro');
        $clienteId  = Validator::id($_POST['cliente_id'] ?? null, 'ID del cliente');
        $fecha      = Validator::texto($_POST['fecha'] ?? null, 'fecha', ['required' => true, 'pattern' => '/^\d{4}-\d{2}-\d{2}$/']);
        $total      = (float)($_POST['total'] ?? 0);
        $estado     = Validator::texto($_POST['estado'] ?? 'pendiente', 'estado', ['required' => true]);

        $resultado = $this->model->actualizar($id, $clienteId, $fecha, $total, $estado);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Factura actualizada exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar la factura']
        );
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del registro');
        $resultado = $this->model->eliminar($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Factura eliminada exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar la factura']
        );
    }

    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        $result = ['success' => $success];
        if ($data !== null) $result['data'] = $data;
        if ($error) $result['error'] = $error;
        echo json_encode($result);
    }
}
```

#### Convenciones del controlador

| Aspecto | Convención |
|---|---|
| **Namespace** | `App\Controllers` |
| **Nombre** | `{Nombre}Controller` (PascalCase) |
| **Acciones estándar** | `listar`, `detalle`, `crear`, `actualizar`, `eliminar`, `kpis` |
| **CSRF** | Verificar en `crear`, `actualizar`, `eliminar` (POST) |
| **Respuesta** | Siempre JSON: `{ success: bool, data?: any, error?: string, message?: string }` |
| **Errores FK** | Detectar `foreign key constraint` y retornar mensaje amigable |

---

### Paso 4: Crear la Vista

**Ubicación:** `src/app/Views/facturacion.php`

La vista es un archivo PHP que genera el HTML/JS del módulo. Se inyecta dentro del layout vía `require $contentView`.

```php
<!-- src/app/Views/facturacion.php -->

<!-- Header del módulo -->
<div class="row" style="margin-bottom:0;">
    <div class="col s12">
        <h4>Gestión de Facturación</h4>
    </div>
</div>

<!-- KPIs -->
<div class="row">
    <div class="col s12 m4">
        <div class="card-panel center-align">
            <h5 id="kpiTotal">0</h5>
            <p>Total Facturas</p>
        </div>
    </div>
</div>

<!-- Botón crear -->
<div class="row">
    <div class="col s12">
        <a class="btn waves-effect waves-light indigo" id="btnCrear">
            <i class="material-icons left">add</i>Nueva Factura
        </a>
    </div>
</div>

<!-- Tabla de registros -->
<div class="row">
    <div class="col s12">
        <table id="tablaFacturacion" class="striped highlight">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal de crear/editar -->
<div id="modalFacturacion" class="modal">
    <div class="modal-content">
        <h4 id="modalTitulo">Nueva Factura</h4>
        <form id="formFacturacion">
            <input type="hidden" id="factId" name="id">
            <div class="row">
                <div class="input-field col s12 m6">
                    <input type="number" id="factClienteId" name="cliente_id" required>
                    <label for="factClienteId">ID Cliente</label>
                </div>
                <div class="input-field col s12 m6">
                    <input type="date" id="factFecha" name="fecha" required>
                    <label for="factFecha" class="active">Fecha</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12 m6">
                    <input type="number" id="factTotal" name="total" step="0.01" min="0.01" required>
                    <label for="factTotal">Total</label>
                </div>
                <div class="input-field col s12 m6">
                    <select id="factEstado" name="estado">
                        <option value="pendiente">Pendiente</option>
                        <option value="pagada">Pagada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                    <label for="factEstado">Estado</label>
                </div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-red btn-flat">Cancelar</a>
        <a href="#!" class="waves-effect waves-green btn" id="btnGuardar">Guardar</a>
    </div>
</div>

<!-- Modal de confirmar eliminar -->
<div id="modalEliminar" class="modal">
    <div class="modal-content">
        <h4>Confirmar Eliminación</h4>
        <p>¿Estás seguro de eliminar esta factura? Esta acción no se puede deshacer.</p>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-red btn-flat">Cancelar</a>
        <a href="#!" class="waves-effect waves-red btn" id="btnConfirmarEliminar">Eliminar</a>
    </div>
</div>
```

---

### Paso 5: Registrar en el Router

**Archivo:** `src/app/core/router.php`

Realizar **3 cambios** en este archivo:

#### 5a. Importar la clase del controlador (al inicio del archivo)

```php
// Agregar después de los otros use:
use App\Controllers\FacturacionController;
```

#### 5b. Agregar el título de la página en `PAGE_TITLES`

```php
private const PAGE_TITLES = [
    // ... entradas existentes ...
    'facturacion'         => 'Facturación',        // ← NUEVO
];
```

#### 5c. Agregar el controlador en `CONTROLLERS`

```php
private const CONTROLLERS = [
    // ... entradas existentes ...
    'facturacion'         => FacturacionController::class,  // ← NUEVO
];
```

#### Notas importantes

- El nombre de la clave (`'facturacion'`) debe ser **idéntico** al parámetro `?pagina=facturacion` que se usará en la URL.
- Solo se registra en `CONTROLLERS` si el módulo necesita acciones AJAX. Si solo es una página estática, no es necesario.
- El nombre de la vista debe coincidir exactamente: `src/app/Views/facturacion.php` (la extensión `.php` se agrega automáticamente por el router).

---

### Paso 6: Agregar al Menú Sidebar

**Archivo:** `src/app/template/layout.php`

Agregar un elemento `<li>` en la sección del sidebar (dentro del `<ul id="slide-out">`):

```html
<!-- Facturación - Gestión de facturas -->
<li><a href="?pagina=facturacion" class="sidenav-link<?php echo $pagina === 'facturacion' ? ' active' : ''; ?>"><i
            class="material-icons left">receipt</i>Facturación</a></li>
```

**Elementos clave:**
- `href="?pagina=facturacion"` — debe coincidir con la clave registrada en el Router
- `$pagina === 'facturacion'` — marca el ítem como activo cuando estás en esa página
- `<i class="material-icons left">receipt</i>` — ícono de Material Icons (buscar en [material.io/icons](https://fonts.google.com/icons))

---

### Paso 7: Agregar Scripts JS (Opcional)

Si el módulo requiere JavaScript personalizado:

**7a. Crear el archivo:** `src/Public/js/app.facturacion.js`

**7b. Agregar carga condicional en el layout** (`src/app/template/layout.php`):

Agrega este bloque al final del archivo, junto a los otros `if` de módulos:

```php
<!-- Módulo de Facturación -->
<?php if ($pagina === 'facturacion'): ?>
<script src="Public/js/app.facturacion.js"></script>
<?php endif; ?>
```

**Nota:** Si el módulo es simple y solo usa jQuery + DataTables, puede no necesitar un archivo JS separado.

---

### Paso 8: Agregar Headers Extra (Opcional)

Si la página necesita chips, badges u otros elementos en la barra de navegación:

**Archivo:** `src/app/core/router.php`

Agregar en la constante `PAGE_EXTRA_HEADERS`:

```php
private const PAGE_EXTRA_HEADERS = [
    'ciberControl' => '<span id="hdrDisponibles" class="chip green white-text">Disponibles</span>...',
    'facturacion'  => '<span id="hdrPendientes" class="chip orange white-text">Pendientes</span>',  // ← NUEVO
];
```

---

## 5. Referencia Rápida

### Checklist de Archivos a Crear/Modificar

| # | Acción | Archivo | Obligatorio |
|---|---|---|---|
| 1 | **Crear** tabla SQL | MySQL | Sí |
| 2 | **Crear** modelo | `src/app/Models/Facturacion.php` | Sí |
| 3 | **Crear** controlador | `src/app/Controllers/FacturacionController.php` | Sí |
| 4 | **Crear** vista | `src/app/Views/facturacion.php` | Sí |
| 5a | **Importar** controlador | `src/app/core/router.php` (use) | Sí |
| 5b | **Registrar** título | `src/app/core/router.php` (PAGE_TITLES) | Sí |
| 5c | **Registrar** controlador | `src/app/core/router.php` (CONTROLLERS) | Sí |
| 6 | **Agregar** al sidebar | `src/app/template/layout.php` | Sí |
| 7 | **Crear** script JS | `src/Public/js/app.facturacion.js` | No |
| 7b | **Cargar** script JS | `src/app/template/layout.php` | No |
| 8 | **Agregar** headers extra | `src/app/core/router.php` (PAGE_EXTRA_HEADERS) | No |

### Estructura de una petición AJAX

```
URL:  ?pagina=facturacion&action=listar
      ?pagina=facturacion&action=detalle&id=5
      ?pagina=facturacion&action=crear
      ?pagina=facturacion&action=actualizar
      ?pagina=facturacion&action=eliminar
      ?pagina=facturacion&action=kpis
```

### Formato de respuesta JSON

```json
// Éxito con datos
{ "success": true, "data": [...] }

// Éxito con mensaje
{ "success": true, "message": "Factura creada exitosamente" }

// Error
{ "success": false, "error": "Token de seguridad inválido" }

// Error con detalles de campo
{ "success": false, "error": "Completa todos los campos obligatorios.", "fieldErrors": { "fecha": "La fecha es obligatoria" } }
```

### URLs de acceso

```
https://tudominio.com/facturacion              → Renderiza la vista con layout
https://tudominio.com/?pagina=facturacion      → Misma vista (query string)
https://tudominio.com/facturacion?action=listar → Retorna JSON (requiere sesión)
```

---

## 6. Ejemplo Completo: Módulo "Facturación"

### Resumen visual de archivos

```
src/
├── app/
│   ├── Controllers/
│   │   └── FacturacionController.php    ← NUEVO (Paso 3)
│   ├── Models/
│   │   └── Facturacion.php              ← NUEVO (Paso 2)
│   ├── Views/
│   │   └── facturacion.php              ← NUEVO (Paso 4)
│   ├── core/
│   │   └── router.php                   ← MODIFICADO (Paso 5)
│   └── template/
│       └── layout.php                   ← MODIFICADO (Paso 6 + 7b)
└── Public/
    └── js/
        └── app.facturacion.js           ← NUEVO Opcional (Paso 7)
```

### Script JS de ejemplo (`app.facturacion.js`)

```javascript
$(document).ready(function () {
    // Inicializar DataTable
    const table = $('#tablaFacturacion').DataTable({
        ajax: {
            url: '?pagina=facturacion&action=listar',
            dataSrc: 'data'
        },
        columns: [
            { data: 'id' },
            { data: null, render: (d) => `${d.nombre} ${d.apellido}` },
            { data: 'fecha' },
            { data: 'total', render: (d) => `$${parseFloat(d).toFixed(2)}` },
            { data: 'estado', render: (d) => `<span class="chip">${d}</span>` },
            {
                data: null,
                orderable: false,
                render: (d) => `
                    <a class="btn-small waves-effect waves-light blue" onclick="editar(${d.id})">
                        <i class="material-icons">edit</i>
                    </a>
                    <a class="btn-small waves-effect waves-light red" onclick="eliminar(${d.id})">
                        <i class="material-icons">delete</i>
                    </a>`
            }
        ]
    });

    // Cargar KPIs
    $.get('?pagina=facturacion&action=kpis', function (res) {
        if (res.success) {
            $('#kpiTotal').text(res.data.total);
        }
    });

    // Abrir modal crear
    $('#btnCrear').on('click', function () {
        $('#formFacturacion')[0].reset();
        $('#factId').val('');
        $('#modalTitulo').text('Nueva Factura');
        $('#modalFacturacion').modal('open');
    });

    // Guardar
    $('#btnGuardar').on('click', function () {
        const id = $('#factId').val();
        const data = $('#formFacturacion').serialize();
        const action = id ? 'actualizar' : 'crear';

        $.post(`?pagina=facturacion&action=${action}`, data, function (res) {
            if (res.success) {
                M.toast({ html: res.message, classes: 'green' });
                $('#modalFacturacion').modal('close');
                table.ajax.reload();
            } else {
                M.toast({ html: res.error, classes: 'red' });
            }
        }, 'json');
    });
});

function editar(id) {
    $.get(`?pagina=facturacion&action=detalle&id=${id}`, function (res) {
        if (res.success) {
            const d = res.data;
            $('#factId').val(d.id);
            $('#factClienteId').val(d.cliente_id);
            $('#factFecha').val(d.fecha);
            $('#factTotal').val(d.total);
            $('#factEstado').val(d.estado).formSelect();
            $('#modalTitulo').text('Editar Factura');
            $('#modalFacturacion').modal('open');
        }
    }, 'json');
}

function eliminar(id) {
    $('#btnConfirmarEliminar').off('click').on('click', function () {
        $.post(`?pagina=facturacion&action=eliminar`, { id: id }, function (res) {
            if (res.success) {
                M.toast({ html: res.message, classes: 'green' });
                $('#tablaFacturacion').DataTable().ajax.reload();
            } else {
                M.toast({ html: res.error, classes: 'red' });
            }
            $('#modalEliminar').modal('close');
        }, 'json');
    });
    $('#modalEliminar').modal('open');
}
```

---

## 7. Convenciones y Buenas Prácticas

### Nomenclatura

| Elemento | Formato | Ejemplo |
|---|---|---|
| Nombre de la página (URL) | `camelCase` o `kebab-case` | `facturacion`, `proveedores-gestion` |
| Modelo | Singular, PascalCase | `Facturacion`, `ProveedorGestion` |
| Controlador | Singular + `Controller` | `FacturacionController` |
| Vista | Igual que la página | `facturacion.php` |
| Tabla MySQL | Plural, snake_case | `facturacion`, `proveedores_gestion` |
| Script JS | `app.{pagina}.js` | `app.facturacion.js` |

### Seguridad

- **Siempre** validar CSRF en operaciones de escritura (POST)
- **Siempre** usar `Validator` para validar datos de entrada
- **Nunca** confiar en `$_GET` o `$_POST` sin sanitizar
- **Usar** `Logger::error()` para registrar errores en lugar de `error_log()`
- **Retornar** mensajes de error genéricos al cliente, nunca errores SQL crudos

### Base de datos

- **Siempre** usar Prepared Statements con `bindParam()`
- **Nunca** concatenar valores directamente en SQL
- **Usar** `PDO::PARAM_INT` para enteros y `PDO::PARAM_STR` para strings
- **Implementar** `existeCedula()` o similar para validar unicidad antes de crear

### Validación

- **Validar** en el modelo (setters) para reutilizar en todos los controladores
- **Validar** en el controlador (campos obligatorios) con `Validator::requeridos()`
- **Validar** CSRF antes de cualquier operación de escritura
- **Sanitizar** siempre antes de mostrar en HTML (el `sanitizeString()` del modelo lo hace con `htmlspecialchars`)

### Errores FK (Foreign Key)

```php
} catch (\PDOException $e) {
    Logger::error($e, 'Módulo - consulta SQL');
    $msg = $e->getMessage();
    if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
        echo json_encode(['success' => false, 'error' => 'No se puede eliminar: el registro tiene dependencias.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
    }
}
```
