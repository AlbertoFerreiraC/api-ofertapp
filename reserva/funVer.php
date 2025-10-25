<?php
include_once '../db.php';
header('Content-Type: application/json');

// Crear instancia de conexión
$db = new DB();
$pdo = $db->connect(); 

$input = json_decode(file_get_contents("php://input"), true);
$idReserva = $input['idReserva'] ?? null;

if (!$idReserva) {
    echo json_encode([]);
    exit;
}

try {
    $sql = "
        SELECT 
            r.id_reserva,
            r.cantidad_reserva,
            r.fecha_reserva,
            r.estado,
            r.comentario,
            p.titulo AS producto,
            p.descripcion,
            p.imagen
        FROM dosisma_ofertapp.Reserva r
        INNER JOIN dosisma_ofertapp.Producto p 
            ON r.id_producto = p.idProducto
        WHERE r.id_reserva = :idReserva
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['idReserva' => $idReserva]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($reserva ?: []);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
