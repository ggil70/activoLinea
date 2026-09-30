<div class="subtitulo">
<h2>Consulta Tarjeta de Crédito <?php echo enmascaraMovil($this->data['DetalleProducto']['nro_tarjeta']) ?></h2>
<div></div>
</div>

<div class="contenedor_encabezado">

<table class="tabla" cellpadding="0" cellspacing="0">
<thead>
<tr>
<td>Cr&eacute;dito Disponible Bs.</td>
<td>Pago Mínimo Bs.</td>
<td>Fecha Próximo Pago</td>
</tr>
</thead>
<tbody>
<tr>
<td class="monto"><?php echo formatea_monto_fromWs($detalle_tarjeta['CARAVC']); ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($detalle_tarjeta['CARPMA']); ?></td>
<td class="texto_centro"><?php echo formatea_fecha($detalle_tarjeta['CARED1'],$detalle_tarjeta['CARED2'],$detalle_tarjeta['CARED3'])?></td>
</tr>
</tbody>
</table>
</div>
<div class="espacio_vertical_10"></div>

<table class="tabla" cellpadding="0" cellspacing="0">
<thead>
	<tr>
		<td colspan="3" class="resumen_paginacion">
			<div style="text-align:left; width:48%; float:left; font-weight:bold;">
				Movimientos Pendientes por Facturar
			</div>
			<div style="text-align:right; width:48%; float:right;">
				<?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?>
			</div>
		</td>
	</tr>
<tr>
<td>Fecha</td>
<td>Descripción</td>
<td>Monto Bs.</td>
</tr>
</thead>
<tbody>
<?php

if(count($arrData)){
	foreach($arrData as $i){
	$fecha = $i['CCATRDAT'];
	$anio = substr($fecha,0,4);
	$mes = substr($fecha,4,2);
	$dia = substr($fecha,6,2);
	
?> 
<tr>
<td class="texto_centro"><?php echo formatea_fecha($dia,$mes,$anio)?></td>
<td class="descripcion"><?php echo $i['CCATRDSC']?></td>
<td class="monto"><?php if($i['CCATRAMDB']>0){
	echo '<span class="menos">-</span>'.formatea_monto_fromWs($i['CCATRAMDB']);
} elseif($i['CCATRAMCR']>0) {
	echo formatea_monto_fromWs($i['CCATRAMCR']);
}
?></td>
</tr>
<?php
	}
}
else {
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
<td class="monto_total"><?php echo formatea_monto_fromWs($totalesTDC['ABONOSTDC']-$totalesTDC['CREDITOSTDC'])?></td>
</tr>
</tfoot>
</table>

<div class="espacio_vertical_10"></div>

<div class="botonera">
<form class="formulario_enlace" method="post" action="<?php echo Router::url($ruta_regreso) ?>">
<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta']?>" />
<input type="submit" class="button" value="Volver" />
</form>
</div>