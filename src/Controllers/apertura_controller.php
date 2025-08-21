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
 * Controlador de Aperturas para API REST
 * Maneja operaciones CRUD de aperturas de cuestionarios en periodos
 * Migrado desde apertura_controller.php (Legacy)
 * 
 * Funcionalidades:
 * - Obtener cuestionarios sin aperturas activas
 * - Obtener periodos activos para asignar
 * - Obtener aperturas existentes del usuario
 * - Crear aperturas (asignar cuestionario a periodo)
 * - Desactivar aperturas
 */
class apertura_controller extends BaseController
{
	private $jwtSecret;
	
	public function __construct(ContainerInterface $c)
	{
		parent::__construct($c);
		$this->jwtSecret = $_ENV['JWT_SECRET'];
	}

	/**
	 * Obtiene los cuestionarios del usuario que no tienen aperturas activas
	 * GET /api/aperturas/cuestionarios-disponibles
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getCuestionariosDisponibles(Request $request, Response $response, array $args): Response{
		$stmt = null;
		$db = null;
		try {
			// Obtener conexión a la base de datos
			$db = $this->container->get('db');
			
			// Obtener y validar el token JWT
			$userId = $this->getUserIdFromToken($request);
			if (!$userId) {
				return $this->errorResponse($response, 'Token inválido o no proporcionado', 401);
			}
			
			// Consulta para obtener cuestionarios sin aperturas activas
			$query = "
				SELECT 
					rcp.id, 
					c.id as cuestionario_id,
					c.titulo, 
					c.descripcion,
					p.nombre as programa_nombre
				FROM 
					relacion_cuestionario_programa rcp 
				JOIN 
					cuestionario c ON rcp.id_cuestionario = c.id
				JOIN 
					programa p ON rcp.id_programa = p.id
				LEFT JOIN 
					apertura a ON rcp.id = a.id_relacion_cuestionario_programa AND a.activo = 1
				WHERE 
					rcp.id_docente = :docente_id
					AND rcp.activo = 1
					AND a.id IS NULL
			";
			
			$stmt = $db->prepare($query);
			$stmt->bindParam(':docente_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			
			$cuestionariosDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return $this->successResponse($response, 'Cuestionarios disponibles obtenidos exitosamente', [
				'cuestionarios' => $cuestionariosDisponibles,
				'total' => count($cuestionariosDisponibles)
			]);
			
		} catch (Exception $e) {
			error_log("Error en getCuestionariosDisponibles: " . $e->getMessage());
			return $this->errorResponse($response, 'Error interno del servidor', 500);
		}finally{
			if($stmt !== null){
				$stmt = null;
			}
			if($db !== null){
				$db = null;
			}
		}
	}

	/**
	 * Obtiene los periodos activos disponibles para asignar
	 * GET /api/aperturas/periodos-activos
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getPeriodosActivos(Request $request, Response $response, array $args): Response{
		$stmt = null;
		$db = null;
		try {
			// Obtener conexión a la base de datos
			$db = $this->container->get('db');
			
			// Obtener y validar el token JWT
			$userId = $this->getUserIdFromToken($request);
			if (!$userId) {
				return $this->errorResponse($response, 'Token inválido o no proporcionado', 401);
			}
			
			// Consulta para obtener periodos activos
			$query = "SELECT id, nombre, fecha_inicio, fecha_fin FROM periodo WHERE activo = 1 ORDER BY fecha_inicio DESC";
			$stmt = $db->prepare($query);
			$stmt->execute();
			
			$periodosActivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return $this->successResponse($response, 'Periodos activos obtenidos exitosamente', [
				'periodos' => $periodosActivos,
				'total' => count($periodosActivos)
			]);
			
		} catch (Exception $e) {
			error_log("Error en getPeriodosActivos: " . $e->getMessage());
			return $this->errorResponse($response, 'Error interno del servidor', 500);
		}finally{
			if($stmt !== null){
				$stmt = null;
			}
			if($db !== null){
				$db = null;
			}
		}
	}

	/**
	 * Obtiene las aperturas activas del usuario autenticado
	 * GET /api/aperturas
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getAperturas(Request $request, Response $response, array $args): Response{
		$stmt = null;
		$db = null;
		try {
			// Obtener conexión a la base de datos
			$db = $this->container->get('db');
			
			// Obtener y validar el token JWT
			$userId = $this->getUserIdFromToken($request);
			if (!$userId) {
				return $this->errorResponse($response, 'Token inválido o no proporcionado', 401);
			}
			
			// Consulta para obtener aperturas activas del usuario
			$query = "SELECT 
					a.id, 
					c.titulo, 
					c.descripcion,
					p.nombre as programa_nombre,
					per.nombre as periodo_nombre,
					per.fecha_inicio,
					per.fecha_fin,
					a.activo,
					c.id as cuestionario_id,
					per.id as periodo_id
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
					rcp.id_docente = :docente_id
					AND a.activo = 1
				ORDER BY 
					per.fecha_inicio DESC
			";
			
			$stmt = $db->prepare($query);
			$stmt->bindParam(':docente_id', $userId, PDO::PARAM_INT);
			$stmt->execute();
			
			$aperturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			// Asignar el estado a cada apertura según las fechas
			foreach ($aperturas as $key => $apertura) {
				$aperturas[$key]['estado'] = $this->determinarEstadoCuestionario(
					$apertura['fecha_inicio'],
					$apertura['fecha_fin']
				);
			}
			
			return $this->successResponse($response, 'Aperturas obtenidas exitosamente', [
				'aperturas' => $aperturas,
				'total' => count($aperturas)
			]);
			
		} catch (Exception $e) {
			error_log("Error en getAperturas: " . $e->getMessage());
			return $this->errorResponse($response, 'Error interno del servidor', 500);
		}finally{
			if($stmt !== null){
				$stmt = null;
			}
			if($db !== null){
				$db = null;
			}
		}
	}

	/**
	 * Crea una nueva apertura (asigna un cuestionario a un periodo)
	 * POST /api/aperturas
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function create(Request $request, Response $response, array $args): Response{
		$db = null;
		$stmt_verificar = null;
		$stmt_verificar_apertura = null;
		$stmt_crear_apertura = null;
		$stmt_nueva_apertura = null;
		try {
			// Obtener conexión a la base de datos
			$db = $this->container->get('db');
			
			// Obtener y validar el token JWT
			$userId = $this->getUserIdFromToken($request);
			if (!$userId) {
				return $this->errorResponse($response, 'Token inválido o no proporcionado', 401);
			}
			
			// Obtener y validar datos JSON del request
			$inputData = $this->getJsonInput($request);
			
			if (!$inputData) {
				return $this->errorResponse($response, 'Datos JSON inválidos', 400);
			}
			
			// Validar campos requeridos
			if (!isset($inputData['cuestionario_id']) || empty($inputData['cuestionario_id'])) {
				return $this->errorResponse($response, 'El ID del cuestionario es requerido', 400);
			}
			
			if (!isset($inputData['periodo_id']) || empty($inputData['periodo_id'])) {
				return $this->errorResponse($response, 'El ID del periodo es requerido', 400);
			}
			
			$cuestionarioId = $this->sanitizeInput($inputData['cuestionario_id']);
			$periodoId = $this->sanitizeInput($inputData['periodo_id']);
			
			// Verificar que el cuestionario pertenezca al usuario actual
			$query_verificar = "
				SELECT 
					rcp.id
				FROM 
					relacion_cuestionario_programa rcp
				WHERE 
					rcp.id = :cuestionario_id
					AND rcp.id_docente = :docente_id
					AND rcp.activo = 1
			";
			$stmt_verificar = $db->prepare($query_verificar);
			$stmt_verificar->bindParam(':cuestionario_id', $cuestionarioId, PDO::PARAM_INT);
			$stmt_verificar->bindParam(':docente_id', $userId, PDO::PARAM_INT);
			$stmt_verificar->execute();
			
			if ($stmt_verificar->rowCount() === 0) {
				return $this->errorResponse($response, 'No tiene permisos para crear una apertura para este cuestionario o no existe', 403);
			}
			
			$relacionId = $stmt_verificar->fetch(PDO::FETCH_ASSOC)['id'];
			
			// Verificar que no exista ya una apertura activa para este cuestionario
			$query_verificar_apertura = "
				SELECT 
					id
				FROM 
					apertura
				WHERE 
					id_relacion_cuestionario_programa = :relacion_id
					AND activo = 1
			";
			$stmt_verificar_apertura = $db->prepare($query_verificar_apertura);
			$stmt_verificar_apertura->bindParam(':relacion_id', $relacionId, PDO::PARAM_INT);
			$stmt_verificar_apertura->execute();
			
			if ($stmt_verificar_apertura->rowCount() > 0) {
				return $this->errorResponse($response, 'Este cuestionario ya tiene una apertura activa asignada', 409);
			}
			
			// Crear la apertura con activo = 1 por defecto
			$query_crear_apertura = "
				INSERT INTO apertura 
					(id_periodo, id_relacion_cuestionario_programa, activo) 
				VALUES 
					(:periodo_id, :relacion_id, 1)
			";
			$stmt_crear_apertura = $db->prepare($query_crear_apertura);
			$stmt_crear_apertura->bindParam(':periodo_id', $periodoId, PDO::PARAM_INT);
			$stmt_crear_apertura->bindParam(':relacion_id', $relacionId, PDO::PARAM_INT);
			$stmt_crear_apertura->execute();
			
			$aperturaId = $db->lastInsertId();
			
			// Obtener datos de la apertura recién creada
			$query_nueva_apertura = "
				SELECT 
					a.id, 
					c.titulo, 
					c.descripcion,
					p.nombre as programa_nombre,
					per.nombre as periodo_nombre,
					per.fecha_inicio,
					per.fecha_fin,
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
			";
			$stmt_nueva_apertura = $db->prepare($query_nueva_apertura);
			$stmt_nueva_apertura->bindParam(':apertura_id', $aperturaId, PDO::PARAM_INT);
			$stmt_nueva_apertura->execute();
			
			$nuevaApertura = $stmt_nueva_apertura->fetch(PDO::FETCH_ASSOC);
			
			return $this->successResponse($response, 'Apertura creada correctamente', [
				'apertura' => $nuevaApertura
			]);
			
		} catch (Exception $e) {
			error_log("Error en create apertura: " . $e->getMessage());
			return $this->errorResponse($response, 'Error interno del servidor', 500);
		}finally{
			if($stmt_verificar !== null){
				$stmt_verificar = null;
			}
			if($stmt_verificar_apertura !== null){
				$stmt_verificar_apertura = null;
			}
			if($stmt_crear_apertura !== null){
				$stmt_crear_apertura = null;
			}
			if($stmt_nueva_apertura !== null){
				$stmt_nueva_apertura = null;
			}
			if($db !== null){
				$db = null;
			}
		}
	}

	/**
	 * Desactiva una apertura existente
	 * DELETE /api/aperturas/{id}
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function deactivate(Request $request, Response $response, array $args): Response{
		$stmt_verificar = null;
		$stmt_desactivar = null;
		$db = null;
		try {
			// Obtener conexión a la base de datos
			$db = $this->container->get('db');
			
			// Obtener y validar el token JWT
			$userId = $this->getUserIdFromToken($request);
			if (!$userId) {
				return $this->errorResponse($response, 'Token inválido o no proporcionado', 401);
			}
			
			// Obtener ID de la apertura a desactivar
			$aperturaId = $args['apertura_id'] ?? null;
			if (!$aperturaId) {
				return $this->errorResponse($response, 'ID de apertura requerido', 400);
			}
			
			// Verificar que la apertura pertenezca al usuario actual
			$query_verificar = "SELECT 
					a.id
				FROM 
					apertura a
				JOIN 
					relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
				WHERE 
					a.id = :apertura_id
					AND rcp.id_docente = :docente_id
					AND a.activo = 1
			";
			$stmt_verificar = $db->prepare($query_verificar);
			$stmt_verificar->bindParam(':apertura_id', $aperturaId, PDO::PARAM_INT);
			$stmt_verificar->bindParam(':docente_id', $userId, PDO::PARAM_INT);
			$stmt_verificar->execute();
			
			if ($stmt_verificar->rowCount() === 0) {
				return $this->errorResponse($response, 'No tiene permisos para desactivar esta apertura o no existe', 403);
			}
			
			// Desactivar la apertura
			$query_desactivar = "UPDATE apertura SET activo = 0 WHERE id = :apertura_id";
			$stmt_desactivar = $db->prepare($query_desactivar);
			$stmt_desactivar->bindParam(':apertura_id', $aperturaId, PDO::PARAM_INT);
			$stmt_desactivar->execute();
			
			return $this->successResponse($response, 'Apertura desactivada correctamente', [
				'apertura_id' => $aperturaId
			]);
			
		} catch (Exception $e) {
			error_log("Error en deactivate apertura: " . $e->getMessage());
			return $this->errorResponse($response, 'Error interno del servidor', 500);
		}finally{
			if($stmt_verificar !== null){
				$stmt_verificar = null;
			}
			if($stmt_desactivar !== null){
				$stmt_desactivar = null;
			}
			if($db !== null){
				$db = null;
			}
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

	public function apertInfo(Request $request, Response $response, array $args){
		$db = null;
		$stmt = null;
		try{
			$apertura_id = $args["id"];
			if($apertura_id <= 0){return $this->errorResponse($response, 'ID de apertura inválido', 400);}
			$docente_id = $this->getUserIdFromToken($request);
			if(!$docente_id){return $this->errorResponse($response, 'Token inválido o no proporcionado', 401);}
			$db = $this->container->get('db');
			$sql_apertura = "SELECT 
								a.id as apertura_id,
								c.id as cuestionario_id,
								c.titulo,
								c.descripcion,
								p.nombre as programa_nombre,
								per.nombre as periodo_nombre,
								per.fecha_inicio,
								per.fecha_fin,
								a.activo as apertura_activa
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
			$stmt = $db->prepare($sql_apertura);
			$stmt->bindParam("apertura_id", $apertura_id);
			$stmt->bindParam("docente_id", $docente_id);
			$stmt->execute();
			$aperturaSelec = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$aperturaSelec){return $this->errorResponse($response, 'No tiene permisos para ver esta apertura o no existe', 403);}
			return $this->successResponse($response, 'Apertura obtenida exitosamente', $aperturaSelec);
		}catch(Exception $e){
			return $this->errorResponse($response, 'Error interno del servidor', 500);
		}finally{
			if($stmt !== null){$stmt = null;}
			if($db !== null){$db = null;}
		}
	}
}
