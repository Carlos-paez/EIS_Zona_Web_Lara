# Mapa Navegacional — EIS Zona Web Lara (ZWL)

  

> Diagrama de navegación de la aplicación PHP generado con Mermaid.

  

---

  

## Diagrama

  

```mermaid
---

config:

  layout: elk

  theme: mc

---

flowchart TB

 subgraph subGraph1["CAPA DE DATOS"]

        STATIC["📁 Datos Estáticos/Simulados<br>(Prototipo UI)"]

        DB@{ label: "🗄️ DB 'zwl'<br>MySQL + PDO" }

        USERS_MODEL["crud_users.php"]

        ASES_MODEL["crud_asesorias.php"]

  end

    ENTRY["/ (Raíz)"] -- ".htaccess redirige a src/" --> INDEX["src/index.php"]

    INDEX -- require --> ROUTER["src/app/core/router.php"]

    ROUTER -- "?pagina=login (default)" --> LOGIN["login.php<br>Formulario de acceso"]

    ROUTER -- "?pagina=login_validate" --> VALIDATE["login_validate.php<br>Validar credenciales"]

    ROUTER -- "?pagina=dashboard" --> DASHBOARD["dashboard.php"]

    ROUTER -- "?pagina=inventario" --> INVENTARIO["inventario.php"]

    ROUTER -- "?pagina=ventas" --> VENTAS["ventas.php"]

    ROUTER -- "?pagina=proveedores" --> PROVEEDORES["proveedores.php"]

    ROUTER -- "?pagina=ciberControl" --> CIBER["ciberControl.php"]

    ROUTER -- "?pagina=reportes" --> REPORTES["reportes.php"]

    ROUTER -- "?pagina=activos" --> ACTIVOS["activos.php"]

    ROUTER -- "?pagina=asesorias" --> ASESORIAS["asesorias.php"]

    LOGIN -- POST usuario/contraseña --> VALIDATE

    VALIDATE -- admin / 1234 --> SESSION@{ label: "$_SESSION['logged_in'] = true" }

    VALIDATE -- fallo --> LOGIN_ERROR["?pagina=login&error=1"]

    SESSION -- redirect --> DASHBOARD

    LAYOUT["layout.php"] --> SIDEBAR["Sidebar Izquierdo<br>8 módulos"] & TOPBAR["Barra Superior<br>Reloj + Notificaciones + Usuario"] & CONTENT["require \$contentView<br>(vista específica)"]

    SIDEBAR -- Dashboard --> DASHBOARD

    SIDEBAR -- Inventario --> INVENTARIO

    SIDEBAR -- Ventas (POS) --> VENTAS

    SIDEBAR -- Solicitudes --> PROVEEDORES

    SIDEBAR -- Cyber --> CIBER

    SIDEBAR -- Reportes --> REPORTES

    SIDEBAR -- Activos --> ACTIVOS

    SIDEBAR -- Asesoría Legal --> ASESORIAS

    SIDEBAR -- Cerrar Sesión --> LOGIN

    DASHBOARD -- Panel de Control<br>KPIs, Horas pico, Stock crítico --> DASH_VIEW["📊 Vista Dashboard"]

    INVENTARIO -- Gestión de Inventario<br>Búsqueda, Tabla, Paginación --> INV_VIEW["📦 Vista Inventario"]

    VENTAS -- Punto de Venta<br>Catálogo, Carrito, Procesar --> VENT_VIEW["🛒 Vista Ventas"]

    PROVEEDORES -- Solicitudes a Proveedores<br>Filtros, Tabla, Paginación --> PROV_VIEW["📋 Vista Proveedores"]

    CIBER -- Control Cybercafé<br>3 Zonas, 10 Estaciones --> CIBER_VIEW["🖥️ Vista Cyber"]

    REPORTES -- Reportes y Estadísticas<br>KPIs mensuales, Generador --> REP_VIEW["📈 Vista Reportes"]

    ACTIVOS -- Gestión de Activos<br>Equipos, Licencias, Herramientas --> ACT_VIEW["🔧 Vista Activos"]

    ASESORIAS -- Asesoría Legal<br>Registro, Validación, Historial --> ASE_VIEW["⚖️ Vista Asesorías"]

    DASH_VIEW -. (futuro) .-> DB

    INV_VIEW -. (futuro) .-> DB

    VENT_VIEW -. (futuro) .-> DB

    PROV_VIEW -. (futuro) .-> DB

    CIBER_VIEW -. (futuro) .-> DB

    REP_VIEW -. (futuro) .-> DB

    ACT_VIEW -. (futuro) .-> DB

    ASE_VIEW -. (futuro) .-> ASES_MODEL

    ASES_MODEL --> DB

    USERS_MODEL --> DB

  

    SESSION@{ shape: rect}

    DB@{ shape: cylinder}
```

  

