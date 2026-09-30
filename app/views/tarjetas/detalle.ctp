<?php

/**
 * Consulta de detalle de la tarjeta de crédito
 **/
?>
<div>
	<h2 class="user">Consulta Tarjeta de Cr&eacute;dito</h2>
	<div class="line_title_orange"></div>
</div>

<div class="contenedor_encabezado">
	<?php

	echo $form->create(
		'DetalleProducto',
		array(
			'url' => array(
				'controller' => 'tarjetas',
				'action' => 'detalle'
			),
			'inputDefaults' => array(
				'div' => false,
				'label' => 'etiqueta',
				'before' => '<tr><td class="etiqueta">',
				'between' => '</td><td class="campo">',
				'after' => '</td></tr>',
				'error' => false
			)
		)
	);

	echo $html->tag(
		'table',
		null,
		array(
			'class' => 'formulario',
			'cellpadding' => '0',
			'cellspacing' => '0',
			'border' => '0'
		)
	);

	if ($flag_buscar) {
		echo '<td class="etiqueta">N&uacute;mero Tarjeta:</td><td>' . enmascara($this->data['DetalleProducto']['nro_tarjeta']) . '</td>';
	} else {
		echo $form->input(
			'nro_tarjeta',
			array(
				'label' => 'N&uacutemero Tarjeta:',
				'type' => 'select',
				'options' => $tarjetaVarias,
				'empty' => false,
				'class' => 'cambio_producto'
			)
		);
	}

	echo '</table>';
	echo '<input type="hidden" name="data[DetalleProducto][tipo]" value="' . $this->data['DetalleProducto']['tipo'] . '">';
	echo '<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="' . $this->data['DetalleProducto']['nro_tarjeta_padre'] . '">';
	echo $form->end();

	?>

	<table class="tabla" cellpadding="0" cellspacing="0" width="100%">
		<thead>
			<tr>
				<td>Tipo de Producto</td>
				<td>Alias Tarjeta</td>
				<td>L&iacute;mite Cr&eacute;dito Bs</td>
				<td>L&iacute;mite Cr&eacutedito UVC</td>
				<td>Cr&eacutedito Disponible Bs.</td>				
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="texto_centro"><?php echo $consultaTdc['franquicia']; ?></td>
				<td class="texto_centro"><?php echo $alias; ?></td>
				<td class="monto"><?php echo formatea_monto_fromWs($consultaTdc['limiteBS']); ?></td>
				<td class="monto"><?php echo formatea_monto_fromWs($consultaTdc['limiteUVC']); ?></td>
				<td class="monto"><?php echo formatea_monto_fromWs($consultaTdc['creditoBS']); ?></td>
			</tr>
		</tbody>
		<thead>

		<tr>
			<td>Saldo Actual Bs.</td>
			<td>Saldo Actual UVC.</td>
			<td>Pago M&iacutenimo Bs.</td>
			<td>Fecha Pr&oacuteximo Pago</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="monto"><?php echo formatea_monto_fromWs($consultaTdc['saldo_actual']); ?></td>
				<td class="monto"><?php echo formatea_monto_fromWs($consultaTdc['saldo_actual_uvc']); ?></td> 
				<td class="monto"><?php echo formatea_monto_fromWs($consultaTdc['pago_minimo']); ?></td>
				<td class="texto_centro"><?php echo formatea_fecha($consultaTdc['dia_pago'], $consultaTdc['mes_pago'], $consultaTdc['anio_pago']); ?></td>
			</tr>
		</tbody>
	</table>

 
	<?php if (!isset($flag_buscar) || empty($flag_buscar)) { ?>
		<div class="espacio_vertical_10"></div>
		<table class="tabla" cellpadding="0" cellspacing="0" style="width:100%">
			<tbody>
				<tr>
					<td align="center" width="33%">
						<form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjetas/buscar'); ?>">
							<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta'] ?>" />
							<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta_padre'] ?>" />
							<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $this->data['DetalleProducto']['tipo'] ?>" />
							<input type="submit" class="boton_enlace" value="Búsqueda Avanzada" />
						</form>
					</td>
					<td align="center" width="33%">
						<form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjetas/pagos_pendientes') ?>">
							<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta'] ?>" />
							<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta_padre'] ?>" />
							<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $this->data['DetalleProducto']['tipo'] ?>" />
							<input type="submit" class="boton_enlace" value="Pagos Pendientes por Aplicar" />
						</form>
					</td>
					<td align="center" width="33%">
						<form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjetas/estado_tarjeta'); ?>">
							<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta'] ?>" />
							<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta_padre'] ?>" />
							<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $this->data['DetalleProducto']['tipo'] ?>" />
							<input type="submit" class="boton_enlace" value="Estado de Cuenta Histórico" />
						</form>
					</td>
				</tr>
			</tbody>
		</table>
	<?php } ?> 
