# Documentación del Sistema de Enrutado - EIS System

## Arquitectura General

El sistema de enrutado de EIS System sigue el patrón **Front Controller**: todas las solicitudes HTTP pasan por un único punto de entrada (`index.php`), que delega la lógica de navegación a un **router central** (`router.php`). La configuración del servidor Apache (`.htaccess`) habilita URLs limpias y redirige todo el tráfico al Front Controller.

```
Solicitud HTTP
     │
     ▼
  .htaccess  (Apache: reescribe URLs amigables → ?pagina=XXX)
     │
     ▼
  index.php  (Front Controller: punto de entrada único)
     │
     ▼
  router.php  (Router: determina página, verifica auth, carga vista)
     │
     ├── Páginas públicas → vista directa (login, login_validate)
     │
     └── Páginas protegidas → layout.php + vista específica
```

---

## Archivo 1: `.htaccess` — Reescritura de URLs (Apache)

**Ubicación:** `src/.htaccess`

```
Linea 1:  Options All -Indexes
Linea 2:  (en blanco)
Linea 3:  # URLS AMIGABLES
Linea 4:  RewriteEngine on
Linea 5:  (en blanco)
Linea 6:  RewriteRule ^$ index.php [L,QSA]
Linea 7:  (en blanco)
Linea 8:  RewriteCond %{REQUEST_FILENAME} !-f
Linea 9:  RewriteCond %{REQUEST_FILENAME} !-d
Linea 10: RewriteRule ^(\w+)$ index.php?pagina=$1 [L,QSA]
```

### Explicación línea por línea

| Línea | Código | Explicación |
|-------|--------|-------------|
| **1** | `Options All -Indexes` | Deshabilita el listado automático de directorios por seguridad. Si un usuario accede a una carpeta sin `index.php`, Apache mostrará un error 403 en lugar de listar los archivos. |
| **4** | `RewriteEngine on` | Activa el motor `mod_rewrite` de Apache, necesario para reescribir URLs. |
| **6** | `RewriteRule ^$ index.php [L,QSA]` | Cuando se accede a la raíz (`/`), redirige internamente a `index.php`. `[L]` = última regla, `[QSA]` = conserva cualquier parámetro GET existente. |
| **8** | `RewriteCond %{REQUEST_FILENAME} !-f` | Condición: solo aplica la reescritura si el archivo solicitado NO existe físicamente (para no interceptar archivos reales como CSS, JS o imágenes). |
| **9** | `RewriteCond %{REQUEST_FILENAME} !-d` | Condición: solo aplica si el directorio solicitado NO existe físicamente. |
| **10** | `RewriteRule ^(\w+)$ index.php?pagina=$1 [L,QSA]` | Toma cualquier palabra (`\w+` = letras, números, guión bajo) en la URL y la pasa como parámetro `?pagina=` a `index.php`. Ej: `/dashboard` → `index.php?pagina=dashboard`. |

### Flujo del .htaccess

```
URL de entrada:     /dashboard
                    │
                    ▼
  ¿Es la raíz?      No  →  RewriteRule ^$  (no aplica)
                    │
                    ▼
  ¿Existe el archivo/directorio?  No
                    │
                    ▼
  Reescribe a:      index.php?pagina=dashboard
```

---

## Archivo 2: `index.php` — Front Controller (Punto de Entrada)

**Ubicación:** `src/index.php`

```
Linea 1:  <?php
Linea 2:
Linea 3:  require_once __DIR__.'/app/core/router.php';
```

### Explicación línea por línea

| Línea | Código | Explicación |
|-------|--------|-------------|
| **1** | `<?php` | Abre el bloque de código PHP. |
| **3** | `require_once __DIR__.'/app/core/router.php'` | Incluye el archivo `router.php` una sola vez. `__DIR__` es la constante mágica que devuelve la ruta absoluta del directorio donde está este archivo (`src/`). Toda la lógica de enrutado está contenida en `router.php`, por lo que `index.php` es simplemente un puente. |

---

## Archivo 3: `router.php` — Router Principal

**Ubicación:** `src/app/core/router.php`

