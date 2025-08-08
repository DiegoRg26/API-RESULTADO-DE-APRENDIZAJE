<?php
// Iniciar sesión al principio del script
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Database {
    private $host = 'localhost';
    private $db_name = 'testv2';
    private $username = 'root'; 
    private $password = '';
    private $conn;
    
    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['email'];
    // $nombre = $_POST['nombre'];
    $documento = $_POST['documento'];

    $database = new Database();
    $db = $database->connect();

    $id_cuestionario = $_GET['id'];
    
    $query = "SELECT e.* 
                FROM estudiante e
                JOIN asignacion a ON a.id_estudiante = e.id
                JOIN apertura ap ON a.id_apertura = ap.id
                JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
                WHERE e.email = :email 
                AND e.identificacion = :documento
                AND rcp.id_cuestionario = :id_cuestionario";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    // $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':documento', $documento);
    $stmt->bindParam(':id_cuestionario', $id_cuestionario);
    $stmt->execute();

    

    if($stmt->rowCount() == 1){
        $estudiante = $stmt->fetch();
        $_SESSION['usuario_id'] = $estudiante['id'];
        $_SESSION['usuario_nombre'] = $estudiante['nombre'];
        $_SESSION['usuario_email'] = $email;
        $_SESSION['usuario_identificacion'] = $documento;
        
        // Verificar si el estudiante está asignado al cuestionario y si el periodo está activo
        $query_asignacion = "
            SELECT a.id, a.id_apertura, p.fecha_inicio, p.fecha_fin, p.nombre as periodo_nombre
            FROM asignacion a
            JOIN apertura ap ON a.id_apertura = ap.id
            JOIN periodo p ON ap.id_periodo = p.id
            JOIN relacion_cuestionario_programa rcp ON ap.id_relacion_cuestionario_programa = rcp.id
            WHERE a.id_estudiante = :id_estudiante 
            AND rcp.id_cuestionario = :id_cuestionario
            AND ap.activo = 1
        ";
        $stmt_asignacion = $db->prepare($query_asignacion);
        $stmt_asignacion->bindParam(':id_estudiante', $estudiante['id']);
        $stmt_asignacion->bindParam(':id_cuestionario', $id_cuestionario);
        $stmt_asignacion->execute();
        
        if($stmt_asignacion->rowCount() > 0){
            $asignacion = $stmt_asignacion->fetch();
            $fecha_actual = date('Y-m-d');
            $fecha_inicio = $asignacion['fecha_inicio'];
            $fecha_fin = $asignacion['fecha_fin'];
            $apertura_id = $asignacion['id_apertura'];
            
            // Verificar si el periodo está activo
            if ($fecha_actual >= $fecha_inicio && $fecha_actual <= $fecha_fin) {
                // Verificar si el estudiante ya completó este cuestionario en el periodo actual
                $query_intento = "
                    SELECT ic.id 
                    FROM intento_cuestionario ic
                    WHERE ic.id_estudiante = :id_estudiante 
                    AND ic.id_apertura = :apertura_id
                    AND ic.completado = 1
                ";
                $stmt_intento = $db->prepare($query_intento);
                $stmt_intento->bindParam(':id_estudiante', $estudiante['id']);
                $stmt_intento->bindParam(':apertura_id', $apertura_id);
                $stmt_intento->execute();
                
                if ($stmt_intento->rowCount() > 0) {
                    // El estudiante ya completó este cuestionario en este periodo
                    $error = "Ya has completado este cuestionario en el periodo actual (" . $asignacion['periodo_nombre'] . ")";
                    $_SESSION['error_mensaje'] = $error;
                    header("Location: ../public/linksystem.php?id=" . $id_cuestionario . "&error=1");
                    exit();
                }
                
                // El periodo está activo y no ha completado el cuestionario, permitir acceso
                header("Location: ../resource/views/resolver.php?id=" . $id_cuestionario);
                exit();
            } else {
                // El periodo no está activo
                // Obtener el nombre del cuestionario
                $query_cuestionario = "
                    SELECT c.titulo
                    FROM cuestionario c
                    JOIN relacion_cuestionario_programa rcp ON c.id = rcp.id_cuestionario
                    WHERE c.id = :id_cuestionario
                    LIMIT 1
                ";
                $stmt_cuestionario = $db->prepare($query_cuestionario);
                $stmt_cuestionario->bindParam(':id_cuestionario', $id_cuestionario);
                $stmt_cuestionario->execute();
                $cuestionario = $stmt_cuestionario->fetch();
                
                $error = "El cuestionario '" . $cuestionario['titulo'] . "' no se encuentra disponible, fechas válidas: " . 
                        date('d/m/Y', strtotime($fecha_inicio)) . " al " . 
                        date('d/m/Y', strtotime($fecha_fin));
                $_SESSION['error_mensaje'] = $error;
                header("Location: ../public/linksystem.php?id=" . $id_cuestionario . "&error=1");
                exit();
            }
        } else {
            // El estudiante no está asignado al cuestionario activo
            $error = "No estás habilitado para realizar este cuestionario en el periodo actual";
            // Mostrar mensaje en linksystem.php
            $_SESSION['error_mensaje'] = $error;
            header("Location: ../public/linksystem.php?id=" . $id_cuestionario . "&error=1");
            exit();
        }
    }else{
        $error = "Credenciales incorrectas";
        $_SESSION['error_mensaje'] = $error;
        header("Location: ../public/linksystem.php?id=" . $id_cuestionario . "&error=1");
        exit();
    }
}