<div class="subtitulo">
	<h2>Pago Tarjeta Crédito Tercero en Otro Banco</h2>
	<div></div>
</div>

<div>
	<?php for($i = 0; $i < count($this->data['pasos']); $i++) { 
	$class = ($i==1) ? '_actual' : '';
	if($i==1){
	?>
	<div class="paso_desc<?php echo $class ?>"><?php echo $this->data['pasos'][$i] ?></div>
	<?php }} ?>
</div>

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
			<td class="etiqueta">Banco del Beneficiario:</td>
			<td><?php echo $this->data['Pagostdc']['banco'] ?></td>
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
			<td><?php echo $this->data['Pagostdc']['tipoCedulaRif'] . '-' . $this->data['Pagostdc']['nroCedulaRif'] ?></td>
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
			<td><?php echo @$this->data['Pagostdc']['descripcion'] ?></td>
		</tr>
		<?php if($this->data['Pagostdc']['afiliar'] == 's'){ ?>
			<tr>
				<td class="etiqueta">Alias:</td>
				<td><?php echo ($this->data['Pagostdc']['alias'] != '') ? $this->data['Pagostdc']['alias'] : 'Alias no asignado' ?></td>
			</tr>
			<?php if(!empty($this->data['Pagostdc']['monto_maximo']) && empty($juridico) ){ ?>
				<tr>
					<td class="etiqueta">Monto m&aacute;ximo Bs.:</td>
					<td><?php echo $this->data['Pagostdc']['monto_maximo'] ?></td>
				</tr>
			<?php
			}
		}?>
		
		<tr>
			<td colspan="2" class="botonera">

			<?php
			if($this->data['Pagostdc']['tipoDestinatario'] == 'a'){
				echo $html->link(
					'Volver',
					'/pagos_tdc/terceros_otros_afiliada',
					array(
						'class'=>'button bloqueo_repetir'
					)
				);
			} else {
				echo $html->link(
					'Volver',
					'/pagos_tdc/terceros_otros_noafiliada',
					array(
						'class'=>'button bloqueo_repetir'
					)
				);
			}

			echo '&nbsp;&nbsp;';

			echo $html->link(
				'Aceptar',
				'/pagos_tdc/terceros_otros_resultado',
				array(
					'class'=>'button bloqueo_repetir'
				)
			);
			?>

			</td>
		</tr>
	</table>
</div>