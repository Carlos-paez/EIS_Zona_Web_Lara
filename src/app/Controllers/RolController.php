<?php
// =============================================================================
// CONTROLADOR RolController (API JSON para roles y permisos)
// =============================================================================
// Propósito: Manejar las peticiones AJAX del módulo de roles y permisos.
//            Responde siempre en formato JSON. Permite listar, crear, editar,
//            eliminar roles, gestionar permisos y asignar roles a usuarios.
// =============================================================================

// Declara el espacio de nombres al que pertenece esta clase, siguiendo la estructura PSR-4
namespace App\Controllers;

// Importa el modelo Rol para acceder a los datos de roles, permisos y usuarios
use App\Models\Rol;

/**
 * Controlador de roles y permisos (API JSON)
 * 
 * Maneja todas las peticiones AJAX del módulo de roles y permisos.
 * Proporciona acciones CRUD para roles, gestión de permisos por rol,
 * y asignación de roles a usuarios del sistema.
 * Todas las respuestas se devuelven en formato JSON.
 */
class RolController
{
    /**
     * Instancia del modelo Rol
     * 
     * Almacena el objeto del modelo que proporciona los métodos
     * para consultar y modificar roles, permisos y asignaciones
     * en la base de datos.
     */
    private Rol $model;

    /**
     * Constructor de la clase RolController
     * 
     * Inicializa la propiedad $model creando una nueva instancia
     * del modelo Rol para acceder a los datos de roles y permisos.
     */
    public function __construct()
    {
        // Crea una nueva instancia del modelo Rol y la asigna a la propiedad $model
        $this->model = new Rol();
    }

