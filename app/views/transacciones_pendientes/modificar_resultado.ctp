<?php
/**
 * Resultado de modificación de pagos y transferencias
**/
?>
<div>
<h2 class="user">Aprobaciones Pendientes - Modificar</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Modificación de Operación</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado de la Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<?php 

if($error_bd){
	echo '<p class="error-message">'._m('error.fatal').'</p>';
} else { ?>

<table class="confirmacion" cellspacing="0" cellpadding="0" border="0">
<thead>
<tr>
<td colspan="2" class="resumen_paginacion">
<p class="exito">Operación realizada satisfactoriamente</p>
</td>
</tr>
</thead>
</table>

<?php echo $this->element('transaccion_detalle',compact('transaccion')); ?>


<?php } ?>

<table class="confirmacion" cellspacing="0" cellpadding="0" border="0">
<tr>
<td colspan="2" class="botonera">
<?php 

echo $html->link(
	'Volver',
	'index',
	array(
		'class'=>'button',
		'id'=>'volver'
	)
);

?>
</td>
</tr>
</tbody>

</table>

</div>