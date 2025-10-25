<?php
include_once '../db.php';
header('Content-Type: application/json; charset=utf-8');

// =============== PARÁMETRO ===============
$idUsuario = $_GET['idUsuario'] ?? null;

if (!$idUsuario) {
    echo json_encode([]);
    exit;
}

try {
    $db = new DB();
    $pdo = $db->connect();

    // ======================================================
    // CONSULTA: Historial unificado de Reservas + Comentarios
    // ======================================================
    $sql = "
        SELECT 
            p.titulo AS producto,
            e.nombre AS empresa,
            
            -- Indicadores de acción
            CASE WHEN rsv.id_reserva IS NOT NULL THEN 1 ELSE 0 END AS hizo_reserva,
            CASE WHEN c.idcomentario IS NOT NULL THEN 1 ELSE 0 END AS hizo_comentario,

            -- Cantidad reservada si existe
            rsv.cantidad_reserva,

            -- Datos adicionales
            c.comentario,
            rsn.calificacion,

            -- Fecha más reciente entre reserva y comentario
            DATE_FORMAT(
                COALESCE(c.fecha_agregado, rsv.fecha_reserva),
                '%d/%m/%Y %H:%i'
            ) AS fecha

        FROM dosisma_ofertapp.Producto p
        INNER JOIN dosisma_ofertapp.Empresa e 
            ON p.Empresa_idEmpresa = e.idEmpresa
        
        -- LEFT JOIN para reservas del usuario
        LEFT JOIN dosisma_ofertapp.Reserva rsv 
            ON rsv.id_producto = p.idProducto 
           AND rsv.id_usuario = :idUsuario
        
        -- LEFT JOIN con reseñas hechas por el usuario
        LEFT JOIN dosisma_ofertapp.resena rsn 
            ON rsn.Producto_idProducto = p.idProducto
           AND rsn.Usuario_id_usuario = :idUsuario
        
        -- LEFT JOIN con comentarios asociados a la reseña
        LEFT JOIN dosisma_ofertapp.comentario c 
            ON c.resena_idresena = rsn.idresena
        
        WHERE 
            rsv.id_usuario = :idUsuario
            OR rsn.Usuario_id_usuario = :idUsuario

        GROUP BY 
            p.idProducto, e.idEmpresa, rsv.id_reserva, c.idcomentario, rsn.idresena

        ORDER BY fecha DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['idUsuario' => $idUsuario]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
