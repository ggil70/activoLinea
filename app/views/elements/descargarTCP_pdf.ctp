<?php
/**
 * Generador PDF de recibos de pago de servicios
**/
App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo_express.jpg',10,10,70);

$pdf->setTextColor(0,0,0);
$pdf->setXY(165,10);
$pdf->setFont('Arial','B',14);
$pdf->cell(20,10,"SOLICITUD");
$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Fecha:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,date('d/m/Y'));
$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Hora:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,date('h:i:s A'));
$pdf->ln();
$pdf->ln();

$pdf->setX(30);
$pdf->setFont('Arial','',12);




if($tipo == 'F'){
	$pdf->cell(40,10,"TARJETA PREPAGADA CASH");
}else{
	$pdf->cell(40,10,"TARJETA PREPAGADA CASH VIRTUAL");	
}



$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(65,10,"Apellido(s), nombre(s)","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10, desencriptar($usuario['nombre']),"LTRB",0,'L',true);


//Cedula
$cedula = $usuario['tipo_cedula'] . "-" . desencriptar($usuario['cedula']);
$pdf->ln(); 
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(65,10,"Cedula","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10, $cedula,"LTRB",0,'L',true);



$pdf->ln();
$pdf->cell(0,10,$mensaje_footer,'',0,'C');
$pdf->ln();
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');

$pdf->output($tipo_operacion. '-'. substr($data['NRO_BENEFICIARIO'], strlen($data['NRO_BENEFICIARIO']) - 4).'-'.date('Ymdhis').'.pdf','D');
exit;
		
?>