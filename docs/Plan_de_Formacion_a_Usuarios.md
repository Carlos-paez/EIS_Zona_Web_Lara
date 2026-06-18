# Plan de Formación a Usuarios — EIS System (Zona Web Lara)

## Objetivo

Capacitar a todos los usuarios finales del sistema EIS en el uso correcto de cada módulo según su rol, garantizando que puedan realizar sus tareas diarias de manera eficiente y autónoma.

---

## Perfiles de Usuario

| Perfil | Descripción | Módulos que utiliza |
|--------|-------------|---------------------|
| **Administrador** | Dueño o gerente del negocio. Gestiona usuarios, configura el sistema y supervisa todas las áreas. | Todos los módulos |
| **Vendedor (POS)** | Encargado del punto de venta. Registra ventas, consulta inventario y genera reportes básicos. | Dashboard, Inventario (consulta), Ventas/POS, Reportes |
| **Operador de Cybercafé** | Atiende las estaciones de cybercafé, inicia/cierra sesiones y cobra a los clientes. | Dashboard, Cyber Control, Reportes |
| **Asesor Legal** | Registra casos de asesoría legal, consulta historial y da seguimiento a expedientes. | Dashboard, Asesoría Legal, Reportes |
| **Encargado de Almacén** | Gestiona el inventario, realiza movimientos de stock (entradas/salidas) y controla solicitudes a proveedores. | Dashboard, Inventario, Proveedores, Activos Fijos |

---

## Estructura de la Formación

### Modalidad: Presencial con apoyo de manuales digitales
- **Sesiones grupales** por perfil de usuario (máx. 5 personas por grupo)
- **Sesión general** inicial con todos los usuarios (visión general del sistema)
- **Material de apoyo**: Guías rápidas impresas + videotutoriales grabados
- **Duración total estimada**: 12 horas distribuidas en 4 días

### Cronograma de Capacitación

| Día | Sesión | Perfiles | Duración | Temas |
|-----|--------|----------|----------|-------|
| **Día 1 - Mañana** | Sesión General | Todos los usuarios | 2h | Visión general, login, layout, navegación, tema oscuro/claro, PWA |
| **Día 1 - Tarde** | Módulo de Inventario | Admin + Enc. Almacén + Vendedor | 2h | CRUD productos, KPIs, movimientos de stock, búsqueda y filtros |
| **Día 2 - Mañana** | Módulo de Ventas/POS | Admin + Vendedor | 2h | Catálogo, carrito de compras, procesar venta, historial |
| **Día 2 - Tarde** | Módulo Cyber Control | Admin + Operador Cyber | 2h | Estaciones, cambio de estado, tarifas, sesiones, reporte de cobro |
| **Día 3 - Mañana** | Proveedores + Activos | Admin + Enc. Almacén | 2h | Solicitudes de compra, estados, activos fijos, mantenimiento |
| **Día 3 - Tarde** | Asesoría Legal + Dashboard | Admin + Asesor Legal | 2h | Registro de casos, documentos permitidos, historial, KPIs del dashboard |
| **Día 4 - Mañana** | Usuarios + Reportes | Solo Administradores | 2h | CRUD de usuarios, roles y permisos, generador de reportes, exportación |
| **Día 4 - Tarde** | Práctica guiada + Q&A | Todos | 2h | Ejercicios prácticos supervisados, resolución de dudas, evaluación |

---

## Contenido Detallado por Sesión

### Sesión General — Todos los Usuarios (2h)

| Tema | Duración | Descripción |
|------|----------|-------------|
| **Introducción al sistema** | 15 min | ¿Qué es EIS System? Módulos disponibles, objetivo del negocio |
| **Acceso al sistema** | 15 min | Cómo ingresar: URL, credenciales, login, recuperación de contraseña |
| **Navegación general** | 20 min | Sidebar, barra superior, breadcrumbs, botón "volver arriba" |
| **Layout y componentes** | 15 min | Tarjetas de métricas (KPIs), tablas, paginación, badges, modales |
| **Tema oscuro/claro** | 5 min | Cómo alternar tema, persistencia en localStorage |
| **PWA y modo offline** | 15 min | Instalación como app, funcionamiento sin Internet, página offline |
| **Notificaciones** | 10 min | Campana de notificaciones, badges, toasts (mensajes emergentes) |
| **Práctica guiada** | 25 min | Cada usuario inicia sesión, navega por los módulos, cambia tema |
| **Q&A** | 10 min | Preguntas y respuestas |

