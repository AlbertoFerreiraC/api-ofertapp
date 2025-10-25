<?php
require '../../vendor/autoload.php';
include_once '../db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// ===== CABECERAS PARA DESCARGA =====
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=informe_productos_mas_buscados.xlsx");

// ===== PARÁMETROS =====
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;

// ===== CONEXIÓN BD =====
$db = new DB();
$pdo = $db->connect();

// ===== CONSULTA: productos con más reseñas =====
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

// ===== CREAR DOCUMENTO EXCEL =====
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Productos más buscados');

// ===== ENCABEZADO PRINCIPAL =====
$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', 'OfertApp - Informe de Productos Más Buscados');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFF08438');

// ===== SUBTÍTULOS =====
$fechaHoy = date("d/m/Y H:i");
$rango = $fecha_inicio ? "Fecha seleccionada: " . $fecha_inicio : "Todas las fechas";

$sheet->setCellValue('A3', $rango);
$sheet->setCellValue('F3', "Generado el: " . $fechaHoy);
$sheet->getStyle('A3:F3')->getFont()->setSize(10);

// ===== ENCABEZADOS DE COLUMNA =====
$sheet->setCellValue('A5', 'N°');
$sheet->setCellValue('B5', 'Producto');
$sheet->setCellValue('C5', 'Categoría');
$sheet->setCellValue('D5', 'Tienda');
$sheet->setCellValue('E5', 'Interacciones');
$sheet->setCellValue('F5', 'Última Fecha');

// ===== ESTILO ENCABEZADOS =====
$sheet->getStyle('A5:F5')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
$sheet->getStyle('A5:F5')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFF08438');
$sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// ===== LLENAR DATOS =====
$fila = 6;
if (empty($productos)) {
    $sheet->mergeCells("A{$fila}:F{$fila}");
    $sheet->setCellValue("A{$fila}", "No hay registros disponibles");
    $sheet->getStyle("A{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
} else {
    foreach ($productos as $i => $p) {
        $sheet->setCellValue("A{$fila}", $i + 1);
        $sheet->setCellValue("B{$fila}", $p['nombre_producto']);
        $sheet->setCellValue("C{$fila}", $p['categoria']);
        $sheet->setCellValue("D{$fila}", $p['tienda']);
        $sheet->setCellValue("E{$fila}", $p['total_interacciones']);
        $sheet->setCellValue("F{$fila}", $p['ultima_interaccion']);
        $fila++;
    }
}

// ===== AJUSTAR ANCHOS DE COLUMNA =====
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// ===== PIE DE PÁGINA =====
$sheet->setCellValue("A" . ($fila + 2), "Generado automáticamente por OfertApp — © " . date("Y"));
$sheet->mergeCells("A" . ($fila + 2) . ":F" . ($fila + 2));
$sheet->getStyle("A" . ($fila + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A" . ($fila + 2))->getFont()->setSize(9)->setItalic(true)->getColor()->setARGB('777777');

// ===== GENERAR ARCHIVO =====
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
