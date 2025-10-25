<?php
session_start();
include_once "../db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["error" => "No hay sesión activa."]);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$db = new DB();
$pdo = $db->connect();

$stmt = $pdo->prepare("SELECT idEmpresa, nombre FROM Empresa WHERE Usuario_id_usuario = :id LIMIT 1");
$stmt->execute([':id' => $id_usuario]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if ($empresa) {
    echo json_encode($empresa);
} else {
    echo json_encode(["error" => "No se encontró empresa asociada."]);
}
