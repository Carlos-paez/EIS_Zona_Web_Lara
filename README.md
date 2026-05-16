# EIS_Zona_Web_Lara - Sistema de Gestión Empresarial

## Descripcion

**EIS System** es una aplicacion web de gestion empresarial desarrollada en **PHP vanilla** con **Materialize CSS** y **jQuery**. El proyecto esta disenado para administrar multiples aspectos de un negocio que incluye: ventas (POS), inventario, proveedores, activos fijos y control de cybercafe.

**NOTA IMPORTANTE**: A pesar del nombre "eis_zona_web_lara", este proyecto **NO es Laravel**. Es una aplicacion PHP personalizada con arquitectura MVC basica y Material Design.

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
- **Busqueda en Tablas** - Filtros con debounce en inventario, proveedores y activos
- **Filtro por Estado** - Select dinamico para filtrar registros
- **Paginacion** - UI de paginacion con navegacion
- **Animacion de Contadores** - Metricas con animacion progresiva
- **Esquema de Base de Datos** - Completo con 10 tablas, 9 indices y relaciones
- **Documentacion de BD** - Completa y detallada (3 archivos MD)

### Parcialmente Implementado (UI Estatica)
- **Dashboard** - Metricas estaticas con animacion (deberian venir de consultas SQL)
- **Inventario** - Interfaz con tabla, busqueda y filtros pero sin conexion a BD
- **Ventas (POS)** - Carrito funciona pero no guarda en BD (solo simulacion)
- **Cyber Control** - Cambios de estado temporales (no persisten en BD)
- **Solicitudes** - Interfaz con tabla y filtros sin funcionalidad backend
- **Activos** - Visualizacion estatica con busqueda
- **Reportes** - Generador simulado con toasts

### No Implementado
- **CRUD Operations** - No hay create, update, delete real
- **Controladores** - Directorio `Controlers/` vacio (pendiente de implementar)
- **Persistencia** - Las vistas no se conectan a la base de datos
- **Seguridad** - Credenciales hardcodeadas, sin CSRF, sin password hashing

---

## Estructura del Proyecto

```
eis_zona_web_lara/
├── src/
│   ├── index.php                      # Punto de entrada (3 lineas)
│   ├── Config/
│   │   └── database.php               # Configuracion BD (PDO + MySQL)
│   ├── app/
│   │   ├── core/
│   │   │   └── router.php             # Enrutador + layout (50 lineas)
│   │   ├── Controlers/                # VACIO (typo: deberia ser Controllers)
│   │   ├── Models/
│   │   │   └── crud_users.php         # CRUD usuarios (38 lineas, NO usado)
│   │   ├── template/
│   │   │   └── layout.php             # Layout maestro con Materialize + jQuery
│   │   └── Views/
│   │       ├── login.php              # Login (86 lineas)
│   │       ├── login_validate.php     # Validacion (19 lineas)
│   │       ├── dashboard.php          # Panel principal (135 lineas)
│   │       ├── inventario.php         # Gestion inventario (110 lineas)
│   │       ├── ventas.php             # POS con carrito (110 lineas)
│   │       ├── proveedores.php        # Solicitudes (98 lineas)
│   │       ├── reportes.php           # Reportes (132 lineas)
│   │       ├── activos.php            # Activos (185 lineas)
│   │       ├── ciberControl.php       # Control cyber (158 lineas)
│   │       └── menu.php               # Menu alternativo (133 lineas)
│   ├── Database/
│   │   ├── mian.sql                   # Esquema BD (138 lineas)
│   │   └── seed.sql                   # Datos prueba (102 lineas)
│   └── Public/
│       ├── css/
│       │   ├── styles.css             # Estilos personalizados (404 lineas)
│       │   └── login.css              # Estilos login (58 lineas)
│       └── js/
│           └── app.js                 # JS comun con jQuery (362 lineas)
├── docs/
│   ├── database-conceptual-design.md  # Diseno conceptual (346 lineas)
│   ├── database-logical-design.md     # Diseno logico (497 lineas)
│   ├── database-physical-design.md    # Diseno fisico (189 lineas)
│   └── *.pdf                          # Versiones PDF
├── vendor/                            # Autoloader de Composer
├── composer.json                      # Configuracion Composer
├── DOCUMENTACION.md                   # Documentacion tecnica (linea por linea)
├── DOCUMENTACION_JQUERY.md            # Documentacion integracion jQuery
└── README.md                          # Este archivo
```

