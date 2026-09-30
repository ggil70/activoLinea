<?php
/**
 * Formulario para configurar preguntas de desafío
**/
?>
<div>
<h2 class="user"><?php echo $data_session['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($data_session['pasos']); $i++) { 
	$class = ($i==$data_session['paso_actual']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $data_session['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	
	<?php
	
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'usuarios',
				'action'=>'asignar_preguntas'
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
			'width'=>'450px'
		)
	);
	
	
	?>
	
	<tr><td colspan="2" class="mensaje">Debe crear tres (3) preguntas y respuestas que ser&aacute;n requeridas al momento de olvidar la clave de ingreso Activo en L&iacute;nea</td></tr>
	
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr><td colspan="2" class="azul"><span class="requerido">*</span> 1.- Pregunta Desaf&iacute;o</td></tr>
	<tr>
		<td colspan="2">
			<?php
				echo $form->input(
					'preg1',
					array(
						'label'=>false,
						'div'=>false,
						'type'=>'select',
						'class'=>'desafio',
						'options'=>$preguntas,
						'empty'=>'Seleccione',
						'error'=>array(
							'requerido'=>_m('seleccion.requerida',array('la pregunta desafío'))
						),
						'before'=>'<span class="estiloTexto"></span>'
					)
				);
			?>
		</td>
	</tr>

	<tr class="pregPropia1" style="display:none;">
		<td class="etiqueta" style="vertical-align:top;"><span class="requerido">*</span> Pregunta:</td>
		<td width="100%">
			<?php
			echo $form->input(
				'pregUser1',
				array(
					'label'=>false,
					'div'=>false,
					'type'=>'text',
					'maxlength'=>'30',
					'minLength'=>'5',
					'class'=>'alfanumericoEspacio',
					'error'=>array(
						'validar_otra_pregunta'=>_m('campo.requerido',array('la pregunta desafío')),
						'requerido'=>_m('seleccion.requerida',array('la pregunta desafío')),
						'minLength'=>_m('longitud.minima',array('la pregunta desafío', 'más de 5','caracteres')),
						'maxLength'=>_m('longitud.maxima',array('la pregunta desafío', 'menos de 30','caracteres'))
					)
				)
			);
			?>
		</td>
	</tr>
	<tr>
		<td class="etiqueta" style="vertical-align:top;"><span class="requerido">*</span> Respuesta Desaf&iacute;o:</td>
		<td width="100%">
			<?php	
				echo $form->input(
					'resp1',
					array(
						'label'=>false,
						'div'=>false,
						'type'=>'text',
						'maxlength'=>'30',
						'minLength'=>'5',
						'class'=>'alfanumericoEspacio',
						'error'=>array(
							'requerido'=>_m('campo.requerido',array('la respuesta desafío')),
							'minLength'=>_m('longitud.minima',array('la respuesta desafío', 'más de 5','caracteres')),
							'maxLength'=>_m('longitud.maxima',array('la respuesta desafío', 'menos de 30','caracteres')),
							'alfanumericoEspacio'=>_m('alfanumerico.invalido',array('la respuesta desafío'))
						)
					)
				);
			?>
		</td>
	</tr>

	<tr><td colspan="2">&nbsp;</td></tr>

	<tr><td colspan="2" class="etiqueta"><span class="requerido">*</span> 2.- Pregunta Desaf&iacute;o</td></tr>
	<tr>
		<td colspan="2">
			<?php
				echo $form->input(
					'preg2',
					array(
						'label'=>false,
						'div'=>false,
						'type'=>'select',
						'class'=>'desafio',
						'options'=>$preguntas,
						'empty'=>'Seleccione',
						'error'=>array(
							'noigual'=>_m('pregunta.repetida'),
							'requerido'=>_m('seleccion.requerida',array('la pregunta desafío'))
						),
						'before'=>'<span class="estiloTexto"></span>'
					)
				);
			?>
		</td>
	</tr>

	<tr class="pregPropia2" style="display:none;">
		<td class="etiqueta" style="vertical-align:top;"><span class="requerido">*</span> Pregunta:</td>
		<td>
			<?php
			echo $form->input(
				'pregUser2',
				array(
					'label'=>false,
					'div'=>false,
					'type'=>'text',
					'minLength'=>'5',
					'maxlength'=>'30',
					'error'=>array(
						'validar_otra_pregunta'=>_m('campo.requerido',array('la pregunta desafío')),
						'noigual'=>_m('pregunta.repetida'),
						'minLength'=>_m('longitud.minima',array('la pregunta desafío', '5','caracteres')),
						'maxLength'=>_m('longitud.maxima',array('la pregunta desafío', '30','caracteres'))
					)
				)
			);
			?>
		</td>
	</tr>
	<tr>
		<td class="etiqueta" style="vertical-align:top;"><span class="requerido">*</span> Respuesta Desaf&iacute;o:</td>
		<td>
			<?php	
				echo $form->input(
					'resp2',
					array(
						'label'=>false,
						'div'=>false,
						'type'=>'text',
						'maxlength'=>'30',
						'minLength'=>'5',
						'class'=>'alfanumericoEspacio',
						'error'=>array(
							'requerido'=>_m('campo.requerido',array('la respuesta desafío')),
							'minLength'=>_m('longitud.minima',array('la respuesta desafío', '5','caracteres')),
							'maxLength'=>_m('longitud.maxima',array('la respuesta desafío', '30','caracteres')),
							'noigual'=>_m('respuesta.repetida'),
							'alfanumericoEspacio'=>_m('alfanumerico.invalido',array('la respuesta desafío'))
						)
					)
				);
			?>
		</td>
	</tr>


	<tr><td colspan="2">&nbsp;</td></tr>

	<tr><td colspan="2" class="etiqueta"><span class="requerido">*</span> 3.- Pregunta Desaf&iacute;o</td></tr>
	<tr>
		<td colspan="2">
			<?php
				echo $form->input(
					'preg3',
					array(
						'label'=>false,
						'div'=>false,
						'type'=>'select',
						'class'=>'desafio',
						'options'=>$preguntas,
						'empty'=>'Seleccione',
						'error'=>array(
							'noigual'=>_m('pregunta.repetida'),
							'requerido'=>_m('seleccion.requerida',array('la pregunta desafío'))
						),
						'before'=>'<span class="estiloTexto"></span>'
					)
				);
			?>
		</td>
	</tr>
	
	<tr class="pregPropia3" style="display:none;">
		<td class="etiqueta" style="vertical-align:top;"><span class="requerido">*</span> Pregunta:</td>
		<td>
			<?php
			echo $form->input(
				'pregUser3',
				array(
					'label'=>false,
					'div'=>false,
					'type'=>'text',
					'minLength'=>'5',
					'maxlength'=>'30',
					'error'=>array(
						'validar_otra_pregunta'=>_m('campo.requerido',array('la pregunta desafío')),
						'noigual'=>_m('pregunta.repetida'),
							'minLength'=>_m('longitud.minima',array('la pregunta desafío', '5','caracteres')),
							'maxLength'=>_m('longitud.maxima',array('la pregunta desafío', '30','caracteres'))
					)
				)
			);
			?>
		</td>
	</tr>
	<tr>
		<td class="etiqueta" style="vertical-align:top;"><span class="requerido">*</span> Respuesta Desaf&iacute;o:</td>
		<td>
			<?php	
				echo $form->input(
					'resp3',
					array(
						'label'=>false,
						'div'=>false,
						'type'=>'text',
						'maxlength'=>'30',
						'minLength'=>'5',
						'class'=>'alfanumericoEspacio',
						'error'=>array(
							'requerido'=>_m('campo.requerido',array('la respuesta desafío')),
							'minLength'=>_m('longitud.minima',array('la respuesta desafío', '5','caracteres')),
							'maxLength'=>_m('longitud.maxima',array('la respuesta desafío', '30','caracteres')),
							'noigual'=>_m('respuesta.repetida'),
							'alfanumericoEspacio'=>_m('alfanumerico.invalido',array('la respuesta desafío'))
						)
					)
				);
			?>
		</td>
	</tr>

	<tr><td colspan="2">&nbsp;</td></tr>	
	
	<?php
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td colspan="2" class="submit">';
	
	echo $form->submit(
		'Aceptar',
		array(
			'class'=>'button',
			'div' => false
		)
	);
	
	echo '</td></tr></table>';
	
	
	echo $form->end();
	
	?>
</div>

<script>
<?php 
$preguntas2 = array();
foreach($preguntas as $k => $v) $preguntas2[utf8_encode($k)]=utf8_encode($v);
?>
var preguntas = <?php echo json_encode($preguntas2) ?>;

function acomodar(){
	$('.desafio').each(function() {
		var valor = $(this).val();
		var id = $(this).attr('id');
		var desafio = id.substring(id.length - 1);
		
		if(valor=='otra'){
			if($('.pregPropia'+desafio).css('display')=='none'){
				$('.pregPropia'+desafio).show();
			}
		} else {
			if($('.pregPropia'+desafio).css('display')!='none'){
				$('.pregPropia'+desafio).hide();
			}
		}
		
		$(this).empty();
		
		var otros = $('.desafio[id!="'+id+'"]').map(function(){return $(this).val()});
		
		$(this).append('<option value="">Seleccione</option>');
		
		for(i in preguntas){
			if(i=='otra' || 0>$.inArray(i,otros)){
				$(this).append('<option value="'+i+'">'+preguntas[i]+'</option>');
			}
		}
				
		$(this).val(valor);
		
	});
}

$(function(){
	acomodar();
	$('.desafio').change(acomodar);
	
	// para la validacion
	$('#UsuarioAsignarPreguntasForm').validate({
		rules:{
			'data[Usuario][preg1]':{
				required:true
			},
			'data[Usuario][resp1]':{
				required:true,
				alfanumericoEspacio:true
			},
			'data[Usuario][pregUser1]':{
				required:true
			},
			'data[Usuario][preg2]':{
				required:true
			},
			'data[Usuario][pregUser2]':{
				required:true, noigual:'#UsuarioPregUser1'
			},
			'data[Usuario][resp2]':{
				required:true,
				noigual:'#UsuarioResp1',
				alfanumericoEspacio:true
			},
			'data[Usuario][preg3]':{
				required:true
			},
			'data[Usuario][pregUser3]':{
				required:true, noigual:['#UsuarioPregUser1','#UsuarioPregUser2']
			},
			'data[Usuario][resp3]':{
				required:true,
				noigual:['#UsuarioResp1','#UsuarioResp2'],
				alfanumericoEspacio:true
			}
		},
		messages:{
			'data[Usuario][preg1]':{
				required:<?php echo _m('campo.requerido',array('la pregunta desafío'),true) ?>
			},
			'data[Usuario][resp1]':{
				required:<?php echo _m('campo.requerido',array('la respuesta desafío'),true) ?>,
				alfanumericoEspacio:<?php echo _m('alfanumerico.invalido',array('la respuesta desafío'),true) ?>,
				minlength:<?php echo _m('longitud.minima',array('la respuesta desafío', '5','caracteres'),true) ?>
			},
			'data[Usuario][pregUser1]':{
				required:<?php echo _m('campo.requerido',array('la pregunta desafío'),true) ?>,
				minlength:<?php echo _m('longitud.minima',array('la pregunta desafío', '5','caracteres'),true) ?>
			},
			'data[Usuario][preg2]':{
				required:<?php echo _m('campo.requerido',array('la pregunta desafío'),true) ?>
			},
			'data[Usuario][pregUser2]':{
				required:<?php echo _m('campo.requerido',array('la pregunta desafío'),true) ?>,
				noigual:<?php echo _m('pregunta.repetida',null,true) ?>,
				minlength:<?php echo _m('longitud.minima',array('la pregunta desafío', '5','caracteres'),true) ?>
			},
			'data[Usuario][resp2]':{
				required:<?php echo _m('campo.requerido',array('la respuesta desafío'),true) ?>,
				noigual:<?php echo _m('respuesta.repetida',null,true) ?>,
				alfanumericoEspacio:<?php echo _m('alfanumerico.invalido',array('la respuesta desafío'),true) ?>,
				minlength:<?php echo _m('longitud.minima',array('la respuesta desafío', '5','caracteres'),true) ?>
			},
			'data[Usuario][preg3]':{
				required:<?php echo _m('campo.requerido',array('la pregunta desafío'),true) ?>
			},
			'data[Usuario][pregUser3]':{
				required:<?php echo _m('campo.requerido',array('la pregunta desafío'),true) ?>,
				noigual:<?php echo _m('pregunta.repetida',null,true) ?>,
				minlength:<?php echo _m('longitud.minima',array('la pregunta desafío', '5','caracteres'),true) ?>
			},
			'data[Usuario][resp3]':{
				required:<?php echo _m('campo.requerido',array('la respuesta desafío'),true) ?>,
				noigual:<?php echo _m('respuesta.repetida',null,true) ?>,
				alfanumericoEspacio:<?php echo _m('alfanumerico.invalido',array('la respuesta desafío'),true) ?>,
				minlength:<?php echo _m('longitud.minima',array('la respuesta desafío', '5','caracteres'),true) ?>
			}
		}
	});
	
});
</script>
