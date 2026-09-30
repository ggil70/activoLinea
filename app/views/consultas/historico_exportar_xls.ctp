<?php
    /**
     *  Archivo xls para mostrar el listado del histórico de transacciones
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

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row,utf8_encode('NOMBRE DEL CLIENTE'));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$row,utf8_encode('FECHA'));
$row++;

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row,utf8_encode(desencriptar($usuario['nombre'])));
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$row,utf8_encode(date('d/m/Y')));

$row++;

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row,' ');

$columnas = array();

if(
	isset($this->data['HistoricoTransacciones']['configuraciones']) &&
	is_array($this->data['HistoricoTransacciones']['configuraciones']) &&
	in_array('e',$this->data['HistoricoTransacciones']['configuraciones'])
){

for($c=0;$c<count($this->data['HistoricoTransacciones']['campos']);$c++){
		$campo = $this->data['HistoricoTransacciones']['campos'][$c];
		
		switch($campo){
			case 'f':
				$columnas= 'Fecha';
				break;
			case 'r':			 
				$columnas= 'Referencia';
				break;
			case 'd':
				$columnas= 'Descripción';
				break;
			case 'm':
				$columnas= 'Monto';
				break;
		}
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c,$row,utf8_encode($columnas));
	}

$row ++;
}
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0,$row,' ');
	
$icontador = 0;


foreach($x as $i){	
	$icontador = 0;
	foreach($this->data['HistoricoTransacciones']['campos'] as $y){
		
		if(isset($y) && $y =='r'){
			$referencia = ($i['Bitacora']['referencia']!='') ? strval(desencriptar($i['Bitacora']['referencia'])).' ' : 'S/R';
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,utf8_encode($referencia));
		}
		if(isset($y) && $y =='d'){
			
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
<<<<<<< .mine
				if(strlen($numeros)==20){
					$detalleProducto=enmascara($numeros);
		   			$des=substr($cadena,0,15).' '.$detalleProducto ;
		   			utf8_decode($des);
				}else{
					$des=desencriptar($i['Bitacora']['descripcion']);
	utf8_decode($des);
				}
||||||| .r4168
				if(strlen($numeros)==20){
					$detalleProducto=enmascara($numeros);
		   			$des=substr($cadena,0,15).' '.$detalleProducto ;
				}else{
					$des=desencriptar($i['Bitacora']['descripcion']);
	
				}
=======

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

>>>>>>> .r4169
				//Fin Modificacion...***
			}

			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,utf8_encode($des));
		}
		if(isset($y) && $y =='f'){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,utf8_encode(
			formato_fecha_exportar(
				$this->data['HistoricoTransacciones']['formatoFecha'], 
				 date('d', strtotime($i['Bitacora']['fecha_hora'])),	
				 date('m', strtotime($i['Bitacora']['fecha_hora'])),
				 date('Y', strtotime($i['Bitacora']['fecha_hora']))
			)));
		}
		if(isset($y) && $y == 'm'){
			$monto = ($i['Bitacora']['monto']!='') ? $i['Bitacora']['monto'] : ' - ';
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($icontador,$row,utf8_encode(
				' '.formato_monto_exportar($this->data['HistoricoTransacciones']['formatoMonto'],$monto)
			));
		}
		$icontador++;
	}
	$row ++;
}

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Historico Transacciones');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);



// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.'HT'.date('YmdHis').'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;

?>