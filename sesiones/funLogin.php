<?php
// Inicia la sesión para poder guardar las variables del usuario
session_start();

// Importamos la clase de Sesion
include_once 'apiSesiones.php';

// Establecemos la cabecera para indicar que la respuesta será JSON
header('Content-Type: application/json');

// Leemos el JSON que envía el JavaScript
$json = file_get_contents('php://input');
$data = json_decode($json);

// Verificamos que los datos llegaron
if (!isset($data->usuario) || !isset($data->pass)) {
    http_response_code(400); // Bad Request
    echo json_encode(['mensaje' => 'Faltan datos de usuario o contraseña.']);
    exit;
}

$item = array(
    'usuario' => $data->usuario,
    'pass' => $data->pass
);

$api = new ApiSesiones();

// 1. Llamamos a tu función de login y CAPTURAMOS el resultado (true o false).
$resultadoLogin = $api->login($item);

// 2. Comprobamos el resultado.
if ($resultadoLogin === true) {
    // --- LOGIN EXITOSO ---

    // Enviamos una respuesta JSON de éxito con el nombre del usuario, que el JS necesita.
    http_response_code(200); // OK
    echo json_encode([
        "mensaje" => "Login correcto",
        "nombre" => $_SESSION["nombre"] // Leemos el nombre que se guardó en la sesión
    ]);
} else {
    // --- LOGIN FALLIDO ---

    // Enviamos una respuesta JSON de error.
    http_response_code(401); // Unauthorized
    echo json_encode(['mensaje' => 'Usuario o contraseña incorrectos.']);
}
