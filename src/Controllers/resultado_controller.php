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

class resultado_Controller extends BaseController{

    public function __construct(ContainerInterface $c){
        parent::__construct($c);
    }


    // Verificar si el intento existe y pertenece al usuario
    public function verificaciones($intento_id, $usuario_id){
        $db = null;
        $stmt_verificar = null;
        try{
            $db = $this->container->get('db');
            $query_verificar = "SELECT ic.id 
                                FROM intento_cuestionario ic
                                WHERE ic.id = :intento_id 
                                AND ic.id_estudiante = :usuario_id
                                LIMIT 1";
            $stmt_verificar = $db->prepare($query_verificar);
            $stmt_verificar->bindParam("intento_id",$intento_id);
            $stmt_verificar->bindParam("usuario_id",$usuario_id);
            $stmt_verificar->execute();
            if($stmt_verificar->rowCount() == 0){
                return false;
            }
            return true;
        }catch(Exception $e){
            return false;
        }finally{
            if($db !== null){
                $db = null;
            }
            if($stmt_verificar !== null){
                $stmt_verificar = null;
            }
        }
    }

    // Obtener los resultados del intento
    public function obtenerResultado(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt_resultado = null;
        try{
            $db = $this->container->get('db');
            $intento_id = $args['intento_id'];
            if(!$intento_id){return $this->errorResponse($response, 'No se proporciono un intento valido', 400);}
            $usuario_id = $this->getUserIdFromToken($request);
            if(!$usuario_id){return $this->errorResponse($response, 'No se proporciono un token valido', 401);}

            $verificaciones = $this->verificaciones($intento_id, $usuario_id);
            if(!$verificaciones){
                return $this->errorResponse($response, "El intento no existe o no pertenece al usuario", 404);
            }

            $sql_query_resultado = "SELECT 
                                        c.id as cuestionario_id,
                                        c.titulo,
                                        c.descripcion,
                                        d.nombre as creador_nombre,
                                        p.nombre as programa_nombre,
                                        n.nombre as nivel_nombre,
                                        cam.nombre as campus_nombre,
                                        e.id as estudiante_id,
                                        COUNT(re.id) as total_respondidas,
                                        (SELECT COUNT(*) FROM preguntas WHERE id_cuestionario = c.id) as total_preguntas,
                                        (SELECT COUNT(*) FROM respuesta_estudiante re2 
                                        JOIN opcion_respuesta op ON re2.id_opcion_seleccionada = op.id 
                                        WHERE re2.id_intento = :intento_id 
                                        AND op.opcion_correcta = 1) as respuestas_correctas,
                                        (SELECT SUM(peso_pregunta) FROM preguntas WHERE id_cuestionario = c.id) as puntaje_total,
                                        (SELECT SUM(p.peso_pregunta) 
                                        FROM respuesta_estudiante re2 
                                        JOIN opcion_respuesta op ON re2.id_opcion_seleccionada = op.id 
                                        JOIN preguntas p ON re2.id_pregunta = p.id
                                        WHERE re2.id_intento = :intento_id 
                                        AND op.opcion_correcta = 1) as puntaje_obtenido,
                                        ic.fecha_fin as fecha_completado
                                    FROM 
                                        intento_cuestionario ic
                                    JOIN 
                                        apertura a ON ic.id_apertura = a.id
                                    JOIN 
                                        relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                                    JOIN 
                                        cuestionario c ON rcp.id_cuestionario = c.id
                                    JOIN 
                                        estudiante e ON ic.id_estudiante = e.id
                                    JOIN 
                                        programa p ON e.id_programa = p.id
                                    JOIN 
                                        nivel n ON p.id_nivel = n.id
                                    JOIN 
                                        campus cam ON p.id_campus = cam.id
                                    JOIN
                                        docente d ON rcp.id_docente = d.id
                                    LEFT JOIN
                                        respuesta_estudiante re ON re.id_intento = ic.id
                                    WHERE 
                                        ic.id = :intento_id
                                        AND ic.id_estudiante = :usuario_id
                                    GROUP BY 
                                        c.id, e.id, ic.id";

            $stmt_resultado = $db->prepare($sql_query_resultado);
            $stmt_resultado->bindParam(':intento_id', $intento_id);
            $stmt_resultado->bindParam(':usuario_id', $usuario_id);
            $stmt_resultado->execute();  
            if($stmt_resultado->rowCount() == 0){return $this->errorResponse($response, "El intento no existe o no pertenece al usuario", 404);}
            $resultado = $stmt_resultado->fetch(PDO::FETCH_ASSOC);

            $porcentaje = 0;
            if ($resultado['puntaje_total'] > 0) {
            $porcentaje = round(($resultado['puntaje_obtenido'] / $resultado['puntaje_total']) * 100);
            }
            $resultado['porcentaje'] = $porcentaje;

            return $this->successResponse($response, "Datos de resultado obtenidos correctamente: ", [
                "titulo" => $resultado['titulo'],
                "programa_nombre" => $resultado['programa_nombre'],
                "creador_nombre" => $resultado['creador_nombre'],
                "puntaje_obtenido" => $resultado['puntaje_obtenido'],
                "porcentaje" => $porcentaje,
                "respuestas_correctas" => $resultado['respuestas_correctas'],
                "total_respondidas" => $resultado['total_respondidas'],
                "fecha_completado" => $resultado['fecha_completado'],
            ]);
        }catch(Exception $e){
            return $this->errorResponse($response, "Error al obtener los resultados: " . $e->getMessage(), 500);
        }finally{
            if($db !== null){
                $db = null;
            }
            if($stmt_resultado !== null){
                $stmt_resultado = null;
            }
        }
    }

