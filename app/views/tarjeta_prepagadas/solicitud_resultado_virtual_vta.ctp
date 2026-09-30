<?php
    /**
     * Vista para la confirmación  afiliaciones
     */
?>

<div>
<h2 class="user">Solicitud de Tarjeta Prepago CASH Virtual  </h2>
<div class="line_title_orange"></div>
</div>


<div class="paso_nro">1</div>
<div class="paso_desc">Seleccionar Tipo de Tarjeta</div>


<div class="paso_nro">2</div>


<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
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
		
		Operación realizada satisfactoriamente
		
		</td>
	</tr>
	</thead>
	<tr>
		<td class="etiqueta">Nombre(s) Apellido(s):</td>
		<td><?php echo $v_nombre; ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Cédula:</td>
		<td><?php echo $v_cedula; ?></td>
	</tr>
 
	<tr>


		<td class="etiqueta">Télefono: </td>
		<td><?php echo $v_celular; ?></td>
	</tr>
	
        
	<tr>
		<td class="etiqueta">Email</td>
		<td><?php echo $v_email; ?></td>
	</tr>
	
 <tr>
		<td class="etiqueta">Dirección:</td>
		<td><?php echo $v_direccion;?> </td>
	</tr>

	
	<tr>
		<td class="etiqueta">Tipo Tarjeta:</td>
		<td>Virtual</td>
		
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
			'/tarjeta_prepagadas/descargarTCP_pdf/V',
			array(
			'class'=>'button'
                        )
        );
        


		?>
		</td>
		</tr>

        <tr>
        	<td colspan="2" class="azul" style="border:0">
                 <span class="azul">Nota: <?php echo _m('tcp.tarjeta.solicitud.virtual', true) ?></span>
        	</td>	

        </tr>	


	</table>
</div>



