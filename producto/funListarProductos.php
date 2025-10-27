<?php
include_once 'controlador.php';

header('Content-Type: application/json; charset=UTF-8');

$api = new ApiProducto();

$idUsuario = $_GET['idUsuario'] ?? null;
$tipoUsuario = $_GET['tipoUsuario'] ?? null;

if ($idUsuario && $tipoUsuario === 'comercial') {
    $api->listarApiPorUsuario($idUsuario);
} elseif ($tipoUsuario === 'administrador' || $tipoUsuario === 'personal') {
    // Todos los productos
    $api->listarApiProducto();
} else {
    $api->listarApiProducto();
}
