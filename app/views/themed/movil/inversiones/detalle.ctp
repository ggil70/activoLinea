<div class="subtitulo">
	<h2>Dep&oacute;sito a Plazo <?php echo $this->data['DetalleProducto']['nro_inversion'] ?></h2>
	<div></div>
</div>
<div class="contenedor_encabezado">
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		
		<tr>
			<td class="etiqueta">Alias Inversi&oacute;n</td>
			<td><?php echo $alias; ?></td>
		</tr>
		
		<tr>
			<td class="etiqueta">Estatus:</td>
			<td><?php echo $estatus ?></td>
		</tr>
		
		<tr>
			<td class="etiqueta">Moneda:</td>
			<td><?php echo $detalle['CDSDEACCY']; ?></td>
		</tr>
		
		<tr>
			<td class="etiqueta">Monto Invertido:</td>
			<td><?php echo formatea_monto_fromWs($detalle['CDSDEAMEP']); ?></td>
		</tr>
		
		<tr>
			<td class="etiqueta">Fecha Apertura:</td>
			<td><?php echo str_pad($detalle['CDSOPEDAT1'], 2, '0', STR_PAD_LEFT) . '/' . str_pad($detalle['CDSOPEDAT2'], 2, '0', STR_PAD_LEFT) . '/' . str_pad($detalle['CDSOPEDAT3'], 4, '200', STR_PAD_LEFT); ?></td>
		</tr>
		
		<tr>
			<td class="etiqueta">Fecha Vencimiento:</td>
			<td><?php echo str_pad($detalle['CDSMATDTE1'], 2, '0', STR_PAD_LEFT) . '/' . str_pad($detalle['CDSMATDTE2'], 2, '0', STR_PAD_LEFT) . '/' . str_pad($detalle['CDSMATDTE3'], 4, '200', STR_PAD_LEFT); ?></td>
		</tr>
		
		<tr>
			<td class="etiqueta">Plazo:</td>
			<td><?php echo $detalle['CDSTERLOAN'].' D&iacute;as'; ?></td>
		</tr>
		
		<tr>
			<td class="etiqueta">Tasa Inter&eacute;s:</td>
			<td><?php echo formatea_monto_fromWs($detalle['CDSDEARTE']); ?></td>
		</tr>
		
		<tr>
			<td class="etiqueta" width="50%">Intereses al Vencimiento:</td>
			<td><?php echo formatea_monto_fromWs($detalle['CDSINTMAT']); ?></td>
		</tr>
		
		<tr>
			<td class="etiqueta">Intereses Pagados:</td>
			<td><?php echo formatea_monto_fromWs($detalle['CDSDEAIPY']);?></td>
		</tr>
		
		<tr>
			<td class="etiqueta">Intereses por Pagar:</td>
			<td><?php echo formatea_monto_fromWs($totalInteresPorPagar);?></td>
		</tr>
	</table>
	
	<div class="botonera">

		<?php
		echo $html->link(
			'Volver', 
			$ruta_volver, 
			array('class' => 'button')
		);
		?>

	</div>	
</div>