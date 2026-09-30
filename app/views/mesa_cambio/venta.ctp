<?php
/**
 * Formulario de mesa de cambio ventas
**/
?>
<div>
    <h2 class="user">Venta de Dívisas</h2>
    <div class="line_title_orange"></div>
</div>


<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<?php
    echo $form->create('MesaCambio',
                array(
            'url'=> array(
                        'controller'=>'mesa_cambio',
                        'action'=>'venta'
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

     echo $form->hidden('moneda');
        echo $form->hidden('monto');
        echo $form->hidden('cuentaOrigen');
        echo $form->hidden('producto');
        echo $form->hidden('tasaCompra');
        echo $form->hidden('tasaMenudeo');
        echo $form->hidden('comisionMenudeo');
        echo $form->hidden('comisionMesa');
        echo $form->hidden('parametroMenudeo');
        echo $form->hidden('porcentajeMenudeo');
        echo $form->hidden('porcentajeMesa');
        echo $form->hidden('currency_source_cod');
        echo $form->hidden('currency_target_cod');
        echo $form->hidden('exchange_operation_cod');
        echo $form->hidden('product_source_cod');
        echo $form->hidden('product_target_cod');
        echo $form->hidden('exchange_type_cod');
        echo $form->hidden('exchange_cod');
        echo $form->hidden('currency_source_codMesa');
        echo $form->hidden('currency_target_codMesa');
        echo $form->hidden('exchange_operation_codMesa');
        echo $form->hidden('product_source_codMesa');
        echo $form->hidden('product_target_codMesa');
        echo $form->hidden('exchange_type_codMesa');
        echo $form->hidden('exchange_codMesa');
        echo $form->hidden('nameProduct');
        echo $form->hidden('customer_type_cod', array('id'=>'customer_type_cod', 'value'=>$tipo));

    echo $html->tag(
        'table',
        null,
        array(
                    'class'=>'formulario',
                    'cellpadding'=>'0',
                    'cellspacing'=>'0',
                    'border'=>'0',
                    'align'=>'center'
        )
    ); 
    

    echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la venta.</td></tr>';

    $input_debitar = array(
            'label'=>'<span class="requerido">* </span>Cuenta Origen:',
            'type'=>'select',
            'options'=>$cuentas
    );

    if(count($cuentas) >= 1) {
        $input_debitar['empty'] = 'Seleccione';
    }else{
        $input_debitar['empty'] = 'No posee cuentas que apliquen';
    }

    echo $form->input(
            'cuentaDebitar',
            $input_debitar
    );    

    if ($form->isFieldError('cuentaDebitar')){
           echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaDebitar',
        array(
                'requerido'=>_m('seleccion.requerida',array('la cuenta origen'))
        )
        ).'</td></tr>';
    }


    if($sw_cuentaDebitar == 1){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo"><span style="color:red">'._m('usuario.no.autorizado').'</span></td></tr>';
    }


    $input_creditar = array(
            'label'=>'<span class="requerido">* </span>Cuenta Destino:',
            'type'=>'select',
            'options'=>$cta_acreditar,
    );

    if(count($cta_acreditar) > 1) $input_creditar['empty'] = 'Seleccione';

    echo $form->input(
            'cuentaCreditar',
            $input_creditar
    );

    if ($form->isFieldError('cuentaCreditar')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaCreditar',
        array(
                    'nroCuenta'=>_m('formato.invalido',array('la cuenta destino')),
                    'comparacion'=>_m('comparacion.cuentas',array('la cuenta destino'))
        )
            ).'</td></tr>'; 
    }

    $input_origen = array(
        'label'=>'<span class="requerido">* </span>Origen de Fondos:',
        'type'=>'select',
        'options'=>$origenFondos
    );

    if(count($origenFondos) > 1) $input_origen['empty'] = 'Seleccione';

    echo $form->input(
            'origenFondos',
            $input_origen
    );

    if ($form->isFieldError('origenFondos')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('origenFondos',
        array(
                    'requerido'=>_m('seleccion.requerida',array('el origen de fondos'))
        )
        ).'</td></tr>';
    }


    $input_motivo = array(
        'label'=>'<span class="requerido">* </span>Motivo:',
        'type'=>'select',
        'options'=>$motivos
    );


    if(count($motivos) > 1) $input_motivo['empty'] = 'Seleccione';
    
    echo $form->input(
            'motivos',
            $input_motivo
    );  

    if ($form->isFieldError('motivos')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('motivos',
        array(
                    'requerido'=>_m('seleccion.requerida',array('el motivo'))
        )
        ).'</td></tr>';
    }   


    echo $form->input(
        'monto_divisa',
         array(
            'label'=>'<span class="requerido">* </span>Monto Divisas:',
            'type'=>'text',
            'class'=>'monto pago',
                  )
    );


    if ($form->isFieldError('monto_divisa')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('monto_divisa',
        array(
            'numerico'=>_m('formato.invalido',array('el monto en divisas')),
            'requerido'=>_m('campo.requerido',array('el monto en divisas')),
            'sin_decimales'=>_m('sin.decimales')                   
        )
        ).'</td></tr>';
    }

    if($errorDivisa==1){
        echo '<tr id="mtoDivisa"><td class="etiqueta">&nbsp; </td><td class="campo"><label><span style="color:red;">'._m('sin.decimales') .'</span></label></td></tr>';
    }




     echo $form->input(
            'tasa',
            array(
        'label'=>'<span id="tasa"><span class="requerido" >* </span>Tasa de Venta:</span>',
        'type'=>'text',
        'value'=>'0,00',
        'class'=>'monto pago',
         'readonly'=>'readonly'
                
            )
    );

      echo $form->input(
            'comision',
            array(
        'label'=>'<span class="requerido">* </span>Comisi&oacute;n Bs.:',
        'type'=>'text',
        'class'=>'monto pago',
                'value'=>'0,00',
                'readonly'=>'readonly'
            )
    );
        
        echo $form->input(
            'total',
            array(
        'label'=>'<span id="total"><span class="requerido" >* </span>Total Bs.:</span>',
        'type'=>'text',
        'class'=>'menudeo',
                'value'=>'0,00',
                'readonly'=>'readonly'
            )
    );
      

    echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
     echo '<tr id="wait"><td colspan="2"><span class="requerido2">Calculando..... </span></td></tr>';
     
    

    echo '<tr id="aceptar"><td>&nbsp;</td><td class="submit">';

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

    function formatear(valor) {
        var num = parseInt(parseFloat(valor) * 100) / 100;
        var x = Number(num).toFixed(2);
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

        $('#MesaCambioTasa').val('0,00'); 
        $('#MesaCambioMonto').val('');
        $('#MesaCambioComision').val('0,00');
        $("#MesaCambioTotal").val('0,00');
        $("#MesaCambioMontoDivisa").val('');

       $('#wait').hide();
      

        var texto = $('select[name="data[MesaCambio][cuentaDebitar]"] option:selected').text();
        if(texto!=='Seleccione' && texto!=='No posee cuentas que apliquen'){            
            var valor = $('select[name="data[MesaCambio][cuentaDebitar]"] option:selected').val();
            var result = valor.split('-');
            var moneda = result[4].trim();            
            var limite = result[3].trim();
            var name = result[6].trim();
            $('#MesaCambioNameProduct').val(name); 
          
            limite = limite.replace(/,/g, '.');

            $('#MesaCambioLimite').val(formatear(limite));

            var monto = $('#MesaCambioMontoDivisa').val();
            if (monto === '') {
                monto = 0;
            } else {
                monto = monto.replace(/[\,\.]/g, '');
                monto = parseInt(monto) / 100;
            }

            var tasa = result[2].trim();
            var porcentaje = result[1].trim();
            tasa = tasa.replace(/,/g, '.');
            porcentaje = porcentaje.replace(/,/g, '.');             
            var total = monto * tasa;
            var comision = total * porcentaje;

            //total = parseInt(total * 100) / 100;
            //comision = parseInt(comision * 100) / 100;

            if (monto === 0) {
                tasaEfectiva = 0;
            }else{
                tasaEfectiva = (total - comision) / monto;
            }

            $('#MesaCambioComisionPorc').val(porcentaje);
            $('#MesaCambioTasaVenta').val(formatear(tasa));
            $('#MesaCambioTasa').val(formatear(round(tasaEfectiva))); 
            $("#MesaCambioTotal").val(formatear(round(total)));
        }

                                                                    

        $('#MesaCambioCuentaDebitar').change(function(){
            var valor = $('select[name="data[MesaCambio][cuentaDebitar]"] option:selected').val();
            var result = valor.split('-');
            var moneda = result[4].trim();
            var producto = result[5].trim();
            var limite = result[3].trim();
            var name = result[6].trim();
             $('#MesaCambioNameProduct').val(name); 
        
           
            limite = limite.replace(/,/g, '.');
            var porcentaje = result[1].trim();                
            porcentaje = porcentaje.replace(/,/g, '.');
            
           $('#MesaCambioLimite').val(formatear(limite)); 
            $('#MesaCambioCuentaOrigen').val(result[0].trim());
                        
            var monto = $('#MesaCambioMontoDivisa').val();
                 monto = monto.replace(/,/g, '.'); 
                              
                    
                 $.ajax({
                    type: "GET",
                    url: '<?php echo Router::url("/mesa_cambio/obtener_tasas/"); ?>' + 'VTA' +'/'+ moneda+'/'+producto +'/'+customer_type_cod,
                    dataType: "json", 
                    beforeSend: function() {
                        ajaxPendiente = true;
                         $('#aceptar').hide();
                          $('#MesaCambioTasa').val('0,00'); 
                         $('#MesaCambioMonto').val('');
                         $('#MesaCambioComision').val('0,00');
                         $("#MesaCambioTotal").val('0,00');
                         $("#MesaCambioMontoDivisa").val('');
                         $('#MesaCambioTasa').attr('readonly', true);
                         $('#MesaCambioMontoDivisa').attr('readonly', true);
                        
                         $('#wait').show();
                                                           
                    }, 
                    success: function(data) {
                        ajaxPendiente = false;                       
                      
                        
                         var tasa = data.MND.rate;

                         var porcentajeMU = data.MND.fee_percentage;
                         var porcentajeMS = data.MDC.fee_percentage;

                                       

                        $('#MesaCambioTasaMenudeo').val(tasa);
                        $('#MesaCambioParametroMenudeo').val(data.limiteMenudeo);
                        $('#MesaCambioMontoDivisa').attr('readonly', false);
                        $('#MesaCambioPorcentajeMenudeo').val(porcentajeMU);
                        $('#MesaCambioPorcentajeMesa').val(porcentajeMS);
                        $('#MesaCambioCurrencySourceCod').val(data.MND.currency_source_cod);
                        $('#MesaCambioCurrencyTargetCod').val(data.MND.currency_target_cod);
                        $('#MesaCambioExchangeOperationCod').val(data.MND.exchange_operation_cod);
                        $('#MesaCambioProductSourceCod').val(data.MND.product_source_cod);
                        $('#MesaCambioProductTargetCod').val(data.MND.product_target_cod);                 
                        $('#MesaCambioExchangeTypeCod').val(data.MND.exchange_type_cod);
                        $('#MesaCambioExchangeCod').val(data.MND.exchange_cod);
                        $('#customer_type_cod').val(data.MND.customer_type_cod);
                        $('#MesaCambioCurrencySourceCodMesa').val(data.MDC.currency_source_cod);
                        $('#MesaCambioCurrencyTargetCodMesa').val(data.MDC.currency_target_cod);
                        $('#MesaCambioExchangeOperationCodMesa').val(data.MDC.exchange_operation_cod);
                        $('#MesaCambioProductSourceCodMesa').val(data.MDC.product_source_cod);
                        $('#MesaCambioProductTargetCodMesa').val(data.MDC.product_target_cod);                 
                        $('#MesaCambioExchangeTypeCodMesa').val(data.MDC.exchange_type_cod);
                        $('#MesaCambioExchangeCodMesa').val(data.MDC.exchange_cod);

                        if(monto!=0.00){


                        
                        if(round(monto) <= round($('#MesaCambioParametroMenudeo').val())){

                            var tasa = $('#MesaCambioTasaMenudeo').val();
                            var  porcentaje = $('#MesaCambioPorcentajeMenudeo').val();
                            var total = monto * tasa;
                           
                            var comision = round(total * porcentaje);

                         $('#MesaCambioTasa').val(formatear(tasa)); 
                         $('#MesaCambioMonto').val(formatear(total));
                         $('#MesaCambioTasa').attr('readonly', true);
                         $('#MesaCambioMontoDivisa').attr('readonly', false);
                         $('#MesaCambioComision').val(formatear(comision));

                         
                             

                        }else{

                        $('#MesaCambioTasa').val('0,00');
                        $('#MesaCambioMontoDivisa').attr('readonly', false);
                        $("#MesaCambioTotal").val('0,00'); 
                        $('#MesaCambioTasa').attr('readonly', false);

                         }
                     }
                         
                        
                         $('#MesaCambioTasa').val('0,00'); 
                         $('#MesaCambioMonto').val('');
                         $('#MesaCambioComision').val('0,00');
                         $("#MesaCambioTotal").val('0,00');
                         $("#MesaCambioMontoDivisa").val('');
                         $('#wait').hide();                       
                         $('#aceptar').show();

                },
                    error: function(data){

                        
                      
                }
               

            });  
        });
            
      
        $('#MesaCambioMontoDivisa').on("keyup", function (e) {
            $('#mtoDivisa').css('display','none'); 
            var valor = $('select[name="data[MesaCambio][cuentaDebitar]"] option:selected').val();
            var result = valor.split('-');
        
            var monto = $('#MesaCambioMontoDivisa').val();
            if (monto === '') {
                monto = 0;
            } else {                 
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
            }

            if (monto!="0,00"){ 


             if(round(monto) <= round($('#MesaCambioParametroMenudeo').val())){



           
            var tasa = $('#MesaCambioTasaMenudeo').val();
            var  porcentaje = $('#MesaCambioPorcentajeMenudeo').val();
            var total = monto * tasa;
            var comision = round(total * porcentaje);
            
            $('#MesaCambioTasa').val(formatear(tasa)); 
            $('#MesaCambioComisionPorc').val(porcentaje);
            $('#MesaCambioMonto').val(formatear(total));
            $('#MesaCambioTasa').attr('readonly', true);
            $('#MesaCambioComision').val(formatear(comision));
            $("#MesaCambioTotal").val(formatear(total - comision));


            }else{

                var tasa = $('#MesaCambioTasaMesa').val();
               
                var  porcentaje = $('#MesaCambioPorcentajeMesa').val();
                var total = monto * tasa;
                var comision = total * porcentaje;


                $('#MesaCambioTasa').val('0,00');
                $('#MesaCambioComision').val('');
                $('#MesaCambioMontoDivisa').attr('readonly', false);
                $("#MesaCambioTotal").val('0,00'); 
                $('#MesaCambioMonto').val(formatear(total));
                $('#MesaCambioTasa').attr('readonly', false);

            }
            }
            

            
                    
        });

         $('#MesaCambioTasa').on("keyup", function (e) {

            $('#mtoDivisa').css('display','none'); 

           var valor = $('select[name="data[MesaCambio][cuentaDebitar]"] option:selected').val();
            var result = valor.split('-');
            var moneda = result[4].trim();
            var producto = result[5].trim();
            var limite = result[3].trim();
            var name = result[6].trim();
            $('#MesaCambioNameProduct').val(name);           
            limite = limite.replace(/,/g, '.');
            var porcentaje = result[1].trim();                
            porcentaje = porcentaje.replace(/,/g, '.'); 
            var monto = $('#MesaCambioMontoDivisa').val();
            if (monto === '') {
                monto = 0;
            } else {
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

            }

          if(round(monto) <= round($('#MesaCambioParametroMenudeo').val())){



            var tasa = $('#MesaCambioTasaMenudeo').val();
            var  porcentaje = $('#MesaCambioPorcentajeMenudeo').val();
            var total = monto * tasa;
            var comision = round(total * porcentaje);

            $('#MesaCambioTasa').val(formatear(tasa)); 
            $('#MesaCambioComision').val(formatear(comision));
            $("#MesaCambioTotal").val(formatear(round(total - comision)));
            $('#MesaCambioComisionPorc').val(porcentaje);
            $('#MesaCambioMonto').val(formatear(total));
            $('#MesaCambioTasa').attr('readonly', true);    

            }else if($('#MesaCambioParametroMenudeo').val()=="0,00"){

                       $('#MesaCambioTasa').val('0,00'); 
                        $('#MesaCambioMonto').val('');
                         $('#MesaCambioComision').val('0,00');
                         $("#MesaCambioTotal").val('0,00');
                         $("#MesaCambioMontoDivisa").val('');



           
            }else{

                
                var tasa = $(this).val();
                tasa=tasa.replace(/,/g, '.');
                var  porcentaje = $('#MesaCambioPorcentajeMesa').val();
                var total = monto * tasa;
                var comision = total * porcentaje;

               
                    $('#MesaCambioTotal').show();
                    $('#total').show();
                    $('#MesaCambioMonto').val(formatear(total));
                    $("#MesaCambioTotal").val(formatear(round(total - comision)));
                    $('#MesaCambioComision').val(formatear(round(comision)));

            }
   
                    
    });

               


    $('#MesaCambioVentaForm').validate({
        rules:{
            'data[MesaCambio][cuentaDebitar]':{
                    required:true
            },
            'data[MesaCambio][cuentaCreditar]':{
                    required:true
            },
            'data[MesaCambio][monto_divisa]':{
                    required:true, numberVE:true
            },
            'data[MesaCambio][origenFondos]':{
                    required:true
            },
            'data[MesaCambio][motivos]':{
                    required:true                        
            },
        },
        messages:{
            'data[MesaCambio][cuentaDebitar]':{
                    required:<?php echo _m('seleccion.requerida',array('la cuenta origen'), true) ?>
            },
            'data[MesaCambio][cuentaCreditar]':{
                    required:<?php echo _m('seleccion.requerida',array('la cuenta destino'), true) ?>
            },
            'data[MesaCambio][monto]':{
                    required:<?php echo _m('campo.requerido',array('el monto en divisas'), true) ?>,
                    numberVE:<?php echo _m('formato.invalido',array('el monto en divisas'), true) ?>
            },
            'data[MesaCambio][monto_divisa]':{
                    required:<?php echo _m('campo.requerido',array('el monto en divisas'), true) ?>,
                    numberVE:<?php echo _m('formato.invalido',array('el monto en divisas'), true) ?>,
                    sin_decimales: function(e){
                        return "Valor"
                    }    

            },
        }
    });   

});
</script>