```
Linea 1:  <?php
Linea 2:  session_start();
Linea 3:
Linea 4:  $pagina = "login";
Linea 5:
Linea 6:  if(!empty($_GET["pagina"])){
Linea 7:      $pagina = $_GET["pagina"];
Linea 8:  }
Linea 9:
Linea 10: if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
Linea 11:     $pagina = "login";
Linea 12: }
Linea 13:
Linea 14: $public_pages = ['login', 'login_validate'];
Linea 15: if (!isset($_SESSION['logged_in']) && !in_array($pagina, $public_pages)) {
Linea 16:     header("Location: ?pagina=login");
Linea 17:     exit;
Linea 18: }
Linea 19:
Linea 20: $rutaVista = __DIR__ . '/../Views/' . $pagina . '.php';
Linea 21:
Linea 22: if(is_file($rutaVista)){
Linea 23:
Linea 24:     if (in_array($pagina, $public_pages)) {
Linea 25:         require $rutaVista;
Linea 26:
Linea 27:     } else {
Linea 28:         $titulos = [
Linea 29:             'dashboard'    => 'Panel de Control',
Linea 30:             'inventario'   => 'Gestión de Inventario',
Linea 31:             'ventas'       => 'Punto de Venta (POS)',
Linea 32:             'ciberControl' => 'Control de Cybercafé',
Linea 33:             'proveedores'  => 'Solicitudes a Proveedores',
Linea 34:             'reportes'     => 'Reportes y Estadísticas',
Linea 35:             'activos'      => 'Gestión de Activos',
Linea 36:         ];
Linea 37:         $extraHeaders = [
Linea 38:             'ciberControl' => '<span ...>7 Disponibles</span>...',
Linea 39:         ];
Linea 40:         $pageTitle = $titulos[$pagina] ?? 'EIS System';
Linea 41:         $headerExtra = $extraHeaders[$pagina] ?? '';
Linea 42:         $contentView = $rutaVista;
Linea 43:         require __DIR__ . '/../template/layout.php';
Linea 44:     }
Linea 45: } else {
Linea 46:     http_response_code(404);
Linea 47:     echo "<h1>Error 404: Página no encontrada</h1>";
Linea 48:     echo "<p>La página <strong>{$pagina}</strong> no existe.</p>";
Linea 49:     echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
Linea 50: }
```

### Explicación línea por línea

#### Bloque 1: Inicio de sesión PHP
| Línea | Código | Explicación |
|-------|--------|-------------|
| **1** | `<?php` | Abre el bloque de código PHP. |
| **2** | `session_start()` | Inicia o reanuda la sesión del usuario. **Debe** ejecutarse antes de cualquier salida HTML porque envía cookies. Las variables de sesión (`$_SESSION`) permiten mantener el estado de autenticación entre páginas. |

#### Bloque 2: Determinar la página solicitada
| Línea | Código | Explicación |
|-------|--------|-------------|
| **4** | `$pagina = "login"` | Establece `login` como valor por defecto. Si no se especifica ninguna página en la URL, se muestra el formulario de inicio de sesión. |
| **6-8** | `if(!empty($_GET["pagina"])){ $pagina = $_GET["pagina"]; }` | Si la URL contiene el parámetro `?pagina=XXX`, sobrescribe el valor por defecto con el nombre de la página solicitada. Ej: `?pagina=dashboard` → `$pagina = "dashboard"`. |

#### Bloque 3: Sanitización (seguridad)
| Línea | Código | Explicación |
|-------|--------|-------------|
| **10-12** | `if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) { $pagina = "login"; }` | **Medida de seguridad crítica.** Valida que `$pagina` contenga solo caracteres alfanuméricos, guiones bajos y guiones medios. Si contiene caracteres extraños (puntos, barras, etc.), redirige al login. Esto previene **path traversal attacks** (ej: `?pagina=../../etc/passwd`). |

#### Bloque 4: Control de acceso (autenticación)
| Línea | Código | Explicación |
|-------|--------|-------------|
| **14** | `$public_pages = ['login', 'login_validate']` | Define un array con las páginas que NO requieren autenticación. Solo estas dos pueden ser accedidas sin haber iniciado sesión. |
| **15-18** | `if (!isset($_SESSION['logged_in']) && !in_array($pagina, $public_pages)) { header("Location: ?pagina=login"); exit; }` | **Guardián de autenticación.** Si el usuario NO tiene la variable `$_SESSION['logged_in']` (no ha iniciado sesión) Y la página solicitada NO está en `$public_pages`, entonces redirige al login. `exit` detiene la ejecución inmediatamente. |

