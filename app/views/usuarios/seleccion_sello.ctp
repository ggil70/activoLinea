<?php
/**
 * Configuración de imagen de seguridad personalizada
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

<div class="contenedor_formulario_inicio">
	
	<?php
	
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'usuarios',
				'action'=>'seleccion_sello'
			),
			'type'=>'file'
		)
	);
	
	
	?>

	
	<table cellpadding="5" cellspacing="0" border="0" align="center" style="width:310px">
	<thead>
	<tr>
	<td class="resumen_paginacion">A continuaci&oacute;n Ud. podr&aacute; crear su Sello personalizado:</td>
	</tr>
	</thead>
	
		<tr>
			<td valign="top">
			<?php echo $form->text('sello1',array('maxlength'=>7,'size'=>7,'class'=>'alfanumericoEspacio')) ?>&nbsp;&nbsp;
			<?php echo $form->text('sello2',array('maxlength'=>7,'size'=>7,'class'=>'alfanumericoEspacio')) ?>&nbsp;&nbsp;
			<?php echo $form->text('sello3',array('maxlength'=>7,'size'=>7,'class'=>'alfanumericoEspacio')) ?></td>
		</tr>
		<tr>
			<td valign="top">
			<div id="errores"></div>
			<?php if($form->isFieldError('sello1')) {echo $form->error('sello1',
				array(
					'alfanumerico'=>_m('alfanumerico.invalido',array('la primera parte del sello')),
					'requerido'=>_m('campo.requerido',array('el texto del sello'))
				)
			);} ?><?php if($form->isFieldError('sello2')) {echo $form->error('sello2',
				array(
					'alfanumerico'=>_m('alfanumerico.invalido',array('la segunda parte del sello'))
				));} ?><?php if($form->isFieldError('sello3')) {echo $form->error('sello3',
				array(
					'alfanumerico'=>_m('alfanumerico.invalido',array('la tercera parte del sello'))
				));} ?></td>
		</tr>		
		<tr>
		<td>
			21 caracteres como máximo
		</td>
		</tr>
		<tr>
			<td class="botonera">
			<?php
				echo $html->link(
					'Volver',
					'config_sello',
					array(
						'class'=>'button'
					)
				);

				echo '&nbsp;&nbsp;';
				
				echo $html->link(
					'Aceptar',
					'javascript:void(0)',
					array(
						'class'=>'button envio_formulario'
					)
				);
			?>
			</td>
		</tr>
	</table>
	
	<?php echo $form->end() ?>
</div>
		

<script>
$(function(){
	
	$('#UsuarioSello2, #UsuarioSello3').change(function(){
		$('#UsuarioSello1').valid();
	});
	
	// para la validacion
	$('#UsuarioSeleccionSelloForm').validate({
		rules:{			
			'data[Usuario][sello1]':{
				required: {
					depends: function(){
						return $("#UsuarioSello2").val()=='' && $("#UsuarioSello3").val()=='';
					}
				},
				sello:true
			},			
			'data[Usuario][sello2]':{
				sello:true
			},			
			'data[Usuario][sello3]':{
				sello:true
			}
		},
		messages:{			
			'data[Usuario][sello1]':{
				required:<?php echo _m('campo.requerido',array('el texto del sello'),true) ?>,
				sello:<?php echo _m('alfanumerico.invalido',array('el primer sello'),true) ?>
			},			
			'data[Usuario][sello2]':{
				required:<?php echo _m('campo.requerido',array('el texto del sello'),true) ?>,
				sello:<?php echo _m('alfanumerico.invalido',array('el segundo sello'),true) ?>
			},			
			'data[Usuario][sello3]':{
				required:<?php echo _m('campo.requerido',array('el texto del sello'),true) ?>,
				sello:<?php echo _m('alfanumerico.invalido',array('el tercer sello'),true) ?>
			}
		},
		errorPlacement: function(error, element) {
			var c = $("<span> </span>");
			c.html(error);
			$("#errores").append(c);
        }   
	});
	
});
</script>
