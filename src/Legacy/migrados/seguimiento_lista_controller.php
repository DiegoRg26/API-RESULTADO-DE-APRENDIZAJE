<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../public/login.php');
    exit;
}

// Incluir la conexión a la base de datos
require_once '../../src/model/conection.php';

$database = new Database();
$db = $database->connect();

// Obtener cuestionarios asignados y su estado de realización por estudiantes
$query_seguimiento = "
    SELECT 
        a.id as apertura_id,
        c.id as cuestionario_id,
        c.titulo,
        c.descripcion,
        p.nombre as periodo_nombre,
        p.fecha_inicio,
        p.fecha_fin,
        prog.nombre as programa_nombre,
        COUNT(DISTINCT asig.id_estudiante) as total_estudiantes_asignados,
        COUNT(DISTINCT ic.id_estudiante) as total_estudiantes_completados
    FROM 
        apertura a
    JOIN 
        relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
    JOIN 
        cuestionario c ON rcp.id_cuestionario = c.id
    JOIN 
        periodo p ON a.id_periodo = p.id
    JOIN 
        programa prog ON rcp.id_programa = prog.id
    JOIN 
        asignacion asig ON a.id = asig.id_apertura
    LEFT JOIN (
        SELECT DISTINCT id_estudiante, id_apertura 
        FROM intento_cuestionario
        WHERE completado = 1
    ) ic ON ic.id_estudiante = asig.id_estudiante AND ic.id_apertura = a.id
    WHERE 
        rcp.activo = 1
        AND rcp.id_docente = :usuario_id
    GROUP BY 
        a.id, c.id
    ORDER BY 
        p.fecha_inicio DESC, c.titulo ASC
";
$stmt_seguimiento = $db->prepare($query_seguimiento);
$stmt_seguimiento->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt_seguimiento->execute();
$cuestionarios_seguimiento = $stmt_seguimiento->fetchAll(PDO::FETCH_ASSOC);

// Calcular estadísticas globales
$total_estudiantes_asignados = 0;
$total_estudiantes_completados = 0;

foreach ($cuestionarios_seguimiento as $cuestionario) {
    $total_estudiantes_asignados += $cuestionario['total_estudiantes_asignados'];
    $total_estudiantes_completados += $cuestionario['total_estudiantes_completados'];
}

$porcentaje_global = ($total_estudiantes_asignados > 0) ? round(($total_estudiantes_completados / $total_estudiantes_asignados) * 100) : 0; 