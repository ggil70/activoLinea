<?php
/**
 * Consulta de detalle de la tarjeta de crédito corporativa o empresarial
**/
?>
<div>
<h2 class="user">Consulta Tarjeta de Crédito</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_encabezado">
<?php

echo $form->create(
		'DetalleProducto',
		array(
			'url'=> array(
				'controller'=>'tarjetas',
				'action'=>'detalle_Emp_Corp'
			),
			'inputDefaults'=>array(
				'div'=>false,
				'label'=>'etiqueta',
				'before'=>'<tr><td class="etiqueta">',
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
			'border'=>'0'
		)
	);
		
	if($flag_buscar){
		echo '<td class="etiqueta">N&uacute;mero Tarjeta:</td><td>'.enmascara($this->data['DetalleProducto']['nro_tarjeta']).'</td>';
	} else {
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
	}
	
	echo '</table>';

	echo $form->end();

?>

<table class="tabla" cellpadding="0" cellspacing="0" width="100%">
<thead>
<tr>
<td>Tipo de Producto</td>
<td>Alias Tarjeta</td>
<td>Cr&eacute;dito Disponible Bs.</td>
<td>Saldo Actual Bs.</td>
<td>Fecha Próximo Pago</td>
</tr>
</thead>
<tbody>
<tr>
<td class="texto_centro">Tarjeta de Crédito <?php echo $this->Activo->getTipoTarjeta($this->data['DetalleProducto']['nro_tarjeta']);  ?></td>
<td class="texto_centro"><?php echo $detalle_tarjeta['ALIAS']?></td>
<td class="monto"><?php echo formatea_monto_fromWs($detalle_tarjeta['SUMAVB']); ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($detalle_tarjeta['SUMBAL']); ?></td>
<td class="monto"><?php echo formatea_fecha($detalle_tarjeta['dia_pago'],$detalle_tarjeta['mes_pago'],$detalle_tarjeta['anio_pago'])?></td>
</tr>
</tbody>
</table>
</div>

<div class="espacio_vertical_10"></div>
<div class="espacio_vertical_10"></div>
<table class="tabla" cellpadding="0" cellspacing="0" style="margin:0 auto; width:800px;">
<thead>
	<tr>
		<td colspan="6" class="resumen_paginacion">
			<div style="text-align:left; width:48%; float:left; font-weight:bold;">
				Tarjetas Asociadas
			</div>
			<div style="text-align:right; width:48%; float:right;">
				<?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?>
			</div>
		</td>
	</tr>
</thead>
<thead>
<tr>
<td>N&uacute;mero</td>
<td>Nombre</td>
<td>Saldo Actual Bs.</td>
<td>Cr&eacute;dito Disponible Bs.</td>
<td>Pago M&iacute;nimo Bs.</td>
<td>Fecha L&iacute;mite Pago</td>
</tr>
</thead>
<?php if(!empty($arrData)) { ?>
<tbody>
<?php foreach($arrData as $t){?>
<tr>
<td class="texto_centro">
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjetas/detalle') ?>">
<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo trim($t['CARNUM']) ?>" />
<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $tipo ?>" />
<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo $tarjeta ?>" />
<input type="submit" class="boton_enlace" value="<?php echo enmascara($t['CARNUM']) ?>" />
</form>
</td>
<td class="texto_centro"><?php echo trim($t['NOMBRE'])?></td>
<td class="monto"><?php echo formatea_monto_fromWs($t['CARBAL'])?></td>
<td class="monto"><?php echo formatea_monto_fromWs($t['CARAVC']) ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($t['CARPMA']) ?></td>
<td class="texto_centro"><?php echo formatea_fecha($t['CARED1'],$t['CARED2'],$t['CARED3'])?></td>
</tr>
<?php } ?>
</tbody>
<?php } ?>
<tfoot>
<tr>
<td colspan="6" class="paginador">
<?php
if($flag_buscar){
	$modelo = 'BusquedaCuenta';
} else {
	$modelo = 'DetalleProducto';	
}

echo $form->create(
	$modelo,
	array(
		'url'=> array(
			'controller'=>'tarjetas',
			'action'=>$this->action
		),
	)
);

if(!empty($this->data[$modelo])){
	foreach($this->data[$modelo] as $k => $v) {
		if(is_array($v)){
			foreach ($v AS $sk => $sv){
				echo $form->hidden($modelo . '.' . $k . '.' .$sk) ;
			}
		}
		else{
			echo $form->hidden($k);
		}
	}
}

echo $this->element('paginador',array('total'=>$total_registros));

echo $form->end();
?>
</td>
</tr>
</tfoot>
</table>

<div class="espacio_vertical_10"></div>

<div class="botonera" style="width:800px;margin:0 auto">

<form class="formulario_enlace" method="post" action="<?php echo Router::url($ruta_regreso) ?>">
<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['DetalleProducto']['nro_tarjeta']?>" />
<input type="submit" class="button" value="Volver" />
</form>
</div>