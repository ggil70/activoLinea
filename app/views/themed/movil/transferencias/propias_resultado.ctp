<?php
/**
 * Resultado de transferencias a cuentas propias en banco activo
**/
?>
<div class="subtitulo">
<h2>Transferencia Mismo Titular en Activo</h2>
<div></div>
</div>

<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>

<div class="contenedor_formulario">

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
<thead>
<?php 
	if($error == 0){
 ?>
		<tr>
		<td colspan="2" class="resumen_paginacion"><p class="exito">
		<?php if($pendiente){ ?>
		Operación pendiente por ejecutar.</p>
		<?php } else { ?>
		Operación realizada satisfactoriamente</p>
		<?php } ?>
		</td>
		</tr>
		</thead>

		<tbody>
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
		<td class="etiqueta">Fecha:</td>
		<td><?php echo date('d/m/Y') ?></td>
		</tr>
		<?php if(!empty($referencia)){ ?>
			<tr>
				<td class="etiqueta">Referencia:</td>
				<td><?php echo $referencia ?></td>
			</tr>
		<?php }?>

	</tbody>
		</table>
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
<?php 
}
else if($error == 1616){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.sobregirada'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error == 223){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.inactiva', 'la cuenta'); ?></div></td>
	</tr>
	</thead>
<?php 
}
else if($error == 1912){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('monto.excedido', 'de la transferencia'); ?></div></td>
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
		<form class="formulario_enlace_right" method="post" action="<?php echo Router::url('/transferencias/propias') ?>">
			<input type="submit" class="button" value="Volver" />
		</form>
	</td>
</tr>

</table>
</div>