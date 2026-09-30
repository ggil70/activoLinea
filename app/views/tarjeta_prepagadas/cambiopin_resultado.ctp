<?php
/**
 * Resultado de Cambio PIN TPC
**/
?>
<div>
<h2 class="user">Generaci&oacute;n de Clave / Tarjeta Prepago CASH</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Cambiar Clave Tarjeta</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">


<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
<thead>
	
<?php 


    //
    $error = 0;
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
    <td class="etiqueta">Tarjeta Prepago CASH:</td>
    <td><?php echo enmascara($tarjeta) ?></td>
    </tr>

 


    <tr>
    <td class="etiqueta">Fecha Vencimiento:</td>
    <td><?php echo $fecVencimiento ?></td>
    </tr>

    <tr>
    <td class="etiqueta">C&oacute;digo de Seguridad:</td>
    <td><?php echo $codSeguridad ?></td>
    </tr>

		<tr>
		<td class="etiqueta">Estatus:</td>
		<td>Gestión Exitosa</td>
		</tr>


		<tr>
		<td class="etiqueta">Fecha Cambio pin:</td>
		<td><?php echo date('d/m/Y') ?></td>
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
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjeta_prepagadas/cambiopin_confirmacion') ?>">
	<input type="submit" class="button" value="Volver" />
</form>
</td>
</tr>
<?php }?>
</table>
</div>