    /**
     * Método principal que despacha las acciones según el parámetro GET 'action'
     * 
     * Establece el encabezado Content-Type como application/json para todas las respuestas.
     * Lee el parámetro 'action' de la URL y utiliza la estructura match() de PHP 8
     * para ejecutar el método correspondiente (listar, crear, permisos, etc.).
     * Captura excepciones de base de datos o errores genéricos para respuestas uniformes.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    public function handle(): void
    {
        // Establece el encabezado HTTP Content-Type para indicar que la respuesta será JSON
        header('Content-Type: application/json');

        // Lee el parámetro GET 'action' de la URL, o cadena vacía si no está presente
        $action = $_GET['action'] ?? '';

        // Bloque try-catch para manejar errores de forma controlada
        try {
            // Utiliza match() de PHP 8 para seleccionar el método según la acción solicitada
            match ($action) {
                // Lista todos los roles registrados en el sistema
                'listar'       => $this->listar(),
                // Obtiene el detalle de un rol específico por su ID
                'detalle'      => $this->detalle(),
                // Crea un nuevo rol con el nombre proporcionado
                'crear'        => $this->crear(),
                // Actualiza el nombre de un rol existente
                'actualizar'   => $this->actualizar(),
                // Elimina un rol del sistema (protege el rol Administrador con ID 1)
                'eliminar'     => $this->eliminar(),
                // Obtiene todos los permisos disponibles en el sistema
                'permisos'     => $this->permisos(),
                // Obtiene los permisos asignados a un rol específico
                'permisosRol'  => $this->permisosRol(),
                // Guarda los permisos seleccionados para un rol
                'guardarPermisos' => $this->guardarPermisos(),
                // Obtiene la lista de usuarios y roles disponibles para asignación
                'usuarios'     => $this->usuarios(),
                // Asigna un rol a un usuario específico
                'asignarRol'   => $this->asignarRol(),
                // Si la acción no coincide con ninguna, devuelve error JSON
                default        => $this->json(false, null, 'Acción no válida'),
            };
        } catch (\PDOException $e) {
            // Captura excepciones de PDO (errores de base de datos) y devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            // Captura cualquier otra excepción genérica y devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Lista todos los roles del sistema
     * 
     * Obtiene el listado completo de roles desde el modelo
     * y los devuelve en una respuesta JSON.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function listar(): void
    {
        // Llama al método listarRoles() del modelo para obtener todos los roles
        $roles = $this->model->listarRoles();
        // Devuelve un JSON con indicador de éxito y el arreglo de roles en 'data'
        echo json_encode(['success' => true, 'data' => $roles]);
    }

    /**
     * Obtiene el detalle de un rol por su ID
     * 
     * Lee el parámetro GET 'id', valida que sea un entero positivo,
     * consulta el rol en el modelo y devuelve sus datos.
     * Si no se encuentra, retorna un mensaje de error.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function detalle(): void
    {
        // Lee el parámetro GET 'id' y lo convierte a entero; si no existe, usa 0
        $id = (int)($_GET['id'] ?? 0);
        // Verifica si el ID es válido (distinto de cero)
        if (!$id) {
            // Si el ID no es válido, devuelve un JSON con mensaje de error
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            // Detiene la ejecución del método
            return;
        }
        // Llama al método obtenerRolPorId() del modelo para buscar el rol por su ID
        $rol = $this->model->obtenerRolPorId($id);
        // Verifica si se encontró un rol con el ID proporcionado
        if ($rol) {
            // Si existe, devuelve un JSON con éxito y los datos del rol
            echo json_encode(['success' => true, 'data' => $rol]);
        } else {
            // Si no existe, devuelve un JSON con mensaje de error
            echo json_encode(['success' => false, 'error' => 'Rol no encontrado']);
        }
    }

    /**
     * Crea un nuevo rol en el sistema
     * 
     * Lee el nombre del rol desde POST, valida que no esté vacío,
     * y llama al modelo para insertarlo en la base de datos.
     * El modelo verifica posibles duplicados de nombre.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function crear(): void
    {
        // Lee el nombre del rol enviado por POST, o cadena vacía si no existe
        $nombre_rol = $_POST['nombre'] ?? '';

        // Valida que el nombre del rol no esté vacío
        if (empty($nombre_rol)) {
            // Si el nombre está vacío, devuelve un JSON con mensaje de error
            echo json_encode(['success' => false, 'error' => 'El nombre del rol es obligatorio']);
            // Detiene la ejecución del método
            return;
        }

        // Llama al método crearRol() del modelo pasando el nombre del rol
        // Devuelve true si la inserción fue exitosa, false en caso contrario
        $resultado = $this->model->crearRol($nombre_rol);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Rol creado exitosamente']
                // Si el resultado es false, mensaje de error (posible nombre duplicado)
                : ['success' => false, 'error' => 'Error al crear el rol (posible nombre duplicado)']
        );
    }

    /**
     * Actualiza el nombre de un rol existente
     * 
     * Lee el ID y el nuevo nombre del rol desde POST,
     * valida que ambos campos estén presentes y llama
     * al modelo para actualizar el registro.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function actualizar(): void
    {
        // Lee el ID del rol y lo convierte a entero; si no existe, usa 0
        $id = (int)($_POST['id'] ?? 0);
        // Lee el nuevo nombre del rol desde POST
        $nombre_rol = $_POST['nombre'] ?? '';

        // Valida que el ID sea válido y el nombre no esté vacío
        if (!$id || empty($nombre_rol)) {
            // Si falta algún campo obligatorio, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'Complete todos los campos obligatorios']);
            // Detiene la ejecución del método
            return;
        }

        // Llama al método actualizarRol() del modelo con el ID y el nuevo nombre
        $resultado = $this->model->actualizarRol($id, $nombre_rol);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Rol actualizado exitosamente']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al actualizar el rol']
        );
    }

    /**
     * Elimina un rol del sistema
     * 
     * Lee el ID del rol desde POST, valida que sea un número positivo,
     * protege el rol de Administrador (ID 1) de ser eliminado,
     * y llama al modelo para eliminar el registro. El modelo
     * verifica que ningún usuario tenga asignado ese rol.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function eliminar(): void
    {
        // Lee el ID del rol y lo convierte a entero; si no existe, usa 0
        $id = (int)($_POST['id'] ?? 0);
        // Verifica si el ID es válido (distinto de cero)
        if (!$id) {
            // Si el ID no es válido, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'ID no válido']);
            // Detiene la ejecución del método
            return;
        }
        // Verifica si el ID es 1 (rol de Administrador) para evitar su eliminación
        if ($id === 1) {
            // No permite eliminar el rol de Administrador por seguridad
            echo json_encode(['success' => false, 'error' => 'No se puede eliminar el rol de Administrador']);
            // Detiene la ejecución del método
            return;
        }
        // Llama al método eliminarRol() del modelo pasando el ID del rol
        $resultado = $this->model->eliminarRol($id);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Rol eliminado exitosamente']
                // Si es false, el modelo rechazó la operación (tiene usuarios asignados)
                : ['success' => false, 'error' => 'No se puede eliminar el rol porque tiene usuarios asignados']
        );
    }

    /**
     * Obtiene todos los permisos disponibles en el sistema
     * 
     * Consulta al modelo la lista completa de permisos
     * definidos y los devuelve en formato JSON.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function permisos(): void
    {
        // Llama al método obtenerPermisos() del modelo para obtener todos los permisos
        $permisos = $this->model->obtenerPermisos();
        // Devuelve un JSON con indicador de éxito y el arreglo de permisos en 'data'
        echo json_encode(['success' => true, 'data' => $permisos]);
    }

    /**
     * Obtiene los permisos asignados a un rol específico
     * 
     * Lee el ID del rol desde GET, valida que sea válido,
     * y consulta al modelo los permisos asociados a ese rol.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function permisosRol(): void
    {
        // Lee el ID del rol desde GET y lo convierte a entero; si no existe, usa 0
        $rol_id = (int)($_GET['rol_id'] ?? 0);
        // Verifica si el ID de rol es válido (distinto de cero)
        if (!$rol_id) {
            // Si el ID no es válido, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'ID de rol no válido']);
            // Detiene la ejecución del método
            return;
        }
        // Llama al método obtenerPermisosPorRol() del modelo para obtener los permisos del rol
        $permisos = $this->model->obtenerPermisosPorRol($rol_id);
        // Devuelve un JSON con éxito y el arreglo de permisos del rol
        echo json_encode(['success' => true, 'data' => $permisos]);
    }

    /**
     * Guarda los permisos asignados a un rol
     * 
     * Lee el ID del rol y el arreglo de IDs de permisos desde POST.
     * Convierte cada ID de permiso a entero para sanitización.
     * Valida el ID del rol y llama al modelo para guardar la asignación.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function guardarPermisos(): void
    {
        // Lee el ID del rol desde POST y lo convierte a entero; si no existe, usa 0
        $rol_id = (int)($_POST['rol_id'] ?? 0);
        // Lee el arreglo de IDs de permisos desde POST; si no existe, asigna un arreglo vacío
        $permiso_ids = isset($_POST['permisos']) ? (array)$_POST['permisos'] : [];

        // Convierte cada elemento del arreglo a entero usando array_map con la función intval
        $permiso_ids = array_map('intval', $permiso_ids);

        // Verifica si el ID de rol es válido
        if (!$rol_id) {
            // Si no es válido, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'ID de rol no válido']);
            // Detiene la ejecución del método
            return;
        }

        // Llama al método guardarPermisosRol() del modelo con el ID de rol y los permisos
        $resultado = $this->model->guardarPermisosRol($rol_id, $permiso_ids);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Permisos guardados exitosamente']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al guardar permisos']
        );
    }

    /**
     * Obtiene la lista de usuarios y roles para la asignación
     * 
     * Consulta al modelo todos los usuarios y todos los roles
     * disponibles, y los devuelve juntos en una misma respuesta
     * JSON para que el frontend pueda mostrar la interfaz de asignación.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function usuarios(): void
    {
        // Obtiene todos los usuarios del sistema desde el modelo
        $usuarios = $this->model->obtenerUsuarios();
        // Obtiene todos los roles disponibles desde el modelo
        $roles = $this->model->obtenerRoles();
        // Devuelve un JSON con ambos arreglos anidados en la clave 'data'
        echo json_encode([
            'success' => true,
            'data' => [
                // Lista de usuarios del sistema
                'usuarios' => $usuarios,
                // Lista de roles disponibles para asignar
                'roles' => $roles,
            ]
        ]);
    }

    /**
     * Asigna un rol a un usuario específico
     * 
     * Lee el ID del usuario y el ID del rol desde POST,
     * valida que ambos sean números positivos, y llama
     * al modelo para realizar la asignación en la base de datos.
     *
     * @return void Responde directamente con echo en formato JSON
     */
    private function asignarRol(): void
    {
        // Lee el ID del usuario desde POST y lo convierte a entero
        $usuario_id = (int)($_POST['usuario_id'] ?? 0);
        // Lee el ID del rol desde POST y lo convierte a entero
        $rol_id = (int)($_POST['rol_id'] ?? 0);

        // Verifica que ambos IDs sean válidos (distintos de cero)
        if (!$usuario_id || !$rol_id) {
            // Si algún ID no es válido, devuelve JSON con error
            echo json_encode(['success' => false, 'error' => 'Datos no válidos']);
            // Detiene la ejecución del método
            return;
        }

        // Llama al método asignarRolAUsuario() del modelo para registrar la asignación
        $resultado = $this->model->asignarRolAUsuario($usuario_id, $rol_id);
        // Evalúa el resultado y devuelve un JSON con mensaje de éxito o error
        echo json_encode(
            $resultado
                // Si el resultado es true, mensaje de éxito
                ? ['success' => true, 'message' => 'Rol asignado exitosamente']
                // Si el resultado es false, mensaje de error
                : ['success' => false, 'error' => 'Error al asignar rol']
        );
    }

    /**
     * Método auxiliar para construir respuestas JSON uniformes
     * 
     * Crea un arreglo asociativo con la estructura estándar de respuesta.
     * Siempre incluye la clave 'success'. Opcionalmente agrega las
     * claves 'data' y 'error' según los parámetros proporcionados.
     *
     * @param bool   $success Indica si la operación fue exitosa o no
     * @param mixed  $data    Datos opcionales a incluir en la respuesta (puede ser null)
     * @param string $error   Mensaje de error opcional (cadena vacía si no hay error)
     * 
     * @return void Responde directamente con echo en formato JSON
     */
    private function json(bool $success, mixed $data = null, string $error = ''): void
    {
        // Crea el arreglo base con el indicador de éxito
        $result = ['success' => $success];
        // Si hay datos, los agrega al arreglo bajo la clave 'data'
        if ($data !== null) $result['data'] = $data;
        // Si hay un mensaje de error, lo agrega bajo la clave 'error'
        if ($error) $result['error'] = $error;
        // Codifica el arreglo como JSON y lo envía como respuesta HTTP
        echo json_encode($result);
    }
}
