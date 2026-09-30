<?php
/**
 * Formulario inicial de recarga servicios
**/
?>
<div>
<h2 class="user">Recarga Simpletv Prepago</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario_400">
	<?php        
	echo $form->create('RecargaSimpletv',
		array(
			'url'=> array(
				'controller'=>'recarga_simpletv',
				'action'=>'recarga_simpletv'
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
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la recarga.</td></tr>';
	echo '<tr><td colspan="2"></td></tr>';
        
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
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaDebitar', _m('una.seleccion.requerida', 'de las cuentas')).'</td></tr>';
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

<script>


$(function(){

	$('#RecargaSimpletvRecargaSimpletvForm').validate({		
		rules:{
			'data[RecargaSimpletv][cuentaDebitar]':{
				required:true
			}
		},	
		messages:{
			'data[RecargaSimpletv][cuentaDebitar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta de débito'),true) ?>
			}
		}	
	});
});

</script>