### Módulo de Inventario — Admin / Enc. Almacén / Vendedor (2h)

| Tema | Duración | Descripción |
|------|----------|-------------|
| **Panel de inventario** | 10 min | KPIs: total productos, stock crítico, stock bajo, valor total |
| **Lista de productos** | 15 min | Columnas, códigos, precios, barras de stock, colores de estado |
| **Búsqueda y filtros** | 10 min | Buscar por nombre/código, filtrar por estado (OK/Crítico/Sin stock) |
| **Crear producto** | 15 min | Formulario: código, nombre, categoría, stock, precios |
| **Editar producto** | 10 min | Modificar datos existentes desde el botón de edición |
| **Movimientos de stock - Entrada** | 15 min | Registrar entrada, seleccionar cantidad y motivo |
| **Movimientos de stock - Salida** | 15 min | Registrar salida, validar stock suficiente, motivo |
| **Historial de movimientos** | 10 min | Ver bitácora completa de un producto (fecha, tipo, cantidad, usuario) |
| **Práctica guiada** | 20 min | Cada participante crea 2 productos, realiza entrada y salida, consulta historial |

### Módulo de Ventas/POS — Admin / Vendedor (2h)

| Tema | Duración | Descripción |
|------|----------|-------------|
| **Interfaz del POS** | 10 min | Catálogo de productos, buscador, resumen del carrito |
| **Agregar productos al carrito** | 15 min | Clic en producto, confirmación visual, contador de items |
| **Carrito de compras** | 15 min | Abrir modal, revisar items, modificar cantidades, eliminar productos |
| **Procesar venta** | 15 min | Botón "Procesar", confirmación, actualización de stock |
| **Historial de ventas** | 10 min | Consultar ventas realizadas, totales por período |
| **Manejo de errores** | 10 min | Producto sin stock, carrito vacío, venta cancelada |
| **Práctica guiada** | 25 min | Simular 3 ventas: agregar productos, procesar, verificar stock |
| **Q&A** | 10 min | Preguntas y respuestas |

### Módulo Cyber Control — Admin / Operador Cyber (2h)

| Tema | Duración | Descripción |
|------|----------|-------------|
| **Panel de estaciones** | 10 min | KPIs: disponibles, ocupadas, mantenimiento, total |
| **Zonas y estaciones** | 15 min | Organización por zonas, identificación por número, íconos de estado |
| **Cambio de estado** | 15 min | Clic en estación: Disponible → Ocupada → Mantenimiento |
| **Inicio de sesión** | 15 min | Registrar cliente, seleccionar tarifa, iniciar sesión |
| **Cierre de sesión** | 15 min | Finalizar sesión, cálculo automático de costo total |
| **Filtros rápidos** | 10 min | Botones: Todas, Disponibles, Ocupadas, Mantenimiento |
| **Historial de sesiones** | 10 min | Consultar sesiones anteriores, ingresos generados |
| **Práctica guiada** | 20 min | Simular 3 sesiones: iniciar, cambiar estado, cerrar y verificar costo |
| **Q&A** | 10 min | Preguntas y respuestas |

### Proveedores + Activos Fijos — Admin / Enc. Almacén (2h)

| Tema | Duración | Descripción |
|------|----------|-------------|
| **Panel de solicitudes** | 10 min | KPIs: total, pendientes, recibidas, proveedores registrados |
| **Lista de solicitudes** | 10 min | Columnas: ID, proveedor, rubro, fecha, estado |
| **Crear solicitud de compra** | 15 min | Seleccionar proveedor, productos, cantidades, montos |
| **Estados de solicitud** | 10 min | Pendiente → Aprobada → Enviada → Recibida → Cancelada |
| **Módulo de activos fijos** | 10 min | Categorías: equipos, licencias, herramientas |
| **Registrar activo** | 10 min | Nombre, serie, categoría, estado, responsable |
| **Estados de activo** | 10 min | Activo, Mantenimiento, Vencido, Disponible |
| **Resumen de activos** | 5 min | Totales, desglose por estado |
| **Práctica guiada** | 25 min | Crear solicitud, cambiar estado, registrar activo |
| **Q&A** | 10 min | Preguntas y respuestas |

