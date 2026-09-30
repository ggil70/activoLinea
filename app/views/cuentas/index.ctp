<?php
    /**
     *  Vista para mostrar el listado de cuentas que posee un cliente
     */
?>

<div>
<h2 class="user">Cuentas Bancarias Moneda Nacional</h2>
<div class="line_title_orange"></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>Alias</td>
<td>N&uacute;mero</td>
<td>Tipo Moneda</td>
<td>Saldo Total</td>
<td>Saldo Disponible</td>


</tr>
</thead>
<?php if(!empty($cuentas)) { ?>
<tbody>
<?php foreach($cuentas as $c){?>
<tr>
<td class="texto_centro"><?php echo $c['ALIAS']; ?></td>
<td class="texto_centro">
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/detalle') ?>">
<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $c['SUMACN'] ?>" />
<input type="submit" class="boton_enlace" value="<?php echo enmascara($c['SUMACN']) ?>" />
</form>
</td>
<td class="texto_centro"><?php echo ($c['SUMCCY']) === 'VEB' ? 'BS.' : 'BS.'; ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($c['SUMBAL']) ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($c['SUMAVB']) ?></td>
</tr>
<?php } ?>
</tbody>
<?php } ?>
<tfoot>
<td colspan="3" class="total">Total:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['SUMBAL']) ?> </td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['SUMAVB']) ?> </td>
</tfoot>
</table>

<?php if(!empty($cuentas_moneda_extranjera)) { ?>
<div>
<h2 class="user">Cuentas Bancarias Moneda Extranjera</h2>
<div class="line_title_orange"></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>Alias</td>
<td>N&uacute;mero</td>
<td>Tipo Moneda</td>
<td>Saldo Total</td>
<td>Saldo Disponible</td>


</tr>
</thead>
<?php if(!empty($cuentas_moneda_extranjera)) { ?>
<tbody>
<?php foreach($cuentas_moneda_extranjera as $ce){?>
<tr>
<td class="texto_centro"><?php echo $ce['ALIAS']; ?></td>
<td class="texto_centro">
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/detalle') ?>">
<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $ce['SUMACN'] ?>" />
<input type="submit" class="boton_enlace" value="<?php echo enmascara($ce['SUMACN']) ?>" />
</form>
</td>
<td class="texto_centro"><?php echo $ce['SUMCCY']; ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($ce['SUMBAL']); ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($ce['SUMAVB']); ?></td>
</tr>
<?php } ?>
</tbody>
<?php } ?>
<tfoot>
<tr>
<td colspan="3" class="total">Total:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales_moneda_extranjera['SUMBAL']); ?> </td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales_moneda_extranjera['SUMAVB']); ?> </td>
</tr>
</tfoot>
</table>
<?php } ?>
