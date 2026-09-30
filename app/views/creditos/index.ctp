<?php
    /**
     *  Vista utilizada para mostrar los créditos que posee un cliente
     */
?>

<div>
<h2 class="user">Cr&eacute;ditos</h2>
<div class="line_title_orange"></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>Alias</td>
<td>N&uacute;mero</td>
<td>Saldo Deudor Bs.</td>
<td>Saldo Capital Bs.</td>
<td>Total Próximo <br> Pago Bs.</td>
<td>Fecha Pr&oacute;ximo Pago</td>
</tr>
</thead>
<?php if(!empty($creditos)) { ?>
<tbody>
<?php foreach($creditos as $i){ ?>
<tr>
<td class="texto_centro"><?php echo $i['ALIAS']; ?></td>
<td class="texto_centro">
	<form class="formulario_enlace" method="post" action="<?php echo Router::url('/creditos/movimientos') ?>">
	<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo trim($i['SUMACN']) ?>" />
	<input type="submit" class="boton_enlace" value="<?php echo trim($i['SUMACN']) ?>" />
	</form>
</td>
<td class="monto"><?php echo formatea_monto_fromWs($i['LNSPOF']) ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($i['LNSPRI']) ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($i['e01DEAMPP']) ?></td>
<td class="texto_centro"><?php echo formatea_fecha($i['e01DEAMPP2'],$i['e01DEAMPP1'],$i['e01DEAMPP3']) ?></td>
</tr>
<?php } ?>
</tbody>
<?php } ?>
<tfoot>
<tr>
<td colspan="2" class="total">Total Bs.:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['LNSPOF']) ?> </td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['LNSPRI']) ?> </td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['e01DEAMPP']) ?> </td>
<td class="monto_total">&nbsp;</td>
</tr>
</tfoot>
</table>
