<?php
    /**
     *  Archivo PDF que genera el comprobante de carga masiva de Pago de Nómina/Proveedores
     */


App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo_express.jpg',10,10,70);

$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Fecha:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,date('d/m/Y'));
$pdf->ln();

$pdf->ln();
$pdf->ln();
$pdf->ln();
$pdf->setFont('Arial','B',12);

$pdf->cell(0,10,"Listado de Pagos No Procesados",'',0,'C');


$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(35,10,"Cedula Beneficiario","LTRB",0,'L',true);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(35,10,"Nombre Beneficiario","LTRB",0,'L',true);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,"Motivo","LTRB",0,'L',true);

foreach ($dataRechazoAux as $registro) {
	$pdf->ln();
	$pdf->setX(30);
	$pdf->setFont('Arial','',10);
	$pdf->setTextColor(0,0,0);
	$pdf->setFillColor(255,255,255);
	$pdf->cell(35,10,$registro['NexpLogsError']['cedula'],"LTRB",0,'L',true);
	$pdf->setFont('Arial','',10);
	$pdf->setTextColor(0,0,0);
	$pdf->setFillColor(255,255,255);
	$pdf->cell(35,10,$registro['NexpLogsError']['nombre'],"LTRB",0,'L',true);

	$pdf->setFont('Arial','',10);
	$pdf->setTextColor(0,0,0);
	$pdf->setFillColor(255,255,255);
	$pdf->cell(100,10,$registro['NexpLogsError']['error_pago'],"LTRB",0,'L',true);
}


$pdf->ln();
$pdf->ln();
$pdf->ln();
$pdf->cell(0,10,"Avanzamos juntos hacia el futuro",'',0,'C');


$pdf->output('Listado_Pago_No_Procesado_'.date('Ymdhis').'.pdf','D');

exit;

?>