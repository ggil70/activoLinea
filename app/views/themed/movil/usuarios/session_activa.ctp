<div class="subtitulo">
<h2>Sesi&oacute;n Activa</h2>
<div></div>
</div>

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