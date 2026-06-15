<?php
// =============================================================================
// SCRIPT DE LÍNEA DE COMANDOS: Crear Usuario
// =============================================================================
// Propósito: Script CLI para crear usuarios en el sistema ZWL desde la
//            terminal. Útil para la configuración inicial del sistema.
//
// Uso:
//   php src/cli/create_user.php --username=admin --password=1234 --nombre="Admin" --email=admin@zwl.com --rol=1
//
// Opciones:
//   --username   (obligatorio) Nombre de usuario para iniciar sesión
//   --password   (obligatorio) Contraseña en texto plano
//   --nombre     (obligatorio) Nombre completo del usuario
//   --email      (obligatorio) Correo electrónico
//   --telefono   (opcional) Número de teléfono
//   --rol        (opcional) ID del rol (1=Admin, 2=Operador, 3=Asesor Legal). Por defecto: 2
//   --help       Muestra esta ayuda
// =============================================================================

require_once __DIR__ . '/../../vendor/autoload.php'; // Carga clases vía Composer

use App\Core\Database;

// Define las opciones largas que acepta el script (con : al final si requieren valor)
$longopts = [
    'username:',   // Requiere valor
    'password:',   // Requiere valor
    'nombre:',     // Requiere valor
    'email:',      // Requiere valor
    'telefono:',   // Requiere valor
    'rol:',        // Requiere valor
    'help',        // No requiere valor (bandera)
];

// Parsear los argumentos de la línea de comandos
$options = getopt('', $longopts);

// Si se pidió ayuda o faltan campos obligatorios, muestra el uso y sale
if (isset($options['help']) || empty($options['username']) || empty($options['password']) || empty($options['nombre']) || empty($options['email'])) {
    echo "Usage: php src/cli/create_user.php --username=USER --password=PASS --nombre=\"Full Name\" --email=user@example.com [--telefono=PHONE] [--rol=1|2|3]\n";
    exit(isset($options['help']) ? 0 : 1); // 0 si fue --help, 1 si fue error
}

// Extrae y asigna los valores de las opciones
$username = $options['username'];
$password = $options['password'];
$nombre   = $options['nombre'];
$email    = $options['email'];
$telefono = $options['telefono'] ?? null;    // Opcional: null si no se proporciona
$rol_id   = (int)($options['rol'] ?? 2);     // Opcional: por defecto rol 2 (Operador)

// Intenta conectar a la base de datos
try {
    $db = Database::getConnection();
} catch (Exception $e) {
    echo "Error: Could not connect to database. " . $e->getMessage() . "\n";
    exit(1);
}

// Verifica si ya existe un usuario con ese username o email (para evitar duplicados)
$check = $db->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
$check->execute([$username, $email]);
if ($check->fetch()) {
    echo "Error: A user with that username or email already exists.\n";
    exit(1);
}

// Hashea la contraseña con BCRYPT (algoritmo seguro) e inserta el nuevo usuario
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $db->prepare("INSERT INTO usuarios (username, password_hash, nombre, email, telefono, rol_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$username, $hash, $nombre, $email, $telefono, $rol_id]);

// Obtiene el ID del usuario recién creado y muestra los datos en pantalla
$userId = $db->lastInsertId();
echo "User created successfully.\n";
echo "ID:       $userId\n";
echo "Username: $username\n";
echo "Name:     $nombre\n";
echo "Email:    $email\n";
echo "Role ID:  $rol_id\n";
