<?php
    /**
     *  Archivo xls para generar el estado de cuenta de la cuenta seleccionada
     */

$usuario = $session->read('usuarios.datos');

App::import('Vendor', 'PHPExcel',array('file'=>'phpexcel'.DS.'Classes'.DS.'PHPExcel.php'));

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties
$objPHPExcel->getProperties()->setCreator("Banco Activo")
							 ->setLastModifiedBy("Banco Activo")
							 ->setTitle("")
							 ->setSubject("")
							 ->setDescription("")
							 ->setKeywords("")
							 ->setCategory("");
							 
$objPHPExcel->setActiveSheetIndex(0);

$row=1;
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize('true');
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize('true');
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize('true');
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize('true');
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize('true');
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize('true');


if(
	isset($this->data['ExportarCuenta']['configuraciones']) &&
	is_array($this->data['ExportarCuenta']['configuraciones']) &&
	in_array('n',$this->data['ExportarCuenta']['configuraciones'])
){
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row,utf8_encode('Número de Cuenta'));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$row,enmascara($this->data['ExportarCuenta']['nro_cuenta']));
	
	$row++;
}

//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row,utf8_encode(''));

$columnas = array();

if(
	isset($this->data['ExportarCuenta']['configuraciones']) &&
	is_array($this->data['ExportarCuenta']['configuraciones']) &&
	in_array('e',$this->data['ExportarCuenta']['configuraciones'])
){
	for($c=0;$c<count($this->data['ExportarCuenta']['campos']);$c++){
		$campo = $this->data['ExportarCuenta']['campos'][$c];

		switch($campo){
			case 'f':
				$columnas= 'Fecha Real Transacción';
				break;
			case 'fp':
				$columnas= 'Fecha Contable';
				break;	
			case 'r':			 
				$columnas= 'Referencia';
				break;
			case 'd':
				$columnas= 'Descripción';
				break;
			case 's':
				$columnas= 'Saldo';
				break;
			case 'b':
				$columnas= 'Débitos';
				break;
			case 'c':
				$columnas= 'Créditos';
				break;
		}
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$row,utf8_encode($columnas));
	}

$row ++;
}


$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row,' ');
	
$icontador = 0;

$flagtemp = 0;

foreach($arrData as $linea){
	if(!$flagtemp){
		$flagtemp = 1;
		continue;
	}
	$icontador = 0;
	foreach($this->data['ExportarCuenta']['campos'] as $y){
		if(isset($y) && $y == 'f'){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,utf8_encode(formato_fecha_exportar($this->data['ExportarCuenta']['formatoFecha'],$linea['i01VALDT1'],$linea['i01VALDT2'],$linea['i01VALDT3'])));	
		}
		if(isset($y) && $y == 'fp'){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,utf8_encode(formato_fecha_exportar($this->data['ExportarCuenta']['formatoFecha'],$linea['i01BATDT1'],$linea['i01BATDT2'],$linea['i01BATDT3'])));	
		}
		if(isset($y) && $y == 'r'){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,utf8_encode(strval($linea['i01NUMCHK']).' '));
		}
		if(isset($y) && $y == 'd'){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,utf8_encode($linea['i01NARRAT']));
		}
		if(isset($y) && $y == 'b'){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,' '.formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],trim($linea['i01DEBITS']) ));
		}
		if(isset($y) && $y == 'c'){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,' '.formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],trim($linea['i01CREDIT'])));
		}
		if(isset($y) && $y == 's'){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,' '.formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],trim($linea['i01BALANC'])));				
		}
		$icontador++;	
	}
	$row ++;
}

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Cuenta');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

$cuenta = $linea['i01ACMACC'];
$cuenta = substr($cuenta, strlen($cuenta)-4);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.'CB'.$cuenta.date('YmdHis').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

?>