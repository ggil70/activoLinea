<?php
  /**
   * Vista para crear/modificar notificaciones de viaje
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

	<?php

	echo $form->create('NotificacionAusencia',
            array(
                'url'=> '/seguridad/notificacion_crear',
                'id'=>'NotificacionCrearForm',
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
                
	echo $form->hidden('id');	
	
        echo $html->tag(
            'table',
            null,
            array(
                'class'=>'formulario',
                'cellpadding'=>'0',
                'cellspacing'=>'0',
                'border'=>'0',
                'align'=>'center',
                'style'=>'table-layout:fixed;'
            )
	);
	
	// La primera fila define la tabla con layout fixed
	echo '<tr><td style="width:200px"></td><td style="width:150px"></td></tr>';

	?>
        <?php if(isset($this->data['NotificacionAusencia']['accion']) && $this->data['NotificacionAusencia']['accion'] == 'Modificación'){ ?>
            <tr><td colspan="2">A continuaci&oacute;n proceda a modificar los datos de la notificaci&oacute;n.</td></tr>
        <?php }else{ ?>
            <tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la notificaci&oacute;n.</td></tr>
        <?php } ?>    		        
        <?php 
               
        $input_ubicacion = array(
            'label'=>'<span class="requerido">* </span>Ubicación de Seguridad:',
            'type'=>'select',
            'options'=>$paises
	);
	
	if(count($paises) > 1) $input_ubicacion['empty'] = 'Seleccione';
	
	echo $form->input(
            'siglas',
            $input_ubicacion
	);
	

	if ($form->isFieldError('codigo_pais')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('codigo_pais',
                array(
                    'requerido'=>_m('seleccion.requerida','el pais')
                )			
            ).'</td></tr>';
	}
        
        echo $form->input(
            'fecha_inicio',
            array(
                'label'=>'<span class="requerido">* </span>Fecha desde:',
                'type'=>'date',
                'timeFormat'=>null,
                'dateFormat'=>'DMY',
                'between'=>'</td><td style="width:250px;">',
                'minYear'=>date( 'Y', strtotime('-1 year') ),
                'maxYear'=>date( 'Y', strtotime('+1 year'))
            )
        );

        if ($form->isFieldError('fecha_inicio')){
            echo '<tr><td>&nbsp;</td><td>'.$form->error('fecha_inicio').'</td></tr>';
        }

        echo $form->input(
            'fecha_fin',
            array(
                'label'=>'<span class="requerido">* </span>Fecha hasta:',
                'type'=>'date',
                'timeFormat'=>null,
                'dateFormat'=>'DMY',
                'between'=>'</td><td style="width:250px;">',
                'minYear'=>date( 'Y', strtotime('-1 year') ),
                'maxYear'=>date( 'Y', strtotime('+1 year'))
            )
        );        
        
        if ($form->isFieldError('fecha_fin')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('fecha_fin',
                array(
                    'requerido'=>_m('campo.requerido','la fecha hasta'),                   
                    'tiempoMax'=>_m('mensaje.limite.viaje',_p('limite_plazo_ausencia')),
                )
            ).'</td></tr>';
	}                	        		
        
	echo '<tr><td class="botonera" colspan="2">';

	echo $html->link(
		'Volver',
		'/seguridad/notificacion_viaje/v',
		array(
                    'class'=>'button'
		)
	);

	echo '&nbsp;&nbsp;&nbsp;';

	echo $html->link(
		'Aceptar',
		'javascript:',
		array(
                    'class'=>'button envio_formulario',
                    'id'=>'aceptar',
                    'div' => false
		)
	);

	echo '</td></tr></table>';              
        
	echo $form->end();
	?>
</div>


<script type="text/javascript">

$(function(){			        
        
	// para la validacion
	$('#NotificacionCrearForm').validate({		
            rules:{	                        			
                    'data[NotificacionAusencia][codigo_pais]':{
                        required:true                                
                    },
                    'data[NotificacionAusencia][fecha_inicio][year]':{
                            fechaMenorIgualQueFijo: '<?php echo date('m/d/Y'); ?>',
                            fechaMayorIgualQueFijo: '<?php echo date('m/d/Y', strtotime('-1 year') ); ?>'
                    },
                    'data[NotificacionAusencia][fecha_fin][year]':{                            
                            fechaMenorIgualQueFijo: '<?php echo date('m/d/Y'); ?>
                    }
            },
            messages:{                        
                    'data[NotificacionAusencia][codigo_pais]':{
                            required:<?php echo _m('campo.requerido', 'el campo solicitado', true) ?>
                    },
                    'data[NotificacionAusencia][fecha_inicio][year]':{
                            fechaMenorIgualQueFijo: <?php echo _m('fecha.menor.igual.actual',null,true) ?>,
                            fechaMayorIgualQueFijo: <?php echo _m('fecha.mayor.igual.fija','a un año',true) ?>
                    },
                    'data[NotificacionAusencia][fecha_fin][year]':{
                            fechaMayorIgualQue: <?php echo _m('fecha.mayor.igual.que', NULL, true) ?>,
                            fechaMenorIgualQueFijo: <?php echo _m('fecha.menor.igual.actual',null,true) ?>
                    }

            }	
	});		
	
 });
	
 
</script>