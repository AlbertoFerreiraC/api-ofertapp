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

$idUsuario = $_GET['idUsuario'] ?? null;
$tipoUsuario = $_GET['tipoUsuario'] ?? null;

try {

    if ($tipoUsuario === 'comercial' && !empty($idUsuario)) {
        // 🟢 Comerciante → solo comentarios de productos de su empresa
        $query = "
            SELECT DISTINCT
                co.idcomentario,
                p.titulo AS producto,
                CONCAT(u.nombre, ' ', u.apellido) AS usuario,
                co.comentario,
                co.fecha_agregado AS fecha,
                COALESCE(r.calificacion, 0) AS calificacion
            FROM comentario co
            INNER JOIN resena r ON co.resena_idresena = r.idresena
            INNER JOIN Producto p ON r.Producto_idProducto = p.idProducto
            INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
            INNER JOIN Usuario u ON r.Usuario_id_usuario = u.id_usuario
            WHERE p.estado = 'activo'
              AND e.Usuario_id_usuario = :idUsuario
            ORDER BY co.fecha_agregado DESC;
        ";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    } else {
        // 🔵 Personal o Administrador → todos los comentarios
        $query = "
            SELECT DISTINCT
                co.idcomentario,
                p.titulo AS producto,
                CONCAT(u.nombre, ' ', u.apellido) AS usuario,
                co.comentario,
                co.fecha_agregado AS fecha,
                COALESCE(r.calificacion, 0) AS calificacion
            FROM comentario co
            INNER JOIN resena r ON co.resena_idresena = r.idresena
            INNER JOIN Producto p ON r.Producto_idProducto = p.idProducto
            INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
            INNER JOIN Usuario u ON r.Usuario_id_usuario = u.id_usuario
            WHERE p.estado = 'activo'
            ORDER BY co.fecha_agregado DESC;
        ";

        $stmt = $pdo->prepare($query);
    }

    $stmt->execute();
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($comentarios ?: [], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener los comentarios: " . $e->getMessage()]);
}
