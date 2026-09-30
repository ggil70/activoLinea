<?php


/**
 * Pago de tarjetas de terceros no afiliadas en otros bancos
**/
?>
<div>
<h2 class="user">Recarga Tercero No Afiliado / Tarjeta Prepagada Cash </h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">


    <?php

    echo $form->create('Recargatercna',
        array(
            'url'=> array(
                'controller'=>'tarjeta_prepagadas',
                'action'=>'recargatercnoafiliado'
            ),
            'inputDefaults'=>array(
                'div'=>false,
                'label'=>'etiqueta',
                'before'=>'<tr><td class="etiqueta">',
                'between'=>'</td><td class="campo">',
                'after'=>'</td></tr>',
                'error'=>false
            )
        )
    );

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



    echo $form->hidden('recarga_minima', array('id'=>'recarga_minima','value'=>$recargaMinima));
    echo $form->hidden('recarga_maxima', array('id'=>'recarga_maxima','value'=>$recargaMaxima));

    echo $form->hidden('comisionF', array('id'=>'comisionF', 'value'=>$comisionTCPF));
    echo $form->hidden('etiqComisionF', array('id'=>'etiqComisionF', 'value'=>$etiqComisionF));

    echo $form->hidden('comisionD', array('id'=>'comisionD', 'value'=>$comisionTCPD));
    echo $form->hidden('etiqComisionD', array('id'=>'etiqComisionD', 'value'=>$etiqComisionD));


    echo '
    <tr>
    <td class="etiqueta"><span class="estiloTexto"></span> Cuenta de D&eacute;bito:</td>
    <td> ' . enmascara($cuentaME) . ' </td>
    </tr>
    ';

    echo '<tr><td colspan="2"></td></tr>';
    echo '<tr><td colspan="2">Indique la informaci&oacute;n que a continuaci&oacute;n se solicita:</td></tr>';


    $nacionalidad = array('V'=>'V','E'=>'E','P'=>'P');
    echo $form->input(
        'tipoCedula',
        array(
            'after'=>false,
            'label'=>'<span class="requerido">* </span>C&eacute;dula / Pasaporte del Beneficiario:',
            'type'=>'select',
            'class'=>'tipoRif',
            'options'=>$nacionalidad
        )
    );

    echo "&nbsp;&nbsp;";

    echo $form->input(
        'nroCedula',
        array(
            'before'=>false,
            'between'=>false,
            'label'=>false,
            'type'=>'text',
            'class'=>'nroRif',
            'maxlength'=>15,
            'value'=>$nroCedula,
            'id'=>'cedula'
        )
    );

    if($swErrorCedula > 0){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo" style="color:red;"><label id="cedulaError" style="display:inline"><span>'.$errorCedula.'</span></label></td></tr>';
    } 


    if ($form->isFieldError('tipoCedula')){  
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tipoCedula',
                array(
                    'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de documento de identidad')),
                    'requerido'=>_m('campo.requerido',array('el tipo de documento de identidad'))
                )).'</td></tr>';
    } else if ($form->isFieldError('nroCedula')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nroCedula',
                array(
                    'requerido'=>_m('campo.requerido',array('la cédula o pasaporte del beneficiario')),
                    'longitud'=>_m('valor.invalido',array('la cédula o pasaporte del beneficiario')),
                    'destinatario'=>_m('identificacion.igualcliente',array('la cédula o Pasaporte del beneficiario'))
                )).'</td></tr>';
    }


    echo '<tr><td colspan="2"><div id="wait" class="texto_centro" style="display:none;">';
    echo 'Cargando... ' . $html->image('/img/ajax-loader_mini.gif') . '</div>';
    echo '</td></tr>';


    $input_tarjeta = array(
            'label'=>'<span class="requerido">* </span>Tarjeta Prepago Cash:',
            'type'=>'select',
            'options'=>$tarjeta,
            'id'=>'tarjeta',
    );
    
    echo $form->input(
            'tarjeta',
            $input_tarjeta
    );

    if ($form->isFieldError('tarjeta')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tarjeta',
        array(
        )
            ).'</td></tr>'; 
    }

    echo '<tr><td></td><td><label><span id="errorST" style="color:red;"></span></label></td></tr>';    



    echo $form->input(
        'email',
        array(
            'label'=>'<span class="requerido">* </span>E-mail del Beneficiario:',
            'type'=>'text',
            'maxlength'=>50
        )
    );

    if ($form->isFieldError('email')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('email',
            array(
                'requerido'=>_m('campo.requerido','el email del beneficiario'),
                'correo'=>_m('correo.invalido','el email del beneficiario')
            )
        ).'</td></tr>';
    }



    echo $form->input(
        'montoRecarga',
        array(
            'label'=>'<span class="requerido">* </span>Monto Recarga:',
            'type'=>'text',
            'class'=>'monto pago',
            'id'=>'montoRecarga',
            'value'=>$montoRecarga,
            )
    );

    if ($form->isFieldError('montoRecarga')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('montoRecarga',
            array(
                 //'requerido'=>_m('campo.requerido',array('el monto de la Recarga')),
                 //'numerico'=>_m('formato.invalido',array('el monto de la Recarga')),
                 //'mayorCero'=>_m('mayor.cero'),                    
            )
            ).'</td></tr>';
    }

    echo '<tr id="mensajeError" style="display:none"><td class="etiqueta" >&nbsp;</td><td class="campo"><span style="color:red">'. _m('tcp.rec.mto.max'). ' (' . $recargaMaxima .'.00)</span></td></tr>';    

    echo $form->input(
        'totalComision',
        array(
            'label'=>'<span class="requerido">* </span>Monto Comisi&oacute;n:',
            'type'=>'text',
            'class'=>'monto pago',
            'value'=>'0,00',
            'readonly'=>'readonly',
            'id'=>'totalComision',
            'value'=>$totalComision
        )
    );

    echo $form->input(
        'totalRecarga',
        array(
            'label'=>'<span class="requerido">* </span>Total a Debitar:',
            'type'=>'text',
            'class'=>'monto pago',
            'value'=>$totalRecarga,
            'readonly'=>'readonly',
            'id'=>'totalRecarga',
            )
    );

    echo '<tr><td class="etiqueta"><span class="estiloTexto"></span>Desea afiliar al Beneficiario?:';

    echo $form->input(
        'afiliar',
        array(
            'before'=>false,
            'label'=>false,
            'type'=>'radio',
            'legend'=>false,
            'separator'=>'&nbsp;&nbsp;',
            'class' => 'seleccion',
            'value' => $afiliar,
            'options'=> array(
                's' => 'S&iacute',
                'n' => 'No'
            )
        )
    );
     echo '</td><td></td></tr>';


        if ($form->isFieldError('afiliar')){
            echo $form->error('afiliar',
                array(
                    'requerido'=>_m('una.seleccion.requerida','de las opciones'),
                    'valoresPermitidos'=>_m('formato.invalido','la selección')
                )
            );
        }




    echo $form->input(
        'alias',
        array(
            'label'=>'<span class="requerido">* </span>Alias:',
            'type'=>'text',
            'class'=>'opciones alfanumericoEspacio',
            'id'=>'alias',
            'value' => $alias, 
        )
    );

    if($swErrorAlias == 1){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo" style="color:red;"><label id="aliasError" style="display:inline"><span>'.$errorAlias.'</span></label></td></tr>';
    } 

    
    if ($form->isFieldError('alias')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('alias',     
                array(
                    'alfanumerico'=>_m('alfanumerico.invalido','el alias'),
                    'alias_nodisponible'=>_m('alias.nodisponible')
                )
            ).'</td></tr>';
    }
    


    echo $form->input(
        'nombre',
        array(
            'label'=>'<span class="requerido">* </span>Nombre Beneficiario:',
            'type'=>'text',
            'class'=>'opciones alfanumericoEspacio',
            'id'=>'nombre',
            'value' => $nombre, 
        )
    );

    if($swErrorNombre == 1){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo" style="color:red;"><label id="aliasError" style="display:inline"><span>'.$errorNombre.'</span></label></td></tr>';
    } 

    
    if ($form->isFieldError('nombre')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nombre',     
                array(
                    'alfanumerico'=>_m('alfanumerico.invalido','el alias'),
                    'alias_nodisponible'=>_m('alias.nodisponible')
                )
            ).'</td></tr>';
    }


    $usuario = $session->read('usuarios.datos');
    
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
$(function(){

    function formatear(valor) {
        //var num = parseInt(parseFloat(valor) * 100) / 100;
        // var x = Number(num).toFixed(2);
        var x =valor.toFixed(2);
        x = x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "-").replace(/\./g, ',');
        return x.replace(/\-/g, '.');
    }
    
    function formatearComision(valor) {
        //var num = parseInt(parseFloat(valor) * 100) / 100;
        // var x = Number(num).toFixed(2);
        var x =valor.toFixed(2);
        x = x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "-").replace(/\./g, ',');
        return x.replace(/\-/g, '.');
    }

    function round(num, decimales = 2) {
        var signo = (num >= 0 ? 1 : -1);
        num = num * signo;
        if (decimales === 0) //con 0 decimales
            return signo * Math.round(num);
        // round(x * 10 ^ decimales)
        num = num.toString().split('e');
        num = Math.round(+(num[0] + 'e' + (num[1] ? (+num[1] + decimales) : decimales)));
        // x * 10 ^ (-decimales)
        num = num.toString().split('e');
        return signo * (num[0] + 'e' + (num[1] ? (+num[1] - decimales) : -decimales));
    }


    function listarTarjeta(cedula){
    $('#tarjeta').empty();

    $('#errorST').css('display','none');   
    
    if(cedula.trim() != ''){     
    $.ajax({
        type: "GET",
        url: '<?php echo Router::url("/tarjeta_prepagadas/listarTarjeta/"); ?>' + cedula,
        dataType: "json",
        cache:false,
        beforeSend: function() {
            ajaxPendiente = true;
            $('#wait').show();
        },
        success: function(source) {
            if(source){
                ajaxPendiente = false;
                
                $('#wait').hide();
                var flag = false;
                var swError = 0;
                var swMensaje = 0;
                var swLista = 0;

                var cadenaMensaje = 'Estimado cliente, ha ocurrido un error inesperado. Por favor intente de nuevo mas tarde.';

                $.each(source, function(index, value){

                    if(index == 'error'){
                        if(value == '0'){
                            swError = 1;
                        }
                    }

                    if(swError==1){
                        if(index == 'fisica'){
                            if(value!=""){
                                swLista = 1;
                                nroTarjeta = value;
                                nroT = nroTarjeta.substring(0,16);
                                nro = nroT.substring(0,4) + '****' + nroT.substring(12,16)

                                //nro =  nroTarjeta.substring(0,16);


                                desTarjeta = nro + " - Fisica";
                                $('#tarjeta').append(
                                    $('<option>',{
                                        'value': nroTarjeta,
                                        'text':  desTarjeta
                                    })
                                );            
                            }
                        }    

                        if(index == 'virtual'){
                            if(value!=""){
                                swLista = 1;                                

                                nroTarjeta = value;
                                nroT = nroTarjeta.substring(0,16);
                                nro = nroT.substring(0,4) + '****' + nroT.substring(12,16)

                                desTarjeta = nro + " - Virtual";
                                $('#tarjeta').append(
                                    $('<option>',{
                                        'value': nroTarjeta,
                                        'text':  desTarjeta
                                    })
                                );                                 
                           }       
                        }
                    }
                });

            }else{
                var cadenaMensaje = 'Estimado cliente, ha ocurrido un error inesperado. Por favor intente de nuevo mas tarde.';
                $('#errorClave').val('cadenaMensaje');
                $('#errorClave').css('display','inline');
                $('#wait').hide();
            }   
        },
        error: function(source){
            var cadenaMensaje = "<?php echo _m('tcp.nosolicitada.recarga.beneficiario');?>";
                $('#errorST').html(cadenaMensaje);
                $('#errorST').css('display','inline');
                $('#wait').hide();
        }
    });
    }

    }







    //Funcion para completar 2 decimales
    $('#RecargatercnaMontoRecarga').blur(function (e) {
        var monto = $('#RecargatercnaMontoRecarga').val();
        //Si el monto solo posee un solo decimal, hay que completar los dos decimale
        var arreglo_monto = monto.split(',');
        var entero = arreglo_monto[0];
        var decimal = arreglo_monto[1];

        if (decimal.length == 1){
           monto = monto + '0';
           $('#RecargatercnaMontoRecarga').val(monto);
        }
    }); 

    // condicion inicial al cargar el formulario
    $(".opciones").attr("disabled", $(".seleccion:checked").val()!='s');
    
    // al seleccionar la opcion negativa habilita los campos
    $(".seleccion").click(function(){
        $(".opciones").attr("disabled", !($(this).val()=='s'));
        
        if( $(this).val()!='s' ) {
            $("#RecargatercnaAlias").val('');
            $("label.error:last").hide();
        }
    });     
    
    // para la validacion
    $('#RecargatercnaRecargatercnoafiliadoForm').validate({


        rules:{

            'data[Recargatercna][nroCedula]':{
                required:true,
                digits:{
                    depends: function(e){
                    return $('#RecargaTipoCedula').val()=='V';
                }               
                },
                alphanumeric:{
                    depends: function(e){
                        return $('#RecargaTipoCedula').val()=='P';
                    }               
                }
            },
            'data[Recargatercna][tarjeta]':{
                required:true
            },            

            'data[Recargatercna][montoRecarga]':{
                required:true,
                numberVE:true,
                montoMaximo:'#PagostdcMontoMaximo',
                montoMaximo3: '#monto_limite_usuario',
                montoMaximo2:'#monto_limite'
            },
            'data[Recargatercna][afiliar]':{
                required:true
            },
            'data[Recargatercna][alias]':{
                required: {
                    depends:function(e){
                        return $(".seleccion:checked").val()=='s';
                    }
                },
                                alphaNumericSpace:true
            },
            'data[Recargatercna][nombre]':{
                required: {
                    depends:function(e){
                        return $(".seleccion:checked").val()=='s';
                    }
                },
                alphaNumericSpace:true
            },

            'data[Recargatercna][email]':{
                required:true, 
                email:true
            },            
        },
        messages:{
                'data[Recargatercna][nroCedula]':{
                    required:<?php echo _m('campo.requerido',array('la cédula o pasaporte del beneficiario'),true) ?>,
                    digits:<?php echo _m('formato.invalido',array('la cédula o pasaporte del beneficiario'),true) ?>,
                    alphanumeric:<?php echo _m('alfanumerico.invalido',array('el pasaporte del beneficiario'),true) ?>
                },   
                'data[Recargatercna][tarjeta]':{
                    required:<?php echo _m('seleccion.requerida',array('la tarjeta a Recargar'), true) ?>
                },                              


            'data[Recargatercna][montoRecarga]':{
                required:<?php echo _m('campo.requerido','el monto',true) ?>,
                numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
                montoMaximo:<?php echo _m('monto.maximo', 'de la tarjeta',true) ?>,
                montoMaximo2:<?php echo _m('monto.excedido', 'para el pago' ,true) ?>,
                montoMaximo3: <?php echo _m('monto.limite.usuario', 'para el pago' ,true) ?>
            },
            'data[Recargatercna][alias]':{
                required:<?php echo _m('campo.requerido','el alias',true) ?>,
                alphaNumericSpace:<?php echo _m('alfanumerico.invalido','el alias',true) ?>
            },
            'data[Recargatercna][nombre]':{
                required:<?php echo _m('campo.requerido','el nombre',true) ?>,
                alphaNumericSpace:<?php echo _m('alfanumerico.invalido','el nombre',true) ?>
            },

            'data[Recargatercna][email]':{
                required:<?php echo _m('campo.requerido','el email del beneficiario', true) ?>,
                email:<?php echo _m('correo.invalido','el email del beneficiario', true) ?>
            },

        }   
    });





