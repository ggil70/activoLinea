<?php
    /**
     * Vista para mostrar el listado con los últimos pagos de nómina y proveedores
     */
?>
<!--
    
-->

<div>
<h2 class="user">Seguimiento de Gestión</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>
<div class="descripcion">En esta opción podra verificar el resultado de la gestión, estatus y detalle de sus lotes de pago. Utilice las opciones de busqueda para ubicar el(los) lote(s) que desea verificar. Haga click en el enlace "Número de Lote" para ir al detalle.</div>
<div class="espacio_vertical_20"></div>

<?php

echo $form->create('TransaccionPendiente',
    array(
        'url'=> array(
                'controller'=>'cash',
                'action'=>'seguimiento_gestion_detalle',
        )
    )
);

echo $form->hidden('id');

if(count($tipo_lote)){
?>

<table  class="tabla" cellpadding="0" cellspacing="0" border="0" width="100%">


<?php
foreach($tipo_lote as $tipo => $lotes){
?>

<thead>
<tr>
<td colspan="8">
	<?php switch($tipo){
            case 'v':
                    echo 'Proveedores';
                    break;
            case 'n':
                    echo 'Nómina';
                    break;
            case 'm':
                echo 'Nómina Moneda Extranjera';
                break;  
            case 'x':
                echo 'Proveedores Moneda Extranjera';
                break;                
	} ?>
</td>
</tr>
</thead>
<thead>
<tr>
<td style="width:80px;">Fecha<br/>Valor</td>
<td style="width:90px;">Número de Lote</td>
<td style="width:50px;">Cantidad de<br/>Registros</td>
<td style="width:100px;">Monto Total<br/></td>
<td style="width:100px;">Monto Procesado<br/></td>
<td style="width:100px;">Monto Pendiente<br/></td>
<td>Descripción</td>
<td style="width:180px;">Estatus</td>
</tr>
</thead>
	<tbody>
		<?php foreach($lotes as $l) {?>
		<tr>			
			<td class="texto_centro" ><?php echo date('d/m/Y',strtotime($l['TransaccionPendiente']['fecha_hora_valor'])) ?></td>
			<td class="texto_centro" >
			<a href="javascript:detalle(<?php echo $l['TransaccionPendiente']['id'] ?>);void(0)">
                            <?php echo $l['TransaccionPendiente']['lote'] ?>
			</a>			
			</td>
			<td class="texto_centro"><?php echo $l['TransaccionPendiente']['cantidad_registros'] ?></td>
			<td class="monto"><?php echo formatea_monto_fromWs($l['TransaccionPendiente']['monto']) ?></td>
			<td class="monto"><?php echo formatea_monto_fromWs($l['TransaccionPendiente']['monto_procesado']) ?></td>
                        <td class="monto"><?php echo formatea_monto_fromWs($l['TransaccionPendiente']['monto_pendiente']) ?></td>
			<td>
                            <?php if(!empty($l['TransaccionPendiente']['descripcion'])){
				echo desencriptar($l['TransaccionPendiente']['descripcion']);
                            } ?>
                        </td>
                        <td><?php echo $l['TransaccionPendiente']['descripcion_estatus'] ?></td>
		</tr>
		<?php } ?>
		
		<tr>
			<td colspan="7" class="paginador">
				<div class="espacio_vertical_20"></div>
			</td>
		</tr>
	</tbody>
	
	<?php }?>
	</table>
	<?php	} else { ?>
		<p class="texto_centro">No se encontraron resultados...</p>
        <?php } ?>
	
<table class="confirmacion" cellspacing="0" cellpadding="0" border="0" style="width:100%;">
<tr>
	<td class="botonera" colspan="2">
		<?php 

		echo $html->link(
			'Volver',
			'seguimiento_gestion_filtros',
			array(
				'class'=>'button'
			)
		);

		echo '&nbsp;&nbsp;';				
		
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
</table>
<div class="espacio_vertical_20"></div>
<?php

echo $form->end();

?>

<?php	
echo $form->create(
    'PagoCash',
    array(
        'url'=> array(
                'controller'=>'cash',
                'action'=>$this->action
        ),
        'id' => 'paginacion'
    )
);

echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>


<script type="text/javascript">

function detalle(id){
    $('input[name="data[TransaccionPendiente][id]"]').val(id);
    $('#TransaccionPendienteSeguimientoGestionForm').submit();
    return false;
}

$(function(){
	
    $('#TransaccionPendienteSeguimientoGestionForm').validate({
        onsubmit:false,
        onkeyup:false,
        onclick:false,
        onfocusout:false,
        rules:{
            'data[gestion][]':{
                    required: true
            }
        }
    });	
});
</script>