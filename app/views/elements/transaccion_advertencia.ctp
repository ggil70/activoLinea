
<div>
<h2 class="user"><?php echo $this->data['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($this->data['pasos']); $i++) { 
	$class = ($i==1) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $this->data['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	
	<thead>
		<tr>
			<td class="resumen_paginacion" colspan="2">
				<?php echo _m($this->data['mensaje_alerta']); ?>
			</td>
		</tr>
	</thead>

	<tr>
	<td colspan="2" class="botonera">

	<?php 
	echo $html->link(
		'Volver',
		$this->data['url_volver'],
		array(
			'class'=>'button bloqueo_repetir'
		)
	);

	echo '&nbsp;&nbsp;';

	echo $html->link(
		'Continuar',
		$this->data['url_continuar'],
		array(
			'class'=>'button bloqueo_repetir'
		)
	);

	

	
	?>

	</td>
	</table>
</div>




