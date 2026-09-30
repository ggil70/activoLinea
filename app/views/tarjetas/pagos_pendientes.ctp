<?php
/**
 * Lista de pagos pendientes por ejecutarse en la tarjeta
**/
?>
<div>
<h2 class="user">Pagos Pendientes por Aplicar</h2>
<div class="line_title_orange"></div>
</div>
<div class="contenedor_encabezado">
	<?php

echo $form->create(
	'DetalleProducto',
	array(
		'url'=> array(
			'controller'=>'tarjetas',
			'action'=>'pagos_pendientes'
		),
		'inputDefaults'=>array(
			'div'=>false,
			'label'=>'etiqueta',
			'before'=>'<tr><td class="etiqueta" style="width:20%">',
			'between'=>'</td><td class="campo">',
			'after'=>'</td></tr>',
			'error'=>false
		)
	)
);


echo $html->tag(
	'table',
	null,
	array(
		'class'=>'formulario',
		'cellpadding'=>'0',
		'cellspacing'=>'0',
		'style'=>'width:450px; margin: 0px auto;',		
		'border'=>'0'
	)
);

echo $form->input(
	'nro_tarjeta',
	array(
		'label'=>'Número Tarjeta:',
		'type'=>'select',
		'options'=>$tarjetas,
		'empty'=>false,
		'class'=>'cambio_producto'
	)
);

echo '<input type="hidden" name="data[DetalleProducto][tipo]" value="'.$this->data['DetalleProducto']['tipo'].'">';
echo '<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="'.$this->data['DetalleProducto']['nro_tarjeta_padre'].'">';
echo $form->end();

?>

	<table class="tabla" cellpadding="0" cellspacing="0" style="width:450px;">
	<thead>
	<tr>
		<td>Fecha</td>
		<td>Descripci&oacute;n</td>
		<td>Abono Bs.</td>
	</tr>
	</thead>
	<tbody>
<?php
if(isset($pagos_pendientes) && count($pagos_pendientes)>0){
foreach($pagos_pendientes as $i){
	$fecha = $i['CCATRDAT'];
	$anio = substr($fecha,0,4);
	$mes = substr($fecha,4,2);
	$dia = substr($fecha,6,2);

?>
<tr>
<td class="texto_centro"><?php echo $dia.'/'.$mes.'/'.$anio?></td>
<td class="descripcion"><?php echo $i['CCATRDSC'] ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($i['CCATRAMCR']) ?></td>
</tr>

<?php
	}

}else {
	?>
		<tr>
			<td colspan="3" class="texto_centro" style="width:650px">No se encontraron resultados</td>
		</tr>
<?php
}
?>
	</body>
	<tfoot>
	<tr>
	<td colspan="2" class="total">Total Bs.:</td>
	<td class="monto_total"><?php echo formatea_monto_fromWs($totalesAbono['ABONOSTDC'])?> </td>
	</tr>
<tr>
	<td colspan="3" class="botonera2"><div class="botonera" >
			<form class="formulario_enlace" method="post" action="<?php echo Router::url($ruta_regreso) ?>">
			<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta']; ?>" />
                        <input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta_padre']?>" />
                        <input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $this->data['DetalleProducto']['tipo']?>" />
			<input type="submit" class="button" value="Volver" />
			</form>
			<form class="formulario_enlace" method="post" onsubmit="print();return false;">
			<input type="submit" class="button" value="Imprimir" />
			</form>
			</div></td>
</tr>
	</tfoot>
	</table>
</div>