<?php
/**
 * Generador de comprobantes avance de efectivo
**/
App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');
$pdf->AliasNbPages();

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);

$usuario = $this->Session->read('usuarios.datos');

foreach($data_post['operaciones'] as $op)
{
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
    $pdf->cell(35,10,$op['t_referencia']);
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
    $pdf->cell(35,10, number_format($op['t_monto_sol'],2,',','.'));

    $pdf->ln();
    $pdf->setFont('Arial','B',14);
    $pdf->cell(0,10,"NOTA DE CRÉDITO",'',0,'C');
    $pdf->ln();
    
    $pdf->setX(30);
    $pdf->setFont('Arial','',12);
    $pdf->cell(40,10,"Concepto:");
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(70,10,"Descripción:","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    
    $pdf->cell(80,10,"0125 - AVANCE DE EFECTIVO ","LTRB",0,'L',true);
    $pdf->ln();
    
    $pdf->setX(30);
    $pdf->setFont('Arial','',12);
    $pdf->cell(40,10,"Origen:");
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(70,10,"Tarjeta de Crédito / Avance de Efectivo:","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    $pdf->cell(80,10,$op['t_msk'],"LTRB",0,'L',true);
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(70,10,"Nombre del tarjetahabiente:","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    $pdf->cell(80,10,$data_confirmacion['nombre_cliente'],"LTRB",0,'L',true);
    $pdf->ln();
    
    $pdf->setX(30);
    $pdf->setFont('Arial','',12);
    $pdf->cell(40,10,"Cuenta de Abono:");
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(70,10,"Cuenta Abonada:","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    $pdf->cell(80,10,$data_confirmacion['cuenta_abono']['msk'],"LTRB",0,'L',true);
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(70,10,"Nombre del beneficiario: ","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    $pdf->cell(80,10,$data_confirmacion['nombre_cliente'],"LTRB",0,'L',true);
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(70,10,"Cédula del beneficiario: ","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    $pdf->cell(80,10,  $usuario['tipo_cedula'].'-'.desencriptar($usuario['cedula']),"LTRB",0,'L',true);
    $pdf->ln();
    
    $pdf->setX(30);
    $pdf->setFont('Arial','',12);
    $pdf->cell(40,10,"Detalle del Abono:");
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(70,10,"Monto Abonado Avance Efectivo:","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    $pdf->cell(80,10,number_format($op['t_monto_sol'],2,',','.'),"LTRB",0,'L',true);
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(70,10,"Comisión Flat: ","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    $pdf->cell(80,10,number_format($op['t_comision'],2,',','.'),"LTRB",0,'L',true);
    $pdf->ln();
    $pdf->setX(30);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(255,255,255);
    $pdf->setFillColor(0,51,102);
    $pdf->cell(70,10,"Estatus: ","LTRB",0,'L',true);
    $pdf->setFont('Arial','',10);
    $pdf->setTextColor(0,0,0);
    $pdf->setFillColor(244,244,244);
    $pdf->cell(80,10, "Procesado","LTRB",0,'L',true);
    $pdf->ln();
    
    
    
    $pdf->ln();
    $pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');
    $pdf->ln();
    $pdf->cell(0,10,'Pag '.$pdf->PageNo().'/{nb}',0,0,'C');
}
$pdf->output("AvanceEfectivo".substr($data_confirmacion['cuenta_abono']['msk'], strlen($data_confirmacion['cuenta_abono']['msk']) - 4).date('Ymdhis').'.pdf','D');
exit;		
?>

