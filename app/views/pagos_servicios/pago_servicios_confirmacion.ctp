<?php
/**
 * Formulario de recarga postpago confirmacion de datos
**/
?>
<div>
<h2 class="user">Pago Servicios Postpago</h2>
<div class="line_title_orange"></div>
</div>
<div>
	<?php for($i = 0; $i < count($this->data['pasos']); $i++) { 
	$class = ($i==1) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $this->data['pasos'][$i] ?></div>
	<?php } ?>
</div>
<div class="espacio_vertical_20"></div>


<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<?php
		if($error == 0){
	?>
	<thead>
	<tr>
		<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
		<td class="etiqueta">Cuenta de D&eacute;bito:</td>
		<td><?php echo enmascara($this->data['PagoServicios']['cuentaDebitar']) ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Tipo de Operaci&oacute;n:</td>
		<td><?php echo $this->data['PagoServicios']['operacion'] ?></td>
	</tr>
	<?php if ($this->data['PagoServicios']['tipoServicio']!=null){ ?>
        <tr>
		<td class="etiqueta">Tipo de Servicio:</td>
		<td><?php echo $servicios['s'][$this->data['PagoServicios']['tipoServicio']] ?></td>
	</tr>
<?php }?>
	<tr>
		<td class="etiqueta">
			<?php if($this->data['PagoServicios']['tipoOperacion'] == 'sd'){?>
				N&uacute;mero de L&iacute;nea 
			<?php }else{ ?>
 		    	N&uacute;mero Servicio o Cuenta Contrato:
        	<?php } ?> 
		</td>
		<td><?php echo $this->data['PagoServicios']['numeroContrato'] ?></td>
	</tr>
	<?php if($this->data['PagoServicios']['tipoOperacion'] != 'sm'){ ?>
		<tr>
			<td class="etiqueta">Nombre Beneficiario:</td>
			<td><?php echo $this->data['PagoServicios']['nombreBeneficiario'] ?></td>
		</tr>
	<?php }?>
	<tr>
		<td class="etiqueta">Monto Bs:</td>
		<td><?php echo $this->data['PagoServicios']['monto'] ?></td>
	</tr>
	<tr>
		<td colspan="2" class="botonera">

			<?php 			
                            if($this->data['PagoServicios']['tipoDestinatario'] == 'a'){
                                    echo $html->link(
                                            'Volver',
                                            '/pagos_servicios/afiliados',
                                            array(
                                                    'class'=>'button bloqueo_repetir'
                                            )
                                    );
                            } else {
                                    echo $html->link(
                                            'Volver',
                                            '/pagos_servicios/noafiliados',
                                            array(
                                                    'class'=>'button bloqueo_repetir'
                                            )
                                    );
                            }

                            echo '&nbsp;&nbsp;';

                            echo $html->link(
                                    'Aceptar',
                                    '/pagos_servicios/pago_servicios_resultado',
                                    array(
                                            'class'=>'button bloqueo_repetir'
                                    )
                            );
			?>

		</td>
	</tr>
	<?php 
	}else if($error == 9996){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('servicio.nocomunicacion'); ?></div></td>
	</tr>
	</thead>
<?php 
}
	else{
	?>
	<thead>
	<tr>
		<td class="resumen_paginacion texto_centro">
			<div class="error-message"><?php echo _m('error.fatal'); ?></div>
		</td>
	</tr>
	</thead>
	<?php 
	}if($error != ''){
	?>
		<tr>
			<td colspan="2" class="botonera"  align="right">
				<?php
					echo $html->link(
						'Volver',
						'/pagos_servicios/afiliados',
						array(
							'class'=>'button bloqueo_repetir'
						)
					);
				?>
			</td>
		</tr>
	<?php } ?>
	</table>
</div>




