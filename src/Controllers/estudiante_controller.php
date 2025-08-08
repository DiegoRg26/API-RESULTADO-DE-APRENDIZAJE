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
}