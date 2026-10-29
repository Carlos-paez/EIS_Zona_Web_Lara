<?php

namespace App\Core;

use App\Models\Usuario;

/**
 * Núcleo de autorización (RBAC) de la aplicación.
 *
 * Concentra las tres decisiones de acceso:
 *
 * 1. Qué página exige qué permiso base (MAPA_PAGINA_PERMISO).
 * 2. Qué verbos de acción ofrece cada módulo (CATALOGO).
 * 3. Qué verbo exige cada acción de cada controlador (MAPA_ACCIONES).
 *
 * Resolución de un permiso compuesto "base:verbo":
 *
 * - Si el rol no tiene ningún verbo de ese módulo, el usuario hereda el
 *   acceso completo del módulo. Así la matriz de permisos existente sigue
 *   funcionando sin cambios.
 * - Si el rol tiene al menos un verbo de ese módulo, solo se permiten los
 *   verbos asignados.
 * - Una acción que no aparece en MAPA_ACCIONES se deniega (fail closed).
 *
 * El enrutador consulta esta clase para cortar el acceso y el layout la
 * usa para filtrar el menú lateral. No se repite la lista de permisos en
 * ningún otro fichero.
 */
final class Permisos
{
    /** Separador entre el permiso base del módulo y el verbo de acción. */
    public const PREFIJO_VERBO = ':';

    /** Roles con acceso total a todos los módulos, con independencia de la matriz. */
    public const SUPERUSUARIOS = [1];

    /** Clave de sesión con los permisos efectivos del usuario. */
    private const SESION_PERMISOS = 'permisos';

    /** Clave de sesión con los permisos que el rol tiene asignados en la matriz. */
    private const SESION_PERMISOS_ROL = 'permisos_rol';

    /** Clave de sesión con el identificador del rol (roles.id). */
    private const SESION_ROL_ID = 'rol_id';

    /** Clave de sesión con el nombre del rol. */
    private const SESION_ROL_NOMBRE = 'rol_nombre';

    /**
     * Página (?pagina=X) => permiso base del módulo.
     *
     * Un valor null significa que la página está abierta para cualquier
     * usuario autenticado (Dashboard y Manual de usuario).
     */
    public const MAPA_PAGINA_PERMISO = [
        'dashboard'           => null,
        'inventario'          => 'gestion_productos',
        'ventas'              => 'gestion_ventas',
        'clientes'            => 'gestion_clientes',
        'proveedores'         => 'gestion_abastecimiento',
        'proveedores-gestion' => 'gestion_proveedores',
        'ciberControl'        => 'gestion_ciber',
        'reportes'            => 'ver_reportes',
        'activos'             => 'gestion_activos',
        'asesorias'           => 'gestion_asesoria',
        'usuarios'            => 'gestion_usuarios',
        'roles'               => 'configuracion',
        'manual_usuario'      => null,
    ];

