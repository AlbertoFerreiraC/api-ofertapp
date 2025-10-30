<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

include_once '../db.php';

try {
    $db = new DB();
    $pdo = $db->connect();
} catch (Exception $e) {
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}

try {
    // ==============================
    // Solo productos activos y en oferta
    // ==============================
    $query = "
        SELECT 
            p.idProducto,
            p.titulo,
            p.descripcion,
            p.costo AS precio,
            p.imagen,
            e.nombre AS empresa,
            c.descripcion AS categoria,
            p.en_oferta,
            p.estado
        FROM Producto p
        INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
        INNER JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
        WHERE p.en_oferta = 1
          AND p.estado = 'activo'
        ORDER BY p.idProducto DESC
        LIMIT 6;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($ofertas ?: [], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener ofertas: " . $e->getMessage()]);
}
