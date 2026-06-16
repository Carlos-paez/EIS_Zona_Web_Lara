# Diagrama de Clases — EIS Zona Web Lara

## 1. Estado Actual (Arquitectura Procedural)

La aplicación actual está implementada con **código procedural** (funciones globales, sin clases de aplicación). Solo existen dos clases en el proyecto, ambas pertenecientes al autoloader de Composer (`Composer\Autoload\ClassLoader` y `ComposerStaticInit...`), que no forman parte de la lógica de negocio.

A continuación, se modelan los **módulos procedurales** como si fueran clases lógicas para representar la estructura real del código:

```mermaid
classDiagram
    class index_php {
        <<front controller>>
        +require router_php
    }

    class router_php {
        <<procedural>>
        +String $pagina
        +String $rutaVista
        +Array $titulos
        +Array $extraHeaders
        +String $pageTitle
        +String $headerExtra
        +String $contentView
        +session_start()
        +sanitize_page()
        +check_auth()
        +resolve_view()
        +render_layout()
        +handle_404()
    }

    class database_php {
        <<config>>
        +String $host
        +String $db
        +String $user
        +String $pass
        +String $charset
        +String $dns
        +Array $options
        +PDO $pdo
        +connect_PDO()
    }

    class crud_users_php {
        <<model/procedural>>
        +crearUsuario(pdo, username, password, nombre, email, telefono, rol_id) bool
        +obtenerUsuarios(pdo) array
        +obtenerUsuarioPorId(pdo, id) array~false
        +obtenerUsuarioPorUsername(pdo, username) array~false
        +autenticarUsuario(pdo, username, password) array~false
        +actualizarUsuario(pdo, id, nombre, email, telefono, rol_id, activo) bool
        +actualizarPassword(pdo, id, password) bool
        +eliminarUsuario(pdo, id) bool
    }

    class crud_asesorias_php {
        <<model/procedural>>
        +crearAsesoria(pdo, ciudadano, cedula, documento, descripcion, estado, usuario_id) bool
        +obtenerAsesorias(pdo) array
        +obtenerAsesoriasPorEstado(pdo, estado) array
        +obtenerAsesoriaPorId(pdo, id) array~false
        +buscarAsesoriasPorCedula(pdo, cedula) array
        +actualizarAsesoria(pdo, id, ciudadano, cedula, documento, descripcion, estado) bool
        +eliminarAsesoria(pdo, id) bool
        +contarAsesoriasPorEstado(pdo) array
    }

    class login_php {
        <<view public>>
        +render_form() void
    }

    class login_validate_php {
        <<view auth>>
        +String $username
        +String $password
        +validate_credentials() void
    }

    class layout_php {
        <<master template>>
        +String $pageTitle
        +String $pagina
        +String $headerExtra
        +String $contentView
        +render_sidebar() void
        +render_navbar() void
        +render_content() void
        +render_scripts() void
    }

    class dashboard_php {
        <<view>>
        +render_kpi_cards() void
        +render_peak_hours() void
        +render_out_of_stock() void
        +render_activity() void
    }

    class inventario_php {
        <<view>>
        +render_search_filter() void
        +render_table() void
        +render_pagination() void
    }

    class ventas_php {
        <<view>>
        +render_pos_catalog() void
        +render_cart() void
        +render_checkout() void
    }

    class proveedores_php {
        <<view>>
        +render_search_filter() void
        +render_table() void
        +render_pagination() void
    }

    class reportes_php {
        <<view>>
        +render_kpi_cards() void
        +render_generator_form() void
        +render_recent_list() void
    }

    class activos_php {
        <<view>>
        +render_categories() void
        +render_summary() void
    }

    class ciberControl_php {
        <<view>>
        +render_metrics() void
        +render_station_grid() void
        +render_filters() void
    }

    class asesorias_php {
        <<view>>
        +render_form() void
        +render_history() void
        +render_allowed_docs() void
    }

    class menu_php {
        <<view>>
        +render_menu_links() void
    }

    class app_js {
        <<client-side>>
        +digital_clock() void
        +theme_toggle() void
        +animate_counters() void
        +search_table_debounce() void
        +pos_cart_system() void
        +cyber_station_interaction() void
        +legal_doc_validation() void
        +back_to_top() void
        +init_materialize() void
        +notifications() void
    }

    index_php --> router_php : require
    router_php --> login_php : require (publica)
    router_php --> login_validate_php : require (publica)
    router_php --> layout_php : require (protegidas)
    layout_php --> dashboard_php : require $contentView
    layout_php --> inventario_php : require $contentView
    layout_php --> ventas_php : require $contentView
    layout_php --> proveedores_php : require $contentView
    layout_php --> reportes_php : require $contentView
    layout_php --> activos_php : require $contentView
    layout_php --> ciberControl_php : require $contentView
    layout_php --> asesorias_php : require $contentView
    layout_php --> menu_php : require $contentView
    crud_users_php --> database_php : require_once
    crud_asesorias_php --> database_php : require_once
```

