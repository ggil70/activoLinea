<?php
/**
 * Confirmación de la configuración de las preguntas de desafío
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
				'action'=>'confirmar_preguntas'
			)
		)
	);

	?>
	<input type="hidden" name="data[Usuario][confirmo]" value="si">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme la informaci&oacute;n suministrada:</td>
	</tr>
	</thead>
	
	<?php for($i=1;$i<=3;$i++){ ?>
	<tr>
	<td class="etiqueta"><?php
		if($this->data['Usuario']['preg'.$i]=='otra'){
			echo $this->data['Usuario']['pregUser'.$i];
		} else {
			echo $preguntas[$this->data['Usuario']['preg'.$i]];		
		}
	?></td>
	<td><?php echo $this->data['Usuario']['resp'.$i] ?></td>
	</tr>
	<?php } ?>
	<tr>
	<td colspan="2" class="botonera">

	<?php 
	
	echo $html->link(
		'Volver',
		'asignar_preguntas',
		array(
			'class'=>'button'
		)
	);
		
	echo '&nbsp;&nbsp;';	
	
	echo $html->link(
		'Aceptar',
		'#',
		array(
			'class'=>'envio_formulario button'
		)
	);
	
	?>

	</td>
	</tr>
	</table>
</form>
</div>

