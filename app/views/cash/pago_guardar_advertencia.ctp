<div>
<h2 class="user"><?php echo $titulo ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>
<div class="espacio_vertical_20"></div>
<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	
	<thead>
		<tr>
			<td class="resumen_paginacion" colspan="2">
				Estimado Cliente, esta opción  sólo permitirá que su pago sea guardado hasta que presione  el botón procesar. ¿ Esta seguro  que desea guardar el pago? 
			</td>
		</tr>
	</thead>

	<tr>
	<td colspan="2" class="botonera">

	<?php 
	echo $html->link(
		'Volver',
		'/cash/pago_modificar/'.$estado['tipo'],
			array(
				'class'=>'button',
				'id'=>'volver'
			)
	);

	echo '&nbsp;&nbsp;';

	echo $html->link(
		'Continuar',
		'pago_modificar_resultado',
		array(
			'class'=>'button'
		)
	);

	?>

	</td>
	</table>
</div>




