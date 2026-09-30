<?php
    /**
     * Vista para la suspensión de cheques o chequeras
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
				'action'=>'index'
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

	echo '<tr><td colspan="2"></td></tr>';
	
	
	echo $form->input(
		'cuenta',
		array(
			'label'=>'<span class="requerido">* </span>Número de Cuenta:',
			'type'=>'select',
			'options'=>$cuentas,
			'empty'=>'Seleccione'
		)
	);

	if ($form->isFieldError('cuenta')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('cuenta', _m('una.seleccion.requerida', 'de las cuentas')).'</td></tr>';
	}

	echo $form->input(
		'tipo',
		array(
			'label'=>' ',
			'type'=>'radio',
			'legend'=>false,
			'separator'=>'<br>',
			'between'=>'</td><td class="campo">',
			'after'=>'',
			'options'=> array(
				'c' => 'Cheque',
				'h' => 'Chequera',
				'r' => 'Rango de Cheque'
			),
			'before'=>'<tr><td class="etiqueta" style="text-align:right;"><span class="requerido">* </span>'
		)
	);
	
	
		echo '<label for="data[Cheque][tipo]" class="error manual"><br>'. _m('seleccion.requerida','el tipo de suspensión') .'</label></td></tr>';
		
		if ($form->isFieldError('tipo')){
			echo '<tr><td>&nbsp;</td><td>';
			echo $form->error('tipo',
				array(
					'requerido'=>_m('seleccion.requerida','el tipo de suspensión'),
					'valoresPermitidos'=>_m('formato.invalido','el tipo de suspensión')
				)
			);
			echo '</td></tr>';
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
	$('#ChequeIndexForm').validate({
		rules:{
			'data[Cheque][cuenta]':{
				required:true
			},
			'data[Cheque][tipo]':{
				required:true
			}
		},	
		messages:{
			'data[Cheque][cuenta]':{
				required:<?php echo _m('una.seleccion.requerida','de las cuentas',true) ?>
			}	
		}
	});
});
</script>