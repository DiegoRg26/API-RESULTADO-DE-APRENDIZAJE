<?php
require_once '../../src/model/conection.php';
verificar_sesion();

/*
    + Este archivo la funcionalidad que cumple es obtener los cuestionarios que no poseen aperturas activas(es decir, los que no han sido asignados a un periodo).
    + Tambien se encarga de crear y desactivar las aperturas.
    + El archivo tambien tiene la funcionalidad de obtener los periodos activos para poder asignarlos a los cuestionarios.
    + Tambien se encarga de obtener los cuestionarios abiertos (es decir, los que no tienen periodo asignado).
*/

$database = new Database();
$db = $database->connect();
$mensaje = '';
$tipo_mensaje = '';

// Obtener cuestionarios del usuario actual (sin periodo asignado)
$query_mis_cuestionarios = "
    SELECT 
        rcp.id, 
        c.id as cuestionario_id,
        c.titulo, 
        c.descripcion,
        p.nombre as programa_nombre
    FROM 
        relacion_cuestionario_programa rcp 
    JOIN 
        cuestionario c ON rcp.id_cuestionario = c.id
    JOIN 
        programa p ON rcp.id_programa = p.id
    LEFT JOIN 
        apertura a ON rcp.id = a.id_relacion_cuestionario_programa AND a.activo = 1
    WHERE 
        rcp.id_docente = :docente_id
        AND rcp.activo = 1
        AND a.id IS NULL
";
$stmt_mis = $db->prepare($query_mis_cuestionarios);
$stmt_mis->bindParam(':docente_id', $_SESSION['usuario_id']);
$stmt_mis->execute();
$mis_cuestionarios = $stmt_mis->fetchAll();




// Obtener periodos disponibles
$query_periodos = "SELECT id, nombre, fecha_inicio, fecha_fin FROM periodo where activo = 1 ORDER BY fecha_inicio DESC";
$stmt_periodos = $db->prepare($query_periodos);
$stmt_periodos->execute();
$periodos = $stmt_periodos->fetchAll();




// Obtener aperturas existentes del usuario
$query_aperturas = "
    SELECT 
        a.id, 
        c.titulo, 
        c.descripcion,
        p.nombre as programa_nombre,
        per.nombre as periodo_nombre,
        per.fecha_inicio,
        per.fecha_fin,
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
        rcp.id_docente = :docente_id
        AND a.activo = 1
    ORDER BY 
        per.fecha_inicio DESC
";
$stmt_aperturas = $db->prepare($query_aperturas);
$stmt_aperturas->bindParam(':docente_id', $_SESSION['usuario_id']);
$stmt_aperturas->execute();
$aperturas = $stmt_aperturas->fetchAll();




// Procesar la desactivación de una apertura
if (isset($_GET['eliminar']) && !empty($_GET['eliminar'])) {
    $apertura_id = sanitize_input($_GET['eliminar']);
    



    // Verificar que la apertura pertenezca al usuario actual
    $query_verificar = "
        SELECT 
            a.id
        FROM 
            apertura a
        JOIN 
            relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
        WHERE 
            a.id = :apertura_id
            AND rcp.id_docente = :docente_id
            AND a.activo = 1
    ";
    $stmt_verificar = $db->prepare($query_verificar);
    $stmt_verificar->bindParam(':apertura_id', $apertura_id);
    $stmt_verificar->bindParam(':docente_id', $_SESSION['usuario_id']);
    $stmt_verificar->execute();
    
    if ($stmt_verificar->rowCount() > 0) {
        try {
            $db->beginTransaction();
            



            // Desactivar la apertura en lugar de eliminarla
            $query_desactivar_apertura = "UPDATE apertura SET activo = 0 WHERE id = :apertura_id";
            $stmt_desactivar_apertura = $db->prepare($query_desactivar_apertura);
            $stmt_desactivar_apertura->bindParam(':apertura_id', $apertura_id);
            $stmt_desactivar_apertura->execute();
            
            $db->commit();
            $mensaje = "La apertura ha sido desactivada correctamente.";
            $tipo_mensaje = "success";
            



            // Actualizar la lista de cuestionarios y aperturas
            $stmt_mis->execute();
            $mis_cuestionarios = $stmt_mis->fetchAll();
            
            $stmt_aperturas->execute();
            $aperturas = $stmt_aperturas->fetchAll();
        } catch (PDOException $e) {
            $db->rollBack();
            $mensaje = "Error al desactivar la apertura: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    } else {
        $mensaje = "No tiene permisos para desactivar esta apertura o no existe.";
        $tipo_mensaje = "warning";
    }
}




// Procesar la creación de una apertura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_apertura'])) {
    $cuestionario_id = sanitize_input($_POST['cuestionario_id']);
    $periodo_id = sanitize_input($_POST['periodo_id']);
    
    if (empty($cuestionario_id) || empty($periodo_id)) {
        $mensaje = "Debe seleccionar un cuestionario y un periodo.";
        $tipo_mensaje = "warning";
    } else {
        try {




            // Verificar que el cuestionario pertenezca al usuario actual
            $query_verificar = "
                SELECT 
                    rcp.id
                FROM 
                    relacion_cuestionario_programa rcp
                WHERE 
                    rcp.id = :cuestionario_id
                    AND rcp.id_docente = :docente_id
                    AND rcp.activo = 1
            ";
            $stmt_verificar = $db->prepare($query_verificar);
            $stmt_verificar->bindParam(':cuestionario_id', $cuestionario_id);
            $stmt_verificar->bindParam(':docente_id', $_SESSION['usuario_id']);
            $stmt_verificar->execute();
            
            if ($stmt_verificar->rowCount() > 0) {
                $relacion_id = $stmt_verificar->fetch()['id'];
                



                // Verificar que no exista ya una apertura activa para este cuestionario
                $query_verificar_apertura = "
                    SELECT 
                        id
                    FROM 
                        apertura
                    WHERE 
                        id_relacion_cuestionario_programa = :relacion_id
                        AND activo = 1
                ";
                $stmt_verificar_apertura = $db->prepare($query_verificar_apertura);
                $stmt_verificar_apertura->bindParam(':relacion_id', $relacion_id);
                $stmt_verificar_apertura->execute();
                
                if ($stmt_verificar_apertura->rowCount() > 0) {
                    $mensaje = "Este cuestionario ya tiene una apertura activa asignada.";
                    $tipo_mensaje = "warning";
                } else {




                    // Crear la apertura con activo = 1 por defecto
                    $query_crear_apertura = "
                        INSERT INTO apertura 
                            (id_periodo, id_relacion_cuestionario_programa, activo) 
                        VALUES 
                            (:periodo_id, :relacion_id, 1)
                    ";
                    $stmt_crear_apertura = $db->prepare($query_crear_apertura);
                    $stmt_crear_apertura->bindParam(':periodo_id', $periodo_id);
                    $stmt_crear_apertura->bindParam(':relacion_id', $relacion_id);
                    $stmt_crear_apertura->execute();
                    
                    $mensaje = "Apertura creada correctamente.";
                    $tipo_mensaje = "success";
                    


                    
                    // Actualizar la lista de cuestionarios y aperturas
                    $stmt_mis->execute();
                    $mis_cuestionarios = $stmt_mis->fetchAll();
                    
                    $stmt_aperturas->execute();
                    $aperturas = $stmt_aperturas->fetchAll();
                }
            } else {
                $mensaje = "No tiene permisos para crear una apertura para este cuestionario o no existe.";
                $tipo_mensaje = "warning";
            }
        } catch (PDOException $e) {
            $mensaje = "Error al crear la apertura: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
}
?>