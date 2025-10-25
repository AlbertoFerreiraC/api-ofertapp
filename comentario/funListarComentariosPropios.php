<?php
include_once '../db.php';
header('Content-Type: application/json');

$db = new DB();
$pdo = $db->connect();

$idUsuario = $_GET['idUsuario'] ?? null;

if (!$idUsuario) {
    echo json_encode([]);
    exit;
}

try {
    $idUsuario = $_GET['idUsuario'] ?? null;

    $sql = "
    SELECT 
        c.idcomentario,
        p.titulo AS producto,
        e.nombre AS empresa,
        c.comentario,
        r.calificacion,
        DATE_FORMAT(c.fecha_agregado, '%d/%m/%Y') AS fecha
    FROM dosisma_ofertapp.comentario c
    INNER JOIN dosisma_ofertapp.resena r ON c.resena_idresena = r.idresena
    INNER JOIN dosisma_ofertapp.Producto p ON r.Producto_idProducto = p.idProducto
    INNER JOIN dosisma_ofertapp.Empresa e ON r.Empresa_idEmpresa = e.idEmpresa
    INNER JOIN dosisma_ofertapp.Usuario u ON r.Usuario_id_usuario = u.id_usuario
    WHERE u.id_usuario = :idUsuario
    ORDER BY c.fecha_agregado DESC
";


    $stmt = $pdo->prepare($sql);
    $stmt->execute(['idUsuario' => $idUsuario]);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($comentarios);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
