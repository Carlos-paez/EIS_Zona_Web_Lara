# Diagrama de Clases — EIS Zona Web Lara

> **Nota:** Esta aplicación es PHP vanilla con PDO (no usa Laravel ni Eloquent).  
> Todas las relaciones de base de datos se expresan mediante consultas SQL explícitas (JOINs).

---

## 1. Diagrama de Jerarquía

```
App\Core\Database                         [Singleton - PDO]
App\Core\Model                            [Abstracta]
  |
  +-- App\Models\Usuario
  +-- App\Models\Rol
  +-- App\Models\inventario
  +-- App\Models\Asesoria

App\Core\Router                           [Front Controller]

App\Controllers\AuthController            [use App\Models\Usuario]
App\Controllers\InventarioController      [use App\Models\inventario]
App\Controllers\RolController             [use App\Models\Rol]
```

---

## 2. Clases del Sistema

### 2.1 `App\Core\Database`

**Tipo:** Clase concreta — Singleton  
**Propósito:** Gestiona una única conexión PDO a MySQL.

| Atributos | Visibilidad | Tipo | Descripción |
|-----------|-------------|------|-------------|
| `$instance` | `private static` | `?PDO` | Única instancia de conexión PDO (inicialmente `null`) |

| Métodos | Visibilidad | Retorno | Descripción |
|---------|-------------|---------|-------------|
| `getConnection()` | `public static` | `PDO` | Retorna la conexión singleton; la crea en la primera llamada |

---

### 2.2 `App\Core\Model` (Abstracta)

**Tipo:** Clase abstracta  
**Propósito:** Clase base para todos los modelos del negocio. Proporciona la conexión PDO a las subclases.

| Atributos | Visibilidad | Tipo | Descripción |
|-----------|-------------|------|-------------|
| `$db` | `protected` | `PDO` | Conexión a base de datos (inyectada en el constructor vía `Database::getConnection()`) |

| Métodos | Visibilidad | Retorno | Descripción |
|---------|-------------|---------|-------------|
| `__construct()` | `public` | `void` | Asigna `Database::getConnection()` a `$this->db` |

---

### 2.3 `App\Models\Usuario`

**Extiende:** `App\Core\Model`  
**Tabla:** `usuarios`  
**Propósito:** CRUD de usuarios, autenticación con bcrypt.

| Métodos | Visibilidad | Retorno | Descripción |
|---------|-------------|---------|-------------|
| `crear(string $username, string $password, string $nombre, string $email, ?string $telefono, int $rol_id)` | `public` | `bool` | Inserta un usuario con password hasheado (bcrypt); `rol_id` por defecto = 2 |
| `obtenerTodos()` | `public` | `array` | Retorna todos los usuarios activos con nombre del rol (JOIN `roles`) |
| `obtenerPorId(int $id)` | `public` | `array\|false` | Retorna un usuario por ID |
| `obtenerPorUsername(string $username)` | `public` | `array\|false` | Retorna un usuario activo por nombre de usuario |
| `autenticar(string $username, string $password)` | `public` | `array\|false` | Verifica password con `password_verify()`, actualiza `ultimo_acceso`, retorna datos del usuario |
| `actualizar(int $id, string $nombre, string $email, ?string $telefono, ?int $rol_id, bool $activo)` | `public` | `bool` | Actualiza perfil del usuario; usa `COALESCE` para conservar rol si no se envía |
| `actualizarPassword(int $id, string $password)` | `public` | `bool` | Actualiza el password con nuevo hash bcrypt |
| `eliminar(int $id)` | `public` | `bool` | Elimina permanentemente un usuario |

---

### 2.4 `App\Models\Rol`

**Extiende:** `App\Core\Model`  
**Tablas:** `roles`, `permisos`, `rol_permiso`  
**Propósito:** CRUD de roles y permisos, asignación de permisos a roles y roles a usuarios.