### Asesoría Legal + Dashboard — Admin / Asesor Legal (2h)

| Tema | Duración | Descripción |
|------|----------|-------------|
| **Dashboard general** | 15 min | KPIs del negocio: ventas hoy, stock crítico, sesiones cyber, solicitudes |
| **Horas pico y actividad reciente** | 10 min | Interpretar tabla de horas pico, actividad reciente |
| **Panel de asesoría legal** | 10 min | Banner informativo, contador de registros del día |
| **Registrar asesoría** | 15 min | Formulario: ciudadano, cédula, tipo de documento, descripción |
| **Validación de documentos** | 10 min | Documentos permitidos vs. derivación a oficina oficial |
| **Historial de asesorías** | 10 min | Buscar por ciudadano, cédula o documento; ver estado del caso |
| **Estados de caso** | 10 min | Pendiente → En Proceso → Finalizada → Archivada |
| **Práctica guiada** | 20 min | Registrar 3 asesorías, buscar en historial, cambiar estado |
| **Q&A** | 10 min | Preguntas y respuestas |

### Usuarios + Reportes — Solo Administradores (2h)

| Tema | Duración | Descripción |
|------|----------|-------------|
| **Gestión de usuarios** | 10 min | Panel: total, activos, inactivos, administradores |
| **Crear usuario** | 15 min | Formulario: nombre, usuario, email, contraseña, rol |
| **Roles y permisos** | 15 min | Admin / Editor / Visor, asignación de módulos por rol |
| **Activar/desactivar usuario** | 10 min | Cambiar estado, impacto en acceso al sistema |
| **Editar usuario** | 10 min | Modificar datos, restablecer contraseña |
| **Generador de reportes** | 15 min | Tipo de reporte, rango de fechas, formato (PDF/Excel/CSV) |
| **Exportación y descarga** | 10 min | Descargar reporte, abrir en visor externo |
| **Reportes recientes** | 5 min | Historial de reportes generados, re-descarga |
| **Práctica guiada** | 20 min | Crear 2 usuarios con distintos roles, generar 2 reportes |
| **Q&A** | 10 min | Preguntas y respuestas |

### Práctica Guiada + Evaluación Final — Todos los Usuarios (2h)

| Tema | Duración | Descripción |
|------|----------|-------------|
| **Ejercicio 1: Recorrido completo** | 20 min | Login → Dashboard → Inventario → Ventas → Cyber → Reportes |
| **Ejercicio 2: Caso real** | 25 min | Simular un día de trabajo completo según el perfil de cada usuario |
| **Ejercicio 3: Resolución de problemas** | 15 min | Escenarios: olvido de contraseña, producto sin stock, estación dañada |
| **Evaluación práctica** | 30 min | Cada usuario completa una lista de verificación de tareas |
| **Cierre y certificados** | 30 min | Resultados, entrega de manuales, certificados de capacitación |

---

## Material de Apoyo

### Para cada usuario (entregable impreso y digital)
1. **Guía Rápida de Inicio** (1 página) — Credenciales, URL, acceso a módulos
2. **Manual del Usuario por Perfil** (8-12 páginas) — Pasos detallados con capturas de pantalla
3. **Hoja de Referencia Rápida** (1 página) — Atajos, íconos, colores de estado

### Para el Administrador
4. **Manual de Administración del Sistema** (20 páginas) — Gestión de usuarios, roles, configuración
5. **Guía de Instalación y Mantenimiento** — Procedimiento de backup, solución de problemas

### Material Digital
6. **Videotutoriales** (uno por módulo, 5-10 min cada uno) — Alojados en carpeta compartida
7. **FAQ** (3 páginas) — Preguntas frecuentes y respuestas

---

## Criterios de Evaluación

Cada usuario debe demostrar competencia en las siguientes tareas según su perfil:

