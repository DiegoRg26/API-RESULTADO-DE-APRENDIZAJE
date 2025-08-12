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

// Obtener parámetros
$estudiante_id = isset($_GET['estudiante_id']) ? (int)$_GET['estudiante_id'] : 0;
$cuestionario_id = isset($_GET['cuestionario_id']) ? (int)$_GET['cuestionario_id'] : 0;

if ($estudiante_id <= 0 || $cuestionario_id <= 0) {
    header('Location: MenuCuestionarios.php');
    exit;
}

// Obtener información del estudiante
$query_estudiante = "
    SELECT 
        e.id,
        e.nombre,
        e.email,
        e.identificacion,
        p.nombre as programa_nombre
    FROM 
        estudiante e
    JOIN 
        programa p ON e.id_programa = p.id
    WHERE 
        e.id = :estudiante_id
";
$stmt_estudiante = $db->prepare($query_estudiante);
$stmt_estudiante->bindParam(':estudiante_id', $estudiante_id);
$stmt_estudiante->execute();
$estudiante = $stmt_estudiante->fetch(PDO::FETCH_ASSOC);

if (!$estudiante) {
    header('Location: MenuCuestionarios.php');
    exit;
}

// Obtener información del cuestionario
$query_cuestionario = "
    SELECT 
        c.id,
        c.titulo,
        c.descripcion
    FROM 
        cuestionario c
    JOIN 
        relacion_cuestionario_programa rcp ON c.id = rcp.id_cuestionario
    WHERE 
        c.id = :cuestionario_id
        AND rcp.id_docente = :usuario_id
";
$stmt_cuestionario = $db->prepare($query_cuestionario);
$stmt_cuestionario->bindParam(':cuestionario_id', $cuestionario_id);
$stmt_cuestionario->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt_cuestionario->execute();
$cuestionario = $stmt_cuestionario->fetch(PDO::FETCH_ASSOC);

if (!$cuestionario) {
    header('Location: MenuCuestionarios.php');
    exit;
}

// Obtener el intento más reciente del estudiante para este cuestionario
$query_intento = "
    SELECT 
        ic.id as intento_id,
        ic.fecha_fin as fecha_respuesta,
        ic.puntaje_total as puntaje_obtenido,
        (SELECT SUM(peso_pregunta) FROM preguntas WHERE id_cuestionario = :cuestionario_id) as puntaje_total
    FROM 
        intento_cuestionario ic
    JOIN 
        apertura a ON ic.id_apertura = a.id
    JOIN 
        relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
    WHERE 
        ic.id_estudiante = :estudiante_id
        AND rcp.id_cuestionario = :cuestionario_id
        AND ic.completado = 1
    ORDER BY 
        ic.fecha_fin DESC
    LIMIT 1
";
$stmt_intento = $db->prepare($query_intento);
$stmt_intento->bindParam(':estudiante_id', $estudiante_id);
$stmt_intento->bindParam(':cuestionario_id', $cuestionario_id);
$stmt_intento->execute();
$intento = $stmt_intento->fetch(PDO::FETCH_ASSOC);

if (!$intento) {
    header('Location: MenuCuestionarios.php');
    exit;
}

$intento_id = $intento['intento_id'];
$fecha_respuesta = $intento['fecha_respuesta'];
$puntaje_total = $intento['puntaje_total'] ?? 0;
$puntaje_obtenido = $intento['puntaje_obtenido'] ?? 0;
$porcentaje = $puntaje_total > 0 ? round(($puntaje_obtenido / $puntaje_total) * 100) : 0;

// Obtener respuestas detalladas
$query_respuestas = "
    SELECT 
        p.id as pregunta_id,
        p.texto_pregunta,
        p.peso_pregunta,
        p.imagen_pregunta,
        p.nombre_imagen_pregunta,
        re.id_opcion_seleccionada,
        op.opcion_correcta as es_correcta
    FROM 
        respuesta_estudiante re
    JOIN 
        preguntas p ON re.id_pregunta = p.id
    JOIN 
        opcion_respuesta op ON re.id_opcion_seleccionada = op.id
    WHERE 
        re.id_intento = :intento_id
    ORDER BY 
        p.orden_pregunta ASC
";
$stmt_respuestas = $db->prepare($query_respuestas);
$stmt_respuestas->bindParam(':intento_id', $intento_id);
$stmt_respuestas->execute();
$respuestas_raw = $stmt_respuestas->fetchAll(PDO::FETCH_ASSOC);

$respuestas = [];
foreach ($respuestas_raw as $respuesta) {
    // Obtener todas las opciones para esta pregunta
    $query_opciones = "
        SELECT 
            op.id,
            op.texto_opcion,
            op.opcion_correcta as es_correcta,
            op.imagen_opcion,
            op.nombre_imagen_opcion
        FROM 
            opcion_respuesta op
        WHERE 
            op.id_pregunta = :pregunta_id
        ORDER BY 
            op.orden ASC
    ";
    $stmt_opciones = $db->prepare($query_opciones);
    $stmt_opciones->bindParam(':pregunta_id', $respuesta['pregunta_id']);
    $stmt_opciones->execute();
    $opciones = $stmt_opciones->fetchAll(PDO::FETCH_ASSOC);
    
    $respuesta['opciones'] = $opciones;
    $respuestas[] = $respuesta;
} 