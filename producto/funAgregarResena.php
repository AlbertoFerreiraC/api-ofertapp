<?php
include_once 'controlador.php';

$api = new ApiProducto();

// Leer JSON recibido
$data = json_decode(file_get_contents("php://input"), true);

header('Content-Type: application/json; charset=UTF-8'); // 👈 importante

if (!$data || !isset($data['producto_id']) || !isset($data['calificacion'])) {
    echo json_encode(["error" => "Datos inválidos"]);
    exit;
}

$api->agregarResenaApi($data);
