<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\BaseController;
use Psr\Container\ContainerInterface;
use PDO;
use Exception;
use DateTime;

/**
 * Controlador de Periodos para API REST
 * Maneja operaciones CRUD de periodos académicos
 * Migrado desde periodoManage_controller.php (Legacy)
 */
class periodo_controller extends BaseController
{
	public function __construct(ContainerInterface $c)
	{
		parent::__construct($c);
	}

	/**
	 * Crear nuevo periodo
	 * POST /periodos
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function create(Request $request, Response $response, array $args): Response{
		$db = null;
		$stmt = null;
		try {
			$db = $this->container->get('db');
			$inputData = $this->getJsonInput($request);

			if (!$inputData) {
				return $this->errorResponse($response, 'Datos JSON inválidos', 400);
			}

			// Validar campos requeridos
			$validation = $this->validatePeriodoData($inputData);
			if (!$validation['valid']) {
				return $this->errorResponse($response, $validation['message'], 400);
			}

			// Validar fechas
			$dateValidation = $this->validateDates($inputData['fecha_inicio'], $inputData['fecha_fin']);
			if (!$dateValidation['valid']) {
				return $this->errorResponse($response, $dateValidation['message'], 400);
			}

			$nombre = $this->sanitizeInput($inputData['nombre']);
			$fecha_inicio = $this->sanitizeInput($inputData['fecha_inicio']);
			$fecha_fin = $this->sanitizeInput($inputData['fecha_fin']);

			// Crear nuevo periodo
			$query = "INSERT INTO periodo (nombre, fecha_inicio, fecha_fin, activo) VALUES (:nombre, :fecha_inicio, :fecha_fin, 1)";
			$stmt = $db->prepare($query);
			$stmt->bindParam(':nombre', $nombre);
			$stmt->bindParam(':fecha_inicio', $fecha_inicio);
			$stmt->bindParam(':fecha_fin', $fecha_fin);

			if ($stmt->execute()) {
				$periodoId = $db->lastInsertId();
				
				// Obtener el periodo creado para devolverlo
				$nuevoPeriodo = $this->getPeriodoById($db, $periodoId);
				
				return $this->successResponse($response, 'Periodo creado correctamente', [
					'periodo' => $nuevoPeriodo
				]);
			} else {
				return $this->errorResponse($response, 'Error al crear el periodo', 500);
			}

		} catch (Exception $e) {
			error_log("Error en create periodo: " . $e->getMessage());
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
	 * Actualizar periodo existente
	 * PUT /periodos/{id}
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function update(Request $request, Response $response, array $args): Response{
		$db = null;
		$stmt = null;
		try {
			$db = $this->container->get('db');
			$id = $args['id'] ?? null;
			
			if (!$id) {
				return $this->errorResponse($response, 'ID de periodo requerido', 400);
			}

			$inputData = $this->getJsonInput($request);
			if (!$inputData) {
				return $this->errorResponse($response, 'Datos JSON inválidos', 400);
			}

			// Validar campos requeridos
			$validation = $this->validatePeriodoData($inputData);
			if (!$validation['valid']) {
				return $this->errorResponse($response, $validation['message'], 400);
			}

			// Validar fechas
			$dateValidation = $this->validateDates($inputData['fecha_inicio'], $inputData['fecha_fin']);
			if (!$dateValidation['valid']) {
				return $this->errorResponse($response, $dateValidation['message'], 400);
			}

			// Verificar que el periodo existe
			$periodoExistente = $this->getPeriodoById($db, $id);
			if (!$periodoExistente) {
				return $this->errorResponse($response, 'Periodo no encontrado', 404);
			}

			$nombre = $this->sanitizeInput($inputData['nombre']);
			$fecha_inicio = $this->sanitizeInput($inputData['fecha_inicio']);
			$fecha_fin = $this->sanitizeInput($inputData['fecha_fin']);

			// Actualizar periodo
			$query = "UPDATE periodo SET nombre = :nombre, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin WHERE id = :id";
			$stmt = $db->prepare($query);
			$stmt->bindParam(':nombre', $nombre);
			$stmt->bindParam(':fecha_inicio', $fecha_inicio);
			$stmt->bindParam(':fecha_fin', $fecha_fin);
			$stmt->bindParam(':id', $id);

			if ($stmt->execute()) {
				// Obtener el periodo actualizado
				$periodoActualizado = $this->getPeriodoById($db, $id);
				
				return $this->successResponse($response, 'Periodo actualizado correctamente', [
					'periodo' => $periodoActualizado
				]);
			} else {
				return $this->errorResponse($response, 'Error al actualizar el periodo', 500);
			}

		} catch (Exception $e) {
			error_log("Error en update periodo: " . $e->getMessage());
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
	 * Desactivar periodo
	 * DELETE /periodos/{id}
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function deactivate(Request $request, Response $response, array $args): Response{
		$db = null;
		$check_stmt = null;
		$stmt = null;
		try {
			$db = $this->container->get('db');
			$id = $args['id'] ?? null;

			if (!$id) {
				return $this->errorResponse($response, 'ID de periodo requerido', 400);
			}

			// Verificar que el periodo existe
			$periodoExistente = $this->getPeriodoById($db, $id);
			if (!$periodoExistente) {
				return $this->errorResponse($response, 'Periodo no encontrado', 404);
			}

					// Verificar si el periodo tiene aperturas activas
		$check_query = "SELECT COUNT(*) as count FROM apertura WHERE id_periodo = :id AND activo = 1";
		$check_stmt = $db->prepare($check_query);
		$check_stmt->bindParam(':id', $id);
		$check_stmt->execute();
		$result = $check_stmt->fetch();

		if ($result->count > 0) {
				return $this->errorResponse($response, 
					'No se puede desactivar el periodo porque tiene aperturas activas. Desactive primero todas las aperturas asociadas a este periodo.', 
					409);
			}

			// Desactivar periodo
			$query = "UPDATE periodo SET activo = 0 WHERE id = :id";
			$stmt = $db->prepare($query);
			$stmt->bindParam(':id', $id);

			if ($stmt->execute()) {
				return $this->successResponse($response, 'Periodo desactivado correctamente', [
					'periodo_id' => $id
				]);
			} else {
				return $this->errorResponse($response, 'Error al desactivar el periodo', 500);
			}

		} catch (Exception $e) {
			error_log("Error en deactivate periodo: " . $e->getMessage());
			return $this->errorResponse($response, 'Error interno del servidor', 500);
		}finally{
			if($stmt !== null){
				$stmt = null;
			}
			if($check_stmt !== null){
				$check_stmt = null;
			}
			if($db !== null){
				$db = null;
			}
		}
	}

	/**
	 * Reactivar periodo
	 * POST /periodos/{id}/activate
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function activate(Request $request, Response $response, array $args): Response{
		$db = null;
		$stmt = null;
		try {
			$db = $this->container->get('db');
			$id = $args['id'] ?? null;

			if (!$id) {
				return $this->errorResponse($response, 'ID de periodo requerido', 400);
			}

			// Verificar que el periodo existe
			$periodoExistente = $this->getPeriodoById($db, $id, false);
			if (!$periodoExistente) {
				return $this->errorResponse($response, 'Periodo no encontrado', 404);
			}

			// Reactivar periodo
			$query = "UPDATE periodo SET activo = 1 WHERE id = :id";
			$stmt = $db->prepare($query);
			$stmt->bindParam(':id', $id);

			if ($stmt->execute()) {
				// Obtener el periodo reactivado
				$periodoReactivado = $this->getPeriodoById($db, $id);
				
				return $this->successResponse($response, 'Periodo reactivado correctamente', [
					'periodo' => $periodoReactivado
				]);
			} else {
				return $this->errorResponse($response, 'Error al reactivar el periodo', 500);
			}

		} catch (Exception $e) {
			error_log("Error en activate periodo: " . $e->getMessage());
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
	 * Listar periodos activos
	 * GET /periodos
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getActive(Request $request, Response $response, array $args): Response{
		$db = null;
		$stmt = null;
		try {
			$db = $this->container->get('db');

					// Obtener todos los periodos activos
		$query = "SELECT * FROM periodo WHERE activo = 1 ORDER BY fecha_inicio DESC";
		$stmt = $db->prepare($query);
		$stmt->execute();
		$periodos = $stmt->fetchAll();

		// Añadir información de aperturas a cada periodo
		foreach ($periodos as &$periodo) {
			$aperturas_info = $this->obtenerAperturasPeriodo($db, $periodo->id);
			$periodo->aperturas_activas = $aperturas_info['aperturas_activas'] ?: 0;
			$periodo->aperturas_inactivas = $aperturas_info['aperturas_inactivas'] ?: 0;
			$periodo->total_aperturas = $aperturas_info['total_aperturas'] ?: 0;
		}
			unset($periodo); // Romper la referencia

			return $this->successResponse($response, 'Periodos activos obtenidos correctamente', [
				'periodos' => $periodos,
				'total' => count($periodos)
			]);

		} catch (Exception $e) {
			error_log("Error en getActive periodo: " . $e->getMessage());
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
	 * Listar periodos inactivos
	 * GET /periodos/inactive
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getInactive(Request $request, Response $response, array $args): Response{
		$db = null;
		$stmt = null;
		try {
			$db = $this->container->get('db');

					// Obtener todos los periodos inactivos
		$query = "SELECT * FROM periodo WHERE activo = 0 ORDER BY fecha_inicio DESC";
		$stmt = $db->prepare($query);
		$stmt->execute();
		$periodos = $stmt->fetchAll();

		// Añadir información de aperturas a cada periodo
		foreach ($periodos as &$periodo) {
			$aperturas_info = $this->obtenerAperturasPeriodo($db, $periodo->id);
			$periodo->aperturas_activas = $aperturas_info['aperturas_activas'] ?: 0;
			$periodo->aperturas_inactivas = $aperturas_info['aperturas_inactivas'] ?: 0;
			$periodo->total_aperturas = $aperturas_info['total_aperturas'] ?: 0;
		}
			unset($periodo); // Romper la referencia

			return $this->successResponse($response, 'Periodos inactivos obtenidos correctamente', [
				'periodos' => $periodos,
				'total' => count($periodos)
			]);

		} catch (Exception $e) {
			error_log("Error en getInactive periodo: " . $e->getMessage());
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
	 * Obtener periodo específico por ID
	 * GET /periodos/{id}
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getById(Request $request, Response $response, array $args): Response{
		try {
			$db = $this->container->get('db');
			$id = $args['id'] ?? null;

			if (!$id) {
				return $this->errorResponse($response, 'ID de periodo requerido', 400);
			}

			$periodo = $this->getPeriodoById($db, $id, false); // false = incluir inactivos también
			
			if (!$periodo) {
				return $this->errorResponse($response, 'Periodo no encontrado', 404);
			}

					// Añadir información de aperturas
		$aperturas_info = $this->obtenerAperturasPeriodo($db, $periodo['id']);
		$periodo['aperturas_activas'] = $aperturas_info['aperturas_activas'] ?: 0;
		$periodo['aperturas_inactivas'] = $aperturas_info['aperturas_inactivas'] ?: 0;
		$periodo['total_aperturas'] = $aperturas_info['total_aperturas'] ?: 0;

			return $this->successResponse($response, 'Periodo encontrado', [
				'periodo' => $periodo
			]);

		} catch (Exception $e) {
			error_log("Error en getById periodo: " . $e->getMessage());
			return $this->errorResponse($response, 'Error interno del servidor', 500);
		}
	}

	/**
	 * Función auxiliar para obtener el número de aperturas activas e inactivas para un periodo
	 * Migrada desde periodoManage_controller.php
	 * 
	 * @param PDO $db
	 * @param int $periodo_id
	 * @return array
	 */
	private function obtenerAperturasPeriodo(PDO $db, int $periodo_id): array{
		$stmt = null;
		try {
			$query = "SELECT 
						SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as aperturas_activas,
						SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as aperturas_inactivas,
						COUNT(*) as total_aperturas
				    FROM apertura 
					WHERE id_periodo = :periodo_id";
			$stmt = $db->prepare($query);
			$stmt->bindParam(':periodo_id', $periodo_id);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			error_log("Error en obtenerAperturasPeriodo: " . $e->getMessage());
			return [
				'aperturas_activas' => 0,
				'aperturas_inactivas' => 0,
				'total_aperturas' => 0
			];
		}finally{
			if($stmt !== null){
				$stmt = null;
			}
		}
	}

