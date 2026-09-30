<?php
    /**
     *  Vista para el resultado del Pago masivo de Nómina/Proveedores
     */

    $titulo = 'Pago Proveedores – Carga Masiva Express';
?>

<div>
<h2 class="user"><?php echo $titulo; ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Selecci&oacute;n Archivo</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->tdc_validada() && $usuario['rol_juridico_id']==2 ) { ?>
<div class="paso_nro">3</div>
<div class="paso_desc">Tarjeta de Coordenadas</div>
<div class="paso_nro_actual">4</div>
<?php } else { ?>
<div class="paso_nro_actual">3</div>
<?php } ?>
<div class="paso_desc_actual">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<?php if(!$exito){ ?>
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo $descripcion_error ?></div></td>
	</tr>
	</thead>
</table>
<?php } else { ?>
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">	
	
<thead>
	<tr>
		<td class="resumen_paginacion" colspan="2">
			<p class="exito">
				<?php echo ($usuario['rol_juridico_id']==2) ? 'Operación pendiente por procesar' : 'Operación pendiente por ejecutar'; ?>
			</p>
			 <p> Recuerde verificar el estatus final en la opción Seguimiento de Gestión.</p>
			<p>Puede descargar el comprobante de la operaci&oacute;n realizada presionando la opci&oacute;n Descargar PDF</p>
		</td>
	</tr>
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
	<td><?php echo formatea_monto_fromWs($estado['PagoCash']['mtoRegistroAprobado']) ?></td>
</tr>
<tr>
	<td class="etiqueta">Cantidad Registros:</td>
	<td><?php echo $estado['PagoCash']['nroRegistroAprobado'] ?></td>
</tr>

<?php
echo '<tr><td class="botonera" colspan="2">';

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
	'Imprimir',
	'javascript:print();void(0);',
	array(
		'class'=>'button'
	)
);

echo '&nbsp;&nbsp;';

echo $html->link(
	'Descargar PDF',
	'descarga_comprobante_express',
	array(
		'class'=>'button'
	)
);

echo '</td></tr>';
?>

</table>
<?php } ?>
	
</div>
