<?php
// Namespace que organiza este modelo dentro de la carpeta App\Models
namespace App\Models;

// Se importa la clase Model del núcleo de la aplicación
use App\Core\Model;
use PDO;

/**
 * Clase Asesoria que extiende de Model.
 * Gestiona las asesorías legales/técnicas, incluyendo ciudadanos y tipos de asesoría.
 *
 * Encapsulación: todos los atributos son privados y se accede a ellos
 * únicamente mediante getters y setters.
 */
class Asesoria extends Model
{
    private int $id = 0;
    private string $cedula = '';
    private string $nombre = '';
    private string $apellido = '';
    private string $documento = '';
    private string $descripcion = '';
    private string $fecha = '';
    private ?int $fkClienteAsesoria = null;
    private ?int $fkTipoAsesoria = null;
    private int $permitido = 1;

    private const MIN_CEDULA      = 5;
    private const MAX_CEDULA      = 20;
    private const MIN_NOMBRE      = 2;
    private const MAX_NOMBRE      = 100;
    private const MAX_APELLIDO    = 100;
    private const MAX_DOCUMENTO   = 100;
    private const MAX_DESCRIPCION = 1000;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $this->sanitizeInt($id);
    }

    public function getCedula(): string
    {
        return $this->cedula;
    }

    public function setCedula(string $cedula): void
    {
        $cedula = $this->sanitizeString($cedula);
        $this->validateNotEmpty($cedula, 'cédula');
        $this->validateMinLength($cedula, 'cédula', self::MIN_CEDULA);
        $this->validateLength($cedula, 'cédula', self::MAX_CEDULA);
        $this->cedula = $cedula;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): void
    {
        $nombre = $this->sanitizeString($nombre);
        $this->validateNotEmpty($nombre, 'nombre');
        $this->validateMinLength($nombre, 'nombre', self::MIN_NOMBRE);
        $this->validateLength($nombre, 'nombre', self::MAX_NOMBRE);
        $this->nombre = $nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido): void
    {
        $apellido = $this->sanitizeString($apellido);
        $this->validateLength($apellido, 'apellido', self::MAX_APELLIDO);
        $this->apellido = $apellido;
    }

    public function getDocumento(): string
    {
        return $this->documento;
    }

    public function setDocumento(string $documento): void
    {
        $documento = $this->sanitizeString($documento);
        $this->validateNotEmpty($documento, 'tipo de documento');
        $this->validateLength($documento, 'tipo de documento', self::MAX_DOCUMENTO);
        $this->documento = $documento;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): void
    {
        $descripcion = $this->sanitizeString($descripcion);
        $this->validateNotEmpty($descripcion, 'descripción');
        $this->validateLength($descripcion, 'descripción', self::MAX_DESCRIPCION);
        $this->descripcion = $descripcion;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function setFecha(string $fecha): void
    {
        $this->fecha = $this->validarFecha($fecha);
    }

    public function getFkClienteAsesoria(): ?int
    {
        return $this->fkClienteAsesoria;
    }

    public function setFkClienteAsesoria(?int $fkClienteAsesoria): void
    {
        $this->fkClienteAsesoria = $fkClienteAsesoria !== null ? $this->sanitizeInt($fkClienteAsesoria) : null;
    }

    public function getFkTipoAsesoria(): ?int
    {
        return $this->fkTipoAsesoria;
    }

    public function setFkTipoAsesoria(?int $fkTipoAsesoria): void
    {
        $this->fkTipoAsesoria = $fkTipoAsesoria !== null ? $this->sanitizeInt($fkTipoAsesoria) : null;
    }

    public function getPermitido(): int
    {
        return $this->permitido;
    }

    public function setPermitido(int $permitido): void
    {
        $this->permitido = $permitido === 1 ? 1 : 0;
    }

    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'cedula'              => $this->cedula,
            'nombre'              => $this->nombre,
            'apellido'            => $this->apellido,
            'documento'           => $this->documento,
            'descripcion'         => $this->descripcion,
            'fecha'               => $this->fecha,
            'fk_cliente_asesoria' => $this->fkClienteAsesoria,
            'fk_tipo_asesoria'    => $this->fkTipoAsesoria,
            'permitido'           => $this->permitido,
        ];
    }

    public static function fromArray(array $data): self
    {
        $a = new self();
        $a->setId((int)($data['id'] ?? 0));
        $a->setCedula($data['cedula'] ?? '');
        $a->setNombre($data['nombre'] ?? '');
        $a->setApellido($data['apellido'] ?? '');
        $a->setDocumento($data['documento'] ?? '');
        $a->setDescripcion($data['descripcion'] ?? '');
        $a->setFecha($data['fecha'] ?? '');
        $a->setFkClienteAsesoria(isset($data['fk_cliente_asesoria']) ? (int)$data['fk_cliente_asesoria'] : null);
        $a->setFkTipoAsesoria(isset($data['fk_tipo_asesoria']) ? (int)$data['fk_tipo_asesoria'] : null);
        $a->setPermitido((int)($data['permitido'] ?? 1));
        return $a;
    }

    /**
     * Obtiene o crea un cliente de asesoría basado en su cédula.
     * Busca en la tabla clientes por cédula; si no existe, lo crea.
     * Luego busca o crea el registro en cliente_asesoria vinculado al cliente.
     *
     * @param string $cedula   Cédula de identidad del cliente.
     * @param string $nombre   Nombre del cliente.
     * @param string $apellido Apellido del cliente (opcional).
     * @return int             ID del registro en cliente_asesoria.
     */
    private function obtenerOcrearCliente(string $cedula, string $nombre, string $apellido = ''): int
    {
        // 1. Buscar o crear en la tabla clientes
        $stmt = $this->db->prepare("SELECT id FROM clientes WHERE cedula = ?");
        $stmt->bindParam(1, $cedula, PDO::PARAM_STR);
        $stmt->execute();
        $cliente = $stmt->fetch();

        if ($cliente) {
            $cliente_id = (int)$cliente['id'];
        } else {
            $stmt = $this->db->prepare("INSERT INTO clientes (cedula, nombre, apellido, direccion, telefono) VALUES (?, ?, ?, '', '')");
            $stmt->bindParam(1, $cedula, PDO::PARAM_STR);
            $stmt->bindParam(2, $nombre, PDO::PARAM_STR);
            $stmt->bindParam(3, $apellido, PDO::PARAM_STR);
            $stmt->execute();
            $cliente_id = (int)$this->db->lastInsertId();
        }

        // 2. Buscar o crear en cliente_asesoria vinculado al cliente
        $stmt = $this->db->prepare("SELECT id FROM cliente_asesoria WHERE fk_cliente = ?");
        $stmt->bindParam(1, $cliente_id, PDO::PARAM_INT);
        $stmt->execute();
        $ca = $stmt->fetch();

        if ($ca) {
            return (int)$ca['id'];
        }

        $stmt = $this->db->prepare("INSERT INTO cliente_asesoria (fk_cliente, email, rif, tipo) VALUES (?, 'N/A', 'N/A', 'civil')");
        $stmt->bindParam(1, $cliente_id, PDO::PARAM_INT);
        $stmt->execute();
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
        $stmt->bindParam(1, $documento, PDO::PARAM_STR);
        $stmt->execute();
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

        // Encapsulación: se cargan los valores mediante setters con validación
        $this->setNombre($nombre_partes[0]);
        $this->setApellido($nombre_partes[1] ?? '');
        $this->setCedula($cedula);
        $this->setDocumento($documento);
        $this->setDescripcion($descripcion);

        // Obtiene o crea el cliente y obtiene su ID
        $fk_cliente = $this->obtenerOcrearCliente($this->cedula, $this->nombre, $this->apellido);
        // Obtiene el ID del tipo de asesoría
        $fk_tipo_asesoria = $this->obtenerTipoAsesoria($this->documento);

        // Inserta la asesoría con la fecha actual (CURDATE) y las claves foráneas
        $sql = "INSERT INTO asesoria (documento, descripcion, fecha, fk_cliente_asesoria, fk_tipo_asesoria) VALUES (?, ?, CURDATE(), ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->documento, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(3, $fk_cliente, PDO::PARAM_INT);
        $stmt->bindParam(4, $fk_tipo_asesoria, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Obtiene todas las asesorías registradas, ordenadas por fecha descendente.
     *
     * @return array Lista de asesorías con datos del cliente y tipo.
     */
    public function obtenerTodas(): array
    {
        // Consulta que une asesoria con cliente_asesoria, clientes y tipo_asesoria
        $stmt = $this->db->query("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   cli.cedula, cli.nombre AS ciudadano_nombre, cli.apellido AS ciudadano_apellido,
                   CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
            LEFT JOIN clientes cli ON ca.fk_cliente = cli.id
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
        // Encapsulación: se normaliza el estado mediante el setter
        $this->setPermitido($estado === 'Permitido' ? 1 : 0);

        // Consulta parametrizada que filtra por el campo permitido de tipo_asesoria
        $stmt = $this->db->prepare("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   cli.cedula, cli.nombre AS ciudadano_nombre, cli.apellido AS ciudadano_apellido,
                   CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
            LEFT JOIN clientes cli ON ca.fk_cliente = cli.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE ta.permitido = ?
            ORDER BY a.fecha DESC
        ");
        // Ejecuta con el valor permitido calculado
        $stmt->bindParam(1, $this->permitido, PDO::PARAM_INT);
        $stmt->execute();
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
        // Encapsulación: se carga el ID mediante el setter
        $this->setId($id);

        // Consulta parametrizada que obtiene una asesoría por su ID
        $stmt = $this->db->prepare("
            SELECT a.*, cli.cedula, cli.nombre AS ciudadano_nombre, cli.apellido AS ciudadano_apellido,
                   CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
            LEFT JOIN clientes cli ON ca.fk_cliente = cli.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE a.id = ?
        ");
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        $stmt->execute();
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
        // Sanitiza el término de búsqueda (sin validar longitud mínima, es búsqueda parcial)
        $cedula = $this->sanitizeString($cedula);

        // Consulta con INNER JOIN a cliente_asesoria para filtrar por cédula
        $stmt = $this->db->prepare("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   cli.cedula, cli.nombre AS ciudadano_nombre, cli.apellido AS ciudadano_apellido,
                   CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            INNER JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
            INNER JOIN clientes cli ON ca.fk_cliente = cli.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE cli.cedula LIKE ?
            ORDER BY a.fecha DESC
        ");
        // Agrega comodines % para la búsqueda parcial
        $patron = "%$cedula%";
        $stmt->bindParam(1, $patron, PDO::PARAM_STR);
        $stmt->execute();
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
        // Encapsulación: se cargan los valores mediante setters con validación
        $this->setId($id);
        $this->setDocumento($documento);
        $this->setDescripcion($descripcion);

        // Obtiene el ID del tipo de asesoría correspondiente al documento
        $fk_tipo_asesoria = $this->obtenerTipoAsesoria($this->documento);
        // Actualiza los campos; COALESCE mantiene el tipo anterior si no se encuentra el nuevo
        $sql = "UPDATE asesoria SET documento = ?, descripcion = ?, fk_tipo_asesoria = COALESCE(?, fk_tipo_asesoria) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(1, $this->documento, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(3, $fk_tipo_asesoria, PDO::PARAM_INT);
        $stmt->bindParam(4, $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Elimina una asesoría por su ID.
     *
     * @param int $id ID de la asesoría a eliminar.
     * @return bool  True si la eliminación fue exitosa.
     */
    public function eliminar(int $id): bool
    {
        // Encapsulación: se carga el ID mediante el setter
        $this->setId($id);
        // Sentencia DELETE parametrizada
        $stmt = $this->db->prepare("DELETE FROM asesoria WHERE id = ?");
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        return $stmt->execute();
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

    /**
     * Valida que una fecha tenga el formato YYYY-MM-DD (o esté vacía).
     *
     * @param string $fecha Fecha a validar.
     * @return string La fecha validada.
     */
    private function validarFecha(string $fecha): string
    {
        $fecha = trim($fecha);
        if ($fecha !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            throw new \InvalidArgumentException('Formato de fecha inválido (use YYYY-MM-DD)');
        }
        return $fecha;
    }
}
