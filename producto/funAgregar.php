<?php
include_once 'controlador.php';

$api = new ApiProducto();

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ================== SUBIR IMAGEN ==================
    $rutaImagen = "";
    if (!empty($_FILES['imagen']['name'])) {
        // Guardar en carpeta pública htdocs/uploads/productos/
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/productos/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetFile)) {
            // Ruta accesible desde el navegador
            $rutaImagen = "/uploads/productos/" . $fileName;
        } else {
            echo json_encode(array("mensaje" => "error_imagen"));
            exit;
        }
    }

    // ================== MAPEAR DATOS ==================
    $item = array(
        'empresa_id'   => $_POST['empresa_id'] ?? 0,   // 👈 idEmpresa logueada
        'categoria_id' => $_POST['categoria_id'] ?? 0,
        'titulo'       => $_POST['titulo'] ?? '',
        'descripcion'  => $_POST['descripcion'] ?? '',
        'cantidad'     => $_POST['cantidad'] ?? 0,
        'costo'        => $_POST['costo'] ?? 0,

        // Con valores por defecto
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
