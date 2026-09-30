<?php
/**
 * Descarga en formato PDF de tarjeta de coordenadas
**/

App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

$pdf = new FPDF('P','mm','Letter');

$pdf->setAutoPageBreak(true,5);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,70);
$pdf->ln(30);
$pdf->setTextColor(0,0,0);
$pdf->setFont('Arial','B',14);
$pdf->cell(0,10,"Tarjeta de Coordenadas",0,0,'C');
$pdf->ln();

$filas = Configure::read('filas.tdc');

$colores = array(array(252,217,187),array(207,249,255));

$pdf->setDrawColor(63,148,143);

for($row=0;$row<=6;$row++){
	$pdf->ln();
	$pdf->setY(.15+$pdf->getY());
	
	for($col=0;$col<=9;$col++){
		if($row == 0){
			if($col==0){
				$pdf->cell(10,10,'','RB');
			} else {
				$pdf->setTextColor(255,255,255);
				$pdf->setFillColor(0,72,128);
				$pdf->cell(10,10,$col,'TRB',0,'C',true);
			}
		} else {
			$color_fill = $colores[$row % count($colores)];
			$pdf->setFillColor($color_fill[0],$color_fill[1],$color_fill[2]);
			$pdf->setTextColor(66,128,168);
	
			if($col==0){
				$pdf->cell(10,10,$filas[$row],'LRB',0,'C',true);
			} else {
				$pdf->cell(10,10,$tdc[$row-1][$col-1],'RB',0,'C',true);
			}
		}
		
		$pdf->setX(.15+$pdf->getX());
	}
}

$pdf->ln(20);
$pdf->setTextColor(0,0,0);
$pdf->setFillColor(255,255,255);

$pdf->cell(0,10,"Serial: ".$serial);

$pdf->output($identificacion.'-'.date('ymdhis').'.pdf','D');

exit;
		
?>