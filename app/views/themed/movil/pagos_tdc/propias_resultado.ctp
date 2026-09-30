<div class="subtitulo">
<h2>Pagos Tarjeta de Crédito Mismo Titular en Activo</h2>
<div></div>
</div>

<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>

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
			<td class="etiqueta">Fecha:</td>
			<td><?php echo date('d/m/Y') ?></td>
		</tr>
		<?php if(!empty($referencia)){ ?>
		<tr>
			<td class="etiqueta">Referencia:</td>
			<td><?php echo $referencia ?></td>
		</tr>
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
	}else if($error == 800){
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('producto.invalido', 'el número cuenta a deditar'); ?></div></td>
		</tr>
		</thead>
	<?php 
	}else if($error == 8120){
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
	}else if($error == 1912){
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
			<td colspan="2" class="botonera submit">
				<?php
				echo $html->link(
					'Volver',
					'/pagos_tdc/propias',
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