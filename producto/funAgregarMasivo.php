<?php
header('Content-Type: application/json');

require_once '../db.php';
require_once 'sql.php';

$response = [
    "mensaje" => "error",
    "insertados" => 0,
    "omitidos" => 0
];

try {

    if (
        !isset($_POST['empresa_id']) ||
        !isset($_POST['productos']) ||
        !isset($_FILES['imagenes'])
    ) {
        echo json_encode($response);
        exit;
    }

    $empresaId = intval($_POST['empresa_id']);
    $productos = json_decode($_POST['productos'], true);
    $imagenes = $_FILES['imagenes'];

    if (!is_array($productos) || count($productos) === 0) {
        echo json_encode($response);
        exit;
    }

    $sql = new Sql();
    $db = $sql->connect();
    $db->beginTransaction();

    $imagenesMap = [];

    // 🔴 EXACTAMENTE IGUAL QUE EL ALTA INDIVIDUAL
    $rutaFisica = $_SERVER['DOCUMENT_ROOT'] . "/uploads/productos/";
    $rutaPublica = "/uploads/productos/";

    if (!file_exists($rutaFisica)) {
        mkdir($rutaFisica, 0777, true);
    }

    foreach ($imagenes['name'] as $i => $nombreOriginal) {

        $tmp = $imagenes['tmp_name'][$i];
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        // Validar extensión
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            continue;
        }

        // Mantener parte del nombre original (mejor trazabilidad)
        $nombreSeguro = preg_replace('/[^a-zA-Z0-9._-]/', '', $nombreOriginal);
        $nombreFinal = uniqid() . "_" . $nombreSeguro;

        $destinoFisico = $rutaFisica . $nombreFinal;

        if (!move_uploaded_file($tmp, $destinoFisico)) {
            throw new Exception("Error al subir imágenes");
        }

        // 🔴 ESTO ES LO QUE VA A LA BD
        $imagenesMap[$nombreOriginal] = $rutaPublica . $nombreFinal;
    }

    // ================= INSERTAR PRODUCTOS =================
    foreach ($productos as $item) {

        // Validaciones mínimas
        if (
            empty($item['titulo']) ||
            empty($item['descripcion']) ||
            !isset($item['cantidad']) ||
            !isset($item['costo']) ||
            empty($item['categoria_id']) ||
            empty($item['imagen'])
        ) {
            $response['omitidos']++;
            continue;
        }

        // Verificar duplicado
        $existe = $sql->verificar_existencia_producto([
            'titulo' => $item['titulo'],
            'empresa_id' => $empresaId
        ]);

        if (!empty($existe)) {
            $response['omitidos']++;
            continue;
        }

        // Verificar imagen mapeada
        if (!isset($imagenesMap[$item['imagen']])) {
            $response['omitidos']++;
            continue;
        }

        // Preparar item
        $producto = [
            'Empresa_idEmpresa' => $empresaId,
            'Categoria_idCategoria' => intval($item['categoria_id']),
            'titulo' => $item['titulo'],
            'descripcion' => $item['descripcion'],
            'cantidad' => intval($item['cantidad']),
            'costo' => intval($item['costo']),
            'color' => $item['color'] ?? null,
            'tamano' => $item['tamano'] ?? null,
            'estado' => $item['estado'] ?? 'activo',
            'condicion' => $item['condicion'] ?? 'nuevo',
            'imagen' => $imagenesMap[$item['imagen']]
        ];

        $sql->agregarProducto($producto);
        $response['insertados']++;
    }

    $db->commit();

    $response['mensaje'] = "ok";
    echo json_encode($response);

} catch (Exception $e) {

    if (isset($db)) {
        $db->rollBack();
    }

    error_log("Carga masiva productos: " . $e->getMessage());

    echo json_encode([
        "mensaje" => "error",
        "detalle" => "Error al procesar la carga masiva"
    ]);
}
