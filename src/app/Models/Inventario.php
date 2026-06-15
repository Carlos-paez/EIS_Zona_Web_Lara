<?php
// =============================================================================
// MODELO inventario (POO)
// =============================================================================
// Propósito: Gestiona todas las operaciones de la tabla 'productos' y tablas
//            relacionadas (categorías, movimientos de stock, etc.).
// Este modelo usa programación orientada a objetos (POO) para gestionar
// todas las operaciones del inventario de forma estructurada y reutilizable.
// =============================================================================

// Define que esta clase pertenece al espacio de nombres App\Models, lo que permite
// organizar las clases y evitar conflictos con otras clases del mismo nombre
namespace App\Models;

// Importa (use) la clase Model del espacio de nombres App\Core, que es la clase
// base de la que heredarán todos los modelos de la aplicación
use App\Core\Model;

// Define la clase "inventario" que extiende (hereda) de la clase Model, lo que
// le da acceso a la conexión a la base de datos a través de $this->db
class inventario extends Model
{
    // =========================================================================
    // Método: crearProducto
    // Propósito: Inserta un nuevo producto en la tabla 'productos' de la BD
    // Parámetros:
    //   $codigo       - Código único del producto (string)
    //   $nombre       - Nombre descriptivo del producto (string)
    //   $categoria_id - ID de la categoría a la que pertenece (int)
    //   $stock        - Cantidad inicial en inventario (int)
    //   $stock_minimo - Cantidad mínima permitida antes de reordenar (int)
    //   $costo_compra - Precio de compra del producto (float)
    //   $precio_venta - Precio de venta al público (float)
    // Retorna: bool (true si se insertó correctamente, false si falló)
    // =========================================================================
    public function crearProducto(string $codigo, string $nombre, int $categoria_id, int $stock, int $stock_minimo, float $costo_compra, float $precio_venta): bool
    {
        // Define la consulta SQL con placeholders (?) para insertar un nuevo
        // producto en la tabla 'productos' con las columnas especificadas
        $sql = "INSERT INTO productos (codigo, nombre, categoria_id, stock, stock_minimo, costo_compra, precio_venta) VALUES (?, ?, ?, ?, ?, ?, ?)";
        // Prepara la consulta SQL para evitar inyección de código malicioso
        $stmt = $this->db->prepare($sql);
        // Ejecuta la consulta reemplazando los placeholders con los valores
        // reales y retorna true si la inserción fue exitosa o false si falló
        return $stmt->execute([$codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta]);
    }

    // =========================================================================
    // Método: obtenerProductos
    // Propósito: Obtiene todos los productos activos con el nombre de su
    //            categoría, ordenados alfabéticamente por nombre
    // Retorna: array - Lista de productos (cada producto es un array asociativo)
    // =========================================================================
    public function obtenerProductos(): array
    {
        // Ejecuta una consulta SELECT con LEFT JOIN para traer todos los
        // productos activos junto con el nombre de su categoría, ordenados
        // alfabéticamente por el nombre del producto
        $stmt = $this->db->query("SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.activo = TRUE ORDER BY p.nombre");
        // fetchAll() devuelve todas las filas como un array de arrays asociativos
        return $stmt->fetchAll();
    }

    // =========================================================================
    // Método: obtenerProductoPorId
    // Propósito: Obtiene un producto específico por su ID, incluyendo el
    //            nombre de la categoría
    // Parámetros:
    //   $id - ID del producto a buscar (int)
    // Retorna: array|false - Array con los datos del producto o false si no existe
    // =========================================================================
    public function obtenerProductoPorId(int $id): array|false
    {
        // Define la consulta SQL con un placeholder para el ID y JOIN con
        // categorías para obtener también el nombre de la categoría
        $sql = "SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.id = ?";
        // Prepara la consulta con un placeholder (?)
        $stmt = $this->db->prepare($sql);
        // Ejecuta la consulta pasando el ID del producto como único parámetro
        $stmt->execute([$id]);
        // fetch() devuelve una sola fila (array asociativo) o false si no encontró nada
        return $stmt->fetch();
    }

