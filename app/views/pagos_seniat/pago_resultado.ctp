<?php
/**
 * Resultado de pago de tarjetas propias en banco activo
**/
?>
<div>
<h2 class="user">Pagos de Impuestos Nacionales/Aduanales <?php echo $descripcion; ?></h2>
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
if($error=='0'){
?>
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion"><p class="exito">
        <?php if(isset($pendiente) && $pendiente){ ?>
        Operación pendiente por ejecutar.</p>
        <?php } else { ?>
        Operación realizada satisfactoriamente</p>Puede descargar los comprobantes de las operaci&oacute;n realizadas presionando la opci&oacute;n Descargar PDF
        <?php } ?>
	</td>
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
	<td class="etiqueta">Fecha:</td>
	<td><?php echo date('d/m/Y') ?></td>
	</tr>
	<?php if(!empty($this->data['PagosSeniat']['referencia'])){ ?>
            <tr>
                <td class="etiqueta">Referencia:</td>
                <td><?php echo $this->data['PagosSeniat']['referencia'] ?></td>
            </tr>
	<?php } ?>
	<?php if(!empty($this->data['PagosSeniat']['autorizacion'])){ ?>
            <tr>
                <td class="etiqueta">N° Autorización:</td>
                <td><?php echo $this->data['PagosSeniat']['autorizacion'] ?></td>
            </tr>
	<?php } ?>
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
                '/pagos_seniat/descargar_pdf',
                array(
                        'class'=>'button'
                )
            );
		
	}
	?>

	</td>
	</tr>
<?php 
}else if($error == '0182' || $error == '0223'){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('producto.invalido', 'el número cuenta a debitar'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error=='2003' || $error=='8028' || $error=='0608'){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.no.beneficiario', ''); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error=='1616'){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('cuenta.sobregirada', 'el número cuenta a debitar'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error=='A3'  || $error=='A4' || $error=='K3' || $error=='G4' || $error=='77' || $error=='91' || $error=='93' || $error=='98' || $error=='01'){//SIN COMUNICACIÓN
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('sin.comunicacion.seniat') . ' ' . $codigoError; ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error=='0001' || $error=='2000'){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('monto.limite.usuario','del compromiso de pago'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error=='J4'){//RIF PAGADOR VENCIDO
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('rif.vencido'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error=='I8'){//RIF PAGADOR NO CORRESPONDE
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('rif.no.autorizado'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error=='ST1'){//Socket AS400 Time Out
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('transaccion.timeout'); ?></div></td>
	</tr>
	</thead>
<?php 
}else if($error=='M9' || $error=='95' || $error=='94' || $error=='I7' || $error=='I3'){//COMPROMISO YA PAGADO 
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('ya.pagado.seniat'); ?></div></td>
	</tr>
	</thead>
<?php 
}else{
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('pago.fallido') . ' ' . $codigoError; ?></div></td>
	</tr>
	</thead>
<?php 
}
if($error != ''){
?>
<tr>
	<td colspan="2" class="botonera">
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/pagos_seniat/compromisos/'.$this->Session->read('tipo')); ?>">
</form>
</td>
</tr>
<?php 
}
?>
</table>
</div>
