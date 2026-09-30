<?php
/**
 * Formulario de validación de preguntas de autenticación
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
<?php if(!empty($estado['preguntas'])){ ?>
<div class="contenedor_formulario">

<?php 

echo $form->create(
	'RespuestasAutenticacion',
	array(
		'url'=> array(
			'controller'=>'seguridad',
			'action'=>'validar_preguntas_autenticacion'
		)
	)
);

?>

<table class="formulario" cellpadding="0" cellspacing="0" border="0"  style="width:450px; margin: 0px auto">
	
<tr>
<td class="mensaje">A continuaci&oacute;n se muestra una lista de preguntas que Ud. debe responder de manera correcta.</td>
</tr>

<?php

for($i=0;$i<count($estado['preguntas']);$i++){
	$pregunta = $estado['preguntas'][$i];
	
	echo '<!--'.$pregunta['codigo'].$pregunta['tipoRespuesta'].'-->';

	echo '<tr><td class="azul"><span class="requerido">* </span>'.($i+1).'- '.$pregunta['pregunta'].'</td></tr>';

	switch($pregunta['formularioTipo']){
	case 'r':
		echo '<tr><td>';
		
		echo $form->radio(
			"RespuestasAutenticacion.respuestas.${i}.opcion",
			array(
				's' => 'S&iacute;',
				'n' => 'No'
			),
			array(
				'separator'=>'&nbsp;&nbsp;',
				'legend'=>false
			)
		);
		
		echo '</td></tr>';

		echo '<tr><td>'
			.'<label for="data[RespuestasAutenticacion][respuestas]['.$i.'][opcion]" class="error manual">'._m('seleccion.requerida',array('la respuesta')).'</label>';

			if ($form->isFieldError("RespuestasAutenticacion.respuestas.${i}.opcion")){
				echo $form->error("RespuestasAutenticacion.respuestas.${i}.opcion");
			}
		echo '</td></tr>';
		
		if(!empty($pregunta['Subpreguntas'])){
			echo '<!-- '.$pregunta['Subpreguntas'][0]['codigo'].$pregunta['Subpreguntas'][0]['tipoRespuesta'].'-->';
		
			echo "<tr id='respuesta_valor${i}' style='display:none;'><td class='azul'>";
			
			echo $pregunta['Subpreguntas'][0]['pregunta'];
			
			echo '<br>';
		
			echo $form->password("RespuestasAutenticacion.respuestas.${i}.valor", array('maxlength'=>$pregunta['Subpreguntas'][0]['formularioMaxlength']) );
			
			echo '</td></tr>';
			
			if ($form->isFieldError("RespuestasAutenticacion.respuestas.${i}.valor")){
				echo '<tr><td colspan="2">'.$form->error("RespuestasAutenticacion.respuestas.${i}.valor").'</td></tr>';
			}
		}
	break;
	case 't':
		echo "<tr id='respuesta_valor${i}'><td class='etiqueta'>";
	
		echo $form->password("RespuestasAutenticacion.respuestas.${i}.valor", array('maxlength'=>$pregunta['formularioMaxlength']));
		
		echo '</td></tr>';
		
		if ($form->isFieldError("RespuestasAutenticacion.respuestas.${i}.valor")){
			echo '<tr><td colspan="2">'.$form->error("RespuestasAutenticacion.respuestas.${i}.valor").'</td></tr>';
		}

		break;
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

<script>

$(function(){

	<?php for($i=0;$i<count($estado['preguntas']);$i++){
		$pregunta = $estado['preguntas'][$i];
		
		if(!empty($pregunta['Subpreguntas'])){ ?>
			if($('input[name="data[RespuestasAutenticacion][respuestas][<?php echo $i ?>][opcion]"]:checked').val()=='s'){
				$('tr#respuesta_valor<?php echo $i ?>').show();	
			} else {
				$('tr#respuesta_valor<?php echo $i ?>').hide();	
			}
				
			$('input[name="data[RespuestasAutenticacion][respuestas][<?php echo $i ?>][opcion]"]').change(function(){
				if($('input[name="data[RespuestasAutenticacion][respuestas][<?php echo $i ?>][opcion]"]:checked').val()=='s'){
					$('tr#respuesta_valor<?php echo $i ?>').show();	
				} else {
					$('tr#respuesta_valor<?php echo $i ?>').hide();	
				}
			});
	<?php
		}
	}
	?>
	
	// para la validacion
	$('#RespuestasAutenticacionValidarPreguntasAutenticacionForm').validate({
		rules:{
			<?php for($i=0;$i<count($estado['preguntas']);$i++){
				$pregunta = $estado['preguntas'][$i];
				if($i > 0) echo ',';
				if($pregunta['formularioTipo']=='r'){
			?>
			'data[RespuestasAutenticacion][respuestas][<?php echo $i ?>][opcion]':{
				required:true
			}
			
			<?php if(!empty($pregunta['Subpreguntas'])){ ?>
			,
			'data[RespuestasAutenticacion][respuestas][<?php echo $i ?>][valor]':{
				required: {
					depends:function(e){
						return $('input[name="data[RespuestasAutenticacion][respuestas][<?php echo $i ?>][opcion]"]:checked').val()=='s';
					}
				},
				digits:true
			}
			<?php }
			} else if($pregunta['formularioTipo']=='t'){ ?>
			'data[RespuestasAutenticacion][respuestas][<?php echo $i ?>][valor]':{
				required: true,
				digits:true
			}
			<?php } 
			} ?>
		},
		messages:{
			<?php for($i=0;$i<count($estado['preguntas']);$i++){
				if($i > 0) echo ',';
			?>		
			'data[RespuestasAutenticacion][respuestas][<?php echo $i ?>][opcion]':{
				required:<?php echo _m('seleccion.requerida',array('la respuesta'), true) ?>
			},
			'data[RespuestasAutenticacion][respuestas][<?php echo $i ?>][valor]':{
				required: <?php echo _m('campo.requerido',array('la respuesta'), true) ?>,
				digits: <?php echo _m('formato.invalido',array('la respuesta'), true) ?>
			}
			<?php } ?>
		}
	});
});



</script>

<?php } ?>