<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../public/login.php");
    exit();
}

require_once '../../src/model/conection.php';

// Crear conexión a la base de datos
$database = new Database();
$db = $database->connect();

// ID del docente actual
$docente_id = $_SESSION['usuario_id'];

// Obtener todos los cuestionarios creados por el docente (activos e inactivos)
$sql_cuestionarios = "
    SELECT 
        c.id as cuestionario_id,
        c.titulo,
        c.descripcion,
        p.nombre as programa_nombre,
        a.id as apertura_id,
        per.nombre as periodo_nombre,
        a.activo as apertura_activa,
        COUNT(DISTINCT ic.id_estudiante) as total_estudiantes_respondieron
    FROM 
        cuestionario c
    JOIN 
        relacion_cuestionario_programa rcp ON c.id = rcp.id_cuestionario
    JOIN 
        programa p ON rcp.id_programa = p.id
    JOIN 
        apertura a ON rcp.id = a.id_relacion_cuestionario_programa
    JOIN 
        periodo per ON a.id_periodo = per.id
    LEFT JOIN 
        intento_cuestionario ic ON a.id = ic.id_apertura AND ic.completado = 1
    WHERE 
        rcp.id_docente = :docente_id
    GROUP BY 
        c.id, c.titulo, c.descripcion, p.nombre, a.id, per.nombre, a.activo
    ORDER BY 
        a.activo DESC, per.fecha_inicio DESC, c.titulo
";

$stmt_cuestionarios = $db->prepare($sql_cuestionarios);
$stmt_cuestionarios->bindParam(':docente_id', $docente_id, PDO::PARAM_INT);
$stmt_cuestionarios->execute();
$cuestionarios = $stmt_cuestionarios->fetchAll(PDO::FETCH_ASSOC);

// Si se selecciona una apertura específica
$apertura_seleccionada = null;
$resultados_estudiantes = [];

if (isset($_GET['apertura_id']) && is_numeric($_GET['apertura_id'])) {
    $apertura_id = $_GET['apertura_id'];
    
    // Obtener detalles de la apertura seleccionada (activa o inactiva)
    $sql_apertura = "
        SELECT 
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
            AND rcp.id_docente = :docente_id
    ";
    
    $stmt_apertura = $db->prepare($sql_apertura);
    $stmt_apertura->bindParam(':apertura_id', $apertura_id, PDO::PARAM_INT);
    $stmt_apertura->bindParam(':docente_id', $docente_id, PDO::PARAM_INT);
    $stmt_apertura->execute();
    $apertura_seleccionada = $stmt_apertura->fetch(PDO::FETCH_ASSOC);
    
    if ($apertura_seleccionada) {
        // Obtener resultados de los estudiantes para esta apertura
        $sql_resultados = "
            SELECT 
                e.id as estudiante_id,
                e.nombre as estudiante_nombre,
                e.identificacion,
                e.email,
                COUNT(DISTINCT p.id) as total_preguntas,
                SUM(CASE WHEN or1.opcion_correcta = 1 THEN 1 ELSE 0 END) as respuestas_correctas,
                MAX(ic.fecha_fin) as fecha_respuesta,
                ic.puntaje_total
            FROM 
                estudiante e
            JOIN 
                intento_cuestionario ic ON e.id = ic.id_estudiante
            JOIN 
                respuesta_estudiante re ON ic.id = re.id_intento
            JOIN 
                preguntas p ON re.id_pregunta = p.id
            JOIN 
                opcion_respuesta or1 ON re.id_opcion_seleccionada = or1.id
            WHERE 
                ic.id_apertura = :apertura_id
                AND ic.completado = 1
            GROUP BY 
                e.id, e.nombre, e.identificacion, e.email, ic.puntaje_total
            ORDER BY 
                respuestas_correctas DESC, e.nombre
        ";
        
        $stmt_resultados = $db->prepare($sql_resultados);
        $stmt_resultados->bindParam(':apertura_id', $apertura_id, PDO::PARAM_INT);
        $stmt_resultados->execute();
        $resultados_estudiantes = $stmt_resultados->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular el porcentaje de aciertos para cada estudiante
        foreach ($resultados_estudiantes as $key => $estudiante) {
            if ($estudiante['total_preguntas'] > 0) {
                $resultados_estudiantes[$key]['porcentaje'] = round(($estudiante['respuestas_correctas'] / $estudiante['total_preguntas']) * 100);
            } else {
                $resultados_estudiantes[$key]['porcentaje'] = 0;
            }
        }
    }
} 