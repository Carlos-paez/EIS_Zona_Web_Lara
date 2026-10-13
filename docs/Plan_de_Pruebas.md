# Plan de Pruebas de Software

## 1. Información General del Proyecto
- **Nombre del Sistema / Proyecto:** EIS System — Zona Web Lara (Sistema Integral de Gestión: Inventario, Punto de Venta, Cyber, Proveedores, Asesoría, Activos, Usuarios y Reportes).
- **Institución / Programa:** Universidad Politécnica Territorial Andrés Eloy Blanco (UPTAEB) - PNF en Informática
- **Autor / Equipo del Proyecto:** Carlos Páez Guerra
- **Fecha:** 07 de octubre de 2026

---

## 2. Objetivos del Plan de Pruebas
- Verificar que las funcionalidades desarrolladas en el software cumplen estrictamente con los requerimientos establecidos.
- Someter al sistema y a sus componentes a una serie de verificaciones rigurosas para garantizar un nivel de fiabilidad aceptable.
- Involucrar a los usuarios finales y responsables en el proceso de verificación y prueba del sistema.

---

## 3. Alcance y Niveles de Pruebas

El plan contempla los siguientes tipos de pruebas:
- **Pruebas del Sistema y Explotación:** Verificación de navegación, procesamiento de datos, recuperación de información, seguridad por roles y casos de uso.
- **Pruebas de Usabilidad e Interfaz de Usuario:** Evaluación de amigabilidad, legibilidad, estética, menús, enlaces, ventanas emergentes y test directo con el usuario/tutor.
- **Pruebas de Base de Datos y Carga de Datos:** Verificación de la conexión al SGBD, almacenamiento, actualización, eliminación de registros y prevención de datos duplicados.
- **Pruebas de Seguridad:** Control de acceso mediante inicio de sesión y restricciones según perfil de usuario.

**Entorno de pruebas:** aplicación PHP 8.2 (patrón MVC, enrutador `?pagina=X&action=Y`) sobre MySQL/MariaDB (`zona_web_lara`), datos iniciales de `estructura.sql` + `seed_data.sql`. Navegador Google Chrome (escritorio) y, para las pruebas PWA/Offline, Chrome con DevTools (modo offline).

**Credenciales de referencia:** `admin / 1234` (usuario administrador creado por CLI) o `cpaez / 3006`. Datos semilla usados como base: clientes `V-12345678` (Carlos González), `J-56789012` (Comercial XYZ, con asesoría asociada), proveedor `J-12345678-9` (TecnoSuministros C.A.), producto `LAP-001` (stock 15), tarifa `2.50 / 5.00`, tipo de asesoría `Asesoría Penal` (no permitido), estados de solicitud `Pendiente`, `Aprobado`, `En Tránsito`, `Recibido Parcial`, `Recibido Completo`, `Cancelado`.

**Nota sobre el campo Resultado Obtenido:** todos los casos se entregan con valor `Pendiente de ejecución.`; los hallazgos detectados durante la revisión previa del código se registran en el **Anexo A** (al final del documento).

---

## 4. Estructura y Formato del Caso de Prueba

A continuación se define la estructura estandarizada para registrar cada caso de prueba dentro del plan:

| Campo | Descripción |
| :--- | :--- |
| **Caso de Prueba** | Título o descripción concreta de la prueba a realizar. |
| **Caso de Uso** | Nombre del caso de uso asociado (o "Ninguno" si es una prueba técnica/infraestructura). |
| **Datos de Prueba** | Valores o parámetros de entrada específicos a ingresar al sistema. |
| **Propósito** | Descripción del objetivo o finalidad perseguida con el caso de prueba. |
| **Prerrequisitos** | Condiciones previas obligatorias antes de ejecutar la prueba. |
| **Pasos** | Secuencia de acciones y procedimientos para llevar a cabo la prueba. |
| **Resultado Esperado** | Comportamiento, mensaje o respuesta teórica que debe emitir el sistema. |
| **Resultado Obtenido** | Comportamiento o respuesta real obtenida durante la ejecución de la prueba. |

---

## 5. Casos de Prueba

Total: **121 casos** distribuidos en 18 grupos:

| Módulo / Grupo | Rango de Casos |
| :--- | :--- |
| Autenticación y sesión | CP-01 – CP-08 |
| Dashboard | CP-09 – CP-11 |
| Inventario y Categorías | CP-12 – CP-24 |
| Punto de Venta (POS) | CP-25 – CP-34 |
| Clientes | CP-35 – CP-39 |
| Solicitudes a Proveedores (Órdenes de Compra) | CP-40 – CP-44 |
| Gestión de Proveedores | CP-45 – CP-47 |
| Control de Ciber | CP-48 – CP-57 |
| Activos | CP-58 – CP-63 |
| Asesoría Legal | CP-64 – CP-69 |
| Usuarios | CP-70 – CP-76 |
| Roles y Permisos | CP-77 – CP-82 |
| Reportes y Estadísticas | CP-83 – CP-89 |
| PWA / Funcionamiento Offline | CP-90 – CP-94 |
| Usabilidad e Interfaz | CP-95 – CP-99 |
| Base de Datos | CP-100 – CP-107 |
| Seguridad | CP-108 – CP-115 |
| Integración de Módulos | CP-116 – CP-121 |

### 5.1 Autenticación y Sesión (CP-01 – CP-08)


### Caso de Prueba CP-01: Inicio de sesión con credenciales válidas

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Ingresar al sistema con usuario y clave correctos. |
| **Caso de Uso** | Iniciar sesión. |
| **Datos de Prueba** | - Intento A: Usuario `admin`, clave `1234`.<br>- Intento B: Usuario `cpaez`, clave `3006`. |
| **Propósito** | Validar que la autenticación permite el acceso a los usuarios registrados y activos, y que la sesión se establece correctamente. |
| **Prerrequisitos** | Servidor en ejecución, base de datos `zona_web_lara` con datos semilla; navegador en la página `?pagina=login`. |
| **Pasos** | 1. Navegar a `?pagina=login`.<br>2. Ingresar usuario `admin` y clave `1234`.<br>3. Hacer clic en "Iniciar Sesión".<br>4. Repetir los pasos 1 a 3 con `cpaez / 3006`. |
| **Resultado Esperado** | Redirección a `?pagina=dashboard`; se muestra el menú lateral completo (Dashboard, Inventario, Ventas, Clientes, Solicitudes, Cyber, Reportes, Activos, Asesoría, Configuración, Roles y Permisos, Manual de Usuario) y el nombre/rol del usuario en la cabecera. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-02: Inicio de sesión con credenciales incorrectas

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Probar ingresar al sistema con usuario y clave incorrectas. |
| **Caso de Uso** | Iniciar sesión. |
| **Datos de Prueba** | - Intento A: Usuario incorrecto (`adminX`) y clave correcta (`1234`).<br>- Intento B: Usuario correcto (`admin`) y clave incorrecta (`0000`). |
| **Propósito** | Validar que el inicio de sesión controle los accesos no autorizados de forma correcta. |
| **Prerrequisitos** | El usuario seleccionó la opción "Iniciar sesión" en la página principal. |
| **Pasos** | 1. En el campo Usuario ingresar valor incorrecto y en Password clave correcta. Hacer clic en "Iniciar Sesión".<br>2. En el campo Usuario ingresar valor correcto y en Password clave incorrecta. Hacer clic en "Iniciar Sesión". |
| **Resultado Esperado** | Se activa una ventana o mensaje especificando el error cometido: se redirige a `?pagina=login&error=1` y se muestra el panel rojo con el texto **"Credenciales incorrectas. Por favor, intenta nuevamente."** No se abre sesión. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-03: Inicio de sesión con campos vacíos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Enviar el formulario de login sin completar usuario ni contraseña. |
| **Caso de Uso** | Iniciar sesión. |
| **Datos de Prueba** | Usuario vacío (""), clave vacía (""). |
| **Propósito** | Validar la distinción entre error de validación de campos vacíos y error de credenciales. |
| **Prerrequisitos** | Navegador en la página `?pagina=login`. |
| **Pasos** | 1. Dejar ambos campos en blanco.<br>2. Hacer clic en "Iniciar Sesión".<br>3. Repetir dejando solo un campo vacío. |
| **Resultado Esperado** | Redirección a `?pagina=login&error=vacio` y panel rojo con el texto **"Por favor, completa todos los campos para iniciar sesión."** |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-04: Inicio de sesión con nombre de usuario inválido

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Enviar un nombre de usuario con formato no permitido o longitud menor a 3 caracteres. |
| **Caso de Uso** | Iniciar sesión. |
| **Datos de Prueba** | Usuario `ab` (longitud < 3) y usuario con caracteres inválidos (`ad min!`), clave `1234`. |
| **Propósito** | Verificar la validación de formato del nombre de usuario antes de consultar la base de datos. |
| **Prerrequisitos** | Navegador en la página `?pagina=login`. |
| **Pasos** | 1. Ingresar usuario `ab` y clave `1234`. Hacer clic en "Iniciar Sesión".<br>2. Ingresar usuario `ad min!` y clave `1234`. Hacer clic en "Iniciar Sesión". |
| **Resultado Esperado** | En ambos casos redirección a `?pagina=login&error=1` con el mensaje **"Credenciales incorrectas. Por favor, intenta nuevamente."** (el sistema no revela el detalle de la validación). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-05: Acceso sin sesión a una página protegida

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Solicitar directamente una URL de página privada sin haber iniciado sesión. |
| **Caso de Uso** | Control de acceso (Ninguno). |
| **Datos de Prueba** | URLs `?pagina=dashboard`, `?pagina=inventario`, `?pagina=ventas`, `?pagina=usuarios`, `?pagina=reportes`. |
| **Propósito** | Verificar que el enrutador bloquea el acceso a páginas privadas cuando no existe sesión activa. |
| **Prerrequisitos** | Navegador sin sesión (ventana anónima o sesión cerrada). |
| **Pasos** | 1. En ventana de incógnito escribir `?pagina=dashboard` y pulsar Enter.<br>2. Repetir con las demás URLs listadas.<br>3. Intentar una acción AJAX sin sesión, p. ej. `?pagina=inventario&action=listar`. |
| **Resultado Esperado** | Todas las páginas privadas redirigen a `?pagina=login`; la petición AJAX sin sesión no devuelve datos privados (se redirige al login). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-06: Cierre de sesión desde el menú lateral

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Hacer clic en "Cerrar Sesión" y verificar que la sesión realmente termina. |
| **Caso de Uso** | Cerrar sesión. |
| **Datos de Prueba** | Sesión iniciada como `admin`. |
| **Propósito** | Validar que el cierre de sesión destruye la sesión del servidor y no permite continuar operando. |
| **Prerrequisitos** | Sesión iniciada; observar el enlace "Cerrar Sesión" del menú lateral. |
| **Pasos** | 1. Con sesión iniciada, hacer clic en "Cerrar Sesión".<br>2. Verificar la URL resultante.<br>3. Sin cerrar la pestaña, escribir `?pagina=dashboard` y pulsar Enter (o usar el botón Atrás).<br>4. Como control, probar la ruta oficial de cierre `?pagina=login&logout=1`. |
| **Resultado Esperado** | La sesión se destruye y se redirige al login; el paso 3 debe volver a mostrar el login (acceso denegado). *Observación de revisión previa: el enlace del menú no incluye el parámetro `logout`, por lo que solo pinta la pantalla de login con la sesión aún viva (ver Anexo A, H-01).* |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-07: Envío del formulario de login por método GET

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Acceder a `?pagina=login_validate` mediante GET (sin cuerpo POST). |
| **Caso de Uso** | Iniciar sesión (prueba técnica). |
| **Datos de Prueba** | URL `?pagina=login_validate?username=admin&password=1234`. |
| **Propósito** | Verificar que el endpoint de autenticación solo procesa peticiones POST. |
| **Prerrequisitos** | Sesión cerrada. |
| **Pasos** | 1. Escribir la URL anterior en el navegador.<br>2. Observar la respuesta. |
| **Resultado Esperado** | El controlador redirige a `?pagina=login` sin autenticar; no se crea sesión. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-08: Inicio de sesión con usuario desactivado

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar iniciar sesión con un usuario existente pero con estatus desactivado. |
| **Caso de Uso** | Iniciar sesión. |
| **Datos de Prueba** | Usuario semilla (p. ej. `pgarcia`) desactivado previamente desde Configuración de Usuarios; clave desconocida → restablecerla primero desde el módulo de Usuarios (CP-75) o crear un usuario de prueba y desactivarlo. |
| **Propósito** | Confirmar que el filtro de estatus (`estatus IN ('1','activo')`) impide el acceso de usuarios inactivos. |
| **Prerrequisitos** | Sesión de administrador activa para desactivar el usuario de prueba; anotar sus credenciales. |
| **Pasos** | 1. Crear usuario `prueba_off` con clave `1234` (o usar CP-75 para conocerla) y verificar que sí puede iniciar sesión.<br>2. Desde la sesión de administrador, desactivarlo (botón "Desactivar" con confirmación).<br>3. Cerrar sesión y volver a intentar entrar con `prueba_off / 1234`. |
| **Resultado Esperado** | El paso 1 funciona; el paso 3 redirige a `?pagina=login&error=1` con **"Credenciales incorrectas. Por favor, intenta nuevamente."** sin distinguir que el usuario está inactivo. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.2 Dashboard (CP-09 – CP-11)


### Caso de Prueba CP-09: Carga de los indicadores (KPIs) del Dashboard

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar la visualización de las tarjetas de indicadores del panel principal. |
| **Caso de Uso** | Consultar dashboard. |
| **Datos de Prueba** | Datos semilla (ventas del día, productos con stock crítico, sesiones ciber, solicitudes pendientes). |
| **Propósito** | Validar que las tarjetas "Ventas Hoy", "Stock Crítico", "Sesiones Cyber" y "Solicitudes Pend." se pintan con valores coherentes con la base de datos. |
| **Prerrequisitos** | Sesión iniciada; conocer el total de ventas de hoy en `orden_de_venta` (consulta SQL previa). |
| **Pasos** | 1. Ir a `?pagina=dashboard`.<br>2. Leer el valor de las 4 tarjetas de métricas.<br>3. Comparar cada valor con la consulta SQL correspondiente (ventas de `CURDATE()`, productos con `stock <= stock_minimo`, sesiones ciber activas, órdenes con estado `Pendiente`).<br>4. Ejecutar `?pagina=dashboard&action=kpis` en el navegador. |
| **Resultado Esperado** | Las tarjetas muestran números no negativos; la respuesta JSON de `action=kpis` tiene `success: true` y los mismos valores (o valores equivalentes) a los renderizados. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-10: Tablas "Ventas del día" y "Stock crítico"

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar el contenido de las dos tablas del dashboard. |
| **Caso de Uso** | Consultar dashboard. |
| **Datos de Prueba** | Semilla con 62 productos (p. ej. `LAP-001` stock 15 / mínimo 3) y órdenes de venta existentes. |
| **Propósito** | Comprobar que las tablas `#tabla-ventas-dia` y `#tabla-stock-critico` listan los registros esperados. |
| **Prerrequisitos** | Sesión iniciada; existen ventas registradas (o registrar una en CP-30). |
| **Pasos** | 1. Ir a `?pagina=dashboard`.<br>2. Revisar la tabla de ventas del día (orden, cliente, monto, usuario).<br>3. Revisar la tabla de stock crítico y confirmar que solo aparecen productos con `stock <= stock_minimo`.<br>4. Vaciar las tablas (sin ventas / sin stock crítico) y recargar. |
| **Resultado Esperado** | Las tablas muestran los datos reales; si no hay registros se muestra el estado vacío sin errores en consola. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-11: Actualización del dashboard tras registrar una venta

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar que los indicadores reflejan la venta recién registrada. |
| **Caso de Uso** | Consultar dashboard / Registrar venta. |
| **Datos de Prueba** | Venta de 2 unidades de `LAP-001` (registrada en CP-30). |
| **Propósito** | Validar la integración POS → Dashboard. |
| **Prerrequisitos** | Anotar el valor actual de "Ventas Hoy" y el stock de `LAP-001`. |
| **Pasos** | 1. Anotar los valores del dashboard.<br>2. Ir a `?pagina=ventas` y registrar la venta (CP-30).<br>3. Volver a `?pagina=dashboard` y recargar (F5).<br>4. Comparar valores y stock crítico. |
| **Resultado Esperado** | "Ventas Hoy" y la tabla de ventas del día incorporan la nueva venta; el stock de `LAP-001` disminuye en 2 unidades y, si cae al mínimo, aparece en la tabla de stock crítico. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.3 Inventario y Categorías (CP-12 – CP-24)