| Tarea | Admin | Vendedor | Op. Cyber | Asesor Legal | Enc. Almacén |
|-------|-------|----------|-----------|--------------|--------------|
| Iniciar sesión y navegar | ✓ | ✓ | ✓ | ✓ | ✓ |
| Usar el dashboard | ✓ | ✓ | ✓ | ✓ | ✓ |
| Consultar inventario | ✓ | ✓ | ✓ | | ✓ |
| Crear/editar producto | ✓ | | | | ✓ |
| Registrar movimiento de stock | ✓ | | | | ✓ |
| Procesar venta en POS | ✓ | ✓ | | | |
| Gestionar estaciones cyber | ✓ | | ✓ | | |
| Cambiar estado de estación | ✓ | | ✓ | | |
| Registrar asesoría legal | ✓ | | | ✓ | |
| Buscar en historial de asesorías | ✓ | | | ✓ | |
| Crear solicitud a proveedor | ✓ | | | | ✓ |
| Gestionar activos fijos | ✓ | | | | ✓ |
| Crear/editar usuario | ✓ | | | | |
| Generar reporte | ✓ | ✓ | ✓ | ✓ | ✓ |

### Escala de Evaluación

| Nivel | Descripción | Criterio |
|-------|-------------|----------|
| **A - Competente** | Realiza todas las tareas sin ayuda | 90-100% |
| **B - Funcional** | Realiza todas las tareas con ayuda mínima | 70-89% |
| **C - Requiere refuerzo** | Realiza tareas básicas, necesita supervisión | 50-69% |
| **D - No competente** | No logra realizar las tareas | < 50% |

Los usuarios con nivel C o D recibirán una sesión de refuerzo adicional de 1 hora.

---

## Recursos Necesarios

### Para la capacitación
- **Espacio**: Sala con proyector y pizarrón
- **Equipos**: 1 computadora por participante + 1 para el instructor
- **Software**: Navegador Chrome/Firefox actualizado, acceso al sistema EIS en entorno de pruebas
- **Datos de prueba**: Usuarios pre-creados para cada perfil con datos de muestra

### Personal
- **1 Instructor técnico** — Conocimiento profundo del sistema EIS
- **1 Soporte** — Para resolver incidencias técnicas durante la capacitación

---

## Plan de Refuerzo Post-Capacitación

| Período | Acción | Responsable |
|---------|--------|-------------|
| **Semana 1** | Acompañamiento en sitio: instructor disponible 4h/día | Instructor |
| **Semana 2** | Acompañamiento remoto: soporte vía WhatsApp/Telegram 2h/día | Soporte |
| **Mes 1** | Seguimiento semanal: reunión de 30 min para resolver dudas | Admin + Soporte |
| **Mes 2-3** | Seguimiento quincenal, recolección de feedback para mejoras | Admin |

---

## Anexo: Guía Rápida de Inicio (1 página)

```
╔══════════════════════════════════════════════════════════════╗
║                   EIS SYSTEM - GUÍA RÁPIDA                   ║
║                   Sistema de Gestión Integral                ║
╚══════════════════════════════════════════════════════════════╝

  ACCESO
  ──────
  URL:    http://localhost/EIS_Zona_Web_Lara/src/
  Admin:  admin / 1234

  NAVEGACIÓN
  ──────────
  ☰ Menú lateral   → Dashboard | Inventario | Ventas | Cyber
                     Proveedores | Reportes | Activos | Asesoría
  🔔 Campana        → Notificaciones y alertas
  🕐 Reloj          → Hora actual
  🌙 Tema           → Alternar oscuro/claro en el menú lateral

  ÍCONOS DE ESTADO (Inventario)
  ─────────────────────────────
  ✅ check_circle   → Stock OK (verde)
  ⚠️ warning        → Stock crítico (rojo)
  🚫 block          → Sin stock (rojo)

  ESTADOS DE ESTACIÓN (Cyber)
  ───────────────────────────
  🟢 check_circle   → Disponible
  🟡 timelapse      → Ocupada
  🔧 build          → Mantenimiento

  ESTADOS DE ASESORÍA
  ───────────────────
  📌 Pendiente      → Caso nuevo sin revisar
  🔄 En Proceso     → En atención
  ✅ Finalizada     → Caso cerrado
  📦 Archivada      → Histórico

  REPORTES
  ────────
  PDF / Excel / CSV → Seleccionar tipo, fechas y formato
```
