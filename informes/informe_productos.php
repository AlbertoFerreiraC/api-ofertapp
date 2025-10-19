<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include_once '../db.php';

try {
    $db = new DB();
    $pdo = $db->connect();
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

// === LEER ENTRADA JSON ===
$data = json_decode(file_get_contents("php://input"), true);
$fecha_inicio = !empty($data['fecha_inicio']) ? $data['fecha_inicio'] : null;

// === CONSULTA BASE ===
$query = "
    SELECT 
        p.idproducto,
        p.titulo AS nombre_producto,
        p.empresa_idempresa AS id_tienda,
        p.imagen,
        COUNT(r.id_reserva) AS total_consultas,
        MAX(r.fecha_reserva) AS ultima_fecha
    FROM Reserva r
    INNER JOIN Producto p ON r.id_producto = p.idproducto
    WHERE 1=1
";

$params = [];

// === FILTRO SOLO POR FECHA ESPECÍFICA ===
if ($fecha_inicio) {
    $query .= " AND DATE(r.fecha_reserva) = :fecha";
    $params[':fecha'] = $fecha_inicio;
}

$query .= "
    GROUP BY p.idproducto, p.titulo, p.empresa_idempresa, p.imagen
    ORDER BY total_consultas DESC;
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetchAll();

    foreach ($result as &$row) {
        $row['nombre_tienda'] = 'Empresa #' . $row['id_tienda'];
    }

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al ejecutar consulta: " . $e->getMessage()]);
}
