<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\Validator;
use App\Models\Asesoria;

/**
 * Class AsesoriaController
 *
 * Controlador encargado de administrar el flujo de operaciones del módulo de Asesorías.
 * Actúa como intermediario entre la capa de presentación (vistas/peticiones cliente)
 * y la capa de datos (modelo Asesoria), manejando serialización JSON y gestión de errores.
 *
 * @package App\Controllers
 */
class AsesoriaController
{
    /**
     * Instancia del modelo de datos para la entidad Asesoria.
     *
     * @var Asesoria
     */
    private Asesoria $model;

    /**
     * Constructor de la clase.
     * Inicializa la propiedad del modelo instanciando la clase Asesoria por defecto.
     */
    public function __construct()
    {
        $this->model = new Asesoria();
    }

    /**
     * Obtiene la instancia actual del modelo de datos.
     *
     * @return Asesoria Retorna la instancia del modelo Asesoria.
     */
    public function getModel(): Asesoria
    {
        return $this->model;
    }

    /**
     * Establece o reemplaza la instancia del modelo de datos.
     * Facilita la inyección de dependencias y el uso de Mocks durante pruebas unitarias.
     *
     * @param Asesoria $model Instancia del modelo Asesoria a inyectar.
     * @return void
     */
    public function setModel(Asesoria $model): void
    {
        $this->model = $model;
    }

