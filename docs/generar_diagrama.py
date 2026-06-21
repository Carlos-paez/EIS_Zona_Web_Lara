#!/usr/bin/env python3
"""Genera el diagrama de clases completo en formato .drawio (diagrams.net)."""

import xml.etree.ElementTree as ET
import xml.dom.minidom as md
import math

# ─── Datos del diagrama ──────────────────────────────────────────────────────

CLASSES = [
    # ── CORE ──
    {
        "name": "«singleton»\nDatabase",
        "attrs": "- static instance: ?PDO",
        "methods": "+ static getConnection(): PDO",
        "layer": "core",
        "stereo": "singleton"
    },
    {
        "name": "«abstract»\nModel",
        "attrs": "# db: PDO",
        "methods": "+ __construct()",
        "layer": "core",
        "stereo": "abstract"
    },
    {
        "name": "Router",
        "attrs": "- pagina: string",
        "methods": "+ __construct()\n+ handle(): void\n- resolvePage(): string\n- requireAuth(): void\n- runController(name): void\n- renderView(): void\n- renderWithLayout(view): void",
        "layer": "core"
    },
    {
        "name": "Request",
        "attrs": "- get: array\n- post: array\n- session: array\n- method: string",
        "methods": "+ __construct()\n+ input(key): mixed\n+ method(): string\n+ isAjax(): bool\n+ validate(rules): array",
        "layer": "core"
    },
    {
        "name": "«interface»\nMiddleware",
        "attrs": "",
        "methods": "+ handle(Request): bool",
        "layer": "core",
        "stereo": "interface"
    },
    {
        "name": "AuthMiddleware",
        "attrs": "",
        "methods": "+ handle(Request): bool",
        "layer": "core"
    },
    {
        "name": "CsrfMiddleware",
        "attrs": "",
        "methods": "+ handle(Request): bool",
        "layer": "core"
    },
    # ── MODELOS CATALOGO ──
    {
        "name": "Rol",
        "attrs": "+ id: int\n+ nombre: string\n+ descripcion: string",
        "methods": "+ listarRoles(): array\n+ obtenerRolPorId(id): array|false\n+ crearRol(nombre, desc): bool\n+ actualizarRol(id, ...): bool\n+ eliminarRol(id): bool\n+ totalRoles(): int",
        "layer": "catalogo"
    },
    {
        "name": "Permiso",
        "attrs": "+ id: int\n+ nombre: string\n+ descripcion: string\n+ icono: string",
        "methods": "+ obtenerTodos(): array\n+ obtenerPorId(id): array|false\n+ obtenerPorRol(rol_id): array\n+ totalPermisos(): int",
        "layer": "catalogo"
    },
    {
        "name": "RolPermiso",
        "attrs": "+ rol_id: int\n+ permiso_id: int",
        "methods": "+ guardarPermisosRol(rol_id, ids): bool\n+ obtenerPermisosPorRol(rol_id): array\n+ tienePermiso(rol_id, permiso_id): bool",
        "layer": "catalogo"
    },
    {
        "name": "Subcategoria",
        "attrs": "+ id: int\n+ nombre: string\n+ descripcion: string\n+ activa: bool",
        "methods": "+ obtenerTodas(): array\n+ obtenerPorId(id): array|false\n+ crear(...): bool\n+ actualizar(...): bool\n+ eliminar(id): bool",
        "layer": "catalogo"
    },
    {
        "name": "Categoria",
        "attrs": "+ id: int\n+ subcategoria_id: int\n+ nombre: string\n+ activa: bool",
        "methods": "+ obtenerTodas(): array\n+ obtenerPorId(id): array|false\n+ obtenerPorSubcategoria(sub_id): array\n+ crear(...): bool\n+ actualizar(...): bool\n+ eliminar(id): bool",
        "layer": "catalogo"
    },
    {
        "name": "Marca",
        "attrs": "+ id: int\n+ nombre: string\n+ descripcion: string",
        "methods": "+ obtenerTodas(): array\n+ obtenerPorId(id): array|false\n+ crear(...): bool\n+ actualizar(...): bool\n+ eliminar(id): bool",
        "layer": "catalogo"
    },
    {
        "name": "ModeloProducto",
        "attrs": "+ id: int\n+ marca_id: int\n+ nombre: string",
        "methods": "+ obtenerTodas(): array\n+ obtenerPorId(id): array|false\n+ obtenerPorMarca(marca_id): array\n+ crear(...): bool\n+ actualizar(...): bool\n+ eliminar(id): bool",
        "layer": "catalogo"
    },
    {
        "name": "TipoActivo",
        "attrs": "+ id: int\n+ nombre: string\n+ descripcion: string",
        "methods": "+ obtenerTodas(): array\n+ obtenerPorId(id): array|false\n+ crear(...): bool\n+ actualizar(...): bool\n+ eliminar(id): bool",
        "layer": "catalogo"
    },
    {
        "name": "TarifaCyber",
        "attrs": "+ id: int\n+ nombre: string\n+ precio_por_hora: float\n+ tiempo_minimo: int\n+ activa: bool",
        "methods": "+ obtenerTodas(): array\n+ obtenerActivas(): array\n+ obtenerPorId(id): array|false\n+ crear(...): bool\n+ actualizar(...): bool\n+ eliminar(id): bool",
        "layer": "catalogo"
    },
    # ── MODELOS MAESTRA ──
    {
        "name": "Usuario",
        "attrs": "+ id: int\n+ username: string\n+ nombre: string\n+ email: string\n+ activo: bool\n+ rol_id: int",
        "methods": "+ autenticar(user, pass): array|false\n+ crear(...): bool\n+ obtenerTodos(): array\n+ obtenerPorId(id): array|false\n+ actualizar(...): bool\n+ actualizarPassword(id, pass): bool\n+ eliminar(id): bool",
        "layer": "maestra"
    },
    {
        "name": "Cliente",
        "attrs": "+ id: int\n+ cedula_rif: string\n+ nombre: string\n+ telefono: string\n+ email: string\n+ activo: bool",
        "methods": "+ obtenerTodos(): array\n+ obtenerPorId(id): array|false\n+ buscar(termino): array\n+ crear(data): bool\n+ actualizar(id, data): bool\n+ eliminar(id): bool",
        "layer": "maestra"
    },
    {
        "name": "ClienteAsesoria",
        "attrs": "+ id: int\n+ cedula: string\n+ nombre: string\n+ email: string\n+ notas_expediente: string",
        "methods": "+ obtenerTodos(): array\n+ obtenerPorId(id): array|false\n+ buscarPorCedula(cedula): array\n+ crear(data): bool\n+ actualizar(id, data): bool\n+ eliminar(id): bool",
        "layer": "maestra"
    },
    {
        "name": "Proveedor",
        "attrs": "+ id: int\n+ nombre: string\n+ rif: string\n+ contacto: string\n+ email: string\n+ activo: bool",
        "methods": "+ obtenerTodos(): array\n+ obtenerPorId(id): array|false\n+ buscar(termino): array\n+ crear(data): bool\n+ actualizar(id, data): bool\n+ eliminar(id): bool",
        "layer": "maestra"
    },
    {
        "name": "Producto",
        "attrs": "+ id: int\n+ codigo: string\n+ nombre: string\n+ stock: int\n+ stock_minimo: int\n+ precio_venta: float\n+ activo: bool",
        "methods": "+ crear(...): bool\n+ obtenerTodos(): array\n+ obtenerPorId(id): array|false\n+ buscar(termino): array\n+ actualizar(...): bool\n+ eliminar(id): bool\n+ totalProductos(): int\n+ stockCritico(): int\n+ stockBajo(): int\n+ valorTotalInventario(): float",
        "layer": "maestra"
    },
    {
        "name": "Activo",
        "attrs": "+ id: int\n+ nombre: string\n+ tipo_activo_id: int\n+ estado: string\n+ valor_adquisicion: float\n+ responsable_id: int",
        "methods": "+ obtenerTodos(): array\n+ obtenerPorId(id): array|false\n+ obtenerPorTipo(tipo_id): array\n+ crear(data): bool\n+ actualizar(id, data): bool\n+ eliminar(id): bool",
        "layer": "maestra"
    },
    {
        "name": "EstacionCyber",
        "attrs": "+ id: int\n+ nombre: string\n+ estado: string\n+ tarifa_id: int\n+ ip_local: string",
        "methods": "+ obtenerTodas(): array\n+ obtenerPorEstado(estado): array\n+ cambiarEstado(id, estado): bool\n+ crear(data): bool\n+ actualizar(id, data): bool\n+ eliminar(id): bool",
        "layer": "maestra"
    },
    # ── MODELOS TRANSACCIONAL ──
    {
        "name": "Venta",
        "attrs": "+ id: int\n+ fecha: string\n+ usuario_id: int\n+ cliente_id: int\n+ subtotal: float\n+ total: float\n+ estado: string",
        "methods": "+ obtenerTodas(): array\n+ obtenerPorId(id): array|false\n+ obtenerPorFecha(desde, hasta): array\n+ crear(data, detalles): int\n+ actualizarEstado(id, estado): bool\n+ anular(id): bool\n+ obtenerEstadisticas(): array",
        "layer": "transaccional"
    },
    {
        "name": "DetalleVenta",
        "attrs": "+ id: int\n+ venta_id: int\n+ producto_id: int\n+ cantidad: int\n+ precio_unitario: float\n+ subtotal: float",
        "methods": "+ obtenerPorVenta(venta_id): array\n+ crear(data): bool",
        "layer": "transaccional"
    },
    {
        "name": "Solicitud",
        "attrs": "+ id: int\n+ codigo: string\n+ proveedor_id: int\n+ fecha: string\n+ estado: string\n+ usuario_id: int",
        "methods": "+ obtenerTodas(): array\n+ obtenerPorId(id): array|false\n+ obtenerPorEstado(estado): array\n+ crear(data, detalles): int\n+ aprobar(id): bool\n+ recibir(id): bool\n+ cancelar(id): bool",
        "layer": "transaccional"
    },
    {
        "name": "DetalleSolicitud",
        "attrs": "+ id: int\n+ solicitud_id: int\n+ producto_id: int\n+ cantidad_solicitada: int\n+ precio_unitario_est: float",
        "methods": "+ obtenerPorSolicitud(solicitud_id): array\n+ actualizarCantidadRecibida(id, cant): bool",
        "layer": "transaccional"
    },
    {
        "name": "SesionCyber",
        "attrs": "+ id: int\n+ estacion_id: int\n+ usuario_id: int\n+ cliente_id: int\n+ hora_inicio: string\n+ costo_total: float\n+ estado: string",
        "methods": "+ obtenerActivas(): array\n+ obtenerPorId(id): array|false\n+ iniciar(data): int\n+ cerrar(id): bool\n+ calcularCosto(sesion_id): float\n+ obtenerSesionesDelDia(): array",
        "layer": "transaccional"
    },
    {
        "name": "Asesoria",
        "attrs": "+ id: int\n+ cliente_asesoria_id: int\n+ documento: string\n+ descripcion: string\n+ estado: string\n+ fecha_registro: string",
        "methods": "+ crear(...): bool\n+ obtenerTodas(): array\n+ obtenerPorEstado(estado): array\n+ obtenerPorId(id): array|false\n+ buscarPorCedula(cedula): array\n+ actualizar(...): bool\n+ eliminar(id): bool\n+ contarPorEstado(): array",
        "layer": "transaccional"
    },
    {
        "name": "BitacoraStock",
        "attrs": "+ id: int\n+ producto_id: int\n+ tipo: string\n+ cantidad: int\n+ stock_anterior: int\n+ fecha: string\n+ usuario_id: int",
        "methods": "+ obtenerPorProducto(producto_id): array\n+ obtenerPorFecha(desde, hasta): array\n+ registrarMovimiento(...): bool",
        "layer": "transaccional"
    },
    {
        "name": "ProductoProveedor",
        "attrs": "+ producto_id: int\n+ proveedor_id: int\n+ codigo_proveedor: string\n+ precio_compra: float\n+ tiempo_entrega_dias: int",
        "methods": "+ obtenerPorProducto(producto_id): array\n+ obtenerPorProveedor(proveedor_id): array\n+ asociar(data): bool\n+ desasociar(prod_id, prov_id): bool",
        "layer": "transaccional"
    },
    {
        "name": "UsuarioAsesoria",
        "attrs": "+ usuario_id: int\n+ asesoria_id: int\n+ rol_en_asesoria: string",
        "methods": "+ obtenerPorAsesoria(asesoria_id): array\n+ obtenerPorUsuario(usuario_id): array\n+ asignar(user_id, asesoria_id, rol): bool\n+ remover(user_id, asesoria_id): bool",
        "layer": "transaccional"
    },
    # ── CONTROLLERS ──
    {
        "name": "AuthController",
        "attrs": "- usuarioModel: Usuario\n- authService: AuthService",
        "methods": "+ __construct()\n+ login(): void\n+ logout(): void\n+ perfil(): void",
        "layer": "controllers"
    },
    {
        "name": "DashboardController",
        "attrs": "- ventaModel: Venta\n- productoModel: Producto\n- sesionModel: SesionCyber",
        "methods": "+ __construct()\n+ index(): void\n+ obtenerKpis(): void",
        "layer": "controllers"
    },
    {
        "name": "InventarioController",
        "attrs": "- productoModel: Producto\n- bitacoraModel: BitacoraStock\n- categoriaModel: Categoria\n- inventarioService: InventarioService",
        "methods": "+ __construct()\n+ handle(): void\n- listar() / kpis() / categorias()\n- detalle() / movimientos() / buscar()\n- crear() / actualizar() / eliminar()\n- entrada() / salida()\n- json(success, data, error): void",
        "layer": "controllers"
    },
    {
        "name": "VentasController",
        "attrs": "- ventaModel: Venta\n- detalleModel: DetalleVenta\n- productoModel: Producto\n- clienteModel: Cliente\n- posService: PosService",
        "methods": "+ __construct()\n+ index(): void\n+ listarProductos(): void\n+ registrarVenta(): void\n+ obtenerHistorial(): void\n+ obtenerDetalle(id): void\n+ anularVenta(id): void",
        "layer": "controllers"
    },
    {
        "name": "ProveedoresController",
        "attrs": "- proveedorModel: Proveedor\n- solicitudModel: Solicitud\n- detalleModel: DetalleSolicitud\n- productoModel: Producto",
        "methods": "+ __construct()\n+ index(): void\n+ listarProveedores(): void\n+ crearSolicitud(): void\n+ aprobarSolicitud(id): void\n+ recibirSolicitud(id): void\n+ cancelarSolicitud(id): void",
        "layer": "controllers"
    },
    {
        "name": "CyberController",
        "attrs": "- estacionModel: EstacionCyber\n- sesionModel: SesionCyber\n- tarifaModel: TarifaCyber\n- clienteModel: Cliente\n- cyberService: CyberService",
        "methods": "+ __construct()\n+ index(): void\n+ listarEstaciones(): void\n+ iniciarSesion(): void\n+ cerrarSesion(id): void\n+ obtenerSesionesActivas(): void\n+ listarTarifas(): void",
        "layer": "controllers"
    },
    {
        "name": "ActivosController",
        "attrs": "- activoModel: Activo\n- tipoActivoModel: TipoActivo\n- usuarioModel: Usuario",
        "methods": "+ __construct()\n+ index(): void\n+ listar(): void\n+ crear(): void\n+ actualizar(id): void\n+ cambiarEstado(id): void\n+ eliminar(id): void",
        "layer": "controllers"
    },
    {
        "name": "AsesoriasController",
        "attrs": "- asesoriaModel: Asesoria\n- clienteModel: ClienteAsesoria\n- uaModel: UsuarioAsesoria",
        "methods": "+ __construct()\n+ index(): void\n+ listar(): void\n+ listarPorEstado(): void\n+ buscar(termino): void\n+ crear(): void\n+ actualizar(id): void\n+ eliminar(id): void",
        "layer": "controllers"
    },
    {
        "name": "UsuariosController",
        "attrs": "- usuarioModel: Usuario\n- rolModel: Rol\n- permisoModel: Permiso\n- rolPermisoModel: RolPermiso",
        "methods": "+ __construct()\n+ index(): void\n+ listar(): void\n+ crear(): void\n+ actualizar(id): void\n+ actualizarPassword(id): void\n+ eliminar(id): void",
        "layer": "controllers"
    },
    {
        "name": "ReportesController",
        "attrs": "- ventaModel: Venta\n- productoModel: Producto\n- sesionModel: SesionCyber\n- reporteService: ReporteService",
        "methods": "+ __construct()\n+ index(): void\n+ ventas(desde, hasta): void\n+ inventario(): void\n+ cyber(desde, hasta): void\n+ exportarPdf(tipo): void\n+ exportarExcel(tipo): void",
        "layer": "controllers"
    },
    # ── SERVICES ──
    {
        "name": "AuthService",
        "attrs": "- usuarioModel: Usuario\n- rolPermisoModel: RolPermiso",
        "methods": "+ __construct(Usuario, RolPermiso)\n+ autenticar(user, pass): array|false\n+ estaAutenticado(): bool\n+ tienePermiso(permiso): bool\n+ generarTokenCsrf(): string\n+ verificarTokenCsrf(token): bool",
        "layer": "services"
    },
    {
        "name": "InventarioService",
        "attrs": "- productoModel: Producto\n- bitacoraModel: BitacoraStock",
        "methods": "+ __construct(Producto, BitacoraStock)\n+ registrarEntrada(...): bool\n+ registrarSalida(...): bool\n+ ajustarStock(...): bool\n+ obtenerKpis(): array\n+ obtenerProductosCriticos(): array",
        "layer": "services"
    },
    {
        "name": "PosService",
        "attrs": "- ventaModel: Venta\n- detalleModel: DetalleVenta\n- productoModel: Producto\n- bitacoraModel: BitacoraStock",
        "methods": "+ __construct(...)\n+ procesarVenta(carrito, user_id, cliente_id, desc): int\n+ calcularSubtotal(items): float\n+ validarStock(items): array\n+ anularVenta(venta_id): bool",
        "layer": "services"
    },
    {
        "name": "CyberService",
        "attrs": "- sesionModel: SesionCyber\n- estacionModel: EstacionCyber\n- tarifaModel: TarifaCyber",
        "methods": "+ __construct(...)\n+ abrirSesion(estacion_id, ...): int\n+ cerrarSesion(sesion_id): array\n+ calcularCosto(sesion_id): float\n+ obtenerEstacionesDisponibles(): array\n+ obtenerResumenDelDia(): array",
        "layer": "services"
    },
    {
        "name": "ReporteService",
        "attrs": "- ventaModel: Venta\n- productoModel: Producto\n- sesionModel: SesionCyber",
        "methods": "+ __construct(...)\n+ ventasPorPeriodo(desde, hasta): array\n+ productosMasVendidos(limite): array\n+ inventarioValorizado(): array\n+ cierreDiario(fecha): array\n+ generarPdf(template, data): string\n+ generarExcel(template, data): string",
        "layer": "services"
    },
]

