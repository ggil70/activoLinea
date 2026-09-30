<?php
/**
 * Vista que despliega mensaje de usuario bloqueado
**/
?>

<div>
<h2 class="user"><?php echo $estado['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($estado['pasos']); $i++) { 
	$class = '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $estado['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p class="mensaje"><?php echo _m('usuario.bloqueo.clave') ?></p>

<div class="botonera">
<?php
echo $html->link(
	'Continuar',
	'/seguridad/olvido_clave',
	array(
		'class'=>'button'
	)
);

?>
</div>

</div>
