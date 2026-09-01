# Planificación de Pruebas Funcionales e Instalación — EIS System (Zona Web Lara)

> **Actualizado (Agosto 2026):** Documento de planificación histórica. El proyecto se encuentra en fase de construcción avanzada: 12 controladores, 13 modelos POO y 21 tablas de base de datos, todos los módulos conectados a la base de datos.

## Alcance

Este documento detalla la planificación de las **pruebas funcionales** de todos los módulos del sistema EIS, así como el **plan de instalación y despliegue** en producción. Está dirigido al equipo de QA y al equipo de infraestructura/devops.

---

## Diagrama de Gantt — Pruebas Funcionales e Instalación

```mermaid
gantt
    title Planificación de Pruebas Funcionales e Instalación - EIS System
    dateFormat  YYYY-MM-DD
    axisFormat  %b %d

    section FASE I: PREPARACIÓN DE PRUEBAS
    Definición de estrategia de pruebas          :a1, 2026-07-01, 3d
    Diseño de casos de prueba funcionales        :a2, after a1, 5d
    Preparación de datos de prueba (SQL)          :a3, after a1, 3d
    Configuración del entorno de pruebas (QA)     :a4, after a1, 2d
    Revisión y aprobación del plan de pruebas     :a5, after a2, 2d
    HITO: Plan de pruebas listo                  :milestone, m1, after a5, 0d

    section FASE II: PRUEBAS FUNCIONALES POR MÓDULO
    Pruebas de autenticación (login/logout/sesiones)  :b1, after a5, 3d
    Pruebas del módulo de inventario (CRUD + movimientos + KPIs) :b2, after b1, 5d
    Pruebas del módulo de ventas/POS (carrito + BD + stock) :b3, after b2, 4d
    Pruebas del módulo de cybercafé (estaciones + tarifas + sesiones) :b4, after b3, 4d
    Pruebas del módulo de proveedores (solicitudes + detalle) :b5, after b4, 3d
    Pruebas del módulo de activos fijos (CRUD + asignación) :b6, after b5, 3d
    Pruebas del módulo de asesoría legal (registro + historial) :b7, after b6, 3d
    Pruebas del módulo de usuarios (CRUD + roles + permisos) :b8, after b7, 3d
    Pruebas del dashboard (KPIs + gráficos + datos reales) :b9, after b8, 3d
    Pruebas del módulo de reportes (PDF/Excel + filtros) :b10, after b9, 3d
    Pruebas PWA (Service Worker + offline + manifest) :b11, after b10, 2d
    HITO: Pruebas funcionales por módulo completadas :milestone, m2, after b11, 0d

    section FASE III: PRUEBAS DE INTEGRACIÓN Y SISTEMA
    Pruebas de integración inter-modular (flujos completos) :c1, after b11, 5d
    Pruebas de seguridad (CSRF, XSS, SQLi, autenticación) :c2, after c1, 4d
    Pruebas de rendimiento y carga (respuesta BD, concurrencia) :c3, after c2, 3d
    Pruebas de compatibilidad (navegadores, dispositivos móviles) :c4, after c3, 3d
    Pruebas de aceptación del usuario (UAT) :c5, after c4, 5d
    Corrección de errores y retesting :c6, after c5, 5d
    HITO: Sistema certificado para producción     :milestone, m3, after c6, 0d

    section FASE IV: INSTALACIÓN Y DESPLIEGUE
    Preparación del servidor de producción (Apache/Nginx + PHP) :d1, after c6, 2d
    Configuración de base de datos en producción (MySQL/MariaDB) :d2, after d1, 1d
    Configuración de SSL/HTTPS (certificado + redirección) :d3, after d2, 1d
    Configuración de variables de entorno y seguridad :d4, after d2, 1d
    Despliegue de la aplicación (código + assets + PWA) :d5, after d4, 1d
    Verificación post-despliegue (humo + funcionalidad crítica) :d6, after d5, 1d
    Capacitación de usuarios (admin, operadores, asesores) :d7, after d6, 3d
    Elaboración de manuales de instalación y operación :d8, after d6, 3d
    Puesta en producción (Go-Live) :d9, after d8, 1d
    Soporte post-lanzamiento (monitoreo + correcciones) :d10, after d9, 10d
    HITO: Cierre del proyecto                    :milestone, m4, after d10, 0d
```

---

## Fase I: Preparación de Pruebas — Duración estimada: 2 semanas

