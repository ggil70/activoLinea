<?php
/**
 * Formulario Solicitud de Tarjeta Prepago Cash  
**/

?>
<div>
<h2 class="user">Solicitud de Tarjeta Prepago CASH </h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Seleccionar Tipo de Tarjeta</div>

<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Solicitud Informaci&oacute;n Banco</div>

<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">4</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">5</div>
<?php } ?>

<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<br><br><br>
<?php 
    echo $form->create('PresolicitudFisica',
            array('url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'Presolicitud_fisica'
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
                    'align'=>'center'
        )
    );

    $input_cuentaBS = array(
            'label'=>'<span class="requerido">* </span>Cuenta Para Debitar Comisión:',
            'type'=>'select',
            'id'=>'cuentaBS',
            'empty'=>'Seleccione',
            'options'=>$cuentasBS,
            'value'=>''
    );
    
    echo $form->input(
            'cuentaBS',
            $input_cuentaBS
    );

    if ($form->isFieldError('cuentaBS')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaME',
        array(
        )
            ).'</td></tr>'; 
    }


    $input_agencias = array(
            'label'=>'<span class="requerido">* </span>Agencia de Retiro de Tarjeta Prepago CASH:',
            'type'=>'select',
            'id'=>'agencia',
            'empty'=>'Seleccione',
            'options'=>$agencias,
            'value'=>''
    );
    

    echo $form->input(
            'agencia',
            $input_agencias
    );

    if ($form->isFieldError('agencia')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('agencia',
        array(
        )
            ).'</td></tr>'; 
    }    
  
    


    echo '<tr><td>&nbsp;</td><td class="submit">';

    echo $form->submit(
        'Aceptar',
        array(
            'class'=>'button'
        )
    );     


    echo '</td></tr>';

    echo '<tr><td colspan="2"><span class="requerido2">'._m('tcp.mensaje.emision.virtual').$etiqComisionBS.'</span></td></tr>';

    echo '</table>';
    echo $form->end();



?>
<script type="text/javascript">
 


    $(function(){


        $('#PresolicitudFisicaPresolicitudFisicaForm').validate({
                rules:{
                    'data[PresolicitudFisica][cuentaBS]':{
                        required:true
                    },

                    'data[PresolicitudFisica][agencia]':{
                        required:true
                    },
               
                },
                messages:{
                    'data[PresolicitudFisica][cuentaBS]':{
                        required:<?php echo _m('seleccion.requerida',array('la Cuenta a debitar'), true) ?>
                    },
                    
                    'data[PresolicitudFisica][agencia]':{
                        required:<?php echo _m('seleccion.requerida',array('la Agencia de retiro de la tarjeta'), true) ?>
                    },
                 


                },
        });

    }); 





</script>
