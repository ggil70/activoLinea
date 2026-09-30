<?php
    /**
     * Vista para mostrar los filtos para la busqueda de informacion para el seguimiento de gestion
     */
?>
<!--
    
-->

<div>
<h2 class="user">Seguimiento de Gestión</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>
<div class="descripcion">En esta opción podra verificar el resultado de la gestión, estatus y detalle de sus lotes de pago. Utilice las opciones de busqueda para ubicar el(los) lote(s) que desea verificar.</div>
<div class="espacio_vertical_20"></div>
<?php

echo $form->create('TransaccionPendiente',
    array(
            'url'=> array(
                'controller'=>'cash',
                'action'=>'seguimiento_gestion_filtros',
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
            'border'=>'0',
            'align'=>'center',
            'style'=>'width:500px;'
	)
);
?>

<tr><td style="width:50%;"></td><td></td></tr>
<tr><td>
<?php
    echo $form->input(
        'operacion',
        array(
                'type'=>'select',
                'options'=>$tipos,
                'empty'=>'Seleccione',
                'id'=>'operacion',
                'label'=>'<span class="estiloTexto">Servicio:</span>'                
        )
    );						
?>
</td></tr>
<tr><td>
<?php
    echo $form->input(
        'estatus',
        array(
                'type'=>'select',
                'options'=>$estatus,
                'empty'=>'Seleccione',
                'id'=>'estatus',
                'label'=>'<span class="estiloTexto">Estatus:</span>'                
        )
    );						
?>
</td></tr>
<tr><td></td></tr>
	
<?php
echo $form->input(
	'lote',
	array(
            'label'=>'<span class="estiloTexto">Número de Lote:</span>',
            'type'=>'text',
            'maxlength'=>'10',
            'class'=>' numerico',
            'size'=>'30'
	)
);

if ($form->isFieldError('lote')){
    echo '<tr><td>&nbsp;</td><td>'.$form->error('lote', _m('valor.invalido',array('el n&uacute;mero de lote')) ).'</td></tr>';
}

echo $form->input(
	'fechaDesde',
	array(
            'label'=>'<span class="requerido">*</span><span class="estiloTexto"/>Fecha desde:',
            'type'=>'date',
            'timeFormat'=>null,
            'dateFormat'=>'DMY',
            'between'=>'</td><td style="width:250px;">',
            'minYear'=>date( 'Y', strtotime('-6 month') ),
            'maxYear'=>date( 'Y' )
	)
);

if ($form->isFieldError('fechaDesde')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('fechaDesde').'</td></tr>';
}

echo $form->input(
	'fechaHasta',
	array(
            'label'=>'<span class="requerido">*</span><span class="estiloTexto"/>Fecha hasta:',
            'type'=>'date',
            'timeFormat'=>null,
            'dateFormat'=>'DMY',
            'between'=>'</td><td style="width:250px;">',
            'minYear'=>date( 'Y', strtotime('-6 month') ),
            'maxYear'=>date( 'Y' )
	)
);

if ($form->isFieldError('fechaHasta')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('fechaHasta').'</td></tr>';
}

echo '<tr><td colspan="2" class="submit">';

echo $form->submit(
	'Aceptar',
	array(
            'div'=>false,
            'class'=>'button'
	)
);

echo '</td></tr></table>';

echo $form->end();

?>

<script type="text/javascript">

$(function(){
	
	$('#TransaccionPendienteSeguimientoGestionFiltrosForm').validate({
            rules:{
                'data[TransaccionPendiente][lote]':{
                        digits:true
                },                
                'data[TransaccionPendiente][fechaDesde][year]':{                      
                        fechaMenorIgualQueFijo: '<?php echo date('m/d/Y'); ?>',
                        fechaMayorIgualQueFijo: '<?php echo date('m/d/Y', strtotime('-6 month') ); ?>'
                },
                'data[TransaccionPendiente][fechaHasta][year]':{
                        fechaMayorIgualQue: '#TransaccionPendienteFechaDesdeYear',
                        fechaMenorIgualQueFijo: '<?php echo date('m/d/Y'); ?>'
                }
            },
            messages:{
                'data[TransaccionPendiente][lote]':{
                        digits:<?php echo _m('valor.invalido',array('el n&uacute;mero de lote'),true) ?>
                },                     
                'data[TransaccionPendiente][fechaDesde][year]':{
                        fechaMenorIgualQueFijo: <?php echo _m('fecha.menor.igual.actual',null,true) ?>,
                        fechaMayorIgualQueFijo: <?php echo _m('fecha.mayor.igual.fija','a 6 meses',true) ?>
                },
                'data[TransaccionPendiente][fechaHasta][year]':{
                        fechaMayorIgualQue: <?php echo _m('fecha.mayor.igual.que', NULL, true) ?>,
                        fechaMenorIgualQueFijo: <?php echo _m('fecha.menor.igual.actual',null,true) ?>
                }
            }		
	});	
});
</script>