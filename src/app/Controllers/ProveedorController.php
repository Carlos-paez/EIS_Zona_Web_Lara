<?php
// =============================================================================
// CONTROLADOR ProveedorController (API JSON para solicitudes a proveedores)
// =============================================================================
// Propósito: Manejar las peticiones AJAX del módulo de proveedores.
//            Gestiona solicitudes de compra (órdenes), sus líneas de detalle,
//            y los catálogos de proveedores, productos y estados.
//            Responde siempre en formato JSON.
// =============================================================================

// Declara el espacio de nombres al que pertenece esta clase, siguiendo la estructura PSR-4
namespace App\Controllers;

// Importa el modelo Proveedor para acceder a los datos de órdenes, proveedores y productos
use App\Models\Proveedor;

/**
 * Controlador de proveedores y solicitudes de compra (API JSON)
 * 
 * Maneja todas las peticiones AJAX del módulo de proveedores.
 * Proporciona acciones CRUD para solicitudes de compra (órdenes),
 * gestión de líneas de detalle de cada orden, y consulta de
 * catálogos de proveedores, productos y estados.
 * Todas las respuestas se devuelven en formato JSON.
 */
class ProveedorController
{
    /**
     * Instancia del modelo Proveedor
     * 
     * Almacena el objeto del modelo que proporciona los métodos
     * para consultar y modificar órdenes, proveedores, productos,
     * líneas de detalle y estados en la base de datos.
     */
    private Proveedor $model;

    /**
     * Constructor de la clase ProveedorController
     * 
     * Inicializa la propiedad $model creando una nueva instancia
     * del modelo Proveedor para acceder a los datos del módulo.
     */
    public function __construct()
    {
        // Crea una nueva instancia del modelo Proveedor y la asigna a la propiedad $model
        $this->model = new Proveedor();
    }

