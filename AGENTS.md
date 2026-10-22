# AGENTS.md

Reglas de trabajo para cualquier agente (o persona) que modifique este repositorio.

Este archivo es normativo. Si una regla y el código actual se contradicen, el código tiene el error: corrígelo o actualiza esta regla en el mismo cambio.

---

## 1. Contexto del proyecto

| Dato | Valor |
| :--- | :--- |
| Lenguaje | PHP 8.x (probado en 8.3), sin framework |
| Frontend | Materialize CSS 1.0.0 + jQuery 3.7.1 + jQuery DataTables 1.13.8, todo local |
| Arquitectura | Front Controller + enrutador OOP + MVC modular con namespaces |
| Autocarga | Composer PSR-4: `App\` → `src/app/` |
| Base de datos | MySQL 8 / MariaDB 10.3+, InnoDB, 21 tablas |
| Dependencias externas | 0 (sin CDN, sin frameworks JS) |
| Rama de trabajo | `Carlos` |
| Versión actual | 4.4 (ver `README.md` y `memory.md`) |

El proyecto es una aplicación de gestión empresarial (ventas POS, inventario, clientes, proveedores, cybercafé, activos fijos, asesoría legal, usuarios, roles y permisos, reportes).

---

## 2. Anatomía del núcleo

```
Navegador → src/.htaccess → src/index.php → App\Core\Router->handle()
                                                     │
                              session_start + token CSRF (uno por sesión)
                                                     │
                                    resolvePagina() (regex anti path-traversal)
                                                     │
                        ┌────────────────────────────┼────────────────────────────┐
                        ▼                            ▼                            ▼
              Control de acceso              Control por permiso            Render de vista
        ¿logged_in? ¿página pública?   Permisos::accesoPagina()        pública (login) o
                        │               Permisos::puedeAccion()        protegida (layout)
                        ▼                        │                              │
              redirect(login)          403 JSON o 403 + acceso_denegado   require layout.php
```

### Ficheros clave

| Ruta | Responsabilidad |
| :--- | :--- |
| `src/index.php` | Front controller, autoloader, manejo global de errores (`App\Core\Logger`) |
| `src/app/core/router.php` | Enrutado, control de acceso, render de vistas |
| `src/app/core/Permisos.php` | Autorización: mapa de páginas, catálogo de verbos, mapa de acciones |
| `src/app/core/Database.php` | Singleton PDO (sin emulación de prepares) |
| `src/app/core/Model.php` | Clase base de los modelos con helpers de validación |
| `src/app/core/Validator.php` | Coerción estricta de tipos y reglas declarativas |
| `src/app/core/LoginThrottle.php` | Bloqueo por fuerza bruta (usuario + IP) |
| `src/app/core/Logger.php` | Registro de errores en `src/logs/errores.md` |
| `src/app/template/layout.php` | Layout maestro: sidebar filtrado por permisos, scripts globales |
| `src/Database/estructura.sql` | Esquema de base de datos (fuente de verdad) |
| `src/Database/seed_data.sql` | Datos de prueba (roles, permisos, usuarios) |

---

## 3. Convenciones obligatorias

### 3.1 PHP

1. Comentarios y documentación en español. Un comentario por bloque, con el formato `// FUNCIÓN:` / `// PROPÓSITO:` en las funciones de JS y `/** ... */` en las clases PHP.
2. Tipado estricto en las firmas (`string $nombre, int $id`): el proyecto es PHP 8.3.
3. Una clase por archivo, namespace `App\Core`, `App\Models` o `App\Controllers`.
4. Los modelos heredan de `App\Core\Model`; los controladores de `App\Controllers\BaseController` cuando usan su API (`jsonSuccess`, `jsonError`, `validarCampos`).
5. Sin dependencias nuevas. Si una librería parece necesaria, justifícala en el `README.md` antes de añadirla.

### 3.2 Base de datos

