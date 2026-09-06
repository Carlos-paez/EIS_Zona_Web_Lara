<?php

namespace App\Controllers;

use App\Core\Router;
use App\Core\Validator;
use App\Models\Inventario;

class InventarioController
{
    private Inventario $model;

    public function __construct()
    {
        $this->model = new Inventario();
    }

    public function getModel(): Inventario
    {
        return $this->model;
    }

    public function setModel(Inventario $model): void
    {
        $this->model = $model;
    }

    public function handle(): void
    {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            match ($action) {
                'listar'              => $this->listar(),
                'kpis'                => $this->kpis(),
                'categorias'          => $this->categorias(),
                'detalle'             => $this->detalle(),
                'buscar'              => $this->buscar(),
                'crear'               => $this->crear(),
                'actualizar'          => $this->actualizar(),
                'eliminar'            => $this->eliminar(),
                'crearCategoria'      => $this->crearCategoria(),
                'actualizarCategoria' => $this->actualizarCategoria(),
                'eliminarCategoria'   => $this->eliminarCategoria(),
                default               => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
        } catch (\InvalidArgumentException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
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

    private function buscar(): void
    {
        $termino = Validator::busqueda($_POST['termino'] ?? '', 'búsqueda');
        if ($termino === '') {
            $productos = $this->model->obtenerProductos();
        } else {
            $productos = $this->model->buscarProductos($termino);
        }
        echo json_encode(['success' => true, 'data' => $productos]);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $codigo       = Validator::texto($_POST['codigo'] ?? null, 'código', ['required' => true, 'max' => 50, 'pattern' => Validator::PATTERN_CODIGO, 'patternMessage' => 'El código del producto contiene caracteres no permitidos']);
        $nombre       = Validator::texto($_POST['nombre'] ?? null, 'nombre', ['required' => true, 'min' => 2, 'max' => 100]);
        $descripcion  = Validator::texto($_POST['descripcion'] ?? null, 'descripción', ['required' => false, 'max' => 1000]);
        $categoria_id = Validator::entero($_POST['categoria_id'] ?? null, 'categoría', ['required' => true, 'min' => 1]);
        $stock        = Validator::entero($_POST['stock'] ?? null, 'stock', ['required' => true, 'min' => 0]);
        $stock_minimo = Validator::entero($_POST['stock_minimo'] ?? 5, 'stock mínimo', ['required' => true, 'min' => 1]);
        $costo_compra = Validator::decimal($_POST['costo_compra'] ?? 0, 'costo de compra', ['required' => false, 'min' => 0]);
        $precio_venta = Validator::decimal($_POST['precio_venta'] ?? null, 'precio de venta', ['required' => true, 'min' => 0.01]);

        if ($costo_compra > 0 && $precio_venta < $costo_compra) {
            echo json_encode(['success' => false, 'error' => 'El precio de venta no puede ser menor al costo de compra']);
            return;
        }

        if ($this->model->existeCodigo($codigo)) {
            echo json_encode(['success' => false, 'error' => 'Ya existe un producto con ese código']);
            return;
        }

        $resultado = $this->model->crearProducto($codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $descripcion);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Producto creado exitosamente']
                : ['success' => false, 'error' => 'Error al crear el producto']
        );
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id           = Validator::id($_POST['id'] ?? null, 'ID del producto');
        $codigo       = Validator::texto($_POST['codigo'] ?? null, 'código', ['required' => true, 'max' => 50, 'pattern' => Validator::PATTERN_CODIGO, 'patternMessage' => 'El código del producto contiene caracteres no permitidos']);
        $nombre       = Validator::texto($_POST['nombre'] ?? null, 'nombre', ['required' => true, 'min' => 2, 'max' => 100]);
        $descripcion  = Validator::texto($_POST['descripcion'] ?? null, 'descripción', ['required' => false, 'max' => 1000]);
        $categoria_id = Validator::entero($_POST['categoria_id'] ?? null, 'categoría', ['required' => true, 'min' => 1]);
        $stock        = Validator::entero($_POST['stock'] ?? null, 'stock', ['required' => true, 'min' => 0]);
        $stock_minimo = Validator::entero($_POST['stock_minimo'] ?? 5, 'stock mínimo', ['required' => true, 'min' => 1]);
        $costo_compra = Validator::decimal($_POST['costo_compra'] ?? 0, 'costo de compra', ['required' => false, 'min' => 0]);
        $precio_venta = Validator::decimal($_POST['precio_venta'] ?? null, 'precio de venta', ['required' => true, 'min' => 0.01]);

        if ($costo_compra > 0 && $precio_venta < $costo_compra) {
            echo json_encode(['success' => false, 'error' => 'El precio de venta no puede ser menor al costo de compra']);
            return;
        }

        if ($this->model->existeCodigo($codigo, $id)) {
            echo json_encode(['success' => false, 'error' => 'Ya existe otro producto con ese código']);
            return;
        }

        $resultado = $this->model->actualizarProducto($id, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $descripcion);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Producto actualizado exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar el producto']
        );
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

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

    private function crearCategoria(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $nombre = Validator::texto($_POST['nombre'] ?? null, 'nombre de categoría', ['required' => true, 'max' => 100]);
        $resultado = $this->model->crearCategoria($nombre);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Categoría creada exitosamente']
                : ['success' => false, 'error' => 'Error al crear la categoría']
        );
    }

    private function actualizarCategoria(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID de la categoría');
        $nombre = Validator::texto($_POST['nombre'] ?? null, 'nombre de categoría', ['required' => true, 'max' => 100]);
        $resultado = $this->model->actualizarCategoria($id, $nombre);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Categoría actualizada exitosamente']
                : ['success' => false, 'error' => 'Error al actualizar la categoría']
        );
    }

    private function eliminarCategoria(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID de la categoría');
        $resultado = $this->model->eliminarCategoria($id);
        echo json_encode(
            $resultado
                ? ['success' => true, 'message' => 'Categoría eliminada exitosamente']
                : ['success' => false, 'error' => 'Error al eliminar la categoría']
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
