<?php
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

function verificar_sesion() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: ../public/linksystem.php");
        exit();
    }
}

verificar_sesion();

$database = new Database();
$db = $database->connect();

$cuestionario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mensaje = '';
$error = '';



// Verificar que el cuestionario existe y está activo
$query_cuestionario = "
    SELECT 
        c.id, 
        c.titulo, 
        c.descripcion, 
        rcp.id as relacion_id,
        d.nombre as creador_nombre,
        p.nombre as programa_nombre,
        n.nombre as nivel_nombre,
        cam.nombre as campus_nombre
    FROM 
        cuestionario c
    JOIN 
        relacion_cuestionario_programa rcp ON c.id = rcp.id_cuestionario
    JOIN 
        docente d ON rcp.id_docente = d.id
    JOIN 
        programa p ON rcp.id_programa = p.id
    JOIN 
        nivel n ON p.id_nivel = n.id
    JOIN 
        campus cam ON p.id_campus = cam.id
    WHERE 
        c.id = :id 
        AND rcp.activo = 1
    LIMIT 1
";

$stmt_cuestionario = $db->prepare($query_cuestionario);
$stmt_cuestionario->bindParam(':id', $cuestionario_id);
$stmt_cuestionario->execute();

if ($stmt_cuestionario->rowCount() == 0) {
    // header("Location: principal.php");
    echo "No se encontró el cuestionario con ID: " . $cuestionario_id;
    exit();
}

$cuestionario = $stmt_cuestionario->fetch();

// Verificar si el periodo está activo
$query_periodo = "
    SELECT 
        p.fecha_inicio, 
        p.fecha_fin, 
        p.nombre as periodo_nombre
    FROM 
        apertura a
    JOIN 
        periodo p ON a.id_periodo = p.id
    JOIN 
        relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
    JOIN
        asignacion asig ON a.id = asig.id_apertura
    WHERE 
        rcp.id_cuestionario = :cuestionario_id
        AND asig.id_estudiante = :estudiante_id
        AND a.activo = 1
    LIMIT 1
";
$stmt_periodo = $db->prepare($query_periodo);
$stmt_periodo->bindParam(':cuestionario_id', $cuestionario_id);
$stmt_periodo->bindParam(':estudiante_id', $_SESSION['usuario_id']);
$stmt_periodo->execute();

if ($stmt_periodo->rowCount() > 0) {
    $periodo = $stmt_periodo->fetch();
    $fecha_actual = date('Y-m-d');
    $fecha_inicio = $periodo['fecha_inicio'];
    $fecha_fin = $periodo['fecha_fin'];
    
    // Verificar si el periodo está activo
    if ($fecha_actual < $fecha_inicio || $fecha_actual > $fecha_fin) {
        $error = "El cuestionario '" . $cuestionario['titulo'] . "' no se encuentra disponible, fechas válidas: " . 
                date('d/m/Y', strtotime($fecha_inicio)) . " al " . 
                date('d/m/Y', strtotime($fecha_fin));
        echo $error;
        exit();
    }
}

// Verificar si ya resolvió este cuestionario en la apertura actual
$query_verificar = "
    SELECT ic.id 
    FROM intento_cuestionario ic
    JOIN apertura a ON ic.id_apertura = a.id
    JOIN relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
    WHERE ic.id_estudiante = :usuario_id 
    AND rcp.id_cuestionario = :cuestionario_id
    AND ic.completado = 1
    AND a.activo = 1
    LIMIT 1
";
$stmt_verificar = $db->prepare($query_verificar);
$stmt_verificar->bindParam(':usuario_id', $_SESSION['usuario_id']);
$stmt_verificar->bindParam(':cuestionario_id', $cuestionario_id);
$stmt_verificar->execute();

if ($stmt_verificar->rowCount() > 0) {
    // header("Location: principal.php");
    // echo "Ya has realizado este cuestionario";
    header("Location: ../../public/linksystem.php");
    $_SESSION['error_mensaje'] = "Ya has realizado este cuestionario en el periodo actual";
    exit();
}

// Obtener preguntas y opciones
$query_preguntas = "
    SELECT 
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
    ORDER BY p.orden_pregunta, o.orden
";
$stmt_preguntas = $db->prepare($query_preguntas);
$stmt_preguntas->bindParam(':cuestionario_id', $cuestionario_id);
$stmt_preguntas->execute();

$preguntas_raw = $stmt_preguntas->fetchAll();
$preguntas = [];

foreach ($preguntas_raw as $row) {
    if (!isset($preguntas[$row['pregunta_id']])) {
        $preguntas[$row['pregunta_id']] = [
            'id' => $row['pregunta_id'],
            'texto_pregunta' => $row['texto_pregunta'],
            'orden_pregunta' => $row['orden_pregunta'],
            'peso_pregunta' => $row['peso_pregunta'],
            'imagen_pregunta' => $row['imagen_pregunta'],
            'opciones' => []
        ];
    }
    
    if ($row['opcion_id']) {
        $preguntas[$row['pregunta_id']]['opciones'][] = [
            'id' => $row['opcion_id'],
            'texto_opcion' => $row['texto_opcion'],
            'imagen_opcion' => $row['imagen_opcion'],
            'es_correcta' => $row['opcion_correcta'],
            'orden_opcion' => $row['orden']
        ];
    }
}

