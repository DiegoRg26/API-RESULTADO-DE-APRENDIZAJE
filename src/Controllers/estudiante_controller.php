<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\BaseController;
use Psr\Container\ContainerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Exception;

class estudiante_controller extends BaseController{

    private $jwtSecret;
    public function __construct(ContainerInterface $c){
        parent::__construct($c);
        $this->jwtSecret = $_ENV['JWT_SECRET'];
    }

    public function getEstudiantes(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $db = $this->container->get('db');
            //Obtiene los datos del usuario autenticado
            $userData = $this->getUserDataFromToken($request);
            $programa_id = $userData['programa_id'];
            
            //Valida si el usuario pertenece o no a un programa, en caso de que no, se obtiene todos los estudiantes de todos los programas
            if(!$programa_id){
                // Query para obtener todos los estudiantes con sus programas (agrupados por estudiante)
                $sql_get_estudiantes = "SELECT 
                                            e.id, 
                                            e.nombre, 
                                            e.email, 
                                            e.identificacion,
                                            GROUP_CONCAT(
                                                CONCAT(p.id, ':', p.nombre) 
                                                ORDER BY p.nombre 
                                                SEPARATOR '|'
                                            ) as programas_info
                                        FROM estudiante e
                                        JOIN relacion_programa_estudiante rpe ON e.id = rpe.estudiante_id
                                        JOIN programa p ON rpe.programa_id = p.id
                                        WHERE e.estado = 1
                                        GROUP BY e.id, e.nombre, e.email, e.identificacion
                                        ORDER BY e.nombre";
                $stmt = $db->prepare($sql_get_estudiantes);
            }else{
                // Query para obtener estudiantes de un programa específico con todos sus programas
                $sql_get_estudiantes = "SELECT DISTINCT
                                            e.id, 
                                            e.nombre, 
                                            e.email, 
                                            e.identificacion,
                                            GROUP_CONCAT(
                                                CONCAT(p2.id, ':', p2.nombre) 
                                                ORDER BY p2.nombre 
                                                SEPARATOR '|'
                                            ) as programas_info
                                        FROM estudiante e
                                        JOIN relacion_programa_estudiante rpe ON e.id = rpe.estudiante_id
                                        JOIN relacion_programa_estudiante rpe2 ON e.id = rpe2.estudiante_id
                                        JOIN programa p2 ON rpe2.programa_id = p2.id
                                        WHERE rpe.programa_id = :programa_id AND e.estado = 1
                                        GROUP BY e.id, e.nombre, e.email, e.identificacion
                                        ORDER BY e.nombre";
                $stmt = $db->prepare($sql_get_estudiantes);
                $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
            }
            
            if($stmt->execute()){
                $estudiantes_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if(count($estudiantes_raw) > 0){
                    // Procesar los datos para estructurar los programas
                    $estudiantes = [];
                    foreach($estudiantes_raw as $estudiante){
                        $programas = [];
                        if(!empty($estudiante['programas_info'])){
                            $programas_array = explode('|', $estudiante['programas_info']);
                            foreach($programas_array as $programa_info){
                                $parts = explode(':', $programa_info, 2);
                                if(count($parts) == 2){
                                    $programas[] = [
                                        'id' => (int)$parts[0],
                                        'nombre' => $parts[1]
                                    ];
                                }
                            }
                        }
                        
                        $estudiantes[] = [
                            'id' => (int)$estudiante['id'],
                            'nombre' => $estudiante['nombre'],
                            'email' => $estudiante['email'],
                            'identificacion' => $estudiante['identificacion'],
                            'programas' => $programas
                        ];
                    }
                    
                    return $this->successResponse($response, 'Estudiantes obtenidos correctamente', [
                        'estudiantes' => $estudiantes,
                        'total' => count($estudiantes)
                    ]);
                }else{
                    return $this->errorResponse($response, 'No se encontraron estudiantes', 404);
                }
            }else{
                return $this->errorResponse($response, 'Error al obtener los estudiantes', 500);
            }
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener los estudiantes: ' . $e->getMessage(), 500);
        }finally{
            if($stmt !== null){
                $stmt = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }

    public function agregarEstudiante(Request $request, Response $response, array $args): Response{
        $stmt = null;
        $db = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            
            $nombre = $this->sanitizeInput($inputData['nombre']);
            $email = $this->sanitizeInput($inputData['email']);
            $identificacion = $this->sanitizeInput($inputData['identificacion']);
    
            $userData = $this->getUserDataFromToken($request);
            $programa_id = $userData['programa_id'];
    
            if(!$programa_id){
                $programa_id = $inputData['programa_id'];
            }
            
            //Valida si el programa es requerido
            if(!$programa_id){
                return $this->errorResponse($response, 'El programa es requerido', 400);
            }
            
            $db->beginTransaction();
            
            // Verificar si el estudiante ya existe por identificación
            $sql_verificar_estudiante = "SELECT id FROM estudiante WHERE identificacion = :identificacion";
            $stmt = $db->prepare($sql_verificar_estudiante);
            $stmt->bindParam(':identificacion', $identificacion, PDO::PARAM_STR);
            $stmt->execute();
            
            $estudiante_existente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($estudiante_existente){
                $estudiante_id = $estudiante_existente['id'];
                
                // Verificar si ya existe la relación estudiante-programa
                $sql_verificar_relacion = "SELECT id FROM relacion_programa_estudiante 
                                            WHERE estudiante_id = :estudiante_id AND programa_id = :programa_id";
                $stmt = $db->prepare($sql_verificar_relacion);
                $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
                $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
                $stmt->execute();
                
                if($stmt->rowCount() > 0){
                    $db->rollBack();
                    return $this->errorResponse($response, 'El estudiante ya está asociado a este programa', 400);
                }
                
                // Insertar nueva relación estudiante-programa
                $sql_insertar_relacion = "INSERT INTO relacion_programa_estudiante (estudiante_id, programa_id) 
                                        VALUES (:estudiante_id, :programa_id)";
                $stmt = $db->prepare($sql_insertar_relacion);
                $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
                $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
                
                if($stmt->execute()){
                    $db->commit();
                    return $this->successResponse($response, 'Estudiante asociado al programa correctamente', [
                        'estudiante' => [
                            'id' => $estudiante_id,
                            'nombre' => $nombre,
                            'email' => $email,
                            'identificacion' => $identificacion,
                            'programa_id' => $programa_id,
                            'accion' => 'asociado_a_programa'
                        ]
                    ]);
                }else{
                    $db->rollBack();
                    return $this->errorResponse($response, 'Error al asociar el estudiante al programa', 500);
                }
            }else{
                // El estudiante no existe, crear nuevo estudiante
                $sql_insertar_estudiante = "INSERT INTO estudiante (nombre, email, identificacion) 
                                            VALUES (:nombre, :email, :identificacion)";
                $stmt = $db->prepare($sql_insertar_estudiante);
                $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':identificacion', $identificacion, PDO::PARAM_STR);
                
                if($stmt->execute()){
                    $estudiante_id = $db->lastInsertId();
                    
                    // Crear la relación estudiante-programa
                    $sql_insertar_relacion = "INSERT INTO relacion_programa_estudiante (estudiante_id, programa_id) 
                                            VALUES (:estudiante_id, :programa_id)";
                    $stmt = $db->prepare($sql_insertar_relacion);
                    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
                    $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
                    
                    if($stmt->execute()){
                        $db->commit();
                        return $this->successResponse($response, 'Estudiante creado y asociado al programa correctamente', [
                            'estudiante' => [
                                'id' => $estudiante_id,
                                'nombre' => $nombre,
                                'email' => $email,
                                'identificacion' => $identificacion,
                                'programa_id' => $programa_id,
                                'accion' => 'creado_y_asociado'
                            ]
                        ]);
                    }else{
                        $db->rollBack();
                        return $this->errorResponse($response, 'Error al asociar el estudiante al programa', 500);
                    }
                }else{
                    $db->rollBack();
                    return $this->errorResponse($response, 'Error al crear el estudiante', 500);
                }
            }
            
        }catch(Exception $e){
            if($db !== null){
                $db->rollBack();
            }
            return $this->errorResponse($response, 'Error al agregar el estudiante: ' . $e->getMessage(), 500);
        }finally{
            if($stmt !== null){
                $stmt = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }

    public function deshabilitarEstudiante(Request $request, Response $response, array $args): Response{
        $stmt = null;
        $db = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            $estudiante_id = $this->sanitizeInput($inputData['estudiante_id']);
            $estudiante_identificacion = $this->sanitizeInput($inputData['identificacion']);
            $sql_deshabilitar_estudiante = "UPDATE estudiante SET estado = 0 WHERE id = :estudiante_id AND  identificacion = :identificacion";
            $stmt = $db->prepare($sql_deshabilitar_estudiante);
            $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
            $stmt->bindParam(':identificacion', $estudiante_identificacion, PDO::PARAM_STR);
            if($stmt->execute() && $stmt->rowCount() > 0){
                return $this->successResponse($response, 'Estudiante deshabilitado correctamente', [
                    'estudiante' => [
                        'id' => $estudiante_id,
                        'identificacion' => $estudiante_identificacion
                    ]
                ]);
            }else{
                return $this->errorResponse($response, 'Error al deshabilitar el estudiante', 500);
            }
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al deshabilitar el estudiante: ' . $e->getMessage(), 500);
        }finally{
            if($stmt !== null){
                $stmt = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }

    public function habilitarEstudiante(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            $estudiante_id = $this->sanitizeInput($inputData['estudiante_id']);
            $estudiante_identificacion = $this->sanitizeInput($inputData['identificacion']);
            $sql_habilitar_estudiante = "UPDATE estudiante SET estado = 1 WHERE id = :estudiante_id AND  identificacion = :identificacion";
            $stmt = $db->prepare($sql_habilitar_estudiante);
            $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
            $stmt->bindParam(':identificacion', $estudiante_identificacion, PDO::PARAM_STR);
            if($stmt->execute() && $stmt->rowCount() > 0){
                return $this->successResponse($response, 'Estudiante habilitado correctamente', [
                    'estudiante' => [
                        'id' => $estudiante_id,
                        'identificacion' => $estudiante_identificacion
                    ]
                ]);
            }else{
                return $this->errorResponse($response, 'Error al habilitar el estudiante', 500);
            }
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al habilitar el estudiante: ' . $e->getMessage(), 500);
        }finally{
            if($stmt !== null){
                $stmt = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }

    public function getEstInfo(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $estudiante_id = $args['estudiante_id'];
            if($estudiante_id <= 0){return $this->errorResponse($response, 'Estudiante no encontrado', 404);}
            $db = $this->container->get('db');
            $sql_estudiante = "SELECT 
                                e.id,
                                e.nombre,
                                e.email,
                                e.identificacion,
                                p.nombre as programa_nombre
                            FROM 
                                estudiante e
                            JOIN 
                                programa p ON e.id_programa = p.id
                            WHERE 
                                e.id = :estudiante_id";
            $stmt = $db->prepare($sql_estudiante);
            $stmt -> bindParam(':estudiante_id', $estudiante_id);
            $stmt -> execute();
            $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$estudiante){return $this->errorResponse($response, 'Estudiante no encontrado', 404);}
            return $this->successResponse($response, 'Informacion del estudiante', $estudiante);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener la informacion del estudiante: ' . $e->getMessage(), 500);
        }finally{
            if($stmt !== null){$stmt = null;}
            if($db !== null){$db = null;}
        }
    }

    public function getCuestionariosAsignados(Request $request, Response $response, array $args): Response {
        $db = null;
        $stmt = null;
        try {
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $db = $this->container->get('db');
            
            // Obtener el ID del estudiante autenticado
            $estudiante_id = $this->getUserIdFromToken($request);

            if (!$estudiante_id) {
                return $this->errorResponse($response, 'Usuario no autenticado', 401);
            }
    
            $sql = "SELECT 
                        c.id AS cuestionario_id,
                        c.titulo,
                        c.descripcion,
                        c.tiempo_limite,
                        p.nombre AS periodo,
                        p.fecha_inicio,
                        p.fecha_fin,
                        a.id AS asignacion_id,
                        prog.id AS programa_id,
                        prog.nombre AS programa_nombre
                    FROM asignacion a
                    INNER JOIN apertura ap ON a.id_apertura = ap.id
                    INNER JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                    INNER JOIN periodo p ON ap.id_periodo = p.id
                    INNER JOIN programa prog ON rcp.id_programa = prog.id
                    WHERE a.id_estudiante = :estudiante_id
                    AND ap.activo = 1
                    AND p.fecha_inicio <= CURDATE()
                    AND p.fecha_fin >= CURDATE()
                    AND NOT EXISTS (
                        SELECT 1 FROM intento_cuestionario ic 
                        WHERE ic.id_estudiante = :estudiante_id 
                        AND ic.id_apertura = ap.id
                    )";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $cuestionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($cuestionarios) > 0) {
                    return $this->successResponse($response, 'Cuestionarios obtenidos correctamente', [
                        'cuestionarios' => $cuestionarios,
                        'total' => count($cuestionarios)
                    ]);
                } else {
                    return $this->errorResponse($response, 'No se encontraron cuestionarios asignados', 204);
                }
            } else {
                return $this->errorResponse($response, 'Error al obtener los cuestionarios', 500);
            }
        } catch (Exception $e) {
            return $this->errorResponse($response, 'Error al obtener los cuestionarios: ' . $e->getMessage(), 500);
        } finally {
            if ($stmt !== null) {
                $stmt = null;
            }
            if ($db !== null) {
                $db = null;
            }
        }
    }

    public function getCuestionariosCompletados(Request $request, Response $response, array $args): Response {
        $db = null;
        $stmt = null;
        try {
            $db = $this->container->get('db');
            // Obtener el ID del estudiante autenticado
            $estudiante_id = $this->getUserIdFromToken($request);

            if (!$estudiante_id) {
                return $this->errorResponse($response, 'Usuario no autenticado', 401);
            }
    
            $sql = "SELECT 
                        c.id AS cuestionario_id,
                        c.titulo,
                        c.descripcion,
                        c.tiempo_limite,
                        p.nombre AS periodo,
                        ic.fecha_inicio,
                        ic.fecha_fin,
                        ic.puntaje_total,
                        ic.completado,
                        a.id AS asignacion_id,
                        ap.id AS apertura_id,
                        prog.id AS programa_id,
                        prog.nombre AS programa_nombre
                    FROM intento_cuestionario ic
                    INNER JOIN apertura ap ON ic.id_apertura = ap.id
                    LEFT JOIN asignacion a ON a.id_estudiante = ic.id_estudiante AND a.id_apertura = ic.id_apertura
                    INNER JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                    INNER JOIN periodo p ON ap.id_periodo = p.id
                    INNER JOIN programa prog ON rcp.id_programa = prog.id
                    WHERE ic.id_estudiante = :estudiante_id
                    AND ic.completado = 1
                    ORDER BY ic.fecha_fin DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $cuestionarios_completados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($cuestionarios_completados) > 0) {
                    return $this->successResponse($response, 'Cuestionarios completados obtenidos correctamente', [
                        'cuestionarios_completados' => $cuestionarios_completados,
                        'total' => count($cuestionarios_completados)
                    ]);
                } else {
                    return $this->errorResponse($response, 'No se encontraron cuestionarios completados', 204);
                }
            } else {
                return $this->errorResponse($response, 'Error al obtener los cuestionarios completados', 500);
            }
        } catch (Exception $e) {
            return $this->errorResponse($response, 'Error al obtener los cuestionarios completados: ' . $e->getMessage(), 500);
        } finally {
            if ($stmt !== null) {
                $stmt = null;
            }
            if ($db !== null) {
                $db = null;
            }
        }
    }

    public function getCuestionariosProgramados(Request $request, Response $response, array $args): Response {
        $db = null;
        $stmt = null;
        try {
            $db = $this->container->get('db');
            // Obtener el ID del estudiante autenticado
            $estudiante_id = $this->getUserIdFromToken($request);

            if (!$estudiante_id) {
                return $this->errorResponse($response, 'Usuario no autenticado', 401);
            }
    
            $sql = "SELECT 
                        c.id AS cuestionario_id,
                        c.titulo,
                        c.descripcion,
                        c.tiempo_limite,
                        p.nombre AS periodo,
                        p.fecha_inicio,
                        p.fecha_fin,
                        a.id AS asignacion_id,
                        ap.id AS apertura_id,
                        prog.id AS programa_id,
                        prog.nombre AS programa_nombre
                    FROM asignacion a
                    INNER JOIN apertura ap ON a.id_apertura = ap.id
                    INNER JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                    INNER JOIN periodo p ON ap.id_periodo = p.id
                    INNER JOIN programa prog ON rcp.id_programa = prog.id
                    WHERE a.id_estudiante = :estudiante_id
                    AND ap.activo = 1
                    AND p.fecha_inicio > CURDATE()
                    ORDER BY p.fecha_inicio ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $cuestionarios_programados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($cuestionarios_programados) > 0) {
                    return $this->successResponse($response, 'Cuestionarios programados obtenidos correctamente', [
                        'cuestionarios_programados' => $cuestionarios_programados,
                        'total' => count($cuestionarios_programados)
                    ]);
                } else {
                    return $this->errorResponse($response, 'No se encontraron cuestionarios programados', 204);
                }
            } else {
                return $this->errorResponse($response, 'Error al obtener los cuestionarios programados', 500);
            }
        } catch (Exception $e) {
            return $this->errorResponse($response, 'Error al obtener los cuestionarios programados: ' . $e->getMessage(), 500);
        } finally {
            if ($stmt !== null) {
                $stmt = null;
            }
            if ($db !== null) {
                $db = null;
            }
        }
    }

    public function getCuestionariosExpirados(Request $request, Response $response, array $args): Response {
        $db = null;
        $stmt = null;
        try {
            $db = $this->container->get('db');
            
            // Obtener el ID del estudiante autenticado
            $estudiante_id = $this->getUserIdFromToken($request);

            if (!$estudiante_id) {
                return $this->errorResponse($response, 'Usuario no autenticado', 401);
            }
    
            $sql = "SELECT 
                        c.id AS cuestionario_id,
                        c.titulo,
                        c.descripcion,
                        c.tiempo_limite,
                        p.nombre AS periodo,
                        p.fecha_inicio,
                        p.fecha_fin,
                        a.id AS asignacion_id,
                        ap.id AS apertura_id,
                        prog.id AS programa_id,
                        prog.nombre AS programa_nombre
                    FROM asignacion a
                    INNER JOIN apertura ap ON a.id_apertura = ap.id
                    INNER JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                    INNER JOIN periodo p ON ap.id_periodo = p.id
                    INNER JOIN programa prog ON rcp.id_programa = prog.id
                    WHERE a.id_estudiante = :estudiante_id
                    AND ap.activo = 1
                    AND p.fecha_fin < CURDATE()
                    AND NOT EXISTS (
                        SELECT 1 FROM intento_cuestionario ic 
                        WHERE ic.id_estudiante = :estudiante_id 
                        AND ic.id_apertura = ap.id
                    )
                    ORDER BY p.fecha_fin DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $cuestionarios_expirados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($cuestionarios_expirados) > 0) {
                    return $this->successResponse($response, 'Cuestionarios expirados obtenidos correctamente', [
                        'cuestionarios_expirados' => $cuestionarios_expirados,
                        'total' => count($cuestionarios_expirados)
                    ]);
                } else {
                    return $this->errorResponse($response, 'No se encontraron cuestionarios expirados', 204);
                }
            } else {
                return $this->errorResponse($response, 'Error al obtener los cuestionarios expirados', 500);
            }
        } catch (Exception $e) {
            return $this->errorResponse($response, 'Error al obtener los cuestionarios expirados: ' . $e->getMessage(), 500);
        } finally {
            if ($stmt !== null) {
                $stmt = null;
            }
            if ($db !== null) {
                $db = null;
            }
        }
    }

    /**
     * Agrega múltiples estudiantes a la vez
     * 
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function agregarEstudiantes(Request $request, Response $response, array $args): Response {
        $db = null;
        $stmt = null;
        
        try {
            // Verificar autenticación
            $user_id = $this->getUserIdFromToken($request);
            if (!$user_id) {
                return $this->errorResponse($response, 'Usuario no autenticado', 401);
            }

            // Obtener datos del usuario autenticado
            $userData = $this->getUserDataFromToken($request);
            $programa_id = $userData['programa_id'];
            
            // Obtener datos de entrada
            $inputData = $this->getJsonInput($request);
            if (!$inputData || !isset($inputData['estudiantes']) || !is_array($inputData['estudiantes'])) {
                return $this->errorResponse($response, 'Se requiere un array de estudiantes en formato JSON', 400);
            }

            $estudiantes = $inputData['estudiantes'];
            
            // Si no se proporciona programa_id en los datos y el usuario no tiene uno asignado, devolver error
            if (!$programa_id && !isset($inputData['programa_id'])) {
                return $this->errorResponse($response, 'El programa es requerido', 400);
            }

            // Si el usuario no tiene programa asignado, usar el proporcionado en la solicitud
            if (!$programa_id) {
                $programa_id = $inputData['programa_id'];
            }

            $db = $this->container->get('db');
            $db->beginTransaction();

            $resultados = [
                'exitosos' => [],
                'fallidos' => []
            ];

            foreach ($estudiantes as $index => $estudiante) {
                try {
                    // Validar datos del estudiante
                    if (empty($estudiante['nombre']) || empty($estudiante['email']) || empty($estudiante['identificacion'])) {
                        $resultados['fallidos'][] = [
                            'indice' => $index,
                            'error' => 'Datos incompletos. Se requieren nombre, email e identificación',
                            'datos' => $estudiante
                        ];
                        continue;
                    }

                    // Sanitizar datos
                    $nombre = $this->sanitizeInput($estudiante['nombre']);
                    $email = $this->sanitizeInput($estudiante['email']);
                    $identificacion = $this->sanitizeInput($estudiante['identificacion']);

                    // Verificar si el estudiante ya existe
                    $sql_verificar = "SELECT id FROM estudiante WHERE identificacion = :identificacion";
                    $stmt = $db->prepare($sql_verificar);
                    $stmt->bindParam(':identificacion', $identificacion, PDO::PARAM_STR);
                    $stmt->execute();

                    $estudiante_existente = $stmt->fetch(PDO::FETCH_ASSOC);
                    $estudiante_id = null;

                    if ($estudiante_existente) {
                        // El estudiante ya existe, usar su ID
                        $estudiante_id = $estudiante_existente['id'];
                        
                        // Verificar si ya está relacionado con este programa
                        $sql_verificar_relacion = "SELECT id FROM relacion_programa_estudiante 
                                                    WHERE estudiante_id = :estudiante_id AND programa_id = :programa_id";
                        $stmt = $db->prepare($sql_verificar_relacion);
                        $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
                        $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            $resultados['fallidos'][] = [
                                'indice' => $index,
                                'error' => 'El estudiante ya está asignado a este programa',
                                'datos' => $estudiante
                            ];
                            continue;
                        }
                    } else {
                        // El estudiante no existe, crearlo
                        $sql_insertar = "INSERT INTO estudiante (nombre, email, identificacion) 
                                        VALUES (:nombre, :email, :identificacion)";
                        $stmt = $db->prepare($sql_insertar);
                        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                        $stmt->bindParam(':identificacion', $identificacion, PDO::PARAM_STR);

                        if (!$stmt->execute()) {
                            throw new Exception('Error al insertar el estudiante');
                        }

                        $estudiante_id = $db->lastInsertId();
                    }

                    // Crear la relación estudiante-programa
                    $sql_relacion = "INSERT INTO relacion_programa_estudiante (estudiante_id, programa_id) 
                                    VALUES (:estudiante_id, :programa_id)";
                    $stmt = $db->prepare($sql_relacion);
                    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
                    $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);

                    if ($stmt->execute()) {
                        $resultados['exitosos'][] = [
                            'id' => $estudiante_id,
                            'nombre' => $nombre,
                            'email' => $email,
                            'identificacion' => $identificacion,
                            'programa_id' => $programa_id,
                            'accion' => $estudiante_existente ? 'asignado_programa' : 'creado_y_asignado'
                        ];
                    } else {
                        throw new Exception('Error al crear la relación estudiante-programa');
                    }

                } catch (Exception $e) {
                    $resultados['fallidos'][] = [
                        'indice' => $index,
                        'error' => $e->getMessage(),
                        'datos' => $estudiante
                    ];
                }
            }

            // Si hay al menos un estudiante exitoso, confirmar la transacción
            if (count($resultados['exitosos']) > 0) {
                $db->commit();
            } else {
                $db->rollBack();
                $errorData = [
                    'mensaje' => 'No se pudo agregar ningún estudiante',
                    'errores' => $resultados['fallidos']
                ];
                return $this->errorResponse(
                    $response, 
                    json_encode($errorData),
                    400
                );
            }

            // Preparar respuesta
            $respuesta = [
                'mensaje' => 'Proceso de carga de estudiantes completado',
                'total_estudiantes' => count($estudiantes),
                'exitosos' => count($resultados['exitosos']),
                'fallidos' => count($resultados['fallidos']),
                'detalles_exitosos' => $resultados['exitosos']
            ];

            // Si hay fallos, establecer el código de estado 207 (Multi-Status)
            if (!empty($resultados['fallidos'])) {
                $respuesta['detalles_fallidos'] = $resultados['fallidos'];
                $response = $response->withStatus(207);
            }
            
            // Devolver la respuesta como JSON
            $response->getBody()->write(json_encode($respuesta));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (Exception $e) {
            if ($db !== null) {
                $db->rollBack();
            }
            return $this->errorResponse($response, 'Error al procesar la solicitud: ' . $e->getMessage(), 500);
        } finally {
            if ($stmt !== null) {
                $stmt = null;
            }
            if ($db !== null) {
                $db = null;
            }
        }
    }
    
}