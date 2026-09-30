<?php
    /**
     *  Archivo txt para mostrar el listado del histórico de transacciones
     */	 
$usuario = $session->read('usuarios.datos');

$columnas = array();

switch($this->data['HistoricoTransacciones']['divisionCampos']){
	case 'tab':
		$separador = "\t";
		break;
	case 'f':
		$separador = "f";
		break;
	default:
		$separador = $this->data['HistoricoTransacciones']['divisionCampos'];
		break;
}

echo str_pad("NOMBRE DEL CLIENTE",70,' ',STR_PAD_RIGHT);
echo str_pad("",15,' ',STR_PAD_BOTH);
echo str_pad("FECHA",16,' ',STR_PAD_BOTH);
echo "\r\n";

echo str_pad(desencriptar($usuario['nombre']),70,' ',STR_PAD_RIGHT);
echo str_pad("",15,' ',STR_PAD_BOTH);
echo str_pad(date('d/m/Y'),16,' ',STR_PAD_BOTH);
echo "\r\n";


echo "\r\n";

if(
	isset($this->data['HistoricoTransacciones']['configuraciones']) &&
	is_array($this->data['HistoricoTransacciones']['configuraciones']) &&
	in_array('e',$this->data['HistoricoTransacciones']['configuraciones'])
){

	for($c=0;$c<count($this->data['HistoricoTransacciones']['campos']);$c++){
		$campo = $this->data['HistoricoTransacciones']['campos'][$c];
		
		switch($campo){
			case 'f':
				if($separador=='f'){ echo str_pad("Fecha",18,' ',STR_PAD_RIGHT); } else { if($c>0) echo $separador; echo 'Fecha'; }
				$columnas['f'] = true;
				break;
			case 'r':			
				if($separador=='f'){ echo str_pad("Referencia",18,' ',STR_PAD_LEFT) . '    '; } else { if($c>0) echo $separador; echo 'Referencia'; }
				$columnas['r'] = true;
				break;
			case 'd':
				if($separador=='f'){ echo str_pad("Descripción",70,' ',STR_PAD_RIGHT); } else { if($c>0) echo $separador; echo 'Descripción'; }
				$columnas['d'] = true;
				break;
			case 'm':
				if($separador=='f'){ echo str_pad("Monto Bs.",15,' ',STR_PAD_LEFT) . '    '; } else { if($c>0) echo $separador; echo 'Monto Bs.'; }
				$columnas['m'] = true;
				break;
		}
	}

echo "\r\n";
}
foreach($x as $i){	

	for($j=0;$j<count($this->data['HistoricoTransacciones']['campos']);$j++){
		$y = $this->data['HistoricoTransacciones']['campos'][$j];

		if(isset($y) && $y =='r'){
			$referencia = ($i['Bitacora']['referencia']!='') ? strval(desencriptar($i['Bitacora']['referencia'])) : 'S/R';
			
			if($separador == 'f'){
				echo str_pad($referencia,18,' ',STR_PAD_LEFT) . '    ';
			}else{
				if($j>0) echo $separador;
				echo $referencia;
			}
		}
		
		if(isset($y) && $y == 'f'){
			if($separador == 'f'){
				echo str_pad(
						formato_fecha_exportar(
							$this->data['HistoricoTransacciones']['formatoFecha'], 
							 date('d', strtotime($i['Bitacora']['fecha_hora'])),	
							 date('m', strtotime($i['Bitacora']['fecha_hora'])),
							 date('Y', strtotime($i['Bitacora']['fecha_hora']))
						),
						18,' ',STR_PAD_RIGHT
					); 
			}else{
				if($j>0) echo $separador;
				
				echo formato_fecha_exportar($this->data['HistoricoTransacciones']['formatoFecha'],
					date('d', strtotime($i['Bitacora']['fecha_hora'])),	
					date('m', strtotime($i['Bitacora']['fecha_hora'])),
					date('Y', strtotime($i['Bitacora']['fecha_hora'])));
			}
		}
		utf8_encode($i['Bitacora']['descripcion']);      
		if(isset($y) && $y == 'd'){
			if(empty($i['Bitacora']['descripcion'])){
				if(empty($i['Modulo']['nombre'])){
 					$des =  $i['Transaccion']['nombre'];	
				} else {
					$des= $i['Modulo']['nombre'].' - '.$i['Transaccion']['nombre'];
				}
			} else {
				//*** Se agrego un manejo especial a la variable descripcion para enmascarar el numero de cuenta Miriam Cardozo / Albert Soto.php
				$cadena=desencriptar($i['Bitacora']['descripcion']);
				$numeros= filter_var($cadena, FILTER_SANITIZE_NUMBER_INT);
<<<<<<< .mine
				if(strlen($numeros)==20){
					$detalleProducto=enmascara($numeros);
		   			$des=substr($cadena,0,15).' '.$detalleProducto ;
		   		}else{

					$des=desencriptar($i['Bitacora']['descripcion']);
					
				}
||||||| .r4164
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
			if($separador == 'f'){
				echo str_pad($des,70,' ',STR_PAD_RIGHT);
			}else{
				if($j>0) echo $separador;
				echo $des;
			}
		}

		if(isset($y) && $y == 'm'){
			$monto = ($i['Bitacora']['monto']!='') ? $i['Bitacora']['monto'] : ' - ';
			if($separador == 'f'){
				echo str_pad(formato_monto_exportar($this->data['HistoricoTransacciones']['formatoMonto'],$monto),15,' ',STR_PAD_LEFT) . '    ';
			}else{
				if($j>0) echo $separador;
				echo formato_monto_exportar($this->data['HistoricoTransacciones']['formatoMonto'],$monto); 
			}
		}

	}

	echo   "\r\n";
}

header('Content-Type: text/plain');
header('Content-Disposition: attachment;filename="'.'HT'.date('Ymdhis').'.txt"');
header('Cache-Control: max-age=0');
	
?>