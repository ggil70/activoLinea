<?php
/**
 * Resultado del cambio de los datos de contacto
**/
?>
<div>
    <h2 class="user">Cambiar Datos Contacto</h2>
    <div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Tarjeta de Coordenada</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">4</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">
	<?php if(!$this->data['Error']){?>
		<p class="exito"><?php echo _m('titulo.operacion.exito'); ?></p>
		<p>Estimado Cliente, Ud. ha modificado sus datos de contacto de forma exitosa.</p>
	<?php
	}
	else{ 
		echo '<div class="error-message">' . _m('operacion.fallida') . '</div>';
	}
	?>
	</td>
	</tr>
	<tr>
	<td colspan="2" class="botonera">

	<?php 
	
	echo $html->link(
		'Volver',
		'/seguridad/cambiar_datos',
		array(
			'class'=>'button'
		)
	);
?>	
</td>
</tr>
	</thead>
	</table>

</div>




