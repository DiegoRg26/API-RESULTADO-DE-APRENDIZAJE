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

// Eliminar estudiante
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id_estudiante = $_GET['eliminar'];
    
    // Verificar si el estudiante tiene asignaciones
    $sql_verificar = "SELECT COUNT(*) as total FROM asignacion WHERE id_estudiante = :id_estudiante";
    $stmt = $db->prepare($sql_verificar);
    $stmt->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row['total'] > 0) {
        $mensaje = "No se puede eliminar el estudiante porque tiene asignaciones asociadas.";
        $tipo_mensaje = "warning";
    } else {
        // Eliminar estudiante
        $sql_eliminar = "DELETE FROM estudiante WHERE id = :id_estudiante";
        $stmt = $db->prepare($sql_eliminar);
        $stmt->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $mensaje = "Estudiante eliminado con éxito.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar el estudiante.";
            $tipo_mensaje = "danger";
        }
    }
}

// Agregar estudiante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_estudiante'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $identificacion = $_POST['identificacion'];
    $programa_id = $_POST['programa_id'];
    
    // Validar datos
    $error = false;
    
    if (empty($nombre) || empty($email) || empty($identificacion) || empty($programa_id)) {
        $mensaje = "Todos los campos son obligatorios.";
        $tipo_mensaje = "warning";
        $error = true;
    }
    
    // Verificar si el email ya existe
    if (!$error) {
        $sql_verificar = "SELECT id FROM estudiante WHERE email = :email OR identificacion = :identificacion";
        $stmt = $db->prepare($sql_verificar);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':identificacion', $identificacion, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $mensaje = "Ya existe un estudiante con ese email o identificación.";
            $tipo_mensaje = "warning";
            $error = true;
        }
    }
    
    // Insertar estudiante
    if (!$error) {
        $sql_insertar = "INSERT INTO estudiante (nombre, email, identificacion, id_programa) VALUES (:nombre, :email, :identificacion, :programa_id)";
        $stmt = $db->prepare($sql_insertar);
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':identificacion', $identificacion, PDO::PARAM_STR);
        $stmt->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $mensaje = "Estudiante agregado con éxito.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al agregar el estudiante.";
            $tipo_mensaje = "danger";
        }
    }
}

// Obtener programas
$sql_programas = "SELECT id, nombre FROM programa ORDER BY nombre";
$stmt = $db->prepare($sql_programas);
$stmt->execute();
$programas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si la variable de sesión usuario_programa existe
$programa_docente = isset($_SESSION['usuario_programa']) ? $_SESSION['usuario_programa'] : null;

// Filtrar programas según el programa del docente
$programas_filtrados = [];
if ($programa_docente !== null && $programa_docente > 0) {
    // Si el docente tiene un programa asignado, filtrar solo ese programa
    foreach ($programas as $programa) {
        if ($programa['id'] == $programa_docente) {
            $programas_filtrados[] = $programa;
            break;
        }
    }
} else {
    // Si el docente no tiene programa asignado o es admin, mostrar todos los programas
    $programas_filtrados = $programas;
}

// Obtener estudiantes que pertenecen al mismo programa que el docente
// Si es admin o no tiene programa asignado, muestra todos los estudiantes
$es_admin = ($_SESSION['usuario_email'] === 'admin');

if ($es_admin || $programa_docente === null) {
    $sql_estudiantes = "SELECT e.id, e.nombre, e.email, e.identificacion, p.nombre as programa_nombre
                    FROM estudiante e
                    JOIN programa p ON e.id_programa = p.id
                    ORDER BY e.nombre";
    $stmt = $db->prepare($sql_estudiantes);
} else {
    $sql_estudiantes = "SELECT e.id, e.nombre, e.email, e.identificacion, p.nombre as programa_nombre
                    FROM estudiante e
                    JOIN programa p ON e.id_programa = p.id
                    WHERE e.id_programa = :programa_id
                    ORDER BY e.nombre";
    $stmt = $db->prepare($sql_estudiantes);
    $stmt->bindParam(':programa_id', $programa_docente, PDO::PARAM_INT);
}

$stmt->execute();
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar importación de estudiantes desde CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importar_estudiantes']) && isset($_FILES['archivo_estudiantes'])) {
    // Procesar archivo CSV de estudiantes
    $archivo = $_FILES['archivo_estudiantes'];
    
    // Verificar si el archivo es CSV o tiene otro tipo MIME aceptable
    $tipos_aceptados = ['text/csv', 'application/vnd.ms-excel', 'text/plain', 'application/octet-stream'];
    
    if ($archivo['error'] === UPLOAD_ERR_OK && (in_array($archivo['type'], $tipos_aceptados) || pathinfo($archivo['name'], PATHINFO_EXTENSION) === 'csv')) {
        $handle = fopen($archivo['tmp_name'], "r");
        
        if ($handle !== FALSE) {
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
            
            // Saltar la primera línea (encabezados)
            fgetcsv($handle, 1000, $delimitador);
            
            $db->beginTransaction();
            
            try {
                while (($data = fgetcsv($handle, 1000, $delimitador)) !== FALSE) {
                    if (count($data) >= 4) {
                        $nombre = trim($data[0]);
                        $email = trim($data[1]);
                        $identificacion = trim($data[2]);
                        $programa_id = trim($data[3]);
                        
                        if (empty($nombre) || empty($email) || empty($identificacion) || empty($programa_id)) {
                            $errores[] = "Fila con datos incompletos: " . implode(", ", $data);
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
                        }
                    } else {
                        $errores[] = "Fila con formato incorrecto: " . implode(", ", $data);
                    }
                }
                
                $db->commit();
                $mensaje = "Se importaron estudiantes correctamente.";
                if (!empty($errores)) {
                    $mensaje .= " Hubo " . count($errores) . " errores.";
                }
                $tipo_mensaje = "success";
            } catch (PDOException $e) {
                $db->rollBack();
                $mensaje = "Error al importar estudiantes: " . $e->getMessage();
                $tipo_mensaje = "danger";
            }
            
            fclose($handle);
            
            // Actualizar la lista de estudiantes después de importar
            if ($es_admin || $programa_docente === null) {
                $sql_estudiantes = "SELECT e.id, e.nombre, e.email, e.identificacion, p.nombre as programa_nombre
                            FROM estudiante e
                            JOIN programa p ON e.id_programa = p.id
                            ORDER BY e.nombre";
                $stmt = $db->prepare($sql_estudiantes);
            } else {
                $sql_estudiantes = "SELECT e.id, e.nombre, e.email, e.identificacion, p.nombre as programa_nombre
                            FROM estudiante e
                            JOIN programa p ON e.id_programa = p.id
                            WHERE e.id_programa = :programa_id
                            ORDER BY e.nombre";
                $stmt = $db->prepare($sql_estudiantes);
                $stmt->bindParam(':programa_id', $programa_docente, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $mensaje = "Error al abrir el archivo.";
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = "El archivo debe ser de tipo CSV. Tipo detectado: " . $archivo['type'];
        $tipo_mensaje = "warning";
    }
}
