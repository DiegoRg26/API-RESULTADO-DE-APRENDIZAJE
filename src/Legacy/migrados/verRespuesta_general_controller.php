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

// Verificar si se han proporcionado los parámetros necesarios
if (!isset($_GET['apertura_id']) || !isset($_GET['estudiante_id']) || 
    !is_numeric($_GET['apertura_id']) || !is_numeric($_GET['estudiante_id'])) {
    $error = "Parámetros incorrectos o faltantes.";
} else {
    $apertura_id = $_GET['apertura_id'];
    $estudiante_id = $_GET['estudiante_id'];

    // Verificar que la apertura pertenezca al docente actual
    $query_apertura = "
        SELECT 
            a.id as apertura_id,
            c.id as cuestionario_id,
            c.titulo,
            c.descripcion,
            p.id as programa_id,
            p.nombre as programa_nombre,
            per.id as periodo_id,
            per.nombre as periodo_nombre,
            a.activo
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
    
    $stmt_apertura = $db->prepare($query_apertura);
    $stmt_apertura->bindParam(':apertura_id', $apertura_id, PDO::PARAM_INT);
    $stmt_apertura->bindParam(':docente_id', $docente_id, PDO::PARAM_INT);
    $stmt_apertura->execute();
    $cuestionario = $stmt_apertura->fetch(PDO::FETCH_ASSOC);

    if (!$cuestionario) {
        $error = "No tiene acceso a este cuestionario o no existe.";
    } else {
        // Obtener información del estudiante
        $query_estudiante = "
            SELECT 
                e.id,
                e.nombre,
                e.identificacion,
                e.email
            FROM 
                estudiante e
            WHERE 
                e.id = :estudiante_id
        ";
        
        $stmt_estudiante = $db->prepare($query_estudiante);
        $stmt_estudiante->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
        $stmt_estudiante->execute();
        $estudiante = $stmt_estudiante->fetch(PDO::FETCH_ASSOC);

        if (!$estudiante) {
            $error = "Estudiante no encontrado.";
        } else {
            // Obtener el intento del cuestionario
            $query_intento = "
                SELECT 
                    ic.id as intento_id,
                    ic.fecha_inicio,
                    ic.fecha_fin,
                    ic.completado,
                    ic.puntaje_total
                FROM 
                    intento_cuestionario ic
                WHERE 
                    ic.id_estudiante = :estudiante_id
                    AND ic.id_apertura = :apertura_id
                    AND ic.completado = 1
                ORDER BY 
                    ic.fecha_fin DESC
                LIMIT 1
            ";
            
            $stmt_intento = $db->prepare($query_intento);
            $stmt_intento->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
            $stmt_intento->bindParam(':apertura_id', $apertura_id, PDO::PARAM_INT);
            $stmt_intento->execute();
            $intento = $stmt_intento->fetch(PDO::FETCH_ASSOC);

            if (!$intento) {
                $error = "No se encontró ningún intento completado para este estudiante en este cuestionario.";
            } else {
                // Calcular el porcentaje de aciertos
                $porcentaje = 0;
                
                // Obtener las respuestas del estudiante con detalles
                $query_respuestas = "
                    SELECT 
                        p.id as pregunta_id,
                        p.texto_pregunta,
                        p.peso_pregunta,
                        p.imagen_pregunta,
                        p.nombre_imagen_pregunta,
                        re.id_opcion_seleccionada,
                        osel.texto_opcion as texto_opcion_seleccionada,
                        osel.imagen_opcion as imagen_opcion_seleccionada,
                        osel.nombre_imagen_opcion as nombre_imagen_opcion_seleccionada,
                        osel.opcion_correcta as es_correcta,
                        (SELECT texto_opcion FROM opcion_respuesta WHERE id_pregunta = p.id AND opcion_correcta = 1 LIMIT 1) as texto_opcion_correcta,
                        (SELECT imagen_opcion FROM opcion_respuesta WHERE id_pregunta = p.id AND opcion_correcta = 1 LIMIT 1) as imagen_opcion_correcta,
                        (SELECT nombre_imagen_opcion FROM opcion_respuesta WHERE id_pregunta = p.id AND opcion_correcta = 1 LIMIT 1) as nombre_imagen_opcion_correcta
                    FROM 
                        preguntas p
                    JOIN 
                        respuesta_estudiante re ON p.id = re.id_pregunta
                    JOIN 
                        opcion_respuesta osel ON re.id_opcion_seleccionada = osel.id
                    WHERE 
                        re.id_intento = :intento_id
                    ORDER BY 
                        p.orden_pregunta
                ";
                
                $stmt_respuestas = $db->prepare($query_respuestas);
                $stmt_respuestas->bindParam(':intento_id', $intento['intento_id'], PDO::PARAM_INT);
                $stmt_respuestas->execute();
                $respuestas = $stmt_respuestas->fetchAll(PDO::FETCH_ASSOC);

                // Contar respuestas correctas y calcular puntaje obtenido
                $respuestas_correctas = 0;
                $puntaje_obtenido = 0;
                foreach ($respuestas as $respuesta) {
                    if ($respuesta['es_correcta']) {
                        $respuestas_correctas++;
                        $puntaje_obtenido += $respuesta['peso_pregunta'];
                    }
                }
                
                // Calcular el porcentaje basado en respuestas correctas / total de preguntas
                $total_preguntas = count($respuestas);
                if ($total_preguntas > 0) {
                    $porcentaje = round(($respuestas_correctas / $total_preguntas) * 100);
                } else {
                    $porcentaje = 0;
                }
                
                // Guardar el puntaje obtenido en el array de intento
                $intento['puntaje_obtenido'] = $puntaje_obtenido;
            }
        }
    }
} 