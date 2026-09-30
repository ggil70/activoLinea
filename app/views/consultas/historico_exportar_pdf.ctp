<?php
    /**
     * Archivo PDF para mostrar el listado del histórico de transacciones
     */


App::Import('Vendor', 'FPDF',array('file'=>'fpdf16/fpdf.php'));
$usuario = $session->read('usuarios.datos');

$pdf = new FPDF('P','mm','Letter');

Class HistoricoTransaccionesPDF extends FPDF{
	var $usuario;
	var $data;
	
	
	var $columnas = array(
		'f'=> array(25, 'Fecha/Hora'),
		'r'=> array(25, 'Referencia'),
		'd'=> array(110, 'Descripción'),
		'm'=> array(30, 'Monto')
	);

	function header(){

		$this->image(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'img'.DS.'logo.jpg',10,10,70);

		$this->setTextColor(0,0,0);
		$this->setXY(138,40);
		$this->setFont('Arial','B',12);
		$this->cell(70,10,"HISTÓRICO DE TRANSACCIONES",0,0,'C');


		$this->ln();
		$this->setFont('Arial','',8);
		$this->cell(50,5,"NOMBRE CLIENTE",0,0,'L');
		$this->cell(80,5,"");
		$this->cell(70,5,"FECHA",0,0,'C');

		$this->ln();
		$this->cell(50,5, desencriptar($this->usuario['nombre']),0,0,'L');
		$this->cell(80,5,"");
		$this->cell(70,5,date('d/m/Y'),0,0,'C');
		$this->ln();

		$x=10;
		$step=1;

		while($x<205+$step*2){
			$this->line($x,$this->getY(),$x+$step,$this->getY());
			$x+=$step*2;
		}

		$this->ln();
		if(
			isset($this->data['HistoricoTransacciones']['configuraciones']) &&
			is_array($this->data['HistoricoTransacciones']['configuraciones']) &&
			in_array('e',$this->data['HistoricoTransacciones']['configuraciones'])
		){
			for($c=0;$c<count($this->data['HistoricoTransacciones']['campos']);$c++){
				$campo = $this->data['HistoricoTransacciones']['campos'][$c];
				$this->cell($this->columnas[$campo][0],10,$this->columnas[$campo][1],0,0,'C');
			}
		}

		$this->ln();
		
		$x=10;
		$step=1;

		while($x<205+$step*2){
			$this->line($x,$this->getY(),$x+$step,$this->getY());
			$x+=$step*2;
		}
	}
	
	function Footer(){
		$this->ln();
		$this->SetY(-35);
		$this->line(10,$this->getY(),270,$this->getY());
		$this->SetFont('Arial','I',8);
		$this->setXY(10,-35);
		$this->setTextColor(0,51,102);
		$this->cell(97,5,'Av. Francisco de Miranda con Av. Los Cortijos de Campo Alegre, Torre Europa PH');
		$this->setTextColor(253,141,28);
		$this->cell(99,5,'Centro de Atención Activo: 0500-ACTIVAT(228.4828)',0,0,'R');
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

$pdf = new HistoricoTransaccionesPDF('P','mm','Letter');
$pdf->data=$this->data;
$pdf->usuario= $usuario;

$pdf->setAutoPageBreak(true,40);
$pdf->setMargins(10,10,10);
$pdf->AddPage();


foreach($x as $i){	

	foreach($this->data['HistoricoTransacciones']['campos'] as $y){

		if(isset($y) && $y =='r'){
			$referencia = ($i['Bitacora']['referencia']!='') ? strval(desencriptar($i['Bitacora']['referencia'])) : 'S/R';
			$pdf->cell($pdf->columnas[$y][0],10,$referencia,0,0,'C');	
		}	
		 if(isset($y) && $y == 'f'){
			$pdf->cell($pdf->columnas[$y][0],10,formato_fecha_exportar(
					$this->data['HistoricoTransacciones']['formatoFecha'], 
					 date('d', strtotime($i['Bitacora']['fecha_hora'])),	
					 date('m', strtotime($i['Bitacora']['fecha_hora'])),
					 date('Y', strtotime($i['Bitacora']['fecha_hora']))
				),0,0,'C');
		}
		if(isset($y) && $y == 'd'){
			
			if(empty($i['Bitacora']['descripcion'])){
				if(empty($i['Modulo']['nombre'])){
					$des =  $i['Transaccion']['nombre'];	
				} else {
					$des= $i['Modulo']['nombre'].' - '.$i['Transaccion']['nombre'];
				}
			} else {
				//*** Se agrego un manejo especial a la variable descripcion para enmascarar el numero de cuenta Miriam Cardozo / Albert Soto.
				$cadena=desencriptar($i['Bitacora']['descripcion']);
				$numeros= filter_var($cadena, FILTER_SANITIZE_NUMBER_INT);

						if(strlen($numeros)==20){
		$detalleProducto=enmascara($numeros);
		$des= substr(desencriptar($i['Bitacora']['descripcion']),0,15).'  '.$detalleProducto ;
	}elseif(strlen($numeros)==21){
			 $cuenta=substr($numeros,1,21);
			 $detalleProducto=enmascara($cuenta);
                   $des=substr(desencriptar($i['Bitacora']['descripcion']),0,20).'  '.$detalleProducto ;

	}else{

		$des=desencriptar($i['Bitacora']['descripcion']);
	}

				//Fin Modificacion...***
			}
			
			$pdf->cell($pdf->columnas[$y][0],10,$des,0,0,'C');
		}

		if(isset($y) && $y == 'm'){
			$monto = ($i['Bitacora']['monto']!='') ? $i['Bitacora']['monto'] : ' - ';
			$pdf->cell($pdf->columnas[$y][0],10,$monto,0,0,'R');
		}
	}
	
	$pdf->ln();
}
$pdf->ln();

$x=10;
$step=1;

while($x<205+$step*2){
	$pdf->line($x,$pdf->getY(),$x+$step,$pdf->getY());
	$x+=$step*2;
}
$pdf->output('HT'.date('YmdHis').'.pdf','D');
exit;

?>