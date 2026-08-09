<?php

namespace App\Models;

use App\Core\Model;
use App\Models\Cliente;
use PDO;

class CiberControl extends Model
{
    private const MAX_CEDULA     = 20;
    private const MAX_NOMBRE     = 100;
    private const MAX_APELLIDO   = 100;
    private const MAX_DIRECCION  = 500;
    private const MAX_TELEFONO   = 20;
    private const MAX_TIEMPO_USO = 50;

    /**
     * Lista las estaciones (activos de cybercafé) activas con su estado actual.
     * Una estación está "ocupada" si tiene una sesión sin finalizar.
     *
     * @return array Lista de estaciones con su estado, cliente y tarifa asociados.
     */
    public function listarEstaciones(): array
    {
        $stmt = $this->db->query("
            SELECT a.id, a.marca, a.descripcion, a.fk_tipo_activo,
                   ta.nombre_tipo,
                   sc.id AS sesion_id, sc.tiempo_uso, sc.fk_tarifa,
                   cli.cedula, CONCAT(cli.nombre, ' ', cli.apellido) AS cliente,
                   tr.tarifa_hora, tr.precio_tiempo,
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
            LEFT JOIN clientes cli ON sc.fk_cliente = cli.id
            LEFT JOIN tarifas tr ON sc.fk_tarifa = tr.id
            WHERE a.is_ciber = 1 AND a.activa = 1
            ORDER BY a.id
        ");
        return $stmt->fetchAll();
    }

    /**
     * Lista las tarifas disponibles para las sesiones de cybercafé.
     *
     * @return array Lista de tarifas.
     */
    public function listarTarifas(): array
    {
        $stmt = $this->db->query("
            SELECT id, tarifa_hora, precio_tiempo
            FROM tarifas
            ORDER BY tarifa_hora
        ");
        return $stmt->fetchAll();
    }

    /**
     * Busca un cliente por su cédula (reutiliza el modelo Cliente).
     *
     * @param string $cedula Cédula del cliente.
     * @return array|false   Datos del cliente o false si no existe.
     */
    public function buscarCliente(string $cedula): array|false
    {
        return (new Cliente())->obtenerClientePorCedula($cedula);
    }

    /**
     * Inicia una sesión de cybercafé de forma transaccional:
     * cliente (get-or-create) + registro en sesion_ciber.
     * Valida que la estación exista, esté activa, sea de cybercafé
     * y no tenga una sesión sin finalizar.
     *
     * @param string $ciudadano  Nombre completo del cliente.
     * @param string $cedula     Cédula del cliente.
     * @param string $direccion  Dirección del cliente (opcional).
     * @param string $telefono   Teléfono del cliente (opcional).
     * @param int    $activoId   ID de la estación (activo) a ocupar.
     * @param int    $tarifaId   ID de la tarifa aplicada.
     * @param string $tiempoUso  Tiempo de uso en formato HH:MM:SS.
     * @return int|false         ID de la sesión creada, o false si falla.
     */
    public function iniciarSesion(string $ciudadano, string $cedula, string $direccion = '', string $telefono = '', int $activoId = 0, int $tarifaId = 0, string $tiempoUso = ''): int|false
    {
        $ciudadano = $this->sanitizeString($ciudadano);
        $cedula    = $this->sanitizeString($cedula);
        $direccion = $this->sanitizeString($direccion);
        $telefono  = $this->sanitizeString($telefono);
        $tiempoUso = $this->sanitizeString($tiempoUso);

        $this->validateNotEmpty($ciudadano, 'nombre del cliente');
        $this->validateNotEmpty($cedula, 'cédula');
        $this->validateLength($cedula, 'cédula', self::MAX_CEDULA);
        $this->validateLength($ciudadano, 'nombre del cliente', self::MAX_NOMBRE);
        $this->validateLength($direccion, 'dirección', self::MAX_DIRECCION);
        $this->validateLength($telefono, 'teléfono', self::MAX_TELEFONO);
        $this->validateLength($tiempoUso, 'tiempo de uso', self::MAX_TIEMPO_USO);

        if ($activoId <= 0 || $tarifaId <= 0) {
            throw new \InvalidArgumentException('Estación y tarifa son obligatorias');
        }
        if ($tiempoUso === '' || !preg_match('/^\d{1,3}:\d{2}:\d{2}$/', $tiempoUso)) {
            throw new \InvalidArgumentException('El tiempo de uso debe tener formato HH:MM:SS');
        }

        $nombre_partes = explode(' ', $ciudadano, 2);
        $nombre        = $nombre_partes[0];
        $apellido      = $nombre_partes[1] ?? '';

        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > self::MAX_NOMBRE) {
            throw new \InvalidArgumentException('El nombre del cliente debe tener entre 2 y ' . self::MAX_NOMBRE . ' caracteres');
        }
        if ($apellido !== '' && mb_strlen($apellido) > self::MAX_APELLIDO) {
            throw new \InvalidArgumentException('El apellido no puede exceder ' . self::MAX_APELLIDO . ' caracteres');
        }

        try {
            $this->db->beginTransaction();

            // 1. Verifica que la estación exista, esté activa y sea de cybercafé
            $stmt = $this->db->prepare("SELECT id FROM activos WHERE id = ? AND is_ciber = 1 AND activa = 1");
            $stmt->bindParam(1, $activoId, PDO::PARAM_INT);
            $stmt->execute();
            if (!$stmt->fetch()) {
                throw new \InvalidArgumentException('La estación no existe o no está disponible');
            }

            // 2. Verifica que la estación no tenga una sesión sin finalizar
            $stmt = $this->db->prepare("SELECT id FROM sesion_ciber WHERE fk_activo = ? AND finalizada = 0 LIMIT 1");
            $stmt->bindParam(1, $activoId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetch()) {
                throw new \InvalidArgumentException('La estación ya está ocupada');
            }

            // 3. Verifica que la tarifa exista
            $stmt = $this->db->prepare("SELECT id FROM tarifas WHERE id = ?");
            $stmt->bindParam(1, $tarifaId, PDO::PARAM_INT);
            $stmt->execute();
            if (!$stmt->fetch()) {
                throw new \InvalidArgumentException('La tarifa seleccionada no existe');
            }

            // 4. Obtener o crear el cliente (reutiliza el modelo centralizado)
            $cliente = new Cliente();
            $fk_cliente = $cliente->obtenerOCrearPorCedula($cedula, $nombre, $apellido, $direccion, $telefono);

            // 5. Registrar la sesión de cybercafé
            $sql = "INSERT INTO sesion_ciber (tiempo_uso, fk_cliente, fk_tarifa, fk_activo) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(1, $tiempoUso, PDO::PARAM_STR);
            $stmt->bindParam(2, $fk_cliente, PDO::PARAM_INT);
            $stmt->bindParam(3, $tarifaId, PDO::PARAM_INT);
            $stmt->bindParam(4, $activoId, PDO::PARAM_INT);
            $stmt->execute();

            $sesion_id = (int)$this->db->lastInsertId();

            $this->db->commit();
            return $sesion_id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Finaliza una sesión de cybercafé marcándola como finalizada.
     *
     * @param int $sesionId ID de la sesión a finalizar.
     * @return bool         True si se finalizó exitosamente.
     */
    public function finalizarSesion(int $sesionId): bool
    {
        $sesionId = $this->sanitizeInt($sesionId);
        if ($sesionId <= 0) {
            throw new \InvalidArgumentException('ID de sesión no válido');
        }

        $stmt = $this->db->prepare("UPDATE sesion_ciber SET finalizada = 1 WHERE id = ? AND finalizada = 0");
        $stmt->bindParam(1, $sesionId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
