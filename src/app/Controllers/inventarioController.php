<?php
// =============================================================================
// CONTROLADOR InventarioController (API JSON para inventario)
// =============================================================================
// Propósito: Manejar las peticiones AJAX del módulo de inventario.
//            Responde siempre en formato JSON. Cada acción se define mediante
//            el parámetro GET 'action' (listar, crear, editar, eliminar, etc.).
// =============================================================================
namespace App\Controllers;

use App\Models\Inventario;

class InventarioController
{
    private Inventario $model;

    public function __construct()
    {
        $this->model = new Inventario();
    }

    // Método principal: despacha la acción según el parámetro GET 'action'
    public function handle(): void
    {
        header('Content-Type: application/json'); // Todas las respuestas son JSON

        $action = $_GET['action'] ?? ''; // Lee la acción a ejecutar

        try {
            // match() es una estructura de PHP 8 que funciona como switch mejorado
            match ($action) {
                'listar'      => $this->listar(),       // Lista todos los productos
                'kpis'        => $this->kpis(),         // Obtiene indicadores
                'categorias'  => $this->categorias(),   // Obtiene categorías
                'detalle'     => $this->detalle(),      // Detalle de un producto
                'movimientos' => $this->movimientos(),  // Historial de movimientos
                'buscar'      => $this->buscar(),       // Busca productos
                'crear'       => $this->crear(),        // Crea un producto
                'actualizar'  => $this->actualizar(),   // Actualiza un producto
                'eliminar'    => $this->eliminar(),     // Elimina un producto
                'entrada'     => $this->entrada(),      // Entrada de stock
                'salida'      => $this->salida(),       // Salida de stock
                default       => $this->json(false, null, 'Acción no válida'), // Acción desconocida
            };
        } catch (\PDOException $e) {
            // Error específico de base de datos
            echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            // Error genérico
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Acción: listar todos los productos
    private function listar(): void
    {
        $productos = $this->model->obtenerProductos();
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    // Acción: obtener KPIs (total productos, stock crítico, bajo, valor total)
    private function kpis(): void
    {
        echo json_encode([
            'success' => true,
            'data' => [
                'total'   => $this->model->totalProductos(),       // Cantidad total de productos
                'critico' => $this->model->stockCritico(),         // Stock crítico (0 o menos)
                'bajo'    => $this->model->stockBajo(),            // Stock bajo (menor al mínimo)
                'valor'   => $this->model->valorTotalInventario(), // Valor total del inventario
            ]
        ]);
    }

    // Acción: obtener lista de categorías
    private function categorias(): void
    {
        $categorias = $this->model->obtenerCategorias();
        echo json_encode(['success' => true, 'data' => $categorias]);
    }

    // Acción: obtener detalle de un producto por ID
    private function detalle(): void
    {
        $id = (int)($_GET['id'] ?? 0); // ID del producto desde la URL
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
        $producto = $this->model->obtenerProductoPorId($id);
        if ($producto) {
            echo json_encode(['success' => true, 'data' => $producto]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
        }
    }

    // Acción: obtener movimientos de stock de un producto
    private function movimientos(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
        $movimientos = $this->model->obtenerMovimientos($id);
        echo json_encode(['success' => true, 'data' => $movimientos]);
    }

    // Acción: buscar productos por texto (nombre o código)
    private function buscar(): void
    {
        $termino = $_POST['termino'] ?? ''; // Término de búsqueda
        if (trim($termino) === '') {
            $productos = $this->model->obtenerProductos(); // Sin filtro, trae todos
        } else {
            $productos = $this->model->buscarProductos($termino); // Con filtro
        }
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    // Acción: crear un nuevo producto
    private function crear(): void
    {
        // Obtiene y sanitiza los datos del formulario
        $codigo       = $_POST['codigo'] ?? '';
        $nombre       = $_POST['nombre'] ?? '';
        $descripcion  = $_POST['descripcion'] ?? '';
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        $stock        = (int)($_POST['stock'] ?? 0);
        $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
        $costo_compra = (float)($_POST['costo_compra'] ?? 0);
        $precio_venta = (float)($_POST['precio_venta'] ?? 0);

        // Validación de campos obligatorios
        if (empty($codigo) || empty($nombre) || !$categoria_id) {
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            return;
        }

        $resultado = $this->model->crearProducto($codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $descripcion);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Producto creado exitosamente']
                : ['success' => false, 'error' => 'Error al crear el producto']
        );
    }

    // Acción: actualizar un producto existente
    private function actualizar(): void
    {
        $id           = (int)($_POST['id'] ?? 0);
        $codigo       = $_POST['codigo'] ?? '';
        $nombre       = $_POST['nombre'] ?? '';
        $descripcion  = $_POST['descripcion'] ?? '';
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        $stock        = (int)($_POST['stock'] ?? 0);
        $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
        $costo_compra = (float)($_POST['costo_compra'] ?? 0);
        $precio_venta = (float)($_POST['precio_venta'] ?? 0);

        if (!$id || empty($codigo) || empty($nombre) || !$categoria_id) {
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            return;
        }

        $resultado = $this->model->actualizarProducto($id, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $descripcion);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Producto actualizado exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar el producto']
        );
    }

    // Acción: eliminar un producto
    private function eliminar(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            return;
        }
        $resultado = $this->model->eliminarProducto($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Producto eliminado exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar el producto']
        );
    }

    // Acción: registrar entrada de stock
    private function entrada(): void
    {
        $producto_id = (int)($_POST['producto_id'] ?? 0);
        $cantidad    = (int)($_POST['cantidad'] ?? 0);
        $usuario_id  = (int)($_SESSION['user_id'] ?? 1); // Usuario actual o 1 por defecto
        $motivo      = $_POST['motivo'] ?? 'Entrada manual';

        if (!$producto_id || $cantidad <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos de entrada no válidos']);
            return;
        }

        $resultado = $this->model->registrarEntrada($producto_id, $cantidad, $usuario_id, $motivo);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Entrada registrada exitosamente']
                : ['success' => false, 'error' => 'Error al registrar la entrada']
        );
    }

    // Acción: registrar salida de stock
    private function salida(): void
    {
        $producto_id = (int)($_POST['producto_id'] ?? 0);
        $cantidad    = (int)($_POST['cantidad'] ?? 0);
        $usuario_id  = (int)($_SESSION['user_id'] ?? 1);
        $motivo      = $_POST['motivo'] ?? 'Salida manual';

        if (!$producto_id || $cantidad <= 0) {
            echo json_encode(['success' => false, 'error' => 'Datos de salida no válidos']);
            return;
        }

        $resultado = $this->model->registrarSalida($producto_id, $cantidad, $usuario_id, $motivo);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Salida registrada exitosamente']
                : ['success' => false, 'error' => 'Error al registrar la salida']
        );
    }

    // Método auxiliar para construir respuestas JSON uniformes
    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        $result = ['success' => $success]; // Indica si la operación fue exitosa
        if ($data !== null) $result['data'] = $data;  // Datos de respuesta
        if ($error) $result['error'] = $error;        // Mensaje de error
        echo json_encode($result); // Codifica y envía como JSON
    }
}
