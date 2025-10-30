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
    // Traer todos los productos activos, ordenados por calificación o cantidad
    // ==============================

    // Si tu tabla "resena" tiene calificación, usaremos el promedio
    $query = "
        SELECT 
            p.idProducto,
            p.titulo,
            p.descripcion,
            p.costo AS precio,
            p.imagen,
            e.nombre AS empresa,
            c.descripcion AS categoria,
            p.estado,
            COALESCE(AVG(r.calificacion), 0) AS rating,
            p.en_oferta
        FROM Producto p
        INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
        INNER JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
        LEFT JOIN resena r ON p.idProducto = r.Producto_idProducto
        WHERE p.estado = 'activo' and en_oferta != 1
        GROUP BY p.idProducto, p.titulo, p.descripcion, p.costo, p.imagen, e.nombre, c.descripcion, p.estado
        ORDER BY rating DESC, p.idProducto DESC;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos ?: [], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener productos más vendidos: " . $e->getMessage()]);
}
