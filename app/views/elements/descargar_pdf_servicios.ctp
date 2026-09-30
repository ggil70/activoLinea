<?php
/**
 * Generador PDF de recibos de pago de servicios
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
$pdf->cell(25,10,"Monto Bs.:");
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
$pdf->cell(65,10,"Descripción","LTRB",0,'L',true);
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
$pdf->cell(65,10,"Cuenta de Débito","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,enmascara($data['cuentaDebitar']),"LTRB",0,'L',true);
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(65,10,"Nombre o Razón Social","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10, desencriptar($usuario['nombre']),"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',12);
$pdf->cell(40,10,"SERVICIO:");
$pdf->ln();


switch ($data['operacion']){
	case 'CANTV':
		$titulo_tipo = 'CANTV';
		$tipo_operacion = 'CANTV';
        $mensaje_footer = '';
		break;
	case 'MOVILNET':
		$titulo_tipo = 'MOVILNET';
		$tipo_operacion = 'MOVILNET';
        $mensaje_footer = '';
		break;
    case 'Pago Digitel':
		$titulo_tipo = 'Pago Digitel';
		$tipo_operacion = 'Pago Digitel';
        $mensaje_footer = 'Para más información comuníquese al 121 desde su Digitel o al 04129121121';
		break;
    case 'Recarga Digitel':
		$titulo_tipo = 'Recarga Digitel';
		$tipo_operacion = 'Recarga Digitel';
        $mensaje_footer = 'Para más información comuníquese al 121 desde su Digitel o al 04129121121';
		break;
    case 'Movistar':
		$titulo_tipo = 'Movistar';
		$tipo_operacion = 'Movistar';
        $mensaje_footer = '';
		break;
	case 'Recarga Inter':
		$titulo_tipo = 'Inter';
		$tipo_operacion = 'Inter';
        $mensaje_footer = '';
		break;    
	default:
		$titulo_tipo = 'Cantv.net';
		$tipo_operacion = 'Cantv.net';
        $mensaje_footer = '';
		break;
}

$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(65,10,"Nombre Empresa Servicio","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$titulo_tipo,"LTRB",0,'L',true);

$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(65,10,"Número de Servicio o Cuenta Contrato","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$data['numeroContrato'],"LTRB",0,'L',true);

if ($data['operacion'] != 'MOVILNET'){
	$pdf->ln();
	$pdf->setX(30);
	$pdf->setFont('Arial','',10);
	$pdf->setTextColor(255,255,255);
	$pdf->setFillColor(0,51,102);
	$pdf->cell(65,10,"Nombre del Beneficiario","LTRB",0,'L',true);
	$pdf->setFont('Arial','',10);
	$pdf->setTextColor(0,0,0);
	$pdf->setFillColor(244,244,244);
	$pdf->cell(100,10,$data['nombreBeneficiario'],"LTRB",0,'L',true);
}
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',12);
$pdf->cell(40,10,"DETALLE PAGO:");
$pdf->ln();

$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(65,10,"Número Confirmación Operadora","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$data['REFERENCIA_OPERADOR'],"LTRB",0,'L',true);
$pdf->ln();

$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(65,10,"Estatus","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$data['ESTATUS'],"LTRB",0,'L',true);

$pdf->ln();
$pdf->cell(0,10,$mensaje_footer,'',0,'C');
$pdf->ln();
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');

$pdf->output($tipo_operacion. '-'. substr($data['NRO_BENEFICIARIO'], strlen($data['NRO_BENEFICIARIO']) - 4).'-'.date('Ymdhis').'.pdf','D');
exit;
		
?>