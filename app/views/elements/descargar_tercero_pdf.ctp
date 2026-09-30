<?php
/**
 * Generador de comprobantes de pagos y transferencias en PDF 
**/

$moneda = '$';

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

/*
$pdf->setX(140);
$pdf->setFont('Arial','B',12);
$pdf->cell(25,10,"Referencia:");
$pdf->setFont('Arial','',12);
$pdf->cell(35,10,$data['referencia']);
$pdf->ln();
*/

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
$pdf->cell(35,10, $data['mtoRecarga']);

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
$pdf->cell(100,10,$data['descripcion'],"LTRB",0,'L',true);





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
$pdf->cell(100,10,enmascara($data['ctaDebito']),"LTRB",0,'L',true);
$pdf->ln();


$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Nombre o Razón Social","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,  desencriptar($data['nombreT']),"LTRB",0,'L',true);
$pdf->ln();
$pdf->setX(30);
$pdf->setFont('Arial','',12);
$pdf->cell(40,10,"BENEFICIARIO:");
$pdf->ln();

$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Número Tarjeta","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,enmascara($data['nroTarjetaB']),"LTRB",0,'L',true);  
$pdf->ln();


$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Cédula o RIF","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$data['cedulaB'],"LTRB",0,'L',true);
$pdf->ln();


    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(50,10,"Nombre Beneficiario","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    $pdf->cell(100,10,$data['nombreB'],"LTRB",0,'L',true);
    $pdf->ln();



$pdf->setX(30);
$pdf->setFont('Arial','',12);
$pdf->cell(40,10,"DETALLE PAGO:");
$pdf->ln();

$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Descripción","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$data['descripcionPago'],"LTRB",0,'L',true);
$pdf->ln();





$pdf->setX(30);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(255,255,255);
$pdf->setFillColor(0,51,102);
$pdf->cell(50,10,"Estatus","LTRB",0,'L',true);
$pdf->setFont('Arial','',10);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(244,244,244);
$pdf->cell(100,10,$data['estatus'],"LTRB",0,'L',true);
$pdf->ln();
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');





//$tamCedula = len($data['cedulaB']);

//$cedula = substr($data['cedulaB'], 2,$tamCedula-2);

//$pdf->output('TPC'.'.pdf','D');
$cedula = trim($data['cedulaB']);

$pdf->output('TPC'. $cedula.'.pdf','D');
exit;
		
?>