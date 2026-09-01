# Explicación Detallada Línea por Línea - EIS System (PHP MVC)

Este documento proporciona un análisis exhaustivo y explicativo línea por línea de los archivos de código que componen el núcleo de la aplicación PHP. La aplicación sigue el patrón arquitectónico **Modelo-Vista-Controlador (MVC)**, con un enrutador central de entrada única (**Front Controller**), un sistema de gestión de conexiones mediante el patrón **Singleton**, y soporte para aplicaciones web progresivas (**PWA**) y cambios de tema en caliente con Materialize CSS.

---

## Índice
1. [Arquitectura General](#arquitectura-general)
2. [Front Controller: `src/index.php`](#1-front-controller-srcindexphp)
3. [Configuración Procedural de Base de Datos: `src/Config/database.php`](#2-configuración-procedural-de-base-de-datos-srcconfigdatabasephp)
4. [Singleton de Base de Datos: `src/app/core/Database.php`](#3-singleton-de-base-de-datos-srcappcoredatabasephp)
5. [Modelo Base Abstracto: `src/app/core/Model.php`](#4-modelo-base-abstracto-srcappcoremodelphp)
6. [Enrutador de la Aplicación: `src/app/core/router.php`](#5-enrutador-de-la-aplicación-srcappcorerouterphp)
7. [Controlador de Autenticación: `src/app/Controllers/AuthController.php`](#6-controlador-de-autenticación-srcappcontrollersauthcontrollerphp)
8. [Script de Consola CLI: `src/cli/create_user.php`](#7-script-de-consola-cli-srcclicreate_userphp)
9. [Modelo Orientado a Objetos vs Procedural: `Usuario.php` & `crud_users.php`](#8-modelo-orientado-a-objetos-vs-procedural-usuario-y-crud_users)
10. [Plantilla Maestra de la Interfaz: `src/app/template/layout.php`](#9-plantilla-maestra-de-la-interfaz-srcapptemplatelayoutphp)
11. [Control de Acceso Seguro: `src/app/Views/login_validate.php`](#10-control-de-acceso-seguro-srcappviewslogin_validatephp)
12. [Filtro y Fallback Offline: `src/offline.php`](#11-filtro-y-fallback-offline-srcofflinephp)

---

## Arquitectura General

La aplicación está diseñada bajo una estructura robusta y desacoplada de la siguiente manera:
- **Punto de Entrada Único (`index.php`)**: Implementa el patrón *Front Controller*. Todas las solicitudes web pasan por este archivo. Utiliza el autoloader de Composer (compatible con PSR-4) para cargar clases automáticamente según sus espacios de nombres (*namespaces*).
- **Enrutamiento Inteligente (`Router.php`)**: Analiza la variable `$_GET['pagina']` de forma segura mediante expresiones regulares contra ataques de *Path Traversal*. Gestiona tanto las peticiones de renderizado de vistas comunes como las llamadas asíncronas AJAX redirigiéndolas a sus correspondientes controladores API JSON.
- **Acceso Eficiente a Datos (`Database.php` y `Model.php`)**: Utiliza el patrón de diseño *Singleton* para garantizar que exista solo una conexión activa de PDO con MySQL por cada solicitud, optimizando el consumo de recursos de base de datos.
- **Vistas con Layout Común (`layout.php`)**: Las páginas que requieren autenticación se cargan dinámicamente dentro de un contenedor o plantilla maestra que provee el menú de navegación superior, la barra lateral (*sidebar*), soporte de tema oscuro/claro y el Service Worker para funcionamiento fuera de línea (*Offline PWA*).

---

## 1. Front Controller: `src/index.php`

Este archivo es el punto de partida de toda la ejecución de la aplicación web.

```php
1: <?php
```
* **Línea 1**: Abre la etiqueta de PHP para iniciar la interpretación del archivo.

```php
2: // =============================================================================
3: // ARCHIVO DE ENTRADA PRINCIPAL (Front Controller)
4: // =============================================================================
5: // Propósito: Punto de entrada único para todas las peticiones web.
6: //            Carga el autoloader de Composer, instancia el Router
7: //            y ejecuta el manejador para procesar la solicitud.
8: // Todos los request pasan por aquí (usando reglas de reescritura del servidor).
9: // =============================================================================
```
* **Líneas 2-9**: Bloque de comentarios explicativos sobre el propósito fundamental de este Front Controller.

```php
11: // Carga el autoloader de Composer para tener disponibles todas las clases
12: // y namespaces registrados en el proyecto (autoloading PSR-4)
13: require_once __DIR__ . '/../vendor/autoload.php';
```
* **Líneas 11-12**: Comentario aclarando el uso de Composer para la carga de dependencias y clases.
* **Línea 13**: Usa `require_once` combinando la constante mágica `__DIR__` (directorio del archivo actual) para subir un nivel en el directorio e incluir el autoloader de Composer (`vendor/autoload.php`). Esto registra de forma transparente la carga automática para todas las clases del proyecto bajo la convención PSR-4.

```php
15: // Importa la clase Router del namespace App\Core para usarla sin prefijo
16: use App\Core\Router;
```
* **Línea 15**: Comentario explicativo sobre la importación de clases.
* **Línea 16**: Declara el alias `use App\Core\Router` permitiéndonos instanciar la clase simplemente escribiendo `new Router()` en lugar del nombre completo con su namespace.

```php
18: // Crea una instancia del enrutador principal (inicia sesión y resuelve la página solicitada)
19: $router = new Router();
```
* **Línea 18**: Comentario indicando el inicio del Router.
* **Línea 19**: Instancia un objeto de la clase `Router` y lo asigna a la variable `$router`. En el constructor de esta clase se iniciará la sesión con `session_start()` y se resolverá de manera segura qué página se ha solicitado.

```php
20: // Procesa la solicitud entrante: determina qué acción ejecutar y renderiza la respuesta
21: $router->handle();
```
* **Línea 20**: Comentario sobre la ejecución de la solicitud.
* **Línea 21**: Llama al método público `handle()` de la clase `Router` para procesar el ciclo de vida del request y retornar la respuesta visual (HTML) o de datos (JSON) adecuada.

---

## 2. Configuración Procedural de Base de Datos: `src/Config/database.php`

Mantiene un puente de compatibilidad procedural con archivos legacy y scripts de administración rápida.

```php
1: <?php
```
* **Línea 1**: Abre la etiqueta de PHP.

```php
2: // =============================================================================
3: // CONFIGURACIÓN DE LA BASE DE DATOS
4: // =============================================================================
5: // Propósito: Establecer la conexión PDO a MySQL usando las credenciales
6: //            definidas aquí. Este archivo es incluido por los CRUDs basados
7: //            en funciones sueltas (crud_*.php). Crea la variable $pdo.
8: // NOTA: Existe una versión moderna en App\Core\Database (patrón Singleton).
9: // =============================================================================
```
* **Líneas 2-9**: Bloque explicativo sobre el uso y alcance de este archivo, advirtiendo sobre la existencia de la clase moderna basada en el patrón Singleton.

```php
11: // Configuración de la conexión a la base de datos MySQL usando PDO
```
* **Línea 11**: Comentario aclaratorio.

```php
13: // Dirección del servidor de base de datos (localhost = misma máquina)
14: $host = "localhost";
```
* **Líneas 13-14**: Define en la variable `$host` la dirección IP o dominio del servidor de base de datos. Por defecto es `localhost` (entorno local).

```php
15: // Nombre de la base de datos a la que se conectará la aplicación
16: $db = "zona_web_lara";
```
* **Líneas 15-16**: Almacena en `$db` el nombre físico del esquema de base de datos MySQL a consultar: `zona_web_lara`.

```php
17: // Usuario de MySQL con permisos sobre la base de datos
18: $user = "root";
```
* **Líneas 17-18**: Define en `$user` el usuario de la base de datos MySQL (`root` en desarrollo).

```php
19: // Contraseña del usuario de MySQL (vacía en entorno de desarrollo local)
20: $pass = "";
```
* **Líneas 19-20**: Define la contraseña de acceso en `$pass` (vacía por defecto en instalaciones típicas locales).

```php
21: // Juego de caracteres UTF-8 que soporta emojis y caracteres especiales
22: $charset = 'utf8mb4';
```
* **Líneas 21-22**: Configura el juego de caracteres como `utf8mb4` para garantizar que la codificación admita acentos, la letra ñ y emojis directamente en la persistencia de datos.

```php
24: // Cadena de conexión (DSN - Data Source Name) que PDO necesita para conectarse
25: $dns = "mysql:host=$host;dbname=$db;charset=$charset";
```
* **Líneas 24-25**: Concatena las variables anteriores en una sola cadena formateada llamada DSN (Data Source Name) necesaria para inicializar el adaptador PDO de PHP para bases de datos MySQL.

```php
27: // Opciones de configuración de la conexión PDO
28: $options = [
```
* **Líneas 27-28**: Define un array asociativo llamado `$options` para ajustar el comportamiento de la instancia de conexión PDO.

```php
29:     // Modo de error: lanza excepciones cuando ocurre un error SQL
30:     PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
```
* **Líneas 29-30**: Configura la llave `PDO::ATTR_ERRMODE` al valor de constante `PDO::ERRMODE_EXCEPTION`. Esto fuerza a PDO a lanzar objetos de tipo `PDOException` cuando ocurra un error de consulta SQL o de conexión, facilitando la captura estructurada de errores con bloques `try-catch`.

```php
31:     // Modo de obtención por defecto: devuelve los resultados como array asociativo
32:     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
```
* **Líneas 31-32**: Establece el formato predeterminado para la obtención de registros en `PDO::FETCH_ASSOC`. Esto retorna cada registro SQL como un array donde las llaves corresponden a los nombres de las columnas, omitiendo índices numéricos duplicados.

```php
33:     // Desactiva la emulación de consultas preparadas (usa consultas reales, más seguro)
34:     PDO::ATTR_EMULATE_PREPARES   => false,
35: ];
```
* **Líneas 33-35**: Ajusta `PDO::ATTR_EMULATE_PREPARES` a `false`. Al desactivarlo, se delega de forma obligatoria la preparación y ejecución parametrizada de las sentencias directo al motor de base de datos MySQL. Esto representa la barrera de seguridad más robusta contra ataques de **inyección de consultas SQL (SQL Injection)**.

```php
37: try {
```
* **Línea 37**: Abre un bloque de ejecución controlada `try` para interceptar posibles problemas críticos durante la conexión.

```php
38:     // Intenta crear la conexión PDO con los parámetros definidos anteriormente
39:     $pdo = new PDO($dns, $user, $pass, $options);
```
* **Línea 38-39**: Intenta instanciar la clase predefinida de PHP `PDO` con los parámetros DNS, usuario, contraseña y opciones de configuración, almacenando el socket activo en la variable procedural `$pdo`.

```php
41: }catch (\PDOException $e) {
```
* **Línea 41**: Captura cualquier error de tipo `PDOException` que ocurra dentro del bloque `try` y lo asigna a la variable local `$e`.

```php
42:     // Captura cualquier error de conexión a la base de datos
43:     // Relanza la excepción con el mensaje y código original para que sea manejada arriba
44:     throw new \PDOException($e->getMessage(), (int)$e->getCode());
```
* **Líneas 42-43**: Comentario sobre el relanzamiento del error.
* **Línea 44**: Vuelve a lanzar una excepción estructurada `PDOException` enviando el mensaje original (`$e->getMessage()`) y el código correspondiente (`(int)$e->getCode()`) para delegar el control de fallas a la clase superior que haya invocado el archivo de conexión.

```php
46: }
```
* **Línea 46**: Cierra la llave del bloque `catch`.

---

## 3. Singleton de Base de Datos: `src/app/core/Database.php`

Implementa el patrón de diseño Singleton para centralizar y compartir la conexión activa PDO.

```php
1: <?php
```
* **Línea 1**: Abre la etiqueta de PHP.

```php
2: // =============================================================================
3: // CLASE Database (Conexión a Base de Datos - Patrón Singleton)
4: // =============================================================================
5: // Propósito: Gestionar una única conexión PDO a MySQL para toda la aplicación.
6: //            Implementa el patrón Singleton para evitar múltiples conexiones
7: //            que consuman recursos innecesarios.
8: // =============================================================================
```
* **Líneas 2-8**: Comentarios explicativos sobre la importancia del patrón Singleton para mitigar la sobrecarga por excesivos sockets abiertos simultáneamente.

```php
10: // Declara el namespace 'App\Core' para organizar esta clase dentro de la aplicación
11: namespace App\Core;
```
* **Línea 10**: Comentario sobre el espacio de nombres.
* **Línea 11**: Registra la clase bajo el namespace `App\Core`, alineado a las reglas del cargador automático de Composer.

```php
13: // Importa la clase PDO de PHP para poder usarla sin escribir el namespace completo
14: use PDO;
```
* **Línea 14**: Declara la importación de la clase global nativa `PDO`.

```php
15: // Importa la clase PDOException para manejar errores de base de datos
16: use PDOException;
```
* **Línea 16**: Importa la clase global `PDOException`.

```php
18: /**
19:  * Clase Database - Implementa el patrón Singleton para la conexión PDO a MySQL.
...
24:  */
25: class Database
26: {
```
* **Líneas 18-24**: Bloque de documentación PHPDoc de la clase `Database`.
* **Línea 25**: Declara el inicio de la clase pública `Database`.

```php
27:     /**
28:      * Única instancia de la conexión PDO (patrón Singleton).
...
35:      */
36:     private static ?PDO $instance = null;
```
* **Líneas 27-35**: Bloque PHPDoc de la propiedad de instancia estática.
* **Línea 36**: Declara la variable de propiedad estática privada `$instance`, la cual puede almacenar una instancia de tipo `PDO` o un valor nulo (`?PDO`). Se inicializa explícitamente en `null`. Al ser estática, su valor persiste a través de la vida de ejecución de la solicitud.

```php
38:     /**
39:      * Obtiene o crea la conexión PDO a la base de datos (Singleton).
...
47:      */
48:     public static function getConnection(): PDO
49:     {
```
* **Líneas 38-47**: PHPDoc del método estático para obtener la conexión única.
* **Línea 48**: Define el método de acceso público estático `getConnection()`, configurado para retornar obligatoriamente un objeto del tipo nativo `PDO`.

```php
50:         // Solo crea la conexión si aún no existe (primera vez que se llama)
51:         if (self::$instance === null) {
```
* **Línea 50**: Comentario explicativo.
* **Línea 51**: Verifica mediante una estructura condicional `if` si la propiedad `$instance` de la propia clase (`self::`) sigue valiendo `null` (lo cual ocurre únicamente en la primera llamada de toda la petición).

```php
52:             // Configuración del servidor MySQL
53:             $host = 'localhost';               // Dirección del servidor
54:             $db   = 'zona_web_lara';           // Nombre de la base de datos
55:             $user = 'root';                    // Usuario de MySQL
56:             $pass = '';                        // Contraseña (vacía en desarrollo)
57:             $charset = 'utf8mb4';              // Juego de caracteres UTF-8 completo
```
* **Líneas 52-57**: Comentarios e inicialización de las credenciales y variables de red de la base de datos MySQL local de manera segura y delimitada en alcance dentro de este bloque de configuración de conexión.

```php
59:             // Cadena de conexión (DSN - Data Source Name) con los parámetros configurados
60:             $dns = "mysql:host=$host;dbname=$db;charset=$charset";
```
* **Líneas 59-60**: Genera la cadena formateada DSN (`mysql:host=...;dbname=...;charset=...`).

```php
62:             // Opciones de configuración de PDO
63:             $options = [
64:                 PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en errores SQL
65:                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Los resultados se devuelven como arrays asociativos
66:                 PDO::ATTR_EMULATE_PREPARES   => false,                   // Usa consultas preparadas reales (más seguro contra inyección SQL)
67:             ];
```
* **Líneas 62-67**: Define el array de comportamiento avanzado de PDO (Lanzamiento de excepciones críticas, array asociativo por defecto y ejecución de consultas preparadas directas del servidor), replicando las óptimas medidas de seguridad de la versión procedural.

```php
69:             try {
70:                 // Intenta crear la conexión PDO con las credenciales y opciones definidas
71:                 self::$instance = new PDO($dns, $user, $pass, $options);
```
* **Línea 69**: Abre el bloque de contingencia de errores `try`.
* **Línea 71**: Crea una nueva instancia de `PDO` y la asigna directamente a la propiedad estática `self::$instance`. A partir de este momento, cualquier llamada subsiguiente omitirá este bloque completo.

```php
72:             } catch (PDOException $e) {
73:                 // Si falla la conexión, relanza la excepción para que sea manejada más arriba
74:                 throw new PDOException($e->getMessage(), (int)$e->getCode());
75:             }
```
* **Líneas 72-75**: Captura excepciones de base de datos (`PDOException`) y las relanza estructuralmente para evitar fallos mudos que oculten problemas de infraestructura de red o credenciales incorrectas.

```php
76:         }
```
* **Línea 76**: Cierra la estructura del condicional `if (self::$instance === null)`.

```php
78:         // Devuelve la conexión (ya sea recién creada o la que ya existía)
79:         return self::$instance;
80:     }
81: }
```
* **Línea 79**: Retorna de forma segura la conexión `PDO` única y activa guardada en la propiedad de clase.
* **Línea 80**: Finaliza el cuerpo del método estático `getConnection`.
* **Línea 81**: Finaliza la declaración de la clase `Database`.

---

## 4. Modelo Base Abstracto: `src/app/core/Model.php`

Actúa como plantilla común para todos los modelos específicos de datos de la aplicación, con helpers de validación reutilizables.

```php
1: <?php
```
* **Línea 1**: Abre la etiqueta de PHP.

```php
2: // =============================================================================
3: // CLASE ABSTRACTA Model (con helpers de validación)
4: // =============================================================================
5: // Propósito: Clase base para todos los modelos de la aplicación.
6: //            Proporciona la conexión a la base de datos PDO y helpers
7: //            de validación reutilizables para todos los modelos.
8: // =============================================================================
```
* **Líneas 2-8**: Explica el papel de la clase abstracta para consolidar la inyección implícita de la conexión Singleton PDO y los helpers de validación.

```php
10: // Declara el namespace 'App\Core' para organizar esta clase dentro de la aplicación
11: namespace App\Core;
```
* **Línea 11**: Declara el espacio de nombres de pertenencia `App\Core`.

```php
13: use PDO;
```
* **Línea 13**: Importa la clase base del sistema PDO de PHP.

```php
16: abstract class Model
17: {
```
* **Línea 16**: Define la clase como `abstract`.

```php
19:     protected PDO $db;
```
* **Línea 19**: Declara la propiedad protegida `$db` que acepta objetos `PDO`.

```php
21:     public function __construct()
22:     {
23:         $this->db = Database::getConnection();
24:     }
```
* **Línea 23**: Invoca `Database::getConnection()` para resolver la conexión PDO Singleton.

### Helpers de Sanitización

```php
    protected function sanitizeString($value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    protected function sanitizeInt($value): int
    {
        return (int) $value;
    }

    protected function sanitizeFloat($value): float
    {
        return (float) $value;
    }
```
* **sanitizeString()**: Limpia la cadena con `trim()` y `htmlspecialchars()` para prevenir XSS.
* **sanitizeInt()**: Convierte a entero seguro.
* **sanitizeFloat()**: Convierte a float seguro.

### Helpers de Validación

```php
    protected function validateNotEmpty($value, string $fieldName): void
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException("El campo {$fieldName} no puede estar vacío");
        }
    }

    protected function validateMinLength($value, int $min, string $fieldName): void
    {
        if (strlen(trim($value)) < $min) {
            throw new \InvalidArgumentException("{$fieldName} debe tener al menos {$min} caracteres");
        }
    }

    protected function validatePattern($value, string $pattern, string $fieldName, string $message): void
    {
        if (!preg_match($pattern, $value)) {
            throw new \InvalidArgumentException($message);
        }
    }

    protected function validatePositive($value, string $fieldName): void
    {
        if ($value < 0) {
            throw new \InvalidArgumentException("{$fieldName} debe ser positivo");
        }
    }

    protected function validateGreaterOrEqual($value, int $min, string $fieldName): void
    {
        if ($value < $min) {
            throw new \InvalidArgumentException("{$fieldName} debe ser mayor o igual a {$min}");
        }
    }

    protected function validateEmail($value, string $fieldName): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("{$fieldName} debe ser un email válido");
        }
    }
```
* Cada helper lanza `\InvalidArgumentException` cuando la validación falla.
* Los controladores capturan esta excepción y muestran el mensaje al usuario.

### Helper de Verificación en BD

```php
    protected function existeEnTabla(string $tabla, string $columna, $valor): bool
    {
        $sql = "SELECT COUNT(*) FROM {$tabla} WHERE {$columna} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$valor]);
        return (int) $stmt->fetchColumn() > 0;
    }
```
* Verifica si un valor existe en una tabla, útil para unicidad y FK checks.

---

## 5. Enrutador de la Aplicación: `src/app/core/router.php`

Este archivo contiene el motor de enrutamiento principal. Se detallan sus partes más representativas y la lógica de validación de rutas seguras.

```php
11: namespace App\Core;
```
* **Línea 11**: Define el namespace organizativo de la clase como `App\Core`.

```php
21: class Router
22: {
```
* **Línea 21**: Declara el inicio de la clase centralizada de control `Router`.

```php
31:     private string $pagina;
```
* **Línea 31**: Propiedad de visibilidad privada que mantiene en memoria el nombre de la ruta o vista validada a procesar (ej. "dashboard", "inventario", "login").

```php
39:     public function __construct()
40:     {
41:         // Inicia o reanuda la sesión del usuario para acceder a $_SESSION
42:         session_start();
43:         // Genera un token CSRF único para esta sesión
44:         $this->csrfToken = bin2hex(random_bytes(32));
45:         // Determina qué página se pidió mediante el método resolvePage()
46:         $this->pagina = $this->resolvePage();
47:     }
```
* **Línea 39**: Constructor del Router.
* **Línea 42**: Ejecuta `session_start()` al inicio del ciclo de vida de la solicitud.
* **Línea 44**: Genera un token CSRF único con `bin2hex(random_bytes(32))` **una sola vez por sesión** (solo si `$_SESSION['csrf_token']` está vacío). Este token se inyecta en `window.EIS.csrfToken` para uso en AJAX y en `<input name="csrf_token">` para formularios, y se valida con `Router::verifyCsrfToken()`.
* **Línea 46**: Resuelve y valida el nombre del recurso solicitado.

```php
57:     public function handle(): void
58:     {
```
* **Línea 57**: Define el método principal `handle()`, que orquesta todo el ciclo de la solicitud.

```php
60:         // Página de cierre de sesión (GET ?pagina=login con intención de logout).
61:         if (
62:             $this->pagina === 'login'
63:             && isset($_GET['logout'])
64:             && isset($_SESSION['logged_in'])
65:         ) {
66:             $this->logout();
67:         }
```
* **Líneas 60-67**: Detecta la intención de cierre de sesión (página `login` con parámetro `logout` y sesión activa) y delega en `logout()`, que regenera el ID de sesión, limpia `$_SESSION` y redirige a login.

```php
69:         // Control de acceso: las páginas privadas requieren sesión.
70:         if (
71:             !isset($_SESSION['logged_in'])
72:             && !in_array($this->pagina, self::PUBLIC_PAGES, true)
73:         ) {
74:             $this->redirect('login');
75:         }
```
* **Líneas 69-75**: Guardián de acceso. Si no hay sesión activa y la página no está en `PUBLIC_PAGES` (`['login', 'login_validate']`), redirige mediante `redirect('login')` (cabecera `Location` + `exit`).

```php
77:         // Despacho de peticiones AJAX de los módulos (?pagina=X&action=Y).
78:         if (array_key_exists($this->pagina, self::CONTROLLERS) && isset($_GET['action'])) {
79:             $this->dispatchAction();
80:         }
```
* **Líneas 77-80**: Si la página solicitada existe como clave en el mapa `CONTROLLERS` y la URL trae `?action=...`, delega en `dispatchAction()`, que instancia el controlador correspondiente y llama a su método `handle()` (que termina con `exit`).

```php
82:         // Flujo de inicio de sesión (POST ?pagina=login_validate).
83:         if ($this->pagina === 'login_validate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
84:             (new AuthController())->login();
85:             return;
86:         }
```
* **Líneas 82-86**: Intercepta el flujo de login: solo acepta POST hacia `login_validate` y deriva en `AuthController::login()`.

```php
88:         $this->render();
89:     }
```
* **Línea 88**: Si no es ninguna acción especial ni AJAX, procede a renderizar la vista mediante `render()`.

```php
97:     private function resolvePage(): string
98:     {
99:         // Página por defecto si no se especifica en la URL
100:         $pagina = 'login';
```
* **Línea 97**: Declara el método privado auxiliar `resolvePage()`.
* **Línea 100**: Define de forma predeterminada que la ruta inicial de la aplicación sea `'login'`, actuando como puerto de seguridad principal.

```php
103:         if (!empty($_GET['pagina'])) {
104:             $pagina = $_GET['pagina'];
105:         }
```
* **Líneas 103-105**: Si se envía la variable de parámetro URL `pagina` mediante método GET y no está vacía, actualiza la variable local temporal con dicho valor.

```php
107:         // Valida que el nombre solo contenga caracteres seguros (letras, números, guiones)
108:         // Esto evita inyección de rutas como "../../etc/passwd"
109:         if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pagina)) {
110:             // Si no pasa la validación, redirige a login como medida de seguridad
111:             $pagina = 'login';
112:         }
```
* **Línea 109**: Aplica una expresión regular `^[a-zA-Z0-9_-]+$` sobre el nombre de la página solicitada. Esto valida estrictamente que la ruta esté construida únicamente de letras (mayúsculas o minúsculas), números, guiones medios o guiones bajos. **Esto bloquea por completo intentos de ataques de Path Traversal** como inyecciones de directorios con secuencias de puntos y barras diagonales (`../../`).
* **Línea 111**: En caso de no superar la validación regex de seguridad, se fuerza el retorno al estado seguro de `'login'`.

```php
114:         // Devuelve el nombre de la página ya validado
115:         return $pagina;
116:     }
```
* **Línea 115**: Retorna el nombre resuelto de la página, totalmente sanitizado para prevenir inyecciones de archivos locales (*Local File Inclusion*).

```php
108:     private function dispatchAction(): void
109:     {
110:         $controllerClass = self::CONTROLLERS[$this->pagina];
111:         $controller      = new $controllerClass();
112:
113:         if (method_exists($controller, 'handle')) {
114:             $controller->handle();
115:             exit;
116:         }
117:     }
```
* **Líneas 108-117**: Resuelve la clase del controlador desde el mapa `CONTROLLERS` (cada `pagina` apunta a una clase como `ClienteController::class`), la instancia y delega en su método `handle()`. Si el controlador posee ese método, ejecuta y termina con `exit` para evitar el render posterior. Este patrón centraliza el despacho: en lugar de una cascada de `if (isAjaxX()) runXController()`, basta añadir la entrada `pagina => Clase::class` al mapa `CONTROLLERS`.

```php
120:     private function render(): void
121:     {
122:         // Vista de inicio de sesión (páginas públicas).
123:         if (in_array($this->pagina, self::PUBLIC_PAGES, true)) {
124:             $rutaVista = $this->viewsDir() . $this->pagina . '.php';
125:             if (is_file($rutaVista)) {
126:                 require $rutaVista;
127:             } else {
128:                 http_response_code(404);
129:                 echo '<h1>Error 404: Página no encontrada</h1>';
130:             }
131:             return;
132:         }
```
* **Líneas 120-132**: El método `render()` distingue las páginas públicas (login, login_validate) y las incluye de forma individual sin layout mediante `require`, con control de existencia del archivo (404 si no existe).

```php
134:         // Vistas protegidas con layout.
135:         $rutaVista = $this->viewsDir() . $this->pagina . '.php';
136:
137:         if (!is_file($rutaVista)) {
138:             http_response_code(404);
139:             echo '<h1>Error 404: Página no encontrada</h1>';
140:             echo "<p>La página <strong>{$this->pagina}</strong> no existe.</p>";
141:             echo "<a href='?pagina=dashboard'>Volver al dashboard</a>";
142:             return;
143:         }
144:
145:         $pageTitle  = self::PAGE_TITLES[$this->pagina] ?? 'EIS System';
146:         $headerExtra = self::PAGE_EXTRA_HEADERS[$this->pagina] ?? '';
147:         $contentView = $rutaVista;
148:         $pagina      = $this->pagina;
149:
150:         require __DIR__ . '/../template/layout.php';
151:     }
```
* **Líneas 134-151**: Para recursos protegidos, valida la existencia de la vista (404 con enlace de retorno si falta), resuelve el título y cabeceras extra desde las constantes `PAGE_TITLES` y `PAGE_EXTRA_HEADERS`, define `$pageTitle`, `$headerExtra`, `$contentView` y `$pagina`, e incluye `layout.php` para inyectar el contenido dentro del menú lateral y navbar.

---

## 6. Controlador de Autenticación: `src/app/Controllers/AuthController.php`

Maneja los procesos de validación de identidad y cierre de sesiones de usuarios en la plataforma, con CSRF y session hardening.

```php
1: <?php
```
* **Línea 1**: Abre la etiqueta de PHP.

```php
11: namespace App\Controllers;
```
* **Línea 11**: Declara el namespace de este controlador bajo `App\Controllers` (PSR-4).

```php
14: use App\Models\Usuario;
```
* **Línea 14**: Importa la clase del modelo específico de acceso de datos `Usuario`.

```php
23: class AuthController
24: {
25:     private Usuario $model;
```
* **Línea 23**: Declara la clase `AuthController`.
* **Línea 25**: Define la propiedad privada de tipado fuerte `Usuario $model`.

```php
40:     public function __construct()
41:     {
42:         $this->model = new Usuario();
43:     }
```
* **Línea 42**: Instancia el modelo especializado en usuarios.

```php
56:     public function login(): void
57:     {
58:         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
59:             header('Location: ?pagina=login');
60:             exit;
61:         }
```
* **Línea 58**: Evalúa si el método de petición es estrictamente `POST`.

```php
67:         $username = $_POST['username'] ?? '';
68:         $password = $_POST['password'] ?? '';
```
* **Líneas 67-68**: Extrae las credenciales de forma segura con operador de fusión de nulos.

```php
73:         $usuario = $this->model->autenticar($username, $password);
```
* **Línea 73**: Invoca al método de negocio `autenticar()` que busca el registro en BD y ejecuta `password_verify()`.

```php
76:         if (($usuario)) {
77:             session_regenerate_id(true);
78:             $_SESSION['logged_in'] = true;
79:             $_SESSION['user_id']   = $usuario['id'];
80:             $_SESSION['username']  = $usuario['user_name'];
81:             $_SESSION['nombre']    = $usuario['nombre'];
82:             header('Location: ?pagina=dashboard');
83:             exit;
84:         }
```
* **Línea 77**: `session_regenerate_id(true)` — regenera el ID de sesión para prevenir session fixation attacks.
* **Líneas 78-81**: Almacena las variables de contexto del usuario en la sesión.

```php
105:     public function logout(): void
106:     {
107:         session_regenerate_id(true);
108:         session_destroy();
109:         header('Location: ?pagina=login');
110:         exit;
111:     }
```
* **Línea 107**: `session_regenerate_id(true)` — regenera el ID antes de destruir la sesión por seguridad.
* **Línea 108**: Destruye completamente la sesión del servidor.

---

## 7. Script de Consola CLI: `src/cli/create_user.php`

Este script permite realizar la administración y creación rápida de cuentas de usuarios utilizando el motor de línea de comandos de PHP (CLI), totalmente aislado de la interfaz web.

```php
13: // Carga el autoloader de Composer para tener disponibles todas las clases del proyecto
14: require_once __DIR__ . '/../../vendor/autoload.php';
```
* **Línea 14**: Sube dos niveles en los directorios partiendo del archivo local en `src/cli/` para enlazar e iniciar el autoloader de Composer.

```php
17: use App\Core\Database;
```
* **Línea 17**: Importa el singleton de la conexión `Database`.

```php
20: $longopts = [
21:     'username:',    // Nombre de usuario (requiere valor)
22:     'password:',    // Contraseña (requiere valor)
23:     'nombre:',      // Nombre real (requiere valor)
24:     'apellido:',    // Apellido (requiere valor)
25:     'email:',       // Correo electrónico (requiere valor)
26:     'help',         // Muestra ayuda (no requiere valor)
27: ];
```
* **Línea 20**: Define un array asociativo `$longopts` detallando los argumentos con formato largo (dos guiones `--argumento=valor`) que aceptará el script en consola. Los dos puntos al final de cada argumento indican que es obligatorio enviarle un valor adjunto.

```php
30: $options = getopt('', $longopts);
```
* **Línea 30**: Llama a la función nativa `getopt()` de PHP para parsear los argumentos ingresados por la terminal e incorporarlos en el array `$options`.

```php
33: if (isset($options['help']) || empty($options['username']) || empty($options['password']) || empty($options['nombre']) || empty($options['email'])) {
34:     // Muestra las instrucciones de uso del script
35:     echo "Usage: php src/cli/create_user.php --username=USER --password=PASS --nombre=\"First Name\" --apellido=\"Last Name\" --email=user@example.com\n";
36:     // Sale con código 0 si fue ayuda, o 1 si faltaron parámetros (indicando error)
37:     exit(isset($options['help']) ? 0 : 1);
38: }
```
* **Línea 33**: Evalúa si se solicitó ayuda, o si falta rellenar los datos mínimos para crear una cuenta (usuario, contraseña, nombre y correo).
* **Línea 35**: Imprime instrucciones claras y un ejemplo de uso de sintaxis en la terminal.
* **Línea 37**: Invoca la función `exit()` enviando código de sistema `0` (operación limpia si solo se consultó la ayuda) o `1` (error de argumentos ausentes).

```php
41: $username = $options['username'];
42: $password = $options['password'];
43: $nombre   = $options['nombre'];
44: $apellido = $options['apellido'] ?? '';
45: $email    = $options['email'];
```
* **Líneas 41-45**: Asigna los valores de consola filtrados a variables locales limpias.

```php
51: try {
52:     $db = Database::getConnection();
53: } catch (Exception $e) {
54:     echo "Error: Could not connect to database. " . $e->getMessage() . "\n";
55:     exit(1);
56: }
```
* **Líneas 51-56**: Resuelve e inicializa de manera aislada la conexión única estática del Singleton a MySQL. Si falla (servidor inactivo, credenciales incorrectas, etc.), lo comunica de manera clara en terminal y frena la ejecución.

```php
61: // Prepara una consulta SQL para verificar si ya existe un usuario con el mismo username o email
62: $check = $db->prepare("SELECT id FROM usuarios WHERE user_name = ? OR email = ?");
63: // Ejecuta la consulta pasando el username y email como parámetros (previene inyección SQL)
64: $check->execute([$username, $email]);
```
* **Línea 62**: Prepara la consulta parametrizada para buscar colisiones de registros preexistentes.
* **Línea 64**: Ejecuta de forma segura enviando los parámetros en el array de ejecución de PDO para desactivar toda opción de inyección SQL.

```php
66: if ($check->fetch()) {
67:     // Muestra mensaje de error indicando que el usuario o email ya están registrados
68:     echo "Error: A user with that username or email already exists.\n";
69:     // Sale del script con código 1 indicando error
70:     exit(1);
71: }
```
* **Línea 66**: Evalúa si `fetch()` retornó algún registro que coincida.
* **Línea 68**: Si hay coincidencia, frena la inserción emitiendo una alerta en terminal y cerrando el script con código de salida `1`.

```php
73: // Genera un hash seguro de la contraseña usando el algoritmo Bcrypt
74: $hash = password_hash($password, PASSWORD_BCRYPT);
```
* **Línea 74**: Hashea la contraseña en texto plano utilizando la función estándar de la industria `password_hash()` con la constante criptográfica de alta seguridad `PASSWORD_BCRYPT` (Bcrypt genera hashes de 60 caracteres de forma aleatoria con salting interno dinámico, protegiendo contra ataques de fuerza bruta y tablas Rainbow).

```php
75: // Prepara la consulta SQL para insertar el nuevo usuario en la tabla 'usuarios'
76: $stmt = $db->prepare("INSERT INTO usuarios (user_name, password_hash, nombre, apellido, email) VALUES (?, ?, ?, ?, ?)");
77: // Ejecuta la inserción pasando todos los valores como parámetros (seguro contra inyección SQL)
78: $stmt->execute([$username, $hash, $nombre, $apellido, $email]);
```
* **Línea 76**: Prepara la consulta parametrizada de inserción SQL.
* **Línea 78**: Inserta de manera atómica y segura el registro correspondiente a la cuenta de usuario en MySQL.

```php
80: // Obtiene el ID autoincremental asignado al nuevo registro
81: $userId = $db->lastInsertId();
82: // Muestra mensaje de éxito en la creación del usuario
83: echo "User created successfully.\n";
...
91: echo "Email:    $email\n";
```
* **Línea 81**: Obtiene de forma directa el ID asignado por el campo autoincremental llamando a `$db->lastInsertId()`.
* **Líneas 82-91**: Muestra en la terminal un informe estructurado y limpio confirmando el éxito de la creación y los datos asociados a la nueva cuenta.

---

## 8. Modelo Orientado a Objetos vs Procedural: `Usuario.php` & `crud_users.php`

La aplicación demuestra una excelente versatilidad técnica al ofrecer dos capas de interacción equivalentes con la persistencia de datos: una moderna basada en **Programación Orientada a Objetos (POO)** heredando del núcleo, y otra basada en **Funciones Procedurales**.

### A. Mapeo de Inserción (Creación de Cuentas)

En el modelo Orientado a Objetos `src/app/Models/Usuario.php`:
```php
24:     public function crear(string $user_name, string $password, string $nombre, string $apellido, string $email): bool
25:     {
26:         // Genera el hash bcrypt de la contraseña para almacenamiento seguro
27:         $hash = password_hash($password, PASSWORD_BCRYPT);
28:         // Sentencia SQL para insertar un nuevo usuario con los datos proporcionados
29:         $sql = "INSERT INTO usuarios (user_name, password_hash, nombre, apellido, email, estatus) VALUES (?, ?, ?, ?, ?, '1')";
30:         // Prepara la sentencia SQL para evitar inyección SQL
31:         $stmt = $this->db->prepare($sql);
32:         // Ejecuta la consulta con los valores y retorna el resultado booleano
33:         return $stmt->execute([$user_name, $hash, $nombre, $apellido, $email]);
34:     }
```
* El método se registra en el contexto del objeto (`$this->db`). No requiere inyectar una variable de conexión externa en sus parámetros, ya que la conexión única PDO del Singleton está disponible en la propiedad protegida de la herencia del modelo padre.

En la capa procedural `src/app/Models/crud_users.php`:
```php
17: function crearUsuario($pdo, $user_name, $password, $nombre, $apellido, $email) {
18:     // Genera el hash bcrypt de la contraseña para almacenamiento seguro
19:     $hash = password_hash($password, PASSWORD_BCRYPT);
20:     // Sentencia SQL para insertar un nuevo usuario
21:     $sql = "INSERT INTO usuarios (user_name, password_hash, nombre, apellido, email, estatus) VALUES (?, ?, ?, ?, ?, '1')";
22:     $stmt = $pdo->prepare($sql);
23:     return $stmt->execute([$user_name, $hash, $nombre, $apellido, $email]);
24: }
```
* La versión procedural implementa una función pura y aislada. Requiere de manera explícita recibir la variable activa `$pdo` de conexión a MySQL como el primer argumento en cada ejecución.

### B. Mapeo de Autenticación de Cuentas

En el modelo Orientado a Objetos `src/app/Models/Usuario.php`:
```php
107:     public function autenticar(string $username, string $password): array|false
108:     {
109:         // Obtiene el usuario por su nombre de usuario
110:         $usuario = $this->obtenerPorUsername($username);
111:         // Si el usuario existe y la contraseña coincide con el hash, retorna los datos
112:         if ($usuario && password_verify($password, $usuario['password_hash'])) {
113:             return $usuario;
114:         }
115:         // Si no coincide o no existe, retorna false
116:         return false;
117:     }
```
* Utiliza el tipado fuerte nativo de PHP 8 (`array|false`) para denotar que el método retornará un arreglo de datos del usuario autenticado o un booleano falso si falla, empleando la función segura `password_verify` para comparar el hash.

En la versión procedural `src/app/Models/crud_users.php`:
```php
93: function autenticarUsuario($pdo, $username, $password) {
94:     // Obtiene el usuario por su nombre de usuario
95:     $usuario = obtenerUsuarioPorUsername($pdo, $username);
96:     // Si existe y la contraseña coincide con el hash, retorna los datos
97:     if ($usuario && password_verify($password, $usuario['password_hash'])) {
98:         return $usuario;
99:     }
100:     // Si no coincide o no existe, retorna false
101:     return false;
102: }
```
* Replica la misma lógica conceptual de seguridad utilizando el flujo procedural acoplado al parámetro `$pdo`.

---

## 9. Plantilla Maestra de la Interfaz: `src/app/template/layout.php`

Este archivo centraliza el esqueleto HTML, la barra lateral (*sidebar*), menús superiores de navegación, cambio en tiempo real del tema oscuro/claro de la UI y el registro del Service Worker.

```php
20:     <title><?php echo $pageTitle; ?> - EIS System</title>
```
* **Línea 20**: Inyecta dinámicamente en el encabezado `<title>` la variable `$pageTitle` que es calculada por el enrutador según la ruta activa, garantizando pestañas de navegación dinámicas y profesionales.

```php
35:     <ul id="slide-out" class="sidenav sidenav-fixed">
```
* **Línea 35**: Declara el menú lateral persistente (*sidebar*) aplicando las clases `sidenav` y `sidenav-fixed` de Materialize CSS, manteniéndolo siempre a la vista en pantallas medianas y de escritorio.

```php
46:         <li><a href="?pagina=dashboard" class="sidenav-link<?php echo $pagina === 'dashboard' ? ' active' : ''; ?>"><i
47:                     class="material-icons left">dashboard</i>Dashboard</a></li>
```
* **Línea 46**: Imprime una etiqueta de enlace para dirigir al usuario al Dashboard.
* **Línea 46 (PHP)**: Evalúa dinámicamente mediante una expresión condicional ternaria si la ruta activa es estrictamente `'dashboard'`. De ser así, inyecta en caliente la clase CSS `' active'`, coloreando y marcando el enlace en el menú lateral para indicar la sección actual.

```php
81:         <li><a class="sidenav-link" id="themeToggle" style="cursor:pointer;"><i class="material-icons left"
82:                     id="themeIcon">dark_mode</i><span id="themeLabel">Modo Oscuro</span></a></li>
```
* **Líneas 81-82**: Define el elemento de menú interactivo con identificador `themeToggle` que sirve como gatillo para alternar de forma instantánea entre tema oscuro y tema claro.

```php
103:                     <?php if (!empty($headerExtra)): ?>
104:                     <li class="header-extra"><?php echo $headerExtra; ?></li>
105:                     <?php endif; ?>
```
* **Línea 103-105**: Condicional de PHP que inyecta elementos gráficos adicionales en el menú superior (por ejemplo, los chips de estado "5 Disponibles / 4 Ocupadas" de la vista de Cyber Control) únicamente si la variable `$headerExtra` fue poblada previamente.

```php
130:     <main>
131:         <div class="container" style="padding-top:1.5rem;padding-bottom:2rem;max-width:1400px;width:95%;">
132:             <?php require $contentView; ?>
133:             <!-- Aquí se inyecta la vista específica de cada página mediante require -->
134:             <!-- $contentView es la ruta absoluta al archivo .php de la vista activa -->
135:         </div>
136:     </main>
```
* **Línea 130**: Abre la sección `<main>` de contenido de página principal.
* **Línea 132**: **Inyección del cuerpo de la vista**: Incluye dinámicamente mediante `require` el contenido específico de la página solicitada utilizando la ruta del archivo resuelta en la variable `$contentView` (ej. la interfaz gráfica de `ventas.php` o `inventario.php`). Esto encapsula la interfaz de manera modular.

```php
192:     <script>
193:     // Verifico si el navegador soporta Service Workers
194:     if ('serviceWorker' in navigator) {
195:         // Registro el archivo sw.js para habilitar cache offline y funcionalidad PWA
196:         navigator.serviceWorker.register('sw.js');
197:     }
198:     </script>
```
* **Líneas 192-198**: Script de soporte para Aplicaciones Web Progresivas (PWA). Verifica si la característica nativa `serviceWorker` está disponible en el navegador del cliente. De estar soportada, registra asíncronamente el archivo `sw.js` para cachear recursos críticos (CSS, JS, Fuentes locales) y permitir el funcionamiento y visualización del sistema de manera independiente a la existencia de conexión a internet.

---

## 10. Control de Acceso Seguro: `src/app/Views/login_validate.php`

Este script actúa como una barrera de desvío y cortafuegos de seguridad a nivel de vista.

```php
1: <?php
```
* **Línea 1**: Abre la etiqueta de PHP.

```php
2: // =============================================================================
3: // VISTA: LOGIN_VALIDATE (redirección de seguridad)
4: // =============================================================================
5: // Propósito: Este archivo nunca debe renderizarse directamente. Si alguien
6: //            accede a ?pagina=login_validate sin enviar el formulario POST,
7: //            simplemente redirige al login. La validación real ocurre en
8: //            AuthController::login() mediante una petición POST.
9: // =============================================================================
```
* **Líneas 2-9**: Comentarios de seguridad que explican por qué este script existe como trampa para desviar intentos de escaneo de rutas o accesos sin envío de formularios.

```php
13: // Redirigir al login si se accede directamente a este archivo sin enviar POST
14: // Se envía una cabecera HTTP Location para redireccionar al navegador
15: header('Location: ?pagina=login');
```
* **Línea 15**: Envía inmediatamente la cabecera HTTP `Location: ?pagina=login` para que el navegador del cliente regrese al panel seguro de inicio de sesión si se intentó vulnerar la URL directa.

```php
16: // Finaliza la ejecución del script para que no se procese nada más
17: exit;
```
* **Línea 17**: Aborta de inmediato la ejecución física con `exit`, bloqueando de raíz que se envíe contenido residual al navegador del atacante o cliente.

---

## 11. Filtro y Fallback Offline: `src/offline.php`

Este archivo sirve como pantalla de contingencia visual cuando el cliente pierde la conexión de red y el Service Worker intercepta la solicitud.

```php
1: <!DOCTYPE html>
2: <!-- Declaración del tipo de documento como HTML5 -->
3: <html lang="es">
```
* **Líneas 1-3**: Declara la estructura base de un documento HTML5 estándar en idioma español.

```php
11:   <meta name="theme-color" content="#1a237e">
```
* **Línea 11**: Configura el color de la barra del navegador móvil con el tono índigo corporativo del sistema.

```php
15:   <link rel="stylesheet" href="Public/css/material-icons.css">
16:   <link rel="stylesheet" href="Public/css/materialize.min.css">
```
* **Líneas 15-16**: Enlaza los estilos locales e independientes de fuentes de íconos y del framework CSS Materialize. Al ser archivos **locales** (no basados en CDN externos), se garantiza que se puedan leer directamente de la caché del navegador cuando el dispositivo no tenga internet.

```php
62:       <div class="offline-icon material-icons">cloud_off</div>
```
* **Línea 62**: Renderiza el ícono nativo `cloud_off` (nube tachada) representativo del estado offline.

```php
68:       <button class="btn waves-effect waves-light indigo retry-btn" onclick="location.reload()">
```
* **Línea 68**: Define un botón Materialize con el evento nativo JavaScript `onclick="location.reload()"`. Al ser pulsado, fuerza la recarga completa de la página actual para que el cliente pueda reintentar la conexión de manera rápida y cómoda tan pronto como recupere la señal.

---

## Resumen de los Componentes Restantes

Para dar una explicación completa de toda la aplicación, se resume a continuación el comportamiento de los componentes que interactúan con el núcleo detallado anteriormente:

### Controladores Secundarios (Módulos AJAX JSON)
* **`ClienteController.php`**: API JSON para gestión de clientes. Maneja acciones CRUD con validación completa: cédula (min 5, formato, unicidad), nombre (min 2), apellido (min 2), dirección y teléfono (no vacíos). Captura `\InvalidArgumentException` para mensajes de validación al usuario.
* **`inventarioController.php`**: Controla toda la gestión de mercancías. Procesa acciones como `'listar'`, `'kpis'`, `'categorias'`, `'crear'` y `'actualizar'` mediante `match()`. Incluye validación de rangos numéricos (stock >= 0, precio_venta >= costo_compra) y verificación de FK de categoría.
* **`ProveedorController.php`**: API JSON para órdenes de abastecimiento. Maneja FK de proveedor/status, validación de fecha (YYYY-MM-DD), número (no vacío), cantidad (>= 1) y precio (> 0).
* **`ProveedorGestionController.php`**: API JSON para gestión de proveedores. Valida RIF (min 5, unicidad), nombre (min 2), email (formato), teléfono (no vacío).
* **`RolController.php`**: Gestiona RBAC. Ofrece endpoints para crear/editar roles, listar permisos, guardar matriz de permisos en transacciones SQL, con protección de admin (id=1) y unicidad de nombre.

### Modelos de Datos Adicionales (OOP)
* **`Cliente.php`**: CRUD clientes con validación completa en setters: `setCedula()` (min 5, formato V-/E-), `setNombre()` (min 2), `setApellido()` (min 2), `setDireccion()` (no vacío), `setTelefono()` (no vacío). `existeCedula(excludeId)` para unicidad.
* **`Inventario.php`**: CRUD productos con validación numérica: `setStock()` (>= 0), `setStockMinimo()` (>= 1), `setCostoCompra()` (>= 0), `setPrecioVenta()` (> 0). `existeCodigo(excludeId)` para unicidad y `existeCategoria()` para FK.
* **`Proveedor.php`**: CRUD proveedores/órdenes con FK: `existeProveedor()` y `existeStatus()`. Validación de fecha, número, cantidad y precio en setters.
* **`ProveedorGestion.php`**: CRUD proveedores (gestión) con `setRif()` (min 5, unicidad), `setNombre()` (min 2), `setEmail()` (formato), `setTelefono()` (no vacío).
* **`Rol.php`**: CRUD roles/permisos con `setNombre()` (min 2, unicidad via `existeNombreRol(excludeId)`). Protección de admin (id=1) en controlador.
* **`Asesoria.php` & `crud_asesorias.php`**: Gestión de asesorías legales con tablas cruzadas.

---
*Fin de la explicación detallada del sistema PHP MVC.*
