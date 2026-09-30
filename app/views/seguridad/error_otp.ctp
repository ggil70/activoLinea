<div>
<h2 class="user"><?php echo $estado['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($estado['pasos']); $i++) { 
            $class = ($i==$estado['paso_actual']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $estado['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	
        <div class="espacio_vertical_20"></div>
        
	<thead>
		<tr>
			<td class="resumen_paginacion" colspan="2">
				<?php echo _m('error.otp'); ?>
			</td>
		</tr>
	</thead>

	<tr>
	<td colspan="2" class="botonera">

	<?php 	

	echo $html->link(
		'Aceptar',
		$estado['url_volver'],
		array(
			'class'=>'button bloqueo_repetir'
		)
	);

	?>

	</td>
	</table>
</div>