### Caso de Prueba CP-12: Crear un producto nuevo

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Registrar un producto con datos válidos desde el módulo de inventario. |
| **Caso de Uso** | Gestionar productos (adicionar). |
| **Datos de Prueba** | Código `TEST-001`, nombre `Teclado de prueba`, descripción `Producto para pruebas`, categoría `Teclados`, stock `5`, stock mínimo `2`, costo `10.00`, precio venta `20.00`. |
| **Propósito** | Verificar el alta completa de productos y su persistencia en la tabla `productos`. |
| **Prerrequisitos** | Sesión iniciada; acceso al módulo Inventario; no existir el código `TEST-001`. |
| **Pasos** | 1. Ir a `?pagina=inventario`.<br>2. Hacer clic en el botón de nuevo producto y completar todos los campos con los datos de prueba.<br>3. Guardar.<br>4. Buscar `TEST-001` en el listado y verificar sus valores. |
| **Resultado Esperado** | Mensaje de éxito (toast verde), el producto aparece en la tabla con los valores ingresados y los KPIs de inventario se actualizan. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-13: Crear producto con código duplicado

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar crear un producto con un código ya existente. |
| **Caso de Uso** | Gestionar productos (adicionar). |
| **Datos de Prueba** | Código `LAP-001` (ya existe en la semilla), resto de campos válidos. |
| **Propósito** | Validar la restricción UNIQUE sobre `productos.codigo`. |
| **Prerrequisitos** | Existir el producto `LAP-001`. |
| **Pasos** | 1. En Inventario, abrir el formulario de nuevo producto.<br>2. Ingresar código `LAP-001` con datos válidos.<br>3. Guardar. |
| **Resultado Esperado** | No se crea el registro; el sistema muestra el error **"Ya existe otro producto con ese código"** (o mensaje equivalente de código duplicado) y el listado mantiene un solo `LAP-001`. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-14: Crear producto con precio de venta menor al costo

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar guardar un producto cuyo precio de venta es inferior al costo de compra. |
| **Caso de Uso** | Gestionar productos (adicionar). |
| **Datos de Prueba** | Código `TEST-002`, costo `50.00`, precio venta `40.00`, resto válido. |
| **Propósito** | Validar la regla de negocio de margen de ganancia. |
| **Prerrequisitos** | Sesión iniciada en Inventario. |
| **Pasos** | 1. Abrir el formulario de nuevo producto.<br>2. Ingresar costo `50.00` y precio venta `40.00`.<br>3. Guardar. |
| **Resultado Esperado** | El sistema rechaza el guardado y muestra **"El precio de venta no puede ser menor al costo de compra"**; no se crea el registro. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-15: Crear producto con valores numéricos inválidos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar guardar stock, stock mínimo, costo o precio con valores negativos o no numéricos. |
| **Caso de Uso** | Gestionar productos (adicionar). |
| **Datos de Prueba** | Código `TEST-003` con intentos: stock `-1`; stock mínimo `-5`; costo `-10`; precio venta `abc`; cantidad con decimales `2.5`. |
| **Propósito** | Verificar la validación numérica de los campos del formulario. |
| **Prerrequisitos** | Sesión iniciada en Inventario. |
| **Pasos** | 1. Abrir el formulario de nuevo producto e ingresar cada combinación inválida.<br>2. Intentar guardar en cada caso.<br>3. Revisar si el navegador bloquea el envío (HTML5) y/o si el servidor responde error. |
| **Resultado Esperado** | Ningún intento crea el producto; se señala el campo inválido (mensaje de validación del servidor o marca roja en el formulario). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-16: Crear producto con campos obligatorios vacíos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Enviar el formulario de producto incompleto. |
| **Caso de Uso** | Gestionar productos (adicionar). |
| **Datos de Prueba** | Formulario con solo el nombre (resto vacío); y variante con todo vacío. |
| **Propósito** | Validar la respuesta de `fieldErrors` del servidor para campos obligatorios. |
| **Prerrequisitos** | Sesión iniciada en Inventario. |
| **Pasos** | 1. Abrir el formulario y dejar los campos vacíos.<br>2. Intentar guardar.<br>3. Repetir con solo el nombre lleno. |
| **Resultado Esperado** | El servidor responde `success: false` con errores por campo (`fieldErrors`); el formulario marca los campos obligatorios vacíos y no se inserta ningún registro. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-17: Editar un producto existente

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Modificar datos de un producto desde el botón Editar. |
| **Caso de Uso** | Gestionar productos (modificar). |
| **Datos de Prueba** | Producto `TEST-001` → nombre `Teclado de prueba Editado`, precio venta `25.00`, categoría `Teclados`. |
| **Propósito** | Verificar la precarga del formulario y la actualización real en BD. |
| **Prerrequisitos** | Existir `TEST-001` (CP-12). |
| **Pasos** | 1. En Inventario, buscar `TEST-001` y pulsar Editar.<br>2. Verificar que el formulario aparece precargado con los valores actuales.<br>3. Cambiar nombre y precio venta; guardar.<br>4. Recargar la página y volver a buscar el producto. |
| **Resultado Esperado** | Toast de éxito; el listado muestra los nuevos valores persistidos tras la recarga. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-18: Editar producto usando un código perteneciente a otro producto

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Cambiar el código de un producto por el código de otro producto distinto. |
| **Caso de Uso** | Gestionar productos (modificar). |
| **Datos de Prueba** | Editar `TEST-001` y colocar código `LAP-001`. |
| **Propósito** | Validar la unicidad de `productos.codigo` en la actualización (la semilla de validación es "el código es de **otro** producto"). |
| **Prerrequisitos** | Existir `TEST-001` y `LAP-001`. |
| **Pasos** | 1. Editar `TEST-001`.<br>2. Cambiar el código a `LAP-001`.<br>3. Guardar; luego editar `LAP-001` conservando su propio código y guardar. |
| **Resultado Esperado** | El intento 2 se rechaza con mensaje de código duplicado (p. ej. **"Ya existe otro producto con ese código"**); el intento 3 sí se permite (editar sin cambiar el código propio no falla). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-19: Modificación de stock desde la edición del producto

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Ajustar el stock de un producto editándolo (vía Existente/Actual), dado que el sistema no expone acciones separadas de entrada/salida de almacén. |
| **Caso de Uso** | Gestionar productos (modificar stock). |
| **Datos de Prueba** | `TEST-001` stock `5` → `12`; luego `12` → `0`. |
| **Propósito** | Comprobar que los movimientos de inventario se realizan únicamente a través del formulario de edición y que el listado y los KPIs lo reflejan. |
| **Prerrequisitos** | Existir `TEST-001` con stock `5`. |
| **Pasos** | 1. Editar `TEST-001`, cambiar stock a `12`, guardar.<br>2. Verificar el nuevo stock en la tabla y en el detalle del producto.<br>3. Editar nuevamente y poner stock `0`.<br>4. Verificar que el producto se marca como agotado/crítico según su stock mínimo (`2`).<br>5. Confirmar en la tabla de stock crítico del dashboard. |
| **Resultado Esperado** | El stock se actualiza en cada guardado; con stock `0` (≤ mínimo `2`) el producto aparece en stock crítico. No existen botones ni reportes de "entrada/salida de stock" en este módulo. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-20: Eliminar un producto

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Eliminar un producto desde el listado de inventario. |
| **Caso de Uso** | Gestionar productos (eliminar). |
| **Datos de Prueba** | Intento A: producto `TEST-002`/`TEST-003` sin ventas asociadas. Intento B: producto `LAP-001` con líneas de venta asociadas. |
| **Propósito** | Verificar la eliminación y el comportamiento de integridad referencial con `lineas_venta` / `lineas_abastecimiento`. |
| **Prerrequisitos** | Sesión iniciada; existir al menos una venta con `LAP-001` (CP-30). |
| **Pasos** | 1. Eliminar `TEST-002` y confirmar en el diálogo de confirmación.<br>2. Verificar que desaparece del listado.<br>3. Intentar eliminar `LAP-001`.<br>4. Revisar en BD (`lineas_venta`) si las líneas históricas de la venta siguen existiendo y si los totales de la orden y los reportes de ventas permanecen consistentes. |
| **Resultado Esperado** | Intento A: se elimina con confirmación. Intento B: registrar el comportamiento observado; si las líneas de venta desaparecen o los totales cambian, reportarlo como hallazgo de integridad referencial (la tabla `lineas_venta` define `ON DELETE CASCADE` sobre `productos`). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-21: Eliminar una categoría con productos asociados

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar eliminar una categoría que tiene productos asignados. |
| **Caso de Uso** | Gestionar categorías (eliminar). |
| **Datos de Prueba** | Categoría `Teclados` (tiene `TEC-001`, `TEC-002`); y categoría nueva vacía `Categoria libre`. |
| **Propósito** | Validar la restricción `ON DELETE RESTRICT` de `productos.fk_categoria`. |
| **Prerrequisitos** | Sesión iniciada; abrir "Gestionar Categorías". |
| **Pasos** | 1. Intentar eliminar la categoría `Teclados`.<br>2. Crear categoría `Categoria libre` y eliminarla. |
| **Resultado Esperado** | Intento A: se muestra error indicando que la categoría tiene productos asociados y no se elimina. Intento B: se elimina sin problemas. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-22: CRUD completo de categorías

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Crear, editar y eliminar categorías desde el modal "Gestionar Categorías". |
| **Caso de Uso** | Gestionar categorías. |
| **Datos de Prueba** | Categoría `Categoria CP-22` → editar a `Categoria CP-22 B`. |
| **Propósito** | Verificar el submódulo de categorías (catálogo maestro del inventario). |
| **Prerrequisitos** | Sesión iniciada en Inventario. |
| **Pasos** | 1. Abrir "Gestionar Categorías".<br>2. Crear `Categoria CP-22`.<br>3. Verificar que aparece en el listado del modal y en el select del formulario de productos.<br>4. Editar su nombre.<br>5. Eliminarla (ver CP-21 si tiene productos). |
| **Resultado Esperado** | Cada operación muestra confirmación/toast y los cambios persisten tras recargar; la categoría creada es seleccionable al crear productos. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-23: Búsqueda y filtros de inventario

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Filtrar el listado de productos por texto, categoría y estado de stock. |
| **Caso de Uso** | Consultar inventario. |
| **Datos de Prueba** | Búsqueda `LAP` (2 resultados), `zzz` (0 resultados), filtro categoría `Laptops`, filtro de stock crítico. |
| **Propósito** | Validar la recuperación de información y el comportamiento de la tabla vacía. |
| **Prerrequisitos** | Sesión iniciada; semilla de 62 productos. |
| **Pasos** | 1. Escribir `LAP` en el buscador y observar el listado.<br>2. Escribir `zzz`.<br>3. Aplicar filtro de categoría `Laptops`.<br>4. Aplicar el filtro de stock crítico y comparar con el dashboard.<br>5. Limpiar filtros. |
| **Resultado Esperado** | `LAP` muestra `LAP-001`/`LAP-002`; `zzz` muestra estado vacío sin error en consola; los filtros se acumulan/clean correctamente y al limpiar se restaura la lista completa. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-24: KPIs y paginación del módulo de inventario

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar los indicadores del encabezado del módulo y la navegación del listado. |
| **Caso de Uso** | Consultar inventario. |
| **Datos de Prueba** | Semilla de productos y datos modificados por CP-12 a CP-20. |
| **Propósito** | Comprobar que los KPIs del módulo (`action=kpis`) coinciden con las acciones del usuario. |
| **Prerrequisitos** | Sesión iniciada; anotar los KPIs antes de las pruebas CP-12 a CP-20. |
| **Pasos** | 1. Abrir Inventario y leer los KPIs.<br>2. Ejecutar `?pagina=inventario&action=kpis`.<br>3. Comparar antes/después de crear y eliminar productos. |
| **Resultado Esperado** | Los KPIs coinciden con el JSON del servidor y reflejan las altas/bajas realizadas. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.4 Punto de Venta (POS) (CP-25 – CP-34)


