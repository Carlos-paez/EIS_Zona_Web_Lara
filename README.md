# EIS_Zona_Web_Lara - Sistema de Gestión Empresarial

## Descripcion

**EIS System** es una aplicacion web de gestion empresarial desarrollada en **PHP vanilla** con **Materialize CSS** y **jQuery**. El proyecto esta disenado para administrar multiples aspectos de un negocio que incluye: ventas (POS), inventario, proveedores, activos fijos, control de cybercafe y asesoria legal.

**NOTA IMPORTANTE**: A pesar del nombre "eis_zona_web_lara", este proyecto **NO es Laravel**. Es una aplicacion PHP personalizada con enrutador procedural y diseno Material Design.

---

## Estado Actual del Proyecto

### Implementado (Funcional)
- **Sistema de Login** - Autenticacion con sesiones PHP (credenciales: admin / 1234)
- **Sistema de Layout** - Template maestro con sidebar persistente, header y footer
- **Enrutador Central** - Router.php maneja navegacion, autenticacion y layout
- **Tema Oscuro/Claro** - Toggle con persistencia en localStorage via jQuery
- **Reloj en Tiempo Real** - Clock actualizado via JavaScript
- **Sistema de Notificaciones** - Toast notifications con Materialize
- **Carrito de Compras (POS)** - Funcionalidad completa en JavaScript con modal
- **Control de Estaciones Cyber** - Toggle de estados con animaciones jQuery
- **Busqueda en Tablas** - Filtros con debounce en inventario, proveedores, activos y asesorias
- **Filtro por Estado** - Select dinamico para filtrar registros
- **Paginacion** - UI de paginacion con navegacion
- **Animacion de Contadores** - Metricas con animacion progresiva
- **Validacion de Asesoria Legal** - Validacion frontend de documentos permitidos
- **Esquema de Base de Datos** - Completo con 19 tablas, 26 indices, vistas, funciones y procedimientos
- **Modelos CRUD** - crud_users.php (8 funciones) y crud_asesorias.php (8 funciones) con PDO preparado
- **Documentacion de BD** - Completa y detallada (3 archivos MD)

### Parcialmente Implementado (UI Estatica)
- **Dashboard** - Metricas estaticas con animacion (deberian venir de consultas SQL)
- **Inventario** - Interfaz con tabla, busqueda y filtros pero sin conexion a BD
- **Ventas (POS)** - Carrito funciona pero no guarda en BD (solo simulacion)
- **Cyber Control** - Cambios de estado temporales (no persisten en BD)
- **Solicitudes** - Interfaz con tabla y filtros sin funcionalidad backend
- **Activos** - Visualizacion estatica con busqueda
- **Reportes** - Generador simulado con toasts
- **Asesoria Legal** - Validacion frontend sin persistencia en BD

### No Implementado
- **Persistencia en BD** - Las vistas no se conectan a la base de datos
- **CRUD via AJAX** - No hay operaciones create, update, delete reales via backend
- **Controladores MVC** - Arquitectura actual es procedural (sin clases)
- **Seguridad** - Credenciales hardcodeadas, sin CSRF, sin password hashing en login

---

## Estructura del Proyecto

