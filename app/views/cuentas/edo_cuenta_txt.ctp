<?php
    /**
     *  Archivo txt para generar el estado de cuenta de la cuenta seleccionada
     */



$usuario = $session->read('usuarios.datos');
$columnas = array();

switch($this->data['ExportarCuenta']['divisionCampos']){
	case 'tab':
		$separador = "\t";
		break;
	case 'f':
		$separador = "f";
		break;
	default:
		$separador = $this->data['ExportarCuenta']['divisionCampos'];
		break;
}

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
			case 'f':
				if($separador=='f'){ echo str_pad("Fecha Real Transacción",25,' ',STR_PAD_RIGHT); } else { if($c>0) echo $separador; echo 'Fecha Real Transacción'; }
				$columnas['f'] = true;
				break;
			case 'fp':
				if($separador=='f'){ echo str_pad("Fecha Contable",15,' ',STR_PAD_RIGHT); } else { if($c>0) echo $separador; echo 'Fecha Contable'; }
				$columnas['fp'] = true;
				break;	
			case 'r':			
				if($separador=='f'){ echo str_pad("Referencia",12,' ',STR_PAD_LEFT) . '    '; } else { if($c>0) echo $separador; echo 'Referencia'; }
				$columnas['r'] = true;
				break;
			case 'd':
				if($separador=='f'){ echo str_pad("Descripción",50,' ',STR_PAD_RIGHT); } else { if($c>0) echo $separador; echo 'Descripción'; }
				$columnas['d'] = true;
				break;
			case 's':
				if($separador=='f'){ echo str_pad("Saldo",15,' ',STR_PAD_LEFT) . '    '; } else { if($c>0) echo $separador; echo 'Saldo'; }
				$columnas['s'] = true;
				break;
			case 'b':
				if($separador=='f'){ echo str_pad("Débitos",15,' ',STR_PAD_LEFT) . '    '; } else { if($c>0) echo $separador; echo 'Débitos'; }
				$columnas['b'] = true;
				break;
			case 'c':
				if($separador=='f'){ echo str_pad("Créditos",15,' ',STR_PAD_LEFT) . '    '; } else { if($c>0) echo $separador; echo 'Créditos'; }
				$columnas['c'] = true;
				break;
		}
	}
}

$iconjtadorlineas = 0;

	foreach($arrData as $linea){
		
		// Limpia los espacios en blanco de la cadena
		$linea = array_map('trim', $linea);
		
		for($i=0;$i<count($this->data['ExportarCuenta']['campos']);$i++){
			$y = $this->data['ExportarCuenta']['campos'][$i];

			if($iconjtadorlineas > 0){
				if(isset($y) && $y == 'f'){
					if($separador == 'f'){
						echo str_pad(formato_fecha_exportar($this->data['ExportarCuenta']['formatoFecha'],$linea['i01VALDT1'],$linea['i01VALDT2'],$linea['i01VALDT3']),25,' ',STR_PAD_RIGHT);
					}else{
						if($i > 0) echo $separador;
					
						echo formato_fecha_exportar($this->data['ExportarCuenta']['formatoFecha'],$linea['i01VALDT1'],$linea['i01VALDT2'],$linea['i01VALDT3']);
					}
				}
				if(isset($y) && $y == 'fp'){
					if($separador == 'f'){
						echo str_pad(formato_fecha_exportar($this->data['ExportarCuenta']['formatoFecha'],$linea['i01BATDT1'],$linea['i01BATDT2'],$linea['i01BATDT3']),15,' ',STR_PAD_RIGHT);
					}else{
						if($i > 0) echo $separador;
					
						echo formato_fecha_exportar($this->data['ExportarCuenta']['formatoFecha'],$linea['i01BATDT1'],$linea['i01BATDT2'],$linea['i01BATDT3']);
					}
				}
				if(isset($y) && $y == 'r'){
					if($separador == 'f'){
						echo str_pad(strval($linea['i01NUMCHK']),12,' ',STR_PAD_LEFT) . '    ';
					}else{
						if($i > 0) echo $separador;

						echo strval($linea['i01NUMCHK']);
					}
				}
				if(isset($y) && $y == 'd'){
					if($separador == 'f'){
						echo str_pad($linea['i01NARRAT'],50,' ',STR_PAD_RIGHT);
					}else{
						if($i > 0) echo $separador;

						echo $linea['i01NARRAT'];
					}
				}
				if(isset($y) && $y == 'b'){
					if($separador == 'f'){
						echo str_pad(formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],$linea['i01DEBITS']),15,' ',STR_PAD_LEFT) . '    ';
					}else{
						if($i > 0) echo $separador;

						echo formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],$linea['i01DEBITS']);
					}
				}
				if(isset($y) && $y == 'c'){
					if($separador == 'f'){
						echo str_pad(formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],$linea['i01CREDIT']),15,' ',STR_PAD_LEFT) . '    ';
					}else{
						if($i > 0) echo $separador;
						echo formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],$linea['i01CREDIT']);
					}
				}
				if(isset($y) && $y == 's'){
					if($separador == 'f'){
						echo str_pad(formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],$linea['i01BALANC']),15,' ',STR_PAD_LEFT) . '    ';
					}else{
						if($i > 0) echo $separador;
						echo formato_monto_exportar($this->data['ExportarCuenta']['formatoMonto'],$linea['i01BALANC']);
					}
				}
			}
		}
		
		echo   "\r\n";
		
		$iconjtadorlineas++;
	}

$cuenta = $linea['i01ACMACC'];
$cuenta = substr($cuenta, strlen($cuenta)-4);

header('Content-Type: text/plain');
header('Content-Disposition: attachment;filename="'.'CB'.$cuenta.date('YmdHis').'.txt"');
header('Cache-Control: max-age=0');
	
?>