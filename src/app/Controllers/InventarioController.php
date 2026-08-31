<?php

namespace App\Controllers;

use App\Core\Router;
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
        $termino = trim($_POST['termino'] ?? '');
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

        $codigo       = trim($_POST['codigo'] ?? '');
        $nombre       = trim($_POST['nombre'] ?? '');
        $descripcion  = trim($_POST['descripcion'] ?? '');
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        $stock        = (int)($_POST['stock'] ?? 0);
        $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
        $costo_compra = (float)($_POST['costo_compra'] ?? 0);
        $precio_venta = (float)($_POST['precio_venta'] ?? 0);

        if (empty($codigo) || empty($nombre) || !$categoria_id) {
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            return;
        }

        if ($stock < 0) {
            echo json_encode(['success' => false, 'error' => 'El stock no puede ser negativo']);
            return;
        }
        if ($stock_minimo < 1) {
            echo json_encode(['success' => false, 'error' => 'El stock mínimo debe ser al menos 1']);
            return;
        }
        if ($costo_compra < 0) {
            echo json_encode(['success' => false, 'error' => 'El costo de compra no puede ser negativo']);
            return;
        }
        if ($precio_venta <= 0) {
            echo json_encode(['success' => false, 'error' => 'El precio de venta debe ser mayor a 0']);
            return;
        }
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

        $id           = (int)($_POST['id'] ?? 0);
        $codigo       = trim($_POST['codigo'] ?? '');
        $nombre       = trim($_POST['nombre'] ?? '');
        $descripcion  = trim($_POST['descripcion'] ?? '');
        $categoria_id = (int)($_POST['categoria_id'] ?? 0);
        $stock        = (int)($_POST['stock'] ?? 0);
        $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
        $costo_compra = (float)($_POST['costo_compra'] ?? 0);
        $precio_venta = (float)($_POST['precio_venta'] ?? 0);

        if (!$id || empty($codigo) || empty($nombre) || !$categoria_id) {
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            return;
        }

        if ($stock < 0) {
            echo json_encode(['success' => false, 'error' => 'El stock no puede ser negativo']);
            return;
        }
        if ($stock_minimo < 1) {
            echo json_encode(['success' => false, 'error' => 'El stock mínimo debe ser al menos 1']);
            return;
        }
        if ($costo_compra < 0) {
            echo json_encode(['success' => false, 'error' => 'El costo de compra no puede ser negativo']);
            return;
        }
        if ($precio_venta <= 0) {
            echo json_encode(['success' => false, 'error' => 'El precio de venta debe ser mayor a 0']);
            return;
        }
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

    private function actualizarCategoria(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            echo json_encode(['success' => false, 'error' => 'Token de seguridad inválido']);
            return;
        }

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

    private function eliminarCategoria(): void
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
