<?php
/**
 * Formulario de solicitud de tarjeta de coordenadas
**/
?>
<div>
<h2 class="user"><?php echo $data_session['titulo'] ?></h2>
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

	echo $form->create('SolicitudTdc',
		array(
			'url'=> array(
				'controller'=>'solicitudes',
				'action'=>'solicitar_tarjeta_coordenada'
			)
		)
	);
	
	echo $form->hidden('dummy');

?>
<table class="tabla" cellpadding="0" cellspacing="0" border="0" style="margin: 0px auto">
<tr><td class="vacio">
<?php if($ya_tiene_tdc) { ?>
	<p class="mensaje">Estimado Cliente, usted ya posee una Tarjeta de Coordenadas.</p>
<?php } else { ?>
	<p class="mensaje">Es un instrumento que proporciona seguridad en las operaciones de la banca electrónica. Contiene un conjunto de números, generados específicamente para cada cliente, los cuales le serán solicitados de manera aleatoria en aquellas operaciones que requieran un nivel más alto de seguridad. Para estas operaciones se le solicitará una coordenada de columna y otra de fila.</p>
	
	<?php echo $html->image('tarjeta-MASTERF.png',array('class'=>'tdc')) ?>
	
	<p class="mensaje">Para obtener su Tarjeta de Coordenada usted debe tener una <b>impresora conectada</b> al computador, la cual se genera en línea y no puede visualizarse en pantalla, únicamente podrá imprimirla.</p>
	
	<p class="mensaje">Es importante que tenga a mano todos sus instrumentos financieros de Banco Activo para poder crear la Tarjeta de Coordenada</p>
<?php } ?>

</td></tr>

<?php if(!$ya_tiene_tdc) { ?>
<tr>
<td class="botonera">
	<?php 

	echo $html->link(
		'Aceptar',
		'#',
		array(
			'class'=>'button envio_formulario'
		)
	);

	echo '&nbsp;&nbsp;';
	
	?>
</td>
</tr>
<?php } ?>
</table>	
</form>	
</div>