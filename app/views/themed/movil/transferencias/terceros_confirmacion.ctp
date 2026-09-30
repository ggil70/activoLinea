<?php
/**
 * Confirmación de transferencias a cuentas de terceros en banco activo
**/
?>
<div class="subtitulo">
<h2>Transferencia Tercero en Activo</h2>
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
	<td class="etiqueta">Cuenta de Débito:</td>
	<td><?php echo enmascaraMovil($this->data['Transferencia']['cuentaDebitar']) ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Número Cuenta Beneficiario:</td>
	<td><?php echo $this->data['Transferencia']['cuentaCreditar'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Nombre Beneficiario:</td>
	<td><?php echo $this->data['Transferencia']['nombreBeneficiario'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Cédula / RIF del beneficiario:</td>
	<td><?php echo $this->data['Transferencia']['tipoCedulaRif'].'-'.$this->data['Transferencia']['nroCedulaRif'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">E-mail del Beneficiario:</td>
	<td><?php echo $this->data['Transferencia']['emailBeneficiario'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Monto Bs.:</td>
	<td><?php echo $this->data['Transferencia']['monto'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Descripci&oacute;n:</td>
	<td><?php echo $this->data['Transferencia']['descripcion'] ?></td>
	</tr>
	<?php if($this->data['Transferencia']['afiliar'] == 's'){ ?>
		<tr>
		<td class="etiqueta">Alias:</td>
		<td><?php echo ($this->data['Transferencia']['alias']!='') ? $this->data['Transferencia']['alias'] : 'Alias no asignado' ?></td>
		</tr>
		<?php if( !empty($this->data['Transferencia']['monto_maximo']) && empty($juridico) ){ ?>
		<tr>
		<td class="etiqueta">Monto m&aacute;ximo Bs:</td>
		<td><?php echo $this->data['Transferencia']['monto_maximo'] ?></td>
		</tr>
	<?php
		}
	}?>
	<tr>
	<td colspan="2" class="botonera">

	<?php 
	
	if($this->data['Transferencia']['tipoDestinatario'] == 'a'){
		echo $html->link(
			'Volver',
			'/transferencias/terceros_afiliados',
			array(
				'class'=>'button bloqueo_repetir'
			)
		);
	} else {
		echo $html->link(
			'Volver',
			'/transferencias/terceros_noafiliados',
			array(
				'class'=>'button bloqueo_repetir'
			)
		);
	}
	
	echo '&nbsp;&nbsp;';

	echo $html->link(
		'Aceptar',
		'/transferencias/terceros_resultado',
		array(
			'class'=>'button bloqueo_repetir'
		)
	);


	?>

	</td>
	</tr>
	</table>
</div>




