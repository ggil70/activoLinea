<?php
/**
 * Formulario Cambio PIN TPC
**/
?>
<div>
<h2 class="user">Generaci&oacute;n de Clave / Tarjeta Prepago CASH</h2>
<div class="line_title_orange"></div>
</div>


<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Cambiar Clave Tarjeta</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>


<!--<div class="contenedor_formulario">-->

 <div style="width:800px;margin:0 auto">

<span class="requerido"><p style="font-size:13px">Modifique su clave de Tarjeta Prepago CASH y presione "Aceptar"</p> </span>



<div style="width:400px;float:left">    
    
    <br>
	<?php

	echo $form->create('Cambiopin',
            array(
		'url'=> array(
                    'controller'=>'tarjeta_prepagadas',
                    'action'=>'cambiopin'
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
                    'align'=>'center'
		)
	);
    ?>


   <?php 
    echo $form->input(
        'pinCambio',
        array(
            'label'=>'<span class="requerido">* </span>Tarjeta:',
            'type'=>'text',
            'class'=>'numerico',
            'value'=>$nroTarjeta,
            'readonly'=>'readonly',
            'id'=>'nroTarjeta',
            )
    );  
    ?> 

    <tr>
       <td class="etiqueta"><span class="requerido">* </span>Fecha de Vencimiento:</td>
     <td class="campo">
    
    <?php    echo $form->input(
        'fechaVencimiento',
        array(
            'before'=>false,
            'between'=>false,
            'label'=>false,
            'type'=>'text',
            'id'=>'fechaVencimiento',
            'class'=>'numerico',
            'maxlength'=>4
        )
    );
    ?>
       </td>
    </tr>

    <tr>
       <td class="etiqueta"><span class="requerido">* </span>CVV:</td>
            <td class="campo">
    <?php    echo $form->input(
        'codSeguridad',
        array(
            'before'=>false,
            'between'=>false,
            'label'=>false,
            'type'=>'text',
            'id'=>'codSeguridad',
            'class'=>'numerico',
            'maxlength'=>3
        )
    );
    ?>
       </td>
    </tr>

    <tr>
       <td class="etiqueta"><span class="requerido">* </span>Clave nueva:</td>
       <td class="campo">
    <?php 
        echo $form->input(
            'clave_nueva',
            array(
                'label'=>false,
                'type'=>'password',
                'class'=>'numerico',
                'id'=>'clave_nueva',
                'maxlength'=>4,
                'minLength'=>4,
                'before'=>false,
                'between'=>false,
            )
        );
    ?>
       </td>
    </tr>

    <tr>
       <td class="etiqueta"><span class="requerido">* </span>Confirmar clave:</td>
       <td class="campo">
    <?php 
        echo $form->input(
            'clave_repetir',
            array(
                'label'=>false,
                'type'=>'password',
                'class'=>'numerico',
                'id'=>'clave_repetir',
                'maxlength'=>4,
                'minLength'=>4,
                'before'=>false,
                'between'=>false,
            )
        );
    ?>
       </td>
    </tr>

    <?php
    echo '<tr><td class="etiqueta">&nbsp; </td><td class="campo"><label id="errorClave" style="display:none"><span style="color:red;">Las claves ingresada son diferentes</span></label><label id="errorClave2" style="display:none"><span style="color:red;">La clave no debe poseer tres(3) digitos iguales de forma consecutivos</span></label></td></tr>';



                    //$('#clave_nueva').val('');
                    //$('#clave_repetir</span></label></td></tr>';
    ?>    






<?php
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
<div style="margin-left:90px;width:300px;float:left;">
<br>    
<?php echo _m('mensaje.cambio.tdd.pin'); ?>
</div>


</div>


<script>

   function valDigitoClave(claveNueva){
       d1 = claveNueva.substring(0,1);
       d2 = claveNueva.substring(1,2);
       d3 = claveNueva.substring(2,3);
       d4 = claveNueva.substring(3,4);
       resp = 0;
       if((d1 == d2) && (d1 == d3)){
           resp=1;
       }else{
           if((d2 == d3) && (d2 == d4)){
               resp = 1;
           } 

       }
       return resp
   }



   function valClave(){
        claveNueva = $('#clave_nueva').val();
        claveRepetir = $('#clave_repetir').val();
        if(claveNueva != '' && claveRepetir != '' ){
            if(claveNueva == claveRepetir){
                $('#errorClave').css('display','none');
                $('#errorClave2').css('display','none');
            }else{
                $('#errorClave').css('display','inline');
                $('#errorClave2').css('display','none');
                $('#clave_repetir').val('');
                $('#clave_nueva').val('');
            }
        }else{
              $('#errorClave').css('display','none');
              $('#errorClave2').css('display','none');
        }
   } 





    
    $(function(){


    $('#clave_nueva').blur(function (e) {
        claveNueva = $('#clave_nueva').val();


        resp = valDigitoClave(claveNueva);

        if(resp==1){
            $('#errorClave').css('display','none');
            $('#errorClave2').css('display','inline'); 
            $('#clave_nueva').val('');
            $('#clave_repetir').val('');
            //$('#clave_nueva').focus();
        }else{
            $('#clave_repetir').val('');
            valClave();            
            $('#errorClave').css('display','none');
            $('#errorClave2').css('display','none');
        }                

    });


    $('#clave_repetir').blur(function (e) {
        valClave();
    });



        
	$('#CambiopinCambiopinForm').validate({
            rules:{
                'data[Cambiopin][fechaVencimiento]':{
                    required:true,
                    minlength:4, 
                    maxlength:4,                    

                },
                'data[Cambiopin][codSeguridad]':{
                    required:true,
                    minlength:3, 
                    maxlength:3,                    

                },
                'data[Cambiopin][clave_nueva]':{
                    required:true,
                    minlength:4, 
                    maxlength:4,   

                },

                'data[Cambiopin][clave_repetir]':{
                    required:true,
                    minlength:4, 
                    maxlength:4, 
                },

            },
            messages:{
                'data[Cambiopin][fechaVencimiento]':{
                    required:<?php echo _m('campo.requerido',array('la Fecha de Vencimiento'), true) ?>,
                },

                'data[Cambiopin][codSeguridad]':{
                    required:<?php echo _m('campo.requerido',array('el codigo de Seguridad '), true) ?>
                },

                'data[Cambiopin][claveNueva]':{
                    required:<?php echo _m('campo.requerido',array('La nueva clave '), true) ?>
                },                
                'data[Cambiopin][claveRepetir]':{
                    required:<?php echo _m('campo.requerido',array('La clave confirmacion'), true) ?>
                },                


            }
	});
});
</script>