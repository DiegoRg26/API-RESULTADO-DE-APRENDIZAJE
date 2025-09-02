<?php

/**
 * Ejemplo de uso del InitSeeder
 * Este archivo muestra cómo utilizar la clase InitSeeder para poblar la base de datos
 */

require_once __DIR__ . '/InitSeeder.php';

// Configuración de la base de datos (ajustar según tu configuración)
$host = 'localhost';
$dbname = 'formtest';
$username = 'root';
$password = '';

try {
	// Crear conexión PDO
	$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
	$pdo = new PDO($dsn, $username, $password, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
	]);
	
	// Crear instancia del seeder
	$seeder = new \App\Database\InitSeeder($pdo);
	
	echo "=== INICIANDO PROCESO DE SEEDERS ===\n\n";
	
	// Opción 1: Limpiar base de datos y ejecutar todos los seeders
	echo "1. Limpiando base de datos...\n";
	$cleanResult = $seeder->cleanDatabase();
	
	if ($cleanResult['success']) {
		echo "✓ Base de datos limpiada correctamente\n\n";
		
		echo "2. Ejecutando todos los seeders...\n";
		$result = $seeder->exeSeeds();
		
		if ($result['success']) {
			echo "\n✓ ¡Seeders ejecutados exitosamente!\n";
			echo "Total de consultas ejecutadas: " . $result['queries_executed'] . "\n";
		} else {
			echo "\n✗ Error ejecutando seeders:\n";
			echo $result['message'] . "\n";
			if (isset($result['errors'])) {
				foreach ($result['errors'] as $error) {
					echo "- " . $error . "\n";
				}
			}
		}
	} else {
		echo "✗ Error limpiando base de datos: " . $cleanResult['message'] . "\n";
	}
	
	/* 
	// Opción 2: Ejecutar un seeder específico
	echo "\n=== EJECUTANDO SEEDER ESPECÍFICO ===\n";
	$singleResult = $seeder->executeSingleSeeder('campus');
	
	if ($singleResult['success']) {
		echo "✓ " . $singleResult['message'] . "\n";
		echo "Consultas ejecutadas: " . $singleResult['queries_executed'] . "\n";
	} else {
		echo "✗ " . $singleResult['message'] . "\n";
		if (isset($singleResult['error'])) {
			echo "Error: " . $singleResult['error'] . "\n";
		}
	}
	*/
	
} catch (PDOException $e) {
	echo "Error de conexión a la base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
	echo "Error general: " . $e->getMessage() . "\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";
