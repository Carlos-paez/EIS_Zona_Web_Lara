<?php

namespace App\Core;

/**
 * Acceso a las reglas de validación definidas en regex_validations.json.
 *
 * El archivo JSON es la fuente única de verdad para las expresiones
 * regulares del backend y del frontend (este último las recibe mediante
 * el archivo generado src/Public/js/regex_validations.js).
 *
 * Las reglas se identifican con claves separadas por puntos, por ejemplo:
 *
 *     RegexRules::test('contacto.email', $email);
 *     preg_match(RegexRules::pcre('identificacion_venezuela.cedula_venezolana'), $cedula);
 *     RegexRules::maxlength('aplicacion.monto');
 */
final class RegexRules
{
    /** @var array<string,mixed>|null */
    private static ?array $rules = null;

    /**
     * Ruta absoluta del archivo de reglas (raíz del proyecto).
     */
    public static function path(): string
    {
        // __DIR__ = <proyecto>/src/app/core
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'regex_validations.json';
    }

    /**
     * Devuelve todas las reglas cargadas (incluye el bloque _meta).
     *
     * @return array<string,mixed>
     */
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
        if ($json === false) {
            throw new \RuntimeException('No se pudo leer el archivo de validaciones: ' . $path);
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('El archivo de validaciones no es un JSON válido: ' . $e->getMessage(), 0, $e);
        }

        if (!is_array($data)) {
            throw new \RuntimeException('El archivo de validaciones debe contener un objeto JSON');
        }

        self::$rules = $data;
        return self::$rules;
    }

    /**
     * Obtiene una regla completa por su clave con puntos.
     *
     * @return array<string,mixed>|null
     */
    public static function get(string $key): ?array
    {
        $node = self::all();

        foreach (explode('.', $key) as $part) {
            if (!is_array($node) || !array_key_exists($part, $node)) {
                return null;
            }
            $node = $node[$part];
        }

        return is_array($node) ? $node : null;
    }

    /**
     * Indica si una regla existe y tiene un patrón utilizable.
     */
    public static function exists(string $key): bool
    {
        $rule = self::get($key);
        return is_array($rule) && isset($rule['pattern']) && is_string($rule['pattern']) && $rule['pattern'] !== '';
    }

    /**
     * Devuelve el patrón PCRE listo para preg_match(), con delimitadores
     * y flags (siempre incluye 'u' para UTF-8).
     */
    public static function pcre(string $key): string
    {
        $pattern = self::requirePattern($key);
        $flags   = self::flags($key);

        // El delimitador elegido (~) no aparece en los patrones del JSON.
        return '~' . str_replace('~', '\\~', $pattern) . '~' . $flags;
    }

    /**
     * Comprueba un valor completo contra la regla indicada.
     */
    public static function test(string $key, string $value): bool
    {
        $resultado = preg_match(self::pcre($key), $value);
        if ($resultado === false) {
            throw new \RuntimeException("El patrón de validación '$key' no es válido para PCRE");
        }
        return $resultado === 1;
    }

    /**
     * Longitud máxima declarada para una regla, o null si no aplica.
     */
    public static function maxlength(string $key): ?int
    {
        return self::integerField($key, 'maxlength');
    }

    /**
     * Longitud mínima declarada para una regla, o null si no aplica.
     */
    public static function minlength(string $key): ?int
    {
        return self::integerField($key, 'minlength');
    }

    /**
     * Etiqueta legible de la regla (para mensajes de error).
     */
    public static function label(string $key, ?string $default = null): string
    {
        $label = self::field($key, 'label');
        return is_string($label) && $label !== '' ? $label : ($default ?? $key);
    }

    /**
     * Mensaje de error recomendado para la regla.
     */
    public static function mensaje(string $key, ?string $default = null): string
    {
        $mensaje = self::field($key, 'mensaje');
        return is_string($mensaje) && $mensaje !== '' ? $mensaje : ($default ?? self::label($key));
    }

    /**
     * Lee una propiedad arbitraria de una regla.
     */
    public static function field(string $key, string $property): mixed
    {
        $rule = self::get($key);
        return $rule[$property] ?? null;
    }

    /**
     * Reglas específicas de la aplicación (sección "aplicacion").
     *
     * @return array<string,array<string,mixed>>
     */
    public static function aplicacion(): array
    {
        $rules = self::get('aplicacion');
        return is_array($rules) ? $rules : [];
    }

    /**
     * Limpia la caché en memoria; útil en pruebas o tras editar el JSON.
     */
    public static function reload(): void
    {
        self::$rules = null;
    }

    private static function requirePattern(string $key): string
    {
        $rule = self::get($key);
        if (!is_array($rule) || !isset($rule['pattern']) || !is_string($rule['pattern']) || $rule['pattern'] === '') {
            throw new \InvalidArgumentException("No existe el patrón de validación '$key' en regex_validations.json");
        }
        return $rule['pattern'];
    }

    private static function flags(string $key): string
    {
        $raw = self::field($key, 'flags');
        $raw = is_string($raw) ? $raw : '';

        // Solo modificadores seguros de PCRE; nunca 'e'.
        $flags = preg_replace('/[^imsuxADJU]/', '', $raw) ?? '';
        $flags = implode('', array_unique(str_split($flags)));

        if (!str_contains($flags, 'u')) {
            $flags .= 'u';
        }

        return $flags;
    }

    private static function integerField(string $key, string $property): ?int
    {
        $value = self::field($key, $property);
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int)$value;
        }
        return null;
    }
}
