<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include_once '../db.php';

try {
    $db = new DB();
    $pdo = $db->connect();
} catch (Exception $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

// === LEER ENTRADA JSON ===
$data = json_decode(file_get_contents("php://input"), true);
$fecha_inicio = !empty($data['fecha_inicio']) ? $data['fecha_inicio'] : null;

// === CONSULTA BASE ===
// Se usa la tabla `resena` como indicador de búsquedas/interacciones
$query = "
    SELECT 
        p.idProducto,
        p.titulo AS nombre_producto,
        c.descripcion AS categoria,
        e.nombre AS tienda,
        COUNT(r.idresena) AS total_interacciones,
        MAX(r.fecha_agregado) AS ultima_interaccion
    FROM resena r
    INNER JOIN Producto p ON r.Producto_idProducto = p.idProducto
    INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
    INNER JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
    WHERE 1=1
";

$params = [];

// === FILTRO POR FECHA (misma lógica que en exportar_informe_busquedas.php) ===
if ($fecha_inicio) {
    $query .= " AND DATE(r.fecha_agregado) = :fecha_inicio";
    $params[':fecha_inicio'] = $fecha_inicio;
}

// === AGRUPAR Y ORDENAR ===
$query .= "
    GROUP BY p.idProducto, p.titulo, c.descripcion, e.nombre
    ORDER BY total_interacciones DESC;
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === FORMATEAR RESULTADOS ===
    if (empty($result)) {
        echo json_encode([]);
        exit;
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al ejecutar consulta: " . $e->getMessage()]);
}
