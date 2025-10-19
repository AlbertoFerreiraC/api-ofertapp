<?php
require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Limpia cualquier salida previa
if (ob_get_length()) ob_end_clean();
header('Content-Type: application/pdf');

// Configuración de Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$html = "<h1>✅ Prueba exitosa</h1><p>Si ves este texto, Dompdf funciona correctamente.</p>";
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("prueba.pdf", ["Attachment" => false]);
exit;
