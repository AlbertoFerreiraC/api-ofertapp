<?php
// adm-ofertapp/api-ofertapp/sesiones/update_password.php

header('Content-Type: application/json');
include_once 'sesiones.php';
include_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $ts = $_POST['ts'] ?? '';
    $sig = $_POST['sig'] ?? '';
    $nuevoPass = $_POST['nuevoPass'] ?? '';

    if (empty($id) || empty($ts) || empty($sig) || empty($nuevoPass)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos para completar la solicitud.']);
        exit;
    }

    // Volvemos a validar el token en el servidor como medida de seguridad
    if (time() - $ts >= 3600) {
        echo json_encode(['success' => false, 'message' => 'El enlace ha expirado. Por favor, solicita uno nuevo.']);
        exit;
    }

    // LÍNEA CORREGIDA:
    $firmaServidor = hash_hmac('sha256', $id . $ts, SECRET_KEY);

    if (!hash_equals($firmaServidor, $sig)) {
        echo json_encode(['success' => false, 'message' => 'La firma del enlace no es válida. Petición rechazada.']);
        exit;
    }

    // Si todo es válido, usamos tu método para actualizar la contraseña
    $sesion = new Sesion();
    $item = [
        'idUsuario' => $id,
        'nuevoPass' => $nuevoPass
    ];

    $resultado = $sesion->actualizarDatosUsuario($item);

    if ($resultado == "ok") {
        echo json_encode(['success' => true, 'message' => 'Tu contraseña ha sido actualizada correctamente. Ya puedes iniciar sesión.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ocurrió un error al actualizar tu contraseña. Por favor, intenta de nuevo.']);
    }
}
