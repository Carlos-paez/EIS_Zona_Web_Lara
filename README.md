# EIS_Zona_Web_Lara — Sistema de Gestión Empresarial

## Descripción

**EIS System** es una aplicación web de gestión empresarial desarrollada en **PHP vanilla** con **Materialize CSS** y **jQuery**. Utiliza una arquitectura **MVC** (Modelo-Vista-Controlador) con enrutador basado en clases, namespaces PSR-4 y autoloading con Composer.

El sistema administra múltiples aspectos de un negocio: ventas (POS), inventario, proveedores, activos fijos, control de cybercafé y asesoría legal.

---

## Estado Actual del Proyecto

### Implementado (Funcional)
- **Arquitectura MVC** — Namespaces PSR-4, clases `Core\Router`, `Core\Controller`, 10 controladores por módulo
- **Sistema de Login** — Autenticación con sesiones PHP (credenciales: admin / 1234)
- **Sistema de Layout** — Template maestro con sidebar persistente, header y footer
- **Enrutador Central** — `Router.php` clase con mapa de rutas, dispatch a controladores y auth
- **Tema Oscuro/Claro** — Toggle con persistencia en localStorage
- **Reloj en Tiempo Real** — Clock actualizado vía JavaScript
- **Sistema de Notificaciones** — Toast notifications con Materialize
- **Carrito de Compras (POS)** — Funcionalidad completa en JavaScript con modal
- **Control de Estaciones Cyber** — Toggle de estados con animaciones jQuery
- **Búsqueda en Tablas** — Filtros con debounce en inventario, proveedores, activos y asesorías
- **Filtro por Estado** — Select dinámico para filtrar registros
- **Paginación** — UI de paginación con navegación
- **Animación de Contadores** — Métricas con animación progresiva
- **Validación de Asesoría Legal** — Validación frontend de documentos permitidos
- **Esquema de Base de Datos** — Completo con 19 tablas, vistas, funciones y procedimientos
- **Modelos CRUD** — `crud_users.php` (8 funciones) y `crud_asesorias.php` (8 funciones) con PDO preparado

### Parcialmente Implementado (UI Estática)
- **Dashboard** — Métricas estáticas (deberían venir de consultas SQL)
- **Inventario** — Interfaz con tabla, búsqueda y filtros pero sin conexión a BD
- **Ventas (POS)** — Carrito funciona pero no guarda en BD (solo simulación)
- **Cyber Control** — Cambios de estado temporales (no persisten en BD)
- **Solicitudes** — Interfaz con tabla y filtros sin funcionalidad backend
- **Activos** — Visualización estática con búsqueda
- **Reportes** — Generador simulado con toasts
- **Asesoría Legal** — Validación frontend sin persistencia en BD

### No Implementado
- **Persistencia en BD** — Las vistas no se conectan a la base de datos
- **CRUD vía AJAX** — No hay operaciones create, update, delete reales vía backend
- **Seguridad** — Credenciales hardcodeadas, sin CSRF, sin password hashing en login

---

## Estructura del Proyecto (MVC)

```
eis_zona_web_lara/
├── src/
│   ├── index.php                      # Front Controller con autoloader + Router
│   ├── .htaccess                      # Reglas de reescritura Apache
│   ├── Config/
│   │   └── database.php               # Configuración BD (PDO + MySQL)
│   ├── app/
│   │   ├── Core/
│   │   │   ├── Router.php             # Enrutador con dispatch a Controllers
│   │   │   └── Controller.php         # Clase base abstracta (render, renderPublic)
│   │   ├── Controllers/
│   │   │   ├── LoginController.php    # index() + validate()
│   │   │   ├── DashboardController.php
│   │   │   ├── InventarioController.php
│   │   │   ├── VentasController.php
│   │   │   ├── ProveedoresController.php
│   │   │   ├── ReportesController.php
│   │   │   ├── ActivosController.php
│   │   │   ├── CiberControlController.php  # Datos PHP movidos del view al controller
│   │   │   ├── AsesoriasController.php
│   │   │   └── MenuController.php
│   │   ├── Models/
│   │   │   ├── crud_users.php         # CRUD usuarios (8 funciones)
│   │   │   └── crud_asesorias.php     # CRUD asesorías (8 funciones)
│   │   └── Views/
│   │       ├── auth/login.php         # Formulario de inicio de sesión
│   │       ├── dashboard/index.php
│   │       ├── inventario/index.php
│   │       ├── ventas/index.php
│   │       ├── proveedores/index.php
│   │       ├── reportes/index.php
│   │       ├── activos/index.php
│   │       ├── ciber-control/index.php
│   │       ├── asesorias/index.php
│   │       ├── menu/index.php
│   │       └── layouts/main.php       # Layout maestro (sidebar + header)
│   ├── Database/
│   │   ├── estructura.sql             # Esquema BD v2.0 (19 tablas)
│   │   └── datos_prueba.sql           # Datos de prueba
│   └── Public/
│       ├── css/
│       │   ├── styles.css             # Estilos personalizados
│       │   └── login.css              # Estilos login
│       └── js/
│           └── app.js                 # JS con jQuery
├── docs/
│   ├── database-conceptual-design.md
│   ├── database-logical-design.md
│   ├── database-physical-design.md
│   ├── routing-system.md
│   └── diagrama-de-clases.md
├── vendor/                            # Autoloader de Composer
├── composer.json                      # PSR-4: "App\\": "src/app/"
├── DOCUMENTACION.md
├── DOCUMENTACION_JQUERY.md
├── DOCUMENTACION_COMPLETA.md
└── README.md
```