    // =========================================================================
    // Método: buscarProductos
    // Propósito: Busca productos activos por nombre o código usando búsqueda
    //            parcial (contiene el término)
    // Parámetros:
    //   $termino - Texto a buscar en nombre o código (string)
    // Retorna: array - Lista de productos que coinciden con la búsqueda
    // =========================================================================
    public function buscarProductos(string $termino): array
    {
        // Define consulta SQL con LIKE para búsqueda parcial en nombre o código
        $sql = "SELECT p.*, c.nombre AS categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE p.activo = TRUE AND (p.nombre LIKE ? OR p.codigo LIKE ?) ORDER BY p.nombre";
        // Prepara la consulta con dos placeholders (uno para nombre, otro para código)
        $stmt = $this->db->prepare($sql);
        // Agrega el caracter % al inicio y final del término para que la
        // búsqueda sea "contiene" en lugar de "empieza con" o "termina con"
        $buscar = "%$termino%";
        // Ejecuta la consulta usando el mismo valor para ambos placeholders
        $stmt->execute([$buscar, $buscar]);
        // Devuelve todas las filas que coincidieron con la búsqueda
        return $stmt->fetchAll();
    }

    // =========================================================================
    // Método: actualizarProducto
    // Propósito: Actualiza todos los campos editables de un producto existente
    // Parámetros:
    //   $id           - ID del producto a actualizar (int)
    //   $codigo       - Nuevo código del producto (string)
    //   $nombre       - Nuevo nombre del producto (string)
    //   $categoria_id - Nueva categoría del producto (int)
    //   $stock        - Nuevo stock del producto (int)
    //   $stock_minimo - Nuevo stock mínimo del producto (int)
    //   $costo_compra - Nuevo costo de compra (float)
    //   $precio_venta - Nuevo precio de venta (float)
    // Retorna: bool - true si se actualizó correctamente, false si falló
    // =========================================================================
    public function actualizarProducto(int $id, string $codigo, string $nombre, int $categoria_id, int $stock, int $stock_minimo, float $costo_compra, float $precio_venta): bool
    {
        // Define la consulta UPDATE con 8 placeholders: 7 para los valores a
        // actualizar y 1 para el ID en la cláusula WHERE
        $sql = "UPDATE productos SET codigo = ?, nombre = ?, categoria_id = ?, stock = ?, stock_minimo = ?, costo_compra = ?, precio_venta = ? WHERE id = ?";
        // Prepara la consulta para evitar inyección SQL
        $stmt = $this->db->prepare($sql);
        // Ejecuta el UPDATE con los nuevos valores y el ID para identificar
        // qué producto modificar; retorna true si se actualizó correctamente
        return $stmt->execute([$codigo, $nombre, $categoria_id, $stock, $stock_minimo, $costo_compra, $precio_venta, $id]);
    }

    // =========================================================================
    // Método: eliminarProducto
    // Propósito: Elimina permanentemente (borrado físico) un producto de la BD
    // Parámetros:
    //   $id - ID del producto a eliminar (int)
    // Retorna: bool - true si se eliminó correctamente, false si falló
    // =========================================================================
    public function eliminarProducto(int $id): bool
    {
        // Define la consulta DELETE para borrar el producto que coincida con el ID
        $sql = "DELETE FROM productos WHERE id = ?";
        // Prepara la consulta para evitar inyección SQL
        $stmt = $this->db->prepare($sql);
        // Ejecuta el borrado con el ID del producto; retorna true si se eliminó
        return $stmt->execute([$id]);
    }

    // =========================================================================
    // SECCIÓN: KPIs (Key Performance Indicators / Indicadores Clave)
    // Estos métodos calculan métricas importantes para el dashboard del
    // módulo de inventario, como total de productos, stock crítico, etc.
    // =========================================================================

