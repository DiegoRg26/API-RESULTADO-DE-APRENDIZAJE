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

// Obtener el ID de la apertura
$apertura_id = isset($_GET['apertura_id']) ? (int)$_GET['apertura_id'] : 0;

if ($apertura_id <= 0) {
    header('Location: MenuCuestionarios.php');
    exit;
}

// Obtener información del cuestionario
$query_cuestionario = "
    SELECT 
        a.id as apertura_id,
        c.id,
        c.titulo,
        c.descripcion,
        p.nombre as periodo_nombre,
        p.fecha_inicio,
        p.fecha_fin,
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
        programa prog ON rcp.id_programa = prog.id
    WHERE 
        a.id = :apertura_id
        AND rcp.id_docente = :usuario_id
";
$stmt_cuestionario = $db->prepare($query_cuestionario);
$stmt_cuestionario->bindParam(':apertura_id', $apertura_id);
$stmt_cuestionario->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt_cuestionario->execute();
$cuestionario = $stmt_cuestionario->fetch(PDO::FETCH_ASSOC);

// Verificar si se encontró el cuestionario
if (!$cuestionario) {
    header('Location: MenuCuestionarios.php');
    exit;
}

// Obtener estudiantes asignados y su estado
$query_estudiantes = "SELECT 
        e.id,
        e.nombre,
        e.email,
        e.identificacion,
        ic.fecha_fin as fecha_respuesta,
        CASE WHEN ic.completado = 1 THEN 1 ELSE 0 END as completado,
        (SELECT COUNT(*) FROM preguntas WHERE id_cuestionario = c.id) as total_preguntas,
        (SELECT COUNT(*) FROM respuesta_estudiante re2 
        JOIN opcion_respuesta op ON re2.id_opcion_seleccionada = op.id 
        JOIN intento_cuestionario ic2 ON re2.id_intento = ic2.id
        WHERE ic2.id_apertura = a.id 
        AND ic2.id_estudiante = e.id 
        AND op.opcion_correcta = 1) as respuestas_correctas,
        (SELECT SUM(peso_pregunta) FROM preguntas WHERE id_cuestionario = c.id) as puntaje_total,
        ic.puntaje_total as puntaje_obtenido,
        CASE 
            WHEN (SELECT SUM(peso_pregunta) FROM preguntas WHERE id_cuestionario = c.id) > 0 
            THEN ROUND((ic.puntaje_total / (SELECT SUM(peso_pregunta) FROM preguntas WHERE id_cuestionario = c.id)) * 100)
            ELSE 0
        END as porcentaje
    FROM 
        estudiante e
    JOIN 
        asignacion asig ON e.id = asig.id_estudiante
    JOIN 
        apertura a ON asig.id_apertura = a.id
    JOIN 
        relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
    JOIN 
        cuestionario c ON rcp.id_cuestionario = c.id
    LEFT JOIN 
        intento_cuestionario ic ON ic.id_estudiante = e.id AND ic.id_apertura = a.id AND ic.completado = 1
    WHERE 
        a.id = :apertura_id
    GROUP BY 
        e.id
    ORDER BY 
        e.nombre ASC
";
$stmt_estudiantes = $db->prepare($query_estudiantes);
$stmt_estudiantes->bindParam(':apertura_id', $apertura_id);
$stmt_estudiantes->execute();
$estudiantes = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);

// Calcular estadísticas
$estudiantes_completados = 0;
foreach ($estudiantes as $estudiante) {
    if ($estudiante['completado']) {
        $estudiantes_completados++;
    }
}

$porcentaje_completado = count($estudiantes) > 0 ? round(($estudiantes_completados / count($estudiantes)) * 100) : 0; 