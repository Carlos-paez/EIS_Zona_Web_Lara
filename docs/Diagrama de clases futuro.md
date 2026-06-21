# Diagrama de Clases Completo — EIS System (Arquitectura MVC Futura)

## Visión General de la Arquitectura

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         PRESENTACIÓN (Views)                                │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │  login   │ │dashboard │ │inventario│ │  ventas  │ │ ... (11  │          │
│  │  .php    │ │  .php    │ │  .php    │ │  .php    │ │  vistas) │          │
│  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘ └──────────┘          │
│       │            │            │            │                              │
│       └────────────┴────────────┴────────────┴──────────────── Layout.php  │
├─────────────────────────────────────────────────────────────────────────────┤
│                       CONTROLADORES (Controllers)                           │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │   Auth   │ │Inventario│ │  Ventas  │ │  Cyber   │ │ ... (8   │          │
│  │Controller│ │Controller│ │Controller│ │Controller│ │ control.)│          │
│  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘ └──────────┘          │
│       │            │            │            │                              │
├───────┼────────────┼────────────┼────────────┼──────────────────────────────┤
│       ▼            ▼            ▼            ▼                              │
│                        SERVICIOS (Services)                                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │   Auth   │ │Inventario│ │   POS    │ │  Cyber   │ │Reporte   │          │
│  │ Service  │ │ Service  │ │ Service  │ │ Service  │ │ Service  │          │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
│       │            │            │            │                              │
├───────┼────────────┼────────────┼────────────┼──────────────────────────────┤
│       ▼            ▼            ▼            ▼                              │
│                        MODELOS (Models)                                     │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │  Model (abstract) — App\Core\Model                                   │   │
│  │  ├── Usuario         ├── Producto        ├── Categoria                │   │
│  │  ├── Cliente         ├── Proveedor       ├── Subcategoria             │   │
│  │  ├── Venta           ├── Solicitud       ├── Marca                    │   │
│  │  ├── DetalleVenta    ├── DetalleSolicitud├── ModeloProducto           │   │
│  │  ├── Activo          ├── TipoActivo      ├── EstacionCyber            │   │
│  │  ├── SesionCyber     ├── TarifaCyber     ├── Asesoria                 │   │
│  │  ├── ClienteAsesoria ├── BitacoraStock   ├── Rol                      │   │
│  │  └── ProductoProveedor ── UsuarioAsesoria                             │   │
│  └──────────────────────────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────────────────────────┤
│                      INFRAESTRUCTURA (Core)                                 │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │  Router  │ │ Database │ │  Model   │ │Request   │ │Middleware│          │
│  │          │ │(Singleton)│ │(Abstract)│ │Validator  │ │  Auth    │          │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Diagrama de Clases Completo (Mermaid)

