<?php
include_once 'controlador.php';

$api = new ApiProducto();

// Leer JSON recibido
$data = json_decode(file_get_contents("php://input"), true);

header('Content-Type: application/json; charset=UTF-8');

if (!$data || !isset($data['producto_id']) || !isset($data['calificacion']) || !isset($data['id_usuario'])) {
    echo json_encode(["error" => "Datos inválidos o incompletos"]);
    exit;
}

// Llamamos al método de la API
$api->agregarResenaApi($data);
