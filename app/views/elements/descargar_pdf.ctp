<?php
/**
 * Generador de comprobantes de pagos y transferencias en PDF 
**/

if(isset($data['MONEDA']) && $data['MONEDA']=='USD'){
    $moneda = '$';
}elseif(isset($data['MONEDA']) && $data['MONEDA']=='EUR'){
    $moneda = '?';
}else{
    $moneda = 'Bs.';
}

App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,70);

$pdf->setTextColor(0,0,0);
$pdf->setXY(165,10);
$pdf->setFont('Arial','B',14);
$pdf->cell(20,10,"RECIBO");
$pdf->ln();
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Referencia:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,$data['REFERENCIA']);
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
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Monto ".$moneda.":");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10, $data['monto']);

$pdf->ln();
$pdf->setFont('Arial','B',14);
$pdf->cell(0,10,"NOTA DE DEBITO",'',0,'C');
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',12);
$pdf->cell(40,10,"CONCEPTO:");
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Descripción","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$data['CONCEPTO'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',12);
$pdf->cell(40,10,"ORIGEN:");
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Cuenta de Débito","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,enmascara($data['cuentaDebitar']),"LTRB",0,'L',true);
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Nombre o Razón Social","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$usuario = $session->read('usuarios.datos');
$datos_basicos = $this->Session->read('usuarios.nibpf3010');

if($usuario['tipo_rif']){
	$pdf->cell(100,10, $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'],"LTRB",0,'L',true);
} else {
	$pdf->cell(100,10, desencriptar($usuario['nombre']),"LTRB",0,'L',true);
}

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',12);
$pdf->cell(40,10,"BENEFICIARIO:");
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Nombre Banco","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$data['banco'],"LTRB",0,'L',true);
$pdf->ln();

switch ($data['TIPO']){
	case 'tdc':
		$titulo_tipo = 'Número Tarjeta';
		$tipo_operacion = 'TC';
		break;
	case 'pago_activo':
		$titulo_tipo = 'Número Celular';
		$tipo_operacion = 'CB';
		break;
	default:
		$titulo_tipo = 'Número Cuenta';
		$tipo_operacion = 'CB';
		break;
}

$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,$titulo_tipo,"LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
if($titulo_tipo=='Número Celular'){
  $pdf->cell(100,10,$data['NRO_BENEFICIARIO'],"LTRB",0,'L',true);  
}else{
    $pdf->cell(100,10,enmascara($data['NRO_BENEFICIARIO']),"LTRB",0,'L',true);
}
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Cédula o RIF","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$data['nroCedulaRif'],"LTRB",0,'L',true);
$pdf->ln();
if($titulo_tipo!='Número Celular'){    
    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(50,10,"Nombre Beneficiario","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    $pdf->cell(100,10,$data['nombreBeneficiario'],"LTRB",0,'L',true);
    $pdf->ln();
}


$pdf->setX(30);
$pdf->setFont('Arial','',12);
$pdf->cell(40,10,"DETALLE PAGO:");
$pdf->ln();

if($data['descripcion'] != ''){

	$pdf->setX(30);
	$pdf->setFont('Arial','',10);
	$pdf->setTextColor(255,255,255);
	$pdf->setFillColor(0,51,102);
	$pdf->cell(50,10,"Descripción","LTRB",0,'L',true);
	$pdf->setFont('Arial','',10);
	$pdf->setTextColor(0,0,0);
	$pdf->setFillColor(244,244,244);
	$pdf->cell(100,10,$data['descripcion'],"LTRB",0,'L',true);

	$pdf->ln();

}

$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Estatus","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$data['ESTATUS'],"LTRB",0,'L',true);
$pdf->ln();
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');



$pdf->output($tipo_operacion.  substr($data['NRO_BENEFICIARIO'], strlen($data['NRO_BENEFICIARIO']) - 4).date('Ymdhis').'.pdf','D');
exit;
		
?>