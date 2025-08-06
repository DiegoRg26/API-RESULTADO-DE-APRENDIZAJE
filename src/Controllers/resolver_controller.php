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

class resolver_controller extends BaseController{
    public function __construct(ContainerInterface $c){
        parent::__construct($c);
    }

    public function verificarResolucion(Request $request, Response $response, array $args): Response{
        try{
            $db = $this->container->get('db');
            $cuestionario_id = $args['id'];
            $estudianteData = $this->getUserDataFromToken($request);
            $estudiante_id = $estudianteData['id'];
            // Verificar que el cuestionario existe y está activo
            $query_cuestionario = "SELECT 
                c.id, 
                c.titulo, 
                c.descripcion, 
                rcp.id as relacion_id,
                d.nombre as creador_nombre,
                p.nombre as programa_nombre,
                n.nombre as nivel_nombre,
                cam.nombre as campus_nombre
            FROM 
                cuestionario c
            JOIN 
                relacion_cuestionario_programa rcp ON c.id = rcp.id_cuestionario
            JOIN 
                docente d ON rcp.id_docente = d.id
            JOIN 
                programa p ON rcp.id_programa = p.id
            JOIN 
                nivel n ON p.id_nivel = n.id
            JOIN 
                campus cam ON p.id_campus = cam.id
            WHERE 
                c.id = :id 
                AND rcp.activo = 1
            LIMIT 1";

            $stmt_cuestionario = $this->$db->prepare($query_cuestionario);
            $stmt_cuestionario->bindParam(':id', $cuestionario_id);
            $stmt_cuestionario->execute();
            if($stmt_cuestionario->rowCount() == 0){
                return $this->errorResponse($response, 'Cuestionario con id: ' . $cuestionario_id . ' no encontrado', 404);
            }
            $cuestionario = $stmt_cuestionario->fetch(PDO::FETCH_ASSOC);

            // Verificar si el periodo está activo
            $query_periodo = "SELECT 
                p.fecha_inicio, 
                p.fecha_fin, 
                p.nombre as periodo_nombre
            FROM 
                apertura a
            JOIN 
                periodo p ON a.id_periodo = p.id
            JOIN 
                relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
            JOIN
                asignacion asig ON a.id = asig.id_apertura
            WHERE 
                rcp.id_cuestionario = :cuestionario_id
                AND asig.id_estudiante = :estudiante_id
                AND a.activo = 1
            LIMIT 1";

            $stmt_periodo = $db->prepare($query_periodo);
            $stmt_periodo->bindParam(':cuestionario_id', $cuestionario_id);
            $stmt_periodo->bindParam(':estudiante_id', $estudiante_id);
            $stmt_periodo->execute();
            if($stmt_periodo->rowCount() == 0){
                return $this->errorResponse($response, 'El estudiante no tiene acceso al cuestionario', 403);
            }
            $periodo = $stmt_periodo->fetch(PDO::FETCH_ASSOC);
            $fecha_actual = date('Y-m-d');
            $fecha_inicio = $periodo['fecha_inicio'];
            $fecha_fin = $periodo['fecha_fin'];
            if($fecha_actual < $fecha_inicio || $fecha_actual > $fecha_fin){
                return $this->errorResponse($response, 'El cuestionario no está disponible, fechas válidas: ' . $fecha_inicio . ' - ' . $fecha_fin, 403);
            }
            // Verificar si ya resolvió este cuestionario en la apertura actual
            $query_verificar = "SELECT ic.id 
                                FROM intento_cuestionario ic
                                JOIN apertura a ON ic.id_apertura = a.id
                                JOIN relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                                WHERE ic.id_estudiante = :usuario_id 
                                AND rcp.id_cuestionario = :cuestionario_id
                                AND ic.completado = 1
                                AND a.activo = 1
                                LIMIT 1";
            $stmt_verificar = $db->prepare($query_verificar);
            $stmt_verificar->bindParam(':usuario_id', $estudiante_id);
            $stmt_verificar->bindParam(':cuestionario_id', $cuestionario_id);
            $stmt_verificar->execute();
            if($stmt_verificar->rowCount() > 0){
                return $this->errorResponse($response, 'Ya has realizado este cuestionario en el periodo actual', 403);
            }
            return $this->successResponse($response, 'Cuestionario disponible', [
                'cuestionario' => $cuestionario,
                'periodo' => $periodo
            ]);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al verificar la resolución: ' . $e->getMessage(), 500);
        }
    }