```
eis_zona_web_lara/
├── src/
│   ├── index.php                      # Punto de entrada (6 lineas)
│   ├── .htaccess                      # Reglas de reescritura Apache
│   ├── Config/
│   │   └── database.php               # Configuracion BD (PDO + MySQL)
│   ├── app/
│   │   ├── core/
│   │   │   └── router.php             # Enrutador + layout (68 lineas)
│   │   ├── Models/
│   │   │   ├── crud_users.php         # CRUD usuarios (54 lineas, 8 funciones)
│   │   │   └── crud_asesorias.php     # CRUD asesorias (49 lineas, 8 funciones)
│   │   ├── template/
│   │   │   └── layout.php             # Layout maestro con Materialize + jQuery (128 lineas)
│   │   └── Views/
│   │       ├── login.php              # Login (123 lineas)
│   │       ├── login_validate.php     # Validacion (30 lineas)
│   │       ├── dashboard.php          # Panel principal (130 lineas)
│   │       ├── inventario.php         # Gestion inventario (129 lineas)
│   │       ├── ventas.php             # POS con carrito (130 lineas)
│   │       ├── proveedores.php        # Solicitudes (115 lineas)
│   │       ├── reportes.php           # Reportes (139 lineas)
│   │       ├── activos.php            # Activos (207 lineas)
│   │       ├── ciberControl.php       # Control cyber (133 lineas)
│   │       ├── asesorias.php          # Asesoria legal (128 lineas)
│   │       └── menu.php               # Menu alternativo (158 lineas)
│   ├── Database/
│   │   ├── estructura.sql             # Esquema BD v2.0 (19 tablas, 526 lineas)
│   │   └── datos_prueba.sql           # Datos prueba (229 lineas)
│   └── Public/
│       ├── css/
│       │   ├── styles.css             # Estilos personalizados (587 lineas)
│       │   └── login.css              # Estilos login (65 lineas)
│       └── js/
│           └── app.js                 # JS comun con jQuery (525 lineas)
├── docs/
│   ├── database-conceptual-design.md  # Diseno conceptual (581 lineas)
│   ├── database-logical-design.md     # Diseno logico (448 lineas)
│   ├── database-physical-design.md    # Diseno fisico (268 lineas)
│   ├── routing-system.md              # Sistema de enrutamiento
│   ├── diagrama-de-clases.md          # Diagrama de clases
│   └── *.pdf                          # Versiones PDF
├── vendor/                            # Autoloader de Composer
├── composer.json                      # Configuracion Composer
├── DOCUMENTACION.md                   # Documentacion tecnica (linea por linea)
├── DOCUMENTACION_JQUERY.md            # Documentacion integracion jQuery
├── DOCUMENTACION_COMPLETA.md          # Documentacion completa para NotebookLM
└── README.md                          # Este archivo
```

---

## Tecnologias Utilizadas

### Backend
- **PHP 7.4+** - Lenguaje principal (sin frameworks)
- **PDO (PHP Data Objects)** - Capa de abstraccion de BD con prepared statements
- **MySQL 8.0+ / MariaDB 10.3+** - Sistema de gestion de BD
- **Motor InnoDB** - Soporte para transacciones y claves foraneas

### Frontend
- **Materialize CSS 1.0.0** - Framework de diseno Material Design (CDN)
- **jQuery 3.7.1** - Manipulacion del DOM y eventos (CDN)
- **HTML5** - Estructura semantica
- **CSS3** - Variables CSS, Flexbox, Grid, Media Queries, tema oscuro/claro
- **Google Fonts / Material Icons** - Tipografia e iconografia

### Herramientas
- **Composer** - Autocargador de clases (PSR-4)
- **Git** - Control de versiones

---

## Modulos del Sistema

| Modulo | Descripcion | Estado | Archivo |
|---------|-------------|--------|---------|
| **Login** | Autenticacion de usuarios | Funcional | `login.php` |
| **Dashboard** | Panel principal con metricas | UI Estatica | `dashboard.php` |
| **Inventario** | Gestion de productos y stock | UI Estatica | `inventario.php` |
| **Punto de Venta** | Sistema POS con carrito modal | Semi-funcional* | `ventas.php` |
| **Cyber Control** | Control de estaciones | Interactivo* | `ciberControl.php` |
| **Solicitudes** | Pedidos a proveedores | UI Estatica | `proveedores.php` |
| **Reportes** | Generacion de estadisticas | Simulado | `reportes.php` |
| **Activos** | Control de activos fijos | UI Estatica | `activos.php` |
| **Asesoria Legal** | Validacion de documentos | Semi-funcional* | `asesorias.php` |
| **Menu** | Menu de navegacion alternativo | Funcional | `menu.php` |

*Funcionalidad del lado del cliente (JavaScript/jQuery) pero sin persistencia en BD.

---

## Instalacion y Configuracion

### Requisitos
- PHP 7.4 o superior
- MySQL 8.0 o superior / MariaDB 10.3+
- Servidor web (Apache/Nginx/XAMPP/WAMP/Laragon)

### Pasos

1. **Clonar el repositorio**
   ```bash
   git clone https://gitlab.com/carlos.paezguerra/eis_zona_web_lara.git
   cd eis_zona_web_lara
   ```

2. **Configurar la base de datos**

   Editar `src/Config/database.php`:
   ```php
   $host = "localhost";
   $db = "zwl";
   $user = "root";
   $pass = "";  // Cambiar por tu contrasena
   ```

3. **Crear la base de datos**
   ```bash
   mysql -u root -p < src/Database/estructura.sql
   mysql -u root -p < src/Database/datos_prueba.sql
   ```

