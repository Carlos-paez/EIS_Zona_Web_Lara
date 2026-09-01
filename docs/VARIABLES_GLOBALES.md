# Variables Globales del Sistema EIS

Este documento detalla todas las variables globales, superglobales, de configuración y de ámbito compartido utilizadas en la aplicación PHP EIS System (MVC).

---

## Índice

1. [Superglobales de PHP](#1-superglobales-de-php)
2. [Variables de Sesión (`$_SESSION`)](#2-variables-de-sesión-_session)
3. [Variables de Configuración de Base de Datos (Procedural)](#3-variables-de-configuración-de-base-de-datos-procedural)
4. [Variables de Configuración de Base de Datos (Singleton OOP)](#4-variables-de-configuración-de-base-de-datos-singleton-oop)
5. [Variables del Front Controller (`src/index.php`)](#5-variables-del-front-controller-srcindexphp)
6. [Variables de la Clase `Router`](#6-variables-de-la-clase-router)
7. [Variables del Layout Principal (`src/app/template/layout.php`)](#7-variables-del-layout-principal-srcapptemplatelayoutphp)
8. [Variables de los Controladores](#8-variables-de-los-controladores)
9. [Variables de la Vista de Login (`src/app/Views/login.php`)](#9-variables-de-la-vista-de-login-srcappviewsloginphp)
10. [Variables del Script CLI (`src/cli/create_user.php`)](#10-variables-del-script-cli-srcclicreate_userphp)
11. [Variables del Service Worker (`src/sw.js`)](#11-variables-del-service-worker-srcswjs)
12. [Variables en las Vistas Protegidas](#12-variables-en-las-vistas-protegidas)
13. [Constantes y Variables Especiales de PHP](#13-constantes-y-variables-especiales-de-php)

---

## 1. Superglobales de PHP

Son arrays nativos del lenguaje accesibles desde cualquier ámbito. La aplicación utiliza las siguientes:

| Variable | Propósito | Archivos donde se usa |
|---|---|---|
| `$_GET` | Parámetros de la URL (query string). Contiene `pagina`, `action`, `id`, `error`, `orden_id`, `rol_id`, `termino` | `router.php`, `login.php`, `InventarioController.php`, `ProveedorController.php`, `RolController.php` |
| `$_POST` | Datos enviados mediante formularios HTTP POST. Contiene credenciales de login, datos CRUD de productos, órdenes, roles, etc. | `AuthController.php`, `InventarioController.php`, `ProveedorController.php`, `RolController.php` |
| `$_SESSION` | Variables de sesión persistentes del lado del servidor. Contiene datos del usuario autenticado. | `router.php`, `AuthController.php`, `layout.php` |
| `$_SERVER['REQUEST_METHOD']` | Método HTTP de la petición (`GET` o `POST`). Se usa para validar que el login solo acepte POST. | `AuthController.php` |

### 1.1 Parámetros de `$_GET`

| Clave | Tipo | Descripción | Valores posibles |
|---|---|---|---|
| `pagina` | `string` | Nombre de la vista o acción a ejecutar | `login`, `dashboard`, `inventario`, `ventas`, `proveedores`, `proveedores-gestion`, `clientes`, `ciberControl`, `reportes`, `activos`, `asesorias`, `usuarios`, `roles`, `login_validate`, `logout`, `menu` |
| `action` | `string` | Acción JSON específica para controladores AJAX | `listar`, `crear`, `actualizar`, `eliminar`, `detalle`, `kpis`, `categorias`, `buscar`, `proveedores`, `productos`, `statuses`, `lineas`, `agregarLinea`, `eliminarLinea`, `guardarProveedor`, `eliminarProveedor`, `proveedorPorId`, `permisos`, `permisosRol`, `guardarPermisos`, `usuarios`, `asignarRol` |
| `id` | `int` | ID del registro a consultar | Entero positivo |
| `rol_id` | `int` | ID del rol para consultar permisos | Entero positivo |
| `orden_id` | `int` | ID de la orden para consultar líneas | Entero positivo |
| `error` | `int` | Indicador de error de autenticación | `1` |

### 1.2 Parámetros de `$_POST`

| Clave | Tipo | Descripción | Controlador |
|---|---|---|---|
| `username` | `string` | Nombre de usuario para login | `AuthController` |
| `password` | `string` | Contraseña para login | `AuthController` |
| `codigo` | `string` | Código de producto | `InventarioController` |
| `nombre` | `string` | Nombre de producto/rol/proveedor | `InventarioController`, `RolController`, `ProveedorController` |
| `descripcion` | `string` | Descripción de producto/asesoría | `InventarioController` |
| `categoria_id` | `int` | ID de categoría de producto | `InventarioController` |
| `stock` | `int` | Cantidad en stock | `InventarioController` |
| `stock_minimo` | `int` | Stock mínimo permitido | `InventarioController` |
| `costo_compra` | `float` | Precio de compra | `InventarioController` |
| `precio_venta` | `float` | Precio de venta | `InventarioController` |
| `id` | `int` | ID del registro a modificar/eliminar | `InventarioController`, `ProveedorController`, `RolController` |
| `termino` | `string` | Término de búsqueda de productos | `InventarioController` |
| `numero` | `string` | Número de orden de compra | `ProveedorController` |
| `fecha` | `string` | Fecha de la orden (YYYY-MM-DD) | `ProveedorController` |
| `fk_proveedor` | `int` | ID del proveedor | `ProveedorController` |
| `fk_status` | `int` | ID del estado de la orden | `ProveedorController` |
| `orden_id` | `int` | ID de la orden para líneas | `ProveedorController` |
| `producto_id` | `int` | ID del producto en línea | `ProveedorController` |
| `cantidad` | `int` | Cantidad solicitada | `ProveedorController` |
| `precio` | `float` | Precio unitario en línea | `ProveedorController` |
| `rif` | `string` | RIF del proveedor | `ProveedorController` |
| `email` | `string` | Email del proveedor | `ProveedorController` |
| `telefono` | `string` | Teléfono del proveedor | `ProveedorController` |
| `rol_id` | `int` | ID del rol | `RolController` |
| `usuario_id` | `int` | ID del usuario | `RolController` |
| `permisos` | `array` | IDs de permisos a asignar | `RolController` |

---

## 2. Variables de Sesión (`$_SESSION`)

La sesión se inicia en `Router::__construct()` mediante `session_start()`.

| Variable | Tipo | Descripción | Se establece en | Se lee en |
|---|---|---|---|---|
| `$_SESSION['logged_in']` | `bool` | Indica si el usuario tiene una sesión activa | `AuthController::login()` | `Router::requireAuth()`, `Router::render()` |
| `$_SESSION['user_id']` | `int` | ID del usuario autenticado (campo `id` de tabla `usuarios`) | `AuthController::login()` | Potencialmente en vistas o controladores |
| `$_SESSION['username']` | `string` | Nombre de usuario (`user_name`) del autenticado | `AuthController::login()` | Potencialmente en vistas |
| `$_SESSION['nombre']` | `string` | Nombre completo del usuario autenticado | `AuthController::login()` | Potencialmente en vistas |

La sesión se destruye completamente con `session_destroy()` en `AuthController::logout()`.

---

## 3. Variables de Configuración de Base de Datos (Procedural)

Archivo: `src/Config/database.php`

Este archivo define variables globales de conexión usadas por los CRUDs procedurales (`crud_users.php`, `crud_asesorias.php`). Al ser incluido con `require_once`, estas variables quedan en el ámbito global.

| Variable | Tipo | Valor por defecto | Descripción |
|---|---|---|---|
| `$host` | `string` | `"localhost"` | Dirección del servidor MySQL |
| `$db` | `string` | `"zona_web_lara"` | Nombre de la base de datos |
| `$user` | `string` | `"root"` | Usuario de MySQL |
| `$pass` | `string` | `""` | Contraseña de MySQL (vacía en desarrollo) |
| `$charset` | `string` | `"utf8mb4"` | Juego de caracteres para la conexión |
| `$dns` | `string` | `"mysql:host=localhost;dbname=zona_web_lara;charset=utf8mb4"` | DSN (Data Source Name) para PDO |
| `$options` | `array` | `[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]` | Opciones de configuración de PDO |
| `$pdo` | `PDO` | Nueva instancia de `PDO` | Objeto de conexión PDO activo |

### 3.1 Opciones de PDO (`$options`)

| Opción | Valor | Efecto |
|---|---|---|
| `PDO::ATTR_ERRMODE` | `PDO::ERRMODE_EXCEPTION` | Lanza excepciones `PDOException` en errores SQL |
| `PDO::ATTR_DEFAULT_FETCH_MODE` | `PDO::FETCH_ASSOC` | Los resultados se devuelven como arrays asociativos (llaves = nombres de columna) |
| `PDO::ATTR_EMULATE_PREPARES` | `false` | Desactiva emulación de consultas preparadas; usa preparación real del motor MySQL (más seguro contra inyección SQL) |

---

## 4. Variables de Configuración de Base de Datos (Singleton OOP)

Archivo: `src/app/core/Database.php`

### 4.1 Propiedad de clase (estática)

| Variable | Tipo | Visibilidad | Descripción |
|---|---|---|---|
| `Database::$instance` | `?PDO` | `private static` | Almacena la única instancia de la conexión PDO. Inicializada en `null`. Se asigna una sola vez en `getConnection()`. |

### 4.2 Variables locales del método `getConnection()`

| Variable | Tipo | Valor por defecto | Descripción |
|---|---|---|---|
| `$host` | `string` | `'localhost'` | Servidor MySQL |
| `$db` | `string` | `'zona_web_lara'` | Nombre de BD |
| `$user` | `string` | `'root'` | Usuario MySQL |
| `$pass` | `string` | `''` | Contraseña MySQL |
| `$charset` | `string` | `'utf8mb4'` | Juego de caracteres |
| `$dns` | `string` | `"mysql:host=localhost;dbname=zona_web_lara;charset=utf8mb4"` | DSN de conexión |
| `$options` | `array` | Mismas opciones que la versión procedural | Opciones PDO |

---

## 5. Variables del Front Controller (`src/index.php`)

| Variable | Tipo | Valor | Descripción |
|---|---|---|---|
| `$router` | `Router` | `new Router()` | Instancia única del enrutador principal que maneja toda la solicitud |

---

## 6. Variables de la Clase `Router`

Archivo: `src/app/core/router.php`

### 6.1 Propiedades de instancia

| Variable | Tipo | Visibilidad | Descripción |
|---|---|---|---|
| `Router::$pagina` | `string` | `private` | Nombre de la página solicitada, resuelta y validada mediante `resolvePage()` |
| `$_SESSION['csrf_token']` | `string` | superglobal | Token CSRF generado una sola vez por sesión con `bin2hex(random_bytes(32))`, inyectado en `window.EIS.csrfToken` y `<input name="csrf_token">`, verificado con `Router::verifyCsrfToken()` |

La clase también define la **constante** `CONTROLLERS` (mapa `pagina => clase` con los 12 controladores AJAX) que se usa en `dispatchAction()` para resolver el controlador según la página.

### 6.2 Variables locales del método `render()`

| Variable | Tipo | Valor | Descripción |
|---|---|---|---|
| `$publicPages` | `array` | `['login']` | Lista de páginas que no requieren autenticación |
| `$rutaVista` | `string` | `__DIR__ . '/../Views/' . $this->pagina . '.php'` | Ruta absoluta al archivo de la vista |

### 6.3 Variables locales del método `renderWithLayout()`

| Variable | Tipo | Descripción |
|---|---|---|
| `$pagina` | `string` | Alias de `$this->pagina` para usar directamente en `layout.php` |
| `$titulos` | `array` | Mapa asociativo `[nombre_pagina => título_descriptivo]` para los títulos de cada sección |
| `$extraHeaders` | `array` | Mapa asociativo con HTML adicional para el navbar de páginas específicas (ej: chips de estado en `ciberControl`) |
| `$pageTitle` | `string` | Título de la página actual (del mapa `$titulos` o `'EIS System'` por defecto). Se pasa a `layout.php` |
| `$headerExtra` | `string` | HTML de cabecera adicional (del mapa `$extraHeaders` o cadena vacía). Se pasa a `layout.php` |
| `$contentView` | `string` | Ruta absoluta a la vista específica. Se pasa a `layout.php` para ser incluida con `require` |

#### Mapa de títulos (`$titulos`)

```php
$titulos = [
    'dashboard'        => 'Panel de Control',
    'inventario'       => 'Gestión de inventario',
    'ventas'           => 'Punto de Venta (POS)',
    'ciberControl'     => 'Control de Cybercafé',
    'proveedores'      => 'Solicitudes a Proveedores',
    'proveedores-gestion' => 'Gestión de Proveedores',
    'clientes'         => 'Gestión de Clientes',
    'reportes'         => 'Reportes y Estadísticas',
    'activos'          => 'Gestión de Activos',
    'asesorias'        => 'Asesoría Legal',
    'usuarios'         => 'Gestión de Usuarios',
    'roles'            => 'Gestión de Roles y Permisos',
];
```

#### Mapa de cabeceras extra (`$extraHeaders`)

```php
$extraHeaders = [
    'ciberControl' => '<span class="chip ...">5 Disponibles</span><span class="chip ...">4 Ocupadas</span>',
];
```

### 6.4 Variables locales del método `resolvePage()`

| Variable | Tipo | Descripción |
|---|---|---|
| `$pagina` | `string` | Variable temporal que almacena el nombre de página. Inicia como `'login'` por defecto, se sobrescribe con `$_GET['pagina']` si existe y pasa la validación regex |

---

## 7. Variables del Layout Principal (`src/app/template/layout.php`)

Estas variables son **inyectadas** por `Router::renderWithLayout()` antes de incluir el layout. Están en el ámbito global de `layout.php`.

| Variable | Tipo | Origen | Descripción |
|---|---|---|---|
| `$pageTitle` | `string` | `Router::renderWithLayout()` | Título de la página para la etiqueta `<title>` y los encabezados del navbar |
| `$pagina` | `string` | `Router::renderWithLayout()` | Identificador de la página actual. Se usa para aplicar la clase `active` en el menú lateral |
| `$headerExtra` | `string` | `Router::renderWithLayout()` | HTML adicional para el navbar (ej: chips de estado). Se muestra solo si no está vacío |
| `$contentView` | `string` | `Router::renderWithLayout()` | Ruta absoluta al archivo `.php` de la vista específica. Se incluye con `require $contentView` |

### 7.1 Uso en el layout

- **`<?php echo $pageTitle; ?>`** - En `<title>`, `.page-title-desktop`, `.page-title-mobile`
- **`<?php echo $pagina === 'dashboard' ? ' active' : ''; ?>`** - En cada ítem del menú lateral para resaltar la sección activa
- **`<?php if (!empty($headerExtra)): ?> ... <?php echo $headerExtra; ?> ... <?php endif; ?>`** - Cabeceras adicionales condicionales
- **`<?php require $contentView; ?>`** - Inyección del contenido específico de cada página
- **`<?php if ($pagina === 'ventas'): ?>`** - Carga condicional de scripts JS específicos por módulo

---

## 8. Variables de los Controladores

### 8.1 `AuthController` (`src/app/Controllers/AuthController.php`)

| Variable | Tipo | Visibilidad | Descripción |
|---|---|---|---|
| `AuthController::$model` | `Usuario` | `private` | Instancia del modelo `Usuario` para consultas de autenticación |

**Variables locales del método `login()`:**

| Variable | Origen | Descripción |
|---|---|---|
| `$username` | `$_POST['username'] ?? ''` | Nombre de usuario del formulario |
| `$password` | `$_POST['password'] ?? ''` | Contraseña del formulario |
| `$usuario` | `$this->model->autenticar($username, $password)` | Resultado de la autenticación: array con datos del usuario o `false` |

### 8.2 `InventarioController` (`src/app/Controllers/inventarioController.php`)

| Variable | Tipo | Visibilidad | Descripción |
|---|---|---|---|
| `InventarioController::$model` | `Inventario` | `private` | Instancia del modelo `Inventario` |

**Variables locales del método `handle()`:**

| Variable | Origen | Descripción |
|---|---|---|
| `$action` | `$_GET['action'] ?? ''` | Acción AJAX a ejecutar |

**Variables locales del método `crear()`:**

Todas provienen de `$_POST` con operador `??`:
- `$codigo`, `$nombre`, `$descripcion` (string)
- `$categoria_id`, `$stock`, `$stock_minimo` (int)
- `$costo_compra`, `$precio_venta` (float)
- `$resultado` (bool) - Resultado del modelo

**Variables locales del método `actualizar()`:**

Mismas que `crear()` más `$id` (int).

**Variables locales del método `eliminar()`:**

- `$id` (int) - ID del producto
- `$resultado` (bool) - Resultado del modelo

### 8.3 `ProveedorController` (`src/app/Controllers/ProveedorController.php`)

| Variable | Tipo | Visibilidad | Descripción |
|---|---|---|---|
| `ProveedorController::$model` | `Proveedor` | `private` | Instancia del modelo `Proveedor` |

Variables locales similares a `InventarioController`, usando `$_GET` y `$_POST` para:
- `$action`, `$id`, `$numero`, `$fecha`, `$fk_proveedor`, `$fk_status`, `$orden_id`, `$producto_id`, `$cantidad`, `$precio`, `$rif`, `$nombre`, `$email`, `$telefono`

### 8.4 `RolController` (`src/app/Controllers/RolController.php`)

| Variable | Tipo | Visibilidad | Descripción |
|---|---|---|---|
| `RolController::$model` | `Rol` | `private` | Instancia del modelo `Rol` |

Variables locales usando `$_GET` y `$_POST` para:
- `$action`, `$id`, `$nombre_rol`, `$rol_id`, `$permiso_ids`, `$usuario_id`

---

## 9. Variables de la Vista de Login (`src/app/Views/login.php`)

### 9.1 Parámetros GET

| Variable | Propósito |
|---|---|
| `$_GET['error']` | Si está presente en la URL (`?pagina=login&error=1`), muestra un mensaje de "Credenciales incorrectas" |

### 9.2 Variables JavaScript (ámbito del navegador)

| Variable | Propósito |
|---|---|
| `currentTheme` | Almacena el tema actual (`'light'` o `'dark'`) leído de `localStorage`. Determina el ícono del botón de tema y el atributo `data-theme` del `<html>` |

---

## 10. Variables del Script CLI (`src/cli/create_user.php`)

### 10.1 Opciones de línea de comandos

| Variable | Origen | Tipo | Descripción |
|---|---|---|---|
| `$longopts` | Definición literal | `array` | Especificación de opciones largas para `getopt()`: `username:`, `password:`, `nombre:`, `apellido:`, `email:`, `help` |
| `$options` | `getopt('', $longopts)` | `array` | Opciones parseadas desde la línea de comandos |
| `$username` | `$options['username']` | `string` | Nombre de usuario |
| `$password` | `$options['password']` | `string` | Contraseña en texto plano |
| `$nombre` | `$options['nombre']` | `string` | Nombre real |
| `$apellido` | `$options['apellido'] ?? ''` | `string` | Apellido (opcional) |
| `$email` | `$options['email']` | `string` | Correo electrónico |
| `$db` | `Database::getConnection()` | `PDO` | Conexión a la base de datos |
| `$hash` | `password_hash($password, PASSWORD_BCRYPT)` | `string` | Hash bcrypt de la contraseña |
| `$check` | `$db->prepare(...)` | `PDOStatement` | Consulta de verificación de duplicados |
| `$stmt` | `$db->prepare(...)` | `PDOStatement` | Consulta de inserción |
| `$userId` | `$db->lastInsertId()` | `string` | ID autoincremental del nuevo usuario |

---

## 11. Variables del Service Worker (`src/sw.js`)

Son variables JavaScript globales del ámbito del Service Worker.

| Variable | Tipo | Valor | Descripción |
|---|---|---|---|
| `CACHE_NAME` | `string` | `'eis-cache-v1'` | Nombre del caché para almacenar assets estáticos |
| `STATIC_ASSETS` | `array` | Lista de rutas de archivos CSS, JS, fuentes e iconos | Assets a cachear durante la instalación del Service Worker |

### Contenido de `STATIC_ASSETS`

```
Public/css/material-icons.css
Public/css/materialize.min.css
Public/css/styles.css
Public/css/login.css
Public/js/jquery-3.7.1.min.js
Public/js/materialize.min.js
Public/js/app.core.js
Public/js/app.init.js
Public/js/app.tables.js
Public/js/app.ui.js
Public/js/app.pos.js
Public/js/app.cyber.js
Public/js/app.legal.js
Public/fonts/MaterialIcons-Regular.ttf
manifest.json
Public/icons/icon-192.svg
Public/icons/icon-512.svg
offline.php
```

---

## 12. Variables en las Vistas Protegidas

Cada vista protegida (ej: `dashboard.php`, `inventario.php`, `ventas.php`, etc.) se incluye dentro de `layout.php` mediante `require $contentView`. Por herencia del ámbito, las vistas tienen acceso a las variables definidas en `Router::renderWithLayout()`:

| Variable | Disponible en vistas |
|---|---|
| `$pagina` | Sí - identifica la página actual |
| `$pageTitle` | Sí - título de la página |
| `$headerExtra` | Sí - HTML extra del navbar |
| `$contentView` | No aplica (es la propia vista) |

Además, las vistas que realizan peticiones AJAX utilizan `$_GET`, `$_POST` y `$_SESSION` indirectamente a través de los controladores.

### 12.1 Vista `login_validate.php`

Esta vista es una trampa de seguridad. Solo contiene:

| Variable | Propósito |
|---|---|
| `header('Location: ?pagina=login')` | Redirige al login si se accede directamente sin POST |
| `exit` | Termina la ejecución |

---

## 13. Constantes y Variables Especiales de PHP

### 13.1 Constantes mágicas

| Constante | Descripción | Archivos donde se usa |
|---|---|---|
| `__DIR__` | Directorio actual del archivo | `index.php`, `router.php`, `cli/create_user.php`, `crud_users.php`, `crud_asesorias.php` |
| `__FILE__` | Ruta completa del archivo actual | No se usa explícitamente |

### 13.2 Constantes de PHP nativas

| Constante | Propósito |
|---|---|
| `PASSWORD_BCRYPT` | Algoritmo de hash para `password_hash()` - genera hashes de 60 caracteres con salting automático |
| `PDO::ATTR_ERRMODE` | Atributo PDO para modo de error |
| `PDO::ERRMODE_EXCEPTION` | Valor que indica lanzar excepciones en errores |
| `PDO::ATTR_DEFAULT_FETCH_MODE` | Atributo PDO para modo de obtención de resultados |
| `PDO::FETCH_ASSOC` | Valor que indica devolver resultados como array asociativo |
| `PDO::ATTR_EMULATE_PREPARES` | Atributo PDO para emulación de consultas preparadas |

### 13.3 Constantes del manifiesto PWA (`src/manifest.json`)

| Propiedad | Valor | Descripción |
|---|---|---|
| `name` | `"EIS System"` | Nombre completo de la aplicación |
| `short_name` | `"EIS"` | Nombre abreviado |
| `display` | `"standalone"` | Modo de visualización sin Chrome UI |
| `background_color` | `"#1a237e"` | Color de fondo de pantalla de carga |
| `theme_color` | `"#1a237e"` | Color de tema (barra de navegación del navegador) |
| `orientation` | `"portrait-primary"` | Orientación preferida |

---

## Resumen de Ámbitos

| Ámbito | Variables |
|---|---|
| **Superglobal** | `$_GET`, `$_POST`, `$_SESSION`, `$_SERVER` |
| **Global (procedural)** | `$host`, `$db`, `$user`, `$pass`, `$charset`, `$dns`, `$options`, `$pdo` |
| **Estático de clase** | `Database::$instance` |
| **Propiedades de instancia** | `Model::$db`, `Router::$pagina`, `AuthController::$model`, `InventarioController::$model`, `ProveedorController::$model`, `RolController::$model` |
| **Variables de template** | `$pageTitle`, `$pagina`, `$headerExtra`, `$contentView` |
| **CLI (local)** | `$longopts`, `$options`, `$username`, `$password`, `$nombre`, `$apellido`, `$email`, `$db`, `$hash`, `$check`, `$stmt`, `$userId` |
| **Service Worker (JS)** | `CACHE_NAME`, `STATIC_ASSETS` |

---

*Documentación generada el 2026-07-09 - EIS System (Zona Web Lara)*
