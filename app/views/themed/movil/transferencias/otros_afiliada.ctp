<?php
/**
 * Formulario de transferencias propias a otros bancos para cuentas afiliadas
**/
?>
<div class="subtitulo">
<h2>Transferencia Mismo Titular Otro Banco</h2>
<div></div>
</div>

<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>

<div class="contenedor_formulario">
	<?php

	echo $form->create('Transferencia',
		array(
			'url'=> array(
				'controller'=>'transferencias',
				'action'=>'otros_afiliada'
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
			'style'=>'table-layout:fixed'
		)
	);
	
	// La primera fila define la tabla con layout fixed
	echo '<tr><td style="width:25%"></td><td style="width:75%"></td></tr>';
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la transferencia.</td></tr>';

	echo '
	<tr>
	<td class="etiqueta">Cuenta de Débito:</td>
	<td> ' . enmascaraMovil($this->data['Transferencia']['cuentaDebitar']) . ' </td>
	</tr>
	';

	echo '<tr><td colspan="2">'
		.'<label for="data[Transferencia][cuentaCreditar]" class="error manual">'._m('seleccion.requerida',array('la cuenta del beneficiario')).'</label>';
		if ($form->isFieldError('cuentaCreditar')){
			echo $form->error('cuentaCreditar',
				array(
					'nroCuenta'=>_m('formato.invalido',array('el beneficiario')),
					'requerido'=>_m('campo.requerido',array('el beneficiario')),
					'banco'=>_m('formato.invalido',array('el número de cuenta'))
				)			
			);
		}
	echo '</td></tr>';

	echo '<tr><td colspan="2">Seleccione el beneficiario de la transferencia y complete los datos de la operación:</td></tr>';
	?>

	<tr>
	<td colspan="2">

	<input type="hidden" name="data[Transferencia][cuentaCreditar]" value="">

	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tabla">
	<thead>
	<tr>
	<td class="ancholibre texto_centro"><span class="requerido">* </span></td>
	<td>Banco Beneficiario</td>
	<td>Número Cuenta</td>
	</tr>
	</thead>
	<tbody>
	<?php
	if($afiliados){
	foreach($resultados as $i){

	?>
	<tr>
	<td class="texto_centro ancholibre">
		<input
			type="radio"
			class="seleccion_afiliado"
			id="<?php echo 'sel'.$i['Afiliacion']['id'] ?>"
			name="data[Transferencia][cuentaCreditar]"
			value="<?php echo desencriptar($i['Afiliacion']['numero_cuenta']) ?>"
			<?php if(
					isset($this->data['Transferencia']['cuentaCreditar'])
					&& 0==strcmp($this->data['Transferencia']['cuentaCreditar'],desencriptar($i['Afiliacion']['numero_cuenta']))
				){
				echo 'checked="checked"';
			}
			?>
		/>
			<?php 
				echo $form->hidden('seleccion', array('id'=>'monto'.$i['Afiliacion']['id'], 'value'=>empty($i['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($i['Afiliacion']['monto_maximo']) ));
			?>
	</td>
	<td><?php echo $i['Afiliacion']['banco'] ?></td>
	<td><?php echo desencriptar($i['Afiliacion']['numero_cuenta']) ?></td>
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
	}else {
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

	echo '<tr><td colspan="2">Indique la información que a continuaci&oacute;n se solicita:</td></tr>';

	echo $form->input(
		'monto',
		array(
			'label'=>'<span class="requerido">* </span>Monto a transferir Bs.:',
			'type'=>'text',
			'class'=>'monto pago'
		)
	);

	if ($form->isFieldError('monto')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('monto',
				array(
					'numerico'=>_m('formato.invalido',array('el monto')),
					'requerido'=>_m('campo.requerido',array('el monto')),
					'montoMaximo'	=> _m('monto.maximo', 'de la cuenta'),
					'montoLimiteDiario'=>_m('monto.excedido', 'de la transferencia')
				)			
			).'</td></tr>';
	}

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
				)).'</td></tr>';
	}
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

	echo '<tr><td>&nbsp;</td><td class="submit">';

	echo $html->link(
		'Volver',
		'otros/v',
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

	echo $form->end();

	?>

</div>

<?php	
echo $form->create(
	'Transferencia',
	array(
		'url'=> array(
			'controller'=>'transferencias',
			'action'=>$this->action
		),
		'id' => 'paginacion'
	)
);
if(!empty($this->data['Transferencia'])){
	foreach($this->data['Transferencia'] as $k => $v) {
		echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script>
$(function(){
	$('#TransferenciaOtrosAfiliadaForm').validate({
		rules:{
			'data[Transferencia][cuentaCreditar]':{
				required:true
			},
			'data[Transferencia][monto]':{
				required:true,
				numberVE:true,
				montoMaximo3: '#monto_limite_usuario',
				montoMaximo2: '#monto_limite',
				montoMaximo: '#monto_maximo'
			},
			'data[Transferencia][descripcion]':{
				required:true, alphaNumericSpace:true
			}
		},
		messages:{
			'data[Transferencia][cuentaCreditar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta de débito'),true) ?>
			},
			'data[Transferencia][monto]':{
				required:<?php echo _m('seleccion.requerida',array('el monto'),true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto'),true) ?>,
				montoMaximo:<?php echo _m('monto.maximo', 'de la cuenta' ,true) ?>,
				montoMaximo2: <?php echo _m('monto.excedido', 'de la transferencia' ,true) ?>,
				montoMaximo3: <?php echo _m('monto.limite.usuario', 'de la transferencia' ,true) ?>
			},
			'data[Transferencia][descripcion]':{
				required:<?php echo _m('campo.requerido','la descripci&oacute;n',true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','la descripci&oacute;n',true) ?>
			}
		}	
	});
});
</script>