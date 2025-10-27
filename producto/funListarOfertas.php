<?php
header('Content-Type: application/json; charset=UTF-8');
include_once 'controlador.php';

$api = new ApiProducto();

// Capturar idUsuario desde el GET
$idUsuario = $_GET['idUsuario'] ?? null;

if ($idUsuario) {
    $api->listarOfertasPorUsuario($idUsuario);
} else {
    $api->listarOfertas();
}