    // =========================================================================
    // Método: totalProductos
    // Propósito: Cuenta el total de productos activos en el inventario
    // Retorna: int - Número de productos activos
    // =========================================================================
    public function totalProductos(): int
    {
        // Ejecuta COUNT(*) que cuenta cuántas filas tienen activo = TRUE
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE activo = TRUE");
        // Obtiene la única fila que devuelve la consulta de conteo
        $fila = $stmt->fetch();
        // Convierte el valor a entero y lo retorna
        return (int)$fila['total'];
    }

    // =========================================================================
    // Método: stockCritico
    // Propósito: Cuenta productos con stock crítico (0 o menos unidades)
    // Retorna: int - Número de productos con stock crítico
    // =========================================================================
    public function stockCritico(): int
    {
        // Cuenta productos activos cuyo stock es menor o igual a 0 (sin existencia)
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE activo = TRUE AND stock <= 0");
        // Obtiene la fila con el resultado del conteo
        $fila = $stmt->fetch();
        // Devuelve el total como entero
        return (int)$fila['total'];
    }

    // =========================================================================
    // Método: stockBajo
    // Propósito: Cuenta productos con stock bajo (mayor a 0 pero menor o igual
    //            al stock mínimo definido)
    // Retorna: int - Número de productos con stock bajo
    // =========================================================================
    public function stockBajo(): int
    {
        // Cuenta productos activos con stock positivo pero por debajo o igual
        // al stock mínimo configurado (necesitan reabastecimiento pronto)
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM productos WHERE activo = TRUE AND stock > 0 AND stock <= stock_minimo");
        // Obtiene la fila con el resultado del conteo
        $fila = $stmt->fetch();
        // Devuelve el total como entero
        return (int)$fila['total'];
    }

    // =========================================================================
    // Método: valorTotalInventario
    // Propósito: Calcula el valor monetario total del inventario basado en el
    //            stock multiplicado por el precio de venta de cada producto
    // Retorna: float - Valor total del inventario, o 0.0 si no hay productos
    // =========================================================================
    public function valorTotalInventario(): float
    {
        // SUM(stock * precio_venta) suma el valor total de todos los productos
        // multiplicando la cantidad en stock por su precio de venta individual
        $stmt = $this->db->query("SELECT SUM(stock * precio_venta) AS total FROM productos WHERE activo = TRUE");
        // Obtiene la fila con la suma total
        $fila = $stmt->fetch();
        // Si el resultado no es nulo, lo devuelve como float; si es nulo (no hay
        // productos), devuelve 0.0 para evitar mostrar un valor vacío o errores
        return $fila['total'] ? (float)$fila['total'] : 0.0;
    }

    // =========================================================================
    // SECCIÓN: Catálogos
    // Estos métodos obtienen los datos de las tablas auxiliares (categorías,
    // subcategorías, marcas, modelos) para llenar los selects de formularios
    // =========================================================================

    // =========================================================================
    // Método: obtenerCategorias
    // Propósito: Obtiene todas las categorías activas ordenadas alfabéticamente
    // Retorna: array - Lista de categorías activas
    // =========================================================================
    public function obtenerCategorias(): array
    {
        // Selecciona todas las columnas de categorías que estén activas,
        // ordenadas alfabéticamente por nombre para mostrarlas en selects
        $stmt = $this->db->query("SELECT * FROM categorias WHERE activa = TRUE ORDER BY nombre");
        // fetchAll() devuelve todas las filas como un array de arrays asociativos
        return $stmt->fetchAll();
    }

    // =========================================================================
    // Método: obtenerSubcategorias
    // Propósito: Obtiene todas las subcategorías activas ordenadas alfabéticamente
    // Retorna: array - Lista de subcategorías activas
    // =========================================================================
    public function obtenerSubcategorias(): array
    {
        // Selecciona todas las columnas de subcategorías activas, ordenadas
        // alfabéticamente por nombre
        $stmt = $this->db->query("SELECT * FROM subcategorias WHERE activa = TRUE ORDER BY nombre");
        // Devuelve todas las filas como un array de arrays asociativos
        return $stmt->fetchAll();
    }