    /**
     * Despachador principal de peticiones (Request Handler / Router Interno).
     *
     * Configura la cabecera HTTP de respuesta a JSON, evalúa la acción solicitada
     * proveniente del parámetro GET 'action' mediante expresión `match` y captura
     * excepciones globales para evitar la interrupción abrupta del script.
     *
     * @return void
     */
    public function handle(): void
    {
        // Establece el encabezado Content-Type para asegurar respuestas en formato JSON UTF-8
        header('Content-Type: application/json');

        // Captura la acción pasada por URL; si no existe, asigna una cadena vacía
        $action = $_GET['action'] ?? '';

        try {
            // Mapea la acción enviada hacia su respectivo método dentro del controlador
            match ($action) {
                'listar'     => $this->listar(),
                'detalle'    => $this->detalle(),
                'buscar'     => $this->buscar(),
                'crear'      => $this->crear(),
                'actualizar' => $this->actualizar(),
                'eliminar'   => $this->eliminar(),
                'kpis'       => $this->kpis(),
                default      => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            // Captura fallos a nivel de base de datos, los registra en el log y responde con error genérico
            Logger::error($e, 'Asesorías - consulta SQL');
            $this->json(false, null, 'Error de base de datos');
        } catch (\InvalidArgumentException $e) {
            // Captura fallos lanzados por validaciones de entrada de datos y retorna el mensaje
            $this->json(false, null, $e->getMessage());
        } catch (\Exception $e) {
            // Captura cualquier otro tipo de excepción no controlada a nivel de servidor
            Logger::error($e, 'Asesorías');
            $this->json(false, null, 'Error interno del servidor');
        }
    }

    /**
     * Recupera y emite en formato JSON el listado completo de asesorías.
     *
     * @return void
     */
    private function listar(): void
    {
        // Consulta todos los registros almacenados en la base de datos
        $asesorias = $this->model->obtenerTodas();
        
        // Emite los datos recuperados con bandera de éxito en true
        $this->json(true, $asesorias);
    }

    /**
     * Recopila, procesa y emite los indicadores clave de rendimiento (KPIs).
     *
     * Agrupa el total de asesorías registradas y cuantifica el volumen
     * correspondiente según su estado operativo ('Permitido' / 'Denegado').
     *
     * @return void
     */
    private function kpis(): void
    {
        // Obtiene el universo total de registros y el desglose por estado desde el modelo
        $total = $this->model->obtenerTodas();
        $porEstado = $this->model->contarPorEstado();

        // Inicialización de contadores estáticos
        $perm = 0;
        $den  = 0;

        // Recorre los resultados agregados devueltos por la base de datos
        foreach ($porEstado as $fila) {
            // Acumula el conteo de registros permitidos
            if (($fila['estado'] ?? '') === 'Permitido') {
                $perm = (int)$fila['total'];
            // Acumula el conteo de registros denegados
            } elseif (($fila['estado'] ?? '') === 'Denegado') {
                $den = (int)$fila['total'];
            }
        }

        // Responde con el objeto formateado conteniendo los totales procesados
        $this->json(true, [
            'total'      => count($total),
            'permitidas' => $perm,
            'derivadas'  => $den,
        ]);
    }

    /**
     * Muestra la información detallada de una asesoría específica identificada por su ID.
     *
     * @return void
     */
    private function detalle(): void
    {
        // Sanea y valida que el parámetro 'id' enviado por GET sea un identificador entero válido
        $id = Validator::id($_GET['id'] ?? null, 'ID de la asesoría');
        
        // Busca el registro coincidente en el modelo
        $asesoria = $this->model->obtenerPorId($id);

        // Evalúa la existencia de la entidad y retorna respuesta adecuada
        if ($asesoria) {
            $this->json(true, $asesoria);
        } else {
            $this->json(false, null, 'Asesoría no encontrada');
        }
    }

    /**
     * Busca y filtra registros de asesorías según el número de cédula del ciudadano.
     *
     * @return void
     */
    private function buscar(): void
    {
        // Obtiene y limpia espacios en blanco del parámetro 'cedula' enviado por GET
        $cedula = trim($_GET['cedula'] ?? '');
        
        // Si el término de búsqueda está vacío, retorna un arreglo de resultados vacío
        if ($cedula === '') {
            $this->json(true, []);
            return;
        }

        // Valida la cédula contra una expresión regular definida en la clase Validator
        if (!preg_match(Validator::PATTERN_CEDULA, $cedula)) {
            $this->json(false, null, 'La cédula no tiene un formato válido');
            return;
        }

        // Realiza la búsqueda mediante el modelo y devuelve las coincidencias encontradas
        $resultados = $this->model->buscarPorCedula($cedula);
        $this->json(true, $resultados);
    }

    /**
     * Procesa la inserción de un nuevo registro de asesoría en el sistema.
     *
     * Realiza las comprobaciones de seguridad (CSRF), verifica campos obligatorios,
     * ejecuta la sanitización/validación de los datos e invoca al modelo para persistir.
     *
     * @return void
     */
    private function crear(): void
    {
        // Verifica la validez del token CSRF recibido vía POST para prevenir ataques Cross-Site Request Forgery
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->json(false, null, 'Token de seguridad inválido');
            return;
        }

        // Comprueba que los campos requeridos estén presentes dentro de la superglobal $_POST
        $errores = Validator::requeridos($_POST, [
            'ciudadano' => 'ciudadano',
            'cedula'    => 'cédula',
            'documento' => 'tipo de documento',
        ]);

        // Si existen omisiones en los campos obligatorios, retorna los errores detallados por campo
        if ($errores) {
            $this->jsonResponse(false, null, 'Completa todos los campos obligatorios.', $errores);
            return;
        }

        // Saneamiento y validación estricta de cada campo individual antes de guardar
        $ciudadano   = Validator::texto($_POST['ciudadano'] ?? null, 'ciudadano', ['required' => true, 'min' => 2, 'max' => 200, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El ciudadano contiene caracteres no permitidos']);
        $cedula      = Validator::cedula($_POST['cedula'] ?? null, 'cédula');
        $documento   = Validator::texto($_POST['documento'] ?? null, 'tipo de documento', ['required' => true, 'min' => 1, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El tipo de documento contiene caracteres no permitidos']);
        $descripcion = Validator::texto($_POST['descripcion'] ?? null, 'descripción', ['required' => false, 'max' => 1000]);
        $direccion   = Validator::texto($_POST['direccion'] ?? null, 'dirección', ['required' => false, 'max' => 500]);
        $telefono    = Validator::telefono($_POST['telefono'] ?? null, 'teléfono');

        // Solicita al modelo la inserción del nuevo registro con los valores ya procesados
        $resultado = $this->model->crear($ciudadano, $cedula, $documento, $descripcion, $direccion, $telefono);

        // Notifica el resultado final de la operación de inserción
        if ($resultado) {
            $this->jsonResponse(true, null, 'Asesoría registrada exitosamente');
        } else {
            $this->json(false, null, 'Error al registrar la asesoría');
        }
    }

    /**
     * Procesa la actualización de los datos editables de una asesoría existente.
     *
     * Valida el token CSRF, confirma los parámetros de identificación y actualiza
     * los valores permitidos a través del modelo.
     *
     * @return void
     */
    private function actualizar(): void
    {
        // Validación del token de seguridad CSRF
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->json(false, null, 'Token de seguridad inválido');
            return;
        }

        // Validación de parámetros obligatorios requeridos para la edición
        $errores = Validator::requeridos($_POST, [
            'id'        => 'ID de la asesoría',
            'documento' => 'tipo de documento',
        ]);

        if ($errores) {
            $this->jsonResponse(false, null, 'Completa todos los campos obligatorios.', $errores);
            return;
        }

        // Extracción, saneamiento y validación de los datos recibidos vía POST
        $id          = Validator::id($_POST['id'] ?? null, 'ID de la asesoría');
        $documento   = Validator::texto($_POST['documento'] ?? null, 'tipo de documento', ['required' => true, 'min' => 1, 'max' => 100, 'pattern' => Validator::PATTERN_TEXTO_LIBRE, 'patternMessage' => 'El tipo de documento contiene caracteres no permitidos']);
        $descripcion = Validator::texto($_POST['descripcion'] ?? null, 'descripción', ['required' => false, 'max' => 1000]);

        // Ejecuta la modificación en el modelo pasando el ID de la entidad
        $resultado = $this->model->actualizar($id, $documento, $descripcion);

        // Retorna respuesta de confirmación o fallo según corresponda
        if ($resultado) {
            $this->jsonResponse(true, null, 'Asesoría actualizada exitosamente');
        } else {
            $this->json(false, null, 'Error al actualizar la asesoría');
        }
    }

    /**
     * Procesa la eliminación física/lógica de un registro de asesoría de la base de datos.
     *
     * @return void
     */
    private function eliminar(): void
    {
        // Verifica la autenticidad de la petición mediante validación del token CSRF
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->json(false, null, 'Token de seguridad inválido');
            return;
        }

        // Obtiene y valida el identificador único del registro a borrar
        $id = Validator::id($_POST['id'] ?? null, 'ID de la asesoría');
        
        // Invoca el método de borrado dentro de la capa del modelo
        $resultado = $this->model->eliminar($id);

        // Retorna el estatus final del proceso de eliminación
        if ($resultado) {
            $this->jsonResponse(true, null, 'Asesoría eliminada exitosamente');
        } else {
            $this->json(false, null, 'Error al eliminar la asesoría');
        }
    }

    /**
     * Imprime una respuesta estandarizada en formato JSON (utilizada para consultas/lecturas).
     *
     * @param bool   $success Indica el éxito (true) o fallo (false) de la solicitud.
     * @param mixed  $data    (Opcional) Datos o payload de respuesta a enviar al cliente.
     * @param string $error   (Opcional) Mensaje descriptivo en caso de error.
     * @return void
     */
    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        // Estructura el payload básico
        $result = ['success' => $success];
        
        // Agrega la clave 'data' únicamente si contiene información explícita
        if ($data !== null) $result['data'] = $data;
        
        // Agrega la clave 'error' si se especificó un mensaje de fallo
        if ($error !== '') $result['error'] = $error;
        
        // Convierte el arreglo asociativo a string JSON e imprime el resultado
        echo json_encode($result);
    }

    /**
     * Imprime una respuesta estandarizada avanzada en JSON (utilizada para mutaciones o acciones).
     *
     * Soporta la inclusión de mensajes descriptivos clave ('message'/'error') y
     * un array de errores mapeados campo por campo para validaciones en formularios UI.
     *
     * @param bool   $success     Indica el éxito (true) o fallo (false) de la operación.
     * @param mixed  $data        (Opcional) Payload o datos devueltos.
     * @param string $message     (Opcional) Texto informativo principal de la respuesta.
     * @param array  $fieldErrors (Opcional) Mapeo asociativo de errores por nombre de campo.
     * @return void
     */
    private function jsonResponse(bool $success, mixed $data = null, string $message = '', array $fieldErrors = []): void
    {
        // Asigna el estado inicial de la respuesta
        $result = ['success' => $success];
        
        // Añade la carga útil si no es nula
        if ($data !== null) $result['data'] = $data;
        
        // Define la clave 'message' en caso de éxito o 'error' en caso de fallar
        if ($message !== '') $result[$success ? 'message' : 'error'] = $message;
        
        // Asigna el desglose de errores específicos por input si existen
        if (!empty($fieldErrors)) $result['fieldErrors'] = $fieldErrors;
        
        // Codifica a JSON e imprime en pantalla
        echo json_encode($result);
    }
}