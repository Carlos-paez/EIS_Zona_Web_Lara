<?php
// Namespace que organiza este modelo dentro de la carpeta App\Models
namespace App\Models;

// Se importa la clase Model del núcleo de la aplicación
use App\Core\Model;

/**
 * Clase Asesoria que extiende de Model.
 * Gestiona las asesorías legales/técnicas, incluyendo ciudadanos y tipos de asesoría.
 */
class Asesoria extends Model
{
    /**
     * Obtiene o crea un cliente de asesoría basado en su cédula.
     * Si el cliente ya existe, retorna su ID; si no, lo inserta y retorna el nuevo ID.
     *
     * @param string $cedula   Cédula de identidad del cliente.
     * @param string $nombre   Nombre del cliente.
     * @param string $apellido Apellido del cliente (opcional).
     * @return int             ID del cliente existente o recién creado.
     */
    private function obtenerOcrearCliente(string $cedula, string $nombre, string $apellido = ''): int
    {
        // Busca si el cliente ya existe por su cédula
        $stmt = $this->db->prepare("SELECT id FROM cliente_asesoria WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $cliente = $stmt->fetch();
        // Si existe, retorna el ID como entero
        if ($cliente) {
            return (int)$cliente['id'];
        }
        // Si no existe, lo inserta en la tabla cliente_asesoria
        $stmt = $this->db->prepare("INSERT INTO cliente_asesoria (cedula, nombre, apellido) VALUES (?, ?, ?)");
        $stmt->execute([$cedula, $nombre, $apellido]);
        // Retorna el ID autogenerado de la inserción
        return (int)$this->db->lastInsertId();
    }

    /**
     * Obtiene el ID del tipo de asesoría comparando el nombre (case-insensitive).
     *
     * @param string $documento Nombre del tipo de documento/asesoría.
     * @return int|null         ID del tipo si existe, null si no se encuentra.
     */
    private function obtenerTipoAsesoria(string $documento): ?int
    {
        // Busca el tipo de asesoría usando LOWER para comparación sin distinción de mayúsculas
        $stmt = $this->db->prepare("SELECT id FROM tipo_asesoria WHERE LOWER(tipo) = LOWER(?)");
        $stmt->execute([$documento]);
        $tipo = $stmt->fetch();
        // Si lo encuentra retorna el ID como entero, si no retorna null
        return $tipo ? (int)$tipo['id'] : null;
    }

    /**
     * Crea una nueva asesoría en la base de datos.
     * Divide el nombre completo en nombre y apellido, busca o crea el cliente,
     * busca el tipo de asesoría y registra la asesoría con la fecha actual.
     *
     * @param string $ciudadano   Nombre completo del ciudadano (nombre y apellido separados por espacio).
     * @param string $cedula      Cédula del ciudadano.
     * @param string $documento   Tipo de documento/asesoría.
     * @param string $descripcion Descripción detallada de la asesoría.
     * @return bool               True si la inserción fue exitosa.
     */
    public function crear(string $ciudadano, string $cedula, string $documento, string $descripcion): bool
    {
        // Separa el nombre completo en nombre y apellido (máximo 2 partes)
        $nombre_partes = explode(' ', $ciudadano, 2);
        // La primera parte es el nombre
        $nombre = $nombre_partes[0];
        // La segunda parte es el apellido (o cadena vacía si no hay)
        $apellido = $nombre_partes[1] ?? '';

        // Obtiene o crea el cliente y obtiene su ID
        $fk_cliente = $this->obtenerOcrearCliente($cedula, $nombre, $apellido);
        // Obtiene el ID del tipo de asesoría
        $fk_tipo_asesoria = $this->obtenerTipoAsesoria($documento);

        // Inserta la asesoría con la fecha actual (CURDATE) y las claves foráneas
        $sql = "INSERT INTO asesoria (documento, descripcion, fecha, fk_cliente_asesoria, fk_tipo_asesoria) VALUES (?, ?, CURDATE(), ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$documento, $descripcion, $fk_cliente, $fk_tipo_asesoria]);
    }

    /**
     * Obtiene todas las asesorías registradas, ordenadas por fecha descendente.
     *
     * @return array Lista de asesorías con datos del cliente y tipo.
     */
    public function obtenerTodas(): array
    {
        // Consulta que une asesoria con cliente_asesoria y tipo_asesoria
        $stmt = $this->db->query("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   c.cedula, c.nombre AS ciudadano_nombre, c.apellido AS ciudadano_apellido,
                   CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN cliente_asesoria c ON a.fk_cliente_asesoria = c.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            ORDER BY a.fecha DESC
        ");
        // Retorna todas las filas como arreglo asociativo
        return $stmt->fetchAll();
    }

    /**
     * Obtiene las asesorías filtradas por estado (Permitido/Denegado).
     *
     * @param string $estado 'Permitido' o 'Denegado'.
     * @return array  Lista de asesorías que coinciden con el estado.
     */
    public function obtenerPorEstado(string $estado): array
    {
        // Convierte el texto a valor booleano: 1 para Permitido, 0 para Denegado
        $permitido = ($estado === 'Permitido' ? 1 : 0);
        // Consulta parametrizada que filtra por el campo permitido de tipo_asesoria
        $stmt = $this->db->prepare("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   c.cedula, c.nombre AS ciudadano_nombre, c.apellido AS ciudadano_apellido,
                   CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN cliente_asesoria c ON a.fk_cliente_asesoria = c.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE ta.permitido = ?
            ORDER BY a.fecha DESC
        ");
        // Ejecuta con el valor permitido calculado
        $stmt->execute([$permitido]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una asesoría específica por su ID.
     *
     * @param int $id ID de la asesoría.
     * @return array|false Datos de la asesoría o false si no existe.
     */
    public function obtenerPorId(int $id): array|false
    {
        // Consulta parametrizada que obtiene una asesoría por su ID
        $stmt = $this->db->prepare("
            SELECT a.*, c.cedula, c.nombre AS ciudadano_nombre, c.apellido AS ciudadano_apellido,
                   CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN cliente_asesoria c ON a.fk_cliente_asesoria = c.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        // Retorna una sola fila o false
        return $stmt->fetch();
    }

    /**
     * Busca asesorías por cédula del ciudadano (búsqueda parcial con LIKE).
     *
     * @param string $cedula Cédula o parte de ella a buscar.
     * @return array  Lista de asesorías que coinciden.
     */
    public function buscarPorCedula(string $cedula): array
    {
        // Consulta con INNER JOIN a cliente_asesoria para filtrar por cédula
        $stmt = $this->db->prepare("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   c.cedula, c.nombre AS ciudadano_nombre, c.apellido AS ciudadano_apellido,
                   CONCAT(c.nombre, ' ', c.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            INNER JOIN cliente_asesoria c ON a.fk_cliente_asesoria = c.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE c.cedula LIKE ?
            ORDER BY a.fecha DESC
        ");
        // Agrega comodines % para la búsqueda parcial
        $stmt->execute(["%$cedula%"]);
        return $stmt->fetchAll();
    }

    /**
     * Actualiza el documento y descripción de una asesoría.
     * El tipo de asesoría se actualiza automáticamente según el nuevo documento.
     *
     * @param int    $id           ID de la asesoría.
     * @param string $documento    Nuevo tipo de documento.
     * @param string $descripcion  Nueva descripción.
     * @return bool  True si la actualización fue exitosa.
     */
    public function actualizar(int $id, string $documento, string $descripcion): bool
    {
        // Obtiene el ID del tipo de asesoría correspondiente al documento
        $fk_tipo_asesoria = $this->obtenerTipoAsesoria($documento);
        // Actualiza los campos; COALESCE mantiene el tipo anterior si no se encuentra el nuevo
        $sql = "UPDATE asesoria SET documento = ?, descripcion = ?, fk_tipo_asesoria = COALESCE(?, fk_tipo_asesoria) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$documento, $descripcion, $fk_tipo_asesoria, $id]);
    }

    /**
     * Elimina una asesoría por su ID.
     *
     * @param int $id ID de la asesoría a eliminar.
     * @return bool  True si la eliminación fue exitosa.
     */
    public function eliminar(int $id): bool
    {
        // Sentencia DELETE parametrizada
        $stmt = $this->db->prepare("DELETE FROM asesoria WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Cuenta las asesorías agrupadas por estado (Permitido / Denegado).
     *
     * @return array Arreglo con cada estado y su total.
     */
    public function contarPorEstado(): array
    {
        // Usa CASE para convertir el campo permitido a texto legible y cuenta registros
        $stmt = $this->db->query("
            SELECT CASE WHEN ta.permitido = 1 THEN 'Permitido' ELSE 'Denegado' END AS estado, COUNT(*) AS total
            FROM asesoria a
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            GROUP BY ta.permitido
        ");
        return $stmt->fetchAll();
    }
}
