<?php
/**
 * Generador de comprobante de cambio de estatus de usuario jurídico
**/

App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,70);

$pdf->setTextColor(0,0,0);
$pdf->setXY(165,10);
$pdf->setFont('Arial','B',14);
$pdf->cell(20,10,"");
$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Fecha:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,date('d/m/Y'));
$pdf->ln();


$pdf->ln();
$pdf->setFont('Arial','B',14);
$pdf->cell(0,10,"Cambio de Estatus",'',0,'C');

$pdf->ln();
$pdf->setX(30);


$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Identificación del Usuario (Login)","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$data['Usuario']['nomUsuario'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"RIF de la Empresa","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$usuario['tipo_rif'].'-'.desencriptar($usuario['rif']),"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Cédula de Identidad","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10, $data['Usuario']['tipoCedulaRif'].'-'.$data['Usuario']['nroCedulaRif'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Nombre","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$data['Usuario']['nombre'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Cargo","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$data['Usuario']['cargo'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Email","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$data['Usuario']['emailPri'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Celular","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$data['Usuario']['codigoCelular'].$data['Usuario']['nroCelular'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Perfil","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$perfiles[$data['Usuario']['perfil']],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);

$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(60,10,"Estatus Usuario","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(90,10,$estatus[$data['Usuario']['co_estado']],"LTRB",0,'L',true);

$pdf->ln();
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');

$pdf->output('CU'.$usuario['tipo_rif'].desencriptar($usuario['rif']).date('Ymdhis').'.pdf','D');

exit;
		