    public function obtenerDetalles(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt_detalles = null;
        try{
            $usuario_id = $this->getUserIdFromToken($request);
            if(!$usuario_id){return $this->errorResponse($response, "No se proporciono un token valido", 401);}
            $db = $this->container->get('db');
            $intento_id = $args['intento_id'];
            if(!$intento_id){return $this->errorResponse($response, "No se proporciono un intento valido", 400);}
            $sql_query_detalles = "SELECT 
                                    p.id as pregunta_id,
                                    p.texto_pregunta,
                                    p.orientacion,
                                    p.orden_pregunta,
                                    p.peso_pregunta,
                                    p.imagen_pregunta,
                                    o_seleccionada.id as opcion_seleccionada_id,
                                    o_seleccionada.texto_opcion as respuesta_usuario,
                                    o_seleccionada.imagen_opcion as imagen_respuesta_usuario,
                                    o_seleccionada.opcion_correcta as usuario_correcto,
                                    (SELECT texto_opcion FROM opcion_respuesta 
                                    WHERE id_pregunta = p.id AND opcion_correcta = 1 
                                    LIMIT 1) as respuesta_correcta,
                                    (SELECT imagen_opcion FROM opcion_respuesta 
                                    WHERE id_pregunta = p.id AND opcion_correcta = 1 
                                    LIMIT 1) as imagen_respuesta_correcta
                                FROM 
                                    respuesta_estudiante re
                                JOIN 
                                    preguntas p ON re.id_pregunta = p.id
                                JOIN 
                                    opcion_respuesta o_seleccionada ON re.id_opcion_seleccionada = o_seleccionada.id
                                WHERE 
                                    re.id_intento = :intento_id
                                ORDER BY 
                                    p.orden_pregunta";
            $stmt_detalles = $db->prepare($sql_query_detalles);
            $stmt_detalles->bindParam(':intento_id', $intento_id);
            $stmt_detalles->execute();
            $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

            return $this->successResponse($response, "Detalles de las respuestas obtenidos correctamente: ", [
                "detalles" => $detalles,
                "detalles_peso" => $detalles['peso_pregunta'],
                "detalles_texto_pregunta" => $detalles['texto_pregunta'],
                "detalles_orden_pregunta" => $detalles['orden_pregunta'],
                "detalles_orientacion" => $detalles['orientacion'],
                "detalles_imagen_pregunta" => $detalles['imagen_pregunta'],
                "detalles_respuesta_pregunta" => $detalles['respuesta_pregunta'],
                "detalles_imagen_respuesta_usuario" => $detalles['imagen_respuesta_usuario'],
            ]);
        }catch(Exception $e){
            return $this->errorResponse($response, "Error al obtener los detalles: " . $e->getMessage(), 500);
        }finally{
            if($db !== null){
                $db = null;
            }
            if($stmt_detalles !== null){
                $stmt_detalles = null;
            }
        }
    }
}