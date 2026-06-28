<?php
// ============================================================
// MODELO: CiberModel (Adaptado a BD zona_web_lara)
// ============================================================

require_once __DIR__ . '/../../Config/database.php';

class CiberModel
{
    /** @var PDO */
    protected $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Obtiene todas las PCs del cyber con su estado actual
     * Calcula estado basado en sesiones activas (created_at + tiempo_uso)
     */
    public function obtenerEstaciones(): array
    {
        if (!$this->pdo) {
            return [];
        }

        try {
            $sql = "SELECT 
                        a.id_activo as id,
                        a.marca,
                        a.descripcion,
                        a.activa,
                        t.nombre_tipo as tipo,
                        -- Calcular estado (Ocupada si hay sesión activa)
                        CASE 
                            WHEN EXISTS (
                                SELECT 1 FROM sesion_ciber s 
                                WHERE s.fk_activo = a.id_activo 
                                AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            ) THEN 'Ocupada'
                            ELSE 'Disponible'
                        END as estado,
                        -- ID de la sesión activa
                        (
                            SELECT s.id_sesion 
                            FROM sesion_ciber s 
                            WHERE s.fk_activo = a.id_activo 
                            AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            LIMIT 1
                        ) as sesion_id,
                        -- Nombre del cliente en sesión activa
                        (
                            SELECT CONCAT(c.nombre, ' ', c.apellido)
                            FROM sesion_ciber s
                            JOIN clientes c ON s.fk_cliente = c.id_cliente
                            WHERE s.fk_activo = a.id_activo 
                            AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            LIMIT 1
                        ) as cliente_nombre,
                        -- Minutos transcurridos de la sesión activa
                        (
                            SELECT TIMESTAMPDIFF(MINUTE, s.created_at, NOW())
                            FROM sesion_ciber s
                            WHERE s.fk_activo = a.id_activo 
                            AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            LIMIT 1
                        ) as minutos_transcurridos,
                        -- Tiempo total contratado (minutos)
                        (
                            SELECT TIME_TO_SEC(s.tiempo_uso) / 60
                            FROM sesion_ciber s
                            WHERE s.fk_activo = a.id_activo 
                            AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            LIMIT 1
                        ) as tiempo_total_minutos,
                        -- Tarifa de la sesión activa
                        (
                            SELECT t.tarifa_hora
                            FROM sesion_ciber s
                            JOIN tarifas t ON s.fk_tarifa = t.id_tarifa
                            WHERE s.fk_activo = a.id_activo 
                            AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            LIMIT 1
                        ) as tarifa_hora,
                        -- Costo estimado actual
                        (
                            SELECT ROUND((TIMESTAMPDIFF(MINUTE, s.created_at, NOW()) / 60) * t.tarifa_hora, 2)
                            FROM sesion_ciber s
                            JOIN tarifas t ON s.fk_tarifa = t.id_tarifa
                            WHERE s.fk_activo = a.id_activo 
                            AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            LIMIT 1
                        ) as costo_estimado
                    FROM activos a
                    JOIN tipo_activo t ON a.fk_tipo_activo = t.id_tipo_activo
                    WHERE a.is_ciber = 1 
                    ORDER BY a.id_activo";

            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Error en obtenerEstaciones: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una PC específica por ID
     */
    public function obtenerEstacionPorId(int $activoId): ?array
    {
        try {
            $sql = "SELECT 
                        a.id_activo as id,
                        a.marca,
                        a.descripcion,
                        a.activa,
                        t.nombre_tipo as tipo,
                        CASE 
                            WHEN EXISTS (
                                SELECT 1 FROM sesion_ciber s 
                                WHERE s.fk_activo = a.id_activo 
                                AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            ) THEN 'Ocupada'
                            ELSE 'Disponible'
                        END as estado,
                        (
                            SELECT s.id_sesion 
                            FROM sesion_ciber s 
                            WHERE s.fk_activo = a.id_activo 
                            AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            LIMIT 1
                        ) as sesion_id,
                        (
                            SELECT CONCAT(c.nombre, ' ', c.apellido)
                            FROM sesion_ciber s
                            JOIN clientes c ON s.fk_cliente = c.id_cliente
                            WHERE s.fk_activo = a.id_activo 
                            AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            LIMIT 1
                        ) as cliente_nombre,
                        (
                            SELECT TIMESTAMPDIFF(MINUTE, s.created_at, NOW())
                            FROM sesion_ciber s
                            WHERE s.fk_activo = a.id_activo 
                            AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            LIMIT 1
                        ) as minutos_transcurridos,
                        (
                            SELECT ROUND((TIMESTAMPDIFF(MINUTE, s.created_at, NOW()) / 60) * t.tarifa_hora, 2)
                            FROM sesion_ciber s
                            JOIN tarifas t ON s.fk_tarifa = t.id_tarifa
                            WHERE s.fk_activo = a.id_activo 
                            AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                            LIMIT 1
                        ) as costo_estimado
                    FROM activos a
                    JOIN tipo_activo t ON a.fk_tipo_activo = t.id_tipo_activo
                    WHERE a.id_activo = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$activoId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            error_log('Error en obtenerEstacionPorId: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Inicia una nueva sesión en una PC
     */
    public function iniciarSesion(int $activoId, int $clienteId, int $tarifaId, string $tiempo): array
    {
        if (!$this->pdo) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
        }

        try {
            // Verificar que la PC existe y está disponible
            $sql = "SELECT a.id_activo, 
                           CASE 
                               WHEN EXISTS (
                                   SELECT 1 FROM sesion_ciber s 
                                   WHERE s.fk_activo = a.id_activo 
                                   AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                               ) THEN 1 
                               ELSE 0 
                           END as esta_ocupada
                    FROM activos a
                    WHERE a.id_activo = ? AND a.is_ciber = 1 AND a.activa = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$activoId]);
            $activo = $stmt->fetch();

            if (!$activo) {
                return ['success' => false, 'message' => 'La PC no existe o está desactivada'];
            }

            if ($activo['esta_ocupada']) {
                return ['success' => false, 'message' => 'La PC ya está ocupada'];
            }

            // Insertar la sesión
            $sql = "INSERT INTO sesion_ciber (tiempo_uso, fk_cliente, fk_tarifa, fk_activo, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tiempo, $clienteId, $tarifaId, $activoId]);
            
            $sesionId = $this->pdo->lastInsertId();

            // Obtener datos actualizados de la PC
            $estacionActualizada = $this->obtenerEstacionPorId($activoId);

            return [
                'success' => true,
                'message' => 'Sesión iniciada correctamente',
                'data' => [
                    'sesion_id' => $sesionId,
                    'activo_id' => $activoId,
                    'estacion' => $estacionActualizada
                ]
            ];

        } catch (PDOException $e) {
            error_log('Error en iniciarSesion: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al iniciar la sesión: ' . $e->getMessage()];
        }
    }

    /**
     * Finaliza una sesión (calcula el costo)
     */
    public function finalizarSesion(int $sesionId): array
    {
        if (!$this->pdo) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
        }

        try {
            // Obtener datos de la sesión
            $sql = "SELECT 
                        s.id_sesion,
                        s.fk_activo,
                        s.created_at as hora_inicio,
                        TIME_TO_SEC(s.tiempo_uso) / 60 as minutos_contratados,
                        t.tarifa_hora,
                        (TIME_TO_SEC(s.tiempo_uso) / 3600) * t.tarifa_hora as costo_estimado
                    FROM sesion_ciber s
                    JOIN tarifas t ON s.fk_tarifa = t.id_tarifa
                    WHERE s.id_sesion = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$sesionId]);
            $sesion = $stmt->fetch();

            if (!$sesion) {
                return ['success' => false, 'message' => 'Sesión no encontrada'];
            }

            // Calcular tiempo real usado (desde inicio hasta ahora)
            $horaInicio = new DateTime($sesion['hora_inicio']);
            $horaFin = new DateTime();
            $diferencia = $horaInicio->diff($horaFin);
            $minutosUsados = ($diferencia->days * 24 * 60) + ($diferencia->h * 60) + $diferencia->i;
            
            // Limitar al tiempo contratado
            $minutosContratados = $sesion['minutos_contratados'];
            $minutosFacturar = min($minutosUsados, $minutosContratados);
            
            // Calcular costo real
            $costoReal = round(($minutosFacturar / 60) * $sesion['tarifa_hora'], 2);

            // Actualizar la sesión con el tiempo real usado
            $sql = "UPDATE sesion_ciber 
                    SET tiempo_uso = SEC_TO_TIME(?)
                    WHERE id_sesion = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$minutosFacturar * 60, $sesionId]);

            // Obtener datos actualizados de la PC
            $estacionActualizada = $this->obtenerEstacionPorId($sesion['fk_activo']);

            return [
                'success' => true,
                'message' => 'Sesión finalizada correctamente',
                'data' => [
                    'sesion_id' => $sesionId,
                    'activo_id' => $sesion['fk_activo'],
                    'minutos_usados' => $minutosFacturar,
                    'costo_total' => $costoReal,
                    'estacion' => $estacionActualizada
                ]
            ];

        } catch (PDOException $e) {
            error_log('Error en finalizarSesion: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al finalizar la sesión: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene el historial de sesiones de una PC
     */
    public function obtenerHistorialEstacion(int $activoId, int $limit = 20): array
    {
        if (!$this->pdo) {
            return [];
        }

        try {
            $sql = "SELECT 
                        s.id_sesion,
                        CONCAT(c.nombre, ' ', c.apellido) as cliente_nombre,
                        s.tiempo_uso,
                        s.created_at as hora_inicio,
                        DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) as hora_fin,
                        t.tarifa_hora,
                        ROUND((TIME_TO_SEC(s.tiempo_uso) / 3600) * t.tarifa_hora, 2) as costo_total,
                        CASE 
                            WHEN DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW() 
                            THEN 'activa' 
                            ELSE 'cerrada' 
                        END as estado,
                        TIMESTAMPDIFF(MINUTE, s.created_at, DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND)) as duracion_minutos
                    FROM sesion_ciber s
                    JOIN clientes c ON s.fk_cliente = c.id_cliente
                    JOIN tarifas t ON s.fk_tarifa = t.id_tarifa
                    WHERE s.fk_activo = ?
                    ORDER BY s.created_at DESC
                    LIMIT ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$activoId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Error en obtenerHistorialEstacion: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas del cyber
     */
    public function obtenerEstadisticas(): array
    {
        if (!$this->pdo) {
            return [
                'total' => 0,
                'disponibles' => 0,
                'ocupadas' => 0,
                'mantenimiento' => 0,
                'sesiones_hoy' => 0,
                'ingresos_hoy' => 0
            ];
        }

        try {
            // Total de PCs
            $sql = "SELECT COUNT(*) as total FROM activos WHERE is_ciber = 1 AND activa = 1";
            $total = (int)$this->pdo->query($sql)->fetchColumn();

            // PCs disponibles
            $sql = "SELECT COUNT(*) as disponibles 
                    FROM activos a
                    WHERE a.is_ciber = 1 AND a.activa = 1
                    AND NOT EXISTS (
                        SELECT 1 FROM sesion_ciber s 
                        WHERE s.fk_activo = a.id_activo 
                        AND DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()
                    )";
            $disponibles = (int)$this->pdo->query($sql)->fetchColumn();

            // PCs ocupadas
            $sql = "SELECT COUNT(DISTINCT fk_activo) as ocupadas 
                    FROM sesion_ciber s
                    WHERE DATE_ADD(s.created_at, INTERVAL TIME_TO_SEC(s.tiempo_uso) SECOND) > NOW()";
            $ocupadas = (int)$this->pdo->query($sql)->fetchColumn();

            // PCs en mantenimiento (activas = 0)
            $sql = "SELECT COUNT(*) as mantenimiento 
                    FROM activos 
                    WHERE is_ciber = 1 AND activa = 0";
            $mantenimiento = (int)$this->pdo->query($sql)->fetchColumn();

            // Sesiones de hoy
            $sql = "SELECT COUNT(*) as sesiones_hoy 
                    FROM sesion_ciber 
                    WHERE DATE(created_at) = CURDATE()";
            $sesionesHoy = (int)$this->pdo->query($sql)->fetchColumn();

            // Ingresos de hoy
            $sql = "SELECT COALESCE(SUM((TIME_TO_SEC(s.tiempo_uso) / 3600) * t.tarifa_hora), 0) as ingresos_hoy
                    FROM sesion_ciber s
                    JOIN tarifas t ON s.fk_tarifa = t.id_tarifa
                    WHERE DATE(s.created_at) = CURDATE()";
            $ingresosHoy = (float)$this->pdo->query($sql)->fetchColumn();

            return [
                'total' => $total,
                'disponibles' => $disponibles,
                'ocupadas' => $ocupadas,
                'mantenimiento' => $mantenimiento,
                'sesiones_hoy' => $sesionesHoy,
                'ingresos_hoy' => $ingresosHoy
            ];

        } catch (PDOException $e) {
            error_log('Error en obtenerEstadisticas: ' . $e->getMessage());
            return [
                'total' => 0,
                'disponibles' => 0,
                'ocupadas' => 0,
                'mantenimiento' => 0,
                'sesiones_hoy' => 0,
                'ingresos_hoy' => 0
            ];
        }
    }

    /**
     * Obtiene todos los clientes para el selector
     */
    public function obtenerClientes(): array
    {
        if (!$this->pdo) {
            return [];
        }

        try {
            $sql = "SELECT id_cliente, CONCAT(nombre, ' ', apellido) as nombre_completo 
                    FROM clientes 
                    ORDER BY nombre";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Error en obtenerClientes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las tarifas para el selector
     */
    public function obtenerTarifas(): array
    {
        if (!$this->pdo) {
            return [];
        }

        try {
            $sql = "SELECT id_tarifa, tarifa_hora, precio_tiempo 
                    FROM tarifas 
                    ORDER BY tarifa_hora";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('Error en obtenerTarifas: ' . $e->getMessage());
            return [];
        }
    }
}