# ── Relaciones ───────────────────────────────────────────────────────────────
# (source_idx, target_idx, label, style)
# style: "extends" = generalization (triangle), "uses" = dependency (dashed arrow), "association" = solid

RELATIONS = [
    # Herencia: Model ← todos los modelos
    (1, 7, "extends", "extends"),   # Model ← Rol (1→7)
    (1, 8, "extends", "extends"),   # Model ← Permiso
    (1, 9, "extends", "extends"),   # Model ← RolPermiso
    (1, 10, "extends", "extends"),  # Model ← Subcategoria
    (1, 11, "extends", "extends"),  # Model ← Categoria
    (1, 12, "extends", "extends"),  # Model ← Marca
    (1, 13, "extends", "extends"),  # Model ← ModeloProducto
    (1, 14, "extends", "extends"),  # Model ← TipoActivo
    (1, 15, "extends", "extends"),  # Model ← TarifaCyber
    (1, 16, "extends", "extends"),  # Model ← Usuario
    (1, 17, "extends", "extends"),  # Model ← Cliente
    (1, 18, "extends", "extends"),  # Model ← ClienteAsesoria
    (1, 19, "extends", "extends"),  # Model ← Proveedor
    (1, 20, "extends", "extends"),  # Model ← Producto
    (1, 21, "extends", "extends"),  # Model ← Activo
    (1, 22, "extends", "extends"),  # Model ← EstacionCyber
    (1, 23, "extends", "extends"),  # Model ← Venta
    (1, 24, "extends", "extends"),  # Model ← DetalleVenta
    (1, 25, "extends", "extends"),  # Model ← Solicitud
    (1, 26, "extends", "extends"),  # Model ← DetalleSolicitud
    (1, 27, "extends", "extends"),  # Model ← SesionCyber
    (1, 28, "extends", "extends"),  # Model ← Asesoria
    (1, 29, "extends", "extends"),  # Model ← BitacoraStock
    (1, 30, "extends", "extends"),  # Model ← ProductoProveedor
    (1, 31, "extends", "extends"),  # Model ← UsuarioAsesoria
    # Implementación: Middleware ← AuthMiddleware, CsrfMiddleware
    (4, 5, "implements", "extends"),  # <<interface>>Middleware ← AuthMiddleware
    (4, 6, "implements", "extends"),  # <<interface>>Middleware ← CsrfMiddleware
    # Dependencias Core
    (1, 0, "getConnection()", "uses"),  # Model -> Database
    (2, 3, "usa", "uses"),  # Router -> Request
    # Dependencias Controllers → Models
    (32, 16, "usa", "uses"),  # AuthController -> Usuario
    (32, 41, "usa", "uses"),  # AuthController -> AuthService
    (33, 23, "usa", "uses"),  # DashboardController -> Venta
    (33, 20, "usa", "uses"),  # DashboardController -> Producto
    (33, 27, "usa", "uses"),  # DashboardController -> SesionCyber
    (34, 20, "usa", "uses"),  # InventarioController -> Producto
    (34, 29, "usa", "uses"),  # InventarioController -> BitacoraStock
    (34, 11, "usa", "uses"),  # InventarioController -> Categoria
    (34, 42, "usa", "uses"),  # InventarioController -> InventarioService
    (35, 23, "usa", "uses"),  # VentasController -> Venta
    (35, 24, "usa", "uses"),  # VentasController -> DetalleVenta
    (35, 20, "usa", "uses"),  # VentasController -> Producto
    (35, 17, "usa", "uses"),  # VentasController -> Cliente
    (35, 43, "usa", "uses"),  # VentasController -> PosService
    (36, 19, "usa", "uses"),  # ProveedoresController -> Proveedor
    (36, 25, "usa", "uses"),  # ProveedoresController -> Solicitud
    (36, 26, "usa", "uses"),  # ProveedoresController -> DetalleSolicitud
    (36, 20, "usa", "uses"),  # ProveedoresController -> Producto
    (37, 22, "usa", "uses"),  # CyberController -> EstacionCyber
    (37, 27, "usa", "uses"),  # CyberController -> SesionCyber
    (37, 15, "usa", "uses"),  # CyberController -> TarifaCyber
    (37, 17, "usa", "uses"),  # CyberController -> Cliente
    (37, 44, "usa", "uses"),  # CyberController -> CyberService
    (38, 21, "usa", "uses"),  # ActivosController -> Activo
    (38, 14, "usa", "uses"),  # ActivosController -> TipoActivo
    (38, 16, "usa", "uses"),  # ActivosController -> Usuario
    (39, 28, "usa", "uses"),  # AsesoriasController -> Asesoria
    (39, 18, "usa", "uses"),  # AsesoriasController -> ClienteAsesoria
    (39, 31, "usa", "uses"),  # AsesoriasController -> UsuarioAsesoria
    (40, 16, "usa", "uses"),  # UsuariosController -> Usuario
    (40, 7, "usa", "uses"),   # UsuariosController -> Rol
    (40, 8, "usa", "uses"),   # UsuariosController -> Permiso
    (40, 9, "usa", "uses"),   # UsuariosController -> RolPermiso
    (41, 23, "usa", "uses"),  # ReportesController -> Venta
    (41, 20, "usa", "uses"),  # ReportesController -> Producto
    (41, 27, "usa", "uses"),  # ReportesController -> SesionCyber
    (41, 45, "usa", "uses"),  # ReportesController -> ReporteService
    # Services → Models
    (42, 20, "usa", "uses"),  # InventarioService -> Producto
    (42, 29, "usa", "uses"),  # InventarioService -> BitacoraStock
    (43, 23, "usa", "uses"),  # PosService -> Venta
    (43, 24, "usa", "uses"),  # PosService -> DetalleVenta
    (43, 20, "usa", "uses"),  # PosService -> Producto
    (43, 29, "usa", "uses"),  # PosService -> BitacoraStock
    (44, 27, "usa", "uses"),  # CyberService -> SesionCyber
    (44, 22, "usa", "uses"),  # CyberService -> EstacionCyber
    (44, 15, "usa", "uses"),  # CyberService -> TarifaCyber
    (45, 23, "usa", "uses"),  # ReporteService -> Venta
    (45, 20, "usa", "uses"),  # ReporteService -> Producto
    (45, 27, "usa", "uses"),  # ReporteService -> SesionCyber
]

