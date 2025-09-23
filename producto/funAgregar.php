<?php
include_once 'controlador.php';

$api = new ApiProducto();

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ================== SUBIR IMAGEN ==================
    $rutaImagen = "";
    if (!empty($_FILES['imagen']['name'])) {
        $targetDir = "../../uploads/productos/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFile)) {
            // 👇 ruta relativa (sin / inicial)
            $rutaImagen = "uploads/productos/" . $fileName;
        } else {
            echo json_encode(array("mensaje" => "error_imagen"));
            exit;
        }
    }

    // ================== DEBUG: LOG POST ==================
    error_log("POST recibido en funAgregar.php:");
    error_log(print_r($_POST, true));
    error_log("empresa_id POST: " . ($_POST['empresa_id'] ?? 'NO LLEGA'));

    // ================== MAPEAR DATOS ==================
    $item = array(
        'empresa_id'   => $_POST['empresa_id'] ?? 0,   // 👈 viene del hidden
        'categoria_id' => $_POST['categoria_id'] ?? 0,
        'titulo'       => $_POST['titulo'] ?? '',
        'descripcion'  => $_POST['descripcion'] ?? '',
        'cantidad'     => $_POST['cantidad'] ?? 0,
        'costo'        => $_POST['costo'] ?? 0,

        // Con valores por defecto si no se envían
        'color'        => $_POST['color'] ?? '',
        'tamano'       => $_POST['tamano'] ?? '',
        'estado'       => $_POST['estado'] ?? 'activo',
        'condicion'    => $_POST['condicion'] ?? 'nuevo',

        'imagen'       => $rutaImagen
    );

    // ================== VALIDAR CAMPOS MÍNIMOS ==================
    if (empty($item['titulo']) || empty($item['descripcion']) || empty($item['categoria_id'])) {
        echo json_encode(array("mensaje" => "datos_incompletos"));
        exit;
    }

    // ================== GUARDAR ==================
    $api->agregarApi($item);

} else {
    echo json_encode(array("mensaje" => "metodo_no_valido"));
}
