<?php
    /**
     * Archivo txt para mostrar el listado con los últimos pagos de nómina y proveedores
     */


echo 'H';
echo ';';
echo count($this->data['Beneficiarios']);
echo ';';
echo trim(str_replace('.', ',', $this->data['TransaccionPendiente']['monto']));
echo ';';
echo trim(str_pad(desencriptar($this->data['TransaccionPendiente']['descripcion']),30,' '));
echo ';';
echo trim(str_pad($this->data['TransaccionPendiente']['lote'],9,'0',STR_PAD_LEFT));
echo ';';
echo trim(date('dmY',strtotime($this->data['TransaccionPendiente']['fecha_hora_valor'])));

foreach($this->data['Beneficiarios'] as $i){

	echo "\r\n";

	echo 'P';
	echo ';';
	echo trim($i['tipo_cedula']).trim(str_pad(desencriptar($i['cedula']),9,'0',STR_PAD_LEFT));
	echo ';';
	echo str_replace('.', ',', $i['monto']);
	echo ';';
	echo trim(str_pad(desencriptar($i['concepto']),30,' '));
	echo ';';
	if(!empty($i['referencia'])){
		echo trim(str_pad(desencriptar($i['referencia']),9,'0',STR_PAD_LEFT));
	} else {
		echo trim(str_pad(' ',9,' '));
	}
	echo ';';
	echo isset($i['respuestaWS']) ? trim($i['respuestaWS']) : '';
	
	// Detalle de un pago de proveedores
	if(isset($i['detalle'])){
		foreach ($i['detalle'] as $d){
			echo "\r\n";
			echo 'D;';
			echo $d['DetallePagoProveedor']['tipo_detalle'].';';
			echo desencriptar($d['DetallePagoProveedor']['referencia']).';';
			echo str_replace('.', ',', $d['DetallePagoProveedor']['monto']);
		}
	}
}

header('Content-Type: text/plain');
header('Content-Disposition: attachment;filename="'.date('Ymdhis').'.txt"');
header('Cache-Control: max-age=0');

?>