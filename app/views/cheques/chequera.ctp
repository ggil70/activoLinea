<?php
    /**
     * Vista para la suspensión de chequeras
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
				'action'=>'chequera'
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
			'style'=>'table-layout:fixed'
		)
	);
	
	// La primera fila define la tabla con layout fixed
	echo '<tr><td style="width:130px"></td><td style="width:400px"></td></tr>';

	echo '<tr><td colspan="2"></td></tr>';
	
	echo '<tr><td class="etiqueta">Número de Cuenta:</td><td>'.enmascara($this->data['Cheque']['cuenta']).'</td></tr>';

?>

<tr><td colspan="2">Para ver el detalle de los Cheques Haga clic sobre el número del primer cheque</td></tr>
<tr><td colspan="2">
<input type="hidden" name="data[Cheque][chequeras][]">
<label for="data[Cheque][chequeras][]" class="error manual"><?php echo _m('seleccion.requerida','las chequeras') ?></label>

<?php 
if ($form->isFieldError('chequeras')){
	echo $form->error('chequeras', array(
		'requerido'=>_m('seleccion.requerida','las chequeras'),
	));
}
?>

<table class="tabla" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto" width="100%">
<thead>
<tr>
<td>Fecha de Activaci&oacute;n</td>
<td>&Uacute;ltimos 6 dígitos Primer Cheque</td>
<td>Total de Cheques</td>
<td>Cheques Usados</td>
<td>Cheques sin usar</td>
<td>Cheques Suspendidos</td>
<td>Suspender</td>
</tr>
</thead>
<tbody>
<?php
if(count($arrData)){
foreach($arrData as $md){
$chequera = str_pad($md['e01CHMICK'],6,'0',STR_PAD_LEFT);

?>
<tr>
<td class="texto_centro"><?php echo formatea_fecha($md['e01CHMDED'],$md['e01CHMDEM'],$md['e01CHMDEY']); ?></td>
<td class="texto_centro"><a href="javascript:ver_cheques('<?php echo str_pad($md['e01CHMICK'],6,'0',STR_PAD_LEFT);?>');void(0)"><?php echo str_pad($md['e01CHMICK'],6,'0',STR_PAD_LEFT);?></a>
</td>
<td class="monto"><?php echo $md['e01CHMNTC'] ?></td>
<td class="monto"><?php echo $md['e01CHMUCK'] ?></td>
<td class="monto"><?php echo $md['e01CHMDCK'] ?></td>
<td class="monto"><?php echo $md['e01CHMSCK'] ?></td>
<td class="texto_centro">
<input
	type="checkbox"
	name="data[Cheque][chequeras][]"
	value="<?php echo $chequera ?>"
	<?php
	if(isset($this->data['Cheque']['chequeras'])){
		$chequeras = $this->data['Cheque']['chequeras'];
		
		if(in_array($chequera, $chequeras)){
			echo 'checked="checked"';
		}
	}
	?>

</td>
</tr>
<?php }
}
else {
	?>
		<tr>
			<td colspan= "7" class="texto_centro">No se encontraron resultados</td>
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
			'label'=>'<span class="requerido">* </span>Especifique:',
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
	
	echo '<td class="submit" colspan="2">';	
	
	echo $html->link(
		'Volver',
		'index',
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
	
<form id="form_cheques" method="post" action="<?php echo Router::url('/cheques/cheques') ?>">
<input type="hidden" name="data[Cheque][chequera]">
</form>	
</div>

<script>
function ver_cheques(chequera){
	$('#form_cheques input[name="data[Cheque][chequera]"]').val(chequera);
	$('#form_cheques').submit();
}

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
	
	$('#ChequeChequeraForm').validate({
		rules:{
			'data[Cheque][chequeras][]':{
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