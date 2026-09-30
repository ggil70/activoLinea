<div>
<h2 class="user">Pago Corpoelec</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Solicitud de Factura</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>
<div class="paso_nro">5</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>
<style>
/* Agregar estos estilos para manejar correctamente los mensajes de error */
.error-message {
    display: block !important; /* Forzar display block */
    color: #ff0000;
    font-weight: bold;
    padding: 10px;
    text-align: center;
    margin: 10px 0;
}

#errorSeleccionAfiliado td {
    padding: 10px 0;
}
</style>
<div class="contenedor_formulario">

    <?php
    $usuario = $this->Session->read('usuarios.datos');

  
    echo $form->create('PagoCorpoelec',
        array(
            'url'=> array(
                'controller'=>'pagos_corpoelec',
                'action'=>'datos'
            ),
            'inputDefaults'=>array(
                'div'=>false,
                'label'=>false,
                'before'=>'<tr><td class="etiqueta">',
                'between'=>'</td><td class="campo">',
                'after'=>'</td></tr>',
                'error'=>false
            )
        )
    );
    
    echo '<input type="hidden" id="none" />';

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
    
    // La primera fila define la tabla con layout fixed
    echo '<tr><td style="width:180px"></td><td style="width:270px"></td></tr>';
    
    echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos requeridos.</td></tr>';
    echo '<tr><td colspan="2"></td></tr>';

?>
    
<?php
    $input_servicio = array(
        'label'=>'<span class="requerido">* </span>Servicio:',
        'type'=>'select',
        'options'=>array(
            ''=>'Seleccione una opcion',
            '1'=>'Cuenta Energia',
            '2'=>'Cuenta Energia + Aseo Relleno',
        ),
        'required'=>true
    );
   
    $input_debitar = array(
        'label'=>'<span class="requerido">* </span>Cuenta de Debito:',
        'type'=>'select',
        'options'=>$cuentas
    );



    echo $form->input(
        'servicio',
        $input_servicio
    );   
    
    if(count($cuentas) > 1) $input_debitar['empty'] = 'Seleccione';
    
    echo $form->input(
        'cuentaDebitar',
        $input_debitar
    );
    

    if ($form->isFieldError('cuentaDebitar')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaDebitar',
            array(
                'requerido'=>_m('seleccion.requerida',array('la cuenta de dÃ©bito'))
            )           
        ).'</td></tr>';
    }
    echo $form->input(
        'afiliado',
        array(
            'label'=>'<span class="requerido">* </span>',
            'type'=>'radio',
            'legend'=>false,
            'separator'=>'&nbsp;&nbsp;',
            'options'=> array(
                'a' => 'Afiliado',
                'n' => 'No afiliado'
            ),
            'before'=>'<tr><td class="etiqueta" style="text-align:right;"><span class="requerido">* </span>'
        )
    );

    echo $form->input(
        'tipoCedula',
        array(
            'after'=>false,
            'label'=>'<span style="display:none" id="labelCedula"><span class="requerido" >* </span>C&eacute;dula del Beneficiario:</span>',
            'type'=>'select',
            'class'=>'tipoRif',
            'style'=>'display:none',
            'options'=>array('V'=>'V','E'=>'E','P'=>'P','J'=>'J','G'=>'G'),
            'required'=>true
        )
    );

    echo $form->input(
        'nroCedula',
        array(
            'before'=>false,
            'between'=>false,
            'label'=>false,
            'type'=>'text',
            'style'=>"width:50%;display:none",
            'class'=>'nroRif',
            'required'=>true
        )
    );

    echo $form->input(
        'nroContrato',
        array(
            'label'=>'<span style="display:none" id="labelContrato"><span class="requerido" >* </span>Nro de contrato:</span>',
            'type'=>'text',
            'style'=>"width:88%;display:none",
            'class'=>'nroRif',
            'hidden'=>true,
            'minlength' => 13,
            'maxlength' => 13,
            'required'=>true
        )
    );
    
