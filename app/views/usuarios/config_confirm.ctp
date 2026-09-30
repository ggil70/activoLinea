<?php
/**
 * Confirmación de la imagen de seguridad
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

<div class="contenedor_formulario" style="width:600px;">
	
	<?php
	
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'usuarios',
				'action'=>'config_confirm'
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
	
	
	echo $form->hidden('idImagen');
	?>
	
	<tr>
		<td>
			<b>Por favor confirme la imagen que selecciono a continuaci&oacute;n:</b>
		</td>
	</tr>
	
	
	<?php
	
	
	echo '<tr><td>';
		echo '<label for="data[Usuario][idConfirm]" class="error manual">'._m('una.seleccion.requerida',array('de las imágenes')).'</label>';
		if ($form->isFieldError('idConfirm')){
			echo $form->error('idConfirm',array(
				'idIgualQue'=>_m('imagen.no.coincide')
			));
		}
	echo '</td></tr>';
	
	
	echo '<tr><td>';
	echo '<input type="hidden" value="" name="data[Usuario][idConfirm]"/>';
	
	// Muestra las imagenes
	foreach($imagenes as $i){
		echo '<div class="sello-images">';
		echo '<img src="'.Router::url('/ws/buscar_imagen/').'/'.$i['idSello'].'"/>';
		echo '<br/><input type="radio" value="'.$i['idSello'].'" name="data[Usuario][idConfirm]"/>';
		echo '</div>';
	}
	echo '</td></tr>';
	
	
	
	echo '<tr><td>&nbsp;</td></tr>';
	
	echo '<tr><td class="submit">';
	
	echo $html->link(
		'Volver',
		'/usuarios/config_sello',
		array(
			'class'=>'button'
		)
	);
	
	echo '&nbsp;&nbsp;&nbsp;';
	
	echo $html->link(
		'Confirmar',
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
	$('#UsuarioConfigConfirmForm').validate({
		rules:{
			'data[Usuario][idConfirm]':{
				required:true
			}
		},
		messages:{
			'data[Usuario][idConfirm]':{
				required:<?php echo _m('una.seleccion.requerida',array('de las imágenes'),true) ?>
			}
		}
	});
	
});
</script>
