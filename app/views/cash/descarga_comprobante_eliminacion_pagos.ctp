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
switch($pagos['tipo']){
	case 'n':
		$pdf->cell(0,10,"$accion PAGO NÓMINA",'',0,'C');
		break;
	case 'p':
		$pdf->cell(0,10,"$accion PAGO PROVEEDOR",'',0,'C');
		break;
}
foreach($pagos['pagos'] as $pago){
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Tipo Operación","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
switch($pagos['tipo']){
	case 'n':
		$pdf->cell(100,10,"Pago Nómina","LTRB",0,'L',true);
		break;
	case 'p':
		$pdf->cell(100,10,"Pago Proveedores","LTRB",0,'L',true);
		break;
}
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Usuario registro","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,desencriptar($usuario['login']),"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Cuenta Débito","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,enmascara($pago['PagoCash']['cuentaDebitar']),"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Fecha Valor","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$pago['PagoCash']['fecha_valor']['day'].'/'.$pago['PagoCash']['fecha_valor']['month'].'/'.$pago['PagoCash']['fecha_valor']['year'] ,"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Descripción","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$pago['PagoCash']['descripcion'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Número de Lote","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$pago['PagoCash']['lote'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Monto","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,formatea_monto_fromWs($pago['PagoCash']['monto_total']),"LTRB",0,'L',true);
$pdf->ln();

}

$pdf->ln();
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');

$pdf->output(desencriptar($usuario['cedula']).'-'.date('ymdhis').'.pdf','D');

exit;

?>