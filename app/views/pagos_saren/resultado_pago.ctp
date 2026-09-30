<?php
/**
 * Resultado de transferencias a cuentas de terceros en otros bancos
**/
?>
<div>
<h2 class="user">Pagos Saren</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro_actual">4</div>
<div class="paso_desc_actual">Resultado de Operación</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<?php if(!$this->data['Error']){?>
	<thead>
		<tr>
		<td colspan="2" class="resumen_paginacion">
                    <p class="exito">Operación realizada satisfactoriamente</p>
                </td>
		</tr>
		<tr>
		        <td class="etiqueta">Cuenta a Débitar:</td>
		        <td><?php echo enmascara($datos['cuentaDebitar']); ?></td>
		    </tr>
		    <tr>
		        <td class="etiqueta">Número de Planilla:</td>
		        <td><?php echo $datos['nro_planilla']; ?></td>
		    </tr>
		    <tr>
		        <td class="etiqueta">Monto a Pagar:</td>
		        <td><?php echo $datos['monto']; ?></td>
		    </tr>
		    <tr>
		        <td class="etiqueta">Tipo de Trámite:</td>
		        <td><?php echo $datos['acto']; ?></td>
		    </tr>
		    <tr>
		        <td class="etiqueta">Oficina:</td>
		        <td><?php echo $datos['oficina']; ?></td>
		    </tr>


		    <tr>
		        <td class="etiqueta">Fecha de emisión: </td>
		        <td><?php echo $fechaFormateada;?></td>
		    </tr>
		    <tr>
		        <td class="etiqueta">Referencia:</td>
		        <td><?php echo $referencia; ?></td>
		    </tr>
		    <tr>
		        <td class="etiqueta">Fecha de pago: </td>
		        <td><?php echo $fechaPago;?></td>
		    </tr>


	<?php
	}
	else{
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo $descripcion; ?></div></td>
		</tr>
		</thead>
	<?php 
	}
	?>
		<tr>
		<td colspan="2" class="botonera">

		<?php 		
        echo $html->link(
            'Volver',
            '/pagos_saren/pago',
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