---

## 2. Arquitectura Objetivo (MVC con Namespaces PSR-4)

La documentación del proyecto describe una **arquitectura MVC planeada** con clases con namespace `App\`, cargadas vía Composer PSR-4. Actualmente **ninguna de estas clases existe en el código**, pero representan el diseño objetivo del sistema:

```mermaid
classDiagram
    class Router {
        -array $routes
        +__construct()
        +dispatch() void
    }

    class Controller {
        <<abstract>>
        #array $pageTitles
        #array $extraHeaders
        #string $currentPage
        +__construct()
        #render(viewPath, data) void
        #renderPublic(viewPath, data) void
    }

    class LoginController {
        +index() void
        +validate() void
    }

    class DashboardController {
        +index() void
    }

    class InventarioController {
        +index() void
    }

    class VentasController {
        +index() void
    }

    class ProveedoresController {
        +index() void
    }

    class ReportesController {
        +index() void
    }

    class ActivosController {
        +index() void
    }

    class CiberControlController {
        +index() void
    }

    class AsesoriasController {
        +index() void
    }

    class MenuController {
        +index() void
    }

    class UserModel {
        <<planeado>>
        +crear(data) bool
        +obtenerTodos() array
        +obtenerPorId(id) array~false
        +obtenerPorUsername(username) array~false
        +autenticar(username, password) array~false
        +actualizar(id, data) bool
        +actualizarPassword(id, password) bool
        +eliminar(id) bool
    }

    class AsesoriaModel {
        <<planeado>>
        +crear(data) bool
        +obtenerTodas() array
        +obtenerPorEstado(estado) array
        +obtenerPorId(id) array~false
        +buscarPorCedula(cedula) array
        +actualizar(id, data) bool
        +eliminar(id) bool
        +contarPorEstado() array
    }

    class Database {
        <<planeado>>
        +getConnection() PDO
    }

    class layout_main_php {
        <<view>>
        +render(data) void
    }

    class auth_login_php {
        <<view>>
        +render(data) void
    }

    class dashboard_index_php {
        <<view>>
        +render(data) void
    }

    class inventario_index_php {
        <<view>>
        +render(data) void
    }

    class ventas_index_php {
        <<view>>
        +render(data) void
    }

    class proveedores_index_php {
        <<view>>
        +render(data) void
    }

    class reportes_index_php {
        <<view>>
        +render(data) void
    }

    class activos_index_php {
        <<view>>
        +render(data) void
    }

    class ciber_control_index_php {
        <<view>>
        +render(data) void
    }

    class asesorias_index_php {
        <<view>>
        +render(data) void
    }

    class menu_index_php {
        <<view>>
        +render(data) void
    }

    index_php --> Router : new Router() + dispatch()
    Router --> LoginController : instancia
    Router --> DashboardController : instancia
    Router --> InventarioController : instancia
    Router --> VentasController : instancia
    Router --> CiberControlController : instancia
    Router --> ProveedoresController : instancia
    Router --> ReportesController : instancia
    Router --> ActivosController : instancia
    Router --> AsesoriasController : instancia
    Router --> MenuController : instancia

    Controller <|-- LoginController : extends
    Controller <|-- DashboardController : extends
    Controller <|-- InventarioController : extends
    Controller <|-- VentasController : extends
    Controller <|-- CiberControlController : extends
    Controller <|-- ProveedoresController : extends
    Controller <|-- ReportesController : extends
    Controller <|-- ActivosController : extends
    Controller <|-- AsesoriasController : extends
    Controller <|-- MenuController : extends

    Controller --> layout_main_php : render()
    Controller --> auth_login_php : renderPublic()
    Controller --> dashboard_index_php : render()
    Controller --> inventario_index_php : render()
    Controller --> ventas_index_php : render()
    Controller --> ciber_control_index_php : render()
    Controller --> proveedores_index_php : render()
    Controller --> reportes_index_php : render()
    Controller --> activos_index_php : render()
    Controller --> asesorias_index_php : render()
    Controller --> menu_index_php : render()

    LoginController --> UserModel : usa
    DashboardController --> UserModel : usa
    AsesoriasController --> AsesoriaModel : usa
    UserModel --> Database : getConnection
    AsesoriaModel --> Database : getConnection