    // =========================================================================
    // Método: obtenerMarcas
    // Propósito: Obtiene todas las marcas registradas ordenadas alfabéticamente
    // Retorna: array - Lista de marcas
    // =========================================================================
    public function obtenerMarcas(): array
    {
        // Selecciona todas las columnas de la tabla marcas, ordenadas por nombre
        $stmt = $this->db->query("SELECT * FROM marcas ORDER BY nombre");
        // Devuelve todas las filas como un array de arrays asociativos
        return $stmt->fetchAll();
    }

    // =========================================================================
    // Método: obtenerModelos
    // Propósito: Obtiene todos los modelos registrados ordenados alfabéticamente
    // Retorna: array - Lista de modelos
    // =========================================================================
    public function obtenerModelos(): array
    {
        // Selecciona todas las columnas de la tabla modelos, ordenadas por nombre
        $stmt = $this->db->query("SELECT * FROM modelos ORDER BY nombre");
        // Devuelve todas las filas como un array de arrays asociativos
        return $stmt->fetchAll();
    }

    // =========================================================================
    // SECCIÓN: Movimientos de Stock
    // Estas funciones registran entradas y salidas de mercancía en la bitácora
    // de movimientos para mantener un historial completo de los cambios de stock
    // =========================================================================

    // =========================================================================
    // Método: registrarEntrada
    // Propósito: Registra una entrada de stock (llegada de mercancía): suma la
    //            cantidad al stock actual y guarda el movimiento en la bitácora
    // Parámetros:
    //   $producto_id - ID del producto que recibe el ingreso (int)
    //   $cantidad    - Cantidad de unidades que ingresan (int)
    //   $usuario_id  - ID del usuario que realizó el movimiento (int)
    //   $motivo      - Razón o justificación de la entrada (string)
    // Retorna: bool - true si la operación fue exitosa, false si hubo error
    // =========================================================================
    public function registrarEntrada(int $producto_id, int $cantidad, int $usuario_id, string $motivo): bool
    {
        // Consulta para obtener el stock actual del producto antes de modificarlo
        $sql = "SELECT stock FROM productos WHERE id = ?";
        // Prepara la consulta con placeholder para el ID del producto
        $stmt = $this->db->prepare($sql);
        // Ejecuta la consulta pasando el ID del producto
        $stmt->execute([$producto_id]);
        // Obtiene la fila con el stock actual del producto
        $producto = $stmt->fetch();

        // Si el producto no existe (fetch devolvió false), retorna false para
        // indicar que no se pudo completar la operación
        if (!$producto) return false;

        // Guarda el valor del stock antes de la entrada para registrar en bitácora
        $stock_anterior = (int)$producto['stock'];
        // Calcula el nuevo stock sumando la cantidad que ingresa al stock actual
        $stock_nuevo = $stock_anterior + $cantidad;

        // Actualiza el stock del producto en la tabla productos con el nuevo valor
        $sql1 = "UPDATE productos SET stock = ? WHERE id = ?";
        // Prepara la consulta de actualización
        $stmt = $this->db->prepare($sql1);
        // Ejecuta la actualización con el nuevo stock calculado y el ID del producto
        $stmt->execute([$stock_nuevo, $producto_id]);

        // Inserta un registro en la bitácora de movimientos con tipo 'entrada'
        // para dejar constancia de quién, cuándo y por qué se hizo el movimiento
        $sql2 = "INSERT INTO bitacora_movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, usuario_id, motivo) VALUES (?, 'entrada', ?, ?, ?, ?, ?)";
        // Prepara la consulta de inserción
        $stmt = $this->db->prepare($sql2);
        // Ejecuta la inserción con todos los datos del movimiento
        $stmt->execute([$producto_id, $cantidad, $stock_anterior, $stock_nuevo, $usuario_id, $motivo]);

        // Retorna true para indicar que la entrada se registró exitosamente
        return true;
    }

