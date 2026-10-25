<?php
/**
 * Genera src/Public/js/regex_validations.js a partir de regex_validations.json.
 *
 * El archivo JSON vive en la raíz del proyecto (fuera del DocumentRoot), por
 * lo que el navegador no puede leerlo directamente. Este script lo convierte
 * en un módulo JavaScript autocontenido y versionado.
 *
 * Uso:
 *     php src/cli/generar_regex_js.php
 *
 * Después de editar regex_validations.json hay que volver a ejecutar este
 * comando. El archivo generado no debe editarse a mano.
 */

declare(strict_types=1);

$raiz       = dirname(__DIR__, 2);
$rutaJson   = $raiz . DIRECTORY_SEPARATOR . 'regex_validations.json';
$rutaSalida = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'regex_validations.js';

if (!is_file($rutaJson)) {
    fwrite(STDERR, "No se encontró el archivo: $rutaJson\n");
    exit(1);
}

try {
    $json = file_get_contents($rutaJson);
    if ($json === false) {
        throw new RuntimeException("No se pudo leer: $rutaJson");
    }
    /** @var array<string,mixed> $datos */
    $datos = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    fwrite(STDERR, 'JSON inválido: ' . $e->getMessage() . "\n");
    exit(1);
}

if (!is_array($datos) || $datos === []) {
    fwrite(STDERR, "El JSON debe contener un objeto de reglas no vacío.\n");
    exit(1);
}

/**
 * Recorre las reglas y comprueba que cada "pattern" sea una expresión PCRE
 * válida. Así un error de sintaxis se detecta al generar y no en producción.
 *
 * @param array<string,mixed> $nodo
 * @param string              $ruta
 */
function validarPatrones(array $nodo, string $ruta = ''): void
{
    foreach ($nodo as $clave => $valor) {
        $actual = $ruta === '' ? (string)$clave : $ruta . '.' . $clave;

        if (!is_array($valor)) {
            continue;
        }

        if (isset($valor['pattern'])) {
            if (!is_string($valor['pattern']) || $valor['pattern'] === '') {
                throw new RuntimeException("La regla '$actual' no tiene un patrón válido.");
            }

            $flags = isset($valor['flags']) && is_string($valor['flags']) ? $valor['flags'] : '';
            $flags = preg_replace('/[^imsuxADJU]/', '', $flags) ?? '';
            $flags = implode('', array_unique(str_split($flags)));
            if (!str_contains($flags, 'u')) {
                $flags .= 'u';
            }

            $pcre = '~' . str_replace('~', '\\~', $valor['pattern']) . '~' . $flags;
            if (@preg_match($pcre, '') === false) {
                throw new RuntimeException("El patrón de la regla '$actual' no es válido para PCRE: $pcre");
            }
        }

        validarPatrones($valor, $actual);
    }
}

