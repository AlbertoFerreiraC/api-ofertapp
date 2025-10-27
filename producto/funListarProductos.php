<?php
include_once 'controlador.php';

header('Content-Type: application/json; charset=UTF-8');

$api = new ApiProducto();
$idUsuario = $_GET['idUsuario'] ?? null;

if ($idUsuario) {
    $api->listarApiPorUsuario($idUsuario);
} else {
    $api->listarApiProducto();
}
?>