```mermaid
classDiagram
    class Router {
        -string $pagina
        -array $middlewares
        +__construct()
        +handle() void
        +addMiddleware(Middleware) void
        -resolvePage() string
        -runController(string $name) void
        -renderView(string $view) void
    }

    class Middleware {
        <<interface>>
        +handle(Request $request) bool
    }

    class AuthMiddleware {
        +handle(Request $request) bool
    }

    class CsrfMiddleware {
        +handle(Request $request) bool
    }

    class Request {
        -array $get
        -array $post
        -array $session
        -array $files
        -string $method
        +__construct()
        +input(string $key) mixed
        +method() string
        +validate(array $rules) array
        +isAjax() bool
    }

    class Database {
        -static ?PDO $instance
        +static getConnection() PDO
    }

    class Model {
        <<abstract>>
        #PDO $db
        +__construct()
    }

    %% ==================== CATALOGO MODELS ====================
    class Rol {
        +int $id
        +string $nombre
        +string $descripcion
        +obtenerTodos() array
        +obtenerPorId(int $id) array
        +crear(array $data) bool
    }

    class Subcategoria {
        +int $id
        +string $nombre
        +string $descripcion
        +bool $activa
        +obtenerTodas() array
        +obtenerPorId(int $id) array
        +crear(array $data) bool
    }

    class Categoria {
        +int $id
        +int $subcategoria_id
        +string $nombre
        +string $descripcion
        +bool $activa
        +obtenerTodas() array
        +obtenerPorId(int $id) array
        +crear(array $data) bool
        +obtenerPorSubcategoria(int $subcategoria_id) array
    }

    class Marca {
        +int $id
        +string $nombre
        +string $descripcion
        +obtenerTodas() array
        +crear(array $data) bool
    }

    class ModeloProducto {
        +int $id
        +int $marca_id
        +string $nombre
        +string $descripcion
        +obtenerTodas() array
        +obtenerPorMarca(int $marca_id) array
        +crear(array $data) bool
    }

    class TipoActivo {
        +int $id
        +string $nombre
        +string $descripcion
        +obtenerTodas() array
        +crear(array $data) bool
    }

    class TarifaCyber {
        +int $id
        +string $nombre
        +float $precio_por_hora
        +int $tiempo_minimo
        +bool $activa
        +obtenerTodas() array
        +obtenerActivas() array
        +crear(array $data) bool
    }

    %% ==================== MAESTRA MODELS ====================
    class Usuario {
        +int $id
        +string $username
        +string $password_hash
        +string $nombre
        +string $email
        +string $telefono
        +bool $activo
        +int $rol_id
        +string $ultimo_acceso
        +autenticar(string $username, string $password) array|false
        +crear(array $data) bool
        +obtenerTodos() array
        +obtenerPorId(int $id) array
        +actualizar(int $id, array $data) bool
        +actualizarPassword(int $id, string $password) bool
        +eliminar(int $id) bool
    }

    class Cliente {
        +int $id
        +string $cedula_rif
        +string $nombre
        +string $telefono
        +string $email
        +string $direccion
        +bool $activo
        +obtenerTodos() array
        +obtenerPorId(int $id) array
        +buscar(string $termino) array
        +crear(array $data) bool
        +actualizar(int $id, array $data) bool
    }

    class ClienteAsesoria {
        +int $id
        +string $cedula
        +string $nombre
        +string $email
        +string $telefono
        +string $direccion
        +string $notas_expediente
        +obtenerTodos() array
        +obtenerPorId(int $id) array
        +buscarPorCedula(string $cedula) array
        +crear(array $data) bool
        +actualizar(int $id, array $data) bool
    }

    class Proveedor {
        +int $id
        +string $nombre
        +string $rif
        +string $tipo_documento
        +string $contacto
        +string $email
        +string $telefono
        +string $direccion
        +bool $es_proveedor_principal
        +bool $activo
        +obtenerTodos() array
        +obtenerPorId(int $id) array
        +buscar(string $termino) array
        +crear(array $data) bool
        +actualizar(int $id, array $data) bool
    }

    class Producto {
        +int $id
        +string $codigo
        +string $codigo_barras
        +string $nombre
        +string $descripcion
        +int $categoria_id
        +int $modelo_id
        +string $unidad_medida
        +int $stock
        +int $stock_minimo
        +string $ubicacion
        +float $costo_compra
        +float $precio_venta
        +bool $permite_descuento
        +string $estado_venta
        +bool $activo
        +obtenerTodos() array
        +obtenerPorId(int $id) array
        +buscar(string $termino) array
        +obtenerKpis() array
        +crear(array $data) bool
        +actualizar(int $id, array $data) bool
        +eliminar(int $id) bool
    }

    class Activo {
        +int $id
        +string $nombre
        +string $descripcion
        +int $tipo_activo_id
        +string $estado
        +string $ubicacion
        +float $valor_adquisicion
        +string $fecha_adquisicion
        +string $fecha_vencimiento
        +int $responsable_id
        +string $notas
        +obtenerTodos() array
        +obtenerPorId(int $id) array
        +obtenerPorTipo(int $tipo_id) array
        +crear(array $data) bool
        +actualizar(int $id, array $data) bool
    }

    class EstacionCyber {
        +int $id
        +string $nombre
        +string $estado
        +int $tarifa_id
        +string $especificaciones
        +string $ip_local
        +string $mac_address
        +obtenerTodas() array
        +obtenerPorEstado(string $estado) array
        +cambiarEstado(int $id, string $estado) bool
        +crear(array $data) bool
    }

    %% ==================== TRANSACCIONAL MODELS ====================
    class Venta {
        +int $id
        +string $fecha
        +int $usuario_id
        +int $cliente_id
        +float $subtotal
        +float $descuento
        +float $total
        +string $estado
        +string $notas
        +obtenerTodas() array
        +obtenerPorId(int $id) array
        +obtenerPorFecha(string $desde, string $hasta) array
        +crear(array $data, array $detalles) int
        +actualizarEstado(int $id, string $estado) bool
        +obtenerEstadisticas() array
    }

    class DetalleVenta {
        +int $id
        +int $venta_id
        +int $producto_id
        +int $cantidad
        +float $precio_unitario
        +float $descuento
        +float $subtotal
        +obtenerPorVenta(int $venta_id) array
        +crear(array $data) bool
    }

    class Solicitud {
        +int $id
        +string $codigo
        +int $proveedor_id
        +string $fecha
        +string $fecha_estimada_entrega
        +int $tiempo_entrega_dias
        +float $subtotal
        +float $total
        +string $estado
        +int $usuario_id
        +string $notas
        +obtenerTodas() array
        +obtenerPorId(int $id) array
        +obtenerPorEstado(string $estado) array
        +crear(array $data, array $detalles) int
        +actualizarEstado(int $id, string $estado) bool
    }

    class DetalleSolicitud {
        +int $id
        +int $solicitud_id
        +int $producto_id
        +int $cantidad_solicitada
        +int $cantidad_recibida
        +float $precio_unitario_estimado
        +float $subtotal
        +obtenerPorSolicitud(int $solicitud_id) array
        +actualizarCantidadRecibida(int $id, int $cantidad) bool
    }

    class SesionCyber {
        +int $id
        +int $estacion_id
        +int $usuario_id
        +int $cliente_id
        +string $hora_inicio
        +string $hora_fin
        +float $costo_total
        +string $estado
        +obtenerActivas() array
        +obtenerPorId(int $id) array
        +iniciar(array $data) int
        +cerrar(int $id) bool
        +obtenerSesionesDelDia() array
    }

    class Asesoria {
        +int $id
        +int $cliente_asesoria_id
        +string $documento
        +string $descripcion
        +string $estado
        +string $fecha_registro
        +string $fecha_cierre
        +obtenerTodas() array
        +obtenerPorId(int $id) array
        +obtenerPorEstado(string $estado) array
        +buscarPorCedula(string $cedula) array
        +crear(array $data) bool
        +actualizar(int $id, array $data) bool
        +eliminar(int $id) bool
        +contarPorEstado() array
    }

    class BitacoraStock {
        +int $id
        +int $producto_id
        +string $tipo
        +int $cantidad
        +int $stock_anterior
        +int $stock_nuevo
        +float $precio_unitario
        +float $costo_total
        +string $fecha
        +int $usuario_id
        +string $referencia_tipo
        +int $referencia_id
        +string $motivo
        +obtenerPorProducto(int $producto_id) array
        +obtenerPorFecha(string $desde, string $hasta) array
        +registrarMovimiento(int $producto_id, string $tipo, int $cantidad, int $usuario_id, string $motivo, string $ref_tipo, int $ref_id) bool
    }

    %% ==================== PUENTE MODELS ====================
    class ProductoProveedor {
        +int $producto_id
        +int $proveedor_id
        +string $codigo_proveedor
        +float $precio_compra
        +int $tiempo_entrega_dias
        +obtenerPorProducto(int $producto_id) array
        +obtenerPorProveedor(int $proveedor_id) array
        +asociar(array $data) bool
        +desasociar(int $producto_id, int $proveedor_id) bool
    }

    class UsuarioAsesoria {
        +int $usuario_id
        +int $asesoria_id
        +string $rol_en_asesoria
        +obtenerPorAsesoria(int $asesoria_id) array
        +obtenerPorUsuario(int $usuario_id) array
        +asignar(int $usuario_id, int $asesoria_id, string $rol) bool
        +remover(int $usuario_id, int $asesoria_id) bool
    }

    %% ==================== CONTROLLERS ====================
    class AuthController {
        -Usuario $usuarioModel
        -AuthService $authService
        +__construct()
        +login() void
        +logout() void
        +perfil() void
    }

    class DashboardController {
        -Venta $ventaModel
        -Producto $productoModel
        -SesionCyber $sesionModel
        -Solicitud $solicitudModel
        +__construct()
        +index() void
        +obtenerKpis() void
    }

    class InventarioController {
        -Producto $productoModel
        -BitacoraStock $bitacoraModel
        -Categoria $categoriaModel
        -InventarioService $inventarioService
        +__construct()
        +handle() void
        -listar() void
        -kpis() void
        -categorias() void
        -detalle() void
        -movimientos() void
        -buscar() void
        -crear() void
        -actualizar() void
        -eliminar() void
        -entrada() void
        -salida() void
        -json(bool $success, mixed $data, string $error) void
    }

    class VentasController {
        -Venta $ventaModel
        -DetalleVenta $detalleModel
        -Producto $productoModel
        -Cliente $clienteModel
        -PosService $posService
        +__construct()
        +index() void
        +listarProductos() void
        +registrarVenta() void
        +obtenerHistorial() void
        +obtenerDetalle(int $id) void
        +anularVenta(int $id) void
    }

    class ProveedoresController {
        -Proveedor $proveedorModel
        -Solicitud $solicitudModel
        -DetalleSolicitud $detalleModel
        -Producto $productoModel
        +__construct()
        +index() void
        +listar() void
        +crearSolicitud() void
        +aprobarSolicitud(int $id) void
        +recibirSolicitud(int $id) void
        +cancelarSolicitud(int $id) void
    }

    class CyberController {
        -EstacionCyber $estacionModel
        -SesionCyber $sesionModel
        -TarifaCyber $tarifaModel
        -Cliente $clienteModel
        -CyberService $cyberService
        +__construct()
        +index() void
        +listarEstaciones() void
        +iniciarSesion() void
        +cerrarSesion(int $id) void
        +obtenerSesionesActivas() void
    }

    class ActivosController {
        -Activo $activoModel
        -TipoActivo $tipoActivoModel
        -Usuario $usuarioModel
        +__construct()
        +index() void
        +listar() void
        +crear() void
        +actualizar(int $id) void
        +cambiarEstado(int $id) void
    }

    class AsesoriasController {
        -Asesoria $asesoriaModel
        -ClienteAsesoria $clienteModel
        -UsuarioAsesoria $uaModel
        +__construct()
        +index() void
        +listar() void
        +listarPorEstado() void
        +buscar(string $termino) void
        +crear() void
        +actualizar(int $id) void
        +eliminar(int $id) void
    }

    class UsuariosController {
        -Usuario $usuarioModel
        -Rol $rolModel
        +__construct()
        +index() void
        +listar() void
        +crear() void
        +actualizar(int $id) void
        +actualizarPassword(int $id) void
        +eliminar(int $id) void
    }

    class ReportesController {
        -Venta $ventaModel
        -Producto $productoModel
        -SesionCyber $sesionModel
        -ReporteService $reporteService
        +__construct()
        +index() void
        +ventas(string $desde, string $hasta) void
        +inventario() void
        +cyber(string $desde, string $hasta) void
        +exportarPdf(string $tipo) void
        +exportarExcel(string $tipo) void
    }

    %% ==================== SERVICES ====================
    class AuthService {
        -Usuario $usuarioModel
        +__construct(Usuario $usuarioModel)
        +autenticar(string $username, string $password) array|false
        +estaAutenticado() bool
        +tienePermiso(string $permiso) bool
        +generarTokenCsrf() string
        +verificarTokenCsrf(string $token) bool
    }

    class InventarioService {
        -Producto $productoModel
        -BitacoraStock $bitacoraModel
        +__construct(Producto $productoModel, BitacoraStock $bitacoraModel)
        +registrarEntrada(int $producto_id, int $cantidad, int $usuario_id, string $motivo) bool
        +registrarSalida(int $producto_id, int $cantidad, int $usuario_id, string $motivo) bool
        +ajustarStock(int $producto_id, int $nuevo_stock, int $usuario_id, string $motivo) bool
        +obtenerKpis() array
        +obtenerProductosCriticos() array
    }

    class PosService {
        -Venta $ventaModel
        -DetalleVenta $detalleModel
        -Producto $productoModel
        -BitacoraStock $bitacoraModel
        +__construct(Venta $ventaModel, DetalleVenta $detalleModel, Producto $productoModel, BitacoraStock $bitacoraModel)
        +procesarVenta(array $carrito, int $usuario_id, ?int $cliente_id, float $descuento) int
        +calcularSubtotal(array $items) float
        +validarStock(array $items) array
        +anularVenta(int $venta_id) bool
    }

    class CyberService {
        -SesionCyber $sesionModel
        -EstacionCyber $estacionModel
        -TarifaCyber $tarifaModel
        +__construct(SesionCyber $sesionModel, EstacionCyber $estacionModel, TarifaCyber $tarifaModel)
        +abrirSesion(int $estacion_id, int $usuario_id, ?int $cliente_id) int
        +cerrarSesion(int $sesion_id) array
        +calcularCosto(int $sesion_id) float
        +obtenerEstacionesDisponibles() array
        +obtenerResumenDelDia() array
    }

    class ReporteService {
        -Venta $ventaModel
        -Producto $productoModel
        -SesionCyber $sesionModel
        +__construct(Venta $ventaModel, Producto $productoModel, SesionCyber $sesionModel)
        +ventasPorPeriodo(string $desde, string $hasta) array
        +productosMasVendidos(int $limite) array
        +inventarioValorizado() array
        +cierreDiario(string $fecha) array
        +generarPdf(string $template, array $data) string
        +generarExcel(string $template, array $data) string
    }

    %% ==================== RELACIONES ====================

    %% Core - Middleware
    Router "1" *--> "*" Middleware : ejecuta
    Middleware <|.. AuthMiddleware : implementa
    Middleware <|.. CsrfMiddleware : implementa

    %% Core - Request
    Router --> Request : usa

    %% Model - Database
    Model --> Database : usa (Singleton)
    Model <|-- Rol : hereda
    Model <|-- Subcategoria : hereda
    Model <|-- Categoria : hereda
    Model <|-- Marca : hereda
    Model <|-- ModeloProducto : hereda
    Model <|-- TipoActivo : hereda
    Model <|-- TarifaCyber : hereda
    Model <|-- Usuario : hereda
    Model <|-- Cliente : hereda
    Model <|-- ClienteAsesoria : hereda
    Model <|-- Proveedor : hereda
    Model <|-- Producto : hereda
    Model <|-- Activo : hereda
    Model <|-- EstacionCyber : hereda
    Model <|-- Venta : hereda
    Model <|-- DetalleVenta : hereda
    Model <|-- Solicitud : hereda
    Model <|-- DetalleSolicitud : hereda
    Model <|-- SesionCyber : hereda
    Model <|-- Asesoria : hereda
    Model <|-- BitacoraStock : hereda
    Model <|-- ProductoProveedor : hereda
    Model <|-- UsuarioAsesoria : hereda

    %% Controllers -> Models
    AuthController --> Usuario : usa
    AuthController --> AuthService : usa
    DashboardController --> Venta : usa
    DashboardController --> Producto : usa
    DashboardController --> SesionCyber : usa
    DashboardController --> Solicitud : usa
    InventarioController --> Producto : usa
    InventarioController --> BitacoraStock : usa
    InventarioController --> Categoria : usa
    InventarioController --> InventarioService : usa
    VentasController --> Venta : usa
    VentasController --> DetalleVenta : usa
    VentasController --> Producto : usa
    VentasController --> Cliente : usa
    VentasController --> PosService : usa
    ProveedoresController --> Proveedor : usa
    ProveedoresController --> Solicitud : usa
    ProveedoresController --> DetalleSolicitud : usa
    ProveedoresController --> Producto : usa
    CyberController --> EstacionCyber : usa
    CyberController --> SesionCyber : usa
    CyberController --> TarifaCyber : usa
    CyberController --> Cliente : usa
    CyberController --> CyberService : usa
    ActivosController --> Activo : usa
    ActivosController --> TipoActivo : usa
    ActivosController --> Usuario : usa
    AsesoriasController --> Asesoria : usa
    AsesoriasController --> ClienteAsesoria : usa
    AsesoriasController --> UsuarioAsesoria : usa
    UsuariosController --> Usuario : usa
    UsuariosController --> Rol : usa
    ReportesController --> Venta : usa
    ReportesController --> Producto : usa
    ReportesController --> SesionCyber : usa
    ReportesController --> ReporteService : usa

    %% Services -> Models
    AuthService --> Usuario : usa
    InventarioService --> Producto : usa
    InventarioService --> BitacoraStock : usa
    PosService --> Venta : usa
    PosService --> DetalleVenta : usa
    PosService --> Producto : usa
    PosService --> BitacoraStock : usa
    CyberService --> SesionCyber : usa
    CyberService --> EstacionCyber : usa
    CyberService --> TarifaCyber : usa
    ReporteService --> Venta : usa
    ReporteService --> Producto : usa
    ReporteService --> SesionCyber : usa

    %% Relaciones BD entre modelos
    Categoria --> Subcategoria : pertenece a
    ModeloProducto --> Marca : pertenece a
    Producto --> Categoria : clasificado en
    Producto --> ModeloProducto : tiene modelo
    Activo --> TipoActivo : categorizado como
    Activo --> Usuario : responsable
    EstacionCyber --> TarifaCyber : aplica tarifa
    Venta --> Usuario : registrada por
    Venta --> Cliente : comprada por
    DetalleVenta --> Venta : pertenece a
    DetalleVenta --> Producto : contiene
    Solicitud --> Proveedor : dirigida a
    Solicitud --> Usuario : creada por
    DetalleSolicitud --> Solicitud : pertenece a
    DetalleSolicitud --> Producto : contiene
    SesionCyber --> EstacionCyber : usa
    SesionCyber --> Usuario : atendida por
    SesionCyber --> Cliente : pertenece a
    Asesoria --> ClienteAsesoria : solicitada por
    BitacoraStock --> Producto : audita
    BitacoraStock --> Usuario : generada por
    ProductoProveedor --> Producto : vincula
    ProductoProveedor --> Proveedor : vincula
    UsuarioAsesoria --> Usuario : asigna
    UsuarioAsesoria --> Asesoria : asigna
```

