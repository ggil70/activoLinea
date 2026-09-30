<?php
    /**
     * Vista utilizada para mostrar los movimientos que tiene un crédito
     */
?>

<div>
<h2 class="user">Consulta Movimientos del Cr&eacute;dito</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_encabezado" style="margin: 0px auto; width:700px;">
<?php

echo $form->create(
	'DetalleProducto',
	array(
		'url'=> array(
			'controller'=>'creditos',
			'action'=>'movimientos'
		),
		'inputDefaults'=>array(
			'div'=>false,
			'label'=>'etiqueta',
			'before'=>'<tr><td class="etiqueta">',
			'between'=>'</td><td class="campo">',
			'after'=>'</td></tr>',
			'error'=>false
		),
		'id'=>'form_change'
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

if(!isset($flag_busqueda)){
	echo $form->input(
		'nro_credito',
		array(
			'label'=>'N&uacute;mero Cr&eacute;dito:',
			'type'=>'select',
			'options'=>$creditos,
			'empty'=>false,
			'class'=>'cambio_producto'
		)
	);
}
else{
	echo '<tr>
		<td class="etiqueta">N&uacute;mero Cr&eacute;dito:</td>
		<td class="campo">'. $this->data['DetalleProducto']['nro_credito'] .'</td>
	</tr>';
}
echo '</table>';
echo $form->end();

?>
<?php if (!empty($det_uvcc['SUMACCS4'])){ ?>
<div class="espacio_vertical_20"></div>
<table class="tabla" cellpadding="0" cellspacing="0">
<thead>
<tr>
	<td class="ancholibre">Saldo Deudor <br/>(UVCC / UVCP)</td>
	<td class="ancholibre">Saldo Deudor <br/>(BS.)</td>
	
</tr>
</thead>
<tbody>
<tr>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($cab_uvcc['SUMSDUVCC3']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($cab_uvcc['SUMSDBBSR3']) ?></td>
</tr>
</tbody>
</table>
<div class="espacio_vertical_20"></div>
<table class="tabla" cellpadding="0" cellspacing="0" border="0">
<thead> 
<tr>
	<td class="ancholibre">Fecha </br>Proximo Pago</td>
	<td class="ancholibre">Proximo Pago </br>(UVCC /UVCP)</td>
	<td class="ancholibre">Proximo Pago </br>(BS.)</td>
	
</tr>
</thead>
<tbody>
<tr>	
	<td class="monto ancholibre"><?php echo $det_uvcc['SUMSDD4'].'/'.$det_uvcc['SUMSMM4'].'/'.date('Y'); ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($det_uvcc['SUMMTOCUVC']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($det_uvcc['SUMMTOCBS']) ?></td>
	
</tr>
</tbody>
</table>

<?php }else{?>

<table class="tabla" cellpadding="0" cellspacing="0">
<thead>
<tr>
	<td class="ancholibre">Tipo Cr&eacute;dito</td>
	<td class="ancholibre">Estado</td>
	<td class="ancholibre">Fecha Otorgamiento</td>
	<td class="ancholibre">Monto Otorgado</td>
	<td class="ancholibre">Fecha Vencimiento</td>
	<td class="ancholibre">Plazo</td>
</tr>
</thead>
<tbody>
<tr>
	<td class="texto_centro ancholibre"><?php echo trim($detalle['e01APCDSC']) ?></td>
	<td class="texto_centro ancholibre"><?php echo $estado; ?></td>
	<td class="texto_centro ancholibre"><?php echo formatea_fecha($detalle['e01OPEND2'],$detalle['e01OPEND1'],$detalle['e01OPEND3']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($detalle['e01DEAOAM']) ?></td>
	<td class="texto_centro ancholibre"><?php echo formatea_fecha($detalle['e01MATUR2'],$detalle['e01MATUR1'],$detalle['e01MATUR3']) ?></td>
	<td class="texto_centro ancholibre"><?php 
		echo $detalle['e01DEATRM'] . ' ';
		switch(trim($detalle['e01DEATRC'])){
			case 'Y':
				echo 'A&ntilde;os';
				break;
			case 'M':
				echo 'Meses';
				break;
			case 'D':
				echo 'D&iacute;as';
				break;
		}
	?></td>
</tr>
</tbody>
</table>
<div class="espacio_vertical_20"></div>

<?php if(!isset($flag_busqueda)){ ?>
<table class="tabla" cellpadding="0" cellspacing="0" border="0">
<thead> 
<tr>
	<td class="ancholibre">Cuotas<br/>Pagadas</td>
	<td class="ancholibre">Saldo<br/>Deudor</td>
	<td class="ancholibre">Saldo<br/>Capital</td>
	<td class="ancholibre">Cuotas<br/>Vencidas</td>
	<td class="ancholibre">Monto<br/>Vencido</td>
	<td class="ancholibre">Monto<br/>Pr&oacute;xima<br/>Cuota</td>
	<td class="ancholibre">Total<br/>Pr&oacute;ximo<br/>Pago</td>
	<td class="ancholibre">Fecha<br/>Pr&oacute;ximo<br/>Pago</td>
	<td class="ancholibre">Inter&eacute;s<br/>Pendiente</td>
	<td class="ancholibre">Mora</td>
</tr>
</thead>
<tbody>
<tr>	
	<td class="ancholibre texto_centro">
		<?php if($detalle['e01DEATCP']>0){?>
		<form class="formulario_enlace" method="post" action="<?php echo Router::url('/creditos/cuotas_pagadas') ?>">
			<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo $this->data['DetalleProducto']['nro_credito'] ?>" />
			<input type="submit" class="boton_enlace" value="<?php echo $detalle['e01DEATCP'] ?>" />
		</form>
		<?php } else { echo $detalle['e01DEATCP']; }?>
	</td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($detalle['e01DEATOT']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($detalle['e01DEAMEP']) ?></td>
	<td class="ancholibre texto_centro">
		<?php if($detalle['e01DEATCV']>0){?>
		<form class="formulario_enlace" method="post" action="<?php echo Router::url('/creditos/cuotas_vencidas') ?>">
			<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo $this->data['DetalleProducto']['nro_credito'] ?>" />
			<input type="submit" class="boton_enlace" value="<?php echo $detalle['e01DEATCV'] ?>" />
		</form>
		<?php } else { echo $detalle['e01DEATCV']; }?>
	</td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($detalle['e01DEATDU']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($detalle['e01DEAMPX']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($detalle['e01DEAMPP']) ?></td>
	<td align="center" class="texto_centro ancholibre"><?php echo formatea_fecha($detalle['e01DEAMPP2'],$detalle['e01DEAMPP1'],$detalle['e01DEAMPP3']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($detalle['e01DEAMEI']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($detalle['e01DEAMEM']) ?></td>
</tr>
</tbody>
</table>

<div class="espacio_vertical_10"></div>
	<table class="tabla" cellpadding="0" cellspacing="0">
	<tbody>
	<tr>
	<td align="center" width="50%">
	<form class="formulario_enlace" method="post" action="<?php echo Router::url('/creditos/exportar_estado') ?>">
	<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo $this->data['DetalleProducto']['nro_credito'] ?>" />
	<input type="submit" class="boton_enlace" value="Estado de Cuenta" />
	</form>
	</td>	
	<td align="center" width="50%">
	<form class="formulario_enlace" method="post" action="<?php echo Router::url('/creditos/historial') ?>">
	<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo $this->data['DetalleProducto']['nro_credito'] ?>" />
	<input type="submit" class="boton_enlace" value="Histórico" />
	</form>
	</td>
	</tr>
	</tbody>
	</table>
<div class="espacio_vertical_10"></div>

<?php } ?>

</div>
<table class="tabla" cellpadding="0" cellspacing="0"  style="margin: 0px auto; width:800px;" border="0">
<thead>
	<tr>
		<td colspan="13" class="resumen_paginacion">
			<div style="text-align:left; width:48%; float:left; font-weight:bold;">
				Detalle Pr&oacute;ximo Pago
			</div>
			<div style="text-align:right; width:48%; float:right;">
				<?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?>
			</div>
		</td>
	</tr>
</thead>
<thead>
<tr>
	<td class="ancholibre">Cuota</td>
	<td class="ancholibre">Fecha<br/>Vencimiento<br/>Pago</td>
	<td class="ancholibre">Monto<br/>Cuota Bs.</td>
	<td class="ancholibre">Capital<br/>Bs.</td>
	<td class="ancholibre">Inter&eacute;s<br/>Bs.</td>
	<td class="ancholibre">Tasa<br/>%</td>
	<td class="ancholibre">Mora<br/>Bs.</td>
	<td class="ancholibre">Otros<br/>Cargos</td>
	<td class="ancholibre">Total<br/>Cuota<br/>Bs.</td>
	<td class="ancholibre">Monto<br/>Pagado<br/>Bs.</td>
	<td class="ancholibre">Fecha<br/>Pago</td>
	<td class="ancholibre">D&iacute;as<br/>Atraso</td>
	<td class="ancholibre">Saldo<br/>Capital Bs.</td>
</tr>
</thead>
<?php if($total_registros){ ?>
<tbody>
<?php foreach($pagos as $p){ ?>
<tr>
	<td class="texto_centro ancholibre"><?php echo $p['e02DLPPNU'] ?></td>
	<td class="texto_centro ancholibre"><?php echo formatea_fecha($p['e02DLPPD1'],$p['e02DLPPD2'],$p['e02DLPPD3']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($p['e02DLPTOT']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($p['e02AMNT01']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($p['e02AMNT03']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($p['e02DESC20']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($p['e02AMNT04']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($p['e02DLPOTH']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($p['e02TOAMNT']) ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($p['e02TOPAID']) ?></td>
	<td class="texto_centro ancholibre"><?php echo formatea_fecha($p['e02DLPDT1'],$p['e02DLPDT2'],$p['e02DLPDT3']) ?></td>
	<td class="texto_centro ancholibre"><?php echo $p['e02DLPVEN'] ?></td>
	<td class="monto ancholibre"><?php echo formatea_monto_fromWs($p['e02DLPBAL']) ?></td>
</tr>
<?php } ?>
</tbody>
<tfoot>
<tr>
<td colspan="13" class="paginador">
<?php
if(!isset($flag_busqueda)){
	$action = 'movimientos';
}else{
	$action = 'historial';
}
$modelo = 'DetalleProducto';

echo $form->create(
	'DetalleProducto',
	array(
		'url'=> array(
			'controller'=>'creditos',
			'action'=>$action
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
<?php } else { echo '<tbody><tr><td colspan="13" class="texto_centro">No se encontraron resultados</td></tr></tbody>'; }?>
</table>

<table class="tabla" cellpadding="0" cellspacing="0"  style="margin: 0px auto; width:800px;" border="0">
<thead>
	<tr>
		<td colspan="13" class="resumen_paginacion">
	<div class="botonera" style="text-align:right; width:48%; float:right;">
	
		<form id="form_volver" class="formulario_enlace" method="post" action="<?php echo Router::url($ruta_volver) ?>">
		<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo $this->data['DetalleProducto']['nro_credito'] ?>" />
		<input type="submit" class="button" value="Volver" />
		</form>
		<form id="form_imprimir" class="formulario_enlace" method="post" onsubmit="print();return false;">
		<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo $this->data['DetalleProducto']['nro_credito'] ?>" />
		<input type="submit" class="button" value="Imprimir" />
		</form>
	</div>
</td>
</tr>
</table>
</div>
<?php }?>