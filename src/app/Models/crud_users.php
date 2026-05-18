<?php
// ============================================================
// MODELO: CRUD DE USUARIOS
// ============================================================
// Este archivo contiene funciones para operaciones CRUD (Crear,
// Leer, Actualizar, Eliminar) sobre la tabla 'usuarios' de la
// base de datos. Todas las funciones reciben la conexión PDO
// como primer parámetro ($pdo) para mantener la flexibilidad.
//
// Convenciones usadas:
//   - Consultas preparadas con ? (placeholders anónimos) para
//     prevenir inyecciones SQL.
//   - Las contraseñas se almacenan usando password_hash() con
//     algoritmo BCRYPT.
//   - Las funciones devuelven arrays asociativos (FETCH_ASSOC)
//     o booleanos según la operación.
//
// NOTA: Actualmente ningún controlador o vista incluye este
// archivo. La autenticación se hace con credenciales hardcodeadas
// en LoginController. Este modelo está listo para cuando se
// implemente la autenticación contra base de datos.

// Incluir la configuración de la base de datos.
// __DIR__.'/../../Config/database.php' establece la conexión PDO
// y la guarda en la variable $pdo.
// require_once evita que se incluya múltiples veces.
require_once __DIR__.'/../../Config/database.php';

// ============================================
// FUNCIÓN: CREAR USUARIO
// ============================================
// Inserta un nuevo usuario en la base de datos con contraseña
// hasheada usando BCRYPT.
//
// Parámetros:
//   $pdo       — Objeto de conexión PDO
//   $username  — Nombre de usuario único
//   $password  — Contraseña en texto plano (se hashea internamente)
//   $nombre    — Nombre completo del usuario
//   $email     — Correo electrónico
//   $telefono  — Número de teléfono (opcional, null por defecto)
//   $rol_id    — ID del rol (2 = Vendedor por defecto)
//
// Retorna:
//   true si la inserción fue exitosa, false en caso contrario
function crearUsuario($pdo, $username, $password, $nombre, $email, $telefono = null, $rol_id = 2) {
    // Generar un hash BCRYPT de la contraseña.
    // password_hash() genera un hash seguro con salt automático.
    // PASSWORD_BCRYPT usa el algoritmo bcrypt (60 caracteres).
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Consulta SQL de inserción con placeholders (?).
    // Los VALUES usan ? para cada campo, que luego se reemplazan
    // con los valores reales mediante execute().
    $sql = "INSERT INTO usuarios (username, password_hash, nombre, email, telefono, rol_id) VALUES (?, ?, ?, ?, ?, ?)";

    // Preparar la consulta. prepare() devuelve un objeto PDOStatement.
    // La consulta se analiza y compila en el motor de MySQL.
    $stmt = $pdo->prepare($sql);

    // Ejecutar la consulta con los valores reales.
    // execute() reemplaza cada ? con el valor correspondiente
    // en el mismo orden. Los valores se escapan automáticamente
    // contra inyecciones SQL.
    return $stmt->execute([$username, $hash, $nombre, $email, $telefono, $rol_id]);
}

// ============================================
// FUNCIÓN: OBTENER TODOS LOS USUARIOS
// ============================================
// Retorna un array con todos los usuarios activos, incluyendo
// el nombre del rol mediante un JOIN con la tabla 'roles'.
//
// Retorna:
//   Array de arrays asociativos con datos del usuario + rol
function obtenerUsuarios($pdo) {
    // Consulta SELECT con JOIN para traer el nombre del rol.
    // ORDER BY u.nombre ordena alfabéticamente por nombre.
    $stmt = $pdo->query("SELECT u.id, u.username, u.nombre, u.email, u.telefono, u.activo, u.ultimo_acceso, u.created_at, r.nombre AS rol FROM usuarios u INNER JOIN roles r ON u.rol_id = r.id ORDER BY u.nombre");

    // fetchAll() devuelve todas las filas como un array.
    // Como el fetch mode está configurado a FETCH_ASSOC,
    // cada fila es un array asociativo (ej: ['id' => 1, 'username' => 'admin', ...]).
    return $stmt->fetchAll();
}

// ============================================
// FUNCIÓN: OBTENER USUARIO POR ID
// ============================================
// Busca un usuario específico por su ID.
//
// Parámetros:
//   $pdo — Conexión PDO
//   $id  — ID del usuario a buscar
//
// Retorna:
//   Array asociativo con los datos del usuario, o false si no existe
function obtenerUsuarioPorId($pdo, $id) {
    // Consulta preparada con placeholder ?
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    // Ejecutar con el valor de $id
    $stmt->execute([$id]);
    // fetch() devuelve una sola fila (o false si no hay resultados)
    return $stmt->fetch();
}

