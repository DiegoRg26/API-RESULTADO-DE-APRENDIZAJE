<?php
require_once '../../src/model/conection.php';
verificar_sesion();

$database = new Database();
$db = $database->connect();

// Procesar formulario de creación/edición de periodo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Obtener y sanitizar datos del formulario
        $nombre = sanitize_input($_POST['nombre']);
        $fecha_inicio = sanitize_input($_POST['fecha_inicio']);
        $fecha_fin = sanitize_input($_POST['fecha_fin']);

        if ($_POST['action'] === 'create') {
            // Crear nuevo periodo
            $query = "INSERT INTO periodo (nombre, fecha_inicio, fecha_fin, activo) VALUES (:nombre, :fecha_inicio, :fecha_fin, 1)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            
            if ($stmt->execute()) {
                $mensaje = "Periodo creado correctamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al crear el periodo.";
                $tipo_mensaje = "danger";
            }
        } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
            // Editar periodo existente
            $id = sanitize_input($_POST['id']);
            $query = "UPDATE periodo SET nombre = :nombre, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio);
            $stmt->bindParam(':fecha_fin', $fecha_fin);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $mensaje = "Periodo actualizado correctamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al actualizar el periodo.";
                $tipo_mensaje = "danger";
            }
        }
    } elseif (isset($_POST['deactivate']) && isset($_POST['id'])) {
        // Desactivar periodo
        $id = sanitize_input($_POST['id']);
        
        // Verificar si el periodo tiene aperturas activas
        $check_query = "SELECT COUNT(*) as count FROM apertura WHERE id_periodo = :id AND activo = 1";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':id', $id);
        $check_stmt->execute();
        $result = $check_stmt->fetch();
        
        if ($result['count'] > 0) {
            $mensaje = "No se puede desactivar el periodo porque tiene aperturas activas. Desactive primero todas las aperturas asociadas a este periodo.";
            $tipo_mensaje = "warning";
        } else {
            $query = "UPDATE periodo SET activo = 0 WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $mensaje = "Periodo desactivado correctamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al desactivar el periodo.";
                $tipo_mensaje = "danger";
            }
        }
    } elseif (isset($_POST['activate']) && isset($_POST['id'])) {
        // Reactivar periodo
        $id = sanitize_input($_POST['id']);
        $query = "UPDATE periodo SET activo = 1 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $mensaje = "Periodo reactivado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al reactivar el periodo.";
            $tipo_mensaje = "danger";
        }
    }
}

// Función para obtener el número de aperturas activas e inactivas para un periodo
function obtener_aperturas_periodo($db, $periodo_id) {
    $query = "SELECT 
                SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as aperturas_activas,
                SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as aperturas_inactivas,
                COUNT(*) as total_aperturas
              FROM apertura 
              WHERE id_periodo = :periodo_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':periodo_id', $periodo_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Obtener todos los periodos activos
$query = "SELECT * FROM periodo WHERE activo = 1 ORDER BY fecha_inicio DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$periodos = $stmt->fetchAll();

// Añadir información de aperturas a cada periodo
foreach ($periodos as &$periodo) {
    $aperturas_info = obtener_aperturas_periodo($db, $periodo['id']);
    $periodo['aperturas_activas'] = $aperturas_info['aperturas_activas'] ?: 0;
    $periodo['aperturas_inactivas'] = $aperturas_info['aperturas_inactivas'] ?: 0;
    $periodo['total_aperturas'] = $aperturas_info['total_aperturas'] ?: 0;
}
unset($periodo); // Romper la referencia

// Obtener todos los periodos inactivos
$query = "SELECT * FROM periodo WHERE activo = 0 ORDER BY fecha_inicio DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$periodos_inactivos = $stmt->fetchAll();

// Añadir información de aperturas a cada periodo inactivo
foreach ($periodos_inactivos as &$periodo) {
    $aperturas_info = obtener_aperturas_periodo($db, $periodo['id']);
    $periodo['aperturas_activas'] = $aperturas_info['aperturas_activas'] ?: 0;
    $periodo['aperturas_inactivas'] = $aperturas_info['aperturas_inactivas'] ?: 0;
    $periodo['total_aperturas'] = $aperturas_info['total_aperturas'] ?: 0;
}
unset($periodo); // Romper la referencia

// Obtener periodo para editar si se ha solicitado
$periodo_editar = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = sanitize_input($_GET['edit']);
    $query = "SELECT * FROM periodo WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $periodo_editar = $stmt->fetch();
}