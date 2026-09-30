<?php 
    /**
     *  Vista para el resultado de la eliminación de mensajes
     */

?>

<div>
<h2 class="user">Mensajes</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario" style="width:500px;">
	<p class="exito">Operaci&oacute;n realizada satisfactoriamente</p>

	<?php
	echo _m('mensaje.eliminado');

	echo '<div class="botonera">';

	echo $html->link(
		'Volver',
		'mensajes',
		array(
			'class'=>'button'
		)
	);

	echo '</div>';
	?>
</div>