<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getDb(): PDO
    {
        return $this->db;
    }

    public function setDb(PDO $db): void
    {
        $this->db = $db;
    }

    protected function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function sanitizeInt(mixed $input): int
    {
        $filtered = filter_var($input, FILTER_VALIDATE_INT);
        return $filtered !== false ? $filtered : 0;
    }

    protected function sanitizeFloat(mixed $input): float
    {
        $filtered = filter_var($input, FILTER_VALIDATE_FLOAT);
        return $filtered !== false ? $filtered : 0.0;
    }

    protected function validateNotEmpty(string $value, string $field): void
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException("El campo '$field' es obligatorio");
        }
    }

    protected function validateMinLength(string $value, string $field, int $min): void
    {
        if (mb_strlen(trim($value)) < $min) {
            throw new \InvalidArgumentException("El campo '$field' debe tener al menos $min caracteres");
        }
    }

    protected function validateLength(string $value, string $field, int $max): void
    {
        if (mb_strlen($value) > $max) {
            throw new \InvalidArgumentException("El campo '$field' no puede exceder $max caracteres");
        }
    }

    protected function validatePattern(string $value, string $pattern, string $message): void
    {
        if (!preg_match($pattern, $value)) {
            throw new \InvalidArgumentException($message);
        }
    }

    protected function validatePositive(float $value, string $field): void
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException("El campo '$field' debe ser mayor a 0");
        }
    }

    protected function validateGreaterOrEqual(float $value, string $field, float $min): void
    {
        if ($value < $min) {
            throw new \InvalidArgumentException("El campo '$field' debe ser mayor o igual a $min");
        }
    }

    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateMax(float $value, string $field, float $max): void
    {
        if ($value > $max) {
            throw new \InvalidArgumentException("El campo '$field' no puede exceder $max");
        }
    }

    protected function validateRango(float $value, string $field, float $min, float $max): void
    {
        if ($value < $min || $value > $max) {
            throw new \InvalidArgumentException("El campo '$field' debe estar entre $min y $max");
        }
    }

    protected function validateEnLista(string $value, string $field, array $allowed): void
    {
        if (!in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException("El valor del campo '$field' no es válido");
        }
    }

    protected function validateFecha(string $fecha, string $field): void
    {
        $fecha = trim($fecha);
        if ($fecha === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException("El campo '$field' no es una fecha válida (use YYYY-MM-DD)");
        }
        if (!checkdate((int)substr($fecha, 5, 2), (int)substr($fecha, 8, 2), (int)substr($fecha, 0, 4))) {
            throw new \InvalidArgumentException("El campo '$field' no es una fecha válida (use YYYY-MM-DD)");
        }
    }

    protected function validateTelefono(string $telefono, string $field): void
    {
        if ($telefono !== '' && !preg_match('/^[0-9+\-()\s.]+$/', $telefono)) {
            throw new \InvalidArgumentException("El campo '$field' no tiene un formato válido");
        }
    }

    protected function validarLibre(string $texto, string $campo, int $min = 1, int $max = 100): void
    {
        if ($texto === '') {
            return;
        }
        if (mb_strlen($texto) < $min || mb_strlen($texto) > $max) {
            throw new \InvalidArgumentException("El campo '$campo' debe tener entre $min y $max caracteres");
        }
        if (!preg_match(Validator::PATTERN_TEXTO_LIBRE, $texto)) {
            throw new \InvalidArgumentException("El campo '$campo' contiene caracteres no permitidos");
        }
    }

    protected function validarSinControl(string $texto, string $campo): void
    {
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $texto)) {
            throw new \InvalidArgumentException("El campo '$campo' contiene caracteres de control no permitidos");
        }
    }

    protected function existeEnTabla(string $tabla, int $id): bool
    {
        if (!preg_match('/^[a-z_]+$/', $tabla)) {
            throw new \InvalidArgumentException('Nombre de tabla no válido');
        }
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM `$tabla` WHERE id = ?");
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetch()['total'] > 0;
    }
}