    /**
     * Catálogo de módulos: permiso base, etiquetas, icono y verbos disponibles.
     *
     * La etiqueta de cada verbo es la que ve el administrador en el módulo
     * de roles y permisos.
     */
    public const CATALOGO = [
        'inventario' => [
            'base'         => 'gestion_productos',
            'etiqueta'     => 'Inventario',
            'etiquetaBase' => 'Acceso completo al módulo de inventario',
            'icono'        => 'inventory_2',
            'verbos' => [
                'crear'     => 'Crear productos',
                'editar'    => 'Editar productos',
                'eliminar'  => 'Eliminar productos',
                'categoria' => 'Gestionar las categorías',
            ],
        ],
        'ventas' => [
            'base'         => 'gestion_ventas',
            'etiqueta'     => 'Ventas (POS)',
            'etiquetaBase' => 'Acceso completo al punto de venta',
            'icono'        => 'shopping_cart',
            'verbos' => [
                'registrar' => 'Registrar ventas',
            ],
        ],
        'clientes' => [
            'base'         => 'gestion_clientes',
            'etiqueta'     => 'Clientes',
            'etiquetaBase' => 'Acceso completo al módulo de clientes',
            'icono'        => 'badge',
            'verbos' => [
                'crear'    => 'Registrar clientes',
                'editar'   => 'Editar clientes',
                'eliminar' => 'Eliminar clientes',
            ],
        ],
        'proveedores' => [
            'base'         => 'gestion_abastecimiento',
            'etiqueta'     => 'Órdenes de compra',
            'etiquetaBase' => 'Acceso completo a las órdenes de compra',
            'icono'        => 'request_quote',
            'verbos' => [
                'crear'    => 'Crear órdenes',
                'editar'   => 'Editar órdenes',
                'eliminar' => 'Eliminar órdenes',
                'linea'    => 'Gestionar las líneas de la orden',
            ],
        ],
        'proveedores-gestion' => [
            'base'         => 'gestion_proveedores',
            'etiqueta'     => 'Gestión de proveedores',
            'etiquetaBase' => 'Acceso completo a la gestión de proveedores',
            'icono'        => 'local_shipping',
            'verbos' => [
                'crear'    => 'Registrar proveedores',
                'editar'   => 'Editar proveedores',
                'eliminar' => 'Eliminar proveedores',
            ],
        ],
        'ciberControl' => [
            'base'         => 'gestion_ciber',
            'etiqueta'     => 'Cyber',
            'etiquetaBase' => 'Acceso completo al control del cybercafé',
            'icono'        => 'computer',
            'verbos' => [
                'iniciar'   => 'Iniciar sesiones de estación',
                'finalizar' => 'Finalizar sesiones de estación',
                'pc'        => 'Gestionar los equipos (PC)',
            ],
        ],
        'activos' => [
            'base'         => 'gestion_activos',
            'etiqueta'     => 'Activos fijos',
            'etiquetaBase' => 'Acceso completo al módulo de activos',
            'icono'        => 'build',
            'verbos' => [
                'crear'    => 'Registrar activos',
                'editar'   => 'Editar activos',
                'eliminar' => 'Eliminar activos',
                'estado'   => 'Cambiar el estado de los activos',
            ],
        ],
        'asesorias' => [
            'base'         => 'gestion_asesoria',
            'etiqueta'     => 'Asesoría legal',
            'etiquetaBase' => 'Acceso completo al módulo de asesoría legal',
            'icono'        => 'gavel',
            'verbos' => [
                'crear' => 'Registrar asesorías',
            ],
        ],
        'usuarios' => [
            'base'         => 'gestion_usuarios',
            'etiqueta'     => 'Configuración de usuarios',
            'etiquetaBase' => 'Acceso completo a la gestión de usuarios',
            'icono'        => 'settings',
            'verbos' => [
                'crear'    => 'Crear usuarios',
                'editar'   => 'Editar usuarios',
                'eliminar' => 'Eliminar usuarios',
                'estado'   => 'Activar o desactivar usuarios',
                'password' => 'Restablecer contraseñas',
            ],
        ],
        'roles' => [
            'base'         => 'configuracion',
            'etiqueta'     => 'Roles y permisos',
            'etiquetaBase' => 'Acceso completo a la configuración de roles',
            'icono'        => 'admin_panel_settings',
            'verbos' => [
                'rol'        => 'Crear, editar y eliminar roles',
                'permisos'   => 'Asignar permisos a los roles',
                'asignacion' => 'Asignar roles a los usuarios',
            ],
        ],
        'reportes' => [
            'base'         => 'ver_reportes',
            'etiqueta'     => 'Reportes',
            'etiquetaBase' => 'Acceso completo a los reportes',
            'icono'        => 'bar_chart',
            'verbos' => [
                'exportar' => 'Exportar reportes (CSV, Excel, PDF)',
            ],
        ],
    ];

