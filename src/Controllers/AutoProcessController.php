<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\BaseController;
use Psr\Container\ContainerInterface;
use PDO;
use Exception;

/**
 * Controlador de Procesos Automáticos para API REST
 * Maneja operaciones automatizadas del sistema
 *
 * Funcionalidades:
 * - Procesar períodos expirados y crear intentos faltantes
 */
class AutoProcessController extends BaseController
{
    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
    }

    /**
     * Procesa períodos expirados y crea intentos faltantes
     * GET /api/autoprocess/expired-periods
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function processExpiredPeriods(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt_aperturas = null;
        $stmt_estudiantes = null;
        $stmt_preguntas = null;
        $stmt_intento = null;
        $stmt_respuesta = null;

        try {
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}

            // Obtener conexión a la base de datos
            $db = $this->container->get('db');

            // Obtener fecha actual
            $fecha_actual = date('Y-m-d');

            // Iniciar transacción
            $db->beginTransaction();

            // Paso 1: Encontrar aperturas activas con períodos expirados
            $query_aperturas = "SELECT
                    a.id as apertura_id,
                    a.id_periodo,
                    a.id_relacion_cuestionario_programa,
                    rcp.id_cuestionario,
                    per.fecha_fin
                FROM
                    apertura a
                JOIN
                    periodo per ON a.id_periodo = per.id
                JOIN
                    relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                WHERE
                    a.activo = 1
                    AND per.fecha_fin < :fecha_actual
                    AND per.activo = 1
            ";

            $stmt_aperturas = $db->prepare($query_aperturas);
            $stmt_aperturas->bindParam(':fecha_actual', $fecha_actual);
            $stmt_aperturas->execute();

            $aperturas_expiradas = $stmt_aperturas->fetchAll(PDO::FETCH_ASSOC);

            if (empty($aperturas_expiradas)) {
                $db->commit();
                return $this->successResponse($response, 'No hay aperturas expiradas para procesar', [
                    'procesadas' => 0
                ]);
            }

            $total_procesados = 0;
            $fecha_fin_proceso = date('Y-m-d H:i:s');

            // Paso 2: Procesar cada apertura expirada
            foreach ($aperturas_expiradas as $apertura) {
                // Encontrar estudiantes asignados que NO tienen intento completado
                $query_estudiantes = "SELECT
                        asig.id_estudiante
                    FROM
                        asignacion asig
                    LEFT JOIN
                        intento_cuestionario ic ON asig.id_estudiante = ic.id_estudiante
                        AND ic.id_apertura = :apertura_id
                        AND ic.completado = 1
                    WHERE
                        asig.id_apertura = :apertura_id
                        AND ic.id IS NULL
                ";

                $stmt_estudiantes = $db->prepare($query_estudiantes);
                $stmt_estudiantes->bindParam(':apertura_id', $apertura['apertura_id']);
                $stmt_estudiantes->execute();

                $estudiantes_sin_intento = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);

                // Paso 3: Obtener todas las preguntas del cuestionario
                $query_preguntas = "SELECT
                        id as pregunta_id
                    FROM
                        preguntas
                    WHERE
                        id_cuestionario = :cuestionario_id
                    ORDER BY
                        orden_pregunta
                ";

                $stmt_preguntas = $db->prepare($query_preguntas);
                $stmt_preguntas->bindParam(':cuestionario_id', $apertura['id_cuestionario']);
                $stmt_preguntas->execute();

                $preguntas = $stmt_preguntas->fetchAll(PDO::FETCH_ASSOC);

                // Paso 4: Crear intentos y respuestas para cada estudiante sin intento
                foreach ($estudiantes_sin_intento as $estudiante) {
                    // Crear intento con calificación 0
                    $query_intento = "INSERT INTO intento_cuestionario
                            (id_estudiante, id_apertura, fecha_inicio, fecha_fin, completado, puntaje_total)
                        VALUES
                            (:estudiante_id, :apertura_id, :fecha_inicio, :fecha_fin, 1, 0)
                    ";

                    $stmt_intento = $db->prepare($query_intento);
                    $stmt_intento->bindParam(':estudiante_id', $estudiante['id_estudiante']);
                    $stmt_intento->bindParam(':apertura_id', $apertura['apertura_id']);
                    $stmt_intento->bindParam(':fecha_inicio', $apertura['fecha_fin']); // Fecha inicio = fecha fin del período
                    $stmt_intento->bindParam(':fecha_fin', $fecha_fin_proceso);
                    $stmt_intento->execute();

                    $intento_id = $db->lastInsertId();

                    // Insertar respuestas NULL para todas las preguntas
                    $query_respuesta = "INSERT INTO respuesta_estudiante
                            (id_intento, id_pregunta, id_opcion_seleccionada, fecha_respuesta)
                        VALUES
                            (:intento_id, :pregunta_id, NULL, :fecha_respuesta)
                    ";

                    $stmt_respuesta = $db->prepare($query_respuesta);

                    foreach ($preguntas as $pregunta) {
                        $stmt_respuesta->bindParam(':intento_id', $intento_id);
                        $stmt_respuesta->bindParam(':pregunta_id', $pregunta['pregunta_id']);
                        $stmt_respuesta->bindParam(':fecha_respuesta', $fecha_fin_proceso);
                        $stmt_respuesta->execute();
                    }

                    $total_procesados++;
                }
            }

            // Confirmar transacción
            $db->commit();

            return $this->successResponse($response, 'Procesamiento de períodos expirados completado exitosamente', [
                'aperturas_procesadas' => count($aperturas_expiradas),
                'intentos_creados' => $total_procesados,
                'fecha_procesamiento' => $fecha_fin_proceso
            ]);

        } catch (Exception $e) {
            // Rollback en caso de error
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }

            error_log("Error en processExpiredPeriods: " . $e->getMessage());
            return $this->errorResponse($response, 'Error interno del servidor al procesar períodos expirados' . $e->getMessage(), 500);
        } finally {
            if ($stmt_aperturas !== null) {
                $stmt_aperturas = null;
            }
            if ($stmt_estudiantes !== null) {
                $stmt_estudiantes = null;
            }
            if ($stmt_preguntas !== null) {
                $stmt_preguntas = null;
            }
            if ($stmt_intento !== null) {
                $stmt_intento = null;
            }
            if ($stmt_respuesta !== null) {
                $stmt_respuesta = null;
            }
            if ($db !== null) {
                $db = null;
            }
        }
    }
}
