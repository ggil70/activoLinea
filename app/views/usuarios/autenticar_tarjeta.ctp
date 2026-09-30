<?php
/**
 * Formulario para autenticación de tarjeta de débito, el cual es utilizado en el
 * proceso de registro
**/
?>

<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
	<div class="paso_nro_actual">1</div>
	<div class="paso_desc_actual">Autenticaci&oacute;n del cliente</div>
	<div class="paso_nro">2</div>
        <div class="paso_desc">Aceptaci&oacute;n de Contrato</div>	
	<div class="paso_nro">3</div>
	<div class="paso_desc">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Código de Autorización</div>
        <div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">7</div>
	<div class="paso_desc">Configuraci&oacute;n de L&iacute;mites</div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	
	<?php
	
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'usuarios',
				'action'=>'autenticar_tarjeta'
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
		)
	);
	
	?>
	
	<tr><td colspan="2" class="mensaje">Ingrese los datos solicitados y luego presione el bot&oacute;n Aceptar.</td></tr>
	
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr><td class="etiqueta">Tarjeta:</td><td class="campo">Tarjeta de D&eacute;bito (Últimos 6 dígitos)</td></tr>
	
	<?php
	
	echo $form->input(
		'nroTarjeta',
		array(
			'label'=>'N&uacute;mero de Tarjeta:',
			'type'=>'text',
			'maxlength'=>'6',
			'class'=>'numerico'
		)
	);

	if ($form->isFieldError('nroTarjeta')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('nroTarjeta',
			array(
				'nroTarjeta'=>_m('formato.invalido', array('el número de tarjeta')),
				'requerido'=>_m('campo.requerido', array('el número de tarjeta'))
			)
		).'</td></tr>';
	}
	
	echo '<tr><td colspan="2">&nbsp;</td></tr>';
	
	echo '<tr><td class="submit" colspan="2">';
	
	
	echo $html->link(
		'Volver',
		'autenticar_cedula',
		array(
			'class'=>'button'
		)
	);
	
	echo '&nbsp;&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'#',
		array(
			'class'=>'button envio_formulario',
			'div' => false
		)
	);
	
	echo '</td></tr></table>';
	
	
	echo $form->end();
	
	?>
</div>

<script>
$(function(){
	// para la validacion
	$('#UsuarioAutenticarTarjetaForm').validate({
		rules:{
			'data[Usuario][nroTarjeta]':{
				required:true,
				digits:true,
				minlength:6,
				maxlength:6
			}
		},
		messages:{
			'data[Usuario][nroTarjeta]':{
				required: <?php echo _m('campo.requerido', array('el número de tarjeta'), true) ?>,
				digits: <?php echo _m('formato.invalido', array('el número de tarjeta'), true) ?>,
				minlength: <?php echo _m('longitud.exacta',array(6,'el número de tarjeta'), true) ?>,
				maxlength: <?php echo _m('longitud.exacta',array(6,'el número de tarjeta'), true) ?>
			}
		}
	});
});
</script>