---

## Mapa de Paquetes (Namespaces)

```
App\
├── Core\
│   ├── Router.php              — Enrutador principal (Front Controller)
│   ├── Database.php            — Conexión PDO (Singleton)
│   ├── Model.php               — Clase abstracta base para modelos
│   ├── Request.php             — Encapsulador de la petición HTTP
│   ├── Middleware.php          — Interfaz de middleware
│   ├── AuthMiddleware.php      — Middleware de autenticación
│   └── CsrfMiddleware.php     — Middleware de protección CSRF
│
├── Controllers\
│   ├── AuthController.php      — Login, logout, perfil
│   ├── DashboardController.php — Panel de control con KPIs reales
│   ├── InventarioController.php— CRUD inventario + movimientos stock
│   ├── VentasController.php    — POS, registro y anulación de ventas
│   ├── ProveedoresController.php— Solicitudes de compra
│   ├── CyberController.php     — Estaciones, sesiones, tarifas
│   ├── ActivosController.php   — CRUD activos fijos
│   ├── AsesoriasController.php — CRUD asesorías legales
│   ├── UsuariosController.php  — CRUD usuarios y roles
│   └── ReportesController.php  — Generación de reportes
│
├── Models\
│   ├── Rol.php                 — Catálogo (1)
│   ├── Subcategoria.php        — Catálogo (2)
│   ├── Categoria.php           — Catálogo (3)
│   ├── Marca.php               — Catálogo (4)
│   ├── ModeloProducto.php      — Catálogo (5)
│   ├── TipoActivo.php          — Catálogo (6)
│   ├── TarifaCyber.php         — Catálogo (7)
│   ├── Usuario.php             — Maestra (8)
│   ├── Cliente.php             — Maestra (9)
│   ├── ClienteAsesoria.php     — Maestra (10)
│   ├── Proveedor.php           — Maestra (11)
│   ├── Producto.php            — Maestra (12)
│   ├── Activo.php              — Maestra (13)
│   ├── EstacionCyber.php       — Maestra (14)
│   ├── Venta.php               — Transaccional (15)
│   ├── DetalleVenta.php        — Transaccional (16)
│   ├── Solicitud.php           — Transaccional (17)
│   ├── DetalleSolicitud.php    — Transaccional (18)
│   ├── SesionCyber.php         — Transaccional (19)
│   ├── Asesoria.php            — Transaccional (20)
│   ├── BitacoraStock.php       — Bitácora (21)
│   ├── ProductoProveedor.php   — Puente M:N (22)
│   └── UsuarioAsesoria.php     — Puente M:N (23)
│
├── Services\
│   ├── AuthService.php         — Lógica de autenticación y permisos
│   ├── InventarioService.php   — Lógica de movimientos de stock y KPIs
│   ├── PosService.php          — Lógica de procesamiento de ventas
│   ├── CyberService.php        — Lógica de gestión de cybercafé
│   └── ReporteService.php     — Lógica de generación de reportes
│
└── Views\                      — Plantillas PHP (sin cambios)
    ├── login.php
    ├── dashboard.php
    ├── inventario.php
    ├── ventas.php
    ├── proveedores.php
    ├── ciberControl.php
    ├── reportes.php
    ├── activos.php
    ├── asesorias.php
    ├── usuarios.php
    └── menu.php
```

