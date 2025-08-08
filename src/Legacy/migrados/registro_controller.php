<?php
require_once '../src/model/conection.php';

$error = '';
$mensaje = '';

// Obtener la lista de programas de la base de datos
$database = new Database();
$db = $database->connect();
$query_programas = "SELECT id, nombre FROM programa ORDER BY nombre";
$stmt_programas = $db->prepare($query_programas);
$stmt_programas->execute();
$programas = $stmt_programas->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = sanitize_input($_POST['nombre']);
    $email = sanitize_input($_POST['email']);
    $identificacion = sanitize_input($_POST['identificacion']);
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    $transversal = isset($_POST['transversal']) ? true : false;
    $programa_id = null;
    
    if (!$transversal) {
        $programa_id = isset($_POST['programa_id']) && $_POST['programa_id'] !== '' ? intval($_POST['programa_id']) : null;
    }
    
    if ($password !== $confirmar_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres';
    } elseif (!$transversal && $programa_id === null) {
        $error = 'Debes seleccionar un programa o marcar la opción Transversal';
    } else {
        $database = new Database();
        $db = $database->connect();
        
        // Verificar si el email ya existe
        $query_verificar = "SELECT id FROM docente WHERE email = :email";
        $stmt_verificar = $db->prepare($query_verificar);
        $stmt_verificar->bindParam(':email', $email);
        $stmt_verificar->execute();
        
        if ($stmt_verificar->rowCount() > 0) {
            $error = 'El email ya está registrado';
        } else {
            // Crear nuevo usuario
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $query_insertar = "INSERT INTO docente (nombre, email, identificacion, password, programa_id) VALUES (:nombre, :email, :identificacion, :password, :programa_id)";
            $stmt_insertar = $db->prepare($query_insertar);
            $stmt_insertar->bindParam(':nombre', $nombre);
            $stmt_insertar->bindParam(':email', $email);
            $stmt_insertar->bindParam(':identificacion', $identificacion);
            $stmt_insertar->bindParam(':password', $password_hash);
            $stmt_insertar->bindParam(':programa_id', $programa_id, PDO::PARAM_INT);
            
            if ($stmt_insertar->execute()) {
                $mensaje = 'Usuario registrado exitosamente. Puedes iniciar sesión ahora.';
                header("Location: ../public/login.php");
                exit();
            } else {
                $error = 'Error al registrar el usuario';
            }
        }
    }
}
