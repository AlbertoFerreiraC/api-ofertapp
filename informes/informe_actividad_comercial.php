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

// === LEER FILTRO DE FECHA OPCIONAL ===
$data = json_decode(file_get_contents("php://input"), true);
$fecha_inicio = !empty($data['fecha_inicio']) ? $data['fecha_inicio'] : null;

// === CONSULTA BASE ===
$query = "
    SELECT 
        e.idEmpresa,
        e.nombre AS empresa,
        c.descripcion AS categoria,
        COUNT(DISTINCT p.idProducto) AS total_productos,
        COUNT(DISTINCT r.id_reserva) AS total_reservas,
        COUNT(DISTINCT rs.idresena) AS total_resenas,
        COALESCE(ROUND(AVG(rs.calificacion), 2), 0) AS promedio_calificacion,
        COUNT(DISTINCT d.iddireccion) AS total_sucursales,
        COALESCE(GREATEST(
            MAX(r.fecha_reserva),
            MAX(rs.fecha_agregado)
        ), NULL) AS ultima_actividad
    FROM Empresa e
    LEFT JOIN Categoria c ON e.Categoria_idCategoria = c.idCategoria
    LEFT JOIN Producto p ON e.idEmpresa = p.Empresa_idEmpresa
    LEFT JOIN Reserva r ON p.idProducto = r.id_producto
    LEFT JOIN resena rs ON p.idProducto = rs.Producto_idProducto
    LEFT JOIN direccion d ON e.idEmpresa = d.Empresa_idEmpresa
    WHERE e.estado = 'activo'
";

// === FILTRO POR FECHA (actividad reciente) ===
$params = [];
if ($fecha_inicio) {
    $query .= " AND (DATE(r.fecha_reserva) = :fecha OR DATE(rs.fecha_agregado) = :fecha)";
    $params[':fecha'] = $fecha_inicio;
}

$query .= "
    GROUP BY e.idEmpresa, e.nombre, c.descripcion
    ORDER BY total_reservas DESC, total_productos DESC;
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result ?: [], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al ejecutar consulta: " . $e->getMessage()]);
}
