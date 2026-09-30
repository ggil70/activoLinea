<?php
/**
 *  Archivo txt para generar el estado de cuenta en formato MT940
 */

set_time_limit(0);

$lineas = 0;

$limite_caracteres = 1800;

$fin_bloque = "-}";

// YYMMDD del estado de cuenta
$fecha_edo_cuenta = $arrData[0]['i01STADT3'].str_pad($arrData[0]['i01STADT2'],2,'0',STR_PAD_LEFT).str_pad($arrData[0]['i01STADT1'],2,'0',STR_PAD_LEFT);

// Asigna los valores del header
$header_mensaje = '{1:F01ACTIVECAXXX0000000000}';
$header_mensaje .= '{2:I940ACTIVECAXXXXN}';
$header_mensaje .= '{3:{108:'.date('mYHis').'}}';
$header_mensaje .= "{4:\n";
$header_mensaje .= ":20:".date('mYHis')."\n";
$header_mensaje .= ":25:".$nro_cuenta."\n";


$pagina = 1;
$header_mensaje_28 = ":28C:".@str_pad(date('m'), 3, '0', STR_PAD_LEFT)."/".$pagina."\n";

$tipo_saldo_inicial = $arrData[0]['i01BEGBAL']>0 ? 'C' : 'D';
$header_mensaje_60 = ":60F:".$tipo_saldo_inicial.$fecha_edo_cuenta._p('moneda').formato_monto_exportar(',', $arrData[0]['i01BEGBAL'])."\n";

$tipo_saldo_disponible = $arrData[(count($arrData)-1)]['i01BALANC']>0 ? 'C' : 'D';
$mensaje_64 = ":64:".$tipo_saldo_disponible.$fecha_edo_cuenta._p('moneda').formato_monto_exportar(',', $arrData[(count($arrData)-1)]['i01BALANC'])."\n";


// impresion
echo $header_mensaje.$header_mensaje_28.$header_mensaje_60;

$transacciones = '';

// Recorre las transacciones del estado de cuenta
foreach( $arrData as $v ){
	
	// El primer registro en la respuesta no se toma en cuenta
	if($lineas==0){
		$lineas++;
		continue;
	}
	
	// Datos de la transaccion
	$tipo_transaccion = ($v['i01CREDIT']>0) ? 'C': 'D';
	$monto_transaccion = formato_monto_exportar(',', ($v['i01DEBITS']>0) ? $v['i01DEBITS'] : $v['i01CREDIT'] );
	$referencia = $v['i01NUMCHK'] ? $v['i01NUMCHK'] : 'NONREF';
	$descripcion = (strlen(trim($v['i01NARRAT']))>34) ? substr(trim($v['i01NARRAT']), 0, 34) : trim($v['i01NARRAT']);
	$descripcion = (!empty($descripcion)) ? "//".$descripcion."\n:86:".$descripcion."\n" : '';
	
	
	// Arma la trama de la transaccion
	// Guarda las transacciones que lleva para saber la cantidad de caracteres
	$transacciones .= $transaccion = ":61:".$v['i01VALDT3'].str_pad($v['i01VALDT2'],2,'0',STR_PAD_LEFT).str_pad($v['i01VALDT1'],2,'0',STR_PAD_LEFT)
		.$tipo_transaccion.$monto_transaccion 
		.trim($v['i01DSCOFC']).$referencia."\n".$descripcion;
	
	
	// Arma el tag 62
	$balance_final = $v['i01BALANC'];
	$tipo_saldo_final = ($balance_final>0) ? 'C' : 'D';
	$mensaje_62 = ":62F:".$tipo_saldo_final.$fecha_edo_cuenta._p('moneda').formato_monto_exportar(',', $balance_final)."\n";

	
	// Verifica si el texto del mensaje alcanzo el limite de caracteres maximos permitidos
	$cantidad_caracteres = strlen($header_mensaje.$header_mensaje_28.$header_mensaje_60.$transacciones.$mensaje_62.$mensaje_64.$fin_bloque);
	if($cantidad_caracteres>$limite_caracteres){
		
		// Arma el tag 62M 
		// Como se divide en varios mensajes el balance para el corte es el de la transaccion anterior
		$balance_final = $arrData[($lineas-1)]['i01BALANC'];
		$tipo_saldo_final = ($balance_final>0) ? 'C' : 'D';
		$mensaje_62 = ":62M:".$tipo_saldo_final.$fecha_edo_cuenta._p('moneda').formato_monto_exportar(',', $balance_final)."\n";
		
		// Cierra el mensaje
		echo $mensaje_62;
		echo $mensaje_64;
		echo $fin_bloque;
		
		// Actualiza variables
		$lineas++;
		$transacciones = $transaccion;
		$pagina++;
		
		// Actualiza headers
		$header_mensaje_28 = ":28C:".@str_pad(date('m'), 3, '0', STR_PAD_LEFT)."/".$pagina."\n";
		$header_mensaje_60 = ":60M:".$tipo_saldo_final.$fecha_edo_cuenta._p('moneda').formato_monto_exportar(',', $balance_final)."\n";
		
		echo "\n$\n";
		echo $header_mensaje.$header_mensaje_28.$header_mensaje_60.$transaccion;
		continue;
	}
		
	// impresion de la transaccion
	echo $transaccion;
	
	// Si es la ultima transaccion imprime el tag 62
	if( (count($arrData)-1) == $lineas ) echo $mensaje_62;
		
	$lineas++;
}

// impresion
echo $mensaje_64;
echo $fin_bloque;

// Header del archivo
header('Content-Type: text/plain');
header('Content-Disposition: attachment;filename="'.'CB'.@substr($nro_cuenta, strlen($nro_cuenta)-4).date('YmdHis').'.txt"');
header('Cache-Control: max-age=0');

?>