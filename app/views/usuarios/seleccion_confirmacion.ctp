<?php
/**
 * Confirmación de imagen de seguridad personalizada
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
				'action'=>'seleccion_confirmacion'
			)
		)
	);
	
	
	echo $form->hidden('imagen');
	?>
	
	<p>El siguiente es el sello personalizado que se presentará al momento de iniciar sesi&oacute;n en el servicio de Activo en L&iacute;nea</p>
	
	<div class="sello_externo">
	<div class="sello_interno">
	<?php echo $this->data['Usuario']['sello1'] ?><br>
	<?php echo $this->data['Usuario']['sello2'] ?><br>
	<?php echo $this->data['Usuario']['sello3'] ?><br>
	</div>
	</div>
	
	<div class="botonera">
	
	<?php
	
	
	echo $html->link(
		'Volver',
		'/usuarios/seleccion_sello',
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
	
	
	?>
	
	</div>
	
	<?php
		
	echo $form->end();
	
	?>
</div>
