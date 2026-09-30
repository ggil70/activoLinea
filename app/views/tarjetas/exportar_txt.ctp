<?php
/**
 * Exportación en formato txt de los movimientos de la tarjeta de crédito
**/

$columnas = array(
	'f'=>array(15,'Fecha',STR_PAD_RIGHT),
	'r'=>array(15,'Referencia    ',STR_PAD_LEFT),
	'd'=>array(30,'Descripcion',STR_PAD_RIGHT),
	'c'=>array(20,'Cargo Bs.    ',STR_PAD_LEFT),
	'a'=>array(20,'Abono Bs.    ',STR_PAD_LEFT),
);





if(count($arreglo)){

	echo 'Número de Tarjeta: '.$this->data['ExportarTarjeta']['nro_tarjeta']."\r\n\r\n";


	
	if( isset($this->data['ExportarTarjeta']['configuraciones']) && is_array($this->data['ExportarTarjeta']['configuraciones']) ){

		for($c=0;$c<count($this->data['ExportarTarjeta']['campos']);$c++){

			$campo = $this->data['ExportarTarjeta']['campos'][$c];

			switch($this->data['ExportarTarjeta']['divisionCampos']){
				case 'f':
					echo str_pad($columnas[$campo][1],$columnas[$campo][0],' ',$columnas[$campo][2]);
					break;
				case 'tab':
					if($c > 0) echo "\t";
					echo trim($columnas[$campo][1]);
					break;
				default :
					if($c > 0) echo $this->data['ExportarTarjeta']['divisionCampos'];
					echo trim($columnas[$campo][1]);
					break;
			}
		}

		echo "\r\n";
	}


	for($i=0;$i<count($arreglo);$i++){
		for($j=0;$j<count($this->data['ExportarTarjeta']['campos']);$j++){
			$campo = $this->data['ExportarTarjeta']['campos'][$j];
			
			switch($this->data['ExportarTarjeta']['divisionCampos']){
				case 'f':
					echo str_pad($arreglo[$i][$campo], $columnas[$campo][0], ' ', $columnas[$campo][2]);
					break;
				case 'tab':
					if($j > 0) echo "\t";
					echo trim($arreglo[$i][$campo]);
					break;
				default:
					if($j > 0) echo $this->data['ExportarTarjeta']['divisionCampos'];
					echo trim($arreglo[$i][$campo]);
					break;
			}
		}
		
		echo "\r\n";
	}

    


	
	if(in_array('a', $this->data['ExportarTarjeta']['campos']) || in_array('c', $this->data['ExportarTarjeta']['campos'])){
		echo "\r\n";
		echo "Totales:\r\n";
		echo "Cargos: " . formato_monto_exportar($this->data['ExportarTarjeta']['formatoMonto'],$totalesTDC['CREDITOSTDC']) . " Bs.\r\n";
		echo "Abonos: " . formato_monto_exportar($this->data['ExportarTarjeta']['formatoMonto'],$totalesTDC['ABONOSTDC']) . " Bs.\r\n";
	}
} else {
	echo 'No se encontraron resultados';
}

$tarjeta = $this->data['ExportarTarjeta']['nro_tarjeta'];
$tarjeta = substr($tarjeta, strlen($tarjeta)-4);

header('Content-Type: text/plain');
header('Content-Disposition: attachment;filename="'.'TC'.$tarjeta.date('YmdHis').'.txt"');
header('Cache-Control: max-age=0');

?>