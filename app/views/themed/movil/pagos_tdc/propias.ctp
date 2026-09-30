<div class="subtitulo">
<h2>Pagos Tarjeta de Crédito Mismo Titular en Activo</h2>
<div></div>
</div>

<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>

<div class="contenedor_formulario">

	<?php

	echo $form->create('Pagostdc',
		array(
			'url'=> array(
				'controller'=>'pagos_tdc',
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
	
	echo '<input type="hidden" id="none" />';
	
	echo $form->hidden('monto');

	echo $html->tag(
		'table',
		null,
		array(
			'class'=>'formulario',
			'cellpadding'=>'0',
			'cellspacing'=>'0',
			'border'=>'0',
			'align'=>'center',
		)
	);
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos del pago.</td></tr>';
	echo '<tr><td colspan="2"></td></tr>';

	$input_debitar = array(
		'label'=>'<span class="requerido">*</span> Cuenta de Débito:',
		'type'=>'select',
		'options'=>$cuentas
	);
	
	if(count($cuentas) > 1) $input_debitar['empty'] = 'Seleccione';
	
	echo $form->input(
		'cuentaDebitar',
		$input_debitar
	);
	
	if ($form->isFieldError('cuentaDebitar')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaDebitar', _m('una.seleccion.requerida','de las cuentas') ).'</td></tr>';
	}


	echo $form->input(
		'numeroTDC',
		array(
			'label'=>'<span class="requerido">*</span> Tarjeta Cr&eacute;dito:',
			'type'=>'select',
			'options'=>$tarjetas,
			'empty'=>'Seleccione'
		)
	);

	if ($form->isFieldError('numeroTDC')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('numeroTDC',
				array(
					'requerido'=>_m('campo.requerido','la tarjeta de crédito'),
					'numeric'=>_m('formato.invalido','la tarjeta de crédito')
				)
			).'</td></tr>';
	}

	
	echo '<tr><td class="etiqueta" colspan="2"><span class="requerido">*</span> Monto Bs.:</td><tr>';
	
	echo '<tr><td colspan="2">';
	
	echo '<label for="data[Pagostdc][tipo_pago]" class="error manual campo">'. _m('seleccion.requerida','el monto') .'</label>';
	
	echo '<label for="tipo_pago_t" class="error manual campo">'. _m('monto.excedido', 'para el pago') .'</label>';
	echo '<label for="tipo_pago_m" class="error manual campo">'. _m('monto.excedido', 'para el pago') .'</label>';
	
	if ($form->isFieldError('monto')){
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('monto', array(
				'montoLimiteDiario'=>_m('monto.excedido', 'para el pago'),
				'montoLimiteUsuario'=>_m('monto.limite.usuario', 'para el pago'),
			)
		).'</td></tr>';
	}
	
	if ($form->isFieldError('tipo_pago')){
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tipo_pago',
			array(
				'requerido'=>_m('seleccion.requerida','el monto'),
				'valoresPermitidos'=>_m('seleccion.invalida','el monto')
			)
		).'</td></tr>';
	}
?>
	<div id="wait" class="texto_centro" style="display:none;"><?php echo 'Cargando... ' . $html->image('/img/ajax-loader_mini.gif'); ?></div>
	<div id="montos_pagos">
		<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
		<tr>
			<td class="etiqueta" style="width:48%">
				<input type="hidden" name="data[Pagostdc][tipo_pago]" value="">
				<input type="radio"
					name="data[Pagostdc][tipo_pago]"
					value="t"
					<?php echo isset($this->data['Pagostdc']['tipo_pago']) && $this->data['Pagostdc']['tipo_pago']=='t' ? 'checked="checked"' : '' ?>
				/> Pago Total
			</td>
			<td class="monto"> <!-- <span id="tipo_pago_t"></span> --> <input type="text" id="tipo_pago_t" name="data[Pagostdc][tipo_pago_t]" value="" readonly="readonly" style="background-color:#FFF; cursor:text; border:none; border-radius:none; box-shadow:none;" /></td>
		</tr>

		<tr>
			<td class="etiqueta">
				<input type="radio"
					class="tipo_pago_m"
					name="data[Pagostdc][tipo_pago]"
					value="m"
					<?php echo isset($this->data['Pagostdc']['tipo_pago']) && $this->data['Pagostdc']['tipo_pago']=='m' ? 'checked="checked"' : '' ?>
				/> Pago M&iacute;nimo
			</td>
			<td class="monto"><!-- <span id="tipo_pago_m"></span> --> <input type="text" id="tipo_pago_m" name="tipo_pago_m" value="" readonly="readonly" style="background-color:#FFF; cursor:text; border:none; border-radius:none; box-shadow:none;" /></td>
		</tr>

		<tr>
			<td class="etiqueta">
			<input
				type="radio"
				name="data[Pagostdc][tipo_pago]"
				value="o"
				<?php echo isset($this->data['Pagostdc']['tipo_pago']) && $this->data['Pagostdc']['tipo_pago']=='o' ? 'checked="checked"' : '' ?>
			/> Otro Monto
			</td>
			<td class="campo"><?php echo $form->text('otro_monto',array('style'=>'width:140px','class'=>'monto pago')) ?></td>
		</tr>
		<?php
		if ($form->isFieldError('otro_monto')){
			echo '<tr><td class="etiqueta" >&nbsp;</td><td class="campo">'.$form->error(
				'otro_monto',
				array(
					'requerido'=>_m('campo.requerido','el monto'),
					'numerico'=>_m('formato.invalido','el monto'),
					'montoMaximo'	=> _m('monto.maximo', 'de la tajeta'),
					'montoLimiteDiario'=>_m('monto.excedido', 'para el pago'),
					'montoLimiteUsuario'=>_m('monto.limite.usuario', 'para el pago'),
					'deudatotal'=>_m('monto.maximo.tdc')
				)
			).'</td></tr>';
		}
		?>
		</table>
	</div>
	<div id="no_deuda" class="error-message" style="display:none;">
		<?php echo _m('sin.deuda.tdc'); ?>
	</div>
</td></tr>
<?php

	echo $form->input(
		'descripcion',
		array(
			'label'=>'<span class="requerido">*</span> Descripci&oacute;n:',
			'type'=>'text',
			'maxlength' => '30',
			'class' => 'alfanumericoEspacio',
		)
	);

	if ($form->isFieldError('descripcion')){
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('descripcion',
			array(
				'requerido'=>_m('campo.requerido','la descripción'),
				'alfanumerico'=>_m('alfanumerico.invalido','la descripción')
			)
		).'</td></tr>';
	}
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td>&nbsp;</td><td class="submit">';

	echo $form->submit(
		'Aceptar',
		array(
			'class'=>'button',
			'id'=>'aceptar'
		)
	);		

	echo '</td></tr></table>';

	echo $form->end();
?>
</div>

<script type="text/javascript">
var ajaxPendiente = false;

function acomoda_otro_monto(){
	var val = $('input[name="data[Pagostdc][tipo_pago]"]:checked').val();
	
	if(val=='o'){
		$('#PagostdcOtroMonto').removeAttr('disabled','enabled');
		$('input[name="data[Pagostdc][monto]"]').val('0');
	} else {
		$('#PagostdcOtroMonto').attr('disabled','disabled');
		$('input[name="data[Pagostdc][otro_monto]"]').val('');
		$('input[name="data[Pagostdc][monto]"]').val($('#tipo_pago_'+val).val());
		$('label.error').hide();
	}
}

function traer_saldos(){
	var valor = $('#PagostdcNumeroTDC').val();
	
	if(valor==''){	
		$('#pago_minimo,#saldo_total,#saldo_actual').text('');
		$('#wait').hide();
	}
	else {
		$.ajax({
			type: "GET",
			url: '<?php echo Router::url("/pagos_tdc/obtenerInfo/"); ?>' + valor,
			dataType: "json",
			cache:false,
			beforeSend: function() {
				ajaxPendiente = true;
				$('#wait').show();
				$('#no_deuda').hide();
			},
			success: function(source) {
				ajaxPendiente = false;
				
				$('#wait').hide();
				var flag = false;
				
				$.each(source, function(index, value){
					if(value=='NM'){
						$('.' + index).prop("disabled", true); //hide();
						value = '0,00';
					}
					else{
						$('.' + index).prop("disabled", false); //show();
					}
					
					$('#' + index).val( value );
					flag = true;
				});
				
				if(flag){
					$('#montos_pagos').show();
					$('#no_deuda').hide();
					$('#aceptar').show();
				}
				else{
					$('#montos_pagos').hide();
					$('#no_deuda').show();
					$('#aceptar').hide();
				}
				
				acomoda_otro_monto();
			},
			error: function(source){
				alert(<?php echo _m('error.fatal',null,true) ?>);
			}
		});
	}
}
	
$(function(){
	traer_saldos();
	$('#PagostdcNumeroTDC').change(traer_saldos);
	
	acomoda_otro_monto();
	$('input[name="data[Pagostdc][tipo_pago]"]').change(acomoda_otro_monto);
	
	$('#aceptar').click(function(){
		if(ajaxPendiente){
			return false;
		}
	});

	$('#PagostdcPropiasForm').validate({
		rules:{
			'data[Pagostdc][cuentaDebitar]':{
				required:true
			},
			'data[Pagostdc][numeroTDC]':{
				required:true
			},
			'data[Pagostdc][otro_monto]':{
				required:true, numberVE:true, montoMaximo2: '#tipo_pago_t'
			},
			'data[Pagostdc][tipo_pago]':{
				required:true
			},
			'data[Pagostdc][descripcion]':{
				required:true,alphaNumericSpace:true
			}
		},	
		messages:{
			'data[Pagostdc][cuentaDebitar]':{
				required:<?php echo _m('una.seleccion.requerida','de las cuentas',true) ?>
			},
			'data[Pagostdc][numeroTDC]':{
				required:<?php echo _m('una.seleccion.requerida','de las tarjeta de cr&eacute;dito',true) ?>
			},
			'data[Pagostdc][otro_monto]':{
				required:<?php echo _m('campo.requerido','el monto',true) ?>,
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				montoMaximo2: <?php echo _m('monto.maximo.tdc', null ,true) ?>
			},
			'data[Pagostdc][tipo_pago]':{
				required:<?php echo _m('campo.requerido','el monto',true) ?>
			},
			'data[Pagostdc][descripcion]':{
				required:<?php echo _m('campo.requerido','la descripci&oacute;n',true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','la descripci&oacute;n',true) ?>
			}
		}
	});
});
</script>