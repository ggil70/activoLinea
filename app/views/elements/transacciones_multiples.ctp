<?php
/**
 * Fragmento de página que se encarga de generar el listado de
 * transacciones pendientes por aprobar. Las cuales fueron seleccionadas
 * con aterioridad
**/

foreach($agrupadas as $cuenta => $transacciones_portipo) { 
$cuenta_des = desencriptar($cuenta);
?>

<div class="espacio_vertical_10"></div>
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
<thead>
<tr>
	<td colspan="<?php echo (isset($mostrar_resultado)) ? 6 : 5; ?>">Número de Cuenta <?php echo enmascara($cuenta_des) ?> - Saldo Disponible Total Bs. <?php if(isset($cuentas_map[$cuenta_des])) echo formatea_monto_fromWs($cuentas_map[$cuenta_des]) ?></td>
</tr>
</thead>

<?php foreach($transacciones_portipo as $tipo => $transacciones) { ?>
<tbody>
<tr><td colspan="<?php echo  (isset($mostrar_resultado)) ? 6 : 5; ?>" style="border:0"></td></tr>
</tbody>

<thead>
<tr>
<td class="etiqueta" colspan="<?php echo (isset($mostrar_resultado)) ? 6 : 5; ?>" style="text-align:center">
<?php switch($tipo){
	case 't':
		echo 'Transferencia';
		break;
        case 'd':
		echo 'Transferencia Moneda Extranjera';
		break;    
	case 'p':
		echo 'Pago de Tarjeta de Crédito';
		break;
	case 'n':
		echo 'Pago de Nómina';
		break;
	case 'm':
		echo 'Pago de Nómina Moneda Extranjera';
		break;	
	case 'i':
		echo 'Pago Impuestos Nacionales/Aduanales';
		break;
	case 'v':
		echo 'Pago a Proveedor';
		break;
	case 'x':
		echo 'Pago a Proveedor Moneda Extranjera';
		break;	
	case 'c':
		echo 'Suspender Cheques o Chequeras';
		break;
        case 's':
		echo 'Pago Servicio';
		break;
        case 'r':
		echo 'Recarga Servicio';
		break;    
} ?>	
</td>
</tr>
<tr>
	
	<?php if($tipo=='c') { ?>
	<td colspan="2" class="etiqueta texto_centro">Cheque(s)</td>
	<td class="etiqueta texto_centro" style="white-space:nowrap">Tipo de Operación</td>
	<td class="etiqueta texto_centro" style="white-space:nowrap">Motivo</td>
	<td class="etiqueta texto_centro">Referencia</td>
	<?php } else if(in_array($tipo,array('t','p','i','s','r'))) { ?>
	<td colspan="2" class="etiqueta texto_centro">Beneficiario</td>
	<td class="etiqueta texto_centro" style="white-space:nowrap">Tipo de Operación</td>
	<td class="etiqueta texto_centro">Referencia</td>
	<?php } else { ?>
	<td class="etiqueta texto_centro" style="white-space:nowrap">Fecha Valor</td>
	<td class="etiqueta texto_centro">Lote</td>
	<td class="etiqueta texto_centro">Descripción Lote</td>
	<td class="etiqueta texto_centro">Registros</td>
	<?php } ?>
	
	<?php if($tipo!='c') { ?>
	<td class="etiqueta texto_centro" style="white-space:nowrap">Monto</td>
	<?php } ?>
	
	<?php if(isset($mostrar_resultado)) { ?>
	<td class="etiqueta texto_centro">Resultado</td>
	<?php } ?>
</tr>
</thead>

<tbody>
<?php foreach($transacciones as $i) {?>
<tr>
	<?php if($tipo=='c') { ?>
	<td colspan="2">
		<?php
		if($i['TransaccionPendiente']['tipo']=='csi' || $i['TransaccionPendiente']['tipo']=='csx'){
			echo desencriptar($i['TransaccionPendiente']['cuenta_destino']);
		}
		else{
			list($primer,$segundo) = @explode('|', desencriptar($i['TransaccionPendiente']['cuenta_destino']));
			echo 'Desde: '.$primer.'<br/>Hasta: '.$segundo;
		}
		?>
	</td>
	<td><?php echo TransaccionPendiente::$tipo_transaccion[$i['TransaccionPendiente']['tipo']] ?></td>
	<td><?php echo desencriptar($i['TransaccionPendiente']['descripcion']) ?></td>
	<td style="text-align:center"><?php echo $i['TransaccionPendiente']['id'] ?></td>
	<?php } else if(in_array($tipo,array('t','p','i','s','r'))) { ?>
	<td colspan="2"><?php echo desencriptar($i['TransaccionPendiente']['nombre_beneficiario']) ?></td>
	<td><?php echo TransaccionPendiente::$tipo_transaccion[$i['TransaccionPendiente']['tipo']] ?></td>
	<td style="text-align:center"><?php echo $i['TransaccionPendiente']['id'] ?></td>
	<?php } else { ?>
	<td class="texto_centro"><?php echo date('d/m/Y',strtotime($i['TransaccionPendiente']['fecha_hora_valor'])) ?></td>
	<td style="text-align:center"><?php  echo $i['TransaccionPendiente']['lote'] ?></td>
	<td><?php echo desencriptar($i['TransaccionPendiente']['descripcion']) ?></td>
	<td style="text-align:center"><?php echo count($i['Beneficiarios']) ?></td>
	<?php } ?>
	
	<?php if($tipo!='c') { ?>
	<td style="text-align:right"><?php echo formatea_monto_fromWs($i['TransaccionPendiente']['monto']) ?></td>
	   <?php if (isset($transaccion['TransaccionPendiente']['resultado']) &&  $transaccion['TransaccionPendiente']['monto_divisaAdjudicado'] != $valores[1] ) {  ?>
                             <tr>
                                <td class="etiqueta">Monto Adjudicado:</td>
                                <td><?php echo $transaccion['TransaccionPendiente']['monto_divisaAdjudicado']?></td>
                            </tr>
                            <?php } ?> 
	<?php } ?>
	
	<?php if(isset($mostrar_resultado)) { ?>
	<td><?php
	if(isset($i['descripcion_error'])){
		echo '<span class="error-message">'.$i['descripcion_error'].'</span>';
	} else {
		echo 'Exitoso';
	}
	?></td>
	<?php } ?>
</tr>
<?php } ?>

</tbody>


<?php } ?>

</table>

<?php } ?>