---

  

## Tabla de rutas

  

| Ruta | Tipo | Vista | Descripción |

|------|------|-------|-------------|

| `?pagina=login` | 🔓 Pública | `login.php` | Formulario de acceso |

| `?pagina=login_validate` | 🔓 Pública | `login_validate.php` | Valida admin/1234, crea sesión |

| `?pagina=dashboard` | 🔒 Privada | `dashboard.php` | Panel de control con KPIs |

| `?pagina=inventario` | 🔒 Privada | `inventario.php` | Gestión de inventario |

| `?pagina=ventas` | 🔒 Privada | `ventas.php` | Punto de venta (POS) |

| `?pagina=proveedores` | 🔒 Privada | `proveedores.php` | Solicitudes a proveedores |

| `?pagina=ciberControl` | 🔒 Privada | `ciberControl.php` | Control de cybercafé |

| `?pagina=reportes` | 🔒 Privada | `reportes.php` | Reportes y estadísticas |

| `?pagina=activos` | 🔒 Privada | `activos.php` | Gestión de activos |

| `?pagina=asesorias` | 🔒 Privada | `asesorias.php` | Asesoría legal |

  

---

  

## Flujo de navegación

  

```

INICIO

  │

  ├─ /  →  .htaccess  →  src/index.php  →  router.php

  │

  ├─ [No autenticado]

  │    └─ ?pagina=login (default)

  │         └─ POST credentials → login_validate.php

  │              ├─ éxito → $_SESSION['logged_in'] → redirect /dashboard

  │              └─ fallo → redirect /login?error=1

  │

  └─ [Autenticado] → layout.php (sidebar + topbar + contenido)

       │

       ├─ /dashboard        →  Panel de Control

       ├─ /inventario       →  Gestión de Inventario

       ├─ /ventas           →  Punto de Venta (POS)

       ├─ /proveedores      →  Solicitudes a Proveedores

       ├─ /ciberControl     →  Control de Cybercafé

       ├─ /reportes         →  Reportes y Estadísticas

       ├─ /activos          →  Gestión de Activos

       ├─ /asesorias        →  Asesoría Legal

       └─ "Cerrar Sesión"   →  /login

```

  

---

  

## Mecanismo de ruteo

  

1. **Apache rewrite** (`.htaccess` en `src/`): `/dashboard` → `index.php?pagina=dashboard`

2. **Front controller** (`index.php`): Requiere `router.php`

3. **Router** (`router.php`):

   - Sanitiza el parámetro `?pagina=` (solo alfanumérico + guiones)

   - Si es página pública (`login`, `login_validate`): renderiza standalone

   - Si es página privada: verifica `$_SESSION['logged_in']`, redirige a login si no existe

   - Si la vista no existe: HTTP 404

   - Para páginas privadas: renderiza dentro de `layout.php` vía `require $contentView`

  

---

  

## Layout principal (`layout.php`)

  

| Componente | Descripción |

|------------|-------------|

| **Sidebar** | Menú lateral fijo con 8 módulos + modo oscuro + cerrar sesión |

| **Topbar** | Barra superior con título de página, reloj, notificaciones, usuario |

| **Contenido** | `<div class="container">` con `require $contentView` |

| **Back-to-top** | Botón flotante en esquina inferior derecha |

| **Scripts** | Materialize JS + `app.js` |