// ============================================
// FUNCIÓN: OBTENER USUARIO POR USERNAME
// ============================================
// Busca un usuario por su nombre de usuario. Filtra solo
// usuarios activos (activo = TRUE).
//
// Parámetros:
//   $pdo      — Conexión PDO
//   $username — Nombre de usuario a buscar
//
// Retorna:
//   Array asociativo con los datos del usuario, o false si no existe
function obtenerUsuarioPorUsername($pdo, $username) {
    // Consulta con dos condiciones: username exacto y activo = TRUE
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? AND activo = TRUE");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

// ============================================
// FUNCIÓN: AUTENTICAR USUARIO
// ============================================
// Verifica las credenciales de un usuario. Si son correctas,
// actualiza el campo ultimo_acceso con la fecha/hora actual.
//
// Parámetros:
//   $pdo      — Conexión PDO
//   $username — Nombre de usuario
//   $password — Contraseña en texto plano (se verifica contra el hash)
//
// Retorna:
//   Array con datos del usuario si la autenticación es exitosa,
//   false si las credenciales son incorrectas
function autenticarUsuario($pdo, $username, $password) {
    // Obtener el usuario por username (solo activos)
    $usuario = obtenerUsuarioPorUsername($pdo, $username);

    // Verificar que el usuario existe y la contraseña coincide.
    // password_verify() compara la contraseña en texto plano con
    // el hash almacenado. No necesita re-hashear, usa el algoritmo
    // que está incrustado en el propio hash.
    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        // Actualizar la fecha del último acceso.
        // NOW() es una función de MySQL que devuelve la fecha/hora actual.
        $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
        $stmt->execute([$usuario['id']]);

        // Devolver los datos del usuario autenticado
        return $usuario;
    }

    // Credenciales inválidas
    return false;
}

// ============================================
// FUNCIÓN: ACTUALIZAR USUARIO
// ============================================
// Actualiza los datos de un usuario existente. Usa COALESCE
// para mantener el valor actual del rol si no se proporciona
// uno nuevo.
//
// Parámetros:
//   $pdo      — Conexión PDO
//   $id       — ID del usuario a actualizar
//   $nombre   — Nuevo nombre completo
//   $email    — Nuevo correo electrónico
//   $telefono — Nuevo teléfono (opcional)
//   $rol_id   — Nuevo ID de rol (opcional, null = mantener actual)
//   $activo   — Estado del usuario (true = activo, false = inactivo)
//
// Retorna:
//   true si la actualización fue exitosa
function actualizarUsuario($pdo, $id, $nombre, $email, $telefono = null, $rol_id = null, $activo = true) {
    // UPDATE con COALESCE: si rol_id es null, se mantiene el valor
    // actual de la columna rol_id (COALESCE devuelve el primer valor
    // no-nulo: ? si no es null, rol_id si es null).
    $sql = "UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, rol_id = COALESCE(?, rol_id), activo = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nombre, $email, $telefono, $rol_id, $activo, $id]);
}

// ============================================
// FUNCIÓN: ACTUALIZAR CONTRASEÑA
// ============================================
// Cambia la contraseña de un usuario. La nueva contraseña
// se hashea con BCRYPT antes de almacenarse.
//
// Parámetros:
//   $pdo      — Conexión PDO
//   $id       — ID del usuario
//   $password — Nueva contraseña en texto plano
//
// Retorna:
//   true si la actualización fue exitosa
function actualizarPassword($pdo, $id, $password) {
    // Generar hash BCRYPT de la nueva contraseña
    $hash = password_hash($password, PASSWORD_BCRYPT);
    // Actualizar solo el campo password_hash
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
    return $stmt->execute([$hash, $id]);
}

// ============================================
// FUNCIÓN: ELIMINAR USUARIO
// ============================================
// Elimina físicamente un usuario de la base de datos.
// (En producción podría ser mejor un borrado lógico:
// establecer activo = false en lugar de DELETE).
//
// Parámetros:
//   $pdo — Conexión PDO
//   $id  — ID del usuario a eliminar
//
// Retorna:
//   true si la eliminación fue exitosa
function eliminarUsuario($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    return $stmt->execute([$id]);
}
