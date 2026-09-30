<?php
/**
 * Mensaje de bloqueo por autenticación fallida de la tarjeta de coordenadas
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

<p class="mensaje"><?php
$url_continuar = '/consultas/integral';

if($this->Session->check('login.juridico')) {
	if(isset($estado['permite_continuar']) && $estado['permite_continuar']){
		echo 'Estimado Cliente, su tarjeta de coordenada ha sido anulada. A continuación proceda a configurar sus nuevas Preguntas y Respuestas de Desafío,  posteriormente canalice la verificación de su usuario para su activación';
		$url_continuar = $estado['url_aceptar'];
	} else {
		echo _m('tdc.anulada');
	}
} else {
	echo _m('tdc.bloqueada');
}?></p>

<div class="botonera">
<?php
echo $html->link(
	'Continuar',
	$url_continuar,
	array(
		'class'=>'button'
	)
);

?>
</div>

</div>

