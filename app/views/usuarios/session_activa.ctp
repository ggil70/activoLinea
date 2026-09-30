<?php
/**
 * Mensaje que el usuario esta intentando ingresar, pero todavía tiene una sesión activa
**/
?>
<div>
	<h2 class="user">Sesi&oacute;n Activa</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p class="mensaje"><?php echo _m('session.activa', _p('tiempo_login')) ?></p>

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