# ─── Layout ──────────────────────────────────────────────────────────────────
# Grid layout: columnas por capa, filas por clase

LAYERS = {
    "core": {"col": 0, "label": "CORE (App\\Core)"},
    "catalogo": {"col": 1, "label": "MODELOS — Catálogo"},
    "maestra": {"col": 2, "label": "MODELOS — Maestras"},
    "transaccional": {"col": 3, "label": "MODELOS — Transaccional / Bitácora / Puente"},
    "controllers": {"col": 4, "label": "CONTROLADORES (App\\Controllers)"},
    "services": {"col": 5, "label": "SERVICIOS (App\\Services)"},
}

COL_W = 280
COL_GAP = 40
ROW_H = 24  # altura por línea de texto
ROW_GAP = 20
HEADER_H = 50
X_START = 40
Y_START = 60

# Calcular altura de cada clase según número de líneas
def calc_height(cls):
    lines = 2  # nombre + separador
    if cls.get("stereo"):
        lines += 1
    all_text = (cls["attrs"] + "\n" + cls["methods"]).split("\n")
    non_empty = [l for l in all_text if l.strip()]
    lines += len(non_empty)
    return max(lines * ROW_H + 20, 60)

def count_lines(cls):
    lines = 2  # nombre + separador atributos
    all_text = (cls["attrs"] + "\n" + cls["methods"]).split("\n")
    non_empty = [l for l in all_text if l.strip()]
    lines += len(non_empty)
    return lines

