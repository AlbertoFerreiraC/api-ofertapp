<?php
include_once 'controlador.php';

$api = new ApiControlador();

// Recibir los datos en formato JSON
$datosRecibidos = file_get_contents("php://input");
$datos = json_decode($datosRecibidos, true);

if ($datos) {
    // Armamos el array con todos los campos
    $item = array(
        'nombre'     => $datos['nombre']     ?? null,
        'apellido'   => $datos['apellido']   ?? null,
        'usuario'    => $datos['usuario']    ?? null,
        'email'      => $datos['email']      ?? null,
        'password'   => isset($datos['password'])
            ? password_hash($datos['password'], PASSWORD_BCRYPT)
            : null,
        'tipoCuenta' => $datos['tipoCuenta'] ?? null,
        'latitud'    => $datos['latitud']    ?? null,
        'longitud'   => $datos['longitud']   ?? null,

        // ================== CAMPOS DE EMPRESA ==================
        'nombreEmpresa'       => $datos['nombreEmpresa']       ?? null,
        'categoriaEmpresa'    => $datos['categoriaEmpresa']    ?? 0,

        // ================== CAMPOS DE DIRECCIÓN ==================
        'calleEmpresa'        => $datos['direccionEmpresa']    ?? null, 
        'numeroEmpresa'       => $datos['numeroEmpresa']       ?? null,
        'barrioEmpresa'       => $datos['barrioEmpresa']       ?? null,
        'ciudadEmpresa'       => $datos['ciudadEmpresa']       ?? null,
        'departamentoEmpresa' => $datos['departamentoEmpresa'] ?? null,
        'paisEmpresa'         => $datos['paisEmpresa']         ?? null
    );

    // Llamamos al método del controlador
    $api->agregarApi($item);
} else {
    echo json_encode([
        "mensaje" => "nok",
        "detalle" => "No se recibieron datos válidos"
    ]);
}
