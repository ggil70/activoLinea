<?php
/**
 * Formulario de pago servicio resultado de la operacion
**/
?>

<div>
<h2 class="user">Pago Servicios Postpago</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">


<?php 


	if($error == 0){
	 ?>
		<thead>
			<tr>
                            <td colspan="2" class="resumen_paginacion"><p class="exito">
                            <?php if($pendiente){ ?>
                                    Operación pendiente por ejecutar.</p>
                            <?php } else { ?>
                            Estimado Cliente, su pago ha sido exitoso.</p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF
                            <?php } ?>
                            </td>
			</tr>
		</thead>

		
		<tr>
			<td class="etiqueta">Cuenta de D&eacute;bito:</td>
			<td><?php echo enmascara($this->data['PagoServicios']['cuentaDebitar']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Tipo de Operación:</td>
			<td><?php echo $this->data['PagoServicios']['operacion'] ?></td>
		</tr>
		<?php if ($this->data['PagoServicios']['tipoServicio']!=null){?>
                <tr>
                        <td class="etiqueta">Tipo de Servicio:</td>
                        <td><?php echo $servicios['s'][$this->data['PagoServicios']['tipoServicio']] ?></td>
                </tr>
                <?php } ?>
		<tr>
			<td class="etiqueta">
				<?php if($this->data['PagoServicios']['tipoOperacion'] == 'sd'){?>
					Número de Línea 
				<?php }else{ ?>
	 		    	Número Servicio o Cuenta Contrato:
	        	<?php } ?> 
			</td>
			<td><?php echo $this->data['PagoServicios']['numeroContrato'] ?></td>
		</tr>
		<?php if($this->data['PagoServicios']['operacion'] != "MOVILNET"){ ?>
			<tr>
				<td class="etiqueta">Nombre Beneficiario:</td>
				<td><?php echo $this->data['PagoServicios']['nombreBeneficiario'] ?></td>
			</tr>
		<?php } ?>
		<tr>
			<td class="etiqueta">Monto Bs:</td>
			<td><?php echo $this->data['PagoServicios']['monto'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Fecha:</td>
			<td><?php echo date('d/m/Y') ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Referencia:</td>
			<td><?php echo $referencia ?></td>
		</tr>
                <?php if(!$pendiente){ ?>
                <tr>
			<td class="etiqueta">Número Confirmación Operadora:</td>
			<td><?php echo $confirmacion ?></td>
		</tr>
                <?php if($tipoOperacion=='sd'){ ?>
                <thead>
                    <tr>
                        <td colspan="2" class="resumen_paginacion">
                            <p class="exito">Para más información comuníquese al 121 desde su Digitel o al 04129121121</p>
                        </td>
                    </tr>
                </thead>
                <?php }
                
                } ?>
		<tr>
			<td colspan="2" class="botonera">

			<?php

			echo $html->link(
				'Imprimir',
				'javascript:print();void(0);',
				array(
					'class'=>'button'
				)
			);
                        
                        if(!$pendiente){
                            echo '&nbsp;&nbsp;';

                            echo $html->link(
                                    'Descargar PDF',
                                    '/pagos_servicios/descargar_pdf_sevicios',
                                                    array(
                                                            'class'=>'button'
                                                    )
                            );
                        }
			?>

			</td>
		
		</tr>

<?php 
}else if($error == 1616){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.sobregirada'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error == 9996){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('servicio.nocomunicacion'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error == 9920){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('monto.maximo.servicio'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error == 8){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('digitel.montoNovalido'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error == 5){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('digitel.bloqueo'); ?></div></td>
	</tr>
	</thead>
<?php 
}
else if($error == 9){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('digitel.lineanoPagable'); ?></div></td>
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
}if($error != 0){
?>
	<thead>
	<tr>
		<td class="botonera" style="padding-right: 50px;">
			
		<?php 

		echo '&nbsp;&nbsp;';

		echo $html->link(
			'Volver',
			'/pagos_servicios/pago_servicios',
			array(
				'class'=>'button'
			)
		);
	?>	
			
	</td>
	</tr>
	</thead>
<?php }?>
</table>
</div>


