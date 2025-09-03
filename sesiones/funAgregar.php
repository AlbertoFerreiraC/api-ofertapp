<?php
include_once 'controlador.php';

$api = new ApiControlador();

// Recibir los datos en formato JSON
$datosRecibidos = file_get_contents("php://input");
$datos = json_decode($datosRecibidos, true);

if ($datos) {
    // Armamos el array con todos los campos
    $item = array(
        'nombre'     => isset($datos['nombre']) ? $datos['nombre'] : null,
        'apellido'   => isset($datos['apellido']) ? $datos['apellido'] : null,
        'usuario'    => isset($datos['usuario']) ? $datos['usuario'] : null,
        'email'      => isset($datos['email']) ? $datos['email'] : null,
        'password'   => isset($datos['password']) ? password_hash($datos['password'], PASSWORD_BCRYPT) : null, // Encriptar la contraseña
        'tipoCuenta' => isset($datos['tipoCuenta']) ? $datos['tipoCuenta'] : null,
        'latitud'    => isset($datos['latitud']) ? $datos['latitud'] : null,
        'longitud'   => isset($datos['longitud']) ? $datos['longitud'] : null
    );

    // Llamamos al método del controlador
    $api->agregarApi($item);
} else {
    echo json_encode([
        "mensaje" => "nok",
        "detalle" => "No se recibieron datos válidos"
    ]);
}
