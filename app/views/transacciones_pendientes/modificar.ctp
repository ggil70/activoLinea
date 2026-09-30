<?php
/**
 * Formulario de modificación de pagos y transferencias
**/
?>
<div>
<h2 class="user">Aprobaciones Pendientes - Modificar</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Modificación de Operación</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de la Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<?php

echo $form->create('TransaccionPendiente',
	array(
		'url'=> '/transacciones_pendientes/modificar',
		'id'=> 'TransaccionPendienteModificarForm',
		'inputDefaults'=>array(
			'div'=>false,
			'label'=>'etiqueta',
			'before'=>'<tr><td class="etiqueta">',
			'between'=>'</td><td class="campo">',
			'after'=>'</td></tr>',
			'error'=>false
		)
	)
);

?>

<table class="formulario" cellpadding="0" cellspacing="0" border="0" align="center">
<tbody>
<tr>
<td colspan="2"><span class="estiloTexto">A continuación proceda a ingresar los datos del pago.</span></td>
</tr>
<tr><td colspan="2"></td></tr>
<tr>
<td class="etiqueta"><span class="estiloTexto">Tipo Operación</span></td>
<td><?php echo TransaccionPendiente::$tipo_transaccion[$transaccion['TransaccionPendiente']['tipo']] ?></td>
</tr>
<tr>
<td class="etiqueta"><span class="estiloTexto">Fecha Registro</span></td>
<td><?php echo date('d/m/Y',strtotime($transaccion['TransaccionPendiente']['fecha_hora_registro'])) ?></td>
</tr>
<tr>
<td class="etiqueta"><span class="estiloTexto">Usuario Registro</span></td>
<td><?php echo desencriptar($transaccion['UsuarioCreador']['login']) ?></td>
</tr>
<tr>
<td class="etiqueta"><span class="estiloTexto">Referencia</span></td>
<td><?php echo $transaccion['TransaccionPendiente']['id'] ?></td>
</tr>
<tr><td colspan="2"></td></tr>
<?php

$input_debitar = array(
	'label'=>'<span class="requerido">* </span>Cuenta Débito:',
	'type'=>'select',
	'options'=>$cuentas
);

if(count($cuentas) > 1) $input_debitar['empty'] = 'Seleccione';

echo $form->input(
	'cuenta_origen',
	$input_debitar
);


if ($form->isFieldError('cuenta_origen')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('cuenta_origen',
		array(
			'requerido'=>_m('seleccion.requerida',array('la cuenta de débito'))
		)
	).'</td></tr>';
}

// Dependiendo del tipo de transacción habilitamos el combo de destino

switch($transaccion['TransaccionPendiente']['tipo']){
case 'tpm':
// Para transferencias propias mismo banco se puede cambiar la cuenta destino

$input_creditar = array(
	'label'=>'<span class="requerido">* </span>Cuenta Crédito:',
	'type'=>'select',
	'options'=>$cuentas,
);

if(count($cuentas) > 1) $input_creditar['empty'] = 'Seleccione';

echo $form->input(
	'cuenta_destino',
	$input_creditar
);

if ($form->isFieldError('cuenta_destino')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('cuenta_destino',
		array(
			'nroCuenta'=>_m('formato.invalido',array('la cuenta de crédito')),
			'comparacion'=>_m('comparacion.cuentas',array('la cuenta destino'))
		)
	).'</td></tr>'; 
}
	
	
break;
case 'ppm':
// Para pago tdc propias mismo banco se puede cambiar la tdc destino

echo $form->input(
	'cuenta_destino',
	array(
		'label'=>'<span class="requerido">*</span> N&uacute;mero Tarjeta de Cr&eacute;dito:',
		'type'=>'select',
		'options'=>$tarjetas,
		'empty'=>'Seleccione'
	)
);

if ($form->isFieldError('cuenta_destino')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('cuenta_destino',
		array(
			'requerido'=>_m('campo.requerido','la tarjeta de crédito'),
			'tdc'=>_m('valor.invalido','la tarjeta de crédito'),
			'nroCuenta'=>_m('valor.invalido',array('el número de cuenta')),
			'banco'=>_m('valor.invalido',array('el número de cuenta')),
			'comparacion'=>_m('comparacion.cuentas',array('la cuenta destino'))			
		)
	).'</td></tr>';
}

break;
case 'tpo':
case 'ttm':
case 'tto':
// No se puede cambiar el destino. Mostrar read only
?>
<tr>
<td class="etiqueta"><span class="estiloTexto">Cuenta Beneficiario</span></td>
<td>
<?php echo enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])) ?>
</td>
</tr>
<?php
break; 
case 'ppo':
case 'ptm':
case 'pto':
// No se puede cambiar el destino. Mostrar read only
?>
<tr>
<td class="etiqueta"><span class="estiloTexto">Número Tarjeta de Crédito</span></td>
<td>
<?php echo enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])) ?>
</td>
</tr>
<?php
break;	
}

