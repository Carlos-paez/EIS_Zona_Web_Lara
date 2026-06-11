<?php
namespace App\Controllers;

use App\Models\Inventario;

class InventarioController
{
    private Inventario $model;

    public function __construct()
    {
        $this->model = new Inventario();
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'listar'      => $this->listar(),
                'kpis'        => $this->kpis(),
                'categorias'  => $this->categorias(),
                'detalle'     => $this->detalle(),
                'movimientos' => $this->movimientos(),
                'buscar'      => $this->buscar(),
                'crear'       => $this->crear(),
                'actualizar'  => $this->actualizar(),
                'eliminar'    => $this->eliminar(),
                'entrada'     => $this->entrada(),
                'salida'      => $this->salida(),
                default       => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function listar(): void
    {
        $productos = $this->model->obtenerProductos();
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    private function kpis(): void
    {
        echo json_encode([
            'success' => true,
            'data' => [
                'total'   => $this->model->totalProductos(),
                'critico' => $this->model->stockCritico(),
                'bajo'    => $this->model->stockBajo(),
                'valor'   => $this->model->valorTotalInventario(),
            ]
        ]);
    }

    private function categorias(): void
    {
        $categorias = $this->model->obtenerCategorias();
        echo json_encode(['success' => true, 'data' => $categorias]);
    }

    private function detalle(): void
    {
        $id = (int)($_GET['id'] ?? 0);
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

    private function buscar(): void
    {
        $termino = $_POST['termino'] ?? '';
        if (trim($termino) === '') {
            $productos = $this->model->obtenerProductos();
        } else {
            $productos = $this->model->buscarProductos($termino);
        }
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    private function crear(): void
    {
        $codigo       = $_POST['codigo'] ?? '';
        $nombre       = $_POST['nombre'] ?? '';
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        $stock        = (int)($_POST['stock'] ?? 0);
        $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
        $costo_compra = (float)($_POST['costo_compra'] ?? 0);
        $precio_venta = (float)($_POST['precio_venta'] ?? 0);

        if (empty($codigo) || empty($nombre) || !$categoria_id) {
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            return;
        }

        $resultado = $this->model->crearProducto($codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Producto creado exitosamente']
                : ['success' => false, 'error' => 'Error al crear el producto']
        );
    }

    private function actualizar(): void
    {
        $id           = (int)($_POST['id'] ?? 0);
        $codigo       = $_POST['codigo'] ?? '';
        $nombre       = $_POST['nombre'] ?? '';
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        $stock        = (int)($_POST['stock'] ?? 0);
        $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
        $costo_compra = (float)($_POST['costo_compra'] ?? 0);
        $precio_venta = (float)($_POST['precio_venta'] ?? 0);

        if (!$id || empty($codigo) || empty($nombre) || !$categoria_id) {
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            return;
        }

        $resultado = $this->model->actualizarProducto($id, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Producto actualizado exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar el producto']
        );
    }

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

    private function entrada(): void
    {
        $producto_id = (int)($_POST['producto_id'] ?? 0);
        $cantidad    = (int)($_POST['cantidad'] ?? 0);
        $usuario_id  = (int)($_SESSION['user_id'] ?? 1);
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

    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        $result = ['success' => $success];
        if ($data !== null) $result['data'] = $data;
        if ($error) $result['error'] = $error;
        echo json_encode($result);
    }
}
