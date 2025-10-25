<?php
require '../../vendor/autoload.php';
include_once '../db.php';
session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

// ====== CONFIGURACIÓN DOMPDF ======
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);

// ====== CONEXIÓN ======
$db = new DB();
$pdo = $db->connect();

// ====== VALIDAR SESIÓN ======
if (!isset($_SESSION['id_usuario'])) {
    die("No hay un usuario en sesión. Por favor, inicie sesión para generar el informe.");
}

$id_usuario = $_SESSION['id_usuario'];
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;

// ====== OBTENER EMPRESA ASOCIADA ======
$queryEmpresa = "
    SELECT 
        e.idEmpresa,
        e.nombre AS empresa,
        c.descripcion AS categoria,
        e.direccion,
        co.telefono,
        co.correo,
        e.estado
    FROM Empresa e
    LEFT JOIN Categoria c ON e.Categoria_idCategoria = c.idCategoria
    LEFT JOIN contacto co ON co.Empresa_idEmpresa = e.idEmpresa
    WHERE e.Usuario_id_usuario = :id_usuario
    LIMIT 1;
";
$stmt = $pdo->prepare($queryEmpresa);
$stmt->execute([':id_usuario' => $id_usuario]);
$empresa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {
    die("No se encontró una empresa asociada al usuario en sesión.");
}

// ====== CONSULTA DE PRODUCTOS ======
$queryProductos = "
    SELECT 
        p.idProducto,
        p.titulo AS nombre_producto,
        c.descripcion AS categoria,
        p.cantidad,
        p.costo,
        p.condicion,
        p.en_oferta,
        COUNT(DISTINCT r.id_reserva) AS total_reservas,
        COALESCE(ROUND(AVG(rs.calificacion), 2), 0) AS promedio_calificacion,
        COALESCE(GREATEST(MAX(r.fecha_reserva), MAX(rs.fecha_agregado)), NULL) AS ultima_actividad
    FROM Producto p
    LEFT JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
    LEFT JOIN Reserva r ON r.id_producto = p.idProducto
    LEFT JOIN resena rs ON rs.Producto_idProducto = p.idProducto
    WHERE p.Empresa_idEmpresa = :id_empresa
";
$params = [':id_empresa' => $empresa['idEmpresa']];
if ($fecha_inicio) {
    $queryProductos .= " AND (DATE(r.fecha_reserva) = :fecha OR DATE(rs.fecha_agregado) = :fecha)";
    $params[':fecha'] = $fecha_inicio;
}
$queryProductos .= "
    GROUP BY p.idProducto, p.titulo, c.descripcion, p.cantidad, p.costo, p.condicion, p.en_oferta
    ORDER BY total_reservas DESC, p.costo DESC;
";
$stmt = $pdo->prepare($queryProductos);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ====== DATOS DEL ENCABEZADO ======
$fecha_hoy = date("d/m/Y H:i");
$rango = $fecha_inicio ? "Actividad registrada el " . $fecha_inicio : "Todas las fechas registradas";

// ====== HTML ======
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Informe de Actividad Comercial - OfertApp</title>
<style>
@page { margin: 25px 20px 40px 20px; }
body {
    font-family: "DejaVu Sans", Arial, sans-serif;
    margin: 0;
    color: #333;
    font-size: 12px;
    background-color: #fffaf6;
}

/* ===== HEADER CLARO Y LEGIBLE ===== */
header {
    background-color: #f08438; /* color sólido */
    color: #ffffff;
    padding: 25px 30px;
    text-align: center;
    border-bottom: 5px solid #e85a0f;
}
header h1 {
    margin: 0;
    font-size: 26px;
    font-weight: bold;
    color: #ffffff;
    letter-spacing: 1px;
}
header h2 {
    margin: 6px 0 0 0;
    font-size: 15px;
    font-weight: normal;
    color: #fffde9;
}

