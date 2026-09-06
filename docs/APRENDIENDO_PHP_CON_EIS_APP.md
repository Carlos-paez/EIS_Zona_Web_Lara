# Aprende PHP desde Cero leyendo la Aplicación EIS System (Zona Web Lara)

> **Para quién es este documento:** para alguien que está empezando a programar en PHP
> y quiere entender cómo funciona una aplicación web real, **archivo por archivo y línea por línea**.
>
> No hace falta que leas todo de corrido una sola vez. Mejor sigue el orden propuesto:
> primero las ideas, luego `index.php`, luego el enrutador, y poco a poco el resto.
> Cada sección asume que ya leíste las anteriores.

---

## Tabla de contenidos

1. [Lo primero: ¿qué es una web y dónde encaja el PHP?](#1-lo-primero-qué-es-una-web-y-dónde-encaja-el-php)
2. [El mapa de la aplicación (estructura de carpetas)](#2-el-mapa-de-la-aplicación-estructura-de-carpetas)
3. [Los 3 pilares del código: HTML, PHP y JavaScript](#3-los-3-pilares-del-código-html-php-y-javascript)
4. [`index.php` — la puerta de entrada (Front Controller)](#4-indexphp--la-puerta-de-entrada-front-controller)
5. [`.htaccess` — las reglas de reescritura de Apache](#5-htaccess--las-reglas-de-reescritura-de-apache)
6. [`composer.json` — el autoloader PSR-4](#6-composerjson--el-autoloader-psr-4)
7. [`router.php` — el enrutador (la pieza central)](#7-routerphp--el-enrutador-la-pieza-central)
8. [`Database.php` — la conexión a MySQL](#8-databasephp--la-conexión-a-mysql)
9. [`Model.php` — la clase base de todos los modelos](#9-modelphp--la-clase-base-de-todos-los-modelos)
10. [`Validator.php` — la validación estricta de datos](#10-validatorphp--la-validación-estricta-de-datos)
11. [Los modelos — el ejemplo de `Cliente.php`](#11-los-modelos--el-ejemplo-de-clientephp)
12. [Los controladores — `ClienteController.php`](#12-los-controladores--clientecontrollerphp)
13. [`AuthController.php` — el inicio de sesión](#13-authcontrollerphp--el-inicio-de-sesión)
14. [Las vistas — `login.php` y `clientes.php`](#14-las-vistas--loginphp-y-clientesphp)
15. [`layout.php` — el diseño común (plantilla maestra)](#15-layoutphp--el-diseño-común-plantilla-maestra)
16. [El JavaScript — `app.clientes.js`](#16-el-javascript--appclientesjs)
17. [`Exporter.php` — descargar CSV/Excel/PDF](#17-exporterphp--descargar-csvexcelpdf)
18. [La base de datos — `estructura.sql`](#18-la-base-de-datos--estructurasql)
19. [Glosario rápido para principiantes](#19-glosario-rápido-para-principiantes)

---

## 1. Lo primero: ¿qué es una web y dónde encaja el PHP?

Cuando abres un sitio web, tu navegador (Chrome, Firefox, Edge) le pide una **página** a un
**servidor** (una computadora conectada a internet que guarda los archivos). Eso se llama
**HTTP**: la petición ("dame la página") es un *request*, y la respuesta (el HTML que se
pinta) es un *response*.

Hay dos tipos de archivos que puede devolver el servidor:

- **Estáticos**: `index.html`, `styles.css`, `logo.png`. El servidor solo los envía tal cual
  están guardados. No cambian según quién los pide.
- **Dinámicos**: generados en el momento. Es aquí donde entra **PHP**.

```
Navegador  --(pide: GET /clientes)-->  Servidor Apache
                                         │
                                         ▼
                              El servidor ejecuta el archivo PHP
                                         │  (se conecta a MySQL, lee datos, valida, decide)
                                         ▼
                              Se genera HTML (o JSON) nuevo cada vez
                                         │
Navegador  <--(recibe: HTML o JSON)-----  Servidor
```

PHP es un lenguaje que **se ejecuta en el servidor**. El navegador nunca ve el código PHP:
solo ve el resultado (HTML o JSON). Eso es lo más importante que debes interiorizar. Por eso
puedes poner contraseñas y lógica de negocio en PHP sin que el cliente las mire.

La aplicación de este proyecto (**EIS System**) es dinámica y usa el patrón **MVC**:

- **M**odel (Modelo): habla con la base de datos. Decide qué datos existen.
- **V**iew (Vista): dibuja los datos en pantalla (HTML).
- **C**ontroller (Controlador): recibe lo que pide el usuario, le dice al Modelo qué hacer
  y a la Vista qué mostrar. Es el "director de orquesta".

En las próximas secciones vas a ver exactamente cómo se reparte el trabajo en este proyecto.

---

## 2. El mapa de la aplicación (estructura de carpetas)

Mira esta estructura (es la real del proyecto). Léela e intenta deducir qué hace cada carpeta
antes de seguir.

```
EIS_Zona_Web_Lara/
├── composer.json                       # Define cómo se cargan las clases automáticamente
├── src/                                # Todo el código de la aplicación
│   ├── index.php                       # ← Punto de entrada: TODAS las peticiones pasan aquí
│   ├── .htaccess                       # Reglas de Apache para URLs limpias
│   ├── manifest.json                   # Datos de la app para instalarse como PWA
│   ├── sw.js                           # Service Worker (permite la app offline)
│   ├── Config/
│   │   └── database.php                # Config de conexión (versión legacy)
│   ├── app/
│   │   ├── core/                       # "Motor" de la app
│   │   │   ├── Database.php            # Conecta a MySQL (un solo objeto compartido)
│   │   │   ├── Model.php               # Clase base de todos los modelos (validación)
│   │   │   ├── Validator.php           # Validación estricta de los datos entrantes
│   │   │   ├── Exporter.php            # Genera CSV / Excel / PDF
│   │   │   ├── PdfBuilder.php          # Construye un PDF mínimo sin librerías
│   │   │   └── router.php              # ← El enrutador (decide qué se ejecuta)
│   │   ├── Controllers/                # 13 controladores
│   │   │   ├── AuthController.php      # Iniciar / cerrar sesión
│   │   │   ├── ClienteController.php   # CRUD de clientes (este lo analizamos)
│   │   │   ├── InventarioController.php
│   │   │   ├── VentaController.php
│   │   │   ├── RolController.php
│   │   │   ├── ProveedorController.php
│   │   │   ├── ProveedorGestionController.php
│   │   │   ├── AsesoriaController.php
│   │   │   ├── CiberController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ReporteController.php
│   │   │   └── UsuarioController.php
│   │   ├── Models/                     # Los modelos (hablan con la BD)
│   │   │   ├── Cliente.php             # ← Ejemplo que analizamos
│   │   │   ├── Usuario.php
│   │   │   ├── Inventario.php
│   │   │   └── ... (13 en total)
│   │   ├── template/
│   │   │   └── layout.php              # El HTML común a todas las páginas
│   │   └── Views/                       # Las vistas (HTML específico por página)
│   │       ├── login.php
│   │       ├── clientes.php
│   │       └── ... (12+ vistas)
│   ├── Public/                          # Recursos públicos: JS, CSS, imágenes
│   │   ├── css/
│   │   ├── js/app.clientes.js          # JS de cada módulo
│   │   └── ...
│   └── Database/
│       ├── estructura.sql               # El esquema: CREATE TABLE de las 21 tablas
│       └── seed_data.sql                # Datos iniciales de prueba
```

**Nota clave:** el navegador **solo** debería poder acceder a `src/Public/` (CSS, JS, imágenes)
de forma directa. Todo el resto es lógica del servidor.

---

## 3. Los 3 pilares del código: HTML, PHP y JavaScript

La aplicación mezcla tres tecnologías. Confundirlas es lo más común al empezar, así que
clarifiquémoslo antes de leer código:

- **HTML (`.php` en las vistas):** define la *estructura* (encabezados, tablas, formularios).
  El PHP se usa para meter contenido dinámico dentro del HTML.
- **PHP (archivos `.php`):** corre **en el servidor**. Genera el HTML *o* devuelve **JSON**
  (para las peticiones AJAX). También se conecta a la base de datos.
- **JavaScript (archivos `.js`):** corre **en el navegador** (en el dispositivo del usuario).
  Hace que la página sea interactiva: reacciona a clics, hace peticiones al servidor sin
  recargar la página (AJAX) y actualiza el HTML en caliente.

El **flujo típico** en esta app para un "CRUD de clientes":

```
1. El usuario entra a ?pagina=clientes
2. PHP genera el HTML de la vista clientes.php (una tabla vacía + botones)
3. El navegador carga también app.clientes.js (JavaScript)
4. El JavaScript hace una petición AJAX: GET ?pagina=clientes&action=listar
5. El controlador ClienteController consulta el modelo Cliente
6. El modelo hace SELECT en MySQL y devuelve los clientes
7. El controlador devuelve esa lista en formato JSON
8. El JavaScript recibe el JSON y dibuja cada fila en la tabla
9. El usuario pincha "guardar" → el JavaScript hace POST al mismo endpoint → se crea el cliente
```

Vas a ver cada uno de esos pasos. Empecemos por donde entra todo: `index.php`.

---

## 4. `index.php` — la puerta de entrada (Front Controller)

Este archivo tiene solo 11 líneas de código (el resto son comentarios), pero es *el más
importante*: cada vez que alguien pide cualquier página, pasa por aquí. A esto se le llama
**Front Controller** ("un solo punto de entrada").

```php
<?php
// =============================================================================
// ARCHIVO DE ENTRADA PRINCIPAL (Front Controller)
// =============================================================================
```

Línea 1: `<?php` le dice a Apache "a partir de aquí el servidor debe interpretar y ejecutar
código PHP". Es la etiqueta de apertura de PHP. Los archivos con solo PHP normalmente la
cierran con `?>`, pero este no — es una buena práctica no cerrarla si el archivo no tiene
HTML después, para evitar que se inyecten espacios en blanco en la respuesta.

Las líneas 2 a 9 son **comentarios**: texto que PHP ignora (empiezan con `//`). Sirven para
que un humano entienda qué hace cada cosa. Esta app los usa para explicar el propósito.

```php
// Carga el autoloader de Composer para tener disponibles todas las clases
// y namespaces registrados en el proyecto (autoloading PSR-4)
require_once __DIR__ . '/../vendor/autoload.php';
```

Línea 13: `require_once` **incluye el contenido de un archivo** en este. Es como si copiaras
todo el código de `autoload.php` aquí mismo. `require` significa "obligatorio; si no existe,
detén todo". (`include` sería opcional, no detiene). El sufijo `_once` garantiza que el archivo
solo se incluya una vez aunque se pida varias veces.

- `__DIR__` es una **constante mágica** de PHP que contiene la ruta absoluta de la carpeta
  donde está el archivo actual (aquí, la carpeta `src/`).
- `'/../vendor/autoload.php'` es la ruta relativa: sube una carpeta (`..`), entra en `vendor/`
  y abre `autoload.php`. Es decir, el autoloader está en `EIS_Zona_Web_Lara/vendor/`.
- Los `.` en `/../` se usan para concatenar textos en las rutas.

`autoload.php` es generado por **Composer** (el gestor de paquetes de PHP). Su único trabajo:
cuando en el código alguien escriba `new ClienteController()`, PHP no necesita que tú hagas
`require` manualmente de ese archivo. El autoloader **lo carga-solo**. Esto es el "autoloading".

```php
// Importa la clase Router del namespace App\Core para usarla sin prefijo
use App\Core\Router;
```

Línea 16: `use` es un **alias**. Solo le dice a PHP "cuando diga `Router`, me refiero a
`App\Core\Router`". Piensa en `use` como un atajo de escritura (no incluye nada; eso lo hace
el autoloader). Los *namespaces* son como apellidos de las clases para evitar choques de
nombres. La clase se llama de verdad `App\Core\Router` pero aquí la podemos nombrar `Router`.

```php
// Crea una instancia del enrutador principal (inicia sesión y resuelve la página solicitada)
$router = new Router();
// Procesa la solicitud entrante: determina qué acción ejecutar y renderiza la respuesta
$router->handle();
```

- Línea 19: `new Router()` crea un **objeto** de la clase `Router`. El prefijo `$` indica que
  es una *variable*. Al crear el objeto se llama automáticamente a su método `__construct()`
  ("constructor"), que verás más adelante.
- Línea 21: `$router->handle()` **ejecuta el método** `handle()` de ese objeto. La flecha
  `->` significa "ejecuta algo que pertenece a este objeto". Aquí ocurre toda la magia.

Y aquí termina este archivo: ¡solo crea el enrutador y le dice "maneja la petición"! Todo lo
demás pasa dentro de `router.php`. Antes de mirarlo, veamos qué hace que todo llegue hasta
`index.php`, porque eso lo consigue `.htaccess`.

---

## 5. `.htaccess` — las reglas de reescritura de Apache

`index.php` es el punto de entrada, pero el usuario escribe en el navegador cosas como
`?pagina=clientes`. ¿Quién hace que esa dirección terminen ejecutando `index.php`? Apache,
con las reglas de este archivo.

```apache
# Impedir que se pueda ver el listado de contenidos de un directorio
Options -Indexes
```

Línea 2: si alguien visita una carpeta sin archivo `index`, Apache por defecto muestra la
lista de archivos. `Options -Indexes` lo prohíbe (seguridad).

```apache
# Activar el motor de reescritura de URLs de Apache
RewriteEngine On
```

Línea 5: enciende el módulo `mod_rewrite`, que permite "reescribir" las URLs.

```apache
RewriteBase /EIS_Zona_Web_Lara/src/
```

Línea 8: la base → de donde partimos para las reescrituras de este archivo.

```apache
# Redirigir la raíz del sitio hacia index.php
RewriteRule ^$ index.php [L,QSA]
```

Línea 11: si la URL es la raíz (`^$` significa "nada"), sirve `index.php`. Los flags `[L,QSA]`
significan "hasta aquí (Last)" y "conserva los parámetros de consulta (Query String Append)".

```apache
# Si el archivo o directorio solicitado NO existe físicamente...
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
```

Líneas 14-15: **Condiciones**. `%{REQUEST_FILENAME}` es lo que el usuario pidió. `!-f` = "no
es un archivo real" y `!-d` = "no es un directorio real". Es decir: si el usuario pide un CSS,
JS o imagen que sí existe, se sirve directo; NO se reescribe (para no romper esos archivos).

```apache
# Redirigir hacia index.php pasando la página como parámetro
RewriteRule ^([\w-]+)$ index.php?pagina=$1 [L,QSA]
```

Línea 18: si lo pedido no es un archivo/carpeta real, captura el nombre (`([\w-]+)` = letras,
números, guiones) y lo reescribe como `index.php?pagina=lo_que_escribiste`. Por ejemplo,
`/clientes` se convierte en `?pagina=clientes`. O crea que ese parámetro `$pagina` después lo
utiliza Router. Lo que en el navegador ves como `?pagina=clientes` es accessible en PHP con la
variable **superglobal** `$_GET['pagina']`.

---

## 6. `composer.json` — el autoloader PSR-4

```json
{
    "name": "carlospez/clase",
    "autoload": {
        "psr-4": {
            "App\\": "src/app/"
        }
    },
    "authors": [
        { "name": "Carlos-paez", "email": "carlospaezguerra@gmail.com" }
    ],
    "require": {}
}
```

Línea por línea:

- `"name"` y `"authors"`: solo metadatos del proyecto.
- `"autoload"` → `"psr-4"`: aquí está lo importante. **PSR-4** es una convención para que
  cada clas → guíe a una ruta de archivo. La regla `"App\\": "src/app/"` dice:

  "Cuando alguien use la clase `ClienteController` (que vive en el namespace `App\Controllers`),
  búscala en `src/app/Controllers/ClienteController.php`".

  Y así se deriva automáticamente la ruta de cualquier clase:

  - `App\Controllers\ClienteController`
  - se reemplaza el prefijo `App\` por `src/app/`
  - → `src/app/Controllers/ClienteController.php`

Este mapa es la razón por la que en `index.php` no hubo que escribir ningún `require` de
`Router`: Composer sabe dónde está cada clase. Por eso `src/app/` es la raíz de todos los
namespaces `App\*`.

**Requiere PHP ≥ 8** (usa `match`, `mixed`, promotion de tipos, etc., que verás en el código).

---

## 7. `router.php` — el enrutador (la pieza central)

Este es el corazón de la app. Se ejecuta SIEMPRE en cada petición y decide qué hacer.
Vamos línea por línea, pero antes de empezar, una aclaración de *namespace* y de *visibilidad*.

```php
<?php

namespace App\Core;
```

Líneas 1-3: la etiqueta `<?php` y declaramos que esta clase pertenece al namespace `App\Core`.
Recuerda: PSR-4 → este archivo está en `src/app/core/`.

```php
use App\Controllers\ActivoController;
use App\Controllers\AuthController;
// ... (todos los controladores)
use App\Controllers\UsuarioController;
```

Líneas 5-17: importamos todos los controladores con `use`. Como ya sabes, es solo un atajo
para escribir `ActivoController` en lugar de `App\Controllers\ActivoController`.

```php
/**
 * Enrutador principal (Front Controller).
 * ... (docblock descriptivo)
 */
class Router
{
```

Línea 31: `class Router { ... }` define la clase. El texto entre `/** ... */` se llama
**docblock**: documentación para quien lee el código (y para editores/IDEs).

```php
/** Páginas accesibles sin iniciar sesión. */
private const PUBLIC_PAGES = ['login', 'login_validate'];
```

Línea 34: `const` declara una **constante** (un valor que no cambia). `private` significa "solo
se puede usar dentro de esta clase". `PUBLIC_PAGES` es un **arreglo** (`[...]`) con los nombres
de las páginas que cualquiera puede ver sin iniciar sesión: la de login y la que valida el
login. Es la *politica de acceso* más simple.

```php
private const PAGE_TITLES = [
    'dashboard'   => 'Panel de Control',
    'inventario'  => 'Gestión de inventario',
    'clientes'    => 'Gestión de Clientes',
    // ...
    'roles'       => 'Roles y Permisos',
];
```

Líneas 37-50: otro arreglo, pero aquí cada elemento es una **pareja clave => valor**. La clave
es el nombre de la página (`?pagina=clientes`) y el valor es el título que se mostrará en el
navegador. Fíjate en la sintaxis del arreglo asociativo: `'clave' => 'valor',`.

```php
private const CONTROLLERS = [
    'clientes'    => ClienteController::class,
    'inventario'  => InventarioController::class,
    'usuarios'    => UsuarioController::class,
    // ...
];
```

Líneas 58-72: el mapa **página → controlador**. Es el "directorio telefónico" del despacho.
`ClienteController::class` es una forma de obtener el **nombre completo de la clase** (con su
namespace) sin crear la instancia. Así podemos crear el controlador al vuelo más tarde.

```php
private string $pagina;
```

Línea 74: una **propiedad** (variable de la clase). La notación `private string $pagina` le
dice a PHP: "esto es privado y además SIEMPRE debe ser de tipo `string` (texto)". Esto se llama
**tipado fuerte**. La usaremos para recordar qué página se está pidiendo.

### 7.1 El constructor

```php
public function __construct()
{
    // Sesión única para toda la aplicación.
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
```

Línea 79-81: la **sesión** es una forma de recordar datos entre peticiones (por ejemplo, "el
usuario ya entró"). PHP por defecto **no recuerda nada** entre una petición y otra; la sesión
lo soluciona guardando datos del lado del servidor y enviando una cookie al navegador.
`sessions_status()` devuelve si ya hay una sesión activa; `PHP_SESSION_NONE` es una constante
que significa "no hay ninguna". Si no hay, la creamos con `session_start()`. El `if` evita
crear varias veces por petición.

```php
    // Token CSRF: se genera una sola vez por sesión y se reutiliza.
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
```

Líneas 84-86: **CSRF** es una protección contra *Cross-Site Request Forgery* (un ataque en el
que un sitio externo intenta que un usuario logueado haga acciones sin querer). La defensa es
un **token secreto**: un texto aleatorio generado una sola vez por sesión.

- `random_bytes(32)` genera 32 bytes aleatorios (criptográficamente seguros).
- `bin2hex(...)` los convierte a un texto hexadecimal legible.
- Lo guardamos en `$_SESSION['csrf_token']` si aún no existe (`empty`).
- Ese token se mete en cada formulario y cada petición AJAX; el servidor lo compara y rechaza
  las peticiones que no lo lleven. Lo verás aplicado en los controladores.

### 7.2 El método `handle()` (decide qué hacer)

```php
public function handle(): void
{
    $this->pagina = $this->resolvePagina();
```

Línea 94: primero descubrimos qué página se pidió y la guardamos en la propiedad `pagina`.
`: void` significa que este método **no devuelve ningún valor** (solo hace cosas).

```php
    // Página de cierre de sesión (GET ?pagina=login con intención de logout).
    if (
        $this->pagina === 'login'
        && isset($_GET['logout'])
        && isset($_SESSION['logged_in'])
    ) {
        $this->logout();
    }
```

Líneas 97-103: comprobación de **logout**. La condición usa `&&` ("y además") y dos operadores:

- `$this->pagina === 'login'`: se está en la página de login.
- `isset($_GET['logout'])`: la URL tiene `?logout` (ej. `?pagina=login&logout=1`).
- `isset($_SESSION['logged_in'])`: el usuario ya estaba logueado (hay sesión).

Si ocurren las tres cosas → `$this->logout()` cierra sesión. `isset()` es una función que dice
si una variable existe y no es null. `===` es "igual y del mismo tipo" (más estricto que `==`).

```php
    // Control de acceso: las páginas privadas requieren sesión.
    if (
        !isset($_SESSION['logged_in'])
        && !in_array($this->pagina, self::PUBLIC_PAGES, true)
    ) {
        $this->redirect('login');
    }
```

Líneas 106-111: el **guardia de acceso**. La condición leída en español:

- `!isset($_SESSION['logged_in'])`: NO existe la variable "logueado" (el usuario no entró).
- `!in_array($this->pagina, self::PUBLIC_PAGES, true)`: y ADEMÁS la página que se pide NO está
  en la lista de públicas.

Si cumplen ambas → `$this->redirect('login')` manda al usuario al login. El prefijo `!`
invierte ("no"). `in_array` busca un valor dentro de un arreglo; el `true` final exige que
además el tipo coincida. `self::` se usa para acceder a constantes *de la misma clase*.

```php
    // Despacho de peticiones AJAX de los módulos (?pagina=X&action=Y).
    if (array_key_exists($this->pagina, self::CONTROLLERS) && isset($_GET['action'])) {
        $this->dispatchAction();
    }
```

Líneas 114-116: aquí se detecta una **petición AJAX**. `array_key_exists($pagina, CONTROLLERS)`
pregunta "¿esta página tiene controlador?" y `isset($_GET['action'])` pregunta "¿el usuario
también envió una `action`?". Si sí → `dispatchAction()`.

```php
    // Flujo de inicio de sesión (POST ?pagina=login_validate).
    if ($this->pagina === 'login_validate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        (new AuthController())->login();
        return;
    }
```

Líneas 119-122: si se envió el formulario de login por POST (método `POST`, el que sirve para
enviar datos), se ejecuta el login del `AuthController`. `(new AuthController())->login()`
crea un controlador "en línea" y llama a su método `login()` sin guardarlo en una variable.
`$_SERVER['REQUEST_METHOD']` dice con qué método llegó la petición (`GET` o `POST`).
`return;` aquí termina la ejecución de `handle()` (no quiere seguir renderizando).

```php
    $this->render();
}
```

Línea 124: si no cayó en ninguno de los casos anteriores (ni AJAX, ni login, ni logout), es
una página normal → la renderizamos. Llamando a `render()`.

### 7.3 Cómo se obtiene la página: `resolvePagina()`

```php
private function resolvePagina(): string
{
    $pagina = $_GET['pagina'] ?? 'login';

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
        $pagina = 'login';
    }

    return $pagina;
}
```

- Línea 132: `$_GET['pagina'] ?? 'login'` lee el parámetro de la URL. El operador `??` ("null
  coalescent") dice: "si `$_GET['pagina']` existe y no es null, úsalo; si no, usa 'login'".
  Así evitamos errores cuando no llega `pagina`.
- Línea 134: `preg_match` comprueba una **expresión regular** (patrón). `/^[a-zA-Z0-9_-]+$/`
  significa "solo letras (mayúsculas y minúsculas), números, guión bajo y guión medio, de
  principio (`^`) a fin (`$`)". Si el valor tiene cualquier otro símbolo, lo cambiamos a
  'login'. Es una defensa contra **inyección de ruta** (no permitimos rutas como `../../etc`).
- Línea 139: devolvemos la página saneada.

### 7.4 Despachar el AJAX: `dispatchAction()`

```php
private function dispatchAction(): void
{
    $controllerClass = self::CONTROLLERS[$this->pagina];
    $controller      = new $controllerClass();

    if (method_exists($controller, 'handle')) {
        $controller->handle();
        exit;
    }
}
```

- Línea 146: tomamos la clase del controlador del mapa (`CONTROLLERS[$this->pagina]`).
- Línea 147: la creamos con `new $controllerClass()`. ¡Fíjate! La variable contiene *el nombre*
  de una clase, y `new` con una variable crea una instancia de esa clase. Esto es lo que hace
  "mágico" al mapa: con una sola línea podemos crear cualquier controlador.
- Línea 149: `method_exists` verifica que el controlador tenga un método `handle`.
- Líneas 150-151: lo ejecutamos y luego `exit` para que nadie más renderice después (la
  respuesta AJAX ya está completa).

### 7.5 Logout

```php
private function logout(): void
{
    session_regenerate_id(true);
    $_SESSION = [];
    session_destroy();
    $this->redirect('login');
}
```

- Línea 161: `session_regenerate_id(true)` cambia el ID de sesión (evita *session fixation*).
- Línea 162: `$_SESSION = []` vacía todos los datos de sesión.
- Línea 163: `session_destroy()` destruye la sesión del servidor.
- Línea 164: `$this->redirect('login')` manda al login.

### 7.6 Renderizar: `render()`

```php
private function render(): void
{
    if (in_array($this->pagina, self::PUBLIC_PAGES, true)) {
        $rutaVista = $this->viewsDir() . $this->pagina . '.php';
        if (is_file($rutaVista)) {
            require $rutaVista;
        } else {
            http_response_code(404);
            echo '<h1>Error 404: Página no encontrada</h1>';
        }
        return;
    }
```

- Línea 172: si la página es pública (login), primero se comprueba la ruta armando la cadena:
  `viewsDir() . $this->pagina . '.php'` → por ejemplo `.../Views/login.php`.
- Línea 174: `is_file()` comprueba que exista.
- Línea 175: `require $rutaVista` ejecuta ese archivo PHP (la vista).
- Líneas 176-179: si no existe → `http_response_code(404)` devuelve ese código HTTP, y `echo`
  escribe HTML en la respuesta.

```php
    // Vistas protegidas con layout.
    $rutaVista = $this->viewsDir() . $this->pagina . '.php';
    if (!is_file($rutaVista)) {
        http_response_code(404);
        echo '<h1>Error 404: Página no encontrada</h1>';
        echo "<p>La página <strong>{$this->pagina}</strong> no existe.</p>";
        return;
    }

    $pageTitle   = self::PAGE_TITLES[$this->pagina] ?? 'EIS System';
    $headerExtra = self::PAGE_EXTRA_HEADERS[$this->pagina] ?? '';
    $contentView = $rutaVista;
    $pagina      = $this->pagina;

    require __DIR__ . '/../template/layout.php';
}
```

- Líneas 186-192: mismo chequeo de 404 para páginas protegidas.
- Líneas 194-197: se preparan **variables** que el `layout.php` usará:
  - `$pageTitle`: el título (con `?? 'EIS System'` por si la página no tiene título definido).
  - `$headerExtra`: HTML extra (chips/badges) o vacío.
  - `$contentView`: la ruta a la vista concreta.
  - `$pagina`: el nombre de página (para marcar el menú activo y cargar el JS correcto).
- Línea 199: `require` del `layout.php`, que a su vez incluirá `$contentView`. Veremos eso en
  la sección 15. Los nombres con `$` (como `$pageTitle`) se vuelven variables "visibles" para
  el archivo incluido porque `require` los agrega al mismo ámbito.

### 7.7 Verificar CSRF

```php
public static function verifyCsrfToken(?string $token): bool
{
    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}
```

- `static`: se llama con `Router::verifyCsrfToken(...)` **sin** necesidad de crear instancia
  (lo hacen los controladores).
- Línea 210: `!empty(...)` el token guardado no está vacío.
- Línea 211: `is_string($token)` el token recibido es texto.
- Línea 212: `hash_equals(a, b)` compara dos strings **de forma segura** (no revela cuántos
  caracteres coinciden → evita *timing attacks*). Devuelve `true` si son iguales.

### 7.8 Helpers

```php
private function viewsDir(): string
{
    return __DIR__ . '/../Views/';
}

private function redirect(string $pagina): void
{
    header('Location: ?pagina=' . $pagina);
    exit;
}
```

- `viewsDir()`: devuelve la carpeta de vistas (usa `__DIR__`, la ruta de `router.php`, y sube
  un nivel con `..`).
- `redirect()`: usa `header('Location: ...')` para enviar una **cabecera HTTP de redirección**
  al navegador (le dice "ve a esta otra URL"), y `exit` corta la ejecución. **Aviso:** una
  redirección solo funciona si todavía no se ha enviado ninguna salida HTML.

---

## 8. `Database.php` — la conexión a MySQL

Este archivo sabe cómo conectarse a la base de datos y **solo crea una conexión** (por eso se
llama *Singleton*). Está en `App\Core`.

```php
<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
```

- Línea 10: la propiedad `$instance` guarda la conexión (`PDO`). `?PDO` significa "puede ser
  un PDO o un `null`" (aún sin conectar). Es `static`, es decir, le pertenece a la clase, no a
  cada objeto; así SOLO puede haber una.

```php
    private const DB_HOST    = 'localhost';
    private const DB_NAME    = 'zona_web_lara';
    private const DB_USER    = 'root';
    private const DB_PASS    = '';
    private const DB_CHARSET = 'utf8mb4';
```

Líneas 12-16: las credenciales como constantes (servidor, nombre de la BD, usuario, contraseña
y charset). Aquí es donde configuras la conexión.

```php
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
```

Línea 20: comprueba si **ya** existe una conexión. Si es `null`, es la primera vez.

```php
            $dns = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::DB_HOST,
                self::DB_NAME,
                self::DB_CHARSET
            );
```

Líneas 21-26: **DNS** ("Data Source Name") — el "código de barras" que le dice a PDO cómo y a
dónde conectar. `sprintf` rellena la plantilla `mysql:host=%s;dbname=%s;charset=%s` con cada
valor (cada `%s` es reemplazado por el argumento correspondiente). Resultado:
`mysql:host=localhost;dbname=zona_web_lara;charset=utf8mb4`.

```php
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
```

Líneas 28-32: opciones de la conexión:

- `ATTR_ERRMODE => ERRMODE_EXCEPTION`: cuando haya un error SQL, PHP **lanza una excepción**
  (objeto que corta el flujo) en lugar de solo avisar. Verás estos errores con `try/catch`.
- `ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC`: cuando pidas un registro, devuélvemelo como arreglo
  asociativo (con nombres de columna), p. ej. `['id' => 1, 'nombre' => 'Ana']`.
- `ATTR_EMULATE_PREPARES => false`: usa las consultas preparadas *reales* de MySQL (la forma
  más segura, evita inyección SQL). Lo explicaré en los modelos.

```php
            try {
                self::$instance = new PDO($dns, self::DB_USER, self::DB_PASS, $options);
            } catch (PDOException $e) {
                throw new PDOException('Error de conexión a base de datos', (int)$e->getCode());
            }
        }

        return self::$instance;
    }
```

- Línea 35: intenta conectar `new PDO(...)` con el DNS, usuario, contraseña y opciones.
- Líneas 36-38: si falla, `catch (PDOException $e)` atrapa la excepción y lanza otra con un
  mensaje genérico (para no exponer detalles internos). El `(int)` "castea" (convierte) el
  código del error a entero.
- Línea 41: ya sea recién creada o existente, devuelve la conexión.

**Resumen:** cualquier parte del código puede pedir `Database::getConnection()` y siempre
recibirá la misma conexión abierta a MySQL.

---

## 9. `Model.php` — la clase base de todos los modelos

Todos los modelos (Cliente, Usuario, Inventario...) comparten el mismo trabajo: **validar datos
y hablar con la BD**. Para no repetir código, heredan de esta clase base. Está declarada
`abstract`, o sea: **no se puede instanciar directamente**; solo se puede heredar de ella.

Mira las propiedades más importantes:

```php
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }
```

- Línea 9: `protected PDO $db` — una propiedad que guarda la conexión a la BD. `protected`
  significa "visible para esta clase y para sus hijas (pero no para afuera)".
- Líneas 11-14: el constructor de TODO modelo: obtiene la conexión a BD y la guarda en `$db`.
  Por eso cada modelo, al crearse, ya está listo para consultar MySQL.

Los métodos *getters/setters* de validación son los "ladrillos" que cada modelo reutiliza.
Ejemplo, `sanitizeString()`:

```php
protected function sanitizeString(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
```

- `trim()` quita espacios al inicio y final.
- `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` **convierte** los caracteres especiales HTML
  (`<`, `>`, `"`, `'`, `&`) en sus entidades seguras (`&lt;`, etc.). Esto es la defensa contra
  **XSS** (Cross-Site Scripting): impide que una cadena como `<script>alert(1)</script>` se
  ejecute como código. Así "saneas" los datos antes de usarlos o guardarlos.

Otro helper, `sanitizeInt()`:

```php
protected function sanitizeInt(mixed $input): int
{
    $filtered = filter_var($input, FILTER_VALIDATE_INT);
    return $filtered !== false ? $filtered : 0;
}
```

- `mixed` = "cualquier tipo". `filter_var(..., FILTER_VALIDATE_INT)` intenta interpretar el
  valor como entero; devuelve el entero si lo logra o `false` si no. Con el operador ternario
  (`condición ? sí : no`) devolvemos el entero, o `0` si falló.

Las validaciones lanzan excepciones si algo no cumple. Ejemplo:

```php
protected function validateNotEmpty(string $value, string $field): void
{
    if (trim($value) === '') {
        throw new \InvalidArgumentException("El campo '$field' es obligatorio");
    }
}
```

- `\InvalidArgumentException` es una **excepción** incorporada en PHP. Cuando se lanza
  (`throw`), se detiene el flujo y "salta" a un `catch` que la atrape (verás en controladores).
  El prefijo `\` significa "clase global de PHP".

Otros validadores de la clase base que verás usados:

```php
protected function validateMinLength(string $value, string $field, int $min): void
// lanza error si strlen < min

protected function validateLength(string $value, string $field, int $max): void
// lanza error si strlen > max

protected function validatePattern(string $value, string $pattern, string $message): void
// lanza error si el valor no coincide con el patrón regex

protected function validateEmail(string $email): bool
{ return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }

protected function validateFecha(string $fecha, string $field): void
// comprueba formato YYYY-MM-DD y que sea una fecha real (checkdate)
```

**Consejo:** no hace falta memorizar cada uno. Entiende que existen, definen reglas de
validación reutilizables, y cada modelo los llama para ser consistente.

---

## 10. `Validator.php` — la validación estricta de datos

`Model.php` tiene validadores básicos, pero esta app además tiene una clase **`final`
`Validator`** en `App\Core` que centraliza la validación y **coerción tipada** de TODA la
entrada del usuario (POST/GET), antes de que cualquier cosa llegue a los modelos o a la BD.
`final` = no se puede heredar de ella. Todos sus métodos son `static` (se llaman sin
instancia: `Validator::texto(...)`).

Empieza definiendo límites que NO pueden saltarse (coherentes con las columnas de SQL):

```php
public const MAX_CEDULA   = 20;
public const MAX_NOMBRE   = 100;
public const MAX_EMAIL    = 100;
public const MAX_MONEY    = 99999999.99;   // DECIMAL(10,2)
```

Y patrones (regex) estrictos:

```php
public const PATTERN_CEDULA = '/^[0-9A-Za-z][0-9A-Za-z.\-\s]{3,18}[0-9A-Za-z]$/';
```

Ese patrón lee: "debe empezar con letra/número (1er char), luego entre 3 y 18 de letras,
números, punto, guion o espacio, y terminar con letra/número". Es decir, cédulas tipo
`V-12345678` o `12345678` de 5 a 20 caracteres.

El método más importante es `texto()`:

```php
public static function texto(mixed $value, string $field, array $opts = []): string
{
    $required = (bool)($opts['required'] ?? false);
    $min      = (int)($opts['min'] ?? 1);
    $max      = (int)($opts['max'] ?? PHP_INT_MAX);
    $pattern  = $opts['pattern'] ?? null;
```

- Lee las opciones con valor por defecto. El arreglo `$opts` es el que cada controlador
  personaliza (por ejemplo, `['required' => true, 'min' => 2, 'max' => 100]`). Los `??`
  garantizan que, si no llega esa clave, se use el valor por defecto. El `(bool)` y `(int)`
  convierten a su tipo.
- `PHP_INT_MAX` es el mayor entero posible (es el "sin límite").

```php
    if ($value === null || is_bool($value) || is_array($value) || is_object($value)) {
        throw new \InvalidArgumentException("El campo '$field' no es válido");
    }

    $texto = trim((string)$value);
```

- Se rechazan tipos inesperados (bool/array/object) para evitar trucos.
- `(string)$value` fuerza a texto y `trim` limpia.

```php
    if ($texto === '') {
        if ($required) { throw new \InvalidArgumentException("El campo '$field' es obligatorio"); }
        return '';
    }

    self::rechazarControl($texto, $field);

    $len = mb_strlen($texto);
    if ($len < $min) { throw ... }
    if ($len > $max) { throw ... }
    if ($pattern !== null && !preg_match($pattern, $texto)) { throw ... }

    return $texto;
}
```

- Si está vacío y era obligatorio → error; si no era obligatorio → devuelve `''`.
- `rechazarControl()` descarta **caracteres de control** (bytes invisibles que podrían
  "colarse"). `mb_strlen` cuenta caracteres (respeta acentos, es multibyte).
- Aplica mínimo, máximo y patrón. `preg_match` devuelve `1` si coincide, `0` si no, `false` si
  errores. El `!` obliga a que "SI NO coincide" → error.
- Si pasó todo, devuelve el texto validado.

Hay más métodos: `entero()`, `decimal()`, `fecha()`, `enum()`, `email()`, `bool()`, `id()`,
`busqueda()`, `itemsVenta()`, y las API amigables `cedula()`, `rif()`, `telefono()`,
`username()`, `tiempoUso()`, `numeroOrden()`. Todos tienen la misma filosofía: **validar,
convertir a tipo correcto y tirar `InvalidArgumentException` si algo falla**, con mensajes que
los controladores transforman en respuestas JSON `{success:false}`.

---

## 11. Los modelos — el ejemplo de `Cliente.php`

Ya entendiste la base. Ahora veamos un modelo real de principio a fin. Está en
`src/app/Models/Cliente.php`.

```php
<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Cliente extends Model
{
    private int $id = 0;
    private string $cedula = '';
    private string $nombre = '';
    private string $apellido = '';
    private string $direccion = '';
    private string $telefono = '';
```

- Línea 8: `class Cliente extends Model` — la clase Cliente **hereda** de `Model` (`extends`).
  Esto le da acceso automáticamente a la conexión `$db` y a todos los validadores de la base.
- Líneas 10-15: propiedades privadas con valores por defecto y tipadas. Representan las
  columnas de la tabla `clientes` pero como **estado interno** del objeto.

```php
    private const MIN_CEDULA    = 5;
    private const MAX_CEDULA    = 20;
    private const MIN_NOMBRE    = 2;
    private const MAX_NOMBRE    = 100;
```

Líneas 17-24: constantes de límites para no escribir números "mágicos" repetidos.

### 11.1 Los setters (donde se valida)

```php
public function setCedula(string $cedula): void
{
    $cedula = $this->sanitizeString($cedula);
    $this->validateNotEmpty($cedula, 'cédula');
    $this->validateMinLength($cedula, 'cédula', self::MIN_CEDULA);
    $this->validateLength($cedula, 'cédula', self::MAX_CEDULA);
    $this->validatePattern($cedula, '/^[0-9A-Za-z][0-9A-Za-z.\-\s]{3,18}[0-9A-Za-z]$/', 'La cédula no tiene un formato válido');
    $this->cedula = $cedula;
}
```

Fíjate en la **encapsulación**: no puedes asignar `$cedula` directamente; debes pasar por
`setCedula()`, que sanea y valida en cadena:

1. `sanitizeString` → contra XSS.
2. `validateNotEmpty` → obligatoria.
3. `validateMinLength` / `validateLength` → rango de longitud.
4. `validatePattern` → formato correcto.
5. Solo si todo pasó, se guarda en `$this->cedula`.

Este patrón se repite en `setNombre`, `setApellido`, `setDireccion`, `setTelefono`. Cuando
algún módulo usa estos setters mal valida automáticamente.

### 11.2 Los getters y `toArray()`/`fromArray()`

Los getters devuelven cada propiedad:

```php
public function getCedula(): string { return $this->cedula; }
```

Y los métodos "puente" convierten entre objeto y arreglo:

```php
public function toArray(): array
{
    return [
        'id'        => $this->id,
        'cedula'    => $this->cedula,
        'nombre'    => $this->nombre,
        // ...
    ];
}

public static function fromArray(array $data): self
{
    $cliente = new self();
    $cliente->setId((int)($data['id'] ?? 0));
    $cliente->setCedula($data['cedula'] ?? '');
    // ...
    return $cliente;
}
```

- `toArray()`: pasa el objeto a un arreglo (cómodo para JSON).
- `fromArray()` (sobre `static`): recibe un arreglo, crea un `Cliente` y rellena cada propiedad
  **a través de los setters** (así también se valida al construir). `$data['cedula'] ?? ''`
  evita errores si falta esa clave.

### 11.3 Consultas a la base de datos

Leer todos los clientes:

```php
public function obtenerClientes(): array
{
    $stmt = $this->db->query("SELECT id, cedula, nombre, apellido, direccion, telefono FROM clientes ORDER BY nombre");
    return $stmt->fetchAll();
}
```

- `$this->db` es la conexión PDO heredada.
- `->query(...)` ejecuta un SQL de solo lectura directamente.
- `$stmt` es el *statement* (resultado). `->fetchAll()` devuelve TODAS las filas como arreglo
  de arreglos asociativos (por el `FETCH_ASSOC` configurado).

Leer **uno** con parámetro (la forma SEGURA con parámetros), aquí es donde se evita la
**inyección SQL**:

```php
public function obtenerClientePorId(int $id): array|false
{
    $id = $this->sanitizeInt($id);
    $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}
```

- `array|false` = "devuelve un arreglo o `false` si no hay resultados" (unión de tipos).
- Línea clave: se **prepara** la consulta con un comodín `?` en el `WHERE id = ?`. Eso es un
  **placeholder**.
- `bindParam(1, $id, PDO::PARAM_INT)` **asocia** el valor al placeholder nº1 indicando que es
  entero. El motor de BD escapa el valor por sí mismo, así que un atacante no puede inyectar
  código SQL. (Regla de oro en esta app: **nunca** concatenar datos del usuario dentro de una
  cadena SQL; siempre usar `prepare`+`bindParam`.)
- `->fetch()` devuelve UNA fila (o `false`).

Insertar (lo mismo, con `?`):

```php
public function crearCliente(string $cedula, string $nombre, string $apellido, string $direccion, string $telefono): bool
{
    $this->setCedula($cedula);
    $this->setNombre($nombre);
    $this->setApellido($apellido);
    $this->setDireccion($direccion);
    $this->setTelefono($telefono);

    $sql = "INSERT INTO clientes (cedula, nombre, apellido, direccion, telefono) VALUES (?, ?, ?, ?, ?)";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(1, $this->cedula, PDO::PARAM_STR);
    $stmt->bindParam(2, $this->nombre, PDO::PARAM_STR);
    // ...
    return $stmt->execute();
}
```

- Primero pasa todos los datos por los setters validadores.
- Luego hace el `INSERT` con 5 placeholders y `bindParam` en cada uno.
- `->execute()` dispara el INSERT; devuelve `true`/`false`.

Un dato curioso del final del archivo: `obtenerOCrearPorCedula()`. Es un método que usan otros
módulos (ventas, asesorías, cyber) para reutilizar un cliente: si la cédula ya existe, lo
actualiza; si no, lo crea. Es un buen ejemplo de **reutilización de lógica en la capa de
modelo**, y de cómo los setters (con su validación) mantienen consistente el dato aunque el
llamante sea otro módulo.

---

## 12. Los controladores — `ClienteController.php`

El controlador recibe la petición AJAX y orquesta: **valida → busca en modelo → devuelve JSON**.

```php
<?php

namespace App\Controllers;

use App\Core\Router;
use App\Core\Validator;
use App\Models\Cliente;

class ClienteController
{
    private Cliente $model;

    public function __construct()
    {
        $this->model = new Cliente();
    }
```

- Línea 15: tiene una propiedad que guarda el modelo `Cliente`.
- Líneas 17-20: el constructor crea el modelo (que a su vez abre la conexión a BD).

### 12.1 `handle()` — el "switch" de acciones

```php
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
        // ... (se explica abajo)
    }
}
```

Intenta quitarle el miedo al código de arriba:

- Línea 30: `header('Content-Type: application/json')` le dice al navegador "lo que viene será
  JSON", no HTML.
- Línea 33: `$action` guarda la `action` que viene en la URL (o cadena vacía).
- Línea 35: `match()` es una expresión de PHP 8 que compara `$action` con cada caso y ejecuta
  lo que esté a la derecha de la flecha `=>`. Funciona como un `switch` más limpio.
- Si `action` vale `'listar'` → corre `$this->listar()`, etc.
- Si no coincide con ninguna → ejecuta el `default`, que responde con un error JSON.

### 12.2 Manejo de errores

```php
    try {
        match ($action) { /* ... */ };
    } catch (\PDOException $e) {
        $msg = $e->getMessage();
        if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
            echo json_encode(['success' => false, 'error' => 'No se puede eliminar: el cliente tiene registros asociados.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
        }
    } catch (\InvalidArgumentException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
    }
```

- `try { ... } catch (Tipo $e) { ... }`: intenta ejecutar; si en algún momento se lanza una
  excepción, salta al `catch` que coincida con su tipo. Es la forma de PHP de manejar errores
  sin romper la aplicación.
- `catch (\PDOException $e)`: errores de la base de datos. Si el mensaje contiene el texto de
  una "foreign key constraint" (es decir, intentan eliminar un cliente que tiene registros
  relacionados), respondemos un mensaje útil; si no, un error genérico.
- `catch (\InvalidArgumentException $e)`: errores de validación (los que tiran `Validator`).
  Respondemos con el propio mensaje `$e->getMessage()`.
- `catch (\Exception $e)`: cualquier otro error → mensaje genérico "Error interno".
- Todo se responde como **JSON** con `json_encode` y un arreglo como
  `['success' => false, 'error' => '...']`. El JavaScript del cliente lo interpreta.

### 12.3 Una acción lista: crear

```php
private function crear(): void
{
    if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
        return;
    }

    $cedula    = Validator::cedula($_POST['cedula'] ?? null, 'cédula');
    $nombre    = Validator::texto($_POST['nombre'] ?? null, 'nombre', ['required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El nombre contiene caracteres no permitidos']);
    $apellido  = Validator::texto($_POST['apellido'] ?? null, 'apellido', ['required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El apellido contiene caracteres no permitidos']);
    $direccion = Validator::texto($_POST['direccion'] ?? null, 'dirección', ['required' => false, 'max' => 500]);
    $telefono  = Validator::telefono($_POST['telefono'] ?? null, 'teléfono');

    if ($this->model->existeCedula($cedula)) {
        echo json_encode(['success' => false, 'error' => 'Ya existe un cliente con esa cédula']);
        return;
    }

    $resultado = $this->model->crearCliente($cedula, $nombre, $apellido, $direccion, $telefono);
    echo json_encode(
        $resultado
            ? ['success' => true, 'message' => 'Cliente creado exitosamente']
            : ['success' => false, 'error' => 'Error al crear el cliente']
    );
}
```

Línea por línea:

1. **CSRF** (`!Router::verifyCsrfToken(...)`): si el token no es válido, responde error y
   `return` (no continúa). El token viene en `$_POST['csrf_token']` (lo inyecta el JS/HTML).
2. **Validación de cada campo** con `Validator`. Los datos del formulario llegan en `$_POST`
   (superglobal para datos enviados por POST). La función `?? null` evita avisos si llega un
   campo sin existir. El resultado de cada `Validator::*` es el dato **ya validado y limpio**.
3. **Regla de negocio**: no puede haber dos clientes con la misma cédula → `existeCedula()`.
4. **Llamar al modelo** `crearCliente(...)`.
5. **Responder JSON** con un operador ternario: si `$resultado` (true/false) → mensaje de éxito
   o de error.

Mira cómo en este controlador **nunca se toca SQL directamente**: solo se valida y se delega en
el modelo. Eso es el patrón MVC bien aplicado: controlador "gordo en lógica", modelo "gordo en
datos".

---

## 13. `AuthController.php` — el inicio de sesión

Es el controlador del login. Tiene dos métodos: `login()` y `logout()`.

```php
public function login(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ?pagina=login');
        exit;
    }
```

- Línea 30: si la petición no es POST (alguien entró directo a esta URL por GET), redirigimos
  al login. Solo se puede iniciar sesión enviando el formulario.

```php
    if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        header('Location: ?pagina=login&error=1');
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $username = Validator::username($username, 'usuario');
    } catch (\InvalidArgumentException) {
        header('Location: ?pagina=login&error=1');
        exit;
    }
```

- Línea 35: verifica el token CSRF. Si no coincide → `?error=1` (el login.php mostrará el
  mensaje).
- Líneas 40-41: leemos el usuario y contraseña del `$_POST`. Se usa `trim` para quitar espacios.
- Líneas 43-48: validamos el username con `Validator::username`. OJO: no se guarda la contraseña en
  variables de sesión ni se imprime; solo se usa para verificar.

```php
    if (empty($password)) { header('Location: ?pagina=login&error=1'); exit; }
    if (mb_strlen($username) < 3) { header('Location: ?pagina=login&error=1'); exit; }

    $usuario = $this->model->autenticar($username, $password);
```

- Línea 59: se llama al **modelo** `autenticar($username, $password)`. Devuelve el usuario en
  arreglo si las credenciales son correctas, o `false` si no. (Ese método hace
  `password_verify` contra el hash guardado; nunca guardamos la contraseña en texto plano.)

```php
    if ($usuario) {
        session_regenerate_id(true);

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id']   = $usuario['id'];
        $_SESSION['username']  = $usuario['user_name'];
        $_SESSION['nombre']    = $usuario['nombre'];

        header('Location: ?pagina=dashboard');
        exit;
    }

    header('Location: ?pagina=login&error=1');
    exit;
}
```

- Línea 61: si `$usuario` es un arreglo (verdadero, autenticado):
  - `session_regenerate_id(true)` renueva el ID de sesión (seguridad).
  - Guardamos en `$_SESSION` los datos que queremos recordar: logueado, id, username, nombre.
  - Redirigimos al `dashboard`.
- Línea 73: si `$usuario` fue `false`, redirigimos al login con `error=1`.

`logout()` hace el cierre (aunque en el Router ya hay un `logout()` propio que es el que usa
la URL): destruye la sesión y redirige al login.

---

## 14. Las vistas — `login.php` y `clientes.php`

Las vistas mezclan HTML con trocitos de PHP (etiquetas `<?php ... ?>`) para meter datos
dinámicos. Veamos primero `login.php`, que es independiente (sin layout):

```php
<form action="?pagina=login_validate" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    ...
    <input type="text" name="username" id="username" required autofocus>
    <input type="password" name="password" id="password" required>
    <button type="submit">Iniciar Sesión</button>
</form>
```

Lo importante:

- El `action="?pagina=login_validate"` envía al enrutador para que ejecute el login.
- `method="post"`: los datos de password van en el POST (no se ven en la URL).
- El `<input type="hidden" name="csrf_token">` inserta el token CSRF para esta petición. Se
  escribe con PHP: `<?php echo ... ?>` es la etiqueta de apertura/cierre del PHP dentro del HTML.
  `htmlspecialchars` evita que un token raro rompa el atributo.

Aviso: NO guardes nunca la contraseña en HTML; va en el campo `password` que el navegador oculta
y viaja encriptado (si hay HTTPS) en el cuerpo del POST.

Ahora `clientes.php` (una vista **dentro del layout**). Mira cómo calcula un dato PHP y luego
solo escribe HTML:

```php
<?php
use App\Models\Cliente;

$clienteModel = new Cliente();
$totalClientes = $clienteModel->totalClientes();
?>
```

- Líneas 1-6: se abre bloque PHP. Se crea el modelo y se pregunta "¿cuántos clientes hay?".
  `totalClientes()` hace un `SELECT COUNT(*)` y devuelve el número. Ese valor mostramos en la
  tarjeta KPI:

```php
<div class="metric-value" id="kpi-total"><?php echo $totalClientes; ?></div>
```

- El `<?php echo $totalClientes; ?>` entre el HTML imprime el número donde toca. (Hay un atajo
  equivalente `<?= $totalClientes ?>`.)

Luego viene la tabla vacía:

```php
<table class="striped" id="tabla-clientes">
    <thead>
        <tr>
            <th>Cédula</th>
            <th>Cliente</th>
            ...
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="5">Cargando...</td></tr>
    </tbody>
</table>
```

Fíjate: **la tabla empieza vacía** (solo dice "Cargando..."). Los datos reales los va a
rellenar el JavaScript cuando haga la petición AJAX. Al final de la vista está el formulario
en un **modal** (ventana emergente) para crear/editar un cliente.

**Lección importante sobre las vistas:** su trabajo es SOLO mostrar. No deben contener
consultas complejas ni lógica de negocio. Idealmente piden datos al modelo (como aquí) o los
reciben del JavaScript.

---

## 15. `layout.php` — el diseño común (plantilla maestra)

Casi todas las páginas se parecen: mismo menú lateral, misma barra superior, mismo pie. Para no
repetir ese HTML en cada vista, existe `layout.php`. Las vistas concretas se "inyectan" dentro.

Recuerda las variables que `router.php` preparó antes de `require 'layout.php'`:
`$pageTitle`, `$headerExtra`, `$contentView`, `$pagina`.

```php
<!DOCTYPE html>
<html lang="es">
<head>
    ...
    <title><?php echo $pageTitle; ?> - EIS System</title>
    <link rel="stylesheet" href="Public/css/materialize.min.css">
    ...
</head>
<body>
    <!-- MENÚ LATERAL (sidebar) -->
    <ul id="slide-out" class="sidenav sidenav-fixed">
        <li><a href="?pagina=dashboard" class="...">Dashboard</a></li>
        <li><a href="?pagina=inventario" class="...">Inventario</a></li>
        <!-- ... todos los módulos ... -->
        <li><a href="?pagina=login" class="...">Cerrar Sesión</a></li>
    </ul>
```

- Línea 20: el `<title>` usa `$pageTitle` → el título cambia por página.
- Cada enlace del menú apunta a `?pagina=X`. Ese mismo `$pagina` se usa para marcar el enlace
  con `active` (por ejemplo `class="...<?php echo $pagina === 'dashboard' ? ' active' : ''; ?>"`).

El contenido dinámico de cada página se mete aquí:

```php
<main>
    <div class="container">
        <?php require $contentView; ?>
    </div>
</main>
```

- Línea 176: `require $contentView` ejecuta la vista concreta (p. ej. `clientes.php`) DENTRO de
  esta estructura común.

Al final de la página se cargan los scripts globales y, según la página, un módulo JS:

```php
<script src="Public/js/app.core.js"></script>
<!-- ... -->

<?php if ($pagina === 'clientes'): ?>
    <script src="Public/js/app.clientes.js"></script>
<?php endif; ?>
```

- La **carga condicional** con `if ($pagina === 'clientes')` hace que solo se cargue el JS del
  módulo que corresponde a la página actual. Fíjate en la sintaxis alternativa de PHP:
  `<?php if (...) : ?> ... <?php endif; ?>` es una forma legible de escribir el if dentro de HTML.

También hay aquí un bloque que inyecta el **token CSRF y el id de usuario** en un objeto global
`window.EIS`, e instala en jQuery un `beforeSend` que añade el `csrf_token` a cada petición
AJAX tipo POST. Así el JS de la página no tiene que añadirlo manualmente.

---

## 16. El JavaScript — `app.clientes.js`

Este es el archivo que hace "viva" a la página de clientes en el navegador. Usa **jQuery**
(una librería que simplifica seleccionar elementos y hacer peticiones). Lo encierra todo en:

```js
$(function () {
    ...
});
```

- `$(function(){})` es la forma corta de decir "cuando el documento HTML esté listo, ejecuta
  esto". Es el punto de entrada del script.

```js
var API = '?pagina=clientes&action=';
```

- Guardamos la base de la URL. Cada petición AJAX se arma como `API + 'listar'` =
  `?pagina=clientes&action=listar`. Eso es exactamente lo que el Router espera (recordemos:
  página `clientes` + `action`).

Tiene una función para refrescar el KPI y otra para refrescar la tabla:

```js
function refrescarKPI() {
    $.getJSON(API + 'kpis', function (r) {
        if (!r.success) return;
        $('#kpi-total').text(r.data.total);
    }).fail(function () {
        EIS.toast('Error al cargar indicadores', 'red', 'error');
    });
}
```

- `$.getJSON(url, callback)` hace una petición **AJAX GET** y espera JSON.
- El `callback` recibe `r` (una "r" de respuesta). `r.success` viene de PHP
  (`['success' => true, ...]`). `r.data.total` es el total de clientes.
- `$('#kpi-total').text(...)` selecciona el elemento con `id="kpi-total"` y cambia su texto.
- `.fail(...)` se ejecuta si hay error de conexión; muestra un "toast" (notificación).

```js
function refrescarTabla() {
    $.getJSON(API + 'listar', function (r) {
        if (!r.success) return;

        var tbody = $('#tabla-clientes tbody');
        tbody.empty();

        if (!r.data || r.data.length === 0) {
            tbody.html('<tr>...</tr>');
            EIS.datatableRefresh('#tabla-clientes');
            return;
        }

        $.each(r.data, function (i, c) {
            var inits = ((c.nombre || '?')[0] + (c.apellido || '?')[0]).toUpperCase();
            var row = '<tr data-id="' + c.id + '">';
            row += '<td>' + $('<span>').text(c.cedula).html() + '</td>';
            row += '<td>' + c.nombre + ' ' + c.apellido + '</td>';
            row += '<td>' + (c.direccion || '-') + '</td>';
            row += '<td>' + (c.telefono || '-') + '</td>';
            row += '<td>... botones editar/eliminar ...</td>';
            tbody.append(row);
        });

        EIS.datatableRefresh('#tabla-clientes');
    }).fail(function () {
        EIS.toast('Error al cargar clientes', 'red', 'error');
    });
}
```

- Obtiene los clientes del backend (`action=listar`).
- Vacía el `tbody` y recorre cada cliente con `$.each(r.data, ...)`.
- Construye una fila (`row`) como cadena con `<tr>` y `<td>`, inyectando los datos del cliente.
  **Detalle de seguridad:** para el texto usan `$('<span>').text(c.cedula).html()`: jquery crea
  un elemento, le pone texto (que escapea caracteres) y devuelve su HTML — esto evita que un
  dato con `<script>` se ejecute en el navegador (protección XSS del lado cliente).
- `c.nombre || '?'` es: "usa `c.nombre`, o si está vacío usa '?'". `[0]` coge la primera letra.
- `(c.direccion || '-')` muestra un guión si no hay dirección.
- Al final `EIS.datatableRefresh('#tabla-clientes')` le dice a DataTables que redibuje la tabla
  (con la paginación/búsqueda).

Cuando se envía el formulario de nuevo/editar:

```js
$('#form-cliente').on('submit', function (e) {
    e.preventDefault();
    // ... validaciones ...
    var id = $('#cliente-id').val();
    var accion = id ? 'actualizar' : 'crear';
    $.post(API + accion, $(this).serialize(), function (r) {
        if (r.success) {
            EIS.toast(r.message, 'green', 'check_circle');
            refrescarKPI();
            refrescarTabla();
        } else {
            EIS.toast(r.error, 'red', 'error');
        }
    }, 'json').fail(function () {
        EIS.toast('Error de conexión', 'red', 'error');
    });
});
```

- `e.preventDefault()` evita que el navegador **recargue la página** al enviar el formulario
  (queremos usar AJAX).
- Se decide: si hay `id` → `actualizar`, si no → `crear`.
- `$.post(API + accion, $(this).serialize(), ...)`: manda los datos del formulario codificados
  (`serialize`) por POST al endpoint. El `csrf_token` lo añade el `beforeSend` del layout.
- Si `r.success` es true → toast verde y refresca tabla/KPI. Si no → toast rojo con el error.

Y el `delete`:

```js
$(document).on('click', '.btn-eliminar-cliente', function () {
    var id = $(this).data('id');
    if (confirm('¿Eliminar el cliente "' + $(this).data('nombre') + '"?...')) {
        $.post(API + 'eliminar', { id: id }, function (r) {
            if (r.success) { EIS.toast(r.message, 'green', 'check_circle'); refrescarKPI(); refrescarTabla(); }
            else { EIS.toast(r.error, 'red', 'error'); }
        }, 'json');
    }
});
```

- `$(document).on('click', '.btn-eliminar-cliente', ...)`: **delegación de eventos**. Como las
  filas se crean dinámicamente, el listener se pone en el `document` y detecta clics en
  elementos que tengan esa clase (aunque se hayan añadido después).
- `$(this).data('id')` lee el atributo `data-id` que pusimos en la fila.
- `confirm(...)` muestra un diálogo nativo; solo si el usuario acepta hace el POST a `eliminar`.
- Observa cómo `{ id: id }` se envía como datos del POST (sin serializar manualmente).

Al final del archivo:

```js
if (window.EIS && EIS.datatableWireSearch) {
    EIS.datatableWireSearch('#tabla-clientes', '#searchCliente');
} else {
    $('#searchCliente').on('keyup', ...); // fallback
}

refrescarTabla();
$('.modal').modal();
EIS.datatable('#tabla-clientes');
```

- `EIS.datatableWireSearch(...)` conecta la caja de búsqueda con la tabla (filtro en cliente).
- `refrescarTabla()` carga los datos por primera vez al abrir la página.
- `$('.modal').modal()` activa los modales de Materialize (nuevo/editar cliente).
- `EIS.datatable('#tabla-clientes')` inicializa la tabla con datos (orden, paginación).

**Patrón AJAX resumido:** el JavaScript pide datos por `$.getJSON`/`$.post` a `?pagina=...
&action=...`, el controlador PHP responde JSON, y el JS dibuja el resultado sin recargar la
página.

---

## 17. `Exporter.php` — descargar CSV/Excel/PDF

Para descargar reportes en distintos formatos, la app usa `Exporter` (`App\Core`). Los métodos
son estáticos y reciben un título, las columnas y las filas.

```php
public static function csv(string $titulo, array $columnas, array $filas): void
{
    $out = fopen('php://temp', 'r+');        // crea un "archivo" temporal en memoria
    fputcsv($out, $columnas);                // escribe la fila de encabezados
    foreach ($filas as $fila) {
        fputcsv($out, self::filaPlana($fila)); // cada fila de datos
    }
    rewind($out);                            // volver al inicio del buffer
    $contenido = stream_get_contents($out);  // leer todo el contenido
    fclose($out);                            // cerrar

    self::send("text/csv; charset=UTF-8", self::nombre($titulo, 'csv'), "\xEF\xBB\xBF" . $contenido);
}
```

- `fopen('php://temp', ...)` es un truco: un archivo solo en memoria (no toca disco).
  `fputcsv` escribe una línea CSV escapando comas y comillas correctamente.
- `self::filaPlana()` convierte cada fila en un arreglo de textos (si un valor es un arreglo,
  lo convierte a JSON).
- `self::nombre()` genera un nombre de archivo seguro con fecha (p. ej. `clientes_20260905_1200.csv`).
- `self::send()` envía las cabeceras HTTP para forzar la **descarga** y `echo` del contenido.
  `"\xEF\xBB\xBF"` es el BOM UTF-8, para que Excel muestre bien los acentos.

Para Excel usa un HTML especial de Microsoft que Excel abre como tabla; para PDF usa
`PdfBuilder` (que genera un PDF mínimo sin librerías externas). Lo importante es el patrón:
**todos terminan en `self::send($mime, $nombre, $contenido)`**, que pone
`Content-Disposition: attachment` (fuerza descarga) e imprime el contenido.

---

## 18. La base de datos — `estructura.sql`

Este archivo contiene el **esquema**: el SQL que crea las 21 tablas. No es código PHP, pero
es imprescindible para entender qué guardan los modelos.

```sql
CREATE DATABASE IF NOT EXISTS zona_web_lara;
USE zona_web_lara;
```

- Crea la base de datos (si no existe) y la selecciona.

```sql
CREATE TABLE roles
(
    id          INT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol  VARCHAR(50)  NOT NULL,
    descripcion VARCHAR(500)          DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;
```

Desglosando `CREATE TABLE roles`:

- `id INT PRIMARY KEY AUTO_INCREMENT`: columna entera, clave primaria (identificador único de
  cada fila) que se **auto-incrementa** (MySQL pone 1, 2, 3... solo).
- `nombre_rol VARCHAR(50) NOT NULL`: texto de hasta 50 chars, obligatorio (`NOT NULL`).
- `descripcion VARCHAR(500) DEFAULT NULL`: texto opcional; si no se da, queda `NULL`.
- `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP`: guarda la fecha/hora de creación
  automáticamente.
- `ENGINE = InnoDB`: motor que soporta transacciones y claves foráneas.
- `CHARACTER SET utf8mb4` / collation `utf8mb4_spanish_ci`: codificación completa de caracteres,
  con reglas de ordenación en español (ñ, tildes).

Otro ejemplo con una **clave foránea** (relación entre tablas):

```sql
CREATE TABLE cliente_asesoria
(
    id         INT PRIMARY KEY AUTO_INCREMENT,
    fk_cliente int,
    email      VARCHAR(80) NOT NULL DEFAULT 'N/A',
    ...
    FOREIGN KEY (fk_cliente) REFERENCES clientes (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ...
```

- `fk_cliente int`: guarda el `id` de un cliente.
- `FOREIGN KEY (fk_cliente) REFERENCES clientes (id)`: impone que `fk_cliente` solo pueda tener
  valores que existan en `clientes.id`. A esto se le llama **integridad referencial**.
- `ON DELETE RESTRICT`: no deja borrar un cliente si hay `cliente_asesoria` que lo referencie.
  (Recuerda el mensaje "el cliente tiene registros asociados" que vimos en el controlador:
  ¡es este `RESTRICT` el que provoca la excepción de FK!).
- `ON UPDATE CASCADE`: si cambia el `id` del cliente, se actualizan los `fk_cliente` que le
  apuntan.

Y la tabla de clientes, la que ya conocemos del modelo:

```sql
CREATE TABLE clientes
(
    id       INT PRIMARY KEY AUTO_INCREMENT,
    cedula   VARCHAR(20) UNIQUE NOT NULL,   -- UNIQUE: no se repite la cédula
    nombre   VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    direccion TEXT NOT NULL,
    telefono VARCHAR(20) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_spanish_ci;
```

Fíjate: los límites de `Validator` (CEDULA=20, NOMBRE=100, etc.) **coinciden** con estos
`VARCHAR` — el backend valida lo mismo que la base de datos permite. Esa coherencia es
intencional y muy buena práctica.

El `seed_data.sql` contiene los **datos de prueba iniciales** (INSERT INTO ...) para poder
usar la app sin escribir datos a mano.

---

## 19. Glosario rápido para principiantes

| Término | Qué significa |
|---------|---------------|
| **PHP** | Lenguaje que corre en el servidor; genera HTML/JSON dinámicamente. |
| **HTTP / GET / POST** | Protocolo de petición. `GET` pide datos (van en la URL); `POST` envía datos (van en el cuerpo). |
| **$_GET / $_POST** | Superglobales de PHP con los datos que llegan por GET y POST. |
| **$_SESSION** | Datos que persisten entre peticiones (identifican al usuario logueado). |
| **$_SERVER** | Superglobal con info del servidor/petición (método, IP, etc.). |
| **Front Controller** | Patrón: un solo archivo de entrada (`index.php`) por el que pasa todo. |
| **Router / Enrutador** | Decide qué código ejecutar según la URL (`?pagina=...&action=...`). |
| **Namespace** | "Apellido" de las clases (`App\Core\Router`) para evitar choques. |
| **use** | Atajo para no escribir el namespace completo. |
| **Autoloader (PSR-4)** | Carga automáticamente una clase según su namespace y ruta. |
| **Clase / Objeto / `new`** | Plantilla (clase) e instancia (objeto) creada con `new`. |
| **Método** | Función que pertenece a una clase (`$obj->metodo()`). |
| **`->`** | Accede a una propiedad/método de un objeto. |
| **`::`** | Accede a algo estático/constante (`self::`, `Validator::texto`). |
| **Property (propiedad)** | Variable que pertenece a una clase. |
| **Constructor `__construct()`** | Método que se ejecuta al hacer `new`. |
| **`private` / `protected` / `public`** | Visibilidad: solo esta clase / esta y sus hijas / cualquier parte. |
| **`static`** | Pertenece a la clase, no a una instancia; se llama con `::`. |
| **`abstract`** | Clase que no se instancia, solo se hereda. |
| **`final`** | Clase que no se puede heredar. |
| **`extends` / heredar** | Una clase que toma lo de otra (base). |
| **MVC** | Model-Vista-Controlador: separa datos, presentación y lógica. |
| **Modelo** | La capa que habla con la base de datos. |
| **Vista** | La capa que dibuja el HTML. |
| **Controlador** | Orquesta: valida, llama al modelo, responde. |
| **CRUD** | Crear, Leer, Actualizar, Eliminar (operaciones típicas). |
| **AJAX** | Petición al servidor sin recargar la página, desde JavaScript. |
| **JSON** | Formato de intercambio de datos entre el JS y el PHP. |
| **PDO** | Extensión de PHP para conectar y consultar bases de datos de forma segura. |
| **Prepared statements** | Consultas con `?` y `bindParam` (evitan inyección SQL). |
| **Inyección SQL** | Ataque que mete SQL malicioso; se evita con prepared statements. |
| **XSS** | Ataque que inyecta scripts; se evita con `htmlspecialchars`/escape. |
| **CSRF** | Ataque de falsificación de petición; se evita con token de sesión. |
| **Session fixation** | Ataque de secuestro de sesión; se evita con `session_regenerate_id`. |
| **Token / hash** | Texto de verificación. `password_hash`/`password_verify` guardan/verifican contraseñas. |
| **Expresión regular (regex)** | Patrón para validar/coincidir textos (`preg_match`). |
| **Arreglo** | Colección de valores. Asociativo = claves con nombres. |
| **`foreach` / `match`** | `foreach` recorre un arreglo; `match` compara un valor con varios casos. |
| **Excepción** | Error que "salta" con `throw` y se atrapa con `try/catch`. |
| **`require` / `require_once`** | Incluye otro archivo PHP. `_once` evita duplicados. |
| **`isset()` / `empty()` / `??`** | `isset`: existe y no es null; `empty`: está vacío; `??`: valor o alternativo. |
| **Ternary `? :`** | Forma corta de `if` que devuelve un valor (`cond ? si : no`). |
| **SQL** | Lenguaje de consulta a bases de datos (`SELECT`, `INSERT`, `UPDATE`, `DELETE`). |
| **`*` en SELECT** | "Todas las columnas". |
| **FOREIGN KEY** | Relación entre tablas; respeta integridad referencial. |

---

## Cómo seguir aprendiendo con esta app

1. **Sigue el flujo de un "listar"** con la app en marcha: abre herramientas del desarrollador
   (F12) → pestaña Network → ve la petición `?pagina=clientes&action=listar` → mira su respuesta
   JSON y dónde la dibuja el JS.
2. **Cambia algo pequeño y observa**: por ejemplo, modifica un texto de la vista `clientes.php`
   y recarga. Así ves qué parte genera el HTML inicial.
3. **Prueba a romper a propósito la validación**: envía una cédula de 3 caracteres y mira el
   mensaje JSON de `Validator`.
4. **Lee el resto de modelos y controladores** (Inventario, Venta, Usuario...) con la misma
   mentalidad: verás patrones repetidos. Los módulos son variaciones del mismo esquema
   (validar → modelo → JSON).
5. **Practica escribiendo**: cambia la regla de validación de un campo y crea un reporte nuevo
   siguiendo a `Exporter`. La mejor forma de aprender es modificar y ver qué pasa.

> **Resumen de una línea:** toda petición entra por `index.php`, el `Router` decide si es una
> página o una petición AJAX, el `Controlador` valida y delega en el `Modelo`, el `Modelo`
> consulta safe a MySQL (prepared statements), y la respuesta (HTML o JSON) vuelve al
> navegador; el JavaScript la dibuja y así se comporta la aplicación.

*Documento generado a partir del código real de EIS System (Zona Web Lara), rama `Carlos`.*