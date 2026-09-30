<?php
    /**
     *  Archivo PDF que genera el estado de cuenta del crédito seleccionado
     */

App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

class EdoCuentaPDF extends FPDF{
	var $usuario;
	var $detalle;
	var $termino = false;
	//var $moneda = ' Bs.'; EN EL EJEMPLO NO APARECE
	var $moneda = '';
	
	function Header()
	{		
		$this->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,70);

		$this->setTextColor(0,0,0);
		$this->setXY(198,40);
		$this->setFont('Arial','B',12);
		$this->cell(72,10,"ESTADO DE CUENTA",0,0,'R');
		
		// Verifica si tiene credito relacionado
		if($this->detalle['e01DEAACCR']){
			$rel = array(
				'tit_nro'	=> 'Crédito Relacionado',
				'nro'		=> $this->detalle['e01DEAACCR'],
				'tit_monto'	=> 'Monto Asociado',
				'monto'		=> formatea_monto_fromWs($this->detalle['e01DEAOAMR']).$this->moneda
			);
		}
		else{
			$rel = array(
				'tit_nro'	=> '',
				'nro'		=> '',
				'tit_monto'	=> '',
				'monto'		=> ''
			);
		}

		$this->ln();
		$this->setFont('Arial','',8);
		$this->cell(75,5,"Nombre del Cliente",0,0,'L');
		$this->cell(50,5,"No. Crédito",0,0,'C');
		$this->cell(50,5,$rel['tit_nro'],0,0,'C');
		$this->cell(50,5,$rel['tit_monto'],0,0,'C');
		$this->cell(30,5,"Fecha",0,0,'C');

		$this->ln();
		$this->cell(75,5, desencriptar($this->usuario['nombre']),0,0,'L');
		$this->cell(50,5, $this->detalle['e01DEAACC'],0,0,'C');
		$this->cell(50,5, $rel['nro'],0,0,'C');
		$this->cell(50,5, $rel['monto'],0,0,'C');
		$this->cell(30,5, date('d/m/Y'), 0, 0, 'C');
		$this->ln();
		$this->ln();
		
		if(!$this->termino){
			if($this->PageNo()==1){
			
				//$this->SetMargins(30,0);
				$this->line(10,$this->getY(),270,$this->getY());
				$this->ln();
				$this->cell(70,5,"Tipo Crédito",0,0,'C');
				$this->cell(30,5,"Estado",0,0,'C');
				$this->cell(35,5,"Fecha Otorgamiento",0,0,'C');
				$this->cell(40,5,"Monto Otorgado",0,0,'C');
				$this->cell(35,5,"Fecha Vencimiento",0,0,'C');
				$this->cell(30,5,"Plazo",0,0,'C');
				$this->ln();
				
				$plazo = $this->detalle['e01DEATRM'] . ' ';
				switch(trim($this->detalle['e01DEATRC'])){
					case 'Y':
						$plazo .= 'Años';
						break;
					case 'M':
						$plazo .= 'Meses';
						break;
					case 'D':
						$plazo .= 'Días';
						break;
				}
				
				$condicion = $this->detalle['e01DEADLC'];
			
				switch($condicion){
					case 1:
						$estado = 'Vigente';
					break;
					case 2:
						$estado = 'Vencido';
					break;
					case 3:
						$estado = 'Castigado';
					break;
					case 4:
						$estado = 'Castigado no Informado';
					break;	
					case 5:
						$estado = 'Acelerado';
					break;
				}
				
				$this->cell(70,5,trim($this->detalle['e01APCDSC']),0,0,'C');
				$this->cell(30,5,$estado,0,0,'C');
				$this->cell(35,5,formatea_fecha($this->detalle['e01OPEND2'],$this->detalle['e01OPEND1'],$this->detalle['e01OPEND3']),0,0,'C');
				$this->cell(40,5,formatea_monto_fromWs($this->detalle['e01DEAOAM']).$this->moneda,0,0,'C');
				$this->cell(35,5,formatea_fecha($this->detalle['e01MATUR2'],$this->detalle['e01MATUR1'],$this->detalle['e01MATUR3']),0,0,'C');
				$this->cell(30,5,$plazo,0,0,'C');
				$this->ln();
				$this->ln();

				$this->line(10,$this->getY(),270,$this->getY());
				$this->ln();
				$this->ln();

				//$this->SetMargins(10,0);
			}
		
			
			$x=10;
			$step=1;

			while($x<270+$step*2){
				$this->line($x,$this->getY(),$x+$step,$this->getY());
				$x+=$step*2;
			}

			$this->SetFillColor(255,255,255);

			$this->ln();
			$this->cell(10,5,"Cuota",0,0,'C');
			$this->cell(20,5,"Fecha",0,0,'C');
			$this->cell(25,5,"Monto",0,0,'C');
			$this->cell(20,5,"Capital",0,0,'C');
			$this->cell(15,5,"Interés",0,0,'C');
			$this->cell(15,5,"Tasa",0,0,'C');
			$this->cell(25,5,"Mora Bs.",0,0,'C');
			$this->cell(25,5,"Otros",0,0,'C');
			$this->cell(25,5,"Total",0,0,'C');
			$this->cell(25,5,"Monto",0,0,'C');
			$this->cell(20,5,"Fecha",0,0,'C');
			$this->cell(10,5,"Días",0,0,'C');
			$this->cell(25,5,"Saldo",0,0,'C');

			$this->ln();
			$this->cell(10,5,"",0,0,'C');
			$this->cell(20,5,"Vencimiento",0,0,'C');
			$this->cell(25,5,"Cuota Bs.",0,0,'C');
			$this->cell(20,5,"Bs.",0,0,'C');
			$this->cell(15,5,"Bs.",0,0,'C');
			$this->cell(15,5,"%",0,0,'C');
			$this->cell(25,5,"",0,0,'C');
			$this->cell(25,5,"Cargos",0,0,'C');
			$this->cell(25,5,"Cuota Bs.",0,0,'C');
			$this->cell(25,5,"Pagado Bs.",0,0,'C');
			$this->cell(20,5,"Pago",0,0,'C');
			$this->cell(10,5,"Atraso",0,0,'C');
			$this->cell(25,5,"Capital Bs.",0,0,'C');

			$this->ln();
			$this->cell(10,5,"",0,0,'C');
			$this->cell(20,5,"Pago",0,0,'C');
			$this->ln();
			$this->ln();

			$x=10;
			$step=1;

			while($x<270+$step*2){
				$this->line($x,$this->getY(),$x+$step,$this->getY());
				$x+=$step*2;
			}
		}
		
