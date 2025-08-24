<?php
// generar_hash_unico.php

// La contraseña que sabemos que es correcta y limpia
$passwordLimpio = 'admin123';

// Generamos el hash nuevo
$hashNuevo = password_hash($passwordLimpio, PASSWORD_DEFAULT);

// Lo mostramos en pantalla para que lo puedas copiar
echo "<!DOCTYPE html><html><head><title>Generador de Hash</title>";
echo "<style>body { font-family: sans-serif; padding: 20px; } strong { font-family: monospace; background: #eee; padding: 5px; border: 1px solid #ccc; }</style>";
echo "</head><body>";
echo "<h1>Hash Nuevo y Limpio</h1>";
echo "<p>Copia y pega el siguiente hash directamente en la columna 'pass' de tu usuario 'admin':</p>";
echo "<hr>";
echo "<strong>" . $hashNuevo . "</strong>";
echo "<hr></body></html>";

?>