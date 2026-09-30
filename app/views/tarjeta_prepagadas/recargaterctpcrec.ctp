<?php
/**
 * Formulario de mesa de cambio comprass
**/
?>
<div>
<h2 class="user">Recarga Tercero / Tarjeta Prepago CASH </h2>

<div class="line_title_orange"></div>
</div>


<div class="paso_nro">1</div>
<div class="paso_desc">Solicitar Informaci&oacute;n Beneficiario</div>

<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Recargar Monto</div>

<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>


<div class="paso_nro">4</div>
<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>


<div class="paso_nro">5</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
    <br><br><br>
	<?php

	echo $form->create('Recargaterctpcrec',
            array(
		'url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'Recargaterctpcrec'
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


    echo $form->hidden('recarga_minima', array('id'=>'recarga_minima','value'=>$recargaMinima));
    echo $form->hidden('recarga_maxima', array('id'=>'recarga_maxima','value'=>$recargaMaxima));
        
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


    echo $form->hidden('comision', array('id'=>'comision', 'value'=>$comisionTCP));
    echo $form->hidden('etiqComision', array('id'=>'etiqComision', 'value'=>$etiqComision));
    ?>


  <?php
    echo '<tr><td colspan="2"><span class="user">Informaci&oacute;n Beneficiario</span></td></tr>';
    echo '<tr><td class="etiqueta" colspan="2"><div class="line_title_orange"></div></td></tr>';

      echo $form->input(
                'cedulaClienteB',
              array(
                'label'=>'<span class="requerido">* </span>C&eacute;dula / Pasaporte Beneficiario:',
                'type'=>'text',
                'readonly'=>'readonly',
                'value'=>$cedulaClienteB
                )
        );


      echo $form->input(
                'nombreBen',
              array(
                'label'=>'<span class="requerido">* </span>Apellidos, nombres Beneficiario:',
                'type'=>'text',
                'readonly'=>'readonly',
                'value'=>$nombreB
                )
        );



  echo $form->input(
            'nroTarjeta',
          array(
            'label'=>'<span class="requerido">* </span>Nro. Tarjeta Beneficiario:',
            'type'=>'text',
            'readonly'=>'readonly',
            'value'=>$nroTarjeta
            )
    );

    $input_ctaCash = array(
            'label'=>'<span class="requerido">* </span>Cuenta Cash a Debitar:',
            'type'=>'select',
            'options'=>$cuentaME,
    );
    
    if(count($cuentaME) >= 1) {
            $input_ctaCash['empty'] = 'Seleccione';
    }            
    
    echo $form->input(
            'cuentaCash',
            $input_ctaCash
    );

    if ($form->isFieldError('cuentaCash')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaCash',
        array(
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
            'label'=>'<span class="requerido">* </span>Monto Comisi&oacute;n:',
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
            'label'=>'<span class="requerido">* </span>Total Recarga:',
            'type'=>'text',
            'class'=>'monto pago',
            'value'=>'0,00',
            'readonly'=>'readonly',
            'id'=>'totalRecarga',
            )
    );




    echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';


   	echo '<tr><td>&nbsp;</td><td class="submit">';

    echo $form->submit(
        'Recargar',
        array(
            'class'=>'button'
        )
	);



    /*
    echo '<tr>
         <td colspan="2"><span class="requerido2">
           El monto de la Recarga desde $ '. formatea_monto_fromWs(_p('tcp_recarga_min')) .' hasta $ '. formatea_monto_fromWs(_p('tcp_recarga_max')) .'
                </span></td>
              </tr>';
    */          


    echo '</table>';
	echo $form->end();



?>
</div>

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
    	$('#RecargaterctpcrecRecargaterctpcrecForm').validate({
                rules:{
                    'data[Recargaterctpcrec][cuentaCash]':{
                        required:true
                    },

                    'data[Recargaterctpcrec][montoRecarga]':{
                            required:true, 
                            numberVE:true
                    },                    
                },
                messages:{
                    'data[Recargaterctpcrec[cuentaCash]':{
                        required:<?php echo _m('seleccion.requerida',array('la tarjeta a Debitar'), true) ?>
                    },
                    'data[Recargaterctpcrec][montoRecarga]':{
                        required:<?php echo _m('campo.requerido',array('monto de la recarga'), true) ?>
                    },


                },
    	});

    });


    $('#montoRecarga').focus(function(e){
         $('#mensajeError').hide();  
    });
    

    $('#montoRecarga').blur(function (e) {

            var monto = $('#montoRecarga').val();  
            var mtoVal = monto.replace(",", '.');
            mtoVal = parseFloat(mtoVal);

            var montoRecMin = $('#recarga_minima').val();  
            var mtoMinVal = montoRecMin.replace(",", '.');
            mtoMinVal = parseFloat(mtoMinVal);             


            var montoRecMax = $('#recarga_maxima').val();  
            var mtoMaxVal = montoRecMax.replace(",", '.');
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
                var porComision = $('#comision').val();
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





</script>