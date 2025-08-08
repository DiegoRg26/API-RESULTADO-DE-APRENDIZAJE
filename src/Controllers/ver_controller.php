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

    private function verificarCuestionario($cuestionario_id){
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
            return $error = array('status' => true, 'mensaje' => 'Cuestionario encontrado', 'cuestionario' => $cuestionario);
        }catch(Exception $e){
            return $error = array('status'=> false,);
        }
    }

    public function getPreguntasRespuestas(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $cuestionario_id = $args['id'];
            $cuestionario = null;
            $error = null;
            $error = $this->verificarCuestionario($cuestionario_id);
            if(!$error['status']){return $this->errorResponse($response, $error['mensaje'], 404);}
            $cuestionario = $error['cuestionario'];
            $db = $this->container->get('db');
            $sql = "SELECT 
                    p.id as pregunta_id,
                    p.texto_pregunta,
                    p.orden_pregunta,
                    p.peso_pregunta,
                    p.imagen_pregunta,
                    o.id as opcion_id,
                    o.texto_opcion,
                    o.imagen_opcion,
                    o.opcion_correcta,
                    o.orden
                FROM preguntas p
                LEFT JOIN opcion_respuesta o ON p.id = o.id_pregunta
                WHERE p.id_cuestionario = :cuestionario_id
                ORDER BY p.orden_pregunta ASC, o.orden ASC";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':cuestionario_id', $cuestionario_id);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $preguntas_con_opciones = [];
            foreach ($resultados as $row){
                $pregunta_id = $row['pregunta_id'];

                if(!isset($preguntas_con_opciones[$pregunta_id])){
                    $preguntas_con_opciones[$pregunta_id] = [
                        'pregunta_id' => $row['pregunta_id'],
                        'texto_pregunta' => $row['texto_pregunta'],
                        'orden_pregunta' => $row['orden_pregunta'],
                        'peso_pregunta' => $row['peso_pregunta'],
                        'imagen_pregunta' => (!empty($row['imagen_pregunta']) ? base64_encode($row['imagen_pregunta']) : null),
                        'opciones' => []
                    ];
                }

                if($row['opcion_id']){
                    $preguntas_con_opciones[$pregunta_id]['opciones'][] = [
                        'opcion_id' => $row['opcion_id'],
                        'texto_opcion' => $row['texto_opcion'],
                        'imagen_opcion' => (!empty($row['imagen_opcion']) ? base64_encode($row['imagen_opcion']) : null),
                        'es_correcta' => $row['opcion_correcta'],
                        'orden_opcion' => $row['orden']
                    ];
                }
            }

            return $this->successResponse($response, 'Preguntas y respuestas obtenidas exitosamente', [
                'preguntas' => $preguntas_con_opciones,
                'cuestionario' => $cuestionario
            ]);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error interno del servidor', 500);
        }
    }
}