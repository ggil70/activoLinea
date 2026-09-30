<?php
/**
 * ----------- Formulario de pago de tarjetas propias en banco activo ----------
**/

$p = explode(',', $params['avance.extra.plazos']);
$plazos = array_combine($p, $p);
$tasa_vig = $params['avance.extra.tasa.vig'];

// Datos del formulario
$formulario['inicio'] = $form->create('AvanceEfectivo',
		array(
			'url'=> array(
				'controller'=>'avance_efectivo',
				'action'=>'avance_extra',
                                'novalidate'=>false
			),
			'inputDefaults'=>array(
				'div'=>false,
                                'error'=>array(
                                    'class'=>'error'
                                )
			)
		)
	);

// Cuentas para el abono del avance
$formulario['cuentas'] = $form->input(
            'cuentaAbono', array(
                'label'=>false,
                'required'=>'true',
                'type'=>'select',
		'options'=>$cuentas
            )
        );

// Sumatoria de todos los montos por tarjeta ss
$formulario['monto_total'] = $form->input(
        'montoTotal', array(
                'label'=>false,
                'type'=>'text',
                'readonly'=>'readonly',
                'value'=>'0,00',
                'minavance' => $params['avance.extra.monto.min']
            )
        );

// Tarjetas disponibles para el avance
$formulario['tarjetas'] = array();
$formulario['tdcvalores'] = array(); // valores de tasa y comision por tarjeta
$i = 0;
foreach($tarjetas as $t){
    $error_msg = "";
  
    if($t['error'])
        $error_msg ='<label generated="true" class="error">'._m('monto.max.avance', true).'</label>';
    
    // plazo solicitado por tarjetas
    $p = explode(',', $t['plazoex']);
    $plazos = array_combine($p, $p);
    $inp_plazo = $form->input(
                'AvanceEfectivo.plazo_tarjeta.'.$t['numero'],array(
                    'label'=>false,
                    'type'=>'select',
                    'class'=>'plazo',
                    'options' => $plazos
                )
            );
    
    // Monto solicitado por tarjetas
    $inp = $form->input(
                'AvanceEfectivo.monto_tarjeta.'.$t['numero'],array(
                    'label'=>false,
                    'class'=>'monto',
                    'id'=>'monto_avance',
                    'minavance'=>$params['avance.extra.monto.min'],
                    'before'=>'<td>'.$t['alias'].'</td><td>'.$t['msk'].'</td><td>',
                    //'before'=>'<td>'.$t['alias'].'</td><td>'.$t['msk'].'</td><td>'.number_format($t['saldo'],2,',','.').'</td><td>',
                    'after'=>$error_msg.'</td><td>'.$inp_plazo.'</td><td>0,00</td><td>0,00</td>',
                )
            );
    array_push($formulario['tarjetas'],$inp);
    
    // Crea campos ocultos de tasa y comision por tdc 
    $i = $i + 1;
    $hidd1 = $form->hidden(
            'tasa'.$i, array(
            'value'=>$t['tasatdc'],
            )
        );  
    array_push($formulario['tdcvalores'],$hidd1);
    
    $hidd1 = $form->hidden(
            'comi'.$i, array(
            'value'=>$t['comisiontdc'],
            )
        );     
     array_push($formulario['tdcvalores'],$hidd1);   
    
    
}  




// Boton de aceptar
 $formulario['aceptar'] = $form->submit(
            'Aceptar', array(
                'type'=>'submit',
                'div' => false,
                'class' => 'button submit'
            )
        );
 
 
 // Errores de Formulario, no contiene errores de campos, aqui se almacenan los errores
 // Generales del formulario
 
 $formulario['errores'] = $form_errores;

// --------------------- Fin de creaci?n del formulario ------------------------
?>