| Actividad | Descripción | Entregable |
|-----------|-------------|------------|
| Definición de estrategia de pruebas | Definir alcance de pruebas, herramientas (browser dev tools, Postman, etc.), tipos de prueba (funcional, integración, seguridad, UAT), y criterios de aceptación | Documento de estrategia de pruebas |
| Diseño de casos de prueba funcionales | Escribir casos de prueba detallados por módulo: escenarios felices, casos borde, errores esperados. Estimar ~15-20 casos por módulo | Matriz de casos de prueba (test cases) |
| Preparación de datos de prueba | Crear scripts SQL con datos representativos para cada módulo: productos, clientes, usuarios, estaciones cyber, asesorías, movimientos de stock | Juego de datos de prueba (`datos_prueba.sql` actualizado) |
| Configuración del entorno de pruebas | Instalar y configurar el entorno QA: servidor web, base de datos, PHP, clonar repositorio, importar esquema y datos | Entorno de pruebas funcional |
| Revisión y aprobación del plan | Revisión del plan de pruebas con el equipo de desarrollo y stakeholders | Plan de pruebas aprobado |

## Fase II: Pruebas Funcionales por Módulo — Duración estimada: 6 semanas

| Módulo | Casos de Prueba Clave | Criterio de Aceptación |
|--------|----------------------|------------------------|
| **Autenticación** | Login con credenciales válidas; login inválido; sesión expirada; logout; acceso directo a rutas protegidas; bloqueo por múltiples intentos | Usuario autenticado accede al sistema; no autenticado es redirigido al login; sesión persiste correctamente |
| **Inventario** | CRUD completo (crear, leer, actualizar, eliminar producto); movimiento de entrada/salida; búsqueda por código/nombre; filtro por estado; KPIs se actualizan en tiempo real; paginación | Todas las operaciones CRUD persisten en BD; stock se actualiza correctamente con cada movimiento; búsqueda y filtros devuelven resultados precisos |
| **Ventas/POS** | Agregar/remover productos del carrito; calcular total con descuento; finalizar venta; validar stock antes de vender; historial de ventas; impresión de ticket | La venta se registra en BD; el stock se descuenta correctamente; el carrito se limpia tras finalizar |
| **Cybercafé** | Iniciar sesión en estación; cambiar estado (Disponible/Ocupada/Mantenimiento); cálculo automático de costo al cerrar; tarifas por hora; historial de sesiones | El costo se calcula correctamente; el estado persiste en BD; las sesiones activas se muestran en tiempo real |
| **Proveedores** | CRUD de proveedores; crear solicitud de compra con detalle; cambiar estado (Pendiente/Aprobada/Enviada/Recibida/Cancelada); relación producto-proveedor | Las solicitudes fluyen correctamente por los estados; el detalle se guarda con precisión |
| **Activos Fijos** | CRUD de activos; asignación a responsable; filtro por tipo/categoría/estado; depreciación | Los activos se registran y consultan correctamente; la asignación a responsable es funcional |
| **Asesoría Legal** | Registrar asesoría con documento válido; búsqueda por cédula; historial de casos; cambio de estado (Pendiente/En Proceso/Finalizada/Archivada); asignación de usuario al caso | Validación de documentos permitidos funciona; los casos se persisten y consultan en BD |
| **Usuarios** | CRUD de usuarios; asignación de roles (Admin/Operator/Legal Advisor); activar/desactivar usuario; registro de último acceso | Solo admin puede gestionar usuarios; los cambios de rol afectan permisos correctamente |
| **Dashboard** | KPIs se cargan desde BD (ventas hoy, stock crítico, sesiones activas, solicitudes pendientes); gráficos se renderizan; datos de actividad reciente son precisos | Todos los indicadores reflejan datos reales de la BD; los gráficos se actualizan al cambiar datos |
| **Reportes** | Generar reporte por módulo (inventario, ventas, cyber); filtro por rango de fechas; exportar a PDF/Excel; reporte vacío (sin datos) | El reporte se genera en el formato seleccionado con los datos correctos del período |
| **PWA** | Service Worker registra y cachea assets; app funciona offline; manifest.json permite instalación; página offline se muestra sin conexión | Todas las páginas estáticas funcionan offline; la app es instalable como PWA |

## Fase III: Pruebas de Integración y Sistema — Duración estimada: 4 semanas

| Actividad | Descripción | Entregable |
|-----------|-------------|------------|
| Pruebas de integración inter-modular | Probar flujos completos: Login → Inventario → Venta → Reporte; Login → Cyber → Cierre sesión → Reporte; Gestión de usuarios → Asignación de roles → Acceso a módulos | Flujos completos certificados |
| Pruebas de seguridad | Validar CSRF en formularios; XSS en campos de texto; SQL injection en inputs; fuerza de password_hash (BCRYPT); protección de rutas por sesión; sanitización de salida en vistas | Informe de seguridad |
| Pruebas de rendimiento | Tiempo de respuesta de consultas BD (índices); concurrencia de usuarios; carga de assets JS/CSS; tiempo de carga PWA vs online | Informe de rendimiento (umbral: < 3s por página) |
| Pruebas de compatibilidad | Navegadores: Chrome, Firefox, Edge, Opera; dispositivos móviles (responsive); resolución de pantalla (1366×768, 1920×1080, 375×667) | Matriz de compatibilidad |
| Pruebas de aceptación (UAT) | Usuarios reales (admin, vendedor, operador cyber, asesor legal) prueban sus módulos asignados durante 5 días; reportan incidencias en formulario | Informe UAT con incidencias |
| Corrección de errores y retesting | Priorizar y corregir bugs según severidad (crítico, mayor, menor); re-ejecutar casos de prueba afectados | Sistema estabilizado |