    /**
     * Acción (?action=Y) => verbo que exige, por página.
     *
     * Un verbo null marca una acción de lectura: basta con el permiso base
     * del módulo. Toda acción de escritura tiene un verbo.
     */
    public const MAPA_ACCIONES = [
        'dashboard' => [
            'kpis' => null,
        ],
        'inventario' => [
            'listar'             => null,
            'kpis'               => null,
            'categorias'         => null,
            'detalle'            => null,
            'buscar'             => null,
            'crear'              => 'crear',
            'actualizar'         => 'editar',
            'eliminar'           => 'eliminar',
            'crearCategoria'     => 'categoria',
            'actualizarCategoria' => 'categoria',
            'eliminarCategoria'  => 'categoria',
        ],
        'ventas' => [
            'productos'     => null,
            'clientes'      => null,
            'buscarCliente' => null,
            'registrar'     => 'registrar',
        ],
        'clientes' => [
            'listar'     => null,
            'detalle'    => null,
            'kpis'       => null,
            'crear'      => 'crear',
            'actualizar' => 'editar',
            'eliminar'   => 'eliminar',
        ],
        'proveedores' => [
            'listar'         => null,
            'kpis'           => null,
            'detalle'        => null,
            'productos'      => null,
            'statuses'       => null,
            'lineas'         => null,
            'siguienteNumero' => null,
            'crear'          => 'crear',
            'actualizar'     => 'editar',
            'eliminar'       => 'eliminar',
            'agregarLinea'   => 'linea',
            'eliminarLinea'  => 'linea',
        ],
        'proveedores-gestion' => [
            'listar'     => null,
            'detalle'    => null,
            'kpis'       => null,
            'crear'      => 'crear',
            'actualizar' => 'editar',
            'eliminar'   => 'eliminar',
        ],
        'ciberControl' => [
            'estaciones'       => null,
            'tarifas'          => null,
            'buscarCliente'    => null,
            'estadisticas'     => null,
            'historial'        => null,
            'tiposActivo'      => null,
            'obtenerPC'        => null,
            'iniciar'          => 'iniciar',
            'finalizar'        => 'finalizar',
            'crearPC'          => 'pc',
            'actualizarPC'     => 'pc',
            'eliminarPC'       => 'pc',
            'cambiarEstadoPC'  => 'pc',
        ],
        'activos' => [
            'listar'     => null,
            'detalle'    => null,
            'kpis'       => null,
            'tipos'      => null,
            'crear'      => 'crear',
            'actualizar' => 'editar',
            'estado'     => 'estado',
            'eliminar'   => 'eliminar',
        ],
        'asesorias' => [
            'listar'  => null,
            'detalle' => null,
            'buscar'  => null,
            'kpis'    => null,
            'crear'   => 'crear',
        ],
        'usuarios' => [
            'listar'     => null,
            'detalle'    => null,
            'kpis'       => null,
            'roles'      => null,
            'crear'      => 'crear',
            'actualizar' => 'editar',
            'estado'     => 'estado',
            'eliminar'   => 'eliminar',
            'password'   => 'password',
        ],
        'roles' => [
            'listar'          => null,
            'detalle'         => null,
            'permisos'        => null,
            'permisosRol'     => null,
            'usuarios'        => null,
            'crear'           => 'rol',
            'actualizar'      => 'rol',
            'eliminar'        => 'rol',
            'guardarPermisos' => 'permisos',
            'asignarRol'      => 'asignacion',
        ],
        'reportes' => [
            'kpis'      => null,
            'filtros'   => null,
            'consultar' => null,
            'exportar'  => 'exportar',
        ],
    ];

    /**
     * Permisos efectivos del usuario autenticado (los que tiene en esta sesión).
     *
     * @return string[]
     */
    public static function permisosDeSesion(): array
    {
        $permisos = $_SESSION[self::SESION_PERMISOS] ?? [];
        return is_array($permisos) ? array_values($permisos) : [];
    }

    /**
     * Permisos que el rol tiene asignados en la matriz permisos_rol.
     *
     * @return string[]
     */
    public static function permisosDelRol(): array
    {
        $permisos = $_SESSION[self::SESION_PERMISOS_ROL] ?? [];
        return is_array($permisos) ? array_values($permisos) : [];
    }

