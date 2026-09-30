<?php
/**
 * Exportación en formato excel de los movimientos de la tarjeta de crédito
**/

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


$fila = 1;

if(count($arreglo)){

	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$fila,utf8_encode('Número de Cuenta'));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$fila,enmascara($this->data['ExportarTarjeta']['nro_tarjeta']));

	$fila = $fila+1;

	if( isset($this->data['ExportarTarjeta']['configuraciones']) && is_array($this->data['ExportarTarjeta']['configuraciones']) ){
		for($c=0;$c<count($this->data['ExportarTarjeta']['campos']);$c++){
			$campo = $this->data['ExportarTarjeta']['campos'][$c];
			
			switch ($campo){
				case 'f':
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$fila,'Fecha');
					break;
				case 'r':
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$fila,'Referencia');
					break;
				case 'd':
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$fila,utf8_encode('Descripción'));
					break;
				case 'c':
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$fila,'Cargo Bs.');
					break;
				case 'a':
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$fila,'Abono Bs.');
					break;
			}
		}

		$fila++;
	}

	for($i=0;$i<count($arreglo);$i++){
		for($j=0;$j<count($this->data['ExportarTarjeta']['campos']);$j++){
			$campo = $this->data['ExportarTarjeta']['campos'][$j];
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($j,$fila,utf8_encode($arreglo[$i][$campo]));
		}
		
		$fila++;
	}
	
	if(in_array('a', $this->data['ExportarTarjeta']['campos']) || in_array('c', $this->data['ExportarTarjeta']['campos'])){
		$fila++;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$fila,'Totales:');
		$fila++;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$fila,'Cargos:');
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$fila,formato_monto_exportar($this->data['ExportarTarjeta']['formatoMonto'],$totalesTDC['CREDITOSTDC']).' Bs.');
		$fila++;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$fila,'Abonos:');
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$fila,formato_monto_exportar($this->data['ExportarTarjeta']['formatoMonto'],$totalesTDC['ABONOSTDC']).' Bs.');
	}
	
	// Rename sheet
	$objPHPExcel->getActiveSheet()->setTitle(utf8_encode('Tarjeta de Crédito'));

	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
}
else{
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$fila,'No se encontraron resultados');
}

$tarjeta = $this->data['ExportarTarjeta']['nro_tarjeta'];
$tarjeta = substr($tarjeta, strlen($tarjeta)-4);

// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.'TC'.$tarjeta.date('YmdHis').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

?>