// Procesar respuestas
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();
        
        // 1. Buscar o crear una apertura para este cuestionario
        $query_apertura = "
            SELECT a.id 
            FROM apertura a
            JOIN relacion_cuestionario_programa rcp ON a.id_relacion_cuestionario_programa = rcp.id
            JOIN asignacion asig ON a.id = asig.id_apertura
            WHERE rcp.id_cuestionario = :cuestionario_id
            AND asig.id_estudiante = :estudiante_id
            AND a.activo = 1
            LIMIT 1
        ";
        $stmt_apertura = $db->prepare($query_apertura);
        $stmt_apertura->bindParam(':cuestionario_id', $cuestionario_id);
        $stmt_apertura->bindParam(':estudiante_id', $_SESSION['usuario_id']);
        $stmt_apertura->execute();
        
        if ($stmt_apertura->rowCount() > 0) {
            $apertura = $stmt_apertura->fetch();
            $apertura_id = $apertura['id'];
        } else {
            // Crear una apertura con el periodo actual o sin periodo
            $query_periodo = "SELECT id FROM periodo WHERE NOW() BETWEEN fecha_inicio AND fecha_fin LIMIT 1";
            $stmt_periodo = $db->prepare($query_periodo);
            $stmt_periodo->execute();
            
            if ($stmt_periodo->rowCount() > 0) {
                $periodo = $stmt_periodo->fetch();
                $periodo_id = $periodo['id'];
            } else {
                // Si no hay periodo activo, usar el primer periodo disponible
                $query_primer_periodo = "SELECT id FROM periodo ORDER BY fecha_inicio LIMIT 1";
                $stmt_primer_periodo = $db->prepare($query_primer_periodo);
                $stmt_primer_periodo->execute();
                
                if ($stmt_primer_periodo->rowCount() > 0) {
                    $primer_periodo = $stmt_primer_periodo->fetch();
                    $periodo_id = $primer_periodo['id'];
                } else {
                    throw new Exception("No se encontró ningún periodo disponible");
                }
            }
            
            // Crear la apertura
            $query_nueva_apertura = "
                INSERT INTO apertura (id_periodo, id_relacion_cuestionario_programa) 
                VALUES (:id_periodo, :id_relacion)
            ";
            $stmt_nueva_apertura = $db->prepare($query_nueva_apertura);
            $stmt_nueva_apertura->bindParam(':id_periodo', $periodo_id);
            $stmt_nueva_apertura->bindParam(':id_relacion', $cuestionario['relacion_id']);
            $stmt_nueva_apertura->execute();
            
            $apertura_id = $db->lastInsertId();
        }
        
        // 2. Crear una asignación para este estudiante
        $query_asignacion = "
            INSERT INTO asignacion (id_apertura, id_estudiante)
            VALUES (:id_apertura, :id_estudiante)
            ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)
        ";
        $stmt_asignacion = $db->prepare($query_asignacion);
        $stmt_asignacion->bindParam(':id_apertura', $apertura_id);
        $stmt_asignacion->bindParam(':id_estudiante', $_SESSION['usuario_id']);
        $stmt_asignacion->execute();
        
        $asignacion_id = $db->lastInsertId();
        
        // 3. Crear un nuevo intento de cuestionario
        $query_intento = "
            INSERT INTO intento_cuestionario (id_estudiante, id_apertura, fecha_inicio) 
            VALUES (:id_estudiante, :id_apertura, NOW())
        ";
        $stmt_intento = $db->prepare($query_intento);
        $stmt_intento->bindParam(':id_estudiante', $_SESSION['usuario_id']);
        $stmt_intento->bindParam(':id_apertura', $apertura_id);
        $stmt_intento->execute();
        
        $intento_id = $db->lastInsertId();
        
        // 4. Procesar y guardar respuestas
        $puntaje_total = 0;
        $puntaje_obtenido = 0;
        $respuestas = $_POST['respuestas'];
        
        foreach ($preguntas as $pregunta_id => $pregunta) {
            if (isset($respuestas[$pregunta_id])) {
                $opcion_seleccionada_id = (int)$respuestas[$pregunta_id];
                $puntaje_total += $pregunta['peso_pregunta'];
                
                // Verificar si la respuesta es correcta
                foreach ($pregunta['opciones'] as $opcion) {
                    if ($opcion['id'] == $opcion_seleccionada_id && $opcion['es_correcta']) {
                        $puntaje_obtenido += $pregunta['peso_pregunta'];
                        break;
                    }
                }
                
                // Guardar respuesta
                $query_respuesta = "
                    INSERT INTO respuesta_estudiante (id_intento, id_pregunta, id_opcion_seleccionada, fecha_respuesta) 
                    VALUES (:id_intento, :id_pregunta, :id_opcion, NOW())
                ";
                $stmt_respuesta = $db->prepare($query_respuesta);
                $stmt_respuesta->bindParam(':id_intento', $intento_id);
                $stmt_respuesta->bindParam(':id_pregunta', $pregunta_id);
                $stmt_respuesta->bindParam(':id_opcion', $opcion_seleccionada_id);
                $stmt_respuesta->execute();
            }
        }
        
        // Calcular porcentaje
        $porcentaje = ($puntaje_total > 0) ? round(($puntaje_obtenido / $puntaje_total) * 100, 2) : 0;
        
        // 5. Actualizar el intento como completado
        $query_actualizar_intento = "
            UPDATE intento_cuestionario 
            SET completado = 1, fecha_fin = NOW(), puntaje_total = :puntaje_total 
            WHERE id = :intento_id
        ";
        $stmt_actualizar_intento = $db->prepare($query_actualizar_intento);
        $stmt_actualizar_intento->bindParam(':puntaje_total', $puntaje_obtenido);
        $stmt_actualizar_intento->bindParam(':intento_id', $intento_id);
        $stmt_actualizar_intento->execute();
        
        $db->commit();
        
        // Redirigir a página de resultados
        header("Location: resultado.php?intento_id=" . $intento_id);
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error al procesar las respuestas: ' . $e->getMessage();
    }
}