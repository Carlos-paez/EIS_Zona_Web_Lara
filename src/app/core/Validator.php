<?php

namespace App\Core;

/**
 * Validador estricto del backend.
 *
 * Centraliza la validación y coerción tipada de TODA la entrada del
 * usuario (POST/GET) para que ningún dato pueda llegar a los modelos o
 * a la base de datos sin pasar por comprobaciones coherentes con los
 * campos de los formularios y con los tipos/longitudes de las columnas
 * definidas en src/Database/estructura.sql.
 *
 * Todas las reglas lanzan \InvalidArgumentException con mensajes claros,
 * que los controladores transforman en respuestas JSON {success:false}.
 */
final class Validator
{
    // =========================================================
    //  Longitudes máximas de columnas (coherentes con estructura.sql)
    // =========================================================
    public const MAX_CEDULA             = 20;
    public const MAX_NOMBRE             = 100;
    public const MAX_APELLIDO           = 100;
    public const MAX_DIRECCION          = 500;
    public const MAX_TELEFONO           = 20;
    public const MAX_RIF                = 20;
    public const MAX_EMAIL              = 100;
    public const MAX_CODIGO             = 50;
    public const MAX_DESCRIPCION        = 1000;
    public const MAX_DOCUMENTO          = 100;
    public const MAX_MARCA              = 100;
    public const MAX_NUMERO_ORDEN       = 50;
    public const MAX_USERNAME           = 50;
    public const MAX_PASSWORD           = 255;
    public const MAX_TIEMPO_USO         = 50;
    public const MAX_NOMBRE_ROL         = 50;
    public const MAX_DESCRIPCION_ROL    = 500;
    public const MAX_CANTIDAD           = 99999;
    public const MAX_ITEMS_VENTA        = 200;
    public const MAX_MONEY              = 99999999.99;   // DECIMAL(10,2)
    public const MAX_TERMINO_BUSQUEDA   = 100;

    // =========================================================
    //  Patrones estrictos
    // =========================================================
    /** Cédula: alfanumérico con guiones/espacios/puntos, 5-20 caracteres. */
    public const PATTERN_CEDULA   = '/^[0-9A-Za-z][0-9A-Za-z.\-\s]{3,18}[0-9A-Za-z]$/';
    /** RIF: alfanumérico con guiones/espacios/puntos, 5-20 caracteres. */
    public const PATTERN_RIF      = '/^[0-9A-Za-z][0-9A-Za-z.\-\s]{3,18}[0-9A-Za-z]$/';
    /** Username: alfanumérico con _ . - (inicia con letra o número). */
    public const PATTERN_USERNAME = '/^[A-Za-z0-9][A-Za-z0-9_.\-]{2,49}$/';
    /** Código de producto: alfanumérico con _ - . / # (máx. 50). */
    public const PATTERN_CODIGO    = '/^[A-Za-z0-9][A-Za-z0-9_.\-\/#\s]{0,49}$/';
    /** Teléfono: dígitos, +, -, paréntesis, espacio, punto. */
    public const PATTERN_TELEFONO  = '/^[0-9+\-()\s.]+$/';
    /** Tiempo de uso HH:MM:SS con horas de 1 a 3 dígitos. */
    public const PATTERN_TIEMPO    = '/^\d{1,3}:\d{2}:\d{2}$/';
    /** Fecha YYYY-MM-DD (la validez real se comprueba con checkdate). */
    public const PATTERN_FECHA     = '/^\d{4}-\d{2}-\d{2}$/';
    /** Número de orden de abastecimiento. */
    public const PATTERN_NUMERO    = '/^[A-Za-z0-9][A-Za-z0-9._\-\/\s]{0,49}$/';
    /** Número de tipo literal con letras, números, espacios y puntuación básica. */
    public const PATTERN_TEXTO_LIBRE = '/^[\p{L}\p{N}\p{M}\s.,;:()\-\/_&\'+°#@%*?!]+$/u';