    // =========================================================================
    // Método: registrarSalida
    // Propósito: Registra una salida de stock (venta o uso): resta la cantidad
    //            al stock actual y guarda el movimiento en la bitácora
    // Parámetros:
    //   $producto_id - ID del producto del que sale mercancía (int)
    //   $cantidad    - Cantidad de unidades que salen (int)
    //   $usuario_id  - ID del usuario que realizó el movimiento (int)
    //   $motivo      - Razón o justificación de la salida (string)
    // Retorna: bool - true si la operación fue exitosa, false si hubo error o
    //                 stock insuficiente
    // =========================================================================
    public function registrarSalida(int $producto_id, int $cantidad, int $usuario_id, string $motivo): bool
    {
        // Consulta para obtener el stock actual del producto antes de modificarlo
        $sql = "SELECT stock FROM productos WHERE id = ?";
        // Prepara la consulta con placeholder para el ID del producto
        $stmt = $this->db->prepare($sql);
        // Ejecuta la consulta pasando el ID del producto
        $stmt->execute([$producto_id]);
        // Obtiene la fila con el stock actual del producto
        $producto = $stmt->fetch();

        // Si el producto no existe, retorna false para cancelar la operación
        if (!$producto) return false;

        // Guarda el valor del stock antes de la salida para registrar en bitácora
        $stock_anterior = (int)$producto['stock'];
        // Calcula el nuevo stock restando la cantidad que sale del stock actual
        $stock_nuevo = $stock_anterior - $cantidad;

        // Verifica que el stock nuevo no sea negativo (no se puede dar salida a
        // más producto del que existe); si es negativo, cancela la operación
        if ($stock_nuevo < 0) return false;

        // Actualiza el stock del producto en la tabla productos con el nuevo valor
        $sql1 = "UPDATE productos SET stock = ? WHERE id = ?";
        // Prepara la consulta de actualización
        $stmt = $this->db->prepare($sql1);
        // Ejecuta la actualización con el stock calculado y el ID del producto
        $stmt->execute([$stock_nuevo, $producto_id]);

        // Inserta un registro en la bitácora de movimientos con tipo 'salida'
        // para mantener un historial completo de todos los cambios de inventario
        $sql2 = "INSERT INTO bitacora_movimientos_stock (producto_id, tipo, cantidad, stock_anterior, stock_nuevo, usuario_id, motivo) VALUES (?, 'salida', ?, ?, ?, ?, ?)";
        // Prepara la consulta de inserción
        $stmt = $this->db->prepare($sql2);
        // Ejecuta la inserción con todos los datos del movimiento
        $stmt->execute([$producto_id, $cantidad, $stock_anterior, $stock_nuevo, $usuario_id, $motivo]);

        // Retorna true para indicar que la salida se registró exitosamente
        return true;
    }

    // =========================================================================
    // Método: obtenerMovimientos
    // Propósito: Obtiene el historial completo de movimientos de stock de un
    //            producto específico, ordenado del más reciente al más antiguo
    // Parámetros:
    //   $producto_id - ID del producto del cual se desea ver los movimientos (int)
    // Retorna: array - Lista de movimientos con datos del usuario que los realizó
    // =========================================================================
    public function obtenerMovimientos(int $producto_id): array
    {
        // Consulta SELECT con LEFT JOIN a la tabla usuarios para mostrar el
        // nombre de la persona que realizó cada movimiento; ordena por fecha
        // descendente (más reciente primero)
        $sql = "SELECT b.*, u.nombre AS usuario FROM bitacora_movimientos_stock b LEFT JOIN usuarios u ON b.usuario_id = u.id WHERE b.producto_id = ? ORDER BY b.fecha DESC";
        // Prepara la consulta con placeholder para el ID del producto
        $stmt = $this->db->prepare($sql);
        // Ejecuta la consulta pasando el ID del producto a consultar
        $stmt->execute([$producto_id]);
        // Devuelve todas las filas como un array de arrays asociativos
        return $stmt->fetchAll();
    }
}
