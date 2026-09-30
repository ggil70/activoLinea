<?php
/**
 * Listado de las inversiones
**/
?>
<div>
<h2 class="user">Inversiones</h2>
<div class="line_title_orange"></div>
</div>
<div class="contenedor_formulario">
<table  class="tabla" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
<thead>
<tr>
<td>Alias</td>
<td>N&uacute;mero</td>
<td>Monto Invertido Bs.</td>
<td>Fecha Vencimiento</td>
<td>Intereses Ganados Bs.</td>
</tr>
</thead>
<?php if(!empty($inversiones)) { ?>
<tbody>
	<?php 
	if(count($inversiones)){
		foreach($inversiones as $c){
		?>
		<tr>
			<td class="texto_centro"><?php echo $c['ALIAS']; ?></td>
			<td class="texto_centro">
				<form class="formulario_enlace" method="post" action="<?php echo Router::url('/inversiones/detalle'); ?>">
				<input type="hidden" name="data[DetalleProducto][nro_inversion]" value="<?php echo $c['SUMACN']; ?>" />
				<input type="submit" class="boton_enlace" value="<?php echo $c['SUMACN']; ?>" />
				</form>
			</td>
			<td class="monto"><?php echo formatea_monto_fromWs($c['SUMBAL']); ?></td>
			<td class="texto_centro"><?php  echo formatea_fecha($c['CDSMATDTE1'],$c['CDSMATDTE2'],$c['CDSMATDTE3']); ?></td>
			<td class="monto"><?php echo formatea_monto_fromWs($c['SUMAVB']) ?></td>
		</tr>
		<?php
		}
	} else{
		echo '<td class="texto_centro" colspan="5">No se encontraron inversiones</td>';
	}
	?>
</tbody>
<?php } ?>
<tfoot>
<tr>
<td class="total" colspan="2">Total Bs.:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['SUMBAL']) ?> </td>
<td class="monto_total">&nbsp;</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['SUMAVB']) ?> </td>
</tr>
</tfoot>
</table>
</div>
