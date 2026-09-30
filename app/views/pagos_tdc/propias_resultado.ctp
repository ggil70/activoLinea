<?php

/**
 * Resultado de pago de tarjetas propias en banco activo
 **/
?>
<div>
	<h2 class="user">Pagos Tarjeta de Cr&eacute;dito Mismo Titular en Activo</h2>
	<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<?php
		if ($error == 0) {
		?>
			<thead>
				<tr>
					<td colspan="2" class="resumen_paginacion">
						<p class="exito">
							<?php if ($pendiente) { ?>
								Operación pendiente por ejecutar.</p>
					<?php } else if ($soapRes['IN982ENDFL'] === 'Y') { ?>
						<p style="color:red; font-weight: bold; text-align: center;" class="mensajeTdcError">Hubo un problema con la transaccion, por favor verifique la respuesta.</p>
					<?php } else if ($soapRes['IN982ENDFL'] === 'N') { ?>
						Operaci&oacute;n realizada satisfactoriamente</p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF
					<?php } ?>
					</td>
				</tr>
			</thead>
			<tr>
				<td class="etiqueta">Cuenta de D&eacute;bito:</td>
				<td><?php echo enmascara($this->data['Pagostdc']['cuentaDebitar']) ?></td>
			</tr>
			<tr>
				<td class="etiqueta">N&uacute;mero Tarjeta de Cr&eacute;dito:</td>
				<td><?php echo enmascara($this->data['Pagostdc']['numeroTDC']) ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Monto Bs.:</td>
				<td><?php echo $this->data['Pagostdc']['monto'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Descripci&oacute;n:</td>
				<td><?php echo $this->data['Pagostdc']['descripcion'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Fecha:</td>
				<td><?php echo date('d/m/Y') ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Respuesta:</td>
				<td><?php echo $soapRes['mensaje'] ?></td>
			</tr>
			<?php if (!empty($referencia)) { ?>
				<tr>
					<td class="etiqueta">Referencia:</td>
					<td><?php echo $referencia ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td colspan="2" class="botonera">

					<?php

					echo $html->link(
						'Imprimir',
						'javascript:print();void(0);',
						array(
							'class' => 'button'
						)
					);

					if (!$pendiente) {
						echo '&nbsp;&nbsp;';

						echo $html->link(
							'Descargar PDF',
							'/consultas/descargar_pdf',
							array(
								'class' => 'button'
							)
						);
					}

					?>

				</td>
			</tr>
		<?php
		} else if ($error == 182) {
		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('producto.invalido', 'el n�mero cuenta a deditar'); ?></div>
					</td>
				</tr>
			</thead>
		<?php
		} else if ($error == 800) {
		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('producto.invalido', 'el n�mero cuenta a deditar'); ?></div>
					</td>
				</tr>
			</thead>
		<?php
		} else if ($error == 8120) {
		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('usuario.no.autorizado', 'el n�mero cuenta a deditar'); ?></div>
					</td>
				</tr>
			</thead>
		<?php
		} else if ($error == 1616) {
		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('cuenta.sobregirada', 'el n�mero cuenta a deditar'); ?></div>
					</td>
				</tr>
			</thead>
		<?php
		} else if ($error == 9920) {
		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('deuda.tdc.menor', 'de la tarjeta'); ?></div>
					</td>
				</tr>
			</thead>
		<?php
		} else if ($error == 1912) {
		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('monto.excedido', 'del pago'); ?></div>
					</td>
				</tr>
			</thead>
		<?php
		} else {
		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('operacion.fallida'); ?></div>
					</td>
				</tr>
			</thead>
		<?php
		}
		if ($error != '') {
		?>
			<!-- <tr>
				<td colspan="2" class="botonera">
					<form class="formulario_enlace" method="post" action="<?php echo Router::url('/pagos_tdc/propias') ?>">
						<input type="submit" class="button" value="Volver" />
					</form>
				</td>
			</tr> -->
		<?php
		}
		?>
		<!--mostrar siempre el voton de volver, modifiacion hecha el 23-08-2024 -->
		<tr>
			<td colspan="2" class="botonera">
				<form class="formulario_enlace" method="post" action="<?php echo Router::url('/pagos_tdc/propias') ?>">
					<input type="submit" class="button" value="Volver" />
				</form>
			</td>
		</tr>
	</table>
</div>