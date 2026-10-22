<?php
/**
 * Verificador de cobertura del mapa de permisos.
 *
 * Uso desde la terminal:
 *   php src/cli/verificar_permisos.php
 *
 * Comprueba que la matriz de autorización está completa y coherente:
 *
 * 1. Toda acción declarada en el `match ($action)` de cada controlador
 *    está mapeada en Permisos::MAPA_ACCIONES. Una acción sin mapear queda
 *    bloqueada (fail closed), pero sigue siendo un error de configuración.
 * 2. Toda entrada de MAPA_ACCIONES corresponde a una acción real.
 * 3. Todo verbo usado en MAPA_ACCIONES existe en el catálogo del módulo.
 * 4. Todo módulo del catálogo tiene su página declarada en
 *    MAPA_PAGINA_PERMISO y su verbo existe en la base de datos.
 *
 * Sale con código 0 si todo es correcto y con código 1 si hay errores.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Permisos;
use App\Core\Router;

$errores   = [];
$avisos    = [];

/**
 * Lee una constante privada del enrutador mediante reflexión.
 *
 * @param string $nombre Nombre de la constante.
 * @return array<string,string>
 */
function constanteDelRouter(string $nombre): array
{
    $reflexion = new ReflectionClass(Router::class);
    $constante = $reflexion->getConstant($nombre);

    return is_array($constante) ? $constante : [];
}

/**
 * Extrae las acciones del `match ($action)` de un controlador.
 *
 * @param string $archivo Ruta del fichero del controlador.
 * @return string[]
 */
function accionesDelControlador(string $archivo): array
{
    $contenido = (string)file_get_contents($archivo);

    if (!preg_match('/match\s*\(\s*\$action\s*\)/', $contenido, $m, PREG_OFFSET_CAPTURE)) {
        return [];
    }

    $inicio = $m[0][1] + strlen($m[0][0]);
    $cuerpo = substr($contenido, $inicio);

    // Se recorta el bloque del match: se corta en la primera llave de cierre
    // al mismo nivel que la de apertura del propio match.
    $profundidad = 0;
    $fin        = null;
    $longitud   = strlen($cuerpo);

    for ($i = 0; $i < $longitud; $i++) {
        $c = $cuerpo[$i];
        if ($c === '{') {
            $profundidad++;
        } elseif ($c === '}') {
            $profundidad--;
            if ($profundidad === 0) {
                $fin = $i;
                break;
            }
        }
    }

    $bloque = $fin === null ? $cuerpo : substr($cuerpo, 0, $fin);

    preg_match_all("/'([A-Za-z0-9_]+)'\s*=>/", $bloque, $coincidencias);

    return array_values(array_unique($coincidencias[1]));
}

// ---------------------------------------------------------------------------
// 1 y 2. Cobertura de acciones por controlador.
// ---------------------------------------------------------------------------
$rutaControladores = __DIR__ . '/../app/Controllers';

foreach (constanteDelRouter('CONTROLLERS') as $pagina => $clase) {
    $archivo = $rutaControladores . '/' . basename(str_replace('\\', '/', $clase)) . '.php';

    if (!is_file($archivo)) {
        $errores[] = "No se encuentra el fichero del controlador de la página '$pagina'.";
        continue;
    }

    $accionesReales    = accionesDelControlador($archivo);
    $accionesMapeadas = array_keys(Permisos::MAPA_ACCIONES[$pagina] ?? []);

    foreach ($accionesReales as $accion) {
        if (!in_array($accion, $accionesMapeadas, true)) {
            $errores[] = "Acción sin mapear: $pagina::$accion (controlador " . basename($archivo) . ").";
        }
    }

    foreach ($accionesMapeadas as $accion) {
        if (!in_array($accion, $accionesReales, true)) {
            $errores[] = "Acción mapeada sin implementación: $pagina::$accion (controlador " . basename($archivo) . ").";
        }
    }
}

// Cada página protegida del enrutador debe tener su página en el mapa.
foreach (array_keys(constanteDelRouter('PAGE_TITLES')) as $pagina) {
    if (!array_key_exists($pagina, Permisos::MAPA_PAGINA_PERMISO)) {
        $errores[] = "La página '$pagina' no está declarada en Permisos::MAPA_PAGINA_PERMISO.";
    }
}

// ---------------------------------------------------------------------------
// 3. Verbos coherentes con el catálogo.
// ---------------------------------------------------------------------------
foreach (Permisos::MAPA_ACCIONES as $pagina => $acciones) {
    $base = Permisos::permisoBase($pagina);

    if ($base === null) {
        continue;
    }

    $modulo = null;
    foreach (Permisos::CATALOGO as $clave => $datos) {
        if ($datos['base'] === $base) {
            $modulo = $clave;
            break;
        }
    }

    if ($modulo === null) {
        $errores[] = "La página $pagina usa el permiso base '$base', que no está en el catálogo.";
        continue;
    }

    foreach ($acciones as $accion => $verbo) {
        if ($verbo === null) {
            continue;
        }
        if (!isset(Permisos::CATALOGO[$modulo]['verbos'][$verbo])) {
            $errores[] = "El verbo '$verbo' de $pagina::$accion no existe en el catálogo del módulo '$modulo'.";
        }
    }
}

// ---------------------------------------------------------------------------
// 4. Páginas del catálogo declaradas en el mapa de páginas.
// ---------------------------------------------------------------------------
foreach (Permisos::CATALOGO as $modulo => $datos) {
    $declarada = false;
    foreach (Permisos::MAPA_PAGINA_PERMISO as $pagina => $base) {
        if ($base === $datos['base']) {
            $declarada = true;
            break;
        }
    }
    if (!$declarada) {
        $errores[] = "El módulo '$modulo' no tiene ninguna página asociada en MAPA_PAGINA_PERMISO.";
    }
}

// ---------------------------------------------------------------------------
// 5. Los permisos compuestos del catálogo existen en la base de datos.
// ---------------------------------------------------------------------------
try {
    $db   = App\Core\Database::getConnection();
    $stmt = $db->query("SELECT permisos FROM permisos");
    $existentes = array_column($stmt->fetchAll(), 'permisos');

    foreach (Permisos::CATALOGO as $modulo => $datos) {
        $requeridos = [$datos['base']];
        foreach (array_keys($datos['verbos']) as $verbo) {
            $requeridos[] = $datos['base'] . Permisos::PREFIJO_VERBO . $verbo;
        }

        foreach ($requeridos as $permiso) {
            if (!in_array($permiso, $existentes, true)) {
                $errores[] = "El permiso '$permiso' (módulo '$modulo') no existe en la tabla permisos. "
                    . "Ejecuta src/Database/migracion_v4_4_permisos.sql.";
            }
        }
    }
} catch (Throwable $e) {
    $avisos[] = 'No se pudo verificar la base de datos: ' . $e->getMessage();
}

// ---------------------------------------------------------------------------
// Resultado.
// ---------------------------------------------------------------------------
foreach ($avisos as $aviso) {
    echo "AVISO: $aviso\n";
}

if ($errores === []) {
    echo "OK: mapa de permisos completo y coherente.\n";
    exit(0);
}

foreach ($errores as $error) {
    echo "ERROR: $error\n";
}
echo "\n" . count($errores) . " error(es) encontrado(s).\n";
exit(1);
