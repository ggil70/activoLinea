<?php
/**
 * Mensaje indicando que el registro fue bloqueado debido a que la autenticación
 * fue fallida
**/
?>
<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
	<div class="paso_nro">1</div>
	<div class="paso_desc">Aceptaci&oacute;n de Contrato</div>
	<div class="paso_nro_actual">2</div>
	<div class="paso_desc_actual">Autenticaci&oacute;n del cliente</div>
	<div class="paso_nro">3</div>
	<div class="paso_desc">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Configuraci&oacute;n de L&iacute;mites</div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	
<p class="mensaje"><?php echo _m('autenticacion.b.2.22') ?></p>

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

