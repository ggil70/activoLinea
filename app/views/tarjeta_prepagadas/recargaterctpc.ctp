<?php
    /**
     * Vista para mostrar el consolidado de productos
     */

?>


<div>
<h2 class="user">Tarjeta Prepago CASH / Recarga a Tercero</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitar Informaci&oacute;n Beneficiario</div>

<div class="paso_nro">2</div>
<div class="paso_desc">Recargar Monto</div>

<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>

<div class="paso_nro">4</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>

<div class="paso_nro">5</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<br><br><br>




<?php

    echo $form->create('Recargaterctpc',
            array('url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'recargaterctpc',
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

    $nacionalidad = array('V'=>'V','E'=>'E','P'=>'P');
    echo $form->input(
        'tipoCedula',
        array(
            'after'=>false,
            'label'=>'<span class="requerido">* </span>C&eacute;dula / Pasaporte del Beneficiario:',
            'type'=>'select',
            'class'=>'tipoRif',
            'options'=>$nacionalidad
        )
    );

    echo "&nbsp;&nbsp;";

    echo $form->input(
        'nroCedula',
        array(
            'before'=>false,
            'between'=>false,
            'label'=>false,
            'type'=>'text',
            'class'=>'nroRif',
            'maxlength'=>15
        )
    );

    if ($form->isFieldError('tipoCedula')){  
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tipoCedula',
                array(
                    'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de documento de identidad')),
                    'requerido'=>_m('campo.requerido',array('el tipo de documento de identidad'))
                )).'</td></tr>';
    } else if ($form->isFieldError('nroCedula')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nroCedula',
                array(
                    'requerido'=>_m('campo.requerido',array('la cédula o pasaporte del beneficiario')),
                    'longitud'=>_m('valor.invalido',array('la cédula o pasaporte del beneficiario')),
                    'destinatario'=>_m('identificacion.igualcliente',array('la cédula o Pasaporte del beneficiario'))
                )).'</td></tr>';
    }

    ?>

   <?php 
  
    echo '<tr><td>&nbsp;</td><td class="submit">';

    echo $form->submit(
        'Validar Beneficiario',
        array(
            'class'=>'button'
        )
    );     


    echo '</td></tr></table>';
    echo $form->end();

?>


<script type="text/javascript">
    $(function(){


        $('#RecargaterctpcRecargaterctpcForm').validate({
                rules:{
                    'data[Recargaterctpc][nroCedula]':{
                        required:true,
                        digits:{
                            depends: function(e){
                            return $('#RecargaterctpcTipoCedula').val()=='V';
                        }               
                    },
                    alphanumeric:{
                        depends: function(e){
                            return $('#RecargaterctpcTipoCedula').val()=='P';
                        }               
                    }
                },
               
                },
                messages:{
                    'data[Recargaterctpc][nroCedula]':{
                        required:<?php echo _m('campo.requerido',array('la cédula o pasaporte del beneficiario'),true) ?>,
                        digits:<?php echo _m('formato.invalido',array('la cédula o pasaporte del beneficiario'),true) ?>,
                        alphanumeric:<?php echo _m('alfanumerico.invalido',array('el pasaporte del beneficiario'),true) ?>
                    },          
                },
        });

    }); 





</script>

