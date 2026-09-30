<?php
/**
 * Formulario de selección de tipo de olvido
**/
?>
<div>
<h2 class="user"><?php echo $titulo ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Tipo de Olvido</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Autenticación del Cliente</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<?php

	echo $form->create('Olvido',
		array(
			'url'=> array(
				'controller'=>'seguridad',
				'action'=>'olvido_tipo'
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
			'border'=>'0'
		)
	);
	
	echo '<tr><td colspan="2" class="mensaje">A continuación usted puede recuperar Usuario, Clave, Sello Personal o Preguntas y Respuestas de Desafío</td></tr>';
	
	$tipos_olvido = array(
		'clave'=>'Clave',
		'sello'=>'Imagen Antiphishing o Sello Personalizado',
		'usuario'=>'Usuario',
		'preguntas'=>'Preguntas y Respuestas Desafío'
	);
	
	echo $form->input(
		'tipo',
		array(
			'label'=>'<span class="requerido">* </span>Olvidó su:',
			'type'=>'select',
			'options'=>$tipos_olvido,
			'empty'=>'Seleccione'
		)
	);

	if ($form->isFieldError('tipo')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('tipo',
			array(
				'requerido'=>_m('campo.requerido',array('el tipo de olvido'))
			)
		).'</td></tr>';
	}	
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td>&nbsp;</td><td class="submit">';
	
	echo $html->link(
		'Volver',
		'/',
		array(
			'class'=>'button'
		)
	);

	echo '&nbsp;&nbsp;&nbsp;';

	echo $html->link(
		'Aceptar',
		'#',
		array(
			'div'=>false,
			'class'=>'button envio_formulario'
		)
	);

	echo '</td></tr></table>';

	echo $form->end();

	?>

</div>


<script>
$(function(){
	$('#OlvidoOlvidoTipoForm').validate({
		rules:{
			'data[Olvido][tipo]':{
				required:true
			}
		},	
		messages:{
			'data[Olvido][tipo]':{
				required:<?php echo _m('campo.requerido',array('el tipo de olvido'),true) ?>
			}
		}	
	});
});
</script>
