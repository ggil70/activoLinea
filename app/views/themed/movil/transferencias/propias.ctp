<?php
/**
 * Formulario de transferencias a cuentas propias en banco activo
**/
?>
<div class="subtitulo">
<h2>Transferencia Mismo Titular en Activo</h2>
<div></div>
</div>

<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>

<div class="contenedor_formulario">
	<?php

	echo $form->create('Transferencia',
		array(
			'url'=> array(
				'controller'=>'transferencias',
				'action'=>'propias'
			),
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

	echo $html->tag(
		'table',
		null,
		array(
			'class'=>'formulario',
			'cellpadding'=>'0',
			'cellspacing'=>'0',
			'border'=>'0',
			'align'=>'center'
		)
	);
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la transferencia.</td></tr>';

	$input_debitar = array(
		'label'=>'<span class="requerido">* </span>Cuenta de Débito:',
		'type'=>'select',
		'options'=>$cuentas
	);
	
	if(count($cuentas) > 1) $input_debitar['empty'] = 'Seleccione';
	
	echo $form->input(
		'cuentaDebitar',
		$input_debitar
	);

	if ($form->isFieldError('cuentaDebitar')){
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaDebitar',
			array(
				'requerido'=>_m('seleccion.requerida',array('la cuenta de débito'))
			)
		).'</td></tr>';
	}

	$input_creditar = array(
		'label'=>'<span class="requerido">* </span>Cuenta de Crédito:',
		'type'=>'select',
		'options'=>$cuentas,
	);
	
	if(count($cuentas) > 1) $input_creditar['empty'] = 'Seleccione';
	
	echo $form->input(
		'cuentaCreditar',
		$input_creditar
	);

	if ($form->isFieldError('cuentaCreditar')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaCreditar',
				array(
					'nroCuenta'=>_m('formato.invalido',array('la cuenta de crédito')),
					'comparacion'=>_m('comparacion.cuentas',array('la cuenta destino'))
				)
			).'</td></tr>'; 
	}

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
					'numerico'=>_m('formato.invalido',array('el monto')),
					'requerido'=>_m('campo.requerido',array('el monto')),
					'montoMaximo'=> _m('monto.maximo', 'de la cuenta'),
					'montoLimiteDiario'=>_m('monto.excedido', 'de la transferencia'),
					'montoLimiteUsuario'=>_m('monto.limite.usuario', 'de la transferencia'),
				)
			).'</td></tr>';
	}

	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td>&nbsp;</td><td class="submit">';

	echo $form->submit(
		'Aceptar',
		array(
			'class'=>'button'
		)
	);		

	echo '</td></tr></table>';


	echo $form->end();
?>
</div>

<script>
$(function(){
	$('#TransferenciaPropiasForm').validate({
		rules:{
			'data[Transferencia][cuentaDebitar]':{
				required:true
			},
			'data[Transferencia][cuentaCreditar]':{
				required:true,
				notEqualTo:'#TransferenciaCuentaDebitar'
			},
			'data[Transferencia][monto]':{
				required:true, numberVE:true
			}
		},
		messages:{
			'data[Transferencia][cuentaDebitar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta de débito'), true) ?>
			},
			'data[Transferencia][cuentaCreditar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta de crédito'), true) ?>
			},
			'data[Transferencia][monto]':{
				required:<?php echo _m('campo.requerido',array('el monto'), true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto'), true) ?>
			}
		}
	});
});
</script>