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

/**
 * Controlador para el menú de cuestionarios
 * Maneja la obtención de cuestionarios creados por el usuario y cuestionarios abiertos
 * con autenticación JWT y respuestas JSON
 */
class MenuCuestionario_controller extends BaseController
{
	private $jwtSecret;
	
	public function __construct(ContainerInterface $c)
	{
		parent::__construct($c);
		$this->jwtSecret = $_ENV['JWT_SECRET'];
	}
	
	/**
	 * Obtiene los cuestionarios creados por el usuario autenticado
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getMisCuestionarios(Request $request, Response $response, array $args): Response
	{
		try {
			// Obtener conexión a la base de datos
			$db = $this->container->get('db');
			
			// Obtener y validar el token JWT
			$userId = $this->getUserIdFromToken($request);
			if (!$userId) {
				return $this->errorResponse($response, 'Token inválido o no proporcionado', 401);
			}
			
			// Consulta para obtener cuestionarios del usuario
			$query = "SELECT rcp.*, c.titulo, c.descripcion 
				        FROM relacion_cuestionario_programa rcp 
				        JOIN cuestionario c ON rcp.id_cuestionario = c.id
					    WHERE rcp.id_docente = :docente_id
					    ORDER BY c.titulo ASC";
			
			$stmt = $db->prepare($query);
			$stmt->bindParam(':docente_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			
			$misCuestionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return $this->successResponse($response, 'Cuestionarios obtenidos exitosamente', [
				'cuestionarios' => $misCuestionarios,
				'total' => count($misCuestionarios)
			]);
			
		} catch (Exception $e) {
			error_log("Error en getMisCuestionarios: " . $e->getMessage());
			return $this->errorResponse($response, 'Error interno del servidor', 500);
		}
	}
	
	/**
	 * Obtiene los cuestionarios abiertos (con periodo asignado) del usuario autenticado
	 * Incluye el estado del cuestionario según las fechas
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getCuestionariosAbiertos(Request $request, Response $response, array $args): Response
	{
		try {
			// Obtener conexión a la base de datos
			$db = $this->container->get('db');
			
			// Obtener y validar el token JWT
			$userId = $this->getUserIdFromToken($request);
			if (!$userId) {
				return $this->errorResponse($response, 'Token inválido o no proporcionado', 401);
			}
			
			// Consulta para obtener cuestionarios abiertos
			$query = "
				SELECT 
					a.id as apertura_id,
					c.id as cuestionario_id,
					c.titulo,
					c.descripcion,
					p.id as periodo_id,
					p.nombre as periodo_nombre,
					p.fecha_inicio,
					p.fecha_fin,
					d.nombre as creador_nombre,
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
					docente d ON rcp.id_docente = d.id
				JOIN 
					programa prog ON rcp.id_programa = prog.id
				WHERE 
					rcp.activo = 1
					AND rcp.id_docente = :usuario_id
					AND a.activo = 1
				ORDER BY 
					p.fecha_inicio DESC, c.titulo ASC
			";
			
			$stmt = $db->prepare($query);
			$stmt->bindParam(':usuario_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			
			$cuestionariosAbiertos = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			// Asignar el estado a cada cuestionario según las fechas
			foreach ($cuestionariosAbiertos as $key => $cuestionario) {
				$cuestionariosAbiertos[$key]['estado'] = $this->determinarEstadoCuestionario(
					$cuestionario['fecha_inicio'],
					$cuestionario['fecha_fin']
				);
			}
			
			return $this->successResponse($response, 'Cuestionarios abiertos obtenidos exitosamente', [
				'cuestionarios_abiertos' => $cuestionariosAbiertos,
				'total' => count($cuestionariosAbiertos)
			]);
			
		} catch (Exception $e) {
			error_log("Error en getCuestionariosAbiertos: " . $e->getMessage());
			return $this->errorResponse($response, 'Error interno del servidor', 500);
		}
	}
	
	/**
	 * Determina el estado del cuestionario según las fechas de inicio y fin
	 * 
	 * @param string $fechaInicio Fecha de inicio del periodo
	 * @param string $fechaFin Fecha de fin del periodo
	 * @return string Estado del cuestionario: 'Programado', 'Disponible' o 'Cerrado'
	 */
	private function determinarEstadoCuestionario(string $fechaInicio, string $fechaFin): string
	{
		$hoy = date('Y-m-d');
		
		if ($hoy < $fechaInicio) {
			return 'Programado';
		} elseif ($hoy >= $fechaInicio && $hoy <= $fechaFin) {
			return 'Disponible';
		} else {
			return 'Cerrado';
		}
	}
}