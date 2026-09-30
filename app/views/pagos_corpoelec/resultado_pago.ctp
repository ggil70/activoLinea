<?php
/**
 * Resultado de transferencias a cuentas de terceros en otros bancos
**/
?>
<div>
<h2 class="user">Pago Corpoelec</h2>
<div class="line_title_orange"></div>
</div>


<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Solicitud de Factura</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Código de Autorizaci&oacute;n</div>
<div class="paso_nro_actual">5</div>
<div class="paso_desc_actual">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">

	<thead>
		<tr>
		<td colspan="2" class="resumen_paginacion">
                    <p class="exito">Operación realizada satisfactoriamente</p>
                </td>
		</tr>
			<tr>
		        <td class="etiqueta">Cuenta a Debitar:</td>
		        <td><?php echo $cta_debito; ?></td>
		    </tr>
		    <tr>
		        <td class="etiqueta">Número de Contrato:</td>
		        <td><?php echo $nro_contrato; ?></td>
		    </tr>
		    <tr>
		        <td class="etiqueta">Monto a Pagar:</td>
		        <td><?php echo $monto; ?></td>
		    </tr>
		    <tr>
		        <td class="etiqueta">Fecha de Proceso:</td>
		        <td><?php echo $fecha_proceso; ?></td>
		    </tr>
		    <tr>
		        <td class="etiqueta">Concepto:</td>
		        <td><?php echo $concepto; ?></td>
		    </tr>
		    <?php if (isset($referencia)) {?>
		    	<tr>
			        <td class="etiqueta">Referencia:</td>
			        <td><?php echo $referencia; ?></td>
		    	</tr>
		    <?php } ?>
		    
		<tr>
		<td colspan="2" class="botonera">

		<?php 		
        echo $html->link(
            'Volver',
            '/pagos_corpoelec/datos',
            array(
                'class'=>'button',
                'id'=>'volver'
            )
        );

		echo "&nbsp;&nbsp;";		

		echo $html->link(
                    'Imprimir',
                    'javascript:print();void(0);',
                    array(
                        'class'=>'button'
                    )
		);

		?>
		</td>
	</tr>

	</thead>
</table>