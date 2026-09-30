<?php
/**
 * Mensaje de advertencia de notificacion de viaje / ausencia
**/
?>
<div>
<h2 class="user">Notificaci&oacute;n de Viaje/Ausencia</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p class="mensaje">
<?php

    echo _m('mensaje.advertencia.viaje');

?>
</p>

<div class="botonera">
<?php
echo $html->link(
	'Continuar',
	'notificacion_viaje',
	array(
		'class'=>'button'
	)
);

?>
</div>

</div>

