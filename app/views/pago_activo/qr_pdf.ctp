<?php

/**
 * Generador de contrato de pago activo
 * */
 
App::Import('Vendor', 'FPDF', array('file' => 'fpdf18/fpdf.php'));

function getImage($dataURI){
  $img = explode(',',$dataURI,2);
  $pic = 'data://text/plain;base64,'.$img[1];
  $type = 'png'; // get the image type
  if ($type=="png") return array($pic, $type);
  return false;
}

$pdf = new FPDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();

$pdf->setAutoPageBreak(true, 10);
$pdf->setMargins(20, 10, 20);

$usuario = $this->Session->read('usuarios.datos');

$pdf->AddPage();

$pdf->image(ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS . 'img' . DS . 'EscaneaQR.jpg', 0, 5, 220);

$pdf->setTextColor(0, 0, 0);
$pdf->setXY(50, 63);
$pdf->setFont('Arial', 'B', 17);
$pdf->ln();
$pdf->ln();
$pdf->ln();
$pdf->cell(0, 7, "CEDULA/RIF : " . $id, '', 0, 'C');
$pdf->ln();
$pdf->cell(0, 7, "TELEFONO : " . $num_telefono, '', 0, 'C');
$pdf->ln();
$pdf->cell(0, 7, "BANCO : 0171 - BANCO ACTIVO", '', 0, 'C');

$pdf->setFont('Arial', '', 12);
$pdf->ln();
$pdf->ln();

$pic = getImage($datosqr);
if ($pic!==false) $pdf->Image($pic[0], 64,102,80,80, $pic[1]);
$pdf->ln();
$pdf->ln();
$pdf->ln();
$pdf->ln();

$pdf->image(ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS . 'img' . DS . 'suicheQR.png', 20, 245, 70, 40);
$pdf->image(ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS . 'img' . DS . 'activoQR.png', 135, 250, 55, 25);

$pdf->output('CodigoQR_ServicioPM'.date('Ymdhis').'.pdf', 'D');
exit;
?>

