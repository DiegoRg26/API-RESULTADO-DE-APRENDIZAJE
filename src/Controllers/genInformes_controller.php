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

class genInformes_controller extends BaseController{
    private $jwtSecret;
    public function __construct(ContainerInterface $c){
        parent::__construct($c);
    }

    public function getInformesByPeriodo(Request $request, Response $response, Array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $inputData = $this->getJsonInput($request);
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            $db = $this->container->get('db');
            $estudiante_id = $inputData['estudiante_id'];
            $periodo_id = $inputData['periodo_id'];
            $sql_query = "SELECT 
                            ic.id AS intento_id,
                            ic.fecha_inicio,
                            ic.fecha_fin,
                            ic.completado,
                            ic.puntaje_total,
                            e.id AS estudiante_id,
                            e.nombre AS estudiante_nombre,
                            e.identificacion AS estudiante_identificacion,
                            c.id AS cuestionario_id,
                            c.titulo AS cuestionario_titulo,
                            c.descripcion AS cuestionario_descripcion,
                            p.id AS periodo_id,
                            p.nombre AS periodo_nombre,
                            p.fecha_inicio AS periodo_inicio,
                            p.fecha_fin AS periodo_fin,
                            prog.id AS programa_id,
                            prog.nombre AS programa_nombre
                        FROM 
                            intento_cuestionario ic
                            INNER JOIN estudiante e ON ic.id_estudiante = e.id
                            INNER JOIN apertura a ON ic.id_apertura = a.id
                            INNER JOIN periodo p ON a.id_periodo = p.id
                            INNER JOIN relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                            INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                            INNER JOIN programa prog ON rcp.id_programa = prog.id
                        WHERE 
                            ic.id_estudiante = :estudiante_id
                            AND a.id_periodo = :periodo_id
                        ORDER BY 
                            ic.fecha_inicio DESC";
            $stmt = $db->prepare($sql_query);
            $stmt->bindParam(':estudiante_id', $estudiante_id);
            $stmt->bindParam(':periodo_id', $periodo_id);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->successResponse($response, 'Informes obtenidos correctamente', $result);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener los informes: ' . $e->getMessage(), 500);
        }finally{
            if($db !== null){$db = null;}
            if($stmt !== null){$stmt = null;}
        }
    }

    public function getInformesByAnio(Request $request, Response $response, Array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $user_rol = $this->getUserDataFromToken($request)['rol'];
            if($user_rol != 0){
                return $this->errorResponse($response, 'Usuario no autorizado', 401);
            }
            $inputData = $this->getJsonInput($request);
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            $db = $this->container->get('db');
            $estudiante_id = $inputData['estudiante_id'];
            $anio = $inputData['anio'];
            $sql_query = "SELECT 
                            ic.id AS intento_id,
                            ic.fecha_inicio,
                            ic.fecha_fin,
                            ic.completado,
                            ic.puntaje_total,
                            e.id AS estudiante_id,
                            e.nombre AS estudiante_nombre,
                            e.identificacion AS estudiante_identificacion,
                            c.id AS cuestionario_id,
                            c.titulo AS cuestionario_titulo,
                            p.nombre AS periodo_nombre,
                            prog.nombre AS programa_nombre
                        FROM 
                            intento_cuestionario ic
                        INNER JOIN 
                            estudiante e ON ic.id_estudiante = e.id
                        INNER JOIN 
                            apertura a ON ic.id_apertura = a.id
                        INNER JOIN 
                            periodo p ON a.id_periodo = p.id
                        INNER JOIN 
                            relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                        INNER JOIN 
                            cuestionario c ON rcp.id_cuestionario = c.id
                        INNER JOIN 
                            programa prog ON rcp.id_programa = prog.id
                        WHERE 
                            ic.id_estudiante = :estudiante_id
                            AND YEAR(ic.fecha_inicio) = :anio
                        ORDER BY 
                            ic.fecha_inicio DESC";
            $stmt = $db->prepare($sql_query);
            $stmt->bindParam(':estudiante_id', $estudiante_id);
            $stmt->bindParam(':anio', $anio);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $this->successResponse($response, 'Informes obtenidos correctamente', $result);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener los informes: ' . $e->getMessage(), 500);
        }finally{
            if($db !== null){$db = null;}
            if($stmt !== null){$stmt = null;}
        }
    }

}