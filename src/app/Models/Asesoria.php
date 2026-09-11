<?php
// Define el espacio de nombres (namespace) para organizar esta clase dentro de la arquitectura MVC.
// Permite que la clase sea cargada automáticamente según el estándar PSR-4 en App\Models.
namespace App\Models;

// Importa la clase base del modelo (Model) desde el núcleo de la aplicación (App\Core\Model),
// la cual provee métodos de validación, sanitización y la conexión a la base de datos ($this->db).
use App\Core\Model;

// Importa la extensión de objetos de datos de PHP (PDO) para ejecutar consultas preparadas y manejar transacciones.
use PDO;

/**
 * Clase Asesoria que extiende de Model.
 * Gestiona las asesorías legales/técnicas, incluyendo ciudadanos y tipos de asesoría.
 *
 * Encapsulación: todos los atributos son privados y se accede a ellos
 * únicamente mediante getters y setters.
 */
class Asesoria extends Model
{
    // =========================================================================
    // ATRIBUTOS PRIVADOS DE LA ENTIDAD
    // =========================================================================

    // Identificador único de la asesoría en la base de datos (Clave Primaria).
    private int $id = 0;

    // Cédula de identidad del ciudadano que solicita la asesoría.
    private string $cedula = '';

    // Nombre(s) del ciudadano.
    private string $nombre = '';

    // Apellido(s) del ciudadano.
    private string $apellido = '';

    // Nombre o título del documento/trámite asociado a la asesoría.
    private string $documento = '';

    // Descripción detallada de la solicitud de asesoría.
    private string $descripcion = '';

    // Fecha en la que se realizó o registró la asesoría (Formato YYYY-MM-DD).
    private string $fecha = '';

    // ID del registro relacional en la tabla `cliente_asesoria` (Clave Foránea).
    // Puede ser nulo si aún no está asignado.
    private ?int $fkClienteAsesoria = null;

    // ID del registro relacional en la tabla `tipo_asesoria` (Clave Foránea).
    // Puede ser nulo si no se especifica o no se encuentra el tipo.
    private ?int $fkTipoAsesoria = null;

    // Estado de la asesoría o tipo de documento (1 = Permitido, 0 = Denegado/Restringido).
    private int $permitido = 1;

    // =========================================================================
    // CONSTANTES DE VALIDACIÓN
    // Limitan las longitudes de los datos recibidos antes de insertarlos a la BD.
    // =========================================================================

    private const MIN_CEDULA      = 5;    // Longitud mínima aceptable para una cédula.
    private const MAX_CEDULA      = 20;   // Longitud máxima aceptable para una cédula.
    private const MIN_NOMBRE      = 2;    // Longitud mínima para el nombre.
    private const MAX_NOMBRE      = 100;  // Longitud máxima para el nombre.
    private const MAX_APELLIDO    = 100;  // Longitud máxima para el apellido.
    private const MAX_DOCUMENTO   = 100;  // Longitud máxima para el tipo de documento.
    private const MAX_DESCRIPCION = 1000; // Longitud máxima para el texto de descripción.

    // =========================================================================
    // MÉTODOS GETTERS Y SETTERS (ENCAPSULAMIENTO Y VALIDACIÓN)
    // =========================================================================

    /**
     * Obtiene el ID único de la asesoría.
     * @return int ID de la asesoría.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Establece el ID de la asesoría, pasándolo primero por sanitización de enteros.
     * @param int $id Identificador numérico.
     */
    public function setId(int $id): void
    {
        // Limpia el número convirtiéndolo formalmente a un entero seguro.
        $this->id = $this->sanitizeInt($id);
    }

    /**
     * Obtiene la cédula del ciudadano.
     * @return string Cédula.
     */
    public function getCedula(): string
    {
        return $this->cedula;
    }

