<div style="clear:both;" class="subtitulo">
<h2>Cr&eacute;ditos</h2>
<div></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>N&uacute;mero</td>
<td>Alias</td>
<td width="15%">Saldo Deudor Bs.</td>
</tr>
</thead>
<?php if(!empty($creditos)) { ?>
<tbody>
<?php foreach($creditos as $i){ ?>
<tr>
<td class="texto_centro">
	<form class="formulario_enlace" method="post" action="<?php echo Router::url('/creditos/movimientos') ?>">
	<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo trim($i['SUMACN']) ?>" />
	<input type="submit" class="boton_enlace" value="<?php echo trim($i['SUMACN']) ?>" />
	</form>
</td>
<td class="texto_centro"><?php echo $i['ALIAS']; ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($i['LNSPOF']) ?></td>
</tr>
<?php } ?>
</tbody>
<?php } ?>
<tfoot>
<tr>
<td class="total" colspan="2">Total Bs.:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales_creditos['LNSPOF']) ?> </td>
</tr>
</tfoot>
</table>