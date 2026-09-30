<?php
    /**
     *   Vista para la suspensión de cheque
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
				'action'=>'cheque'
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
			'align'=>'center',
			'style'=>'table-layout:fixed'
		)
	);
	
	// La primera fila define la tabla con layout fixed
	echo '<tr><td style="width:180px"></td><td style="width:250px"></td></tr>';

	echo '<tr><td colspan="2"></td></tr>';
	
	echo '<tr><td class="etiqueta"><span class="estiloTexto"></span>Número de Cuenta:</td><td>'.enmascara($this->data['Cheque']['cuenta']).'</td></tr>';

	echo $form->input(
		'cheque',
		array(
			'label'=>'<span class="requerido">* </span>Últimos 6 dígitos del Cheque:',
			'type'=>'text',
			'maxlength'=>6,
			'class'=>'numerico'
		)
	);
	
	if ($form->isFieldError('cheque')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('cheque', array(
			'requerido'=>_m('campo.requerido','el cheque'),
			'numeric'=>_m('formato.invalido','el cheque'),
			'maxLength'=>_m('longitud.maxima',array('el cheque','6','dígitos'))
		)).'</td></tr>';
	}
	
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
	
	echo '<tr><td>&nbsp;</td><td class="submit">';
	
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
	
	$('#ChequeChequeForm').validate({
		rules:{
			'data[Cheque][cheque]':{
				required:true,
				digits:true
			},
			'data[Cheque][cuenta]':{
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
			'data[Cheque][cuenta]':{
				required:<?php echo _m('una.seleccion.requerida','de las cuentas',true) ?>
			},	
			'data[Cheque][tipo_motivo]':{
				required:<?php echo _m('seleccion.requerida','el motivos',true) ?>
			},	
			'data[Cheque][motivo]':{
				required:<?php echo _m('campo.requerido','el motivo',true) ?>,
				maxlength:<?php echo _m('longitud.maxima',array('el motivo',30,'caracteres'),true) ?>
			},	
			'data[Cheque][cheque]':{
				required:<?php echo _m('campo.requerido','el cheque',true) ?>,
				digits:<?php echo _m('formato.invalido','el cheque',true) ?>
			}	
		}
	});
});
</script>