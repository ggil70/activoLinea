<div class="subtitulo">
<h2>Tarjetas de Cr&eacute;dito</h2>
<div></div>
</div>

<table  class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>N&uacute;mero</td>
<td>Alias</td>
<td>Disponible Bs.</td>
</tr>
</thead>
<?php if(!empty($tarjetas)) { ?>
<tbody>
<?php foreach($tarjetas as $t){?>
<tr>
<td class="texto_centro">
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjetas/detalle') ?>">
<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo trim($t['SUMACN']) ?>" />
<input type="submit" class="boton_enlace" value="<?php echo enmascaraMovil($t['SUMACN']) ?>" />
</form>
</td>
<td class="texto_centro"><?php echo $t['ALIAS']; ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($t['credito_disponible']); ?></td>
</tr>
<?php } ?>
</tbody>
<?php } ?>
<tfoot>
<tr>
<td class="total" colspan="2">Total Bs.:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['credito_disponible']) ?> </td>
</tr>
</tfoot>
</table>
