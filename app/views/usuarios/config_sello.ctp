<?php
/**
 * Formulario de configuración de sello personalizado
**/
?>
<div>
<?php if($this->Session->check('usuarios.sello.vencido')) { ?>
<p class="error-message"><?php echo _m('usuario.sello.vencido') ?></p>
<?php } else { ?>
<h2 class="user"><?php echo $data_session['titulo']; ?></h2>
<?php } ?>
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

<div class="contenedor_formulario" style="width:600px;">
	
	<?php
	if($listado_sellos){
		
		echo $form->create('Usuario',
			array(
				'url'=> array(
					'controller'=>'usuarios',
					'action'=>'config_sello'
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

		<tr>
			<td>
				A continuaci&oacute;n cree su propio sello haciendo click 
				<?php
					echo $html->link(
						'AQUI',
						'/usuarios/seleccion_sello'
					);
				?>
				o seleccione alguna de las im&aacute;genes que se muestran a continuaci&oacute;n:
			</td>
		</tr>


		<?php
		
	
		echo '<tr><td>';
			echo '<label for="data[Usuario][idImagen]" class="error manual">'._m('una.seleccion.requerida',array('de las imágenes')).'</label>';
			if ($form->isFieldError('idImagen')){
				echo $form->error('idImagen',
					array(
						'requerido'=>_m('una.seleccion.requerida',array('de las imágenes'))
					)
				);
			}
		echo '</td></tr>';

	
		echo '<tr><td>';
		
		echo '<input type="hidden" value="" name="data[Usuario][idImagen]"/>';

		// Muestra las imagenes
		foreach($listado_sellos as $i){
			echo '<div class="sello-images">';
			echo '<img src="'.Router::url('/ws/buscar_imagen/').'/'.$i['idSello'].'" ></img>';
			echo '<br/><input type="radio" value="'.$i['idSello'].'" name="data[Usuario][idImagen]"/>';
			echo '</div>';
		}
		
		echo '</td></tr>';
		
	
		echo '<tr><td>&nbsp;</td></tr>';

	
		echo '<tr><td class="submit">';

		echo $form->submit(
			'Aceptar',
			array(
				'class'=>'button',
				'div' => false
			)
		);

		echo '</td></tr></table>';
		
		echo $form->end();
	}
	else{
		echo'<div class="error_fatal">'._m('error.fatal').'</div>';
	}
	?>
</div>


<script>
$(function(){
	
	// para la validacion
	$('#UsuarioConfigSelloForm').validate({
		rules:{
			'data[Usuario][idImagen]':{
				required:true
			}
		},
		messages:{
			'data[Usuario][idImagen]':{
				required:<?php echo _m('una.seleccion.requerida',array('de las imágenes'),true) ?>
			}
		}
	});
	
});
</script>
