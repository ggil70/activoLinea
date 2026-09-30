<?php
/**
 * Confirmación de pago de tarjetas propias en banco activo
**/
?>
<div class="espacio_vertical_20"></div>
<div>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Votaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<h5 class=""align = "center">Confirme su Voto</h5>
<div class="espacio_vertical_20"></div>
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">

	<tr>
	<td class="etiqueta">Empresa Seleccionada</td>
	<td><?php echo $this->data['AuditoresExternos']['empresa_seleccionada']; ?></td>
	</tr>
	<tr>
	<td colspan="2" class="botonera">

	<?php 
	
	echo $html->link(
		'Volver',
		'/auditores_externos/auditores_externos/v',
		array(
			'class'=>'button bloqueo_repetir'
		)
	);

	echo '&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'/auditores_externos/auditores_externos_resultado',
		array(
			'class'=>'button bloqueo_repetir'
		)
	);

	?>

	</td>
	</table>
</div>