?>
    <tr id="afiliadosContainer" style="display:none; margin-top:20px;">
        <td colspan="2">
            <table class="tabla">
                <thead>
                    <tr>
                        <td class="texto_centro"><span class="requerido">* </span></td>
                        <td>Alias</td>
                        <td>N&uacute;mero de Contrato</td>
                    </tr>
                </thead>
                <tbody id="borron" style="display:none;">
                    <?php 
                    if(count($afiliados1)>0):
                        foreach($afiliados1 as $afiliado):
                    ?>
                        <tr>
                            <td class="texto_centro">
                                <input
                                    type="text"
                                    name="tipoRif" 
                                    value="<?php echo $afiliado['Afiliacion']['tipo_cedula']; ?>" 
                                    style="display: none;"
                                    class="seleccion_afiliado"
                                />
                                <input
                                    type="radio"
                                    name="data[PagoCorpoelec][nroCedula]" 
                                    value="<?php echo desencriptar($afiliado['Afiliacion']['cedula']); ?>" 
                                    class="seleccion_afiliado"
                                />
                            </td>
                            <td style="text-align: center;width: 50%;"><?php echo desencriptar($afiliado["Afiliacion"]['alias']); ?></td>
                            <td style="text-align: center; width: 50%;">
                                <?php 
                                    $tipoCedula = trim($afiliado["Afiliacion"]['tipo_cedula']);
                                    $cedula = trim(desencriptar($afiliado["Afiliacion"]['cedula']));
                                    echo $tipoCedula . '-' . $cedula;
                                ?>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <tr>
                            <td class="texto_centro" colspan="3">
                                <span class="error">No se encontraron resultados.</span>
                            </td>
                        </tr>
                    <?php 
                    endif;
                    ?>
                    
                </tbody>
                <tbody id="corpoelec" style="display:none;">
                    <?php
                    if(count($afiliados2)>0): 
                        foreach($afiliados2 as $afiliadoCorp): 
                    ?>
                        <tr>
                            <td class="texto_centro">
                                <input
                                    type="radio"
                                    name="data[PagoCorpoelec][nroContrato]" 
                                    value="<?php echo $afiliadoCorp['Afiliacion']['numero_contrato']; ?>" 
                                    class="seleccion_afiliado"
                                />
                            </td>
                            <td style="text-align: center;width: 50%;"><?php echo desencriptar($afiliadoCorp["Afiliacion"]['alias']); ?></td>
                            <td style="text-align: center;width: 50%;"><?php echo $afiliadoCorp["Afiliacion"]['numero_contrato']; ?></td>
                        </tr>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <tr>
                            <td class="texto_centro" colspan="3">
                                <span class="error">No se encontraron resultados.</span>
                            </td>
                        </tr>
                    <?php
                    endif;
                    ?>
                </tbody>
            </table>
        </td>
    </tr>
    <tr id="errorSeleccionAfiliado" style="display:none; margin-top:20px;">
        <td class="campo" colspan="3"><label id="error" class="error error-message">Debe seleccionar un contrato a consultar</label></td>
    </tr>
    <tr id="errorAfiliados" style="display:none; margin-top:20px;">
        <td class="campo" colspan="3"><label class="error">Debe seleccionar un servicio a consultar</label></td>
    </tr>
