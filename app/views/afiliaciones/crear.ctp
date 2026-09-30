<?php
/**
 * Vista para crear/modificar afiliaciones
 */


$modificar = 0;
if ($this->Session->read('afiliaciones.proceso') == 'm') {
    $modificar = 1;
}



    /** PARA EL MONTO LIMITE EN LA TRANSACCION **/

    // NUEVOS PARAMETROS DIGITEL Y MOVISTAR y movilnet

    // Declaración de arrays asociativos
    $montos_digitel = array();
    for ($i = 1; $i <= 9; $i++) {
        $monto = _p("monto_digitel_$i");

        // Validación y cast a float (o int si son enteros puros)
        if (empty($monto) && $monto !== '0') {
            $monto = 0; // Valor por defecto si está vacío o nulo
        }

        // Aplicar formato a dos decimales
        $monto_formateado = number_format($monto, 2, ',', ''); // Ej: 10 -> "10,00"

        // **IMPORTANTE: Usar el monto formateado como clave y valor**
        $montos_digitel[$monto_formateado] = $monto_formateado;
    }

    // Declaración de arrays asociativos
    $montos_movistar = array();
    for ($i = 9; $i <= 16; $i++) {
        $monto = _p("monto_movistar_$i");

        // Validación y cast a float
        if (empty($monto) && $monto !== '0') {
            $monto = 0;
        }
        // Aplicar formato a dos decimales
        $monto_formateado = number_format($monto, 2, ',', '');

        // **IMPORTANTE: Usar el monto formateado como clave y valor**
        $montos_movistar[$monto_formateado] = $monto_formateado;
    }

    // Declaración de arrays asociativos
    $montos_movilnet = array();
    for ($i = 9; $i <= 16; $i++) {
        $monto = _p("monto_movilnet_$i");

        // Validación y cast a float
        if (empty($monto) && $monto !== '0') {
            $monto = 0;
        }
        // Aplicar formato a dos decimales
        $monto_formateado = number_format($monto, 2, ',', '');

        // **IMPORTANTE: Usar el monto formateado como clave y valor**
        $montos_movilnet[$monto_formateado] = $monto_formateado;
    }

    $minimo_digitel_php = _p('recarga_minima_digitel');
    $maximo_digitel_php = _p('recarga_maxima_digitel');

    $minimo_movistar_php = _p('recarga_minima_movistar');
    $maximo_movistar_php = _p('recarga_maxima_movistar');

    $minimo_movilnet_php = _p('recarga_minima_movilnet');
    $maximo_movilnet_php = _p('recarga_maxima_movilnet');


    // return array(
    //     'montos_digitel' => $montos_digitel,
    //     'montos_movistar' => $montos_movistar,
    //     'minimo_digitel_php' => $minimo_digitel_php,
    //     'maximo_digitel_php' => $maximo_digitel_php,
    //     'minimo_movistar_php' => $minimo_movistar_php,
    //     'maximo_movistar_php' => $maximo_movistar_php,
    // );


?>

