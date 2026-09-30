<?php
/**
 * ----------- Formulario de pago de tarjetas propias en banco activo ----------
**/

// Datos del formulario
$formulario['inicio'] = $form->create('AvanceEfectivo',
		array(
			'url'=> array(
				'controller'=>'avance_efectivo',
				'action'=>'avance'
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

// Sumatoria de todos los montos por tarjeta
$formulario['monto_total'] = $form->input(
        'montoTotal', array(
                'label'=>false,
                'type'=>'text',
                'readonly'=>'readonly',
                'value'=>'0',
                'minavance' => 1,
            )
        );

// Tarjetas disponibles para el avance
$formulario['tarjetas'] = array();

foreach($tarjetas as $t){
    $error_msg = "";
  
    if($t['error'])
        $error_msg ='<label generated="true" class="error">'._m('monto.max.avance', true).'</label>';
    $inp = $form->input(
                'AvanceEfectivo.monto_tarjeta.'.$t['numero'],array(
                    'label'=>false,
                    'class'=>'monto',
                    'id'=>'monto_avance',
                    'before'=>'<td>'.$t['alias'].'</td><td>'.$t['msk'].'</td><td>',
                    'after'=>$error_msg.'</td><td>0,00</td>',
                    //'maxavance' => $t['saldo']
                )
            );
    array_push($formulario['tarjetas'],$inp);

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
<h2 class="user">Avance de Efectivo</h2>
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
    <?php echo $formulario['inicio']; ?>       
    
            <table class="formulario">
                <tr><td colspan="2">A continuaci&oacute;n ingrese los datos para solicitar Avance de Efectivo:</td></tr>
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
            <table class="tabla">
                <thead>
                    <tr>
                        <td>Alias</td>
                        <td style="width: 150px;">N&uacute;mero</td>
                       <!-- <td>Saldo Disponible</td> -->
                        <td>Monto Solicitar</td>
                        <td>Comisi&oacute;n flat<?php echo(' '.$params['avance.comision'].'%'); ?></td>
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
                        <td colspan="2">Monto Total Solicitado Bs.:</td><td><?php echo $formulario['monto_total']; ?></td>
                    </tr>
                </tbody>         
            </table>
            <div class="espacio_vertical_20"></div>
            <div class="submit pull-right">
                <?php 
                    if(!empty($formulario['tarjetas']))
                    { 
                        echo $formulario['aceptar'];
                        
                    } 
                ?>
            </div>
     
        <input type="hidden" name="data[AvanceEfectivo][monto_min]" id="monto_min" value=<?php echo('"'.$params['avance.monto.min'].'"'); ?>>
        <input type="hidden" name="data[AvanceEfectivo][comision]" id="comision" value=<?php echo($params['avance.comision']); ?>>
    <?php echo $form->end(); ?>
        <script type="text/javascript">
            $( document ).ready(function() {
                    jQuery.extend(jQuery.validator.messages, {maxavance: "<?php echo _m('monto.max.avance', true) ?>"});
                    jQuery.extend(jQuery.validator.messages, {minavance: "<?php echo _m('monto.cero', true) ?>"});
                    montoTotalSolicitud = 0;
                    calculaComisionFlat();
                    $("#AvanceEfectivoAvanceForm").validate();

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
            // de la transacci?n
            function calculaComisionFlat()
            {
                $(".monto").blur(function(e){
                    comision_tasa = parseFloat($("#comision").val());
                    montoTotalSolicitud = 0;
                    var monto = 0;
                    $.each($(".monto"), function(obj){
                        comision = 0;
                        monto = parseFloat($(this).val().replace(/\./g,'').replace(',','.'));
                        if(!isNaN(monto))
                        {   
                            // Math.round10 es un closure que se a?ade a Math y que redondea los decimales
                            comision = Math.round10(((monto * comision_tasa) / 100),-2);
                            montoTotalSolicitud += monto;
                        }
                        $(this).closest('td').next('td').text(comision.toLocaleString("es-VE",{ minimumFractionDigits: 2 }));
                    });
                    $("#AvanceEfectivoMontoTotal").val(montoTotalSolicitud.toLocaleString("es-VE",{ minimumFractionDigits: 2 }));
                    });
            }







        </script>
</div>