    public function obtenerPreguntasyOpciones(Request $request, Response $response, array $args): Response{
        try{
            $cuestionario_id = $args['id'];
            $db = $this->container->get('db');
            $sql_preguntas = "SELECT 
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
                            ORDER BY p.orden_pregunta, o.orden";
            $stmt_preguntas = $db->prepare($sql_preguntas);
            $stmt_preguntas->bindParam(':cuestionario_id', $cuestionario_id);
            $stmt_preguntas->execute();
            $preguntas_raw = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);
            $preguntas = [];

            foreach ($preguntas_raw as $row) {
                $pregunta_id = $row['pregunta_id'];
                $imagen_pregunta = $row['imagen_pregunta'];
                $imagen_opcion = $row['imagen_opcion'];
                $imagen_pregunta_base64 = base64_encode($imagen_pregunta);
                $imagen_opcion_base64 = base64_encode($imagen_opcion);
                // Inicializar la pregunta si no existe
                if (!isset($preguntas[$pregunta_id])) {
                    $preguntas[$pregunta_id] = [
                        'id' => $pregunta_id,
                        'texto_pregunta' => $row['texto_pregunta'],
                        'orden_pregunta' => $row['orden_pregunta'],
                        'peso_pregunta' => $row['peso_pregunta'],
                        'imagen_pregunta' => $imagen_pregunta_base64,
                        'opciones' => []
                    ];
                }
                // Agregar opción si existe
                if ($row['opcion_id'] !== null) {
                    $preguntas[$pregunta_id]['opciones'][] = [
                        'id' => $row['opcion_id'],
                        'texto_opcion' => $row['texto_opcion'],
                        'imagen_opcion' => $imagen_opcion_base64,
                        'es_correcta' => (bool) $row['opcion_correcta'], // Convertir a booleano
                        'orden_opcion' => $row['orden']
                    ];
                }
            }
            $resultado = array_values($preguntas);
            return $this->successResponse($response, 'Preguntas y opciones obtenidas correctamente', $resultado);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener las preguntas y opciones: ' . $e->getMessage(), 500);
        }
    }

    public function guardarIntento(Request $request, Response $response, array $args): Response{
        try{
            $db = $this->container->get('db');
            $cuestionario_id = $args['id'];
            
            // $estudiante_id = $this->getUserIdFromToken($request);  //Habilitar cuando este en produccion

            // Obtener datos del JSON
            $inputData = $this->getJsonInput($request);
            if (!$inputData) {
                return $this->errorResponse($response, 'Datos JSON inválidos', 400);
            }
            $estudiante_id = $inputData['estudiante_id']; //Deshabilitar cuando este desplegada el API, unicamente fue creada para TESTING
            
            // Validar campos requeridos
            $requiredFields = ['respuestas', 'tiempo_utilizado'];
            foreach ($requiredFields as $field) {
                if (!isset($inputData[$field])) {
                    return $this->errorResponse($response, "Campo requerido faltante: $field", 400);
                }
            }
            $respuestas = $inputData['respuestas'];
            $tiempo_utilizado = $inputData['tiempo_utilizado']; // En minutos o segundos según tu frontend
            
            // Verificar que el cuestionario existe y está activo
            $query_verificar_cuestionario = "SELECT 
                c.id, 
                a.id as apertura_id,
                p.fecha_inicio, 
                p.fecha_fin,
                c.tiempo_limite
            FROM 
                cuestionario c
            JOIN 
                relacion_cuestionario_programa rcp ON c.id = rcp.id_cuestionario
            JOIN 
                apertura a ON rcp.id = a.id_relacion_cuestionario_programa
            JOIN
                asignacion asig ON a.id = asig.id_apertura
            JOIN 
                periodo p ON a.id_periodo = p.id
            WHERE 
                c.id = :cuestionario_id
                AND asig.id_estudiante = :estudiante_id
                AND a.activo = 1
                AND rcp.activo = 1
            LIMIT 1";
            
            $stmt_verificar = $db->prepare($query_verificar_cuestionario);
            $stmt_verificar->bindParam(':cuestionario_id', $cuestionario_id);
            $stmt_verificar->bindParam(':estudiante_id', $estudiante_id);
            $stmt_verificar->execute();
            
            if($stmt_verificar->rowCount() == 0){
                return $this->errorResponse($response, 'Cuestionario no disponible para este estudiante', 403);
            }
            
            $cuestionario_data = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
            $apertura_id = $cuestionario_data['apertura_id'];
            
            // Verificar fechas del periodo
            $fecha_actual = date('Y-m-d');
            if($fecha_actual < $cuestionario_data['fecha_inicio'] || $fecha_actual > $cuestionario_data['fecha_fin']){
                return $this->errorResponse($response, 'El cuestionario no está en periodo válido', 403);
            }
            
            // Verificar si ya completó este cuestionario
            $query_verificar_completado = "SELECT id FROM intento_cuestionario 
                                            WHERE id_estudiante = :estudiante_id 
                                            AND id_apertura = :apertura_id 
                                            AND completado = 1
                                            LIMIT 1";
            $stmt_completado = $db->prepare($query_verificar_completado);
            $stmt_completado->bindParam(':estudiante_id', $estudiante_id);
            $stmt_completado->bindParam(':apertura_id', $apertura_id);
            $stmt_completado->execute();
            
            if($stmt_completado->rowCount() > 0){
                return $this->errorResponse($response, 'Ya has completado este cuestionario', 403);
            }
            
            // Iniciar transacción
            $db->beginTransaction();
            
            // Calcular puntaje
            $puntaje_total = 0;
            $respuestas_correctas = 0;
            $total_preguntas = 0;
            
            // Obtener información de las preguntas y opciones correctas
            $query_preguntas = "SELECT 
                p.id as pregunta_id,
                p.peso_pregunta,
                o.id as opcion_id,
                o.opcion_correcta
            FROM preguntas p
            LEFT JOIN opcion_respuesta o ON p.id = o.id_pregunta
            WHERE p.id_cuestionario = :cuestionario_id
            ORDER BY p.id, o.id";
            
            $stmt_preguntas = $db->prepare($query_preguntas);
            $stmt_preguntas->bindParam(':cuestionario_id', $cuestionario_id);
            $stmt_preguntas->execute();
            $preguntas_info = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);
            
            // Organizar datos de preguntas
            $preguntas_data = [];
            foreach ($preguntas_info as $row) {
                $pregunta_id = $row['pregunta_id'];
                if (!isset($preguntas_data[$pregunta_id])) {
                    $preguntas_data[$pregunta_id] = [
                        'peso' => $row['peso_pregunta'],
                        'opciones_correctas' => []
                    ];
                    $total_preguntas++;
                }
                if ($row['opcion_correcta'] == 1) {
                    $preguntas_data[$pregunta_id]['opciones_correctas'][] = $row['opcion_id'];
                }
            }
            
            // Crear registro de intento
            $fecha_inicio = date('Y-m-d H:i:s', strtotime("-$tiempo_utilizado seconds")); // Calcular fecha inicio basada en tiempo utilizado
            $fecha_fin = date('Y-m-d H:i:s');
            
            $query_intento = "INSERT INTO intento_cuestionario 
                            (id_estudiante, id_apertura, fecha_inicio, fecha_fin, completado, puntaje_total) 
                            VALUES (:estudiante_id, :apertura_id, :fecha_inicio, :fecha_fin, 1, :puntaje_total)";
            
            $stmt_intento = $db->prepare($query_intento);
            $stmt_intento->bindParam(':estudiante_id', $estudiante_id);
            $stmt_intento->bindParam(':apertura_id', $apertura_id);
            $stmt_intento->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt_intento->bindParam(':fecha_fin', $fecha_fin);
            
            // Calcular puntaje antes de insertar
            foreach ($respuestas as $respuesta) {
                if (!isset($respuesta['pregunta_id']) || !isset($respuesta['opcion_id'])) {
                    $db->rollBack();
                    return $this->errorResponse($response, 'Formato de respuesta inválido', 400);
                }
                
                $pregunta_id = $respuesta['pregunta_id'];
                $opcion_seleccionada = $respuesta['opcion_id'];
                
                if (isset($preguntas_data[$pregunta_id])) {
                    // Verificar si la opción seleccionada es correcta
                    if (in_array($opcion_seleccionada, $preguntas_data[$pregunta_id]['opciones_correctas'])) {
                        $puntaje_total += $preguntas_data[$pregunta_id]['peso'];
                        $respuestas_correctas++;
                    }
                }
            }
            
            $stmt_intento->bindParam(':puntaje_total', $puntaje_total);
            $stmt_intento->execute();
            
            $intento_id = $db->lastInsertId();
            
            // Guardar respuestas individuales
            $query_respuesta = "INSERT INTO respuesta_estudiante 
                                (id_intento, id_pregunta, id_opcion_seleccionada, fecha_respuesta) 
                                VALUES (:intento_id, :pregunta_id, :opcion_id, :fecha_respuesta)";
            $stmt_respuesta = $db->prepare($query_respuesta);
            
            foreach ($respuestas as $respuesta) {
                $stmt_respuesta->bindParam(':intento_id', $intento_id);
                $stmt_respuesta->bindParam(':pregunta_id', $respuesta['pregunta_id']);
                $stmt_respuesta->bindParam(':opcion_id', $respuesta['opcion_id']);
                $stmt_respuesta->bindParam(':fecha_respuesta', $fecha_fin);
                $stmt_respuesta->execute();
            }
            
            // Confirmar transacción
            $db->commit();
            
            // Calcular porcentaje
            $porcentaje = $total_preguntas > 0 ? round(($respuestas_correctas / $total_preguntas) * 100, 2) : 0;
            
            return $this->successResponse($response, 'Intento guardado exitosamente', [
                'intento_id' => $intento_id,
                'puntaje_total' => $puntaje_total,
                'respuestas_correctas' => $respuestas_correctas,
                'total_preguntas' => $total_preguntas,
                'porcentaje' => $porcentaje,
                'tiempo_utilizado' => $tiempo_utilizado,
                'fecha_completado' => $fecha_fin
            ]);
            
        } catch(Exception $e) {
            // Rollback en caso de error
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return $this->errorResponse($response, 'Error al guardar el intento: ' . $e->getMessage(), 500);
        }
    }

}