    /**
     * Identificador del rol del usuario autenticado (roles.id).
     */
    public static function rolId(): ?int
    {
        $rolId = $_SESSION[self::SESION_ROL_ID] ?? null;
        return $rolId === null ? null : (int)$rolId;
    }

    /**
     * Nombre del rol del usuario autenticado.
     */
    public static function rolNombre(): ?string
    {
        $nombre = $_SESSION[self::SESION_ROL_NOMBRE] ?? null;
        return $nombre === null ? null : (string)$nombre;
    }

    /**
     * Indica si el usuario autenticado tiene un permiso concreto.
     */
    public static function puede(string $permiso): bool
    {
        if ($permiso === '') {
            return false;
        }
        return in_array($permiso, self::permisosDeSesion(), true);
    }

    /**
     * Indica si el usuario autenticado puede entrar a una página.
     *
     * Una página que no está registrada en MAPA_PAGINA_PERMISO se deniega
     * siempre (fail closed): toda vista nueva debe declararse en el mapa.
     */
    public static function accesoPagina(string $pagina): bool
    {
        if (!array_key_exists($pagina, self::MAPA_PAGINA_PERMISO)) {
            // Página no registrada: se deniega.
            return false;
        }

        $base = self::MAPA_PAGINA_PERMISO[$pagina];
        if ($base === null) {
            // Página abierta para cualquier usuario autenticado.
            return true;
        }

        return self::puede($base);
    }

    /**
     * Indica si el usuario autenticado puede ejecutar una acción de un módulo.
     *
     * Una acción que no aparece en MAPA_ACCIONES se deniega (fail closed).
     */
    public static function puedeAccion(string $pagina, string $accion): bool
    {
        $acciones = self::MAPA_ACCIONES[$pagina] ?? null;
        if ($acciones === null || !array_key_exists($accion, $acciones)) {
            // Página o acción no mapeada: se deniega (fail closed).
            return false;
        }

        $base = self::permisoBase($pagina);
        if ($base === null) {
            // Página abierta: la acción es de lectura por definición.
            return true;
        }

        if (!self::puede($base)) {
            return false;
        }

        $verbo = $acciones[$accion];
        if ($verbo === null) {
            // Acción de lectura: basta con el permiso base del módulo.
            return true;
        }

        $verbos = self::verbosDeSesion($base);
        if ($verbos === []) {
            // El rol no restringe este módulo: hereda el acceso completo.
            return true;
        }

        return in_array($base . self::PREFIJO_VERBO . $verbo, $verbos, true);
    }

    /**
     * Indica si el usuario autenticado es superusuario.
     */
    public static function esSuperusuario(): bool
    {
        $rolId = self::rolId();
        return $rolId !== null && in_array($rolId, self::SUPERUSUARIOS, true);
    }

    /**
     * Recarga el rol y los permisos del usuario autenticado desde la base de datos.
     *
     * Se ejecuta en cada petición, de modo que un cambio de rol o de estado
     * surte efecto de inmediato. Devuelve false cuando el usuario ya no está
     * activo: en ese caso la sesión se invalida y el enrutador lo devuelve
     * al inicio de sesión.
     */
    public static function refrescar(): bool
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        $model = new Usuario();

        $estado = $model->obtenerEstadoYRol($userId);
        if ($estado === null) {
            return false;
        }
        if ((int)($estado['activo'] ?? 0) !== 1) {
            return false;
        }
        if (!in_array((string)($estado['estatus'] ?? ''), ['1', 'activo'], true)) {
            return false;
        }

        $rolId     = $estado['rol_id'] !== null ? (int)$estado['rol_id'] : null;
        $permisos = $model->obtenerPermisosPorUsuario($userId);

        $efectivos = $permisos;
        if ($rolId !== null && in_array($rolId, self::SUPERUSUARIOS, true)) {
            // El superusuario dispone de todos los permisos del sistema.
            $efectivos = $model->obtenerTodosLosPermisos();
        }