<div>
    <h2 class="user">Afiliaciones</h2>
    <div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

    <?php



    echo $form->create(
        'Afiliacion',
        array(
            'url' => '/afiliaciones/crear',
            'id' => 'AfiliacionCrearForm',
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

    echo $form->hidden('id');
    echo $form->hidden('monto_limite', array('id' => 'monto_limite'));
    echo $form->hidden('recarga_minima', array('id' => 'recarga_minima'));
    echo $form->hidden('recarga_maxima', array('id' => 'recarga_maxima'));
    // echo $form->hidden('multiplo', array('id' => 'multiplo', 'value' => _p('multiplo.digitel'))); // INHABILITADO: Campo oculto para el múltiplo
    echo $html->tag(
        'table',
        null,
        array(
            'class' => 'formulario',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'border' => '0',
            'align' => 'center',
            'style' => 'table-layout:fixed;'
        )
    );

    // La primera fila define la tabla con layout fixed
    echo '<tr><td style="width:300px"></td><td style="width:250px"></td></tr>';

    ?>
    <tr>
        <td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la afiliaci&oacute;n.</td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="tabla ancholibre" cellpadding="0" cellspacing="0" border="0" width="100%">
                <thead>
                    <tr>
                        <td width="50%"><span class="requerido">* </span>Tipo de Afiliaci&oacute;n</td>
                        <td><span class="requerido">* </span>Tipo de Operaci&oacute;n</td>
                    </tr>
                </thead>
                <tr>
                    <td>
                        <?php
                        $opciones = array(
                            'type' => 'select',
                            'options' => $tipos,
                            'empty' => 'Seleccione',
                            'id' => 'tipo',
                            'label' => false,
                            'div' => false,
                            'before' => false,
                            'between' => false,
                            'after' => false,
                            'style' => 'width:100%'
                        );

                        if (!empty($this->data['Afiliacion']['id'])) {
                            $opciones['disabled'] = 'disabled';
                            $opciones['value'] = $this->data['Afiliacion']['tipo_afiliacion'];
                            echo $form->hidden('tipo_afiliacion');
                        }

                        echo $form->input(
                            'tipo_afiliacion',
                            $opciones
                        );
                        ?>
                    </td>
                    <td id="td_transaccion_wrapper" class="borde">
                        <?php
                        if (empty($subtipos)) {
                            $subtipos = "";
                        }
                        $opciones = array(
                            'type' => 'select',
                            'options' => $subtipos,
                            'empty' => 'Seleccione',
                            'id' => 'subtipo',
                            'label' => false,
                            'div' => false,
                            'before' => false,
                            'between' => false,
                            'after' => false,
                            'style' => 'width:100%'
                        );

                        if (!empty($this->data['Afiliacion']['id'])) {
                            $opciones['disabled'] = 'disabled';
                            $opciones['value'] = trim($this->data['Afiliacion']['tipo_operacion']);
                            echo $form->hidden('tipo_operacion');
                        }

                        echo $form->input(
                            'tipo_operacion',
                            $opciones
                        );
                        ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <?php
    echo '<tr><td colspan="2">'
        . '<label for="tipo" class="error manual">' . _m('seleccion.requerida', 'el tipo de afiliaci&oacute;n') . '</label></br>'
        . '<label for="subtipo" class="error manual">' . _m('seleccion.requerida', 'el tipo de operaci&oacute;n') . '</label>';
    if ($form->isFieldError('tipo')) {
        echo $form->error('tipo', _m('seleccion.requerida', 'el tipo de afiliaci&oacute;n'));
    }
    if ($form->isFieldError('subtipo')) {
        echo $form->error('subtipo', _m('seleccion.requerida', 'el tipo de operaci&oacute;n'));
    }
    echo '</td></tr>';

    echo $form->input(
        'alias',
        array(
            'label' => '<span class="requerido">* </span>Alias:',
            'type' => 'text',
            'maxlength' => '30',
            'class' => 'alfanumericoEspacio requerido'
        )
    );

    if ($form->isFieldError('alias')) {
        echo '<tr><td>&nbsp;</td><td>' . $form->error(
            'alias',
            array(
                'alfanumerico' => _m('alfanumerico.invalido', 'el alias'),
                'alias_nodisponible' => _m('alias.yaregistrado'),
                'requerido' => _m('campo.requerido', 'el alias de la afiliaci&oacute;n')
            )
        ) . '</td></tr>';
    }
    ?>
    <tr id="tipoServicio">
        <td class="etiqueta"><span class="requerido">* </span>Tipo de Servicio:</td>
        <td class="campo">
            <?php

            if (empty($tiposServicios)) {
                $tiposServicios = "";
            }

            $opciones = array(
                'type' => 'select',
                'options' => $tiposServicios,
                'empty' => 'Seleccione',
                'id' => 'servicios',
                'label' => false,
                'div' => false,
                'before' => false,
                'between' => false,
                'after' => false,
                'style' => 'width:100%'
            );

            echo $form->input(
                'tipo_servicio',
                $opciones
            );
            ?>
        </td>
    </tr>
    <?php
    echo '<tr><td>'
        . '<label for="servicios" class="error manual">' . _m('seleccion.requerida', 'el tipo de servicio') . '</label>';
    if ($form->isFieldError('servicios')) {
        echo $form->error('servicios', _m('seleccion.requerida', 'el tipo de servicio'));
    }
    echo '</td></tr>';
    ?>
    <tr id="simfDT">
        <td class="etiqueta"><span class="requerido">* </span>Destino:</td>
        <td class="campo">
            <?php

            $opciones = array(
                'id' => 'destino',
                'label' => false,
                'div' => false,
                'before' => false,
                'between' => false,
                'after' => false,
                'type' => 'select',
                'empty' => 'Seleccione',
                'options' => array(
                    'c' => 'Cuenta',
                    't' => 'Teléfono'
                )
            );

            if (!empty($this->data['Afiliacion']['id'])) {
                $opciones['disabled'] = 'disabled';
                if (trim($this->data['Afiliacion']['telefono']) != '') {
                    $opciones['value'] = 't';
                    $valor = 't';
                } else {
                    $opciones['value'] = 'c';
                    $valor = 'c';
                }

                echo $form->input('destino', array('type' => 'hidden', 'value' => $valor));
            }

            echo $form->input(
                'destino',
                $opciones
            );
            ?>
        </td>
    </tr>
    <?php
    echo '<tr><td>'
        . '<label for="destino" class="error manual">' . _m('seleccion.requerida', 'el destino') . '</label>';
    if ($form->isFieldError('servicios')) {
        echo $form->error('servicios', _m('seleccion.requerida', 'el destino'));
    }
    echo '</td></tr>';
    ?>
    <tr>
        <td class="etiqueta labelBeneficiario"><span class="requerido">* </span>Cuenta del Beneficiario:</td>
        <td class="campo">

            <?php if ($this->data['Afiliacion']['tipo_operacion'] == 't') {
                $opciones = array(
                    'before' => false,
                    'between' => false,
                    'after' => false,
                    'label' => false,
                    'class' => 'codigoTelefono',
                    'type' => 'select',
                    'options' => $prefijos,
                    'id' => 'codigo_telefono',
                    'style' => 'display:none'
                );

                if (!empty($this->data['Afiliacion']['codigo_telefono'])) {

                    $opciones['value'] = $this->data['Afiliacion']['codigo_telefono'];
                    $valor = $this->data['Afiliacion']['codigo_telefono'];
                    echo $form->input('codigo_telefono', array('type' => 'hidden', 'value' => $valor));
                }

                echo $form->input(
                    'codigo_telefono',
                    $opciones
                );
            } else {

                echo $form->input(
                    'codigo_telefono',
                    array(
                        'before' => false,
                        'between' => false,
                        'after' => false,
                        'label' => false,
                        'class' => 'codigoTelefono',
                        'type' => 'select',
                        'style' => 'display:none'
                    )
                );


            }

            echo $form->input(
                'numero_cuenta',
                array(
                    'before' => false,
                    'between' => false,
                    'label' => false,
                    'type' => 'text',
                    'class' => 'numerico'
                )
            );


            ?>

        </td>



    </tr>
    <?php
    if ($this->data['Afiliacion']['tipo_operacion'] != 'si') {
        if ($form->isFieldError('numero_cuenta')) {
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
                'numero_cuenta',
                array(
                    'requerido' => _m('campo.requerido', 'el n&uacute;mero de cuenta'),
                    'numero' => _m('formato.invalido', 'el n&uacute;mero de cuenta'),
                    'otro_banco' => _m('formato.invalido', 'el n&uacute;mero de cuenta'),
                    'producto_nodisponible' => _m('producto.nodisponible'),
                    'formato_servicio' => _m('formato.servicio'),
                )
            ) . '</td></tr>';
        }
    }
    ?>



    <tr id="nro_celular_h">
        <td class="etiqueta"><span class="requerido">* </span>N&uacute;mero Celular:</td>
        <td class="campo">
            <?php

            $opciones = array(
                'before' => false,
                'between' => false,
                'after' => false,
                'label' => false,
                'class' => 'codigoTelefono',
                'type' => 'select',
                'options' => $prefijos
            );

            if (!empty($this->data['Afiliacion']['codigo_telefono'])) {

                $opciones['value'] = $this->data['Afiliacion']['codigo_telefono'];
                $valor = $this->data['Afiliacion']['codigo_telefono'];
                echo $form->input('codigo_telefono_movil', array('type' => 'hidden', 'value' => $valor));
            }

            echo $form->input(
                'codigo_telefono_movil',
                $opciones
            );


            echo $form->input(
                'telefono',
                array(
                    'before' => false,
                    'between' => false,
                    'label' => false,
                    'type' => 'text',
                    'style' => 'width:150px',
                    'class' => 'numerico',
                    'minlength' => 7,
                    'maxlength' => 7
                )
            );
            ?>
        </td>
    </tr>
    <?php
    if ($form->isFieldError('telefono')) {
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
            'celular',
            array(
                'requerido' => _m('campo.requerido', 'el n&uacute;mero de celular'),
                'numero' => _m('formato.invalido', 'el n&uacute;mero de celular')
            )
        ) . '</td></tr>';
    }
    ?>


    <tr>
        <td class="etiqueta"><span class="requerido">* </span>Banco:</td>
        <td class="campo">
            <?php
            $opciones = array(
                'type' => 'select',
                'options' => $bancos,
                'empty' => 'Seleccione',
                'label' => false,
                'div' => false,
                'before' => false,
                'between' => false,
                'after' => false,
                'style' => 'width:100%'
            );

            echo $form->input(
                'banco',
                $opciones
            );
            ?>
        </td>
    </tr>
    <?php
    echo '<tr id="filaBeneficiario">
        <td class="etiqueta"><span class="requerido">* </span>Nombre del Beneficiario:</td>
        <td style="display:none" id="nombre" class="campo">' . $form->text('nombre_beneficiario', array('class' => 'alfanumericoEspacio', 'maxlength' => 50)) . '</td>
        <td style="display:none" id="nombreP" class="campo">' . $this->data['Afiliacion']['nombre_beneficiario'] . $form->hidden('nombre_beneficiario') . '</td>
    </tr>';

    if ($form->isFieldError('nombre_beneficiario')) {
        echo '<tr><td>&nbsp;</td><td>' . $form->error(
            'nombre_beneficiario',
            array(
                'requerido' => ($this->data['Afiliacion']['tipo_afiliacion'] == 'r' || $this->data['Afiliacion']['tipo_afiliacion'] == 's' || $this->data['Afiliacion']['tipo_afiliacion'] == 'i') ? _m('error.fatal') : _m('campo.requerido', 'el nombre'),
                'alfanumerico' => _m('alfanumerico.invalido', 'el nombre')
            )
        ) . '</td></tr>';
    }
    ?>

    <tr id="filaCedula">
        <td class="etiqueta labelCedulaRif"><span class="requerido">* </span>C&eacute;dula / RIF del Beneficiario:</td>
        <td class="campo">
            <?php
            $tiposCedula = array(
                'type' => 'select',
                'id' => 'tipo_cedula',
                'label' => false,
                'div' => false,
                'before' => false,
                'between' => false,
                'after' => false,
                'class' => 'tipoRif'
            );

            echo $form->input(
                'tipo_cedula',
                $tiposCedula
            );

            echo $form->input(
                'cedula',
                array(
                    'before' => false,
                    'between' => false,
                    'label' => false,
                    'type' => 'text',
                    'class' => 'nroRif',
                    'maxlength' => 9,
                    'id' => 'cedula',
                )
            );
            ?>
        </td>
    </tr>


    <?php
    if ($form->isFieldError('tipo_cedula')) {
        echo '<tr><td>&nbsp;</td><td>' . $form->error(
            'tipo_cedula',
            array(
                'requerido' => _m('campo.requerido', 'el tipo de c&eacute;dula/rif'),
                'valoresPermitidos' => _m('seleccion.invalida', 'el tipo de c&eacute;dula/rif')
            )
        ) . '</td></tr>';
    } else if ($form->isFieldError('cedula')) {
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
            'cedula',
            array(
                'requerido' => _m('campo.requerido', 'la c&eacute;dula/rif'),
                'longitud' => _m('valor.invalido', array('la c&eacute;dula/rif')),
                'producto_nodisponible' => _m('producto.nodisponible'),
                'no_disponible' => _m('rif.nodisponible'),
                'destinatario' => _m('identificacion.igualcliente', array('la c&eacute;dula/rif del beneficiario'))
            )
        ) . '</td></tr>';
    }
    ?>











    <tr id="filaErrorTarjeta" style="display:none">
        <td>
            <div id="waitTPC" class="texto_centro" style="display:none;">
                <?php echo 'Cargando... ' . $html->image('/img/ajax-loader_mini.gif') . '</div></td>'; ?>
        <td><label><span id="errorST" style="color:red;"></span></label>
        </td>
    </tr>


    <tr id="filaTarjeta" style="display:none">
        <td class="etiqueta"><span class="requerido">* </span>Tarjeta Prepagada:
        </td>
        <td>
            <?php

            if ($modificar == 0) {
                $opciones = array(
                    'type' => 'select',
                    'id' => 'tarjetatpc',
                    'label' => false,
                    'div' => false,
                    'before' => false,
                    'between' => false,
                    'after' => false,
                    'style' => 'width:100%'
                    //'value'=>$num_tarjeta
                );

                if (!empty($this->data['Afiliacion']['id'])) {
                    $opciones['disabled'] = 'disabled';
                    $opciones['value'] = $this->data['Afiliacion']['tipo_afiliacion'];
                    echo $form->hidden('tipo_afiliacion');
                }

                echo $form->input(
                    'tarjetatpc',
                    $opciones
                );
            } else {
                $num_tarjeta = substr($num_tarjeta, 0, 4) . "****" . substr($num_tarjeta, 16 - 4, 4);
                echo $form->input(
                    'numTarjeta',
                    array(
                        'before' => false,
                        'between' => false,
                        'label' => false,
                        'type' => 'text',
                        'class' => 'nroRif',
                        'maxlength' => 20,
                        'value' => $num_tarjeta,
                        'id' => 'numTarjeta'
                    )
                );

            }
            ?>



        </td>
    </tr>
    <?php
    if ($form->isFieldError('tarjetatpc')) {
        echo "<tr><td>&nbsp;</td><td>'.$form->error('tarjetatpc',
                array(
                    'requerido'=> ($this->data['Afiliacion']['tipo_afiliacion']=='h') ? _m('error.fatal') : _m('campo.requerido','Tarjeta Prepago Cash'))
                )
            ).'</td></tr>";
    }
    ?>

    <?php
    if ($this->data['Afiliacion']['tipo_operacion'] != 'ht') {
        ?>
        <tr>
            <td>
                <div id="wait" style="display:none;" height="60px">
                    <?php echo 'Buscando contratos asociados... ' . $html->image('/img/ajax-loader_mini.gif'); ?></div>
            </td>
        </tr>
        <tr>
            <td>
                <div id="error" style="display:none;" height="60px"></div>
            </td>
        </tr>
        <tr>


            <td class="etiqueta"><span id='contract' style="display:none;"><span class="requerido">* </span>Contrato</td>
            </span>
            <td class="campo">
                <select id='contrato' style="display:none;" required="true">
                    <option id='1'>Seleccione</option>


                </select>
                </span>

            </td>
        </tr>
        <?php
    }
    ?>


    <?php
    if ($this->data['Afiliacion']['tipo_operacion'] == 'si') {
        if ($form->isFieldError('numero_cuenta')) {
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
                'numero_cuenta',
                array(
                    'requerido' => _m('campo.requerido', 'el n&uacute;mero de cuenta'),
                    'numero' => _m('formato.invalido', 'el n&uacute;mero de cuenta'),
                    'otro_banco' => _m('formato.invalido', 'el n&uacute;mero de cuenta'),
                    'producto_nodisponible' => _m('producto.nodisponible'),
                    'formato_servicio' => _m('formato.servicio'),
                )
            ) . '</td></tr>';
        }
    }
    ?>




    <?php

    echo $form->input(
        'email',
        array(
            'label' => '<span class="requerido">* </span>E-mail del Beneficiario:',
            'type' => 'text',
            'maxlength' => 50
        )
    );

    if ($form->isFieldError('email')) {
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
            'email',
            array(
                'requerido' => _m('campo.requerido', 'el email del beneficiario'),
                'correo' => _m('correo.invalido', 'el email del beneficiario')
            )
        ) . '</td></tr>';
    }

    $usuario = $session->read('usuarios.datos');

    if (empty($usuario['rif'])) {
        echo $form->input(
            'monto_maximo',
            array(
                'label' => '<span class="requerido">* </span>Monto M&aacute;ximo Bs.:',
                'type' => 'text',
                'class' => 'monto pago'
            )
        );

        if ($form->isFieldError('monto_maximo')) {
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
                'monto_maximo',
                array(
                    'numerico' => _m('formato.invalido', 'el monto m&aacute;ximo'),
                    'requerido' => _m('seleccion.requerida', 'el monto'),
                    'monto_limite' => _m('monto.limite.afiliacion')
                    // 'recargaMinima' => _m('recarga.minima', $montoMinimo),
                    // 'recargaMaxima' => _m('recarga.maxima', $montoMaximo)
                    // 'montoMultiplo' => _m('monto.multiplo', _p('multiplo.digitel')) // INHABILITADO: Error de validación de múltiplo
                )
            ) . '</td></tr>';
        }
    }

    echo '<tr>
        <td colspan="2">
        <span class="requerido">* </span>
        <span class="requerido2">Campo obligatorio</span>
        </td>
        </tr>';

    if (empty($usuario['rif'])) {
        echo '<tr id="nota">
                            <td colspan="2">
                                <br/>Nota: Si Ud. omite el monto m&aacute;ximo, se adhiere el l&iacute;mite establecido por Banco Activo.
                            </td>
                        </tr>';
    } else {
        echo '<tr id="nota">
                            <td colspan="2">
                            </td>
                        </tr>';

    }

    echo '<tr id="notaSeniat" style="display:none">
                <td colspan="2" align="center" class="requerido2">
                    <b>Recuerde que para realizar la afiliaci&oacute;n a Terceros en los Pagos de
                    Impuestos al SENIAT, debe estar registrado como usuario autorizado del
                    beneficiario en la P&aacute;gina Web del SENIAT: <u>http://www.seniat.gob.ve</u> en
                    Registro Autorizados.</b>
                </td>
              </tr>';

    // // echo '<tr id="notaDigitel" style="display:none">
    //            <td colspan="2">
    //                Estimado Cliente, recuerde que el monto debe ser múltiplo de ' . intval(_p('multiplo.digitel')) . ' entre Bs. ' . formatea_monto_fromWs(_p('recarga_minima_digitel')) . ' y ' . formatea_monto_fromWs(_p('recarga_maxima_digitel')) . '
    //            </td>
    //          </tr>';

    echo '<tr><td class="botonera" colspan="2">';

    echo $html->link(
        'Volver',
        '/afiliaciones/listado/v',
        array(
            'class' => 'button'
        )
    );

    echo '&nbsp;&nbsp;&nbsp;';

    echo $html->link(
        'Aceptar',
        'javascript:',
        array(
            'class' => 'button envio_formulario',
            'id' => 'aceptar',
            'div' => false
        )
    );

    echo '</td></tr></table>';
    echo $form->hidden('lim_t', array('id' => 'lim_t', 'value' => $limite['internas_terceros']));
    echo $form->hidden('lim_ob', array('id' => 'lim_ob', 'value' => $limite['externas']));

    //Pantalla tarjeta Prepagada CAsh
    if ($this->data['Afiliacion']['accion'] == 'Modificación') {
        echo $form->hidden('accion', array('id' => 'accion', 'value' => '1'));
    } else {
        echo $form->hidden('accion', array('id' => 'accion', 'value' => '2'));
    }


    echo $form->end();
    ?>
