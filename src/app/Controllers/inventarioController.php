<?php
// =============================================================================
// CONTROLADOR InventarioController (API JSON para inventario)
// =============================================================================
// Propósito: Manejar las peticiones AJAX del módulo de inventario.
//            Responde siempre en formato JSON. Cada acción se define mediante
//            el parámetro GET 'action' (listar, crear, editar, eliminar, etc.).
// =============================================================================

// Declara el espacio de nombres al que pertenece esta clase, siguiendo la estructura PSR-4
namespace App\Controllers;

// Importa el modelo Inventario para acceder a los datos de productos, stock y categorías
use App\Models\Inventario;

/**
 * Controlador de inventario (API JSON)
 * 
 * Maneja todas las peticiones AJAX del módulo de inventario.
 * Cada método privado corresponde a una acción específica que se
 * selecciona mediante el parámetro GET 'action'.
 * Todas las respuestas se devuelven en formato JSON.
 */
class InventarioController
{
    /**
     * Instancia del modelo Inventario
     * 
     * Almacena el objeto del modelo que proporciona los métodos
     * para consultar y modificar productos, categorías y KPIs
     * en la base de datos.
     */
    private Inventario $model;

    /**
     * Constructor de la clase InventarioController
     * 
     * Inicializa la propiedad $model creando una nueva instancia
     * del modelo Inventario para acceder a los datos del inventario.
     */
    public function __construct()
    {
        // Crea una nueva instancia del modelo Inventario y la asigna a la propiedad $model
        $this->model = new Inventario();
    }

