<div class="subtitulo">
<h2>Consulta Movimientos del Cr&eacute;dito <?php echo trim($this->data['DetalleProducto']['nro_credito']) ?></h2>
<div></div>
</div>

<table class="tabla" cellpadding="0" cellspacing="0"  border="0">
<thead>
	<tr>
		<td colspan="3" class="resumen_paginacion">
			<div style="text-align:left; width:48%; float:left; font-weight:bold;">
				Detalle Pr&oacute;ximo Pago
			</div>
			<div style="text-align:right; width:48%; float:right;">
				<?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?>
			</div>
		</td>
	</tr>
</thead>
<thead>
<tr>
	<td class="ancholibre">Fecha<br/>Vencimiento<br/>Pago</td>
	<td class="ancholibre">Total<br/>Cuota<br/>Bs.</td>
	<td class="ancholibre">D&iacute;as<br/>Atraso</td>
</tr>
</thead>
<?php if($total_registros){ ?>
<tbody>
<?php foreach($pagos as $p){ ?>
<tr>
	<td class="texto_centro ancholibre"><?php echo formatea_fecha($p['e02DLPPD1'],$p['e02DLPPD2'],$p['e02DLPPD3']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($p['e02TOAMNT']) ?></td>
	<td class="texto_centro ancholibre"><?php echo $p['e02DLPVEN'] ?></td>
</tr>
<?php } ?>
</tbody>
<?php } else { echo '<tbody><tr><td colspan="3" class="texto_centro">No se encontraron resultados</td></tr></tbody>'; }?>
</table>

<div class="botonera">
	<form id="form_volver" class="formulario_enlace" method="post" action="<?php echo Router::url($ruta_volver) ?>">
	<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo $this->data['DetalleProducto']['nro_credito'] ?>" />
	<input type="submit" class="button" value="Volver" />
	</form>
</div>