4. **Configurar el servidor web**

   Asegurate de que el directorio raiz apunte a la carpeta `src/` o configura un host virtual.

5. **Acceder a la aplicacion**
   ```
   URL: http://localhost/eis_zona_web_lara/src/
   Usuario: admin
   Contrasena: 1234
   ```

---

## Documentacion Disponible

### DOCUMENTACION.md
Documentacion tecnica **linea por linea** de todo el codigo fuente:
- Explicacion detallada de cada funcion y su proposito
- Cada linea de codigo explicada
- Flujo de ejecucion detallado
- Parametros y valores de retorno
- Conceptos de PHP, PDO, JavaScript, jQuery, y CSS explicados

### DOCUMENTACION_JQUERY.md
Documentacion especifica de la integracion de **jQuery 3.7.1** y **Materialize CSS**:
- Migracion de JS vanilla a jQuery
- Layout maestro y refactorizacion de vistas
- Archivo `app.js` explicado linea por linea

### docs/database-*.md
Documentacion completa de la base de datos (v2.0):
- **Conceptual**: Diagramas ER, entidades, relaciones, reglas de negocio
- **Logico**: Esquemas SQL, tipos de datos, indices, normalizacion
- **Fisico**: Almacenamiento InnoDB, particionamiento, configuracion MySQL

---

## Problemas Conocidos

### Errores y Typos
1. **`login.css`** - No hay variables `--border` y `--shadow` declaradas en login (usadas inline)
2. **`.idea/laravel-idea.xml`** - Referencia incorrecta a Laravel

### Problemas de Seguridad
1. **Credenciales Hardcodeadas** - Usuario y contrasena en codigo fuente
2. **Sin CSRF Protection** - Formularios sin tokens de proteccion
3. **Sin Password Hashing** - Contrasenas en texto plano en el login
4. **Configuracion de BD** - `echo "Conexion exitosa"` romperia respuestas JSON

### Problemas de Arquitectura
1. **Sin Separacion de Capas** - Vistas contienen logica de negocio
2. **Sin Modelos Usados** - Los modelos existen pero no se incluyen en vistas
3. **Datos Estaticos** - Vistas no se conectan a la base de datos
4. **Sin .env** - Configuracion no flexible

---

## Proximos Pasos Recomendados

### Fase 1: Conexion a Base de Datos
- [ ] Conectar Dashboard con consultas SQL reales
- [ ] Hacer que el carrito POS persista ventas en BD
- [ ] Implementar CRUD de productos via AJAX
- [ ] Persistir cambios de estado en cybercafe

### Fase 2: Migracion a MVC
- [ ] Crear controladores con namespaces (App\Controllers)
- [ ] Migrar router procedural a Router con clases
- [ ] Implementar Request y Controller como clases base
- [ ] Separar logica de negocio de las vistas

### Fase 3: Seguridad
- [ ] Implementar password_hash() para contrasenas
- [ ] Agregar CSRF tokens
- [ ] Sanitizar entrada de datos
- [ ] Usar sentencias preparadas (ya configurado PDO)

### Fase 4: Funcionalidad
- [ ] Persistencia de ventas en BD
- [ ] Calculo real de tiempos en cybercafe
- [ ] Generacion real de reportes (PDF/Excel)
- [ ] Gestion completa de inventario

---

## Estadisticas del Proyecto

| Metrica | Valor |
|----------|-------|
| Total lineas de codigo | ~2,800 |
| Lineas de documentacion | ~4,000 |
| Archivos PHP | 15 |
| Archivos CSS | 2 |
| Archivos JS | 1 |
| Archivos SQL | 2 |
| Tablas en BD | 19 |
| Vistas (Views) | 11 |
| Modelos funcionales | 2 (8 funciones c/u) |
| Modulos del sistema | 9 |

---

## Autor

**Carlos Paez Guerra**
Email: carlospaezguerra@gmail.com

---

## Licencia

Este proyecto es propietario. Todos los derechos reservados.

---

## Historial de Versiones

| Version | Fecha | Descripcion |
|---------|-------|-------------|
| 1.2 | 2026 | Agregado modulo de Asesoria Legal, actualizacion de BD a v2.0 (19 tablas), nuevos modelos CRUD |
| 1.1 | 2026 | Refactorizacion con Materialize CSS + jQuery + Layout maestro |
| 1.0 | 2024 | Version inicial - UI Prototype |

---

**Ultima actualizacion**: Mayo 2026
**Estado**: En desarrollo (Prototipo UI)
