<?php
/**
 * Script CLI para crear un nuevo usuario en la base de datos.
 * 
 * Uso desde la terminal:
 *   php src/cli/create_user.php --username=USER --password=PASS --nombre="First Name" --apellido="Last Name" --email=user@example.com
 * 
 * Este script se ejecuta desde la línea de comandos (no vía web) y permite
 * crear usuarios directamente en la tabla 'usuarios' con contraseña
 * encriptada usando Bcrypt.
 */

// Carga el autoloader de Composer para tener disponibles todas las clases del proyecto
require_once __DIR__ . '/../../vendor/autoload.php';

// Importa la clase Database del namespace App\Core para obtener la conexión PDO
use App\Core\Database;

// Define las opciones largas que aceptará el script (formato --opcion=valor)
$longopts = [
    'username:',    // Nombre de usuario (requiere valor)
    'password:',    // Contraseña (requiere valor)
    'nombre:',      // Nombre real (requiere valor)
    'apellido:',    // Apellido (requiere valor)
    'email:',       // Correo electrónico (requiere valor)
    'help',         // Muestra ayuda (no requiere valor)
];

// Obtiene las opciones pasadas por línea de comandos usando getopt()
$options = getopt('', $longopts);

// Verifica si se solicitó ayuda o si faltan campos obligatorios (username, password, nombre, email)
if (isset($options['help']) || empty($options['username']) || empty($options['password']) || empty($options['nombre']) || empty($options['email'])) {
    // Muestra las instrucciones de uso del script
    echo "Usage: php src/cli/create_user.php --username=USER --password=PASS --nombre=\"First Name\" --apellido=\"Last Name\" --email=user@example.com\n";
    // Sale con código 0 si fue ayuda, o 1 si faltaron parámetros (indicando error)
    exit(isset($options['help']) ? 0 : 1);
}

// Asigna el valor del username desde las opciones recibidas
$username = $options['username'];
// Asigna el valor del password desde las opciones recibidas
$password = $options['password'];
// Asigna el valor del nombre desde las opciones recibidas
$nombre   = $options['nombre'];
// Asigna el valor del apellido desde las opciones, o cadena vacía si no se proporcionó
$apellido = $options['apellido'] ?? '';
// Asigna el valor del email desde las opciones recibidas
$email    = $options['email'];

try {
    // Intenta obtener la conexión a la base de datos mediante el Singleton
    $db = Database::getConnection();
} catch (Exception $e) {
    // Si falla la conexión, muestra un mensaje de error descriptivo
    echo "Error: Could not connect to database. " . $e->getMessage() . "\n";
    // Sale del script con código 1 indicando error
    exit(1);
}

// Prepara una consulta SQL para verificar si ya existe un usuario con el mismo username o email
$check = $db->prepare("SELECT id FROM usuarios WHERE user_name = ? OR email = ?");
// Ejecuta la consulta pasando el username y email como parámetros (previene inyección SQL)
$check->execute([$username, $email]);
// Si la consulta devuelve algún resultado, significa que ya existe un usuario duplicado
if ($check->fetch()) {
    // Muestra mensaje de error indicando que el usuario o email ya están registrados
    echo "Error: A user with that username or email already exists.\n";
    // Sale del script con código 1 indicando error
    exit(1);
}

// Genera un hash seguro de la contraseña usando el algoritmo Bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);
// Prepara la consulta SQL para insertar el nuevo usuario en la tabla 'usuarios'
$stmt = $db->prepare("INSERT INTO usuarios (user_name, password_hash, nombre, apellido, email) VALUES (?, ?, ?, ?, ?)");
// Ejecuta la inserción pasando todos los valores como parámetros (seguro contra inyección SQL)
$stmt->execute([$username, $hash, $nombre, $apellido, $email]);

// Obtiene el ID autoincremental asignado al nuevo registro
$userId = $db->lastInsertId();
// Muestra mensaje de éxito en la creación del usuario
echo "User created successfully.\n";
// Muestra el ID del nuevo usuario en la base de datos
echo "ID:       $userId\n";
// Muestra el nombre de usuario creado
echo "Username: $username\n";
// Muestra el nombre y apellido completos del usuario
echo "Name:     $nombre $apellido\n";
// Muestra el correo electrónico del usuario
echo "Email:    $email\n";
