<?php
    /**
     *  Archivo PDF que genera el estado de cuenta de la cuenta seleccionada
     */


App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));

class EdoCuentaPDF extends FPDF
{
	var $arrData;	
        
	function Header()
	{		
            
		$this->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,70);

		$this->setTextColor(0,0,0);
		$this->setXY(138,40);
		$this->setFont('Arial','B',12);
		$this->cell(70,10,"ESTADO DE CUENTA",0,0,'C');
                                
		$this->setXY(138,50);
		$this->setFont('Arial','',8);
		$this->cell(70,10,"FECHA",0,0,'C');                                
                $this->setXY(138,58);
                $this->cell(70,5,formatea_fecha($this->arrData[0]['i01STADT1'],$this->arrData[0]['i01STADT2'],$this->arrData[0]['i01STADT3']),0,0,'C');
                
		$this->ln();                
		$this->setFont('Arial','',8);
		$this->cell(35,5,"No. DE CLIENTE",0,0,'C');

		$this->ln();
		$this->cell(35,5,$this->arrData[0]['i01ACMCUN'],0,0,'C');
		$this->cell(58,5,$this->arrData[0]['i01CUSNA1']);

		$this->ln();
		$this->cell(35,5,"",0,0,'C');
		$this->cell(58,5,$this->arrData[0]['i01CUSNA2']);
                $this->cell(35,5,"",0,0,'C');
                $this->cell(20,5,"NUMERO DE CUENTA",0,0,'C');
                $this->cell(32,5,$this->arrData[0]['i01ACMACC'],0,0,'C');
                
		$this->ln();
		$this->cell(35,5,"",0,0,'C');
		$this->cell(58,5,$this->arrData[0]['i01CUSNA3']);
                $this->cell(32.5,5,"",0,0,'C');
                $this->cell(20,5,"TIPO DE MONEDA",0,0,'C');
                $tMoneda = trim($this->arrData[0]['i01ACMCCY']);

                if(trim($this->arrData[0]['i01ACMCCY'])=='VEB'){
                    $tMoneda = $this->cell(33,5,'Bolívares',0,0,'C');
                }elseif (trim($this->arrData[0]['i01ACMCCY'])=='USD') {
                	$tMoneda = $this->cell(33,5,'Dólares',0,0,'C');
                }elseif (trim($this->arrData[0]['i01ACMCCY'])=='EUR'){
                	$tMoneda = $this->cell(33,5,'Euros',0,0,'C');
                }elseif (trim($this->arrData[0]['i01ACMCCY'])=='COP'){
                	$tMoneda = $this->cell(33,5,'Pesos Colombianos',0,0,'C');
                }
                $this->cell(27,5,$tMoneda,0,0,'C');
		$this->ln();
		$this->cell(35,5,"",0,0,'C');
		$this->cell(58,5,$this->arrData[0]['i01CUSNA4']);                
                
                $this->ln();
                $this->cell(128,5);
		$this->cell(70,5,"SALDO ANTERIOR",0,0,'C');

                $this->ln();                
		$this->cell(128,5);
		$this->cell(70,5,formatea_monto_fromWs($this->arrData[0]['i01BEGBAL']),0,0,'C');
		$this->ln();
		$this->ln();

		$x=10;
		$step=1;

		while($x<205+$step*2){
			$this->line($x,$this->getY(),$x+$step,$this->getY());
			$x+=$step*2;
		}

		$this->ln();
		$this->cell(23,5,'FECHA REAL',0,0,'C');
		$this->cell(23,5,'FECHA',0,0,'C');
		$this->cell(25,5,"REFERENCIA",0,0,'C');
		$this->cell(52,5,"DESCRIPCION",0,0,'C');
		$this->cell(25,5,"DEBITOS",0,0,'C');
		$this->cell(25,5,"CREDITOS",0,0,'C');
		$this->cell(25,5,"SALDO",0,0,'C');
		$this->ln();
		
		$this->cell(23,5,'TRANSACCION',0,0,'C');
		$this->cell(23,5,'CONTABLE',0,0,'C');
		$this->cell(25,5,"",0,0,'C');
		$this->cell(52,5,"",0,0,'C');
		$this->cell(25,5,"",0,0,'C');
		$this->cell(25,5,"",0,0,'C');
		$this->cell(25,5,"",0,0,'C');
		$this->ln();
		$this->ln();

