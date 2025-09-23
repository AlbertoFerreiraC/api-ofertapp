<?php
session_start();

include_once 'apiSesiones.php';

header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json);

if (!isset($data->usuario) || !isset($data->pass)) {
    http_response_code(400);
    echo json_encode(['mensaje' => 'Faltan datos de usuario o contraseña.']);
    exit;
}

$item = array(
    'usuario' => $data->usuario,
    'pass'    => $data->pass
);

$api = new ApiSesiones();
$resultadoLogin = $api->login($item);

if ($resultadoLogin === true) {
    http_response_code(200);
    echo json_encode([
        "mensaje"      => "Login correcto",
        "nombre"       => $_SESSION["nombre"],
        "tipo_usuario" => $_SESSION["tipo_usuario"],
        "idEmpresa"    => $_SESSION["idEmpresa"]   ?? null,
        "empresa"      => $_SESSION["empresa"]     ?? null
    ]);
} else {
    http_response_code(401);
    echo json_encode(['mensaje' => 'Usuario o contraseña incorrectos.']);
}