1. Solo consultas con prepared statements y `bindParam`. Nunca interpolar variables en SQL.
2. Toda entrada externa pasa por `App\Core\Validator` antes de tocar el modelo.
3. `src/Database/estructura.sql` es la fuente de verdad del esquema. Los seeds (`seed_data.sql`, `seed_data_masivo.sql`) son para instalaciones nuevas.
4. Los cambios sobre instalaciones existentes van en un script idempotente en `src/Database/migracion_*.sql`, con `INSERT ... WHERE NOT EXISTS` (la tabla `permisos` no tiene índice único, así que `INSERT IGNORE` no evita duplicados).

### 3.3 Seguridad

1. Toda mutación (crear, actualizar, eliminar, cambiar estado, registrar, exportar) exige `POST` **y** `Router::verifyCsrfToken()`.
2. Escapado en la salida: `htmlspecialchars($x, ENT_QUOTES, 'UTF-8')` en PHP, `$('<span>').text(x).html()` en JS.
3. `password_hash()` / `password_verify()` con Bcrypt para contraseñas. La longitud mínima es 8 caracteres.
4. Los errores técnicos nunca llegan al usuario: se registran con `Logger::error()` y el usuario recibe un mensaje genérico.

### 3.4 Contrato JSON de la API

Todas las respuestas AJAX comparten la misma forma:

```json
{ "success": true,  "data": null, "message": "Operación exitosa" }
{ "success": false, "error": "mensaje legible", "fieldErrors": { "campo": "error" } }
```

| Situación | Código HTTP |
| :--- | :--- |
| Operación correcta | 200 |
| Error de validación (`ValidationException`) | 422 |
| Token CSRF inválido | 400 |
| Recurso inexistente | 404 |
| **Permiso insuficiente** | **403** |

El frontend consume `fieldErrors` en la raíz. `EIS.mostrarErroresFormulario(form, r)` marca cada campo inválido.

### 3.5 Frontend

1. Un módulo JS por vista, en `src/Public/js/app.<modulo>.js`. Se carga condicionalmente desde `layout.php`.
2. Las utilidades compartidas viven en `app.core.js` bajo el namespace `EIS`.
3. Las tablas usan DataTables. Se inicializan con `EIS.datatable(selector)` y se refrescan con `EIS.datatableRefresh(selector)`.
4. Los `select` de Materialize se inicializan con `EIS.formSelect(selector)` (destruye la instancia previa para evitar barras duplicadas).
5. Los formularios se validan en el cliente con `EIS.validarFormulario(form)` y se revalidan en el servidor.

---

## 4. Reglas de control de acceso (RBAC)

El sistema tiene dos niveles de permiso: **módulo** y **acción**.

### 4.1 Fuente de verdad

`src/app/core/Permisos.php` es la única fuente de verdad:

| Constante | Contenido |
| :--- | :--- |
| `MAPA_PAGINA_PERMISO` | Página (`?pagina=X`) → permiso base del módulo. `null` = acceso abierto. |
| `CATALOGO` | Módulo → permiso base, etiqueta, icono y verbos de acción disponibles. |
| `MAPA_ACCIONES` | Página → acción (`?action=Y`) → verbo. `null` = acción de lectura. |

### 4.2 Resolución

1. **Página**: sin el permiso base del módulo, no hay acceso. La respuesta es 403.
2. **Lectura** (`null` en `MAPA_ACCIONES`): permitida con el permiso base.
3. **Escritura**: si el usuario tiene algún permiso compuesto `base:verbo` de ese módulo, solo se permiten los verbos asignados. Si no tiene ninguno, hereda el acceso completo del módulo.
4. **Acción no mapeada**: se deniega. Fail closed.
5. **Superusuario**: el rol `roles.id = 1` (Administrador) recibe todos los permisos de la tabla `permisos`, con independencia de la matriz.

### 4.3 Checklist al agregar o cambiar un módulo