---

## Tecnologias Utilizadas

### Backend
- **PHP 7.4+** - Lenguaje principal (sin frameworks)
- **PDO (PHP Data Objects)** - Capa de abstraccion de BD con prepared statements
- **MySQL 5.7+ / 8.0+** - Sistema de gestion de BD
- **Motor InnoDB** - Soporte para transacciones y claves foraneas

### Frontend
- **Materialize CSS 1.0.0** - Framework de diseno Material Design (CDN)
- **jQuery 3.7.1** - Manipulacion del DOM y eventos (CDN)
- **HTML5** - Estructura semantica
- **CSS3** - Variables CSS, Flexbox, Grid, Media Queries, tema oscuro/claro
- **JavaScript Vanilla** - Logica POS y algunas vistas especificas
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

*Funcionalidad del lado del cliente (JavaScript/jQuery) pero sin persistencia en BD.

---

## Instalacion y Configuracion

### Requisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx/XAMPP/WAMP)

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
   mysql -u root -p < src/Database/mian.sql
   mysql -u root -p < src/Database/seed.sql
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
Documentacion completa de la base de datos:
- **Conceptual**: Diagramas ER, entidades, relaciones, reglas de negocio
- **Logico**: Esquemas SQL, tipos de datos, indices, normalizacion
- **Fisico**: Almacenamiento InnoDB, particionamiento, configuracion MySQL

---

## Problemas Conocidos

### Errores y Typos
1. **`Controlers/`** - Deberia ser `Controllers/` (error de ortografia)
2. **`mian.sql`** - Deberia ser `main.sql` (error de ortografia)
3. **`.idea/laravel-idea.xml`** - Referencia incorrecta a Laravel

### Problemas de Seguridad
1. **Credenciales Hardcodeadas** - Usuario y contrasena en codigo fuente
2. **Sin CSRF Protection** - Formularios sin tokens de proteccion
3. **Sin Password Hashing** - Contrasenas en texto plano
4. **Configuracion de BD** - `echo "Conexion exitosa"` romperia respuestas JSON

### Problemas de Arquitectura
1. **Sin Separacion de Capas** - Vistas contienen logica de negocio
2. **Sin Modelos Usados** - crud_users.php existe pero no se incluye
3. **Datos Estaticos** - Vistas no se conectan a la base de datos
4. **Sin .env** - Configuracion no flexible

---

## Proximos Pasos Recomendados

### Fase 1: Correccion de Errores
- [ ] Renombrar `Controlers/` a `Controllers/`
- [ ] Renombrar `mian.sql` a `main.sql`
- [ ] Corregir configuracion de PHPStorm
- [ ] Eliminar `echo "Conexion exitosa"` de database.php

### Fase 2: Implementacion de Backend
- [ ] Crear controladores en `Controllers/`
- [ ] Expandir modelos para todas las tablas
- [ ] Conectar vistas con base de datos
- [ ] Implementar CRUD completo

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
| Total lineas de codigo | ~2,200 |
| Lineas de documentacion | ~2,200 |
| Archivos PHP | 14 |
| Archivos CSS | 2 |
| Archivos JS | 1 |
| Tablas en BD | 10 |
| Vistas (Views) | 10 |
| Controladores | 0 (vacio) |
| Modelos funcionales | 0 (1 no usado) |

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
| 1.1 | 2026 | Refactorizacion con Materialize CSS + jQuery + Layout maestro |
| 1.0 | 2024 | Version inicial - UI Prototype |

---

**Ultima actualizacion**: Mayo 2026
**Estado**: En desarrollo (Prototipo UI)
# EIS_Zona_Web_Lara
