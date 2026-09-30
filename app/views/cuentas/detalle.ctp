<?php
    /**
     * Vista utilizada para mostrar el detalle de los movimientos de la cuenta seleccionada
     */
?>

<div>
<h2 class="user">Estado de Cuenta</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_encabezado">
	<?php
	
	echo $form->create(
		'DetalleProducto',
		array(
			'url'=> array(
				'controller'=>'cuentas',
				'action'=>'detalle'
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
		echo '<td class="etiqueta">N&uacute;mero Cuenta:</td><td>'.enmascara($this->data['DetalleProducto']['nro_cuenta']).'</td>';
                echo '<tr><td class="etiqueta">Detalle Saldo Real</td></tr>';
	} else {
		echo $form->input(
			'nro_cuenta',
			array(
				'label'=>'N&uacute;mero Cuenta:',
				'type'=>'select',
				'options'=>$cuentas,
				'empty'=>false,
				'class'=>'cambio_producto'
			)
		);
                echo '<tr><td class="etiqueta">Detalle Saldo Real</td></tr>';
	}
	
	echo '</table>';

	echo $form->end();
	// print_r($res);die();
	?>

	<table class="tabla" cellpadding="0" cellspacing="0">
	<thead>
	<tr>
	<td>Nombre Producto</td>
	<td>Alias</td>
	<td>Tipo Moneda</td>
	<td>Saldo Total</td>
	<td>Monto Diferido</td>
	<td>Monto Retenido y Bloqueado</td>
	<td>Saldo Disponible</td>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td class="texto_centro"><?php echo $res['STMRDSJVReturn']['STMBDSJV']['STBPRO']; ?></td>
		<td class="texto_centro"><?php echo $alias; ?></td>
		<td class="texto_centro"><?php echo $moneda; ?></td>
		<td class="monto"><?php echo formatea_monto_fromWs($res['STMRDSJVReturn']['STMBDSJV']['STBMGB']); ?></td>
		<td class="monto">
			<?php
			if($res['STMRDSJVReturn']['STMBDSJV']['STBUNC']>0){
			?>
			<form class="formulario_enlace_right" method="post" action="<?php echo Router::url('/cuentas/diferido'); ?>">
			<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']; ?>" />
			<input type="submit" class="boton_enlace" value="<?php echo formatea_monto_fromWs($res['STMRDSJVReturn']['STMBDSJV']['STBUNC']); ?>" />
			</form>
			<?php
			}
			else{
				echo formatea_monto_fromWs($res['STMRDSJVReturn']['STMBDSJV']['STBUNC']);
			}
			?>
		</td>
		<td class="monto">
			<?php
			if($res['STMRDSJVReturn']['STMBDSJV']['STBHOL']>0){
			?>
			<form class="formulario_enlace_right" method="post" action="<?php echo Router::url('/cuentas/retenido'); ?>">
			<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']; ?>" />
			<input type="submit" class="boton_enlace" value="<?php echo formatea_monto_fromWs($res['STMRDSJVReturn']['STMBDSJV']['STBHOL']); ?>" />
			</form>
			<?php
			}
			else{
				echo formatea_monto_fromWs($res['STMRDSJVReturn']['STMBDSJV']['STBHOL']);
			}
			?>
		</td>
		<td class="monto"><?php echo formatea_monto_fromWs($res['STMRDSJVReturn']['STMBDSJV']['STBAVL']) ?></td>
	</tr>
	</tbody>
	</table>
<div class="espacio_vertical_10"></div>

<?php if (!$flag_buscar){ ?>
	<table class="tabla" cellpadding="0" cellspacing="0" style="width:100%">
	<tbody>
	<tr>
	<td align="center" width="50%">
	<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/buscar') ?>">
	<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']?>" />
	<input type="submit" class="boton_enlace" value="Búsqueda Avanzada" />
	</form>
	</td>
	<td align="center" width="50%">
	<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/historial') ?>">
	<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']?>" />
	<input type="submit" class="boton_enlace" value="Estados de Cuenta Históricos" />
	</form>
	</td>
	</tr>
	</tbody>
	</table>
	<table border ="0" cellpadding="0" cellspacing="0" align="center">
	<tr>
	<td colspan="2" align="center" class="requerido2">
	 <br>Estimado Cliente, a continuación le mostraremos los movimientos del día, en caso de requerir mayor información <br>presione el Botón de Búsqueda Avanzada
	</td>
	</tr>
	</table>
	
<?php } ?>
</div>

<div class="espacio_vertical_10"></div>
<table cellpadding="0" cellspacing="0" style="margin:0 auto; width:750px;" class="tabla">
<thead> 
	<tr>
		<td <?php echo (!$periodo) ? 'colspan="6"' : 'colspan="5"';?> class="resumen_paginacion"><?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?></td>
	</tr>
<tr>
<td>Fecha Real Transacci&oacute;n</td>
<td>Fecha Contable</td>
<td>Referencia</td>
<td>Descripci&oacute;n</td>
<td>Abono</td>
<td style="white-space:nowrap">Cargo</td>
<?php if(!$periodo){?>
<td style="white-space:nowrap">Saldo</td>
<?php }?>
</tr>
</thead>
<tbody>
<?php 
if(count($arrData)){
$j=0;

if(isset($this->params['form']['offset'])){
	$offset = $this->params['form']['offset'];
} else {
	$offset = 0;
}

foreach($arrData as $i){
$j++;
        $tipo = $i['STMCDE'];
        if (trim($i['STMNA9']) == '05305'){
            $tipo = 'PI';
        }

		if($offset == 0 && $j==1 && !$periodo){?> 
		<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>Saldo Final<td>&nbsp;</td><td></td><td class="monto"><?php echo formatea_monto_fromWs($i['STMEBL']) ?></td></tr>
		<?php } ?>
		<tr>
		
			<td class="texto_centro" width="10px"><?php echo formatea_fecha($i['STMBD1'],$i['STMBD2'],$i['STMBD3'])?></td>
			<td class="texto_centro" width="10px"><?php echo formatea_fecha(trim($i['STMNA8']),trim($i['STMNA7']),trim($i['STMNA6']))?></td>
			<?php if ($i['STMCDE'] == 'AE' || $i['STMCDE'] == 'WC' || $i['STMCDE'] == 'CI' || $i['STMCDE'] == 'WD' || $i['STMCDE'] == 'WN' || $i['STMCDE'] == 'PS' || $i['STMCDE'] == 'ND' || $i['STMCDE'] == 'DS' || $i['STMCDE'] == 'CW') {?>
			<td>
			
				<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/movimiento') ?>">
				<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']?>" />
				<input type="hidden" name="data[DetalleProducto][referencia]" value="<?php echo str_pad($i['STMCKN'],9,'0',STR_PAD_LEFT); ?>" />
				<input type="hidden" name="data[DetalleProducto][comision_cheque]" value="<?php echo trim($i['STMMEM']) ?>" />
				<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $tipo ?>" />
                                <input type="hidden" name="data[DetalleProducto][monto]" value="<?php echo formatea_monto_fromWs($i['STMAMT']) ?>" />
				<input type="submit" class="boton_enlace" value="<?php echo str_pad($i['STMCKN'],9,'0',STR_PAD_LEFT); ?>" />
				</form>
				</td>
			<?php } else{ ?>
				<td class="texto_centro"><?php echo str_pad($i['STMCKN'],9,'0',STR_PAD_LEFT); ?> </td>
			<?php }?>
			
			<td class="descripcion"><?php echo $i['STMNAR'] ?> </td>
			<td class="monto"><?php if($i['STMDCC']=='5') echo formatea_monto_fromWs($i['STMAMT']); else echo '0' ?></td>
			<td class="monto"><?php if($i['STMDCC']=='0') echo formatea_monto_fromWs($i['STMAMT']); else echo '0' ?></td>
			<?php if(!$periodo){?>
				<td class="monto"><?php echo formatea_monto_fromWs($i['STMEBL']) ?></td>
			<?php }?>
		</tr>
<?php
	}
	if(isset($i) && ($j+$offset)==$total_registros && !$periodo){
	?>
	<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>Saldo Inicial<td>&nbsp;</td><td></td><td class="monto"><?php echo formatea_monto_fromWs($i['STMBBL']) ?></td></tr>
<?php }}
else {
	?>
		<tr>
			<td <?php echo (!$periodo) ? 'colspan="7"' : 'colspan="6"';?>  class="texto_centro">No se encontraron resultados</td>
		</tr>
<?php
}
?>
</tbody>
<tfoot>
<tr>
<td colspan="4" class="total">Total:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['ABONOS'])?> </td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['CREDITOS'])?> </td>
<?php if(!$periodo){?>
<td class="monto_total">&nbsp;</td>
<?php }?>
</tr>
<tr>
<td <?php echo (!$periodo) ? 'colspan="7"' : 'colspan="6"';?> class="paginador">
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
			'controller'=>'cuentas',
			'action'=>$this->action
		)
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


<p style="width:750px;margin:0 auto">Esta información es entregada para efectos de referencia solamente y se encuentra sujeta a confirmación. No es un sustituto de su estado de cuenta periódico ni de otra información o confirmación que deba ser entregada por el banco, como los términos y condiciones del Banco.</p>

<div class="botonera" style="width:750px;margin:0 auto">
<form class="formulario_enlace" method="post" action="<?php echo Router::url($ruta_regreso) ?>">
	<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']; ?>" />
	<input type="submit" class="button" value="Volver" />
</form>

<?php if(count($arrData)){	?>
<form class="formulario_enlace" method="post" onsubmit="print();return false;">
	<input type="submit" class="button" value="Imprimir" />
</form>
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/exportar') ?>">
	<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']?>" />
	<input type="submit" class="button" value="Exportar" />
</form>
<?php }	?>
</div>