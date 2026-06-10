<?php
// =====================================================================
// CONTROLADOR DE INVENTARIO (inventarioController.php)
// =====================================================================
// Este archivo maneja todas las peticiones AJAX que vienen desde el
// JavaScript (app.inventario.js). Cada accion (listar, crear, eliminar,
// entrada/salida de stock, etc.) se procesa aqui.
//
// Recibe un parametro "action" por GET y ejecuta la funcion del modelo
// que corresponda, devolviendo siempre JSON para que el JS lo procese.
// =====================================================================

// Incluye el archivo del modelo que tiene todas las funciones CRUD
// __DIR__ es la carpeta donde esta este archivo, y subimos a Models/
require_once __DIR__.'/../Models/crud_inventario.php';

// Fuerza que la respuesta sea JSON para que el JavaScript lo entienda
// Sin esto, PHP devolveria texto plano por defecto
header('Content-Type: application/json');

// Lee la accion que viene por GET (ej: ?pagina=inventario&action=listar)
// Si no viene "action", usa cadena vacia para que entre en default
$action = $_GET['action'] ?? '';

// Encierro todo en un try-catch para capturar errores inesperados
// Si algo falla, devuelvo un JSON con el error en lugar de mostrar
// un mensaje feo de PHP
try {

    // Evalua la accion recibida y ejecuta el codigo correspondiente
    switch ($action) {

        // ACCION: listar
        // Devuelve la lista completa de productos activos
        case 'listar':
            // Llama a la funcion obtenerProductos() del modelo (crud_inventario.php)
            // Le pasa $pdo que es la conexion a la base de datos
            $productos = obtenerProductos($pdo);
            // Convierte el arreglo a JSON y lo envia al navegador
            // success: true indica que todo salio bien
            // data: contiene los productos
            echo json_encode(['success' => true, 'data' => $productos]);
            break; // Sale del switch

        // ACCION: kpis
        // Devuelve los indicadores (KPIs) para las tarjetas del panel
        case 'kpis':
            // Devuelve un solo JSON con 4 valores calculados
            echo json_encode([
                'success' => true,
                'data' => [
                    'total'   => totalProductos($pdo),       // Cantidad total de productos activos
                    'critico' => stockCritico($pdo),          // Productos con stock en 0
                    'bajo'    => stockBajo($pdo),             // Productos con stock por debajo del minimo
                    'valor'   => valorTotalInventario($pdo)   // Valor monetario de todo el inventario
                ]
            ]);
            break;

        // ACCION: categorias
        // Devuelve todas las categorias para llenar el select del formulario
        case 'categorias':
            $categorias = obtenerCategorias($pdo);
            echo json_encode(['success' => true, 'data' => $categorias]);
            break;

        // ACCION: detalle
        // Devuelve los datos de UN SOLO producto para editar
        case 'detalle':
            // Toma el ID del producto desde la URL y lo convierte a entero
            // (int) fuerza que sea numero, evitando inyeccion
            $id = (int)($_GET['id'] ?? 0);
            // Si el ID es 0 (no vino o no es valido), devuelvo error
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID no válido']);
                break; // Salgo porque no puedo continuar sin ID
            }
            // Busco el producto en la base de datos por su ID
            $producto = obtenerProductoPorId($pdo, $id);
            // Si encontre el producto, lo devuelvo en data
            // Si no existe, devuelvo error
            if ($producto) {
                echo json_encode(['success' => true, 'data' => $producto]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
            }
            break;

        // ACCION: movimientos
        // Devuelve el historial de movimientos de stock de un producto
        case 'movimientos':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID no válido']);
                break;
            }
            // Obtiene todos los movimientos (entradas y salidas) de ese producto
            $movimientos = obtenerMovimientos($pdo, $id);
            echo json_encode(['success' => true, 'data' => $movimientos]);
            break;

        // ACCION: buscar
        // Busca productos por nombre o codigo (texto parcial)
        case 'buscar':
            // Toma el termino de busqueda desde POST (formulario enviado)
            $termino = $_POST['termino'] ?? '';
            // Si el termino esta vacio, devuelvo todos los productos
            // Si no, busco los que coincidan con el termino
            if (trim($termino) === '') {
                $productos = obtenerProductos($pdo);
            } else {
                $productos = buscarProductos($pdo, $termino);
            }
            echo json_encode(['success' => true, 'data' => $productos]);
            break;

        // ACCION: crear
        // Crea un producto nuevo con los datos del formulario
        case 'crear':
            // Tomo cada campo del formulario enviado por POST
            // Si no viene el campo, uso un valor por defecto (cadena vacia, 0, etc.)
            $codigo       = $_POST['codigo'] ?? '';
            $nombre       = $_POST['nombre'] ?? '';
            $categoria_id = (int)($_POST['categoria_id'] ?? 0);
            $stock        = (int)($_POST['stock'] ?? 0);
            $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
            $costo_compra = (float)($_POST['costo_compra'] ?? 0);
            $precio_venta = (float)($_POST['precio_venta'] ?? 0);

            // Valido que los campos obligatorios no esten vacios
            // codigo, nombre y categoria son obligatorios
            if (empty($codigo) || empty($nombre) || !$categoria_id) {
                echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
                break; // No continuo si falta informacion
            }

            // Llamo a la funcion crearProducto() del modelo
            $resultado = crearProducto($pdo, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta);
            // Si se creo correctamente, devuelvo exito
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Producto creado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al crear el producto']);
            }
            break;

        // ACCION: actualizar
        // Actualiza los datos de un producto existente
        case 'actualizar':
            $id           = (int)($_POST['id'] ?? 0);
            $codigo       = $_POST['codigo'] ?? '';
            $nombre       = $_POST['nombre'] ?? '';
            $categoria_id = (int)($_POST['categoria_id'] ?? 0);
            $stock        = (int)($_POST['stock'] ?? 0);
            $stock_minimo = (int)($_POST['stock_minimo'] ?? 5);
            $costo_compra = (float)($_POST['costo_compra'] ?? 0);
            $precio_venta = (float)($_POST['precio_venta'] ?? 0);

            // Valido que el ID exista y los obligatorios esten completos
            if (!$id || empty($codigo) || empty($nombre) || !$categoria_id) {
                echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
                break;
            }

            $resultado = actualizarProducto($pdo, $id, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta);
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al actualizar el producto']);
            }
            break;

        // ACCION: eliminar
        // Elimina un producto de la base de datos (DELETE fisico)
        case 'eliminar':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) {
                echo json_encode(['success' => false, 'error' => 'ID no válido']);
                break;
            }
            $resultado = eliminarProducto($pdo, $id);
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al eliminar el producto']);
            }
            break;

        // ACCION: entrada
        // Registra una ENTRADA de stock (llega mercancia nueva)
        case 'entrada':
            $producto_id = (int)($_POST['producto_id'] ?? 0); // ID del producto que recibe stock
            $cantidad    = (int)($_POST['cantidad'] ?? 0);     // Cantidad que entra
            $usuario_id  = (int)($_SESSION['user_id'] ?? 1);   // Quien hace el movimiento (de la sesion)
            $motivo      = $_POST['motivo'] ?? 'Entrada manual'; // Por que entra el stock

            // Valido que el producto exista y la cantidad sea positiva
            if (!$producto_id || $cantidad <= 0) {
                echo json_encode(['success' => false, 'error' => 'Datos de entrada no válidos']);
                break;
            }

            // Llama a registrarEntrada() que actualiza el stock y guarda en bitacora
            $resultado = registrarEntrada($pdo, $producto_id, $cantidad, $usuario_id, $motivo);
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Entrada registrada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al registrar la entrada']);
            }
            break;

        // ACCION: salida
        // Registra una SALIDA de stock (se vende o usa un producto)
        case 'salida':
            $producto_id = (int)($_POST['producto_id'] ?? 0);
            $cantidad    = (int)($_POST['cantidad'] ?? 0);
            $usuario_id  = (int)($_SESSION['user_id'] ?? 1);
            $motivo      = $_POST['motivo'] ?? 'Salida manual';

            if (!$producto_id || $cantidad <= 0) {
                echo json_encode(['success' => false, 'error' => 'Datos de salida no válidos']);
                break;
            }

            // registrarSalida() verifica que haya stock suficiente antes de restar
            $resultado = registrarSalida($pdo, $producto_id, $cantidad, $usuario_id, $motivo);
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Salida registrada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al registrar la salida']);
            }
            break;

        // Si la accion no coincide con ningun caso de arriba
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            break;
    }

// Captura errores de la base de datos (PDOException)
} catch (\PDOException $e) {
    // Si falla la conexion o una consulta SQL, devuelvo el error como JSON
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: '.$e->getMessage()]);
// Captura cualquier otro tipo de error
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error: '.$e->getMessage()]);
}
