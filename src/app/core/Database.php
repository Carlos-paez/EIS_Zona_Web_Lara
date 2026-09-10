<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private const DB_CHARSET = 'utf8mb4';

    /**
     * Credenciales de conexión desde variables de entorno (Docker)
     * con valores por defecto para desarrollo local.
     */
    private static function config(): array
    {
        return [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'name' => getenv('DB_NAME') ?: 'zona_web_lara',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
        ];
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $cfg = self::config();

            $dns = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $cfg['host'],
                $cfg['name'],
                self::DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dns, $cfg['user'], $cfg['pass'], $options);
            } catch (PDOException $e) {
                throw new PDOException('Error de conexión a base de datos', (int)$e->getCode());
            }
        }

        return self::$instance;
    }
}
