<?php
include_once 'controlador.php';

$api = new ApiProducto();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $api->detalleApi($id);
} else {
    echo json_encode(["error" => "ID no válido"]);
}
