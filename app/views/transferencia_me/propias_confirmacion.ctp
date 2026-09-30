<?php
/**
 * Confirmación de transferencias a cuentas propias de banco activo
**/
?>
<div>
<h2 class="user">Transferencia Moneda Extranjera Mismo Titular en Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">


	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar Moneda Extranjera:</td>
	</tr>
	</thead>
	<tr>
	<td class="etiqueta">Cuenta de Débito:</td>
	<td><?php echo enmascara($this->data['Transferencia']['cuentaDebitar']) ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Cuenta de Crédito:</td>
	<td><?php echo enmascara($this->data['Transferencia']['cuentaCreditar']) ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Monto:</td>
	<td><?php echo $this->data['Transferencia']['monto'] ?></td>
	</tr>
	<tr>
	<td colspan="2" class="botonera">

	<?php 
	
	echo $html->link(
            'Volver',
            '/transferencia_me/propias/v',
            array(
               'class'=>'button bloqueo_repetir'
            )
	);
	
	echo '&nbsp;&nbsp;';
	
	echo $html->link(
            'Aceptar',
            '/transferencia_me/propias_resultado',
            array(
		'class'=>'button bloqueo_repetir'
            )
	);
	?>

	</td>
	</table>
</div>




