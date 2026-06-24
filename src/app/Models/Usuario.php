<?php
// Namespace que organiza este modelo dentro de la carpeta App\Models
namespace App\Models;

// Se importa la clase Model del núcleo de la aplicación
use App\Core\Model;

/**
 * Clase Usuario que extiende de Model.
 * Proporciona métodos CRUD y de autenticación para la tabla "usuarios".
 */
class Usuario extends Model
{
    /**
     * Crea un nuevo usuario en la base de datos.
     *
     * @param string $user_name  Nombre de usuario único.
     * @param string $password   Contraseña en texto plano (se hashea con bcrypt).
     * @param string $nombre     Nombre real del usuario.
     * @param string $apellido   Apellido del usuario.
     * @param string $email      Correo electrónico del usuario.
     * @return bool              True si la inserción fue exitosa, false en caso contrario.
     */
    public function crear(string $user_name, string $password, string $nombre, string $apellido, string $email): bool
    {
        // Genera el hash bcrypt de la contraseña para almacenamiento seguro
        $hash = password_hash($password, PASSWORD_BCRYPT);
        // Sentencia SQL para insertar un nuevo usuario con los datos proporcionados
        $sql = "INSERT INTO usuarios (user_name, password_hash, nombre, apellido, email, estatus) VALUES (?, ?, ?, ?, ?, '1')";
        // Prepara la sentencia SQL para evitar inyección SQL
        $stmt = $this->db->prepare($sql);
        // Ejecuta la consulta con los valores y retorna el resultado booleano
        return $stmt->execute([$user_name, $hash, $nombre, $apellido, $email]);
    }

    /**
     * Obtiene todos los usuarios con sus roles asociados.
     *
     * @return array Lista de usuarios (cada uno como arreglo asociativo).
     */
    public function obtenerTodos(): array
    {
        // Consulta SQL que obtiene todos los usuarios con JOIN a rol_usuarios y roles
        $stmt = $this->db->query("
            SELECT u.id, u.user_name AS username, u.nombre, u.apellido, u.email, u.estatus AS activo,
                   ru.rol, r.nombre_rol AS rol_nombre
            FROM usuarios u
            LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
            LEFT JOIN roles r ON ru.fk_rol = r.id
            ORDER BY u.nombre
        ");
        // Retorna todas las filas como un arreglo asociativo
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un usuario específico por su ID, incluyendo información del rol.
     *
     * @param int $id ID del usuario a buscar.
     * @return array|false Arreglo con los datos del usuario o false si no se encuentra.
     */
    public function obtenerPorId(int $id): array|false
    {
        // Prepara la consulta con JOIN a tablas de roles, parametrizada por ID
        $stmt = $this->db->prepare("
            SELECT u.*, ru.rol, r.nombre_rol AS rol_nombre
            FROM usuarios u
            LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
            LEFT JOIN roles r ON ru.fk_rol = r.id
            WHERE u.id = ?
        ");
        // Ejecuta pasando el ID del usuario
        $stmt->execute([$id]);
        // Retorna una sola fila (o false si no existe)
        return $stmt->fetch();
    }

    /**
     * Obtiene un usuario por su nombre de usuario, solo si está activo.
     *
     * @param string $username Nombre de usuario a buscar.
     * @return array|false Arreglo con los datos del usuario o false si no se encuentra.
     */
    public function obtenerPorUsername(string $username): array|false
    {
        // Consulta parametrizada que filtra por user_name y estatus 'activo'
        $stmt = $this->db->prepare("
            SELECT u.*, ru.rol, r.nombre_rol AS rol_nombre
            FROM usuarios u
            LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
            LEFT JOIN roles r ON ru.fk_rol = r.id
            WHERE u.user_name = ? AND u.estatus = '1'
        ");
        // Ejecuta pasando el nombre de usuario
        $stmt->execute([$username]);
        // Retorna la fila encontrada o false
        return $stmt->fetch();
    }

    /**
     * Autentica un usuario verificando su contraseña contra el hash almacenado.
     *
     * @param string $username Nombre de usuario.
     * @param string $password Contraseña en texto plano.
     * @return array|false Datos del usuario si la autenticación es exitosa, false si falla.
     */
    public function autenticar(string $username, string $password): array|false
    {
        // Obtiene el usuario por su nombre de usuario
        $usuario = $this->obtenerPorUsername($username);
        // Si el usuario existe y la contraseña coincide con el hash, retorna los datos
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            return $usuario;
        }
        // Si no coincide o no existe, retorna false
        return false;
    }

    /**
     * Actualiza los datos de un usuario existente.
     * El campo fk_rol_usuario solo se actualiza si se proporciona un valor no nulo.
     *
     * @param int      $id              ID del usuario a actualizar.
     * @param string   $nombre          Nuevo nombre.
     * @param string   $apellido        Nuevo apellido.
     * @param string   $email           Nuevo correo electrónico.
     * @param int|null $fk_rol_usuario  Nuevo ID de rol (opcional, si es null conserva el actual).
     * @param string   $estatus         Nuevo estatus ('activo' por defecto).
     * @return bool    True si la actualización fue exitosa.
     */
    public function actualizar(int $id, string $nombre, string $apellido, string $email, ?int $fk_rol_usuario = null, string $estatus = '1'): bool
    {
        // SQL de actualización; COALESCE mantiene el rol actual si se envía NULL
        $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, fk_rol_usuario = COALESCE(?, fk_rol_usuario), estatus = ? WHERE id = ?";
        // Prepara la sentencia
        $stmt = $this->db->prepare($sql);
        // Ejecuta con los valores y retorna el resultado booleano
        return $stmt->execute([$nombre, $apellido, $email, $fk_rol_usuario, $estatus, $id]);
    }

    /**
     * Actualiza únicamente la contraseña de un usuario.
     *
     * @param int    $id       ID del usuario.
     * @param string $password Nueva contraseña en texto plano.
     * @return bool  True si la actualización fue exitosa.
     */
    public function actualizarPassword(int $id, string $password): bool
    {
        // Genera el hash bcrypt de la nueva contraseña
        $hash = password_hash($password, PASSWORD_BCRYPT);
        // Prepara la sentencia de actualización de la contraseña
        $stmt = $this->db->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        // Ejecuta y retorna el resultado
        return $stmt->execute([$hash, $id]);
    }

    /**
     * Elimina un usuario de la base de datos por su ID.
     *
     * @param int $id ID del usuario a eliminar.
     * @return bool  True si la eliminación fue exitosa.
     */
    public function eliminar(int $id): bool
    {
        // Prepara la sentencia DELETE parametrizada
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        // Ejecuta y retorna el resultado booleano
        return $stmt->execute([$id]);
    }
}
