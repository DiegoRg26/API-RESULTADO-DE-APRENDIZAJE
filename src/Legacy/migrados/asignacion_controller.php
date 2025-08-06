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

// Procesar eliminación de asignación
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id_asignacion = $_GET['eliminar'];
    
    // Verificar que la asignación pertenezca al docente actual
    $sql_verificar = "SELECT a.id 
                    FROM asignacion a
                    JOIN apertura ap ON a.id_apertura = ap.id
                    JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    WHERE a.id = ? AND rcp.id_docente = ?";
    
    $stmt = $db->prepare($sql_verificar);
    $stmt->bindParam(1, $id_asignacion, PDO::PARAM_INT);
    $stmt->bindParam(2, $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // La asignación existe y pertenece al docente
        $sql_eliminar = "DELETE FROM asignacion WHERE id = ?";
        $stmt = $db->prepare($sql_eliminar);
        $stmt->bindParam(1, $id_asignacion, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $mensaje = "Asignación eliminada con éxito.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar la asignación.";
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = "No tiene permisos para eliminar esta asignación o no existe.";
        $tipo_mensaje = "warning";
    }
}

// Procesar eliminación masiva de asignaciones por apertura
if (isset($_POST['eliminar_todas']) && isset($_POST['apertura_eliminar']) && is_numeric($_POST['apertura_eliminar'])) {
    $apertura_id = $_POST['apertura_eliminar'];
    
    // Verificar que la apertura pertenezca al docente actual
    $sql_verificar = "SELECT ap.id 
                    FROM apertura ap
                    JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    WHERE ap.id = ? AND rcp.id_docente = ?";
    
    $stmt = $db->prepare($sql_verificar);
    $stmt->bindParam(1, $apertura_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // La apertura existe y pertenece al docente
        $sql_eliminar = "DELETE a FROM asignacion a
                        JOIN apertura ap ON a.id_apertura = ap.id
                        JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                        WHERE a.id_apertura = ? AND rcp.id_docente = ?";
        
        $stmt = $db->prepare($sql_eliminar);
        $stmt->bindParam(1, $apertura_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $_SESSION['usuario_id'], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $filas_afectadas = $stmt->rowCount();
            $mensaje = "Se eliminaron $filas_afectadas asignaciones con éxito.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar las asignaciones.";
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = "No tiene permisos para eliminar estas asignaciones o la apertura no existe.";
        $tipo_mensaje = "warning";
    }
}

// Obtener aperturas de cuestionarios disponibles
$sql_aperturas = "SELECT a.id, c.titulo, c.descripcion, p.nombre as periodo_nombre, 
                p.fecha_inicio, p.fecha_fin, pr.nombre as programa_nombre
                FROM apertura a
                JOIN relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
                JOIN cuestionario c ON rcp.id_cuestionario = c.id
                JOIN periodo p ON a.id_periodo = p.id
                JOIN programa pr ON rcp.id_programa = pr.id
                WHERE rcp.id_docente = :docente_id
                AND a.activo = 1";

$stmt = $db->prepare($sql_aperturas);
$stmt->bindParam(':docente_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
$stmt->execute();
$aperturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si la variable de sesión usuario_programa existe
$programa_docente = isset($_SESSION['usuario_programa']) ? $_SESSION['usuario_programa'] : null;

// Obtener estudiantes que pertenecen al mismo programa que el docente
// Si es admin o no tiene programa asignado, muestra todos los estudiantes
$es_admin = ($_SESSION['usuario_email'] === 'admin');

if ($es_admin || $programa_docente === null) {
    $sql_estudiantes = "SELECT e.id, e.nombre, e.email, e.identificacion, e.id_programa, p.nombre as programa_nombre
                    FROM estudiante e
                    JOIN programa p ON e.id_programa = p.id
                    ORDER BY e.nombre";
    $stmt = $db->prepare($sql_estudiantes);
} else {
    $sql_estudiantes = "SELECT e.id, e.nombre, e.email, e.identificacion, e.id_programa, p.nombre as programa_nombre
                    FROM estudiante e
                    JOIN programa p ON e.id_programa = p.id
                    WHERE e.id_programa = ?
                    ORDER BY e.nombre";
    $stmt = $db->prepare($sql_estudiantes);
    $stmt->bindParam(1, $programa_docente, PDO::PARAM_INT);
}

$stmt->execute();
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar la asignación si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Depuración
    error_log("POST recibido: " . print_r($_POST, true));
    
    if (isset($_POST['asignar'])) {
        $apertura_id = $_POST['apertura_id'];
        $estudiante_ids = isset($_POST['estudiante_ids']) ? $_POST['estudiante_ids'] : [];
        
        // Depuración
        error_log("Apertura ID: " . $apertura_id);
        error_log("Estudiantes seleccionados: " . print_r($estudiante_ids, true));
        
        if (!empty($estudiante_ids) && !empty($apertura_id)) {
            // Preparar la consulta para insertar asignaciones
            $stmt = $db->prepare("INSERT IGNORE INTO asignacion (id_apertura, id_estudiante) VALUES (:apertura_id, :estudiante_id)");
            $exito = true;
            $db->beginTransaction();
            
            try {
                foreach ($estudiante_ids as $estudiante_id) {
                    $stmt->bindParam(':apertura_id', $apertura_id, PDO::PARAM_INT);
                    $stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
                
                $db->commit();
                $mensaje = "Asignación realizada con éxito.";
                $tipo_mensaje = "success";
            } catch (PDOException $e) {
                $db->rollBack();
                $mensaje = "Error al realizar las asignaciones: " . $e->getMessage();
                $tipo_mensaje = "danger";
                $exito = false;
            }
        } else {
            $mensaje = "Debe seleccionar al menos un estudiante y un cuestionario.";
            $tipo_mensaje = "warning";
        }
    } elseif (isset($_POST['importar_estudiantes']) && isset($_FILES['archivo_estudiantes'])) {
        // Procesar archivo CSV de estudiantes
        $archivo = $_FILES['archivo_estudiantes'];
        
        // Depuración
        error_log("Archivo recibido: " . print_r($archivo, true));
        
        // Verificar si el archivo es CSV o tiene otro tipo MIME aceptable
        $tipos_aceptados = ['text/csv', 'application/vnd.ms-excel', 'text/plain', 'application/octet-stream'];
        
        if ($archivo['error'] === UPLOAD_ERR_OK && (in_array($archivo['type'], $tipos_aceptados) || pathinfo($archivo['name'], PATHINFO_EXTENSION) === 'csv')) {
            $handle = fopen($archivo['tmp_name'], "r");
            
            if ($handle !== FALSE) {
                // Obtener el programa del docente actual
                $programa_id = null;
                if (isset($_SESSION['usuario_programa']) && $_SESSION['usuario_programa'] !== null) {
                    $programa_id = $_SESSION['usuario_programa'];
                } else if ($_SESSION['usuario_email'] === 'admin' && isset($_POST['programa_id']) && !empty($_POST['programa_id'])) {
                    // Si es admin y seleccionó un programa
                    $programa_id = $_POST['programa_id'];
                } else {
                    // Si el docente no tiene programa asignado y no es admin, mostrar error
                    if ($_SESSION['usuario_email'] !== 'admin') {
                        $mensaje = "Error: No tiene un programa asignado. Contacte al administrador.";
                        $tipo_mensaje = "danger";
                        fclose($handle);
                        goto end_import;
                    } else {
                        // Si es admin pero no seleccionó programa
                        $mensaje = "Como administrador, debe seleccionar un programa para los estudiantes.";
                        $tipo_mensaje = "warning";
                        fclose($handle);
                        goto end_import;
                    }
                }
                
                // Preparar la consulta para insertar estudiantes
                $stmt = $db->prepare("INSERT INTO estudiante (nombre, email, identificacion, id_programa) 
                                    VALUES (:nombre, :email, :identificacion, :programa_id) 
                                    ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)");
                
                $contador = 0;
                $errores = [];
                
                // Intentar detectar el delimitador
                $primera_linea = fgets($handle);
                rewind($handle);
                
                $delimitador = ';'; // Delimitador por defecto
                if (strpos($primera_linea, ',') !== false && strpos($primera_linea, ';') === false) {
                    $delimitador = ',';
                }
                
                error_log("Usando delimitador: " . $delimitador);
                
                // Saltar la primera línea (encabezados)
                fgetcsv($handle, 1000, $delimitador);
                
                $db->beginTransaction();
                
                try {
                    while (($data = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
                        error_log("Fila leída: " . print_r($data, true));
                        // Ahora esperamos solo 3 columnas: nombre, email, identificacion
                        if (count($data) >= 3) {
                            $nombre = trim($data[0]);
                            $email = trim($data[1]);
                            $identificacion = trim($data[2]);
                            
                            if (empty($nombre) || empty($email) || empty($identificacion)) {
                                $errores[] = "Fila con datos incompletos: " . implode(", ", $data);
                                continue;
                            }
                            
                            // Si es admin y no hay programa_id, no podemos continuar
                            if ($_SESSION['usuario_email'] === 'admin' && empty($programa_id)) {
                                // Para admin, necesitamos que seleccione un programa
                                $errores[] = "Como administrador, debe seleccionar un programa para los estudiantes.";
                                continue;
                            }
                            
                            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                            $stmt->bindParam(':identificacion', $identificacion, PDO::PARAM_STR);
                            $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
                            
                            try {
                                $stmt->execute();
                                
                                if ($stmt->rowCount() > 0) {
                                    $contador++;
                                }
                            } catch (PDOException $e) {
                                $errores[] = "Error en fila: " . implode(", ", $data) . " - " . $e->getMessage();
                                error_log("Error al insertar estudiante: " . $e->getMessage());
                            }
                        } else {
                            $errores[] = "Fila con formato incorrecto: " . implode(", ", $data);
                        }
                    }
                    
                    $db->commit();
                    $mensaje = "Se importaron $contador estudiantes correctamente.";
                    if (!empty($errores)) {
                        $mensaje .= " Hubo " . count($errores) . " errores. " . print_r($errores, true);
                        error_log("Errores de importación: " . print_r($errores, true));
                    }
                    $tipo_mensaje = "success";
                } catch (PDOException $e) {
                    $db->rollBack();
                    $mensaje = "Error al importar estudiantes: " . $e->getMessage();
                    $tipo_mensaje = "danger";
                    error_log("Error de transacción: " . $e->getMessage());
                }
                
                fclose($handle);
                
                // Actualizar la lista de estudiantes
                if ($es_admin || $programa_docente === null) {
                    $stmt = $db->prepare($sql_estudiantes);
                } else {
                    $stmt = $db->prepare($sql_estudiantes);
                    $stmt->bindParam(1, $programa_docente, PDO::PARAM_INT);
                }
                $stmt->execute();
                $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $mensaje = "Error al abrir el archivo.";
                $tipo_mensaje = "danger";
                error_log("No se pudo abrir el archivo: " . $archivo['tmp_name']);
            }
        } else {
            $mensaje = "El archivo debe ser de tipo CSV. Tipo detectado: " . $archivo['type'];
            $tipo_mensaje = "warning";
            error_log("Tipo de archivo no aceptado: " . $archivo['type']);
        }
        
        end_import: // Etiqueta para goto
    }
}

// Obtener asignaciones existentes
$sql_asignaciones = "SELECT a.id, a.id_apertura, a.id_estudiante, e.identificacion,
                    e.nombre as estudiante_nombre, e.email,
                    c.titulo as cuestionario_titulo, p.nombre as periodo_nombre
                    FROM asignacion a
                    JOIN estudiante e ON a.id_estudiante = e.id
                    JOIN apertura ap ON a.id_apertura = ap.id
                    JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                    JOIN cuestionario c ON rcp.id_cuestionario = c.id
                    JOIN periodo p ON ap.id_periodo = p.id
                    WHERE rcp.id_docente = :docente_id";

$stmt = $db->prepare($sql_asignaciones);
$stmt->bindParam(':docente_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
$stmt->execute();
$asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener solo las aperturas que tienen estudiantes asignados
$sql_aperturas_con_asignaciones = "SELECT DISTINCT ap.id, c.titulo, c.descripcion, p.nombre as periodo_nombre, 
                                p.fecha_inicio, p.fecha_fin, pr.nombre as programa_nombre
                                FROM apertura ap
                                JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                                JOIN cuestionario c ON rcp.id_cuestionario = c.id
                                JOIN periodo p ON ap.id_periodo = p.id
                                JOIN programa pr ON rcp.id_programa = pr.id
                                JOIN asignacion a ON ap.id = a.id_apertura
                                WHERE rcp.id_docente = :docente_id
                                AND ap.activo = 1";

$stmt = $db->prepare($sql_aperturas_con_asignaciones);
$stmt->bindParam(':docente_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
$stmt->execute();
$aperturas_con_asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
