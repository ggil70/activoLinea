<?php
/**
 * Formulario de transferencias a cuentas afiliadas de terceros en banco activo
**/
?>
<div>
<h2 class="user">Tarjeta Prepago Cash / Recarga Tercero en Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitar Informaci&oacute;n Beneficiario</div>

<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>

<div class="paso_nro">3</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>

<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>
<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <?php

    echo $form->create('Recarga',
            array(
        'url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'Recargatercero'
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



    echo $form->hidden('cedula', array('id'=>'cedula', 'value'=>''));
    echo $form->hidden('nombre_beneficiario', array('id'=>'nombre_beneficiario', 'value'=>''));
    echo $form->hidden('monto_maximo', array('id'=>'monto_maximo'));
    //echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$monto_limite));
    echo $form->hidden('monto_limite_usuario', array('id'=>'monto_limite_usuario'));


    echo $form->hidden('comisionF', array('id'=>'comisionF', 'value'=>$comisionTCPF));
    echo $form->hidden('etiqComisionF', array('id'=>'etiqComisionF', 'value'=>$etiqComisionF));

    echo $form->hidden('comisionD', array('id'=>'comisionD', 'value'=>$comisionTCPD));
    echo $form->hidden('etiqComisionD', array('id'=>'etiqComisionD', 'value'=>$etiqComisionD));

    echo $form->hidden('tipo_tarjeta', array('id'=>'tipo_tarjeta','value'=>''));



    echo $form->hidden('tarjetatpc', array('id'=>'tarjetatpc', 'value'=>''));    
    
    // La primera fila define la tabla con layout fixed
    echo '<tr><td style="width:150px"></td><td style="width:400px"></td></tr>';
    
    echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la recarga.</td></tr>';


    $cuentaME = enmascara($cuentaME);

    echo $form->input(
        'cuentaME',
        array(
            'label'=>'<span class="requerido"> </span>Cuenta Cash a Debitar:',
            'type'=>'text',
            'maxlength' => '30',
            'readonly'=>'readonly',
            'value'=>$cuentaME
        )
    );

    echo '<tr><td colspan="2">Seleccione el beneficiario de la recarga y complete los datos de la operación:</td></tr>';

    ?>

    <tr>
    <td colspan="2">



    <table cellpadding="0" cellspacing="0" border="0" class="tabla">
    <thead>
    <tr>
    <td class="ancholibre texto_centro"><span class="requerido">*</span></td>
    <td>Alias</td>
    <td>Nombre Beneficiario</td>
    <td>N&uacute;mero</td>
    </tr>
    </thead>
    <tbody>
    <?php

    if($afiliados){
        $total_registros = count($afiliados);
        foreach($afiliados as $i){
        $numeroCuenta = desencriptar($i['Afiliacion']['numero_cuenta']); 
        $cedula = $i['Afiliacion']['tipo_cedula'] . "-" . desencriptar($i['Afiliacion']['cedula']); 

    ?>
            <tr>
            <td class="texto_centro ancholibre">
            <input type="radio"
                    class="seleccion_afiliado"
                    id="<?php echo 'sel'.$i['Afiliacion']['id'] ?>"
                name="data[Recarga][cuentaDebitar]"
                value="<?php echo desencriptar($i['Afiliacion']['alias']);?>"
                data-alias="<?php echo desencriptar($i['Afiliacion']['alias']) .';'.desencriptar($i['Afiliacion']['nombre_beneficiario']).';'. $i['Afiliacion']['monto_maximo'] . ';' .$cedula . ";".desencriptar($i['Afiliacion']['email']) . ";" .  $numeroCuenta?>"
            />
            <?php 
                echo $form->hidden('seleccion', array('id'=>'monto'.$i['Afiliacion']['id'], 'value'=>empty($i['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($i['Afiliacion']['monto_maximo']) ));
            ?>
            </td>
            <td><?php echo desencriptar($i['Afiliacion']['alias']) ?></td>
            <td><?php echo desencriptar($i['Afiliacion']['nombre_beneficiario']) ?></td>
            <td><?php 
                $num_tarjeta = trim(desencriptar($i['Afiliacion']['numero_cuenta']));
                $num_tarjeta = substr($num_tarjeta,0,4) . "****" . substr($num_tarjeta,16-4,4);
                echo $num_tarjeta;
            ?></td>
            </tr>
<?php   
        }

        ?>
        <tr>
        <td colspan="4" class="paginador">
            <?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros,'tamano_paginacion'=>5)); ?>
        </td>
        </tr>
    <?php
        
    }else {
        ?>
            <tr>
                <td colspan="4" class="texto_centro">No se encontraron resultados...</td>
            </tr>
        <?php
    
            }
?>
    </tbody>
    </table>
    </td>
    </tr>
     

    <tr>
    <td colspan="2">
    <div id="mostrar_tabla" style="display:none">    
    <table cellpadding="0" cellspacing="0" border="0">

<?php
    echo '<tr><td colspan="2">Indique la información que a continuación se solicita:</td></tr>';
?>


<?php
/*    echo $form->input(
        'alias',
        array(
            'label'=>'<span class="requerido">* </span>Tarjeta del Beneficiario:',
            'type'=>'text',
            'maxlength' => '30',
            'id'=>'txtCuenta',
            'readonly'=>'readonly'
        )
    );
*/
echo $form->input(
            'nombre',
          array(
            'label'=>'<span class="requerido">* </span>Nombre del Beneficiario:',
            'type'=>'text',
            'readonly'=>'readonly',
            'id'=>'txtNombre',
            'value'=>''
            )
    );


echo $form->input(
            'cedula',
          array(
            'label'=>'<span class="requerido">* </span>C&eacute;dula del Beneficiario:',
            'type'=>'text',
            'readonly'=>'readonly',
            'id'=>'txtCedula',
            'value'=>''
            )
    );

echo $form->input(
            'tarjeta',
          array(
            'label'=>'<span class="requerido">* </span>Tarjeta Prepagada:',
            'type'=>'text',
            'readonly'=>'readonly',
            'id'=>'txtTarjeta',
            'value'=>''
            )
    );


echo $form->input(
            'email',
          array(
            'label'=>'<span class="requerido">* </span>Email del Beneficiario:',
            'type'=>'text',
            'readonly'=>'readonly',
            'id'=>'txtEmail',
            'value'=>''
            )
    );


    echo $form->input(
        'montoRecarga',
        array(
            'label'=>'<span class="requerido">* </span>Monto:',
            'type'=>'text',
            'class'=>'monto pago',
            'id'=>'montoRecarga',
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

    echo '<tr id="mensajeError" style="display:none"><td class="etiqueta" >&nbsp;</td><td class="campo"><span style="color:red">'. _m('tcp.rec.mto.max').'</span></td></tr>';    


    echo $form->input(
        'totalComision',
        array(
            'label'=>'<span class="requerido">* </span>Monto Comisión:',
            'type'=>'text',
            'class'=>'monto pago',
            'value'=>'0,00',
            'readonly'=>'readonly',
            'id'=>'totalComision',
        )
    );

    echo $form->input(
        'totalRecarga',
        array(
            'label'=>'<span class="requerido">* </span>Total a Debitar:',
            'type'=>'text',
            'class'=>'monto pago',
            'value'=>'0,00',
            'readonly'=>'readonly',
            'id'=>'totalRecarga',
            )
    );


    echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
    
    echo '<tr><td>&nbsp;</td><td class="submit">';
    
    echo $html->link(
        'Volver',
        'recarga_tercero/v',
        array(
            'class'=>'button'
        )
    );
    
    echo '&nbsp;&nbsp;';


  echo $html->link(
        'Aceptar',
        'javascript:',
        array(
            'class'=>'button envio_formulario',
            'id'=>'recarga',
        )
    );
    echo '</div>';
    echo '</td></tr></table>';
    echo '</td></tr></table>';

    echo $form->hidden('offset');
    echo $form->end();

    ?>
</div>

<?php   
echo $form->hidden('recarga_minima', array('id'=>'recarga_minima','value'=>$recargaMinima));
echo $form->hidden('recarga_maxima', array('id'=>'recarga_maxima','value'=>$recargaMaxima));



/*echo $form->hidden('comisionF', array('id'=>'comisionF', 'value'=>$comisionTCPF));
echo $form->hidden('etiqComisionF', array('id'=>'etiqComisionF', 'value'=>$etiqComisionF));


echo $form->hidden('comisionD', array('id'=>'comisionD', 'value'=>$comisionTCPD));
echo $form->hidden('etiqComisionD', array('id'=>'etiqComisionD', 'value'=>$etiqComisionD));
*/






echo $form->create(
    'Recargatp',
    array(
        'url'=> array(
            'controller'=>'tarjeta_prepagadas',
            'action'=>$this->action
        ),
        'id' => 'paginacion'
    )
);
if(!empty($this->data['Recargatp'])){
    foreach($this->data['Recargatp'] as $k => $v) {
        echo $form->hidden($k);
    }
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>


<script>


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




$(function(){    



/*function tipoTarjeta(tarjeta,cedula){
    tarjeta = tarjeta.trim();
    nroCedula = cedula.substring(2,10);
    cedula= nroCedula.trim();

    $.ajax({
        type: "GET",
        url: '<?php echo Router::url("/tarjeta_prepagadas/listarTarjeta/"); ?>' + cedula,
        dataType: "json",
        cache:false,
        beforeSend: function() {
            ajaxPendiente = true;
        },
        success: function(source) {
            if(source){
                ajaxPendiente = false;
                $.each(source, function(index, value){
                    if(index == 'fisica'){
                        nro =  value.substring(0,16);
                        if(nro == tarjeta){
                            $('#tipo_tarjeta').val('F');
                        }    
                    }    
                    if(index == 'virtual'){
                        nro =  value.substring(0,16);
                        if(nro == tarjeta){
                            $('#tipo_tarjeta').val('D');
                        }    
                    }    
                });    
            }   
        },
        error: function(source){
            //alert("PAso 3");
        }
    });

}
*/


    

    $('.seleccion_afiliado').click(function(){
        $('#mostrar_tabla').show();
        if($(this).prop('checked')){
            let arr = $(this).data('alias').split(';'); 
            $('#tarjetatpc').val(arr[5]);
            $('#txtNombre').val(arr[1]);
            $('#recarga_maxima').val(arr[2]);
            $('#txtCedula').val(arr[3]);

            nroTarjeta = arr[5];
            cedula = arr[3];
       
            var desTarjeta = nroTarjeta.substring(0,4) + "****" + nroTarjeta.substring(12,16);
            $('#txtTarjeta').val(desTarjeta);

            var tipo = nroTarjeta.substring(16,17);

            $('#tipo_tarjeta').val(tipo);


            $('#txtEmail').val(arr[4]);  
            if(!$(this).data('alias').length){
                $('#txtCuenta').removeAttr('readonly');
                $('#txtNombre').removeAttr('readonly');
                $('#txtCedula').removeAttr('readonly');
                $('#txtEmail').removeAttr('readonly');
                $('#txtTarjeta').removeAttr('readonly');
                
            }else{
                $('#txtCuenta').attr('readonly',true);
                $('#txtNombre').attr('readonly',true);
                $('#txtCedula').attr('readonly',true);
                $('#txtTarjeta').attr('readonly',true);
            }
 
                $('#montoRecarga').val('');
                $('#totalRecarga').val('0,00');
                $('#totalComision').val('0,00');


        }
    });
});


    $(function(){
        $('#RecargaRecargaterceroForm').validate({
                rules:{

                    'data[Recarga][cuentaCash]':{
                        required:true
                    },                    
                    'data[Recarga][alias]':{
                                required:true
                        },                                        
                    'data[Recarga][montoRecarga]':{
                            required:true, 
                            numberVE:true
                    },                                     
                },
                messages:{
                    'data[Recarga[cuentaCash]':{
                        required:<?php echo _m('seleccion.requerida',array('la tarjeta a Debitar'), true) ?>

                    },
                    'data[Recarga][alias]':{
                            required:<?php echo _m('tcp_seleccione_afiliado',array(''), true) ?>
                    },

                    'data[Recarga][montoRecarga]':{
                        required:<?php echo _m('campo.requerido',array('monto de la recarga'), true) ?>
                    },
                },
        });

    });
    $('#montoRecarga').focus(function(e){
         $('#mensajeError').hide();  
    });
    

    $('#montoRecarga').blur(function (e) {
            mostrar = $('#tipo_tarjeta').val();
            var swValidacion = 0;
            var monto = $('#montoRecarga').val();  
            var mtoVal = monto.replace(".", '');
            var mtoVal = mtoVal.replace(",", '.');
            mtoVal = parseFloat(mtoVal);

            var montoRecMin = $('#recarga_minima').val(); 
            var mtoMinVal = montoRecMin.replace(".", '');            
            var mtoMinVal = montoRecMin.replace(",", '.');
            mtoMinVal = parseFloat(mtoMinVal);             

            var montoRecMax = $('#recarga_maxima').val();  
            var mtoMaxVal = montoRecMax.replace(".", '');
            var mtoMaxVal = montoRecMax.replace(",", '.');
            mtoMaxVal = parseFloat(mtoMaxVal);             


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
                if($('#tipo_tarjeta').val()=='D'){
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
               comisionR = round(comision, 2);
               var total = monto + comisionR;
               totalF = formatear(total);
               comisionF = formatear(comisionR);
                $('#totalRecarga').val(totalF);
                $('#totalComision').val(comisionF);
          }//fin de swValidacion      
            
        });

      
</script>