```

---

## 3. Mapa de Transición: Archivos Actuales → MVC Objetivo

| Archivo Actual (Procedural) | Clase/Archivo Objetivo (MVC) | Estado |
|---|---|---|
| `index.php` (front controller simple) | `index.php` (con autoloader + Router) | Parcial |
| `app/core/router.php` (procedural) | `app/Core/Router.php` (clase con namespace) | Planeado |
| — | `app/Core/Controller.php` (clase base abstracta) | Planeado |
| — | `app/Controllers/*.php` (10 controladores) | Planeado |
| `app/Models/crud_users.php` (funciones) | `app/Models/User.php` (clase PSR-4) | Planeado |
| `app/Models/crud_asesorias.php` (funciones) | `app/Models/Asesoria.php` (clase PSR-4) | Planeado |
| `Config/database.php` (config suelta) | `app/Config/Database.php` (clase singleton) | Planeado |
| `app/template/layout.php` | `app/Views/layouts/main.php` | Planeado |
| `app/Views/*.php` (raíz) | `app/Views/{modulo}/index.php` (subdirectorios) | Planeado |

---

## 4. Flujo de Petición Actual

```mermaid
flowchart TD
    A[.htaccess] -->|RewriteRule| B[index.php]
    B -->|require| C[router.php]
    C -->|session_start| D[Leer ?pagina=]
    D -->|sanitizar| E{¿Ruta existe?}
    E -->|No| F[Error 404]
    E -->|Sí| G{¿Requiere auth?}
    G -->|Sí y no logueado| H[redirect login]
    G -->|No| I{¿Página pública?}
    I -->|Sí: login, login_validate| J[require vista standalone]
    I -->|No: protegida| K[Definir $titulos, $pageTitle]
    K --> L[require layout.php]
    L --> M[require $contentView = vista específica]
```

---

## 5. Flujo de Petición Objetivo (MVC)

```mermaid
flowchart TD
    A[.htaccess] -->|RewriteRule| B[index.php]
    B -->|require vendor/autoload.php| C[new Router]
    C -->|$router->dispatch| D{Router::dispatch}
    D -->|session_start| E[Leer ?pagina=]
    E -->|Validar regex| F{¿Ruta existe?}
    F -->|No| G[Error 404]
    F -->|Sí| H{¿Requiere auth?}
    H -->|Sí y no logueado| I[redirect login]
    H -->|No| J[Instanciar Controller]
    J --> K[Ejecutar Controller::method]
    K --> L{Controller::render o renderPublic?}
    L -->|renderPublic| M[auth/login.php]
    L -->|render| N[layouts/main.php]
    N --> O[$contentView = vista específica]
```

---

## Leyenda

| Símbolo | Significado |
|---------|-------------|
| `+` | Público |
| `-` | Privado |
| `#` | Protegido |
| `<< >>` | Estereotipo (abstract, interface, procedural, etc.) |
| `-->` | Asociación / dependencia |
| `<|--` | Herencia (extends) |
| `require` | Inclusión de archivo PHP |
