<?php
/**
 * Mensaje que se muestra en el registro para que el usuario tenga todos sus
 * instrumentos de banco activo a la mano para el proceso de autenticación
**/
?>
<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
<?php if($this->Session->check('login.juridico')){ ?>
	<div class="paso_nro_actual">1</div>
	<div class="paso_desc_actual">Autenticar Empresa</div>
	<div class="paso_nro">2</div>
        <div class="paso_desc">Aceptaci&oacute;n de Contrato</div>	
	<div class="paso_nro">3</div>
	<div class="paso_desc">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Configuraci&oacute;n de L&iacute;mites</div>
	<div class="paso_nro">7</div>
	<div class="paso_desc">Solicitar Tarjeta de Coordenadas</div>
<?php }else{ ?>
        <div class="paso_nro_actual">1</div>
        <div class="paso_desc_actual">Autenticaci&oacute;n del cliente</div>
	<div class="paso_nro">2</div>
        <div class="paso_desc">Aceptaci&oacute;n de Contrato</div>	
	<div class="paso_nro">3</div>
	<div class="paso_desc">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Código de Autorización</div>
        <div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">7</div>
	<div class="paso_desc">Configuraci&oacute;n de L&iacute;mites</div>
<?php } ?>	
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario_inicio">
	
<?php

echo $form->create('Usuario',
	array(
		'url'=> array(
			'controller'=>'usuarios',
			'action'=>'recordar_instrumentos'
		)
	)
);

echo $form->hidden('dummy');

?>

<p class="mensaje"><?php echo _m('autenticacion.a.9') ?></p>

<div class="botonera">
<?php
echo $form->submit(
	'Confirmar',
	array(
		'class'=>'button',
		'div' => false
	)
);
?>
</div>

<?php echo $form->end(); ?>

</div>

