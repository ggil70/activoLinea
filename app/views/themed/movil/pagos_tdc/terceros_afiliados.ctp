<div class="subtitulo">
	<h2>Pagos Tarjeta de Cr&eacute;dito Tercero en Activo</h2>
	<div></div>
</div>

<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>

<div class="contenedor_formulario">

	<?php

	echo $form->create('Pagostdc',
		array(
			'url'=> array(
				'controller'=>'pagos_tdc',
				'action'=>'terceros_afiliados'
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

	echo $form->hidden('tipoDestinatario');
	echo $form->hidden('cuentaDebitar');
	echo $form->hidden('monto_maximo', array('id'=>'monto_maximo'));
	echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$monto_limite));
	echo $form->hidden('monto_limite_usuario', array('id'=>'monto_limite_usuario'));
	
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

	echo '
	<tr>
	<td class="etiqueta" colspan="2">A continuaci&oacute;n proceda a ingresar los datos del pago.</td>
	</tr>
	';

	echo '
	<tr>
	<td class="etiqueta">Cuenta de Débito:</td>
	<td> ' . enmascaraMovil($this->data['Pagostdc']['cuentaDebitar']) . ' </td>
	</tr>
	';

	echo '<tr><td colspan="2">Seleccione el beneficiario del pago y complete los datos de la operación:</td></tr>';

	
	echo '<tr><td colspan="2">'
		.'<label for="data[Pagostdc][numeroTDC]" class="error manual">'. _m('una.seleccion.requerida','de las tarjetas') .'</label>';
		if ($form->isFieldError('numeroTDC')){
			echo $form->error('numeroTDC',
				array(
					'requerido'=>_m('una.seleccion.requerida','de las tarjetas')
				)
			);
		}
	echo '</td></tr>';
	?>

	<tr>
	<td colspan="2">
	<input type="hidden" name="data[Pagostdc][numeroTDC]" value="">

	<table cellpadding="0" cellspacing="0" border="0" class="tabla">
	<thead>
	<tr>
	<td class="ancholibre texto_centro"><span class="requerido">* </span></td>
	<td>Nombre Beneficiario</td>
	<td>Tarjeta Cr&eacute;dito</td>
	</tr>
	</thead>
	<tbody>
	<?php
	if($afiliados){
		foreach ($resultados as $a){ 
	?>
	<tr>
		<td class="texto_centro ancholibre">
			<input
			type="radio"
			class="seleccion_afiliado"
			id="<?php echo 'sel'.$a['Afiliacion']['id'] ?>"
			name="data[Pagostdc][numeroTDC]"
			value="<?php echo desencriptar($a['Afiliacion']['numero_cuenta']) ?>"
			<?php if(
					isset($this->data['Pagostdc']['numeroTDC'])
					&& 0==strcmp($this->data['Pagostdc']['numeroTDC'],desencriptar($a['Afiliacion']['numero_cuenta']))
				){
				echo 'checked="checked"';
			}
			?>
			/>
			<?php 
				echo $form->hidden('seleccion', array('id'=>'monto'.$a['Afiliacion']['id'], 'value'=>empty($a['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($a['Afiliacion']['monto_maximo']) ));
			?>
		</td>
		<td><?php echo desencriptar($a['Afiliacion']['nombre_beneficiario']) ?></td>
		<td><?php echo desencriptar($a['Afiliacion']['numero_cuenta']) ?></td>
	</tr>
	<?php
		}
	?>
		<tr>
		<td colspan="3" class="paginador">
			<?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros,'tamano_paginacion'=>5)); ?>
		</td>
		</tr>
	<?php
	}
	else{
	?>
	<tr>
		<td colspan="3" class="texto_centro">No se encontraron resultados...</td>
	</tr>
	<?php
	}
	?>
	</tbody>
	</table>
	</td>
	</tr>

	<?php

	echo '<tr><td colspan="2">Indique la informaci&oacute;n que a continuación se solicita:</td></tr>';

	echo $form->input(
		'monto',
		array(
			'label'=>'<span class="requerido">*</span> Monto a pagar Bs.:',
			'type'=>'text',
			'class'=>'monto pago'
		)
	);

	if ($form->isFieldError('monto')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('monto',
				array(
					'requerido'		=> _m('campo.requerido','el monto'),
					'numerico'		=> _m('formato.invalido','el monto'),
					'montoMaximo'	=> _m('monto.maximo', 'de la tarjeta'),
					'monto_limite'=>_m('monto.limite'),
					'montoLimiteUsuario'=>_m('monto.limite.usuario','de la tarjeta')
				)
			).'</td></tr>';
	}


	echo $form->input(
		'descripcion',
		array(
			'label'=>'<span class="requerido">*</span> Descripci&oacute;n:',
			'type'=>'text',
			'maxlength' => '30',
			'class' => 'alfanumericoEspacio'
		)
	);

	if ($form->isFieldError('descripcion')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('descripcion',
				array(
					'requerido'=>_m('campo.requerido','la descripción'),
					'alfanumerico'=>_m('alfanumerico.invalido','la descripción')
				)
			).'</td></tr>';
	}
	
		echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td>&nbsp;</td><td class="botonera">';

	echo $html->link(
		'Volver',
		'terceros/v',
		array(
			'class'=>'button'
		)
	);
	
	echo '&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'javascript:',
		array(
			'class'=>'button envio_formulario'
		)
	);	
	

	echo '</td></tr></table>';

	echo $form->hidden('offset');
	echo $form->end();

	?>
</div>
<?php	
echo $form->create(
	'Pagostdc',
	array(
		'url'=> array(
			'controller'=>'pagos_tdc',
			'action'=>$this->action
		),
		'id' => 'paginacion'
	)
);
if(!empty($this->data['Pagostdc'])){
	foreach($this->data['Pagostdc'] as $k => $v) {
		echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script type="text/javascript">
$(function(){
	$('#PagostdcTercerosAfiliadosForm').validate({
		rules:{
			'data[Pagostdc][numeroTDC]':{
				required:true
			},
			'data[Pagostdc][monto]':{
				required:true,
				numberVE:true,
				montoMaximo3: '#monto_limite_usuario',
				montoMaximo2: '#monto_limite',
				montoMaximo: '#monto_maximo'
				
			},
			'data[Pagostdc][descripcion]':{
				required:true,alphaNumericSpace:true
			}
		},
		messages:{
			'data[Pagostdc][monto]':{
				required:<?php echo _m('campo.requerido','el monto',true) ?>,
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				montoMaximo:<?php echo _m('monto.maximo', 'de la tarjeta' ,true) ?>,
				montoMaximo2: <?php echo _m('monto.excedido', 'para el pago' ,true) ?>,
				montoMaximo3: <?php echo _m('monto.limite.usuario', 'para el pago' ,true) ?>
			},
			'data[Pagostdc][descripcion]':{
				required:<?php echo _m('campo.requerido','la descripci&oacute;n',true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','la descripci&oacute;n',true) ?>
			}
		}	
	});
});
</script>