<?php

    if ($form->isFieldError('tipoCedula')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tipoCedula',
            array(
                'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de documento de identidad')),
                'requerido'=>_m('campo.requerido',array('el tipo de documento de identidad'))
            )).'</td></tr>';
    } else if ($form->isFieldError('nroCedula')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nroCedula',
        array(
            'requerido'=>_m('campo.requerido',array('la cÃ©dula del beneficiario')),
            'longitud'=>_m('valor.invalido',array('la cÃ©dula del beneficiario')),
            'destinatario'=>_m('identificacion.igualcliente',array('la cÃ©dula del beneficiario'))
        )).'</td></tr>';
    } else if ($form->isFieldError('nroContrato')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nroContrato',
        array(
            'requerido'=>_m('campo.requerido',array('el numero de contrato')),
            'longitud'=>_m('valor.invalido',array('el numero de contrato')),
            'destinatario'=>_m('identificacion.igualcliente',array('el numero de contrato'))
        )).'</td></tr>';
    }

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
    $(document).ready(function(){
        // Referencias a elementos
        const $servicio = $("#PagoCorpoelecServicio");
        const $afiliadoA = $("#PagoCorpoelecAfiliadoA");
        const $afiliadoN = $("#PagoCorpoelecAfiliadoN");
        const $afiliadosContainer = $("#afiliadosContainer");
        const $errorAfiliados = $("#errorAfiliados");
        const $cuentaDebitar = $("#PagoCorpoelecCuentaDebitar");
        

        // Función para actualizar la interfaz
        function actualizarUI() {
            const afiliado = $afiliadoA[0].checked;
            const noAfiliado = $afiliadoN[0].checked;
            const servicioVal = $servicio.val();
            
            // Reset inicial: ocultar todos los elementos
            $afiliadosContainer.hide();
            $('#borron, #corpoelec').hide();  // Ocultar ambas tablas
            $("[id^=label], [id^=PagoCorpoelecTipoCedula], [id^=PagoCorpoelecNro]").hide();
            $errorAfiliados.hide();
            
            // Validar selección de servicio
            if(!servicioVal) {
                if(afiliado || noAfiliado) $errorAfiliados.show();
                return;
            }
            
            // Lógica para afiliados
            if(afiliado) {
                $afiliadosContainer.show();
                
                // Mostrar SOLO la tabla correspondiente al servicio seleccionado
                if(servicioVal == 1) {
                    $('#borron').show();
                } else if(servicioVal == 2) {
                    $('#corpoelec').show();
                }
            } 
            // Lógica para no afiliados
            else if(noAfiliado) {
                if(servicioVal == 1) {
                    $("#labelCedula, #PagoCorpoelecTipoCedula, #PagoCorpoelecNroCedula").show();
                } else if(servicioVal == 2) {
                    $("#labelContrato, #PagoCorpoelecNroContrato").show();
                }
            }
            resetFields()
        }

        function resetFields() {
            // Limpiar selección en tablas de afiliados
            $('input[name="data[PagoCorpoelec][nroCedula]"], input[name="data[PagoCorpoelec][nroContrato]"]').prop('checked', false);
            
            // Limpiar campos de texto
            $('#PagoCorpoelecNroCedula, #PagoCorpoelecNroContrato').val('');
            
            // Ocultar mensajes de error
            $('#errorSeleccionAfiliado, #errorAfiliados').hide();
        }

        function resetAfiliadoFields() {
            // Desmarcar radio buttons de afiliado
            $afiliadoA.prop('checked', false);
            $afiliadoN.prop('checked', false);
            
            // Ocultar contenedor de afiliados y tablas
            $afiliadosContainer.hide();
            $('#borron, #corpoelec').hide();
            
            // Ocultar campos de cédula y contrato
            $("[id^=label], [id^=PagoCorpoelecTipoCedula], [id^=PagoCorpoelecNro]").hide();
            
            // Limpiar selección en tablas de afiliados
            $('input[name="data[PagoCorpoelec][nroCedula]"], input[name="data[PagoCorpoelec][nroContrato]"]').prop('checked', false);
            
            // Limpiar campos de texto
            $('#PagoCorpoelecNroCedula, #PagoCorpoelecNroContrato').val('');
            
            // Ocultar mensajes de error
            $('#errorSeleccionAfiliado, #errorAfiliados').hide();
        }

        $afiliadoA.add($afiliadoN).on('change', actualizarUI);
        $servicio.on('change', function() {
            resetAfiliadoFields();
            actualizarUI();
        });
        $cuentaDebitar.on('change', resetAfiliadoFields);

        // Event listeners
        
        //$servicio.on('change', actualizarUI);

        // Inicialización
        actualizarUI();
    });