<div>
<h2 class="user">ExtraCr&eacute;dito Efectivo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>
<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style="margin-left: 60px;">
    <?php echo $formulario['inicio']; ?> 
    <table class="formulario">
        
        <tr><td colspan="2">A continuaci&oacute;n ingrese los datos para solicitar ExtraCr&eacute;dito Efectivo:</td></tr>
        <tr>
            <td class="etiqueta">
                <label>
                    <span class="requerido">* </span>
                    Numero de cuenta para Abono: 
                </label>
            </td>
            <td class="campo">
                <?php echo $formulario['cuentas']; ?>
            </td>
        </tr>
        <?php // errores del formulario
        if(!empty($formulario['errores'])){
            foreach ($formulario['errores'] as $error){
            echo('<tr><td colspan = "2"><label class="error">'.$error.'</label></td></tr>');
            }
        } 
        ?>
    </table>
    <table class="tabla extra">
        <thead>
            <tr>
                <td>Alias</td>
                <td>N&uacute;mero</td>
                <td>Monto Solicitar</td>
                <td>Plazo</td>
                <td>Com. flat <?php //echo(' '.$params['avance.extra.comision'].'%'); ?></td>
                <td>* 1era cuota</td>
            </tr>
        </thead>
        <tbody>
            <?php

            foreach($formulario['tarjetas'] as $t){
                echo('<tr>');
                echo($t);
                echo('</tr>');
            }
            ?>
            <tr>
                <td colspan="2">Monto Total Solicitado Bs.:</td>
                <td><?php echo $formulario['monto_total']; ?></td>
            </tr>
        </tbody>         
    </table>
    <div><b>NOTA:</b><br>Estimado Cliente, el monto de la primera cuota es un c&aacute;lculo aproximado. Si alguna de sus Tarjetas se encuentra en condici&oacute;n de atraso o bloqueo, las mismas no podr&aacute;n optar a la solicitud de ExtraCr&eacute;dito Efectivo.
 </div>
    <table style="width:700px;"> <!-- Se forzo el ancho para poder acomodar el boton a la derecha -->
        <tr>
            <td class="submit">
                <?php 
            if(!empty($formulario['tarjetas']))
            { 
                echo $formulario['aceptar'];

            } 
        ?>
            </td>
        </tr>
        
    </table>
    
    <div class="espacio_vertical_20"></div>
 <?php
             foreach($formulario['tdcvalores'] as $t2){

                echo($t2);

            }
 ?>   
    <input type="hidden" name="data[AvanceEfectivo][monto_min]" id="monto_min" value=<?php echo('"'.$params['avance.extra.monto.min'].'"'); ?>>
    <input type="hidden" name="data[AvanceEfectivo][comision]" id="comision" value=<?php echo($params['avance.extra.comision']); ?>>
    <input type="hidden" name="data[AvanceEfectivo][tasa_vig]" id="tasa_vig" value=<?php echo $tasa_vig ?>>
    
    <?php echo $form->end(); ?>
    <script type="text/javascript">
        $( document ).ready(function() {       
                        
            $("#AvanceEfectivoAvanceExtraForm").validate();
            montoTotalSolicitud = 0;
            $(".monto").blur(calculaComisionPago);
            $(".plazo").change(calculaComisionPago);


        $('#monto_avance').blur(function (e) {
            var monto = $('#monto_avance').val();  

            //Si el monto solo posee un solo decimal, hay que completar los dos decimale
            var arreglo_monto = monto.split(',');
            var entero = arreglo_monto[0];
            var decimal = arreglo_monto[1];

            //validar que los decimales sean 2
            if (decimal.length == 1){
               monto = monto + '0';
               $('#monto_avance').val(monto);
            }
        });


            
        });

        // Calcula la comisi?n flat de cada tarjeta y actualiza el monto total
        // de la transacci?n ademas calcula el monto del pago mensual
        function calculaComisionPago()
        {

                //comision_tasa = parseFloat($("#comision").val());                             
                //credito_tasa = (<?php echo $tasa_vig ?>/12)/100;
                 i = 0;                
                montoTotalSolicitud = 0;
                pago_mens = 0;
                var monto = 0;
                $.each($(".monto"), function(obj){
                    comision = 0;
                    pago_mens = 0;
                    i = i +1;
                    Ta = "#AvanceEfectivoTasa".concat(i);
                    Co = "#AvanceEfectivoComi".concat(i);
                    credito_tasa = (parseFloat($(Ta).val())/12)/100;
                    comision_tasa = parseFloat($(Co).val()); 
                    
                    monto = parseFloat($(this).val().replace(/\./g,'').replace(',','.'));
                    if(!isNaN(monto))
                    {   
                        // Math.round10 es un closure que se a?ade a Math y que redondea los decimales
                        comision = Math.round10(((monto * comision_tasa) / 100),-2);
                        montoTotalSolicitud += monto;

                        //EL pago mensual
                        plazo = $(this).closest('td').nextAll('td').first().find('select').val();
                        pago_mens = Math.round10(monto * (credito_tasa / (1 - Math.pow((1 + credito_tasa),(-plazo)))),-2);

                    }
                    $(this).closest('td').next('td').next('td').next('td').text(pago_mens.toLocaleString("es-VE",{ minimumFractionDigits: 2 }));
                    $(this).closest('td').next('td').next('td').text(comision.toLocaleString("es-VE",{ minimumFractionDigits: 2 }));
                });
                $("#AvanceEfectivoMontoTotal").val(montoTotalSolicitud.toLocaleString("es-VE",{ minimumFractionDigits: 2 })); 
        }




        
    </script>

</div>