</div>


<script type="text/javascript">

    var ajaxPendiente = false;
    var servicioValido = true;
    var existeProducto = true;
    var loadpage = false;



    function acomoda_tipo_afiliacion() {

        var tipo = $('#tipo').val();
        var subtipo = $('#subtipo').val();
        $("#subtipo").html('<option value="">Seleccione</option>');

        switch (tipo) {
            case 't':
                <?php foreach ($subtipos_general['t'] as $key => $value) { ?>
                    $('#subtipo').append(
                        $('<option>', {
                            'value': '<?php echo $key ?>',
                            'text': '<?php echo $value ?>'
                        })
                    );
                <?php } ?>
                $("td.borde").css("width", "150px");
                break;
            case 'd':
                <?php foreach ($subtipos_general['d'] as $key => $value) { ?>
                    $('#subtipo').append(
                        $('<option>', {
                            'value': '<?php echo $key ?>',
                            'text': '<?php echo $value ?>'
                        })
                    );
                <?php } ?>
                $("td.borde").css("width", "150px");
                break;
            case 'p':
                <?php foreach ($subtipos_general['p'] as $key => $value) { ?>
                    $('#subtipo').append(
                        $('<option>', {
                            'value': '<?php echo $key ?>',
                            'text': '<?php echo $value ?>'
                        })
                    );
                <?php } ?>
                $("td.borde").css("width", "150px");
                break;

            case 's':
                <?php foreach ($subtipos_general['s'] as $key => $value) { ?>
                    $('#subtipo').append(
                        $('<option>', {
                            'value': '<?php echo $key ?>',
                            'text': '<?php echo $value ?>'
                        })
                    );
                <?php } ?>
                $("td.borde").css("width", "150px");
                break;

            case 'r':
                <?php foreach ($subtipos_general['r'] as $key => $value) { ?>
                    $('#subtipo').append(
                        $('<option>', {
                            'value': '<?php echo $key ?>',
                            'text': '<?php echo $value ?>'
                        })
                    );
                <?php } ?>
                $("td.borde").css("width", "150px");
                break;

            case 'h':
                <?php foreach ($subtipos_general['h'] as $key => $value) { ?>
                    $('#subtipo').append(
                        $('<option>', {
                            'value': '<?php echo $key ?>',
                            'text': '<?php echo $value ?>'
                        })
                    );
                <?php } ?>
                $("td.borde").css("width", "150px");
                break;


            case 'i':
                <?php foreach ($subtipos_general['i'] as $key => $value) {
                    if (($usuario['tipo_rif'] != 'G') || ($usuario['tipo_rif'] == 'G' && $key != 't')) {
                        ?>
                        $('#subtipo').append(
                            $('<option>', {
                                'value': '<?php echo $key ?>',
                                'text': '<?php echo $value ?>'
                            })
                        );
                    <?php }
                } ?>
                $("td.borde").css("width", "150px");
                break;

        }


        $('#subtipo').val(subtipo);
        acomoda_tipo_operacion();
    }

    function acomoda_tipo_operacion() {


        var subtipo = $('#subtipo').val();
        var tipo = $('#tipo').val();

        if (!existeProducto) {
            $('label.error').remove();
            // $('div.error-message').remove();
        }
        var trs = $('table.formulario tr');

        for (i = 5; i < trs.size() - 1; i++) {
            if (tipo == '' || subtipo == '') {
                $(trs.get(i)).hide();
            } else {
                $(trs.get(i)).show();
            }
        }


 
        if (tipo != '' && subtipo != '') {


            var email = $('#AfiliacionEmail').parentsUntil('table').filter('tr');
            var cedula = $('#AfiliacionTipoCedula').parentsUntil('table').filter('tr');
            var nombreCampo = $('#nombre');
            var nombreCampo1 = $('#nombreP');
            var numeroCuenta = $('#AfiliacionNumeroCuenta').parentsUntil('table').filter('tr');
            var codigoTelefono = $('#AfiliacionCodigoTelefonoMovil').parentsUntil('table').filter('tr');
            var numeroTelefono = $('#AfiliacionTelefono').parentsUntil('table').filter('tr');
            var banco = $('#AfiliacionBanco').parentsUntil('table').filter('tr');
            var montoMaximo = $('#AfiliacionMontoMaximo').parentsUntil('table').filter('tr');
            $('#nota').show();
            $('#notaSeniat').hide();
            $('#notaDigitel').hide();
            $('.labelCedulaRif').html('<span class="requerido">* </span>C&eacute;dula/RIF del Beneficiario:');
            if (tipo == 'i') {
                $('#tipo_cedula').html("<option value='V'>V</option><option value='E'>E</option><option value='J'>J</option><option value='G'>G</option>");
            } else if (subtipo != 'ob') {
                $('#tipo_cedula').html("<option value='V'>V</option><option value='E'>E</option><option value='P'>P</option><option value='J'>J</option><option value='G'>G</option>");
            }
            $('#tipo_cedula').removeAttr('readonly');
            $('#cedula').removeAttr('readonly');

            <?php if (!empty($this->data['Afiliacion']['tipo_cedula'])) { ?>
                if ($('#cedula').val() != "") {
                    $('#tipo_cedula').val('<?php echo $this->data['Afiliacion']['tipo_cedula']; ?>');
                }

            <?php } ?>


            // Identificar tipo de afiliación seleccionado


            switch (tipo) {
                case 't':
                    if (subtipo == 't') {
                        email.show();
                        nombreCampo.show();
                        nombreCampo.find('input')
                            .removeAttr('disabled');

                        <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                            if (loadpage) {
                                nombreCampo.find('input').val('');
                                email.find('input').val('');
                                cedula.find('input').val('');
                                numeroTelefono.find('input').val('');
                                $('#AfiliacionNumeroCuenta').val('');
                                $('#AfiliacionMontoMaximo').val('');
                            }
                        <?php } ?>

                        nombreCampo1.hide();
                        $('#tipoServicio').hide();
                        $('#simfDT').hide();
                        banco.hide();
                        codigoTelefono.hide();
                        numeroTelefono.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');
                        $('.labelBeneficiario').html('<span class="requerido">* </span>Cuenta del Beneficiario:');
                        $('#AfiliacionNumeroCuenta')
                            .attr('maxlength', '20')
                            .attr('minlength', '20');
                        $('#AfiliacionCodigoTelefono')
                            .attr('style', 'display:none');
                        if (subtipo != 't' && subtipo != 'ob') {
                            $('#filaCedula').show();
                            $('#filaTarjeta').hide();
                            $('#filaErrorTarjeta').hide();


                        } else {
                            $('#filaCedula').hide();
                            $('#filaTarjeta').hide();
                            $('#filaErrorTarjeta').hide();

                            $('#AfiliacionMontoMaximo').show();

                        }
                    } else {
                        email.hide();
                        $('#filaCedula').hide();
                        $('#filaTarjeta').hide();
                        $('#filaErrorTarjeta').hide();

                        $('#filaBeneficiario').hide();
                        $('#AfiliacionMontoMaximo').show();
                        numeroCuenta.hide();
                        codigoTelefono.hide();
                        numeroTelefono.hide();
                        $('#tipoServicio').hide();
                        acomoda_destino();
                    }

                    break;

                case 'd':
                    email.show();
                    nombreCampo.show();
                    nombreCampo.find('input')
                        .removeAttr('disabled');

                    <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                        if (loadpage) {
                            nombreCampo.find('input').val('');
                            email.find('input').val('');
                            cedula.find('input').val('');
                            $('#AfiliacionNumeroCuenta').val('');
                        }
                    <?php } ?>

                    nombreCampo1.hide();
                    montoMaximo.hide();
                    codigoTelefono.hide();
                    numeroTelefono.hide();
                    banco.hide();
                    $('#tipoServicio').hide();
                    $('#simfDT').hide();
                    nombreCampo1.find('input').attr('disabled', 'disabled');
                    $('.labelBeneficiario').html('<span class="requerido">* </span>Cuenta del Beneficiario:');
                    $('#AfiliacionNumeroCuenta')
                        .attr('maxlength', '20')
                        .attr('minlength', '20');
                    $('#AfiliacionCodigoTelefono')
                        .attr('style', 'display:none');
                    if (subtipo != 't' && subtipo != 'ob') {
                        $('#filaCedula').show();
                        $('#filaTarjeta').hide();
                        $('#filaErrorTarjeta').hide();
                    } else {
                        $('#filaCedula').hide();
                        $('#filaTarjeta').hide();
                        $('#filaErrorTarjeta').hide();
                    }

                    break;
                case 'p':


                    $('#contract').hide();
                    $('#contrato').hide();
                    $('#error').hide();
                    email.show();
                    nombreCampo.show();
                    nombreCampo.find('input')
                        .removeAttr('disabled');

                    <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                        if (loadpage) {
                            nombreCampo.find('input').val('');
                            email.find('input').val('');
                            cedula.find('input').val('');
                            $('#AfiliacionNumeroCuenta').val('');
                            $('#AfiliacionMontoMaximo').val('');
                        }
                    <?php } ?>

                    nombreCampo1.hide();
                    codigoTelefono.hide();
                    numeroTelefono.hide();
                    banco.hide();
                    $('#tipoServicio').hide();
                    $('#simfDT').hide();
                    nombreCampo1.find('input').attr('disabled', 'disabled');
                    $('.labelBeneficiario').html('<span class="requerido">* </span>Tarjeta de Cr\u00e9dito del Beneficiario:');
                    $('#AfiliacionNumeroCuenta').attr('minlength', '14').attr('maxlength', '16');
                    $('#AfiliacionCodigoTelefono').attr('style', 'display:none');
                    //if(subtipo != 't' && subtipo != 'ob'){
                    if (subtipo != 'ob') {
                        $('#filaCedula').show();
                        $('#filaTarjeta').hide();
                        $('#filaErrorTarjeta').hide();

                    } else {
                        $('#filaCedula').hide();
                        $('#filaTarjeta').hide();
                        $('#filaErrorTarjeta').hide();
                    }
                    break;

                //Desarrollo Tarjeta Prepagada.
                case 'h':
                    $('#tipoServicio').hide();
                    $('#simfDT').hide();
                    $('#cuentaBeneficiario').hide();
                    $('#nro_celular_h').hide();
                    $('#filaTarjeta').show();
                    $('#filaErrorTarjeta').show();
                    $('#tarjetatpc').show();
                    banco.hide();
                    //$('#filaBeneficiario').hide();
                    nombreCampo.show();
                    numeroCuenta.hide();
                    montoMaximo.hide();
                    $('#nota').hide();

                    <?php
                    if ($modificar == 1) {
                        ?>
                        $('#tipo_cedula').attr('readonly', 'readonly');
                        $('#cedula').attr('readonly', 'readonly');
                        $('#tarjetatpc').attr('readonly', 'readonly');
                        $('#numTarjeta').attr('readonly', 'readonly');
                        //$('#cedula').removeAttr('onblur');     
                    <?php
                    } else { ?>
                        $('#cedula').attr('onblur', 'listarTarjeta()');
                    <?php }
                    ?>

                    break;
                //fin Desarrollo Tarjeta Prepagada. 

                case 's':
                    if (subtipo == 'si') {
                        $('#simfDT').hide();
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');
                        tipo = 'POSTPAGO';
                        email.hide();
                        numeroCuenta.hide();

                        if ($('#AfiliacionNumeroCuenta').val() != '') {
                            $('#contract').show();
                            $('#contrato').show();
                            $('#contrato').find('option').remove();
                            $('#contrato').append('<option value=' + $('#AfiliacionNumeroCuenta').val() + '>' + $('#AfiliacionNumeroCuenta').val() + '</option>');
                        }
                        $('#AfiliacionAlias')
                        $('#AfiliacionAlias').show();
                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');
                        //cedula.find('input').val('');                                  
                        $('#tipoServicio').hide();
                        $('input[name="data[Afiliacion][tipo_servicio]"]').val('sfi');
                        $('#filaCedula').show();
                        $('#filaTarjeta').hide();
                        $('#filaErrorTarjeta').hide();


                        $('.tipo_cedula').html('<span class="requerido">* </span>N&uacute;mero Cedula: ');
                        $('#filaBeneficiario').show();
                        $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de Servicio o Cuenta Contrato:');
                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');
                        var cedulas = $('#cedula').val()
                        var cedulas = $('#cedula');
                        cedulas.on('blur', function (e) {
                            var cedulas = $('#cedula').val()
                            if (cedulas == '') {
                                $('#error').find('span').remove();
                                //$('#error').append('<span class="error" style="color:red">Estimado cliente, ha ocurrido un error inesperado. Por favor intente    de nuevo más tarde.</span>');
                                $('#wait').hide();
                                return false;
                            }
                            var contratos = $('#contrato');
                            cedulas = $('#cedula').val();
                            $.ajax({
                                type: "GET",
                                url: '<?php echo Router::url("/pagos_servicios/obtenerContratos/"); ?>' + cedulas + '/' + tipo,
                                dataType: "json",
                                beforeSend: function () {
                                    ajaxPendiente = true;
                                    $('#wait').show();
                                    $('#contract').hide();
                                    $('#contrato').hide();
                                    $('#error').hide();
                                    montoMaximo.show();
                                    $('#nota').show();
                                    $('#aceptar').show();


                                },
                                success: function (contrato) {
                                    ajaxPendiente = false;
                                    $('#wait').hide();
                                    $('#error').find('span').remove();
                                    contratos.find('option').remove();
                                    acomoda_prefijos();
                                    $("#contrato").html('<option value="">Seleccione</option>');
                                    $.each(contrato, function (key, value) {
                                        if (value == '999') {
                                            $('#error').find('span').remove();
                                            $('#error').show();
                                            $('#error').append('<span class="error" style="color:red">Estimado cliente, ha ocurrido un error inesperado. Por favor intente de nuevo m&aacute;s tarde.</span>');
                                            $('#aceptar').hide();
                                            montoMaximo.hide();
                                            $('#nota').hide();
                                            return false;
                                        } else if (value.errorCode == -1) {
                                            $('#error').find('span').remove();
                                            $('#error').show();
                                            $('#error').append('<span class="error" style="color:red">' + <?php echo _m('suscriptor.noExiste', null, true) ?> + '</span>');
                                            $('#aceptar').hide();
                                            montoMaximo.hide();
                                            $('#nota').hide();


                                        } else if (value === '991') {
                                            $('#error').find('span').remove();

                                            $('#error').show();
                                            $('#error').append('<span class="error" style="color:red">' + <?php echo _m('nopostpago.inter', null, true) ?> + '</span>');
                                            $('#aceptar').hide();
                                            montoMaximo.hide();
                                            $('#nota').hide();



                                        } else if (value == -2) {

                                            $('#error').show();
                                            $('#error').append('<span class="error" style="color:red">' + <?php echo _m('servicio.noactivo', null, true) ?> + '</span>');
                                            $('#aceptar').hide();
                                            montoMaximo.hide();
                                            $('#nota').hide();


                                        } else {

                                            $('#filaBeneficiario').show();
                                            //$('.labelBeneficiario').html('<span class="requerido">* </span>Número de Servicio o Cuenta Contrato:');
                                            $('#contract').show();
                                            $('#contrato').show();
                                            $("#contrato").append('<option value=' + value.contractId + '>' + value.contractId + '</option>');
                                            montoMaximo.show();
                                        }
                                    });
                                    $("#contrato").change(function () {

                                        $('input[name="data[Afiliacion][numero_cuenta]"]').val($("#contrato").val());

                                    });
                                },
                                error: function (contrato) {
                                    $('#contrato').html(<?php echo _m('error.fatal', null, true) ?>);
                                }
                            });
                        });
                        acomoda_tipo_servicio();

                    } else {
                        $('#contract').hide();
                        $('#contrato').hide();
                        $('#error').hide();
                        $('#simfDT').hide();

                        email.hide();
                        $('#filaCedula').hide();
                        $('#filaTarjeta').hide();
                        $('#filaErrorTarjeta').hide();
                        filaErrorTarjeta
                        $('#filaBeneficiario').hide();
                        $('#simfDT').hide();
                        montoMaximo.hide();
                        numeroCuenta.hide();
                        codigoTelefono.hide();
                        numeroTelefono.hide();
                        banco.hide();
                        acomoda_tipo_servicio();
                    }
                    break;
                case 'r':
                    if (subtipo == 'si') {
                        $('#simfDT').hide();
                        numeroCuenta.hide();
                        codigoTelefono.hide();
                        numeroTelefono.hide();
                        banco.hide();
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');

                        tipo = 'PREPAGO';
                        email.hide();
                        numeroCuenta.hide();
                        if ($('#AfiliacionNumeroCuenta').val() != '') {
                            $('#contract').show();
                            $('#contrato').show();
                            $('#contrato').find('option').remove();
                            $('#contrato').append('<option value=' + $('#AfiliacionNumeroCuenta').val() + '>' + $('#AfiliacionNumeroCuenta').val() + '</option>');
                        }
                        $('#AfiliacionAlias')
                        $('#AfiliacionAlias').show();
                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');
                        //cedula.find('input').val('');
                        $('#AfiliacionNombreBeneficiario').show();

                        $('#tipoServicio').hide();
                        $('input[name="data[Afiliacion][tipo_servicio]"]').val('sfi');
                        $('#filaCedula').show();
                        $('#filaTarjeta').hide();
                        $('#filaErrorTarjeta').hide();


                        $('.tipo_cedula').html('<span class="requerido">* </span>N&uacute;mero Cedula: ');
                        $('#filaBeneficiario').show();
                        $('.labelBeneficiario').html('<span class="requerido">* </span>Numero de Servicio o Cuenta Contrato:');
                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');
                        var cedular = $('#cedula').val()
                        var cedulae = $('#cedula');
                        cedulae.on('blur', function (e) {
                            var cedular = $('#cedula').val()
                            if (cedular == '') {
                                $('#error').find('span').remove();
                                //$('#error').append('<span class="error" style="color:red">Estimado cliente, ha ocurrido un error inesperado. Por favor intente    de nuevo más tarde.</span>');
                                $('#wait').hide();
                                return false;
                            }
                            var contratos = $('#contrato');
                            cedular = $('#cedula').val();
                            $.ajax({
                                type: "GET",
                                url: '<?php echo Router::url("/pagos_servicios/obtenerContratos/"); ?>' + cedular + '/' + tipo,
                                dataType: "json",
                                beforeSend: function () {
                                    ajaxPendiente = true;
                                    $('#wait').show();
                                    $('#contract').hide();
                                    $('#contrato').hide();
                                    $('#error').hide();
                                    montoMaximo.show();
                                    $('#nota').show();
                                    $('#aceptar').show();


                                },
                                success: function (contrato) {
                                    ajaxPendiente = false;
                                    $('#wait').hide();
                                    $('#error').find('span').remove();
                                    contratos.find('option').remove();
                                    acomoda_prefijos();
                                    $("#contrato").html('<option value="">Seleccione</option>');
                                    $.each(contrato, function (key, value) {
                                        if (value == '999') {
                                            $('#error').find('span').remove();
                                            $('#error').show();
                                            $('#error').append('<span class="error" style="color:red">Estimado cliente, ha ocurrido un error inesperado. Por favor intente de nuevo m&aacute;s tarde.</span>');
                                            $('#aceptar').hide();
                                            montoMaximo.hide();
                                            $('#nota').hide();
                                            return false;
                                        } else if (value.errorCode == -1) {
                                            $('#error').find('span').remove();
                                            $('#error').show();
                                            $('#error').append('<span class="error" style="color:red">' + <?php echo _m('suscriptor.noExiste', null, true) ?> + '</span>');
                                            $('#aceptar').hide();
                                            montoMaximo.hide();
                                            $('#nota').hide();

                                        } else if (value === '991') {
                                            $('#error').find('span').remove();
                                            $('#error').show();
                                            $('#error').append('<span class="error" style="color:red">' + <?php echo _m('no.prepagoInter', null, true) ?> + '</span>');
                                            $('#aceptar').hide();
                                            montoMaximo.hide();
                                            $('#nota').hide();



                                        } else if (value == -2) {

                                            $('#error').show();
                                            $('#error').append('<span class="error" style="color:red">' + <?php echo _m('servicio.noactivo', null, true) ?> + '</span>');
                                            $('#aceptar').hide();
                                            montoMaximo.hide();
                                            $('#nota').hide();


                                        } else {

                                            $('#filaBeneficiario').show();
                                            //$('.labelBeneficiario').html('<span class="requerido">* </span>Número de Servicio o Cuenta Contrato:');
                                            $('#contract').show();
                                            $('#contrato').show();
                                            $("#contrato").append('<option value=' + value.contractId + '>' + value.contractId + '</option>');
                                            montoMaximo.show();
                                        }
                                    });
                                    $("#contrato").change(function () {

                                        $('input[name="data[Afiliacion][numero_cuenta]"]').val($("#contrato").val());
                                    });
                                },
                                error: function (contrato) {
                                    $('#contrato').html(<?php echo _m('error.fatal', null, true) ?>);
                                }
                            });
                        });



                    } else {
                        $('#contract').hide();
                        $('#contrato').hide();
                        $('#error').hide();

                        email.hide();


                        $('#filaCedula').hide();
                        $('#filaTarjeta').hide();
                        $('#filaErrorTarjeta').hide();

                        $('#filaBeneficiario').hide();
                        $('#simfDT').hide();
                        montoMaximo.hide();
                        numeroCuenta.hide();
                        codigoTelefono.hide();
                        numeroTelefono.hide();
                        banco.hide();
                        if (subtipo === 'sd') {
                            $('#notaDigitel').show();
                            codigoTelefono.hide();
                        }
                        codigoTelefono.hide();
                        acomoda_tipo_servicio();

                    }
                    break;
                case 'i':
                    $('#contract').hide();
                    $('#contrato').hide();
                    $('#filaTarjeta').hide();
                    $('#filaErrorTarjeta').hide();


                    $('#error').hide();
                    numeroCuenta.hide();
                    codigoTelefono.hide();
                    numeroTelefono.hide();
                    banco.hide();
                    montoMaximo.hide();
                    $('#tipoServicio').hide();
                    $('#simfDT').hide();
                    cedula.show();
                    $('#nota').hide();
                    $('.labelCedulaRif').html('<span class="requerido">* </span>RIF del Beneficiario:');
                    nombreCampo.show();

                    if (subtipo == 't') {
                        nombreCampo.find('input').removeAttr('disabled');
                        email.show();
                        $('#notaSeniat').show();

                    } else {
                        nombreCampo.find('input').attr('disabled', 'disabled');
                        email.hide();
                        nombreCampo.find('input').val('<?php echo $nombreCliente; ?>');
                        nombreCampo1.find('input').val('<?php echo $nombreCliente; ?>');

                        <?php if (empty($usuario['rif'])) { ?>
                            $('#tipo_cedula').html("<option value='<?php echo $usuario['tipo_cedula']; ?>'><?php echo $usuario['tipo_cedula']; ?></option>");
                            $('#tipo_cedula').val('<?php echo $usuario['tipo_cedula']; ?>');
                        <?php } else { ?>
                            $('#tipo_cedula').html("<option value='<?php echo $usuario['tipo_rif']; ?>'><?php echo $usuario['tipo_rif']; ?></option>");
                            $('#tipo_cedula').val('<?php echo $usuario['tipo_rif']; ?>');
                        <?php } ?>
                        $('#tipo_cedula').attr('readonly', 'readonly');
                        $('#cedula').val('<?php echo $rifCliente; ?>');
                        $('#cedula').attr('readonly', 'readonly');

                    }
                    nombreCampo1.hide();
                    nombreCampo1.find('input').attr('disabled', 'disabled');

                    <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                        if (loadpage) {
                            if (subtipo == 't' || tipo == 'te') {
                                nombreCampo.find('input').val('');
                                cedula.find('input').val('');
                            }
                            email.find('input').val('');
                            numeroCuenta.find('input').val('');
                            montoMaximo.find('input').val('');
                        }
                    <?php } ?>

                    break;

            }

            // Identificar tipo de operación seleccionado
            // Sólo aplica para transferencias, pagos de tdc y pagos de impuestos seniat





            if (tipo == 'p' || tipo == 't' || tipo == 'd') {
                if (tipo == 't' || tipo == 'd') {
                    if (subtipo == 't' || tipo == 'd') {
                        $('#AfiliacionNumeroCuenta')
                            .attr('minlength', '10');
                    } else {
                        $('#AfiliacionNumeroCuenta')
                            .attr('minlength', '20');
                    }
                }

                var diff = $('#AfiliacionNombreBeneficiario,#AfiliacionTipoCedula').parentsUntil('table').filter('tr');

                if (subtipo != 'tob' && subtipo != 't') {
                    diff.hide();
                } else {
                    diff.show();
                }

                if (subtipo == 'ob') {
                    email.hide();
                } else {
                    email.show();
                }

                if (subtipo != 'ob' && subtipo != 't') {
                    cedula.show();
                } else {
                    cedula.hide();
                }
            }
            // Límites de montos




            var limite = '';
            switch (subtipo) {
                case 'tob':
                case 'ob':
                case 'sc':
                case 'sm':
                case 'scn':
                    limite = 'ob';
                    break;
                case 't':
                case 'sd':
                case 'd':
                    limite = 't';
                    break;
            }


            $('#monto_limite').val($('#lim_' + limite).val());

        }
        else if (tipo == 's' || tipo == 'r') {
            $('#AfiliacionNumeroCuenta').val('');
            $('#AfiliacionMontoMaximo').val('');
            $('#nombreP').html('');
            // $('div.error-message').remove();
            if (subtipo == 'scn') {
                $('#AfiliacionNumeroCuenta')
                    .removeAttr('minlength')
                    .attr('maxlength', '12');
            }
            else if (subtipo == 'sd') {
                $('#AfiliacionNumeroCuenta')
                    .attr('minlength', '07')
                    .attr('maxlength', '07');
            } else {
                $('#AfiliacionNumeroCuenta')
                    .attr('minlength', '11')
                    .attr('maxlength', '11');
            }
        }
        else {
            $('#AfiliacionNumeroCuenta').val('');
            $('#AfiliacionMontoMaximo').val('');
        }

        //estas lineas permiten quitar el mensaje
        //Nota: Si Ud. omite el monto máximo, se adhiere el límite establecido por Banco Activo.
        //cuando la afiliacion sea postpago y digitel
        if (tipo == 's' && subtipo == 'sd') {
            document.getElementById('nota').style.color = 'white';
        } else {
            document.getElementById('nota').style.color = 'black';
        }
    }

    function acomoda_tipo_servicio() {
        var modalidad = $('#tipo').val();
        var subtipo = $('#subtipo').val();
        var servicios = $('#servicios').val();

        $("#servicios").html('<option value="">Seleccione</option>');

        switch (modalidad) {
            case 's':
                switch (subtipo) {
                    case 'sd':
                        <?php foreach ($tiposServicios['s'] as $key => $value) {
                            if (in_array($key, array('stm', 'sim', 'stf'))) {
                                ?>
                                $('#servicios').append(
                                    $('<option>', {
                                        'value': '<?php echo $key ?>',
                                        'text': '<?php echo $value ?>'
                                    })
                                );
                            <?php }
                        }
                        ?>
                        $("td.borde").css("width", "150px");
                        break;

                    case 'sm':
                        <?php foreach ($tiposServicios['s'] as $key => $value) {
                            if (in_array($key, array('stm'))) {
                                ?>
                                $('#servicios').append(
                                    $('<option>', {
                                        'value': '<?php echo $key ?>',
                                        'text': '<?php echo $value ?>'
                                    })
                                );
                            <?php }
                        }
                        ?>
                        $("td.borde").css("width", "150px");
                        break;
                    case 'st':
                        <?php foreach ($tiposServicios['s'] as $key => $value) {
                            if (in_array($key, array('stm', 'stf', 'sim', 'stv'))) {
                                ?>
                                $('#servicios').append(
                                    $('<option>', {
                                        'value': '<?php echo $key ?>',
                                        'text': '<?php echo $value ?>'
                                    })
                                );
                            <?php }
                        }
                        ?>
                        $("td.borde").css("width", "150px");
                        break;

                    case 'sc':

                        <?php foreach ($tiposServicios['s'] as $key => $value) {
                            if (in_array($key, array('sfi'))) {
                                ?>
                                $('#servicios').append(
                                    $('<option>', {
                                        'value': '<?php echo $key ?>',
                                        'text': '<?php echo $value ?>'
                                    })
                                );
                            <?php }
                        }
                        ?>
                        $("td.borde").css("width", "150px");
                        break;


                }
                break;



            case 'r':
                switch (subtipo) {
                    case 'sd':
                        <?php foreach ($tiposServicios['r'] as $key => $value) {
                            if (in_array($key, array('rtm', 'rim', 'rtf'))) {
                                ?>
                                $('#servicios').append(
                                    $('<option>', {
                                        'value': '<?php echo $key ?>',
                                        'text': '<?php echo $value ?>'
                                    })
                                );
                            <?php }
                        }
                        ?>
                        $("td.borde").css("width", "150px");
                        break;

                    case 'sm':
                        <?php foreach ($tiposServicios['r'] as $key => $value) {
                            if (in_array($key, array('rtm'))) {
                                ?>
                                $('#servicios').append(
                                    $('<option>', {
                                        'value': '<?php echo $key ?>',
                                        'text': '<?php echo $value ?>'
                                    })
                                );

                            <?php }
                        }
                        ?>
                        $("td.borde").css("width", "150px");
                        break;

                    case 'st':
                        <?php foreach ($tiposServicios['r'] as $key => $value) {
                            if (in_array($key, array('rtm', 'rtf', 'rim', 'rtv'))) {
                                ?>
                                $('#servicios').append(
                                    $('<option>', {
                                        'value': '<?php echo $key ?>',
                                        'text': '<?php echo $value ?>'
                                    })
                                );
                            <?php }
                        } ?>
                        $("td.borde").css("width", "150px");
                        break;

                    case 'sc':
                        <?php foreach ($tiposServicios['r'] as $key => $value) {
                            if (in_array($key, array('rfi'))) {
                                ?>
                                $('#servicios').append(
                                    $('<option>', {
                                        'value': '<?php echo $key ?>',
                                        'text': '<?php echo $value ?>'
                                    })
                                );
                            <?php }
                        } ?>
                        $("td.borde").css("width", "150px");
                        break;
                    case 'sv':   //simple TV
                        <?php foreach ($tiposServicios['r'] as $key => $value) {
                            if (in_array($key, array('rtc'))) {
                                ?>
                                $('#servicios').append(
                                    $('<option>', {
                                        'value': '<?php echo $key ?>',
                                        'text': '<?php echo $value ?>'
                                    })
                                );
                            <?php }
                        } ?>
                        $("td.borde").css("width", "150px");
                        break;



                }
                break;




        }
        
        $('#servicios').val(servicios);

    }
    //Carga los prefijos telefonicos segun el tipo de operacion
    function acomoda_prefijos() {


        var tipoServicio = $('#tipo').val();
        var subtipo = $('#subtipo').val();
        var servicios = $('#servicios').val();
        var nombreCampo = $('#nombre');
        var nombreCampo1 = $('#nombreP');
        var numeroCuenta = $('#AfiliacionNumeroCuenta').parentsUntil('table').filter('tr');
        var montoMaximo = $('#AfiliacionMontoMaximo').parentsUntil('table').filter('tr');
        var recarga_minima;
        var recarga_maxima;

        $('#AfiliacionCodigoTelefono')
            .find('option')
            .remove()
            .end()
            ;

        
        switch (subtipo) {
            case 'si':
                if ($('#tipo').val() == 'r') {
                    recarga_minima = <?php echo _p('recarga_minima_inter_pre') ?>;
                    $('#recarga_minima').val(recarga_minima);
                    recarga_maxima = <?php echo _p('recarga_maxima_inter_pre') ?>;
                    $('#recarga_maxima').val(recarga_maxima);

                } else if (($('#tipo').val() == 's')) {
                    recarga_minima = <?php echo _p('recarga_minima_inter_post') ?>;
                    $('#recarga_minima').val(recarga_minima);
                    recarga_maxima = <?php echo _p('recarga_maxima_inter_post') ?>;
                    $('#recarga_maxima').val(recarga_maxima);


                }
                break;
            case 'sd': //Digitel    
                switch (servicios) {
                    case 'stm':
                    case 'rtm':
                    case 'sim':
                    case 'rim':
                        <?php foreach ($prefijos_telefonicos as $key) {
                            if ($key['PrefijosTelefonicos']['tipo_operacion'] == 'sd') {
                                ?>
                                $('#AfiliacionCodigoTelefono').append(
                                    $('<option>', {
                                        'value': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>',
                                        'text': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>'
                                    })
                                );
                            <?php
                            }
                        }
                        ?>
                        <?php if (isset($this->data['Afiliacion']['codigo_telefono']) && !empty($this->data['Afiliacion']['codigo_telefono'])) { ?>
                            $("#AfiliacionCodigoTelefono").val('<?php echo $this->data['Afiliacion']['codigo_telefono'] ?>');
                        <?php } ?>
                        numeroCuenta.show();
                        $('#filaBeneficiario').show();

                        //$('.labelBeneficiario').html('<span class="requerido">* </span>Número de Servicio o Cuenta Contrato:');                                                                                                                        

                        $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de L&iacute;nea:');


                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');

                        <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                            if (loadpage) {
                                nombreCampo1.find('input').val('');
                                $('#AfiliacionNumeroCuenta').val('');
                                $('#AfiliacionMontoMaximo').val('');
                            }
                        <?php } ?>

                        montoMaximo.show();
                        $('#AfiliacionCodigoTelefono').attr('style', 'display:inline');
                        $('#AfiliacionNumeroCuenta').attr('minlength', '07');
                        $('#AfiliacionNumeroCuenta').attr('maxlength', '07');
                        $('#AfiliacionNumeroCuenta').css("width", "150px");
                        recarga_minima = <?php echo $minimo_digitel_php ?>;
                        $('#recarga_minima').val(recarga_minima);
                        recarga_maxima = <?php echo $maximo_digitel_php ?>;
                        $('#recarga_maxima').val(recarga_maxima);
                        acomoda_tipo_servicio();

                        break;
                    case 'stf':
                    case 'rtf':
                        <?php foreach ($prefijos_telefonicos as $key) {
                            if ($key['PrefijosTelefonicos']['tipo_operacion'] == 'all') {
                                ?>
                                $('#AfiliacionCodigoTelefono').append(
                                    $('<option>', {
                                        'value': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>',
                                        'text': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>'
                                    })
                                );
                            <?php
                            }
                        }
                        ?>


                        <?php if (isset($this->data['Afiliacion']['codigo_telefono']) && !empty($this->data['Afiliacion']['codigo_telefono'])) { ?>
                            $("#AfiliacionCodigoTelefono").val('<?php echo $this->data['Afiliacion']['codigo_telefono'] ?>');
                        <?php } ?>


                        numeroCuenta.show();
                        $('#filaBeneficiario').show();
                        $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de L&iacute;nea:');

                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');

                        <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                            if (loadpage) {
                                nombreCampo1.find('input').val('');
                                $('#AfiliacionNumeroCuenta').val('');
                                $('#AfiliacionMontoMaximo').val('');
                            }
                        <?php } ?>

                        montoMaximo.show();
                        $('#AfiliacionCodigoTelefono').attr('style', 'display:inline');
                        $('#AfiliacionNumeroCuenta').attr('minlength', '07');
                        $('#AfiliacionNumeroCuenta').attr('maxlength', '07');
                        $('#AfiliacionNumeroCuenta').css("width", "150px");
                        recarga_minima = <?php echo $minimo_digitel_php ?>;
                        $('#recarga_minima').val(recarga_minima);
                        recarga_maxima = <?php echo $maximo_digitel_php ?>;
                        $('#recarga_maxima').val(recarga_maxima);
                        $('#lim_ob').val(recarga_maxima);
                        acomoda_tipo_servicio();
                        break;
                }

                var modalidad = $('#tipo').val();
                if (modalidad == 's') {
                    montoMaximo.hide();
                } else {
                    montoMaximo.show();
                }

                break;

            //-------------------------------------------------------------------------
            case 'sc':
                switch (servicios) {
                    //case 'stm':
                    //case 'rtm':
                    //case 'sim':
                    case 'rfi':
                        <?php foreach ($prefijos_telefonicos as $key) {
                            if ($key['PrefijosTelefonicos']['tipo_operacion'] == 'all') {
                                ?>

                                $('#AfiliacionCodigoTelefono').append(
                                    $('<option>', {
                                        'value': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>',
                                        'text': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>'
                                    })
                                );
                            <?php
                            }
                        }
                        ?>
                        <?php if (isset($this->data['Afiliacion']['codigo_telefono']) && !empty($this->data['Afiliacion']['codigo_telefono'])) { ?>
                            $("#AfiliacionCodigoTelefono").val('<?php echo $this->data['Afiliacion']['codigo_telefono'] ?>');
                        <?php } ?>
                        numeroCuenta.show();
                        $('#filaBeneficiario').show();
                        $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de Servicio o Cuenta Contrato:');
                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');

                        <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                            if (loadpage) {
                                nombreCampo1.find('input').val('');
                                $('#AfiliacionNumeroCuenta').val('');
                                $('#AfiliacionMontoMaximo').val('');
                            }
                        <?php } ?>

                        montoMaximo.show();
                        $('#AfiliacionCodigoTelefono').attr('style', 'display:inline');
                        $('#AfiliacionNumeroCuenta').attr('minlength', '07');
                        $('#AfiliacionNumeroCuenta').attr('maxlength', '07');
                        $('#AfiliacionNumeroCuenta').css("width", "150px");
                        recarga_minima = <?php echo $minimo_digitel_php ?>;
                        $('#recarga_minima').val(recarga_minima);
                        recarga_maxima = <?php echo $maximo_digitel_php ?>;
                        $('#recarga_maxima').val(recarga_maxima);
                        acomoda_tipo_servicio();

                        break;
                    case 'stf':
                    case 'rtf':
                        <?php foreach ($prefijos_telefonicos as $key) {
                            if ($key['PrefijosTelefonicos']['tipo_operacion'] == 'all') {
                                ?>
                                $('#AfiliacionCodigoTelefono').append(
                                    $('<option>', {
                                        'value': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>',
                                        'text': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>'
                                    })
                                );
                            <?php
                            }
                        }
                        ?>
                        <?php if (isset($this->data['Afiliacion']['codigo_telefono']) && !empty($this->data['Afiliacion']['codigo_telefono'])) { ?>
                            $("#AfiliacionCodigoTelefono").val('<?php echo $this->data['Afiliacion']['codigo_telefono'] ?>');
                        <?php } ?>
                        numeroCuenta.show();
                        $('#filaBeneficiario').show();
                        $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de Servicio o Cuenta Contrato:');
                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');

                        <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                            if (loadpage) {
                                nombreCampo1.find('input').val('');
                                $('#AfiliacionNumeroCuenta').val('');
                                $('#AfiliacionMontoMaximo').val('');
                            }
                        <?php } ?>

                        montoMaximo.show();
                        $('#AfiliacionCodigoTelefono').attr('style', 'display:inline');
                        $('#AfiliacionNumeroCuenta').attr('minlength', '07');
                        $('#AfiliacionNumeroCuenta').attr('maxlength', '07');
                        $('#AfiliacionNumeroCuenta').css("width", "150px");
                        recarga_minima = <?php echo $minimo_digitel_php ?>;
                        $('#recarga_minima').val(recarga_minima);
                        recarga_maxima = <?php echo $maximo_digitel_php ?>;
                        $('#recarga_maxima').val(recarga_maxima);
                        acomoda_tipo_servicio();
                        break;
                }

                var modalidad = $('#tipo').val();
                if (modalidad == 's') {
                    montoMaximo.hide();
                } else {
                    montoMaximo.show();
                }

                break;





            //------------------------------------------------------------------------               

            case 'st': //Movistar
                switch (servicios) {
                    case 'rtm':
                    case 'rim':
                        <?php foreach ($prefijos_telefonicos as $key) {
                            if ($key['PrefijosTelefonicos']['tipo_operacion'] == 'st') {
                                ?>
                                $('#AfiliacionCodigoTelefono').append(
                                    $('<option>', {
                                        'value': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>',
                                        'text': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>'
                                    })
                                );
                            <?php
                            }
                        }
                        ?>
                        <?php if (isset($this->data['Afiliacion']['codigo_telefono']) && !empty($this->data['Afiliacion']['codigo_telefono'])) { ?>
                            $("#AfiliacionCodigoTelefono").val('<?php echo $this->data['Afiliacion']['codigo_telefono'] ?>');
                        <?php } ?>
                        numeroCuenta.show();
                        $('#filaBeneficiario').show();
                        $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de Servicio o Cuenta Contrato:');
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');
                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');

                        <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                            if (loadpage) {
                                nombreCampo1.find('input').val('');
                                $('#AfiliacionNumeroCuenta').val('');
                                $('#AfiliacionMontoMaximo').val('');
                            }
                        <?php } ?>

                        montoMaximo.show();
                        $('#AfiliacionCodigoTelefono').attr('style', 'display:inline');
                        $('#AfiliacionNumeroCuenta').attr('minlength', '07');
                        $('#AfiliacionNumeroCuenta').attr('maxlength', '07');
                        $('#AfiliacionNumeroCuenta').css("width", "150px");
                        acomoda_tipo_servicio();
                        break;
                    case 'rtf':
                        <?php foreach ($prefijos_telefonicos as $key) {
                            if ($key['PrefijosTelefonicos']['tipo_operacion'] == 'all') {
                                ?>
                                $('#AfiliacionCodigoTelefono').append(
                                    $('<option>', {
                                        'value': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>',
                                        'text': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>'
                                    })
                                );
                            <?php
                            }
                        }
                        ?>
                        <?php if (isset($this->data['Afiliacion']['codigo_telefono']) && !empty($this->data['Afiliacion']['codigo_telefono'])) { ?>
                            $("#AfiliacionCodigoTelefono").val('<?php echo $this->data['Afiliacion']['codigo_telefono'] ?>');
                        <?php } ?>

                        numeroCuenta.show();
                        $('#filaBeneficiario').show();
                        $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de Servicio o Cuenta Contrato:');
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');
                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');

                        <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                            if (loadpage) {
                                nombreCampo1.find('input').val('');
                                $('#AfiliacionNumeroCuenta').val('');
                                $('#AfiliacionMontoMaximo').val('');
                            }
                        <?php } ?>

                        montoMaximo.show();
                        $('#AfiliacionCodigoTelefono').attr('style', 'display:inline');
                        $('#AfiliacionNumeroCuenta').attr('minlength', '07');
                        $('#AfiliacionNumeroCuenta').attr('maxlength', '07');
                        $('#AfiliacionNumeroCuenta').css("width", "150px");
                        acomoda_tipo_servicio();
                        break;
                    case 'stf':
                    case 'stm':
                    case 'sim':
                    case 'stv':
                    case 'rtv':
                        $('#filaBeneficiario').show();
                        $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de Servicio o Cuenta Contrato:');
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');
                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');

                        <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                            if (loadpage) {
                                nombreCampo1.find('input').val('');
                                $('#AfiliacionNumeroCuenta').val('');
                                $('#AfiliacionMontoMaximo').val('');
                            }
                        <?php } ?>
                        numeroCuenta.show();
                        $('#AfiliacionCodigoTelefono').attr('style', 'display:none');
                        $('#AfiliacionNumeroCuenta').attr('minlength', '1');
                        $('#AfiliacionNumeroCuenta').attr('maxlength', '20');
                        $('#AfiliacionNumeroCuenta').css("width", "");
                        montoMaximo.show();
                        acomoda_tipo_servicio();
                        break;
                }
                recarga_minima = <?php echo $minimo_movistar_php ?>;
                $('#recarga_minima').val(recarga_minima);
                recarga_maxima = <?php echo $maximo_movistar_php ?>;
                $('#recarga_maxima').val(recarga_maxima);
                $('#lim_ob').val(recarga_maxima);
                break;
            case 'sm':    //MOVILNET

                switch (servicios) {
                    case 'stm':
                    case 'rtm':
                    case 'sim':
                    case 'rim':

                        <?php foreach ($prefijos_telefonicos as $key) {
                            if ($key['PrefijosTelefonicos']['tipo_operacion'] == 'sm') {
                                ?>
                                $('#AfiliacionCodigoTelefono').append(
                                    $('<option>', {
                                        'value': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>',
                                        'text': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>'
                                    })
                                );
                            <?php
                            }
                        }
                        ?>
                        <?php if (isset($this->data['Afiliacion']['codigo_telefono']) && !empty($this->data['Afiliacion']['codigo_telefono'])) { ?>
                            $("#AfiliacionCodigoTelefono").val('<?php echo $this->data['Afiliacion']['codigo_telefono'] ?>');
                        <?php } ?>
                        
                        numeroCuenta.show();
                        $('#filaBeneficiario').show();

                        if(tipoServicio == 'r'){
                            $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de L&iacute;nea:');
                        }else{
                            $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de Contrato:');  
                        }

                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');

                        <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                            if (loadpage) {
                                nombreCampo1.find('input').val('');
                                $('#AfiliacionNumeroCuenta').val('');
                                $('#AfiliacionMontoMaximo').val('');
                            }
                        <?php } ?>

                        if(tipoServicio == 'r'){                          
                            $('#AfiliacionCodigoTelefono').attr('style', ' display:inline');
                            $('#AfiliacionNumeroCuenta').attr('minlength', '07');
                            $('#AfiliacionNumeroCuenta').attr('maxlength', '07');

                            recarga_minima = <?php echo $minimo_movilnet_php ?>;
                            $('#recarga_minima').val(recarga_minima);
                            recarga_maxima = <?php echo $maximo_movilnet_php ?>;
                            $('#recarga_maxima').val(recarga_maxima);
                            $('#lim_ob').val(recarga_maxima);







                        }else{
                            $('#AfiliacionCodigoTelefono').attr('style', ' display:none');
                            $('#AfiliacionNumeroCuenta').attr('minlength', '09');
                            $('#AfiliacionNumeroCuenta').attr('maxlength', '09');

                            recarga_minima = "";
                            $('#recarga_minima').val(recarga_minima);
                            recarga_maxima = "";
                            $('#recarga_maxima').val(recarga_maxima);

                            

                        }    
                        $('#AfiliacionNumeroCuenta').css("width", "150px");

                        //$('#AfiliacionNumeroCuenta').attr('style', 'display:inline');

                        //$('#AfiliacionNumeroCuenta').attr('minlength', '09');
                        //$('#AfiliacionNumeroCuenta').attr('maxlength', '09');
                        //$('#AfiliacionNumeroCuenta').css("width", "150px");
                        //$('#AfiliacionNumeroCuenta').css("width", "150px");

                        //recarga_minima = <?php // echo $minimo_movilnet_php ?>;
                        //$('#recarga_minima').val(recarga_minima);
                        //recarga_maxima = <?php //echo $maximo_movilnet_php ?>;
                        //$('#recarga_maxima').val(recarga_maxima);


                        acomoda_tipo_servicio();
                    break;
                } 


                var modalidad = $('#tipo').val();
                if (modalidad == 's') {
                    montoMaximo.hide();
                } else {
                    montoMaximo.show();
                }

                break;  
               

            /////////Simple TV
            case 'sv': //Simple TV
                switch (servicios) {
                    case 'rtc':   //Telev. cable 

                        $('#AfiliacionCodigoTelefono').hide();
                        numeroCuenta.show();
                        $('#filaBeneficiario').show();
                        $('.labelBeneficiario').html('<span class="requerido">* </span>N&uacute;mero de Tarjeta de Acceso:');
                        nombreCampo1.hide();
                        nombreCampo1.find('input').attr('disabled', 'disabled');
                        nombreCampo.show();
                        nombreCampo.find('input').removeAttr('disabled');

                        <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                            if (loadpage) {
                                nombreCampo1.find('input').val('');
                                $('#AfiliacionNumeroCuenta').val('');
                                $('#AfiliacionMontoMaximo').val('');
                            }
                        <?php } ?>

                        montoMaximo.show();
                        $('#AfiliacionNumeroCuenta').attr('minlength', '12');
                        $('#AfiliacionNumeroCuenta').attr('maxlength', '12');
                        $('#AfiliacionNumeroCuenta').css("width", "150px");

                        <?php
                        if ($modificar == 1) {
                            ?>
                            $('#AfiliacionNumeroCuenta').attr('readonly', 'readonly');
                        <?php
                        }
                        ?>

                        acomoda_tipo_servicio();
                        recarga_minima = <?php echo _p('recarga_minima_simple_tv') ?>;
                        $('#recarga_minima').val(recarga_minima);
                        recarga_maxima = <?php echo _p('recarga_maxima_simple_tv') ?>;
                        $('#recarga_maxima').val(recarga_maxima);

                        break;
                }


                break;




            /////////Fin Simple TV

            default:
                recarga_minima = <?php echo _p('recarga_minima') ?>;
                $('#recarga_minima').val(recarga_minima);
                recarga_maxima = <?php echo _p('recarga_maxima') ?>;
                $('#recarga_maxima').val(recarga_maxima);
        }
    }

    //Carga los campos segun el destino para transferencias otros bancos
    function acomoda_destino() {

        var subtipo = $('#subtipo').val();
        var destino = $('#destino').val();
        var email = $('#AfiliacionEmail').parentsUntil('table').filter('tr');
        var cedula = $('#AfiliacionTipoCedula').parentsUntil('table').filter('tr');
        var nombreCampo = $('#nombre');
        var nombreCampo1 = $('#nombreP');
        var numeroCuenta = $('#AfiliacionNumeroCuenta').parentsUntil('table').filter('tr');
        var banco = $('#AfiliacionBanco').parentsUntil('table').filter('tr');
        var codigoTelefono = $('#AfiliacionCodigoTelefonoMovil').parentsUntil('table').filter('tr');
        var numeroTelefono = $('#AfiliacionTelefono').parentsUntil('table').filter('tr');
        var montoMaximo = $('#AfiliacionMontoMaximo').parentsUntil('table').filter('tr');

        if (destino != '') {
            nombreCampo.show();
            nombreCampo.find('input')
                .removeAttr('disabled');

            <?php if (empty($this->data['Afiliacion']['id'])) { ?>
                if (loadpage) {
                    nombreCampo.find('input').val('');
                    email.find('input').val('');
                    cedula.find('input').val('');
                    numeroTelefono.find('input').val('');
                    $('#AfiliacionNumeroCuenta').val('');
                    $('#AfiliacionMontoMaximo').val('');
                }
            <?php } ?>

            nombreCampo1.hide();
            nombreCampo1.find('input').attr('disabled', 'disabled');
            $('.labelBeneficiario').html('<span class="requerido">* </span>Cuenta del Beneficiario:');
            $('#AfiliacionNumeroCuenta')
                .attr('maxlength', '20')
                .attr('minlength', '20');
            $('#AfiliacionCodigoTelefono')
                .attr('style', 'display:none');
            if (subtipo != 't' && subtipo != 'ob') {
                $('#filaCedula').show();
                email.show();
            } else {
                $('#filaCedula').hide();
            }

            if (destino == 't') {
                banco.show();
                codigoTelefono.show();
                numeroTelefono.show();
                numeroCuenta.hide();
            } else {
                banco.hide();
                codigoTelefono.hide();
                numeroTelefono.hide();
                numeroCuenta.show();
            }
        } else {
            email.hide();
            $('#filaCedula').hide();
            $('#filaBeneficiario').hide();
            montoMaximo.hide();
            numeroCuenta.hide();
            codigoTelefono.hide();
            numeroTelefono.hide();
            banco.hide();
            $('#tipoServicio').hide();
        }
    }
    function blanqueoCampos() {
        $('#AfiliacionNumeroCuenta').val('');
        $('#AfiliacionTelefono').val('');
        // $('#AfiliacionMontoMaximo').val('');
        $('#AfiliacionAlias').val('');
        $('#AfiliacionCedula').val('');
        $('#AfiliacionEmail').val('');
        $('#servicios').val('');
        $('#destino').val('');
        $('div.error-message').remove();
        $('label.error').remove();
        if ($('#tipo').val() != 'i' || ($('#tipo').val() == 'i' && $('#subtipo').val() == 't')) {
            $('#nombreP').html('');
        }
        $('#cedula').val('');
        $("#cedula").off();

    }





    $(function () {
        var subtipo = $('#subtipo').val();
        var tipo = $('#tipo').val();
        var servicio = $('#servicio').val();

        acomoda_tipo_afiliacion();
        acomoda_prefijos();

        if (tipo == 't' && subtipo != 't') {
            acomoda_destino();
        }

        $('#tipo').change(function () {
            blanqueoCampos();
            acomoda_tipo_afiliacion();

            $('#aceptar').show();
            $('#error').hide();


        });

        $('#subtipo').change(function () {
            blanqueoCampos();
            acomoda_tipo_operacion();

            sTipo = $('#subtipo').val();
            //montoMaximo.show(); 
            $('#error').hide();
            if (sTipo == 'ht') {
                $('#nota').hide();
            } else {
                $('#nota').show();
            }



        });

        $('#destino').change(function () {

            acomoda_destino();
        });

        $('#servicios').change(function () {
            $('#nombreP').html('');
            acomoda_prefijos();
        });

        // evitar que se oculte el error cuando es un pago de servicio
        $('#aceptar').click(function () {
            if (!servicioValido && subtipo != 'sd') $('#error_servicio').show();
        });

        $('#AfiliacionNombreBeneficiario').blur(function () { $(this).valid(); });

        // para la validacion
        $('#AfiliacionCrearForm').validate({

            submitHandler: function (form) {

                if (servicioValido) {
                    $.each($('tr td div.error-message'), function () {
                        if ($(this).siblings().size() == 0) {
                            $(this).parent().parent().remove();
                        }
                    });

                    if (!ajaxPendiente) form.submit();
                }
            },
            rules: {
                'data[Afiliacion][tipo_afiliacion]': {
                    required: true
                },
                'data[Afiliacion][tipo_operacion]': {
                    required: true
                },
                'data[Afiliacion][destino]': {
                    required: true
                },
                'data[Afiliacion][tipo_servicio]': {
                    required: true
                },
                'data[Afiliacion][alias]': {
                    required: true,
                    alphaNumericSpace: true
                },
                'data[Afiliacion][numero_cuenta]': {
                    required: true,
                    digits: true,
                    creditcard: {
                        depends: function (e) {
                            return $('#tipo').val() == 'p';
                        }
                    },
                    cuentaActivo: {
                        depends: function (e) {
                            return $('#tipo').val() == 't' && $('#subtipo').val() == 't';
                        }
                    },
                    inarray: {
                        param: <?php echo json_encode((count($bancos)) ? array_values(array_diff(array_keys($bancos), array('0171'))) : array()) ?>,
                        depends: function (e) {
                            return $('#tipo').val() == 't' && ($('#subtipo').val() == 'tob' || $('#subtipo').val() == 'ob');
                        }
                    },
                    //Se deconecto la validación del numero ya que no la efectua y da un error
                    cantv: {
                        depends: function (e) {

                            return ($('#tipo').val() == 's' || $('#tipo').val() == 'r') && ($('#subtipo').val() == 'sc');
                        }
                    },

                    //movilnet: {
                    //    depends: function (e) {
                    //        return ($('#tipo').val() == 's' || $('#tipo').val() //== 'r') && ($('#subtipo').val() == 'sm');
                    //    }
                    //},
                    seniat: {
                        depends: function (e) {
                            return ($('#tipo').val() == 'i');
                        }
                    }
                },
                digitel: {

                    depends: function (e) {
                        return ($('#tipo').val() == 'r') && ($('#subtipo').val() == 'sd');
                    }


                },
                inter: {
                    depends: function (e) {
                        return ($('#tipo').val() == 's') && ($('#subtipo').val() == 'si');
                    }


                },
                'data[Afiliacion][nombre_beneficiario]': {
                    required: true,
                    alphaNumericSpace: true
                },
                'data[Afiliacion][cedula]': {
                    required: true,
                    digits: {
                        depends: function (e) {
                            return ($('#AfiliacionTipoCedulaRif').val() == 'V' || $('#AfiliacionTipoCedulaRif').val() == 'E' ||
                                $('#AfiliacionTipoCedulaRif').val() == 'J' || $('#AfiliacionTipoCedulaRif').val() == 'G');
                        }
                    },
                    alphanumeric: {
                        depends: function (e) {
                            return $('#AfiliacionTipoCedulaRif').val() == 'P';
                        }
                    }
                },
                'data[Afiliacion][tarjetatpc]': {
                    required: true,
                },

                'data[Afiliacion][email]': {
                    required: true,
                    email: true
                },
                'data[Afiliacion][monto_maximo]': {
                    numberVE: true,
                    montoMaximo: '#monto_limite',
                    required: true,
                    montoMinimo: function (e) {

                        if ($('#tipo').val() == 'r' || $('#tipo').val() == 's') return '#recarga_minima';
                    },
                    montoMaximo2: function (e) {
                        if ($('#tipo').val() == 'r' || $('#tipo').val() == 's') return '#recarga_maxima';
                    }
                    // montoMultiplo: function (e) {
                    //     if ($('#tipo').val() == 'r') {

                    //         if (!$('#subtipo').val() === 'si') {

                    //             return '#multiplo';
                    //         }


                    //     }
                    // }
                }
            },
            messages: {
                'data[Afiliacion][tipo_afiliacion]': {
                    required: <?php echo _m('campo.requerido', array('el tipo de afiliaci&oacute;n'), true) ?>
                },
                'data[Afiliacion][tipo_operacion]': {
                    required: <?php echo _m('campo.requerido', array('el tipo de operaci&oacute;n'), true) ?>
                },
                'data[Afiliacion][tipo_servicio]': {
                    required: <?php echo _m('campo.requerido', array('el tipo de servicio'), true) ?>
                },
                'data[Afiliacion][destino]': {
                    required: <?php echo _m('campo.requerido', array('el destino'), true) ?>
                },
                'data[Afiliacion][alias]': {
                    required: <?php echo _m('campo.requerido', array('el alias de la afiliaci&oacute;n'), true) ?>,
                    alphaNumericSpace: <?php echo _m('alfanumerico.invalido', 'el alias', true) ?>
                },
                'data[Afiliacion][numero_cuenta]': {
                    required: <?php echo _m('campo.requerido', 'el campo solicitado', array('el banco'), true) ?>,
                    digits: <?php echo _m('formato.invalido', 'el campo solicitado', true) ?>,
                    cuentaActivo: <?php echo _m('formato.invalido', 'el n&uacute;mero de cuenta', true) ?>,
                    cantv: <?php echo _m('formato.servicio', null, true) ?>,
                    movilnet: <?php echo _m('formato.servicio', null, true) ?>,
                    digitel: <?php echo _m('formato.servicio', null, true) ?>,
                    cantvnet: <?php echo _m('formato.servicio', null, true) ?>
                },
                'data[Afiliacion][nombre_beneficiario]': {
                    required: <?php echo _m('campo.requerido', 'el nombre', true) ?>,
                    alphaNumericSpace: <?php echo _m('alfanumerico.invalido', 'el nombre', true) ?>
                },
                'data[Afiliacion][cedula]': {
                    required: <?php echo _m('campo.requerido', 'la c&eacute;dula/rif', true) ?>,
                    digits: <?php echo _m('formato.invalido', 'la c&eacute;dula/rif', true) ?>,
                    alphanumeric: <?php echo _m('alfanumerico.invalido', 'la c&eacute;dula/rif', true) ?>
                },
                'data[Afiliacion][tarjetatpc]': {
                    required: <?php echo _m('campo.requerido', 'el email del beneficiario', true) ?>,
                    email: <?php echo _m('correo.invalido', 'el email del beneficiario', true) ?>
                },
                'data[Afiliacion][email]': {
                    required: <?php echo _m('campo.requerido', 'el email del beneficiario', true) ?>,
                    email: <?php echo _m('correo.invalido', 'el email del beneficiario', true) ?>
                },
                'data[Afiliacion][monto_maximo]': {

                    required: <?php echo _m('campo.requerido', 'el monto', true) ?>,
                    numberVE: <?php echo _m('formato.invalido', 'el monto m&aacute;ximo', true) ?>,
                    montoMinimo: function (e) {
                        if ($('#subtipo').val() === 'sd') {
                            return <?php echo _m('monto.recarga.invalido', array(' ', formatea_monto_fromWs($minimo_digitel_php), formatea_monto_fromWs($maximo_digitel_php), '204'), true) ?>
                        } else if ($('#subtipo').val() === 'sm') {
                            return <?php echo _m('monto.recarga.invalido', array(' ', formatea_monto_fromWs($minimo_movilnet_php ), formatea_monto_fromWs( $maximo_movilnet_php), '204'), true) ?>

                        } else if ($('#subtipo').val() === 'st') {
                            return <?php echo _m('monto.recarga.invalido', array(' ', formatea_monto_fromWs($minimo_movistar_php ), formatea_monto_fromWs( $maximo_movistar_php), '204'), true) ?>
                            //----------------creado nuevo para cantv
                        } else if ($('#subtipo').val() === 'sc') {
                            return <?php echo _m('monto.recarga.invalido', array(_p('multiplo.cantv'), formatea_monto_fromWs(_p('recarga_minima')), formatea_monto_fromWs(_p('recarga_maxima')), '204'), true) ?>
                            //----------------fin creado nuevo para cantv       

                            //--------Creado nuevo simple TV
                        } else if ($('#subtipo').val() === 'sv') {
                            if ($('#tipo').val() == 'r') {
                                return <?php echo _m('monto.invalido.simple.tv', array(formatea_monto_fromWs(_p('recarga_minima_simple_tv')), formatea_monto_fromWs(_p('recarga_maxima_simple_tv'))), true) ?>
                            }
                            //-------Fin simple Tv


                        } else if ($('#subtipo').val() === 'si') {
                            if ($('#tipo').val() == 's') {
                                return <?php echo _m('monto.invalido.inter', array(formatea_monto_fromWs(_p('recarga_minima_inter_post')), formatea_monto_fromWs(_p('recarga_maxima_inter_post'))), true) ?>
                            } else if ($('#tipo').val() == 'r') {
                                return <?php echo _m('monto.invalido.inter', array(formatea_monto_fromWs(_p('recarga_minima_inter_pre')), formatea_monto_fromWs(_p('recarga_maxima_inter_pre'))), true) ?>
                            }
                        } else {


                            return <?php echo _m('recarga.minima', formatea_monto_fromWs(_p('recarga_minima')), true) ?>
                        }
                    },
                    montoMaximo2: function (e) {

                        if ($('#subtipo').val() === 'sd') {
                            return <?php echo _m('monto.recarga.invalido', array(' ', formatea_monto_fromWs($minimo_digitel_php), formatea_monto_fromWs($maximo_digitel_php), '204'), true) ?>
                            //----------------creado nuevo para movilnet

                        } else if ($('#subtipo').val() === 'sm') {
                            return <?php echo _m('monto.recarga.invalido', array(' ', formatea_monto_fromWs($minimo_movilnet_php), formatea_monto_fromWs($maximo_movilnet_php), '204'), true) ?>
                            //----------------creado nuevo para cantv


                        } else if ($('#subtipo').val() === 'sc') {
                            return <?php echo _m('monto.recarga.invalido.cantv', array(_p('multiplo.cantv'), formatea_monto_fromWs(_p('recarga_minima_cantv')), formatea_monto_fromWs(_p('recarga_maxima_cantv')), '204'), true) ?>
                            //----------------fin creado nuevo para cantv  

                            //--------Creado nuevo simple TV
                        } else if ($('#subtipo').val() === 'sv') {
                            if ($('#tipo').val() == 'r') {
                                return <?php echo _m('monto.invalido.simple.tv', array(formatea_monto_fromWs(_p('recarga_minima_simple_tv')), formatea_monto_fromWs(_p('recarga_maxima_simple_tv'))), true) ?>
                            }
                            //-------Fin simple Tv




                        } else if ($('#subtipo').val() === 'st') {
                            return <?php echo _m('monto.recarga.invalido', array(' ', formatea_monto_fromWs($minimo_movistar_php), formatea_monto_fromWs($maximo_movistar_php), '204'), true) ?>
                        } else if ($('#subtipo').val() === 'si') {
                            if ($('#tipo').val() == 's') {
                                return <?php echo _m('monto.invalido.inter', array(formatea_monto_fromWs(_p('recarga_minima_inter_post')), formatea_monto_fromWs(_p('recarga_maxima_inter_post'))), true) ?>
                            } else if ($('#tipo').val() == 'r') {
                                return <?php echo _m('monto.invalido.inter', array(formatea_monto_fromWs(_p('recarga_minima_inter_pre')), formatea_monto_fromWs(_p('recarga_maxima_inter_pre'))), true) ?>
                            }
                        } else {
                            return <?php echo _m('recarga.maxima', formatea_monto_fromWs(_p('recarga_maxima')), true) ?>
                        }
                    },
                    montoMaximo: <?php echo _m('monto.limite.afiliacion', NULL, true) ?>
                    // montoMultiplo: function (e) {
                    //     if ($('#subtipo').val() === 'sd') {
                    //         return <?php //echo _m('monto.recarga.invalido', array(_p('multiplo.digitel'), formatea_monto_fromWs($minimo_digitel_php), formatea_monto_fromWs($maximo_digitel_php), '204'), true) ?>
                    //     } else if ($('#subtipo').val() === 'st') {
                    //         return <?php //echo _m('monto.recarga.invalido', array(_p('multiplo.movistar'), formatea_monto_fromWs( $minimo_movistar_php), formatea_monto_fromWs($maximo_movistar_php), '204'), true) ?>
                    //     }
                    // }
                }

            }
        });

        //  $('#AfiliacionNumeroCuenta').blur(function(){
        //      if(($('#tipo').val()=='s' || $('#tipo').val()=='r') && ($('#subtipo').val()!='sd')){
        //          if($(this).val()){
        //              obtener_nombre();
        //          }
        //      }
        //  });

        $('#AfiliacionNumeroCuenta').focus(function () {
            $('#nombreP').html('');
        });

        existeProducto = false;

        loadpage = true;

    });


    function listarTarjeta() {
        var cedula = $("#cedula").val();
        $('#tarjetatpc').empty();

        if (cedula.trim() != '') {


            $('#errorST').css('display', 'none');

            $.ajax({
                type: "GET",
                url: '<?php echo Router::url("/tarjeta_prepagadas/listarTarjeta/"); ?>' + cedula,
                dataType: "json",
                cache: false,
                beforeSend: function () {
                    ajaxPendiente = true;
                    $('#waitTPC').show();
                },
                success: function (source) {
                    if (source) {
                        ajaxPendiente = false;

                        $('#wait').hide();
                        var flag = false;
                        var swError = 0;
                        var swMensaje = 0;
                        var swLista = 0;

                        var cadenaMensaje = 'Estimado cliente, ha ocurrido un error inesperado. Por favor intente de nuevo mas tarde.';

                        $.each(source, function (index, value) {

                            if (index == 'error') {
                                if (value == '0') {
                                    swError = 1;
                                }
                            }

                            if (swError == 1) {
                                if (index == 'fisica') {
                                    if (value != "") {
                                        swLista = 1;
                                        nroTarjeta = value;
                                        var desTarjeta = value.substring(0, 4) + "****" + value.substring(12, 16) + " [FISICA]";
                                        $('#tarjetatpc').append(
                                            $('<option>', {
                                                'value': nroTarjeta,
                                                'text': desTarjeta
                                            })
                                        );
                                    }
                                }

                                if (index == 'virtual') {
                                    if (value != "") {
                                        swLista = 1;
                                        nroTarjeta = value;
                                        var desTarjeta = value.substring(0, 4) + "****" + value.substring(12, 16) + " [VIRTUAL]";
                                        $('#tarjetatpc').append(
                                            $('<option>', {
                                                'value': nroTarjeta,
                                                'text': desTarjeta
                                            })
                                        );
                                    }
                                }
                            }
                        });
                        $('#waitTPC').hide();

                    } else {
                        var cadenaMensaje = 'Estimado cliente, ha ocurrido un error inesperado. Por favor intente de nuevo mas tarde.';
                        $('#errorST').val('cadenaMensaje');
                        $('#errorST').css('display', 'inline');
                        $('#waitTPC').hide();
                    }
                },
                error: function (source) {
                    var cadenaMensaje = "<?php echo _m('tcp.nosolicitada.recarga.beneficiario'); ?>";
                    $('#errorST').html(cadenaMensaje);
                    $('#errorST').css('display', 'inline');
                    $('#waitTPC').hide();
                }
            });
        }



    }

</script>