<?php
/**
 * Resultado de Recraga de tercero
**/
?>
<div>
<h2 class="user">Tarjeta Prepago Cash / Recarga Tercero en Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitar Informaci&oacute;n Beneficiario</div>

<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>


<div class="paso_nro">3</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>

<div class="paso_nro_actual">4</div>
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
		Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF</p>
		</td>
		</tr>
		</thead>
		<tbody>

	    <tr>
	    <td class="etiqueta">Cuenta de D&eacute;bito:</td>
	    <td><?php echo enmascara($debito) ?></td>
	    </tr>

	    <tr>
	    <td class="etiqueta">Tarjeta del Beneficiario:</td>
	    <td><?php 
               $tarjeta = substr($tarjeta,0,4) . "****". substr($tarjeta,12,4); 


	    echo enmascara($tarjeta) ?></td>
	    </tr>    


	    <tr>
	    <td class="etiqueta">Nombre del Beneficiario:</td>
	    <td><?php echo strtoupper($nombreB) ?></td>
	    </tr>


	    <tr>
	    <td class="etiqueta">C&eacute;dula del Beneficiario:</td>
	    <td><?php echo $cedulaClienteBNac ?></td>
	    </tr>

	    <tr>
	    <td class="etiqueta">Email del Beneficiario:</td>
	    <td><?php echo $email ?></td>
	    </tr>

	    <?php
	    if(!empty($afiliar)){
	    ?>    
	    <tr>
	    <td class="etiqueta">Alias:</td>
	    <td><?php echo $afiliar?></td>
	    </tr>



	    <?php    
	    }
	    ?>


	    <tr>
	    <td class="etiqueta">Monto:</td>
	    <td><?php echo $montoRecarga ?></td>
	    </tr>

	    <tr>
	    <td class="etiqueta">Monto Comisi&oacute;n:</td>
	    <td><?php echo $comision ?></td>
	    </tr>

	    <tr>
	    <td class="etiqueta">Total a Debitar:</td>
	    <td><?php echo $totalRecarga ?></td>
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
	            '/tarjeta_prepagadas/recarga_tercero/',
	            array(
	               'class'=>'button bloqueo_repetir'
	            )
		);

		echo "&nbsp;&nbsp;";		

		echo $html->link(
			'Descargar PDF',
			'/tarjeta_prepagadas/descargar_tercero_pdf',
			array(
				'class'=>'button'
			)
		);

/*		echo $html->link(
                    'Imprimir',
                    'javascript:print();void(0);',
                    array(
                        'class'=>'button'
                    )
		);
*/
		?>
		</td>
		</tr>

<?php
}
?>


</table>	


</div>