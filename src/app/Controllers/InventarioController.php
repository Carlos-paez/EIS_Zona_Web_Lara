<?php

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Router;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Inventario;

class InventarioController extends BaseController
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

    public function setModel(Inventario $model): self
    {
        $this->model = $model;
        return $this;
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
                default               => $this->jsonError('Acción no válida', null, 400),
            };
        } catch (ValidationException $e) {
            $this->jsonValidationException($e);
        } catch (\PDOException $e) {
            Logger::error($e, 'Inventario - consulta SQL');
            $this->jsonError('Error de base de datos', null, 500);
        } catch (\InvalidArgumentException $e) {
            $this->jsonError($e->getMessage(), null, 400);
        } catch (\Exception $e) {
            Logger::error($e, 'Inventario');
            $this->jsonError('Error interno del servidor', null, 500);
        }
    }

    private function listar(): void
    {
        $productos = $this->model->obtenerProductos();
        $this->jsonSuccess($productos);
    }

    private function kpis(): void
    {
        $this->setResult([
            'total'   => $this->model->totalProductos(),
            'critico' => $this->model->stockCritico(),
            'bajo'    => $this->model->stockBajo(),
            'valor'   => $this->model->valorTotalInventario(),
        ]);
        $this->jsonSuccess($this->getResult());
    }

    private function categorias(): void
    {
        $categorias = $this->model->obtenerCategorias();
        $this->jsonSuccess($categorias);
    }

    private function detalle(): void
    {
        $this->hydrateFromGet(['id' => 'int']);
        $id = $this->get('id', 0);
        if (!$id) {
            $id = (int)($_GET['id'] ?? 0);
        }
        if (!$id) {
            $this->jsonError('ID no válido', null, 400);
            return;
        }
        $producto = $this->model->obtenerProductoPorId($id);
        if ($producto) {
            $this->jsonSuccess($producto);
        } else {
            $this->jsonError('Producto no encontrado', null, 404);
        }
    }

    private function buscar(): void
    {
        $termino = Validator::busqueda($_POST['termino'] ?? '', 'búsqueda');
        $this->set('termino', $termino);
        if ($termino === '') {
            $productos = $this->model->obtenerProductos();
        } else {
            $productos = $this->model->buscarProductos($termino);
        }
        $this->jsonSuccess($productos);
    }

    private function crear(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $datos = $this->validarCampos([
            'codigo'       => ['label' => 'código', 'type' => 'texto', 'required' => true, 'max' => 50, 'pattern' => Validator::PATTERN_CODIGO, 'patternMessage' => 'El código del producto contiene caracteres no permitidos'],
            'nombre'       => ['label' => 'nombre', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100],
            'descripcion'  => ['label' => 'descripción', 'type' => 'texto', 'required' => false, 'max' => 1000],
            'categoria_id' => ['label' => 'categoría', 'type' => 'id'],
            'stock'        => ['label' => 'stock', 'type' => 'entero', 'required' => true, 'min' => 0, 'max' => Validator::MAX_CANTIDAD],
            'stock_minimo' => ['label' => 'stock mínimo', 'type' => 'entero', 'required' => true, 'min' => 1],
            'costo_compra' => ['label' => 'costo de compra', 'type' => 'decimal', 'required' => false, 'min' => 0],
            'precio_venta' => ['label' => 'precio de venta', 'type' => 'decimal', 'required' => true, 'min' => 0.01],
        ]);

        $codigo       = $datos['codigo'];
        $nombre       = $datos['nombre'];
        $descripcion  = $datos['descripcion'];
        $categoria_id = $datos['categoria_id'];
        $stock        = $datos['stock'];
        $stock_minimo = $datos['stock_minimo'];
        $costo_compra = $datos['costo_compra'];
        $precio_venta = $datos['precio_venta'];

        $this->set('codigo', $codigo)
            ->set('nombre', $nombre)
            ->set('descripcion', $descripcion)
            ->set('categoria_id', $categoria_id)
            ->set('stock', $stock)
            ->set('stock_minimo', $stock_minimo)
            ->set('costo_compra', $costo_compra)
            ->set('precio_venta', $precio_venta);

        if ($costo_compra > 0 && $precio_venta < $costo_compra) {
            $this->jsonError('El precio de venta no puede ser menor al costo de compra', null, 400);
            return;
        }

        if ($this->model->existeCodigo($codigo)) {
            $this->jsonError('Ya existe un producto con ese código', null, 400);
            return;
        }

        $resultado = $this->model->crearProducto($codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $descripcion);
        if ($resultado) {
            $this->jsonSuccess(null, 'Producto creado exitosamente');
        } else {
            $this->jsonError('Error al crear el producto', null, 400);
        }
    }

    private function actualizar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $datos = $this->validarCampos([
            'id'           => ['label' => 'ID del producto', 'type' => 'id'],
            'codigo'       => ['label' => 'código', 'type' => 'texto', 'required' => true, 'max' => 50, 'pattern' => Validator::PATTERN_CODIGO, 'patternMessage' => 'El código del producto contiene caracteres no permitidos'],
            'nombre'       => ['label' => 'nombre', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100],
            'descripcion'  => ['label' => 'descripción', 'type' => 'texto', 'required' => false, 'max' => 1000],
            'categoria_id' => ['label' => 'categoría', 'type' => 'id'],
            'stock'        => ['label' => 'stock', 'type' => 'entero', 'required' => true, 'min' => 0],
            'stock_minimo' => ['label' => 'stock mínimo', 'type' => 'entero', 'required' => true, 'min' => 1],
            'costo_compra' => ['label' => 'costo de compra', 'type' => 'decimal', 'required' => false, 'min' => 0],
            'precio_venta' => ['label' => 'precio de venta', 'type' => 'decimal', 'required' => true, 'min' => 0.01],
        ]);

        $id           = $datos['id'];
        $codigo       = $datos['codigo'];
        $nombre       = $datos['nombre'];
        $descripcion  = $datos['descripcion'];
        $categoria_id = $datos['categoria_id'];
        $stock        = $datos['stock'];
        $stock_minimo = $datos['stock_minimo'];
        $costo_compra = $datos['costo_compra'];
        $precio_venta = $datos['precio_venta'];

        $this->set('id', $id)
            ->set('codigo', $codigo)
            ->set('nombre', $nombre)
            ->set('descripcion', $descripcion)
            ->set('categoria_id', $categoria_id)
            ->set('stock', $stock)
            ->set('stock_minimo', $stock_minimo)
            ->set('costo_compra', $costo_compra)
            ->set('precio_venta', $precio_venta);

        if ($costo_compra > 0 && $precio_venta < $costo_compra) {
            $this->jsonError('El precio de venta no puede ser menor al costo de compra', null, 400);
            return;
        }

        if ($this->model->existeCodigo($codigo, $id)) {
            $this->jsonError('Ya existe otro producto con ese código', null, 400);
            return;
        }

        $resultado = $this->model->actualizarProducto($id, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $descripcion);
        if ($resultado) {
            $this->jsonSuccess(null, 'Producto actualizado exitosamente');
        } else {
            $this->jsonError('Error al actualizar el producto', null, 400);
        }
    }

    private function eliminar(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID del producto');
        $this->set('id', $id);
        $resultado = $this->model->eliminarProducto($id);
        if ($resultado) {
            $this->jsonSuccess(null, 'Producto eliminado exitosamente');
        } else {
            $this->jsonError('Error al eliminar el producto', null, 400);
        }
    }

    private function crearCategoria(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $datos = $this->validarCampos([
            'nombre' => ['label' => 'nombre de categoría', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100],
        ]);

        $nombre = $datos['nombre'];
        $this->set('nombre', $nombre);
        $resultado = $this->model->crearCategoria($nombre);
        if ($resultado) {
            $this->jsonSuccess(null, 'Categoría creada exitosamente');
        } else {
            $this->jsonError('Error al crear la categoría', null, 400);
        }
    }

    private function actualizarCategoria(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $datos = $this->validarCampos([
            'id'     => ['label' => 'ID de la categoría', 'type' => 'id'],
            'nombre' => ['label' => 'nombre de categoría', 'type' => 'texto', 'required' => true, 'min' => 2, 'max' => 100],
        ]);

        $id     = $datos['id'];
        $nombre = $datos['nombre'];
        $this->set('id', $id)->set('nombre', $nombre);
        $resultado = $this->model->actualizarCategoria($id, $nombre);
        if ($resultado) {
            $this->jsonSuccess(null, 'Categoría actualizada exitosamente');
        } else {
            $this->jsonError('Error al actualizar la categoría', null, 400);
        }
    }

    private function eliminarCategoria(): void
    {
        if (!Router::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->jsonError('Token de seguridad inválido', null, 400);
            return;
        }

        $id = Validator::id($_POST['id'] ?? null, 'ID de la categoría');
        $this->set('id', $id);
        $resultado = $this->model->eliminarCategoria($id);
        if ($resultado) {
            $this->jsonSuccess(null, 'Categoría eliminada exitosamente');
        } else {
            $this->jsonError('Error al eliminar la categoría', null, 400);
        }
    }
}
