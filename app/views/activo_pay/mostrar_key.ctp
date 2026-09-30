<?php

/**
 * Formulario de mesa de cambio comprass
 **/

    // Mapeo de api para mostrar en front
    $apiKeyCod = $res['error'];
    $apiKeyMensaje = $res['errorMensaje'];
    $apikey = $res['apikey'];

?>
<div>
    <h2 class="user">Mostrar llave de Activo Pay</h2>
    <div class="line_title_orange"></div>
</div>


<!-- <div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de informacion</div>

<div class="paso_nro">2</div>
<div class="paso_desc">Confirmacion</div>
<div class="paso_nro">3</div>
<div class="paso">Resultado de operacion</div> -->

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <br><br><br>
    <?php

    echo $form->create(
        'Activacion',
        array(
            'url' => array(
                'controller' => 'mostrar_key',
                'action' => 'api'
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

            <tr>
        <td class="etiqueta"><span class="requerido">* </span>Tipo de documento:</td>
        <td class="campo">

            <?php echo $form->input(
                'tipo_documento',
                array(
                    'before' => false,
                    'between' => false,
                    'label' => false,
                    'type' => 'text',
                    'id' => 'tipo_documento',
                    'class' => 'numerico',
                    'maxlength' => 4,
                    'placeholder' => 'V',
                    'value' => $tipo_documento,
                    'readonly' => true
                )
            );
           
            ?>
        </td>
    </tr>


    <tr>
        <td class="etiqueta"><span class="requerido">* </span>N&uacute;mero de documento:</td>
        <td class="campo">

            <?php echo $form->input(
                'nro_documento',
                array(
                    'before' => false,
                    'between' => false,
                    'label' => false,
                    'type' => 'text',
                    'id' => 'nro_documento',
                    'class' => 'numerico',
                    'maxlength' => 4,
                    'placeholder' => '10000000',
                    'value' => $documento,
                    'readonly' => true
                )
            );
           
            ?>
        </td>
    </tr>

<tr>
        <td class="etiqueta"><span class="requerido">* </span>Llave privada:</td>
        <td class="campo">

            <?php 
            if($apiKeyCod == 1000){
                ?> <p style="font-size: 15px; font-weight: bold; color: red; text-align: center;"> <?php echo $apiKeyMensaje; ?> </p> <?php
            }else if($apiKeyCod == 0000){
            ?>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <?php
                    echo $form->input(
                        'api_key',
                        array(
                            'before' => false,
                            'between' => false,
                            'label' => false,
                            'type' => 'password', // ⬅️ CAMBIADO a 'password' por defecto
                            'id' => 'api_key',
                            'class' => 'numerico',
                            'maxlength' => 4,
                            'placeholder' => 'Llave',
                            'value' => $apikey,
                            'readonly' => true // ⬅️ AGREGADO para bloquear la edición
                        )
                    );
                    ?>
                   
                </div>


<td class="etiqueta"><span class="requerido">* </span>Acci&oacute;n:</td>

         <td class="campo">
<!--              <span id="toggleKey" style="cursor: pointer;" title="Mostrar llave">
                Mostrar clave
            </span> -->

            <button type="button" id="toggleKey" class="button_mostrar button" style="cursor: pointer;">Mostrar llave</button>
                    
            <button type="button" id="copyKey" class="button_copiar button" style="cursor: pointer;">Copiar llave</button>
        </td>
    </tr>
            <?php
            }else{
                ?> <p style="font-size: 15px; font-weight: bold; color: red; text-align: center;"> Estimado usuario, estamos presentando problemas para mostrarle su llave, en breve estaremos mostrando su información. </p> <?php
            }
        ?>
        </td>







    <?php
    echo '<tr><td>&nbsp;</td><td class="submit">';

    // echo $form->submit(
    //     'Consultar',
    //     array(
    //         'class' => 'button'
    //     )
    // );
    echo '</td></tr></table>';
    // echo $form->end();

    ?>


        
</div>

<style>
    /* Estilo básico para el botón de copiar */
/*    .button_copiar,
    .button_mostrar {
        padding: 5px 10px;
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        cursor: pointer;
        border-radius: 4px;
        font-size: 14px;
        transition: background-color 0.3s;
    }
    .button_copiar:hover,
    .button_mostrar:hover {
        background-color: #e0e0e0;
    }*/
</style>

<script>
    // Asegúrate de que jQuery esté cargado antes de este script
    $(document).ready(function() {
        
        // Solo ejecuta la lógica si el campo api_key existe
        if ($('#api_key').length) {
            
            /**
             * 1. Funcionalidad Mostrar/Ocultar (Icono 👁️/🙈)
             * (Corregido el error de 'type property can't be changed')
             */
            $('#toggleKey').on('click', function() {
                var keyInput = $('#api_key');
                var currentType = keyInput.attr('type');
                var newType = (currentType === 'password') ? 'text' : 'password';
                var $container = keyInput.parent(); // El <div> padre

                // 1. Clonar el input existente
                var newKeyInput = keyInput.clone();
                
                // 2. Modificar el tipo y restaurar el valor
                newKeyInput.attr('type', newType);
                
                // 3. Reemplazar el input antiguo por el nuevo
                keyInput.replaceWith(newKeyInput);
                
                // 4. Actualizar el icono del ojo y el título
                var $toggleKey = $(this);
                if (newType === 'text') {
                    $toggleKey.attr('title', 'Ocultar llave').text('Ocultar llave');
                } else {
                    $toggleKey.attr('title', 'Mostrar llave').text('Mostrar llave');
                }
            });

            /**
             * 2. Funcionalidad Copiar al hacer click (Botón "Copiar")
             * (El resto del código de copiar se mantiene igual, pero lo ajusto para 
             * seleccionar el input que esté actualmente en el DOM)
             */
            $('#copyKey').on('click', function() {
                // Selecciona el input actual después de los posibles reemplazos
                var keyInput = $('#api_key')[0]; 
                var originalText = $(this).text();
                var $button = $(this);
                
                // Opción 1: Usa la API moderna (asíncrona) de portapapeles
                if (navigator.clipboard && window.isSecureContext) {
                    var valueToCopy = keyInput.value;
                    
                    navigator.clipboard.writeText(valueToCopy).then(function() {
                        $button.text('Copiado');
                        setTimeout(function() {
                            $button.text(originalText);
                        }, 2000);
                    }).catch(function(err) {
                        console.error('Error al copiar (API Clipboard): ', err);
                        fallbackCopy(keyInput, originalText, $button);
                    });
                } else {
                    // Opción 2: Método de fallback 
                    fallbackCopy(keyInput, originalText, $button);
                }
            });

            // Función de fallback para copiar (utiliza document.execCommand)
            function fallbackCopy(inputElement, originalText, $button) {
                // Nota: Esta parte ya realiza el cambio de 'type' temporalmente para la copia
                // lo que podría generar el mismo error si el campo es de tipo password.
                // Sin embargo, si el campo ya fue reemplazado por tipo="text" 
                // con la lógica del toggle, funcionará. Mantenemos el fallback
                // si falla la API de Clipboard.
                
                var originalType = $(inputElement).attr('type');
                var needsRestore = false;

                // Solo si el campo es de tipo password, necesitamos cambiarlo para seleccionar.
                if (originalType === 'password') {
                     // ⚠️ Esto puede generar el error nuevamente en navegadores muy viejos.
                     // Pero en el contexto del fallback (donde Clipboard API falló), 
                     // es la única opción de copia nativa en jQuery 1.x.
                     try {
                        $(inputElement).attr('type', 'text');
                        needsRestore = true;
                     } catch(e) {
                        // Si el cambio de tipo falla aquí, simplemente seleccionamos el valor
                        // y avisamos al usuario.
                        console.error("No se pudo cambiar el tipo para el fallback de copia.");
                     }
                }

                inputElement.select();
                inputElement.setSelectionRange(0, 99999); 
                
                try {
                    document.execCommand('copy');
                    $button.text('Copiado');
                    setTimeout(function() {
                        $button.text(originalText);
                    }, 2000);
                } catch (err) {
                    alert('Error al intentar copiar. Por favor, copie manualmente.');
                }
                
                // Restaura el tipo de input original y el ícono
                if (needsRestore) {
                    // Revertimos el cambio de tipo
                    try {
                        $(inputElement).attr('type', originalType);
                    } catch(e) {
                        // Si revertir falla, forzamos el reemplazo para mantener la coherencia
                        var newKeyInput = $(inputElement).clone().attr('type', originalType);
                        $(inputElement).replaceWith(newKeyInput);
                    }
                }
            }
        }
    });
</script>