### Caso de Prueba CP-25: Carga del módulo POS y listado de productos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar la apertura del módulo de Punto de Venta. |
| **Caso de Uso** | Registrar venta (consulta previa). |
| **Datos de Prueba** | Semilla de productos (p. ej. `LAP-001`, `TEC-001`, `MOU-001`). |
| **Propósito** | Comprobar que la página `?pagina=ventas` carga el catálogo, el buscador y los controles del carrito sin errores. |
| **Prerrequisitos** | Sesión iniciada; permisos de navegación. |
| **Pasos** | 1. Ir a `?pagina=ventas`.<br>2. Verificar la presencia del buscador de productos, el botón del carrito (`#openCartBtn`), el selector de cliente (`#posClienteSelect`) y los campos de ciudadano/cédula.<br>3. Revisar la consola del navegador (F12) por errores. |
| **Resultado Esperado** | El módulo carga con el catálogo de productos visible, contador del carrito en `0` y sin errores JavaScript en consola. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-26: Agregar un producto al carrito

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Seleccionar un producto para agregarlo al carrito de compra. |
| **Caso de Uso** | Registrar venta. |
| **Datos de Prueba** | Producto `TEC-001` (precio 45.00, stock 30), cantidad 1. |
| **Propósito** | Validar la construcción del carrito en cliente con su total. |
| **Prerrequisitos** | Sesión iniciada en `?pagina=ventas`. |
| **Pasos** | 1. Hacer clic en la tarjeta del producto `TEC-001`.<br>2. Observar el contador del carrito y el resumen/total.<br>3. Repetir con otro producto distinto (`MOU-001`). |
| **Resultado Esperado** | El producto se agrega al carrito, el contador incrementa, el total corresponde a la suma de precios y no hay errores en consola. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-27: Agregar el mismo producto dos veces

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Seleccionar repetidamente un producto ya presente en el carrito. |
| **Caso de Uso** | Registrar venta. |
| **Datos de Prueba** | `TEC-001` seleccionado en dos ocasiones. |
| **Propósito** | Verificar la regla de duplicidad del carrito (acumula cantidad) y su aviso. |
| **Prerrequisitos** | `TEC-001` ya agregado una vez (CP-26). |
| **Pasos** | 1. Hacer clic de nuevo en `TEC-001`.<br>2. Abrir el modal del carrito (`#openCartBtn`) y revisar la línea del producto. |
| **Resultado Esperado** | El sistema muestra el aviso **"El producto se repite en el carrito"** (acumulando la cantidad en la misma línea en lugar de duplicar el renglón), y el total se recalcula correctamente. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-28: Procesar la venta con el carrito vacío

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar procesar una venta sin productos en el carrito. |
| **Caso de Uso** | Registrar venta. |
| **Datos de Prueba** | Carrito vacío. |
| **Propósito** | Validar la validación de servidor del carrito vacío. |
| **Prerrequisitos** | Sesión iniciada, carrito sin ítems. |
| **Pasos** | 1. Abrir el modal del carrito y verificar que está vacío.<br>2. Intentar procesar la venta (`#procesarVenta`).<br>3. Inspeccionar la respuesta de `?pagina=ventas&action=registrar`. |
| **Resultado Esperado** | El servidor responde `success: false` con **"El carrito está vacío"**; no se crea `orden_de_venta` ni `lineas_venta`. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-29: Carrito que excede el máximo de 200 ítems

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar superar el límite de 200 ítems del carrito. |
| **Caso de Uso** | Registrar venta. |
| **Datos de Prueba** | Secuencia automatizada o manual que agregue más de 200 ítems (p. ej. 201 selecciones). |
| **Propósito** | Verificar la regla de negocio del límite de ítems del carrito. |
| **Prerrequisitos** | Sesión iniciada; técnica para agregar ítems repetidamente (console: dispara el clic en `.pos-product` 201 veces). |
| **Pasos** | 1. Agregar productos hasta superar los 200 ítems.<br>2. Observar el aviso y el comportamiento del contador.<br>3. Intentar procesar la venta. |
| **Resultado Esperado** | El sistema informa **"El carrito excede el máximo de 200 ítems permitidos"** y no permite continuar con la venta. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-30: Registrar una venta con cliente existente

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Procesar una venta completa seleccionando un cliente del listado. |
| **Caso de Uso** | Registrar venta. |
| **Datos de Prueba** | Cliente `V-12345678` (Carlos González) vía `#posClienteSelect`; productos `TEC-001` (x2) y `MOU-001` (x1). |
| **Propósito** | Validar el flujo completo de venta: carrito → cliente → confirmación → persistencia → descuento de stock. |
| **Prerrequisitos** | Sesión iniciada; anotar el stock actual de `TEC-001` y `MOU-001`; conocer el total de ventas del día (dashboard). |
| **Pasos** | 1. En `?pagina=ventas`, agregar `TEC-001` (cantidad 2) y `MOU-001` (1).<br>2. Seleccionar al cliente `V-12345678` en el selector de clientes (activar la búsqueda con `EIS.activarBusquedaEnSelect` si aplica).<br>3. Hacer clic en procesar venta y confirmar el diálogo **"¿Procesar venta por $X?"**.<br>4. Revisar el toast de éxito y la limpieza del carrito.<br>5. Recargar Inventario y verificar los stocks; abrir Dashboard y Reportes de ventas. |
| **Resultado Esperado** | La venta se registra con su número de orden; el carrito queda vacío; el stock de `TEC-001` baja 2 y el de `MOU-001` baja 1; la venta aparece en el dashboard y en el reporte de ventas del día. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-31: Registrar una venta con cliente nuevo (ciudadano/cédula)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Completar la venta ingresando manualmente ciudadano y cédula de un cliente no registrado. |
| **Caso de Uso** | Registrar venta. |
| **Datos de Prueba** | Ciudadano `Cliente Prueba POS`, cédula `V-99999999`; producto `AUD-001` (x1). |
| **Propósito** | Verificar la creación/uso de cliente en el flujo POS y los campos `ciudadano`/`cedula` requeridos por `action=registrar`. |
| **Prerrequisitos** | No existir la cédula `V-99999999`; sesión iniciada. |
| **Pasos** | 1. Agregar `AUD-001` al carrito.<br>2. Llenar los campos de ciudadano y cédula nuevos (sin usar el selector).<br>3. Procesar la venta y confirmar.<br>4. Verificar en el módulo de Clientes que la cédula quedó registrada (o en la venta asociada). |
| **Resultado Esperado** | La venta se registra correctamente con el cliente nuevo asociado; la cédula `V-99999999` queda disponible para búsquedas posteriores. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-32: Registrar venta sin datos de cliente obligatorios

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Procesar la venta sin seleccionar cliente ni proporcionar ciudadano/cédula. |
| **Caso de Uso** | Registrar venta. |
| **Datos de Prueba** | Carrito con 1 producto; sin cliente seleccionado, ciudadano y cédula vacíos. |
| **Propósito** | Validar los campos obligatorios del endpoint `registrar` (`ciudadano`, `cedula`, `items`). |
| **Prerrequisitos** | Sesión iniciada; producto en carrito. |
| **Pasos** | 1. Dejar el selector de cliente vacío y sin llenar ciudadano/cédula.<br>2. Intentar procesar la venta.<br>3. Revisar la respuesta JSON y el mensaje mostrado. |
| **Resultado Esperado** | El servidor responde `success: false` con **"Completa todos los campos obligatorios."** (o el error de campo correspondiente); no se crea la orden. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-33: Vaciar el carrito

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Descartar todos los productos del carrito. |
| **Caso de Uso** | Registrar venta (anulación de carrito). |
| **Datos de Prueba** | Carrito con 3 productos distintos. |
| **Propósito** | Verificar la anulación local del carrito con confirmación. |
| **Prerrequisitos** | Carrito con ítems. |
| **Pasos** | 1. Abrir el modal del carrito y hacer clic en `#vaciarCarrito`.<br>2. Cancelar el diálogo de confirmación.<br>3. Repetir y aceptar la confirmación. |
| **Resultado Esperado** | Al cancelar el carrito permanece igual; al aceptar, el carrito queda vacío (contador 0, total $0) sin efectos en la base de datos. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-34: Integridad de datos de la venta en base de datos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar la consistencia de `orden_de_venta` y `lineas_venta` después de varias ventas. |
| **Caso de Uso** | Registrar venta (verificación técnica). |
| **Datos de Prueba** | Dos ventas consecutivas (CP-30 y CP-31) y una venta anulada/no procesada. |
| **Propósito** | Confirmar transaccionalidad: orden + líneas + stock se actualizan de forma atómica. |
| **Prerrequisitos** | Acceso SQL a `zona_web_lara`; ventas CP-30/CP-31 realizadas. |
| **Pasos** | 1. Consultar `orden_de_venta` y `lineas_venta` de las ventas nuevas.<br>2. Verificar que el número de orden es único y que `fk_usuario`/`fk_cliente` son correctos.<br>3. Recalcular la suma de `cantidad * precio` por orden y compararla con el total mostrado en pantalla.<br>4. Intentar una venta sin confirmar (cancelar el diálogo) y verificar que no queda registro huérfano. |
| **Resultado Esperado** | Cada orden tiene sus líneas coincidentes con el total mostrado, sin órdenes huérfanas ni líneas sin orden; el stock descontado corresponde exactamente a las líneas vendidas. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.5 Clientes (CP-35 – CP-39)


### Caso de Prueba CP-35: Crear un cliente nuevo

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Registrar un cliente con datos válidos. |
| **Caso de Uso** | Gestionar clientes (adicionar). |
| **Datos de Prueba** | Cédula `V-11112222`, nombre `Juan`, apellido `Prueba`, dirección `Calle 1, Ciudad`, teléfono `0412-0001111`. |
| **Propósito** | Validar el alta de clientes y su persistencia en `clientes`. |
| **Prerrequisitos** | Sesión iniciada en `?pagina=clientes`; no existir la cédula. |
| **Pasos** | 1. Ir a Clientes y abrir el formulario nuevo (`.btn-nuevo-cliente`).<br>2. Completar todos los campos.<br>3. Guardar y buscar la cédula en el listado. |
| **Resultado Esperado** | Toast de éxito; el cliente aparece en la tabla con los datos ingresados. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-36: Crear cliente con cédula duplicada

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar registrar un cliente con una cédula ya existente. |
| **Caso de Uso** | Gestionar clientes (adicionar). |
| **Datos de Prueba** | Cédula `V-12345678` (ya existe), nombre `Otro`, apellido `Cliente`. |
| **Propósito** | Validar la restricción UNIQUE de `clientes.cedula`. |
| **Prerrequisitos** | Existir `V-12345678` en la semilla. |
| **Pasos** | 1. Abrir el formulario de nuevo cliente.<br>2. Ingresar la cédula existente con otro nombre.<br>3. Guardar. |
| **Resultado Esperado** | El sistema rechaza el guardado con **"Ya existe un cliente con esa cédula"**; no se crea un segundo registro. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-37: Crear cliente con cédula en formato inválido

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar guardar una cédula que no cumple el formato esperado. |
| **Caso de Uso** | Gestionar clientes (adicionar). |
| **Datos de Prueba** | Cédula `1234` (sin prefijo V/J/E), cédula `V-ABCDEF` y cédula vacía. |
| **Propósito** | Validar el formato de cédula nacional (prefijo + dígitos) en cliente, POS y asesoría. |
| **Prerrequisitos** | Sesión iniciada en Clientes. |
| **Pasos** | 1. Intentar crear un cliente con cada una de las cédulas inválidas.<br>2. Observar el mensaje devuelto. |
| **Resultado Esperado** | Ninguna se guarda; el sistema responde **"La cédula no tiene un formato válido"** (o el mensaje de campo obligatorio si está vacía). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-38: Editar y buscar clientes

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Modificar un cliente existente y localizarlo por búsqueda. |
| **Caso de Uso** | Gestionar clientes (modificar y consultar). |
| **Datos de Prueba** | Cliente `V-11112222` → teléfono `0414-9999999`; búsquedas `Juan`, `V-11112222`, `zzz`. |
| **Propósito** | Verificar precarga del formulario de edición, actualización y recuperación de información. |
| **Prerrequisitos** | Existir `V-11112222` (CP-35). |
| **Pasos** | 1. Buscar el cliente y pulsar Editar.<br>2. Verificar precarga, cambiar el teléfono y guardar.<br>3. Recargar y confirmar el cambio persistido.<br>4. Buscar por nombre, por cédula y con un texto sin coincidencias. |
| **Resultado Esperado** | La edición persiste tras recarga; las búsquedas filtran correctamente y la búsqueda sin coincidencias muestra estado vacío sin errores. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-39: Eliminar clientes (con y sin registros asociados)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar eliminar un cliente sin historial y otro con asesorías/ventas asociadas. |
| **Caso de Uso** | Gestionar clientes (eliminar). |
| **Datos de Prueba** | Intento A: cliente creado en CP-35 sin ventas ni asesorías. Intento B: cliente `V-12345678` (id 1, con `cliente_asesoria` y posibles ventas). |
| **Propósito** | Verificar la integridad referencial (`ON DELETE RESTRICT`) y el mensaje de la aplicación. |
| **Prerrequisitos** | Existir el cliente de CP-35 y el cliente semilla con asesoría. |
| **Pasos** | 1. Eliminar el cliente de CP-35 y confirmar.<br>2. Intentar eliminar `V-12345678` (localizarlo por cédula).<br>3. Revisar el mensaje mostrado y verificar en BD que el registro sigue existente. |
| **Resultado Esperado** | Intento A: se elimina con confirmación. Intento B: se muestra **"No se puede eliminar: el cliente tiene registros asociados."** y el cliente permanece en la tabla. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.6 Solicitudes a Proveedores — Órdenes de Compra (CP-40 – CP-44)


### Caso de Prueba CP-40: Crear una orden de abastecimiento

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Registrar una nueva solicitud/orden de compra a proveedor con sus líneas de producto. |
| **Caso de Uso** | Gestionar solicitudes a proveedores (adicionar). |
| **Datos de Prueba** | Proveedor `J-12345678-9` (TecnoSuministros C.A.), estado `Pendiente`, líneas: `LAP-001` cantidad `3` precio `450.00`; `TEC-001` cantidad `10` precio `25.00`. |
| **Propósito** | Validar la creación de la orden con número automático (`siguienteNumero`) y sus líneas. |
| **Prerrequisitos** | Sesión iniciada en `?pagina=proveedores`; existir el proveedor y los productos. |
| **Pasos** | 1. Ir a Solicitudes y crear una nueva orden.<br>2. Seleccionar proveedor y dejar estado `Pendiente`.<br>3. Agregar las líneas de producto con cantidades y precios.<br>4. Guardar.<br>5. Verificar la orden en el listado y su detalle (`lineas`). |
| **Resultado Esperado** | La orden se crea con número consecutivo y estado `Pendiente`; el detalle muestra ambas líneas con los totales correctos; los KPIs de solicitudes se actualizan. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-41: Modificar líneas de una orden (agregar y eliminar)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Agregar y eliminar líneas en una orden existente. |
| **Caso de Uso** | Gestionar solicitudes (modificar). |
| **Datos de Prueba** | Orden creada en CP-40: agregar línea `MOU-001` cantidad `5` precio `20.00` y eliminar la línea de `TEC-001`. |
| **Propósito** | Verificar las acciones `agregarLinea` y `eliminarLinea`. |
| **Prerrequisitos** | Existir la orden de CP-40. |
| **Pasos** | 1. Abrir la orden y agregar la línea nueva.<br>2. Verificar el recálculo del total.<br>3. Eliminar la línea de `TEC-001` con su confirmación.<br>4. Recargar y revisar el detalle persistido. |
| **Resultado Esperado** | Las líneas se actualizan y el total se recalcula correctamente; los cambios persisten tras recargar. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-42: Cambiar el estado de una orden de compra

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Actualizar el estado de seguimiento de una orden. |
| **Caso de Uso** | Gestionar solicitudes (modificar estado). |
| **Datos de Prueba** | Orden de CP-40: `Pendiente` → `Aprobado` → `En Tránsito` → `Recibido Completo` → `Cancelado`. |
| **Propósito** | Validar la transición entre los estados semilla de `status_seguimiento` y su visualización. |
| **Prerrequisitos** | Existir la orden de CP-40. |
| **Pasos** | 1. Cambiar el estado sucesivamente por cada valor de la lista.<br>2. Verificar el badge/etiqueta en el listado tras cada cambio.<br>3. Intentar establecer un estado inexistente (manipulando la petición `action=actualizar`). |
| **Resultado Esperado** | Los cambios de estado válidos persisten y se muestran en el listado; un estado inexistente es rechazado con error de validación. *Nota: revisar que no exista una máquina de estados (cualquier transición es aceptada) y reportarlo como hallazgo (Anexo A, H-06).* |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-43: Eliminar una orden de compra

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Eliminar una solicitud desde el listado. |
| **Caso de Uso** | Gestionar solicitudes (eliminar). |
| **Datos de Prueba** | Orden creada en CP-40 (o de prueba). |
| **Propósito** | Verificar la eliminación en cascada de `lineas_abastecimiento`. |
| **Prerrequisitos** | Existir la orden de prueba. |
| **Pasos** | 1. Eliminar la orden desde el listado y confirmar.<br>2. Verificar que desaparece del listado y de los KPIs.<br>3. Consultar en BD `lineas_abastecimiento` de la orden eliminada. |
| **Resultado Esperado** | La orden se elimina junto con sus líneas (cascada); no quedan líneas huérfanas. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-44: Listado y KPIs de solicitudes

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar los indicadores y el listado de órdenes. |
| **Caso de Uso** | Consultar solicitudes. |
| **Datos de Prueba** | Órdenes semilla (`Pendiente`, `Recibido Completo`, etc.) más las creadas en pruebas. |
| **Propósito** | Comprobar la agregación `contarPorEstado` y el render del listado. |
| **Prerrequisitos** | Sesión iniciada en Solicitudes. |
| **Pasos** | 1. Leer los KPIs (pendientes/recibidas).<br>2. Ejecutar `?pagina=proveedores&action=kpis` y `action=listar`.<br>3. Comparar con el conteo SQL por estado. |
| **Resultado Esperado** | Los KPIs coinciden con la BD; `action=listar` devuelve `success: true` con las órdenes y sus estados. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.7 Gestión de Proveedores (CP-45 – CP-47)


