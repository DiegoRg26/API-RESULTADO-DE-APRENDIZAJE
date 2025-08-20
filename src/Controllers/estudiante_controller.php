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
            $db = $this->container->get('db');
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){
                return $this->errorResponse($response, 'Usuario no autenticado', 401);
            }
            //Obtiene los datos del usuario autenticado
            $userData = $this->getUserDataFromToken($request);
            $programa_id = $userData['programa_id'];
            //Valida si el usuario pertenece o no a un programa, en caso de que no, se obtiene todos los estudiantes de todos los programas
            if(!$programa_id){
                $sql_get_estudiantes = "SELECT e.id, e.nombre, e.email, e.identificacion, p.nombre as programa_nombre
                                        FROM estudiante e
                                        JOIN programa p ON e.id_programa = p.id
                                        WHERE e.estado = 1
                                        ORDER BY e.nombre";
                    $stmt = $db->prepare($sql_get_estudiantes);
                    $stmt->execute();
                }else{
                    $sql_get_estudiantes = "SELECT e.id, e.nombre, e.email, e.identificacion, p.nombre as programa_nombre
                                            FROM estudiante e
                                            JOIN programa p ON e.id_programa = p.id
                                            WHERE e.id_programa = :programa_id AND e.estado = 1
                                            ORDER BY e.nombre";
                    $stmt = $db->prepare($sql_get_estudiantes);
                    $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
                }
            if($stmt->execute()){
                $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(count($estudiantes) > 0){
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
            //Valida si el estudiante ya existe
            $sql_verificar = "SELECT id FROM estudiante WHERE identificacion = :identificacion AND id_programa = :programa_id";
            $stmt = $db->prepare($sql_verificar);
            $stmt->bindParam(':identificacion', $identificacion, PDO::PARAM_STR);
            $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount() > 0){
                return $this->errorResponse($response, 'El estudiante ya existe', 400);
            }
            //Inserta el estudiante
            $db->beginTransaction();
            $sql_insertar = "INSERT INTO estudiante (nombre, email, identificacion, id_programa) VALUES (:nombre, :email, :identificacion, :programa_id)";
            $stmt = $db->prepare($sql_insertar);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':identificacion', $identificacion, PDO::PARAM_STR);
            $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
            if($stmt->execute()){
                $estudiante_id = $db->lastInsertId();
                $db->commit();
                return $this->successResponse($response, 'Estudiante agregado correctamente', [
                    'estudiante' => [
                        'id' => $estudiante_id,
                        'nombre' => $nombre,
                        'email' => $email,
                        'identificacion' => $identificacion,
                        'programa_id' => $programa_id
                        ]
                    ]);
                }else{
                    $db->rollBack();
                    return $this->errorResponse($response, 'Error al agregar el estudiante', 500);
            }
        }catch(Exception $e){
            $db->rollBack();
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
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            $estudiante_id = $this->sanitizeInput($inputData['id']);
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
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            $estudiante_id = $this->sanitizeInput($inputData['id']);
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
            $estudiante_id = $args['id'];
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
                        a.id AS asignacion_id
                    FROM asignacion a
                    INNER JOIN apertura ap ON a.id_apertura = ap.id
                    INNER JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                    INNER JOIN periodo p ON ap.id_periodo = p.id
                    WHERE a.id_estudiante = :estudiante_id
                    AND ap.activo = 1
                    AND p.fecha_fin >= CURDATE()";
            
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
                    return $this->errorResponse($response, 'No se encontraron cuestionarios asignados', 404);
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
                        ap.id AS apertura_id
                    FROM intento_cuestionario ic
                    INNER JOIN asignacion a ON ic.id_estudiante = a.id_estudiante AND ic.id_apertura = a.id_apertura
                    INNER JOIN apertura ap ON a.id_apertura = ap.id
                    INNER JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                    INNER JOIN periodo p ON ap.id_periodo = p.id
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
                    return $this->errorResponse($response, 'No se encontraron cuestionarios completados', 404);
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
                        ap.id AS apertura_id
                    FROM asignacion a
                    INNER JOIN apertura ap ON a.id_apertura = ap.id
                    INNER JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                    INNER JOIN periodo p ON ap.id_periodo = p.id
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
                    return $this->errorResponse($response, 'No se encontraron cuestionarios programados', 404);
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
                        ap.id AS apertura_id
                    FROM asignacion a
                    INNER JOIN apertura ap ON a.id_apertura = ap.id
                    INNER JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                    INNER JOIN periodo p ON ap.id_periodo = p.id
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
                    return $this->errorResponse($response, 'No se encontraron cuestionarios expirados', 404);
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

    
}