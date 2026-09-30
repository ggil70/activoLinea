<?php
/**
 * Formulario de anulación de tarjeta de coordenadas. Donde se solicita el motivo
**/
?>
<div>
<h2 class="user">Anular Tarjeta de Coordenadas</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Motivo de Anulaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<?php if($ya_tiene_tdc){

	echo $form->create('SolicitudTdc',
		array(
			'url'=> array(
				'controller'=>'solicitudes',
				'action'=>'anular_tc_motivo'
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
	echo '<tr><td style="width:100px"></td><td style="width:250px"></td></tr>';

	echo '<tr><td colspan="2">Ingrese el motivo de la anulaci&oacute;n a continuaci&oacute;n:</td></tr>';
	
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
				'requerido'=>_m('seleccion.requerida','el motivo')
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
	
	echo '<tr><td>&nbsp;</td><td class="botonera">';

	echo $form->button(
		'Aceptar',
		array(
			'class'=>'button',
			'id'=>'aceptar'
		)
	);

	echo '</td></tr></table>';
	
	echo $form->end();
} else {?>
<p class="mensaje">Estimado Cliente, usted no posee una Tarjeta de Coordenadas activa.</p>
<?php } ?>
</div>

<script>
function ajustar(){
	var val = $('#SolicitudTdcTipoMotivo').val();
	
	if(val == 'o'){
		$('#SolicitudTdcMotivo').parents('tr').first().show();
	} else {
		$('#SolicitudTdcMotivo').parents('tr').first().hide();
	}
}

$(function(){
	ajustar();
	$('#SolicitudTdcTipoMotivo').change(ajustar);
	
	// para la validacion
	$('#SolicitudTdcAnularTcMotivoForm').validate({
		rules:{
			'data[SolicitudTdc][tipo_motivo]':{
				required:true
			},
			'data[SolicitudTdc][motivo]':{
				required:true,
				maxlength:30
			}
		},
		messages:{
			'data[SolicitudTdc][tipo_motivo]':{
				required:<?php echo _m('campo.requerido','el motivo',true) ?>
			},
			'data[SolicitudTdc][motivo]':{
				required:<?php echo _m('campo.requerido','el motivo',true) ?>,
				maxlength:<?php echo _m('longitud.maxima',array('el motivo','30','caracteres'),true) ?>
			}
		}
	});
	
});
</script>