		$this->ln();
	}
	
	function Footer()
	{
		$this->ln();
		$this->SetY(-35);
		$this->line(10,$this->getY(),270,$this->getY());
		$this->SetFont('Arial','I',8);
		$this->setXY(10,-35);
		$this->setTextColor(0,51,102);
		$this->cell(97,5,'Av. Francisco de Miranda con Av. Los Cortijos de Campo Alegre, Torre Europa PH');
		$this->setTextColor(253,141,28);
		$this->cell(165,5,'Centro de Atención Activo: 0500-ACTIVAT(228.4828)',0,0,'R');
		$this->ln();
		$this->setTextColor(0,51,102);
		$this->cell(97,5,'Campo Alegre. Caracas – Venezuela');
		$this->ln();
		$this->SetFont('Arial','',5);
		$this->setTextColor(0,0,0);
		$this->multiCell(0,3,'De conformidad con lo previsto en el articulo 130 de la Ley de Bancos y otras Instituciones Financieras, Si el titular de la cuenta tiene observaciones que formular al estado de cuenta, deberá, bajo pena de caducidad, hacerlas llegar al banco por escrito y en forma detallada y razonada, dentro de los seis meses siguientes a la fecha de terminaciones del respectivo mes o periodo de liquidación Si el referido plazo de seis meses transcurre sin que el banco haya recibido ni las observaciones ni la conformidad del cliente o sin que se haya impugnado, por parte del banco o del cliente, el respectivo estado de cuenta, este se tendrá por reconocido en la forma presentada, sus saldos deudores o acreedores serán definitivos en la fecha de la cuenta y las firmas estampadas en los cheques se tendrá como reconocidas por el titular de la cuenta',0,'J');
		
		// Print centered page number
		$this->ln();
		$this->SetFont('Arial','',8);
		$this->Cell(0,5,'Página '.$this->PageNo(),0,0,'R');
	}
}

$pdf = new EdoCuentaPDF('L','mm','Letter');
$pdf->detalle = $detalle;
$pdf->usuario = $usuario;

$pdf->setAutoPageBreak(true,40);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$ebal = 0;


