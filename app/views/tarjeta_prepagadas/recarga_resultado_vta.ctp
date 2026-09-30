<?php
/**
 * Resultado de mesa de cambio compra
**/



?>
<div>
<h2 class="user">Recarga Tarjeta Prepago CASH</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Seleccione Tarjeta Prepago CASH</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>

<br><br>


<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
<thead>
	

<?php 
	if($error == 0){
 ?>
		<tr>
		<td colspan="2" class="resumen_paginacion"><p class="exito">
		Operación realizada satisfactoriamente</p>
		</td>
		</tr>
		</thead>
		<tbody>



	    <tr>
	    <td class="etiqueta">Nro. Tarjeta Prepago CASH:</td>
	    <td><?php echo enmascara($tarjeta) ?></td>
	    </tr>

	    <!--<tr>
	    <td class="etiqueta">Cuenta Cash a Debitar:</td>
	    <td><?php //echo enmascara($debito) ?></td>
	    </tr>
	    -->


	    <tr>
	    <td class="etiqueta">Monto Recarga:</td>
	    <td><?php echo $montoRecarga ?></td>
	    </tr>

	    <tr>
	    <td class="etiqueta">Comisión:</td>
	    <td><?php echo $comision ?></td>
	    </tr>


	    <tr>
	    <td class="etiqueta">Total a Debitar:</td>
	    <td><?php echo $montoTotal ?></td>
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
	            'Nueva Recarga',
	            '/tarjeta_prepagadas/recarga/',
	            array(
	               'class'=>'button bloqueo_repetir'
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
	</tbody>
	</table>


<?php 
}
?>

</div>