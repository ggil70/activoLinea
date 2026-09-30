<?php
    /**
     * Vista para la confirmación  afiliaciones
     */

?>


<div>
<h2 class="user">Solicitud de Tarjeta Prepago CASH</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Seleccionar Tipo de Tarjeta</div>

<div class="paso_nro">2</div>
<div class="paso_desc">Validar Informaci&oacute;n Banco</div>

<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>

<div class="paso_nro_actual">4</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>



<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>

		<tr>
		<td colspan="2" class="resumen_paginacion"><p class="exito">
		Operación realizada satisfactoriamente</p>
		</td>
		</tr>



	</thead>

	<tr>
	<td colspan="2" class="resumen_paginacion"><h2 class="user">Información Cliente</h2></td>
	</tr>  

	<tr>
		<td class="etiqueta">Cédula:</td>
		<td><?php echo $v_cedula ?></td>
	</tr>


	<tr>
		<td class="etiqueta">Apellido(s) , Nombre(s):</td>
		<td><?php echo $v_nombre ?></td>
	</tr>

	<tr>
		<td class="etiqueta">Télefono Celular: </td>
		<td><?php echo $v_celular ?></td>
	</tr>

	<tr>
	<td colspan="2" class="resumen_paginacion"><h2 class="user">Información Banco</h2></td>
	</tr> 

	<tr>
		<td class="etiqueta">Cuenta CASH Para Debitar Comisión</td>
		<td><?php echo $v_cuentaBS ?></td>
	</tr>

	<tr>
		<td class="etiqueta">Agencia de Retiro de Tarjeta Prepago CASH</td>
		<td><?php echo $v_agencia ?></td>
	</tr>


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


        echo '&nbsp;&nbsp;';
        echo $html->link(
			'Descargar PDF',
			'/tarjeta_prepagadas/descargarTCP_pdf/F',
			array(
			'class'=>'button'
                        )
        );


		?>
		</td>
		</tr>

        <tr>
        	<td colspan="2" class="azul" style="border:0">
                 <span class="azul">Nota: Solicitud de Tarjeta Prepago CASH en proceso</span>
        	</td>	

        </tr>	


	</table>
</div>


