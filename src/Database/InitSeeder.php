<?php

namespace App\Database;
use App\Controllers\BaseController;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class InitSeeder extends BaseController{
	private $db;
	
	public function __construct(ContainerInterface $c){
        $this->container = $c;
    }
	
	/**
	 * Ejecuta todos los seeders en el orden correcto respetando las dependencias
	 */
	public function exeSeeds(Request $request, Response $response, array $args): Response{
		try {
			// Iniciar transacción para asegurar consistencia
			$this->db = $this->container->get('db');
			$this->db->beginTransaction();
			
			// Orden de ejecución respetando dependencias de claves foráneas
			$seedersOrder = [
				'campus',
				'nivel', 
				'cuestionario',
				'periodo',
				'programa',
				'docente',
				'estudiante',
				'desempeno_nivel',
				'desempeno_indicadores',
				'relacion_cuestionario_programa',
				'preguntas',
				'opcion_respuesta',
			];
			
			$executedQueries = 0;
			$errors = [];
			
			foreach ($seedersOrder as $tableName) {
				$seederFile = __DIR__ . "/Seeders/seeder-{$tableName}.php";
				
				if (file_exists($seederFile)) {
					// echo "Ejecutando seeder para tabla: {$tableName}\n";
					
					$queries = include $seederFile;
					
					if (is_array($queries)) {
						foreach ($queries as $query) {
							try {
								$stmt = $this->db->prepare($query);
								$stmt->execute();
								$executedQueries++;
							} catch (\Exception $e) {
								$errors[] = "Error en tabla {$tableName}: " . $e->getMessage();
								echo "Error ejecutando query en {$tableName}: " . $e->getMessage() . "\n";
							}
						}
					}
					
					// echo "Seeder {$tableName} completado.\n";
				} else {
					echo "Archivo seeder no encontrado: {$seederFile}\n";
				}
			}
			
			// Confirmar transacción si no hay errores críticos
			if (empty($errors)) {
				$this->db->commit();
				echo "\n=== SEEDERS EJECUTADOS EXITOSAMENTE ===\n";
				echo "Total de consultas ejecutadas: {$executedQueries}\n";
				return $this->successResponse($response, 'Todos los seeders se ejecutaron correctamente', [
					'success' => true,
					'message' => 'Todos los seeders se ejecutaron correctamente',
					'queries_executed' => $executedQueries
				]);

			} else {
				$this->db->rollback();
				echo "\n=== ERRORES ENCONTRADOS ===\n";
				foreach ($errors as $error) {
					echo $error . "\n";
				}
				// return [
				// 	'success' => false,
				// 	'message' => 'Se encontraron errores durante la ejecución',
				// 	'errors' => $errors,
				// 	'queries_executed' => $executedQueries
				// ];
				return $this->errorResponse($response, 'Se encontraron errores durante la ejecución', 400);
			}
			
		} catch (\Exception $e) {
			$this->db->rollback();
			echo "Error crítico: " . $e->getMessage() . "\n";
			return $this->errorResponse($response, 'Error crítico durante la ejecución de seeders', 500);
		}
	}
	
	/**
	 * Ejecuta un seeder específico
	 */
	// public function executeSingleSeeder($tableName)
	// {
	// 	$seederFile = __DIR__ . "/Seeders/seeder-{$tableName}.php";
		
	// 	if (!file_exists($seederFile)) {
	// 		return [
	// 			'success' => false,
	// 			'message' => "Archivo seeder no encontrado: seeder-{$tableName}.php"
	// 		];
	// 	}
		
	// 	try {
	// 		$this->connection->beginTransaction();
			
	// 		$queries = include $seederFile;
	// 		$executedQueries = 0;
			
	// 		if (is_array($queries)) {
	// 			foreach ($queries as $query) {
	// 				$stmt = $this->connection->prepare($query);
	// 				$stmt->execute();
	// 				$executedQueries++;
	// 			}
	// 		}
			
	// 		$this->connection->commit();
			
	// 		return [
	// 			'success' => true,
	// 			'message' => "Seeder {$tableName} ejecutado correctamente",
	// 			'queries_executed' => $executedQueries
	// 		];
			
	// 	} catch (\Exception $e) {
	// 		$this->connection->rollback();
	// 		return [
	// 			'success' => false,
	// 			'message' => "Error ejecutando seeder {$tableName}",
	// 			'error' => $e->getMessage()
	// 		];
	// 	}
	// }
	
	/**
	 * Limpia todas las tablas antes de ejecutar los seeders
	 */
	// public function cleanDatabase()
	// {
	// 	try {
	// 		$this->connection->beginTransaction();
			
	// 		// Desactivar verificación de claves foráneas temporalmente
	// 		$this->connection->exec("SET FOREIGN_KEY_CHECKS = 0");
			
	// 		// Orden inverso para limpiar respetando dependencias
	// 		$tables = [
	// 			'sesion_estudiante',
	// 			'respuesta_estudiante',
	// 			'intento_cuestionario',
	// 			'asignacion',
	// 			'apertura',
	// 			'opcion_respuesta',
	// 			'preguntas',
	// 			'relacion_cuestionario_programa',
	// 			'desempeno_indicadores',
	// 			'desempeno_nivel',
	// 			'estudiante',
	// 			'docente',
	// 			'programa',
	// 			'periodo',
	// 			'cuestionario',
	// 			'nivel',
	// 			'campus'
	// 		];
			
	// 		foreach ($tables as $table) {
	// 			$this->connection->exec("TRUNCATE TABLE {$table}");
	// 			echo "Tabla {$table} limpiada.\n";
	// 		}
			
	// 		// Reactivar verificación de claves foráneas
	// 		$this->connection->exec("SET FOREIGN_KEY_CHECKS = 1");
			
	// 		$this->connection->commit();
			
	// 		return [
	// 			'success' => true,
	// 			'message' => 'Base de datos limpiada correctamente'
	// 		];
			
	// 	} catch (\Exception $e) {
	// 		$this->connection->rollback();
	// 		$this->connection->exec("SET FOREIGN_KEY_CHECKS = 1");
			
	// 		return [
	// 			'success' => false,
	// 			'message' => 'Error limpiando la base de datos',
	// 			'error' => $e->getMessage()
	// 		];
	// 	}
	// }
}
