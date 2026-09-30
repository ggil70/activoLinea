<?php if(!empty($cuentas) || isset($error_fatal)) { ?>
<div class="subtitulo">
<h2>Cuentas Bancarias</h2>
<div></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td width="33%">N&uacute;mero</td>
<td width="33%">Alias</td>
<td width="33%">Disponible Bs.</td>

</tr>
</thead>
<?php if(!empty($cuentas)) { ?>
<tbody>
<?php foreach($cuentas as $c){?>
<tr>
<td class="texto_centro">
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/detalle') ?>">
<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $c['SUMACN'] ?>" />
<input type="submit" class="boton_enlace" value="<?php echo enmascaraMovil($c['SUMACN']) ?>" />
</form>
</td>
<td class="texto_centro"><?php echo $c['ALIAS']; ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($c['SUMAVB']); ?></td>
</tr>
<?php } ?>
</tbody>
<?php } ?>
<tfoot>
<tr>
<td class="total" colspan="2">Total Bs.:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['SUMAVB']); ?> </td>
</tr>
</tfoot>
</table>
<?php } ?>

<?php if(!empty($tarjetas)) { ?>
<div class="subtitulo">
<h2>Tarjetas de Cr&eacute;dito</h2>
<div></div>
</div>

<table  class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td width="33%">N&uacute;mero</td>
<td width="33%">Alias</td>
<td width="33%">Disponible Bs.</td>
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
<td class="monto_total"><?php echo formatea_monto_fromWs($totales_tarjetas['credito_disponible']) ?> </td>
</tr>
</tfoot>
</table>
<?php } ?>

<?php if(!empty($creditos)) { ?>
<div style="clear:both;" class="subtitulo">
<h2>Cr&eacute;ditos</h2>
<div></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td width="33%">N&uacute;mero</td>
<td width="33%">Alias</td>
<td width="33%">Saldo Deudor Bs.</td>
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
<?php } ?>

<?php if(!empty($inversiones)) { ?>
<div class="subtitulo">
<h2>Inversiones</h2>
<div></div>
</div>

<table  class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td width="33%">N&uacute;mero</td>
<td width="33%">Monto Invertido Bs.</td>
<td width="33%">Fecha Vencimiento</td>
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
<?php } ?>

<?php if(!empty($lineasCredito)) { ?>
<div style="clear:both;" class="subtitulo">
<h2>Líneas de Crédito</h2>
<div></div>
</div>

<table  class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td width="33%">N&uacute;mero</td>
<td width="33%">Descripción</td>
<td width="33%">Disponible Bs.</td>
</tr>
</thead>
<?php
if(!empty($lineasCredito)) {
	foreach($lineasCredito as $lc){
?>
<tbody>
<tr>
	<td class="texto_centro"><?php echo $lc['LN61LNENUM'] ?></td>
	<td class="texto_centro">&nbsp;</td>
	<td class="monto"><?php echo formatea_monto_fromWs($lc['LN61LNEAMU']); ?></td>
</tr>
</tbody>
<?php 
	} 
}
?>
</table>
<?php } ?>