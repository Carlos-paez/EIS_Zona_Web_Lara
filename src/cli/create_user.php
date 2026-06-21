<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;

$longopts = [
    'username:',
    'password:',
    'nombre:',
    'apellido:',
    'email:',
    'help',
];

$options = getopt('', $longopts);

if (isset($options['help']) || empty($options['username']) || empty($options['password']) || empty($options['nombre']) || empty($options['email'])) {
    echo "Usage: php src/cli/create_user.php --username=USER --password=PASS --nombre=\"First Name\" --apellido=\"Last Name\" --email=user@example.com\n";
    exit(isset($options['help']) ? 0 : 1);
}

$username = $options['username'];
$password = $options['password'];
$nombre   = $options['nombre'];
$apellido = $options['apellido'] ?? '';
$email    = $options['email'];

try {
    $db = Database::getConnection();
} catch (Exception $e) {
    echo "Error: Could not connect to database. " . $e->getMessage() . "\n";
    exit(1);
}

$check = $db->prepare("SELECT id FROM usuarios WHERE user_name = ? OR email = ?");
$check->execute([$username, $email]);
if ($check->fetch()) {
    echo "Error: A user with that username or email already exists.\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $db->prepare("INSERT INTO usuarios (user_name, password_hash, nombre, apellido, email) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$username, $hash, $nombre, $apellido, $email]);

$userId = $db->lastInsertId();
echo "User created successfully.\n";
echo "ID:       $userId\n";
echo "Username: $username\n";
echo "Name:     $nombre $apellido\n";
echo "Email:    $email\n";