# ─── Generación del XML ──────────────────────────────────────────────────────

def make_cell_content(cls):
    """Genera el HTML contenido para una celda de clase UML en draw.io."""
    name = cls["name"]
    attrs = cls["attrs"].split("\n") if cls["attrs"] else []
    methods = cls["methods"].split("\n") if cls["methods"] else []
    stereo = cls.get("stereo", "")

    parts = ['<div style="background-color:#1a1a2e;color:#e0e0e0;">']

    # Header
    header_bg = {"core": "#16213e", "catalogo": "#1a3a4a", "maestra": "#2d1f3d",
                 "transaccional": "#3d1f2e", "controllers": "#1e3a2e", "services": "#3a2e1e"}
    bg = header_bg.get(cls["layer"], "#1a1a2e")

    parts.append(f'<div style="background-color:{bg};padding:8px;text-align:center;font-weight:bold;border-bottom:2px solid #e94560;">')
    parts.append(f'<span style="font-size:12px;">{name.replace("«", "&laquo;").replace("»", "&raquo;")}</span>')
    if stereo:
        parts.append(f'<br/><span style="font-size:9px;font-weight:normal;font-style:italic;">&laquo;{stereo}&raquo;</span>')
    parts.append('</div>')

    # Attrs section
    if attrs and attrs[0].strip():
        parts.append(f'<div style="background-color:#1e1e3a;padding:4px 8px;border-bottom:1px solid #333;font-size:10px;">')
        for a in attrs:
            if a.strip():
                parts.append(f'<div>{a.strip()}</div>')
        parts.append('</div>')

    # Methods section
    if methods and methods[0].strip():
        parts.append(f'<div style="background-color:#16162e;padding:4px 8px;font-size:10px;">')
        for m in methods:
            if m.strip():
                parts.append(f'<div>{m.strip()}</div>')
        parts.append('</div>')

    parts.append('</div>')
    return "".join(parts)


