<?php
/**
 * @file
 * Archivo: consulta_resultado.ctp
 * Vista de Resultado Final para la Activación/Desactivación del Servicio Contactless.
 *
 * Muestra el éxito o el fallo de la ejecución final del servicio, incluyendo
 * los detalles de la transacción y el estatus final.
 *
 * Variables de Vista Requeridas:
 * - $resultado (array): Contiene todos los detalles de la transacción:
 * - ['accion_solicitada'] (string): "Activación" o "Inactivación".
 * - ['error_flag'] (int): 0 para éxito, 1 para error.
 * - ['tarjeta_enmascarada'] (string): Tarjeta afectada (ej: 'Tarjeta Debito (***1234)').
 * - ['descripcion_texto'] (string): Mensaje detallado del resultado (éxito o causa del error).
 * - ['estatus_texto'] (string): Estatus final mapeado (ej: "Activo", "Inactivo", "Error de Servicio").
 * - ['fecha_accion'] (string): Fecha y hora de la ejecución (solo en éxito).
 *
 * Helpers Utilizados:
 * - $html (HtmlHelper): Para generar enlaces.
 */

?>

<div class="header-section">
    <h2 class="user"><?php echo $resultado['accion_solicitada']; ?> de Servicio Contactless</h2>
    <div class="line_title_orange"></div>
</div>

<div class="pasos-container">
    <div class="paso_nro">1</div>
    <div class="paso_desc">Solicitud de informaci&oacute;n</div>

    <div class="paso_nro">2</div>
    <div class="paso_desc">Confirmaci&oacute;n</div>

    <div class="paso_nro_actual">3</div>
    <div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">

        <?php
        // --------------------------------------------------------------------
        // LÓGICA DE ÉXITO (error_flag == 0)
        // --------------------------------------------------------------------
        if($resultado['error_flag'] == 0):
        ?>
            <thead>
                <tr>
                    <td colspan="2" class="resumen_paginacion">
                        <p class="exito">Operaci&oacute;n realizada satisfactoriamente</p>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="etiqueta">Tarjeta D&eacute;bito:</td>
                    <td><?php echo $resultado['tarjeta_enmascarada']; ?></td>
                </tr>

<!--                 <tr>
                    <td class="etiqueta">N&uacute;mero de cuenta:</td>
                    <td><?php echo $resultado['numero_cuenta']; ?></td>
                </tr> -->

                <tr>
                    <td class="etiqueta">Descripci&oacute;n:</td>
                    <td><?php echo $resultado['descripcion_texto']; ?></td>
                </tr>

                <tr>
                    <td class="etiqueta">Estatus:</td>
                    <td><?php echo $resultado['estatus_texto']; ?></td>
                </tr>

                <tr>
                    <td class="etiqueta">Fecha de Acci&oacute;n:</td>
                    <td><?php echo $resultado['fecha_accion']; ?></td>
                </tr>

                <tr>
                    <td colspan="2" class="botonera">
                        <?php
                        // Botón Imprimir (Usando javascript:print() para la función nativa)
                        echo $html->link(
                            'Imprimir',
                            'javascript:print();void(0);',
                            array(
                                'class' => 'button'
                            )
                        );
                        ?>
                        <?php
                        // Botón Volver a Consultar (Regresa al inicio del flujo)
                        echo $html->link(
                            'Volver a Consultar',
                            array('controller' => 'contact_less', 'action' => 'consulta'),
                            array(
                                'class' => 'button'
                            )
                        );
                        ?>
                    </td>
                </tr>
            </tbody>

        <?php
        // --------------------------------------------------------------------
        // LÓGICA DE ERROR (error_flag != 0)
        // --------------------------------------------------------------------
        else:
        ?>
            <thead>
                <tr>
                    <td colspan="2" class="resumen_paginacion texto_centro">
                        <div class="error-message">Error de Operaci&oacute;n</div>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="etiqueta">Descripci&oacute;n:</td>
                    <td class="error_texto"><?php echo $resultado['descripcion_texto']; ?></td>
                </tr>

                <tr>
                    <td class="etiqueta">Tarjeta D&eacute;bito:</td>
                    <td><?php echo $resultado['tarjeta_enmascarada']; ?></td>
                </tr>

                <tr>
                    <td class="etiqueta">Estatus:</td>
                    <td><?php echo $resultado['estatus_texto']; ?></td>
                </tr>

                <tr>
                    <td colspan="2" class="botonera">
                        <?php
                        echo $html->link(
                            'Volver a Consultar',
                            array('controller' => 'contact_less', 'action' => 'consulta'),
                            array(
                                'class' => 'button'
                            )
                        );
                        ?>
                    </td>
                </tr>
            </tbody>
        <?php
        endif; // Fin del bloque if/else que maneja Éxito o Error
        ?>
    </table>
</div>