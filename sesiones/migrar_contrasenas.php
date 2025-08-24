<?php

include_once '../db.php';
$dryRun = false;
echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Migración de Contraseñas</title>';
echo '<style>
        body { font-family: monospace; background-color: #1e1e1e; color: #d4d4d4; padding: 20px; }
        .container { max-width: 800px; margin: auto; }
        .header { background-color: #252526; padding: 15px; border-left: 5px solid #007acc; margin-bottom: 20px; }
        .warning { color: #f48771; border: 1px solid #f48771; padding: 15px; margin-bottom: 20px; }
        .success { color: #8fce00; }
        .info { color: #009ccc; }
        .error { color: #f44747; }
        .skipped { color: #777777; }
      </style></head><body><div class="container">';

echo '<div class="header"><h1>Script de Migración de Contraseñas (base64 a password_hash)</h1></div>';

echo '<div class="warning">
        <h2>¡MUY IMPORTANTE! ANTES DE CONTINUAR:</h2>
        <ol>
            <li><strong>REALIZA UNA COPIA DE SEGURIDAD COMPLETA</strong> de tu tabla `Usuario`.</li>
            <li>Ejecuta este script primero con <strong>$dryRun = true;</strong> para simular el proceso.</li>
            <li>Cuando estés seguro, cambia a <strong>$dryRun = false;</strong> para aplicar los cambios.</li>
            <li><strong>ELIMINA ESTE ARCHIVO</strong> del servidor una vez que hayas terminado la migración.</li>
        </ol>
      </div>';

if ($dryRun) {
    echo '<h2 class="info">MODO SIMULACIÓN (DRY RUN) ACTIVO. No se realizarán cambios en la base de datos.</h2>';
} else {
    echo '<h2 class="error">MODO REAL (LIVE) ACTIVO. Se modificarán los datos en la base de datos.</h2>';
}

try {
    $database = new DB();
    $pdo = $database->connect();

    $stmt = $pdo->query("SELECT id_usuario, usuario, pass FROM Usuario");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($users)) {
        echo "<p class='info'>No se encontraron usuarios en la base de datos.</p>";
        exit;
    }

    echo "<p>Se encontraron " . count($users) . " usuarios. Iniciando proceso...</p><hr>";

    $processedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;

    foreach ($users as $user) {
        $id = $user['id_usuario'];
        $username = $user['usuario'];
        $currentPass = $user['pass'];

        echo "<div>Procesando usuario: <strong>{$username}</strong> (ID: {$id})</div>";

        if (password_get_info($currentPass)['algoName'] !== 'unknown') {
            echo "<div class='skipped'>&nbsp;&nbsp;-> Contraseña ya está hasheada. Omitiendo.</div>";
            $skippedCount++;
            continue;
        }

        if (empty($currentPass)) {
            echo "<div class='skipped'>&nbsp;&nbsp;-> Contraseña está vacía. Omitiendo.</div>";
            $skippedCount++;
            continue;
        }

        $plainTextPass = base64_decode($currentPass, true);

        if ($plainTextPass === false) {
            echo "<div class='error'>&nbsp;&nbsp;-> ERROR: No se pudo decodificar la contraseña (no es un base64 válido). Omitiendo.</div>";
            $errorCount++;
            continue;
        }

        $newHash = password_hash($plainTextPass, PASSWORD_DEFAULT);

        echo "<div class='info'>&nbsp;&nbsp;-> Contraseña decodificada y hasheada correctamente.</div>";

        if (!$dryRun) {
            $updateStmt = $pdo->prepare("UPDATE Usuario SET pass = :newPass WHERE id_usuario = :id");
            $updateStmt->bindParam(':newPass', $newHash, PDO::PARAM_STR);
            $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($updateStmt->execute()) {
                echo "<div class='success'>&nbsp;&nbsp;-> ÉXITO: Base de datos actualizada.</div>";
                $processedCount++;
            } else {
                echo "<div class='error'>&nbsp;&nbsp;-> ERROR: No se pudo actualizar la base de datos.</div>";
                $errorCount++;
            }
        } else {
            echo "<div>&nbsp;&nbsp;-> (Simulación) Se actualizaría la contraseña a: '{$newHash}'</div>";
            $processedCount++;
        }
        echo "<br>";
    }

    echo "<hr><h2>Proceso finalizado.</h2>";
    echo "<p class='success'>Usuarios procesados para migración: {$processedCount}</p>";
    echo "<p class='skipped'>Usuarios omitidos (ya migrados o sin pass): {$skippedCount}</p>";
    echo "<p class='error'>Errores encontrados: {$errorCount}</p>";
} catch (PDOException $e) {
    die("<div class='error'>Error de conexión a la base de datos: " . $e->getMessage() . "</div>");
}

echo '</div></body></html>';