def generate_drawio():
    # Calcular posiciones por capa
    layer_items = {}
    for i, cls in enumerate(CLASSES):
        layer_items.setdefault(cls["layer"], []).append(i)

    # Posiciones X por capa
    col_x = {}
    for layer_name, info in LAYERS.items():
        col_x[layer_name] = X_START + info["col"] * (COL_W + COL_GAP)

    # Calcular Y para cada clase dentro de su capa
    class_pos = {}
    for layer_name, indices in layer_items.items():
        y = Y_START + HEADER_H
        for idx in indices:
            cls = CLASSES[idx]
            h = calc_height(cls)
            class_pos[idx] = (col_x[layer_name], y, COL_W, h)
            y += h + ROW_GAP

    # Build XML
    mxfile = ET.Element("mxfile")
    diagram = ET.SubElement(mxfile, "diagram", {"name": "Diagrama de Clases - EIS Zona Web Lara"})
    graph = ET.SubElement(diagram, "mxGraphModel", {
        "dx": "0", "dy": "0", "grid": "1", "gridSize": "10",
        "guides": "1", "tooltips": "1", "connect": "1",
        "arrows": "1", "fold": "1", "page": "0", "pageScale": "1",
        "pageWidth": "2200", "pageHeight": "2400",
        "background": "#0a0a1a"
    })
    root = ET.SubElement(graph, "root")

    # Celda 0 y 1 (obligatorias de draw.io)
    ET.SubElement(root, "mxCell", {"id": "0"})
    ET.SubElement(root, "mxCell", {"id": "1", "parent": "0"})

    cell_id = 2

    # Headers de capa
    for layer_name, info in LAYERS.items():
        x = col_x[layer_name]
        y = Y_START - 5
        cell = ET.SubElement(root, "mxCell", {
            "id": str(cell_id), "value": info["label"], "style": "text;html=1;fillColor=none;fontColor=#e94560;fontSize=13;fontStyle=1;verticalAlign=middle;align=center;",
            "vertex": "1", "parent": "1"
        })
        ET.SubElement(cell, "mxGeometry", {"x": str(x), "y": str(y), "width": str(COL_W), "height": "30", "as": "geometry"})
        cell_id += 1

    # Clases
    class_cell_ids = {}
    for idx, cls in enumerate(CLASSES):
        x, y, w, h = class_pos[idx]
        content = make_cell_content(cls)
        # Escapar el contenido HTML
        escaped = content.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
        # Usar HTML
        cell = ET.SubElement(root, "mxCell", {
            "id": str(cell_id),
            "value": content,
            "style": "shape=umlClass;html=1;whiteSpace=wrap;overflow=hidden;fillColor=#1a1a2e;strokeColor=#e94560;gradientColor=none;fontColor=#e0e0e0;fontSize=10;rounded=1;arcSize=8;",
            "vertex": "1", "parent": "1"
        })
        ET.SubElement(cell, "mxGeometry", {
            "x": str(x), "y": str(y), "width": str(w), "height": str(h), "as": "geometry"
        })
        class_cell_ids[idx] = str(cell_id)
        cell_id += 1

    # Relaciones
    edge_style = {
        "extends": "edgeStyle=elbowEdgeStyle;rounded=1;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#e94560;strokeWidth=2;endArrow=block;endFill=true;",
        "uses": "edgeStyle=elbowEdgeStyle;rounded=1;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#888;strokeWidth=1;endArrow=open;dashed=1;endFill=false;",
        "association": "edgeStyle=elbowEdgeStyle;rounded=1;orthogonalLoop=1;jettySize=auto;html=1;strokeColor=#aaa;strokeWidth=1;endArrow=open;endFill=false;",
    }

    for src, tgt, label, rel_type in RELATIONS:
        if src not in class_cell_ids or tgt not in class_cell_ids:
            continue
        style = edge_style.get(rel_type, edge_style["uses"])
        if label == "extends":
            label_attr = ""
        else:
            label_attr = label

        edge = ET.SubElement(root, "mxCell", {
            "id": str(cell_id),
            "value": label_attr,
            "style": style,
            "edge": "1", "parent": "1",
            "source": class_cell_ids[src],
            "target": class_cell_ids[tgt],
        })
        ET.SubElement(edge, "mxGeometry", {"relative": "1", "as": "geometry"})
        cell_id += 1

    # Formatear XML
    rough = ET.tostring(mxfile, encoding="unicode")
    dom = md.parseString(rough)
    return dom.toprettyxml(indent="  ")


if __name__ == "__main__":
    xml = generate_drawio()
    output_path = __file__.replace("generar_diagrama.py", "Diagrama de clases completo.drawio")
    with open(output_path, "w", encoding="utf-8") as f:
        f.write(xml)
    print(f"OK Diagrama generado: {output_path}")
    print(f"  {len(CLASSES)} clases, ~{len(RELATIONS)} relaciones")
