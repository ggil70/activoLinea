<?php
/**
 * @file
 * Archivo: consulta_confirmacion.ctp
 * Vista de Confirmación para la Activación/Desactivación del Servicio Contactless.
 *
 * Muestra el resumen de la acción solicitada y requiere la confirmación del usuario
 * antes de proceder a la validación OTP y la ejecución final.
 *
 * Variables de Vista Requeridas:
 * - $mensaje_accion: Texto descriptivo de la acción ('Activación' o 'Inactivación').
 * - $tarjeta_enmascarada: Ahora contiene el Número de Cuenta enmascarado (ej: 'Cuenta (***9876)').
 *
 * Helpers Utilizados:
 * - $html (HtmlHelper): Para generar enlaces.
 */


?>

<div class="header-section">
    <h2 class="user"><?php echo $mensaje_accion; ?> de servicio Contactless</h2>
    <div class="line_title_orange"></div>
</div>

<div class="pasos-container">
    <div class="paso_nro">1</div>
    <div class="paso_desc">Solicitud de informaci&oacute;n</div>

    <div class="paso_nro_actual">2</div>
    <div class="paso_desc_actual">Confirmaci&oacute;n</div>

    <div class="paso_nro">3</div>
    <div class="paso_desc">Resultado de Operaci&oacute;n</div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

    <div id="error_contrato" style="display: none">
        <label class="error"><?php echo "Estimado Cliente, debe confirmar la operación."; ?></label>
    </div>

    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
        <thead>
            <tr>
                <td colspan="2" class="resumen_paginacion">
                    <!-- Se ajusta el mensaje para hacer referencia explícita al servicio y la cuenta. -->
                    Confirme la <?php echo $mensaje_accion; ?> del servicio Contactless para la siguiente cuenta:
                </td>
            </tr>
        </thead>
        <tbody>
            <!-- Fila agregada para mostrar la acción a realizar -->
            <tr>
                <td class="etiqueta">Acci&oacute;n a realizar:</td>
                <td><?php echo $mensaje_accion; ?></td>
            </tr>

            <!-- Fila modificada para mostrar el Número de Cuenta enmascarado en lugar de la tarjeta -->
            <tr>
                <td class="etiqueta">N&uacute;mero de Tarjeta:</td>
                <td><?php echo $tarjeta_enmascarada; ?></td>
            </tr>

<!--             <tr>
                <td class="etiqueta">N&uacute;mero de Cuenta:</td>
                <td><?php echo 'N&uacute;mero de Cuenta: (' . $cuenta_enmascarada . ')'; ?></td>
            </tr> -->

            <tr>
                <td colspan="2" class="botonera">
                    <?php

                    // 1. Botón "Volver" (Enlace a la acción de consulta)
                    // Usa $html->link para generar la URL correcta.
                    echo $html->link(
                        'Volver',
                        array('controller' => 'contact_less', 'action' => 'consulta'),
                        array(
                           'class' => 'button bloqueo_repetir'
                        )
                    );

                    echo '&nbsp;&nbsp;';

                    // 2. Botón "Aceptar" (Enlace a la acción de ejecución/OTP)
                    // Este enlace dispara la acción que inicia la validación OTP
                    // y luego llama a ejecutarAccion.
                    echo $html->link(
                        'Aceptar',
                        array('controller' => 'contact_less', 'action' => 'ejecutarAccion'), // Apunta a ejecutarAccion
                        array(
                           'class' => 'button bloqueo_repetir',
                           'id' => 'aceptar_btn'
                        )
                    );
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>