main { padding: 20px 25px; }
.meta {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 10px; font-size: 13px; color: #444;
    border-bottom: 2px solid #f08438; padding-bottom: 5px;
}
.summary {
    margin-top: 15px;
    background: #fff8f1;
    padding: 15px 20px;
    border-left: 5px solid #f08438;
    border-radius: 6px;
    font-size: 13px;
    color: #333;
    box-shadow: 0 0 6px rgba(0, 0, 0, 0.05);
}
.empresa-info {
    margin-top: 20px;
    background: #fff3e6;
    padding: 15px 25px;
    border-left: 5px solid #f08438;
    border-radius: 8px;
    font-size: 13px;
    line-height: 1.6;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.08);
}
.empresa-info h3 { margin: 0 0 8px 0; color: #e85a0f; font-size: 15px; }
.empresa-info span { font-weight: bold; color: #333; }

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
    table-layout: fixed;
    word-wrap: break-word;
    background-color: white;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
th, td {
    border: 1px solid #ddd;
    padding: 6px;
    text-align: center;
    font-size: 11px;
    white-space: normal;
}
th {
    background-color: #f08438;
    color: #fff;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
tr:nth-child(even) { background-color: #fff4eb; }
tr:hover { background-color: #ffe1c4; transition: background 0.3s ease; }
.stars { color: #ffcc00; font-size: 13px; text-shadow: 0 0 2px rgba(0,0,0,0.2); }
.price { color: #e85a0f; font-weight: bold; }
.in-offer {
    background-color: #dff6dd; color: #3e8e41;
    padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: bold;
}
.no-offer {
    background-color: #fce7e7; color: #c62828;
    padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: bold;
}
footer {
    text-align: center; font-size: 11px; color: #666;
    margin-top: 20px; padding: 10px 0; border-top: 1px solid #ddd;
}
</style>
</head>

<body>
<header>
    <h1>OfertApp</h1>
    <h2>Informe de Actividad Comercial — ' . htmlspecialchars($empresa['empresa']) . '</h2>
</header>

<main>
    <div class="meta">
        <span><b>Empresa:</b> ' . htmlspecialchars($empresa['empresa']) . '</span>
        <span><b>Generado el:</b> ' . $fecha_hoy . '</span>
    </div>

    <div class="summary">
        <b>Resumen:</b> Este informe detalla la actividad comercial de la empresa <b>' . htmlspecialchars($empresa['empresa']) . '</b> durante el
        periodo analizado, incluyendo información de sus productos activos, ofertas, reservas y reseñas de usuarios
        en la plataforma OfertApp.
    </div>

    <section class="empresa-info">
        <h3>Datos de la Empresa</h3>
        <p><span>Nombre:</span> ' . htmlspecialchars($empresa['empresa']) . '</p>
        <p><span>Categoría:</span> ' . htmlspecialchars($empresa['categoria'] ?? '-') . '</p>
        <p><span>Dirección:</span> ' . htmlspecialchars($empresa['direccion'] ?? '-') . '</p>
        <p><span>Correo:</span> ' . htmlspecialchars($empresa['correo'] ?? '-') . '</p>
        <p><span>Teléfono:</span> ' . htmlspecialchars($empresa['telefono'] ?? '-') . '</p>
        <p><span>Estado:</span> ' . htmlspecialchars(ucfirst($empresa['estado'])) . '</p>
    </section>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Cantidad</th>
                <th>Precio (Gs)</th>
                <th>Condición</th>
                <th>Oferta</th>
                <th>Reservas</th>
                <th>⭐ Calificación</th>
                <th>Última Actividad</th>
            </tr>
        </thead>
        <tbody>';

if (empty($productos)) {
    $html .= '<tr><td colspan="10">No hay productos registrados</td></tr>';
} else {
    foreach ($productos as $i => $p) {
        $stars = '';
        $rating = (float)$p['promedio_calificacion'];
        for ($s = 1; $s <= 5; $s++) {
            $stars .= $s <= round($rating) ? '★' : '☆';
        }

        $html .= '<tr>
            <td>' . ($i + 1) . '</td>
            <td><b>' . htmlspecialchars($p['nombre_producto']) . '</b></td>
            <td>' . htmlspecialchars($p['categoria'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['cantidad']) . '</td>
            <td class="price">' . number_format($p['costo'], 0, ',', '.') . '</td>
            <td>' . htmlspecialchars($p['condicion'] ?? '-') . '</td>
            <td>' . ($p['en_oferta'] ? '<span class="in-offer">Sí</span>' : '<span class="no-offer">No</span>') . '</td>
            <td>' . htmlspecialchars($p['total_reservas']) . '</td>
            <td class="stars">' . $stars . '</td>
            <td>' . htmlspecialchars($p['ultima_actividad'] ?? '-') . '</td>
        </tr>';
    }
}

$html .= '
        </tbody>
    </table>
</main>

<footer>
    Generado automáticamente por <b>OfertApp</b> — © ' . date("Y") . ' | Sistema de Comparativa de Precios en Paraguay
</footer>
</body>
</html>';

// ====== GENERAR PDF ======
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="informe_actividad_' . $empresa['empresa'] . '.pdf"');
echo $dompdf->output();
exit;
