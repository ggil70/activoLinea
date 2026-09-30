<?php
/**
 * Detalle de las inversiones
**/
?>
<div>
	<h2 class="user">Dep&oacute;sito a Plazo</h2>
	<div class="line_title_orange"></div>
</div>
<div class="contenedor_encabezado">

	<?php
	echo $form->create(
		'DetalleProducto', array(
			'url' => array(
				'controller' => 'inversiones',
				'action' => 'detalle'
			),
			'inputDefaults' => array(
				'div' => false,
				'label' => 'etiqueta',
				'before' => '<tr><td class="etiqueta" style="width:20%">',
				'between' => '</td><td class="campo">',
				'after' => '</td></tr>',
				'error' => false
			)
		)
	);

	echo $html->tag(
		'table', null, array(
		'class' => 'formulario',
		'cellpadding' => '0',
		'cellspacing' => '0',
		'border' => '0',
		'align' => 'center',
		'style' => 'width:550px;margin:0 auto;'
			)
	);

	echo $form->input(
		'nro_inversion',
			array(
			'label' => 'N&uacute;mero de Inversi&oacute;n:',
			'type' => 'select',
			'options' => $inversiones,
			'empty' => false,
			'class'=>'cambio_producto'
		)
	);
	echo '</table>';
	echo $form->end() ;
	?>

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		
		<tr>
			<td class="etiqueta">Alias Inversi&oacute;n:</td>
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
		
		<tr>
			<td class="botonera" colspan="2">

				<?php
				echo $html->link(
					'Volver', 
					$ruta_volver, 
					array('class' => 'button')
				);

				echo '&nbsp;&nbsp;';

				echo $html->link(
					'Imprimir', 
					'javascript:print();void(0);', 
					array('class' => 'button')
				);
				?>

			</td>
		</tr>
	</table>
</div>