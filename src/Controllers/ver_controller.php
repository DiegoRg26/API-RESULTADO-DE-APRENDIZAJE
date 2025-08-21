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

    /**
     * Funcion que verifica si el cuestionario existe y le pertenece al docente logueado.|
     */
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

    private function verificarAperturaDocente($apertura_id, Request $request){
        $db = null;
        $stmt = null;
        try{
            $db = $this->container->get('db');
            $docente_id = $this->getUserIdFromToken($request);
            $check_query = "SELECT 
                                a.id as apertura_id,
                                c.id as cuestionario_id,
                                c.titulo,
                                c.descripcion,
                                p.id as programa_id,
                                p.nombre as programa_nombre,
                                per.id as periodo_id,
                                per.nombre as periodo_nombre,
                                a.activo
                            FROM 
                                apertura a
                            JOIN 
                                relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                            JOIN 
                                cuestionario c ON rcp.id_cuestionario = c.id
                            JOIN 
                                programa p ON rcp.id_programa = p.id
                            JOIN 
                                periodo per ON a.id_periodo = per.id
                            WHERE 
                                a.id = :apertura_id 
                                AND rcp.id_docente = :docente_id";
            $stmt = $db->prepare($check_query);
            $stmt->bindParam(":apertura_id", $apertura_id,);
            $stmt->bindParam(":docente_id", $docente_id,);
            $stmt->execute();
            $apertura = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$apertura){
                return $error = array("status"=> false, "mensaje"=> "No tienes acceso a este cuestionario o no existe");
            }
            return $error = array("status"=> true, "mensaje"=> "Cuestionario encontrado", "apertura"=> $apertura);
        }catch(Exception $e){
            return $error = array("status"=> false, "mensaje"=> $e->getMessage());
        }
    }

    /**
     * Metodo que obtiene las preguntas y respuestas de un cuestionario.
     * Parametros (GET):
     * id -> URL
     */
    public function getPreguntasRespuestas(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $cuestionario_id = $args['cuestionario_id'];
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
                    $_FILES['imagen_pregunta'] = $row['imagen_pregunta'];
                    $_FILES['imagen_opcion'] = $row['imagen_opcion'];
                    $imagen_pregunta = $_FILES['imagen_pregunta'];
                    $preguntas_con_opciones[$pregunta_id] = [
                        'pregunta_id' => $row['pregunta_id'],
                        'texto_pregunta' => $row['texto_pregunta'],
                        'orden_pregunta' => $row['orden_pregunta'],
                        'peso_pregunta' => $row['peso_pregunta'],
                        'imagen_pregunta' => (!empty($row['imagen_pregunta']) ? base64_encode($imagen_pregunta) : null),
                        'opciones' => []
                    ];
                }

                if($row['opcion_id']){
                    $imagen_opcion = $_FILES['imagen_opcion'];
                    $preguntas_con_opciones[$pregunta_id]['opciones'][] = [
                        'opcion_id' => $row['opcion_id'],
                        'texto_opcion' => $row['texto_opcion'],
                        'imagen_opcion' => (!empty($row['imagen_opcion']) ? base64_encode($imagen_opcion) : null),
                        'es_correcta' => $row['opcion_correcta'],
                        'orden_opcion' => $row['orden']
                    ];
                }
            }

            return $this->successResponse($response, 'Preguntas y respuestas obtenidas exitosamente', [
                'cuestionario' => $cuestionario,
                'preguntas' => $preguntas_con_opciones
            ]);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error interno del servidor', 500);
        }
    }
    
    /**
     * Metodo que obtiene el intento mas reciente de un estudiante en x cuestionario.
     * Parametros (POST):
     * estudiante_id
     * cuestionario_id
     */
    public function getLastTry(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $inputData = $this->getJsonInput($request);
            if(!$inputData){return $this->errorResponse($response, 'No se proporcionaron datos', 400);}
            $estudiante_id = $this->sanitizeInput($inputData['estudiante_id']);
            $cuestionario_id = $this->sanitizeInput($inputData['cuestionario_id']);
            if($estudiante_id <= 0 || $cuestionario_id <= 0){return $this->errorResponse($response, 'ID de estudiante o cuestionario no valido', 400);}
            $db = $this->container->get('db');
            $sql_intento = "SELECT 
                                ic.id as intento_id,
                                ic.fecha_fin as fecha_respuesta,
                                ic.puntaje_total as puntaje_obtenido,
                                (SELECT SUM(peso_pregunta) FROM preguntas WHERE id_cuestionario = :cuestionario_id) as puntaje_total
                            FROM 
                                intento_cuestionario ic
                            JOIN 
                                apertura a ON ic.id_apertura = a.id
                            JOIN 
                                relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                            WHERE 
                                ic.id_estudiante = :estudiante_id
                                AND rcp.id_cuestionario = :cuestionario_id
                                AND ic.completado = 1
                            ORDER BY 
                                ic.fecha_fin DESC
                            LIMIT 1";
            $stmt = $db->prepare($sql_intento);
            $stmt->bindParam(":cuestionario_id", $cuestionario_id);
            $stmt->bindParam(":estudiante_id", $estudiante_id);
            $stmt->execute();
            $intento = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$intento){return $this->errorResponse($response, 'Intento no encontrado', 404);}
            $porcentaje = $intento['puntaje_total'] > 0 ? round(($intento['puntaje_obtenido'] / $intento['puntaje_total']) * 100) : 0;
            return $this->successResponse($response, 'Intento encontrado', [
                'intento_id' => $intento['intento_id'],
                'fecha_respuesta' => $intento['fecha_respuesta'],
                'puntaje_total' => $intento['puntaje_total'] ?? 0,
                'puntaje_obtenido' => $intento['puntaje_obtenido'] ?? 0,
                'porcentaje' => $porcentaje,
            ]);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error interno del servidor', 500);
        }finally{
            if($stmt !== null){$stmt = null;}
            if($db !== null){$db = null;}
        }
    }
    
    /**
     * Metodo que obtiene las respuestas de un estudiante para un cuestionario.
     * Parametros (GET):
     * intento_id
     */
    public function getRespDet(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt_preguntas = null;
        $stmt_respuestas = null;
        try{
            $intento_id = $args['intento_id'];
            if($intento_id <= 0){return $this->errorResponse($response, 'ID de intento no valido', 400);}
            $db = $this->container->get('db');
            $sql_preguntas = "SELECT 
                                p.id as pregunta_id,
                                p.texto_pregunta,
                                p.peso_pregunta,
                                p.imagen_pregunta,
                                p.nombre_imagen_pregunta,
                                re.id_opcion_seleccionada,
                                op.opcion_correcta as es_correcta
                            FROM 
                                respuesta_estudiante re
                            JOIN 
                                preguntas p ON re.id_pregunta = p.id
                            JOIN 
                                opcion_respuesta op ON re.id_opcion_seleccionada = op.id
                            WHERE 
                                re.id_intento = :intento_id
                            ORDER BY 
                                p.orden_pregunta ASC";
            $stmt_preguntas = $db->prepare($sql_preguntas);
            $stmt_preguntas->bindParam(':intento_id', $intento_id, PDO::PARAM_INT);
            $stmt_preguntas->execute();
            $preguntas = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);
            
            $respuestas = [];
            foreach($preguntas as $pregunta){
                $sql_opciones = "SELECT 
                                    op.id,
                                    op.texto_opcion,
                                    op.opcion_correcta as es_correcta,
                                    op.imagen_opcion,
                                    op.nombre_imagen_opcion
                                FROM 
                                    opcion_respuesta op
                                WHERE 
                                    op.id_pregunta = :pregunta_id
                                ORDER BY 
                                    op.orden ASC";
                $stmt_opciones = $db->prepare($sql_opciones);
                $stmt_opciones->bindParam(':pregunta_id', $pregunta['pregunta_id'], PDO::PARAM_INT);
                $stmt_opciones->execute();
                $opciones = $stmt_opciones->fetchAll(PDO::FETCH_ASSOC);
                foreach($opciones as $opcion){
                    $opcion['imagen_opcion'] = (!empty($opcion['imagen_opcion']) ? base64_encode($opcion['imagen_opcion']) : null);
                }
                $pregunta['opciones'] = $opciones;
                $pregunta['imagen_pregunta'] = (!empty($pregunta['imagen_pregunta']) ? base64_encode($pregunta['imagen_pregunta']) : null);
                $respuestas[] = $pregunta;
            }
            return $this->successResponse($response, 'Respuestas obtenidas exitosamente', $respuestas);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error interno del servidor', 500);
        }finally{
            if($stmt_preguntas !== null){$stmt_preguntas = null;}
            if($stmt_respuestas !== null){$stmt_respuestas = null;}
            if($db !== null){$db = null;}
        }
    }
}