<?php
/**
 * Mensaje de usuario creado
**/
?>
<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
<?php 
if($this->Session->check('login.juridico')){ 
	if(isset($this->data['pasos'])){
		 for($i = 0; $i < count($this->data['pasos']); $i++) { 
			$class = ($i==0) ? '_actual' : '';
			?>
			<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
			<div class="paso_desc<?php echo $class ?>"><?php echo $this->data['pasos'][$i] ?></div>
			<?php 
		}
	}
	else{
?>
	<div class="paso_nro">1</div>
	<div class="paso_desc">Aceptaci&oacute;n de Contrato</div>
	<div class="paso_nro">2</div>
	<div class="paso_desc">Autenticar Empresa</div>
	<div class="paso_nro_actual">3</div>
	<div class="paso_desc_actual">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Tarjeta de Coordenadas</div>
<?php 
	}
}else{ 
?>
	<div class="paso_nro">1</div>
	<div class="paso_desc">Aceptaci&oacute;n de Contrato</div>
	<div class="paso_nro">2</div>
	<div class="paso_desc">Autenticaci&oacute;n del cliente</div>
	<div class="paso_nro_actual">3</div>
	<div class="paso_desc_actual">Registro de Usuario</div>
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

<div class="contenedor_formulario">
	
<?php

echo $form->create('Usuario',
	array(
		'url'=> array(
			'controller'=>'usuarios',
			'action'=>'usuario_creado'
		)
	)
);

foreach($this->data['Usuario'] as $k => $v){
	echo $form->hidden($k);
}

?>
	
<p class="mensaje"><?php echo _m('autenticacion.a.28') ?></p>

<div class="botonera">
<?php
echo $form->submit(
	'Continuar',
	array(
		'class'=>'button',
		'div' => false
	)
);

echo $form->end();

?>
</div>

</div>

