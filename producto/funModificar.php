<?php
include_once 'controlador.php';

$api = new ApiProducto();

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =========================================================
    // Procesar archivo de imagen (si fue enviado)
    // =========================================================
    $rutaImagen = $_POST['imagen_actual'] ?? ''; // mantener imagen previa

    if (!empty($_FILES['imagen_editar']['name'])) {
        // Guardar imagen en /uploads/productos/
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/productos/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = uniqid() . "_" . basename($_FILES['imagen_editar']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['imagen_editar']['tmp_name'], $targetFile)) {
            $rutaImagen = "/uploads/productos/" . $fileName;
        } else {
            echo json_encode(array("mensaje" => "error_imagen"));
            exit;
        }
    }

    // =========================================================
    // Validar que venga el idProducto
    // =========================================================
    if (!isset($_POST['idProductoEditar'])) {
        echo json_encode(array("mensaje" => "id_invalido"));
        exit;
    }

    // =========================================================
    // Mapear datos desde los nombres del formulario editar
    // =========================================================
    $item = array(
        'idProducto'   => $_POST['idProductoEditar'],
        'empresa_id'   => $_POST['empresa_id_editar'] ?? 0,
        'categoria_id' => $_POST['categoria_editar'] ?? 0,
        'titulo'       => $_POST['titulo_editar'] ?? '',
        'descripcion'  => $_POST['descripcion_editar'] ?? '',
        'cantidad'     => $_POST['cantidad_editar'] ?? 0,
        'costo'        => $_POST['costo_editar'] ?? 0,
        'color'        => $_POST['color_editar'] ?? '',
        'tamano'       => $_POST['tamano_editar'] ?? '',
        'estado'       => $_POST['estado_editar'] ?? 'activo',
        'condicion'    => $_POST['condicion_editar'] ?? 'nuevo',
        'imagen'       => $rutaImagen,
        'en_oferta' => $_POST['en_oferta_editar'] ?? 0

    );

    // =========================================================
    // Llamar al método del controlador
    // =========================================================
    $api->modificarApi($item);
} else {
    echo json_encode(array("mensaje" => "metodo_no_valido"));
}
