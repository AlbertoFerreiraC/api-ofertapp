<?php
header('Content-Type: application/json; charset=UTF-8');
include_once 'controlador.php';

$api = new ApiProducto();

$idUsuario = $_GET['idUsuario'] ?? null;
$tipoUsuario = $_GET['tipoUsuario'] ?? null;

// Validar tipo de usuario
if ($idUsuario && $tipoUsuario === 'comercial') {
    // 👤 Comercial → solo sus ofertas
    $api->listarOfertasPorUsuario($idUsuario);
} elseif ($tipoUsuario === 'administrador') {
    // 🧑‍💼 Administrador → todas las ofertas
    $api->listarOfertas();
} else {
    // 👀 Personal o visitante → todas las ofertas públicas
    $api->listarOfertas();
}
