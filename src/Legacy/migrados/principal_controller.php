<?php
require_once '../../src/model/conection.php';
verificar_sesion();

$database = new Database();
$db = $database->connect();

if ($_SESSION['usuario_programa'] == null) {
    $programa_nombre = 'Transversal';
} else {
    $query = "SELECT * FROM programa WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_SESSION['usuario_programa']);
    $stmt->execute();

    $programa = $stmt->fetch();

    $programa_nombre = $programa['nombre'];
}
