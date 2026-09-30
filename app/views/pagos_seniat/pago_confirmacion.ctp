<?php
/**
 * Confirmación de pago de tarjetas propias en banco activo
**/
?>
<div>
<h2 class="user">Pagos de Impuestos Nacionales/Aduanales <?php echo $descripcion; ?></h2>
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
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
	<td class="etiqueta">Cuenta de D&eacute;bito:</td>
	<td><?php echo enmascara($this->data['PagosSeniat']['cuentaDebitar']) ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Nombre Beneficiario:</td>
	<td><?php echo $this->data['PagosSeniat']['beneficiario'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">RIF del Beneficiario:</td>
	<td><?php echo $this->data['PagosSeniat']['rif'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Forma:</td>
	<td><?php echo $this->data['PagosSeniat']['forma'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Nombre Impuesto:</td>
	<td><?php echo $this->data['PagosSeniat']['impuesto'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">N° Planilla:</td>
	<td><?php echo $this->data['PagosSeniat']['planilla'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">N° Porci&oacute;n:</td>
	<td><?php echo $this->data['PagosSeniat']['porcion'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Per&iacute;odo:</td>
	<td><?php echo $this->data['PagosSeniat']['periodo'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Vencimiento:</td>
	<td><?php echo $this->data['PagosSeniat']['vencimiento'] ?></td>
	</tr>
	<?php
        if ($this->data['PagosSeniat']['aduana'] != "" && $this->data['PagosSeniat']['expediente'] != ""){
        ?>
	<tr>
	<td class="etiqueta">Aduana:</td>
	<td><?php echo $this->data['PagosSeniat']['aduana'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Expediente:</td>
	<td><?php echo $this->data['PagosSeniat']['expediente'] ?></td>
	</tr>
        <?php
        }
        ?>
        <tr>
	<td class="etiqueta">Monto:</td>
	<td><?php echo $this->data['PagosSeniat']['monto'] ?></td>
	</tr>
	<tr>
	<td colspan="2" class="botonera">

	<?php 
	
	echo $html->link(
		'Volver',
		'/pagos_seniat/compromisos/'. $this->data['PagosSeniat']['tipo'] .'/v',
		array(
			'class'=>'button bloqueo_repetir',
                        'id'=>'volver'
		)
	);
        
	echo '&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'/pagos_seniat/pago_resultado',
		array(
			'class'=>'button bloqueo_repetir',
                        'id'=>'aceptar'
		)
	);
        
 	?>

	</td>
        </tr>
	</table>
</div>

<div class="basic-modal-content" id="procesandoP">
	<h3>Procesando...</h3>
	<p><?php echo(_m('procesando.transaccion','el pago solicitado')); ?></p>
</div>
<div class="basic-modal-content" id="procesandoC">
	<h3>Procesando...</h3>
	<p><?php echo(_m('procesando.transaccion','la consulta solicitada')); ?></p>
</div>


<script type="text/javascript">

$(function(){

	$('#aceptar').click(function(){
            $('#procesandoP').modal({minHeight:120,minWidth:400,close:false});
	});
	$('#volver').click(function(){
            $('#procesandoC').modal({minHeight:120,minWidth:400,close:false});
	});
	
});
</script>