?>

<?php if(!empty($transaccion['TransaccionPendiente']['banco'])){ ?>
<tr>
<td class="etiqueta"><span class="estiloTexto">Banco del Beneficiario</span></td>
<td>
<?php echo $transaccion['TransaccionPendiente']['banco'] ?>
</td>
</tr>
<?php } ?>

<?php if(!in_array($transaccion['TransaccionPendiente']['tipo'],array('tpm','ppm'))) { ?>
<tr>
<td class="etiqueta"><span class="estiloTexto">Nombre del Beneficiario</span></td>
<td>
<?php echo desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']) ?>
</td>
</tr>
<tr>
<td class="etiqueta"><span class="estiloTexto">Cédula / RIF</span></td>
<td><?php echo $transaccion['TransaccionPendiente']['tipo_id_beneficiario'].'-'.desencriptar($transaccion['TransaccionPendiente']['id_beneficiario']) ?></td>
</tr>
<?php } ?>

<?php if($transaccion['TransaccionPendiente']['tipo'][1] != 'p') { ?>
<tr>
<td class="etiqueta"><span class="estiloTexto">E-mail Beneficiario</span></td>
<td><?php echo desencriptar($transaccion['TransaccionPendiente']['email_beneficiario']) ?></td>
</tr>
<?php } ?>

<?php

echo $form->input(
	'monto',
	array(
		'label'=>'<span class="requerido">* </span>Monto Bs.:',
		'type'=>'text',
		'class'=>'monto pago'
	)
);

if ($form->isFieldError('monto')){
	echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('monto',
		array(
			'requerido'=>_m('campo.requerido',array('el monto')),
			'numerico'=>_m('formato.invalido',array('el monto')),
			'montoMaximo'	=> _m('monto.maximo', 'de la cuenta'),
			'montoLimiteDiario'=>_m('monto.excedido', 'de la transferencia')
		)
	).'</td></tr>';
}

if($transaccion['TransaccionPendiente']['tipo'] != 'tpm') {
	echo $form->input(
		'descripcion',
		array(
			'label'=>'<span class="requerido">* </span>Descripci&oacute;n:',
			'type'=>'text',
			'maxlength' => '30',
			'class'=>'alfanumericoEspacio'
		)
	);

	if ($form->isFieldError('descripcion')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('descripcion',
			array(
				'requerido'=>_m('campo.requerido',array('la descripción')),
				'alfanumerico'=>_m('alfanumerico.invalido',array('la descripción'))
			)
		).'</td></tr>';
	}
}

echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

?>

<tr>
<td class="botonera" colspan="2">
<?php

echo $html->link(
	'Volver',
	'detalle/v',
	array(
		'class'=>'button',
		'id'=>'volver'						
	)
);

echo '&nbsp;&nbsp;&nbsp;';

echo $html->link(
	'Aceptar',
	'javascript:',
	array(
		'class'=>'button envio_formulario',
		'id'=>'aceptar'						
	)
);

?>

</td>
</tr>


</table>


<?php echo $form->end() ?>


</div>

<script>
$(function(){
	
	$('#TransaccionPendienteModificarForm').validate({
		rules:{
			'data[TransaccionPendiente][cuenta_origen]':{
				required:true
			},
			'data[TransaccionPendiente][cuenta_destino]':{
				required:true,
				notEqualTo:'#TransferenciaPendienteModificiarCuentaDestino'
			},
			'data[TransaccionPendiente][monto]':{
				required:true, numberVE:true
			},
			'data[TransaccionPendiente][descripcion]':{
				required:true, alphaNumericSpace:true
			}
		},
		messages:{
			'data[TransaccionPendiente][cuenta_origen]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta de débito'), true) ?>
			},
			'data[TransaccionPendiente][cuentaCreditar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta de crédito'), true) ?>
			},
			'data[TransaccionPendiente][monto]':{
				required:<?php echo _m('campo.requerido',array('el monto'), true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto'), true) ?>
			},
			'data[TransaccionPendiente][descripcion]':{
				required:<?php echo _m('campo.requerido','la descripci&oacute;n',true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','la descripci&oacute;n',true) ?>
			}
		}
	});	
	
});
</script>