<?php
/**
 * Formulario de mesa de cambio contact less (MODIFICADO con AJAX para Cuentas y Estatus)
 **/

// Se asume que $tarjetas_dropdown, $tarjetasConEstatus, $statueResponse, $cuentas_dropdown
// y la cuenta seleccionada por defecto están disponibles.

$hasTarjetas = !empty($tarjetas_dropdown);

// EXTRAER LA CLAVE RAW DE LA CUENTA POR DEFECTO:
// Si hay cuentas, el primer key es nuestra "cuenta principal" por defecto.
$defaultRawAccountKey = isset($cuentas_dropdown) && count($cuentas_dropdown) > 0 ? key($cuentas_dropdown) : null;
// Extraer el número de la primera tarjeta para la inicialización (no necesario si usamos val() de NumTarjeta)
$initialCardNumber = $hasTarjetas ? key($tarjetas_dropdown) : null;

// Asegurarse de que el helper de HTML esté cargado
if (!isset($html)) {
    // Para el propósito de esta modificación, asumimos que existe $html.
}
?>
<div>
    <h2 class="user">Activar / Desactivar Servicio Contactless</h2>
    <div class="line_title_orange"></div>
</div>


<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de informaci&oacute;n</div>

<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <br><br><br>
    <?php

    // 1. CREACIÓN DEL FORMULARIO
    echo $form->create(
        'GetTarjetaSelect',
        array(
            'url' => array(
                'controller' => 'contact_less',
                'action' => 'cambiarEstatus' // <-- ACCIÓN PARA MODIFICAR/INACTIVAR
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
                'label' => '<span class="requerido">* </span>N&uacute;mero Tarjeta de D&eacute;bito:',
                'type' => 'select',
                'options' => $tarjetas_dropdown,
                'empty' => $hasTarjetas ? false : 'No se encontraron tarjetas',
                'id' => 'NumTarjeta', // ID esencial para JavaScript
                'class' => 'campo',
                'disabled' => !$hasTarjetas
            )
        );
    ?>

    <?php
        echo $form->input(
            'numCuenta',
            array(
                'label' => '<span class="requerido">* </span>N&uacute;mero de cuenta:',
                'type' => 'select',
                // Las opciones iniciales son las de la primera tarjeta
                'options' => isset($cuentas_dropdown) ? $cuentas_dropdown : array(),
                // MODIFICADO: Quitamos el 'empty' si hay cuentas para que muestre la cuenta principal.
                'empty' => $hasTarjetas ? false : 'Primero seleccione una tarjeta', 
                'id' => 'NumCuenta', // ID esencial para JavaScript
                'class' => 'campo',
                // Establecer el valor seleccionado por defecto (la cuenta RAW)
                'value' => $defaultRawAccountKey,
                'disabled' => !$hasTarjetas
            )
        );
    ?>
    
    <tr>
        <td class="etiqueta" style="vertical-align: middle;"><span class="requerido">* </span>Estatus del Servicio:</td>
        <td class="campo" style="vertical-align: middle;">

            <?php if (!$hasTarjetas): ?>
                <p style="color: red;">No se encontraron tarjetas asociadas.</p>
            <?php endif; ?>

            <?php echo $form->input(
                'statusServicio',
                array(
                    'before' => false,
                    'between' => false,
                    'label' => false,
                    'type' => 'text',
                    'id' => 'statusServicio',
                    'class' => 'campo',
                    // El estatus inicial (de la primera tarjeta) se muestra aquí
                    'value' => $statueResponse,
                    'readonly' => true,
                    'disabled' => !$hasTarjetas
                )
            );
            ?>
        </td>
    </tr>
    
    <!-- NUEVO: Contenedor para el mensaje de advertencia -->
    <tr>
        <td colspan="2" style="text-align: center;">
            <div id="warning-message" style="
                display:none; 
                margin-top: 15px;
                font-weight: bold;
                text-align: left;
                color: #ff0000;
            ">
                Estimado cliente, se le informa que su servicio contactless est&aacute; inactivo. Si desea cambiar la cuenta principal, por favor active de nuevo el servicio.
            </div>
        </td>
    </tr>
    
    <!-- INDICADOR DE CARGA AJAX -->
    <tr>
        <td class="etiqueta" style="vertical-align: middle;"></td>
        <td class="campo" style="vertical-align: middle;">
            <div id="loading-cuentas" style="display:none; color: #007bff; font-weight: bold;">
                Cargando cuentas...
            </div>
        </td>
    </tr>

    <?php
    echo '<tr><td>&nbsp;</td><td class="submit">';

    // BOTÓN DE ACTIVAR
    echo $form->submit(
        'Activar Servicio', 
        array(
            'name' => 'data[GetTarjetaSelect][activar]', // <--- USAR 'activar' para Acción 'A'
            'class' => 'button accion-boton',
            'id' => 'btnActivar', // Renombrado para claridad
            'disabled' => !$hasTarjetas,
            'style' => 'display:none;' // Inicialmente oculto por JS
        )
    );
    
    // BOTÓN DE INACTIVAR
    echo $form->submit(
        'Inactivar Servicio',
        array(
            'name' => 'data[GetTarjetaSelect][inactivar]', // <-- Identificador para ACCIÓN 'I'
            'class' => 'button accion-boton',
            'id' => 'btnInactivar', // ID para JavaScript
            'disabled' => !$hasTarjetas,
            'style' => 'display:none;' // Inicialmente oculto por JS
        )
    );

    // Cierre de submit y apertura de otro para el botón Modificar (Botón Modificar en su propia fila)


    // NUEVO BOTÓN: Modificar Cuentas (si el estatus es ACTIVO y la cuenta no es la principal)
    echo $form->submit(
        'Modificar Cuenta Principal',
        array(
            'name' => 'data[GetTarjetaSelect][modificar]', // <-- Identificador para ACCIÓN 'M'
            'class' => 'button accion-boton',
            'id' => 'btnModificar', // Nuevo ID para JavaScript
            'disabled' => !$hasTarjetas,
            'style' => 'display:none;' // Inicialmente oculto por JS
        )
    );
    echo '</td></tr>'; 
    ?>

   
    <?php
    


    echo '</td></tr></table>'; // Cierre de la tabla y la última fila de submit
    echo $form->end();

    ?>
</div>


<script>
    // CRUCIAL: Mapeamos la data de PHP a JavaScript.
    // 1. cardStatuses: Mapea Tarjeta -> Estatus descriptivo.
    var cardStatuses = <?php echo json_encode($tarjetasConEstatus); ?>;
    var hasTarjetas = <?php echo $hasTarjetas ? 'true' : 'false'; ?>;
    
    // Obtener la clave de la cuenta principal por defecto desde PHP
    var defaultRawAccountKey = '<?php echo $defaultRawAccountKey; ?>'; 
    
    // URL para la llamada AJAX que obtendrá las cuentas
    // NOTA: Se asume que esta URL existe y devuelve las cuentas asociadas a la tarjeta
    var getAccountsUrl = '<?php echo $html->url(array('controller' => 'contact_less', 'action' => 'obtenerCuentasPorTarjeta')); ?>';

    // Función para actualizar el campo de estatus y los botones de acción (Activar/Inactivar/Modificar)
    function updateStatusAndButtons(selectedCardNumber) {
        if (!hasTarjetas) return;

        // --- OBTENER VALORES ACTUALES ---
        var statusText = cardStatuses[selectedCardNumber] || 'Desconocido';
        var $numCuenta = $('#NumCuenta');
        var selectedAccountRaw = $numCuenta.val(); // La cuenta seleccionada actualmente (RAW)
        var $warningMessage = $('#warning-message');
        
        // La clave de la cuenta que se cargó como principal (la primera)
        // Si el select tiene opciones, obtenemos la primera opción. Si no, usamos la de PHP.
        var firstAccountInSelect = $numCuenta.find('option:first').val();
        // Usamos la primera opción visible como referencia para la cuenta principal, 
        // ya que el valor inicial de PHP solo es válido en la carga de la página.
        var mainAccountKey = firstAccountInSelect || defaultRawAccountKey;


        // --- ACTUALIZAR EL ESTATUS DEL SERVICIO ---
        $('#statusServicio').val(statusText);
        
        // --- GESTIÓN DE BOTONES ---
        var $btnActivar = $('#btnActivar');
        var $btnInactivar = $('#btnInactivar');
        var $btnModificar = $('#btnModificar');
        
        // Ocultar todos los botones y el mensaje de advertencia por defecto
        $btnActivar.hide();
        $btnInactivar.hide();
        $btnModificar.hide();
        $warningMessage.hide(); // Ocultar mensaje de advertencia

        
        // Verificar si hay una cuenta seleccionada y si esta es secundaria
        var isAccountSelected = selectedAccountRaw && selectedAccountRaw.length > 0;
        var isSecondaryAccountSelected = isAccountSelected && (selectedAccountRaw !== mainAccountKey);
        
        // Verificar si el servicio está Inactivo
        var isServiceInactive = statusText === 'Inactivo';


        if (isAccountSelected) {
            
            if (isServiceInactive) {
                // Si el servicio está Inactivo:
                
                // Mostrar siempre el botón Activar (regla anterior)
                $btnActivar.show();
                
                if (isSecondaryAccountSelected) {
                    // NUEVA REGLA: Si Inactivo Y Secundaria, mostrar mensaje de advertencia
                    $warningMessage.show(); 
                }
                
            } else if (isSecondaryAccountSelected) {
                // REGLA 2: Si es cuenta secundaria Y servicio Activo, solo Modificar.
                $btnModificar.show(); 
            } else {
                // REGLA 1: Si es cuenta principal Y servicio Activo, solo Inactivar.
                // (Ya sabemos que no es Inactivo, por lo tanto debe ser Activo)
                if (statusText === 'Activo') {
                    $btnInactivar.show();
                } else {
                    // Si el estatus es Desconocido o cualquier otra cosa, no se muestra nada.
                }
            }
            
        }

        // Deshabilitar/Habilitar todos los botones de acción si no hay cuenta seleccionada.
        if (!isAccountSelected) {
            $('.accion-boton').attr('disabled', 'disabled');
        } else {
            // Si hay cuenta seleccionada, quitamos el disabled. La lógica show/hide ya maneja cuál se ve.
            $('.accion-boton').removeAttr('disabled');
        }
    }

    /**
     * Realiza la llamada AJAX para obtener las cuentas de la tarjeta seleccionada.
     * @param {string} selectedCardNumber - El número de tarjeta RAW seleccionado.
     */
    function fetchAccountsForCard(selectedCardNumber) {
        if (!selectedCardNumber) return;

        var $numCuenta = $('#NumCuenta');
        var $loading = $('#loading-cuentas');
        var $formButtons = $('.accion-boton');
        var $warningMessage = $('#warning-message');

        // Deshabilitar/Limpiar antes de cargar
        $numCuenta.empty().attr('disabled', 'disabled');
        $formButtons.attr('disabled', 'disabled'); // Deshabilita botones de acción
        $loading.show();
        $warningMessage.hide(); // Ocultar mensaje al cambiar de tarjeta

        $.ajax({
            url: getAccountsUrl,
            type: 'POST',
            // En CakePHP 1.x o similar, la data podría requerir un prefijo
            data: { 
                'data[GetTarjetaSelect][numTarjeta]': selectedCardNumber 
            },
            dataType: 'json',
            success: function(response) {
                $loading.hide();
                $numCuenta.empty();
                
                var accountsOptions = response;
                var count = 0;

                if (accountsOptions && Object.keys(accountsOptions).length > 0) {
                    // Rellenar el select con las cuentas
                    $.each(accountsOptions, function(value, text) {
                        $numCuenta.append($('<option>', {
                            value: value,
                            text : text
                        }));
                        count++;
                    });
                    
                    $numCuenta.removeAttr('disabled');

                    // Seleccionar la primera cuenta por defecto (será la cuenta principal)
                    if (count > 0) {
                        // Al cargar por AJAX, la primera cuenta es la principal para esta tarjeta
                        var firstAccountKey = Object.keys(accountsOptions)[0];
                        $numCuenta.val(firstAccountKey); 
                        // Actualizamos la variable global de referencia para el caso inicial si es necesario
                        defaultRawAccountKey = firstAccountKey;
                    }

                } else {
                    // Si no hay cuentas
                    $numCuenta.append($('<option>', {
                        value: '',
                        text : 'No hay cuentas disponibles'
                    }));
                }
                
                // Después de cargar las cuentas, actualizamos los botones y estatus
                updateStatusAndButtons(selectedCardNumber);
                // Si la carga fue exitosa (con o sin cuentas), habilitamos la validación
                // Los botones se habilitan dentro de updateStatusAndButtons si hay cuenta seleccionada.
            },
            error: function() {
                $loading.hide();
                $numCuenta.empty().append($('<option>', {
                    value: '',
                    text : 'Error al cargar las cuentas'
                }));
                $formButtons.attr('disabled', 'disabled'); // Mantiene botones deshabilitados en caso de error
                console.error("Error al cargar las cuentas.");
            }
        });
    }


    $(function(){
        // Solo si hay tarjetas, adjuntamos el listener
        if (Object.keys(cardStatuses).length > 0) {
            
            var initialCard = $('#NumTarjeta').val(); // Tarjeta seleccionada al cargar la página

            // 1. Inicializar la interfaz al cargar la página
            // En este punto, las cuentas ya fueron cargadas por PHP, solo actualizamos botones.
            updateStatusAndButtons(initialCard);

            // 2. Agregar el listener 'change' al dropdown de tarjetas
            $('#NumTarjeta').change(function() {
                var selectedCard = $(this).val();
                
                // 3. Realiza la llamada AJAX para cargar las nuevas cuentas y actualizar la interfaz.
                fetchAccountsForCard(selectedCard);
            });
            
            // 4. Listener para el cambio de cuenta (actualiza la visibilidad de los botones y el mensaje)
            $('#NumCuenta').change(function() {
                var currentCard = $('#NumTarjeta').val();
                updateStatusAndButtons(currentCard);
            });
        }
        
        // El resto de la validación se mantiene igual
        $('#GetTarjetaSelectForm').validate({
            rules:{
                'data[GetTarjetaSelect][numTarjeta]':{  
                    required:true,
                },      
                'data[GetTarjetaSelect][numCuenta]':{ // VALIDACIÓN PARA EL CAMPO CUENTA
                    required:true,
                },
                'data[GetTarjetaSelect][statusServicio]':{
                    required:true,
                    minlength:4,    
                    maxlength:100,
                },
            },
            messages:{
                'data[GetTarjetaSelect][numTarjeta]':{
                    required:<?php echo _m('campo.requerido',array('la Tarjeta Debito'), true) ?>,
                },      
                'data[GetTarjetaSelect][numCuenta]':{
                    required:<?php echo _m('campo.requerido',array('el Numero de Cuenta'), true) ?>,
                },
                'data[GetTarjetaSelect][statusServicio]':{
                    required:<?php echo _m('campo.requerido',array('el estatus de servicio'), true) ?>,
                },
            }
        });
    });
</script>