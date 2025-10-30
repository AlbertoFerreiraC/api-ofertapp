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

$categoria = $_GET['categoria'] ?? null;

if (empty($categoria)) {
    echo json_encode(["error" => "Debe especificar una categoría."], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $query = "
        SELECT 
            p.idProducto AS id,
            p.titulo AS nombre,
            p.descripcion,
            p.cantidad,
            p.costo AS precio,
            p.color,
            p.tamano,
            p.estado,
            p.condicion,
            p.imagen AS img,
            ANY_VALUE(e.nombre) AS empresa,
            ANY_VALUE(c.descripcion) AS categoria,
            IFNULL(AVG(r.calificacion), 0) AS rating,
            ANY_VALUE(g.latitud) AS latitud,
            ANY_VALUE(g.longitud) AS longitud
        FROM Producto p
        INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
        INNER JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
        LEFT JOIN resena r ON r.Producto_idProducto = p.idProducto
        LEFT JOIN georeferencia g ON g.Empresa_idEmpresa = e.idEmpresa
        WHERE p.estado = 'activo'
          AND LOWER(c.descripcion) = LOWER(:categoria)
        GROUP BY p.idProducto
        ORDER BY p.idProducto DESC
        LIMIT 0, 200;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos ?: [], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al filtrar por categoría: " . $e->getMessage()]);
}
