<?php
/**
 * Fragmento de pï¿½gina que se genera el popup con las indicaciones
 * para configurar la contraseï¿½a
**/
?>
<div class="basic-modal-content" id="tip_contrasena" style="display:none">
	<h3>Configuración de contraseña</h3>
	<p>
	<ul>
	<li>Longitud mínima (08) Máxima (12) caracteres</li>
	<li>Alfanumérica</li>
	<li>Caracteres especiales permitidos: !"#$%&/()=?ï¿½ debe ingresar al menos uno</li>
	<li>El uso de mayúsculas es opcional</li>
        <li>No se permiten
	<ul>
		<li>Caracteres repetitivos</li>
		<li>Sucesiones</li>
		<li>Clave en blanco</li>
		<li>Número de cï¿½dula</li>
		<li>Número de tarjeta</li>
		<li>Los primeros 4 dígitos de la tarjeta</li>
		<li>Los últimos 4 dígitos de la tarjeta</li>
		<li>Número de cuenta</li>
		<li>Usuario o login</li>
		<li>Nombre del banco</li>
		<li>Fecha de nacimiento</li>
		<li>Número de teléfono</li>
		<li>Primer nombre</li>
		<li>Segundo nombre</li>
		<li>Primer apellido</li>
		<li>Segundo apellido</li>
	</ul>
	</li>
	</ul>	
	</p>
</div>
<script>

$(function(){
	$('.input_tip_contrasena').after('<div style="position:absolute;top:0;left:250px;cursor:pointer" class="icon-ayuda" onclick="$(\'#tip_contrasena\').modal({minHeight:400,minWidth:300})"></div>');
});

</script>