try {
    validarPatrones($datos);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$opciones = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
$jsonJs   = json_encode($datos, $opciones);
if ($jsonJs === false) {
    fwrite(STDERR, 'No se pudo serializar el JSON para JavaScript: ' . json_last_error_msg() . "\n");
    exit(1);
}

// Indenta el JSON dentro del IIFE para que el archivo generado sea legible.
$jsonJs = ltrim(preg_replace('/^/m', '    ', $jsonJs) ?? $jsonJs);

$version = (string)($datos['_meta']['version'] ?? 'sin-version');
$fecha   = date('Y-m-d H:i:s');

$js = <<<JS
/* =====================================================================
 * ARCHIVO GENERADO AUTOMÁTICAMENTE. NO EDITAR A MANO.
 *
 * Fuente:  regex_validations.json (versión {$version})
 * Generado: {$fecha}
 * Comando: php src/cli/generar_regex_js.php
 *
 * Expone window.EIS.Regex con:
 *   get(clave)             -> regla completa o null
 *   has(clave)             -> true si la regla existe
 *   test(clave, valor)     -> true/false
 *   maxlength(clave)       -> número o null
 *   minlength(clave)       -> número o null
 *   mensaje(clave, def)    -> mensaje de la regla
 *   label(clave, def)      -> etiqueta legible
 *   all()                  -> objeto completo de reglas
 *   version()              -> versión del JSON de origen
 *   clearCache()           -> limpia las expresiones compiladas
 * ===================================================================== */

(function (global) {
    'use strict';

    var RULES = {$jsonJs};

    var cache = Object.create(null);

    function get(clave) {
        if (typeof clave !== 'string' || clave === '') {
            return null;
        }

        var nodo = RULES;
        var partes = clave.split('.');

        for (var i = 0; i < partes.length; i++) {
            if (!nodo || typeof nodo !== 'object' || !Object.prototype.hasOwnProperty.call(nodo, partes[i])) {
                return null;
            }
            nodo = nodo[partes[i]];
        }

        return (nodo && typeof nodo === 'object') ? nodo : null;
    }

    function has(clave) {
        var regla = get(clave);
        return !!(regla && typeof regla.pattern === 'string' && regla.pattern !== '');
    }

    function normalizarFlags(flags) {
        var permitidos = 'imsu';
        var salida = '';
        var texto = (typeof flags === 'string') ? flags : '';

        for (var i = 0; i < texto.length; i++) {
            var flag = texto.charAt(i).toLowerCase();
            if (permitidos.indexOf(flag) !== -1 && salida.indexOf(flag) === -1) {
                salida += flag;
            }
        }

        // Los patrones del proyecto son UTF-8.
        if (salida.indexOf('u') === -1) {
            salida += 'u';
        }

        return salida;
    }

    function compilar(clave) {
        if (cache[clave]) {
            return cache[clave];
        }

        var regla = get(clave);
        if (!regla || typeof regla.pattern !== 'string' || regla.pattern === '') {
            return null;
        }

        // Los patrones del JSON ya están anclados con ^ y $, igual que en PHP.
        var regex = new RegExp(regla.pattern, normalizarFlags(regla.flags));
        cache[clave] = regex;
        return regex;
    }

    function test(clave, valor) {
        var regex = compilar(clave);
        if (!regex) {
            return false;
        }

        var texto = (valor === null || valor === undefined) ? '' : String(valor);
        return regex.test(texto);
    }

    function enteroDe(clave, propiedad) {
        var regla = get(clave);
        if (!regla) {
            return null;
        }

        var valor = regla[propiedad];
        if (typeof valor === 'number' && isFinite(valor)) {
            return valor;
        }
        if (typeof valor === 'string' && /^\d+$/.test(valor)) {
            return parseInt(valor, 10);
        }
        return null;
    }

    function maxlength(clave) {
        return enteroDe(clave, 'maxlength');
    }

    function minlength(clave) {
        return enteroDe(clave, 'minlength');
    }

    function label(clave, porDefecto) {
        var regla = get(clave);
        if (regla && typeof regla.label === 'string' && regla.label !== '') {
            return regla.label;
        }
        return (porDefecto !== undefined) ? porDefecto : clave;
    }

    function mensaje(clave, porDefecto) {
        var regla = get(clave);
        if (regla && typeof regla.mensaje === 'string' && regla.mensaje !== '') {
            return regla.mensaje;
        }
        if (porDefecto !== undefined) {
            return porDefecto;
        }
        var etiqueta = label(clave, clave);
        return 'El campo "' + etiqueta + '" no es válido.';
    }

    function all() {
        return RULES;
    }

    function version() {
        return (RULES._meta && RULES._meta.version) ? String(RULES._meta.version) : '{$version}';
    }

    function clearCache() {
        cache = Object.create(null);
    }

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
})(typeof window !== 'undefined' ? window : globalThis);

JS;

if (file_put_contents($rutaSalida, $js, LOCK_EX) === false) {
    fwrite(STDERR, "No se pudo escribir: $rutaSalida\n");
    exit(1);
}

$total = substr_count($jsonJs, '"pattern"');
fwrite(STDOUT, "Generado: $rutaSalida\n");
fwrite(STDOUT, "Reglas con patrón: $total\n");
fwrite(STDOUT, "Versión: $version\n");
