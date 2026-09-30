<?php
    /**
     * Vista para la selección del cheque o los cheques a suspender de una chequera
     */
?>

<div>
<h2 class="user">Suspender Cheques/Chequeras</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<?php

	echo $form->create('Cheque',
		array(
			'url'=> array(
				'controller'=>'cheques',
				'action'=>'cheques'
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
			'width'=>'600px',
			'align'=>'center'
		)
	);

	echo '<tr><td colspan="2"></td></tr>';
?>
<tr><td class="etiqueta" width="15%">Número de Cuenta:</td><td><?php echo enmascara($this->data['Cheque']['cuenta']) ?></td></tr>
<tr><td class="etiqueta">&Uacute;ltimos 6 dígitos Primer Cheque:</td><td><?php echo str_pad($chequera['e01CHMICK'],8,'0',STR_PAD_LEFT); ?></td></tr>
<tr><td colspan="2">
<input type="hidden" name="data[Cheque][cheques]">
<label for="data[Cheque][cheques][]" class="error manual"><?php echo _m('seleccion.requerida','al menos un cheque') ?></label>

<table class="tabla" cellpadding="0" cellspacing="0" border="0" width="100%">
<thead>
<tr>
<td>&Uacute;ltimos 6 dígitos del Cheque:</td>
<td>Estatus:</td>
<td>&nbsp;</td>
</tr>
</thead>
<tbody>

<?php
if(count($disChe)){
foreach($disChe as $ch){

	$numero = $ch;
	$cheque = str_pad($numero,6,'0',STR_PAD_LEFT);
?>
<tr>
<td><?php echo $cheque; ?></td>
<td><?php echo $estatus = 'Disponible'?></td>
<td style="text-align:center;">
<input
	type="checkbox"
	name="data[Cheque][cheques][]"
	value="<?php echo $cheque;?>"
	<?php 
	if(isset($this->data['Cheque']['cheques'])){
		$cheques = $this->data['Cheque']['cheques'];
		if(in_array($cheque, $cheques)){
			echo 'checked="checked"';
		}
	}
	?>
>
</td>
</tr>
<?php }
}else {
	?>
		<tr>
			<td colspan= "3" class="texto_centro">No se encontraron resultados</td>
		</tr>
<?php
}
?>
</tbody>
</table>

</td></tr>

<?php
	
	echo $form->input(
		'tipo_motivo',
		array(
			'label'=>'<span class="requerido">* </span>Motivo:',
			'type'=>'select',
			'options'=>$motivos,
			'empty'=>'Seleccione'
		)
	);
	
	if ($form->isFieldError('tipo_motivo')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('tipo_motivo', array(
			'requerido'=>_m('seleccion.requerida','el motivo'),
			'valoresPermitidos'=>_m('seleccion.invalida','el motivo')
		)).'</td></tr>';
	}
	
	echo $form->input(
		'motivo',
		array(
			'label'=>'Especifique:',
			'type'=>'text',
			'maxlength'=>30,
			'class'=>'letrasEspacio'
		)
	);
	
	if ($form->isFieldError('motivo')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('motivo', array(
			'requerido'=>_m('campo.requerido','el motivo'),
			'maxLength'=>_m('longitud.maxima',array('el motivo','30','caracteres'))
		)).'</td></tr>';
	}
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td>&nbsp;</td><td class="submit">';
	
	echo $html->link(
		'Volver',
		'chequera',
		array(
			'class'=>'button'
		)
	);
	
	echo '&nbsp;&nbsp;';	
	
	echo $html->link(
		'Aceptar',
		'#',
		array(
			'class'=>'button envio_formulario'
		)
	);

	echo '</td></tr></table>';

	echo $form->end();

	?>
</div>

<script>
function ajustar(){
	var val = $('#ChequeTipoMotivo').val();
	
	if(val == 'o'){
		$('#ChequeMotivo').parents('tr').first().show();
	} else {
		$('#ChequeMotivo').parents('tr').first().hide();
	}
}

$(function(){
	ajustar();
	$('#ChequeTipoMotivo').change(ajustar);
	
	$('#ChequeChequesForm').validate({
		rules:{
			'data[Cheque][cheques][]':{
				required:true
			},
			'data[Cheque][tipo_motivo]':{
				required:true
			},
			'data[Cheque][motivo]':{
				required:true,
				maxlength:30
			}
		},	
		messages:{
			'data[Cheque][tipo_motivo]':{
				required:<?php echo _m('seleccion.requerida','el motivo',true) ?>
			},	
			'data[Cheque][motivo]':{
				required:<?php echo _m('campo.requerido','el motivo',true) ?>,
				maxlength:<?php echo _m('longitud.maxima',array('el motivo',30,'caracteres'),true) ?>
			}	
		}
	});
});
</script>