<?php
    /**
     *  Vista utilizada mostrar las cuotas pagadas
     */
?>

<h2 class="user">Cuotas Pagadas</h2>
<div class="line_title_orange"></div>
<div>

<div class="contenedor_encabezado" style="margin: 0px auto; width:650px;">
	<?php

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

	echo '<tr>
			<td class="etiqueta">N&uacute;mero Cr&eacute;dito:</td>
			<td class="campo">'. $this->data['DetalleProducto']['nro_credito'] .'</td>
		</tr>';

	echo '</table>';
	?>

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
		<td class="texto_centro ancholibre"><?php echo $detalle['e01APCDSC'] ?></td>
		<td class="texto_centro ancholibre"><?php if($detalle['e01DEASTS']=='C') { echo 'Cancelado'; } else { echo 'Activo'; } ?></td>
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
</div>
	
<div class="espacio_vertical_20"></div>

<table class="tabla" cellpadding="0" cellspacing="0" style="margin: 0px auto;">
<thead>
	<tr>
		<td colspan="13" class="resumen_paginacion"><?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?></td>
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
echo $form->create(
	'DetalleProducto',
	array(
		'url'=> array(
			'controller'=>'creditos',
			'action'=>'cuotas_pagadas'
		),
	)
);

if(!empty($this->data['DetalleProducto'])){
	foreach($this->data['DetalleProducto'] as $k => $v) {
		echo $form->hidden($k);
	}
}

echo $this->element('paginador',array('total'=>$total_registros));

echo $form->end();
?>
</td>
</tr>
</tfoot>
<?php } else { echo '<tbody><tr><td colspan="13" class="texto_centro error-message">No se encontraron resultados</td></tr></tbody>'; }?>
</table>
<table class="tabla" cellpadding="0" cellspacing="0"  style="margin: 0px auto; width:730px;" border="0">
<thead>
	<tr>
		<td colspan="13" class="resumen_paginacion">
	<div class="botonera">
	
		<form class="formulario_enlace" method="post" action="<?php echo Router::url('/creditos/movimientos') ?>">
		<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo $this->data['DetalleProducto']['nro_credito'] ?>" />
		<input type="submit" class="button" value="Volver" />
		</form>
		
		<form class="formulario_enlace" method="post" onsubmit="print();return false">
		<input type="submit" class="button" value="Imprimir" />
		</form>
		
	
	</div>
	</td>
</tr>
</table>
</div>
