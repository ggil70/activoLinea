<div class="subtitulo">
<h2>Inversiones</h2>
<div></div>
</div>

<table  class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>N&uacute;mero</td>
<td>Monto Invertido Bs.</td>
<td>Fecha Vencimiento</td>
</tr>
</thead>
<?php if(!empty($inversiones)) { ?>
<tbody>
<?php
foreach($inversiones as $c){
?>
<tr>
	<td class="texto_centro">
		<form class="formulario_enlace" method="post" action="<?php echo Router::url('/inversiones/detalle'); ?>">
		<input type="hidden" name="data[DetalleProducto][nro_inversion]" value="<?php echo $c['SUMACN']; ?>" />
		<input type="submit" class="boton_enlace" value="<?php echo $c['SUMACN']; ?>" />
		</form>
	</td>
	<td class="monto"><?php echo formatea_monto_fromWs($c['SUMBAL']); ?></td>
	<td class="texto_centro"><?php  echo formatea_fecha($c['CDSMATDTE1'],$c['CDSMATDTE2'],$c['CDSMATDTE3']); ?></td>
</tr>
<?php
}
?>
</tbody>
<?php } ?>
<tfoot>
<tr>
<td class="total">Total Bs.:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales_inversiones['SUMBAL']); ?> </td>
<td class="monto_total">&nbsp;</td>
</tr>
</tfoot>
</table>