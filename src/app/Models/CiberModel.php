<?php
// src/app/Models/CiberModel.php

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
     * Obtiene todas las estaciones con su estado y sesión activa
     * @return array
     */
    public function obtenerEstaciones(): array
    {
        if (!$this->pdo) {
            return [];
        }

        try {
            $sql = "SELECT 
                        ec.id,
                        ec.nombre,
                        ec.estado,
                        ec.especificaciones,
                        ec.tarifa_id,
                        t.nombre as tarifa_nombre,
                        t.precio_por_hora,
                        sc.id as sesion_id,
                        sc.cliente_nombre,
                        sc.hora_inicio,
                        sc.usuario_id,
                        u.nombre as usuario_nombre,
                        TIMESTAMPDIFF(MINUTE, sc.hora_inicio, NOW()) as minutos_transcurridos,
                        CASE 
                            WHEN sc.hora_inicio IS NOT NULL THEN 
                                ROUND(TIMESTAMPDIFF(MINUTE, sc.hora_inicio, NOW()) / 60.0 * t.precio_por_hora, 2)
                            ELSE NULL
                        END as costo_estimado
                    FROM estaciones_cyber ec
                    LEFT JOIN tarifas_cyber t ON ec.tarifa_id = t.id
                    LEFT JOIN sesiones_cyber sc ON ec.id = sc.estacion_id AND sc.estado = 'activa'
                    LEFT JOIN usuarios u ON sc.usuario_id = u.id
                    ORDER BY ec.nombre";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log('Error en obtenerEstaciones: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Inicia una sesión en una estación
     * @param int $estacionId ID de la estación
     * @param int $usuarioId ID del usuario que inicia la sesión
     * @param string $clienteNombre Nombre del cliente
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function iniciarSesion(int $estacionId, int $usuarioId, string $clienteNombre): array
    {
        if (!$this->pdo) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
        }

        try {
            // Verificar que la estación existe y está disponible
            $sql = "SELECT id, estado, tarifa_id FROM estaciones_cyber WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$estacionId]);
            $estacion = $stmt->fetch();

            if (!$estacion) {
                return ['success' => false, 'message' => 'La estación no existe'];
            }

            if ($estacion['estado'] !== 'Disponible') {
                return ['success' => false, 'message' => 'La estación no está disponible'];
            }

            // Iniciar transacción
            $this->pdo->beginTransaction();

            // Crear la sesión
            $sql = "INSERT INTO sesiones_cyber 
                    (estacion_id, usuario_id, cliente_nombre, tarifa_id, hora_inicio, estado) 
                    VALUES (?, ?, ?, ?, NOW(), 'activa')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$estacionId, $usuarioId, $clienteNombre, $estacion['tarifa_id']]);
            
            $sesionId = $this->pdo->lastInsertId();

            // Actualizar el estado de la estación
            $sql = "UPDATE estaciones_cyber SET estado = 'Ocupada', updated_at = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$estacionId]);

            // Confirmar transacción
            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Sesión iniciada correctamente',
                'data' => [
                    'sesion_id' => $sesionId,
                    'estacion_id' => $estacionId,
                    'estado' => 'Ocupada',
                    'cliente_nombre' => $clienteNombre,
                    'hora_inicio' => date('Y-m-d H:i:s')
                ]
            ];

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('Error en iniciarSesion: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al iniciar la sesión: ' . $e->getMessage()];
        }
    }

    /**
     * Finaliza una sesión en una estación
     * @param int $sesionId ID de la sesión a finalizar
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function finalizarSesion(int $sesionId): array
    {
        if (!$this->pdo) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
        }

        try {
            // Verificar que la sesión existe y está activa
            $sql = "SELECT sc.id, sc.estacion_id, sc.hora_inicio, t.precio_por_hora 
                    FROM sesiones_cyber sc
                    INNER JOIN estaciones_cyber ec ON sc.estacion_id = ec.id
                    INNER JOIN tarifas_cyber t ON ec.tarifa_id = t.id
                    WHERE sc.id = ? AND sc.estado = 'activa'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$sesionId]);
            $sesion = $stmt->fetch();

            if (!$sesion) {
                return ['success' => false, 'message' => 'La sesión no existe o ya está cerrada'];
            }

            // Calcular duración y costo
            $horaInicio = new DateTime($sesion['hora_inicio']);
            $horaFin = new DateTime();
            $diferencia = $horaInicio->diff($horaFin);
            $minutos = ($diferencia->days * 24 * 60) + ($diferencia->h * 60) + $diferencia->i;
            
            $precioPorHora = floatval($sesion['precio_por_hora']);
            $costo = round(($minutos / 60) * $precioPorHora, 2);

            // Iniciar transacción
            $this->pdo->beginTransaction();

            // Actualizar la sesión
            $sql = "UPDATE sesiones_cyber 
                    SET hora_fin = NOW(), 
                        costo_total = ?, 
                        estado = 'cerrada',
                        updated_at = NOW()
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$costo, $sesionId]);

            // Liberar la estación
            $sql = "UPDATE estaciones_cyber 
                    SET estado = 'Disponible', updated_at = NOW() 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$sesion['estacion_id']]);

            // Confirmar transacción
            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Sesión finalizada correctamente',
                'data' => [
                    'sesion_id' => $sesionId,
                    'estacion_id' => $sesion['estacion_id'],
                    'duracion_minutos' => $minutos,
                    'costo_total' => $costo,
                    'hora_fin' => date('Y-m-d H:i:s'),
                    'estado' => 'Disponible'
                ]
            ];

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('Error en finalizarSesion: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al finalizar la sesión: ' . $e->getMessage()];
        }
    }

    /**
     * Obtiene el historial de sesiones de una estación
     * @param int $estacionId ID de la estación
     * @param int $limit Límite de registros
     * @return array
     */
    public function obtenerHistorialEstacion(int $estacionId, int $limit = 10): array
    {
        if (!$this->pdo) {
            return [];
        }

        try {
            $sql = "SELECT 
                        id,
                        cliente_nombre,
                        hora_inicio,
                        hora_fin,
                        costo_total,
                        estado,
                        TIMESTAMPDIFF(MINUTE, hora_inicio, hora_fin) as duracion_minutos
                    FROM sesiones_cyber
                    WHERE estacion_id = ?
                    ORDER BY hora_inicio DESC
                    LIMIT ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$estacionId, $limit]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Error en obtenerHistorialEstacion: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas del cyber
     * @return array
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
            // Estadísticas de estaciones
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN estado = 'Disponible' THEN 1 ELSE 0 END) as disponibles,
                        SUM(CASE WHEN estado = 'Ocupada' THEN 1 ELSE 0 END) as ocupadas,
                        SUM(CASE WHEN estado = 'Mantenimiento' THEN 1 ELSE 0 END) as mantenimiento
                    FROM estaciones_cyber";
            $stmt = $this->pdo->query($sql);
            $estadisticas = $stmt->fetch();

            // Sesiones de hoy
            $sql = "SELECT 
                        COUNT(*) as sesiones_hoy,
                        COALESCE(SUM(costo_total), 0) as ingresos_hoy
                    FROM sesiones_cyber
                    WHERE DATE(hora_inicio) = CURDATE()";
            $stmt = $this->pdo->query($sql);
            $hoy = $stmt->fetch();

            return array_merge($estadisticas, $hoy);

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
}