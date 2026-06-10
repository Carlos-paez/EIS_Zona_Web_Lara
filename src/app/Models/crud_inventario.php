<?php
// =====================================================================
// MODELO DE INVENTARIO (crud_inventario.php)
// =====================================================================
// Este archivo contiene todas las funciones que interactuan con la
// base de datos para el modulo de inventario.
//
// CRUD significa: Create (Crear), Read (Leer), Update (Actualizar),
// Delete (Eliminar) - las 4 operaciones basicas sobre los datos.
//
// Ademas hay funciones para:
//   - KPIs (indicadores como total de productos, stock critico, etc.)
//   - Catalogo (categorias, subcategorias, marcas, modelos)
//   - Movimientos de stock (entradas, salidas y su historial)
//
// Todas las funciones reciben $pdo que es la conexion a la base de
// datos. $pdo se crea en database.php usando la clase PDO de PHP.
// =====================================================================

// Incluye el archivo database.php que tiene la conexion a MySQL
// Ese archivo crea la variable $pdo que usamos para hablar con la BD
require_once __DIR__.'/../../Config/database.php';

// ============================================================
// PRODUCTOS - Funciones para hacer CRUD de productos
// CRUD significa: Crear, Leer, Actualizar, Eliminar
// ============================================================

// Crea un producto nuevo en la tabla productos
// Recibe la conexion $pdo y los datos del producto
function crearProducto($pdo, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta) {
    // Escribo la sentencia SQL para insertar un producto
    // Los signos ? son "placeholders" o comodines que luego reemplazo con valores reales
    // Esto evita que alguien pueda inyectar codigo SQL malicioso
    $sql = "INSERT INTO productos (codigo, nombre, categoria_id, stock, stock_minimo, costo_compra, precio_venta) VALUES (?, ?, ?, ?, ?, ?, ?)";
    // prepare() le dice a MySQL que prepare la consulta pero sin ejecutarla aun
    $stmt = $pdo->prepare($sql);
    // execute() reemplaza los ? por los valores reales y ejecuta la consulta
    // El orden de los valores debe coincidir con el orden de los ? en el SQL
    $stmt->execute([$codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta]);
    // Devuelve el objeto statement para que quien llame a la funcion pueda ver si funciono
    // Si el INSERT fue exitoso, $stmt es "true"; si fallo, arroja una excepcion
    return $stmt;
}

// Obtiene TODOS los productos activos de la base de datos
function obtenerProductos($pdo) {
    // LEFT JOIN significa: traeme todos los productos aunque no tengan categoria
    // p.* significa "todas las columnas de la tabla productos"
    // c.nombre AS categoria le pone el alias "categoria" al nombre de la categoria
    // WHERE activo = TRUE solo trae los productos que no han sido eliminados
    // ORDER BY p.nombre los ordena alfabeticamente por nombre
    $sql = "SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.activo = TRUE ORDER BY p.nombre";
    // query() ejecuta la consulta directamente porque no tiene parametros variables
    $stmt = $pdo->query($sql);
    // fetchAll() devuelve TODAS las filas como un arreglo de arreglos asociativos
    // Cada fila es un arreglo donde las claves son los nombres de las columnas
    return $stmt->fetchAll();
}

// Obtiene UN SOLO producto por su ID
function obtenerProductoPorId($pdo, $id) {
    // La consulta es igual a la anterior pero con WHERE id = ? para filtrar por un producto especifico
    $sql = "SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ?";
    // Uso prepare() porque el ID viene del usuario y no debo confiar en el
    $stmt = $pdo->prepare($sql);
    // Pongo el ID en el placeholder con execute()
    $stmt->execute([$id]);
    // fetch() devuelve UNA sola fila (no fetchAll), o false si no encontro nada
    return $stmt->fetch();
}

