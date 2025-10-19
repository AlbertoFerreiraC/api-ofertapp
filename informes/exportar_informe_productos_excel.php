<?php
require '../../vendor/autoload.php';
include_once '../db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=informe_productos.xlsx");

$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;

// === CONEXIÓN BD ===
$db = new DB();
$pdo = $db->connect();

// === CONSULTA ===
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

// === CREAR HOJA DE CÁLCULO ===
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Productos más consultados');

// === ENCABEZADOS ===
$sheet->setCellValue('A1', 'N°');
$sheet->setCellValue('B1', 'Producto');
$sheet->setCellValue('C1', 'Tienda');
$sheet->setCellValue('D1', 'Consultas');
$sheet->setCellValue('E1', 'Última Fecha');

// === ESTILOS ENCABEZADO ===
$sheet->getStyle('A1:E1')->getFont()->setBold(true);
$sheet->getStyle('A1:E1')->getFill()
    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFF08438');
$sheet->getStyle('A1:E1')->getFont()->getColor()->setARGB('FFFFFFFF');

// === LLENAR DATOS ===
$row = 2;
foreach ($productos as $i => $p) {
    $sheet->setCellValue("A{$row}", $i + 1);
    $sheet->setCellValue("B{$row}", $p['nombre_producto']);
    $sheet->setCellValue("C{$row}", $p['id_tienda']);
    $sheet->setCellValue("D{$row}", $p['total_consultas']);
    $sheet->setCellValue("E{$row}", $p['ultima_fecha']);
    $row++;
}

// === AJUSTAR ANCHO DE COLUMNAS ===
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// === DESCARGAR ARCHIVO ===
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
