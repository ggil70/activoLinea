<?php
    /**
     *   Archivo PDF que genera el comprobante de afiliación en la carga masiva de beneficiarios Nómina/Proveedores
     */

App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,70);
/*
$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Referencia:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,$this->data['AfiliacionCash']['id']);*/
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
$pdf->setFont('Arial','B',14);
switch($this->data['AfiliacionCash']['tipo']){
	case 'n':
		$pdf->cell(0,10,"$accion BENEFICIARIO NOMINA",'',0,'C');
		break;
	case 'p':
		$pdf->cell(0,10,"$accion BENEFICIARIO PROVEEDOR",'',0,'C');
		break;
}

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Nombre Beneficiario","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$this->data['AfiliacionCash']['nombre'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Identificación Beneficiario","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$this->data['AfiliacionCash']['tipo_cedula'].$this->data['AfiliacionCash']['cedula'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Número Cuenta Beneficiario","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,enmascara($this->data['AfiliacionCash']['numero_cuenta']),"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Banco Beneficiario","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$this->data['AfiliacionCash']['banco'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"E-mail Beneficiario","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$this->data['AfiliacionCash']['email'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Número Teléfono Celular","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$this->data['AfiliacionCash']['celular'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');

$pdf->output(desencriptar($usuario['cedula']).'-'.date('ymdhis').'.pdf','D');

exit;

?>