for($j=0;$j<count($pagos);$j++){
	$p=$pagos[$j];
	$pdf->cell(10,5,$p['e02DLPPNU'],0,0,'C');
	$pdf->cell(20,5,formatea_fecha($p['e02DLPPD1'],$p['e02DLPPD2'],$p['e02DLPPD3']),0,0,'C');
	$pdf->cell(25,5,formatea_monto_fromWs($p['e02DLPTOT']),0,0,'C');
	$pdf->cell(20,5,formatea_monto_fromWs($p['e02AMNT01']),0,0,'C');
	$pdf->cell(15,5,formatea_monto_fromWs($p['e02AMNT03']),0,0,'C');
	$pdf->cell(15,5,formatea_monto_fromWs($p['e02DESC20']),0,0,'C');
	$pdf->cell(25,5,formatea_monto_fromWs($p['e02AMNT04']),0,0,'C');
	$pdf->cell(25,5,formatea_monto_fromWs($p['e02DLPOTH']),0,0,'C');
	$pdf->cell(25,5,formatea_monto_fromWs($p['e02TOAMNT']),0,0,'C');
	$pdf->cell(25,5,formatea_monto_fromWs($p['e02TOPAID']),0,0,'C');
	$pdf->cell(20,5,formatea_fecha($p['e02DLPDT1'],$p['e02DLPDT2'],$p['e02DLPDT3']),0,0,'C');
	$pdf->cell(10,5,$p['e02DLPVEN'],0,0,'C');
	$pdf->cell(25,5,formatea_monto_fromWs($p['e02DLPBAL']),0,0,'C');
	$pdf->ln();
}

$pdf->ln();

$x=10;
$step=1;

while($x<270+$step*2){
	$pdf->line($x,$pdf->getY(),$x+$step,$pdf->getY());
	$x+=$step*2;
}

$pdf->termino = true;

$pdf->AddPage();

$pdf->cell(20,5,"TOTALES:",0,0,'C');
$pdf->ln();

$pdf->line(10,$pdf->getY(),270,$pdf->getY());

$pdf->ln();
$pdf->cell(60,5,"Total Cuotas Pagadas:",0,0,'L');
$pdf->cell(35,5,$detalle['e01DEATCP'],0,0,'L');

$pdf->ln();
$pdf->cell(60,5,"Total Cuotas Vencidas:",0,0,'L');
$pdf->cell(35,5,$detalle['e01DEATCV'],0,0,'L');
$pdf->cell(30,5,"Por un Monto de:",0,0,'L');
$pdf->cell(35,5,formatea_monto_fromWs($detalle['e01DEATDU']).$pdf->moneda,0,0,'L');

$pdf->ln();
$pdf->cell(60,5,"Monto Próxima Cuota a Pagar:",0,0,'L');
$pdf->cell(35,5,formatea_monto_fromWs($detalle['e01DEAMPX']).$pdf->moneda,0,0,'L');

$pdf->ln();
$pdf->cell(60,5,"Fecha Vencimiento Próxima Cuota a Pagar:",0,0,'L');
$pdf->cell(35,5,formatea_fecha($detalle['e01DEAMPP2'],$detalle['e01DEAMPP1'],$detalle['e01DEAMPP3']),0,0,'L');

$pdf->ln();
$pdf->cell(60,5,"Estado del Préstamo:",0,0,'L');
$pdf->cell(35,5,$estado,0,0,'L');

$pdf->ln();
$pdf->ln();
$pdf->cell(60,5,"Saldo Capital:",0,0,'L');
$pdf->cell(35,5,formatea_monto_fromWs($detalle['e01DEAMEP']).$pdf->moneda,0,0,'L');

$pdf->ln();
$pdf->cell(60,5,"Interés Pendiente:",0,0,'L');
$pdf->cell(35,5,formatea_monto_fromWs($detalle['e01DEAMEI']).$pdf->moneda,0,0,'L');

$pdf->ln();
$pdf->cell(60,5,"Mora:",0,0,'L');
$pdf->cell(35,5,formatea_monto_fromWs($detalle['e01DEAMEM']).$pdf->moneda,0,0,'L');

$pdf->ln();
$pdf->cell(60,5,"(*) Total Saldo Deudor:",0,0,'L');
$pdf->cell(35,5,formatea_monto_fromWs($detalle['e01DEATOT']).$pdf->moneda,0,0,'L');
$pdf->ln();
$pdf->ln();


$pdf->SetFont('Arial','',6);
$pdf->cell(100,5,"(*) El estado de cuenta solicitado queda sin efecto si no se cancela el día para el cual fue requerido",0,0,'L');
$pdf->ln();
$pdf->cell(100,5,"(*) La cancelación del crédito está sujeto a la aceptación del cheque por cuenta del Banco Girador",0,0,'L');

$pdf->AddPage();

$imgdata = array(
	'pag'=> array('form_pagares.png',10,75,260,80),
	'pre'=> array('form_prestamos.png',10,60,255,120),
);

$posicion = strpos(trim($detalle['e01DEAPRO']), 'PA');
$tipo_formula = ($posicion!==FALSE && $posicion == 0) ? 'pag' : 'pre';

$pdf->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.$imgdata[$tipo_formula][0],$imgdata[$tipo_formula][1],$imgdata[$tipo_formula][2],$imgdata[$tipo_formula][3],$imgdata[$tipo_formula][4]);

$pdf->output('CR'.$detalle['e01DEAACC'].date('YmdHis').'.pdf','D');
exit();
		
?>