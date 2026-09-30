<?php

/**
 * Resultado de mesa de cambio compra
 **/
?>
<div>
    <h2 class="user">Activar Tarjeta de credito</h2>
    <div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de informacion</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmacion</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado de operacion</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
        <thead>

            <?php
            //
            $error = 0;
            if ($error == 0) {
            ?>
                <tr>
                    <td colspan="2" class="resumen_paginacion">
                        <p class="exito">
                            Resultado de activacion</p>

                            <?php 
                            if($soapResponse['codigo'] !== '00'){
                                echo '<p style="color:red;"> Estimado usuario, hay un error en los datos, por favor verificar y volver a intentar. </p>';
                            }
                         ?>   
                    </td>
                </tr>

        </thead>
        <tbody>
            <tr>
                <td class="etiqueta">Cedula:</td>
                <td><?php echo $usuario['tipo_cedula'] . desencriptar($usuario['cedula']) ?></td>
            </tr>

            <tr>
                <td class="etiqueta">C&oacutedigo de tarjeta:</td>
                <td><?php echo $all_user_tdc_request['cvc'] ?></td>
            </tr>

            <tr>
                <td class="etiqueta">Fecha</td>
                <td><?php echo $all_user_tdc_request['fecha'] ?></td>
            </tr>

            
            <tr>
                <td class="etiqueta">Numero de tarjeta:</td>
                <td><?php echo $all_user_tdc_request['tarjeta'] ?></td>
            </tr>

            <tr>
                <td class="etiqueta">Estatus:</td>
                <td><?php 

                    if($soapResponse['codigo'] !== '00'){
                        echo '<strong style="color: red;">' . $soapResponse['mensaje'] . '<strong>';
                    }else{
                        '<strong>' . $soapResponse['mensaje'] . '<strong>';
                    }

                 ?></td>
            </tr>


            <tr>
                <td class="etiqueta">Fecha activaci&oacuten:</td> 
                <td><?php echo date('d/m/Y') ?></td>
            </tr>
            <!-- <?php if (!empty($referencia)) { ?>
                <tr>
                    <td class="etiqueta">Referencia:</td>
                    <td><?php echo $referencia ?></td>
                </tr>
            <?php } ?> -->
            <tr>
                <td colspan="2" class="botonera">

                    <?php

                    echo $html->link(
                        'Imprimir',
                        'javascript:print();void(0);',
                        array(
                            'class' => 'button'
                        )
                    );

                    ?>
                </td>
            </tr>
            <tr>
            <td colspan="2" class="botonera">
                <form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjeta_credito_credicard/tdc_activacion') ?>">
                    <input type="submit" class="button" value="Volver" />
                </form>
            </td>
        </tr>
        </tbody>
    </table>
    <table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
    <?php
            } else {
    ?>
        <thead>
            <tr>

                <td class="resumen_paginacion texto_centro">
                    <div class="error-message"><?php echo $descripcion; ?></div>
                </td>
            </tr>
        </thead>
    <?php
            }
            if ($error != 0) {
    ?>
        <tr>
            <td colspan="2" class="botonera">
                <form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjeta_prepagadas/activacion_confirmacion') ?>">
                    <input type="submit" class="button" value="Volver" />
                </form>
            </td>
        </tr>
    <?php } ?>
    </table>
</div>