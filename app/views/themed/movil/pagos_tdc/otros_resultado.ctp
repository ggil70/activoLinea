<div class="subtitulo">
<h2>Pago Tarjeta Crédito Mismo Titular Otro Banco</h2>
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
	<?php if($error == 0){ ?>
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
				<td><?php echo ($this->data['Pagostdc']['alias']!='') ? $this->data['Pagostdc']['alias'] : 'Alias no asignado' ?></td>
			</tr>
			<?php if( !empty($this->data['Pagostdc']['monto_maximo']) && empty($juridico) ){ ?>
				<tr>
					<td class="etiqueta">Monto m&aacute;ximo Bs.:</td>
					<td><?php echo $this->data['Pagostdc']['monto_maximo'] ?></td>
				</tr>
			<?php
			}
		}?>
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
	else if($error== 800){
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('producto.invalido', 'el número de tarjeta de crédito'); ?></div></td>
		</tr>
		</thead>
	<?php 
	}else if($error== 862){
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.sobregirada'); ?></div></td>
		</tr>
		</thead>
	<?php 
	}
	else if($error == 1912){
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
			<form class="formulario_enlace_right" method="post" action="<?php echo Router::url('/pagos_tdc/otros') ?>">
				<input type="submit" class="button" value="Volver" />
			</form>
		</td>
	</tr>
	
		<tr><td colspan="2" class="botonera">&nbsp;</td></tr>
	</table>
</div>