	/**
	 * Obtener periodo por ID
	 * 
	 * @param PDO $db
	 * @param int $id
	 * @param bool $activeOnly Solo activos
	 * @return array|false
	 */
	private function getPeriodoById(PDO $db, int $id, bool $activeOnly = true){
		$stmt = null;
		try {
			$query = "SELECT * FROM periodo WHERE id = :id";
			if ($activeOnly) {
				$query .= " AND activo = 1";
			}
			
			$stmt = $db->prepare($query);
			$stmt->bindParam(':id', $id);
			$stmt->execute();
			return $stmt->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			error_log("Error en getPeriodoById: " . $e->getMessage());
			return false;
		}finally{
			if($stmt !== null){
				$stmt = null;
			}
		}
	}

	/**
	 * Valida los datos del periodo
	 * 
	 * @param array $data
	 * @return array
	 */
	private function validatePeriodoData(array $data): array
	{
		if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
			return ['valid' => false, 'message' => 'El nombre del periodo es requerido'];
		}

		if (!isset($data['fecha_inicio']) || empty(trim($data['fecha_inicio']))) {
			return ['valid' => false, 'message' => 'La fecha de inicio es requerida'];
		}

		if (!isset($data['fecha_fin']) || empty(trim($data['fecha_fin']))) {
			return ['valid' => false, 'message' => 'La fecha de fin es requerida'];
		}