    /**
     * Establece y valida la cédula de identidad según las reglas de negocio.
     * @param string $cedula Número o código de cédula.
     */
    public function setCedula(string $cedula): void
    {
        // 1. Elimina caracteres HTML o scripts maliciosos de la cadena.
        $cedula = $this->sanitizeString($cedula);
        
        // 2. Comprueba que el campo no llegue vacío.
        $this->validateNotEmpty($cedula, 'cédula');
        
        // 3. Valida que cumpla con la longitud mínima configurada (5 caracteres).
        $this->validateMinLength($cedula, 'cédula', self::MIN_CEDULA);
        
        // 4. Valida que no sobrepase la longitud máxima configurada (20 caracteres).
        $this->validateLength($cedula, 'cédula', self::MAX_CEDULA);
        
        // 5. Valida mediante Expresión Regular que tenga una estructura correcta de cédula
        // (letras, números, puntos, guiones, espacios, empezando y terminando en alfanumérico).
        $this->validatePattern($cedula, '/^[0-9A-Za-z][0-9A-Za-z.\-\s]{3,18}[0-9A-Za-z]$/', 'La cédula no tiene un formato válido');
        
        // Asigna el valor ya validado a la propiedad de la clase.
        $this->cedula = $cedula;
    }

    /**
     * Obtiene el nombre del ciudadano.
     * @return string Nombre.
     */
    public function getNombre(): string
    {
        return $this->nombre;
    }

    /**
     * Establece y valida el nombre del ciudadano.
     * @param string $nombre Nombre del ciudadano.
     */
    public function setNombre(string $nombre): void
    {
        // Sanitización previa contra Inyección XSS.
        $nombre = $this->sanitizeString($nombre);
        
        // Validaciones de campo requerido y rangos de caracteres.
        $this->validateNotEmpty($nombre, 'nombre');
        $this->validateMinLength($nombre, 'nombre', self::MIN_NOMBRE);
        $this->validateLength($nombre, 'nombre', self::MAX_NOMBRE);
        
        // Validación personalizada de texto libre (evita caracteres de control incompatibles).
        $this->validarLibre($nombre, 'nombre');
        
        // Asignación de la propiedad.
        $this->nombre = $nombre;
    }

    /**
     * Obtiene el apellido del ciudadano.
     * @return string Apellido.
     */
    public function getApellido(): string
    {
        return $this->apellido;
    }

    /**
     * Establece y valida el apellido del ciudadano (es opcional pero si viene se valida).
     * @param string $apellido Apellido del ciudadano.
     */
    public function setApellido(string $apellido): void
    {
        // Sanitiza la cadena.
        $apellido = $this->sanitizeString($apellido);
        
        // Verifica que no supere el límite máximo (100 caracteres).
        $this->validateLength($apellido, 'apellido', self::MAX_APELLIDO);
        
        // Valida la integridad del texto.
        $this->validarLibre($apellido, 'apellido');
        
        // Asigna el apellido a la propiedad.
        $this->apellido = $apellido;
    }

    /**
     * Obtiene el nombre del documento o trámite.
     * @return string Documento.
     */
    public function getDocumento(): string
    {
        return $this->documento;
    }

    /**
     * Establece y valida el tipo de documento o trámite de la asesoría.
     * @param string $documento Nombre del documento.
     */
    public function setDocumento(string $documento): void
    {
        // Sanitización.
        $documento = $this->sanitizeString($documento);
        
        // Validaciones: requerido, longitud máxima y caracteres permitidos.
        $this->validateNotEmpty($documento, 'tipo de documento');
        $this->validateLength($documento, 'tipo de documento', self::MAX_DOCUMENTO);
        $this->validarLibre($documento, 'tipo de documento');
        
        // Asignación final.
        $this->documento = $documento;
    }

    /**
     * Obtiene la descripción detallada de la asesoría.
     * @return string Descripción.
     */
    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    /**
     * Establece y valida la descripción larga del caso de asesoría.
     * @param string $descripcion Texto explicativo de la asesoría.
     */
    public function setDescripcion(string $descripcion): void
    {
        // Sanitización de caracteres maliciosos.
        $descripcion = $this->sanitizeString($descripcion);
        
        // Validaciones: no vacío y tope de 1000 caracteres.
        $this->validateNotEmpty($descripcion, 'descripción');
        $this->validateLength($descripcion, 'descripción', self::MAX_DESCRIPCION);
        
        // Asegura que no contenga caracteres de control ASCII no imprimibles.
        $this->validarSinControl($descripcion, 'descripción');
        
        // Asignación de la propiedad.
        $this->descripcion = $descripcion;
    }

