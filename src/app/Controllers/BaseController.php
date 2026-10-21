<?php

namespace App\Controllers;

use App\Core\ValidationException;
use App\Core\Validator;

abstract class BaseController
{
    /**
     * Datos hidratados desde request
     */
    protected array $input = [];

    /**
     * Errores de validación
     */
    protected array $errors = [];

    /**
     * Resultado de la operación
     */
    protected array $result = [];

    /**
     * Obtener valor del input hidratado
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->input) ? $this->input[$key] : $default;
    }

    /**
     * Asignar valor al input (setter)
     */
    public function set(string $key, mixed $value): self
    {
        $this->input[$key] = $value;
        return $this;
    }

    /**
     * Verifica si existe una clave en input
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->input);
    }

    /**
     * Obtener todos los datos hidratados
     */
    public function getAll(): array
    {
        return $this->input;
    }

    /**
     * Limpiar input
     */
    public function clearInput(): self
    {
        $this->input = [];
        return $this;
    }

    /**
     * Agregar error de validación
     */
    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    /**
     * Obtener errores
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Tiene errores?
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Limpiar errores
     */
    public function clearErrors(): self
    {
        $this->errors = [];
        return $this;
    }

    /**
     * Setear resultado
     */
    public function setResult(array $result): self
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Obtener resultado
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * Respuesta JSON de éxito
     */
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

    /**
     * Respuesta JSON de error
     */
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

    /**
     * Respuesta JSON con errores de validación
     */
    public function jsonValidationErrors(array $errors = null): void
    {
        http_response_code(422);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error'   => 'Errores de validación',
            'errors'  => $errors ?? $this->getErrors()
        ]);
        exit;
    }

    /**
     * Valida un esquema de campos y devuelve los datos normalizados.
     *
     * Si algún campo no cumple su regla lanza ValidationException, que
     * transporta el mapa [campo => mensaje] con TODOS los errores a la vez.
     *
     * @param array<string,array<string,mixed>> $reglas Esquema de validación.
     * @param array<string,mixed>|null          $origen Datos a validar (por defecto $_POST).
     * @return array<string,mixed>
     * @throws \App\Core\ValidationException
     */
    protected function validarCampos(array $reglas, ?array $origen = null): array
    {
        return Validator::validar($origen ?? $_POST, $reglas);
    }

    /**
     * Responde una ValidationException como HTTP 422 con fieldErrors a nivel
     * raíz, formato que el frontend (EIS.mostrarErroresFormulario) interpreta
     * para marcar cada campo inválido con su propio mensaje.
     */
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

    /**
     * Hidratar desde POST (usando Validator para seguridad)
     */
    public function hydrateFromPost(array $fields): self
    {
        foreach ($fields as $key => $type) {
            $value = $_POST[$key] ?? null;

            switch ($type) {
                case 'int':
                case 'integer':
                    $this->set($key, Validator::entero($value, $key, ['required' => false]));
                    break;
                case 'float':
                case 'decimal':
                case 'numeric':
                    $this->set($key, Validator::decimal($value, $key, ['required' => false]));
                    break;
                case 'bool':
                case 'boolean':
                    $this->set($key, Validator::bool($value, $key, false));
                    break;
                case 'date':
                case 'fecha':
                    $this->set($key, Validator::fecha($value, $key, ['required' => false]));
                    break;
                case 'email':
                    $this->set($key, Validator::email($value, $key, false));
                    break;
                case 'string':
                case 'texto':
                default:
                    $this->set($key, Validator::texto($value ?? '', $key, ['required' => false]));
                    break;
            }
        }
        return $this;
    }

    /**
     * Hidratar desde GET
     */
    public function hydrateFromGet(array $fields): self
    {
        foreach ($fields as $key => $type) {
            $value = $_GET[$key] ?? null;

            switch ($type) {
                case 'int':
                case 'integer':
                    $this->set($key, Validator::entero($value, $key, ['required' => false]));
                    break;
                case 'float':
                case 'decimal':
                case 'numeric':
                    $this->set($key, Validator::decimal($value, $key, ['required' => false]));
                    break;
                case 'bool':
                case 'boolean':
                    $this->set($key, Validator::bool($value, $key, false));
                    break;
                case 'date':
                case 'fecha':
                    $this->set($key, Validator::fecha($value, $key, ['required' => false]));
                    break;
                case 'email':
                    $this->set($key, Validator::email($value, $key, false));
                    break;
                case 'string':
                case 'texto':
                default:
                    $this->set($key, Validator::texto($value ?? '', $key, ['required' => false]));
                    break;
            }
        }
        return $this;
    }
}