    /**
     * Método principal que despacha las acciones según el parámetro GET 'action'
     * 
     * Establece el encabezado Content-Type como application/json para todas las respuestas.
     * Lee el parámetro 'action' de la URL y utiliza la estructura match() de PHP 8
     * para ejecutar el método correspondiente. Si ocurre una excepción, captura el error
     * y devuelve una respuesta JSON con el mensaje de error.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    public function handle(): void
    {
        // Establece el encabezado HTTP Content-Type para indicar que la respuesta será JSON
        header('Content-Type: application/json');

        // Lee el parámetro GET 'action' de la URL, o cadena vacía si no está presente
        $action = $_GET['action'] ?? '';

        // Bloque try-catch para manejar errores de forma controlada
        try {
            // Utiliza match() de PHP 8 (similar a switch pero con comparación estricta)
            // para ejecutar el método correspondiente según el valor de $action
            match ($action) {
                // Si action es 'listar', llama al método listar() para obtener todos los productos
                'listar'      => $this->listar(),
                // Si action es 'kpis', llama al método kpis() para obtener los indicadores del inventario
                'kpis'        => $this->kpis(),
                // Si action es 'categorias', llama al método categorias() para listar las categorías
                'categorias'  => $this->categorias(),
                // Si action es 'detalle', llama al método detalle() para ver un producto por ID
                'detalle'     => $this->detalle(),
                // Si action es 'buscar', llama al método buscar() para buscar productos por texto
                'buscar'      => $this->buscar(),
                // Si action es 'crear', llama al método crear() para agregar un nuevo producto
                'crear'       => $this->crear(),
                // Si action es 'actualizar', llama al método actualizar() para modificar un producto
                'actualizar'  => $this->actualizar(),
                // Si action es 'eliminar', llama al método eliminar() para borrar un producto
                'eliminar'    => $this->eliminar(),
                // CRUD de categorías
                'crearCategoria'       => $this->crearCategoria(),
                'actualizarCategoria'  => $this->actualizarCategoria(),
                'eliminarCategoria'    => $this->eliminarCategoria(),
                // Si action no coincide con ningún valor conocido, devuelve un JSON con error
                default       => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            // Captura excepciones específicas de PDO (errores de base de datos)
            // Devuelve un JSON con el mensaje de error de base de datos
            echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            // Captura cualquier otra excepción genérica que pueda ocurrir
            // Devuelve un JSON con el mensaje de error genérico
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Lista todos los productos del inventario
     * 
     * Obtiene el listado completo de productos desde el modelo
     * y los devuelve en una respuesta JSON con formato uniforme.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function listar(): void
    {
        // Llama al método obtenerProductos() del modelo para traer todos los productos
        $productos = $this->model->obtenerProductos();
        // Devuelve un JSON con indicador de éxito y el arreglo de productos en la clave 'data'
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    /**
     * Obtiene los KPIs (indicadores clave) del inventario
     * 
     * Consulta al modelo cuatro métricas: total de productos,
     * productos con stock crítico (0 o menos), productos con stock
     * bajo (menor al mínimo) y el valor total del inventario en dinero.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function kpis(): void
    {
        // Devuelve un JSON con los cuatro indicadores del inventario anidados en 'data'
        echo json_encode([
            'success' => true,
            'data' => [
                // Cantidad total de productos registrados en el inventario
                'total'   => $this->model->totalProductos(),
                // Cantidad de productos con stock crítico (0 o unidades negativas)
                'critico' => $this->model->stockCritico(),
                // Cantidad de productos con stock por debajo del mínimo configurado
                'bajo'    => $this->model->stockBajo(),
                // Valor monetario total del inventario (suma de precio_venta * stock)
                'valor'   => $this->model->valorTotalInventario(),
            ]
        ]);
    }

    /**
     * Obtiene la lista de categorías de productos
     * 
     * Consulta al modelo todas las categorías disponibles para
     * clasificar los productos y las devuelve en formato JSON.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function categorias(): void
    {
        // Llama al método obtenerCategorias() del modelo para traer todas las categorías
        $categorias = $this->model->obtenerCategorias();
        // Devuelve un JSON con indicador de éxito y el arreglo de categorías en 'data'
        echo json_encode(['success' => true, 'data' => $categorias]);
    }

    /**
     * Obtiene el detalle de un producto específico por su ID
     * 
     * Lee el parámetro GET 'id', lo valida como entero positivo,
     * consulta el producto en el modelo y devuelve sus datos.
     * Si el ID no es válido o el producto no existe, retorna un error.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function detalle(): void
    {
        // Lee el parámetro GET 'id' y lo convierte a entero; si no existe, usa 0 por defecto
        $id = (int)($_GET['id'] ?? 0);
        // Verifica si el ID es válido (distinto de cero, que indica ausencia de ID)
        if (!$id) {
            // Si el ID no es válido, devuelve un JSON con mensaje de error
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            // Detiene la ejecución del método para no continuar procesando
            return;
        }
        // Llama al método obtenerProductoPorId() del modelo pasando el ID del producto
        $producto = $this->model->obtenerProductoPorId($id);
        // Verifica si se encontró un producto con ese ID
        if ($producto) {
            // Si existe, devuelve un JSON con éxito y los datos del producto
            echo json_encode(['success' => true, 'data' => $producto]);
        } else {
            // Si no existe, devuelve un JSON con mensaje de error
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
        }
    }

    /**
     * Busca productos por texto (nombre o código)
     * 
     * Lee el término de búsqueda desde POST 'termino'.
     * Si el término está vacío, devuelve todos los productos.
     * Si tiene contenido, filtra los productos que coincidan
     * con el término en nombre o código.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function buscar(): void
    {
        // Lee el término de búsqueda enviado por POST, o cadena vacía si no existe
        $termino = $_POST['termino'] ?? '';
        // Verifica si el término está vacío después de eliminar espacios en blanco
        if (trim($termino) === '') {
            // Si no hay término de búsqueda, obtiene todos los productos sin filtrar
            $productos = $this->model->obtenerProductos();
        } else {
            // Si hay un término, llama al método buscarProductos() con el filtro
            $productos = $this->model->buscarProductos($termino);
        }
        // Devuelve un JSON con indicador de éxito y el arreglo de productos encontrados
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    /**
     * Crea un nuevo producto en el inventario
     * 
     * Lee y sanitiza todos los campos del formulario enviados por POST.
     * Valida que los campos obligatorios (código, nombre, categoría)
     * estén presentes. Si la validación pasa, llama al modelo para
     * insertar el producto en la base de datos.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function crear(): void
    {
        // Lee y asigna el código del producto desde el formulario POST
        $codigo       = $_POST['codigo'] ?? '';
        // Lee y asigna el nombre del producto desde el formulario POST
        $nombre       = $_POST['nombre'] ?? '';
        // Lee y asigna la descripción del producto desde el formulario POST
        $descripcion  = $_POST['descripcion'] ?? '';
        // Lee el ID de categoría y lo convierte a entero; si no existe, usa 0
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        // Lee el stock inicial y lo convierte a entero; si no existe, usa 0
        $stock        = (int)($_POST['stock'] ?? 0);
        // Lee el stock mínimo y lo convierte a entero; por defecto 5
        $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
        // Lee el costo de compra y lo convierte a flotante; si no existe, usa 0
        $costo_compra = (float)($_POST['costo_compra'] ?? 0);
        // Lee el precio de venta y lo convierte a flotante; si no existe, usa 0
        $precio_venta = (float)($_POST['precio_venta'] ?? 0);

        // Valida que los campos obligatorios no estén vacíos: código, nombre y categoría
        if (empty($codigo) || empty($nombre) || !$categoria_id) {
            // Si falta algún campo obligatorio, devuelve un JSON con mensaje de error
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            // Detiene la ejecución del método
            return;
        }

        // Llama al método crearProducto() del modelo con todos los datos del formulario
        // Devuelve true si la inserción fue exitosa, false en caso contrario
        $resultado = $this->model->crearProducto($codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $descripcion);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error según corresponda
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Producto creado exitosamente']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al crear el producto']
        );
    }

    /**
     * Actualiza un producto existente en el inventario
     * 
     * Lee todos los campos del formulario enviados por POST,
     * incluyendo el ID del producto a modificar. Valida que
     * el ID y los campos obligatorios estén presentes antes
     * de llamar al modelo para actualizar el registro.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function actualizar(): void
    {
        // Lee el ID del producto y lo convierte a entero; si no existe, usa 0
        $id           = (int)($_POST['id'] ?? 0);
        // Lee el código del producto desde el formulario POST
        $codigo       = $_POST['codigo'] ?? '';
        // Lee el nombre del producto desde el formulario POST
        $nombre       = $_POST['nombre'] ?? '';
        // Lee la descripción del producto desde el formulario POST
        $descripcion  = $_POST['descripcion'] ?? '';
        // Lee el ID de categoría y lo convierte a entero; si no existe, usa 0
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        // Lee el stock y lo convierte a entero; si no existe, usa 0
        $stock        = (int)($_POST['stock'] ?? 0);
        // Lee el stock mínimo y lo convierte a entero; por defecto 5
        $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
        // Lee el costo de compra y lo convierte a flotante; si no existe, usa 0
        $costo_compra = (float)($_POST['costo_compra'] ?? 0);
        // Lee el precio de venta y lo convierte a flotante; si no existe, usa 0
        $precio_venta = (float)($_POST['precio_venta'] ?? 0);

        // Valida que el ID sea válido y que los campos obligatorios estén completos
        if (!$id || empty($codigo) || empty($nombre) || !$categoria_id) {
            // Si falta algún campo obligatorio o el ID no es válido, devuelve error
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            // Detiene la ejecución del método
            return;
        }

        // Llama al método actualizarProducto() del modelo con todos los datos
        // Devuelve true si la actualización fue exitosa, false en caso contrario
        $resultado = $this->model->actualizarProducto($id, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $descripcion);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Producto actualizado exitosamente']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al actualizar el producto']
        );
    }

    /**
     * Elimina un producto del inventario
     * 
     * Lee el ID del producto desde POST, valida que sea un
     * número positivo, y llama al modelo para eliminar el
     * registro de la base de datos.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function eliminar(): void
    {
        // Lee el ID del producto y lo convierte a entero; si no existe, usa 0
        $id = (int)($_POST['id'] ?? 0);
        // Verifica si el ID es válido (distinto de cero)
        if (!$id) {
            // Si el ID no es válido, devuelve un JSON con mensaje de error
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            // Detiene la ejecución del método
            return;
        }
        // Llama al método eliminarProducto() del modelo pasando el ID
        // Devuelve true si la eliminación fue exitosa, false en caso contrario
        $resultado = $this->model->eliminarProducto($id);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Producto eliminado exitosamente']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al eliminar el producto']
        );
    }

    /**
     * Crea una nueva categoría
     */
    private function crearCategoria(): void
    {
        $nombre = trim($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            echo json_encode(['success' => false, 'error' => 'El nombre es obligatorio']);
            return;
        }
        $resultado = $this->model->crearCategoria($nombre);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Categoría creada exitosamente']
                : ['success' => false, 'error' => 'Error al crear la categoría']
        );
    }

    /**
     * Actualiza una categoría existente
     */
    private function actualizarCategoria(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        if (!$id || empty($nombre)) {
            echo json_encode(['success' => false, 'error' => 'ID y nombre son obligatorios']);
            return;
        }
        $resultado = $this->model->actualizarCategoria($id, $nombre);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Categoría actualizada exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar la categoría']
        );
    }

    /**
     * Elimina una categoría
     */
    private function eliminarCategoria(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
        $resultado = $this->model->eliminarCategoria($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Categoría eliminada exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar la categoría']
        );
    }

    /**
     * Método auxiliar para construir respuestas JSON uniformes
     * 
     * Crea un arreglo asociativo con la estructura estándar de respuesta
     * que incluye siempre la clave 'success'. Opcionalmente agrega las
     * claves 'data' y 'error' según los parámetros recibidos.
     *
     * @param bool   $success Indica si la operación fue exitosa (true) o no (false)
     * @param mixed  $data    Datos opcionales a incluir en la respuesta (puede ser null)
     * @param string $error   Mensaje de error opcional (cadena vacía si no hay error)
     * 
     * @return void Responde directamente con echo en formato JSON
     */
    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        // Crea el arreglo base de respuesta con el indicador de éxito como primer elemento
        $result = ['success' => $success];
        // Si se proporcionaron datos (no es null), los agrega al arreglo bajo la clave 'data'
        if ($data !== null) $result['data'] = $data;
        // Si hay un mensaje de error (no es cadena vacía), lo agrega bajo la clave 'error'
        if ($error) $result['error'] = $error;
        // Codifica el arreglo como JSON y lo envía al cliente como respuesta HTTP
        echo json_encode($result);
    }
}
