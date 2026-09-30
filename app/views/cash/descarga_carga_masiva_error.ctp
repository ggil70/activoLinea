<?php
    /**
     *  Archivo PDF que muestra la lista de errores encontrados en la carga masiva de beneficiarios Nómina/Proveedores
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
$pdf->setFont('Arial','B',12);
$pdf->setX(18);
$pdf->multicell(180,5,"Listado de Errores del Archivo ".$estado['archivo'],'','C');

$alto = 10;
$ancho = array(
	15,
	28,
	39,
	115
);

$x = $pdf->getX();
$y = $pdf->getY();
$pdf->setFont('Arial','B',8);
$pdf->cell($ancho[0],$alto,'Registro',"LTRB",0,"C");
$pdf->cell($ancho[1],$alto,'Identificación',"LTRB",0,"C");
$pdf->cell($ancho[2],$alto,'Nombre',"LTRB",0,"C");
$pdf->cell($ancho[3],$alto,'Error',"LTRB",0,"C");
$pdf->ln();

$pdf->setFont('Arial','',7);
		
foreach($estado['errores'] as $i){
	$pdf->cell($ancho[0],$alto,$i['registro'],'LTBR',0);
	$y = $pdf->getY();
	
	$pdf->cell($ancho[1],$alto,$i['identificacion'],'LTBR',0);
	
	$x = $pdf->getX();
	$pdf->cell($ancho[2],$alto,"",'LTBR',0);
	$nx = $pdf->getX();
	$pdf->setXY($x,$y);
	$pdf->multicell($ancho[2],($alto/2),$i['nombre'],0,'L');
	$pdf->setXY($nx,$y);
	
	$x = $pdf->getX();
	$pdf->cell($ancho[3],$alto,"",'LTBR',0);
	$nx = $pdf->getX();
	$pdf->setXY($x,$y);
	$pdf->multicell($ancho[3],($alto/2),$i['error'],0,'L');
	$pdf->setXY($nx,$y);
	
	$pdf->ln($alto);
}

$pdf->ln();
$pdf->setFont('Arial','B',10);
$pdf->cell(0,10,"BANCO ACTIVO, Se mueve Contigo !!!",'',0,'C');

$pdf->output(desencriptar($usuario['cedula']).'-'.date('ymdhis').'.pdf','D');

exit;

?>