<?php
/**
 * Resultado Activacion Tarjeta Prepagada
**/
?>
<div>
<h2 class="user">Pagos Corpoelec</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Solicitud de Factura</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>
<div class="paso_nro">5</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>
<div class="contenedor_formulario">

    
<?php 
    echo $form->create('PagoCorpoelec',
        array(
            'url'=> array(
                'controller'=>'pagos_corpoelec',
                'action'=>'pago'
            ),
            'inputDefaults'=>array(
                'div'=>false,
                'label'=>'etiqueta',
                'before'=>'',
                'between'=>'',
                'after'=>'',
                'error'=>false
            )
        )
    );
 
        echo '<input type="hidden" id="none" />';

        echo '';

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

        echo '<tr><td style="width:180px"></td><td style="width:270px"></td></tr>';
    
        echo '<tr><td colspan="2">A continuaci&oacuten proceda a ingresar los datos requeridos.</td></tr>';
        echo '<tr><td colspan="2"></td></tr>';

        
        function FormatoMoneda($numero){
            $decimales = substr($numero, -2);
            $entero = substr($numero, 0, -2);
            $numeroFormateado = number_format($entero, 0, '', '.');
            $resultadoFinal = $numeroFormateado . '.' . $decimales;
            return $resultadoFinal;
        }

