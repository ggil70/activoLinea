<div class="subtitulo">
<h2>Estado de Cuenta <?php echo enmascaraMovil($this->data['DetalleProducto']['nro_cuenta']) ?></h2>
<div></div>
</div>

<div class="contenedor_encabezado">
	<table class="tabla" cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<td>Saldo Total Bs.</td>
		<td>Monto Diferido Bs.</td>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td class="monto"><?php echo formatea_monto_fromWs($res['STMRDSJVReturn']['STMBDSJV']['STBMNB']); ?></td>
		<td class="monto"><?php echo formatea_monto_fromWs($res['STMRDSJVReturn']['STMBDSJV']['STBUNC']); ?></td>
	</tr>
	</tbody>
	<thead>
	<tr>
		<td>Monto Bloqueado Bs.</td>
		<td>Saldo Disponible Bs.</td>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td class="monto"><?php echo formatea_monto_fromWs($res['STMRDSJVReturn']['STMBDSJV']['STBHOL']); ?></td>
		<td class="monto"><?php echo formatea_monto_fromWs($res['STMRDSJVReturn']['STMBDSJV']['STBAVL']) ?></td>
	</tr>
	</tbody>
	</table>
</div>

<div class="espacio_vertical_10"></div>

<table cellpadding="0" cellspacing="0" class="tabla">
<thead> 
<tr>
	<td colspan="3" class="resumen_paginacion"><?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?></td>
</tr>
<tr>
<td>Fecha</td>
<td>Descripci&oacute;n</td>
<td>Monto</td>
</tr>
</thead>
<tbody>
<?php 
if(count($arrData)){
	foreach($arrData as $i){
	?>
		<tr>
			<td class="texto_centro"><?php echo formatea_fecha($i['STMBD1'],$i['STMBD2'],$i['STMBD3'])?></td>
			<td class="descripcion">
				<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/movimiento') ?>">
				<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo trim($this->data['DetalleProducto']['nro_cuenta']) ?>" />
				<input type="hidden" name="data[DetalleProducto][referencia]" value="<?php echo trim($i['STMCKN']) ?>" />
				<input type="hidden" name="data[DetalleProducto][comision_cheque]" value="<?php echo trim($i['STMMEM']) ?>" />
				<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo trim($i['STMCDE']) ?>" />
				<input type="submit" class="boton_enlace" style="white-space:normal; height:auto;" value="<?php echo trim($i['STMNAR']) ?>" />
				</form>			
			</td>
			<td class="monto"><?php
			
			if($i['STMDCC']=='0'){
				echo '<span class="menos">-</span>';
			}
			
			echo formatea_monto_fromWs($i['STMAMT']);?>
			</td>
		</tr>
	<?php
	}
} else {
	?>
		<tr>
			<td colspan="3" class="texto_centro">No se encontraron resultados</td>
		</tr>
<?php
}
?>
</tbody>
<tfoot>
<tr>
<td colspan="2" class="total">Total Bs.:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['ABONOS']-$totales['CREDITOS'])?> </td>
</tr>
</tfoot>
</table>

<div class="botonera">
<form class="formulario_enlace" method="post" action="<?php echo Router::url($ruta_regreso) ?>">
	<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']; ?>" />
	<input type="submit" class="button" value="Volver" />
</form>

</div>