// Busca productos por nombre o codigo (busqueda parcial)
function buscarProductos($pdo, $termino) {
    // LIKE ? significa que el texto puede coincidir parcialmente
    // Uso OR para buscar en nombre O en codigo
    $sql = "SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.activo = TRUE AND (p.nombre LIKE ? OR p.codigo LIKE ?) ORDER BY p.nombre";
    $stmt = $pdo->prepare($sql);
    // Agrego % al principio y al final para que busque "contiene" en lugar de "empieza con"
    // Ejemplo: si buscan "mouse", "%mouse%" encuentra "Mouse Inalambrico" y "Almohadilla para mouse"
    $buscar = "%$termino%";
    // Uso $buscar dos veces porque tengo dos ? en el SQL (uno para nombre y otro para codigo)
    $stmt->execute([$buscar, $buscar]);
    return $stmt->fetchAll();
}

// Actualiza los datos de un producto existente
function actualizarProducto($pdo, $id, $codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta) {
    // UPDATE cambia los valores de la fila que coincida con WHERE id = ?
    // SET asigna cada columna con el nuevo valor
    $sql = "UPDATE productos SET codigo = ?, nombre = ?, categoria_id = ?, stock = ?, stock_minimo = ?, costo_compra = ?, precio_venta = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    // Ejecuto con los valores nuevos y el ID para saber cual producto actualizar
    // El ID es el ultimo porque en el SQL WHERE id = ? esta al final
    $stmt->execute([$codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $id]);
    return $stmt;
}

// Elimina un producto de la base de datos (DELETE fisico)
function eliminarProducto($pdo, $id) {
    // DELETE FROM borra la fila permanentemente de la tabla
    // Solo borra el producto cuyo ID coincida
    $sql = "DELETE FROM productos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt;
}

// ============================================================
// KPIS - Indicadores para las tarjetas del dashboard
// KPI significa Key Performance Indicator (Indicador Clave)
// ============================================================

// Cuenta cuantos productos hay en total (los que estan activos)
function totalProductos($pdo) {
    // COUNT(*) cuenta cuantas filas tiene la consulta
    // AS total le pone nombre "total" al resultado del conteo
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM productos WHERE activo = TRUE");
    // fetch() obtiene la unica fila que devuelve un COUNT
    $fila = $stmt->fetch();
    // Devuelvo solo el numero que esta en la columna "total"
    return $fila['total'];
}

// Cuenta los productos que tienen stock CRITICO (0 o menos)
function stockCritico($pdo) {
    // WHERE stock <= 0 significa productos que no tienen nada en inventario
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM productos WHERE activo = TRUE AND stock <= 0");
    $fila = $stmt->fetch();
    return $fila['total'];
}

// Cuenta los productos con stock BAJO (tienen poco pero menos del minimo)
function stockBajo($pdo) {
    // stock > 0 AND stock <= stock_minimo significa: tiene algo pero esta por debajo del minimo
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM productos WHERE activo = TRUE AND stock > 0 AND stock <= stock_minimo");
    $fila = $stmt->fetch();
    return $fila['total'];
}

// Calcula cuanto dinero vale todo el inventario
function valorTotalInventario($pdo) {
    // SUM() suma todos los valores de una columna
    // stock * precio_venta multiplica el stock por el precio de cada producto y luego suma todo
    $stmt = $pdo->query("SELECT SUM(stock * precio_venta) AS total FROM productos WHERE activo = TRUE");
    $fila = $stmt->fetch();
    // Si $fila['total'] no tiene valor (es null porque no hay productos), devuelvo 0
    // Esto evita que la pagina muestre un error o un espacio en blanco
    return $fila['total'] ? $fila['total'] : 0;
}

// ============================================================
// CATEGORIAS, SUBCATEGORIAS, MARCAS Y MODELOS
// Estas funciones traen los datos de las tablas de catalogo
// para llenar los combos/selects del formulario
// ============================================================

// Obtiene todas las categorias activas
function obtenerCategorias($pdo) {
    // SELECT * trae todas las columnas de la tabla categorias
    $stmt = $pdo->query("SELECT * FROM categorias WHERE activa = TRUE ORDER BY nombre");
    return $stmt->fetchAll();
}

