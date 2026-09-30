<?php
    /**
     * Vista para la confirmación afiliaciones pago activo
     */
?>
<div>
<h2 class="user">Afiliaciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
		<td class="etiqueta">Tipo de Afiliaci&oacute;n:</td>
		<td>Saldo Activo</td>
	</tr>
	<tr>
		<td class="etiqueta">Cuenta:</td>
		<td><?php echo $this->Activo->enmascara('01710000'.$this->data['Afiliacion']['numero_cuenta']) ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Tarjeta / Cuenta Cash:</td>
		<td><?php 
		if (substr($this->data['Afiliacion']['numero_tarjeta'],10,1) !=  " ") {
			echo $this->Activo->enmascara($this->data['Afiliacion']['numero_tarjeta']);
		}else{
		echo $this->Activo->enmascara(substr($this->data['Afiliacion']['numero_tarjeta'], 0,9)); 
		}
		?></td>
	</tr>
	<tr>
		<td colspan="2" class="botonera">

		<?php 
		echo $html->link(
			'Volver',
			'/afiliaciones/listado_saldo_activo',
			array(
				'class'=>'button',
				'id'=>'volver'
			)
		);
		
		echo '&nbsp;&nbsp;&nbsp;';
		
		echo $html->link(
			'Aceptar',
			'/afiliaciones/eliminar_resultado_saldo_activo',
			array(
				'class'=>'button'
			)
		); 
		?>
		</td>
	</table>
</div>

