<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
require_once "../db.php"; // ✅ ruta corregida (un nivel arriba desde /cabezote)

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

try {
    // ✅ Crear instancia de conexión como en tus otros scripts
    $db = new DB();
    $pdo = $db->connect();

    $sql = "
        SELECT 
            p.idProducto,
            p.titulo,
            p.descripcion,
            p.costo,
            p.imagen,
            e.nombre AS empresa,
            c.descripcion AS categoria,
            IFNULL(AVG(r.calificacion), 0) AS rating
        FROM Producto p
        INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
        INNER JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
        LEFT JOIN resena r ON r.Producto_idProducto = p.idProducto
        WHERE p.estado = 'activo'
          AND (p.titulo LIKE :q OR p.descripcion LIKE :q)
        GROUP BY p.idProducto
        ORDER BY p.costo ASC
        LIMIT 10;
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':q' => "%$q%"]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al buscar productos: " . $e->getMessage()]);
}