//////////////////////
    $('#montoRecarga').focus(function(e){
         $('#mensajeError').hide();  
    });
    
    $('#alias').blur(function(e){
        $('#aliasError').css('display','none');
    });

    $('#cedula').blur(function(e){
        $('#cedulaError').css('display','none');
        var cedula = $('#cedula').val();
        if(cedula.trim() != ''){
            listarTarjeta(cedula);
        }    


    });


    $('#montoRecarga').blur(function (e) {
            var monto = $('#montoRecarga').val();
            var mtoVal = monto.replace(".", '');
            var mtoVal = mtoVal.replace(",", '.');
            mtoVal = parseFloat(mtoVal);

            var montoRecMin = $('#recarga_minima').val();  
            var mtoMinVal = montoRecMin.replace(".", '');
            var mtoMinVal = mtoMinVal.replace(",", '.');
            mtoMinVal = parseFloat(mtoMinVal);             


            var montoRecMax = $('#recarga_maxima').val();  
            var mtoMaxVal = montoRecMax.replace(".", '');
            var mtoMaxVal = mtoMaxVal.replace(",", '');
            mtoMaxVal = parseFloat(mtoMaxVal);             

           var swValidacion = 0; 
           $('#mensajeError').hide();

           if(monto.length==0){
                $('#totalRecarga').val('0,00');
                $('#totalComision').val('0,00');
                swValidacion = 1;
           }

           if(mtoVal > mtoMaxVal){
                swValidacion = 1;
                $('#montoRecarga').val('');
                $('#totalRecarga').val('0,00');
                $('#totalComision').val('0,00');
                $('#mensajeError').show(); 
           }else{
               if(mtoVal < mtoMinVal){
                   $('#montoRecarga').val('');
                   $('#totalRecarga').val('0,00');
                   $('#totalComision').val('0,00');
                   //$('#mensajeError').show();
                   swValidacion = 1;
               }    

           }

            
           if(swValidacion==0){


                var nroTarjeta = $('#tarjeta').val();
                var tipoC = nroTarjeta.substr(-1);

                if(tipoC=='D'){

                   var porComision = $('#comisionD').val();   
                }else{
                   var porComision = $('#comisionF').val()   
                }



                //var porComision = $('#comision').val();
                //Si el monto solo posee un solo decimal, hay que completar los dos deimales ya que error en los calculos de Recarga

                var arreglo_monto = monto.split(',');
                var entero = arreglo_monto[0];
                if(arreglo_monto[1]){
                    var decimal = arreglo_monto[1];
                }else{
                    decimal = '00';
                }    

                if (decimal.length == 1){
                   monto = monto + '0';
                   $('#montoRecarga').val(monto);
                }
                   
      
                if(monto.indexOf(",")!=-1){
                    var resultMonto = monto.split(',');                    
                    if(resultMonto[1].length===2){
                        monto = monto.replace(/[\,\.]/g, '');
                        monto = parseInt(monto) / 100; 
                    }else if(resultMonto[1].length===1){
                        monto = monto.replace(/[\,\.]/g, '');
                        monto = parseInt(monto) / 10; 
                    }else{
                        monto = monto.replace(/[\,\.]/g, '');
                        monto = parseInt(monto); 
                    }                    
                }else{
                    monto = monto.replace(/[\,\.]/g, '');
                    monto = parseInt(monto);
                }                    

                
               var comision = (monto * porComision)/100;
               //var comisionR =comision.toFixed(3);

               comisionR = round(comision, 2);

               var total = monto + comisionR;
               totalF = formatear(total);
               comisionF = formatear(comisionR);

                $('#totalRecarga').val(totalF);
                $('#totalComision').val(comisionF);
          }//fin de swValidacion      
            
        });





///////////////////


    
});  //final function




    $('#tarjeta').change(function (e) {
        $('#montoRecarga').val('');    
        $('#totalRecarga').val('0,00');
        $('#totalComision').val('0,00');
              
    }); 









</script>