## Fase IV: Instalación y Despliegue — Duración estimada: 3 semanas

| Actividad | Descripción | Entregable |
|-----------|-------------|------------|
| Preparación del servidor de producción | Instalar Apache/Nginx 2.4+, PHP 8.0+, MySQL 8.0+/MariaDB 10.3+, Composer. Configurar virtual host, permisos de directorios, `upload_max_filesize`, `memory_limit` | Servidor listo con requisitos |
| Configuración de base de datos | Crear base de datos `zwl`, importar `estructura.sql`, crear usuario con permisos solo a `zwl.*`, configurar charset utf8mb4, verificar Foreign Keys | Base de datos productiva |
| Configuración de SSL/HTTPS | Obtener certificado SSL (Let's Encrypt o comercial), configurar redirección HTTP → HTTPS, HSTS, reforzar seguridad TLS | HTTPS funcional (Grado A en SSL Labs) |
| Variables de entorno y seguridad | Crear archivo de configuración seguro (`Config/database.php` con credenciales reales), deshabilitar errores PHP en producción (`display_errors = Off`), configurar firewall | Configuración segura |
| Despliegue de la aplicación | Clonar repositorio en servidor, ejecutar `composer install --no-dev`, asignar permisos 755 a directorios, verificar archivos estáticos (CSS, JS, fonts), registrar Service Worker | Aplicación desplegada |
| Verificación post-despliegue | Realizar smoke test: cargar login, autenticarse, ver dashboard, probar inventario (CRUD), verificar PWA offline, revisar consola JS sin errores | Smoke test aprobado |
| Capacitación de usuarios | Sesión de 2 horas con administradores (gestión de usuarios, roles, configuración); sesión de 1 hora con operadores (inventario, ventas, cyber, asesorías) | Usuarios capacitados |
| Manuales de instalación y operación | Documentar: requisitos del servidor, pasos de instalación, configuración de BD, solución de problemas comunes, procedimiento de backup/restore | Manuales técnicos |
| Puesta en producción (Go-Live) | Migrar datos reales (si aplica), habilitar acceso público, monitorear primeras 24 horas, estar atento a incidencias | Sistema en producción |
| Soporte post-lanzamiento | Monitoreo de logs, corrección de bugs emergentes, backups automáticos, plan de continuidad | Acta de cierre |

---

## Resumen de esfuerzo

| Fase | Días hábiles | % del total |
|------|-------------|-------------|
| I. Preparación de Pruebas | 10 | 12% |
| II. Pruebas Funcionales por Módulo | 36 | 42% |
| III. Pruebas de Integración y Sistema | 25 | 29% |
| IV. Instalación y Despliegue | 15 | 17% |
| **Total** | **86** | **100%** |

> **Nota:** 86 días hábiles ≈ 18 semanas ≈ 4 meses calendario, asumiendo dedicación de 1 recurso QA y 1 devops.

---

## Roles y Responsabilidades

| Rol | Responsabilidades |
|-----|-------------------|
| **Analista QA** | Diseñar casos de prueba, ejecutar pruebas funcionales y de integración, reportar bugs, verificar correcciones |
| **Desarrollador** | Corregir bugs reportados, apoyar en pruebas de integración, preparar entorno de pruebas |
| **DevOps / Infraestructura** | Preparar servidores, configurar SSL, desplegar aplicación, monitorear post-lanzamiento |
| **Usuario final (UAT)** | Ejecutar pruebas de aceptación, validar que el sistema cumple con sus necesidades |
| **Jefe de Proyecto** | Aprobar plan de pruebas, supervisar avance, dar visto bueno para Go-Live |

---

## Criterios de Aceptación para Go-Live

1. Todos los casos de prueba críticos y mayores tienen estado **PASSED**
2. No existen bugs de severidad **crítica** abiertos
3. Las pruebas UAT tienen una tasa de aprobación ≥ 90%
4. El rendimiento cumple con el umbral (< 3s por página)
5. HTTPS funciona correctamente (Grado A)
6. El backup de la base de datos está configurado y verificado
7. Los usuarios han sido capacitados
