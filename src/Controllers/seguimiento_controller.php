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

class seguimiento_controller extends BaseController{

    public function __construct(ContainerInterface $c){
		parent::__construct($c);
	}

    public function getCuestionarioInfo(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $apertura_id = $args["apertura_id"];
            if($apertura_id <= 0){return $this->errorResponse($response, 'ID de apertura inválido', 400);}
            $db = $this->container->get('db');

            $query_cuestionario = "SELECT 
                                    a.id as apertura_id,
                                    a.activo,
                                    c.id,
                                    c.titulo,
                                    c.descripcion,
                                    p.nombre as periodo_nombre,
                                    p.fecha_inicio,
                                    p.fecha_fin,
                                    prog.nombre as programa_nombre
                                FROM 
                                    apertura a
                                JOIN 
                                    relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                                JOIN 
                                    cuestionario c ON rcp.id_cuestionario = c.id
                                JOIN 
                                    periodo p ON a.id_periodo = p.id
                                JOIN 
                                    programa prog ON rcp.id_programa = prog.id
                                WHERE 
                                    a.id = :apertura_id
                                    AND rcp.id_docente = :usuario_id";
            $stmt = $db->prepare($query_cuestionario);
            $stmt->bindParam(':apertura_id', $apertura_id);
            $stmt->bindParam(':usuario_id', $user_id);
            $stmt->execute();
            $cuestionario = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$cuestionario){return $this->errorResponse($response, 'Cuestionario no encontrado', 404);}
            return $this->successResponse($response, "Cuestionario obtenido exitosamente", $cuestionario);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener el cuestionario', 500);
        }finally{
            if($stmt !== null){$stmt !== null;}
            if($db !== null){$db !== null;}
        }
    }

    public function getSeguiEstudiantes(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $apertura_id = $args["apertura_id"];
            if($apertura_id <= 0){return $this->errorResponse($response, 'ID de apertura inválido', 400);}
            $db = $this->container->get('db');
            $query_estudiantes = "SELECT 
                                        e.id,
                                        e.nombre,
                                        e.email,
                                        e.identificacion,
                                        ic.fecha_fin as fecha_respuesta,
                                        CASE WHEN ic.completado = 1 THEN 1 ELSE 0 END as completado,
                                        (SELECT COUNT(*) FROM preguntas WHERE id_cuestionario = c.id) as total_preguntas,
                                        (SELECT COUNT(*) FROM respuesta_estudiante re2 
                                        JOIN opcion_respuesta op ON re2.id_opcion_seleccionada = op.id 
                                        JOIN intento_cuestionario ic2 ON re2.id_intento = ic2.id
                                        WHERE ic2.id_apertura = a.id 
                                        AND ic2.id_estudiante = e.id 
                                        AND op.opcion_correcta = 1) as respuestas_correctas,
                                        (SELECT SUM(peso_pregunta) FROM preguntas WHERE id_cuestionario = c.id) as puntaje_total,
                                        ic.puntaje_total as puntaje_obtenido,
                                        CASE 
                                            WHEN (SELECT SUM(peso_pregunta) FROM preguntas WHERE id_cuestionario = c.id) > 0 
                                            THEN ROUND((ic.puntaje_total / (SELECT SUM(peso_pregunta) FROM preguntas WHERE id_cuestionario = c.id)) * 100)
                                            ELSE 0
                                        END as porcentaje
                                    FROM 
                                        estudiante e
                                    JOIN 
                                        asignacion asig ON e.id = asig.id_estudiante
                                    JOIN 
                                        apertura a ON asig.id_apertura = a.id
                                    JOIN 
                                        relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                                    JOIN 
                                        cuestionario c ON rcp.id_cuestionario = c.id
                                    LEFT JOIN 
                                        intento_cuestionario ic ON ic.id_estudiante = e.id AND ic.id_apertura = a.id AND ic.completado = 1
                                    WHERE 
                                        a.id = :apertura_id
                                    GROUP BY 
                                        e.id
                                    ORDER BY 
                                        e.nombre ASC";
            $stmt = $db->prepare($query_estudiantes);
            $stmt->bindParam(':apertura_id', $apertura_id);
            $stmt->execute();
            $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->successResponse($response, "Estudiantes obtenidos exitosamente", $estudiantes);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener los estudiantes', 500);
        }finally{
            if($stmt !== null){$stmt !== null;}
            if($db !== null){$db !== null;}
        }
    }

    public function getSeguiCuestEstudiantes(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $db = $this->container->get('db');
            $query_seguimiento = "SELECT 
                                    a.id as apertura_id,
                                    c.id as cuestionario_id,
                                    c.titulo,
                                    c.descripcion,
                                    p.nombre as periodo_nombre,
                                    p.fecha_inicio,
                                    p.fecha_fin,
                                    prog.nombre as programa_nombre,
                                    COUNT(DISTINCT asig.id_estudiante) as total_estudiantes_asignados,
                                    COUNT(DISTINCT ic.id_estudiante) as total_estudiantes_completados
                                FROM 
                                    apertura a
                                JOIN 
                                    relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                                JOIN 
                                    cuestionario c ON rcp.id_cuestionario = c.id
                                JOIN 
                                    periodo p ON a.id_periodo = p.id
                                JOIN 
                                    programa prog ON rcp.id_programa = prog.id
                                JOIN 
                                    asignacion asig ON a.id = asig.id_apertura
                                LEFT JOIN (
                                    SELECT DISTINCT id_estudiante, id_apertura 
                                    FROM intento_cuestionario
                                    WHERE completado = 1
                                ) ic ON ic.id_estudiante = asig.id_estudiante AND ic.id_apertura = a.id
                                WHERE 
                                    rcp.activo = 1
                                    AND rcp.id_docente = :usuario_id
                                GROUP BY 
                                    a.id, c.id
                                ORDER BY 
                                    p.fecha_inicio DESC, c.titulo ASC";
        $stmt = $db->prepare($query_seguimiento);
        $stmt->bindParam(':usuario_id', $user_id);
        $stmt->execute();
        $cuestionarios_seguimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_estudiantes_asignados = 0;
        $total_estudiantes_completados = 0;

        foreach ($cuestionarios_seguimiento as $cuestionario) {
            $total_estudiantes_asignados += $cuestionario['total_estudiantes_asignados'];
            $total_estudiantes_completados += $cuestionario['total_estudiantes_completados'];
        }
        $porcentaje_global = ($total_estudiantes_asignados > 0) ? round(($total_estudiantes_completados / $total_estudiantes_asignados) * 100) : 0;
        
        return $this->successResponse($response, "Cuestionarios obtenidos exitosamente", [
            "cuestionarios_seguimiento" => $cuestionarios_seguimiento,
            "total_estudiantes_asignados" => $total_estudiantes_asignados,
            "total_estudiantes_completados" => $total_estudiantes_completados,
            "porcentaje_global" => $porcentaje_global,
        ]);
        }catch(Exception $e){
            return $this->errorResponse($response, "Error al obtener los cuestionarios", 500);
        }finally{
            if($stmt !== null){$stmt !== null;}
            if($db !== null){$db !== null;}
        }
    }

    public function getAllDocQuiz(Request $request, Response $response, array $args){
        $db = null;
        $stmt = null;
        try{
            $docente_id = $this->getUserIdFromToken($request);
            if(!$docente_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $db = $this->container->get('db');
            if($docente_id != 99){
                $sql_allQuiz = "SELECT 
                                    c.id as cuestionario_id,
                                    c.titulo,
                                    c.descripcion,
                                    p.nombre as programa_nombre,
                                    a.id as apertura_id,
                                    per.nombre as periodo_nombre,
                                    a.activo as apertura_activa,
                                    COUNT(DISTINCT ic.id_estudiante) as total_estudiantes_respondieron
                                FROM 
                                    cuestionario c
                                JOIN 
                                    relacion_cuestionario_programa rcp ON c.id = rcp.id_cuestionario
                                JOIN 
                                    programa p ON rcp.id_programa = p.id
                                JOIN 
                                    apertura a ON rcp.id = a.id_relacion_cuestionario_programa
                                JOIN 
                                    periodo per ON a.id_periodo = per.id
                                LEFT JOIN 
                                    intento_cuestionario ic ON a.id = ic.id_apertura AND ic.completado = 1
                                WHERE 
                                    rcp.id_docente = :docente_id
                                GROUP BY 
                                    c.id, c.titulo, c.descripcion, p.nombre, a.id, per.nombre, a.activo
                                ORDER BY 
                                    a.activo DESC, per.fecha_inicio DESC, c.titulo";
                $stmt = $db->prepare($sql_allQuiz);
                $stmt->bindParam(':docente_id', $docente_id);
            }else{
                $sql_allQuiz = "SELECT 
                                    c.id as cuestionario_id,
                                    c.titulo,
                                    c.descripcion,
                                    p.nombre as programa_nombre,
                                    a.id as apertura_id,
                                    per.nombre as periodo_nombre,
                                    a.activo as apertura_activa,
                                    COUNT(DISTINCT ic.id_estudiante) as total_estudiantes_respondieron
                                FROM 
                                    cuestionario c
                                JOIN 
                                    relacion_cuestionario_programa rcp ON c.id = rcp.id_cuestionario
                                JOIN 
                                    programa p ON rcp.id_programa = p.id
                                JOIN 
                                    apertura a ON rcp.id = a.id_relacion_cuestionario_programa
                                JOIN 
                                    periodo per ON a.id_periodo = per.id
                                LEFT JOIN 
                                    intento_cuestionario ic ON a.id = ic.id_apertura AND ic.completado = 1
                                GROUP BY 
                                    c.id, c.titulo, c.descripcion, p.nombre, a.id, per.nombre, a.activo
                                ORDER BY 
                                    a.activo DESC, per.fecha_inicio DESC, c.titulo";
                $stmt = $db->prepare($sql_allQuiz);
            }
            $stmt->execute();
            $cuestionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(!$cuestionarios){return $this->errorResponse($response, 'No se encontraron cuestionarios',500);}
            return $this->successResponse($response, 'Cuestionarios obtenidos exitosamente', $cuestionarios);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener los cuestionarios',500);
        }
    }

    
}