<?php
/**
 * Mensaje que se muestra cuando el usuario necesita realizar una operación
 * que requiere la tarjeta de coordenadas y el mismo no la posee
**/
?>
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

<p class="mensaje"><?php echo _m('tdc.no.creada') ?></p>

<div class="botonera">
<?php
echo $html->link(
	'Continuar',
	'/solicitudes/solicitar_tarjeta_coordenada',
	array(
		'class'=>'button'
	)
);

?>
</div>

</div>

