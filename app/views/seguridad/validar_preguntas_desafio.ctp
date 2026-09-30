<?php
/**
 * Formulario de validación de preguntas de desafío
**/
?>
<div>
<h2 class="user"><?php echo $estado['titulo'] ?></h2>
<div class="line_title_orange"></div>
</div>

<?php for($i = 0; $i < count($estado['pasos']); $i++) { 
$class = $i==$estado['paso_actual'] ? '_actual' : '';
?>

<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
<div class="paso_desc<?php echo $class ?>"><?php echo $estado['pasos'][$i] ?></div>

<?php } ?>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<?php 

echo $form->create('RespuestaDesafio',
	array(
		'url'=> array(
			'controller'=>'seguridad',
			'action'=>'validar_preguntas_desafio'
		),
		'inputDefaults'=>array(
			'div'=>false,
			'legend'=>false,
			'label'=>'etiqueta',
			'before'=>'<tr><td class="campo">',
			'between'=>'',
			'after'=>'</td></tr>',
			'error'=>false
		)
	)
);

echo $form->hidden('indice');

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

echo '<tr><td class="mensaje">A continuación se muestra una lista de preguntas que Ud. debe responder de manera correcta.</td></tr>';

if(isset($preguntas)){
	for($i=0;$i<count($preguntas);$i++){

		echo '<tr><td class="azul"><span class="requerido">* </span>'.($i+1).' : '.$preguntas[$i].'</td></tr>';

		echo $form->input(
			"RespuestaDesafio.respuesta.{$i}",
			array(
				'label'=>'',
				'after'=>'</td>',
				'type'=>'password',
				'class'=>'uso_teclado'
			)
		);

		if ($form->isFieldError("RespuestaDesafio.respuesta.{$i}")){
				echo '<tr><td>'.$form->error("RespuestaDesafio.respuesta.{$i}").'</td></tr>';
		}

	}
}

echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

echo '<tr><td class="submit">';

echo $html->link(
	'Volver',
	$estado['url_volver'],
	array(
		'class'=>'button'
	)
);	


if(isset($preguntas)){

	echo '&nbsp;&nbsp;&nbsp;';

	echo $html->link(
		'Aceptar',
		'#',
		array(
			'div'=>false,
			'class'=>'button envio_formulario'
		)
	);	

}

echo '</td></tr></table>';



echo $form->end();

?>


<?php if(isset($preguntas)) { ?>
<script>

$(function(){
	// para la validacion
	$('#RespuestaDesafioValidarPreguntasDesafioForm').validate({
		rules:{
			<?php for($i=0;$i<count($preguntas);$i++){
				if($i > 0) echo ',';
			?>
			'data[RespuestaDesafio][respuesta][<?php echo $i?>]':{
				required:true
			}
			<?php } ?>
		},
		messages:{
			<?php for($i=0;$i<count($preguntas);$i++){
				if($i > 0) echo ',';
			?>
			'data[RespuestaDesafio][respuesta][<?php echo $i?>]':{
				required:<?php echo _m('campo.requerido',array('la respuesta'),true) ?>
			}
			<?php } ?>
		}		
	});
});



</script>

<?php } ?>