---

## Tecnologías Utilizadas

### Backend
- **PHP 7.4+** — Lenguaje principal con namespaces y POO
- **PDO (PHP Data Objects)** — Capa de abstracción de BD con prepared statements
- **MySQL 8.0+ / MariaDB 10.3+** — Sistema de gestión de BD
- **Motor InnoDB** — Soporte para transacciones y claves foráneas
- **Composer** — Autocargador de clases PSR-4

### Frontend
- **Materialize CSS 1.0.0** — Framework de diseño Material Design (CDN)
- **jQuery 3.7.1** — Manipulación del DOM y eventos (CDN)
- **HTML5** — Estructura semántica
- **CSS3** — Variables CSS, Flexbox, Grid, Media Queries, tema oscuro/claro
- **Google Fonts / Material Icons** — Tipografía e iconografía

---

## Módulos del Sistema

| Módulo | Controlador | Vista | Estado |
|--------|-------------|-------|--------|
| **Login** | `LoginController` | `auth/login.php` | Funcional |
| **Dashboard** | `DashboardController` | `dashboard/index.php` | UI Estática |
| **Inventario** | `InventarioController` | `inventario/index.php` | UI Estática |
| **Punto de Venta** | `VentasController` | `ventas/index.php` | Semi-funcional* |
| **Cyber Control** | `CiberControlController` | `ciber-control/index.php` | Interactivo* |
| **Solicitudes** | `ProveedoresController` | `proveedores/index.php` | UI Estática |
| **Reportes** | `ReportesController` | `reportes/index.php` | Simulado |
| **Activos** | `ActivosController` | `activos/index.php` | UI Estática |
| **Asesoría Legal** | `AsesoriasController` | `asesorias/index.php` | Semi-funcional* |
| **Menú** | `MenuController` | `menu/index.php` | Funcional |

*Funcionalidad del lado del cliente (JavaScript/jQuery) pero sin persistencia en BD.

---

## Instalación y Configuración

### Requisitos
- PHP 7.4 o superior
- MySQL 8.0 o superior / MariaDB 10.3+
- Servidor web (Apache/Nginx/XAMPP/WAMP/Laragon)
- Composer (para el autoloader)

### Pasos

1. **Clonar el repositorio**
   ```bash
   git clone <url-del-repositorio>
   cd eis_zona_web_lara
   ```

2. **Instalar dependencias (autoloader)**
   ```bash
   composer install
   ```

3. **Configurar la base de datos**
   
   Editar `src/Config/database.php`:
   ```php
   $host = "localhost";
   $db = "zwl";
   $user = "root";
   $pass = "";  // Cambiar por tu contraseña
   ```

4. **Crear la base de datos**
   ```bash
   mysql -u root -p < src/Database/estructura.sql
   mysql -u root -p < src/Database/datos_prueba.sql
   ```

5. **Configurar el servidor web**
   
   Asegúrate de que el directorio raíz apunte a la carpeta `src/` o configura un host virtual.

6. **Acceder a la aplicación**
   ```
   URL: http://localhost/eis_zona_web_lara/src/
   Usuario: admin
   Contraseña: 1234
   ```

---

## Arquitectura MVC

### Flujo de una petición

```
Navegador → src/.htaccess → src/index.php
                                   │
                          require vendor/autoload.php
                                   │
                          new Router()
                                   │
                          $router->dispatch()
                                   │
                    ┌──────────────┼──────────────┐
                    ▼                             ▼
               ¿Autenticado?               ¿Ruta existe?
                    │                             │
          ┌─────────┤                             ▼
          ▼         ▼                        Error 404
       No ──→ redirect login
          Sí
          │
          ▼
   Instanciar Controller::method()
          │
          ▼
   Controller::render('vista', $data)
          │
     ┌────┴────┐
     ▼         ▼
  layout    Vista
  main.php  específica
     │
     ▼
   Respuesta HTML
```

