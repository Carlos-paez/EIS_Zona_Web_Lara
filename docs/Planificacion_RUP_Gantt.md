# Planificación de Desarrollo — EIS System (Zona Web Lara)

## Metodología: RUP (Rational Unified Process)

### Fases de RUP aplicadas al proyecto

| Fase | Objetivo |
|------|----------|
| **I. Concepción (Inception)** | Definir el alcance del sistema, identificar stakeholders, casos de uso del negocio y viabilidad del proyecto |
| **II. Elaboración (Elaboration)** | Analizar requisitos detallados, diseñar la arquitectura, la base de datos y los prototipos de interfaz |
| **III. Construcción (Construction)** | Codificar, probar e integrar cada módulo del sistema |
| **IV. Transición (Transition)** | Desplegar el sistema, capacitar usuarios y realizar mantenimiento |

---

## Diagrama de Gantt — Plan de Trabajo

```mermaid
gantt
    title Planificación de Desarrollo - EIS System (RUP)
    dateFormat  YYYY-MM-DD
    axisFormat  %b %Y

    section FASE I: CONCEPCIÓN (Inception)
    Identificación de requerimientos del negocio    :a1, 2026-01-05, 10d
    Definición del alcance y objetivos del sistema  :a2, after a1, 5d
    Identificación de stakeholders y actores        :a3, after a1, 5d
    Diagrama de casos de uso del negocio            :a4, after a2, 7d
    Análisis de viabilidad técnica y económica      :a5, after a4, 5d
    Prototipado inicial de interfaz (wireframes)    :a6, after a5, 7d
    Planificación del proyecto (cronograma)         :a7, after a6, 5d
    HITO: Documento de visión aprobado              :milestone, m1, after a7, 0d

    section FASE II: ELABORACIÓN (Elaboration)
    Análisis detallado de requisitos funcionales    :b1, after a7, 14d
    Análisis de requisitos no funcionales           :b2, after b1, 7d
    Diseño de la arquitectura del sistema           :b3, after b1, 10d
    Diseño de la base de datos (conceptual/lógico)  :b4, after b1, 10d
    Diseño del esquema físico BD (21 tablas)        :b5, after b4, 7d
    Diseño del Front Controller y enrutador         :b6, after b3, 5d
    Diseño del layout maestro y template            :b7, after b3, 5d
    Diseño de módulos JS y arquitectura frontend    :b8, after b6, 7d
    Diseño de vistas, stored procedures y triggers  :b9, after b5, 7d
    Modelado de clases (Inventario, Usuario, Asesoria) :b10, after b3, 10d
    HITO: Arquitectura base definida                :milestone, m2, after b10, 0d

    section FASE III: CONSTRUCCIÓN (Construction)
    Implementación del núcleo (Router, Database, Model)  :c1, after b10, 10d
    Implementación del módulo de autenticación (AuthController + Usuario) :c2, after c1, 7d
    Implementación del módulo de inventario (CRUD + AJAX + BD) :c3, after c2, 20d
    Implementación del módulo de ventas/POS con BD           :c4, after c3, 15d
    Implementación del módulo de proveedores/solicitudes      :c5, after c4, 10d
    Implementación del módulo de cybercafé con BD             :c6, after c4, 12d
    Implementación del módulo de activos fijos                :c7, after c5, 10d
    Implementación del módulo de asesoría legal con BD        :c8, after c4, 10d
    Implementación del módulo de usuarios (CRUD + roles)      :c9, after c5, 10d
    Implementación del dashboard con datos reales            :c10, after c8, 10d
    Implementación del módulo de reportes (PDF/Excel)        :c11, after c10, 10d
    Implementación de PWA (Service Worker + offline + manifest) :c12, after c9, 7d
    Integración de seguridad (CSRF, hashing, sanitización)   :c13, after c12, 10d
    Pruebas unitarias (modelos y controladores)              :c14, after c13, 10d
    Pruebas de integración (módulos completos)               :c15, after c14, 10d
    HITO: Sistema completo funcional                         :milestone, m3, after c15, 0d

    section FASE IV: TRANSICIÓN (Transition)
    Pruebas de aceptación del usuario (UAT)          :d1, after c15, 10d
    Corrección de errores y ajustes                  :d2, after d1, 7d
    Documentación técnica del sistema                :d3, after d1, 10d
    Manuales de usuario y capacitación               :d4, after d3, 7d
    Configuración del entorno de producción           :d5, after d2, 5d
    Despliegue en producción                          :d6, after d5, 3d
    Soporte post-despliegue y mantenimiento           :d7, after d6, 15d
    HITO: Cierre del proyecto                         :milestone, m4, after d7, 0d
```

---

## Detalle de Actividades por Fase

### Fase I: Concepción (Inception) — Duración estimada: 5 semanas

| Actividad | Descripción | Entregable |
|-----------|-------------|------------|
| Identificación de requerimientos del negocio | Relevar necesidades del cliente para gestión de inventario, ventas, cybercafé, activos y asesoría legal | Lista de requerimientos priorizada |
| Definición del alcance y objetivos | Definir qué módulos se desarrollarán y cuáles quedan fuera del alcance | Documento de alcance |
| Identificación de stakeholders | Identificar usuarios del sistema: admin, vendedores, operadores cyber, asesores legales | Mapa de actores |
| Casos de uso del negocio | Modelar interacciones principales: login, CRUD inventario, realizar venta, gestionar cyber | Diagramas de casos de uso |
| Análisis de viabilidad | Evaluar stack tecnológico (PHP + MySQL + Materialize + jQuery) y viabilidad económica | Estudio de viabilidad |
| Prototipado inicial | Crear wireframes de las pantallas principales (login, dashboard, inventario, POS, cyber) | Prototipos navegables |
| Planificación del proyecto | Definir cronograma, recursos y metodología (RUP) | Plan de proyecto |

