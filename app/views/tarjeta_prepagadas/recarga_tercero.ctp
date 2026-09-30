<?php

/**
 * Formulario inicial de transferencias a cuentas de terceros en otros bancos
**/
?>
<div>
    <h2 class="user">Tarjeta Prepago Cash / Recarga Tercero en Activo</h2>
    <div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario_400">
    <?php

    echo $form->create('Recarga',
        array(
            'url'=> array(
                'controller'=>'tarjeta_prepagadas',
                'action'=>'recarga_tercero'
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
    
    echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la Recarga.</td></tr>';

    $input_debitar = array(
        'label'=>'<span class="requerido">* </span>Cuenta Cash Debitar:',
        'type'=>'select',
        'options'=>$cuentaME
    );
    
    if(count($cuentaME) > 1) $input_debitar['empty'] = 'Seleccione';
    
    echo $form->input(
        'cuentaDebitar',
        $input_debitar
    );
    
    if ($form->isFieldError('cuentaDebitar')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaDebitar',
                array(
                    'requerido'=>_m('seleccion.requerida',array('la cuenta a debitar'))
                )           
            ).'</td></tr>';
    }




    echo $form->input(
        'tipoDestinatario',
        array(
            'label'=>' ',
            'type'=>'radio',
            'legend'=>false,
            'separator'=>'&nbsp;&nbsp;',
            'class'=>'seleccion',
            'options'=> array(
                'a' => 'Afiliado',
                'n' => 'No afiliado'
            ),
            'before'=>'<tr><td class="etiqueta" style="text-align:right;"><span class="requerido">* </span>'
        )
    );
    
    echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'
        .'<label for="data[Recarga][tipoDestinatario]" class="error manual">'._m('seleccion.requerida',array('el tipo de beneficiario')).'</label>';
        if ($form->isFieldError('tipoDestinatario')){
            echo $form->error('tipoDestinatario',
                array(
                    'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de beneficiario')),
                    'requerido'=>_m('seleccion.requerida',array('el tipo de beneficiario'))
                )               
            );
        }
    echo '</td></tr>';

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

<script>
    $(function () {

        $('#RecargaRecargaTerceroForm').validate({
            rules: {
                'data[Recarga][cuentaDebitar]': {
                    required: true
                },

                'data[Recarga][tipoDestinatario]': {
                    required: true
                }
            },

            messages: {
                'data[Recarga][cuentaDebitar]': {
                    required:<?php echo _m('seleccion.requerida',array('la cuenta de débito'),true) ?>
                },
                'data[Recarga][tipoDestinatario]': {
                    required:<?php echo _m('seleccion.requerida',array('el tipo de beneficiario'),true) ?>
                },
            },
        });
    });
</script>