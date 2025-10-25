<?php
include_once '../db.php';
header('Content-Type: application/json');

// Crear instancia de conexión
$db = new DB();
$pdo = $db->connect(); // ✅ conexión lista

$idUsuario = $_GET['idUsuario'] ?? null;

if (!$idUsuario) {
    echo json_encode([]);
    exit;
}

try {
    $sql = "
        SELECT 
            r.id_reserva,
            p.titulo AS producto,
            r.cantidad_reserva,
            r.fecha_reserva,
            r.estado,
            r.comentario
        FROM dosisma_ofertapp.Reserva r
        INNER JOIN dosisma_ofertapp.Producto p 
            ON r.id_producto = p.idProducto
        WHERE r.id_usuario = :idUsuario
        ORDER BY r.fecha_reserva DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['idUsuario' => $idUsuario]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
