<?php
/**
 * Fragmento de página que se encarga de generar el popup con las indicaciones
 * para configurar el nombre de usuario
**/
?>
<div class="basic-modal-content" id="tip_usuario" style="display:none">
	<h3>Configuración de usuario</h3>
	<p>
	<ul>
	<li>Longitud mínima (08) Máxima (12) caracteres</li>
	<li>Debe ser alfanumérico, el primer caracter alfabético.</li>
	<li>No se permiten
	<ul>
		<li>Caracteres repetitivos</li>
		<li>Sucesiones</li>
		<li>En blanco</li>
		<li>Número de cédula</li>
		<li>Los primeros 6 dígitos de la tarjeta</li>
		<li>Los últimos 6 dígitos de la tarjeta</li>
	</ul>
	</li>
	</ul>	
	</p>
</div>
<script>

$(function(){
	$('.input_tip_usuario').after('<div style="position:absolute;top:0;left:250px;cursor:pointer" class="icon-ayuda" onclick="$(\'#tip_usuario\').modal({minHeight:400,minWidth:300})"></div>');
});

</script>