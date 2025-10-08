<?php
header('Content-Type: application/json; charset=utf-8');
include_once 'controlador.php';

$api = new ApiProducto();
$api->listarOfertas();
