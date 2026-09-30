<?php

/**
 * Formulario de mesa de cambio comprass
 **/
?>
<div>
    <h2 class="user">Activar tarjeta de cr&eacutedito</h2>
    <div class="line_title_orange"></div>
</div>


<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de informacion</div>

<div class="paso_nro">2</div>
<div class="paso_desc">Confirmacion</div>
<div class="paso_nro">3</div>
<div class="paso">Resultado de operacion</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <br><br><br>
    <?php

    echo $form->create(
        'Activacion',
        array(
            'url' => array(
                'controller' => 'tarjeta_credito_credicard',
                'action' => 'activacion'
            ),
            'inputDefaults' => array(
                'div' => false,
                'label' => 'etiqueta',
                'before' => '<tr><td class="etiqueta">',
                'between' => '</td><td class="campo">',
                'after' => '</td></tr>',
                'error' => false
            )
        )
    );
    echo $html->tag(
        'table',
        null,
        array(
            'class' => 'formulario',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'border' => '0',
            'align' => 'center'
        )
    );
    ?>


    <?php
        echo $form->input(
            'numTarjeta',
            array(
                'label' => 'N&uacutemero Tarjeta cr&eacute;dito: ',
                'type' => 'select',
                'options' => $soapTarjeta,
                'empty' => false,
            )
        );
     ?>   




    <tr>
        <td class="etiqueta"><span class="requerido">* </span>Fecha Vencimiento:</td>
        <td class="campo">

            <?php echo $form->input(
                'fechaVencimiento',
                array(
                    'before' => false,
                    'between' => false,
                    'label' => false,
                    'type' => 'text',
                    'id' => 'fechaVencimiento',
                    'class' => 'numerico',
                    'maxlength' => 4,
                    'placeholder' => 'MMAA',
                )
            );
           
            ?>
        </td>
    </tr>

    <tr>
        <td class="etiqueta"><span class="requerido">* </span>C&oacutedigo de tarjeta:</td>
        <td class="campo">
            <?php echo $form->input(
                'codSeguridad',
                array(
                    'before' => false,
                    'between' => false,
                    'label' => false,
                    'type' => 'text',
                    'id' => 'codSeguridad',
                    'class' => 'numerico',
                    'maxlength' => 3,
                    'placeholder' => 'CVV',
                )
            );
            ?>
        </td>
    </tr>


    <?php
    echo '<tr><td>&nbsp;</td><td class="submit">';

    echo $form->submit(
        'Activar',
        array(
            'class' => 'button'
        )
    );
    echo '</td></tr></table>';
    echo $form->end();

    ?>
</div>


<script>
    
    $(function(){
         
    $('#ActivacionActivacionForm').validate({
            rules:{
                'data[Activacion][numTarjeta]':{
                    required:true,
                },                
                'data[Activacion][fechaVencimiento]':{
                    required:true,
                    minlength:4, 
                    maxlength:4,                    

                },
                'data[Activacion][codSeguridad]':{
                    required:true,
                    minlength:3, 
                    maxlength:3,                    

                },


            },
            messages:{
                'data[Activacion][numTarjeta]':{
                    required:<?php echo _m('campo.requerido',array('la Tarjeta Credito'), true) ?>,
                },                
                'data[Activacion][fechaVencimiento]':{
                    required:<?php echo _m('campo.requerido',array('la Fecha de Vencimiento'), true) ?>,
                },

                'data[Activacion][codSeguridad]':{
                    required:<?php echo _m('campo.requerido',array('el codigo de Seguridad '), true) ?>
                },

            }
    });
});
</script>