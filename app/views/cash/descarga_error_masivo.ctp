<?php
    /**
     *   Archivo PDF que muestra la lista de errores encontrados en la carga masiva de beneficiarios de Pago de Nómina/Proveedores
     */


App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,70);

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

$pdf->setX(18);
$pdf->multicell(180,5,"Listado de Errores del Archivo ".$estado['archivo']['name'],'','C');
$pdf->ln();

$pdf->setFont('Arial','B',7);
$pdf->cell(20,10,'Registro',"LTRB",0,"C");
$pdf->cell(20,10,'Identificación',"LTRB",0,"C");
$pdf->cell(156,10,'Error',"LTRB",0,"C");
$pdf->ln();
$pdf->setFont('Arial','',7);

foreach($estado['errores'] as $i){
	$pdf->cell(20,10,$i['registro'],"LTRB");
	$pdf->cell(20,10,$i['identificacion'],"LTRB");
	$x=$pdf->getX();
	$y=$pdf->getY();
	$pdf->cell(156,10,'',"LTRB");
	$x2=$pdf->getX();
	$y2=$pdf->getY();
	$pdf->setXY($x,$y);
	$pdf->SetLeftMargin($x);
	$pdf->write(5,$i['error']);
	$pdf->SetLeftMargin(10);
	$pdf->setXY($x2,$y2);
	$pdf->ln(10);
}

$pdf->ln();
$pdf->setFont('Arial','',10);
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');

$pdf->output(desencriptar($usuario['cedula']).'-'.date('ymdhis').'.pdf','D');

exit;

?>