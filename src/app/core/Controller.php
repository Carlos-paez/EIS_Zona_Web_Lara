<?php
// ============================================================
// CLASE BASE CONTROLLER — CONTROLADOR ABSTRACTO
// ============================================================
// Esta clase es la base de todos los controladores de la
// aplicación. Proporciona métodos compartidos para:
//   - Renderizar vistas dentro del layout principal
//   - Renderizar páginas públicas (sin layout)
//   - Definir títulos de página dinámicos
//   - Definir contenido HTML extra para el encabezado
//
// Al ser abstracta, no puede instanciarse directamente.
// Todos los controladores concretos deben extender esta clase.

namespace App\Core;

abstract class Controller
{
    // ============================================
    // TÍTULOS DE PÁGINA
    // ============================================
    // Array asociativo que mapea cada nombre de página a su
    // título en español. Estos títulos se muestran en:
    //   - La barra de navegación superior
    //   - La etiqueta <title> del HTML
    //   - El elemento brand-logo en el layout
    protected array $pageTitles = [
        'dashboard'    => 'Panel de Control',
        'inventario'   => 'Gestión de Inventario',
        'ventas'       => 'Punto de Venta (POS)',
        'ciberControl' => 'Control de Cybercafé',
        'proveedores'  => 'Solicitudes a Proveedores',
        'reportes'     => 'Reportes y Estadísticas',
        'activos'      => 'Gestión de Activos',
        'asesorias'    => 'Asesoría Legal',
    ];

    // ============================================
    // ENCABEZADOS EXTRA
    // ============================================
    // Array asociativo para contenido HTML adicional que se
    // muestra en la barra de navegación superior de páginas
    // específicas. Por ejemplo, en ciberControl se muestran
    // chips con el conteo de estaciones disponibles/ocupadas.
    // Si una página no tiene entrada aquí, $headerExtra será ''.
    protected array $extraHeaders = [
        // Para la página ciberControl: dos chips de estado con
        // colores verde (disponibles) y naranja (ocupadas).
        // Estos valores son estáticos (UI prototype) y deberían
        // calcularse desde la base de datos en producción.
        'ciberControl' => '<span class="chip green white-text" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">5 Disponibles</span><span class="chip orange white-text" style="border-radius:4px;height:auto;padding:0.1rem 0.5rem;line-height:1.5;font-size:0.75rem;">4 Ocupadas</span>',
    ];

    // ============================================
    // PÁGINA ACTUAL
    // ============================================
    // Almacena el nombre de la página actual (el valor de
    // ?pagina=). Se usa en el layout para resaltar el elemento
    // activo del menú lateral (sidebar).
    protected string $currentPage;

    // ============================================
    // CONSTRUCTOR
    // ============================================
    // Se ejecuta automáticamente al crear una instancia de
    // cualquier controlador. Establece $currentPage con el
    // valor de ?pagina=, o 'dashboard' como valor por defecto.
    public function __construct()
    {
        // Leer el parámetro ?pagina= de la URL.
        // Si no existe, usar 'dashboard' como predeterminado.
        $this->currentPage = $_GET["pagina"] ?? 'dashboard';
    }

    // ============================================
    // MÉTODO RENDER — VISTA PROTEGIDA CON LAYOUT
    // ============================================
    // Renderiza una vista dentro del layout principal.
    // Las páginas protegidas (que requieren autenticación) usan
    // este método para mostrar su contenido envuelto en el layout
    // que incluye sidebar, navbar, scripts globales, etc.
    //
    // Parámetros:
    //   $viewPath (string): Ruta relativa desde Views/ sin extensión
    //                       Ej: 'dashboard/index' → Views/dashboard/index.php
    //   $data (array):      Datos a extraer como variables en la vista
    //                       Ej: ['productos' => [...]] → $productos disponible en la vista
    protected function render(string $viewPath, array $data = []): void
    {
        // Determinar el título de la página desde el array pageTitles.
        // Si no existe una entrada para la página actual, usar 'EIS System'.
        $pageTitle = $this->pageTitles[$this->currentPage] ?? 'EIS System';

        // Determinar el HTML extra para el encabezado.
        // Si no existe, será una cadena vacía.
        $headerExtra = $this->extraHeaders[$this->currentPage] ?? '';

        // Guardar el nombre de la página actual para que esté
        // disponible en el layout (usado para la clase 'active'
        // en los enlaces del menú lateral).
        $pagina = $this->currentPage;

        // Extraer las variables del array $data.
        // extract() crea variables PHP a partir de las claves del array.
        // Ej: ['nombre' => 'Juan'] → $nombre = 'Juan'
        // Esto permite que las vistas accedan a los datos directamente
        // como variables en lugar de $data['nombre'].
        extract($data);

        // Construir la ruta absoluta al archivo de vista.
        // __DIR__ es la ruta del directorio actual (src/app/Core).
        // /../Views/ sube un nivel y entra al directorio Views.
        // .php se añade al final para completar el nombre del archivo.
        $contentView = __DIR__ . '/../Views/' . $viewPath . '.php';

        // Incluir el layout principal.
        // El layout incluirá $contentView en su interior.
        // Las variables $pageTitle, $headerExtra, $pagina y $contentView
        // están disponibles dentro del layout.
        require __DIR__ . '/../Views/layouts/main.php';
    }

    // ============================================
    // MÉTODO RENDER PUBLIC — VISTA PÚBLICA SIN LAYOUT
    // ============================================
    // Renderiza una vista pública sin el layout principal.
    // Las páginas públicas (como login) tienen su propia estructura
    // HTML completa y no necesitan el sidebar ni la navbar.
    //
    // Parámetros:
    //   $viewPath (string): Ruta relativa desde Views/
    //   $data (array):      Datos a extraer como variables en la vista
    protected function renderPublic(string $viewPath, array $data = []): void
    {
        // Extraer las variables del array $data para que estén
        // disponibles en la vista como variables individuales.
        extract($data);

        // Incluir directamente el archivo de vista sin layout.
        // La vista debe contener su propia estructura HTML completa
        // (<!DOCTYPE html>, <html>, <head>, <body>, etc.).
        require __DIR__ . '/../Views/' . $viewPath . '.php';
    }
}
