<?php
/**
 * Formulario de seleccion de compromisos pago seniat
**/
?>
<div>
<h2 class="user">Pagos de Impuestos Nacionales/Aduanales <?php echo $descripcion; ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style="margin-left: 10px;">

	<?php

        echo $form->create('PagosSeniat',
		array(
			'url'=> array(
				'controller'=>'pagos_seniat',
				'action'=>'compromisos/'.$tipo
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
        
        echo $form->hidden('forma');
        echo $form->hidden('impuesto');
        echo $form->hidden('planilla');
        echo $form->hidden('porcion');
        echo $form->hidden('periodo');
        echo $form->hidden('vencimiento');
        echo $form->hidden('aduana');
        echo $form->hidden('expediente');
        echo $form->hidden('monto');
        echo $form->hidden('rif');
        echo $form->hidden('beneficiario');
        echo $form->hidden('rifPagador');
        echo $form->hidden('idAfiliacion');
         
        echo $html->tag(
		'table',
		null,
		array(
			'class'=>'formulario',
			'cellpadding'=>'0',
			'cellspacing'=>'0',
			'border'=>'0',
			'align'=>'center',
			'style'=>'table-layout:fixed',
                        'width'=>'100%'
		)
	);
	
	// La primera fila define la tabla con layout fixed
	if($error == '0'){
            echo '<tr><td style="width:150px"></td><td style="width:630px"></td></tr>';

            echo '<tr><td colspan="2">A continuaci&oacute;n proceda a seleccionar los datos del pago.</td></tr>';
            echo '<tr><td colspan="2"></td></tr>';


            $input_debitar = array(
                    'label'=>'<span class="requerido">* </span>Cuenta de Débito:',
                    'type'=>'select',
                    'options'=>$cuentas
            );

            if(count($cuentas) > 1) $input_debitar['empty'] = 'Seleccione';

            echo $form->input(
                    'cuentaDebitar',
                    $input_debitar
            );

            if ($form->isFieldError('cuentaDebitar')){
                echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaDebitar', _m('una.seleccion.requerida','de las cuentas') ).'</td></tr>';
            }

            echo '<tr id="nombre_b"><td class="etiqueta">&nbsp;&nbsp;&nbsp;&nbsp;Nombre Beneficiario:</td><td class="campo">'. desencriptar($afiliacion['Afiliacion']['nombre_beneficiario']) .'</td></tr>';
            echo '<tr id="rif_b"><td class="etiqueta">&nbsp;&nbsp;&nbsp;&nbsp;RIF del Beneficiario:</td><td class="campo">'. desencriptar($afiliacion['Afiliacion']['numero_cuenta']) .'</td></tr>';
            echo '<tr><td colspan="2"><span style="font-weight: bold; color: #00487F;">&nbsp;&nbsp;&nbsp;&nbsp;Compromiso de Pago:</span><br>';
            echo '<label for="data[PagosSeniat][id]" class="error manual">'._m('uno.seleccion.requerida','de los registros').'</label>';
            echo '<table class="tabla" style="margin-left: 10px;" cellpadding="0" cellspacing="0" border="0" width="100%">'.
                    '<thead>'.
                       '<tr class="etiqueta" style="font-size:11px">'.
                          '<td class="texto_centro ancholibre"><span class="requerido">*</span></td>'.
                          '<td>Forma</td>'.
                          '<td>Nombre Impuesto</td>'.
                          '<td>N° Planilla</td>'.
                          '<td>Porci&oacute;n</td>'.
                          '<td>Per&iacute;odo</td>'.
                          '<td>Vencimiento</td>'.
                          '<td>Aduana-Expediente</td>'.
                          '<td>Monto</td>'.
                        '</tr>'.
                     '</thead>'.
                     '<tbody>';
                     if($total_registros>0){
                        $j=0;

                       foreach($compromisos as $l) {
                            $j++;
                            echo '<tr class="campo" style="font-size:11px" id="'. intval($l['IN9701IDCO']) .'">'.
                                    '<td>'.
                                       '<input type="radio" name="data[PagosSeniat][id]" value="'. intval($l['IN9701IDCO']) .'"';
                                        if(isset($this->data['PagosSeniat']['id']) && 0==strcmp($this->data['PagosSeniat']['id'],intval($l['IN9701IDCO']))){
                                             echo 'checked="checked"';
                                        }
                                        echo '/>'.
                                    '</td>'.
                                    '<td style="text-align: center;">'. $l['IN9701CFOR'] .'</td>'.
                                    '<td style="text-align: left;">'. trim($l['IN9701CIMN']) . ' - ' . ltrim(ucwords(strtolower($l['IN9701CONC']))) .'</td>'.
                                    '<td style="text-align: center;">'. $l['IN9701NPLA'] .'</td>'.
                                    '<td style="text-align: center;">'. intval($l['IN9701NPOR']) .'</td>'.
                                    '<td style="text-align: center;">'. formatea_fecha(substr($l['IN9701PLIQ'],6),substr($l['IN9701PLIQ'],4,2),substr($l['IN9701PLIQ'],0,4)) .'</td>'.
                                    '<td style="text-align: center;">'. formatea_fecha(substr($l['IN9701FVCT'],6),substr($l['IN9701FVCT'],4,2),substr($l['IN9701FVCT'],0,4)) .'</td>'.
                                    $aduExpe = '';
                                    if($l['IN9701CIMN']=='500'){
                                        if($l['IN9701CADU']<>'000000') $aduExpe = $aduExpe.trim($l['IN9701CADU']).' - ';
                                        if($l['IN9701NADU']<>'') $aduExpe = $aduExpe.trim(ucwords(strtolower($l['IN9701NADU']))).' - ';
                                        if($l['IN9701NEXP']<>'00000000000000000000') $aduExpe = $aduExpe.trim($l['IN9701NEXP']);
                                        if (substr($aduExpe, strlen($aduExpe)-3) == ' - ') $aduExpe = substr($aduExpe, 0, strlen($aduExpe)-3);
                                    }
                                    echo '<td style="text-align: left;">'.$aduExpe.'</td>'.
                                            
                                    '<td style="text-align: right;">'. formatea_monto_fromWs($l['IN9701MTOT']) .'</td>'.
                                  '</tr>';
                            }
                             echo '<tr>'.
                                     '<td colspan="10" class="paginador">'.
                                         $this->element('paginador',array('total'=>$total_registros,'procesando'=>true)) . 
                                     '</td>'.
                                   '</tr>'.
                                   '<tr>'.
                                     '<td colspan="10" style="border: 0px;" >'.
                                        '<span class="requerido">* </span><span class="requerido2">Campo obligatorio</span>'.
                                     '</td></tr>';
                             if ($tipo != 'p'){
                                echo '<tr>'.
                                         '<td colspan="10" class="botonera">';
                                 
                                    echo  $html->link(
                                            'Volver',
                                            '/pagos_seniat/terceros_listado',
                                            array(
                                                    'class'=>'button bloqueo_repetir'
                                            )
                                    );

                                    echo '&nbsp;&nbsp;';

                                    echo $html->link(
                                            'Aceptar',
                                            'javascript:',
                                            array(
                                                    'class'=>'button',
                                                    'id'=>'aceptar'
                                            )
                                          );
                                echo     '</td>'.
                                     '</tr>'; 
                             }else{
                                echo  '<tr>'.
                                         '<td colspan="10" class="botonera">';
                                             echo $html->link(
                                                    'Aceptar',
                                                    'javascript:',
                                                    array(
                                                            'class'=>'button',
                                                            'id'=>'aceptar'
                                                    )
                                                  );
                                echo  '</td>'.
                                       '</tr>'; 
                             } 
                             
                     }else{
                         echo '<tr><td colspan="10" class="texto_centro">No se encontraron compromisos del SENIAT pendientes de pago</td></tr>';
                         if ($tipo != 'p'){
                                echo '<tr>'.
                                         '<td colspan="10" class="botonera">';
                                 
                                echo  $html->link(
                                            'Volver',
                                            '/pagos_seniat/terceros_listado',
                                            array(
                                                    'class'=>'button bloqueo_repetir'
                                            )
                                    );

                                echo     '</td>'.
                                     '</tr>'; 
                             }
                     }
                echo '</tbody>'.
                '</table></td></tr>'; ; 
        }else if($error == 'J4'){//RIF PAGADOR VENCIDO
           echo '<tr><td colspan="8" class="texto_centro">'. _m('rif.vencido') .'</td></tr>';              
        }else if($error == 'I8'){//RIF PAGADOR NO CORRESPONDE
           echo '<tr><td colspan="8" class="texto_centro">'. _m('rif.no.autorizado') .'</td></tr>';              
        }else if($error == 'ST1'){//TIME OUT CON 400
           echo '<tr><td colspan="8" class="texto_centro">'. _m('transaccion.timeout') .'</td></tr>';              
        }else if($error=='A3'  || $error=='A4' || $error=='K3' || $error=='G4' || $error=='77' || $error=='91' || $error=='93' || $error=='98'){//SIN COMUNICACIÓN SENIAT
           echo '<tr><td colspan="8" class="texto_centro">'. _m('sin.comunicacion.seniat') . ' ' . $codigoError .'</td></tr>';              
        }else if ($error == 'RA'){
          echo '<tr><td colspan="8" class="texto_centro">No se encontraron afiliaciones para Pago de Impuestos Nacionales/Aduanales '. $descripcion .'</td></tr>';  
        }else if($error == 'U'){
          echo '<tr><td colspan="8" class="texto_centro">Estimado Cliente, usuario no autorizado para realizar la operación.</td></tr>';              
        }else if($error == '8028'){
          echo '<tr><td colspan="8" class="texto_centro">No se encontraron compromisos del SENIAT pendientes de pago</td></tr>';
        }else {
          echo '<tr><td colspan="8" class="texto_centro">'. _m('consulta.fallida') . ' ' . $codigoError .'</td></tr>';
        } 
    echo '</table>';
    
    echo $form->hidden('offset');
    echo $form->hidden('paginando');
    
    echo $form->end();
?>
</div>

<div class="basic-modal-content" id="deshabilitada_limite">
	<h3>Pago Deshabilitado</h3>
	<p><?php echo(_m('monto.limite.usuario','del compromiso de pago')); ?></p>
</div>

<div class="basic-modal-content" id="deshabilitada_pendiente">
	<h3>Pago Deshabilitado</h3>
	<p><?php echo(_m('seniat.pago.pendiente')); ?></p>
</div>

<script type="text/javascript">
	
$(function(){
    
        $('input[name="data[PagosSeniat][paginando]"]').val('p');
        
        $("input[type=radio]").click(function () { 
            $("label[for='data[PagosSeniat][id]']").css("display", "none");
            id_compromiso = $("input[name='data[PagosSeniat][id]']:checked").val();
            pendientes = '<?php echo $idPendientes; ?>';
            
            if (pendientes.indexOf(id_compromiso) != -1){
                $("input[name='data[PagosSeniat][id]']:checked").prop('checked', false);
                $('#deshabilitada_pendiente').modal({minHeight:100,minWidth:400});
                return false;
            }else{
                monto = $('#'+id_compromiso).children('td')[8].innerHTML;
                limite = <?php echo $limite; ?>;
                monto = monto.replace(/\./g,'');
                monto = monto.replace(/\,/g,'.');
                
                if (limite != -1){
                    if(monto > limite){
                        $("input[name='data[PagosSeniat][id]']:checked").prop('checked', false);
                        $('#deshabilitada_limite').modal({minHeight:100,minWidth:400});
                        return false;

                    }
                }   
             }
        });
	        
        $('#aceptar').click(function(){
            $('input[name="data[PagosSeniat][paginando]"]').val('');
            $("label[for='data[PagosSeniat][id]']").html('<?php echo _m('uno.seleccion.requerida','de los registros'); ?>');
        
            if($('#PagosSeniatCompromisosForm').valid()){
                    id_compromiso = $("input[name='data[PagosSeniat][id]']:checked").val();
                    $('input[name="data[PagosSeniat][forma]"]').val($('#'+id_compromiso).children('td')[1].innerHTML);
                    $('input[name="data[PagosSeniat][impuesto]"]').val($('#'+id_compromiso).children('td')[2].innerHTML);
                    $('input[name="data[PagosSeniat][planilla]"]').val($('#'+id_compromiso).children('td')[3].innerHTML);
                    $('input[name="data[PagosSeniat][porcion]"]').val($('#'+id_compromiso).children('td')[4].innerHTML);
                    $('input[name="data[PagosSeniat][periodo]"]').val($('#'+id_compromiso).children('td')[5].innerHTML);
                    $('input[name="data[PagosSeniat][vencimiento]"]').val($('#'+id_compromiso).children('td')[6].innerHTML);
                    
                    $('input[name="data[PagosSeniat][monto]"]').val($('#'+id_compromiso).children('td')[8].innerHTML);
                    $('input[name="data[PagosSeniat][rif]"]').val($('#rif_b').children('td')[1].innerHTML);
                    $('input[name="data[PagosSeniat][beneficiario]"]').val($('#nombre_b').children('td')[1].innerHTML);
                    $('input[name="data[PagosSeniat][rifPagador]"]').val('<?php echo $rifPagador ?>');
                    
                    aduana = $('#'+id_compromiso).children('td')[7].innerHTML;
                    expediente = "";
                    if (aduana != ""){
                        datosAduana = aduana.split(' - ');
                        if (datosAduana.length>1){
                            expediente = datosAduana[2];
                            aduana = datosAduana[0]+' - '+datosAduana[1];
                        }
                    }
    
                    $('input[name="data[PagosSeniat][aduana]"]').val(aduana);
                    $('input[name="data[PagosSeniat][expediente]"]').val(expediente);
                    
                    $('#PagosSeniatCompromisosForm').submit();
            }
	});
	
	$('#PagosSeniatCompromisosForm').validate({
                onsubmit:false,
		onkeyup:false,
		onclick:false,
		onfocusout:false,
                rules:{
			'data[PagosSeniat][cuentaDebitar]':{
				required:true
			},
                        'data[PagosSeniat][id]':{
				required: true
			}
		},	
		messages:{
			'data[PagosSeniat][cuentaDebitar]':{
				required:<?php echo _m('una.seleccion.requerida','de las cuentas',true) ?>
			}
		}
	});
        
});


</script>