#### Bloque 5: Resolver la ruta de la vista
| Línea | Código | Explicación |
|-------|--------|-------------|
| **20** | `$rutaVista = __DIR__ . '/../Views/' . $pagina . '.php'` | Construye la ruta absoluta al archivo de vista. `__DIR__` apunta a `src/app/core/`, entonces sube un nivel (`/../`) y entra a `Views/`. Resultado: `src/app/Views/[pagina].php`. |

#### Bloque 6: Cargar la vista
| Línea | Código | Explicación |
|-------|--------|-------------|
| **22** | `if(is_file($rutaVista)){` | Verifica que el archivo de vista exista físicamente en el sistema de archivos. Si no existe, va al `else` (error 404). |
| **24-25** | `if (in_array($pagina, $public_pages)) { require $rutaVista; }` | **Páginas públicas**: se renderizan **sin** el layout maestro. `require` incluye el archivo de vista directamente (login o login_validate), que son HTML completos con sus propios `<head>` y `<body>`. |
| **27-43** | `} else { ... require __DIR__ . '/../template/layout.php'; }` | **Páginas protegidas**: se renderizan **dentro** del layout maestro. Antes de incluirlo, prepara variables que el layout necesita. |

#### Bloque 7: Preparar variables para el layout
| Línea | Código | Explicación |
|-------|--------|-------------|
| **28-36** | `$titulos = [ ... ]` | Array asociativo que mapea cada nombre de página a su título legible. El título se muestra en la barra de navegación y en el `<title>` de la pestaña. |
| **37-39** | `$extraHeaders = [ ... ]` | Array opcional para contenido HTML extra en la barra de navegación de páginas específicas. Ejemplo: chips de estado "Disponibles/Ocupadas" en la página de cybercafé. |
| **40** | `$pageTitle = $titulos[$pagina] ?? 'EIS System'` | Obtiene el título del array, o usa 'EIS System' como valor por defecto si la página no está en el array (operador `??` de fusión null). |
| **41** | `$headerExtra = $extraHeaders[$pagina] ?? ''` | Obtiene el HTML extra o cadena vacía si no hay definido. |
| **42** | `$contentView = $rutaVista` | Asigna la ruta de la vista a `$contentView` para que el layout pueda incluirla con `require $contentView`. |
| **43** | `require __DIR__ . '/../template/layout.php'` | Incluye el layout maestro, que a su vez incluirá `$contentView` en su `<main>`. |

#### Bloque 8: Manejo de Error 404
| Línea | Código | Explicación |
|-------|--------|-------------|
| **45-49** | `} else { http_response_code(404); echo "<h1>Error 404...</h1>"; ... }` | Si el archivo de vista no existe (`is_file()` devolvió `false`), establece el código de respuesta HTTP 404 y muestra un mensaje de error con un enlace para volver al dashboard. |

---

## Diagrama de Flujo Completo del Router

```
                    ┌──────────────────────┐
                    │  www.dominio.com/XXX  │
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │     .htaccess        │
                    │  RewriteRule ^(\w+)$ │
                    │  → ?pagina=XXX       │
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │     index.php        │
                    │  require router.php  │
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │   session_start()    │
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │  $pagina =           │
                    │  $_GET["pagina"]     │
                    │  (defecto: "login")  │
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │  Validar caracteres  │
                    │  [a-zA-Z0-9_-]       │
                    │  ¿Válido?            │
                    └──────────┬───────────┘
                         ┌─────┴─────┐
                    SÍ   │           │  NO
                         │           │
                         │    ┌──────▼───────┐
                         │    │ $pagina =    │
                         │    │ "login"      │
                         │    └──────┬───────┘
                         │           │
                    ┌─────▼──────────▼───────┐
                    │  ¿Está autenticado?    │
                    │  $_SESSION['logged_in']│
                    └──────┬──────────┬──────┘
                    SÍ     │          │  NO
                           │          │
                           │    ┌─────▼──────────┐
                           │    │ ¿Es página     │
                           │    │ pública?        │
                           │    │ (login,         │
                           │    │ login_validate) │
                           │    └──────┬──────────┘
                           │       ┌───┴───┐
                           │   SÍ  │      │  NO
                           │       │      │
                           │  ┌────▼──┐ ┌──▼─────────┐
                           │  │ Seguir│ │ Redirigir  │
                           │  │       │ │ a login    │
                           │  └───────┘ └────────────┘
                           │
                    ┌──────▼──────────────────────┐
                    │  $rutaVista =               │
                    │  Views/{$pagina}.php        │
                    └──────┬──────────────────────┘
                           │
                    ┌──────▼──────────────┐
                    │  ¿Existe el archivo?│
                    │  is_file()          │
                    └──────┬──────────────┘
                      ┌────┴────┐
                  SÍ  │        │  NO
                      │        │
                 ┌────▼──┐ ┌──▼──────────────┐
                 │ ¿Es   │ │  HTTP 404       │
                 │pública?│ │  Mensaje error  │
                 └───┬───┘ └─────────────────┘
                ┌────┴────┐
             SÍ │         │ NO
                │         │
          ┌─────▼──┐ ┌───▼──────────────┐
          │ Cargar │ │ Preparar vars    │
          │ vista  │ │ $pageTitle       │
          │ directa│ │ $contentView     │
          └────────┘ │ Cargar layout    │
                     │ con $contentView │
                     └──────────────────┘
```

