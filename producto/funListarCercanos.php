<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

include_once '../db.php';

$lat = $_GET['lat'] ?? null;
$lng = $_GET['lng'] ?? null;

if (!$lat || !$lng) {
    echo json_encode(["error" => "Faltan coordenadas de ubicación."], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = new DB();
    $pdo = $db->connect();

    $query = "SELECT 
            p.idProducto AS id,
            p.titulo AS nombre,
            p.descripcion,
            p.cantidad,
            p.costo AS precio,
            p.color,
            p.tamano,
            p.condicion,
            p.imagen AS img,
            ANY_VALUE(e.nombre) AS empresa,
            ANY_VALUE(c.descripcion) AS categoria,
            IFNULL(AVG(r.calificacion), 0) AS rating,
            ANY_VALUE(g.latitud) AS latitud,
            ANY_VALUE(g.longitud) AS longitud,
            (
                6371 * ACOS(
                    COS(RADIANS(:lat)) * COS(RADIANS(ANY_VALUE(g.latitud))) *
                    COS(RADIANS(ANY_VALUE(g.longitud)) - RADIANS(:lng)) +
                    SIN(RADIANS(:lat)) * SIN(RADIANS(ANY_VALUE(g.latitud)))
                )
            ) AS distancia_km
        FROM Producto p
        INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
        INNER JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
        LEFT JOIN resena r ON r.Producto_idProducto = p.idProducto
        LEFT JOIN georeferencia g ON g.Empresa_idEmpresa = e.idEmpresa
        WHERE p.estado = 'activo'
        GROUP BY p.idProducto
        HAVING distancia_km <= 20
        ORDER BY precio ASC
        LIMIT 0, 100;
";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":lat", $lat);
    $stmt->bindParam(":lng", $lng);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($productos ?: [], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener productos cercanos: " . $e->getMessage()]);
}
