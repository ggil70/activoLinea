<?php
    /**
     * Vista utilizada para mostrar el detalle de los movimientos P2P de la cuenta seleccionada
     */
?>

<div>
<h2 class="user">Estado de Cuenta Pago Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_encabezado">
	<?php
	
	echo $form->create(
		'DetalleProducto',
		array(
                    'url'=> '/pago_activo/detalle',
                    'id'=>'DetalleProductoForm',
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
	} else {
            
                $input_cuentas = array(
                    'label'=>'N&uacute;mero Cuenta:',
                    'type'=>'select',
                    'options'=>$cuentas,
                    'empty'=>false,
                    'class'=>'cambio_producto'
                );

                if(count($cuentas) == 0) $input_cuentas['empty'] = 'No posee cuentas afiliadas';                

                echo $form->input(
                    'nro_cuenta',
                    $input_cuentas
                );		            
	}
	
	echo '</table>';

	echo $form->end();
	
	?>	
<div class="espacio_vertical_10"></div>

<?php if (!$flag_buscar){ ?>	
	<table border ="0" cellpadding="0" cellspacing="0" align="center">
	<tr>
	<td colspan="2" align="center" class="requerido2">
	 <br>Estimado Cliente, a continuación le mostraremos los movimientos del mes, en caso de requerir mayor información <br>presione el Botón de Búsqueda Avanzada
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
<td>Referencia</td>
<td>Descripci&oacute;n</td>
<td>Abono Bs.</td>
<td style="white-space:nowrap">Cargo Bs.</td>
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

    if($offset == 0 && $j==1 && !$periodo){?>		
    <?php } ?>
    <tr>				
        <td class="texto_centro" width="10px"><?php echo formatea_fecha(trim($i['STMNA8']),trim($i['STMNA7']),trim($i['STMNA6']))?></td>                       
        <td class="texto_centro"><?php echo str_pad($i['STMCKN'],9,'0',STR_PAD_LEFT); ?> </td>           
        <td class="descripcion"><?php echo $i['STMNAR'] ?> </td>
        <td class="monto"><?php if($i['STMDCC']=='5') echo formatea_monto_fromWs($i['STMAMT']); else echo '0' ?></td>
        <td class="monto"><?php if($i['STMDCC']=='0') echo formatea_monto_fromWs($i['STMAMT']); else echo '0' ?></td>            
    </tr>
<?php
	}
    }
else {
    ?>
    <tr>
        <td colspan="5"  class="texto_centro">No se encontraron resultados</td>
    </tr>
<?php
}
?>
</tbody>
<tfoot>
<tr>
<td colspan="3" class="total">Total Bs.:</td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['ABONOS'])?> </td>
<td class="monto_total"><?php echo formatea_monto_fromWs($totales['CREDITOS'])?> </td>
</tr>
<tr>
<td colspan="5" class="paginador">
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
            'controller'=>'pago_activo',
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
<?php if(!empty($ruta_regreso)){ ?>    
<form class="formulario_enlace" method="post" action="<?php echo Router::url($ruta_regreso) ?>">
    <input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']; ?>" />
    <input type="submit" class="button" value="Volver" />
</form>
<?php }	?>
<?php if(count($arrData)){ ?>
<form class="formulario_enlace" method="post" onsubmit="print();return false;">
    <input type="submit" class="button" value="Imprimir" />
</form>
<?php }	?>
<?php if(empty($ruta_regreso)){ ?>     
<form class="formulario_enlace" method="post" action="<?php echo Router::url('/pago_activo/buscar') ?>">
    <input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']?>" />
    <input type="submit" class="button" value="Búsqueda Avanzada" />
</form>    
<?php }	?>    
</div>