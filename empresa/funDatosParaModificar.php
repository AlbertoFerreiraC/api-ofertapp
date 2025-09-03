<?php
include_once 'controlador.php';

$api = new ApiEmpresa();

// Leer JSON recibido
$datosRecibidos = file_get_contents("php://input");
$datos = json_decode($datosRecibidos);

// Validar que venga un id
if (!isset($datos->idEmpresa)) {
    echo json_encode(array("mensaje" => "id_invalido"));
    exit;
}

// Mapear datos
$item = array(
    'idEmpresa' => $datos->idEmpresa
);

// Llamar al controlador
$api->obtenerDatosParaModificarApi($item);
