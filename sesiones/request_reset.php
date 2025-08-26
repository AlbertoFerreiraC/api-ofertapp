<?php
// api-ofertapp/sesiones/request_reset.php

header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ======================================================================
// RUTA CORREGIDA: Usamos '../../' para subir dos niveles y encontrar la carpeta vendor
// ======================================================================
require '../../vendor/autoload.php';

// Estos archivos están en la misma carpeta, así que la ruta está bien
include_once 'sesiones.php';
include_once 'config.php'; // Asegúrate de tener este archivo con tu SECRET_KEY

// El resto de tu código ya es correcto, pero lo incluyo para que tengas el archivo completo
// ...
// ... (El resto del archivo se mantiene igual)
// ...

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'El formato del correo electrónico no es válido.']);
        exit;
    }

    $sesion = new Sesion();
    $usuarios = $sesion->obtenerUsuarioPorEmail($email);
    $responseMessage = 'Si hay una cuenta asociada a este correo, recibirás un enlace para restablecer tu contraseña.';

    if ($usuarios && count($usuarios) > 0) {
        $usuario = $usuarios[0];
        $userId = $usuario['id_usuario'];
        $timestamp = time();

        // LÍNEA CORREGIDA:
        $signature = hash_hmac('sha256', $userId . $timestamp, SECRET_KEY);

        $resetLink = 'http://localhost/ofertapp-app/adm-ofertapp/index.php?ruta=reset-password&id=' . $userId . '&ts=' . $timestamp . '&sig=' . $signature;

        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0; // Desactivado por ahora
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'albertof6064@gmail.com';
            $mail->Password   = 'afqrenpusexwgssy';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('no-reply@ofertapp.com', 'Soporte OfertApp');
            $mail->addAddress($email, $usuario['nombre'] . ' ' . $usuario['apellido']);
            $mail->isHTML(true);
            $mail->Subject = 'Restablecimiento de Contraseña - OfertApp';
            $mail->Body    = "<h1>Restablecer Contraseña</h1><p>Hola " . htmlspecialchars($usuario['nombre']) . ",</p><p>Haz clic en el siguiente enlace para restablecer tu contraseña. El enlace es válido por 1 hora:</p><p style='text-align:center;'><a href='$resetLink' style='background-color:#0C2A3E;color:#ffffff;padding:12px 20px;text-decoration:none;border-radius:8px;font-weight:bold;'>RESTABLECER MI CONTRASEÑA</a></p><p>Si el botón no funciona, copia y pega esta URL en tu navegador: <code>$resetLink</code></p>";
            $mail->send();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => "El correo no pudo ser enviado. Error: {$mail->ErrorInfo}"]);
            exit;
        }
    }
    echo json_encode(['success' => true, 'message' => $responseMessage]);
}