### Caso de Prueba CP-45: Crear un proveedor

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Registrar un proveedor nuevo con RIF válido. |
| **Caso de Uso** | Gestionar proveedores (adicionar). |
| **Datos de Prueba** | RIF `J-99998888-1`, nombre `Proveedor CP-45`, email `cp45@prov.com`, teléfono `0212-9998888`. |
| **Propósito** | Validar el alta de proveedores y la restricción UNIQUE de `proveedores.rif`. |
| **Prerrequisitos** | Sesión iniciada en `?pagina=proveedores-gestion`; no existir el RIF. |
| **Pasos** | 1. Ir a Gestión de Proveedores y abrir el formulario nuevo.<br>2. Completar los datos y guardar.<br>3. Intentar crear un segundo proveedor con el mismo RIF `J-99998888-1`. |
| **Resultado Esperado** | El primer guardado muestra éxito y el proveedor aparece en el listado; el duplicado es rechazado con el mensaje de validación de RIF existente. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-46: Editar y buscar proveedores

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Modificar un proveedor y localizarlo por búsqueda. |
| **Caso de Uso** | Gestionar proveedores (modificar y consultar). |
| **Datos de Prueba** | Proveedor `J-99998888-1` → nombre `Proveedor CP-45 Editado`; búsqueda `Tecno`, `J-12345678-9`, `zzz`. |
| **Propósito** | Verificar edición, precarga y filtro del listado. |
| **Prerrequisitos** | Existir el proveedor de CP-45. |
| **Pasos** | 1. Editar el proveedor, cambiar el nombre y guardar.<br>2. Recargar y confirmar el cambio.<br>3. Buscar por nombre, por RIF y con texto sin coincidencias. |
| **Resultado Esperado** | La edición persiste; las búsquedas filtran correctamente y la búsqueda vacía muestra estado sin resultados. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-47: Eliminar proveedores (con y sin órdenes)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar eliminar un proveedor sin órdenes y uno con órdenes asociadas. |
| **Caso de Uso** | Gestionar proveedores (eliminar). |
| **Datos de Prueba** | Intento A: proveedor de CP-45 sin órdenes. Intento B: `J-12345678-9` (TecnoSuministros, con órdenes semilla). |
| **Propósito** | Validar `ON DELETE RESTRICT` de `orden_abastecimiento.fk_proveedor`. |
| **Prerrequisitos** | Existir la orden de CP-40 asociada a TecnoSuministros (o una orden semilla). |
| **Pasos** | 1. Eliminar el proveedor de CP-45 y confirmar.<br>2. Intentar eliminar `J-12345678-9`.<br>3. Verificar en BD que el proveedor con órdenes permanece. |
| **Resultado Esperado** | Intento A: se elimina. Intento B: no se elimina y el sistema informa que tiene registros/órdenes asociadas. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.8 Control de Ciber (CP-48 – CP-57)


### Caso de Prueba CP-48: Iniciar una sesión de ciber en estación disponible

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Iniciar sesión de uso de PC con cliente, tarifa y tiempo válidos. |
| **Caso de Uso** | Iniciar sesión de ciber. |
| **Datos de Prueba** | Cliente `V-23456789` (o nuevo con cédula válida), tarifa `2.50 / 5.00` (primera de la lista), tiempo `01:30:00`, estación disponible (p. ej. PC 1). |
| **Propósito** | Validar el alta de `sesion_ciber` y el cambio de estado de la estación a ocupada. |
| **Prerrequisitos** | Sesión iniciada en `?pagina=ciberControl`; existir al menos una estación disponible (chip "Disponibles"). |
| **Pasos** | 1. Ir a Control de Ciber y hacer clic en una estación disponible.<br>2. Completar cliente, tarifa y tiempo `01:30:00`.<br>3. Confirmar el inicio.<br>4. Observar el tablero (chips `#hdrDisponibles` / `#hdrOcupadas`). |
| **Resultado Esperado** | La estación pasa a "Ocupada", los chips de cabecera se actualizan y la sesión aparece en el historial/estadísticas. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-49: Iniciar sesión en una estación ya ocupada

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar iniciar una sesión en una PC que ya tiene sesión activa. |
| **Caso de Uso** | Iniciar sesión de ciber. |
| **Datos de Prueba** | Estación ocupada (creada en CP-48), cliente `V-34567890`, tarifa y tiempo válidos. |
| **Propósito** | Verificar el control de concurrencia de estaciones. |
| **Prerrequisitos** | Existir una estación ocupada. |
| **Pasos** | 1. Clic en la estación ocupada (o forzar `action=iniciar` con esa estación).<br>2. Intentar iniciar una nueva sesión. |
| **Resultado Esperado** | El sistema rechaza con **"La estación ya está ocupada"** y no crea una segunda sesión. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-50: Iniciar sesión con tiempo de uso inválido

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Enviar tiempo con formato incorrecto o fuera de rango. |
| **Caso de Uso** | Iniciar sesión de ciber. |
| **Datos de Prueba** | Intento A: tiempo `90:00:00` (horas inválidas) o `1h30`. Intento B: tiempo `01:75:00` y `01:30:99` (minutos/segundos > 59). |
| **Propósito** | Validar la máscara `HH:MM:SS` y el rango de minutos/segundos. |
| **Prerrequisitos** | Sesión iniciada en Cyber con estación disponible. |
| **Pasos** | 1. Intentar iniciar con el tiempo del intento A.<br>2. Intentar iniciar con los tiempos del intento B.<br>3. Inspeccionar los mensajes devueltos. |
| **Resultado Esperado** | Intento A: **"El tiempo de uso debe tener formato HH:MM:SS"**. Intento B: **"El tiempo de uso debe tener minutos y segundos entre 00 y 59"**. No se crea ninguna sesión. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-51: Iniciar sesión con tarifa inexistente

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Enviar un `fk_tarifa` que no existe en la tabla `tarifas`. |
| **Caso de Uso** | Iniciar sesión de ciber (prueba técnica). |
| **Datos de Prueba** | `fk_tarifa = 9999` con cliente y tiempo válidos. |
| **Propósito** | Verificar la validación de la tarifa seleccionada. |
| **Prerrequisitos** | Sesión iniciada; técnica para manipular la petición (DevTools o `action=iniciar` manual). |
| **Pasos** | 1. Abrir la petición de inicio de sesión en DevTools.<br>2. Modificar el identificador de tarifa a `9999`.<br>3. Enviar la petición. |
| **Resultado Esperado** | Respuesta `success: false` con **"La tarifa seleccionada no existe"**; no se crea la sesión. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-52: Finalizar una sesión de ciber

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Finalizar la sesión iniciada en CP-48 y verificar cálculo de tiempo/costo. |
| **Caso de Uso** | Finalizar sesión de ciber. |
| **Datos de Prueba** | Sesión activa de CP-48 (tarifa 2.50/5.00, tiempo planificado `01:30:00`). |
| **Propósito** | Validar que la finalización marca la sesión, libera la estación y calcula tiempo y costo reales. |
| **Prerrequisitos** | Existir sesión activa; anotar hora actual de la BD (`NOW()` de la sesión iniciada). |
| **Pasos** | 1. Hacer clic en la estación ocupada y confirmar la finalización.<br>2. Verificar que la estación vuelve a "Disponible".<br>3. Revisar la sesión en `sesion_ciber` (campo `finalizada` y `tiempo_uso`).<br>4. Consultar el historial y las estadísticas del módulo. |
| **Resultado Esperado** | La estación se libera y la sesión queda registrada; el tiempo y el costo se calculan y muestran en el historial/estadísticas. *Nota de revisión previa: `finalizarSesion()` solo marca `finalizada = 1` sin recalcular tiempo/costo (Anexo A, H-05): verificar el comportamiento real y registrar la diferencia.* |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-53: Tablero de estaciones y estados

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar la visualización de disponibilidad de PCs y sus estados. |
| **Caso de Uso** | Consultar control de ciber. |
| **Datos de Prueba** | Estaciones semilla: varias disponibles y al menos una ocupada (CP-48). |
| **Propósito** | Validar el tablero, los chips de cabecera y la persistencia de estados (BD `activos`). |
| **Prerrequisitos** | Sesión iniciada en Cyber. |
| **Pasos** | 1. Observar el tablero al ingresar (disponibles vs ocupadas).<br>2. Comparar los chips `#hdrDisponibles`/`#hdrOcupadas` con el conteo real.<br>3. Iniciar y finalizar sesiones; recargar la página y verificar que los estados persisten. |
| **Resultado Esperado** | El tablero, los chips y la BD coinciden siempre; los estados sobreviven a la recarga (no son solo visuales). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-54: Estadísticas e indicadores del módulo ciber

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar los KPIs y gráficos del módulo. |
| **Caso de Uso** | Consultar estadísticas de ciber. |
| **Datos de Prueba** | Sesiones históricas semilla + las creadas en CP-48/CP-52. |
| **Propósito** | Validar `action=estadisticas` y su render. |
| **Prerrequisitos** | Sesión iniciada; varias sesiones en BD. |
| **Pasos** | 1. Abrir la sección de estadísticas del módulo.<br>2. Ejecutar `?pagina=ciberControl&action=estadisticas`.<br>3. Comparar totales con SQL (`COUNT`, suma de tiempo/costo por tarifa). |
| **Resultado Esperado** | Los indicadores coinciden con la BD y se actualizan al crear/finalizar sesiones. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-55: Registrar una PC (activo de ciber)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Crear un equipo de cómputo desde el módulo de ciber. |
| **Caso de Uso** | Gestionar PCs del ciber (adicionar). |
| **Datos de Prueba** | Tipo de activo existente, marca `CP55`, descripción `PC de pruebas CP-55`, en ciber `Sí`; y duplicado: marca `CP55` + misma descripción. |
| **Propósito** | Validar la creación de activos desde ciber y su detección de duplicados (marca + descripción). |
| **Prerrequisitos** | Sesión iniciada en Cyber; existir al menos un tipo de activo. |
| **Pasos** | 1. Hacer clic en "Nueva PC" (`#btnNuevaPC`).<br>2. Seleccionar tipo, marca y descripción; guardar.<br>3. Intentar crear otra PC con la misma marca y descripción.<br>4. Inspeccionar los mensajes del servidor. |
| **Resultado Esperado** | La primera creación tiene éxito y la PC aparece en el tablero; la segunda se rechaza con **"Ya existe una PC con esa marca y descripción"**. *Nota: verificar también el posible error interno por `Validator` no importado en `CiberController` (Anexo A, H-04) y registrarlo si ocurre.* |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-56: Editar y eliminar una PC

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Modificar los datos de una PC y eliminarla. |
| **Caso de Uso** | Gestionar PCs del ciber (modificar/eliminar). |
| **Datos de Prueba** | PC de CP-55 → descripción `PC de pruebas CP-55 v2`; duplicado con otra PC existente; eliminación final. |
| **Propósito** | Validar `obtenerPC`, `actualizarPC` y `eliminarPC`. |
| **Prerrequisitos** | Existir la PC de CP-55. |
| **Pasos** | 1. Seleccionar la PC y cargar sus datos (obtenerPC).<br>2. Editar la descripción y guardar.<br>3. Intentar dejar marca/descripción idénticos a otra PC existente.<br>4. Eliminar la PC con confirmación. |
| **Resultado Esperado** | La edición persiste; el duplicado se rechaza con **"Ya existe otro activo con esa marca y descripción"**; la eliminación retira la PC del tablero (o informa si tiene sesiones asociadas — `sesion_ciber.fk_activo` es `RESTRICT`). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-57: Historial de sesiones y búsqueda de clientes

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Consultar el historial de sesiones ciber y buscar clientes asociados. |
| **Caso de Uso** | Consultar historial de ciber / Buscar cliente. |
| **Datos de Prueba** | Sesiones de CP-48 y CP-52; búsqueda por cédula `V-23456789` y cédula inexistente `V-00000000`. |
| **Propósito** | Verificar `action=historial` y `action=buscarCliente`. |
| **Prerrequisitos** | Sesión iniciada en Cyber; sesiones previas en BD. |
| **Pasos** | 1. Abrir el historial (`#btnHistorialCyber`) y revisar las sesiones (cliente, PC, tarifa, tiempo, costo).<br>2. Usar la búsqueda de cliente con la cédula existente.<br>3. Repetir con la cédula inexistente. |
| **Resultado Esperado** | El historial lista las sesiones con sus datos; la búsqueda existente devuelve el cliente y la inexistente informa que no se encontró (sin error en consola). |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.9 Activos (CP-58 – CP-63)


### Caso de Prueba CP-58: Registrar un activo sin seleccionar tipo

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar crear un activo sin elegir tipo de activo. |
| **Caso de Uso** | Gestionar activos (adicionar). |
| **Datos de Prueba** | Marca `CP58`, descripción `Activo de pruebas`, tipo de activo sin seleccionar. |
| **Propósito** | Validar la obligatoriedad del tipo de activo. |
| **Prerrequisitos** | Sesión iniciada en `?pagina=activos`. |
| **Pasos** | 1. Abrir el formulario nuevo de activos (`.btn-nuevo-activo`).<br>2. Llenar marca y descripción dejando el tipo vacío.<br>3. Guardar. |
| **Resultado Esperado** | El sistema rechaza con **"Debes seleccionar un tipo de activo"** y no crea el registro. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-59: Registrar activo con tipo de activo inexistente

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Enviar un identificador de tipo de activo inexistente (prueba técnica). |
| **Caso de Uso** | Gestionar activos (adicionar, prueba técnica). |
| **Datos de Prueba** | `fk_tipo_activo = 9999`. |
| **Propósito** | Verificar la validación de existencia del tipo de activo. |
| **Prerrequisitos** | Sesión iniciada; manipulación de la petición en DevTools. |
| **Pasos** | 1. Enviar `?pagina=activos&action=crear` con tipo `9999` y datos válidos.<br>2. Inspeccionar la respuesta JSON. |
| **Resultado Esperado** | Respuesta `success: false` con **"El tipo de activo seleccionado no existe"**. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-60: Registrar activo con marca y descripción duplicadas

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar crear un activo igual a uno existente. |
| **Caso de Uso** | Gestionar activos (adicionar). |
| **Datos de Prueba** | Marca `CP58` + descripción `Activo de pruebas` (creados en CP-58). |
| **Propósito** | Validar la regla de unicidad marca + descripción. |
| **Prerrequisitos** | Existir el activo de CP-58. |
| **Pasos** | 1. Intentar crear otro activo con idéntica marca y descripción (tipo igual o distinto).<br>2. Observar el mensaje. |
| **Resultado Esperado** | Se rechaza con **"Ya existe un activo con esa marca y descripción"**. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-61: Editar un activo

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Modificar los datos de un activo existente. |
| **Caso de Uso** | Gestionar activos (modificar). |
| **Datos de Prueba** | Activo de CP-58 → descripción `Activo de pruebas v2`; y colisión con otro activo existente. |
| **Propósito** | Validar la precarga y la actualización, incluida la variante "otro activo" de la unicidad. |
| **Prerrequisitos** | Existir el activo de CP-58 y al menos otro activo distinto. |
| **Pasos** | 1. Abrir Editar en el activo y verificar la precarga (incluye indicador de uso en ciber `is_ciber`).<br>2. Cambiar la descripción y guardar; recargar y verificar.<br>3. Intentar poner la combinación marca+descripción de otro activo existente. |
| **Resultado Esperado** | La edición persiste; el paso 3 se rechaza con **"Ya existe otro activo con esa marca y descripción"**. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-62: Cambiar estado y eliminar un activo

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Activar/desactivar un activo y eliminarlo. |
| **Caso de Uso** | Gestionar activos (estado/eliminar). |
| **Datos de Prueba** | Activo de CP-58 (con y sin sesiones ciber asociadas). |
| **Propósito** | Verificar `action=estado`, `action=eliminar` y el `RESTRICT` de `sesion_ciber.fk_activo`. |
| **Prerrequisitos** | Existir el activo de CP-58. |
| **Pasos** | 1. Cambiar el estado del activo y recargar para confirmar persistencia.<br>2. Intentar eliminar un activo que tenga sesiones ciber históricas.<br>3. Eliminar el activo de CP-58 sin sesiones (o con sesiones previas finalizadas si la restricción lo permite). |
| **Resultado Esperado** | El cambio de estado persiste; los activos con sesiones asociadas no se eliminan (error de restricción/registros asociados) o, si se elimina, el sistema informa correctamente el efecto. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-63: Listado, búsqueda y KPIs de activos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Consultar el inventario de activos y sus indicadores. |
| **Caso de Uso** | Consultar activos. |
| **Datos de Prueba** | Semilla de activos + los creados en CP-58/CP-61. |
| **Propósito** | Validar `action=listar`, `action=buscar` y `action=kpis`. |
| **Prerrequisitos** | Sesión iniciada en Activos. |
| **Pasos** | 1. Revisar el listado y los KPIs.<br>2. Buscar por marca/descripción existente y no existente.<br>3. Ejecutar `?pagina=activos&action=listar` y `action=kpis`. |
| **Resultado Esperado** | El listado y los KPIs coinciden con la BD; la búsqueda sin coincidencias muestra estado vacío; los JSON responden `success: true`. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.10 Asesoría Legal (CP-64 – CP-69)


