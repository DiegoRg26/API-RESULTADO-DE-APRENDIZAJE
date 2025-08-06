<?php
require_once '../../src/model/conection.php';
verificar_sesion();

$database = new Database();
$db = $database->connect();
$mensaje = '';
$error = '';

// Obtener programas disponibles para el docente
// Si el docente tiene un programa asignado, mostrar solo ese programa
// Si no tiene programa asignado, mostrar todos los programas
if (isset($_SESSION['usuario_programa']) && $_SESSION['usuario_programa'] > 0) {
    $query_programas = "SELECT p.id, p.nombre, n.nombre as nivel_nombre, cam.nombre as campus_nombre 
                        FROM programa p 
                        JOIN nivel n ON p.id_nivel = n.id 
                        JOIN campus cam ON p.id_campus = cam.id
                        WHERE p.id = :programa_id";
    $stmt_programas = $db->prepare($query_programas);
    $stmt_programas->bindParam(':programa_id', $_SESSION['usuario_programa'], PDO::PARAM_INT);
} else {
    $query_programas = "SELECT p.id, p.nombre, n.nombre as nivel_nombre, cam.nombre as campus_nombre 
                        FROM programa p 
                        JOIN nivel n ON p.id_nivel = n.id 
                        JOIN campus cam ON p.id_campus = cam.id";
    $stmt_programas = $db->prepare($query_programas);
}
$stmt_programas->execute();
$programas = $stmt_programas->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        // Insertar cuestionario
        $titulo = sanitize_input($_POST['titulo']);
        $descripcion = sanitize_input($_POST['descripcion']);
        $programa_id = sanitize_input($_POST['programa_id']);

        // 1. Insertar en la tabla cuestionario
        $query_cuestionario = "INSERT INTO cuestionario (titulo, descripcion) VALUES (:titulo, :descripcion)";
        $stmt_cuestionario = $db->prepare($query_cuestionario);
        $stmt_cuestionario->bindParam(':titulo', $titulo);
        $stmt_cuestionario->bindParam(':descripcion', $descripcion);
        $stmt_cuestionario->execute();

        $cuestionario_id = $db->lastInsertId();

        // 2. Crear relación cuestionario-programa
        $query_relacion = "INSERT INTO relacion_cuestionario_programa (id_cuestionario, id_programa, id_docente, activo) 
                            VALUES (:id_cuestionario, :id_programa, :id_docente, 1)";
        $stmt_relacion = $db->prepare($query_relacion);
        $stmt_relacion->bindParam(':id_cuestionario', $cuestionario_id);
        $stmt_relacion->bindParam(':id_programa', $programa_id);
        $stmt_relacion->bindParam(':id_docente', $_SESSION['usuario_id']);
        $stmt_relacion->execute();

        $relacion_id = $db->lastInsertId();

        // 3. Insertar preguntas y opciones
        $preguntas = $_POST['preguntas'];
        foreach ($preguntas as $index => $pregunta_data) {
            if (!empty($pregunta_data['texto'])) {
                $peso_pregunta = isset($pregunta_data['peso']) ? floatval($pregunta_data['peso']) : 1.00;
                
                // Procesar imagen de la pregunta
                $imagen_pregunta = null;
                $nombre_imagen_pregunta = null;
                
                if (isset($_FILES['preguntas']['name'][$index]['imagen']) && !empty($_FILES['preguntas']['name'][$index]['imagen'])) {
                    $file_name = $_FILES['preguntas']['name'][$index]['imagen'];
                    $file_tmp = $_FILES['preguntas']['tmp_name'][$index]['imagen'];
                    $file_type = $_FILES['preguntas']['type'][$index]['imagen'];
                    $file_size = $_FILES['preguntas']['size'][$index]['imagen'];
                    
                    // Validar que sea una imagen
                    if (strpos($file_type, 'image/') === 0) {
                        // Leer el contenido de la imagen
                        $imagen_pregunta = file_get_contents($file_tmp);
                        $nombre_imagen_pregunta = $file_name;
                    }
                    
                }
                
                // Insertar pregunta
                $query_pregunta = "INSERT INTO preguntas (id_cuestionario, texto_pregunta, orden_pregunta, peso_pregunta, imagen_pregunta, nombre_imagen_pregunta) 
                                    VALUES (:id_cuestionario, :texto_pregunta, :orden_pregunta, :peso_pregunta, :imagen_pregunta, :nombre_imagen_pregunta)";
                $stmt_pregunta = $db->prepare($query_pregunta);
                $stmt_pregunta->bindParam(':id_cuestionario', $cuestionario_id);
                $stmt_pregunta->bindParam(':texto_pregunta', $pregunta_data['texto']);
                $stmt_pregunta->bindParam(':orden_pregunta', $index);
                $stmt_pregunta->bindParam(':peso_pregunta', $peso_pregunta);
                $stmt_pregunta->bindParam(':imagen_pregunta', $imagen_pregunta, PDO::PARAM_LOB);
                $stmt_pregunta->bindParam(':nombre_imagen_pregunta', $nombre_imagen_pregunta);
                $stmt_pregunta->execute();

                $pregunta_id = $db->lastInsertId();

                // Insertar opciones
                if (!empty($pregunta_data['opciones'])) {
                    foreach ($pregunta_data['opciones'] as $opcion_index => $opcion_data) {
                        // Verificar que la opción tenga texto
                        if (!empty($opcion_data['texto'])) {
                            $es_correcta = (isset($pregunta_data['correcta']) && $pregunta_data['correcta'] == $opcion_index) ? 1 : 0;
                            
                            // Procesar imagen de la opción
                            $imagen_opcion = null;
                            $nombre_imagen_opcion = null;
                            
                            if (isset($_FILES['preguntas']['name'][$index]['opciones'][$opcion_index]['imagen']) && 
                                !empty($_FILES['preguntas']['name'][$index]['opciones'][$opcion_index]['imagen'])) {
                                $file_name = $_FILES['preguntas']['name'][$index]['opciones'][$opcion_index]['imagen'];
                                $file_tmp = $_FILES['preguntas']['tmp_name'][$index]['opciones'][$opcion_index]['imagen'];
                                $file_type = $_FILES['preguntas']['type'][$index]['opciones'][$opcion_index]['imagen'];
                                
                                // Validar que sea una imagen
                                if (strpos($file_type, 'image/') === 0) {
                                    // Leer el contenido de la imagen
                                    $imagen_opcion = file_get_contents($file_tmp);
                                    $nombre_imagen_opcion = $file_name;
                                }
                            }

                            $query_opcion = "INSERT INTO opcion_respuesta (id_pregunta, texto_opcion, opcion_correcta, orden, imagen_opcion, nombre_imagen_opcion) 
                                            VALUES (:id_pregunta, :texto_opcion, :opcion_correcta, :orden, :imagen_opcion, :nombre_imagen_opcion)";
                            $stmt_opcion = $db->prepare($query_opcion);
                            $stmt_opcion->bindParam(':id_pregunta', $pregunta_id);
                            $stmt_opcion->bindParam(':texto_opcion', $opcion_data['texto']);
                            $stmt_opcion->bindParam(':opcion_correcta', $es_correcta);
                            $stmt_opcion->bindParam(':orden', $opcion_index);
                            $stmt_opcion->bindParam(':imagen_opcion', $imagen_opcion, PDO::PARAM_LOB);
                            $stmt_opcion->bindParam(':nombre_imagen_opcion', $nombre_imagen_opcion);
                            $stmt_opcion->execute();
                        }
                    }
                }
            }
        }

        $db->commit();
        $mensaje = 'Cuestionario creado exitosamente';
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error al crear el cuestionario: ' . $e->getMessage();
    }
}