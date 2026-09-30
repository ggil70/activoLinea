<?php
/**
 * Mensaje para los usuarios Juridicos con estatus Pendiente, opcion para descargar planilla
**/
?>
<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	
	<thead>
		<tr>
			<td class="resumen_paginacion" colspan="2">
				<?php  echo _m('registro.pendiente'); ?>
			</td>
		</tr>
	</thead>

	<tr>
	<td colspan="2" class="botonera">

	<?php 
	echo $html->link(
		'Volver',
		'/',
		array(
			'class'=>'button bloqueo_repetir'
		)
	);

	echo '&nbsp;&nbsp;';

	echo $html->link(
		'Descargar Planilla',
		'descargar_planilla/d',
		array(
			'class'=>'button',
                        'id'=>'download'
		)
	);
	
	?>

	</td>
	</table>
</div>