### Caso de Prueba CP-64: Registrar una asesoría

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Crear una asesoría para un cliente ya vinculado a la tabla `cliente_asesoria`. |
| **Caso de Uso** | Gestionar asesorías (adicionar). |
| **Datos de Prueba** | Cliente `V-12345678` (Carlos González, con `cliente_asesoria`), tipo `Asesoría general` (permitido), documento `DOC-CP64`, descripción `Asesoría de prueba CP-64`. |
| **Propósito** | Validar el alta de asesorías con sus relaciones (`fk_cliente_asesoria`, `fk_tipo_asesoria`). |
| **Prerrequisitos** | Sesión iniciada en `?pagina=asesorias`; existir el cliente con registro asociado. |
| **Pasos** | 1. Ir a Asesoría Legal y abrir el formulario (`#asesoriaForm`).<br>2. Seleccionar/ingresar el cliente (cédula `V-12345678`), tipo, documento y descripción.<br>3. Habilitar y hacer clic en registrar (`#btnRegistrar`).<br>4. Recargar y buscar la asesoría en el listado. |
| **Resultado Esperado** | Toast de éxito; la asesoría aparece en la tabla con tipo, fecha y badge de estado permitido. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-65: Registrar asesoría con campos obligatorios incompletos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Enviar el formulario de asesoría sin ID y/o tipo de documento. |
| **Caso de Uso** | Gestionar asesorías (adicionar). |
| **Datos de Prueba** | Formulario sin identificación del cliente; cédula `V-00000000` (inexistente); cédula con formato inválido `1234`. |
| **Propósito** | Validar las validaciones de `crear` y el chequeo de documento (`'ID y tipo de documento son obligatorios'`). |
| **Prerrequisitos** | Sesión iniciada en Asesorías. |
| **Pasos** | 1. Intentar registrar sin ID/tipo de documento.<br>2. Intentar con cédula de formato inválido.<br>3. Intentar con un cliente que no existe en `cliente_asesoria`. |
| **Resultado Esperado** | Ningún intento crea el registro; se muestran los mensajes de validación correspondientes (incluido **"ID y tipo de documento son obligatorios"** cuando aplica). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-66: Registrar asesoría con cliente no vinculado

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Registrar una asesoría para un cliente que aún no tiene registro en `cliente_asesoria`. |
| **Caso de Uso** | Gestionar asesorías (adicionar). |
| **Datos de Prueba** | Cliente `V-67890123` (Luis Pérez, sin fila en `cliente_asesoria`), tipo `Asesoría documental`. |
| **Propósito** | Verificar el manejo de clientes nuevos/no vinculados y el aviso **"Cliente ya registrado: se actualizará su información de contacto"** cuando corresponda. |
| **Prerrequisitos** | Sesión iniciada en Asesorías. |
| **Pasos** | 1. Buscar/identificar al cliente `V-67890123`.<br>2. Registrar la asesoría con sus datos de contacto.<br>3. Repetir para un cliente ya vinculado y observar el aviso de actualización de contacto. |
| **Resultado Esperado** | El sistema crea (o actualiza el vínculo de) `cliente_asesoria` sin duplicarlo y registra la asesoría; el aviso de actualización aparece cuando el cliente ya existía. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-67: El historial de asesorías no ofrece editar ni eliminar

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar que el historial de asesorías es de solo registro y consulta (sin editar/eliminar). |
| **Caso de Uso** | Consultar asesorías. |
| **Datos de Prueba** | Asesoría de CP-64. |
| **Propósito** | Confirmar que las opciones de editar/eliminar se retiraron y que `action=actualizar`/`action=eliminar` ya no existen. |
| **Prerrequisitos** | Existir la asesoría de CP-64. |
| **Pasos** | 1. En `?pagina=asesorias`, revisar que la tabla del historial no tiene columna de acciones ni botones de editar/eliminar.<br>2. Verificar que no existe el modal de edición (`#modal-asesoria` ausente).<br>3. Ejecutar `?pagina=asesorias&action=actualizar` (o `action=eliminar`) y comprobar que el backend responde `Acción no válida`. |
| **Resultado Esperado** | El historial solo muestra datos; no hay botones de editar/eliminar ni modal, y el backend rechaza esas acciones. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-68: KPIs y etiquetas Permitido/Denegado de asesorías

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar los indicadores y el estado según el tipo de asesoría. |
| **Caso de Uso** | Consultar asesorías. |
| **Datos de Prueba** | Asesoría con tipo `Asesoría Penal` (permitido = FALSE) y asesoría con `Asesoría general` (permitido = TRUE). |
| **Propósito** | Validar `action=kpis` y la columna de estado (`Permitido`/`Denegado`). |
| **Prerrequisitos** | Crear ambas asesorías de prueba. |
| **Pasos** | 1. Registrar una asesoría de tipo `Asesoría Penal` y otra de `Asesoría general`.<br>2. Observar la columna de estado en el listado.<br>3. Ejecutar `?pagina=asesorias&action=kpis` y comparar con el listado. |
| **Resultado Esperado** | La de tipo Penal muestra badge **Denegado** (ícono `cancel`) y la general **Permitido** (ícono `check_circle`); los KPIs coinciden con los totales por estado. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-69: Búsqueda y listado de asesorías

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Filtrar asesorías por cliente/cédula/tipo. |
| **Caso de Uso** | Consultar asesorías. |
| **Datos de Prueba** | Búsqueda por cédula de CP-64, texto parcial del nombre y texto sin coincidencias (`zzz`). |
| **Propósito** | Validar `action=listar`, `action=buscar` y el estado vacío. |
| **Prerrequisitos** | Existir al menos una asesoría. |
| **Pasos** | 1. Buscar por cédula del cliente.<br>2. Buscar por fragmento de nombre.<br>3. Buscar `zzz`. |
| **Resultado Esperado** | Las búsquedas filtran correctamente; la sin coincidencias muestra estado vacío sin errores en consola. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.11 Usuarios (CP-70 – CP-76)


### Caso de Prueba CP-70: Listado y KPIs de usuarios

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar el listado de usuarios y sus indicadores. |
| **Caso de Uso** | Gestionar usuarios (consultar). |
| **Datos de Prueba** | 6 usuarios semilla (`admin`, `jperalta`, `mfernandez`, `crivas`, `amendoza`, `pgarcia`). |
| **Propósito** | Validar `action=listar`, `action=kpis` y `action=roles`. |
| **Prerrequisitos** | Sesión iniciada en `?pagina=usuarios`. |
| **Pasos** | 1. Ir a Configuración de Usuarios y revisar las tarjetas de KPIs.<br>2. Revisar la tabla (nombre, email, rol, estatus, acciones).<br>3. Ejecutar `?pagina=usuarios&action=listar`, `action=kpis` y `action=roles`. |
| **Resultado Esperado** | Se listan los usuarios semilla con sus roles; los KPIs coinciden con el conteo (total/activos/inactivos); `action=roles` devuelve los 5 roles. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-71: Crear usuario con nombre de usuario duplicado

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Crear un usuario nuevo y luego intentar duplicar su nombre de usuario. |
| **Caso de Uso** | Gestionar usuarios (adicionar). |
| **Datos de Prueba** | Usuario `cp_test`, nombre `Carlos`, apellido `Prueba`, email `cpt@test.com`, rol `Vendedor`, clave `1234`; duplicado: `admin`. |
| **Propósito** | Validar la creación y la restricción UNIQUE de `usuarios.user_name`. |
| **Prerrequisitos** | Sesión iniciada en Usuarios; no existir `cp_test`. |
| **Pasos** | 1. Abrir el formulario de nuevo usuario y completar los datos con `cp_test`.<br>2. Guardar y verificar en el listado.<br>3. Intentar crear otro usuario con nombre `admin`. |
| **Resultado Esperado** | La creación tiene éxito; el duplicado se rechaza con **"El nombre de usuario ya está en uso"**. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-72: Editar usuario (datos y rol)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Modificar nombre, apellido, email y rol de un usuario. |
| **Caso de Uso** | Gestionar usuarios (modificar). |
| **Datos de Prueba** | `cp_test` → apellido `Prueba Editado`, rol `Almacenista`. |
| **Propósito** | Verificar `action=actualizar` y la relación `COALESCE(?, fk_rol_usuario)` al cambiar/no cambiar el rol. |
| **Prerrequisitos** | Existir `cp_test` (CP-71). |
| **Pasos** | 1. Hacer clic en Editar (`.btn-editar-usuario`) y verificar la precarga.<br>2. Cambiar apellido y rol; guardar.<br>3. Recargar y confirmar; editar de nuevo sin tocar el rol y guardar. |
| **Resultado Esperado** | Los cambios persisten; guardar sin modificar el rol conserva el rol actual (no se pone en nulo). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-73: Activar y desactivar usuarios

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Cambiar el estatus de un usuario con su confirmación. |
| **Caso de Uso** | Gestionar usuarios (estado). |
| **Datos de Prueba** | Usuario `cp_test` (activo → inactivo → activo). |
| **Propósito** | Validar `action=estado` y el diálogo de confirmación. |
| **Prerrequisitos** | Existir `cp_test` activo. |
| **Pasos** | 1. Hacer clic en "Desactivar" (`.btn-estado-usuario`) y observar la confirmación.<br>2. Confirmar y recargar el listado.<br>3. Volver a "Activar" y recargar. |
| **Resultado Esperado** | La confirmación aparece (**¿Desactivar este usuario?** / **¿Activar este usuario?**); el estatus cambia en el listado (badge y botón de acción invertidos) y persiste tras recargar. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-74: Eliminar usuario con registros asociados

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar eliminar un usuario que tiene ventas u otras referencias. |
| **Caso de Uso** | Gestionar usuarios (eliminar). |
| **Datos de Prueba** | Intento A: usuario recién creado sin registros (`cp_test`). Intento B: usuario `admin` (o el usuario que registró ventas, `fk_usuario` de `orden_de_venta`). |
| **Propósito** | Validar `ON DELETE RESTRICT` de `orden_de_venta.fk_usuario` y el mensaje de la aplicación. |
| **Prerrequisitos** | Existir una venta asociada al usuario de intento B. |
| **Pasos** | 1. Eliminar `cp_test` aceptando la confirmación.<br>2. Intentar eliminar el usuario con ventas.<br>3. Verificar en BD que el usuario con ventas sigue activo. |
| **Resultado Esperado** | Intento A: se elimina. Intento B: **"No se puede eliminar: el usuario tiene registros asociados."** y el registro permanece. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-75: Restablecer la contraseña de un usuario

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Cambiar la clave de un usuario desde la acción `password`. |
| **Caso de Uso** | Gestionar usuarios (cambiar contraseña). |
| **Datos de Prueba** | Usuario `cp_test` → nueva clave `nueva1234`. |
| **Propósito** | Verificar `action=password` (hash bcrypt) y el nuevo acceso con la clave cambiada. |
| **Prerrequisitos** | Existir `cp_test` (CP-71) y conocer su clave actual. |
| **Pasos** | 1. Usar la opción de cambiar contraseña del usuario (action `password` desde la interfaz).<br>2. Establecer `nueva1234`.<br>3. Cerrar sesión e intentar entrar con `cp_test / nueva1234`.<br>4. Intentar entrar con la clave anterior. |
| **Resultado Esperado** | La nueva clave abre sesión y la anterior deja de funcionar (error de credenciales); en BD el `password_hash` se actualizó a un hash bcrypt distinto. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-76: Usuario desactivado no puede iniciar sesión (integración)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Confirmar el bloqueo de acceso de usuarios inactivos y la revocación de sesión activa. |
| **Caso de Uso** | Iniciar sesión / Gestionar usuarios. |
| **Datos de Prueba** | Usuario `cp_test` desactivado con sesión abierta en otra pestaña. |
| **Propósito** | Integrar CP-08 y CP-73: el estatus se evalúa en cada login. |
| **Prerrequisitos** | Tener `cp_test` activo y logueado en una ventana; administrador en otra. |
| **Pasos** | 1. Con `cp_test` logueado, desactivarlo desde la sesión de administrador.<br>2. En la ventana de `cp_test`, cerrar sesión e intentar entrar de nuevo.<br>3. Refrescar una página protegida en la sesión abierta antes de desactivar y observar el comportamiento. |
| **Resultado Esperado** | El nuevo login falla con mensaje de credenciales; sobre la sesión previamente abierta, registrar el comportamiento observado (el sistema no expone revocación en vivo; reportar si la sesión sigue operativa). |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.12 Roles y Permisos (CP-77 – CP-82)


