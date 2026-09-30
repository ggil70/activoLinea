<?php
    /**
     * Vista para mostrar el consolidado de productos
     */
?>




<div>
<h2 class="user">Tarjeta Prepago CASH / Movimientos</h2>
<div class="line_title_orange"></div>
</div>


<table class="tabla" cellpadding="0" cellspacing="0" align="center">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>

		<tr>
		<td colspan="2" class="resumen_paginacion"><p class="exito">
		Informaci&oacute;n de la Tarjeta Prepago CASH</p>
		</td>
		</tr>

	<tr>
		<td class="etiqueta">N&uacute;mero Tajeta:</td>
		<td><?php echo $nroCuenta ?></td>
	</tr>

	<tr>
		<td class="etiqueta">Estatus Tarjeta:</td>
		<td><?php echo $estatusCuenta ?></td>
	</tr>

</thead>
</table>

<br>

<table class="tabla" cellpadding="0" cellspacing="0" align="center">
	<thead>	
	<tr>
	<td colspan="2" class="resumen_paginacion"><p class="exito">
	Movimientos</p>
	</td>
	</tr>
</thead>
</table>


<table class="tabla" cellpadding="0" cellspacing="0" align="center">
<thead>
<tr>
<td>Fecha Transacci&oacute;n</td>
<td>Tipo</td>
<td>Origen</td>
<td>Referencia</td>
<td>Abono $.</td>
<td>Cargo $</td>
<td>Saldo $</td>
</tr>
</thead>
<tbody>

<?php


$swValidar = 0;
if(isset($arrayMovimientos['PrepagoMovimientosResponse'])){
	$arrayContenido = $arrayMovimientos['PrepagoMovimientosResponse'];
    $swValidar=1;
}



if(isset($arrayContenido[0]) && $swValidar==1){
	foreach ($arrayMovimientos as $arreglo) {
		foreach($arreglo as $key){
            $fecha =  $key['fechaTransaccion'];
            $hora = substr($fecha,8,9); 
            $fecha = substr($fecha,0,8); 
            $fecha = substr($fecha,6,2) . "/". substr($fecha,3,2) . "/20" . substr($fecha,0,2) . $hora;

         
	?>
	<tr>
		<td class="texto_centro"><?php echo $fecha; ?></td>
		<td class="texto_centro"><?php echo $key['descMovimiento']; ?></td>
		<td class="texto_centro"><?php echo $key['origen']; ?></td>

		<td class="texto_centro"><?php echo $key['referencia']; ?></td>
		<?php
		if($key['signo']=='+'){
			?>
	   	    <td class="monto"><?php echo formatea_monto_fromWs($key['monto']); ?></td>
	   	<?php }else{
	   		echo '<td class="monto"></td>';
	    }		

		if($key['signo']=='-'){
			?>
	   	    <td class="monto"><?php echo formatea_monto_fromWs($key['monto']); ?></td>
	   	<?php }else{
	   		echo '<td class="monto"></td>';
	    }		
		?> 
		<td class="texto_centro"><?php echo $key['saldoReal']; ?></td>
	<?php
	}
	}
	echo "</tr>";
	echo "<tr>";
    ?>
		<td colspan="6" class="botonera" style="align:right">

		<?php 
		echo $html->link(
	            'Volver',
	            '/tarjeta_prepagadas/movimientos/',
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
}else{
?>	
   <tr><td colspan="6">No hay movimiento para el periodo de fecha seleccionado</td></tr> 
   </tbody>
	</table>
<?php
}
?>

