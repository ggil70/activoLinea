<?php
/**
 * Confirmación de modificación de pagos y transferencias
**/
?>
<div>
<h2 class="user">Aprobaciones Pendientes - Modificar</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Modificación de Operación</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de la Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<?php

echo $form->create('TransaccionPendiente',
	array(
		'url'=> array(
			'controller'=>'transacciones_pendientes',
			'action'=>'modificar_confirmar'
		)
	)
);


echo $form->hidden('id');

?>


<table class="confirmacion" cellspacing="0" cellpadding="0" border="0">
<thead>
<tr>
<td colspan="2" class="resumen_paginacion">Confirme los datos de la operación que desea realizar:</td>
</tr>
</thead>
</table>

<?php echo $this->element('transaccion_detalle',compact('transaccion')) ?>


<table class="confirmacion" cellspacing="0" cellpadding="0" border="0">
<tr>
<td colspan="2" class="botonera">
<?php 

echo $html->link(
	'Volver',
	'modificar/v',
	array(
		'class'=>'button',
		'id'=>'volver'
	)
);

echo '&nbsp;&nbsp;&nbsp;';

echo $html->link(
	'Aceptar',
	'javascript:',
	array(
		'class'=>'button envio_formulario',
		'id'=>'aceptar'
	)
);

?>
</td>
</tr>
</tbody>

</table>

<?php echo $form->end() ?>

</div>