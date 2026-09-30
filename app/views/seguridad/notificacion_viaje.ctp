<?php
    /**
     *   Vista para la notificacion de viaje / ausencia
     */
?>

<div>
<h2 class="user">Notificaci&oacute;n de Viaje/Ausencia</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Ubicaci&oacute;n de Seguridad</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style= "width:500px">
	
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Ubicaci&oacute;n de Seguridad:</td>
	</tr>
	</thead>
	<tr>
	<td class="etiqueta" style="width:50%">Pa&iacute;s:</td>
	<td><?php echo $this->data['NotificacionAusencia']['pais'] ?></td>
	</tr>	
	<tr>
	<td colspan="2" class="botonera">

	<?php 	
	
	echo $html->link(
		'Modificar',
		'listado_notificacion_viaje',
		array(
			'class'=>'button'
		)
	);

	?>

	</td>
	</table>
</div>