| Métodos | Visibilidad | Retorno | Descripción |
|---------|-------------|---------|-------------|
| `listarRoles()` | `public` | `array` | Retorna todos los roles con conteo de usuarios (subquery) |
| `obtenerRolPorId(int $id)` | `public` | `array\|false` | Retorna un rol por ID |
| `crearRol(string $nombre, string $descripcion)` | `public` | `bool` | Inserta un nuevo rol |
| `actualizarRol(int $id, string $nombre, string $descripcion)` | `public` | `bool` | Actualiza nombre y descripción de un rol |
| `eliminarRol(int $id)` | `public` | `bool` | Elimina un rol solo si no tiene usuarios asignados |
| `obtenerPermisos()` | `public` | `array` | Retorna todos los permisos ordenados por nombre |
| `obtenerPermisosPorRol(int $rol_id)` | `public` | `array` | Retorna IDs de permisos asignados a un rol |
| `guardarPermisosRol(int $rol_id, array $permiso_ids)` | `public` | `bool` | Elimina permisos existentes e inserta nuevos en una transacción |
| `obtenerRoles()` | `public` | `array` | Retorna lista simplificada (id, nombre) de todos los roles |
| `obtenerUsuarios()` | `public` | `array` | Retorna todos los usuarios con nombre del rol (LEFT JOIN) |
| `asignarRolAUsuario(int $usuario_id, int $rol_id)` | `public` | `bool` | Asigna un rol a un usuario |
| `totalRoles()` | `public` | `int` | Cuenta todos los roles |
| `totalPermisos()` | `public` | `int` | Cuenta todos los permisos |

---

### 2.5 `App\Models\inventario`

**Extiende:** `App\Core\Model`  
**Tablas:** `productos`, `categorias`, `subcategorias`, `marcas`, `modelos`, `bitacora_movimientos_stock`  
**Propósito:** Gestión completa de inventario: CRUD de productos, KPIs, movimientos de stock.

| Métodos | Visibilidad | Retorno | Descripción |
|---------|-------------|---------|-------------|
| `crearProducto(string $codigo, string $nombre, int $categoria_id, int $stock, int $stock_minimo, float $costo_compra, float $precio_venta)` | `public` | `bool` | Inserta un nuevo producto |
| `obtenerProductos()` | `public` | `array` | Retorna todos los productos activos con nombre de categoría (LEFT JOIN) |
| `obtenerProductoPorId(int $id)` | `public` | `array\|false` | Retorna un producto con su categoría |
| `buscarProductos(string $termino)` | `public` | `array` | Busca productos por nombre o código (LIKE) |
| `actualizarProducto(int $id, string $codigo, string $nombre, int $categoria_id, int $stock, int $stock_minimo, float $costo_compra, float $precio_venta)` | `public` | `bool` | Actualiza todos los campos editables de un producto |
| `eliminarProducto(int $id)` | `public` | `bool` | Elimina permanentemente un producto |
| `totalProductos()` | `public` | `int` | Cuenta productos activos |
| `stockCritico()` | `public` | `int` | Cuenta productos con stock <= 0 |
| `stockBajo()` | `public` | `int` | Cuenta productos con stock > 0 pero <= stock_minimo |
| `valorTotalInventario()` | `public` | `float` | Calcula SUM(stock * precio_venta) |
| `obtenerCategorias()` | `public` | `array` | Retorna todas las categorías activas |
| `obtenerSubcategorias()` | `public` | `array` | Retorna todas las subcategorías activas |
| `obtenerMarcas()` | `public` | `array` | Retorna todas las marcas |
| `obtenerModelos()` | `public` | `array` | Retorna todos los modelos |
| `registrarEntrada(int $producto_id, int $cantidad, int $usuario_id, string $motivo)` | `public` | `bool` | Agrega stock y registra movimiento tipo 'entrada' |
| `registrarSalida(int $producto_id, int $cantidad, int $usuario_id, string $motivo)` | `public` | `bool` | Resta stock (falla si queda negativo) y registra movimiento tipo 'salida' |
| `obtenerMovimientos(int $producto_id)` | `public` | `array` | Retorna historial de movimientos de stock con nombre del usuario |

---

### 2.6 `App\Models\Asesoria`

**Extiende:** `App\Core\Model`  
**Tabla:** `asesorias`  
**Propósito:** Gestión de asesorías legales: registro de ciudadanos, documentos, estados.

| Métodos | Visibilidad | Retorno | Descripción |
|---------|-------------|---------|-------------|
| `crear(string $ciudadano, string $cedula, string $documento, string $descripcion, string $estado, ?int $usuario_id)` | `public` | `bool` | Inserta una asesoría; estado por defecto 'Pendiente' |
| `obtenerTodas()` | `public` | `array` | Retorna todas las asesorías con nombre del usuario registrador, ordenadas descendente |
| `obtenerPorEstado(string $estado)` | `public` | `array` | Filtra asesorías por estado (Pendiente, Finalizada, Archivada) |
| `obtenerPorId(int $id)` | `public` | `array\|false` | Retorna una asesoría por ID con nombre de usuario |
| `buscarPorCedula(string $cedula)` | `public` | `array` | Busca asesorías por cédula del ciudadano (LIKE) |
| `actualizar(int $id, string $ciudadano, string $cedula, string $documento, string $descripcion, string $estado)` | `public` | `bool` | Actualiza asesoría; asigna `fecha_cierre` si el estado es 'Finalizada' o 'Archivada' |
| `eliminar(int $id)` | `public` | `bool` | Elimina permanentemente una asesoría |
| `contarPorEstado()` | `public` | `array` | Retorna conteo de asesorías agrupadas por estado |

