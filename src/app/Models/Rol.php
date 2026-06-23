<?php
// Namespace que organiza este modelo dentro de la carpeta App\Models
namespace App\Models;

// Se importa la clase Model del núcleo de la aplicación
use App\Core\Model;

/**
 * Clase Rol que extiende de Model.
 * Gestiona roles, permisos y la asignación de roles a usuarios.
 */
class Rol extends Model
{
    /**
     * Lista todos los roles con el total de usuarios asignados a cada uno.
     *
     * @return array Arreglo de roles con su nombre y conteo de usuarios.
     */
    public function listarRoles(): array
    {
        // Consulta con subconsulta para contar usuarios por rol, ordenada por nombre
        $stmt = $this->db->query("
            SELECT r.id, r.nombre_rol AS nombre,
                   (SELECT COUNT(*) FROM rol_usuarios ru WHERE ru.fk_rol = r.id) AS total_usuarios
            FROM roles r ORDER BY r.nombre_rol
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un rol específico por su ID.
     *
     * @param int $id ID del rol.
     * @return array|false Datos del rol o false si no existe.
     */
    public function obtenerRolPorId(int $id): array|false
    {
        // Consulta parametrizada para obtener un rol por ID
        $stmt = $this->db->prepare("SELECT id, nombre_rol AS nombre FROM roles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crea un nuevo rol en la base de datos.
     *
     * @param string $nombre_rol Nombre del nuevo rol.
     * @return bool  True si la inserción fue exitosa.
     */
    public function crearRol(string $nombre_rol): bool
    {
        // Inserta un nuevo rol con el nombre proporcionado
        $sql = "INSERT INTO roles (nombre_rol) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre_rol]);
    }

    /**
     * Actualiza el nombre de un rol existente.
     *
     * @param int    $id         ID del rol a actualizar.
     * @param string $nombre_rol Nuevo nombre para el rol.
     * @return bool  True si la actualización fue exitosa.
     */
    public function actualizarRol(int $id, string $nombre_rol): bool
    {
        // Actualiza el nombre del rol identificado por ID
        $sql = "UPDATE roles SET nombre_rol = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre_rol, $id]);
    }

    /**
     * Elimina un rol solo si no tiene usuarios asignados.
     *
     * @param int $id ID del rol a eliminar.
     * @return bool  True si se eliminó, false si tiene usuarios asociados.
     */
    public function eliminarRol(int $id): bool
    {
        // Primero verifica cuántos usuarios tienen asignado este rol
        $sql = "SELECT COUNT(*) AS total FROM rol_usuarios WHERE fk_rol = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $fila = $stmt->fetch();
        // Si hay al menos un usuario asignado, no permite eliminar el rol
        if ((int)$fila['total'] > 0) return false;
        // Si no hay usuarios, procede a eliminar el rol
        $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Obtiene todos los permisos disponibles en el sistema.
     *
     * @return array Lista de permisos (id y nombre).
     */
    public function obtenerPermisos(): array
    {
        // Consulta todos los permisos ordenados alfabéticamente
        $stmt = $this->db->query("SELECT id, permisos AS nombre FROM permisos ORDER BY permisos");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene los IDs de los permisos asignados a un rol específico.
     *
     * @param int $rol_id ID del rol.
     * @return array Arreglo de IDs de permisos asignados al rol.
     */
    public function obtenerPermisosPorRol(int $rol_id): array
    {
        // Consulta los permisos asociados a un rol en la tabla permisos_rol
        $sql = "SELECT fk_permiso AS permiso_id FROM permisos_rol WHERE fk_rol = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$rol_id]);
        $rows = $stmt->fetchAll();
        // Extrae solo la columna 'permiso_id' como un arreglo simple
        return array_column($rows, 'permiso_id');
    }

    /**
     * Guarda (reemplaza) los permisos asignados a un rol.
     * Elimina todos los permisos existentes y luego inserta los nuevos.
     * La operación se realiza dentro de una transacción.
     *
     * @param int   $rol_id      ID del rol.
     * @param array $permiso_ids Arreglo de IDs de permisos a asignar.
     * @return bool  True si la operación fue exitosa.
     */
    public function guardarPermisosRol(int $rol_id, array $permiso_ids): bool
    {
        // Inicia una transacción para asegurar atomicidad
        $this->db->beginTransaction();
        try {
            // Elimina todos los permisos actuales del rol
            $stmt = $this->db->prepare("DELETE FROM permisos_rol WHERE fk_rol = ?");
            $stmt->execute([$rol_id]);
            // Si hay nuevos permisos para asignar, los inserta uno por uno
            if (!empty($permiso_ids)) {
                $sql = "INSERT INTO permisos_rol (fk_rol, fk_permiso) VALUES (?, ?)";
                $stmt = $this->db->prepare($sql);
                foreach ($permiso_ids as $pid) {
                    $stmt->execute([$rol_id, (int)$pid]);
                }
            }
            // Confirma la transacción (commit)
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Si ocurre algún error, revierte todos los cambios (rollback)
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Obtiene todos los roles (solo id y nombre) ordenados alfabéticamente.
     *
     * @return array Lista de roles.
     */
    public function obtenerRoles(): array
    {
        $stmt = $this->db->query("SELECT id, nombre_rol AS nombre FROM roles ORDER BY nombre_rol");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los usuarios con su información básica y el nombre del rol asignado.
     *
     * @return array Lista de usuarios con datos personales y rol.
     */
    public function obtenerUsuarios(): array
    {
        // Consulta con JOIN a rol_usuarios y roles para obtener el nombre del rol
        $stmt = $this->db->query("
            SELECT u.id, u.user_name AS username, u.nombre, u.apellido, u.email, u.estatus AS activo,
                   r.nombre_rol AS rol
            FROM usuarios u
            LEFT JOIN rol_usuarios ru ON u.fk_rol_usuario = ru.id
            LEFT JOIN roles r ON ru.fk_rol = r.id
            ORDER BY u.nombre
        ");
        return $stmt->fetchAll();
    }

    /**
     * Asigna un rol a un usuario actualizando su fk_rol_usuario.
     *
     * @param int $usuario_id ID del usuario.
     * @param int $rol_id     ID del rol a asignar.
     * @return bool True si la asignación fue exitosa.
     */
    public function asignarRolAUsuario(int $usuario_id, int $rol_id): bool
    {
        // Actualiza el campo fk_rol_usuario del usuario con el ID del rol
        $stmt = $this->db->prepare("UPDATE usuarios SET fk_rol_usuario = ? WHERE id = ?");
        return $stmt->execute([$rol_id, $usuario_id]);
    }

    /**
     * Cuenta el total de roles registrados en el sistema.
     *
     * @return int Número total de roles.
     */
    public function totalRoles(): int
    {
        // Cuenta todas las filas de la tabla roles
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM roles");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }

    /**
     * Cuenta el total de permisos registrados en el sistema.
     *
     * @return int Número total de permisos.
     */
    public function totalPermisos(): int
    {
        // Cuenta todas las filas de la tabla permisos
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM permisos");
        $fila = $stmt->fetch();
        return (int)$fila['total'];
    }
}
