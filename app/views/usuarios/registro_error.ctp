<?php
/**
 * Vista para mostrar cuando ocurre un error en el proceso de registro de un 
 * usuario jurídico migrado.
 * 
 */
?>
<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
	<div class="paso_nro">1</div>
	<div class="paso_desc">Aceptaci&oacute;n de Contrato</div>
	<div class="paso_nro">2</div>
	<div class="paso_desc">Autenticar Empresa</div>
	<div class="paso_nro">3</div>
	<div class="paso_desc">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	
<p class="mensaje error-message"><?php echo $error['mensaje'] ?></p>

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

