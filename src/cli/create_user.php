<?php
/**
 * CLI script to create a new user in the ZWL system.
 *
 * Usage:
 *   php src/cli/create_user.php --username=admin --password=1234 --nombre="Admin" --email=admin@zwl.com --rol=1
 *
 * Options:
 *   --username   (required) Login username
 *   --password   (required) Plain text password
 *   --nombre     (required) Full name
 *   --email      (required) Email address
 *   --telefono   (optional) Phone number
 *   --rol        (optional) Role ID (1=Admin, 2=Operator, 3=Legal Advisor). Default: 2
 *   --help       Show this help
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;

$longopts = [
    'username:',
    'password:',
    'nombre:',
    'email:',
    'telefono:',
    'rol:',
    'help',
];

$options = getopt('', $longopts);

if (isset($options['help']) || empty($options['username']) || empty($options['password']) || empty($options['nombre']) || empty($options['email'])) {
    echo "Usage: php src/cli/create_user.php --username=USER --password=PASS --nombre=\"Full Name\" --email=user@example.com [--telefono=PHONE] [--rol=1|2|3]\n";
    exit(isset($options['help']) ? 0 : 1);
}

$username = $options['username'];
$password = $options['password'];
$nombre   = $options['nombre'];
$email    = $options['email'];
$telefono = $options['telefono'] ?? null;
$rol_id   = (int)($options['rol'] ?? 2);

try {
    $db = Database::getConnection();
} catch (Exception $e) {
    echo "Error: Could not connect to database. " . $e->getMessage() . "\n";
    exit(1);
}

$check = $db->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
$check->execute([$username, $email]);
if ($check->fetch()) {
    echo "Error: A user with that username or email already exists.\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $db->prepare("INSERT INTO usuarios (username, password_hash, nombre, email, telefono, rol_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$username, $hash, $nombre, $email, $telefono, $rol_id]);

$userId = $db->lastInsertId();
echo "User created successfully.\n";
echo "ID:       $userId\n";
echo "Username: $username\n";
echo "Name:     $nombre\n";
echo "Email:    $email\n";
echo "Role ID:  $rol_id\n";
