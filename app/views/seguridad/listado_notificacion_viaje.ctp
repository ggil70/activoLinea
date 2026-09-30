<?php
    /**
     * Vista para mostrar el listado de notificaciones de viaje que posee el cliente
     */
?>
<div>
<h2 class="user">Notificaci&oacute;n de Viaje/Ausencia</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Ubicaci&oacute;n de Seguridad</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p>En esta opci&oacute;n Ud. podr&aacute; crear, modificar y eliminar notificaciones de Ausencia/Viaje</p>

<?php
echo $form->create('NotificacionAusencia',
	array(
		'url'=> array(
			'controller'=>'seguridad',
			'action'=>'listado_notificacion_viaje'
		),
                'id' => 'NotificacionViajeListadoForm'
	)
);

?>
<input type="hidden" name="data[accion]">

<table class="formulario" cellpadding="0" cellspacing="0" border="0">	
	<tr>
	<td>
            <label for="data[NotificacionAusencia][id]" class="error manual"><?php echo _m('uno.seleccion.requerida','de los registros'); ?></label>
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
            <td colspan="6" class="resumen_paginacion">
                <div style="text-align:left; width:58%; float:left;">
                        Seleccione la notificación que desea modificar:
                </div>
                <div style="text-align:right; width:38%; float:right;">
                        <?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?>
                </div>
            </td>
	</tr>
        </thead>
        <thead>
	<tr>
            <td class="texto_centro ancholibre"><span class="requerido">*</span></td>
            <td>Ubicación</td>
            <td>Período</td>		            		
	</tr>
	</thead>
	
	<tbody>
            <?php 
            if(count($listado)){
                foreach($listado as $l) {
            ?>
            <tr>
                <td class="texto_centro ancholibre">
                        <input type="radio" name="data[NotificacionAusencia][id]" value="<?php echo $l['NotificacionAusencia']['id'] ?>"/>
                </td>
                <td class="texto_centro ancholibre"><?php echo $paises[$l['NotificacionAusencia']['codigo_pais']]; ?></td>
                <td class="texto_centro ancholibre"><?php echo date('d/m/Y', strtotime($l['NotificacionAusencia']['fecha_inicio'])) . "-" . date('d/m/Y', strtotime($l['NotificacionAusencia']['fecha_fin'])); ?></td>                
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td colspan="6" class="paginador">				
                    <?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros)); ?>
                </td>
            </tr>
            <tr><td colspan="6" style="border: 0px;" ><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>
            <tr>
                <td colspan="6" class="botonera">

                    <?php 
                    
                    if(count($listado)<_p('limite_ubicacion_seguridad')){                        
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

                   /* echo $html->link(
                        'Modificar',
                        'javascript:',
                        array(
                            'id'=>'modificar',
                            'class'=>'button'
                        )
                    );

                    echo '&nbsp;&nbsp;&nbsp;';*/

                    echo $html->link(
                        'Eliminar',
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
                <tr><td colspan="4" class="texto_centro">No se encontraron resultados...</td></tr>
                <tr>
                    <td colspan="4" class="botonera">
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
	'NotificacionAusencia',
	array(
            'url'=> array(
                'controller'=>'seguridad',
                'action'=>$this->action
            ),
            'id' => 'paginacion'
	)
);
if(!empty($this->data['NotificacionAusencia'])){
    foreach($this->data['NotificacionAusencia'] as $k => $v) {
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
            $('#NotificacionViajeListadoForm').submit();		
	});

	/*$('#modificar').click(function(){
            if($('#NotificacionViajeListadoForm').valid()){		
                $('input[name="data[accion]"]').val('editar');
                $('#NotificacionViajeListadoForm').submit();
            }
	});*/

	$('#eliminar').click(function(){
            if($('#NotificacionViajeListadoForm').valid()){		
                $('input[name="data[accion]"]').val('eliminar');
                $('#NotificacionViajeListadoForm').submit();
            }
	});
	
	$('#NotificacionViajeListadoForm').validate({
		onsubmit:false,
		onkeyup:false,
		onclick:false,
		onfocusout:false,
		rules:{
                    'data[NotificacionAusencia][id]':{
                        required: true
                    }
		}
	});	
});
</script>
