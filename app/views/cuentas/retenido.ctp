<?php
    /**
     * Vista utilizada para mostrar el saldo retenido de una cuenta
     */
?>

<div>
<h2 class="user">Monto Retenido y Bloqueado</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_encabezado">
	<?php

echo $form->create(
	'DetalleProducto',
	array(
		'url'=> array(
			'controller'=>'cuentas',
			'action'=>'retenido'
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

echo $form->input(
	'nro_cuenta',
	array(
		'label'=>'Número Cuenta:',
		'type'=>'select',
		'options'=>$cuentas,
		'empty'=>false,
			'class'=>'cambio_producto'
	)
);

echo $form->end();

?>

	<table class="tabla" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td>Descripción</td>
	<td>Fecha</td>
	<td>Fecha Vencimiento</td>
	<td>No. Días</td>
	<td>Monto</td>
	</tr>
	</thead>
	<tbody>
<?php
if(isset($retenido) && count($retenido)>0){
foreach($retenido as $i){
?>
<tr>
<td class="descripcion"><?php echo $i['e01UNCRSN'] ?></td>
<td class="texto_centro"><?php echo formatea_fecha($i['e01UNCRD1'],$i['e01UNCRD2'],$i['e01UNCRD3'])?></td>
<td class="texto_centro"><?php echo formatea_fecha($i['e01UNCMD1'],$i['e01UNCMD2'],$i['e01UNCMD3'])?></td>
<td class="texto_centro"><?php echo $i['e01UNCDYS'] ?></td>
<td class="monto"><?php echo formatea_monto_fromWs($i['e01UNCAMN']) ?></td>
</tr>
<?php
	}

}else {
	?>
		<tr>
			<td colspan="5" class="texto_centro" style="width:650px;">No se encontraron resultados</td>
		</tr>
<?php
}
?>

</body>
</table>

<div class="botonera">
	<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/detalle') ?>">
		<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta'] ?>" />
		<input type="submit" class="button" value="Volver" />
	</form>
	<form class="formulario_enlace" method="post" onsubmit="print();return false;">
		<input type="submit" class="button" value="Imprimir" />
	</form>	
</div>		
</div>