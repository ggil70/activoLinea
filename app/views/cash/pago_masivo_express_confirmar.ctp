<?php
    /**
     *  Vista para la confirmacin del Pago masivo de Nómina/Proveedores
     */

    $titulo = 'Pago Proveedores – Carga Masiva Express';
    $mtoTotal = $estado['PagoCash']['monto_total'];


/*    $mtoTotal = str_replace('.','', $mtoTotal);
    $mtoTotal = str_replace(',','.',$mtoTotal);
*/    

    $mtoAprobado = $estado['PagoCash']['mtoRegistroAprobado'];
    $mtoAprobado = str_replace(',','.',$mtoAprobado);

    $mtoError = $estado['PagoCash']['mtoRegistroError'];
    $mtoError = str_replace(',','.',$mtoError);


?>


<div>
<h2 class="user"><?php echo $titulo; ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Selecci&oacute;n Archivo</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->tdc_validada() && $usuario['rol_juridico_id']==2 ) { ?>
<div class="paso_desc">Tarjeta de Coordenadas</div>
<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">	
	
<thead>
	<tr><td class="resumen_paginacion" colspan="2">A continuaci&oacute;n proceda a confirmar los datos del pago masivo que desea procesar.</td></tr>
</thead>

<tr>
	<td class="etiqueta">Archivo:</td>
	<td><?php echo $estado['PagoCash']['archivo']['name'] ?></td>
</tr>
<tr>
	<td class="etiqueta">Usuario Registro:</td>
	<td><?php echo desencriptar($usuario['nombre']) ?></td>
</tr>
<tr>
	<td class="etiqueta">Cuenta D&eacute;bito:</td>
	<td><?php echo $activo->enmascara($estado['PagoCash']['cuentaDebitar']) ?></td>
</tr>
<tr>
	<td class="etiqueta">Fecha Valor:</td>
	<td><?php echo $estado['PagoCash']['fecha_valor']['day'].'/'.$estado['PagoCash']['fecha_valor']['month'].'/'.$estado['PagoCash']['fecha_valor']['year'] ?></td>
</tr>
<tr>
	<td class="etiqueta">Descripci&oacute;n:</td>
	<td><?php echo$estado['PagoCash']['descripcion'] ?></td>
</tr>
<tr>
	<td class="etiqueta">N&uacute;mero Lote:</td>
	<td><?php echo$estado['PagoCash']['lote'] ?></td>
</tr>
<tr>
	<td class="etiqueta">Monto Bs.:</td>
	<td><?php echo $mtoTotal ?></td>
</tr>
<tr>
	<td class="etiqueta">Cantidad Registros:</td>
	<td><?php echo $estado['PagoCash']['cantidad_registros'] ?></td>
</tr>


<tr>
	<td class="etiqueta">Cantidad Registros a Procesar:</td>
	<td><?php echo $estado['PagoCash']['nroRegistroAprobado'] ?></td>
</tr>

<tr>
	<td class="etiqueta">Total Monto a Procesar:</td>
	<td><?php echo formatea_monto_fromWs($mtoAprobado);	?></td>
</tr>

<tr>
	<td class="etiqueta">Cantidad Registros Rechazados:</td>
	<td><?php echo $estado['PagoCash']['nroRegistroError'] ?></td>
</tr>


<tr>
	<td class="etiqueta">Total Monto Rechazado:</td>
	<td><?php echo formatea_monto_fromWs($mtoError);
	?></td>
</tr>



<?php

/*echo '<tr><td colspan="2" class="azul"><strong>Nota: Estimado cliente, las transacciones con un monto mayor o igual a Bs. '. formatea_monto_fromWs(formatea_monto_toWs($parametro_altoValor['MONTO'])) .' serán aplicadas el siguiente día hábil.</strong></td></tr>';
*/

echo '<tr><td class="botonera" colspan="2">';

echo $html->link(
	'Volver',
	'pago_masivo_express/p',
	array(
		'class'=>'button'
	)
);

echo '&nbsp;&nbsp;';
if($estado['PagoCash']['nroRegistroError']==1){
	echo $html->link(
		'Descargar Pagos Rechazados PDF',
		'descarga_error_masivo_express',
		array(
			'class'=>'button'
		)
	);
	echo '&nbsp;&nbsp;';
}

echo $html->link(
	'Procesar Archivo',
	'pago_masivo_express_resultado',
	array(
		'class'=>'button'
	)
);

echo '</td></tr>';
?>

</table>

</div>