### Caso de Prueba CP-77: Listado de roles y permisos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar los roles y permisos del sistema. |
| **Caso de Uso** | Gestionar roles (consultar). |
| **Datos de Prueba** | 5 roles semilla (Administrador, Vendedor, Almacenista, Asesor, Soporte Técnico) y 11 permisos (`gestion_usuarios`, `gestion_productos`, `gestion_ventas`, `gestion_abastecimiento`, `gestion_asesoria`, `gestion_activos`, `gestion_clientes`, `gestion_proveedores`, `ver_reportes`, `configuracion`, `gestion_ciber`). |
| **Propósito** | Validar `action=listar` y `action=permisos`. |
| **Prerrequisitos** | Sesión iniciada en `?pagina=roles`. |
| **Pasos** | 1. Ir a Roles y Permisos y revisar la lista de roles.<br>2. Revisar el catálogo de permisos.<br>3. Ejecutar `?pagina=roles&action=listar` y `action=permisos`. |
| **Resultado Esperado** | Se muestran los 5 roles y los 11 permisos; los JSON responden `success: true` con esos datos. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-78: Crear y editar un rol

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Crear un rol nuevo y modificarlo. |
| **Caso de Uso** | Gestionar roles (adicionar/modificar). |
| **Datos de Prueba** | Rol `Prueba QA` → editar a `Prueba QA v2`. |
| **Propósito** | Validar el CRUD de roles (`crear`, `actualizar`). |
| **Prerrequisitos** | Sesión iniciada en Roles; no existir el rol. |
| **Pasos** | 1. Crear el rol `Prueba QA`.<br>2. Verificarlo en el listado.<br>3. Editarlo a `Prueba QA v2` y recargar. |
| **Resultado Esperado** | El rol se crea y se edita con persistencia tras recargar; los toast de éxito acompañan cada operación. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-79: Asignar permisos a un rol

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Guardar la matriz de permisos de un rol. |
| **Caso de Uso** | Gestionar roles (permisos). |
| **Datos de Prueba** | Rol `Prueba QA` con permisos `gestion_productos` y `ver_reportes`; luego todos los permisos; luego ninguno. |
| **Propósito** | Validar `action=guardarPermisos` y su persistencia en `permisos_rol`. |
| **Prerrequisitos** | Existir el rol de CP-78. |
| **Pasos** | 1. Abrir la asignación de permisos del rol (checkboxes).<br>2. Marcar `gestion_productos` y `ver_reportes`; guardar.<br>3. Reabrir y verificar (`action=permisosRol&rol_id=X`).<br>4. Marcar todos, guardar y reabrir; luego desmarcar todos, guardar y reabrir. |
| **Resultado Esperado** | Cada guardado refleja exactamente la selección hecha al reabrir; el JSON `permisosRol` coincide con lo mostrado. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-80: Consultar usuarios y permisos de un rol

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Revisar los usuarios asignados a un rol y sus permisos efectivos. |
| **Caso de Uso** | Gestionar roles (consultar). |
| **Datos de Prueba** | Rol `Vendedor` (usuarios `jperalta`, `mfernandez`; permisos `gestion_ventas`, `gestion_clientes`, `ver_reportes`). |
| **Propósito** | Validar `action=usuarios` y `action=detalle`. |
| **Prerrequisitos** | Sesión iniciada en Roles. |
| **Pasos** | 1. Seleccionar el rol `Vendedor` y ver sus usuarios.<br>2. Ver el detalle/permisos del rol.<br>3. Ejecutar `?pagina=roles&action=usuarios` y `action=detalle&id=2`. |
| **Resultado Esperado** | El rol muestra a `jperalta` y `mfernandez` con los 3 permisos de la semilla; los JSON coinciden. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-81: Asignar un rol a un usuario

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Cambiar el rol de un usuario desde el módulo de roles. |
| **Caso de Uso** | Gestionar roles (asignación a usuarios). |
| **Datos de Prueba** | Usuario `cp_test` (rol `Almacenista`) → rol `Soporte Técnico`. |
| **Propósito** | Validar `action=asignarRol` y el impacto en la sesión del usuario. |
| **Prerrequisitos** | Existir `cp_test` y el rol `Soporte Técnico`. |
| **Pasos** | 1. En Roles, usar la asignación de usuario a rol (`asignarRol`).<br>2. Asignar `Soporte Técnico` a `cp_test`.<br>3. Verificar con `?pagina=usuarios&action=detalle&id=...` y en el listado de usuarios.<br>4. Cerrar sesión de `cp_test` y volver a entrar para observar el rol en la cabecera. |
| **Resultado Esperado** | El cambio de rol persiste en el listado de usuarios y el nuevo login muestra el rol actualizado en la sesión. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-82: Eliminar un rol

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Eliminar el rol de prueba y un rol con usuarios/permisos asignados. |
| **Caso de Uso** | Gestionar roles (eliminar). |
| **Datos de Prueba** | Intento A: rol `Prueba QA` (sin usuarios). Intento B: rol `Vendedor` (con 2 usuarios y permisos). |
| **Propósito** | Verificar `action=eliminar`, la confirmación y la integridad referencial (`permisos_rol` en cascada, `rol_usuarios`/`usuarios` en `RESTRICT`). |
| **Prerrequisitos** | Existir el rol de CP-78 y los roles semilla. |
| **Pasos** | 1. Eliminar `Prueba QA` confirmando **"¿Está seguro de eliminar el rol …?"**.<br>2. Intentar eliminar `Vendedor`.<br>3. Verificar en BD el resultado (y que `permisos_rol` del rol eliminado desapareció). |
| **Resultado Esperado** | Intento A: se elimina con su confirmación y desaparecen sus asignaciones de permisos. Intento B: no se elimina (usuarios referenciados) y el sistema informa el motivo; los usuarios conservan su rol. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.13 Reportes y Estadísticas (CP-83 – CP-89)


### Caso de Prueba CP-83: KPIs del módulo de reportes

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar los indicadores iniciales del módulo. |
| **Caso de Uso** | Consultar reportes. |
| **Datos de Prueba** | Datos semilla + movimientos de las pruebas previas. |
| **Propósito** | Validar la carga inicial de `action=kpis`. |
| **Prerrequisitos** | Sesión iniciada en `?pagina=reportes`. |
| **Pasos** | 1. Ir a Reportes y Estadísticas.<br>2. Ejecutar `?pagina=reportes&action=kpis`.<br>3. Comparar con la BD. |
| **Resultado Esperado** | Los KPIs se pintan y coinciden con los totales SQL; `success: true`. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-84: Consultar reporte de ventas por rango de fechas

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Generar el reporte de ventas para un período. |
| **Caso de Uso** | Generar reportes. |
| **Datos de Prueba** | Tipo `ventas`, fecha inicio `2026-01-01`, fecha fin `2026-12-31`. |
| **Propósito** | Validar `action=consultar` con el tipo `ventas`. |
| **Prerrequisitos** | Existir ventas en el rango. |
| **Pasos** | 1. Seleccionar tipo `ventas`.<br>2. Definir el rango de fechas y consultar.<br>3. Verificar el listado (orden, fecha, cliente, total) y comparar con SQL. |
| **Resultado Esperado** | El reporte muestra las ventas del período con sus totales; la respuesta JSON tiene `success: true`. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-85: Consultar con rango de fechas inválido

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Enviar fecha de fin anterior a la de inicio. |
| **Caso de Uso** | Generar reportes. |
| **Datos de Prueba** | Inicio `2026-12-31`, fin `2026-01-01`; y fechas no válidas (`2026-13-45`). |
| **Propósito** | Validar `validarRango` y la captura de `InvalidArgumentException`. |
| **Prerrequisitos** | Sesión iniciada en Reportes. |
| **Pasos** | 1. Consultar con el rango invertido.<br>2. Consultar con fechas malformadas.<br>3. Observar el mensaje devuelto. |
| **Resultado Esperado** | El servidor responde `success: false` con el mensaje de validación de rango (error de fechas), sin excepción PHP ni página en blanco. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-86: Consultar los demás tipos de reporte

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Generar reportes de inventario, movimientos, proveedores y ciber. |
| **Caso de Uso** | Generar reportes. |
| **Datos de Prueba** | Tipos `inventario`, `movimientos`, `proveedores`, `cyber` con rango `2026-01-01` a `2026-12-31`. |
| **Propósito** | Validar los 5 tipos soportados por el modelo `Reporte`. |
| **Prerrequisitos** | Datos semilla en todas las tablas involucradas. |
| **Pasos** | 1. Consultar cada tipo con el mismo rango.<br>2. Verificar columnas específicas de cada reporte (productos/stock, movimientos de stock, órdenes por proveedor, sesiones ciber).<br>3. Comparar los conteos con SQL. |
| **Resultado Esperado** | Cada tipo devuelve su conjunto de datos coherente con la BD; los tipos no soportados no están en el select. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-87: Exportar reporte a CSV

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Descargar un reporte en formato CSV. |
| **Caso de Uso** | Exportar reportes. |
| **Datos de Prueba** | Tipo `ventas`, rango completo, formato `csv`. |
| **Propósito** | Validar `action=exportar` con `formato=csv`. |
| **Prerrequisitos** | Consulta previa exitosa (CP-84); permiso `ver_reportes`. |
| **Pasos** | 1. Ejecutar la exportación a CSV desde la interfaz (o `?pagina=reportes&action=exportar&tipo=ventas&formato=csv&csrf_token=…`).<br>2. Abrir el archivo descargado. |
| **Resultado Esperado** | Se descarga un CSV con encabezados y las mismas filas de la consulta; se puede abrir en hoja de cálculo sin errores de codificación. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-88: Exportar reporte a Excel y PDF

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Descargar reportes en formatos Excel y PDF. |
| **Caso de Uso** | Exportar reportes. |
| **Datos de Prueba** | Tipo `inventario`, formato `excel` y `pdf`. |
| **Propósito** | Validar los formatos de exportación restantes. |
| **Prerrequisitos** | Consulta previa del tipo `inventario`. |
| **Pasos** | 1. Exportar a `excel` y abrir el archivo.<br>2. Exportar a `pdf` y abrirlo. |
| **Resultado Esperado** | Ambos archivos se descargan, abren correctamente y contienen los datos del reporte con encabezados legibles. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-89: Exportar con parámetros inválidos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Exportar con formato no soportado o sin rango de fechas. |
| **Caso de Uso** | Exportar reportes (prueba técnica). |
| **Datos de Prueba** | `formato=txt`; `formato=csv` sin fechas; petición sin `csrf_token`. |
| **Propósito** | Validar los `catch` del controlador de exportación (formato inválido, excepción de validación, CSRF). |
| **Prerrequisitos** | Sesión iniciada; DevTools. |
| **Pasos** | 1. Solicitar `action=exportar&tipo=ventas&formato=txt`.<br>2. Solicitar sin parámetros de fecha.<br>3. Solicitar sin `csrf_token`.<br>4. Observar las respuestas (código HTTP y cuerpo). |
| **Resultado Esperado** | En todos los casos el servidor responde un JSON de error (`success: false`) o error controlado, sin descargas corruptas ni errores PHP 500. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.14 PWA / Funcionamiento Offline (CP-90 – CP-94)


### Caso de Prueba CP-90: Registro del Service Worker

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar que la aplicación registra el Service Worker (`sw.js`). |
| **Caso de Uso** | Instalación PWA (Ninguno). |
| **Datos de Prueba** | Navegador Chrome con DevTools; archivos `sw.js` y `manifest.json` en la raíz del sitio. |
| **Propósito** | Confirmar la habilitación de la PWA desde el login y desde el layout. |
| **Prerrequisitos** | Servidor accesible por HTTP(S); DevTools abierto (F12). |
| **Pasos** | 1. Abrir `?pagina=login`, ir a Application → Service Workers.<br>2. Iniciar sesión y volver a verificar el registro en el layout.<br>3. Revisar la pestaña Manifest (Application → Manifest). |
| **Resultado Esperado** | `sw.js` aparece como `activated and running` (o `installing`) en ambas páginas; el manifest carga sin errores (nombre, íconos). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-91: Página offline ante falla de red

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Navegar con la red cortada y verificar la página de respaldo. |
| **Caso de Uso** | Funcionamiento offline (Ninguno). |
| **Datos de Prueba** | Chrome DevTools → Network → Offline; página precacheada `offline.php`. |
| **Propósito** | Validar el fallback del Service Worker (`return caches.match('offline.php')`). |
| **Prerrequisitos** | Haber visitado el sistema al menos una vez (caché poblada); CP-90 OK. |
| **Pasos** | 1. Con sesión iniciada, activar modo offline en DevTools.<br>2. Recargar la página actual (F5).<br>3. Intentar navegar a otra página del sistema.<br>4. Restaurar la red y recargar. |
| **Resultado Esperado** | Sin red se muestra la página offline (`offline.php`, ícono `cloud_off` y mensaje de sin conexión) en lugar de un error del navegador; al restaurar la red la aplicación vuelve a funcionar normalmente. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-92: Caché de recursos estáticos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar la caché de CSS, JS, fuentes e íconos. |
| **Caso de Uso** | Funcionamiento offline (Ninguno). |
| **Datos de Prueba** | DevTools → Application → Cache Storage. |
| **Propósito** | Comprobar que los recursos listados en `sw.js` (`/Public/css/`, `/Public/js/`, `/Public/fonts/`, `/Public/icons/`, `manifest.json`) se almacenan. |
| **Prerrequisitos** | Visita previa al sistema. |
| **Pasos** | 1. Abrir Cache Storage en DevTools.<br>2. Inspeccionar las entradas de caché.<br>3. Cerrar y reabrir la pestaña sin conexión; abrir el login. |
| **Resultado Esperado** | Los recursos críticos aparecen cacheados y la UI del login (estilos e íconos) se renderiza sin conexión. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-93: La caché offline no expone datos privados

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar que la página offline y la caché no contienen datos de sesión. |
| **Caso de Uso** | Funcionamiento offline / Seguridad. |
| **Datos de Prueba** | Páginas privadas visitadas (dashboard, inventario) con sesión iniciada. |
| **Propósito** | Analizar qué HTML se cachea y si respeta el control de acceso. |
| **Prerrequisitos** | Sesión iniciada; CP-91 ejecutado. |
| **Pasos** | 1. Con la red cortada, intentar abrir `?pagina=inventario` desde una URL nueva.<br>2. Inspeccionar en DevTools qué contenido se sirve (SW response).<br>3. Revisar el código de `sw.js` respecto a qué páginas cachea (`offline.php`, assets). |
| **Resultado Esperado** | Las URLs privadas no sirven datos almacenados de otros usuarios: se responde con la página offline o se redirige al login; el SW solo precachea `offline.php` y estáticos. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-94: Instalación de la aplicación (Add to Home Screen)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Instalar la PWA en escritorio/móvil. |
| **Caso de Uso** | Instalación PWA. |
| **Datos de Prueba** | Chrome (menú ⋮ → "Instalar aplicación…") o Android (Añadir a pantalla de inicio). |
| **Propósito** | Verificar el manifiesto y el ícono de instalación. |
| **Prerrequisitos** | CP-90 OK; manifest con nombre e íconos. |
| **Pasos** | 1. Abrir el sistema y revisar el ícono de instalación en la barra de direcciones.<br>2. Instalar la aplicación.<br>3. Abrir la app instalada y navegar. |
| **Resultado Esperado** | La instalación utiliza `manifest.json` (nombre/íconos correctos) y la app instalada carga la interfaz del sistema. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.15 Usabilidad e Interfaz (CP-95 – CP-99)


