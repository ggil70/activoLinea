<?php
/**
 * Vista que despliega mensaje de usuario jurídico bloqueado
**/
?>

<div>
<h2 class="user">Usuario Bloqueado</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p class="mensaje"><?php echo _m('usuario.bloqueo.acceso') ?></p>

<div class="botonera">
<?php
echo $html->link(
	'Continuar',
	'/',
	array(
		'class'=>'button'
	)
);

?>
</div>

</div>