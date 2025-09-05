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

class asignacion_controller extends BaseController{
    private $jwtSecret;

    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $this->jwtSecret = $_ENV['JWT_SECRET'];
    }

    public function crearAsignacion(Request $request, Response $response, array $args): Response {
        $stmt_crear_asignacion = null;
        $db = null;
        try {
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            if (!$inputData) {
                return $this->errorResponse($response, 'Datos JSON inválidos', 400);
            }
            // Verificar campos requeridos
            if (!isset($inputData['id_apertura']) || !isset($inputData['id_estudiante'])) {
                return $this->errorResponse($response, 'Faltan campos requeridos: id_apertura o id_estudiante', 400);
            }
            $apertura_id = $this->sanitizeInput($inputData['id_apertura']);
            $estudiante_ids = $inputData['id_estudiante']; // No sanitizar aún, necesitamos verificar si es array
            // Validar que id_apertura sea un entero válido
            if (!is_numeric($apertura_id) || $apertura_id <= 0) {
                return $this->errorResponse($response, 'ID de apertura inválido', 400);
            }
            // Validar que id_estudiante sea un array y no esté vacío
            if (!is_array($estudiante_ids) || empty($estudiante_ids)) {
                return $this->errorResponse($response, 'Debe proporcionar al menos un ID de estudiante', 400);
            }
            // Sanitizar cada ID de estudiante
            $estudiante_ids = array_map([$this, 'sanitizeInput'], $estudiante_ids);
            // Preparar consulta
            $stmt_crear_asignacion = $db->prepare("INSERT IGNORE INTO asignacion (id_apertura, id_estudiante) VALUES (:apertura_id, :estudiante_id)");
            $db->beginTransaction();
            try {
                foreach ($estudiante_ids as $estudiante_id) {
                    if (!is_numeric($estudiante_id) || $estudiante_id <= 0) {
                        continue; // o devolver error, según tu política
                    }
                    $stmt_crear_asignacion->bindParam(':apertura_id', $apertura_id, PDO::PARAM_INT);
                    $stmt_crear_asignacion->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
                    $stmt_crear_asignacion->execute();
                }
                $db->commit();
                return $this->successResponse($response, 'Asignaciones creadas exitosamente');
            } catch (Exception $e) {
                $db->rollback();
                return $this->errorResponse($response, 'Error al crear asignaciones: ' . $e->getMessage(), 500);
            }
        } catch (Exception $e) {
            return $this->errorResponse($response, 'Error interno del servidor: ' . $e->getMessage(), 500);
        }finally{
            if($stmt_crear_asignacion !== null){
                $stmt_crear_asignacion = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }

    public function getAsignaciones(Request $request, Response $response, array $args): Response{
        $stmt_get_asignaciones = null;
        $db = null;
        try{
            $docente_id = $this->getUserIdFromToken($request);
            if(!$docente_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $db = $this->container->get('db');
            if(empty($docente_id)){return $this->errorResponse($response, 'ID de docente inválido', 400);}
                $query_get_asignaciones = "SELECT a.id, a.id_apertura, a.id_estudiante, e.identificacion,
                        e.nombre as estudiante_nombre, e.email,
                        c.titulo as cuestionario_titulo, p.nombre as periodo_nombre
                        FROM asignacion a
                        JOIN estudiante e ON a.id_estudiante = e.id
                        JOIN apertura ap ON a.id_apertura = ap.id
                        JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                        JOIN cuestionario c ON rcp.id_cuestionario = c.id
                        JOIN periodo p ON ap.id_periodo = p.id
                        WHERE rcp.id_docente = :docente_id";
                $stmt_get_asignaciones = $db->prepare($query_get_asignaciones);
                $stmt_get_asignaciones->bindParam(':docente_id', $docente_id);
                $stmt_get_asignaciones->execute();
                $asignaciones = $stmt_get_asignaciones->fetchAll();
                return $this->successResponse($response, 'Asignaciones obtenidas exitosamente', [
                    'asignaciones' => $asignaciones,
                    'total' => count($asignaciones)
                ]);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener las asignaciones: ' . $e->getMessage(), 500);
        }finally{
            if($stmt_get_asignaciones !== null){
                $stmt_get_asignaciones = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }

    public function getAsignacionesByApertura(Request $request, Response $response, array $args): Response{
        $stmt_get_asignaciones = null;
        $db = null;
        try{
            $db = $this->container->get('db');
            $docente_id = $this->getUserIdFromToken($request);
            if(!$docente_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $sql_get_asignaciones = "SELECT DISTINCT ap.id, c.titulo, c.descripcion, p.nombre as periodo_nombre, 
                                p.fecha_inicio, p.fecha_fin, pr.nombre as programa_nombre
                                FROM apertura ap
                                JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                                JOIN cuestionario c ON rcp.id_cuestionario = c.id
                                JOIN periodo p ON ap.id_periodo = p.id
                                JOIN programa pr ON rcp.id_programa = pr.id
                                JOIN asignacion a ON ap.id = a.id_apertura
                                WHERE rcp.id_docente = :docente_id
                                AND ap.activo = 1";
            $stmt_get_asignaciones = $db->prepare($sql_get_asignaciones);
            $stmt_get_asignaciones->bindParam(':docente_id', $docente_id, PDO::PARAM_INT);
            $stmt_get_asignaciones->execute();
            $asignaciones = $stmt_get_asignaciones->fetchAll(PDO::FETCH_ASSOC);
            return $this->successResponse($response, 'Asignaciones obtenidas exitosamente', [
                'Aperturas con asignaciones' => $asignaciones,
                'total' => count($asignaciones),
            ]);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener las asignaciones: ' . $e->getMessage(), 500);
        }finally{
            if($stmt_get_asignaciones !== null){
                $stmt_get_asignaciones = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }

    public function deleteAsignacion(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt_verificar = null;
        try{
            //verificar que la asignacion pertenezca al docente
            $id_asignacion = $args['id_asignacion'];
            $id_docente = $this->getUserIdFromToken($request);
            if(!$id_docente){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $db = $this->container->get('db');
            $sql_verificar = "SELECT a.id 
                    FROM asignacion a
                    JOIN apertura ap ON a.id_apertura = ap.id
                    JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    WHERE a.id = ? AND rcp.id_docente = ?";
                    $stmt_verificar = $db->prepare($sql_verificar);
                    $stmt_verificar->bindParam(1, $id_asignacion, PDO::PARAM_INT);
                    $stmt_verificar->bindParam(2, $id_docente, PDO::PARAM_INT);
                    $stmt_verificar->execute();
                    $result = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
                    if($result){
                        $sql_eliminar = "DELETE FROM asignacion WHERE id = ?";
                        $stmt_eliminar = $db->prepare($sql_eliminar);
                        $stmt_eliminar->bindParam(1, $id_asignacion, PDO::PARAM_INT);
                        if($stmt_eliminar->execute()){
                            return $this->successResponse($response, 'Asignación eliminada exitosamente');
                        }else{
                            return $this->errorResponse($response, 'Error al eliminar la asignación', 500);
                        }
                    }else{
                        return $this->errorResponse($response, 'No tiene permisos para eliminar esta asignación o no existe', 403);
                    }
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al eliminar la asignación: ' . $e->getMessage(), 500);
        }finally{
            if($stmt_verificar !== null){
                $stmt_verificar = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }

    public function deleteAllAsignaciones(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt_verificar = null;
        $stmt_eliminar = null;
        try{
            $docente_id = $this->getUserIdFromToken($request);
            if(!$docente_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $inputData = $this->getJsonInput($request);
            $db = $this->container->get('db');
            if(!$inputData){
                return $this->errorResponse($response, 'Datos JSON inválidos', 400);
            }
            $apertura_id = $this->sanitizeInput($inputData['apertura_id']);
            $sql_verificar = "SELECT ap.id 
                            FROM apertura ap
                            JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                            WHERE ap.id = ? AND rcp.id_docente = ?";
            $stmt_verificar = $db->prepare($sql_verificar);
            $stmt_verificar->bindParam(1, $apertura_id, PDO::PARAM_INT);
            $stmt_verificar->bindParam(2, $docente_id, PDO::PARAM_INT);
            $stmt_verificar->execute();
            $result = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
            if($result){
                $sql_eliminar = "DELETE a FROM asignacion a
                                JOIN apertura ap ON a.id_apertura = ap.id
                                JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                                WHERE a.id_apertura = ? AND rcp.id_docente = ?";
                $stmt_eliminar = $db->prepare($sql_eliminar);
                $stmt_eliminar->bindParam(1, $apertura_id, PDO::PARAM_INT);
                $stmt_eliminar->bindParam(2, $docente_id, PDO::PARAM_INT);
                if($stmt_eliminar->execute()){
                    return $this->successResponse($response, 'Asignaciones eliminadas exitosamente');
                }else{
                    return $this->errorResponse($response, 'Error al eliminar las asignaciones', 500);
                }
            }else{
                return $this->errorResponse($response, 'No tiene permisos para eliminar estas asignaciones o la apertura no existe', 403);
            }
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al eliminar las asignaciones: ' . $e->getMessage(), 500);
        }finally{
            if($stmt_verificar !== null){
                $stmt_verificar = null;
            }
            if($stmt_eliminar !== null){
                $stmt_eliminar = null;
            }
            if($db !== null){
                $db = null;
            }
        }
    }
}