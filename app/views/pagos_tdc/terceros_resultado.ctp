<?php

/**
 * Resultado de pago de tarjetas de terceros en banco activo
 **/

?>
<div>
	<h2 class="user">Pagos Tarjeta de Crédito Tercero en Activo</h2>
	<div class="line_title_orange"></div>
</div>

<div>
	<?php for ($i = 0; $i < count($this->data['pasos']); $i++) {
		$class = ($i == $this->data['paso_final']) ? '_actual' : '';
	?>
		<div class="paso_nro<?php echo $class ?>"><?php echo $i + 1 ?></div>
		<div class="paso_desc<?php echo $class ?>"><?php echo $this->data['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<?php
		if ($error == '0') {

		?>
			<thead>
				<tr>
					<td colspan="2" class="resumen_paginacion">
						<p class="exito">
					<?php if ($pendiente) { ?>
						<p>Operación pendiente por ejecutar.</p>
					<?php } else if ($soapRes['IN982ENDFL'] === 'Y') { ?>

						<p style="color:red; font-weight: bold; text-align: center;" class="mensajeTdcError">Hubo un problema con la transaccion, por favor verifique la respuesta.</p>
					<p style="color:red; font-weight: bold; text-align: center;" class="mensajeTdcError"><?php echo $soapRes['mensaje'] ?></p>

					<?php } else if ($error == 1616) { ?>
		 		
					<p style="color:red; font-weight: bold; text-align: center;" class="mensajeTdcError">		<?php echo $descripcion ?>
					</p>

					<?php } else if ($soapRes['IN982ENDFL'] === 'N') { ?>

						Operación realizada satisfactoriamente</p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF
					
					</td>
				</tr>
			</thead>
			<tr>
				<td class="etiqueta">Cuenta de D&eacute;bito:</td>
				<td><?php echo enmascara($this->data['Pagostdc']['cuentaDebitar']) ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Tarjeta del Beneficiario:</td>
				<td><?php echo $this->data['Pagostdc']['numeroTDC'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Nombre del Beneficiario:</td>
				<td><?php echo $this->data['Pagostdc']['nombreBeneficiario'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">C&eacute;dula / RIF del Beneficiario:</td>
				<td><?php echo $this->data['Pagostdc']['tipoCedulaRif'] . '-' . $this->data['Pagostdc']['nroCedulaRif']  ?></td>
			</tr>
			<tr>
				<td class="etiqueta">E-mail del Beneficiario:</td>
				<td><?php echo $this->data['Pagostdc']['emailBeneficiario'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Monto Bs.:</td>
				<td><?php echo $this->data['Pagostdc']['monto'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Descripci&oacute;n:</td>
				<td><?php echo $this->data['Pagostdc']['descripcion'] ?></td>
			</tr>
			<?php if ($this->data['Pagostdc']['afiliar'] == 's') { ?>
				<tr>
					<td class="etiqueta">Alias:</td>
					<td><?php echo ($this->data['Pagostdc']['alias'] != '') ? $this->data['Pagostdc']['alias'] : 'Alias no asignado' ?></td>
				</tr>

				<?php if (empty($juridico)) { ?>
					<tr>
						<td class="etiqueta">Monto m&aacute;ximo Bs.:</td>
						<td><?php echo $this->data['Pagostdc']['monto_maximo'] ?></td>
					</tr>
				<?php } ?>

			<?php
			}
			?>
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
			<?php } ?>
			<?php if ($error_afiliacion) { ?>

				<tr>
					<td colspan="2"></td>
				</tr>
				<tr>
					<td colspan="2" class="botonera">
						<div class="error-message"><?php echo _m('afiliacion.fallida', 'de la tarjeta'); ?></div>
					</td>
				</tr>
			<?php } ?>
		<?php

		} else if ($error == 182) {

		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('producto.invalido', 'el número cuenta a deditar'); ?></div>
					</td>
				</tr>
			</thead>
		<?php
		} else if ($error == 800) {
		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('producto.invalido', 'el número de tarjeta a acreditar'); ?></div>
					</td>
				</tr>
			</thead>
		<?php
		} else if ($error == 8120) {
		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('usuario.no.autorizado', 'el número cuenta a deditar'); ?></div>
					</td>
				</tr>
			</thead>
		<?php
		} else if ($error == 1616) {
		?>
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message"><?php echo _m('cuenta.sobregirada', 'el número cuenta a deditar'); ?></div>
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
		} else if ($error == 8412) {
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
		if ($error != 0) {
		?>
			<!-- <tr>
				<td colspan="2" class="botonera">
					<form class="formulario_enlace" method="post" action="<?php echo Router::url('/pagos_tdc/terceros') ?>">
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
				<form class="formulario_enlace" method="post" action="<?php echo Router::url('/pagos_tdc/terceros') ?>">
					<input type="submit" class="button" value="Volver" />
				</form>
			</td>
		</tr>
	</table>
</div>
