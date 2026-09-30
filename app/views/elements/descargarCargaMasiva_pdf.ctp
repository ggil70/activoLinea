<?php
/**
 * Generador de comprobante de carga masiva de afiliaciones en cash
 * management
**/

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
$pdf->setFont('Arial','B',14);
// switch($estado['tipo']){
	// case 'n':
		// $pdf->cell(0,10,"PAGO NOMINA",'',0,'C');
		// break;
	// case 'p':
		// $pdf->cell(0,10,"PAGO PROVEEDOR",'',0,'C');
		// break;
// }

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Tipo Operación:","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,"Afiliación Nomina","LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Cantidad de Registros:","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,count($cargaMasiva),"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Fecha Operación:","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,date('d/m/Y'),"LTRB",0,'L',true);



$pdf->ln();

$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');


$pdf->AddPage('L');
$pdf->SetFont('Arial','',8);

$pdf->cell(20,5,"Listado de Afiliaciones:",0,0,'C');
$pdf->ln();
$pdf->ln();

$pdf->line(5,$pdf->getY(),270,$pdf->getY());

$height = 4;

$width = array(
	'a' => 40,
	'b' => 40,
	'c' => 60,
	'd' => 65,
	'e' => 30,
);

$pdf->cell($width['a'],$height,'Nombre',0,0,'C');
$pdf->cell($width['b'],$height,'Identificación',0,0,'C');
$pdf->cell($width['c'],$height,'Número Cuenta',0,0,'C');
$pdf->cell($width['d'],$height,'Banco',0,0,'C');
$pdf->cell($width['e'],$height,'E-mail',0,0,'C');
$pdf->cell($width['e'],$height,'Número teléfono',0,0,'C');
$pdf->ln();

$pdf->cell($width['a'],$height,'Beneficiario',0,0,'C');
$pdf->cell($width['b'],$height,'Beneficiario',0,0,'C');
$pdf->cell($width['c'],$height,'Beneficiario',0,0,'C');
$pdf->cell($width['d'],$height,'Beneficiario',0,0,'C');
$pdf->cell($width['e'],$height,'Beneficiario',0,0,'C');
$pdf->cell($width['e'],$height,'celular',0,0,'C');
$pdf->ln();

$pdf->line(5,$pdf->getY(),270,$pdf->getY());
$height = 8;
foreach ($cargaMasiva as $x){	
	
	$b = desencriptarCampos($x['AfiliacionCash'], array('nombre','cedula','numero_cuenta','email','celular'));
//	if(array_key_exists($b['id'], $estado['Beneficiarios'])){
		
		$pdf->cell($width['a'],$height,$b['nombre'],0,0,'L');
		
		$pdf->cell($width['b'],$height,$b['tipo_cedula'].$b['cedula'],0,0,'L');

		$pdf->cell($width['c'],$height,$b['numero_cuenta'],0,0,'C');

		$pdf->cell($width['d'],$height,trim($bancos[substr($b['numero_cuenta'],0,4)]),0,0,'L');

		$pdf->cell($width['e'],$height,$b['email'],0,0,'C');
		
		$pdf->cell($width['e'],$height,$b['celular'],0,0,'C');

		$pdf->ln();
	//}
}


$pdf->output(desencriptar($usuario['cedula']).'-'.date('Ymdhis').'.pdf','D');

exit;

?>