</script>
<script type="text/javascript">
    var ajaxPendiente = false;
    
    $(function(){
        const $form = $('#PagoCorpoelecDatosForm');

        // Configurar validación personalizada para el grupo de radio buttons
        $.validator.addMethod("afiliadoSeleccionado", function(value, element) {
            const servicioVal = $("#PagoCorpoelecServicio").val();
            var afiliadoCheck = $("#PagoCorpoelecAfiliadoA")
            if(afiliadoCheck[0].checked){
                if (servicioVal == 1) {
                    return $('input[name="data[PagoCorpoelec][nroCedula]"]:checked').length > 0;
                } else if (servicioVal == 2) {
                    return $('input[name="data[PagoCorpoelec][nroContrato]"]:checked').length > 0;
                }
            }else{
               return value.length > 0
            }
            return false;
        }, "Debe seleccionar un afiliado a consultar");

        // Configurar la validación del formulario
        $form.validate({
            rules: {
                'data[PagoCorpoelec][cuentaDebitar]': {
                    required: true
                },
                'data[PagoCorpoelec][nroCedula]': {
                    afiliadoSeleccionado: true
                },
                'data[PagoCorpoelec][nroContrato]': {
                    afiliadoSeleccionado: true
                }
            },
            messages: {
                'data[PagoCorpoelec][cuentaDebitar]':{
                    required:<?php echo _m('seleccion.requerida',array('la cuenta de débito'),true) ?>
                },
                'data[PagoCorpoelec][nroCedula]':{
                    required:<?php echo _m('campo.requerido',array('la c&eacute;dula del beneficiario'),true) ?>,
                    digits:<?php echo _m('formato.invalido',array('la c&eacute;dula del beneficiario'),true) ?>,
                    alphanumeric:<?php echo _m('alfanumerico.invalido',array('el pasaporte del beneficiario'),true) ?>
                }
            },
            errorPlacement: function(error, element) {
                // Para los campos de afiliado, mostramos el error en nuestra fila personalizada
                if (element.attr('name') === 'data[PagoCorpoelec][nroCedula]' || 
                    element.attr('name') === 'data[PagoCorpoelec][nroContrato]') {
                    var afiliadoCheck = $("#PagoCorpoelecAfiliadoA")
                    if(afiliadoCheck[0].checked){
                        if(!element.checked){
                            // Actualizamos el mensaje de error
                            $('label#error.error.error-message').show();
                            $('label#error.error.error-message').text(error.text())
                            $('#errorSeleccionAfiliado').show();
                            $('#error').show();
                            $('label#error.error.error-message').show();
                        }
                    }else{
                        let valueInput = element.val()
                        if(valueInput == null || valueInput == ""){
                            // Actualizamos el mensaje de error
                            $('label#error.error.error-message').show();
                            $('label#error.error.error-message').text(error.text())
                            $('#errorSeleccionAfiliado').show();
                            $('#error').show();
                            $('label#error.error.error-message').show();
                        }
                       
                    }
                } else {
                    error.insertAfter(element);
                }
            },
            success: function(label, element) {
                // Cuando se corrige el error, ocultamos nuestra fila personalizada
                if ($(element).attr('name') === 'data[PagoCorpoelec][nroCedula]' || 
                    $(element).attr('name') === 'data[PagoCorpoelec][nroContrato]') {
                    $('#errorSeleccionAfiliado').hide();
                    $('#error').hide();
                    $('label#error.error.error-message').hide();
                }
                label.remove();
            },
            submitHandler: function(form) {
                // Validación adicional antes de enviar
                const servicioVal = $("#PagoCorpoelecServicio").val();
                var afiliadoCheck = $("#PagoCorpoelecAfiliadoA")
                let seleccionado = false;
                if(afiliadoCheck[0].checked){
                    if (servicioVal == 1) {
                        seleccionado = $('input[name="data[PagoCorpoelec][nroCedula]"]:checked').length > 0;
                    } else if (servicioVal == 2) {
                        seleccionado = $('input[name="data[PagoCorpoelec][nroContrato]"]:checked').length > 0;
                    }
                    if (!seleccionado) {
                        $('label#error.error.error-message').show();
                        $('label#error.error.error-message').removeAttr('style');
                        $('#errorSeleccionAfiliado').show();
                        return false;
                    }
                }else{
                    var NroCedula=$('input[id="PagoCorpoelecNroCedula"]').val()
                    var NroContrato=$('input[id="PagoCorpoelecNroContrato"]').val()
                    let aux = false;

                    if (servicioVal == 1) {
                        if(NroCedula == "" || NroCedula == null){
                            aux=true;
                        }
                    }else if (servicioVal == 2){
                        if(NroContrato == "" || NroContrato == null){
                            aux=true;
                        }
                    }
                    if (aux) {
                        $('label#error.error.error-message').show();
                        $('label#error.error.error-message').removeAttr('style');
                        $('#errorSeleccionAfiliado').show();
                        return false;
                    }
                }
                if(ajaxPendiente) return false;
                form.submit();
            }
        });
        
        // Forzar validación al cambiar selección
        $('input[name="data[PagoCorpoelec][nroCedula]"], input[name="data[PagoCorpoelec][nroContrato]"]').change(function() {
            $form.validate().element(this);
        });
    });
</script>
