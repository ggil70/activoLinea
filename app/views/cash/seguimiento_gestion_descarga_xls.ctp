<?php
    /**
     *  Archivo xls para mostrar el listado con los últimos pagos de nómina y proveedores
     */

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


// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Seguimiento de Gestion');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row,utf8_encode('H'));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$row,count($this->data['Beneficiarios']));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$row,str_replace('.', ',', $this->data['TransaccionPendiente']['monto']));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$row,utf8_encode(desencriptar($this->data['TransaccionPendiente']['descripcion'])));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$row,$this->data['TransaccionPendiente']['lote']);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$row,date('dmY',strtotime($this->data['TransaccionPendiente']['fecha_hora_valor'])));

foreach($this->data['Beneficiarios'] as $i){
	$row++;
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row,utf8_encode('P'));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$row,utf8_encode($i['tipo_cedula'].str_pad(desencriptar($i['cedula']),9,'0',STR_PAD_LEFT)));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$row,str_replace('.', ',', $i['monto']));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$row,desencriptar($i['concepto']));
	if(!empty($i['referencia'])){
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$row,desencriptar($i['referencia']));
	} else {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$row,'');
	}
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5,$row,isset($i['respuestaWS']) ? $i['respuestaWS'] : '');
	
	
	// Detalle de un pago de proveedores
	if(isset($i['detalle'])){
		foreach ($i['detalle'] as $d){
			$row++;
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row,utf8_encode('D'));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$row,utf8_encode($d['DetallePagoProveedor']['tipo_detalle']));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$row,utf8_encode(desencriptar($d['DetallePagoProveedor']['referencia'])));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$row,str_replace('.', ',', $d['DetallePagoProveedor']['monto']));
		}
	}
	
}


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.date('YmdHis').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
?>