?>
        <tr>

            <td class="etiqueta">Cuenta a Debitar:</td>
            <td><?php echo enmascara($datos['cuentaDebitar']); ?></td>
        </tr>
    <?php if($datos['servicio'] == "1"): ?>
        <tr>
            <td class="etiqueta">C&eacute;dula del beneficiario:</td>
            <td><?php echo $datos['tipoCedula'] . "-" . $datos['nroCedula']; ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Nombre beneficiario:</td>
            <td><?php echo $datos['beneficiario']; ?></td>
        </tr>
        <tr>
            <td class="etiqueta">Nro de contrato:</td>
            <td>
                <?php
                $inputContrato = array(
                    'label'=>false,
                    'type'=>'select',
                    'style'=>'width:200px',
                    'options'=>$contratos
                );
                

                if(count($contratos) > 0) $inputContrato['empty'] = 'Seleccione un contrato';
                    
                echo $form->input('contrato',$inputContrato);

                if ($form->isFieldError('contrato')){
                    echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('contrato',
                        array(
                            'requerido'=>_m('seleccion.requerida',array('Debe seleccionar un contrato'))
                        )
                    ).'</td></tr>';
                }
            
                ?>
            </td>
        </tr>
        <tr>
            <td class="etiqueta">Grupo:</td>
            <td><span id="Grupo"></span></td>
        </tr>
        <tr>
            <td class="etiqueta">Fecha de emisi&oacute;n: </td>
            <td><?php echo $datos['fecha']; ?></td>
        </tr>
        <tr>  
            <td class="etiqueta">Factura a Cancelar: </td>
            <td class="description">
                <?php 
                $input_factura = array(
                'label'=>false,
                'type'=>'select',
                'style'=>'width:200px',
                'options'=>array()
                );
        
                $input_factura['empty'] = 'Seleccione';
        
                echo $form->input('factura',$input_factura);

                if ($form->isFieldError('factura')){
                    echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('factura',
                        array(
                            'requerido'=>_m('seleccion.requerida',array('la factura a cancelar'))
                        )
                    ).'</td></tr>';
                }
                ?>
            </td> 
        </tr> 
    <?php elseif($datos['servicio'] == "2"):?>
        <tr>
            <td class="etiqueta">Numero de contrato: </td>
            <td><?php echo $datos['nroContrato']; ?></td>
        </tr>
        <tr>
            <td class="etiqueta" colspan="2">Energia Electrica: </td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="75%" style="margin: 0; padding: 0;" border="0">
                    <?php
                        $options = array(
                            'Deuda Vencida' => FormatoMoneda($datos["response"]['Deuda_Energia_Vencida']),
                            'Deuda Vigente' => FormatoMoneda($datos["response"]['Deuda_Energia_Vigente']),
                            'Deuda Total'   => FormatoMoneda($datos["response"]['Deuda_Energia_Total']),
                        );

                        foreach ($options as $key => $value) {
                    ?>
                        <tr>
                            <td width="5%">
                                <?php if($key == "Deuda Vigente" && $options["Deuda Vencida"] > 0): ?>
                                    <input 
                                        type="radio"
                                        name="data[PagoCorpoelec][deudaPagar]"
                                        value="<?php echo $key?>"
                                        onclick="TipoTransaccion('Energia')"
                                        autocomplete="off"
                                        disabled="disabled"
                                    />
                                <?php else: ?>
                                    <input 
                                        type="radio"
                                        name="data[PagoCorpoelec][deudaPagar]"
                                        value="<?php echo $key?>"
                                        onclick="TipoTransaccion('Energia')"
                                        autocomplete="off"
                                        <?php 
                                            echo $value=='0.00' ? 'disabled="disabled"' : '' 
                                        ?>
                                    />
                                <?php endif; ?>
                            </td>
                            <td class='etiqueta' width='35%'>
                            <span><?php echo $key; ?>: </span>
                            </td>
                            <td class='etiqueta' width='15%'>
                            <span><?php echo $value; ?> </span>
                            </td>
                        </tr>
                    <?php }?>
                </table>
            </td>
        </tr>
        <tr>
            <td class="etiqueta" colspan="2">Aseo y Relleno Sanitario: </td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="75%" style="margin: 0; padding: 0;" border="0">
                    <?php
                        $options = array(
                            'Deuda Vencida' => FormatoMoneda($datos["response"]['Deuda_Aseo+Relleno_Vencida']),
                            'Deuda Vigente' => FormatoMoneda($datos["response"]['Deuda_Aseo+Relleno_Vigente']),
                            'Deuda Total'   => FormatoMoneda($datos["response"]['Deuda_Aseo+Relleno_Total']),
                        );

                        foreach ($options as $key => $value) {
                    ?>
                        <tr>
                            <td width="5%">
                                <?php if($key == "Deuda Vigente" && $options["Deuda Vencida"] > 0): ?>
                                    <input 
                                        type="radio"
                                        name="data[PagoCorpoelec][deudaPagar]"
                                        value="<?php echo $key?>"
                                        onclick="TipoTransaccion('Aseo+Relleno')"
                                        autocomplete="off"
                                        disabled="disabled"
                                    />
                                <?php else: ?>
                                    <input 
                                        type="radio"
                                        name="data[PagoCorpoelec][deudaPagar]"
                                        value="<?php echo $key?>"
                                        onclick="TipoTransaccion('Aseo+Relleno')"
                                        autocomplete="off"
                                        <?php 
                                            echo $value=='0.00' ? 'disabled="disabled"' : '' 
                                        ?>
                                    />
                                <?php endif; ?>
                            </td>
                            <td class='etiqueta' width='35%'>
                            <span><?php echo $key; ?>: </span>
                            </td>
                            <td class='etiqueta' width='15%'>
                            <span><?php echo $value; ?> </span>
                            </td>
                        </tr>
                    <?php }?>
                </table>
            </td>
        </tr>
        <tr>
            <td class="etiqueta" colspan="2">Deuda Total: </td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="75%" style="margin: 0; padding: 0;" border="0">
                    <?php
                        $options = array(
                            'Deuda Vencida' => FormatoMoneda($datos["response"]['Deuda_Vencida']),
                            'Deuda Vigente' => FormatoMoneda($datos["response"]['Deuda_Vigente']),
                            'Deuda Total'   => FormatoMoneda($datos["response"]['Deuda_Total']),
                        );

                        foreach ($options as $key => $value) {
                    ?>
                        <tr>
                            <td width="5%">
                                <input 
                                    type="radio"
                                    name="data[PagoCorpoelec][deudaPagar]"
                                    value="<?php echo $key?>"
                                    onclick="TipoTransaccion('Total')"
                                    autocomplete="off"
                                    <?php 
                                        echo $value=='0.00' ? 'disabled="disabled"' : '' 
                                    ?>
                                />
                            </td>
                            <td class='etiqueta' width='35%'>
                            <span><?php echo $key; ?>: </span>
                            </td>
                            <td class='etiqueta' width='15%'>
                            <span><?php echo $value; ?> </span>
                            </td>
                        </tr>
                    <?php }?>
                </table>
            </td>
        </tr>
        <?php
            // Manejo de errores
            if ($form->isFieldError('deudaPagar')) {
                echo '<tr><td colspan="2" class="etiqueta">&nbsp;</td><td class="campo">' . 
                     $form->error('deudaPagar', array(
                         'requerido' => _m('seleccion.requerida', array('la factura a cancelar'))
                     )) . '</td></tr>';
            }
        ?>
        <tr>
            <td class="etiqueta">Fecha de emisi&oacute;n: </td>
            <td>
                <?php echo $datos['fecha']; ?>
            </td>
        </tr>          
    <?php endif;?>

    <input type="hidden" id="tipoServicio" name="data[PagoCorpoelec][tipoServicio]" value="" />
    <div id="fecha-error" style="color: red;"></div>
    
    <?php

        echo '<tr><td>&nbsp;&nbsp;</td><td class="submit">';

        echo $html->link(
            'Volver',
            '/pagos_corpoelec/datos',
            array(
                'class'=>'button',
                'id'=>'volver'
            )
        );

        echo '</td>&nbsp;<td class="submit">';

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
    $(document).ready(function(){
        var dataContratos = <?php echo json_encode($dataContratos); ?>;
        
        $("#PagoCorpoelecContrato").on('change', function(e) {
            var contratoSeleccionado = $(this).val();
            if(!contratoSeleccionado || !dataContratos[contratoSeleccionado]) return;
            
            $("span#Grupo").text(dataContratos[contratoSeleccionado].grupo);
            
            $("#PagoCorpoelecFactura").empty().append(
                $("<option>", {value: "", text: "Seleccione"})
            );
            
            var deudas = dataContratos[contratoSeleccionado].deudas;
            
            deudas.forEach(function(deuda) {
                $("#PagoCorpoelecFactura").append(
                    $("<option>", {
                        value: deuda.id + " - Bs. " + deuda.amount + " - " + deuda.concept,
                        text: "Bs. " + deuda.amount + " - " + deuda.concept
                    })
                );
            });
        });
    });
    function TipoTransaccion(servicio){
    $("#tipoServicio").val(servicio);
    }

    $(function(){
        
    $('#aceptar').click(function(){
        if(ajaxPendiente){
            return false;
        }
    });

    $('#PagoCorpoelecPagoForm').validate({
        rules:{
            'data[PagoCorpoelec][contrato]':{
                required:true
            },
            'data[PagoCorpoelec][factura]':{
                required:true
            },
            'data[PagoCorpoelec][deudaPagar]': {
                required: true
            }
        },  
        messages:{
            'data[PagoCorpoelec][contrato]':{
                required:<?php echo _m('seleccion.requerida',array('la factura a cancelar'),true) ?>
            },
            'data[PagoCorpoelec][factura]':{
                required:<?php echo _m('seleccion.requerida',array('la factura a cancelar'),true) ?>
            },
            'data[PagoCorpoelec][deudaPagar]': {
                required: <?php echo _m('seleccion.requerida',array('el monto a cancelar'),true) ?>
            }
        },
        errorPlacement: function(error, element) {
            if (element.attr("name") == "data[PagoCorpoelec][deudaPagar]") {
                // Mostrar mensaje de error debajo de la fecha de emisión
                $("#fecha-error").show();
                error.appendTo("#fecha-error");
            } else {
                // Comportamiento normal para otros campos
                error.insertAfter(element);
            }
        },
        submitHandler: function(form) {
            // Ocultar mensaje de error si el formulario es válido
            $("#fecha-error").hide();
            form.submit();
        }
    });
});
</script>