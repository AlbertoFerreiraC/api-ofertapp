<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
include_once '../db.php';

$idUsuario = $_GET['idUsuario'] ?? null;

if (!$idUsuario) {
    echo json_encode(["error" => "Falta el ID del usuario comercial."], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = new DB();
    $pdo = $db->connect();

    $query = "
        SELECT 
            r.id_reserva,
            r.id_producto,
            r.cantidad_reserva,
            r.comentario,
            r.fecha_reserva,
            r.estado,
            p.titulo AS producto,
            u.nombre AS cliente,
            u.apellido AS cliente_apellido,
            u.email AS cliente_email,
            co.telefono AS cliente_telefono
        FROM Reserva r
        INNER JOIN Producto p ON r.id_producto = p.idProducto
        INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
        INNER JOIN Usuario u ON r.id_usuario = u.id_usuario
        LEFT JOIN contacto co ON co.Usuario_id_usuario = u.id_usuario
        WHERE r.estado = 'pendiente'
          AND e.Usuario_id_usuario = :idUsuario
        ORDER BY r.fecha_reserva DESC;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":idUsuario", $idUsuario, PDO::PARAM_INT);
    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($reservas ?: [], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener reservas: " . $e->getMessage()]);
}
