<?php
/**
 * Confirmación de transferencias a cuentas propias de banco activo
**/
?>
<div class="subtitulo">
<h2>Transferencia Mismo Titular en Activo</h2>
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
			<td class="etiqueta">Cuenta de Débito:</td>
			<td><?php echo enmascaraMovil($this->data['Transferencia']['cuentaDebitar']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Cuenta de Crédito:</td>
			<td><?php echo enmascaraMovil($this->data['Transferencia']['cuentaCreditar']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Monto Bs.:</td>
			<td><?php echo $this->data['Transferencia']['monto'] ?></td>
		</tr>
		<tr>
			<td colspan="2" class="botonera">
				<?php 

				echo $html->link(
					'Volver',
					'/transferencias/propias/v',
					array(
						'class'=>'button bloqueo_repetir'
					)
				);

				echo '&nbsp;&nbsp;';

				echo $html->link(
					'Aceptar',
					'/transferencias/propias_resultado',
					array(
						'class'=>'button bloqueo_repetir'
					)
				);

				?>
			</td>
		</tr>
	</table>
</div>




