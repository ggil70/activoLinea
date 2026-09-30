<div class="subtitulo">
	<h2>Pagos Tarjeta de Cr&eacute;dito Tercero en Activo</h2>
	<div></div>
</div>

<div>
	<?php for($i = 0; $i < count($this->data['pasos']); $i++) { 
	$class = ($i==$this->data['paso_final']) ? '_actual' : '';
	if($i==$this->data['paso_final']){
	?>
	<div class="paso_desc<?php echo $class ?>"><?php echo $this->data['pasos'][$i] ?></div>
	<?php }} ?>
</div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<?php if($error==0){ ?>
		<thead>
			<tr>
				<td colspan="2" class="resumen_paginacion"><p class="exito">
					<?php if($pendiente){ ?>
					Operación pendiente por ejecutar.</p>
					<?php } else { ?>
					Operación realizada satisfactoriamente.</p>
					<?php } ?>	
				</td>
			</tr>
		</thead>
		<tr>
			<td class="etiqueta">Cuenta de D&eacute;bito:</td>
			<td><?php echo enmascaraMovil($this->data['Pagostdc']['cuentaDebitar']) ?></td>
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
			<td><?php echo $this->data['Pagostdc']['tipoCedulaRif'].'-'.$this->data['Pagostdc']['nroCedulaRif']  ?></td>
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
		<?php if($this->data['Pagostdc']['afiliar'] == 's'){ ?>
			<tr>
				<td class="etiqueta">Alias:</td>
				<td><?php echo ($this->data['Pagostdc']['alias']!='') ? $this->data['Pagostdc']['alias'] : 'Alias no asignado' ?></td>
			</tr>
			<?php if( empty($juridico) ){ ?>
			<tr>
				<td class="etiqueta">Monto m&aacute;ximo Bs.:</td>
				<td><?php echo $this->data['Pagostdc']['monto_maximo'] ?></td>
			</tr>
			<?php } ?>
		<?php } ?>
		<tr>
			<td class="etiqueta">Fecha:</td>
			<td><?php echo date('d/m/Y') ?></td>
		</tr>
		<?php if(!empty($referencia)){ ?>
		<tr>
			<td class="etiqueta">Referencia:</td>
			<td><?php echo $referencia ?></td>
		</tr>
		<?php } ?>
		
		<?php if($error_afiliacion){ ?>
		<tr><td colspan="2" ></td></tr>
		<tr><td colspan="2" class="botonera" ><div class="error-message"><?php echo _m('afiliacion.fallida', 'de la tarjeta'); ?></div></td></tr>
		<?php } ?>
	<?php 
	}
	else if($error == 182){
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('producto.invalido', 'el número cuenta a deditar'); ?></div></td>
		</tr>
		</thead>
	<?php 
	}
	else if($error == 800){
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('producto.invalido', 'el número de tarjeta a acreditar'); ?></div></td>
		</tr>
		</thead>
	<?php 
	}
	else if($error == 8120){
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('usuario.no.autorizado', 'el número cuenta a deditar'); ?></div></td>
		</tr>
		</thead>
	<?php 
	}else if($error==1616){
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.sobregirada', 'el número cuenta a deditar'); ?></div></td>
		</tr>
		</thead>
	<?php 
	}else if($error == 9920){
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('deuda.tdc.menor', 'de la tarjeta'); ?></div></td>
		</tr>
		</thead>
	<?php 
	}else if($error == 8412){
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('monto.excedido', 'del pago'); ?></div></td>
		</tr>
		</thead>
	<?php 
	}
	else{
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('operacion.fallida'); ?></div></td>
		</tr>
		</thead>
	<?php
	}
	
	?>
		<tr>
			<td colspan="2" class="botonera">
				<?php
				echo $html->link(
					'Volver',
					'/pagos_tdc/terceros',
					array(
						'class'=>'button'
					)
				);
				?>
			</td>
		</tr>
		<tr><td colspan="2" class="botonera">&nbsp;</td></tr>
	</table>
</div>
