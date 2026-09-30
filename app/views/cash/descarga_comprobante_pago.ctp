<?php
    /**
    * Archivo PDF que genera el comprobante de Pago de Nómina/Proveedores
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
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Monto:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,$estado['PagoCash']['monto_total']);

$pdf->ln();
$pdf->ln();
$pdf->ln();
$pdf->setFont('Arial','B',14);
switch($estado['tipo']){
	case 'n':
		$pdf->cell(0,10,"PAGO NÓMINA",'',0,'C');
		break;
	case 'p':
		$pdf->cell(0,10,"PAGO PROVEEDOR",'',0,'C');
		break;
}

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Usuario Registro","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,desencriptar($usuario['nombre']),"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Cuenta Débito","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,enmascara($estado['PagoCash']['cuentaDebitar']),"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Fecha Valor","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$estado['PagoCash']['fecha_valor']['day'].'/'.$estado['PagoCash']['fecha_valor']['month'].'/'.$estado['PagoCash']['fecha_valor']['year'],"LTRB",0,'L',true);


$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Descripción","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$estado['PagoCash']['descripcion'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Número Lote","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$estado['PagoCash']['lote'],"LTRB",0,'L',true);


$pdf->ln();
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');


$pdf->AddPage('L');
$pdf->SetFont('Arial','',8);

$pdf->cell(20,5,"Beneficiarios:",0,0,'C');
$pdf->ln();
$pdf->ln();

$pdf->line(5,$pdf->getY(),270,$pdf->getY());

$height = 8;
$width = array(
	'a' => 40,
	'b' => 65,
	'c' => 60,
	'd' => 65,
	'e' => 30,
);

$pdf->cell($width['a'],$height,'Identificación Beneficiario',0,0,'C');
$pdf->cell($width['b'],$height,'Nombre Beneficiario',0,0,'C');
$pdf->cell($width['c'],$height,'Número Cuenta Beneficiario',0,0,'C');
$pdf->cell($width['d'],$height,'Banco Beneficiario',0,0,'C');
$pdf->cell($width['e'],$height,'Monto Bs.',0,0,'C');
$pdf->ln();

$pdf->line(5,$pdf->getY(),270,$pdf->getY());

foreach ($estado['Beneficiarios'] as $x){	
	
	$b = desencriptarCampos($x, array('nombre','cedula','numero_cuenta','email','celular'));
		
	$pdf->cell($width['a'],$height,$b['tipo_cedula'].$b['cedula'],0,0,'L');

	$pdf->cell($width['b'],$height,$b['nombre'],0,0,'L');

	$pdf->cell($width['c'],$height,$b['numero_cuenta'],0,0,'C');

	$pdf->cell($width['d'],$height,trim($bancos[substr($b['numero_cuenta'],0,4)]),0,0,'L');

	$pdf->cell($width['e'],$height,formatea_monto_fromWs($x['monto']),0,0,'R');

	$pdf->ln();
}


$pdf->output(desencriptar($usuario['cedula']).'-'.date('Ymdhis').'.pdf','D');

exit;

?>