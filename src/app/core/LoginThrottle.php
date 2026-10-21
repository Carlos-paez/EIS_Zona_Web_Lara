<?php

namespace App\Core;

/**
 * Limitador de intentos de inicio de sesión (protección contra fuerza bruta).
 *
 * Lleva la cuenta de los intentos fallidos por combinación de usuario e IP
 * en un archivo JSON dentro de src/logs, de forma que descartar la cookie de
 * sesión no reinicia el conteo. Tras superar el máximo de intentos dentro de
 * la ventana de tiempo, la combinación queda bloqueada temporalmente.
 *
 * Se registra tanto en la sesión como en el archivo compartido: la sesión
 * da una respuesta inmediata al usuario y el archivo evita que un atacante
 * reinicie el conteo simplemente borrando sus cookies.
 */
final class LoginThrottle
{
    /** Intentos fallidos permitidos antes de bloquear. */
    public const MAX_INTENTOS = 5;
    /** Ventana de tiempo (segundos) en la que se agrupan los intentos. */
    public const VENTANA_SEGUNDOS = 900; // 15 minutos
    /** Duración del bloqueo (segundos) una vez superado el máximo. */
    public const BLOQUEO_SEGUNDOS = 300; // 5 minutos

    /** Archivo compartido donde se persisten los intentos. */
    private const ARCHIVO = __DIR__ . '/../../logs/login_intentos.json';

    /** Clave de sesión para el bloqueo. */
    private const SESION_CLAVE = 'login_bloqueado';

    /** Huella usuario|IP que identifica la combinación vigilada. */
    private string $clave;

    public function __construct(string $usuario)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
        $this->clave = hash('sha256', mb_strtolower(trim($usuario)) . '|' . $ip);
    }

    /**
     * Indica si la combinación usuario/IP está bloqueada.
     */
    public function bloqueado(): bool
    {
        return $this->segundosBloqueo() > 0;
    }

    /**
     * Segundos que faltan para poder intentar de nuevo (0 si no está bloqueado).
     */
    public function segundosBloqueo(): int
    {
        // Bloqueo inmediato guardado en la sesión actual.
        $hastaSesion = (int)($_SESSION[self::SESION_CLAVE] ?? 0);
        if ($hastaSesion > time()) {
            return $hastaSesion - time();
        }

        $registro = $this->leer()[$this->clave] ?? null;
        if ($registro === null) {
            return 0;
        }

        $hasta = (int)($registro['bloqueado_hasta'] ?? 0);
        return max(0, $hasta - time());
    }

    /**
     * Registra un intento fallido y bloquea la combinación al superar el máximo.
     */
    public function registrarFallo(): void
    {
        $ahora = time();
        $datos = $this->podar($this->leer());
        $registro = $datos[$this->clave]
            ?? ['intentos' => 0, 'primer_intento' => $ahora, 'bloqueado_hasta' => 0];

        // Si la ventana de conteo ya expiró, se reinicia.
        if ($ahora - (int)($registro['primer_intento'] ?? 0) > self::VENTANA_SEGUNDOS) {
            $registro = ['intentos' => 0, 'primer_intento' => $ahora, 'bloqueado_hasta' => 0];
        }

        $registro['intentos'] = (int)($registro['intentos'] ?? 0) + 1;
        if ($registro['intentos'] >= self::MAX_INTENTOS) {
            $registro['bloqueado_hasta'] = $ahora + self::BLOQUEO_SEGUNDOS;
            $registro['intentos']        = 0;
            $registro['primer_intento']  = $ahora;
        }

        $datos[$this->clave] = $registro;
        $this->escribir($datos);

        if (($registro['bloqueado_hasta'] ?? 0) > $ahora) {
            $_SESSION[self::SESION_CLAVE] = (int)$registro['bloqueado_hasta'];
        }
    }

    /**
     * Limpia el conteo tras un inicio de sesión correcto.
     */
    public function limpiar(): void
    {
        unset($_SESSION[self::SESION_CLAVE]);

        $datos = $this->leer();
        if (isset($datos[$this->clave])) {
            unset($datos[$this->clave]);
            $this->escribir($datos);
        }
    }

    /**
     * Lee los intentos persistidos.
     *
     * @return array<string,array{intentos:int,primer_intento:int,bloqueado_hasta:int}>
     */
    private function leer(): array
    {
        if (!is_file(self::ARCHIVO)) {
            return [];
        }
        $contenido = @file_get_contents(self::ARCHIVO);
        $datos     = $contenido !== false ? json_decode($contenido, true) : null;
        return is_array($datos) ? $datos : [];
    }

    /**
     * Persiste los intentos en el archivo compartido.
     *
     * @param array<string,array<string,int>> $datos
     */
    private function escribir(array $datos): void
    {
        $dir = dirname(self::ARCHIVO);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents(
            self::ARCHIVO,
            json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    /**
     * Elimina los registros ya expirados para que el archivo no crezca.
     *
     * @param array<string,array<string,mixed>> $datos
     * @return array<string,array<string,mixed>>
     */
    private function podar(array $datos): array
    {
        $ahora = time();
        foreach ($datos as $clave => $registro) {
            $bloqueadoHasta = (int)($registro['bloqueado_hasta'] ?? 0);
            $primerIntento  = (int)($registro['primer_intento'] ?? 0);
            if ($bloqueadoHasta < $ahora && ($ahora - $primerIntento) > self::VENTANA_SEGUNDOS) {
                unset($datos[$clave]);
            }
        }
        return $datos;
    }
}
