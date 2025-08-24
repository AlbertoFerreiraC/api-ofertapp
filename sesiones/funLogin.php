<?php
// funLogin.php

include_once 'apiSesiones.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('content-type: application/json; charset=utf-8');

$api = new ApiSesiones();

$datosRecibidos = file_get_contents("php://input");
// Validamos que los datos no estén vacíos
if (empty($datosRecibidos)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['mensaje' => 'No se recibieron datos.']);
    exit;
}

$data = json_decode($datosRecibidos);

// Validamos que el JSON sea válido y contenga los campos necesarios
if (json_last_error() !== JSON_ERROR_NONE || !isset($data->usuario) || !isset($data->pass)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['mensaje' => 'JSON mal formado o datos incompletos.']);
    exit;
}

$item = array(
    'usuario' => $data->usuario,
    'pass' => $data->pass
);

$api->login($item);
