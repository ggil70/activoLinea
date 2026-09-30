<?php
/**
 * Formulario de TPC Solicitud Virtual
**/
die("Mensaje: " . _m('tcp.mensaje.emision.virtual'));

?>
<div>
<h2 class="user">Solicitud de Tarjeta Prepago CASH Virtual</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud Informaci&oacute;n Banco</div>

<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">4</div>
<?php } ?>

<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<br><br><br>
<?php 
    echo $form->create('Presolicitudvirtual',
            array('url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'Presolicitudvirtual'
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
            'label'=>'<span class="requerido">* </span>Cuenta Bs Para Debitar Comisión:',
            'type'=>'select',
            'id'=>'cuentaBS',
            'empty'=>'Seleccione',
            'options'=>$cuentasBS,
            'value'=>$idCuentaBS
    );
    
    echo $form->input(
            'cuentaBS',
            $input_cuentaBS
    );

    if ($form->isFieldError('cuentaBS')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaBS',
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
    


    echo '<tr><td colspan="2"><span class="requerido2">'. _m('tcp.mensaje.emision.virtual'). ' ' .$etiqComisionBS.'</span></td></tr>';


    echo '</table>';
    echo $form->end();



?>
<script type="text/javascript">
    $(function(){


        $('#PresolicitudvirtualPresolicitudvirtualForm').validate({
                rules:{
                    'data[Presolicitudvirtual][cuentaBS]':{
                        required:true
                    },               
                },
                messages:{
                    'data[Presolicitudvirtual][cuentaBS]':{
                        required:<?php echo _m('seleccion.requerida',array('la Cuenta a debitar'), true) ?>
                    },
                },
        });

    }); 
</script>
