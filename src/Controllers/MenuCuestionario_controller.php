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
	public function getMisCuestionarios(Request $request, Response $response, array $args): Response{
		$db = null;
		$stmt = null;
		try {
			$user_id = $this->getUserIdFromToken($request);
			if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
			// Obtener conexión a la base de datos
			$db = $this->container->get('db');
			$userdata = $this->getUserDataFromToken($request);
			$user_rol = $userdata['rol'];
			if($user_rol == 0){
				// Consulta para obtener cuestionarios de todos los usuarios
				$sql_get = "SELECT rcp.*, c.titulo, c.descripcion 
				        FROM relacion_cuestionario_programa rcp 
				        JOIN cuestionario c ON rcp.id_cuestionario = c.id
					    ORDER BY c.titulo ASC";
				$stmt = $db->prepare($sql_get);
				$stmt->execute();
				$allCuestionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return $this->successResponse($response, 'Cuestionarios obtenidos exitosamente', [
					'cuestionarios' => $allCuestionarios,
					'total' => count($allCuestionarios)
			]);
			}else{
				// Consulta para obtener cuestionarios del usuario
				$query = "SELECT rcp.*, c.titulo, c.descripcion 
							FROM relacion_cuestionario_programa rcp 
							JOIN cuestionario c ON rcp.id_cuestionario = c.id
							WHERE rcp.id_docente = :docente_id
							ORDER BY c.titulo ASC";
				
				$stmt = $db->prepare($query);
				$stmt->bindParam(':docente_id', $user_id, PDO::PARAM_INT);
				$stmt->execute();
				
				$misCuestionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
				
				return $this->successResponse($response, 'Cuestionarios obtenidos exitosamente', [
					'cuestionarios' => $misCuestionarios,
					'total' => count($misCuestionarios)
				]);
			}
		} catch (Exception $e) {
			error_log("Error en getMisCuestionarios: " . $e->getMessage());
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
	 * Obtiene los cuestionarios abiertos (con periodo asignado) del usuario autenticado
	 * Incluye el estado del cuestionario según las fechas
	 * 
	 * @param Request $request
	 * @param Response $response
	 * @param array $args
	 * @return Response
	 */
	public function getCuestionariosAbiertos(Request $request, Response $response, array $args): Response{
		$db = null;
		$stmt = null;
		try {
			
			// Obtener y validar el token JWT
			$userId = $this->getUserIdFromToken($request);
			if (!$userId) {
				return $this->errorResponse($response, 'Token inválido o no proporcionado', 401);
			}
			// Obtener conexión a la base de datos
			$db = $this->container->get('db');
			
			// Consulta para obtener cuestionarios abiertos
			$query = "SELECT 
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
	 * Determina el estado del cuestionario según las fechas de inicio y fin
	 * 
	 * @param string $fechaInicio Fecha de inicio del periodo
	 * @param string $fechaFin Fecha de fin del periodo
	 * @return string Estado del cuestionario: 'Programado', 'Disponible' o 'Cerrado'
	 */
	private function determinarEstadoCuestionario(string $fechaInicio, string $fechaFin): string{
		$hoy = date('Y-m-d');
		
		if ($hoy < $fechaInicio) {
			return 'Programado';
		} elseif ($hoy >= $fechaInicio && $hoy <= $fechaFin) {
			return 'Disponible';
		} else {
			return 'Cerrado';
		}
	}

	public function getCuestInfo(Request $request, Response $response, array $args): Response{
        $db = null;
        $stmt = null;
        try{
            $user_id = $this->getUserIdFromToken($request);
            if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
            $cuest_id = $args['cuestionario_id'];
            if($cuest_id <= 0){return $this->errorResponse($response, 'Cuestionario no valido', 404);}
            $db = $this->container->get('db');
            $sql_cuest = "SELECT 
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
                            c.id = :cuestionario_id
                            and rcp.id_docente = :usuario_id";
            $stmt = $db->prepare($sql_cuest);
            $stmt->bindParam(':cuestionario_id', $cuest_id, PDO::PARAM_INT);
            $stmt->bindParam(':usuario_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $cuestionario = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$cuestionario){return $this->errorResponse($response, 'Cuestionario no encontrado', 404);}
            return $this->successResponse($response, 'Cuestionario obtenido exitosamente', $cuestionario);
        }catch(Exception $e){
            return $this->errorResponse($response, 'Error al obtener el cuestionario: ' . $e->getMessage(), 500);
        }finally{
            if($stmt !== null){$stmt = null;}
            if($db !== null){$db = null;}
        }
    }

	public function getAllcuestionarios(Request $request, Response $response, array $args): Response{
		$db = null;
		$stmt = null;
		try{
			$user_id = $this->getUserIdFromToken($request);
			if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
			$db = $this->container->get('db');
			$sql_get = "SELECT rcp.*, c.titulo, c.descripcion 
				        FROM relacion_cuestionario_programa rcp 
				        JOIN cuestionario c ON rcp.id_cuestionario = c.id
					    ORDER BY c.titulo ASC";
			$stmt = $db->prepare($sql_get);
			$stmt->execute();
			$allCuestionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $this->successResponse($response, 'Cuestionarios obtenidos exitosamente', [
				'cuestionarios' => $allCuestionarios,
				'total' => count($allCuestionarios)
			]);
		}catch(Exception $e){
			return $this->errorResponse($response, 'Error al obtener los cuestionarios: ' . $e->getMessage(), 500);
		}finally{
			if($db !== null){$db = null;}
			if($stmt !== null){$stmt = null;}
		}
	}

	public function getAllCuestionariosAbiertos(Request $request, Response $response, array $args): Response{
		$db = null;
		$stmt = null;
		try{
			$user_id = $this->getUserIdFromToken($request);
			if(!$user_id){return $this->errorResponse($response, 'Usuario no autenticado', 401);}
			$db = $this->container->get('db');
			$sql_all_forms = "SELECT 
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
					AND a.activo = 1
				ORDER BY 
					p.fecha_inicio DESC, c.titulo ASC";
			$stmt = $db->prepare($sql_all_forms);
			$stmt->execute();
			$allCuestionariosAbiertos = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $this->successResponse($response, 'Cuestionarios abiertos obtenidos exitosamente', [
				'cuestionarios_abiertos' => $allCuestionariosAbiertos,
				'total' => count($allCuestionariosAbiertos)
			]);
		}catch(Exception $e){
			return $this->errorResponse($response, 'Error al obtener los cuestionarios abiertos: ' . $e->getMessage(), 500);
		}finally{
			if($stmt !== null){
				$stmt = null;
			}
			if($db !== null){
				$db = null;
			}
		}
	}
}