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
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmacion</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado de operacion</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <br><br><br>
    <?php

    echo $form->create(
        'Activacion',
        array(
            'url' => array(
                'controller' => 'tarjeta_credito_credicard',
                'action' => 'tdc_activacion'
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

    <!-- <?php
    echo $form->input(
        'tdc_cedula',
        array(
            'label' => '<span class="requerido">* </span>Cedula de identidad:',
            'type' => 'text',
            'class' => 'monto pago hidde',
            'value' => '00'. desencriptar($usuario['cedula']),
            'readonly' => 'readonly',
            'id' => 'tdc_cedula',
        )
    );
    ?> -->


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
                    'placeholder' => 'MM/AA',
                    'required' => true
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
                    'required' => true
                )
            );
            ?>
        </td>
    </tr>

    <tr>
        <td class="etiqueta"><span class="requerido">* </span>N&uacutemero de tarjeta de cr&eacutedito:</td>
        <td class="campo">
            <?php echo $form->input(
                'numTarjeta',
                array(
                    'before' => false,
                    'between' => false,
                    'label' => false,
                    'type' => 'text',
                    'id' => 'numTarjeta',
                    'class' => 'numerico',
                    'maxlength' => 20,
                    'placeholder' => 'Número de su tarjeta',
                    'required' => true
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