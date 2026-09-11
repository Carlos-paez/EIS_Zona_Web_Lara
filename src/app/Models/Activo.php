<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Activo extends Model
{
    private int $id = 0;
    private string $marca = '';
    private string $descripcion = '';
    private int $fkTipoActivo = 0;
    private int $activa = 1;
    private int $isCiber = 0;

    private const MIN_MARCA        = 2;
    private const MAX_MARCA        = 100;
    private const MAX_DESCRIPCION  = 1000;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $this->sanitizeInt($id);
    }

    public function getMarca(): string
    {
        return $this->marca;
    }

    public function setMarca(string $marca): void
    {
        $marca = $this->sanitizeString($marca);
        $this->validateNotEmpty($marca, 'marca');
        $this->validateMinLength($marca, 'marca', self::MIN_MARCA);
        $this->validateLength($marca, 'marca', self::MAX_MARCA);
        $this->validarLibre($marca, 'marca');
        $this->marca = $marca;
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
        $this->validarSinControl($descripcion, 'descripción');
        $this->descripcion = $descripcion;
    }

    public function getFkTipoActivo(): int
    {
        return $this->fkTipoActivo;
    }

    public function setFkTipoActivo(int $fkTipoActivo): void
    {
        $this->fkTipoActivo = $this->sanitizeInt($fkTipoActivo);
    }

    public function getActiva(): int
    {
        return $this->activa;
    }

    public function setActiva(int $activa): void
    {
        $this->activa = $activa ? 1 : 0;
    }

    public function getIsCiber(): int
    {
        return $this->isCiber;
    }

    public function setIsCiber(int $isCiber): void
    {
        $this->isCiber = $isCiber ? 1 : 0;
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'marca'          => $this->marca,
            'descripcion'    => $this->descripcion,
            'fk_tipo_activo' => $this->fkTipoActivo,
            'activa'         => $this->activa,
            'is_ciber'       => $this->isCiber,
        ];
    }

    public static function fromArray(array $data): self
    {
        $activo = new self();
        $activo->setId((int)($data['id'] ?? 0));
        $activo->setMarca($data['marca'] ?? '');
        $activo->setDescripcion($data['descripcion'] ?? '');
        $activo->setFkTipoActivo((int)($data['fk_tipo_activo'] ?? 0));
        $activo->setActiva((int)($data['activa'] ?? 1));
        $activo->setIsCiber((int)($data['is_ciber'] ?? 0));
        return $activo;
    }

    /**
     * Lista todos los activos con su tipo asociado y el estado de sesión
     * para las estaciones de cybercafé (ocupada/disponible).
     *
     * @return array
     */
    public function obtenerActivos(): array
    {
        $stmt = $this->db->query("
            SELECT a.id, a.marca, a.descripcion, a.activa, a.is_ciber,
                   a.fk_tipo_activo AS tipo_activo_id, ta.nombre_tipo,
                   CASE WHEN sc.id IS NOT NULL THEN 'ocupada' ELSE 'disponible' END AS estado
            FROM activos a
            LEFT JOIN tipo_activo ta ON a.fk_tipo_activo = ta.id
            LEFT JOIN (
                SELECT s1.*
                FROM sesion_ciber s1
                JOIN (
                    SELECT fk_activo, MAX(id) AS max_id
                    FROM sesion_ciber
                    WHERE finalizada = 0
                    GROUP BY fk_activo
                ) s2 ON s1.id = s2.max_id
            ) sc ON sc.fk_activo = a.id
            ORDER BY a.id
        ");
        return $stmt->fetchAll();
    }

    public function obtenerActivoPorId(int $id): array|false
    {
        $id = $this->sanitizeInt($id);
        if ($id <= 0) {
            return false;
        }
        $stmt = $this->db->prepare("
            SELECT a.id, a.marca, a.descripcion, a.activa, a.is_ciber,
                   a.fk_tipo_activo AS tipo_activo_id, ta.nombre_tipo
            FROM activos a
            LEFT JOIN tipo_activo ta ON a.fk_tipo_activo = ta.id
            WHERE a.id = ?
        ");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Crea un activo con su tipo asociado.
     *
     * @param string $marca
     * @param string $descripcion
     * @param int    $tipoActivoId
     * @param int    $activa
     * @param int    $isCiber
     * @return int|false ID del activo creado o false si falla.
     */
    public function crearActivo(string $marca, string $descripcion, int $tipoActivoId, int $activa = 1, int $isCiber = 0): int|false
    {
        $this->setMarca($marca);
        $this->setDescripcion($descripcion);
        $this->setFkTipoActivo($tipoActivoId);
        $this->setActiva($activa);
        $this->setIsCiber($isCiber);

        if ($this->fkTipoActivo <= 0) {
            throw new \InvalidArgumentException('Debes seleccionar un tipo de activo');
        }
        if (!$this->existeTipoActivo($this->fkTipoActivo)) {
            throw new \InvalidArgumentException('El tipo de activo seleccionado no existe');
        }
        if ($this->existeActivo($this->marca, $this->descripcion)) {
            throw new \InvalidArgumentException('Ya existe un activo con esa marca y descripción');
        }

        $stmt = $this->db->prepare("
            INSERT INTO activos (marca, descripcion, is_ciber, activa, fk_tipo_activo)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bindParam(1, $this->marca, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->isCiber, PDO::PARAM_INT);
        $stmt->bindParam(4, $this->activa, PDO::PARAM_INT);
        $stmt->bindParam(5, $this->fkTipoActivo, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualiza un activo existente.
     *
     * @param int    $id
     * @param string $marca
     * @param string $descripcion
     * @param int    $tipoActivoId
     * @param int    $activa
     * @param int    $isCiber
     * @return bool
     */
    public function actualizarActivo(int $id, string $marca, string $descripcion, int $tipoActivoId, int $activa = 1, int $isCiber = 0): bool
    {
        $this->setId($id);
        $this->setMarca($marca);
        $this->setDescripcion($descripcion);
        $this->setFkTipoActivo($tipoActivoId);
        $this->setActiva($activa);
        $this->setIsCiber($isCiber);

        if ($this->id <= 0) {
            throw new \InvalidArgumentException('ID no válido');
        }
        if ($this->fkTipoActivo <= 0) {
            throw new \InvalidArgumentException('Debes seleccionar un tipo de activo');
        }
        if (!$this->existeTipoActivo($this->fkTipoActivo)) {
            throw new \InvalidArgumentException('El tipo de activo seleccionado no existe');
        }
        if ($this->existeActivo($this->marca, $this->descripcion, $this->id)) {
            throw new \InvalidArgumentException('Ya existe otro activo con esa marca y descripción');
        }

        $stmt = $this->db->prepare("
            UPDATE activos SET marca = ?, descripcion = ?, is_ciber = ?, activa = ?, fk_tipo_activo = ?
            WHERE id = ?
        ");
        $stmt->bindParam(1, $this->marca, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(3, $this->isCiber, PDO::PARAM_INT);
        $stmt->bindParam(4, $this->activa, PDO::PARAM_INT);
        $stmt->bindParam(5, $this->fkTipoActivo, PDO::PARAM_INT);
        $stmt->bindParam(6, $this->id, PDO::PARAM_INT);
        return $stmt->execute() && $stmt->rowCount() >= 0;
    }

    /**
     * Cambia el estado activo/inactivo de un activo.
     *
     * @param int $id
     * @param int $activa
     * @return bool
     */
    public function cambiarEstadoActivo(int $id, int $activa): bool
    {
        $id     = $this->sanitizeInt($id);
        $activa = $activa ? 1 : 0;
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID no válido');
        }
        $stmt = $this->db->prepare("UPDATE activos SET activa = ? WHERE id = ?");
        $stmt->bindParam(1, $activa, PDO::PARAM_INT);
        $stmt->bindParam(2, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Elimina un activo solo si no tiene sesiones de cybercafé asociadas.
     *
     * @param int $id
     * @return bool
     */
    public function eliminarActivo(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID no válido');
        }
        if ($this->tieneSesiones($id)) {
            throw new \InvalidArgumentException('No se puede eliminar un activo con sesiones de cybercafé registradas. Desactívalo en su lugar');
        }
        $stmt = $this->db->prepare("DELETE FROM activos WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function existeActivo(string $marca, string $descripcion, int $excludeId = 0): bool
    {
        $marca       = $this->sanitizeString($marca);
        $descripcion = $this->sanitizeString($descripcion);
        if ($excludeId > 0) {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM activos WHERE marca = ? AND descripcion = ? AND id != ?");
            $stmt->bindParam(1, $marca, PDO::PARAM_STR);
            $stmt->bindParam(2, $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(3, $excludeId, PDO::PARAM_INT);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM activos WHERE marca = ? AND descripcion = ?");
            $stmt->bindParam(1, $marca, PDO::PARAM_STR);
            $stmt->bindParam(2, $descripcion, PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int)$stmt->fetch()['total'] > 0;
    }

    public function existeTipoActivo(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM tipo_activo WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetch()['total'] > 0;
    }

    public function tieneSesiones(int $id): bool
    {
        $id = $this->sanitizeInt($id);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sesion_ciber WHERE fk_activo = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Tipos de activo disponibles.
     *
     * @return array
     */
    public function listarTiposActivo(): array
    {
        $stmt = $this->db->query("SELECT id, nombre_tipo FROM tipo_activo ORDER BY nombre_tipo");
        return $stmt->fetchAll();
    }

    public function totalActivos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM activos");
        return (int)$stmt->fetch()['total'];
    }

    public function totalCiber(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM activos WHERE is_ciber = 1");
        return (int)$stmt->fetch()['total'];
    }

    public function totalActivosOcupados(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) AS total
            FROM sesion_ciber s
            JOIN (
                SELECT fk_activo, MAX(id) AS max_id
                FROM sesion_ciber
                WHERE finalizada = 0
                GROUP BY fk_activo
            ) m ON s.id = m.max_id
        ");
        return (int)$stmt->fetch()['total'];
    }

    public function totalInactivos(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM activos WHERE activa = 0");
        return (int)$stmt->fetch()['total'];
    }

    public function totalPorTipo(): array
    {
        $stmt = $this->db->query("
            SELECT ta.nombre_tipo, COUNT(a.id) AS cantidad
            FROM tipo_activo ta
            LEFT JOIN activos a ON a.fk_tipo_activo = ta.id
            GROUP BY ta.id, ta.nombre_tipo
            ORDER BY cantidad DESC, ta.nombre_tipo
        ");
        return $stmt->fetchAll();
    }
}
