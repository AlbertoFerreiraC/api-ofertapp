<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include_once '../db.php';

// ================== VERIFICAR LOGIN ==================
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    echo json_encode(["error" => "Sesión expirada o no iniciada"]);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// ================== VALIDAR DATOS DEL FORMULARIO ==================
$data = json_decode(file_get_contents("php://input"), true);

$id_producto = isset($data['id_producto']) ? intval($data['id_producto']) : 0;
$cantidad_reserva = isset($data['cantidad_reserva']) ? intval($data['cantidad_reserva']) : 0;
$comentario = isset($data['comentario']) ? trim($data['comentario']) : "";

if ($id_producto <= 0 || $cantidad_reserva <= 0) {
    echo json_encode(["error" => "Datos incompletos o inválidos"]);
    exit;
}

try {
    // ================== CONEXIÓN A BD ==================
    $db = new DB();
    $pdo = $db->connect();

    // ================== VERIFICAR STOCK DISPONIBLE ==================
    $sqlStock = "SELECT cantidad, titulo FROM Producto WHERE idProducto = :id";
    $stmtStock = $pdo->prepare($sqlStock);
    $stmtStock->bindParam(":id", $id_producto, PDO::PARAM_INT);
    $stmtStock->execute();
    $producto = $stmtStock->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo json_encode(["error" => "Producto no encontrado"]);
        exit;
    }

    $stockActual = intval($producto['cantidad']);
    $tituloProducto = $producto['titulo'];

    if ($stockActual <= 0) {
        echo json_encode(["error" => "El producto '$tituloProducto' no tiene stock disponible."]);
        exit;
    }

    if ($cantidad_reserva > $stockActual) {
        echo json_encode([
            "error" => "Solo hay $stockActual unidades disponibles de '$tituloProducto'."
        ]);
        exit;
    }

    // ================== INSERTAR RESERVA ==================
    $sql = "INSERT INTO Reserva (id_producto, id_usuario, cantidad_reserva, comentario, estado)
            VALUES (:id_producto, :id_usuario, :cantidad_reserva, :comentario, 'pendiente')";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
    $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
    $stmt->bindParam(":cantidad_reserva", $cantidad_reserva, PDO::PARAM_INT);
    $stmt->bindParam(":comentario", $comentario, PDO::PARAM_STR);

    if ($stmt->execute()) {
        // ================== ACTUALIZAR STOCK ==================
        $nuevoStock = $stockActual - $cantidad_reserva;
        $sqlUpdate = "UPDATE Producto SET cantidad = :nuevoStock WHERE idProducto = :id";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->bindParam(":nuevoStock", $nuevoStock, PDO::PARAM_INT);
        $stmtUpdate->bindParam(":id", $id_producto, PDO::PARAM_INT);
        $stmtUpdate->execute();

        echo json_encode([
            "mensaje" => "ok",
            "producto" => $tituloProducto,
            "stock_restante" => $nuevoStock
        ]);
    } else {
        echo json_encode(["error" => "No se pudo registrar la reserva"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
}
