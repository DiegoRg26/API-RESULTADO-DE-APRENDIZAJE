<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../public/login.php');
    exit;
}

// Incluir la conexión a la base de datos
require_once '../../src/model/conection.php';

$database = new Database();
$db = $database->connect();

// Obtener cuestionarios del usuario actual
$query_mis_cuestionarios = "SELECT rcp.*, c.titulo, c.descripcion FROM relacion_cuestionario_programa rcp 
                            JOIN cuestionario c ON rcp.id_cuestionario = c.id
                            WHERE id_docente = :docente_id";
$stmt_mis = $db->prepare($query_mis_cuestionarios);
$stmt_mis->bindParam(':docente_id', $_SESSION['usuario_id']);
$stmt_mis->execute();
$mis_cuestionarios = $stmt_mis->fetchAll();

// Obtener cuestionarios abiertos (con periodo asignado)
$query_cuestionarios_abiertos = "
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
$stmt_abiertos = $db->prepare($query_cuestionarios_abiertos);
$stmt_abiertos->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt_abiertos->execute();
$cuestionarios_abiertos = $stmt_abiertos->fetchAll();

// Función para determinar el estado del cuestionario
function determinarEstadoCuestionario($fechaInicio, $fechaFin) {
    $hoy = date('Y-m-d');
    
    if ($hoy < $fechaInicio) {
        return 'PROGRAMADO';
    } elseif ($hoy >= $fechaInicio && $hoy <= $fechaFin) {
        return 'DISPONIBLE';
    } else {
        return 'VENCIDO !!';
    }
}

// Asignar el estado a cada cuestionario abierto
foreach ($cuestionarios_abiertos as $key => $cuestionario) {
    $cuestionarios_abiertos[$key]['estado'] = determinarEstadoCuestionario(
        $cuestionario['fecha_inicio'],
        $cuestionario['fecha_fin']
    );
}
