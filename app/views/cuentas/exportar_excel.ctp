<?php
    /**
     *  Archivo xls para exportar el estado de cuenta seleccionado
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

$cuenta = $this->data['ExportarCuenta']['nro_cuenta'];
$cuenta = substr($cuenta, strlen($cuenta)-4);

$fila = 1;

if(
	isset($this->data['ExportarCuenta']['configuraciones']) &&
	is_array($this->data['ExportarCuenta']['configuraciones']) &&
	in_array('n',$this->data['ExportarCuenta']['configuraciones'])
){
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$fila,utf8_encode('Número de Cuenta'));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$fila,enmascara($this->data['ExportarCuenta']['nro_cuenta']));
	
	$fila++;
}						

if(
	isset($this->data['ExportarCuenta']['configuraciones']) &&
	is_array($this->data['ExportarCuenta']['configuraciones']) &&
	in_array('e',$this->data['ExportarCuenta']['configuraciones'])
){
	for($c=0;$c<count($this->data['ExportarCuenta']['campos']);$c++){
		$campo = $this->data['ExportarCuenta']['campos'][$c];
		
		switch($campo){
			case 's':
				$nombre_columna='Saldo';
				break;
			case 'm':
				$nombre_columna='Monto';
				break;
			case 'c':
				$nombre_columna='Código de Transacción';
				break;
			case 'f':
				$nombre_columna='Fecha Real Transacción';
				break;
			case 'fp':
				$nombre_columna='Fecha Contable';
				break;	
			case 'r':
				$nombre_columna='Referencia';
				break;
			case 'd':
				$nombre_columna='Descripción';
				break;
			case 't':
				$nombre_columna='Tipo de Transacción';
				break;
		}
		
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$fila,utf8_encode($nombre_columna));
	}

	$fila++;
}

for($i=0;$i<count($movimientos);$i++){
	for($j=0;$j<count($movimientos[$i]);$j++){
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j,$fila+$i,utf8_encode($movimientos[$i][$j]));
	}
}

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Cuenta');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.'CB'.$cuenta.date('YmdHis').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

?>