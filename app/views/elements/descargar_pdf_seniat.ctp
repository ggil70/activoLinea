<?php

/**
 * Generador de comprobantes de pagos y transferencias en PDF 
 * */
App::Import('Vendor', 'FPDF', array('file' => 'fpdf16/fpdf.php'));

$pdf = new FPDF('P', 'mm', 'Letter');

$pdf->setAutoPageBreak(true, 5);
$pdf->setMargins(10, 10, 10);
$pdf->AddPage();

$pdf->image(ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS . 'img' . DS . 'logo.jpg', 10, 10, 70);

$pdf->setTextColor(0, 0, 0);
$pdf->setXY(165, 10);
$pdf->setFont('Arial', 'B', 14);
$pdf->cell(20, 10, "RECIBO");

$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial', 'B', 12);
$pdf->cell(25, 10, "Referencia:");
$pdf->setFont('Arial', '', 12);
$pdf->cell(35, 10, $data['referencia']);

$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial', 'B', 12);
$pdf->cell(25, 10, "Fecha:");
$pdf->setFont('Arial', '', 12);
$pdf->cell(35, 10, date('d/m/Y'));

$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial', 'B', 12);
$pdf->cell(25, 10, "Hora:");
$pdf->setFont('Arial', '', 12);
$pdf->cell(35, 10, date('h:i:s A'));

$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial', 'B', 12);
$pdf->cell(25, 10, "Monto Bs.:");
$pdf->setFont('Arial', '', 12);
$pdf->cell(35, 10, $data['monto']);

$pdf->ln();
$pdf->setFont('Arial', 'B', 14);
$pdf->cell(0, 10, "NOTA DE DEBITO", '', 0, 'C');

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 12);
$pdf->cell(40, 10, "CONCEPTO:");

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(45, 10, "Descripción", "LTRB", 0, 'L', true);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
$pdf->cell(115, 10, 'PAGO SENIAT INTERNET', "LTRB", 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 12);
$pdf->cell(40, 10, "ORIGEN:");

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(45, 10, "Cuenta de Débito", "LTRB", 0, 'L', true);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
$pdf->cell(115, 10, enmascara($data['cuentaDebitar']), "LTRB", 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(45, 10, "Nombre o Razón Social", "LTRB", 0, 'L', true);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
$usuario = $session->read('usuarios.datos');
$datos_basicos = $this->Session->read('usuarios.nibpf3010');
if ($usuario['tipo_rif']) {
    $pdf->cell(115, 10, $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'], "LTRB", 0, 'L', true);
} else {
    $pdf->cell(115, 10, desencriptar($usuario['nombre']), "LTRB", 0, 'L', true);
}

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 12);
$pdf->cell(40, 10, "BENEFICIARIO:");

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(45, 10, "RIF Beneficiario", "LTRB", 0, 'L', true);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
$pdf->cell(115, 10, $data['rif'], "LTRB", 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 12);
$pdf->cell(40, 10, "DETALLE PAGO:");

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(45, 10, "Descripción", "LTRB", 0, 'L', true);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
$pdf->cell(115, 10, $data['concepto'], "LTRB", 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(45, 10, "Impuesto", "LTRB", 0, 'L', true);
if (strlen(trim($data['impuesto'])) <= 50) {
    $pdf->setFont('Arial', '', 10);
} else {
    $pdf->setFont('Arial', '', 8);
}
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
$pdf->cell(115, 10, trim($data['impuesto']), "LTRB", 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(45, 10, "Forma", "LTRB", 0, 'L', true);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
$pdf->cell(115, 10, $data['forma'], "LTRB", 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(45, 10, "N° Planilla", "LTRB", 0, 'L', true);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
$pdf->cell(115, 10, $data['planilla'], "LTRB", 0, 'L', true);

if ($data['aduana'] != "") {
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial', '', 10);
    $pdf->setTextColor(255, 255, 255);
    $pdf->setFillColor(0, 51, 102);
    $pdf->cell(45, 10, "Aduana", "LTRB", 0, 'L', true);
    if (strlen(trim($data['aduana'])) <= 50) {
        $pdf->setFont('Arial', '', 10);
    } else {
        $pdf->setFont('Arial', '', 8);
    }
    $pdf->setTextColor(0, 0, 0);
    $pdf->setFillColor(244, 244, 244);
    $pdf->cell(115, 10, $data['aduana'], "LTRB", 0, 'L', true);

    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial', '', 10);
    $pdf->setTextColor(255, 255, 255);
    $pdf->setFillColor(0, 51, 102);
    $pdf->cell(45, 10, "Expediente", "LTRB", 0, 'L', true);
    $pdf->setFont('Arial', '', 10);
    $pdf->setTextColor(0, 0, 0);
    $pdf->setFillColor(244, 244, 244);
    $pdf->cell(115, 10, $data['expediente'], "LTRB", 0, 'L', true);
}

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(45, 10, "N° Autorización", "LTRB", 0, 'L', true);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
$pdf->cell(115, 10, $data['autorizacion'], "LTRB", 0, 'L', true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(255, 255, 255);
$pdf->setFillColor(0, 51, 102);
$pdf->cell(45, 10, "Estatus", "LTRB", 0, 'L', true);
$pdf->setFont('Arial', '', 10);
$pdf->setTextColor(0, 0, 0);
$pdf->setFillColor(244, 244, 244);
$pdf->cell(115, 10, $data['estatus'], "LTRB", 0, 'L', true);

$pdf->ln();
$pdf->cell(0, 10, "BANCO ACTIVO, Se mueve Contigo !!!", '', 0, 'C');


$pdf->output('PagoSENIAT' . $data['rif'] . date('Ymdhis') . '.pdf', 'D');
exit;
?>