### Caso de Prueba CP-95: Navegación completa por el menú lateral

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Recorrer todos los enlaces del menú lateral. |
| **Caso de Uso** | Navegación del sistema. |
| **Datos de Prueba** | Enlaces: Dashboard, Inventario, Ventas, Clientes, Solicitudes (submenu: Órdenes de Compra, Proveedores), Cyber, Reportes, Activos, Asesoría, Configuración, Roles y Permisos, Manual de Usuario. |
| **Propósito** | Verificar que cada enlace carga la vista correspondiente con su título y sin errores. |
| **Prerrequisitos** | Sesión iniciada. |
| **Pasos** | 1. Hacer clic en cada enlace del menú (incluido el submenú "Solicitudes").<br>2. Verificar el título de la pestaña/encabezado según `PAGE_TITLES`.<br>3. Revisar la consola del navegador en cada página. |
| **Resultado Esperado** | Todas las páginas cargan con su título correcto (Dashboard, Gestión de inventario, Punto de Venta (POS), etc.), sin 404 ni errores en consola. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-96: Tema claro/oscuro persistente

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Cambiar el tema y verificar su persistencia. |
| **Caso de Uso** | Configuración de interfaz. |
| **Datos de Prueba** | Botón "Modo Oscuro" del menú y `#themeToggle` del login. |
| **Propósito** | Validar el alternador de tema y su almacenamiento (localStorage/cookie). |
| **Prerrequisitos** | Navegador con almacenamiento habilitado. |
| **Pasos** | 1. Cambiar a tema oscuro desde el menú lateral.<br>2. Recargar la página y navegar a otro módulo.<br>3. Cerrar sesión y observar el login; volver a entrar.<br>4. Volver al tema claro. |
| **Resultado Esperado** | El tema seleccionado persiste en todas las páginas y sesiones; el ícono del botón (`dark_mode`/`light_mode`) refleja el estado actual. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-97: Comportamiento en diferentes tamaños de ventana

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar la respuesta visual en escritorio, tableta y móvil. |
| **Caso de Uso** | Navegación del sistema. |
| **Datos de Prueba** | Viewports 1920×1080, 768×1024 y 375×666 (DevTools device toolbar); módulos POS e Inventario. |
| **Propósito** | Evaluar la adaptación de tablas (`responsive-table`), menú y formularios. |
| **Prerrequisitos** | Sesión iniciada. |
| **Pasos** | 1. Abrir Dashboard, Inventario y POS en cada tamaño.<br>2. Verificar que el menú lateral se adapta (hamburguesa), las tablas permiten scroll/ocultan columnas y los botones son alcanzables.<br>3. Registrar textos cortados o elementos superpuestos. |
| **Resultado Esperado** | Sin texto cortado crítico ni solapamientos; la navegación es posible en los tres tamaños. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-98: Elementos no funcionales del login

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Probar los botones sociales y enlaces auxiliares del login. |
| **Caso de Uso** | Iniciar sesión (usabilidad). |
| **Datos de Prueba** | Botones de Google y GitHub; enlace "¿Olvidaste tu contraseña?"; enlace "Regístrate"; botón `#themeToggle`. |
| **Propósito** | Verificar que las opciones no funcionales informan al usuario (sin romper la interfaz). |
| **Prerrequisitos** | Navegador en `?pagina=login`. |
| **Pasos** | 1. Hacer clic en el botón de Google; luego en el de GitHub.<br>2. Hacer clic en los enlaces de recuperación de clave y registro.<br>3. Cambiar el tema con el botón flotante. |
| **Resultado Esperado** | Los botones sociales muestran `alert('Funcionalidad no disponible')`; los enlaces no rompen la página (no hay registro real habilitado); el tema cambia sin error. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-99: Mensajes, toasts y confirmaciones de la interfaz

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Revisar la coherencia de mensajes de éxito/error y diálogos de confirmación. |
| **Caso de Uso** | Todos (usabilidad). |
| **Datos de Prueba** | Operaciones CRUD de los módulos ya probados; eliminaciones con confirmación. |
| **Propósito** | Evaluar legibilidad, tono y visibilidad de los mensajes (toasts Materialize, `card-panel` de errores, `confirm()`). |
| **Prerrequisitos** | Haber ejecutado los grupos 5.3 a 5.13. |
| **Pasos** | 1. Recopilar los mensajes observados en las pruebas (éxito, validación, error de BD).<br>2. Verificar que cada operación informa su resultado en pantalla (no solo en consola).<br>3. Verificar que las acciones destructivas piden confirmación (eliminar, vaciar carrito, desactivar usuario).<br>4. Aplicar el cuestionario de la Sección 6 con el usuario/tutor. |
| **Resultado Esperado** | Toda operación visible informa su resultado; las destructivas confirman antes; los mensajes son claros y en español; cuestionario de usabilidad respondido. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.16 Base de Datos (CP-100 – CP-107)


### Caso de Prueba CP-100: Conexión y estructura de la base de datos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar la conexión PDO y la creación del esquema. |
| **Caso de Uso** | Ninguno. |
| **Datos de Prueba** | Usuario/clave correctos en el archivo de configuración; `estructura.sql`. |
| **Propósito** | Confirmar que la aplicación conecta y que las 17 tablas existen con sus índices. |
| **Prerrequisitos** | MySQL en ejecución; base `zona_web_lara` importada. |
| **Pasos** | 1. Importar `estructura.sql` en una BD limpia (o verificar la existente).<br>2. Navegar por varias páginas del sistema y revisar logs.<br>3. Listar tablas: `roles`, `permisos`, `categoria`, `clientes`, `cliente_asesoria`, `proveedores`, `status_seguimiento`, `tipo_asesoria`, `tarifas`, `tipo_activo`, `rol_usuarios`, `usuarios`, `permisos_rol`, `productos`, `orden_de_venta`, `lineas_venta`, `orden_abastecimiento`, `lineas_abastecimiento`, `asesoria`, `activos`, `sesion_ciber`. |
| **Resultado Esperado** | Conexión sin errores; todas las tablas presentes con ENGINE InnoDB/utf8mb4; la aplicación opera sin excepciones PDO en los logs. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-101: Carga de datos semilla

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar la carga de `seed_data.sql`. |
| **Caso de Uso** | Ninguno. |
| **Datos de Prueba** | `seed_data.sql` (5 roles, 11 permisos, 10 clientes, 9 proveedores, 6 estados, 6 tipos de asesoría, tarifas, usuarios semilla, productos, órdenes y sesiones ciber). |
| **Propósito** | Validar que el entorno de pruebas parte de datos consistentes. |
| **Prerrequisitos** | Estructura importada (CP-100). |
| **Pasos** | 1. Importar `seed_data.sql`.<br>2. Contar registros por tabla clave.<br>3. Iniciar sesión y verificar los datos en los módulos. |
| **Resultado Esperado** | Los conteos coinciden con la semilla y cada módulo muestra sus registros (ej. 5 roles, 10 clientes, 9 proveedores). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-102: Prevención de datos duplicados (UNIQUE)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Forzar duplicados en las claves únicas. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | Cédula `V-12345678` en `clientes`; RIF `J-12345678-9` en `proveedores`; usuario `admin` en `usuarios`; código `LAP-001` en `productos`. |
| **Propósito** | Verificar los constraints UNIQUE (y su traducción a mensajes de aplicación). |
| **Prerrequisitos** | Datos semilla; acceso SQL. |
| **Pasos** | 1. Intentar INSERTs duplicados vía SQL (capturar error 1062).<br>2. Intentar los mismos duplicados por la interfaz (CP-13, CP-36, CP-45, CP-71). |
| **Resultado Esperado** | SQL devuelve error 1062 por cada constraint; la interfaz muestra los mensajes de negocio ya definidos y no duplica filas. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-103: Integridad referencial RESTRICT

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar borrados prohibidos por la BD. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | Borrar por SQL: cliente con `cliente_asesoria` (id 1), producto con FK desde `orden_de_venta`… (`usuarios` con ventas), categoría con productos, proveedor con órdenes, activo con `sesion_ciber`. |
| **Propósito** | Validar las relaciones `ON DELETE RESTRICT` de `estructura.sql`. |
| **Prerrequisitos** | Datos semilla intactos; acceso SQL con constraints activos. |
| **Pasos** | 1. Ejecutar los DELETEs indicados.<br>2. Capturar los errores de restricción (1451).<br>3. Verificar que los registros permanecen. |
| **Resultado Esperado** | Cada DELETE falla con error de clave foránea y los datos originales se conservan; la aplicación muestra los mensajes "registros asociados" correspondientes (CP-39, CP-47, CP-74). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-104: Integridad referencial en cascada (CASCADE)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar borrados en cascada y su efecto colateral. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | Eliminar una orden de venta creada en pruebas (CP-30); eliminar una orden de abastecimiento de prueba (CP-40); eliminar el rol `Prueba QA` con permisos (CP-82). |
| **Propósito** | Validar `lineas_venta`, `lineas_abastecimiento` y `permisos_rol` en cascada, además del caso riesgoso `lineas_venta.fk_producto` → `productos`. |
| **Prerrequisitos** | Registros de prueba propios (no tocar la semilla). |
| **Pasos** | 1. Eliminar la orden de venta creada y verificar que sus líneas desaparecen.<br>2. Eliminar la orden de abastecimiento y verificar sus líneas.<br>3. Eliminar el rol de prueba y verificar `permisos_rol`.<br>4. Eliminar un producto con líneas de venta (CP-20 intento B) y revisar si desaparecen líneas históricas. |
| **Resultado Esperado** | Las cascadas esperadas ocurren sin huérfanos; en el paso 4 registrar el resultado: si las líneas de venta históricas se borran, elevar hallazgo de integridad (los reportes de ventas perderían detalle). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-105: Actualización masiva y concurrencia básica

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Modificar el mismo registro desde dos sesiones/ventanas. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | Dos sesiones (dos navegadores/ventanas anónimas) editando el mismo producto `TEC-001`. |
| **Propósito** | Observar el comportamiento ante escrituras simultáneas (última escritura gana). |
| **Prerrequisitos** | Dos sesiones de administrador. |
| **Pasos** | 1. Abrir la edición de `TEC-001` en ambas ventanas.<br>2. Guardar en la ventana A (precio 50.00) y luego en la ventana B (precio 55.00).<br>3. Recargar y ver el valor final.<br>4. Repetir intentando eliminar desde A mientras B lo edita. |
| **Resultado Esperado** | No hay errores 500 ni bloqueos; el valor final es el de la última escritura (documentar comportamiento); la eliminación concurrente deja un estado manejable (mensaje de registro inexistente o datos consistentes). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-106: Logs de la aplicación

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar el registro de errores en logs. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | Acciones que provoquen `Logger::error` (p. ej. SQL inválido por manipulación, fallo de exportación). |
| **Propósito** | Comprobar que las excepciones se persisten para diagnóstico. |
| **Prerrequisitos** | Directorio de logs escribible. |
| **Pasos** | 1. Provocar un error controlado (p. ej. `action=exportar` con parámetros corruptos — CP-89).<br>2. Revisar los archivos de log generados.<br>3. Reiniciar el servidor y confirmar que la aplicación sigue operativa. |
| **Resultado Esperado** | El error queda registrado con fecha/módulo/mensaje y la aplicación responde JSON de error al usuario (no página en blanco). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-107: Respaldo y restauración de la base de datos

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Generar un respaldo completo y restaurarlo en una BD de prueba. |
| **Caso de Uso** | Ninguno (infraestructura). |
| **Datos de Prueba** | BD `zona_web_lara` con datos de todas las pruebas; `mysqldump`. |
| **Propósito** | Asegurar la recuperación ante pérdida de datos. |
| **Prerrequisitos** | Acceso CLI a MySQL; permisos de CREATE DATABASE. |
| **Pasos** | 1. Ejecutar `mysqldump` de la BD completa.<br>2. Restaurar el dump en `zona_web_lara_pruebas`.<br>3. Apuntar temporalmente la aplicación a la BD restaurada (o importarla en el mismo nombre tras respaldar la original).<br>4. Verificar módulos y conteos. |
| **Resultado Esperado** | La restauración reproduce los datos con exactitud (mismos conteos y relaciones) y la aplicación opera igual sobre la copia. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.17 Seguridad (CP-108 – CP-115)


### Caso de Prueba CP-108: Protección CSRF en formularios

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Enviar peticiones de escritura sin token CSRF o con token alterado. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | POST a `?pagina=login_validate` sin `csrf_token`; POST a `?pagina=inventario&action=crear` con token vacío o de otra sesión. |
| **Propósito** | Verificar `Router::verifyCsrfToken()` (hash_equals) en login y acciones POST. |
| **Prerrequisitos** | DevTools o cliente HTTP (cURL); sesión iniciada para las acciones de módulo. |
| **Pasos** | 1. Enviar el login válido pero sin campo `csrf_token`.<br>2. Enviar la creación de un producto con `csrf_token=` vacío.<br>3. Reutilizar el token de otra sesión.<br>4. Enviar el mismo login con token correcto (control). |
| **Resultado Esperado** | Los intentos 1 a 3 son rechazados (login redirige a `error=1`; las acciones no aplican cambios); el intento 4 funciona normalmente. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-109: Control de acceso por URL y acción

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Intentar invocar acciones de módulos manipulando la URL. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | `?pagina=usuarios&action=listar` sin sesión; `?pagina=usuarios&action=eliminar&id=1` con método GET desde otra cuenta; páginas inexistentes `?pagina=xxx`. |
| **Propósito** | Verificar que el despacho exige sesión (`logged_in`) y que las mutaciones son POST con CSRF; validar el manejo 404. |
| **Prerrequisitos** | Dos cuentas (admin y usuario común); DevTools. |
| **Pasos** | 1. Sin sesión, solicitar `action=listar` de usuarios y de inventario.<br>2. Con sesión de usuario no administrador, intentar acciones de módulos restringidos por URL.<br>3. Solicitar `?pagina=inexistente` y `?pagina=../../etc` (nombre inválido). |
| **Resultado Esperado** | Sin sesión todo redirige al login; las mutaciones por GET no producen cambios; `pagina` inválida cae en login o 404 controlado (**Error 404: Página no encontrada**). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-110: Evaluación efectiva de permisos por rol (H-02)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar si `$_SESSION['permisos']` restringe el acceso real a módulos. |
| **Caso de Uso** | Gestión de usuarios / Seguridad. |
| **Datos de Prueba** | Usuario con rol `Vendedor` (permisos `gestion_ventas`, `gestion_clientes`, `ver_reportes`); URLs de Inventario, Usuarios, Cyber. |
| **Propósito** | Confirmar el hallazgo de revisión previa: los permisos se cargan en sesión pero no se evalúan en el enrutador (Anexo A, H-02). |
| **Prerrequisitos** | Usuario de rol `Vendedor` con clave conocida. |
| **Pasos** | 1. Iniciar sesión con `jperalta` (Vendedor).<br>2. Navegar directamente a `?pagina=inventario`, `?pagina=usuarios`, `?pagina=roles`, `?pagina=ciberControl`.<br>3. Intentar acciones CRUD en esos módulos.<br>4. Comparar con la matriz `permisos_rol` de la semilla. |
| **Resultado Esperado** | Acceso denegado a los módulos sin permiso (resultado deseado). *Si el sistema los permite, registrar el hallazgo H-02: los permisos no se evalúan y el menú es el único límite.* |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-111: Inyección SQL en búsquedas

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Inyectar cadenas SQL en los campos de búsqueda y formularios. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | Búsquedas con `' OR '1'='1`, `'; DROP TABLE productos; --`, `%` y `--` en Inventario, Clientes, Proveedores, POS y Reportes. |
| **Propósito** | Validar el uso de consultas preparadas (PDO) en todos los modelos. |
| **Prerrequisitos** | Sesión iniciada; respaldo previo de la BD (CP-107). |
| **Pasos** | 1. Introducir cada payload en los buscadores de los módulos.<br>2. Revisar respuestas y conservación de datos.<br>3. Inspeccionar los logs de MySQL/PHP. |
| **Resultado Esperado** | Ningún payload altera o extrae datos; las búsquedas devuelven 0 coincidencias o tratan el texto literalmente; las tablas permanecen intactas. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-112: Cross-Site Scripting (XSS) en datos de usuario

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Almacenar HTML/JavaScript en campos de texto y verificar su render. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | Nombre de producto `<script>alert('XSS')</script>`, cliente con nombre `<img src=x onerror=alert(1)>`, descripción de asesoría con `<b>negrita</b>`. |
| **Propósito** | Verificar la salida escapada (`htmlspecialchars`) en vistas y listados (incluido `M.toast`). |
| **Prerrequisitos** | Sesión iniciada; DevTools. |
| **Pasos** | 1. Crear registros con los payloads en Inventario, Clientes y Asesorías.<br>2. Recargar los listados y los detalles.<br>3. Revisar los toasts de confirmación que muestran nombres. |
| **Resultado Esperado** | El código se muestra como texto literal; no se ejecuta ningún script ni se dispara una alerta. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-113: Gestión de sesión (regeneración y expiración)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Verificar la regeneración de ID tras el login y el comportamiento de la sesión. |
| **Caso de Uso** | Iniciar sesión / Cerrar sesión. |
| **Datos de Prueba** | Cookie `PHPSESSID` antes/después del login y del logout. |
| **Propósito** | Validar `session_regenerate_id(true)` en login y logout (prevención de fijación de sesión). |
| **Prerrequisitos** | DevTools → Application → Cookies. |
| **Pasos** | 1. Anotar el `PHPSESSID` en el login.<br>2. Autenticarse y comparar el ID de sesión.<br>3. Ejecutar el logout oficial (`?pagina=login&logout=1`) y comprobar que la cookie/sesión del servidor quedaron inválidas.<br>4. Intentar reutilizar el ID anterior. |
| **Resultado Esperado** | El ID se regenera al autenticar; tras el logout la sesión previa no sirve para acceder a páginas privadas. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-114: Exposición de información en errores

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Provocar errores y revisar qué información entrega al cliente. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | Acciones con datos corruptos (exportar sin parámetros, `action=inexistente`, `action=buscar` sin id). |
| **Propósito** | Verificar que no se filtran trazas PHP, rutas del servidor ni credenciales. |
| **Prerrequisitos** | DevTools; revisión de respuestas. |
| **Pasos** | 1. Invocar `action=noExiste` en varios módulos (debe responder "Acción no válida").<br>2. Invocar acciones sin parámetros obligatorios.<br>3. Revisar el cuerpo de las respuestas y los logs. |
| **Resultado Esperado** | Respuestas JSON de error genérico (`Error interno del servidor`, `Acción no válida`, `Error de base de datos`) sin stack traces, rutas del filesystem ni credenciales. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-115: Protección de datos sensibles en el cliente

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Revisar que no se expongan hashes, tokens o datos de otros usuarios. |
| **Caso de Uso** | Ninguno (prueba técnica). |
| **Datos de Prueba** | Respuestas JSON de `listar`/`detalle` en Usuarios, Clientes, Reportes; código fuente de las vistas. |
| **Propósito** | Confirmar que `password_hash`, `csrf_token` de terceros y listados sensibles no viajan al navegador indebidamente. |
| **Prerrequisitos** | Sesión iniciada; DevTools (pestaña Network). |
| **Pasos** | 1. Inspeccionar los JSON de `usuarios&action=listar` y `detalle`.<br>2. Revisar el HTML fuente de las páginas (buscar hashes y tokens ajenos).<br>3. Revisar los logs y respuestas de error. |
| **Resultado Esperado** | No aparecen `password_hash` ni tokens de otras sesiones en las respuestas; el `csrf_token` visible corresponde solo a la sesión actual. |
| **Resultado Obtenido** | Pendiente de ejecución. |

