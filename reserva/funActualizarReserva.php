<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
include_once '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$idReserva = $data['idReserva'] ?? null;

if (!$idReserva) {
    echo json_encode(["error" => "Falta el ID de la reserva."]);
    exit;
}

try {
    $db = new DB();
    $pdo = $db->connect();

    $query = "UPDATE Reserva SET estado = 'finalizado' WHERE id_reserva = :idReserva";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":idReserva", $idReserva, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Reserva marcada como finalizada."]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al actualizar la reserva: " . $e->getMessage()]);
}
