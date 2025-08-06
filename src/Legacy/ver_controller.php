<?php
require_once '../../src/model/conection.php';
verificar_sesion();

$database = new Database();
$db = $database->connect();
$error = '';

// Obtener ID del cuestionario via GET
$cuestionario_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Validar que se haya proporcionado un ID válido
if (!$cuestionario_id || $cuestionario_id <= 0) {
    $error = 'Error: Debe proporcionar un ID de cuestionario válido.';
}

if (empty($error)) {
    try {
        // Verificar que el cuestionario existe y obtener información
        $check_sql = "SELECT 
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
                        c.id = :cuestionario_id";
        $check_stmt = $db->prepare($check_sql);
        $check_stmt->bindParam(':cuestionario_id', $cuestionario_id, PDO::PARAM_INT);
        $check_stmt->execute();
        
        $cuestionario = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cuestionario) {
            $error = "Error: No se encontró un cuestionario con ID: $cuestionario_id";
        }
    } catch (PDOException $e) {
        $error = 'Error de conexión: ' . $e->getMessage();
    }
}

if (empty($error)) {
    try {
        // Query para obtener preguntas y respuestas
        $sql = "SELECT 
                    p.id as pregunta_id,
                    p.texto_pregunta,
                    p.orden_pregunta,
                    p.peso_pregunta,
                    p.imagen_pregunta,
                    o.id as opcion_id,
                    o.texto_opcion,
                    o.imagen_opcion,
                    o.opcion_correcta,
                    o.orden
                FROM preguntas p
                LEFT JOIN opcion_respuesta o ON p.id = o.id_pregunta
                WHERE p.id_cuestionario = :cuestionario_id
                ORDER BY p.orden_pregunta ASC, o.orden ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':cuestionario_id', $cuestionario_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organizar los datos en un array estructurado
        $preguntas_con_opciones = [];
        
        foreach ($resultados as $row) {
            $pregunta_id = $row['pregunta_id'];
            
            // Si la pregunta no existe en el array, la creamos
            if (!isset($preguntas_con_opciones[$pregunta_id])) {
                $preguntas_con_opciones[$pregunta_id] = [
                    'pregunta_id' => $row['pregunta_id'],
                    'texto_pregunta' => $row['texto_pregunta'],
                    'orden_pregunta' => $row['orden_pregunta'],
                    'peso_pregunta' => $row['peso_pregunta'],
                    'imagen_pregunta' => $row['imagen_pregunta'],
                    'opciones' => []
                ];
            }
            
            // Agregar la opción si existe
            if ($row['opcion_id']) {
                $preguntas_con_opciones[$pregunta_id]['opciones'][] = [
                    'opcion_id' => $row['opcion_id'],
                    'texto_opcion' => $row['texto_opcion'],
                    'imagen_opcion' => $row['imagen_opcion'],
                    'es_correcta' => $row['opcion_correcta'],
                    'orden_opcion' => $row['orden']
                ];
            }
        }
    } catch (PDOException $e) {
        $error = 'Error al obtener preguntas: ' . $e->getMessage();
    }
}
