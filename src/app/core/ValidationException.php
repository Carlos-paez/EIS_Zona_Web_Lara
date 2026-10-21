<?php

namespace App\Core;

/**
 * Excepción de validación con errores por campo.
 *
 * A diferencia de una \InvalidArgumentException normal (que solo describe
 * un error), esta excepción transporta un mapa [campo => mensaje] con TODOS
 * los errores detectados de una vez. Los controladores la capturan y la
 * transforman en una respuesta JSON {success:false, fieldErrors:{...}} para
 * que el frontend marque cada campo inválido con su propio mensaje.
 */
final class ValidationException extends \InvalidArgumentException
{
    /** @var array<string,string> Mapa campo => mensaje legible. */
    private array $errores;

    /**
     * @param array<string,string> $errores Mapa campo => mensaje.
     * @param string                $mensaje Mensaje general mostrado al usuario.
     */
    public function __construct(array $errores, string $mensaje = 'Revisa los datos ingresados e intenta de nuevo.')
    {
        parent::__construct($mensaje);
        $this->errores = $errores;
    }

    /**
     * Devuelve los errores por campo.
     *
     * @return array<string,string>
     */
    public function errores(): array
    {
        return $this->errores;
    }
}