		$x=10;
		$step=1;

		while($x<205+$step*2){
			$this->line($x,$this->getY(),$x+$step,$this->getY());
			$x+=$step*2;
		}
		
		$this->ln();
	}
	
	function Footer()
	{
		$this->SetY(-40);
		$this->line(10,$this->getY(),205,$this->getY());
		$this->SetFont('Arial','I',8);
		$this->setXY(10,-39);
		$this->setTextColor(0,51,102);
		$this->cell(97,5,'Av. Francisco de Miranda con Av. Los Cortijos de Campo Alegre, Torre Europa PH');
		$this->setTextColor(253,141,28);
		$this->cell(97,5,'Centro de Atención Activo: 0500-ACTIVAT(228.4828)',0,0,'R');
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

$pdf = new EdoCuentaPDF('P','mm','Letter');
$pdf->arrData=$arrData;

$pdf->setAutoPageBreak(true,40);
$pdf->setMargins(10,10,10);
$pdf->AddPage();

$ebal = 0;
$cantidad = 16;
$nuevaPagina = 0;
$registros = 22;

for($j=1;$j<count($arrData);$j++){
	$i=$arrData[$j];
	$pdf->cell(23,5,formatea_fecha($i['i01VALDT1'],$i['i01VALDT2'],$i['i01VALDT3']),0,0,'C');
	$pdf->cell(23,5,formatea_fecha($i['i01BATDT1'],$i['i01BATDT2'],$i['i01BATDT3']),0,0,'C');	
	$pdf->cell(25,5,$i['i01NUMCHK']?$i['i01NUMCHK']:'',0,0,'C');
	$pdf->cell(52,5,$i['i01NARRAT'],0,0,'L');
	$pdf->cell(25,5,formatea_monto_fromWs($i['i01DEBITS']),0,0,'R');
	$pdf->cell(25,5,formatea_monto_fromWs($i['i01CREDIT']),0,0,'R');
	$pdf->cell(25,5,formatea_monto_fromWs($i['i01BALANC']),0,0,'R');
	$ebal = $i['i01BALANC'];
	$pdf->ln();
        
        if($j>=$cantidad){
            $nuevaPagina = 1;            
        }else{
            $nuevaPagina = 0;
        }
        
        if($j==$registros){            
            $cantidad=$cantidad+$registros;
            $registros = $registros+22;
        }
}

$pdf->ln();

if ($nuevaPagina == 1){
    $pdf->AddPage();
}

$x=10;
$step=1;

while($x<205+$step*2){
	$pdf->line($x,$pdf->getY(),$x+$step,$pdf->getY());
	$x+=$step*2;
}

$pdf->ln();
$pdf->cell(27,5,"",0,0,'C');
$pdf->cell(54,5,"No. DE OPERACIONES",0,0,'C');
$pdf->cell(27,5,"TOTAL DEBITOS",0,0,'C');
$pdf->cell(27,5,"TOTAL CREDITOS",0,0,'C');
$pdf->cell(27,5,"NUEVO SALDO",0,0,'C');
$pdf->cell(27,5,"",0,0,'C');
$pdf->ln();
$pdf->cell(27,5,"",0,0,'C');
$pdf->cell(54,5,count($arrData)-1,0,0,'C');
$pdf->cell(27,5,formatea_monto_fromWs($totales['i01DEBITS']),0,0,'C');
$pdf->cell(27,5,formatea_monto_fromWs($totales['i01CREDIT']),0,0,'C');
$pdf->cell(27,5,formatea_monto_fromWs($ebal),0,0,'C');
$pdf->cell(27,5,"",0,0,'C');
$pdf->ln();
$pdf->ln();

$x=10;
$step=1;

while($x<205+$step*2){
	$pdf->line($x,$pdf->getY(),$x+$step,$pdf->getY());
	$x+=$step*2;
}

$cuenta = $arrData[0]['i01ACMACC'];
$cuenta = substr($cuenta, strlen($cuenta)-4);

$pdf->output('CB'.$cuenta.date('YmdHis').'.pdf','D');
exit;
		
?>