    /**
     * Obtiene la fecha registrada para la asesoría.
     * @return string Fecha (YYYY-MM-DD).
     */
    public function getFecha(): string
    {
        return $this->fecha;
    }

    /**
     * Establece y valida el formato de la fecha.
     * @param string $fecha Cadena de fecha.
     */
    public function setFecha(string $fecha): void
    {
        // Ejecuta la validación interna del formato y asigna el resultado limpiado.
        $this->fecha = $this->validarFecha($fecha);
    }

    /**
     * Obtiene el ID del cliente asesorado (Foreign Key).
     * @return int|null Clave foránea o nulo.
     */
    public function getFkClienteAsesoria(): ?int
    {
        return $this->fkClienteAsesoria;
    }

    /**
     * Establece el ID relacional hacia la tabla `cliente_asesoria`.
     * @param int|null $fkClienteAsesoria ID relacional.
     */
    public function setFkClienteAsesoria(?int $fkClienteAsesoria): void
    {
        // Si no es nulo, lo pasa por la función sanitizadora de enteros; si es nulo, mantiene el null.
        $this->fkClienteAsesoria = $fkClienteAsesoria !== null ? $this->sanitizeInt($fkClienteAsesoria) : null;
    }

    /**
     * Obtiene el ID del tipo de asesoría (Foreign Key).
     * @return int|null Clave foránea o nulo.
     */
    public function getFkTipoAsesoria(): ?int
    {
        return $this->fkTipoAsesoria;
    }

    /**
     * Establece el ID relacional hacia la tabla `tipo_asesoria`.
     * @param int|null $fkTipoAsesoria ID relacional.
     */
    public function setFkTipoAsesoria(?int $fkTipoAsesoria): void
    {
        // Sanitiza la clave foránea numérica si no es nula.
        $this->fkTipoAsesoria = $fkTipoAsesoria !== null ? $this->sanitizeInt($fkTipoAsesoria) : null;
    }

    /**
     * Obtiene el estado de permiso asignado a la asesoría.
     * @return int 1 si es permitido, 0 si no.
     */
    public function getPermitido(): int
    {
        return $this->permitido;
    }

    /**
     * Establece el indicador numérico de permitido (Binario: 1 o 0).
     * @param int $permitido Valor numérico.
     */
    public function setPermitido(int $permitido): void
    {
        // Evalúa de forma estricta: si recibe exactamente 1 asigna 1, en cualquier otro caso asigna 0.
        $this->permitido = $permitido === 1 ? 1 : 0;
    }

    // =========================================================================
    // MÉTODOS DE CONVERSIÓN Y SERIALIZACIÓN DE DATOS
    // =========================================================================

