<?php
/**
 * Confirmación de los cambios en los datos de contacto
**/
?>
<div>
<h2 class="user">Cambiar Datos Contacto</h2>
<div class="line_title_orange"></div>
</div>
<div class="paso_nro">1</div>
<div class="paso_desc">Tarjeta de Coordenadas</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<?php

		echo $form->create('Usuario',
			array(
				'url'=> array(
					'controller'=>'seguridad',
					'action'=>'cambiar_datos_confirmacion'
				)
			)
		);
		
	?>
	
	<input type="hidden" name="data[Usuario][confirmado]" value="si">


	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
	<td class="etiqueta">Email Principal:</td>
	<td><?php echo $this->data['Usuario']['emailPri'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Email Secundario:</td>
	<td><?php echo $this->data['Usuario']['emailSec'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">N&uacute;mero Celular:</td>
	<td><?php echo $this->data['Usuario']['codigoCelular'].$this->data['Usuario']['nroCelular'] ?></td>
	</tr>
	<tr>
	<td colspan="2" class="botonera">

	<?php 
	
	echo $html->link(
		'Volver',
		'/seguridad/cambiar_datos/v',
		array(
			'class'=>'button'
		)
	);
	
	echo '&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'/seguridad/cambiar_datos_confirmacion/v',
		array(
			'class'=>'envio_formulario button'
		)
	);

	

	

	?>
	</td>
	</table>
	
	</form>
</div>