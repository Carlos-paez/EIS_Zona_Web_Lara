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

    protected function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function sanitizeInt(int $input): int
    {
        return filter_var($input, FILTER_VALIDATE_INT) !== false ? $input : 0;
    }

    protected function sanitizeFloat(float $input): float
    {
        return filter_var($input, FILTER_VALIDATE_FLOAT) !== false ? $input : 0.0;
    }

    protected function validateLength(string $value, string $field, int $max): void
    {
        if (mb_strlen($value) > $max) {
            throw new \InvalidArgumentException("El campo '$field' no puede exceder $max caracteres");
        }
    }

    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateRequired(array $fields, array $data): void
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                throw new \InvalidArgumentException("El campo '$field' es obligatorio");
            }
        }
    }
}
