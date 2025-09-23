<?php
include_once 'controlador.php';

$api = new ApiProducto();

// Leer JSON recibido
$datosRecibidos = file_get_contents("php://input");
$datos = json_decode($datosRecibidos);

// Validar que venga un idProducto
if (!isset($datos->idProducto)) {
    echo json_encode(array("mensaje" => "id_invalido"));
    exit;
}

// Mapear datos
$item = array(
    'idProducto' => $datos->idProducto
);

// Llamar al controlador
$api->obtenerDatosParaModificarApi($item);
