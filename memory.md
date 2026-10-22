# memory.md

Estado vivo del proyecto EIS_Zona_Web_Lara. Se actualiza al cerrar cada iteración.

---

## 1. Identidad del proyecto

| Dato | Valor |
| :--- | :--- |
| Nombre | EIS_Zona_Web_Lara |
| Descripción | Sistema de gestión integral (ventas POS, inventario, clientes, proveedores, cybercafé, activos fijos, asesoría legal, usuarios, roles, reportes) |
| Stack | PHP 8.3 vanilla, Materialize CSS 1.0.0, jQuery 3.7.1, jQuery DataTables 1.13.8, MySQL 8 |
| Arquitectura | Front Controller + Router OOP + MVC modular PSR-4 (`App\` → `src/app/`) |
| Versión actual | 4.4 (en construcción, ver §3) |
| Versión anterior | 4.3 |
| Rama de trabajo | `Carlos` |
| Autor | Carlos Páez Guerra |
| Base de datos | `zona_web_lara`, 21 tablas InnoDB |
| Dependencias CDN | 0 |

---

## 2. Módulos del sistema

Todos operáticos con base de datos, AJAX JSON y DataTables.

| Módulo | Vista | Controlador | Modelo | Permiso base |
| :--- | :--- | :--- | :--- | :--- |
| Login | `login.php` | `AuthController` | `Usuario` | público |
| Dashboard | `dashboard.php` | `DashboardController` | `Dashboard` | abierto |
| Inventario | `inventario.php` | `InventarioController` | `Inventario` | `gestion_productos` |
| Ventas (POS) | `ventas.php` | `VentaController` | `Venta` | `gestion_ventas` |
| Clientes | `clientes.php` | `ClienteController` | `Cliente` | `gestion_clientes` |
| Órdenes de compra | `proveedores.php` | `ProveedorController` | `Proveedor` | `gestion_abastecimiento` |
| Gestión de proveedores | `proveedores-gestion.php` | `ProveedorGestionController` | `ProveedorGestion` | `gestion_proveedores` |
| Cybercafé | `ciberControl.php` | `CiberController` | `CiberControl` | `gestion_ciber` |
| Reportes | `reportes.php` | `ReporteController` | `Reporte` | `ver_reportes` |
| Activos fijos | `activos.php` | `ActivoController` | `Activo` | `gestion_activos` |
| Asesoría legal | `asesorias.php` | `AsesoriaController` | `Asesoria` | `gestion_asesoria` |
| Usuarios | `usuarios.php` | `UsuarioController` | `Usuario` | `gestion_usuarios` |
| Roles y permisos | `roles.php` | `RolController` | `Rol` | `configuracion` |
| Manual de usuario | `manual_usuario.php` | (estática) | (ninguno) | abierto |

### Core OOP

`Database`, `Model`, `Router`, `Validator`, `Logger`, `Exporter`, `PdfBuilder`, `LoginThrottle`, `ValidationException`, `Permisos`.

---

## 3. Iteración en curso: control de acceso por rol (v4.4)

### Objetivo

Que un usuario con permisos insuficientes no pueda acceder a los módulos que no le corresponden, ni por el menú ni por URL directa ni por llamada AJAX.

### Punto de partida

- `$_SESSION['permisos']` se cargaba en el login y nunca se evaluaba. Hallazgo **H-02** en `docs/Plan_de_Pruebas.md`.
- `src/app/Views/acceso_denegado.php` existía sin referenciar. Hallazgo **H-03**.
- `Usuario::obtenerPermisosPorUsuario()` unía `permisos_rol.fk_rol` (FK de `roles.id`) contra `rol_usuarios.id`. Funcionaba solo por coincidencia de ids en la semilla.

### Decisiones tomadas

| # | Decisión | Motivo |
| :--- | :--- | :--- |
| D1 | El rol Administrador (`roles.id = 1`) es superusuario: recibe todos los permisos de la tabla `permisos` | Evita el auto-bloqueo cuando un administrador edita la matriz de su propio rol |
| D2 | El control es por módulo **y** por acción | Un permiso `base:verbo` por operación con riesgo distinto (`registrar`, `exportar`, `password`, `asignacion`) |
| D3 | Los permisos se refrescan en **cada** petición | El usuario ve de inmediato cualquier cambio de rol. Coste: 2 consultas por petición |
| D4 | La denegación AJAX responde HTTP 403 con cuerpo JSON | Queda registrada como 403 en los logs del servidor. El frontend muestra un toast claro |
| D5 | Un usuario sin rol o con un rol sin permisos solo ve Dashboard y Manual | Prefiere entrar y avisar a que bloquear el login |
| D6 | Catálogo granular de 32 verbos | `exportar`, `registrar`, `iniciar`, `finalizar`, `password` y `asignacion` no son un CRUD genérico |
| D7 | El modal de permisos se agrupa por módulo | 43 interruptores en lista plana son inmanejables para el administrador |
| D8 | Migración SQL idempotente (`INSERT ... WHERE NOT EXISTS`) | La tabla `permisos` no tiene índice único, `INSERT IGNORE` no evita duplicados |

### Reglas de resolución

1. Página sin el permiso base: 403.
2. Acción de lectura: permitida con el permiso base.
3. Acción de escritura: si el rol tiene algún `base:verbo` de ese módulo, solo se permiten los verbos asignados. Si no tiene ninguno, hereda el acceso completo del módulo.
4. Acción no mapeada: denegada (fail closed).
5. `roles.id = 1`: superusuario.

### Fases

| Fase | Contenido | Estado |
| :--- | :--- | :--- |
| F0 | `AGENTS.md` y `memory.md` | Hecho |
| F1 | `Permisos.php`, corrección del JOIN, `Rol::obtenerPermisos()` enriquecido | Pendiente |
| F2 | Migración SQL de verbos, `estructura.sql`, seeds | Pendiente |
| F3 | `AuthController` (carga de permisos) y `Router` (corte por página y por acción) | Pendiente |
| F4 | `layout.php`, `acceso_denegado.php`, `app.roles.js`, `sw.js` v6 | Pendiente |
| F5 | `create_user.php --rol`, unificación de la regla del rol Administrador | Pendiente |
| F6 | Verificación y casos de prueba | Pendiente |
| F7 | Documentación y cierre de `memory.md` | Pendiente |

---

## 4. Decisiones de diseño históricas

| Decisión | Motivo |
| :--- | :--- |
| PHP vanilla, sin framework | Requisito del proyecto y de las entregas evaluadas |
| Assets 100 % locales y PWA | Funciona sin conexión a Internet |
| Exportadores propios (`Exporter`, `PdfBuilder`) | Sin dependencias externas |
| `Validator` con coerción estricta de tipos | Un solo punto de validación de entrada |
| `Logger` en `src/logs/errores.md` | Los errores técnicos no se muestran al usuario |
| Token CSRF único por sesión | Un solo token en `layout.php`, agregado por `$.ajaxSetup` |
| Tabla `rol_usuarios` con `UNIQUE(fk_rol)` | Heredada del diseño original. Se mantiene, no se migra |

---

## 5. Hallazgos

| ID | Estado | Descripción |
| :--- | :--- | :--- |
| H-01 | Abierto | `src/Config/database.php` duplica las credenciales de `src/app/core/Database.php` |
| H-02 | **Cerrado en v4.4** | `$_SESSION['permisos']` no se evaluaba en ningún punto del flujo |
| H-03 | **Cerrado en v4.4** | `acceso_denegado.php` era un archivo huérfano |
| H-04 | Abierto | Los controladores confían en el corte del Router. Una llamada directa a un controlador desde código nuevo saltaría la comprobación de permisos |
| H-05 | Abierto | `Modelos legacy` (`CiberModel`, `crud_users`, `crud_asesorias`) sin referencias: código muerto |

---

## 6. Pendientes

| # | Tarea | Prioridad |
| :--- | :--- | :--- |
| 1 | Mover las credenciales de base de datos a variables de entorno (`.env`) | Media |
| 2 | Unificar `rol_usuarios` con `roles` (eliminar la tabla intermedia) | Media |
| 3 | URLs limpias (`/nombre` en lugar de `?pagina=nombre`) | Baja |
| 4 | Unificar los modelos legacy con los POO modernos | Baja |
| 5 | Middleware de autenticación y CSRF como capa separada | Media |
| 6 | Invalidación de sesión al cambiar un rol (en lugar de refresco por petición) | Baja |

---

## 7. Base de conocimiento

### Comandos

```bash
# Instalar dependencias (solo el autoloader)
composer install

# Crear la base de datos de desarrollo
mysql -u root -p < src/Database/estructura.sql
mysql -u root -p < src/Database/seed_data.sql

# Aplicar la migración de permisos de la v4.4
mysql -u root -p < src/Database/migracion_v4_4_permisos.sql

# Crear un usuario por línea de comandos
php src/cli/create_user.php --username=admin --password=1234 --nombre="Administrador" --apellido="Sistema" --email=admin@ejemplo.com --rol=1

# Comprobar la sintaxis de un fichero
php -l src/app/core/router.php
```

### Usuarios de prueba (semilla)

| Usuario | Rol | Permisos |
| :--- | :--- | :--- |
| `admin` | Administrador | Todos (superusuario) |
| `jperalta` | Vendedor | Ventas, Clientes, Reportes |
| Almacenista | Almacenista | Inventario, Órdenes, Reportes |
| Asesor | Asesor | Asesoría, Clientes, Reportes |
| Soporte Técnico | Soporte Técnico | Activos, Reportes |

### Rutas críticas

| Ruta | Para qué |
| :--- | :--- |
| `src/app/core/Permisos.php` | Mapa de permisos, catálogo de verbos y mapa de acciones |
| `src/app/core/router.php` | Enrutado y cortes de acceso |
| `src/app/template/layout.php` | Sidebar filtrado por permisos |
| `src/Database/seed_data.sql` | Matriz de permisos por rol |
| `src/Database/migracion_v4_4_permisos.sql` | Permisos de verbo para instalaciones existentes |
| `docs/Plan_de_Pruebas.md` | Casos de prueba, formato en `formato_plan_de_pruebas.md` |

### Orden de arranque

1. `composer install`
2. Importar `estructura.sql` y `seed_data.sql` (o `migracion_v4_4_permisos.sql` sobre una base existente)
3. El document root del virtual host apunta a `src/`
4. `http://localhost/eis_zona_web_lara/src/`

---

## 8. Registro de cambios

| Fecha | Versión | Cambio |
| :--- | :--- | :--- |
| Oct 2026 | 4.4 | Control de acceso por rol y por acción: `Permisos.php`, corte en el Router, sidebar filtrado, modal de permisos agrupado, CLI con `--rol`, migración de verbos, `AGENTS.md` y `memory.md` |
| Sep 2026 | 4.3 | Corrección de la barra de búsqueda de los `select` de Materialize, `CACHE_NAME` del Service Worker a `eis-cache-v5` |
| Sep 2026 | 4.2 | Módulo de Usuarios con controlador propio, manejo global de errores con `Logger`, `Validator`, barras de búsqueda en `select` |
| Sep 2026 | 4.1 | Integración de jQuery DataTables, corrección de 7 bugs de funcionalidad |
| Ago 2026 | 4.0 | Dashboard y Reportes con datos reales y exportación CSV/Excel/PDF, CRUD de Activos, CyberControl con sesiones |
