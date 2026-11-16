# Validaciones de backend y frontend en EIS Zona Web

> Documento técnico del sistema de validación de formularios.
>
> Cubre la validación estricta en PHP, la validación de experiencia de usuario
> en JavaScript con jQuery y jQuery Validation, y las alertas con jQuery Toast
> Plugin. Todos los fragmentos están tomados del código real del proyecto.
>
> Las rutas son relativas a la raíz del repositorio.

---

## Índice

1. [Idea central: dos capas y una sola fuente de reglas](#1-idea-central-dos-capas-y-una-sola-fuente-de-reglas)
2. [Arquitectura de un vistazo](#2-arquitectura-de-un-vistazo)
3. [La fuente única: `regex_validations.json`](#3-la-fuente-única-regex_validationsjson)
4. [Validación de backend (PHP)](#4-validación-de-backend-php)
5. [Validación de frontend (JS + jQuery + jQuery Validation)](#5-validación-de-frontend-js--jquery--jquery-validation)
6. [Alertas con jQuery Toast Plugin](#6-alertas-con-jquery-toast-plugin)
7. [Recorrido completo de un formulario](#7-recorrido-completo-de-un-formulario)
8. [Contrato JSON entre backend y frontend](#8-contrato-json-entre-backend-y-frontend)
9. [Cómo añadir un campo nuevo](#9-cómo-añadir-un-campo-nuevo)
10. [Problemas frecuentes](#10-problemas-frecuentes)
11. [Referencia rápida](#11-referencia-rápida)
12. [Archivos implicados](#12-archivos-implicados)

---

## 1. Idea central: dos capas y una sola fuente de reglas

El sistema valida **dos veces** el mismo dato:

| Capa | Dónde | Para qué | ¿Se puede saltar? |
|---|---|---|---|
| Backend | PHP, `Validator` | Seguridad e integridad de los datos | **No** |
| Frontend | JS, `app.validate.js` | Experiencia de usuario: avisar antes de enviar | Sí (DevTools, scripts, clientes HTTP) |

La regla de oro del proyecto está escrita en el propio JSON de reglas:

> «La validación del servidor siempre es obligatoria; la validación en
> JavaScript es solo UX.»

Eso significa que **el frontend nunca es la última línea de defensa**. Aunque un
formulario se vea perfecto en el navegador, el controlador vuelve a validar
todo con `Validator`. Si el cliente intenta inyectar un valor manipulado, el
backend lo rechaza igual.

La segunda idea importante es que **PHP y JavaScript no duplican patrones**. Los
dos leen la misma definición desde `regex_validations.json`:

- PHP la consume con `App\Core\RegexRules`.
- JavaScript la consume con `EIS.Regex`, desde el archivo generado
  `src/Public/js/regex_validations.js`.

Así, si mañana cambia el formato de una cédula o de un correo, se edita un solo
archivo y ambas capas quedan alineadas.

---

## 2. Arquitectura de un vistazo

```
regex_validations.json                 (fuente única, raíz del proyecto)
        │
        ├──► PHP  App\Core\RegexRules ──► App\Core\Validator
        │                                      │
        │                                      ├─ ValidationException
        │                                      └─ BaseController
        │                                             │
        │                                             ▼
        │                                   Respuesta JSON (422 / 400 / 200)
        │                                             │
        └──► JS   EIS.Regex  ◄── regex_validations.js (generado)
                     │
                     ▼
              app.validate.js
              ├─ EIS.validarCampo()
              ├─ EIS.validarFormulario()
              ├─ jQuery Validation (regla puente "eisRegla")
              ├─ feedback inline (marcarErrorCampo)
              └─ EIS.toast() ──► jQuery Toast Plugin
```

Flujo resumido:

1. El usuario escribe en un campo.
2. `app.validate.js` valida en vivo con `EIS.validarCampo` y pinta el error
   inline; opcionalmente lanza un toast.
3. Al enviar, el formulario se valida completo con `EIS.validarFormulario`
   (que usa jQuery Validation si está disponible).
4. Si hay errores, el envío se bloquea y se muestra el resumen del formulario.
5. Si todo es válido, el módulo hace el POST por AJAX.
6. El controlador PHP verifica CSRF y valida con `Validator::validar`.
7. Si algo falla, responde `{success:false, fieldErrors:{...}}` con HTTP 422.
8. El frontend puede repintar esos errores con `EIS.mostrarErroresFormulario`.

---

## 3. La fuente única: `regex_validations.json`

### 3.1 Anatomía de una regla

Cada regla es un objeto con estas propiedades habituales:

```json
{
  "label": "Cédula venezolana",
  "pattern": "^(?:[VEJPG][-.\\s]?)?(?:\\d{5,9}|\\d{1,2}\\.\\d{3}\\.\\d{3})$",
  "flags": "iu",
  "minlength": 5,
  "maxlength": 14,
  "mensaje": "Ingresa una cédula válida (ej.: V-12345678 o 12345678).",
  "ejemplo_valido": "V-12345678",
  "ejemplo_invalido": "V-12"
}
```

| Propiedad | Uso |
|---|---|
| `pattern` | Expresión regular anclada con `^` y `$` para validar el valor completo. |
| `flags` | Modificadores PCRE/JS. PHP siempre añade `u` (UTF-8). |
| `minlength` / `maxlength` | Longitud en caracteres, usada por ambas capas. |
| `mensaje` | Texto mostrado al usuario cuando el patrón falla. |
| `label` | Nombre legible del campo. |
| `ejemplo_valido` | Ejemplo que se añade a las pistas de formato. |

### 3.2 Ejemplos reales

**Monto del sistema** (máximo 99.999.999,99):

```json
{
  "label": "Monto del sistema (máximo 99.999.999,99)",
  "pattern": "^(?:0|[1-9]\\d{0,7})(?:\\.\\d{1,2})?$",
  "maxlength": 12,
  "mensaje": "Ingresa un monto válido con hasta 2 decimales (máximo 99.999.999,99).",
  "ejemplo_valido": "1250.99"
}
```

**Correo electrónico**:

```json
{
  "label": "Correo electrónico",
  "pattern": "^(?!.*\\.\\.)(?!^[.-])[A-Za-z0-9](?:[A-Za-z0-9._%+-]{0,62}[A-Za-z0-9])?@(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?\\.)+[A-Za-z]{2,63}$",
  "flags": "u",
  "maxlength": 254,
  "mensaje": "Ingresa un correo electrónico válido (ej.: usuario@dominio.com).",
  "ejemplo_valido": "usuario.nombre+tag@sub.dominio.com"
}
```

**Contraseña media** (8 a 72 caracteres, al menos una letra y un número):

```json
{
  "label": "Contraseña de 8 a 72 caracteres con al menos una letra y un número",
  "pattern": "^(?=.*[A-Za-z])(?=.*\\d).{8,72}$",
  "minlength": 8,
  "maxlength": 72,
  "mensaje": "La contraseña debe tener entre 8 y 72 caracteres e incluir al menos una letra y un número.",
  "ejemplo_valido": "ClaveSegura2026"
}
```

### 3.3 Del JSON a PHP: `RegexRules`

`src/app/core/RegexRules.php` es una fachada estática sobre el JSON. Carga el
archivo una sola vez por petición y lo cachea en memoria.

```php
final class RegexRules
{
    private static ?array $rules = null;

    public static function path(): string
    {
        // __DIR__ = <proyecto>/src/app/core
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'regex_validations.json';
    }

    public static function all(): array
    {
        if (self::$rules !== null) {
            return self::$rules;
        }

        $path = self::path();
        if (!is_file($path)) {
            throw new \RuntimeException('No se encontró el archivo de validaciones: ' . $path);
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        self::$rules = $data;
        return self::$rules;
    }
}
```

Las operaciones más usadas:

```php
// Regla completa (array) o null
RegexRules::get('contacto.email');

// ¿Existe y tiene patrón?
RegexRules::exists('contacto.email');

// Patrón PCRE listo para preg_match()
RegexRules::pcre('identificacion_venezuela.cedula_venezolana');

// Validación completa de un valor
RegexRules::test('contacto.email', $email);

// Metadatos
RegexRules::minlength('seguridad.password_media'); // 8
RegexRules::maxlength('seguridad.password_media'); // 72
RegexRules::mensaje('contacto.email');
RegexRules::label('contacto.email');
```

`pcre()` construye el patrón con delimitador `~` y añade el flag `u` si no
viene:

```php
public static function pcre(string $key): string
{
    $pattern = self::requirePattern($key);
    $flags   = self::flags($key);

    // El delimitador elegido (~) no aparece en los patrones del JSON.
    return '~' . str_replace('~', '\\~', $pattern) . '~' . $flags;
}

public static function test(string $key, string $value): bool
{
    $resultado = preg_match(self::pcre($key), $value);
    if ($resultado === false) {
        throw new \RuntimeException("El patrón de validación '$key' no es válido para PCRE");
    }
    return $resultado === 1;
}
```

`sanitiza` los flags para aceptar solo modificadores seguros (`imsuxADJU`) y
**nunca** el modificador `e`, que permitiría ejecución de código.

### 3.4 Del JSON a JavaScript: `regex_validations.js`

El navegador no puede leer el JSON de la raíz porque está fuera del
`DocumentRoot`. Por eso existe un generador:

```
src/cli/generar_regex_js.php
```

que produce:

```
src/Public/js/regex_validations.js
```

El archivo generado se abre con una advertencia:

```js
/* =====================================================================
 * ARCHIVO GENERADO AUTOMÁTICAMENTE. NO EDITAR A MANO.
 *
 * Fuente:  regex_validations.json (versión 2.0.0)
 * Comando: php src/cli/generar_regex_js.php
 * ===================================================================== */
```

Y expone el namespace `EIS.Regex`:

```js
global.EIS = global.EIS || {};
global.EIS.Regex = {
    get: get,
    has: has,
    test: test,
    maxlength: maxlength,
    minlength: minlength,
    mensaje: mensaje,
    label: label,
    all: all,
    version: version,
    clearCache: clearCache
};
```

Uso típico desde consola o desde otro módulo:

```js
EIS.Regex.test('contacto.email', 'usuario@dominio.com'); // true
EIS.Regex.get('seguridad.password_media').maxlength;     // 72
EIS.Regex.mensaje('identificacion_venezuela.cedula_venezolana');
```

### 3.5 Regenerar el archivo

Cada vez que se edite `regex_validations.json`:

```bash
php src/cli/generar_regex_js.php
```

El script valida que el JSON sea correcto, que las reglas tengan `pattern` y
que los patrones sean compilables antes de escribir el archivo. Si algo falla,
termina con error y no deja el JS a medias.

---

## 4. Validación de backend (PHP)

### 4.1 Componentes

| Archivo | Responsabilidad |
|---|---|
| `src/app/core/RegexRules.php` | Leer patrones y metadatos del JSON. |
| `src/app/core/Validator.php` | Validar y normalizar cada tipo de dato. |
| `src/app/core/ValidationException.php` | Transportar **todos** los errores por campo. |
| `src/app/Controllers/BaseController.php` | Convertir validaciones en respuestas JSON. |
| `src/app/core/router.php` | Generar y verificar el token CSRF. |

### 4.2 `Validator`: validaciones unitarias

`Validator` es una clase `final` con métodos estáticos. Cada método recibe el
valor crudo, el nombre legible del campo y opciones, y devuelve el valor ya
normalizado o lanza `\InvalidArgumentException` con un mensaje en español.

#### Texto

```php
public static function texto(mixed $value, string $field, array $opts = []): string
{
    $required = (bool)($opts['required'] ?? false);
    $ruleKey  = isset($opts['rule']) && is_string($opts['rule']) ? $opts['rule'] : null;
    $min      = (int)($opts['min'] ?? ($ruleKey !== null ? (RegexRules::minlength($ruleKey) ?? 1) : 1));
    $max      = (int)($opts['max'] ?? ($ruleKey !== null ? (RegexRules::maxlength($ruleKey) ?? PHP_INT_MAX) : PHP_INT_MAX));
    $pattern  = $opts['pattern'] ?? null;
    $patternMessage = (string)($opts['patternMessage'] ?? ($ruleKey !== null
        ? RegexRules::mensaje($ruleKey, "El campo '$field' contiene caracteres no permitidos.")
        : "El campo '$field' contiene caracteres no permitidos."));

    if ($pattern === null && $ruleKey !== null) {
        $pattern = RegexRules::pcre($ruleKey);
    }

    if ($value === null || is_bool($value) || is_array($value) || is_object($value)) {
        throw new \InvalidArgumentException("El campo '$field' no es válido.");
    }

    $texto = trim((string)$value);

    if ($texto === '') {
        if ($required) {
            throw new \InvalidArgumentException("El campo '$field' es obligatorio.");
        }
        return '';
    }

    self::rechazarControl($texto, $field);

    $len = mb_strlen($texto);
    if ($len < $min) {
        throw new \InvalidArgumentException("El campo '$field' debe tener al menos $min caracteres.");
    }
    if ($len > $max) {
        throw new \InvalidArgumentException("El campo '$field' no puede superar los $max caracteres.");
    }
    if ($pattern !== null && !preg_match($pattern, $texto)) {
        throw new \InvalidArgumentException($patternMessage);
    }

    return $texto;
}
```

Puntos clave:

- Rechaza arrays, objetos y booleanos: un atacante no puede enviar
  `nombre[]=x` para romper la lógica.
- Hace `trim()` antes de medir y comparar.
- Usa `mb_strlen` para contar caracteres UTF-8, no bytes.
- Rechaza caracteres de control con la regla `aplicacion.caracteres_control`.
- Si la regla tiene `minlength`/`maxlength`, los usa como valores por defecto.

#### Entero

```php
public static function entero(mixed $value, string $field, array $opts = []): int
{
    $required = (bool)($opts['required'] ?? true);
    $min      = (int)($opts['min'] ?? PHP_INT_MIN);
    $max      = (int)($opts['max'] ?? PHP_INT_MAX);

    if ($value === null || is_bool($value) || is_array($value) || is_object($value)) {
        if (($value === null || $value === '') && !$required) {
            return 0;
        }
        throw new \InvalidArgumentException("El campo '$field' debe ser un número entero.");
    }

    if (is_float($value) && $value != floor($value)) {
        throw new \InvalidArgumentException("El campo '$field' debe ser un número entero.");
    }

    if (is_string($value)) {
        $value = trim($value);
        if ($value === '') {
            if ($required) {
                throw new \InvalidArgumentException("El campo '$field' es obligatorio.");
            }
            return 0;
        }
        if (!RegexRules::test('aplicacion.entero', $value)) {
            throw new \InvalidArgumentException("El campo '$field' debe ser un número entero.");
        }
        // Evita desbordamientos absurdos (ej. 999999999999999999999)
        if (strlen(ltrim($value, '-')) > 10) {
            throw new \InvalidArgumentException("El campo '$field' supera el rango permitido.");
        }
    }

    $n = (int)$value;
    if ($n < $min) {
        throw new \InvalidArgumentException("El campo '$field' debe ser mayor o igual a $min.");
    }
    if ($n > $max) {
        throw new \InvalidArgumentException("El campo '$field' no puede superar $max.");
    }
    return $n;
}
```

No confía en el casting de PHP: primero comprueba el formato con el patrón y
limita la longitud para evitar desbordamientos.

#### Decimal

```php
public static function decimal(mixed $value, string $field, array $opts = []): float
{
    $required = (bool)($opts['required'] ?? false);
    $min      = (float)($opts['min'] ?? 0.0);
    $max      = (float)($opts['max'] ?? self::MAX_MONEY);

    // ...

    if (is_string($value)) {
        $value = trim($value);
        if ($value === '') {
            if ($required) {
                throw new \InvalidArgumentException("El campo '$field' es obligatorio.");
            }
            return 0.0;
        }
        if (!RegexRules::test('aplicacion.monto', $value)) {
            throw new \InvalidArgumentException(
                RegexRules::mensaje('aplicacion.monto', "El campo '$field' debe ser un número con hasta 2 decimales.")
            );
        }
    }

    $f = round((float)$value, 2);
    if ($f < $min) {
        throw new \InvalidArgumentException("El campo '$field' debe ser mayor o igual a $min.");
    }
    if ($f > $max) {
        throw new \InvalidArgumentException("El campo '$field' no puede superar $max.");
    }
    return $f;
}
```

El máximo por defecto es `99999999.99`, coherente con `DECIMAL(10,2)`.

#### Fecha

```php
public static function fecha(mixed $value, string $field, array $opts = []): string
{
    $required = (bool)($opts['required'] ?? false);
    $noFutura = (bool)($opts['noFutura'] ?? false);

    // ...

    if (
        !RegexRules::test('fechas_y_horas.fecha_iso', $fecha)
        || !checkdate((int)substr($fecha, 5, 2), (int)substr($fecha, 8, 2), (int)substr($fecha, 0, 4))
    ) {
        throw new \InvalidArgumentException("El campo '$field' debe ser una fecha válida en formato AAAA-MM-DD.");
    }

    if ($noFutura && $fecha > date('Y-m-d')) {
        throw new \InvalidArgumentException("El campo '$field' no puede ser una fecha futura.");
    }

    return $fecha;
}
```

El patrón solo garantiza el formato; `checkdate()` garantiza que la fecha
exista de verdad. `2026-02-31` pasa el patrón pero no `checkdate`.

#### Lista blanca (`enum`)

```php
public static function enum(mixed $value, string $field, array $allowed, bool $required = true): string
{
    $value = is_scalar($value) ? trim((string)$value) : '';
    if ($value === '') {
        if ($required) {
            throw new \InvalidArgumentException("El campo '$field' es obligatorio.");
        }
        return '';
    }
    if (!in_array($value, $allowed, true)) {
        throw new \InvalidArgumentException("El valor del campo '$field' no es válido.");
    }
    return $value;
}
```

Comparación estricta (`true`), no `==`: evita que `0 == "texto"` cuele valores.

#### Correo

```php
public static function email(mixed $value, string $field, bool $required = false): string
{
    $value = is_scalar($value) ? trim((string)$value) : '';
    if ($value === '') {
        if ($required) {
            throw new \InvalidArgumentException("El campo '$field' es obligatorio.");
        }
        return '';
    }
    if (mb_strlen($value) > self::MAX_EMAIL) {
        throw new \InvalidArgumentException("El campo '$field' no puede superar los " . self::MAX_EMAIL . ' caracteres.');
    }
    if (!RegexRules::test('contacto.email', $value)) {
        throw new \InvalidArgumentException("El formato del campo '$field' no es válido.");
    }
    return $value;
}
```

#### Booleano

```php
public static function bool(mixed $value, string $field, bool $required = false): int
{
    if ($value === null || $value === '') {
        if ($required) {
            throw new \InvalidArgumentException("El campo '$field' es obligatorio.");
        }
        return 0;
    }
    if (is_bool($value)) {
        return $value ? 1 : 0;
    }
    if (is_int($value)) {
        return in_array($value, [0, 1], true) ? $value : self::boolInvalido($field);
    }
    $v = strtolower(trim((string)$value));
    if ($v === '1' || $v === 'true' || $v === 'on' || $v === 'yes' || $v === 'activo') {
        return 1;
    }
    if ($v === '0' || $v === 'false' || $v === 'off' || $v === 'no' || $v === 'inactivo') {
        return 0;
    }
    self::boolInvalido($field);
    return 0;
}
```

#### Identificador, contraseña y helpers

```php
public static function id(mixed $value, string $field): int
{
    return self::entero($value, $field, ['required' => true, 'min' => 1]);
}
```

La contraseña **no** hace `trim`, porque un espacio puede ser parte legítima de
la clave:

```php
public static function password(mixed $value, string $field, array $opts = []): string
{
    $required    = (bool)($opts['required'] ?? true);
    $complejidad = (bool)($opts['complejidad'] ?? false);
    $reglaPass   = $complejidad ? 'seguridad.password_media' : 'seguridad.password_basica';
    $min         = (int)($opts['min'] ?? (RegexRules::minlength($reglaPass) ?? self::MIN_PASSWORD));
    $max         = (int)($opts['max'] ?? (RegexRules::maxlength($reglaPass) ?? self::MAX_PASSWORD));

    if ($value === null || is_bool($value) || is_array($value) || is_object($value)) {
        if (($value === null || $value === '') && !$required) {
            return '';
        }
        throw new \InvalidArgumentException("El campo '$field' es obligatorio.");
    }

    $pass = (string)$value;

    if ($pass === '') {
        if ($required) {
            throw new \InvalidArgumentException("El campo '$field' es obligatorio.");
        }
        return '';
    }

    self::rechazarControl($pass, $field);

    if (mb_strlen($pass) < $min) {
        throw new \InvalidArgumentException("El campo '$field' debe tener al menos $min caracteres.");
    }
    if (mb_strlen($pass) > $max) {
        throw new \InvalidArgumentException("El campo '$field' no puede superar los $max caracteres.");
    }

    if (!RegexRules::test($reglaPass, $pass)) {
        throw new \InvalidArgumentException(RegexRules::mensaje($reglaPass, "El campo '$field' no tiene un formato válido."));
    }

    return $pass;
}
```

Helpers que envuelven `texto()` con la regla correcta:

```php
Validator::cedula($valor, 'cédula');
Validator::rif($valor, 'RIF');
Validator::telefono($valor, 'teléfono');
Validator::username($valor, 'usuario');
Validator::tiempoUso($valor, 'tiempo de uso');
Validator::numeroOrden($valor, 'número de orden');
Validator::busqueda($valor, 'búsqueda');
```

`tiempoUso` añade una comprobación semántica después del patrón: minutos y
segundos deben estar entre `00` y `59`.

#### Campos requeridos y carrito de venta

`Validator::requeridos()` devuelve un mapa `[campo => mensaje]` con los campos
vacíos. Es útil para flujos que no usan esquema, como el login:

```php
public static function requeridos(array $data, array $campos): array
{
    $errores = [];
    foreach ($campos as $campo => $etiqueta) {
        $valor = $data[$campo] ?? '';
        if (is_string($valor)) {
            $valor = trim($valor);
        }
        if ($valor === '' || $valor === null || $valor === []) {
            $errores[$campo] = "El campo $etiqueta es obligatorio.";
        }
    }
    return $errores;
}
```

`Validator::itemsVenta()` valida el JSON del carrito: estructura, IDs positivos,
cantidades entre 1 y `MAX_CANTIDAD`, máximo de 200 ítems y productos repetidos.

### 4.3 `Validator::validar`: esquemas y errores acumulados

En lugar de validar campo por campo y lanzar en el primero, los controladores
declaran un **esquema**:

```php
$datos = $this->validarCampos([
    'cedula'    => ['label' => 'cédula', 'type' => 'cedula'],
    'nombre'    => [
        'label' => 'nombre',
        'type' => 'texto',
        'required' => true,
        'min' => 2,
        'max' => 100,
        'pattern' => Validator::patron('aplicacion.texto_libre'),
        'patternMessage' => 'El nombre contiene caracteres no permitidos',
    ],
    'direccion' => ['label' => 'dirección', 'type' => 'texto', 'required' => false, 'max' => 500],
    'telefono'  => ['label' => 'teléfono', 'type' => 'telefono'],
]);
```

`validar()` recorre **todas** las reglas y acumula los errores:

```php
public static function validar(array $data, array $reglas): array
{
    $errores = [];
    $limpio  = [];

    foreach ($reglas as $campo => $regla) {
        $valor    = $data[$campo] ?? null;
        $etiqueta = (string)($regla['label'] ?? $campo);

        try {
            $limpio[$campo] = self::aplicarRegla($valor, $etiqueta, $regla);
        } catch (ValidationException $e) {
            foreach ($e->errores() as $k => $v) {
                $errores[$k] = $v;
            }
        } catch (\InvalidArgumentException $e) {
            $errores[$campo] = $e->getMessage();
        }
    }

    if ($errores) {
        throw new ValidationException($errores);
    }

    return $limpio;
}
```

Ventaja: el usuario no descubre los errores de uno en uno. Recibe de golpe
todos los campos que debe corregir.

`aplicarRegla()` traduce el `type` del esquema al método correspondiente:

```php
switch ($tipo) {
    case 'id':        return self::id($valor, $etiqueta);
    case 'entero':    return self::entero($valor, $etiqueta, [...]);
    case 'decimal':   return self::decimal($valor, $etiqueta, [...]);
    case 'fecha':     return self::fecha($valor, $etiqueta, [...]);
    case 'bool':      return self::bool($valor, $etiqueta, $required);
    case 'email':     return self::email($valor, $etiqueta, $required);
    case 'enum':      return self::enum($valor, $etiqueta, (array)($regla['allowed'] ?? []), $required);
    case 'password':  return self::password($valor, $etiqueta, $regla);
    case 'cedula':    return self::cedula($valor, $etiqueta, $required);
    case 'rif':       return self::rif($valor, $etiqueta, $required);
    case 'telefono':  return self::telefono($valor, $etiqueta, $required);
    case 'username':  return self::username($valor, $etiqueta, $required);
    case 'tiempoUso': return self::tiempoUso($valor, $etiqueta, $required);
    case 'numeroOrden': return self::numeroOrden($valor, $etiqueta, $required);
    case 'busqueda':  return self::busqueda($valor, $etiqueta);
    default:          return self::texto($valor, $etiqueta, $opts);
}
```

### 4.4 `ValidationException`

```php
final class ValidationException extends \InvalidArgumentException
{
    /** @var array<string,string> Mapa campo => mensaje legible. */
    private array $errores;

    public function __construct(
        array $errores,
        string $mensaje = 'Revisa los datos ingresados e intenta de nuevo.'
    ) {
        parent::__construct($mensaje);
        $this->errores = $errores;
    }

    public function errores(): array
    {
        return $this->errores;
    }
}
```

Es la diferencia entre un error suelto y un **mapa de errores**. El controlador
la captura y la convierte en `fieldErrors`, que el frontend sabe interpretar.

### 4.5 `BaseController`: el puente HTTP

`src/app/Controllers/BaseController.php` ofrece cuatro salidas:

```php
// 200 OK
public function jsonSuccess(mixed $data = null, string $message = 'Operación exitosa'): void
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data'    => $data,
        'message' => $message
    ]);
    exit;
}

// 400, 404, 500... según el código recibido
public function jsonError(string $error, mixed $data = null, int $code = 400): void
{
    if ($code >= 400 && $code < 600) {
        http_response_code($code);
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error'   => $error,
        'data'    => $data
    ]);
    exit;
}
```

Y las dos variantes de validación:

```php
// Formato antiguo: errores bajo la clave "errors"
public function jsonValidationErrors(?array $errors = null): void
{
    http_response_code(422);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error'   => 'Revisa los datos del formulario.',
        'errors'  => $errors ?? $this->getErrors()
    ]);
    exit;
}

// Formato actual: errores bajo la clave "fieldErrors" en la raíz
public function jsonValidationException(\App\Core\ValidationException $e): void
{
    http_response_code(422);
    header('Content-Type: application/json');
    echo json_encode([
        'success'     => false,
        'error'       => $e->getMessage(),
        'fieldErrors' => $e->errores(),
    ]);
    exit;
}
```

> **Importante:** `EIS.mostrarErroresFormulario` solo mapea `fieldErrors`
> (en la raíz o dentro de `data`). Si un controlador responde con la clave
> `errors` de `jsonValidationErrors`, el panel general se muestra, pero los
> campos no se marcan individualmente. Para el flujo moderno se usa
> `jsonValidationException`.

También existe el atajo de validación:

```php
protected function validarCampos(array $reglas, ?array $origen = null): array
{
    return Validator::validar($origen ?? $_POST, $reglas);
}
```

Y los hidratadores `hydrateFromPost()` / `hydrateFromGet()`, que convierten
tipos usando `Validator` sin lanzar excepción cuando el campo es opcional.

### 4.6 CSRF: antes de validar, verificar identidad

El token se genera una vez por sesión en `src/app/core/router.php`:

```php
// Token CSRF: se genera una sola vez por sesión y se reutiliza.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

Y se verifica con comparación en tiempo constante:

```php
public static function verifyCsrfToken(?string $token): bool
{
    return !empty($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}
```

El layout inyecta el token en una variable global y lo añade a **todo** POST de
jQuery:

```php
<script>
window.EIS = window.EIS || {};
window.EIS.csrfToken = '<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>';
window.EIS.userId = '<?php echo (int)($_SESSION['user_id'] ?? 0); ?>';
$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        if (settings.type === 'POST' || settings.type === 'post') {
            if (typeof settings.data === 'string') {
                settings.data += '&csrf_token=' + encodeURIComponent(window.EIS.csrfToken);
            } else if (typeof settings.data === 'object' && settings.data !== null) {
                settings.data.csrf_token = window.EIS.csrfToken;
            }
        }
    }
});
</script>
```

El login, que es un POST de formulario normal, lleva el token en un campo
oculto:

```php
<input type="hidden" name="csrf_token"
       value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
```

Cada controlador verifica el token **antes** de validar:

```php
if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    $this->jsonError('La sesión o el formulario expiró. Recarga la página e intenta de nuevo.', null, 400);
    return;
}
```

Esto evita que un sitio externo envíe formularios en nombre del usuario.

### 4.7 Ejemplo completo: `ClienteController`

`handle()` centraliza el `try/catch`:

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
            default      => $this->jsonError('La acción solicitada no es válida.', null, 400),
        };
    } catch (ValidationException $e) {
        $this->jsonValidationException($e);
    } catch (\PDOException $e) {
        Logger::error($e, 'Clientes - consulta SQL');
        $msg = $e->getMessage();
        if (str_contains($msg, 'foreign key constraint') || str_contains($msg, 'a foreign key constraint fails')) {
            $this->jsonError('No se puede eliminar el cliente porque tiene registros asociados.', null, 400);
        } else {
            $this->jsonError('No se pudo conectar con la base de datos. Intenta de nuevo.', null, 500);
        }
    } catch (\InvalidArgumentException $e) {
        $this->jsonError($e->getMessage(), null, 400);
    } catch (\Exception $e) {
        Logger::error($e, 'Clientes');
        $this->jsonError('Ocurrió un error interno. Intenta de nuevo.', null, 500);
    }
}
```

Y `crear()` muestra el orden correcto: **CSRF → esquema → reglas de negocio →
modelo**.

```php
private function crear(): void
{
    if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $this->jsonError('La sesión o el formulario expiró. Recarga la página e intenta de nuevo.', null, 400);
        return;
    }

    $datos = $this->validarCampos([
        'cedula'    => ['label' => 'cédula', 'type' => 'cedula'],
        'nombre'    => [
            'label' => 'nombre',
            'type' => 'texto',
            'required' => true,
            'min' => 2,
            'max' => 100,
            'pattern' => Validator::patron('aplicacion.texto_libre'),
            'patternMessage' => 'El nombre contiene caracteres no permitidos',
        ],
        'apellido'  => [
            'label' => 'apellido',
            'type' => 'texto',
            'required' => true,
            'min' => 2,
            'max' => 100,
            'pattern' => Validator::patron('aplicacion.texto_libre'),
            'patternMessage' => 'El apellido contiene caracteres no permitidos',
        ],
        'direccion' => [
            'label' => 'dirección',
            'type' => 'texto',
            'required' => false,
            'max' => 500,
            'pattern' => Validator::patron('aplicacion.direccion'),
            'patternMessage' => 'La dirección contiene caracteres no permitidos',
        ],
        'telefono'  => ['label' => 'teléfono', 'type' => 'telefono'],
    ]);

    $cedula    = $datos['cedula'];
    $nombre    = $datos['nombre'];
    $apellido  = $datos['apellido'];
    $direccion = $datos['direccion'];
    $telefono  = $datos['telefono'];

    $this->set('cedula', $cedula)
        ->set('nombre', $nombre)
        ->set('apellido', $apellido)
        ->set('direccion', $direccion)
        ->set('telefono', $telefono);

    if ($this->model->existeCedula($cedula)) {
        $this->jsonError('Ya existe un cliente registrado con esa cédula.', null, 400);
        return;
    }

    $resultado = $this->model->crearCliente($cedula, $nombre, $apellido, $direccion, $telefono);
    if ($resultado) {
        $this->jsonSuccess(null, 'Cliente creado exitosamente');
    } else {
        $this->jsonError('No se pudo crear el cliente. Intenta de nuevo.', null, 400);
    }
}
```

Observa que la cédula duplicada **no** es un error de formato: es una regla de
negocio. Por eso se responde con `jsonError(..., 400)`, no con
`ValidationException`. El frontend lo muestra como toast o como error general,
no como error de campo.

### 4.8 Ejemplo: `UsuarioController` (enum, email y contraseña)

```php
$datos = Validator::validar($_POST, [
    'id'       => ['label' => 'ID del usuario', 'type' => 'id'],
    'nombre'   => ['label' => 'nombre', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El nombre contiene caracteres no permitidos'],
    'apellido' => ['label' => 'apellido', 'type' => 'texto', 'required' => false, 'max' => 100, 'pattern' => Validator::patron('aplicacion.texto_libre'), 'patternMessage' => 'El apellido contiene caracteres no permitidos'],
    'email'    => ['label' => 'email', 'type' => 'email', 'required' => true],
    'rol'      => ['label' => 'rol', 'type' => 'entero', 'required' => false, 'min' => 1],
    'estatus'  => ['label' => 'estado', 'type' => 'enum', 'allowed' => ['0', '1'], 'required' => true],
    'password' => ['label' => 'contraseña', 'type' => 'password', 'required' => false, 'min' => 8, 'complejidad' => true],
]);
```

Aquí se ve el uso de:

- `id` para claves primarias.
- `enum` para un valor que solo puede ser `"0"` o `"1"`.
- `email` con patrón del JSON.
- `password` con complejidad y mínimo de 8.

Y una validación de negocio que también usa `ValidationException`:

```php
if ($this->model->existeUsername($user_name)) {
    throw new ValidationException(['user_name' => 'Ya existe un usuario con ese nombre de usuario']);
}
```

Así el frontend recibe el error asociado al campo `user_name` y no un mensaje
suelto.

### 4.9 Lo que el backend **no** hace

- No confía en el `required` del HTML: lo vuelve a comprobar.
- No usa `$_POST` directamente en los modelos: siempre pasa por `Validator`.
- No devuelve HTML de error: devuelve JSON.
- No registra contraseñas ni tokens en los logs.
- No usa `eval` ni patrones con flag `e`.

---

## 5. Validación de frontend (JS + jQuery + jQuery Validation)

### 5.1 Orden de carga

`src/app/template/layout.php` carga las piezas en un orden que importa:

```html
<!-- jQuery (base de todo) -->
<script src="Public/js/jquery-3.7.1.min.js"></script>

<!-- jQuery Validation Plugin (local): validación de formularios -->
<script src="Public/js/jquery.validate.min.js"></script>
<!-- jQuery Toast Plugin (local): notificaciones del sistema -->
<script src="Public/js/jquery.toast.min.js"></script>

<!-- Core: namespace EIS y utilidades compartidas -->
<script src="Public/js/app.core.js"></script>
<!-- Regex: patrones generados desde regex_validations.json -->
<script src="Public/js/regex_validations.js"></script>
<!-- Validate: validación de formularios del lado del cliente -->
<script src="Public/js/app.validate.js"></script>
```

El orden garantiza que:

1. `jQuery` exista antes que los plugins.
2. `app.core.js` cree `window.EIS` y `EIS.toast`.
3. `regex_validations.js` cree `EIS.Regex` antes de que `app.validate.js` lo
   lea.
4. `app.validate.js` pueda registrar la regla puente `eisRegla` en
   `$.validator`.

### 5.2 `EIS.Regex`

Es la API de solo lectura sobre las reglas generadas:

```js
EIS.Regex.get('contacto.email');      // { pattern, flags, mensaje, ... }
EIS.Regex.has('contacto.email');      // true
EIS.Regex.test('contacto.email', 'a@b.com'); // true
EIS.Regex.maxlength('seguridad.password_media'); // 72
EIS.Regex.minlength('seguridad.password_media'); // 8
EIS.Regex.mensaje('contacto.email');
EIS.Regex.label('contacto.email');
EIS.Regex.all();                      // objeto completo
EIS.Regex.version();                  // "2.0.0"
EIS.Regex.clearCache();               // limpia regex compiladas
```

### 5.3 `app.validate.js`: mapa de responsabilidades

El archivo es un IIFE que recibe `jQuery`:

```js
(function ($) {
    'use strict';

    var EIS = window.EIS = window.EIS || {};
    var Regex = EIS.Regex || null;

    var USAR_JQUERY_VALIDATE = !!(window.jQuery && $.fn && typeof $.fn.validate === 'function');
```

Sus bloques son:

| Bloque | Función |
|---|---|
| Alias | Traducir `cedula`, `email`, `moneda`... a claves del JSON. |
| Resolución | Encontrar la regla efectiva (manual → alias → clave completa). |
| `EIS.validarCampo` | Validar un campo y devolver mensaje o `''`. |
| `EIS.validarFormulario` | Devolver `{campo: mensaje}` de todo el formulario. |
| jQuery Validation | Motor principal cuando el plugin está presente. |
| Tiempo real | Eventos `focusin`, `input`, `change`, `focusout`. |
| Filtros | Bloquear caracteres no permitidos al escribir/pegar. |
| Feedback | Pintar borde, icono, mensaje y contador. |
| Submit | Bloquear envío inválido y dobles envíos. |
| Toasts | Avisos contextuales con `EIS.toast`. |

### 5.4 Declarar reglas en HTML

La forma recomendada es declarativa: el formulario lleva `data-validate` y cada
campo sus atributos.

```html
<form id="form-cliente" data-validate>
    <input type="hidden" name="id" id="cliente-id" value="">

    <input type="text" name="cedula" id="cliente-cedula"
           maxlength="20" title="Entre 5 y 20 caracteres."
           required minlength="5" data-validate="cedula">

    <input type="text" name="nombre" id="cliente-nombre"
           maxlength="100" title="Entre 2 y 100 caracteres."
           required minlength="2" data-validate="nombre">

    <input type="text" name="apellido" id="cliente-apellido"
           maxlength="100" title="Entre 2 y 100 caracteres."
           required minlength="2" data-validate="nombre">

    <input type="text" name="direccion" id="cliente-direccion"
           maxlength="500" data-validate="direccion">

    <input type="tel" name="telefono" id="cliente-telefono"
           maxlength="20" minlength="7" data-validate="telefono">
</form>
```

Atributos que entiende la capa propia:

| Atributo | Efecto |
|---|---|
| `data-validate="alias"` | Aplica una o varias reglas (separadas por espacio o coma). |
| `required` | Campo obligatorio. |
| `minlength` / `maxlength` | Longitud mínima/máxima. |
| `min` / `max` | Rango numérico. |
| `type="email"`, `tel`, `url`, `number` | Regla nativa asociada. |
| `pattern` | Patrón HTML5 (solo si no hay `data-validate`). |
| `data-match="#otro"` | Debe coincidir con otro campo (confirmar contraseña). |
| `data-msg-required`, `data-msg-match`... | Mensaje personalizado. |
| `data-hint="..."` | Pista de formato mostrada al enfocar. |
| `title="..."` | Pista de respaldo. |

También se pueden usar claves completas del JSON:

```html
<input type="text" name="correo" data-validate="contacto.email">
<input type="password" name="clave" data-validate="seguridad.password_media">
```

### 5.5 Resolución de reglas y alias

Los alias están en `app.validate.js`:

```js
var ALIASES = {
    // Identificación
    cedula:  'identificacion_venezuela.cedula_venezolana',
    rif:     'identificacion_venezuela.rif_venezolano',
    pasaporte: 'identificacion_venezuela.pasaporte',

    // Contacto
    email:    'contacto.email',
    correo:   'contacto.email',
    telefono: 'contacto.telefono_flexible',
    url:      'contacto.sitio_web',

    // Textos y personas
    username: 'texto_y_personas.nombre_usuario',
    nombre:   'aplicacion.texto_libre',
    direccion: 'aplicacion.direccion',

    // Números
    entero:  'aplicacion.entero',
    moneda:  'aplicacion.monto',
    decimal: 'numerico.decimal',

    // Seguridad
    password: 'seguridad.password_media',
    // ...
};
```

`resolverRegla()` aplica esta prioridad:

1. Regla registrada o sobrescrita manualmente en
   `EIS.reglasValidacion[clave]`.
2. Alias corto (`cedula` → clave del JSON).
3. Clave completa usada directamente (`contacto.email`).

```js
function resolverRegla(clave) {
    if (!clave) return null;

    // 1) Reglas registradas/sobrescritas manualmente (compatibilidad).
    var manual = EIS.reglasValidacion[clave];
    if (manual && (manual.regex instanceof RegExp || typeof manual.pattern === 'string')) {
        return {
            regex: regexDeRegla(manual),
            mensaje: manual.mensaje || '',
            label: manual.label || clave,
            ejemplo: manual.ejemplo || manual.ejemplo_valido || '',
            minlength: aEntero(manual.minlength),
            maxlength: aEntero(manual.maxlength),
            clave: manual.clave || clave
        };
    }

    if (!Regex) return null;

    // 2) Alias corto -> clave del JSON.
    var claveJson = ALIASES[clave] || null;

    // 3) Clave completa del JSON usada directamente.
    if (!claveJson && Regex.has(clave)) {
        claveJson = clave;
    }

    if (!claveJson) return null;

    var regla = Regex.get(claveJson);
    if (!regla || typeof regla.pattern !== 'string') return null;

    var regex = compilarRegex(regla.pattern, regla.flags);
    if (!regex) return null;

    return {
        regex: regex,
        mensaje: regla.mensaje || '',
        label: regla.label || claveJson,
        ejemplo: regla.ejemplo_valido || '',
        minlength: aEntero(regla.minlength),
        maxlength: aEntero(regla.maxlength),
        clave: claveJson
    };
}
```

Esto permite que un módulo añada una regla propia sin tocar el JSON:

```js
EIS.reglasValidacion.codigo_interno = {
    regex: /^[A-Z]{2}-\d{4}$/,
    mensaje: 'El código debe tener el formato AB-1234.'
};
```

### 5.6 `EIS.validarCampo`: orden exacto de comprobaciones

```js
EIS.validarCampo = function (el) {
    var $el = $(el);
    if (!$el.length) return '';
    if ($el.is(':disabled') || $el.is('[readonly]')) return '';

    var tipo = tipoDe($el);
    if (TIPOS_CONTROL[tipo]) return '';

    var esCheck = esCheckable($el);
    var esRadio = tipo === 'radio';
    var requerido = $el.is('[required]') || (esCheck && grupoTieneRequerido(el));

    // Checkbox / radio ------------------------------------------------
    if (esCheck) {
        var marcado = esRadio ? grupoMarcado(el) : el.checked;
        if (!marcado) {
            return requerido ? mensaje('required', $el) : '';
        }
    }

    var valor = valorDe($el);
    var password = esPassword($el);
    var texto = password ? valor : valor.trim();
    var vacio = esCheck ? false : (texto === '');

    // Select sin opción elegida.
    if ($el.is('select') && (valor === '' || valor === null)) {
        return requerido ? mensaje('required', $el) : '';
    }

    // Campo obligatorio ----------------------------------------------
    if (requerido && vacio) {
        return mensaje('required', $el);
    }

    // Un campo opcional vacío no sigue validando.
    if (vacio) return '';

    // ... longitudes, números, tipos nativos y data-validate ...
};
```

Después aplica, en este orden:

1. **Longitudes**: atributos HTML5 y metadatos del JSON. Si ambos existen, se
   respeta el más estricto.
2. **Numéricos**: formato, `min` y `max`.
3. **Tipos nativos**: `email`, `tel`, `url`.
4. **`data-validate`**: cada token, en orden.
5. **`pattern`**: solo si ninguna regla propia lo cubrió.
6. **`data-match`**: comparación con otro campo.

`comprobarRegla()` hace `trim` salvo en contraseñas, traduce `datetime-local`
(`T` → espacio) y aplica validaciones semánticas que un patrón no puede hacer:

```js
function validarSemantica(clave, valor) {
    if (!clave) return true;

    if (clave === 'fechas_y_horas.fecha_iso') return esFechaISOReal(valor);
    if (clave === 'fechas_y_horas.fecha_latina') return esFechaLatinaReal(valor);
    if (clave === 'fechas_y_horas.fecha_hora_iso') return esFechaHoraISOReal(valor);

    return true;
}
```

Así `2026-02-31` no pasa aunque cumpla el formato.

### 5.7 `EIS.validarFormulario`

Devuelve un mapa `{ nombreCampo: mensaje }`. Si jQuery Validation está
disponible, lo usa como motor; si no, recorre los campos con
`EIS.validarCampo`.

```js
EIS.validarFormulario = function (form) {
    var $form = $(form);
    var errores = {};
    if (!$form.length) return errores;

    if (USAR_JQUERY_VALIDATE && EIS.initValidacionJQuery) {
        EIS.initValidacionJQuery($form[0]);
        var validator = $form.data('validator');
        if (validator) {
            var valido = validator.checkForm();
            if (!valido) {
                $.each(validator.errorList || [], function (_, error) {
                    var nombre = $(error.element).attr('name');
                    if (!nombre) return;
                    if (Object.prototype.hasOwnProperty.call(errores, nombre)) return;
                    errores[nombre] = error.message;
                });
            }
            return errores;
        }
    }

    // Respaldo: validación propia.
    $form.find(SELECTOR_CAMPOS).each(function () {
        var nombre = $(this).attr('name');
        if (!nombre) return;
        if (Object.prototype.hasOwnProperty.call(errores, nombre)) return;

        var m = EIS.validarCampo(this);
        if (m) errores[nombre] = m;
    });

    return errores;
};
```

`checkForm()` valida **sin pintar**. El pintado lo decide quien llama:

- el bloqueo de `submit`, que usa `EIS.mostrarErroresFormulario`;
- o el `showErrors` del plugin cuando se dispara la validación del plugin.

Atajo booleano:

```js
EIS.esFormularioValido = function (form) {
    return Object.keys(EIS.validarFormulario(form)).length === 0;
};
```

### 5.8 Integración con jQuery Validation

El plugin se usa como **motor** y como **ciclo de vida**, pero las reglas
siguen saliendo del JSON.

#### Mensajes en español

```js
$.extend($.validator.messages, {
    required: 'Este campo es obligatorio.',
    email: 'Ingresa un correo electrónico válido.',
    url: 'Ingresa una URL válida.',
    number: 'Ingresa un número válido.',
    digits: 'Solo se permiten dígitos.',
    minlength: $.validator.format('Debe tener al menos {0} caracteres.'),
    maxlength: $.validator.format('No puede superar los {0} caracteres.'),
    rangelength: $.validator.format('Debe tener entre {0} y {1} caracteres.'),
    min: $.validator.format('El valor mínimo permitido es {0}.'),
    max: $.validator.format('El valor máximo permitido es {0}.'),
    equalTo: 'Los valores no coinciden.',
    date: 'Ingresa una fecha válida.',
    dateISO: 'Ingresa una fecha válida (AAAA-MM-DD).',
    step: $.validator.format('El valor debe ser múltiplo de {0}.')
});
```

#### La regla puente `eisRegla`

Esta es la pieza clave. En lugar de duplicar la lógica de cada patrón en el
plugin, se registra un método que delega en `EIS.validarCampo`:

```js
$.validator.addMethod('eisRegla', function (value, element) {
    return EIS.validarCampo(element) === '';
}, function (param, element) {
    return EIS.validarCampo(element) || 'El valor no es válido.';
});
```

Así, cuando el plugin valida un campo con `eisRegla`, ejecuta exactamente la
misma lógica que la capa propia y el backend.

#### Construcción automática de reglas

`construirReglas($form)` recorre los campos y arma el objeto `rules` del
plugin a partir de los atributos HTML5 y de `data-validate`:

```js
function construirReglas($form) {
    var rules = {};
    var messages = {};

    $form.find('input, select, textarea').each(function () {
        var $el = $(this);
        var nombre = $el.attr('name');
        if (!nombre) return;

        var tipo = tipoDe($el);
        if (TIPOS_CONTROL[tipo] || tipo === 'hidden') return;

        var meta = metadatosDe($el);
        var reglas = rules[nombre] || {};

        if ($el.is('[required]') || $el.prop('required')) {
            reglas.required = true;
        }
        if (meta.minlength !== null && meta.minlength > 0) {
            reglas.minlength = meta.minlength;
        }
        if (meta.maxlength !== null && meta.maxlength > 0) {
            reglas.maxlength = meta.maxlength;
        }

        if (tipo === 'email') reglas.email = true;
        if (tipo === 'url') reglas.url = true;
        if (tipo === 'number') {
            reglas.number = true;
            var minNum = parseFloat($el.attr('min'));
            var maxNum = parseFloat($el.attr('max'));
            if (!isNaN(minNum)) reglas.min = minNum;
            if (!isNaN(maxNum)) reglas.max = maxNum;
        }

        var selectorIgual = $el.attr('data-match');
        if (selectorIgual) reglas.equalTo = selectorIgual;

        // La regla puente cubre data-validate, los tipos email/tel/url,
        // el atributo pattern y la confirmación de contraseña.
        if (meta.tokens.length || tipo === 'email' || tipo === 'tel' ||
            tipo === 'url' || $el.attr('pattern') || selectorIgual) {
            reglas.eisRegla = true;
        }

        rules[nombre] = reglas;
    });

    return { rules: rules, messages: messages };
}
```

#### Configuración del validator

```js
var validator = $form.validate({
    rules: config.rules,
    messages: config.messages,
    errorElement: 'div',
    errorClass: 'eis-field-error',
    validClass: 'eis-valid',
    ignore: function (index, element) {
        if (element.disabled) return true;
        var tag = element.tagName.toLowerCase();
        if (tag === 'select' || tag === 'textarea') return false;
        return $(element).is(':hidden');
    },
    onkeyup: false,
    onfocusout: false,
    onclick: false,
    showErrors: function (errorMap, errorList) {
        var form = this.currentForm;
        var conError = {};

        $.each(errorList || [], function (_, error) {
            var nombre = $(error.element).attr('name');
            if (nombre) conError[nombre] = true;
        });

        $(form).find(SELECTOR_CAMPOS).each(function () {
            var nombre = $(this).attr('name');
            if (!nombre || !conError[nombre]) {
                EIS.quitarErrorCampo(this);
            }
        });
        $.each(errorList || [], function (_, error) {
            EIS.marcarErrorCampo(error.element, error.message);
        });
    },
    invalidHandler: function (event, val) {
        if (!EIS.toast) return;
        var total = (val.errorList || []).length;
        if (!total) return;
        EIS.toast(
            total > 1
                ? 'Revisa los ' + total + ' campos marcados e intenta de nuevo.'
                : 'Revisa el campo marcado e intenta de nuevo.',
            'red',
            'error',
            {
                heading: 'No se pudo enviar',
                hideAfter: 4200,
                className: 'eis-toast-form',
                stack: 3
            }
        );
    },
    submitHandler: function (formEl, event) {
        if ($(formEl).attr('action')) {
            formEl.submit();
            return false;
        }
        if (event && event.preventDefault) {
            event.preventDefault();
        }
        return true;
    }
});
```

Detalles importantes:

- `onkeyup`, `onfocusout` y `onclick` están desactivados **a propósito** para
  que el feedback en tiempo real lo gobierne la capa propia y no se dupliquen
  mensajes.
- `showErrors` no usa el pintado por defecto del plugin: reutiliza
  `EIS.marcarErrorCampo` / `EIS.quitarErrorCampo`, de modo que el error del
  navegador y el del servidor se ven igual.
- `ignore` incluye los `<select>` y `<textarea>` aunque Materialize los oculte.
- `submitHandler` no hace el POST: solo evita el envío nativo. Los módulos son
  quienes hacen el AJAX.

#### Inicialización

Al cargar el DOM:

```js
$(function () {
    $('form[data-validate]').each(function () {
        inicializarFormulario(this);
    });
});
```

Y para formularios inyectados después (modales, AJAX):

```js
$(document).on('focusin click',
    'form[data-validate] input, form[data-validate] select, form[data-validate] textarea',
    function () {
        if (this.form) inicializarFormulario(this.form);
    });
```

También se expone la API pública:

```js
EIS.initValidacionJQuery = function (form) {
    var $form = $(form);
    $form.each(function () { inicializarFormulario(this); });
    return $form;
};
```

### 5.9 Validación en vivo

El bloque de tiempo real no depende del plugin: escucha eventos delegados en
`document` y usa `EIS.validarCampo`. El flujo es:

1. **`focusin`**: muestra una pista inline (`data-hint`, `title` o mensaje +
   ejemplo de la regla) y, si el formato es exigido, un toast informativo
   único.
2. **`input` / `change`**: filtra caracteres, pinta el estado al instante y
   programa un toast tras una pausa de `600 ms`.
3. **`focusout`**: valida y avisa sin esperar más tecleo.
4. **Corrección**: si el campo pasa a ser válido y antes había error, lanza un
   toast verde breve («Campo completado correctamente.»).
5. **Checkbox / radio**: se validan al marcarse y limpian el error de todo el
   grupo.

La evaluación diferida evita spam:

```js
function evaluarErrorTiempoReal(el) {
    var clave = claveCampo(el);
    if (!camposTocados[clave]) return;

    var msg = pintarEnVivo(el);

    if (!msg) {
        if (ultimoAviso[clave]) {
            delete ultimoAviso[clave];
            if (valorDe($(el)).trim() !== '') {
                avisarCampo(el, 'Campo completado correctamente.', 'success', {
                    heading: 'Listo'
                });
            }
        }
        return;
    }

    if (ultimoAviso[clave] === msg) return;   // mismo error: no repetir

    if (pistasTexto[clave] && pistasTexto[clave] === msg) {
        ultimoAviso[clave] = msg;
        return;
    }

    ultimoAviso[clave] = msg;
    avisarCampo(el, msg, 'warning', {
        heading: 'Revisa este campo',
        incluirEtiqueta: true,
        hideAfter: 4600
    });
}
```

`avisarCampo()` además deduplica por campo y mensaje durante 4 segundos.

### 5.10 Filtros de caracteres

Mientras el usuario escribe, se eliminan caracteres que la regla no admite:

```js
var FILTROS_TIPO = {
    entero:  function (v) { return v.replace(/[^0-9-]/g, ''); },
    decimal: function (v) { return v.replace(/[^0-9.-]/g, ''); },
    moneda:  function (v) { return v.replace(/[^0-9.]/g, ''); },
    cedula:  function (v) { return v.replace(/[^0-9VEJPGvejpg-]/g, ''); },
    rif:     function (v) { return v.replace(/[^0-9VEJPGvejpg-]/g, ''); },
    telefono: function (v) { return v.replace(/[^0-9+\-\s()]/g, ''); },
    username: function (v) { return v.replace(/[^A-Za-z0-9._-]/g, ''); },
    codigo:  function (v) { return v.replace(/[^A-Za-z0-9_.\-\/#\s]/g, ''); },
    numero_orden: function (v) { return v.replace(/[^A-Za-z0-9._\-\/\s]/g, ''); }
};
```

`obtenerFiltro($el)` decide el filtro a partir del alias o de la clave JSON.
`filtrarValorCampo(el)` lo aplica a lo pegado o arrastrado, conservando la
posición del cursor cuando es posible.

> Nota: el filtro es una ayuda de UX, no una validación de seguridad. El
> backend vuelve a validar el valor completo.

### 5.11 Feedback inline y accesibilidad

Las funciones públicas son:

```js
EIS.marcarErrorCampo(el, mensaje); // rojo + mensaje + icono
EIS.quitarErrorCampo(el);          // limpia todo
EIS.marcarCampoValido(el);         // verde + check
```

El mensaje se inserta en un contenedor `.eis-field-feedback` con
`role="alert"` y `aria-describedby`, y el campo recibe `aria-invalid="true"`.
También se muestra un contador de caracteres cuando el campo tiene
`maxlength`:

```
[icono] mensaje                        12 / 100
```

Los estilos viven en `src/Public/css/forms.eis.css`:

```css
.eis-field-feedback { /* contenedor del mensaje */ }
.eis-field-error    { /* mensaje de error */ }
.eis-field-hint     { /* pista de formato */ }
.eis-field-counter  { /* contador de caracteres */ }
.eis-invalid > label { /* etiqueta en rojo */ }
.eis-form-error     { /* panel resumen del formulario */ }
```

### 5.12 Bloqueo del `submit` y doble envío

El formulario con `data-validate` se intercepta en **fase de captura**, antes
de los manejadores de los módulos:

```js
document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || form.tagName !== 'FORM' || !form.hasAttribute('data-validate')) return;

    // Evita envíos duplicados por doble clic (ventana de 800 ms).
    var ahora = Date.now();
    var ultimo = parseInt(form.getAttribute('data-eis-enviando') || '0', 10);
    if (ultimo && (ahora - ultimo) < 800) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return;
    }

    var errores = EIS.validarFormulario(form);
    if (Object.keys(errores).length) {
        e.preventDefault();
        e.stopImmediatePropagation();

        if (EIS.mostrarErroresFormulario) {
            EIS.mostrarErroresFormulario(form, {
                error: 'Revisa los campos marcados e intenta de nuevo.',
                fieldErrors: errores
            });
        } else {
            $.each(errores, function (nombre, texto) {
                EIS.marcarErrorCampo($(form).find('[name="' + nombre + '"]').first(), texto);
            });
        }
        return;
    }

    form.setAttribute('data-eis-enviando', String(ahora));
    if (EIS.limpiarErroresFormulario) {
        EIS.limpiarErroresFormulario(form);
    }
}, true);
```

Además, al cargar el DOM se desactiva la validación nativa del navegador para
que el evento `submit` siempre se dispare y los mensajes tengan el mismo
formato propio:

```js
$(function () {
    $('form[data-validate]').each(function () { this.noValidate = true; });
});
```

### 5.13 Validación cruzada personalizada

Cuando la regla depende de dos campos (por ejemplo, precio de venta no menor
al costo), se usa `EIS.marcarErrorCampo` desde el módulo:

```js
function validarMargenEnVivo() {
    var $costo = $('#producto-costo');
    var $precio = $('#producto-precio');
    var costoVal = parseFloat($costo.val()) || 0;
    var precioVal = parseFloat($precio.val()) || 0;

    if (costoVal > 0 && precioVal > 0 && precioVal < costoVal) {
        EIS.marcarErrorCampo(
            $precio,
            'El precio de venta no puede ser menor al costo de compra ($' + costoVal.toFixed(2) + ').'
        );
        return false;
    }

    var msgActual = $precio.closest('.input-field').find('.eis-field-error').text();
    if (msgActual.indexOf('precio de venta no puede ser menor') !== -1) {
        EIS.quitarErrorCampo($precio);
    }
    return true;
}
```

Este tipo de regla no se declara en el JSON porque no es un formato, es una
relación entre datos.

---

## 6. Alertas con jQuery Toast Plugin

### 6.1 `EIS.toast`

Todas las notificaciones pasan por un único envoltorio en `app.core.js`:

```js
EIS.toast = function (msg, color, icon, options) {
    color = color || 'indigo';
    icon = icon || 'check_circle';
    options = options || {};

    if (window.jQuery && typeof $.toast === 'function') {
        var tipo = 'info';
        var fondo = '#1a237e';
        var c = String(color).toLowerCase();

        if (c.indexOf('green') !== -1 || c.indexOf('success') !== -1) {
            tipo = 'success';
            fondo = '#1b5e20';
        } else if (c.indexOf('red') !== -1 || c.indexOf('danger') !== -1) {
            tipo = 'error';
            fondo = '#b71c1c';
        } else if (c.indexOf('orange') !== -1 || c.indexOf('amber') !== -1 ||
                   c.indexOf('yellow') !== -1 || c.indexOf('warning') !== -1) {
            tipo = 'warning';
            fondo = '#e65100';
        } else if (c.indexOf('indigo') !== -1 || c.indexOf('blue') !== -1 ||
                   c.indexOf('info') !== -1) {
            tipo = 'info';
            fondo = '#1a237e';
        }

        $.toast({
            text: msg,
            heading: options.heading || '',
            icon: tipo,
            position: options.position || 'top-right',
            hideAfter: (options.hideAfter !== undefined) ? options.hideAfter : 3500,
            stack: (options.stack !== undefined) ? options.stack : 5,
            loader: options.loader === true,
            loaderBg: '#ffffff',
            allowToastClose: true,
            showHideTransition: 'slide',
            bgColor: fondo,
            textColor: '#ffffff',
            textAlign: 'left',
            'class': options.className || ''
        });
        return;
    }

    // Respaldo: toast de Materialize si el plugin no está disponible.
    // ...
};
```

Firma:

```js
EIS.toast(mensaje, color, icono, opciones)
```

| Parámetro | Uso |
|---|---|
| `mensaje` | Texto principal. |
| `color` | `'green'`, `'red'`, `'orange'`, `'indigo'`... De aquí se deriva el tipo visual. |
| `icono` | Solo se usa en el respaldo de Materialize. jQuery Toast usa el icono del tipo. |
| `options.heading` | Título corto encima del mensaje. |
| `options.hideAfter` | Milisegundos antes de ocultarse. |
| `options.position` | `top-right`, `top-left`, `bottom-right`... |
| `options.stack` | Máximo de toasts apilados. |
| `options.className` | Clase CSS extra. |
| `options.loader` | Barra de progreso inferior. |

### 6.2 Mapeo de colores

| Color recibido | Tipo jQuery Toast | Fondo |
|---|---|---|
| `green`, `success` | `success` | `#1b5e20` |
| `red`, `danger` | `error` | `#b71c1c` |
| `orange`, `amber`, `yellow`, `warning` | `warning` | `#e65100` |
| `indigo`, `blue`, `info` | `info` | `#1a237e` |

El color se busca como subcadena, así que `'red darken-2'` también funciona.

### 6.3 Uso desde los módulos

Éxito:

```js
EIS.toast(r.message, 'green', 'check_circle');
```

Error de conexión:

```js
EIS.toast('No se pudo conectar con el servidor. Intenta de nuevo.', 'red', 'error');
```

Error de negocio:

```js
EIS.toast(r.error || 'No se pudo eliminar. Intenta de nuevo.', 'red', 'error');
```

Aviso informativo:

```js
EIS.toast('Cliente encontrado. Se precargaron sus datos.', 'indigo', 'person');
```

### 6.4 `avisarCampo`: toasts contextuales de validación

La validación en vivo no llama directamente a `EIS.toast`; usa un envoltorio
que pone el nombre del campo como título y evita repetir el mismo aviso:

```js
function avisarCampo(el, texto, tipo, opciones) {
    if (!texto || typeof EIS.toast !== 'function') return;

    var clave = claveCampo(el);
    var ahora = Date.now();
    var previo = ultimosToasts[clave];
    tipo = tipo || 'warning';

    if (previo && previo.texto === texto && previo.tipo === tipo &&
        (ahora - previo.ts) < 4000) {
        return;
    }
    ultimosToasts[clave] = { texto: texto, tipo: tipo, ts: ahora };

    var colores = { info: 'indigo', warning: 'orange', success: 'green' };
    var iconos  = { info: 'info', warning: 'warning', success: 'success' };
    var etiqueta = etiquetaCampo(el);
    var heading = 'Validación';

    if (opciones && opciones.heading) {
        heading = opciones.heading;
    } else if (etiqueta) {
        heading = etiqueta;
    }
    if (opciones && opciones.incluirEtiqueta && etiqueta) {
        heading += ': ' + etiqueta;
    }

    EIS.toast(escaparHtml(texto), colores[tipo] || 'indigo', iconos[tipo] || 'info', {
        heading: escaparHtml(heading),
        hideAfter: (opciones && opciones.hideAfter !== undefined)
            ? opciones.hideAfter
            : (tipo === 'success' ? 2400 : 4200),
        className: 'eis-toast-field eis-toast-' + tipo,
        stack: 3
    });
}
```

Tipos usados:

| Tipo | Color | Duración | Cuándo |
|---|---|---|---|
| `info` | indigo | 5200 ms | Pista de formato al enfocar. |
| `warning` | orange | 4600 ms | Error de campo tras pausa o blur. |
| `success` | green | 2400 ms | Campo corregido. |

### 6.5 `EIS.mostrarErroresFormulario`: panel persistente + toast

Los toasts son efímeros. Para los errores de validación se necesita algo que
permanezca mientras el usuario corrige. Eso lo hace
`EIS.mostrarErroresFormulario`:

1. Limpia errores previos.
2. Lee `fieldErrors` (raíz o `data.fieldErrors`).
3. Marca cada campo con `EIS.marcarErrorCampo`.
4. Inserta un panel `.eis-form-error` al inicio del formulario con:
   - icono de error,
   - mensaje general,
   - lista de hasta 6 campos con enlaces que llevan el foco al campo.
5. Lanza un toast rojo, evitando repetirlo si el mismo mensaje se mostró hace
   menos de 3 segundos.
6. Enfoca el primer campo inválido.

```js
var fieldErrors = (data && data.fieldErrors)
    ? data.fieldErrors
    : (data && data.data && data.data.fieldErrors) ? data.data.fieldErrors : {};

$.each(fieldErrors, function (name, msg) {
    var $field = $form.find('[name="' + name + '"]').first();
    if (!$field.length) return;

    if (typeof EIS.marcarErrorCampo === 'function') {
        EIS.marcarErrorCampo($field, msg);
        return;
    }
    // Respaldo si app.validate.js no estuviera cargado.
    // ...
});
```

El panel es accesible:

```js
var $panel = $('<div class="card-panel eis-form-error" role="alert" aria-live="assertive"></div>');
```

### 6.6 Respaldo Materialize

Si jQuery Toast no está cargado, `EIS.toast` cae en `M.toast`:

```js
var titulo = options.heading
    ? '<strong style="display:block;margin-bottom:0.15rem;">' + options.heading + '</strong>'
    : '';
var html = '<i class="material-icons left" style="font-size:1.2rem;">' + icon + '</i>' +
    titulo + msg;
M.toast({
    html: html,
    classes: color + ' rounded',
    displayLength: (options.hideAfter !== undefined) ? options.hideAfter : 3000
});
```

Esto mantiene la aplicación funcionando aunque falle la carga del plugin.

### 6.7 Buenas prácticas con toasts

- **No usar toast para errores de campo**: para eso está el feedback inline y
  el panel de resumen. El toast es un refuerzo, no el único canal.
- **No repetir el mismo mensaje**: `avisarCampo` y `mostrarErroresFormulario`
  ya deduplican; no añadir otro toast en el mismo evento.
- **Mensajes accionables**: «Ingresa una cédula válida (ej.: V-12345678)» es
  mejor que «Error de validación».
- **Duración proporcional**: 2400 ms para éxito, 4200-4600 ms para errores.
- **Un solo toast por evento**: si se muestran varios, usar `stack: 3` para no
  tapar la pantalla.
- **Escapar HTML**: `avisarCampo` usa `escaparHtml()` antes de pasar el texto
  al plugin.

---

## 7. Recorrido completo de un formulario

Tomemos el formulario de clientes.

### 7.1 HTML

```html
<form id="form-cliente" data-validate>
    <input type="hidden" name="id" id="cliente-id" value="">

    <input type="text" name="cedula" id="cliente-cedula"
           maxlength="20" title="Entre 5 y 20 caracteres."
           required minlength="5" data-validate="cedula">

    <input type="text" name="nombre" id="cliente-nombre"
           maxlength="100" title="Entre 2 y 100 caracteres."
           required minlength="2" data-validate="nombre">

    <input type="text" name="apellido" id="cliente-apellido"
           maxlength="100" title="Entre 2 y 100 caracteres."
           required minlength="2" data-validate="nombre">

    <input type="text" name="direccion" id="cliente-direccion"
           maxlength="500" data-validate="direccion">

    <input type="tel" name="telefono" id="cliente-telefono"
           maxlength="20" minlength="7" data-validate="telefono">
</form>
```

### 7.2 Lo que hace el navegador

1. Al enfocar `#cliente-cedula`, `app.validate.js` muestra la pista:
   «Ingresa una cédula válida (ej.: V-12345678)» y un toast informativo.
2. Al escribir, el filtro elimina letras que no sean `V/E/J/P/G`, dígitos o
   guion.
3. Al salir del campo, `EIS.validarCampo` comprueba:
   - obligatorio,
   - longitud mínima 5,
   - patrón de cédula.
4. Si falla, pinta el borde rojo, el mensaje inline y, tras la pausa, un toast
   naranja con el nombre del campo.
5. Si se corrige, pinta verde y lanza un toast de éxito breve.

### 7.3 Lo que hace el servidor

1. El módulo `app.clientes.js` hace el POST con `$form.serialize()`.
2. `$.ajaxSetup` añade `csrf_token`.
3. `ClienteController::crear()` verifica el token.
4. `validarCampos()` ejecuta el esquema con `Validator::validar`.
5. Si hay errores, lanza `ValidationException` con **todos** los mensajes.
6. `handle()` la captura y llama a `jsonValidationException`.
7. La respuesta es HTTP 422:

```json
{
  "success": false,
  "error": "Revisa los datos ingresados e intenta de nuevo.",
  "fieldErrors": {
    "cedula": "Ingresa una cédula válida (ej.: V-12345678 o 12345678).",
    "nombre": "El campo nombre debe tener al menos 2 caracteres."
  }
}
```

8. Si todo es válido, el modelo inserta y responde HTTP 200:

```json
{
  "success": true,
  "data": null,
  "message": "Cliente creado exitosamente"
}
```

### 7.4 El detalle del HTTP 422 en el frontend

`EIS.mostrarErroresFormulario` ya sabe leer `fieldErrors`. Pero para que se
ejecute hay que llamarlo desde el manejador correcto. jQuery considera un 422
como error, así que el manejador debe leer `xhr.responseJSON`:

```js
$.ajax({
    url: API + accion,
    type: 'POST',
    data: $form.serialize(),
    dataType: 'json',
    success: function (r) {
        if (r.success) {
            EIS.toast(r.message, 'green', 'check_circle');
            $('#modal-cliente').modal('close');
            refrescarKPI();
            refrescarTabla();
        } else {
            EIS.mostrarErroresFormulario($form, r);
        }
    },
    error: function (xhr) {
        var r = xhr.responseJSON || {};
        if (xhr.status === 422 && r.fieldErrors) {
            EIS.mostrarErroresFormulario($form, r);
            return;
        }
        EIS.toast(r.error || 'No se pudo conectar con el servidor. Intenta de nuevo.', 'red', 'error');
    }
});
```

> En varios módulos actuales el manejador `error` solo muestra un toast
> genérico y no lee `xhr.responseJSON`. El contrato del backend está listo
> (`422` + `fieldErrors`); para aprovecharlo al 100 % hay que añadir la rama
> anterior en el módulo correspondiente.

### 7.5 Diagrama de secuencia

```
Usuario          Navegador (app.validate.js)              Servidor (PHP)
   │                        │                                    │
   │ escribe en campo       │                                    │
   ├───────────────────────►│ filtro + validarCampo              │
   │                        │ feedback inline + toast            │
   │                        │                                    │
   │ pulsa Guardar          │                                    │
   ├───────────────────────►│ submit (captura)                   │
   │                        │ validarFormulario()                │
   │                        │                                    │
   │              ¿errores? │                                    │
   │        sí ◄────────────┤ panel + toast, stop                │
   │                        │                                    │
   │         no             │ POST + csrf_token                  │
   │                        ├───────────────────────────────────►│ verifyCsrfToken
   │                        │                                    │ validarCampos()
   │                        │                                    │ Validator::validar
   │                        │                                    │
   │                        │        422 + fieldErrors           │
   │                        │◄───────────────────────────────────┤
   │                        │ mostrarErroresFormulario           │
   │                        │ marca campos + panel + toast       │
   │                        │                                    │
   │         corrige        │                                    │
   │                        │ POST válido                        │
   │                        ├───────────────────────────────────►│ modelo + INSERT
   │                        │        200 + success               │
   │                        │◄───────────────────────────────────┤
   │                        │ toast verde + cerrar modal         │
   │◄───────────────────────┤                                    │
```

---

## 8. Contrato JSON entre backend y frontend

### Respuesta de éxito

```json
{
  "success": true,
  "data": { },
  "message": "Operación exitosa"
}
```

### Error de validación (recomendado)

HTTP `422`:

```json
{
  "success": false,
  "error": "Revisa los datos ingresados e intenta de nuevo.",
  "fieldErrors": {
    "campo": "Mensaje legible para el usuario."
  }
}
```

`EIS.mostrarErroresFormulario` mapea `fieldErrors` a los `[name="campo"]` del
formulario.

### Error de negocio o de conexión

HTTP `400`, `404`, `500`:

```json
{
  "success": false,
  "error": "Ya existe un cliente registrado con esa cédula.",
  "data": null
}
```

No trae `fieldErrors`: se muestra como toast o mensaje general.

### Error de validación antiguo

HTTP `422`:

```json
{
  "success": false,
  "error": "Revisa los datos del formulario.",
  "errors": {
    "campo": "Mensaje"
  }
}
```

La clave es `errors`, no `fieldErrors`. El panel general se muestra, pero los
campos no se marcan. Se mantiene por compatibilidad; el flujo nuevo usa
`jsonValidationException`.

---

## 9. Cómo añadir un campo nuevo

Checklist para un campo validado de punta a punta:

1. **Define la regla** en `regex_validations.json` si es un formato nuevo.
   Incluye `pattern`, `flags`, `minlength`, `maxlength`, `mensaje` y
   `ejemplo_valido`.
2. **Regenera** `src/Public/js/regex_validations.js`:
   ```bash
   php src/cli/generar_regex_js.php
   ```
3. **Añade el alias** en `ALIASES` de `app.validate.js` si quieres usar un
   nombre corto. Si no, usa la clave completa en `data-validate`.
4. **Declara el campo en la vista**:
   ```html
   <input type="text" name="mi_campo" id="mi-campo"
          required minlength="3" maxlength="50"
          data-validate="mi_alias"
          data-msg-required="Ingresa mi campo."
          title="Entre 3 y 50 caracteres.">
   ```
5. **Añade la regla al esquema del controlador**:
   ```php
   'mi_campo' => [
       'label' => 'mi campo',
       'type' => 'texto',
       'required' => true,
       'min' => 3,
       'max' => 50,
       'pattern' => Validator::patron('mi_seccion.mi_regla'),
       'patternMessage' => 'Mi campo contiene caracteres no permitidos.',
   ],
   ```
6. **Verifica el CSRF** en la acción correspondiente.
7. **Maneja el error 422** en el módulo JS con
   `EIS.mostrarErroresFormulario($form, xhr.responseJSON)`.
8. **Prueba**:
   - vacío,
   - longitud mínima y máxima,
   - caracteres inválidos,
   - valor válido,
   - manipulación con DevTools (el backend debe rechazarlo igual).

---

## 10. Problemas frecuentes

### El mensaje del servidor no marca el campo

Causa probable: el controlador usa `jsonValidationErrors` (clave `errors`) o
el módulo no lee `xhr.responseJSON` en `error`. Solución: usar
`jsonValidationException` y llamar a `EIS.mostrarErroresFormulario` con la
respuesta del 422.

### `data-validate="cedula"` no hace nada

Revisa que:

- `regex_validations.js` esté cargado antes de `app.validate.js`;
- el alias exista en `ALIASES`;
- el formulario tenga el atributo `data-validate`;
- no haya un error de JavaScript anterior que detenga la ejecución.

### El formulario se envía dos veces

El bloqueo de `submit` cubre formularios con `data-validate`. Si el módulo
escucha `click` en un botón en lugar de `submit`, el bloqueo de doble envío no
aplica. Usa `submit` o desactiva el botón mientras dura la petición.

### El toast no aparece

Comprueba que:

- `jquery.toast.min.js` y su CSS estén cargados;
- `EIS.toast` exista (depende de `app.core.js`);
- el mensaje no esté vacío;
- no se esté llamando antes de que el DOM esté listo.

### Materialize oculta el `<select>` y no se valida

El `ignore` del validator incluye explícitamente `select` y `textarea`, y
`EIS.validarCampo` trata el select vacío como requerido. Si aun así falla,
verifica que el `name` esté en el `<select>` y no solo en el input visual de
Materialize.

### Una fecha válida en formato no existe

El patrón solo valida formato. La comprobación real la hacen `checkdate()` en
PHP y `esFechaValida()` en JS. Si añades una regla de fecha nueva, replica esa
validación semántica.

### El backend acepta un valor que el frontend rechaza

Es normal: el frontend puede ser más estricto por UX. Lo importante es lo
contrario: **el backend nunca debe aceptar algo que viole la regla de negocio**.
Si el frontend rechaza y el backend acepta, revisa que ambos usen la misma
clave del JSON.

---

## 11. Referencia rápida

### 11.1 Tipos de `Validator`

| `type` | Método | Notas |
|---|---|---|
| `texto` / `string` | `texto()` | `trim`, control chars, min/max, patrón. |
| `cedula` | `cedula()` | Regla `identificacion_venezuela.cedula_venezolana`. |
| `rif` | `rif()` | Regla `identificacion_venezuela.rif_venezolano`. |
| `telefono` | `telefono()` | Regla `contacto.telefono_flexible`. |
| `username` | `username()` | Regla `texto_y_personas.nombre_usuario`. |
| `email` | `email()` | Regla `contacto.email`, máximo 100. |
| `password` | `password()` | Sin `trim`, complejidad opcional. |
| `entero` / `int` | `entero()` | Patrón `aplicacion.entero`, rango. |
| `id` | `id()` | Entero requerido, mínimo 1. |
| `decimal` / `float` | `decimal()` | Patrón `aplicacion.monto`, 2 decimales. |
| `fecha` / `date` | `fecha()` | `checkdate`, opción `noFutura`. |
| `bool` / `boolean` | `bool()` | Devuelve 0 o 1. |
| `enum` | `enum()` | Lista blanca con `in_array` estricto. |
| `tiempoUso` | `tiempoUso()` | `HH:MM:SS`, minutos/segundos 00-59. |
| `numeroOrden` | `numeroOrden()` | Regla `aplicacion.numero_orden`. |
| `busqueda` | `busqueda()` | Término parcial sin HTML. |

### 11.2 Atributos HTML

| Atributo | Ejemplo | Efecto |
|---|---|---|
| `data-validate` | `data-validate="cedula"` | Aplica reglas del JSON. |
| `required` | `required` | Obligatorio. |
| `minlength` | `minlength="5"` | Longitud mínima. |
| `maxlength` | `maxlength="20"` | Longitud máxima. |
| `min` / `max` | `min="0.01"` | Rango numérico. |
| `pattern` | `pattern="[A-Z]{2}"` | Patrón HTML5. |
| `data-match` | `data-match="#password2"` | Debe coincidir con otro campo. |
| `data-msg-*` | `data-msg-required="..."` | Mensaje personalizado. |
| `data-hint` | `data-hint="Formato: V-12345678"` | Pista al enfocar. |
| `title` | `title="Entre 2 y 100 caracteres"` | Pista de respaldo. |
| `type` | `type="email"` | Regla nativa asociada. |

### 11.3 API pública de `EIS`

| Función | Devuelve | Uso |
|---|---|---|
| `EIS.validarCampo(el)` | `string` | Mensaje de error o `''`. |
| `EIS.validarFormulario(form)` | `object` | `{campo: mensaje}`. |
| `EIS.esFormularioValido(form)` | `boolean` | Atajo booleano. |
| `EIS.marcarErrorCampo(el, msg)` | — | Pinta error inline. |
| `EIS.quitarErrorCampo(el)` | — | Limpia el campo. |
| `EIS.marcarCampoValido(el)` | — | Pinta estado válido. |
| `EIS.mostrarErroresFormulario(form, data)` | — | Panel + campos + toast. |
| `EIS.limpiarErroresFormulario(form)` | — | Limpia todo. |
| `EIS.mostrarErrorAnexo(form, selector, data)` | — | Error en contenedor propio. |
| `EIS.toast(msg, color, icon, opts)` | — | Notificación jQuery Toast. |
| `EIS.initValidacionJQuery(form)` | jQuery | Inicializa el plugin. |
| `EIS.etiquetaCampo(el)` | `string` | Etiqueta legible del campo. |
| `EIS.resolverReglaValidacion(clave)` | `object\|null` | Regla efectiva. |
| `EIS.actualizarEstadoCampo(el)` | `string` | Repinta y devuelve mensaje. |
| `EIS.reglasValidacion` | `object` | Reglas manuales/overrides. |

---

## 12. Archivos implicados

### Backend

| Ruta | Contenido |
|---|---|
| `regex_validations.json` | Fuente única de patrones y mensajes. |
| `src/app/core/RegexRules.php` | Acceso PHP al JSON. |
| `src/app/core/Validator.php` | Validación y normalización tipada. |
| `src/app/core/ValidationException.php` | Errores por campo. |
| `src/app/Controllers/BaseController.php` | Respuestas JSON y `validarCampos`. |
| `src/app/Controllers/ClienteController.php` | Ejemplo completo con CSRF y esquema. |
| `src/app/Controllers/UsuarioController.php` | Ejemplo con `enum`, `email`, `password`. |
| `src/app/Controllers/AuthController.php` | Login, `Validator::username`, throttle. |
| `src/app/core/router.php` | Generación y verificación de CSRF. |
| `src/cli/generar_regex_js.php` | Generador del módulo JS. |

### Frontend

| Ruta | Contenido |
|---|---|
| `src/Public/js/app.core.js` | `EIS.toast`, `mostrarErroresFormulario`, limpieza. |
| `src/Public/js/regex_validations.js` | Reglas generadas y API `EIS.Regex`. |
| `src/Public/js/app.validate.js` | Motor de validación, jQuery Validation, tiempo real. |
| `src/Public/js/app.clientes.js` | Patrón de envío AJAX con `data-validate`. |
| `src/Public/js/app.roles.js` | Validación manual con `EIS.validarFormulario`. |
| `src/Public/js/app.inventario.js` | Validación cruzada precio/costo. |
| `src/Public/js/app.cyber.js` | Validación manual + `mostrarErroresFormulario`. |
| `src/app/template/layout.php` | Orden de carga y CSRF global. |
| `src/app/Views/clientes.php` | Ejemplo de atributos de validación. |
| `src/app/Views/login.php` | Formulario con `data-msg-required` y CSRF. |
| `src/Public/css/forms.eis.css` | Estilos del feedback y del panel de errores. |

---

## Resumen final

- **Una fuente**: `regex_validations.json` alimenta a PHP y a JavaScript.
- **Dos capas**: el backend valida por seguridad; el frontend valida por UX.
- **Backend**: `Validator` normaliza y acumula errores en
  `ValidationException`; `BaseController` los convierte en `422 + fieldErrors`.
- **Frontend**: `app.validate.js` resuelve reglas, valida en vivo, integra
  jQuery Validation mediante `eisRegla` y bloquea envíos inválidos.
- **Alertas**: `EIS.toast` envuelve jQuery Toast Plugin y unifica colores,
  duración y deduplicación.
- **Errores persistentes**: `EIS.mostrarErroresFormulario` pinta campos y panel;
  los toasts son el refuerzo no intrusivo.
- **Regla de oro**: nunca confiar en la validación del navegador.
