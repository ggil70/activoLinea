<div class="subtitulo">
<h2>Cuentas Bancarias</h2>
<div></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>N&uacute;mero</td>
<td>Alias</td>
<td>Disponible Bs.</td>

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

<div class="botonera">
<?php echo $html->link(
	'Volver',
	'/consultas/integral',
	array(
		'class'=>'button'
	)
); ?>
</div>