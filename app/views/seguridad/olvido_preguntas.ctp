<?php
/**
 * Mensaje para usuarios master de descargar planilla de actualización de 
**/
?>
<div>
<h2 class="user"><?php echo $estado_olvido['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($estado_olvido['pasos']); $i++) { 
	$class = ($i==$estado_olvido['paso_planilla']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $estado_olvido['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p class="mensaje">Por favor descargue e imprima la planilla de “Solicitud de Activación de Usuario por Olvido de Preguntas y Respuestas de Desafio" y diríjase a una Oficina Comercial</p>

<div class="botonera">
<?php
echo $html->link(
	'Descargar Planilla',
	'descargar_planilla_qa',
	array(
		'id'=>'descargar',
		'class'=>'button'
	)
);

echo '&nbsp;&nbsp;';

echo $html->link(
	'Continuar',
	'/',
	array(
		'id'=>'continuar',
		'class'=>'button',
		'style'=>'display:none'
	)
);

?>
</div>

</div>

<script>
$(function(){
	$('#descargar').click(function(){
		$('#continuar').show();
	});
});
</script>
