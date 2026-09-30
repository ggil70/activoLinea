<?php
/**
 * Advertencia de bloqueo por respuesta de desafío inválido
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

<p class="mensaje"><?php echo _m('autenticacion.b.2.20') ?></p>

<div class="botonera">
<?php
echo $html->link(
	'Continuar',
	'validar_preguntas_desafio',
	array(
		'class'=>'button'
	)
);

?>
</div>

</div>