### Enrutador (`Router.php`)
- Mapa de rutas: `?pagina=xxx` → `[Controller, method]`
- Control de autenticación (rutas públicas vs protegidas)
- Instanciación automática del controlador vía PSR-4
- Manejo de errores 404

### Controladores
- Extienden `Core\Controller`
- `render($viewPath, $data)` — renderiza vista dentro del layout
- `renderPublic($viewPath, $data)` — renderiza vista standalone (login)
- `CiberControlController` prepara datos PHP en el servidor y los pasa a la vista

### Vistas
- Organizadas en subdirectorios por módulo (`dashboard/index.php`)
- Solo contienen HTML y PHP de presentación (sin lógica de negocio)
- `layouts/main.php` — layout principal con sidebar y navbar

### Modelos
- Funciones CRUD con consultas preparadas PDO
- `crud_users.php` — autenticación con `password_hash`/`password_verify`
- `crud_asesorias.php` — registro de asesorías con estados

---

## Documentación Disponible

### `DOCUMENTACION.md`
Documentación técnica **línea por línea** de todo el código fuente.

### `DOCUMENTACION_JQUERY.md`
Documentación específica de la integración de **jQuery 3.7.1** y **Materialize CSS**.

### `DOCUMENTACION_COMPLETA.md`
Documentación completa para NotebookLM.

### `docs/database-*.md`
Documentación completa de la base de datos (v2.0):
- **Conceptual**: Diagramas ER, entidades, relaciones, reglas de negocio
- **Lógico**: Esquemas SQL, tipos de datos, índices, normalización
- **Físico**: Almacenamiento InnoDB, particionamiento, configuración MySQL

---

## Problemas Conocidos

### Seguridad
1. **Credenciales Hardcodeadas** — Usuario y contraseña en `LoginController.php`
2. **Sin CSRF Protection** — Formularios sin tokens de protección
3. **Sin Password Hashing** — Contraseñas en texto plano en el login
4. **Configuración de BD** — `echo "Conexión exitosa"` rompería respuestas JSON

### Arquitectura
1. **Modelos No Usados** — Los modelos existen pero no se incluyen en los controladores
2. **Datos Estáticos** — Vistas no se conectan a la base de datos
3. **Sin .env** — Configuración no flexible

---

## Próximos Pasos Recomendados

### Fase 1: Conexión a Base de Datos
- [ ] Conectar Dashboard con consultas SQL reales
- [ ] Hacer que el carrito POS persista ventas en BD
- [ ] Implementar CRUD de productos vía AJAX
- [ ] Persistir cambios de estado en cybercafé

### Fase 2: Mejoras MVC
- [ ] Convertir models procedurales a clases con namespace
- [ ] Implementar Request como clase encapsuladora
- [ ] Agregar sistema de middleware (auth, CSRF)
- [ ] Implementar URLs limpias (/nombre en lugar de ?pagina=nombre)

### Fase 3: Seguridad
- [ ] Implementar `password_hash()` para contraseñas
- [ ] Agregar CSRF tokens
- [ ] Sanitizar entrada de datos
- [ ] Usar prepared statements desde los controladores

### Fase 4: Funcionalidad
- [ ] Persistencia de ventas en BD
- [ ] Cálculo real de tiempos en cybercafé
- [ ] Generación real de reportes (PDF/Excel)
- [ ] Gestión completa de inventario

---

## Estadísticas del Proyecto

| Métrica | Valor |
|---------|-------|
| Total archivos PHP | 15 |
| Clases con namespace | 12 (Router + Controller + 10 Controllers) |
| Modelos funcionales | 2 (8 funciones c/u) |
| Vistas | 11 |
| Archivos CSS | 2 |
| Archivos JS | 1 |
| Archivos SQL | 2 |
| Tablas en BD | 19 |
| Módulos del sistema | 9 |

---

## Autor

**Carlos Páez Guerra**
Email: carlospaezguerra@gmail.com

---

## Historial de Versiones

| Versión | Fecha | Descripción |
|---------|-------|-------------|
| 2.0 | 2026 | Migración completa a MVC con namespaces PSR-4, Router clase, 10 Controllers, autoloading Composer |
| 1.2 | 2026 | Agregado módulo de Asesoría Legal, actualización de BD a v2.0 (19 tablas) |
| 1.1 | 2026 | Refactorización con Materialize CSS + jQuery + Layout maestro |
| 1.0 | 2024 | Versión inicial — UI Prototype procedural |

---

**Última actualización**: Mayo 2026
**Estado**: En desarrollo (Prototipo UI con arquitectura MVC)