---

## Contraste: Estado Actual vs. Arquitectura Futura

| Capa | Actual | Futuro Completo |
|------|--------|-----------------|
| **Core** | Router, Database (Singleton), Model (abstract) | + Request encapsulador, Middleware (Auth + CSRF) |
| **Controllers** | 2 (Auth, Inventario) | 10 (todos los módulos) |
| **Models OOP** | 3 (inventario, Usuario, Asesoria) | 23 (todos los catálogos, maestras, transaccionales, puentes y bitácora) |
| **Services** | 0 (lógica en controladores/vistas) | 5 (Auth, Inventario, POS, Cyber, Reportes) |
| **Procedural legacy** | crud_users.php, crud_asesorias.php | Eliminados (migrados a OOP) |

## Resumen de Clases

| Categoría | Cantidad | Clases |
|-----------|----------|--------|
| **Core** | 6 | Router, Database, Model, Request, AuthMiddleware, CsrfMiddleware |
| **Controllers** | 10 | Auth, Dashboard, Inventario, Ventas, Proveedores, Cyber, Activos, Asesorias, Usuarios, Reportes |
| **Models** | 23 | Rol, Subcategoria, Categoria, Marca, ModeloProducto, TipoActivo, TarifaCyber, Usuario, Cliente, ClienteAsesoria, Proveedor, Producto, Activo, EstacionCyber, Venta, DetalleVenta, Solicitud, DetalleSolicitud, SesionCyber, Asesoria, BitacoraStock, ProductoProveedor, UsuarioAsesoria |
| **Services** | 5 | Auth, Inventario, POS, Cyber, Reportes |
| **Total** | **44** | |
