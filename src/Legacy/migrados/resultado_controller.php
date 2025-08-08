<?php
require_once '../../src/model/conection.php';

$database = new Database();
$db = $database->connect();

$intento_id = isset($_GET['intento_id']) ? (int)$_GET['intento_id'] : 0;

// Verificar que se proporcionaron los parámetros necesarios
if ($intento_id <= 0) {
    header("Location: principal.php");
    exit();
}

// Verificar que el intento pertenece al estudiante actual
$query_verificar = "
    SELECT ic.id 
    FROM intento_cuestionario ic
    WHERE ic.id = :intento_id 
    AND ic.id_estudiante = :usuario_id
    LIMIT 1
";
$stmt_verificar = $db->prepare($query_verificar);
$stmt_verificar->bindParam(':intento_id', $intento_id);
$stmt_verificar->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt_verificar->execute();

if ($stmt_verificar->rowCount() == 0) {
    header("Location: principal.php");
    exit();
}

// Obtener información del cuestionario y resultados
$query_resultado = "
    SELECT 
        c.id as cuestionario_id,
        c.titulo,
        c.descripcion,
        d.nombre as creador_nombre,
        p.nombre as programa_nombre,
        n.nombre as nivel_nombre,
        cam.nombre as campus_nombre,
        e.id as estudiante_id,
        COUNT(re.id) as total_respondidas,
        (SELECT COUNT(*) FROM preguntas WHERE id_cuestionario = c.id) as total_preguntas,
        (SELECT COUNT(*) FROM respuesta_estudiante re2 
        JOIN opcion_respuesta op ON re2.id_opcion_seleccionada = op.id 
        WHERE re2.id_intento = :intento_id 
        AND op.opcion_correcta = 1) as respuestas_correctas,
        (SELECT SUM(peso_pregunta) FROM preguntas WHERE id_cuestionario = c.id) as puntaje_total,
        (SELECT SUM(p.peso_pregunta) 
        FROM respuesta_estudiante re2 
        JOIN opcion_respuesta op ON re2.id_opcion_seleccionada = op.id 
        JOIN preguntas p ON re2.id_pregunta = p.id
        WHERE re2.id_intento = :intento_id 
        AND op.opcion_correcta = 1) as puntaje_obtenido,
        ic.fecha_fin as fecha_completado
    FROM 
        intento_cuestionario ic
    JOIN 
        apertura a ON ic.id_apertura = a.id
    JOIN 
        relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
    JOIN 
        cuestionario c ON rcp.id_cuestionario = c.id
    JOIN 
        estudiante e ON ic.id_estudiante = e.id
    JOIN 
        programa p ON e.id_programa = p.id
    JOIN 
        nivel n ON p.id_nivel = n.id
    JOIN 
        campus cam ON p.id_campus = cam.id
    JOIN
        docente d ON rcp.id_docente = d.id
    LEFT JOIN
        respuesta_estudiante re ON re.id_intento = ic.id
    WHERE 
        ic.id = :intento_id
        AND ic.id_estudiante = :usuario_id
    GROUP BY 
        c.id, e.id, ic.id
";
$stmt_resultado = $db->prepare($query_resultado);
$stmt_resultado->bindParam(':intento_id', $intento_id);
$stmt_resultado->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt_resultado->execute();

if ($stmt_resultado->rowCount() == 0) {
    header("Location: ../../Resource/views/principal.php");
    exit();
}

$resultado = $stmt_resultado->fetch();

// Calcular porcentaje
$porcentaje = 0;
if ($resultado['puntaje_total'] > 0) {
    $porcentaje = round(($resultado['puntaje_obtenido'] / $resultado['puntaje_total']) * 100);
}
$resultado['porcentaje'] = $porcentaje;

// Obtener detalles de respuestas
$query_detalles = "
    SELECT 
        p.id as pregunta_id,
        p.texto_pregunta,
        p.orden_pregunta,
        p.peso_pregunta,
        p.imagen_pregunta,
        o_seleccionada.id as opcion_seleccionada_id,
        o_seleccionada.texto_opcion as respuesta_usuario,
        o_seleccionada.imagen_opcion as imagen_respuesta_usuario,
        o_seleccionada.opcion_correcta as usuario_correcto,
        (SELECT texto_opcion FROM opcion_respuesta 
        WHERE id_pregunta = p.id AND opcion_correcta = 1 
        LIMIT 1) as respuesta_correcta,
        (SELECT imagen_opcion FROM opcion_respuesta 
        WHERE id_pregunta = p.id AND opcion_correcta = 1 
        LIMIT 1) as imagen_respuesta_correcta
    FROM 
        respuesta_estudiante re
    JOIN 
        preguntas p ON re.id_pregunta = p.id
    JOIN 
        opcion_respuesta o_seleccionada ON re.id_opcion_seleccionada = o_seleccionada.id
    WHERE 
        re.id_intento = :intento_id
    ORDER BY 
        p.orden_pregunta
";
$stmt_detalles = $db->prepare($query_detalles);
$stmt_detalles->bindParam(':intento_id', $intento_id);
$stmt_detalles->execute();
$detalles = $stmt_detalles->fetchAll();