---

### 2.7 `App\Core\Router`

**Tipo:** Clase concreta — Front Controller  
**Propósito:** Punto de entrada único. Lee `?pagina=` de la URL, maneja autenticación, despacha peticiones AJAX a controladores y renderiza vistas dentro del layout maestro.

| Atributos | Visibilidad | Tipo | Descripción |
|-----------|-------------|------|-------------|
| `$pagina` | `private` | `string` | Nombre de la página solicitada (resuelta de `$_GET['pagina']`) |

| Métodos | Visibilidad | Retorno | Descripción |
|---------|-------------|---------|-------------|
| `__construct()` | `public` | `void` | Inicia sesión (`session_start()`) y resuelve `$this->pagina` |
| `handle()` | `public` | `void`| Despachador principal: verifica AJAX (inventario/roles), acciones de auth (login/logout), o renderiza vista |
| `resolvePage()` | `private` | `string` | Lee `$_GET['pagina']` con validación regex, por defecto 'login' |
| `isAjaxInventario()` | `private` | `bool` | Verifica si es petición AJAX de inventario |
| `isAjaxRoles()` | `private` | `bool` | Verifica si es petición AJAX de roles |
| `isAuthAction()` | `private` | `bool` | Verifica si es acción de autenticación (login_validate, logout) |
| `requireAuth()` | `private` | `void` | Finaliza con JSON error si no hay sesión activa |
| `runInventarioController()` | `private` | `void` | Instancia `InventarioController` y ejecuta `handle()` |
| `runRolController()` | `private` | `void` | Instancia `RolController` y ejecuta `handle()` |
| `runAuthAction()` | `private` | `void` | Para logout llama a `AuthController::logout()`; para login a `AuthController::login()` |
| `renderView()` | `private` | `void` | Carga archivo de vista, renderiza páginas públicas directamente o protegidas con layout |
| `renderWithLayout(string $contentView)` | `private` | `void` | Renderiza el layout maestro (`layout.php`) con mapeo de títulos de página |

---

### 2.8 `App\Controllers\AuthController`

**Dependencia:** `App\Models\Usuario`  
**Propósito:** Controlador de autenticación. Procesa login/logout y gestiona variables de sesión.

| Atributos | Visibilidad | Tipo | Descripción |
|-----------|-------------|------|-------------|
| `$model` | `private` | `Usuario` | Instancia del modelo Usuario |

| Métodos | Visibilidad | Retorno | Descripción |
|---------|-------------|---------|-------------|
| `__construct()` | `public` | `void` | Inicializa `$this->model = new Usuario()` |
| `login()` | `public` | `void` | Procesa formulario POST; llama a `$this->model->autenticar()`; redirige al dashboard o al login |
| `logout()` | `public` | `void` | Destruye sesión y redirige a `?pagina=login` |

---

### 2.9 `App\Controllers\InventarioController`

**Dependencia:** `App\Models\inventario`  
**Propósito:** API JSON para el módulo de inventario. Despacha acciones vía `$_GET['action']` usando `match()`.

| Atributos | Visibilidad | Tipo | Descripción |
|-----------|-------------|------|-------------|
| `$model` | `private` | `inventario` | Instancia del modelo Inventario |

| Métodos | Visibilidad | Retorno | Descripción |
|---------|-------------|---------|-------------|
| `__construct()` | `public` | `void` | Inicializa `$this->model = new inventario()` |
| `handle()` | `public` | `void` | Define header JSON, lee `$_GET['action']` y despacha al método correspondiente |
| `listar()` | `private` | `void` | Retorna JSON con todos los productos |
| `kpis()` | `private` | `void` | Retorna JSON con KPIs (total, stock crítico, stock bajo, valor total) |
| `categorias()` | `private` | `void` | Retorna JSON con las categorías |
| `detalle()` | `private` | `void` | Retorna JSON con detalle de un producto por `$_GET['id']` |
| `movimientos()` | `private` | `void` | Retorna JSON con historial de movimientos de un producto |
| `buscar()` | `private` | `void` | Retorna JSON con resultados de búsqueda por `$_POST['termino']` |
| `crear()` | `private` | `void` | Crea producto desde POST y retorna JSON success/error |
| `actualizar()` | `private` | `void` | Actualiza producto desde POST y retorna JSON success/error |
| `eliminar()` | `private` | `void` | Elimina producto por `$_POST['id']` y retorna JSON success/error |
| `entrada()` | `private` | `void` | Registra entrada de stock y retorna JSON |
| `salida()` | `private` | `void` | Registra salida de stock y retorna JSON |
| `json(bool $success, mixed $data, string $error)` | `private` | `void` | Helper para construir respuestas JSON uniformes |

