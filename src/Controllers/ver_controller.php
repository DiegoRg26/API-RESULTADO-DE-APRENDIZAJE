<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\BaseController;
use Psr\Container\ContainerInterface;
use PDO;
use Exception;

class ver_controller extends BaseController{

    public function __construct(ContainerInterface $c){
		parent::__construct($c);
	}

    private function verificarCuestionario($cuestionario_id, Response $response){
        $db = null;
        $stmt = null;
        try{
            if($cuestionario_id <= 0 || !$cuestionario_id){return $error = array('status' => false, 'mensaje' => 'Cuestionario no encontrado');}
            $db = $this->container->get('db');
            $check_query = "SELECT 
                        c.id, 
                        c.titulo, 
                        c.descripcion, 
                        d.nombre as creador_nombre,
                        p.nombre as programa_nombre,
                        n.nombre as nivel_nombre,
                        cam.nombre as campus_nombre,
                        rcp.id as relacion_id
                    FROM 
                        relacion_cuestionario_programa rcp
                    JOIN 
                        cuestionario c ON rcp.id_cuestionario = c.id
                    JOIN 
                        docente d ON rcp.id_docente = d.id
                    JOIN 
                        programa p ON rcp.id_programa = p.id
                    JOIN 
                        nivel n ON p.id_nivel = n.id
                    JOIN 
                        campus cam ON p.id_campus = cam.id
                    WHERE 
                        c.id = :cuestionario_id";
            $stmt = $db->prepare($check_query);
            $stmt->bindParam(':cuestionario_id', $cuestionario_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $cuestionario = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$cuestionario){
                return $error = array('status' => false, 'mensaje' => 'Cuestionario no encontrado');
            }
            return $error = array('status' => true, 'mensaje' => 'Cuestionario encontrado');
        }catch(Exception $e){
            return $error = array('status'=> false,);
        }
    }

    public function getPreguntasRespuestas(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            return $this->successResponse($response, 'Preguntas y respuestas obtenidas exitosamente');
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error interno del servidor', 500);
        }
    }
}