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

    public function calcularPromedioEstudiante(Request $request, Response $response, Array $args): Response{
        $db = null;
        $stmt_estudiante = null;
        $stmt_programa = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $inputData = $this->getJsonInput($request);
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            
            // Validar campos requeridos
            if(!isset($inputData['estudiante_id']) || !isset($inputData['programa_id'])){
                return $this->errorResponse($response, 'Faltan campos requeridos: estudiante_id, programa_id', 400);
            }
            
            $db = $this->container->get('db');
            $estudiante_id = $inputData['estudiante_id'];
            $programa_id = $inputData['programa_id'];
            $periodo_id = isset($inputData['periodo_id']) ? $inputData['periodo_id'] : null;
            
            // Construir condición de periodo si se proporciona
            $periodo_condition = $periodo_id ? "AND p.id = :periodo_id" : "";
            
            // Calcular promedio del estudiante específico
            $sql_estudiante = "SELECT 
                                e.id AS estudiante_id,
                                e.nombre AS estudiante_nombre,
                                e.identificacion AS estudiante_identificacion,
                                COUNT(DISTINCT ic.id) AS total_cuestionarios_completados,
                                AVG(ic.puntaje_total) AS promedio_estudiante,
                                MIN(ic.puntaje_total) AS puntaje_minimo,
                                MAX(ic.puntaje_total) AS puntaje_maximo,
                                SUM(ic.puntaje_total) AS suma_puntajes
                            FROM 
                                estudiante e
                                INNER JOIN intento_cuestionario ic ON e.id = ic.id_estudiante
                                INNER JOIN apertura a ON ic.id_apertura = a.id
                                INNER JOIN periodo p ON a.id_periodo = p.id
                                INNER JOIN relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                                INNER JOIN programa prog ON rcp.id_programa = prog.id
                            WHERE 
                                e.id = :estudiante_id
                                AND prog.id = :programa_id
                                AND ic.completado = 1
                                {$periodo_condition}
                            GROUP BY 
                                e.id, e.nombre, e.identificacion";
            
            $stmt_estudiante = $db->prepare($sql_estudiante);
            $stmt_estudiante->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
            $stmt_estudiante->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
            if($periodo_id){
                $stmt_estudiante->bindParam(':periodo_id', $periodo_id, PDO::PARAM_INT);
            }
            $stmt_estudiante->execute();
            $datos_estudiante = $stmt_estudiante->fetch(PDO::FETCH_ASSOC);
            
            // Si el estudiante no tiene cuestionarios completados
            if(!$datos_estudiante){
                return $this->successResponse($response, 'El estudiante no tiene cuestionarios completados en este programa', [
                    'estudiante' => [
                        'estudiante_id' => $estudiante_id,
                        'promedio' => 0,
                        'total_cuestionarios' => 0
                    ],
                    'programa' => [
                        'promedio_general' => 0,
                        'total_estudiantes' => 0
                    ],
                    'comparacion' => [
                        'diferencia' => 0,
                        'posicion' => null,
                        'percentil' => 0
                    ]
                ]);
            }
            
            // Calcular promedio general del programa
            $sql_programa = "SELECT 
                                COUNT(DISTINCT e.id) AS total_estudiantes,
                                AVG(ic.puntaje_total) AS promedio_programa,
                                MIN(ic.puntaje_total) AS puntaje_minimo_programa,
                                MAX(ic.puntaje_total) AS puntaje_maximo_programa,
                                STDDEV(ic.puntaje_total) AS desviacion_estandar
                            FROM 
                                estudiante e
                                INNER JOIN intento_cuestionario ic ON e.id = ic.id_estudiante
                                INNER JOIN apertura a ON ic.id_apertura = a.id
                                INNER JOIN periodo p ON a.id_periodo = p.id
                                INNER JOIN relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                                INNER JOIN programa prog ON rcp.id_programa = prog.id
                            WHERE 
                                prog.id = :programa_id
                                AND ic.completado = 1
                                {$periodo_condition}";
            
            $stmt_programa = $db->prepare($sql_programa);
            $stmt_programa->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
            if($periodo_id){
                $stmt_programa->bindParam(':periodo_id', $periodo_id, PDO::PARAM_INT);
            }
            $stmt_programa->execute();
            $datos_programa = $stmt_programa->fetch(PDO::FETCH_ASSOC);
            
            // Calcular posición del estudiante en el programa (ranking)
            $sql_ranking = "SELECT 
                                e.id,
                                AVG(ic.puntaje_total) AS promedio
                            FROM 
                                estudiante e
                                INNER JOIN intento_cuestionario ic ON e.id = ic.id_estudiante
                                INNER JOIN apertura a ON ic.id_apertura = a.id
                                INNER JOIN periodo p ON a.id_periodo = p.id
                                INNER JOIN relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                                INNER JOIN programa prog ON rcp.id_programa = prog.id
                            WHERE 
                                prog.id = :programa_id
                                AND ic.completado = 1
                                {$periodo_condition}
                            GROUP BY 
                                e.id
                            ORDER BY 
                                promedio DESC";
            
            $stmt_ranking = $db->prepare($sql_ranking);
            $stmt_ranking->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
            if($periodo_id){
                $stmt_ranking->bindParam(':periodo_id', $periodo_id, PDO::PARAM_INT);
            }
            $stmt_ranking->execute();
            $ranking = $stmt_ranking->fetchAll(PDO::FETCH_ASSOC);
            
            // Encontrar la posición del estudiante
            $posicion = null;
            $total_estudiantes_ranking = count($ranking);
            foreach($ranking as $index => $estudiante_ranking){
                if($estudiante_ranking['id'] == $estudiante_id){
                    $posicion = $index + 1;
                    break;
                }
            }
            
            // Calcular percentil
            $percentil = $total_estudiantes_ranking > 0 
                ? round((($total_estudiantes_ranking - $posicion + 1) / $total_estudiantes_ranking) * 100, 2)
                : 0;
            
            // Calcular diferencia con el promedio del programa
            $diferencia = round($datos_estudiante['promedio_estudiante'] - $datos_programa['promedio_programa'], 2);
            
            // Determinar rendimiento relativo
            $rendimiento = 'promedio';
            if($diferencia > 10){
                $rendimiento = 'sobresaliente';
            }elseif($diferencia > 0){
                $rendimiento = 'superior';
            }elseif($diferencia < -10){
                $rendimiento = 'bajo';
            }elseif($diferencia < 0){
                $rendimiento = 'inferior';
            }
            
            return $this->successResponse($response, 'Promedio calculado exitosamente', [
                'estudiante' => [
                    'estudiante_id' => (int)$datos_estudiante['estudiante_id'],
                    'nombre' => $datos_estudiante['estudiante_nombre'],
                    'identificacion' => $datos_estudiante['estudiante_identificacion'],
                    'promedio' => round($datos_estudiante['promedio_estudiante'], 2),
                    'puntaje_minimo' => round($datos_estudiante['puntaje_minimo'], 2),
                    'puntaje_maximo' => round($datos_estudiante['puntaje_maximo'], 2),
                    'total_cuestionarios_completados' => (int)$datos_estudiante['total_cuestionarios_completados']
                ],
                'programa' => [
                    'programa_id' => $programa_id,
                    'promedio_general' => round($datos_programa['promedio_programa'], 2),
                    'puntaje_minimo' => round($datos_programa['puntaje_minimo_programa'], 2),
                    'puntaje_maximo' => round($datos_programa['puntaje_maximo_programa'], 2),
                    'desviacion_estandar' => round($datos_programa['desviacion_estandar'], 2),
                    'total_estudiantes' => (int)$datos_programa['total_estudiantes']
                ],
                'comparacion' => [
                    'diferencia' => $diferencia,
                    'diferencia_porcentual' => $datos_programa['promedio_programa'] > 0 
                        ? round(($diferencia / $datos_programa['promedio_programa']) * 100, 2) 
                        : 0,
                    'posicion' => $posicion,
                    'total_estudiantes' => $total_estudiantes_ranking,
                    'percentil' => $percentil,
                    'rendimiento' => $rendimiento
                ]
            ]);
            
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al calcular promedio: ' . $e->getMessage(), 500);
        }finally{
            if($stmt_estudiante !== null){$stmt_estudiante = null;}
            if($stmt_programa !== null){$stmt_programa = null;}
            if($db !== null){$db = null;}
        }
    }

    public function validarCuestionariosCompletados(Request $request, Response $response, Array $args): Response{
        $db = null;
        $stmt_asignados = null;
        $stmt_completados = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $inputData = $this->getJsonInput($request);
            if(!$inputData){return $this->errorResponse($response, 'Datos JSON inválidos', 400);}
            
            // Validar campos requeridos
            if(!isset($inputData['estudiante_id']) || !isset($inputData['programa_id']) || !isset($inputData['periodo_id'])){
                return $this->errorResponse($response, 'Faltan campos requeridos: estudiante_id, programa_id, periodo_id', 400);
            }
            
            $db = $this->container->get('db');
            $estudiante_id = $inputData['estudiante_id'];
            $programa_id = $inputData['programa_id'];
            $periodo_id = $inputData['periodo_id'];
            
            // Obtener todos los cuestionarios asignados al estudiante para el programa y periodo específico
            $sql_asignados = "SELECT DISTINCT
                                c.id AS cuestionario_id,
                                c.titulo AS cuestionario_titulo,
                                c.descripcion AS cuestionario_descripcion,
                                a.id AS apertura_id,
                                asig.id AS asignacion_id
                            FROM 
                                asignacion asig
                                INNER JOIN apertura a ON asig.id_apertura = a.id
                                INNER JOIN periodo p ON a.id_periodo = p.id
                                INNER JOIN relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                                INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                                INNER JOIN programa prog ON rcp.id_programa = prog.id
                            WHERE 
                                asig.id_estudiante = :estudiante_id
                                AND prog.id = :programa_id
                                AND p.id = :periodo_id
                                AND a.activo = 1
                            ORDER BY 
                                c.titulo";
            
            $stmt_asignados = $db->prepare($sql_asignados);
            $stmt_asignados->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
            $stmt_asignados->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
            $stmt_asignados->bindParam(':periodo_id', $periodo_id, PDO::PARAM_INT);
            $stmt_asignados->execute();
            $cuestionarios_asignados = $stmt_asignados->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay cuestionarios asignados
            if(empty($cuestionarios_asignados)){
                return $this->successResponse($response, 'No hay cuestionarios asignados para este estudiante en el programa y periodo especificados', [
                    'puede_generar_informe' => false,
                    'total_cuestionarios' => 0,
                    'completados' => 0,
                    'pendientes' => 0,
                    'cuestionarios_pendientes' => [],
                    'cuestionarios_completados' => []
                ]);
            }
            
            // Obtener los cuestionarios completados por el estudiante
            $sql_completados = "SELECT DISTINCT
                                    c.id AS cuestionario_id,
                                    ic.completado,
                                    ic.fecha_fin,
                                    ic.puntaje_total
                                FROM 
                                    intento_cuestionario ic
                                    INNER JOIN apertura a ON ic.id_apertura = a.id
                                    INNER JOIN relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                                    INNER JOIN cuestionario c ON rcp.id_cuestionario = c.id
                                    INNER JOIN programa prog ON rcp.id_programa = prog.id
                                    INNER JOIN periodo p ON a.id_periodo = p.id
                                WHERE 
                                    ic.id_estudiante = :estudiante_id
                                    AND prog.id = :programa_id
                                    AND p.id = :periodo_id
                                    AND ic.completado = 1";
            
            $stmt_completados = $db->prepare($sql_completados);
            $stmt_completados->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
            $stmt_completados->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
            $stmt_completados->bindParam(':periodo_id', $periodo_id, PDO::PARAM_INT);
            $stmt_completados->execute();
            $cuestionarios_completados = $stmt_completados->fetchAll(PDO::FETCH_ASSOC);
            
            // Crear un array con los IDs de cuestionarios completados
            $ids_completados = array_column($cuestionarios_completados, 'cuestionario_id');
            
            // Identificar cuestionarios pendientes
            $cuestionarios_pendientes = [];
            $cuestionarios_completados_detalle = [];
            
            foreach($cuestionarios_asignados as $cuestionario){
                if(in_array($cuestionario['cuestionario_id'], $ids_completados)){
                    // Buscar los detalles del completado
                    $detalle_completado = array_filter($cuestionarios_completados, function($c) use ($cuestionario){
                        return $c['cuestionario_id'] == $cuestionario['cuestionario_id'];
                    });
                    $detalle_completado = array_values($detalle_completado)[0];
                    
                    $cuestionarios_completados_detalle[] = [
                        'cuestionario_id' => $cuestionario['cuestionario_id'],
                        'titulo' => $cuestionario['cuestionario_titulo'],
                        'descripcion' => $cuestionario['cuestionario_descripcion'],
                        'fecha_completado' => $detalle_completado['fecha_fin'],
                        'puntaje_total' => $detalle_completado['puntaje_total']
                    ];
                }else{
                    $cuestionarios_pendientes[] = [
                        'cuestionario_id' => $cuestionario['cuestionario_id'],
                        'titulo' => $cuestionario['cuestionario_titulo'],
                        'descripcion' => $cuestionario['cuestionario_descripcion'],
                        'apertura_id' => $cuestionario['apertura_id']
                    ];
                }
            }
            
            $total_cuestionarios = count($cuestionarios_asignados);
            $total_completados = count($cuestionarios_completados_detalle);
            $total_pendientes = count($cuestionarios_pendientes);
            $puede_generar_informe = ($total_pendientes === 0);
            
            $mensaje = $puede_generar_informe 
                ? 'El estudiante ha completado todos los cuestionarios. Puede generar el informe.' 
                : 'El estudiante tiene cuestionarios pendientes. No puede generar el informe hasta completarlos.';
            
            return $this->successResponse($response, $mensaje, [
                'puede_generar_informe' => $puede_generar_informe,
                'total_cuestionarios' => $total_cuestionarios,
                'completados' => $total_completados,
                'pendientes' => $total_pendientes,
                'cuestionarios_pendientes' => $cuestionarios_pendientes,
                'cuestionarios_completados' => $cuestionarios_completados_detalle
            ]);
            
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al validar cuestionarios: ' . $e->getMessage(), 500);
        }finally{
            if($stmt_asignados !== null){$stmt_asignados = null;}
            if($stmt_completados !== null){$stmt_completados = null;}
            if($db !== null){$db = null;}
        }
    }

}