		// Validar formato de fechas
		if (!$this->isValidDate($data['fecha_inicio'])) {
			return ['valid' => false, 'message' => 'La fecha de inicio no tiene un formato válido (YYYY-MM-DD)'];
		}

		if (!$this->isValidDate($data['fecha_fin'])) {
			return ['valid' => false, 'message' => 'La fecha de fin no tiene un formato válido (YYYY-MM-DD)'];
		}

		return ['valid' => true, 'message' => 'Datos válidos'];
	}

	/**
	 * Valida que la fecha de inicio no sea mayor a la fecha de fin
	 * Nueva validación solicitada por el usuario
	 * 
	 * @param string $fecha_inicio
	 * @param string $fecha_fin
	 * @return array
	 */
	private function validateDates(string $fecha_inicio, string $fecha_fin): array
	{
		try {
			$inicio = new DateTime($fecha_inicio);
			$fin = new DateTime($fecha_fin);

			if ($inicio > $fin) {
				return ['valid' => false, 'message' => 'La fecha de inicio no puede ser mayor a la fecha de fin'];
			}

			return ['valid' => true, 'message' => 'Fechas válidas'];
		} catch (Exception $e) {
			return ['valid' => false, 'message' => 'Error al validar las fechas'];
		}
	}

	/**
	 * Valida si una fecha tiene formato válido
	 * 
	 * @param string $date
	 * @return bool
	 */
	private function isValidDate(string $date): bool
	{
		$d = DateTime::createFromFormat('Y-m-d', $date);
		return $d && $d->format('Y-m-d') === $date;
	}
}