// Obtiene todas las subcategorias activas
function obtenerSubcategorias($pdo) {
    $stmt = $pdo->query("SELECT * FROM subcategorias WHERE activa = TRUE ORDER BY nombre");
    return $stmt->fetchAll();
}

// Obtiene todas las marcas
function obtenerMarcas($pdo) {
    $stmt = $pdo->query("SELECT * FROM marcas ORDER BY nombre");
    return $stmt->fetchAll();
}

// Obtiene todos los modelos
function obtenerModelos($pdo) {
    $stmt = $pdo->query("SELECT * FROM modelos ORDER BY nombre");
    return $stmt->fetchAll();
}

// ============================================================
// MOVIMIENTOS DE STOCK (Entradas y Salidas)
// Cada vez que entra o sale mercancia se registra en la
// bitacora para llevar un historial de todo lo que pasa
// ============================================================

// Registra una ENTRADA de stock (cuando llega mercancia nueva)
function registrarEntrada($pdo, $producto_id, $cantidad, $usuario_id, $motivo) {
    // Primero busco el producto para saber cuanto stock tiene actualmente
    $sql = "SELECT stock FROM productos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();

    // Si el producto no existe (fetch devolvio false), termino la funcion
    // Devuelvo false para indicar que hubo un error
    if (!$producto) return false;

    // Guardo el stock que tenia antes de la entrada
    $stock_anterior = $producto['stock'];
    // Calculo el nuevo stock sumando la cantidad que entro
    $stock_nuevo = $stock_anterior + $cantidad;

    // Actualizo el stock del producto en la tabla productos
    $sql1 = "UPDATE productos SET stock = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql1);
    $stmt->execute([$stock_nuevo, $producto_id]);

    // Inserto un registro en la bitacora para dejar constancia
    // 'entrada' es el tipo de movimiento
    // Guardo el stock_anterior y stock_nuevo para saber como estaba antes y como quedo
    $sql2 = "INSERT INTO bitacora_movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, usuario_id, motivo) VALUES (?, 'entrada', ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql2);
    $stmt->execute([$producto_id, $cantidad, $stock_anterior, $stock_nuevo, $usuario_id, $motivo]);

    // Devuelvo true para indicar que todo salio bien
    return true;
}

// Registra una SALIDA de stock (cuando se vende o se usa un producto)
function registrarSalida($pdo, $producto_id, $cantidad, $usuario_id, $motivo) {
    // Primero busco el producto para ver su stock actual
    $sql = "SELECT stock FROM productos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();

    // Si el producto no existe, termino la funcion
    if (!$producto) return false;

    // Guardo el stock anterior
    $stock_anterior = $producto['stock'];
    // A diferencia de la entrada, en la salida RESTO la cantidad
    $stock_nuevo = $stock_anterior - $cantidad;

    // Verifico que el stock no quede en negativo
    // No se puede vender algo que no existe
    if ($stock_nuevo < 0) return false;

    // Actualizo el stock restando la cantidad que salio
    $sql1 = "UPDATE productos SET stock = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql1);
    $stmt->execute([$stock_nuevo, $producto_id]);

    // Inserto en la bitacora con tipo 'salida'
    $sql2 = "INSERT INTO bitacora_movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, usuario_id, motivo) VALUES (?, 'salida', ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql2);
    $stmt->execute([$producto_id, $cantidad, $stock_anterior, $stock_nuevo, $usuario_id, $motivo]);

    return true;
}

// Obtiene el historial de movimientos de un producto especifico
function obtenerMovimientos($pdo, $producto_id) {
    // LEFT JOIN con usuarios para mostrar el nombre de quien hizo el movimiento
    // b.* trae todas las columnas de la bitacora
    // u.nombre AS usuario trae el nombre de la persona que hizo el movimiento
    // ORDER BY b.fecha DESC ordena del mas reciente al mas antiguo
    $sql = "SELECT b.*, u.nombre AS usuario FROM bitacora_movimientos_stock b LEFT JOIN usuarios u ON b.usuario_id = u.id WHERE b.producto_id = ? ORDER BY b.fecha DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$producto_id]);
    return $stmt->fetchAll();
}
