<?php
/**
 * Mensaje de advertencia de bloqueo de tarjeta de coordenadas debido a
 * que el usuario suministró un valor inválido en alguna de las coordenadas
 * solicitadas por el sistema
**/
?>
<div>
<h2 class="user"><?php echo $estado['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($estado['pasos']); $i++) { 
	$class = ($i==$estado['paso_actual']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $estado['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p class="mensaje">
<?php
if($this->Session->check('login.juridico')) {
	echo _m('coordenada.juridico.invalida');
} else {
	echo _m('coordenada.tdc.invalida');
}?>
</p>

<div class="botonera">
<?php
echo $html->link(
	'Continuar',
	'validar_tdc',
	array(
		'class'=>'button'
	)
);

?>
</div>

</div>

