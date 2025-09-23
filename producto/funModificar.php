<?php
include_once 'controlador.php';

$api = new ApiProducto();

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Procesar archivo de imagen si fue enviado
    $rutaImagen = $_POST['imagen_actual'] ?? ''; // valor previo por si no se cambia la imagen

    if (!empty($_FILES['imagen']['name'])) {
        $targetDir = "../../uploads/productos/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFile)) {
            $rutaImagen = "/uploads/productos/" . $fileName;
        } else {
            echo json_encode(array("mensaje" => "error_imagen"));
            exit;
        }
    }

    // Validar que venga el idProducto
    if (!isset($_POST['idProducto'])) {
        echo json_encode(array("mensaje" => "id_invalido"));
        exit;
    }

    // Mapear datos
    $item = array(
        'idProducto'   => $_POST['idProducto'],
        'empresa_id'   => $_POST['empresa_id'] ?? 0,
        'categoria_id' => $_POST['categoria_id'] ?? 0,
        'titulo'       => $_POST['titulo'] ?? '',
        'descripcion'  => $_POST['descripcion'] ?? '',
        'cantidad'     => $_POST['cantidad'] ?? 0,
        'costo'        => $_POST['costo'] ?? 0,
        'color'        => $_POST['color'] ?? '',
        'tamano'       => $_POST['tamano'] ?? '',
        'estado'       => $_POST['estado'] ?? 'activo',
        'condicion'    => $_POST['condicion'] ?? 'nuevo',
        'imagen'       => $rutaImagen
    );

    // Llamar al método del controlador
    $api->modificarApi($item);
} else {
    echo json_encode(array("mensaje" => "metodo_no_valido"));
}