**Acciones soportadas:** `listar`, `kpis`, `categorias`, `detalle`, `movimientos`, `buscar`, `crear`, `actualizar`, `eliminar`, `entrada`, `salida`

---

### 2.10 `App\Controllers\RolController`

**Dependencia:** `App\Models\Rol`  
**Propósito:** API JSON para el módulo de roles y permisos. Despacha acciones vía `$_GET['action']` usando `match()`.

| Atributos | Visibilidad | Tipo | Descripción |
|-----------|-------------|------|-------------|
| `$model` | `private` | `Rol` | Instancia del modelo Rol |

| Métodos | Visibilidad | Retorno | Descripción |
|---------|-------------|---------|-------------|
| `__construct()` | `public` | `void` | Inicializa `$this->model = new Rol()` |
| `handle()` | `public` | `void` | Define header JSON, lee `$_GET['action']` y despacha con try/catch |
| `listar()` | `private` | `void` | Retorna JSON con todos los roles y conteo de usuarios |
| `detalle()` | `private` | `void` | Retorna JSON con detalle de un rol por `$_GET['id']` |
| `crear()` | `private` | `void` | Crea rol desde `$_POST['nombre']` y `$_POST['descripcion']` |
| `actualizar()` | `private` | `void` | Actualiza rol desde POST |
| `eliminar()` | `private` | `void` | Elimina rol por `$_POST['id']` (bloquea eliminación del rol ID 1 — Administrador) |
| `permisos()` | `private` | `void` | Retorna JSON con todos los permisos |
| `permisosRol()` | `private` | `void` | Retorna JSON con IDs de permisos asignados a un rol |
| `guardarPermisos()` | `private` | `void` | Guarda asignación de permisos a un rol usando transacción |
| `usuarios()` | `private` | `void` | Retorna JSON con listas de usuarios y roles |
| `asignarRol()` | `private` | `void` | Asigna un rol a un usuario vía POST |
| `json(bool $success, mixed $data, string $error)` | `private` | `void` | Helper para construir respuestas JSON uniformes |

**Acciones soportadas:** `listar`, `detalle`, `crear`, `actualizar`, `eliminar`, `permisos`, `permisosRol`, `guardarPermisos`, `usuarios`, `asignarRol`

---

## 3. Matriz de Relaciones entre Clases

| Clase A | Clase B | Tipo de Relación | Descripción |
|---------|---------|-----------------|-------------|
| `Model` | `Database` | **Uso (Dependency)** | `Model` llama a `Database::getConnection()` en su constructor para obtener la conexión PDO. |
| `Usuario` | `Model` | **Herencia (Generalization)** | `Usuario` extiende la clase abstracta `Model`, heredando `$db`. |
| `Rol` | `Model` | **Herencia (Generalization)** | `Rol` extiende la clase abstracta `Model`, heredando `$db`. |
| `inventario` | `Model` | **Herencia (Generalization)** | `inventario` extiende la clase abstracta `Model`, heredando `$db`. |
| `Asesoria` | `Model` | **Herencia (Generalization)** | `Asesoria` extiende la clase abstracta `Model`, heredando `$db`. |
| `AuthController` | `Usuario` | **Asociación / Composición débil** | `AuthController` crea una instancia de `Usuario` en su constructor y la usa para autenticar. |
| `InventarioController` | `inventario` | **Asociación / Composición débil** | `InventarioController` crea una instancia de `inventario` en su constructor y delega operaciones CRUD. |
| `RolController` | `Rol` | **Asociación / Composición débil** | `RolController` crea una instancia de `Rol` en su constructor y delega operaciones CRUD. |
| `Router` | `AuthController` | **Uso (Dependency)** | `Router` instancia `AuthController` y llama a sus métodos estáticos `login()` y `logout()`. |
| `Router` | `InventarioController` | **Uso (Dependency)** | `Router` instancia `InventarioController` y llama a `handle()`. |
| `Router` | `RolController` | **Uso (Dependency)** | `Router` instancia `RolController` y llama a `handle()`. |
| `Router` | Archivos de Vista (`.php`) | **Uso (Dependency)** | `Router` incluye (`require`) archivos de vista y los renderiza dentro del layout. |

