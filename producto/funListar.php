<?php
include_once 'controlador.php';
header('Content-Type: application/json; charset=UTF-8');

$api = new ApiProducto();

$idUsuario = $_GET['idUsuario'] ?? null;
$tipoUsuario = $_GET['tipoUsuario'] ?? null;

if ($idUsuario && $tipoUsuario === 'comercial') {
    // 👤 Usuario comercial → solo sus productos
    $api->listarApiPorUsuarioActivo($idUsuario);
} elseif ($tipoUsuario === 'administrador') {
    // 🧑‍💼 Administrador → ve todos los productos
    $api->listarApi();
} else {
    // 🚫 No autenticado o sin permisos → vacío
    echo json_encode([]);
}
