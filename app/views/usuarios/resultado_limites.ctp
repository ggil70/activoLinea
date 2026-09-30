<?php
/**
 * Mensaje de resultado de configuración de montos límites
**/
?>
<div>
<h2 class="user"><?php echo $data_session['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($data_session['pasos']); $i++) { 
	$class = ($i==$data_session['paso_actual']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $data_session['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	
<?php

echo $form->create('Usuario',
	array(
		'url'=> array(
			'controller'=>'usuarios',
			'action'=>'resultado_limites'
		)
	)
);

?>

<input type="hidden" name="data[Usuario][acepto]" value="si">

<p class="exito">Operación realizada satisfactoriamente</p>
<?php echo _m('limites.configurados') ?>

<?php if($data_session['url_aceptar']) { ?>
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
<?php } ?>

</div>
