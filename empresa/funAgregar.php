<?php
include_once 'controlador.php';

$api = new ApiEmpresa();

// Leer JSON enviado
$datosRecibidos = file_get_contents("php://input");
$datos = json_decode($datosRecibidos, true); // true = array asociativo

// Validar datos mínimos
if (!isset($datos['nombre']) || !isset($datos['latitud']) || !isset($datos['longitud'])) {
    echo json_encode(array("mensaje" => "datos_incompletos"));
    exit;
}

// Mapear datos recibidos
$item = array(
    // Empresa
    'categoria_id' => $datos['categoria_id'] ?? 0,
    'usuario_id'   => $datos['usuario_id'] ?? 0,
    'nombre'       => $datos['nombre'],
    'direccion'    => $datos['direccion'] ?? '',
    'estado'       => $datos['estado'] ?? 'activo',

    // Dirección
    'calle'        => $datos['calle'] ?? '',
    'numero'       => $datos['numero'] ?? '',
    'barrio'       => $datos['barrio'] ?? '',
    'ciudad'       => $datos['ciudad'] ?? '',
    'departamento' => $datos['departamento'] ?? '',
    'pais'         => $datos['pais'] ?? '',

    // Georeferencia
    'latitud'      => $datos['latitud'],
    'longitud'     => $datos['longitud']
);

// Llamar a la API
$api->agregarApi($item);