    /**
     * Método principal que despacha las acciones según el parámetro GET 'action'
     * 
     * Establece el encabezado Content-Type como application/json.
     * Lee el parámetro 'action' de la URL y utiliza match() de PHP 8
     * para ejecutar la acción solicitada (listar, crear, detalle, etc.).
     * Captura y maneja excepciones de base de datos y errores genéricos.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    public function handle(): void
    {
        // Establece el encabezado HTTP Content-Type para indicar respuesta JSON
        header('Content-Type: application/json');

        // Lee el parámetro GET 'action' de la URL, o cadena vacía si no está presente
        $action = $_GET['action'] ?? '';

        // Bloque try-catch para manejo controlado de errores
        try {
            // Utiliza match() de PHP 8 para seleccionar el método según la acción solicitada
            match ($action) {
                // Lista todas las solicitudes de compra (órdenes)
                'listar'        => $this->listar(),
                // Obtiene indicadores clave (KPIs) de solicitudes
                'kpis'          => $this->kpis(),
                // Obtiene el detalle de una solicitud específica por ID
                'detalle'       => $this->detalle(),
                // Obtiene el catálogo de proveedores disponibles
                'proveedores'   => $this->proveedores(),
                // Obtiene el catálogo de productos disponibles
                'productos'     => $this->productos(),
                // Obtiene los estados posibles para las solicitudes
                'statuses'      => $this->statuses(),
                // Crea una nueva solicitud de compra
                'crear'         => $this->crear(),
                // Actualiza una solicitud de compra existente
                'actualizar'    => $this->actualizar(),
                // Elimina una solicitud de compra
                'eliminar'      => $this->eliminar(),
                // Obtiene las líneas de detalle de una orden específica
                'lineas'        => $this->lineas(),
                // Agrega una línea de producto a una orden
                'agregarLinea'  => $this->agregarLinea(),
                // Elimina una línea de detalle de una orden
                'eliminarLinea' => $this->eliminarLinea(),
                // Si la acción no coincide con ninguna, devuelve error JSON
                default         => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            // Captura excepciones de PDO (base de datos) y devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            // Captura cualquier otra excepción y devuelve JSON con error genérico
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Lista todas las solicitudes de compra (órdenes)
     * 
     * Obtiene el listado completo de órdenes de compra desde el modelo
     * y las devuelve en una respuesta JSON.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function listar(): void
    {
        // Llama al método obtenerOrdenes() del modelo para traer todas las órdenes
        $ordenes = $this->model->obtenerOrdenes();
        // Devuelve un JSON con indicador de éxito y el arreglo de órdenes en 'data'
        echo json_encode(['success' => true, 'data' => $ordenes]);
    }

    /**
     * Obtiene los KPIs (indicadores clave) de las solicitudes de compra
     * 
     * Consulta al modelo el conteo de solicitudes agrupadas por estado
     * (pendientes, recibidas, canceladas) y también el total de solicitudes
     * y la cantidad de proveedores registrados.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function kpis(): void
    {
        // Obtiene del modelo un arreglo con el conteo de solicitudes por estado
        $porEstado = $this->model->contarPorEstado();
        // Inicializa el contador de solicitudes pendientes en 0
        $pendientes = 0;
        // Inicializa el contador de solicitudes recibidas en 0
        $recibidas = 0;
        // Inicializa el contador de solicitudes canceladas en 0
        $canceladas = 0;
        // Itera sobre cada fila del resultado de conteo por estado
        foreach ($porEstado as $row) {
            // Convierte el nombre del estado a minúsculas para comparación insensible a mayúsculas
            $est = strtolower($row['estado']);
            // Si el estado contiene 'pendiente' o 'pend', suma el total al contador de pendientes
            if ($est === 'pendiente' || str_contains($est, 'pend')) $pendientes = (int)$row['total'];
            // Si el estado contiene 'recibida' o 'recib', suma el total al contador de recibidas
            elseif ($est === 'recibida' || str_contains($est, 'recib')) $recibidas = (int)$row['total'];
            // Si el estado contiene 'cancelada' o 'cancel', suma el total al contador de canceladas
            elseif ($est === 'cancelada' || str_contains($est, 'cancel')) $canceladas = (int)$row['total'];
        }
        // Devuelve un JSON con los KPIs calculados
        echo json_encode([
            'success' => true,
            'data' => [
                // Total de solicitudes de compra registradas
                'total'      => $this->model->totalSolicitudes(),
                // Cantidad de solicitudes en estado pendiente
                'pendientes' => $pendientes,
                // Cantidad de solicitudes en estado recibida
                'recibidas'  => $recibidas,
                // Cantidad total de proveedores registrados en el sistema
                'proveedores' => $this->model->totalProveedores(),
            ]
        ]);
    }

    /**
     * Obtiene el detalle de una solicitud de compra por su ID
     * 
     * Lee el parámetro GET 'id', valida que sea un entero positivo,
     * consulta la orden en el modelo y devuelve sus datos completos.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function detalle(): void
    {
        // Lee el parámetro GET 'id' y lo convierte a entero; si no existe, usa 0
        $id = (int)($_GET['id'] ?? 0);
        // Verifica si el ID es válido (distinto de cero)
        if (!$id) {
            // Si el ID no es válido, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            // Detiene la ejecución del método
            return;
        }
        // Llama al método obtenerOrdenPorId() del modelo para buscar la orden por su ID
        $orden = $this->model->obtenerOrdenPorId($id);
        // Verifica si se encontró una orden con ese ID
        if ($orden) {
            // Si existe, devuelve un JSON con éxito y los datos de la orden
            echo json_encode(['success' => true, 'data' => $orden]);
        } else {
            // Si no existe, devuelve JSON con mensaje de error
            echo json_encode(['success' => false, 'error' => 'Solicitud no encontrada']);
        }
    }

    /**
     * Obtiene el catálogo de proveedores disponibles
     * 
     * Consulta al modelo la lista de todos los proveedores
     * registrados en el sistema.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function proveedores(): void
    {
        // Llama al método obtenerProveedores() del modelo para traer todos los proveedores
        $proveedores = $this->model->obtenerProveedores();
        // Devuelve un JSON con el listado de proveedores
        echo json_encode(['success' => true, 'data' => $proveedores]);
    }

    /**
     * Obtiene el catálogo de productos disponibles
     * 
     * Consulta al modelo la lista de todos los productos
     * que pueden ser solicitados a proveedores.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function productos(): void
    {
        // Llama al método obtenerProductos() del modelo para traer todos los productos
        $productos = $this->model->obtenerProductos();
        // Devuelve un JSON con el listado de productos
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    /**
     * Obtiene los estados posibles para las solicitudes de compra
     * 
     * Consulta al modelo los estados disponibles (pendiente, recibida, cancelada, etc.)
     * que pueden asignarse a las órdenes de compra.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function statuses(): void
    {
        // Llama al método obtenerStatuses() del modelo para obtener los estados
        $statuses = $this->model->obtenerStatuses();
        // Devuelve un JSON con el listado de estados
        echo json_encode(['success' => true, 'data' => $statuses]);
    }

    /**
     * Crea una nueva solicitud de compra (orden)
     * 
     * Lee los datos del formulario desde POST: número de orden,
     * fecha, ID del proveedor e ID del estado. Valida los campos
     * obligatorios y llama al modelo para insertar la orden.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function crear(): void
    {
        // Lee el número de orden desde el formulario POST
        $numero       = $_POST['numero'] ?? '';
        // Lee la fecha desde POST; si no se envía, usa la fecha actual en formato YYYY-MM-DD
        $fecha        = $_POST['fecha'] ?? date('Y-m-d');
        // Lee el ID del proveedor y lo convierte a entero
        $fk_proveedor = (int)($_POST['fk_proveedor'] ?? 0);
        // Lee el ID del estado y lo convierte a entero
        $fk_status    = (int)($_POST['fk_status'] ?? 0);

        // Valida que los campos obligatorios estén completos: número, proveedor y estado
        if (empty($numero) || !$fk_proveedor || !$fk_status) {
            // Si falta algún campo, devuelve JSON con mensaje de error
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            // Detiene la ejecución del método
            return;
        }

        // Llama al método crearOrden() del modelo para insertar la orden en la base de datos
        $resultado = $this->model->crearOrden($numero, $fecha, $fk_proveedor, $fk_status);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Solicitud creada exitosamente']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al crear la solicitud']
        );
    }

    /**
     * Actualiza una solicitud de compra existente
     * 
     * Lee todos los campos del formulario POST incluyendo el ID de la orden.
     * Valida que el ID y los campos obligatorios estén presentes antes
     * de llamar al modelo para actualizar el registro.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function actualizar(): void
    {
        // Lee el ID de la orden y lo convierte a entero
        $id           = (int)($_POST['id'] ?? 0);
        // Lee el número de orden desde POST
        $numero       = $_POST['numero'] ?? '';
        // Lee la fecha desde POST
        $fecha        = $_POST['fecha'] ?? '';
        // Lee el ID del proveedor y lo convierte a entero
        $fk_proveedor = (int)($_POST['fk_proveedor'] ?? 0);
        // Lee el ID del estado y lo convierte a entero
        $fk_status    = (int)($_POST['fk_status'] ?? 0);

        // Valida que el ID, número, proveedor y estado sean válidos
        if (!$id || empty($numero) || !$fk_proveedor || !$fk_status) {
            // Si falta algún campo obligatorio, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            // Detiene la ejecución del método
            return;
        }

        // Llama al método actualizarOrden() del modelo para modificar la orden
        $resultado = $this->model->actualizarOrden($id, $numero, $fecha, $fk_proveedor, $fk_status);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Solicitud actualizada exitosamente']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al actualizar la solicitud']
        );
    }

    /**
     * Elimina una solicitud de compra
     * 
     * Lee el ID de la orden desde POST, valida que sea un número
     * positivo, y llama al modelo para eliminar la orden de la
     * base de datos.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function eliminar(): void
    {
        // Lee el ID de la orden y lo convierte a entero; si no existe, usa 0
        $id = (int)($_POST['id'] ?? 0);
        // Verifica si el ID es válido (distinto de cero)
        if (!$id) {
            // Si el ID no es válido, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            // Detiene la ejecución del método
            return;
        }
        // Llama al método eliminarOrden() del modelo para borrar la orden
        $resultado = $this->model->eliminarOrden($id);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Solicitud eliminada exitosamente']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al eliminar la solicitud']
        );
    }

    /**
     * Obtiene las líneas de detalle de una orden específica
     * 
     * Lee el ID de la orden desde GET, valida que sea válido,
     * y consulta al modelo las líneas de detalle (productos,
     * cantidades, precios) junto con los datos de la orden padre.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function lineas(): void
    {
        // Lee el ID de la orden desde GET y lo convierte a entero
        $orden_id = (int)($_GET['orden_id'] ?? 0);
        // Verifica si el ID de orden es válido
        if (!$orden_id) {
            // Si no es válido, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'ID de orden no válido']);
            // Detiene la ejecución del método
            return;
        }
        // Obtiene las líneas de detalle de la orden desde el modelo
        $lineas = $this->model->obtenerLineas($orden_id);
        // Obtiene los datos de la orden padre para contexto
        $orden = $this->model->obtenerOrdenPorId($orden_id);
        // Devuelve un JSON con las líneas y la información de la orden
        echo json_encode(['success' => true, 'data' => ['lineas' => $lineas, 'orden' => $orden]]);
    }

    /**
     * Agrega una línea de producto a una solicitud de compra
     * 
     * Lee el ID de la orden, ID del producto, cantidad y precio
     * desde POST. Valida que todos los datos sean positivos y
     * llama al modelo para insertar la línea de detalle.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function agregarLinea(): void
    {
        // Lee el ID de la orden desde POST y lo convierte a entero
        $orden_id   = (int)($_POST['orden_id'] ?? 0);
        // Lee el ID del producto desde POST y lo convierte a entero
        $producto_id = (int)($_POST['producto_id'] ?? 0);
        // Lee la cantidad solicitada y la convierte a entero
        $cantidad   = (int)($_POST['cantidad'] ?? 0);
        // Lee el precio unitario y lo convierte a flotante
        $precio     = (float)($_POST['precio'] ?? 0);

        // Valida que todos los campos sean válidos: IDs positivos, cantidad y precio mayores a cero
        if (!$orden_id || !$producto_id || $cantidad <= 0 || $precio <= 0) {
            // Si algún dato no es válido, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'Datos de línea no válidos']);
            // Detiene la ejecución del método
            return;
        }

        // Llama al método agregarLinea() del modelo para insertar el detalle
        $resultado = $this->model->agregarLinea($orden_id, $producto_id, $cantidad, $precio);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Producto agregado a la solicitud']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al agregar producto']
        );
    }

    /**
     * Elimina una línea de detalle de una solicitud de compra
     * 
     * Lee el ID de la línea desde POST, valida que sea un número
     * positivo, y llama al modelo para eliminar el registro
     * de la base de datos.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function eliminarLinea(): void
    {
        // Lee el ID de la línea desde POST y lo convierte a entero
        $id = (int)($_POST['id'] ?? 0);
        // Verifica si el ID es válido (distinto de cero)
        if (!$id) {
            // Si el ID no es válido, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            // Detiene la ejecución del método
            return;
        }
        // Llama al método eliminarLinea() del modelo para borrar la línea de detalle
        $resultado = $this->model->eliminarLinea($id);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Línea eliminada']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al eliminar línea']
        );
    }

    /**
     * Método auxiliar para construir respuestas JSON uniformes
     * 
     * Crea un arreglo asociativo con la estructura estándar de respuesta.
     * Siempre incluye la clave 'success'. Opcionalmente agrega las
     * claves 'data' y 'error' según los parámetros proporcionados.
     *
     * @param bool   $success Indica si la operación fue exitosa o no
     * @param mixed  $data    Datos opcionales a incluir en la respuesta (puede ser null)
     * @param string $error   Mensaje de error opcional (cadena vacía si no hay error)
     * 
     * @return void Responde directamente con echo en formato JSON
     */
    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        // Crea el arreglo base con el indicador de éxito
        $result = ['success' => $success];
        // Si se proporcionaron datos (no es null), los agrega al arreglo bajo la clave 'data'
        if ($data !== null) $result['data'] = $data;
        // Si hay un mensaje de error (no es cadena vacía), lo agrega bajo la clave 'error'
        if ($error) $result['error'] = $error;
        // Codifica el arreglo como JSON y lo envía como respuesta HTTP
        echo json_encode($result);
    }
}
