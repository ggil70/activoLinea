<?php
    /**
     * Vista para mostrar el listado de afiliaciones de Pago Activo por canal que posee el cliente
     */
?>
<div>
<h2 class="user">Afiliaciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p>En esta opci&oacute;n Ud. podr&aacute; crear, modificar y desafiliar cuentas y tarjetas del Servicio Pago Activo</p>

<?php
echo $form->create('Afiliacion',
    array(
        'url'=> array(
            'controller'=>'pago_activo',
            'action'=>'listado_pago_activo'
        )                
    )
);

?>
<input type="hidden" name="data[accion]">

<table class="formulario" cellpadding="0" cellspacing="0" border="0">	
	<tr>
	<td>
		<label for="data[Afiliacion][id]" class="error manual"><?php echo _m('uno.seleccion.requerida','de los registros'); ?></label>
		<?php
			if ($form->isFieldError('id')){
				echo $form->error('id', _m('uno.seleccion.requerida','de los registros'));
			}
		?>
	</td>
	</tr>
</table>
<table class="tabla" width="100%" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
            <td colspan="7" class="resumen_paginacion">			
                <div style="text-align:right; width:38%; float:right;">
                        <?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?>
                </div>
            </td>
	</tr>
        </thead>
        <thead>
	<tr>
		<td class="texto_centro ancholibre"><span class="requerido">*</span></td>
		<td>Cuentas/Tarjeta Pagadora</td>
                <td>Número Teléfono</td>
		<td>Cuenta Receptora</td>		
		<td>Canal</td>
                <td>Tipo</td>
                <td>Afiliado</td>
	</tr>
	</thead>
	
	<tbody>
		<?php 
		if(count($listado)){
                    foreach($listado as $l) {
		?>
		<tr>
                    <td class="texto_centro ancholibre">
                        <input type="radio" name="data[Afiliacion][id]" value="<?php echo $l['Afiliacion']['id'] ?>"/>
                    </td>
                    <?php
                        $numero_cuenta = trim($l['Afiliacion']['numero_cuenta']);
                        $numero_cuenta_ahorro = trim($l['Afiliacion']['numero_cuenta_ahorro']);
                        if((!empty($numero_cuenta) || !empty($numero_cuenta_ahorro)) && $l['Afiliacion']['tipo_servicio'] != 7){
                    ?>
                        <td class="texto_centro ancholibre"><?php echo enmascara(desencriptar($l['Afiliacion']['numero_cuenta'])); ?> <br> <?php echo enmascara(desencriptar($l['Afiliacion']['numero_cuenta_ahorro'])); ?></td>
                    <?php
                        }else{
                    ?>
                        <td class="texto_centro ancholibre">No Posee</td>
                    <?php } ?>    
                    <td class="texto_centro ancholibre"><?php echo $l['Afiliacion']['codigo_telefono'].desencriptar($l['Afiliacion']['telefono']); ?></td>
                    <td class="texto_centro ancholibre"><?php echo trim($l['Afiliacion']['numero_cuenta_receptora'])!='' ? enmascara(desencriptar($l['Afiliacion']['numero_cuenta_receptora'])) : 'No Recibe Pagos'; ?></td>
                    <td class="texto_centro ancholibre">Pago Activo</td>
                    <td class="texto_centro ancholibre"><?php echo $canal[trim($l['Afiliacion']['tipo_servicio'])]; ?></td>
                    <?php
                        $numero_contrato = trim($l['Afiliacion']['numero_contrato']);                        
                        if(!empty($numero_contrato)){
                    ?>
                        <td class="texto_centro ancholibre"><?php echo desencriptar($l['Afiliacion']['numero_contrato']); ?></td>
                    <?php
                        }else{
                    ?>
                        <td class="texto_centro ancholibre">No Aplica</td>
                    <?php } ?>  
		</tr>
		<?php } ?>
		<tr>
                    <td colspan="7" class="paginador">				
                            <?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros)); ?>
                    </td>
		</tr>
		
		<tr><td colspan="7" style="border: 0px;" ><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>
	
		<tr>
                    <td colspan="7" class="botonera">

                            <?php 
                            if($superado==0){
                                echo $html->link(
                                        'Crear',
                                        'javascript:',
                                        array(
                                            'class'=>'button',
                                            'id'=>'crear'
                                        )
                                    );
                            }                                                               
                            echo '&nbsp;&nbsp;&nbsp;';

                            echo $html->link(
                                    'Modificar',
                                    'javascript:',
                                    array(
                                        'id'=>'modificar',
                                        'class'=>'button'
                                    )
                            );

                            echo '&nbsp;&nbsp;&nbsp;';

                            echo $html->link(
                                    'Desafiliar',
                                    'javascript:',
                                    array(
                                        'class'=>'button',
                                        'id'=>'eliminar'						
                                    )
                            );
                            ?>

                    </td>
		</tr>
		
		<?php
		}
		else{
		?>
                    <tr><td colspan="7" class="texto_centro">No se encontraron resultados...</td></tr>
                    <tr>
                            <td colspan="5" class="botonera">
                                    <?php 
                                    echo $html->link(
                                            'Crear',
                                            'javascript:',
                                            array(
                                                'class'=>'button',
                                                'id'=>'crear'
                                            )
                                    );
                                    ?>
                            </td>
                    </tr>
		<?php
		}
		?>
	</tbody>
</table>
<?php echo $form->end(); ?>
</div>

<?php	
echo $form->create(
	'Afiliacion',
	array(
		'url'=> array(
                    'controller'=>'pago_activo',
                    'action'=>$this->action
		),
		'id' => 'paginacion'
	)
);
if(!empty($this->data['Afiliacion'])){
	foreach($this->data['Afiliacion'] as $k => $v) {
            echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script type="text/javascript">

$(function(){

	$('#crear').click(function(){            
            $('input[name="data[accion]"]').val('crear');            
            $('#AfiliacionListadoPagoActivoForm').submit();		
	});

	$('#modificar').click(function(){
            if($('#AfiliacionListadoPagoActivoForm').valid()){		
                $('input[name="data[accion]"]').val('editar');
                $('#AfiliacionListadoPagoActivoForm').submit();
            }
	});

	$('#eliminar').click(function(){
            if($('#AfiliacionListadoPagoActivoForm').valid()){		
                $('input[name="data[accion]"]').val('eliminar');
                $('#AfiliacionListadoPagoActivoForm').submit();
            }
	});
	
	$('#AfiliacionListadoPagoActivoForm').validate({
		onsubmit:false,
		onkeyup:false,
		onclick:false,
		onfocusout:false,
		rules:{
                    'data[Afiliacion][id]':{
                            required: true
                    }
		}
	});	
});
</script>
