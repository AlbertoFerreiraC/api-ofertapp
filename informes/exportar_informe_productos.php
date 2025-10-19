<?php
require '../../vendor/autoload.php';
include_once '../db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===== LIMPIAR CUALQUIER SALIDA PREVIA =====
while (ob_get_level()) {
  ob_end_clean();
}
header_remove();
header('Content-Type: application/pdf');

// ===== LEER PARÁMETROS =====
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;

// ===== CONEXIÓN A BD =====
$db = new DB();
$pdo = $db->connect();

// ===== CONSULTA =====
$query = "
    SELECT 
        p.idProducto,
        p.titulo AS nombre_producto,
        p.Empresa_idEmpresa AS id_tienda,
        COUNT(r.id_reserva) AS total_consultas,
        MAX(r.fecha_reserva) AS ultima_fecha
    FROM Reserva r
    INNER JOIN Producto p ON r.id_producto = p.idProducto
    WHERE 1=1
";
$params = [];

if ($fecha_inicio) {
  $query .= " AND DATE(r.fecha_reserva) = :fecha";
  $params[':fecha'] = $fecha_inicio;
}

$query .= "
    GROUP BY p.idProducto, p.titulo, p.Empresa_idEmpresa
    ORDER BY total_consultas DESC;
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== ARMAR HTML =====
$fecha_hoy = date("d/m/Y H:i");
$rango = $fecha_inicio ? "Fecha seleccionada: " . $fecha_inicio : "Sin filtro de fecha";

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 25px; }
h1, h3 { text-align: center; color: #333; margin: 5px 0; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #777; padding: 6px; text-align: center; }
th { background-color: #f08438; color: white; }
footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #777; }
</style>
</head>
<body>
<h1>OfertApp</h1>
<h3>Informe de Productos Más Consultados</h3>
<p style="text-align:center;">' . htmlspecialchars($rango) . '</p>
<p style="text-align:right;">Generado el: ' . $fecha_hoy . '</p>
<table>
<thead>
<tr>
<th>#</th>
<th>Producto</th>
<th>Tienda</th>
<th>Consultas</th>
<th>Última Fecha</th>
</tr>
</thead>
<tbody>';

if (empty($productos)) {
  $html .= '<tr><td colspan="5">No hay registros para la fecha seleccionada</td></tr>';
} else {
  foreach ($productos as $i => $p) {
    $html .= '<tr>
            <td>' . ($i + 1) . '</td>
            <td>' . htmlspecialchars($p['nombre_producto']) . '</td>
            <td>' . htmlspecialchars($p['id_tienda']) . '</td>
            <td>' . htmlspecialchars($p['total_consultas']) . '</td>
            <td>' . htmlspecialchars($p['ultima_fecha']) . '</td>
        </tr>';
  }
}

$html .= '
</tbody>
</table>
<footer>Generado automáticamente por OfertApp — © ' . date("Y") . '</footer>
</body>
</html>';

// ===== GENERAR PDF =====
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ===== SALIDA MANUAL =====
$pdfOutput = $dompdf->output();

// Limpieza total y envío directo
while (ob_get_level()) {
  ob_end_clean();
}
header_remove();
header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdfOutput));
header('Content-Disposition: inline; filename="informe_productos.pdf"');

echo $pdfOutput;
exit;