### 5.18 Integración de Módulos (CP-116 – CP-121)


### Caso de Prueba CP-116: Flujo completo de venta (Cliente → POS → Inventario → Dashboard → Reportes)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Ejecutar el ciclo de venta extremo a extremo. |
| **Caso de Uso** | Registrar venta / Consultar reportes. |
| **Datos de Prueba** | Cliente nuevo `V-55556666`, productos `TEC-001` y `MOU-001`. |
| **Propósito** | Verificar la trazabilidad del dato entre los cinco módulos. |
| **Prerrequisitos** | Anotar stocks, KPIs del dashboard y ventas del día antes de empezar. |
| **Pasos** | 1. Crear el cliente en Clientes (CP-35).<br>2. Registrar la venta en POS usando ese cliente (CP-30).<br>3. Verificar el stock en Inventario (CP-19).<br>4. Verificar Dashboard (CP-11) y Reporte de ventas (CP-84).<br>5. Verificar que el detalle de la venta muestra el cliente creado. |
| **Resultado Esperado** | Un solo conjunto de datos es visible y consistente en los cinco módulos (mismo cliente, mismos totales, mismo stock descontado). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-117: Flujo de abastecimiento (Proveedores → Solicitudes)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Relacionar proveedor con órdenes y verificar su efecto en KPIs y reportes. |
| **Caso de Uso** | Gestionar proveedores / Gestionar solicitudes. |
| **Datos de Prueba** | Proveedor `J-99998888-1` (CP-45) y orden CP-40 cambiando de estado hasta `Recibido Completo`. |
| **Propósito** | Validar la integración catálogo de proveedores → órdenes → reportes de proveedores. |
| **Prerrequisitos** | Ejecutados CP-40 y CP-45. |
| **Pasos** | 1. Crear una orden usando el proveedor nuevo.<br>2. Cambiar su estado hasta `Recibido Completo` (CP-42).<br>3. Verificar los KPIs de Solicitudes (CP-44).<br>4. Generar el reporte `proveedores` (CP-86) y localizar la orden.<br>5. Intentar eliminar el proveedor ahora (CP-47). |
| **Resultado Esperado** | La orden aparece ligada al proveedor en el listado y el reporte; los KPIs reflejan el cambio de estado; el proveedor ya no se puede eliminar (RESTRICT). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-118: Flujo de asesoría (Clientes → Asesoría Legal)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Vincular un cliente y una asesoría y verificar ambas vistas. |
| **Caso de Uso** | Gestionar clientes / Gestionar asesorías. |
| **Datos de Prueba** | Cliente `V-77778888` + asesoría tipo `Asesoría Penal` (Denegado). |
| **Propósito** | Verificar `cliente_asesoria` y la coherencia cliente ↔ asesoría. |
| **Prerrequisitos** | Ejecutados CP-35 y CP-64. |
| **Pasos** | 1. Crear el cliente.<br>2. Registrar la asesoría de tipo Penal.<br>3. Buscar la asesoría por la cédula del cliente.<br>4. Intentar eliminar el cliente con asesoría (CP-39 intento B).<br>5. Verificar el KPI de asesorías denegadas. |
| **Resultado Esperado** | La asesoría se vincula al cliente, se encuentra por cédula, el cliente no se puede eliminar y el badge muestra **Denegado**. |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-119: Flujo de ciber (Activos → Sesiones → Reportes)

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Relacionar PCs del ciber con sesiones y su reporte. |
| **Caso de Uso** | Gestionar PCs / Iniciar sesión de ciber / Generar reportes. |
| **Datos de Prueba** | PC creada en CP-55, sesiones CP-48/CP-52, reporte `cyber`. |
| **Propósito** | Validar la cadena activo (PC) → `sesion_ciber` → estadísticas → reporte. |
| **Prerrequisitos** | Ejecutados CP-48, CP-52 y CP-55. |
| **Pasos** | 1. Iniciar y finalizar una sesión en la PC nueva.<br>2. Verificar la sesión en el historial (CP-57).<br>3. Verificar estadísticas (CP-54) y dashboard (CP-09).<br>4. Generar el reporte `cyber` y localizar la sesión.<br>5. Intentar eliminar la PC con sesión registrada (CP-56). |
| **Resultado Esperado** | La sesión aparece en historial, estadísticas, dashboard y reporte con los mismos datos; la PC con sesiones no se elimina (o el sistema informa la restricción). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-120: Flujo de usuarios y roles → acceso efectivo

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Crear usuario, asignarle rol/permisos y comprobar su experiencia de acceso. |
| **Caso de Uso** | Gestionar usuarios / Gestionar roles. |
| **Datos de Prueba** | Usuario `cp_int` con rol `Almacenista` (permisos `gestion_productos`, `gestion_abastecimiento`, `ver_reportes`). |
| **Propósito** | Integrar CP-71, CP-79, CP-81 y CP-110. |
| **Prerrequisitos** | Rol `Almacenista` con su matriz de permisos intacta. |
| **Pasos** | 1. Crear `cp_int` con clave `1234`.<br>2. Asignarle el rol `Almacenista`.<br>3. Verificar los permisos del rol (CP-79).<br>4. Iniciar sesión con `cp_int` e intentar acceder a los módulos (CP-110).<br>5. Observar si el menú lateral muestra o oculta módulos. |
| **Resultado Esperado** | El usuario opera según su rol; el menú refleja los permisos; registrar si el acceso directo por URL a módulos no permitidos está restringido (H-02). |
| **Resultado Obtenido** | Pendiente de ejecución. |


### Caso de Prueba CP-121: Regresión integral post-implementación

| Campo | Contenido |
| :--- | :--- |
| **Caso de Prueba** | Recorrer los módulos principales en una pasada final de humo (smoke test). |
| **Caso de Uso** | Todos. |
| **Datos de Prueba** | Datos resultantes de todas las pruebas anteriores. |
| **Propósito** | Detectar regresiones tras la ejecución completa del plan. |
| **Prerrequisitos** | Grupos 5.1 a 5.18 ejecutados; hallazgos del Anexo A registrados. |
| **Pasos** | 1. Login y recorrido rápido por los 12 módulos del menú (CP-95).<br>2. Una venta puntual en POS y una consulta de reporte con exportación CSV.<br>3. Revisar consola del navegador y logs de la aplicación.<br>4. Reintentar cada hallazgo del Anexo A para confirmar su estado. |
| **Resultado Esperado** | Sin errores nuevos en consola ni en logs; las operaciones básicas funcionan; los hallazgos del Anexo A quedan confirmados o corregidos. |
| **Resultado Obtenido** | Pendiente de ejecución. |

---

## 6. Test de Usabilidad para el Usuario Final / Tutor
Cuestionario de evaluación cualitativa aplicado durante las pruebas de interfaz:
1. ¿Le parece adecuada la selección de contenidos presentes en el menú principal?
2. Al hacer clic en un contenido del menú, ¿halló en la información ofrecida lo que esperaba encontrar?
3. En general, ¿cree que los textos introductorios, títulos y enlaces son claros y concisos? ¿Le costó leerlos?
4. ¿El sistema contiene los colores institucionales pertinentes?
5. ¿El sistema está sobrecargado de colores?

**Aplicación:** se responde durante la ejecución de CP-95 a CP-99 con el usuario final y el tutor, registrando observaciones en el Anexo A o en un anexo aparte de respuestas.

---

## 7. Análisis e Informe de Resultados de las Pruebas

Sección final para resumir los hallazgos en cada una de las dimensiones evaluadas (se diligencia al cierre de la ejecución del plan):

- **Módulos Individuales:** Evaluación de las funciones CRUD (adicionar, modificar, consultar, eliminar y generar reportes). *Grupos 5.3 a 5.13.*
- **Integración de Módulos:** Verificación del flujo completo entre componentes del sistema. *Grupo 5.18 (CP-116 a CP-121).*
- **Validación de Requisitos:** Comprobación con datos reales y validación de reglas de negocio (ej. prevención de cédulas duplicadas). *CP-13, CP-14, CP-36, CP-37, CP-50, CP-60.*
- **Base de Datos:** Pruebas de transferencia, llenado de tablas, actualización y borrado. *Grupo 5.16 (CP-100 a CP-107).*
- **Seguridad del Sistema:** Verificación de permisos, perfiles y autenticación. *Grupos 5.1, 5.17.*
- **Pruebas en la Web:** Comprobación de la correcta integración y extracción de datos hacia el sitio web. *Grupo 5.14 (PWA/Offline) y CP-87 a CP-89 (exportaciones).*

---

## Anexo A. Hallazgos de la Revisión Previa del Código

Hallazgos detectados mediante revisión estática del código antes de la ejecución; deben confirmarse o descartarse durante las pruebas y reclasificarse por severidad al cierre.

| ID | Hallazgo | Ubicación | Casos de prueba relacionados |
| :--- | :--- | :--- | :--- |
| H-01 | El enlace "Cerrar Sesión" del menú lateral apunta a `?pagina=login` **sin** el parámetro `logout`: muestra la pantalla de login pero la sesión sigue viva (el usuario cree que cerró sesión). El cierre real solo ocurre en `?pagina=login&logout=1`. | `src/app/Views/template/layout.php` (menú lateral) vs `src/app/core/router.php:98-104` | CP-06, CP-113 |
| H-02 | `$_SESSION['permisos']` se carga en el login pero **nunca se evalúa** en el enrutador ni en los controladores: cualquier usuario autenticado puede navegar e invocar acciones de todos los módulos solo con conocer la URL; el menú lateral es el único límite visible. | `AuthController::login()` (`src/app/Controllers/AuthController.php:85-86`), `Router::handle()` (`src/app/core/router.php:107-116`) | CP-110, CP-120 |
| H-03 | La vista `src/app/Views/acceso_denegado.php` existe pero no es referenciada por el enrutador ni por las vistas: archivo huérfano, no se usa en ningún flujo de denegación. | `src/app/Views/acceso_denegado.php` | CP-110 |
| H-04 | `CiberController` invoca `Validator::requeridos()` sin la sentencia `use App\Core\Validator`; si PHP resuelve `Validator` en el namespace del controlador se produce "class not found" **no capturada** por los `catch` declarados (respuesta con error 500 o fatal) en las acciones que validan con esa clase (p. ej. creación/edición de PC). | `src/app/Controllers/CiberController.php` | CP-55, CP-56, CP-114 |
| H-05 | `finalizarSesion()` solo marca la sesión como `finalizada = 1` sin recalcular ni persistir el tiempo real de uso ni el costo (no se actualizan `tiempo_uso` ni el cobro derivado de la tarifa). | `src/app/Controllers/CiberController.php` (`finalizarSesion`) | CP-52, CP-54, CP-119 |
| H-06 | No existe máquina de estados para las órdenes de abastecimiento: el sistema acepta cualquier transición de `status_seguimiento` (p. ej. de `Cancelado` a `Pendiente` o de `Recibido Completo` a `Pendiente`) sin validar coherencia del ciclo de vida. | `src/app/Controllers/ProveedorController.php` (`actualizar`), modelo de órdenes | CP-42, CP-44 |
| H-07 | `lineas_venta.fk_producto` y `lineas_abastecimiento.fk_producto` usan `ON DELETE CASCADE`: eliminar un producto con ventas asociadas **borra el detalle histórico** de esas ventas (los totales de la orden y los reportes quedan inconsistentes). No hay protección en `InventarioController::eliminar`. | `src/app/Database/estructura.sql:196-201` e `InventarioController::eliminar` | CP-20, CP-104 |
| H-08 | No existen acciones de entrada/salida de almacén en Inventario: los movimientos de stock solo pueden realizarse editando el producto. Cualquier expectativa de módulo de movimientos (auditoría de quién/qué/cuándo) queda sin cubrir; la tabla de movimientos del reporte `movimientos` debe verificarse contra su implementación real. | `src/app/Controllers/InventarioController.php` (acciones `listar`, `kpis`, `categorias`, `detalle`, `buscar`, `crear`, `actualizar`, `eliminar`, `crearCategoria`, `actualizarCategoria`, `eliminarCategoria`) | CP-19, CP-86 |

**Criterio de cierre:** al finalizar la ejecución, cada hallazgo se marca como *Confirmado*, *Corregido* o *Descartado*, con fecha y responsable, y se anexa a la Sección 7.

