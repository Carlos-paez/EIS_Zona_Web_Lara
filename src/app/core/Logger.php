<?php

namespace App\Core;

use Throwable;

/**
 * Registrador de errores en formato Markdown.
 *
 * Los errores capturados en la aplicación se escriben de forma legible
 * en src/logs/errores.md para poder diagnosticar fallos sin exponer
 * detalles técnicos al usuario final.
 */
class Logger
{
    private const LOG_DIR  = __DIR__ . '/../../logs';
    private const LOG_FILE = self::LOG_DIR . '/errores.md';

    /**
     * Registra una excepción/error técnico en el log.
     *
     * @param Throwable|string $error  Excepción o mensaje de error.
     * @param string           $context Breve descripción de dónde ocurrió.
     */
    public static function error(Throwable|string $error, string $context = ''): void
    {
        if ($error instanceof Throwable) {
            self::write('ERROR', $error->getMessage(), $context, $error);
        } else {
            self::write('ERROR', $error, $context);
        }
    }

    /**
     * Registra un mensaje informativo en el log.
     */
    public static function info(string $message, string $context = ''): void
    {
        self::write('INFO', $message, $context);
    }

    /**
     * Registra una advertencia en el log.
     */
    public static function warning(string $message, string $context = ''): void
    {
        self::write('WARNING', $message, $context);
    }

    /**
     * Escribe una entrada Markdown en el archivo de errores.
     */
    private static function write(string $level, string $message, string $context = '', ?Throwable $exception = null): void
    {
        if (!is_dir(self::LOG_DIR)) {
            @mkdir(self::LOG_DIR, 0775, true);
        }

        $message = trim($message) !== '' ? $message : '(sin mensaje)';

        $entry  = "\n## [" . date('Y-m-d H:i:s') . "] $level\n\n";

        if ($context !== '') {
            $entry .= "- **Contexto:** " . self::esc($context) . "\n";
        }

        $entry .= "- **Mensaje:** " . self::esc($message) . "\n";

        if ($exception !== null) {
            $entry .= "- **Tipo:** `" . get_class($exception) . "`\n";
            $entry .= "- **Archivo:** `" . self::esc($exception->getFile()) . ':' . $exception->getLine() . "`\n";
            $trace  = $exception->getTraceAsString();
            $trace  = self::indentTrace($trace);
            $entry .= "- **Traza:**\n\n```text\n" . $trace . "\n```\n";
        }

        $entry .= "\n---\n";

        @file_put_contents(self::LOG_FILE, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Escapa caracteres que podrían romper el Markdown.
     */
    private static function esc(string $text): string
    {
        return str_replace(['|', "\r", "\n"], ['\\|', ' ', ' '], $text);
    }

    /**
     * Da formato a la traza para que se vea indentada dentro del bloque de código.
     */
    private static function indentTrace(string $trace): string
    {
        return preg_replace('/^#/m', '    #', $trace) ?? $trace;
    }
}