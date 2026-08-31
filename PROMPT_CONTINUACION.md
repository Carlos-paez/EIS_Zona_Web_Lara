# PROMPT DE CONTINUACIÓN — Registro de clientes en Asesoría, Ventas (POS) y CiberControl

> Copia este archivo como prompt para continuar el trabajo en una sesión nueva de opencode.

## Objective
Implementar en el frontend de la app PHP (`EIS_Zona_Web_Lara`, rama `Carlos`) la creación y modificación segura de registros de clientes en los tres módulos relacionados: Asesoría, Ventas (POS) y CiberControl.
Alcance elegido por el usuario: **Asesoría + Ventas + CiberControl**.

## Estado ACTUAL (ya hecho y commiteado)
- Commit `8a7d5e3` (rama `Carlos`): feature parcial. `git push origin Carlos` **aún NO ejecutado** para este commit.
- Backend Cliente: `Cliente::obtenerOCrearPorCedula(cedula, nombre, apellido, direccion, telefono): int` (get-or-create validado: crea si no existe, actualiza solo campos no vacíos si existe; resetea propiedades opcionales entre llamadas) y `obtenerClientePorCedula()`. Probado.
- Backend Asesoría: `Asesoria::crear()` delega en Cliente + transacción (cliente + cliente_asesoria + asesoria), acepta `direccion`/`telefono`. `AsesoriaController` con `listar`, `kpis`, `detalle`, `buscar`, `crear`, `actualizar`, `eliminar`; CSRF en mutaciones. Lint PASS.
- Nuevo: `src/app/Models/Venta.php` (`listarProductos()` → productos con stock > 0; `registrarVenta()` transaccional: cliente get-or-create + `orden_de_venta` + `lineas_venta` con precio tomado de la BD + descuento de stock) y `src/app/Controllers/VentaController.php` (`productos`, `buscarCliente`, `registrar` con CSRF). Lint PASS.
- Router: rutas AJAX registradas para `pagina=asesorias`, `pagina=ventas` y `pagina=ciberControl` → `AsesoriaController`, `VentaController`, `CiberController` (este último AÚN no existe; la ruta `ciberControl&action` respondería 500 hasta crearlo). Chips de cabecera de ciberControl ahora con `id="hdrDisponibles"` / `id="hdrOcupadas"`.
- Vista `asesorias.php`: agregados campos `direccion` y `telefono` al formulario + modal de edición `#modal-asesoria` al final del archivo (fuera del form). `app.legal.js` reescrito: AJAX (`?pagina=asesorias&action=...`), CSRF automático vía `$.ajaxSetup` del layout, render seguro `escHtml`, historial con editar/eliminar, KPI chip/badge, búsqueda filtrable, aviso "cliente ya registrado" al blur de cédula.
- Patrón CSRF: el layout inyecta `window.EIS.csrfToken` y `$.ajaxSetup` lo agrega a todo POST (no hace falta incluir el token en el JS).

## Pendiente (orden sugerido)
1. **Ventas frontend**: reescribir `src/app/Views/ventas.php` (catálogo dinámico desde `?pagina=ventas&action=productos`, modal carrito con form de cliente: `ciudadano`, `cedula`, `direccion`, `telefono`; al blur de cédula prefill con `buscarCliente`) y `src/Public/js/app.pos.js` (carrito con cantidades, POST `registrar` con `items` JSON: `[{id, cantidad}]`, CSRF, render seguro). La vista actual es prototipo estático (tarjetas hardcodeadas) y `app.pos.js` es carrito simulado sin backend.
2. **Backend CiberControl**: ALTER `sesion_ciber ADD COLUMN finalizada TINYINT(1) NOT NULL DEFAULT 0` en `src/Database/estructura.sql` y aplicarlo en la BD dev (`zona_web_lara`). Crear `src/app/Models/CiberControl.php` + `src/app/Controllers/CiberController.php` (listar activos ciber activos, tarifas, buscar cliente por cédula, iniciar sesión = cliente get-or-create + `sesion_ciber`, finalizar sesión marcando `finalizada=1`). CSRF en mutaciones. Seguir patrón de `VentaController`.
3. **CiberControl frontend**: rehacer `src/app/Views/ciberControl.php` y `src/Public/js/app.cyber.js` (estado de estaciones dinámico, registrar cliente, iniciar/finalizar sesión; actualizar chips `#hdrDisponibles`/`#hdrOcupadas`).
4. **Verificación**: `php -l` en todos los PHP; smoke tests contra BD dev (los 3 módulos) sin dejar datos residuales.
5. **Commit**: commitear la feature completa (excluir `.idea/php.xml`, que tiene cambios de IDE no deseados) y ejecutar `git push origin Carlos`.

## Detalles clave / contratos
- BD: `zona_web_lara`. Tablas relevantes: `clientes`, `cliente_asesoria`, `asesoria`, `productos` (`precio_venta`, `stock`), `orden_de_venta` (`numero_de_orden`, `fecha`, `fk_usuario`, `fk_cliente`), `lineas_venta` (`cantidad`, `precio`, `fk_orden`, `fk_producto`), `tarifas` (`tarifa_hora`, `precio_tiempo`), `activos` (`marca`, `descripcion`, `is_ciber`, `activa`, `fk_tipo_activo`), `sesion_ciber` (`tiempo_uso`, `fk_cliente`, `fk_tarifa`, `fk_activo` — falta `finalizada`).
- Respuestas JSON: `{success:bool, data?:..., error?:string, message?:string}`.
- `listar` de asesorías devuelve: `id, documento, descripcion, fecha, cedula, ciudadano_nombre, ciudadano_apellido, ciudadano, tipo_documento, permitido`.
- Usuario logueado: `$_SESSION['user_id']`.
- Validación backend en setters del modelo (`sanitizeString`, `validateLength`, `validateNotEmpty` de `App\Core\Model`).
- Frontend: jQuery 3.7.1 + Materialize local, global `EIS.toast(msg, color, icon)`, `debounce`, `escHtml` (recomendado `$('<span>').text(...).html()`), escape de HTML SIEMPRE en render.
- Referencias de patrón: `src/app/Controllers/ClienteController.php`, `src/app/Models/Inventario.php`, `src/Public/js/app.clientes.js`, `src/Public/js/app.proveedores-gestion.js`.
- Rutas de controladores registradas en `src/app/core/router.php` (`isAjax*` + `run*Controller` + dispatch en `handle()`).