---

## Mapa de Rutas Disponibles

| Ruta (`?pagina=`) | Archivo de Vista | Autenticación | Título |
|-------------------|-----------------|---------------|--------|
| `login` | `Views/login.php` | ❌ Pública | — |
| `login_validate` | `Views/login_validate.php` | ❌ Pública | — |
| `dashboard` | `Views/dashboard.php` | ✅ Requerida | Panel de Control |
| `inventario` | `Views/inventario.php` | ✅ Requerida | Gestión de Inventario |
| `ventas` | `Views/ventas.php` | ✅ Requerida | Punto de Venta (POS) |
| `ciberControl` | `Views/ciberControl.php` | ✅ Requerida | Control de Cybercafé |
| `proveedores` | `Views/proveedores.php` | ✅ Requerida | Solicitudes a Proveedores |
| `reportes` | `Views/reportes.php` | ✅ Requerida | Reportes y Estadísticas |
| `activos` | `Views/activos.php` | ✅ Requerida | Gestión de Activos |
| `menu` | `Views/menu.php` | ✅ Requerida | (usa valor por defecto) |
| *cualquier otra* | — | — | Error 404 |

---

## Resumen del Ciclo de Vida de una Solicitud

1. **El usuario hace clic** en un enlace como `?pagina=inventario` o escribe `/inventario` en el navegador.

2. **Apache** recibe la solicitud:
   - Si es `/inventario`, `.htaccess` la reescribe internamente como `index.php?pagina=inventario`.
   - Si ya es `?pagina=inventario`, pasa directamente.

3. **`index.php`** se ejecuta e incluye `router.php`.

4. **`router.php`** ejecuta `session_start()` para mantener la sesión.

5. **Determina la página**: Toma `$_GET["pagina"]` o usa `"login"` por defecto.

6. **Sanitiza**: Si contiene caracteres no alfanuméricos, fuerza `"login"`.

7. **Verifica autenticación**: Si el usuario no ha iniciado sesión y la página no es pública, redirige a `login`.

8. **Carga la vista**:
   - **Páginas públicas**: incluye el archivo de vista directamente (HTML completo).
   - **Páginas protegidas**: pasa el título y la ruta de la vista al layout maestro, que renderiza la estructura completa (sidebar, navbar, contenido).

9. **Si la vista no existe**: muestra un error 404 con un enlace de retorno al dashboard.

---

## Consideraciones de Seguridad Implementadas

1. **Sanitización del parámetro `pagina`**: La expresión regular `^[a-zA-Z0-9_-]+$` previene inyección de rutas (`../`) y caracteres peligrosos.

2. **Redirección en lugar de inclusión directa**: Las páginas protegidas redirigen al login en lugar de mostrar mensajes de error que revelen información.

3. **Verificación de existencia de archivo**: `is_file()` evita que se incluyan archivos que no sean vistas legítimas.

4. **Uso de `require` con rutas absolutas**: `__DIR__` garantiza que las rutas sean siempre relativas al archivo actual, evitando confusiones con el directorio de trabajo.
