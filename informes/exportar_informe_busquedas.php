<?php
require '../../vendor/autoload.php';
include_once '../db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ===== LIMPIAR CUALQUIER SALIDA PREVIA =====
while (ob_get_level()) {
    ob_end_clean();
}
header_remove();
header('Content-Type: application/pdf; charset=utf-8');

// ===== LEER PARÁMETROS =====
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;

// ===== CONEXIÓN BD =====
$db = new DB();
$pdo = $db->connect();

// ===== CONSULTA: productos con más reseñas (como proxy de búsquedas) =====
$query = "
    SELECT 
        p.idProducto,
        p.titulo AS nombre_producto,
        c.descripcion AS categoria,
        e.nombre AS tienda,
        COUNT(r.idresena) AS total_interacciones,
        MAX(r.fecha_agregado) AS ultima_interaccion
    FROM resena r
    INNER JOIN Producto p ON r.Producto_idProducto = p.idProducto
    INNER JOIN Empresa e ON p.Empresa_idEmpresa = e.idEmpresa
    INNER JOIN Categoria c ON p.Categoria_idCategoria = c.idCategoria
    WHERE 1=1
";

$params = [];
if ($fecha_inicio) {
    $query .= " AND DATE(r.fecha_agregado) = :fecha_inicio";
    $params[':fecha_inicio'] = $fecha_inicio;
}

$query .= "
    GROUP BY p.idProducto, p.titulo, c.descripcion, e.nombre
    ORDER BY total_interacciones DESC;
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== ARMAR HTML =====
$fecha_hoy = date("d/m/Y H:i");
$rango = $fecha_inicio ? "Fecha seleccionada: " . $fecha_inicio : "Todas las fechas";

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
<h3>Informe: Productos Más Buscados</h3>
<p style="text-align:center;">' . htmlspecialchars($rango) . '</p>
<p style="text-align:right;">Generado el: ' . $fecha_hoy . '</p>
<table>
<thead>
<tr>
<th>#</th>
<th>Producto</th>
<th>Categoría</th>
<th>Tienda</th>
<th>Interacciones</th>
<th>Última Fecha</th>
</tr>
</thead>
<tbody>';

if (empty($productos)) {
    $html .= '<tr><td colspan="6">No hay registros disponibles</td></tr>';
} else {
    foreach ($productos as $i => $p) {
        $html .= '<tr>
            <td>' . ($i + 1) . '</td>
            <td>' . htmlspecialchars($p['nombre_producto']) . '</td>
            <td>' . htmlspecialchars($p['categoria']) . '</td>
            <td>' . htmlspecialchars($p['tienda']) . '</td>
            <td>' . htmlspecialchars($p['total_interacciones']) . '</td>
            <td>' . htmlspecialchars($p['ultima_interaccion']) . '</td>
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

// ===== SALIDA DIRECTA =====
$pdfOutput = $dompdf->output();
header('Content-Disposition: inline; filename="informe_productos_mas_buscados.pdf"');
header('Content-Length: ' . strlen($pdfOutput));
echo $pdfOutput;
exit;