    /**
     * Exporta todos los datos del objeto en forma de un arreglo asociativo.
     * Útil para responder con JSON a API endpoints o para depuración.
     * @return array Arreglo asociativo con los atributos de la asesoría.
     */
    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'cedula'              => $this->cedula,
            'nombre'              => $this->nombre,
            'apellido'            => $this->apellido,
            'documento'           => $this->documento,
            'descripcion'         => $this->descripcion,
            'fecha'               => $this->fecha,
            'fk_cliente_asesoria' => $this->fkClienteAsesoria,
            'fk_tipo_asesoria'    => $this->fkTipoAsesoria,
            'permitido'           => $this->permitido,
        ];
    }

    /**
     * Método de Factoría (Factory): Crea e hidrata una nueva instancia de Asesoria
     * a partir de los datos recibidos en un arreglo (por ejemplo, desde $_POST o una API).
     * 
     * @param array $data Arreglo asociativo de datos.
     * @return self Nueva instancia de la clase Asesoria.
     */
    public static function fromArray(array $data): self
    {
        // Crea una nueva instancia vacía de esta clase.
        $a = new self();

        // Mapea y pasa cada valor del arreglo por sus respectivos setters para ejecutar las validaciones.
        $a->setId((int)($data['id'] ?? 0));
        $a->setCedula($data['cedula'] ?? '');
        $a->setNombre($data['nombre'] ?? '');
        $a->setApellido($data['apellido'] ?? '');
        $a->setDocumento($data['documento'] ?? '');
        $a->setDescripcion($data['descripcion'] ?? '');
        $a->setFecha($data['fecha'] ?? '');
        
        // Verifica si la llave viene en el arreglo para convertir a entero o asignar null.
        $a->setFkClienteAsesoria(isset($data['fk_cliente_asesoria']) ? (int)$data['fk_cliente_asesoria'] : null);
        $a->setFkTipoAsesoria(isset($data['fk_tipo_asesoria']) ? (int)$data['fk_tipo_asesoria'] : null);
        $a->setPermitido((int)($data['permitido'] ?? 1));

        // Retorna la instancia de la clase completamente configurada.
        return $a;
    }

    // =========================================================================
    // MÉTODOS PRIVADOS AUXILIARES (LÓGICA INTERNA DE BASE DE DATOS)
    // =========================================================================

    /**
     * Obtiene o crea un cliente de asesoría basado en su cédula.
     * Delega la creación/actualización del registro en la tabla clientes
     * al modelo Cliente (validación y encapsulación centralizadas).
     * Luego busca o crea el registro en cliente_asesoria vinculado al cliente.
     *
     * @param string $cedula   Cédula de identidad del cliente.
     * @param string $nombre   Nombre del cliente.
     * @param string $apellido Apellido del cliente (opcional).
     * @param string $direccion Dirección del cliente (opcional).
     * @param string $telefono  Teléfono del cliente (opcional).
     * @return int             ID del registro en cliente_asesoria.
     */
    private function obtenerOcrearCliente(string $cedula, string $nombre, string $apellido = '', string $direccion = '', string $telefono = ''): int
    {
        // 1. Instancia el modelo Cliente para delegarle la búsqueda/creación del ciudadano en la tabla `clientes`.
        $cliente = new Cliente();
        
        // Llama al método de Cliente que maneja la persistencia y retorna la ID del cliente en la tabla general `clientes`.
        $cliente_id = $cliente->obtenerOCrearPorCedula($cedula, $nombre, $apellido, $direccion, $telefono);

        // 2. Prepara la consulta para verificar si este cliente ya tiene un registro intermedio en `cliente_asesoria`.
        $stmt = $this->db->prepare("SELECT id FROM cliente_asesoria WHERE fk_cliente = ?");
        // Asigna el ID del cliente obtenido al marcador posicional (1).
        $stmt->bindParam(1, $cliente_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Obtiene el resultado en modo arreglo asociativo.
        $ca = $stmt->fetch();

        // Si existe el registro en `cliente_asesoria`, retorna directamente su ID.
        if ($ca) {
            return (int)$ca['id'];
        }

        // Si no existe, inserta una nueva fila en `cliente_asesoria` asociándole el ID del cliente,
        // asignando valores predeterminados para email, RIF y tipo.
        $stmt = $this->db->prepare("INSERT INTO cliente_asesoria (fk_cliente, email, rif, tipo) VALUES (?, 'N/A', 'N/A', 'civil')");
        $stmt->bindParam(1, $cliente_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Devuelve el ID generado para la nueva fila insertada en `cliente_asesoria`.
        return (int)$this->db->lastInsertId();
    }

    /**
     * Obtiene el ID del tipo de asesoría comparando el nombre (case-insensitive).
     *
     * @param string $documento Nombre del tipo de documento/asesoría.
     * @return int|null         ID del tipo si existe, null si no se encuentra.
     */
    private function obtenerTipoAsesoria(string $documento): ?int
    {
        // Prepara una consulta SQL que pasa tanto la columna `tipo` como el parámetro a minúsculas
        // usando LOWER() de SQL para lograr un cotejo Insensible a Mayúsculas/Minúsculas.
        $stmt = $this->db->prepare("SELECT id FROM tipo_asesoria WHERE LOWER(tipo) = LOWER(?)");
        $stmt->bindParam(1, $documento, PDO::PARAM_STR);
        $stmt->execute();
        
        // Recupera la fila resultante.
        $tipo = $stmt->fetch();
        
        // Si lo encuentra retorna el ID convertido a entero, si la consulta fue vacía retorna null.
        return $tipo ? (int)$tipo['id'] : null;
    }

    // =========================================================================
    // MÉTODOS PÚBLICOS DEL CRUD (OPERACIONES PRINCIPALES DE LA BD)
    // =========================================================================

    /**
     * Crea una nueva asesoría en la base de datos.
     * Divide el nombre completo en nombre y apellido, busca o crea el cliente,
     * busca el tipo de asesoría y registra la asesoría con la fecha actual.
     *
     * @param string $ciudadano   Nombre completo del ciudadano (nombre y apellido separados por espacio).
     * @param string $cedula      Cédula del ciudadano.
     * @param string $documento   Tipo de documento/asesoría.
     * @param string $descripcion Descripción detallada de la asesoría.
     * @return bool              True si la inserción fue exitosa.
     */
    public function crear(string $ciudadano, string $cedula, string $documento, string $descripcion, string $direccion = '', string $telefono = ''): bool
    {
        // Separa la cadena de texto $ciudadano por espacios en blanco, creando un array con 2 elementos como máximo.
        // El primer elemento será el nombre y el resto se agrupará como apellido.
        $nombre_partes = explode(' ', $ciudadano, 2);

        // Encapsulación y validación automática: Asigna cada campo usando los métodos setter.
        $this->setNombre($nombre_partes[0]);
        $this->setApellido($nombre_partes[1] ?? ''); // Si no hay apellido, pasa una cadena vacía.
        $this->setCedula($cedula);
        $this->setDocumento($documento);
        $this->setDescripcion($descripcion);

        try {
            // Inicia una transacción de BD. Asegura que la inserción de cliente,
            // relación de asesoría y el registro de la asesoría ocurran de forma atómica.
            $this->db->beginTransaction();

            // Obtiene o registra el cliente en sus respectivas tablas y retorna la clave foránea.
            $fk_cliente = $this->obtenerOcrearCliente($this->cedula, $this->nombre, $this->apellido, $direccion, $telefono);
            
            // Busca el ID del tipo de asesoría según el tipo de documento ingresado.
            $fk_tipo_asesoria = $this->obtenerTipoAsesoria($this->documento);

            // Sentencia SQL preparada para insertar la nueva asesoría. Usamos la función nativa CURDATE() de la BD para la fecha.
            $sql = "INSERT INTO asesoria (documento, descripcion, fecha, fk_cliente_asesoria, fk_tipo_asesoria) VALUES (?, ?, CURDATE(), ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            // Asigna individualmente cada parámetro sanitizado a la sentencia.
            $stmt->bindParam(1, $this->documento, PDO::PARAM_STR);
            $stmt->bindParam(2, $this->descripcion, PDO::PARAM_STR);
            $stmt->bindParam(3, $fk_cliente, PDO::PARAM_INT);
            $stmt->bindParam(4, $fk_tipo_asesoria, PDO::PARAM_INT);
            
            // Ejecuta la sentencia SQL.
            $resultado = $stmt->execute();

            // Si todo fue correcto, confirma y consolida los cambios permanentemente en la base de datos.
            $this->db->commit();
            
            return $resultado;
        } catch (\Throwable $e) {
            // Si ocurre cualquier error o excepción, deshace absolutamente todos los cambios realizados en la transacción.
            $this->db->rollBack();
            // Re-lanza la excepción para que sea capturada por la capa del controlador o manejador de errores global.
            throw $e;
        }
    }

    /**
     * Obtiene todas las asesorías registradas, ordenadas por fecha descendente.
     *
     * @return array Lista de asesorías con datos del cliente y tipo.
     */
    public function obtenerTodas(): array
    {
        // Realiza una consulta multitabla con LEFT JOINs para consolidar la información
        // de la asesoría, del registro intermedio cliente_asesoria, de la tabla clientes y del catálogo tipo_asesoria.
        $stmt = $this->db->query("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   cli.cedula, cli.nombre AS ciudadano_nombre, cli.apellido AS ciudadano_apellido,
                   CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
            LEFT JOIN clientes cli ON ca.fk_cliente = cli.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            ORDER BY a.fecha DESC
        ");
        
        // Retorna todos los resultados mapeados como arreglos asociativos.
        return $stmt->fetchAll();
    }

    /**
     * Obtiene las asesorías filtradas por estado (Permitido/Denegado).
     *
     * @param string $estado 'Permitido' o 'Denegado'.
     * @return array  Lista de asesorías que coinciden con el estado.
     */
    public function obtenerPorEstado(string $estado): array
    {
        // Usa el setter para evaluar la palabra recibida y transformarla a 1 o 0 dentro del atributo $this->permitido.
        $this->setPermitido($estado === 'Permitido' ? 1 : 0);

        // Prepara la consulta filtrando mediante la cláusula WHERE por el valor del campo `permitido` en la tabla `tipo_asesoria`.
        $stmt = $this->db->prepare("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   cli.cedula, cli.nombre AS ciudadano_nombre, cli.apellido AS ciudadano_apellido,
                   CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
            LEFT JOIN clientes cli ON ca.fk_cliente = cli.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE ta.permitido = ?
            ORDER BY a.fecha DESC
        ");
        
        // Vincula el atributo $this->permitido (1 o 0) como parámetro de la consulta.
        $stmt->bindParam(1, $this->permitido, PDO::PARAM_INT);
        $stmt->execute();
        
        // Devuelve el listado de resultados filtrados.
        return $stmt->fetchAll();
    }

    /**
     * Obtiene una asesoría específica por su ID.
     *
     * @param int $id ID de la asesoría.
     * @return array|false Datos de la asesoría o false si no existe.
     */
    public function obtenerPorId(int $id): array|false
    {
        // Asigna y valida el ID introducido.
        $this->setId($id);

        // Prepara la consulta para buscar un registro específico por su Clave Primaria `a.id`.
        $stmt = $this->db->prepare("
            SELECT a.*, cli.cedula, cli.nombre AS ciudadano_nombre, cli.apellido AS ciudadano_apellido,
                   CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            LEFT JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
            LEFT JOIN clientes cli ON ca.fk_cliente = cli.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE a.id = ?
        ");
        
        // Asigna el ID numérico validado.
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Retorna un único arreglo asociativo con la asesoría hallada o `false` en caso de no encontrarse.
        return $stmt->fetch();
    }

    /**
     * Busca asesorías por cédula del ciudadano (búsqueda parcial con LIKE).
     *
     * @param string $cedula Cédula o parte de ella a buscar.
     * @return array  Lista de asesorías que coinciden.
     */
    public function buscarPorCedula(string $cedula): array
    {
        // Limpia posibles espacios o caracteres peligrosos de la cadena recibida.
        $cedula = $this->sanitizeString($cedula);

        // Prepara una consulta con INNER JOIN para consultar coincidencias en el campo `cedula` de la tabla `clientes`.
        $stmt = $this->db->prepare("
            SELECT a.id, a.documento, a.descripcion, a.fecha,
                   cli.cedula, cli.nombre AS ciudadano_nombre, cli.apellido AS ciudadano_apellido,
                   CONCAT(cli.nombre, ' ', cli.apellido) AS ciudadano,
                   ta.tipo AS tipo_documento, ta.permitido
            FROM asesoria a
            INNER JOIN cliente_asesoria ca ON a.fk_cliente_asesoria = ca.id
            INNER JOIN clientes cli ON ca.fk_cliente = cli.id
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            WHERE cli.cedula LIKE ?
            ORDER BY a.fecha DESC
        ");
        
        // Agrega comodines de porcentaje (%) antes y después del término de búsqueda para permitir coincidencia parcial.
        $patron = "%$cedula%";
        $stmt->bindParam(1, $patron, PDO::PARAM_STR);
        $stmt->execute();
        
        // Retorna las coincidencias encontradas.
        return $stmt->fetchAll();
    }

    /**
     * Actualiza el documento y descripción de una asesoría.
     * El tipo de asesoría se actualiza automáticamente según el nuevo documento.
     *
     * @param int    $id          ID de la asesoría.
     * @param string $documento   Nuevo tipo de documento.
     * @param string $descripcion Nueva descripción.
     * @return bool  True si la actualización fue exitosa.
     */
    public function actualizar(int $id, string $documento, string $descripcion): bool
    {
        // Asigna los parámetros a través de los setters con validaciones integradas.
        $this->setId($id);
        $this->setDocumento($documento);
        $this->setDescripcion($descripcion);

        // Intenta obtener el ID del nuevo tipo de asesoría según el nombre de documento suministrado.
        $fk_tipo_asesoria = $this->obtenerTipoAsesoria($this->documento);
        
        // Prepara la sentencia UPDATE. Usa COALESCE para que, si $fk_tipo_asesoria resulta NULL,
        // la BD conserve el valor previo que tenía el campo `fk_tipo_asesoria`.
        $sql = "UPDATE asesoria SET documento = ?, descripcion = ?, fk_tipo_asesoria = COALESCE(?, fk_tipo_asesoria) WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        // Vincula los valores validados a los marcadores posicionales.
        $stmt->bindParam(1, $this->documento, PDO::PARAM_STR);
        $stmt->bindParam(2, $this->descripcion, PDO::PARAM_STR);
        $stmt->bindParam(3, $fk_tipo_asesoria, PDO::PARAM_INT);
        $stmt->bindParam(4, $this->id, PDO::PARAM_INT);
        
        // Ejecuta y retorna true/false indicando si la actualización se ejecutó con éxito.
        return $stmt->execute();
    }

    /**
     * Elimina una asesoría por su ID.
     *
     * @param int $id ID de la asesoría a eliminar.
     * @return bool  True si la eliminación fue exitosa.
     */
    public function eliminar(int $id): bool
    {
        // Setea y valida el ID a eliminar.
        $this->setId($id);
        
        // Prepara la instrucción de eliminación según el ID de la asesoría.
        $stmt = $this->db->prepare("DELETE FROM asesoria WHERE id = ?");
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        
        // Ejecuta la consulta y retorna el resultado de la operación.
        return $stmt->execute();
    }

    /**
     * Cuenta las asesorías agrupadas por estado (Permitido / Denegado).
     *
     * @return array Arreglo con cada estado y su total.
     */
    public function contarPorEstado(): array
    {
        // Realiza una consulta con la estructura condicional CASE de SQL.
        // Evalúa el campo `permitido`: si vale 1 muestra la etiqueta 'Permitido', de lo contrario 'Denegado'.
        // Agrupa por `ta.permitido` y cuenta el total de filas por cada categoría.
        $stmt = $this->db->query("
            SELECT CASE WHEN ta.permitido = 1 THEN 'Permitido' ELSE 'Denegado' END AS estado, COUNT(*) AS total
            FROM asesoria a
            LEFT JOIN tipo_asesoria ta ON a.fk_tipo_asesoria = ta.id
            GROUP BY ta.permitido
        ");
        
        // Retorna el arreglo agrupado con los contadores numéricos (por ejemplo para dashboards o estadísticas).
        return $stmt->fetchAll();
    }

    // =========================================================================
    // MÉTODOS PRIVADOS DE VALIDACIÓN ESPECÍFICA
    // =========================================================================

    /**
     * Valida que una fecha tenga el formato YYYY-MM-DD (o esté vacía).
     *
     * @param string $fecha Fecha a validar.
     * @return string La fecha validada.
     */
    private function validarFecha(string $fecha): string
    {
        // Remueve espacios al inicio y al final de la cadena de fecha.
        $fecha = trim($fecha);
        
        // Si la cadena no está vacía, ejecuta la validación de fecha heredada de Model.
        if ($fecha !== '') {
            $this->validateFecha($fecha, 'fecha');
        }
        
        // Devuelve la fecha procesada.
        return $fecha;
    }
}