    /**
     * Valle estricto de un campo de texto.
     *
     * @param mixed $value  Valor crudo recibido del cliente.
     * @param string $field Nombre legible del campo para los mensajes.
     * @param array<string,mixed> $opts {required, min, max, pattern, patternMessage}
     * @return string Valor recortado y validado.
     */
    public static function texto(mixed $value, string $field, array $opts = []): string
    {
        $required = (bool)($opts['required'] ?? false);
        $min      = (int)($opts['min'] ?? 1);
        $max      = (int)($opts['max'] ?? PHP_INT_MAX);
        $pattern  = $opts['pattern'] ?? null;
        $patternMessage = (string)($opts['patternMessage'] ?? "El campo '$field' contiene caracteres no permitidos");

        if ($value === null || is_bool($value) || is_array($value) || is_object($value)) {
            throw new \InvalidArgumentException("El campo '$field' no es válido");
        }

        $texto = trim((string)$value);

        if ($texto === '') {
            if ($required) {
                throw new \InvalidArgumentException("El campo '$field' es obligatorio");
            }
            return '';
        }

        self::rechazarControl($texto, $field);

        $len = mb_strlen($texto);
        if ($len < $min) {
            throw new \InvalidArgumentException("El campo '$field' debe tener al menos $min caracteres");
        }
        if ($len > $max) {
            throw new \InvalidArgumentException("El campo '$field' no puede exceder $max caracteres");
        }
        if ($pattern !== null && !preg_match($pattern, $texto)) {
            throw new \InvalidArgumentException($patternMessage);
        }

        return $texto;
    }

    /**
     * Coerción estricta a entero con rango.
     */
    public static function entero(mixed $value, string $field, array $opts = []): int
    {
        $required = (bool)($opts['required'] ?? true);
        $min      = (int)($opts['min'] ?? PHP_INT_MIN);
        $max      = (int)($opts['max'] ?? PHP_INT_MAX);

        if ($value === null || is_bool($value) || is_array($value) || is_object($value)) {
            if (($value === null || $value === '') && !$required) {
                return 0;
            }
            throw new \InvalidArgumentException("El campo '$field' no es un número entero válido");
        }

        if (is_float($value) && $value != floor($value)) {
            throw new \InvalidArgumentException("El campo '$field' no es un número entero válido");
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                if ($required) {
                    throw new \InvalidArgumentException("El campo '$field' es obligatorio");
                }
                return 0;
            }
            if (!preg_match('/^-?\d+$/', $value)) {
                throw new \InvalidArgumentException("El campo '$field' no es un número entero válido");
            }
            // Evita desbordamientos absurdos (ej. 999999999999999999999)
            if (strlen(ltrim($value, '-')) > 10) {
                throw new \InvalidArgumentException("El campo '$field' excede el rango permitido");
            }
        }

