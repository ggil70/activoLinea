<div class="subtitulo">
	<h2>Pagos Tarjeta de Crédito Mismo Titular en Activo</h2>
	<div></div>
</div>

<div class="paso_desc_actual">Confirmaci&oacute;n</div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
			</tr>
		</thead>
		<tr>
			<td class="etiqueta">Cuenta de D&eacute;bito:</td>
			<td><?php echo enmascaraMovil($this->data['Pagostdc']['cuentaDebitar']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Tarjeta de Cr&eacute;dito:</td>
			<td><?php echo enmascaraMovil($this->data['Pagostdc']['numeroTDC']) ?></td>
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
			<td colspan="2" class="botonera">

			<?php 

			echo $html->link(
				'Volver',
				'/pagos_tdc/propias/v',
				array(
					'class'=>'button bloqueo_repetir'
				)
			);

			echo '&nbsp;&nbsp;';

			echo $html->link(
				'Aceptar',
				'/pagos_tdc/propias_resultado',
				array(
					'class'=>'button bloqueo_repetir'
				)
			);

			?>

			</td>
		</tr>
	</table>
</div>