<?php
header('Content-Type: application/json; charset=utf-8');
include_once '../db.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["status" => "error", "message" => "Sesión no iniciada"]);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$pass = $_POST['pass'] ?? '';
$estado = $_POST['estado'] ?? 'activo';

try {
    $db = new DB();
    $pdo = $db->connect();

    if ($pass !== '') {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $query = "UPDATE Usuario 
                  SET nombre = :nombre, apellido = :apellido, email = :email, pass = :pass, estado = :estado
                  WHERE id_usuario = :id";
        $params = [
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':email' => $email,
            ':pass' => $hash,
            ':estado' => $estado,
            ':id' => $id_usuario
        ];
    } else {
        $query = "UPDATE Usuario 
                  SET nombre = :nombre, apellido = :apellido, email = :email, estado = :estado
                  WHERE id_usuario = :id";
        $params = [
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':email' => $email,
            ':estado' => $estado,
            ':id' => $id_usuario
        ];
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // 🔹 ACTUALIZAR DATOS EN SESIÓN
    $_SESSION['nombre'] = $nombre;
    $_SESSION['apellido'] = $apellido;
    $_SESSION['email'] = $email;

    echo json_encode(["status" => "ok"]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al actualizar: " . $e->getMessage()
    ]);
}