        $n = (int)$value;
        if ($n < $min) {
            throw new \InvalidArgumentException("El campo '$field' debe ser mayor o igual a $min");
        }
        if ($n > $max) {
            throw new \InvalidArgumentException("El campo '$field' no puede exceder $max");
        }
        return $n;
    }

    /**
     * Coerción estricta a decimal (hasta 2 decimales, DECIMAL(10,2)).
     */
    public static function decimal(mixed $value, string $field, array $opts = []): float
    {
        $required = (bool)($opts['required'] ?? false);
        $min      = (float)($opts['min'] ?? 0.0);
        $max      = (float)($opts['max'] ?? self::MAX_MONEY);

        if ($value === null || is_bool($value) || is_array($value) || is_object($value)) {
            if (($value === null || $value === '') && !$required) {
                return 0.0;
            }
            throw new \InvalidArgumentException("El campo '$field' no es un número válido");
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                if ($required) {
                    throw new \InvalidArgumentException("El campo '$field' es obligatorio");
                }
                return 0.0;
            }
            if (!preg_match('/^-?\d{1,8}(?:\.\d{1,2})?$/', $value)) {
                throw new \InvalidArgumentException("El campo '$field' debe ser un número con hasta 2 decimales");
            }
        } elseif (is_int($value)) {
            $value = sprintf('%d', $value);
        } elseif (is_float($value)) {
            $value = number_format($value, 2, '.', '');
        }

        $f = round((float)$value, 2);
        if ($f < $min) {
            throw new \InvalidArgumentException("El campo '$field' debe ser mayor o igual a $min");
        }
        if ($f > $max) {
            throw new \InvalidArgumentException("El campo '$field' no puede exceder $max");
        }
        return $f;
    }

    /**
     * Validación de fecha YYYY-MM-DD real (checkdate).
     */
    public static function fecha(mixed $value, string $field, array $opts = []): string
    {
        $required = (bool)($opts['required'] ?? false);
        $noFutura = (bool)($opts['noFutura'] ?? false);

        if ($value === null || is_bool($value) || is_array($value) || is_object($value)) {
            if (($value === null || $value === '') && !$required) {
                return '';
            }
            throw new \InvalidArgumentException("El campo '$field' no es una fecha válida");
        }

        $fecha = trim((string)$value);
        if ($fecha === '') {
            if ($required) {
                throw new \InvalidArgumentException("El campo '$field' es obligatorio");
            }
            return '';
        }

        if (
            !preg_match(self::PATTERN_FECHA, $fecha)
            || !checkdate((int)substr($fecha, 5, 2), (int)substr($fecha, 8, 2), (int)substr($fecha, 0, 4))
        ) {
            throw new \InvalidArgumentException("El campo '$field' no es una fecha válida (use YYYY-MM-DD)");
        }

        if ($noFutura && $fecha > date('Y-m-d')) {
            throw new \InvalidArgumentException("El campo '$field' no puede ser una fecha futura");
        }

        return $fecha;
    }

    /**
     * Validación contra una lista blanca de valores permitidos.
     */
    public static function enum(mixed $value, string $field, array $allowed, bool $required = true): string
    {
        $value = is_scalar($value) ? trim((string)$value) : '';
        if ($value === '') {
            if ($required) {
                throw new \InvalidArgumentException("El campo '$field' es obligatorio");
            }
            return '';
        }
        if (!in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException("El valor del campo '$field' no es válido");
        }
        return $value;
    }

    /**
     * Validación de email (FILTER_VALIDATE_EMAIL) con longitud máxima.
     */
    public static function email(mixed $value, string $field, bool $required = false): string
    {
        $value = is_scalar($value) ? trim((string)$value) : '';
        if ($value === '') {
            if ($required) {
                throw new \InvalidArgumentException("El campo '$field' es obligatorio");
            }
            return '';
        }
        if (mb_strlen($value) > self::MAX_EMAIL) {
            throw new \InvalidArgumentException("El campo '$field' no puede exceder " . self::MAX_EMAIL . ' caracteres');
        }
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("El formato del campo '$field' no es válido");
        }
        return $value;
    }

    /**
     * Booleano estricto (0/1/'0'/'1'/'true'/'false'/'on'/etc.). Devuelve 0 o 1.
     */
    public static function bool(mixed $value, string $field, bool $required = false): int
    {
        if ($value === null || $value === '') {
            if ($required) {
                throw new \InvalidArgumentException("El campo '$field' es obligatorio");
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
        return 0; // unreachable
    }

    private static function boolInvalido(string $field): int
    {
        throw new \InvalidArgumentException("El valor del campo '$field' no es válido");
    }

    /**
     * Valida un identificador entero positivo (> 0) usado como PK/FK.
     */
    public static function id(mixed $value, string $field): int
    {
        return self::entero($value, $field, ['required' => true, 'min' => 1]);
    }

    /**
     * Valida un término de búsqueda (permite parcial, sin HTML).
     */
    public static function busqueda(mixed $value, string $field): string
    {
        $texto = self::texto($value, $field, ['required' => false, 'max' => self::MAX_TERMINO_BUSQUEDA, 'pattern' => '/^[\p{L}\p{N}\s.,;:()\-\/_%#@+]+$/u', 'patternMessage' => "El campo '$field' contiene caracteres no permitidos"]);
        return $texto;
    }

    /**
     * Valida los ítems de una venta (JSON): estructura, tipos y cantidades.
     *
     * @return array<int,array{id:int,cantidad:int}> Ítems validados y ordenados.
     */
    public static function itemsVenta(mixed $json): array
    {
        if (!is_string($json) || trim($json) === '') {
            throw new \InvalidArgumentException('El carrito de la venta es obligatorio');
        }

        $items = json_decode($json, true);
        if (!is_array($items)) {
            throw new \InvalidArgumentException('El formato del carrito no es válido');
        }
        if (count($items) === 0) {
            throw new \InvalidArgumentException('El carrito está vacío');
        }
        if (count($items) > self::MAX_ITEMS_VENTA) {
            throw new \InvalidArgumentException('El carrito excede el máximo de ' . self::MAX_ITEMS_VENTA . ' ítems permitidos');
        }

        $validados = [];
        $vistos = [];
        foreach ($items as $i => $item) {
            if (!is_array($item)) {
                throw new \InvalidArgumentException("El ítem #" . ($i + 1) . ' del carrito no es válido');
            }
            $producto = self::entero($item['id'] ?? null, 'producto del ítem #' . ($i + 1), ['required' => true, 'min' => 1]);
            $cantidad = self::entero($item['cantidad'] ?? null, 'cantidad del ítem #' . ($i + 1), ['required' => true, 'min' => 1, 'max' => self::MAX_CANTIDAD]);

            if (isset($vistos[$producto])) {
                throw new \InvalidArgumentException('El producto se repite en el carrito');
            }
            $vistos[$producto] = true;

            $validados[] = ['id' => $producto, 'cantidad' => $cantidad];
        }

        return $validados;
    }

    /**
     * API conveniente para validar la cédula.
     */
    public static function cedula(mixed $value, string $field, bool $required = true): string
    {
        return self::texto($value, $field, [
            'required'       => $required,
            'min'            => 5,
            'max'            => self::MAX_CEDULA,
            'pattern'        => self::PATTERN_CEDULA,
            'patternMessage' => "El campo '$field' no tiene un formato válido",
        ]);
    }

    /**
     * API conveniente para validar el RIF.
     */
    public static function rif(mixed $value, string $field, bool $required = true): string
    {
        return self::texto($value, $field, [
            'required'       => $required,
            'min'            => 5,
            'max'            => self::MAX_RIF,
            'pattern'        => self::PATTERN_RIF,
            'patternMessage' => "El campo '$field' no tiene un formato válido",
        ]);
    }

    /**
     * API conveniente para validar un teléfono opcional.
     */
    public static function telefono(mixed $value, string $field, bool $required = false): string
    {
        return self::texto($value, $field, [
            'required'       => $required,
            'min'            => 7,
            'max'            => self::MAX_TELEFONO,
            'pattern'        => self::PATTERN_TELEFONO,
            'patternMessage' => "El campo '$field' no tiene un formato válido",
        ]);
    }

    /**
     * API conveniente para validar un username.
     */
    public static function username(mixed $value, string $field, bool $required = true): string
    {
        return self::texto($value, $field, [
            'required'       => $required,
            'min'            => 3,
            'max'            => self::MAX_USERNAME,
            'pattern'        => self::PATTERN_USERNAME,
            'patternMessage' => "El campo '$field' debe contener solo letras, números, guiones o puntos y tener al menos 3 caracteres",
        ]);
    }

    /**
     * API conveniente para validar un tiempo de uso HH:MM:SS.
     */
    public static function tiempoUso(mixed $value, string $field, bool $required = true): string
    {
        $t = self::texto($value, $field, [
            'required'       => $required,
            'min'            => 8,
            'max'            => self::MAX_TIEMPO_USO,
            'pattern'        => self::PATTERN_TIEMPO,
            'patternMessage' => "El campo '$field' debe tener formato HH:MM:SS",
        ]);
        [$h, $m, $s] = array_map('intval', explode(':', $t));
        if ($m > 59 || $s > 59) {
            throw new \InvalidArgumentException("El campo '$field' debe tener minutos y segundos entre 00 y 59");
        }
        return $t;
    }

    /**
     * API conveniente para validar el número de una orden de abastecimiento.
     */
    public static function numeroOrden(mixed $value, string $field, bool $required = true): string
    {
        return self::texto($value, $field, [
            'required'       => $required,
            'min'            => 1,
            'max'            => self::MAX_NUMERO_ORDEN,
            'pattern'        => self::PATTERN_NUMERO,
            'patternMessage' => "El campo '$field' contiene caracteres no permitidos",
        ]);
    }

    /**
     * Rechaza caracteres de control que puedan "colarse" en el texto.
     */
    private static function rechazarControl(string $texto, string $field): void
    {
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $texto)) {
            throw new \InvalidArgumentException("El campo '$field' contiene caracteres de control no permitidos");
        }
    }
}