---

## 4. Relaciones a Nivel de Base de Datos (Lógico)

Aunque no hay relaciones Eloquent, el esquema SQL implementa las siguientes relaciones:

| Tabla A | Tabla B | Tipo | Campo Clave |
|---------|---------|------|-------------|
| `usuarios` | `roles` | Muchos a Uno (N:1) | `usuarios.rol_id` → `roles.id` |
| `roles` | `permisos` | Muchos a Muchos (N:M) | `rol_permiso.rol_id` → `roles.id` y `rol_permiso.permiso_id` → `permisos.id` |
| `productos` | `categorias` | Muchos a Uno (N:1) | `productos.categoria_id` → `categorias.id` |
| `productos` | `subcategorias` | Muchos a Uno (N:1) | `productos.subcategoria_id` → `subcategorias.id` |
| `productos` | `marcas` | Muchos a Uno (N:1) | `productos.marca_id` → `marcas.id` |
| `productos` | `modelos` | Muchos a Uno (N:1) | `productos.modelo_id` → `modelos.id` |
| `bitacora_movimientos_stock` | `productos` | Muchos a Uno (N:1) | `bitacora_movimientos_stock.producto_id` → `productos.id` |
| `bitacora_movimientos_stock` | `usuarios` | Muchos a Uno (N:1) | `bitacora_movimientos_stock.usuario_id` → `usuarios.id` |
| `asesorias` | `usuarios` | Muchos a Uno (N:1) | `asesorias.usuario_id` → `usuarios.id` |

---

## 5. Diagrama de Paquetes (Namespaces)

```
+---------------------------+
|       App\Core            |
|  +--------+  +---------+  |
|  |Database|  |  Model   |  |
|  |(Single-|  |(Abstract)|  |
|  |  ton)  |  +---------+  |
|  +--------+       |       |
|                   |herencia|
|  +--------+       v       |
|  | Router |               |
|  +--------+               |
+---------------------------+
         | usa
         v
+-----------------------------+
|       App\Models            |
|  +---------+  +----------+  |
|  | Usuario |  |   Rol    |  |
|  +---------+  +----------+  |
|  +-----------+  +---------+ |
|  | inventario|  | Asesoria| |
|  +-----------+  +---------+ |
+-----------------------------+
         ^ inyecta
         |
+-------------------------------+
|      App\Controllers          |
|  +---------------+            |
|  |AuthController|------------| usa Usuario
|  +---------------+            |
|  +-------------------+        |
|  |InventarioController|------| usa inventario
|  +-------------------+        |
|  +---------------+            |
|  | RolController  |----------| usa Rol
|  +---------------+            |
+-------------------------------+
```

---

## 6. Leyenda de Relaciones UML

| Símbolo | Significado |
|---------|-------------|
| Línea sólida con triángulo hueco (`⊳—`) | **Herencia (Generalization):** la clase hija extiende a la clase padre |
| Línea discontinua con flecha (`- - ->`) | **Dependencia / Uso:** una clase usa a otra como parámetro o la instancia temporalmente |
| Línea sólida con flecha (`—▶`) | **Asociación:** una clase tiene una referencia permanente a otra (atributo) |
| Línea sólida con rombo relleno (`—◆`) | **Composición:** la clase contenida no existe sin la contenedora |
| Línea sólida con rombo vacío (`—◇`) | **Agregación:** la clase contenida puede existir independientemente |

---

## 7. Notas Arquitectónicas

1. **Arquitectura híbrida:** El proyecto combina código OOP (PSR-4) con funciones procedurales legacy (`crud_users.php`, `crud_asesorias.php`). Los modelos OOP están reemplazando gradualmente a las funciones procedurales.
2. **Sin ORM:** A pesar del sufijo "Lara" en el nombre del proyecto, no usa Laravel ni Eloquent. Todo el acceso a datos es mediante PDO y SQL directo.
3. **Singleton de conexión:** `Database` garantiza una única conexión PDO por request.
4. **Controladores JSON API:** `InventarioController` y `RolController` son APIs JSON (sin renderizado de vistas). `AuthController` maneja redirecciones HTTP.
5. **Router como Front Controller:** `Router` es el único punto de entrada que decide qué controlador o vista ejecutar basado en el parámetro `?pagina=`.
