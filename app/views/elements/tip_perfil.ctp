<?php
/**
 * Fragmento de página que se encarga de generar el popup con las indicaciones
 * para configurar el nombre de usuario
**/
?>
<div class="basic-modal-content" id="tip_perfil" style="display:none">
	<h3>Configuración de perfil</h3>
	<h4>Master:</h4>
	<p>
	Este perfil permite al usuario tener el rol de administrador de la seguridad del Servicio de Activo en Línea Persona Jurídica, el cual contempla: la creación de los distintos usuarios a los cuales se les delegará las funciones específicas que tendrá cada persona de acuerdo a su perfil, los límites de montos y el esquemas de aprobación que van a manejar cada uno para las transacciones financieras.
	</p>
	<h4>Único:</h4>
	<p>
	Este perfil permite al usuario tener la autonomía para realizar cualquier operación disponible en el Servicio Activo en Línea
	Persona Jurídica. No permite delegar funciones en terceras personas.
	</p>
</div>
<script>

$(function(){
	$('.input_tip_perfil').after('<div style="position:absolute;top:0;left:250px;cursor:pointer" class="icon-ayuda" onclick="$(\'#tip_perfil\').modal({minHeight:400,minWidth:300})"></div>');
});

</script>