### Fase II: Elaboración (Elaboration) — Duración estimada: 7 semanas

| Actividad | Descripción | Entregable |
|-----------|-------------|------------|
| Requisitos funcionales | Especificar funcionalidades de cada módulo (11 módulos) | Documento de requisitos (ERS) |
| Requisitos no funcionales | Definir rendimiento, seguridad, usabilidad, disponibilidad offline (PWA) | Especificación no funcional |
| Arquitectura del sistema | Definir patrón Front Controller + MVC con PHP, PSR-4 autoloading | Diagrama de arquitectura |
| Diseño BD conceptual | Modelo entidad-relación: 21 tablas agrupadas en 6 dominios | Diagrama ER conceptual |
| Diseño BD físico | Esquema SQL con InnoDB, PKs, FKs, índices, vistas, SP y triggers | Script SQL (`estructura.sql`) |
| Front Controller y Router | Diseñar flujo de peticiones, enrutamiento por `?pagina=`, seguridad regex | Diseño del Router |
| Layout maestro | Diseñar template con sidebar, navbar, footer, carga JS condicional | Maqueta del layout |
| Arquitectura frontend | Modular JS (8 módulos), tema oscuro/claro, PWA, offline support | Diseño de módulos JS |
| Modelado de clases | Clases: `Inventario`, `Usuario`, `Asesoria`, `Model` base, controladores | Diagrama de clases |

### Fase III: Construcción (Construction) — Duración estimada: 20 semanas

| Actividad | Descripción | Entregable |
|-----------|-------------|------------|
| **Núcleo del framework** | `Database.php` (Singleton PDO), `Model.php` (clase abstracta), `Router.php` | Base del sistema funcional |
| **Autenticación** | `AuthController` + `Usuario` (BCRYPT, sesiones, login/logout) | Login funcional con BD |
| **Inventario** | CRUD completo con AJAX, stock movements, KPIs, categorías, búsqueda | **YA IMPLEMENTADO** |
| **Ventas/POS** | Carrito de compras, persistencia en BD, detalle_ventas, actualización de stock | POS funcional con BD |
| **Proveedores** | Solicitudes a proveedores, detalle_solicitudes, producto_proveedor | Módulo de solicitudes completo |
| **Cybercafé** | Gestión de estaciones, tarifas, sesiones, cálculo automático de costos | Cyber control con BD |
| **Activos Fijos** | CRUD de activos, tipos de activo, asignación a responsables | Módulo de activos completo |
| **Asesoría Legal** | CRUD de asesorías, asignación usuario-asesoría, búsqueda por cédula | **YA IMPLEMENTADO (backend)** |
| **Usuarios** | CRUD de usuarios, roles, permisos, último acceso | Gestión de usuarios completa |
| **Dashboard** | KPIs reales desde consultas SQL, gráficos, actividad reciente | Dashboard con datos vivos |
| **Reportes** | Generación de reportes en PDF/Excel, filtros por fecha y módulo | Reportes funcionales |
| **PWA** | Service Worker, manifest.json, offline.php, assets locales | **YA IMPLEMENTADO** |
| **Seguridad** | CSRF tokens, password_hash, sanitización de entradas, prepared statements | Sistema seguro |
| **Pruebas unitarias** | Tests para modelos (Inventario, Usuario, Asesoria) y controladores | Suite de pruebas |
| **Pruebas de integración** | Pruebas de flujos completos (login → inventario → venta → reporte) | Informe de pruebas |

### Fase IV: Transición (Transition) — Duración estimada: 7 semanas

| Actividad | Descripción | Entregable |
|-----------|-------------|------------|
| Pruebas UAT | Usuarios reales prueban cada módulo y reportan incidencias | Informe UAT |
| Corrección de errores | Resolver bugs encontrados en UAT y pruebas internas | Sistema estabilizado |
| Documentación técnica | Documentar código, arquitectura, BD, endpoints API | Documentación completa |
| Manuales de usuario | Guías de uso para cada módulo del sistema | Manuales de usuario |
| Configuración producción | Servidor web, base de datos, variables de entorno, SSL | Entorno productivo |
| Despliegue | Puesta en producción del sistema completo | Sistema en producción |
| Soporte y mantenimiento | Corrección de bugs post-lanzamiento y mejoras continuas | Acta de cierre |

---

## Resumen de esfuerzo

| Fase | Semanas | % del total |
|------|---------|-------------|
| I. Concepción | 5 | 13% |
| II. Elaboración | 7 | 18% |
| III. Construcción | 20 | 51% |
| IV. Transición | 7 | 18% |
| **Total** | **39** | **100%** |

## Estado Actual del Proyecto (Junio 2026)

| Estado | Módulos |
|--------|---------|
| **Completado** | Arquitectura base (Router, Database, Model), Autenticación, Inventario (CRUD + AJAX + BD), Asesoría Legal (backend), PWA (Service Worker + offline), Assets locales, Tema oscuro/claro |
| **En desarrollo** | Resto de módulos (ventas, proveedores, cybercafé, activos, dashboard, reportes, usuarios) |
| **Pendiente** | Seguridad (CSRF, hashing), pruebas formales, documentación completa, despliegue |
