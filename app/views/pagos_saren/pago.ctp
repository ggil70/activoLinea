<?php
/**
 * Formulario de pago de tarjetas propias en banco activo
**/
?>
<div>
<h2 class="user">Pagos Saren</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Información</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmación</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operación</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

    <?php

    echo $form->create('PagoSaren',
        array(
            'url'=> array(
                'controller'=>'pagos_saren',
                'action'=>'pago'
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
    
    echo '<input type="hidden" id="none" />';

    echo $html->tag(
        'table',
        null,
        array(
            'class'=>'formulario',
            'cellpadding'=>'0',
            'cellspacing'=>'0',
            'border'=>'0',
            'align'=>'center',
            'style'=>'table-layout:fixed'
        )
    );
    
    // La primera fila define la tabla con layout fixed
    echo '<tr><td style="width:180px"></td><td style="width:270px"></td></tr>';
    
    echo '<tr><td colspan="2">A continuación proceda a ingresar los datos requeridos.</td></tr>';
    echo '<tr><td colspan="2"></td></tr>';

?>
    
<?php

$input_debitar = array(
        'label'=>'<span class="requerido">* </span>Cuenta de Débito:',
        'type'=>'select',
        'options'=>$cuentas
    );
    
    if(count($cuentas) > 1) $input_debitar['empty'] = 'Seleccione';
    
    echo $form->input(
        'cuentaDebitar',
        $input_debitar
    );
    

    if ($form->isFieldError('cuentaDebitar')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaDebitar',
                array(
                    'requerido'=>_m('seleccion.requerida',array('la cuenta de débito'))
                )           
            ).'</td></tr>';
    }
            
            echo $form->input(
                'nro_planilla',
                array(
                    'label'=>'<span class="requerido">*</span> Número de Planilla:',
                    'div'=>false,
                    'type'=>'text',
                    'maxlength'=>'11',
                    'minLength'=>'11',
                    'class'=>'numerico',
                    
                )
            );
    
    
    if ($form->isFieldError('nro_planilla')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nro_planilla',
            array(
                'requerido'=>_m('campo.requerido','número de planilla')
            )
        ).'</td></tr>';
    }
    
    echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
    
    echo '<tr><td>&nbsp;</td><td class="submit">';

    echo $form->submit(
        'Aceptar',
        array(
            'class'=>'button'
        )
    );    

    echo '</td></tr></table>';

    echo $form->end();
?>
</div>

<script type="text/javascript">
var ajaxPendiente = false;
    
$(function(){
        
    $('#aceptar').click(function(){
        if(ajaxPendiente){
            return false;
        }
    });

    $('#PagoSarenPagoForm').validate({
        rules:{
            'data[PagoSaren][cuentaDebitar]':{
                required:true
            },
            'data[PagoSaren][nro_planilla]':{
                required:true
            }
        },  
        messages:{
            'data[PagoSaren[cuentaDebitar]':{
                required:<?php echo _m('seleccion.requerida',array('la cuenta de débito'),true) ?>
            },
            'data[PagoSaren][nro_planilla]':{
                required:<?php echo _m('campo.requerido','el número de planilla',true) ?>,
            }
        }
    });
});





</script>