</div>
<div class="espacio_vertical_10"></div>

<table class="tabla" cellpadding="0" cellspacing="0" style="margin:0 auto; width:700px;">
	<thead>
		<tr>
			<td colspan="5" class="resumen_paginacion">
				<div style="text-align:left; width:48%; float:left; font-weight:bold;">
					<!-- Movimientos Pendientes por Facturar -->
					Movimientos
				</div>
				<div style="text-align:right; width:48%; float:right;">
					<?php echo $this->element('paginador_resumen', array('total' => $total_registros)) ?>
				</div>
			</td>
		</tr>
	</thead>
	<thead>
		<tr>
			<td>Fecha</td>
			<td>Referencia</td>
			<td>Descripci&oacuten</td>
			<td>Cargo Bs.</td>
			<td>Abono Bs.</td>
		</tr>
	</thead>
	<tbody>
		<?php
		
		// die(json_encode($moviendotosTdc));

		if (count($moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'])) {

			foreach ($moviendotosTdc['ConsultaAutorizacionesenLineaTDCResponse'] as $i) {
			
				// $fecha = $i['diatxn'] . '/' . $i['mestxn'] . '/' . $i['aniotxn'];
				$anio = $i['aniotxn'];
				$mes = $i['mestxn'];
				$dia = $i['diatxn'];

		?>
				<tr>
					<td class="texto_centro"><?php echo formatea_fecha($dia, $mes, $anio) ?></td>
					<td class="texto_centro"><?php echo $i['nroref'] ?></td>
					<td class="descripcion"><?php echo $i['conceptotxn'] ?></td>
					
					<?php 
						if($i['tipotxn'] !== 'C'){

							?> 

							<td class="monto">0,00</td>
							<td class="monto"><?php echo formatea_monto_fromWs($i['mtotxn']) ?></td> 
							
							<?php

						}else{
							?>
							
							<td class="monto"><?php echo formatea_monto_fromWs($i['mtotxn']) ?></td> 
							<td class="monto">0,00</td> 
							<?php
						}
					
					?>
					
				</tr>
			<?php
			}
		} else {
			?>
			<tr>
				<td colspan="5" class="texto_centro">No se encontraron resultados</td>
			</tr>
		<?php
		}
		?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3" class="total">Total Bs.:</td>
			<td class="monto_total"><?php echo formatea_monto_fromWs($totalesTDC['CREDITOSTDC']) ?></td>
			<td class="monto_total"><?php echo formatea_monto_fromWs($totalesTDC['ABONOSTDC']) ?></td>
		</tr>
		<tr>
			<td colspan="5" class="paginador">
				<?php
				if ($flag_buscar) {
					$modelo = 'BusquedaCuenta';
				} else {
					$modelo = 'DetalleProducto';
				}

				echo $form->create(
					$modelo,
					array(
						'url' => array(
							'controller' => 'tarjetas',
							'action' => $this->action
						),
					)
				);

				if (!empty($this->data[$modelo])) {
					foreach ($this->data[$modelo] as $k => $v) {
						if (is_array($v)) {
							foreach ($v as $sk => $sv) {
								echo $form->hidden($modelo . '.' . $k . '.' . $sk);
							}
						} else {
							echo $form->hidden($k);
						}
					}
				}

				echo $this->element('paginador', array('total' => $total_registros));

				echo $form->end();
				?>
			</td>
		</tr>
	</tfoot>
</table>

<div class="espacio_vertical_10"></div>

<div class="botonera" style="width:700px;margin:0 auto">

	<form class="formulario_enlace" method="post" action="<?php echo Router::url($ruta_regreso) ?>">
		<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta'] ?>" />
		<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta_padre'] ?>" />
		<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $this->data['DetalleProducto']['tipo'] ?>" />
		<input type="submit" class="button" value="Volver" />
	</form>

	<?php if (count($arrData)) { ?>
		<form name="formulario_enlace" class="formulario_enlace" method="post" onsubmit="print();return false;">
			<input type="submit" class="button" value="Imprimir" />
		</form>
		<form name="formulario_enlace" class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjetas/exportar') ?>">
			<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta'] ?>" />
			<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta_padre'] ?>" />
			<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $this->data['DetalleProducto']['tipo'] ?>" />
			<input type="submit" class="button" value="Exportar" />
		</form>
	<?php } ?>
</div>