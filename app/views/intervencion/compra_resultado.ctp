<?php
/**
 * Resultado de intervencion compra
**/
?>
<div>
<h2 class="user">Compra de Divisas en Intervención Cambiaria</h2>
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
<thead>
<?php 
	if($error == 0){
 ?>
		<tr>
		<td colspan="2" class="resumen_paginacion"><p class="exito">
		<?php if($pendiente){ ?>
		Operación pendiente por ejecutar.</p>
		<?php } else { ?>
		Operación realizada satisfactoriamente</p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF
		<?php } ?>
		</td>
		</tr>
		</thead>

		<tbody>
		<tr>
                <td class="etiqueta">Cuenta Origen:</td>
                <td><?php echo enmascara($this->data['Intervencion']['cuentaDebitar']) ?></td>
                </tr>
                <tr>
                <td class="etiqueta">Cuenta Destino:</td>
                <td><?php echo enmascara($this->data['Intervencion']['cuentaDestino']) ?></td>
                </tr>
                <tr>
                <td class="etiqueta">Monto de Orden:</td>
                <td><?php echo $this->data['Intervencion']['monto_divisa'] ?></td>
                </tr>
                <tr>
                <td class="etiqueta">Tasa de Compra:</td>
                <td><?php echo $this->data['Intervencion']['tasaCompra'] ?></td>
                </tr>
                <tr>
                <td class="etiqueta">SubTotal Bs.:</td>
                <td><?php echo $this->data['Intervencion']['monto'] ?></td>
                </tr>
                <tr>
                <td class="etiqueta">Comisión <?php echo  number_format($this->data['Intervencion']['comisionPorc'] * 100, 2, ",", ".") ?>%:</td>
                <td><?php echo $this->data['Intervencion']['comision'] ?></td>
                </tr>                
                <tr>
                <td class="etiqueta">Total Bs.:</td>
                <td><?php echo $this->data['Intervencion']['total'] ?></td>
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
			'/consultas/descargar_pdf',
			array(
			'class'=>'button'
                        )
                    );
		}
		?>
		</td>
		</tr>

	</tbody>
	</table>
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
<?php 
} else {
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo $descripcion; ?></div></td>
	</tr>
	</thead>
<?php 
}
if($error != 0){
?>
<tr>
	<td colspan="2" class="botonera">
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/intervencion/compra') ?>">
	<input type="submit" class="button" value="Volver" />
</form>
</td>
</tr>
<?php }?>
</table>
</div>