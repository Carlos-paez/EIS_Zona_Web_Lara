<?php
// ============================================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// ============================================================
// Este archivo establece la conexión a la base de datos MySQL
// usando PDO (PHP Data Objects). PDO es una capa de abstracción
// que permite trabajar con diferentes bases de datos de forma
// segura, utilizando consultas preparadas para evitar
// inyecciones SQL.
//
// NOTA: Este archivo es incluido por los Models cuando necesitan
// acceder a la base de datos. Actualmente la aplicación es un
// prototipo UI y los Models no son invocados desde las vistas.

// ============================================
// 1. PARÁMETROS DE CONEXIÓN
// ============================================

// Dirección del servidor de base de datos (localhost = misma máquina)
$host = "localhost";

// Nombre de la base de datos a la que nos conectaremos
// "zwl" = "Zona Web Lara"
$db = "zwl";

// Usuario de MySQL con permisos de acceso a la base de datos
$user = "root";

// Contraseña del usuario de MySQL
// Vacía en entornos de desarrollo local (XAMPP/WAMP por defecto)
$pass = "";

// Juego de caracteres UTF-8 Multibyte
// utf8mb4 soporta caracteres especiales (tildes, ñ, emojis, etc.)
// a diferencia de utf8 que solo soporta un subconjunto.
$charset = 'utf8mb4';

// ============================================
// 2. CADENA DE CONEXIÓN (DSN - Data Source Name)
// ============================================
// Construye la cadena DSN que PDO necesita para conectarse.
// Formato: "mysql:host=HOST;dbname=DATABASE;charset=CHARSET"
$dns = "mysql:host=$host;dbname=$db;charset=$charset";

// ============================================
// 3. OPCIONES DE PDO
// ============================================
// Array asociativo con opciones de configuración de la conexión PDO.
// Estas opciones mejoran la seguridad y el manejo de errores.
$options = [
    // PDO::ATTR_ERRMODE: modo de reporte de errores
    // PDO::ERRMODE_EXCEPTION: lanza una excepción (PDOException)
    // cuando ocurre un error SQL. Esto permite capturar errores
    // con bloques try/catch en lugar de revisar manualmente.
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

    // PDO::ATTR_DEFAULT_FETCH_MODE: modo de obtención de resultados
    // PDO::FETCH_ASSOC: devuelve cada fila como un array asociativo
    // donde las claves son los nombres de las columnas.
    // Ejemplo: ['id' => 1, 'nombre' => 'Admin']
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // PDO::ATTR_EMULATE_PREPARES: emulación de consultas preparadas
    // false: usa consultas preparadas REALES del motor MySQL
    // Esto es más seguro porque la consulta y los datos viajan
    // por separado, evitando inyecciones SQL.
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// ============================================
// 4. ESTABLECER LA CONEXIÓN
// ============================================
try {
    // Intentar crear la conexión PDO.
    // new PDO(DSN, usuario, contraseña, opciones)
    // Si falla, lanza una excepción PDOException.
    $pdo = new PDO($dns, $user, $pass, $options);
    // Mensaje de depuración para verificar que la conexión funciona.
    // En producción debería eliminarse o reemplazarse por logging.
    echo "Conexión exitosa";

} catch (\PDOException $e) {
    // Capturar y relanzar cualquier error de conexión.
    // getMessage(): descripción del error
    // getCode(): código numérico del error
    // El prefijo \ indica que la clase está en el namespace global.
    throw new \PDOException($e->getMessage(), (int)$e->getCode());

}
