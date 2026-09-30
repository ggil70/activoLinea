<?php

/**
 * Generador de condiciones mesa de cambio
 * */
 
App::Import('Vendor', 'FPDF', array('file' => 'fpdf16/fpdf.php'));

$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();

$pdf->setAutoPageBreak(true, 10);
$pdf->setMargins(20, 10, 20);

$usuario = $this->Session->read('usuarios.datos');


$pdf->AddPage();

$pdf->image(ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS . 'img' . DS . 'logo.jpg', 10, 10, 70);

$pdf->setTextColor(0, 0, 0);
$pdf->setXY(20, 50);
$pdf->setFont('Arial', 'B', 12);
$pdf->ln();
$pdf->cell(0, 10, "DECLARACIÓN CLIENTE NATURAL MESA DE CAMBIO VENTA", '', 0, 'C');

$pdf->setFont('Arial', '', 12);
$pdf->ln();
$pdf->ln();

$pdf->MultiCell(0, 6, _m('mesa.venta.natural'));
$pdf->ln();

$pdf->output('mesaCambioVenta'.date('Ymdhis').'.pdf', 'D');
exit;
?>

