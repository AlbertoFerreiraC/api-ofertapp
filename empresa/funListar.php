<?php
header('Content-Type: application/json; charset=UTF-8');
include_once 'controlador.php';

$api = new ApiEmpresa();
$idUsuario = $_GET['idUsuario'] ?? null;

if ($idUsuario) {
    $api->listarApiPorUsuario($idUsuario);
} else {
    $api->listarApi();
}
