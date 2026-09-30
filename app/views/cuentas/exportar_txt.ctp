<?php
    /**
     *  Archivo txt para generar el estado de cuenta de la cuenta seleccionada
     */



$usuario = $session->read('usuarios.datos');

$padding_data = array(
	's'=>array(max(15,strlen('Saldo')),STR_PAD_LEFT),
	'd'=>array(max(50,strlen('Descripcion')),STR_PAD_RIGHT),
	'r'=>array(max(15,strlen('Referencia')),STR_PAD_LEFT),
	'c'=>array(max(25,strlen('Código de Transacción')),STR_PAD_RIGHT),
	't'=>array(max(25,strlen('Tipo de Transacción')),STR_PAD_RIGHT),
	'm'=>array(max(15,strlen('Monto')),STR_PAD_LEFT),
	'f'=>array(max(25,strlen('Fecha Real Transacción')),STR_PAD_RIGHT),
	'fp'=>array(max(15,strlen('Fecha Contable')),STR_PAD_RIGHT),
);

if(
	isset($this->data['ExportarCuenta']['configuraciones']) &&
	is_array($this->data['ExportarCuenta']['configuraciones']) &&
	in_array('n',$this->data['ExportarCuenta']['configuraciones'])
){
	echo 'Número de Cuenta: '.$this->data['ExportarCuenta']['nro_cuenta']."\r\n";
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
				$nombre_columna='Saldo    ';
				break;
			case 'm':
				$nombre_columna='Monto    ';
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
				$nombre_columna='Referencia    ';
				break;
			case 'd':
				$nombre_columna='Descripción';
				break;
			case 't':
				$nombre_columna='Tipo de Transacción';
				break;
		}
		
		switch($this->data['ExportarCuenta']['divisionCampos']){
			case 'f':
				$campo = $this->data['ExportarCuenta']['campos'][$c];		
				$pd = $padding_data[$campo];
				echo str_pad($nombre_columna,$pd[0],' ',$pd[1]);
				break;
			case ';':
				if($c>0) echo ";";
				echo trim($nombre_columna);
				break;
			case 'tab':
				if($c>0) echo "\t";
				echo trim($nombre_columna);
				break;
		}
	}
	
	echo "\r\n";
}

for($i=0;$i<count($movimientos);$i++){
	for($j=0;$j<count($movimientos[$i]);$j++){

		switch($this->data['ExportarCuenta']['divisionCampos']){
			case 'f':
				$campo = $this->data['ExportarCuenta']['campos'][$j];		
				$pd = $padding_data[$campo];
				echo str_pad($movimientos[$i][$j],$pd[0],' ',$pd[1]);
				break;
			case ';':
				if($j>0) echo ";";
				echo trim($movimientos[$i][$j]);
				break;
			case 'tab':
				if($j>0) echo "\t";
				echo trim($movimientos[$i][$j]);
				break;
		}
	}
	
	echo "\r\n";
}

	$cuenta = $this->data['ExportarCuenta']['nro_cuenta'];
	$cuenta = substr($cuenta, strlen($cuenta)-4);

	header('Content-Type: text/plain');
	header('Content-Disposition: attachment;filename="'.'CB'.$cuenta.date('YmdHis').'.txt"');
	header('Cache-Control: max-age=0');

?>