        $_SESSION[self::SESION_PERMISOS]     = array_values($efectivos);
        $_SESSION[self::SESION_PERMISOS_ROL] = array_values($permisos);
        $_SESSION[self::SESION_ROL_ID]       = $rolId;
        $_SESSION[self::SESION_ROL_NOMBRE]   = $estado['rol_nombre'] ?? null;

        return true;
    }

    /**
     * Cierra la sesión del usuario y elimina la cookie.
     */
    public static function cerrarSesion(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Se renueva el identificador antes de destruir la sesión para
            // evitar la reutilización del ID por otro usuario.
            session_regenerate_id(true);
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool)$params['secure'],
                (bool)$params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Permiso base de una página (null si la página está abierta).
     */
    public static function permisoBase(string $pagina): ?string
    {
        return self::MAPA_PAGINA_PERMISO[$pagina] ?? null;
    }

    /**
     * Páginas a las que el usuario autenticado tiene acceso.
     *
     * @return string[]
     */
    public static function paginasPermitidas(): array
    {
        $paginas = [];

        foreach (array_keys(self::MAPA_PAGINA_PERMISO) as $pagina) {
            if (self::accesoPagina($pagina)) {
                $paginas[] = $pagina;
            }
        }

        return $paginas;
    }

    /**
     * Indica si el usuario tiene acceso a algún módulo protegido.
     *
     * Un usuario sin módulos solo ve el Dashboard y el Manual de usuario.
     */
    public static function tieneModulos(): bool
    {
        foreach (self::MAPA_PAGINA_PERMISO as $pagina => $base) {
            if ($base !== null && self::puede($base)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lista de permisos compuestos "base:verbo" que el usuario tiene de un módulo.
     *
     * @return string[]
     */
    private static function verbosDeSesion(string $base): array
    {
        $prefijo = $base . self::PREFIJO_VERBO;
        $verbos  = [];

        foreach (self::permisosDeSesion() as $permiso) {
            if (str_starts_with($permiso, $prefijo)) {
                $verbos[] = $permiso;
            }
        }

        return $verbos;
    }

    /**
     * Describe un permiso para el módulo de roles y permisos.
     *
     * Devuelve el módulo al que pertenece, la etiqueta legible y el verbo
     * (null en los permisos base de módulo).
     *
     * @return array{nombre:string,modulo:string,etiquetaModulo:string,etiqueta:string,icono:string,verbo:?string,esBase:bool}
     */
    public static function describirPermiso(string $nombre): array
    {
        $base  = $nombre;
        $verbo = null;

        if (str_contains($nombre, self::PREFIJO_VERBO)) {
            [$base, $verbo] = explode(self::PREFIJO_VERBO, $nombre, 2);
        }

        $modulo = self::moduloDePermisoBase($base);

        if ($modulo === null) {
            return [
                'nombre'        => $nombre,
                'modulo'        => 'otros',
                'etiquetaModulo' => 'Otros permisos',
                'etiqueta'      => $verbo === null ? $nombre : $base . ' · ' . $verbo,
                'icono'         => 'lock_open',
                'verbo'         => $verbo,
                'esBase'        => false,
            ];
        }

        $datos = self::CATALOGO[$modulo];

        if ($verbo === null) {
            $etiqueta = $datos['etiquetaBase'];
        } else {
            $etiqueta = $datos['verbos'][$verbo] ?? ($base . ' · ' . $verbo);
        }

        return [
            'nombre'         => $nombre,
            'modulo'         => $modulo,
            'etiquetaModulo' => $datos['etiqueta'],
            'etiqueta'       => $etiqueta,
            'icono'          => $datos['icono'],
            'verbo'          => $verbo,
            'esBase'         => $verbo === null,
        ];
    }

    /**
     * Módulo del catálogo al que pertenece un permiso base.
     */
    private static function moduloDePermisoBase(string $base): ?string
    {
        foreach (self::CATALOGO as $modulo => $datos) {
            if ($datos['base'] === $base) {
                return $modulo;
            }
        }
        return null;
    }
}