1. Añade la página a `Router::PAGE_TITLES` y, si tiene acciones, a `Router::CONTROLLERS`.
2. Añade el módulo a `Permisos::CATALOGO` con su permiso base y sus verbos.
3. Añade la página a `Permisos::MAPA_PAGINA_PERMISO`.
4. Mapea **todas** las acciones del controlador en `Permisos::MAPA_ACCIONES`. Una acción sin mapear queda bloqueada.
5. Añade el enlace al sidebar de `layout.php`, envuelto en `if ($puede('permiso_base'))`.
6. Añade los permisos nuevos a `src/Database/seed_data.sql` y a `src/Database/migracion_v*.sql` si la instalación ya existe.
7. Añade el caso de prueba a `docs/Plan_de_Pruebas.md` con el formato de `formato_plan_de_pruebas.md`.

### 4.4 Prohibiciones

1. Ningún endpoint AJAX puede quedar fuera del control de acceso. El corte vive en `Router::handle()`, antes de `dispatchAction()`.
2. No copies la lista de permisos a mano en varios ficheros. Todo pasa por `App\Core\Permisos`.
3. No inventes permisos fuera del catálogo. Un permiso no catalogado no tiene acciones asociadas y no se puede evaluar.

---

## 5. Service Worker y caché

`src/sw.js` sirve los assets estáticos con estrategia *Stale-While-Revalidate*: la primera carga tras un despliegue devuelve la versión cacheada y actualiza en segundo plano.

**Si modificas cualquier fichero de `src/Public/js/`:**

1. Sube `CACHE_NAME` en `src/sw.js` (`eis-cache-v5` → `eis-cache-v6` → ...).
2. Añade el fichero a `STATIC_ASSETS` si no está.
3. Anota el bump en el `README.md` (sección "Historial de Versiones").

Sin el bump, el navegador sirve el script viejo y el cambio no aparece hasta la segunda recarga.

Las páginas PHP se sirven con *Network First*: no necesitan bump de caché.

---

## 6. Verificación antes de dar por terminado un cambio

```bash
# 1. Sintaxis de cada PHP tocado
php -l src/app/core/Permisos.php
php -l src/app/core/router.php

# 2. Autocarga tras crear o renombrar clases
composer dump-autoload

# 3. Sin errores PHP ni warnings en los logs
#    Revisar src/logs/errores.md
```

Checklist manual mínimo:

1. Login correcto e incorrecto, y bloqueo por `LoginThrottle` tras 5 intentos.
2. Un usuario sin permiso recibe 403 al entrar por URL directa.
3. Un usuario sin permiso recibe 403 en una acción AJAX.
4. El sidebar solo muestra los módulos permitidos.
5. Un usuario desactivado pierde el acceso en la petición siguiente.
6. Los módulos sin cambios siguen funcionando (CRUD completo de uno de ellos).

Documenta el resultado en `docs/Plan_de_Pruebas.md` con la plantilla de `formato_plan_de_pruebas.md`.

---

## 7. Documentación

| Qué tocas | Qué actualizas |
| :--- | :--- |
| Añades o cambias un módulo | `README.md` (tabla de módulos), `docs/Documentacion/ANALISIS_TECNICO.md` |
| Cambias el núcleo (router, base de datos, validador) | `docs/Documentacion/DOCUMENTACION.md`, `docs/EXPLICACION_LINEA_POR_LINEA.md` |
| Cambias el enrutado o el control de acceso | `docs/Documentacion/Enrutado del proyecto.md`, `docs/Documentacion/routing-system.md`, `docs/Documentacion/Mapa navegacional.md` |
| Cambias el esquema de base de datos | `src/Database/estructura.sql`, `src/Database/migracion_*.sql`, `docs/Documentacion/Base de datos.md` |
| Cambias permisos o roles | `src/app/core/Permisos.php`, seeds, `docs/Documentacion/ANALISIS_TECNICO.md` |
| Subes la versión | `README.md` (tabla, historial, estadísticas), `memory.md` |

El `README.md` y `memory.md` no quedan desfasados respecto al código. Si una tabla del `README.md` dice "13 controladores" y añades uno, actualiza el número en la misma entrega.

---

## 8. Estado vivo

`memory.md` registra el estado actual del proyecto: versión, rama, qué se hizo, qué falta, decisiones de diseño con su motivo y los hallazgos abiertos. Se actualiza al cerrar cada iteración.
