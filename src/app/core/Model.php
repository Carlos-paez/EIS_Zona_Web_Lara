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
}
