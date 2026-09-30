<?php
/**
 * Formulario de recarga prepago numeros afiliados
**/
?>
<div>
<h2 class="user">Recarga Simpletv</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<?php

	echo $form->create('RecargaSimpletv',
		array(
			'url'=> array(
				'controller'=>'recarga_simpletv',
				'action'=>'afiliados'
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

	echo $form->hidden('tipoDestinatario');
	echo $form->hidden('cuentaDebitar');        
        echo $form->hidden('recarga_minima', array('id'=>'recarga_minima'));
        echo $form->hidden('recarga_maxima', array('id'=>'recarga_maxima'));
        //Monto limite Afiliacion
	echo $form->hidden('monto_maximo', array('id'=>'monto_maximo'));
        //Monto limite acumulado
	echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$monto_limite));
	echo $form->hidden('montoafiliacion', array('id'=>'montoafiliacion', 'value'=>$montoAfiliadoVta));
        //Monto limite para usuarios juridicos con delegacion
        echo $form->hidden('monto_limite_usuario', array('id'=>'monto_limite_usuario', 'value'=>$monto_limite_usuario));
        echo $form->hidden('tipoOperacion');
        echo $form->hidden('tipoServicio');
        echo $form->hidden('contrato');
        echo $form->hidden('id');        
 
        
	
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
	echo '<tr><td style="width:180px"></td><td style="width:400px"></td></tr>';

	echo '
	<tr>
	<td class="etiqueta" colspan="2">A continuaci&oacute;n proceda a ingresar los datos del pago.</td>
	</tr>
	';

	echo '
	<tr>
	<td class="etiqueta">Cuenta de Débito:</td>
	<td> ' . enmascara($recarga['RecargaSimpletv']['cuentaDebitar']) . ' </td>
	</tr>
	';

	echo '<tr><td colspan="2">Seleccione el beneficiario de la recarga y complete los datos de la operación:</td></tr>';

	
	echo '<tr><td colspan="2">'
		.'<label for="data[RecargaSimpletv][numservicio]" class="error manual">'. _m('uno.seleccion.requerida','de los servicios') .'</label>';
		if ($form->isFieldError('numservicio')){
			echo $form->error('numservicio',
				array(
					'requerido'=>_m('uno.seleccion.requerida','de los servicios')
				)
			);
		}
	echo '</td></tr>';      
	?>

	<tr>
	<td colspan="2">	

	<table cellpadding="0" cellspacing="0" border="0" class="tabla">
	<thead>
	<tr>
	<td class="ancholibre texto_centro"><span class="requerido">* </span></td>
	<td>Alias</td>
	<td>Nombre Beneficiario</td>
	<td>N&uacute;mero de Tarjeta de Acceso</td>
	</tr>
	</thead>
	<tbody>
	<?php
	if($servicios){
		foreach ($resultados as $a){                     
	?>
	<tr>
		<td class="texto_centro ancholibre">
			<input
			type="radio"
			class="seleccion_afiliado"
			id="<?php echo 'sel'.$a['Afiliacion']['id'] ?>"
			name="data[RecargaSimpletv][numservicio]"
			value="<?php echo $a['Afiliacion']['codigo_telefono'].desencriptar($a['Afiliacion']['numero_cuenta']) ?>"
			<?php if(
					isset($this->data['RecargaSimpletv']['numservicio'])
					&& 0==strcmp($this->data['RecargaSimpletv']['numservicio'],$a['Afiliacion']['codigo_telefono'].desencriptar($a['Afiliacion']['numero_cuenta']))
				){
				echo 'checked="checked"';
			}
			?>
                        data-datos="<?php echo $a['Afiliacion']['id'].".".desencriptar($a['Afiliacion']['alias']).".".trim($a['Afiliacion']['tipo_operacion']).".".trim($a['Afiliacion']['tipo_servicio']).".".desencriptar(trim($a['Afiliacion']['numero_cuenta'])).".".$a['Afiliacion']['monto_maximo']?>"                        
			/>
			<?php 
				echo $form->hidden('seleccion', array('id'=>'monto'.$a['Afiliacion']['id'], 'value'=>empty($a['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($a['Afiliacion']['monto_maximo']) ));
			?>
		</td>
		<td><?php echo ($a['Afiliacion']['alias']!="") ? desencriptar($a['Afiliacion']['alias']) : 'Alias no asignado' ?></td>
		<td><?php echo desencriptar($a['Afiliacion']['nombre_beneficiario']) ?></td>
		<td><?php echo $a['Afiliacion']['codigo_telefono'].desencriptar($a['Afiliacion']['numero_cuenta']) ?></td>
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
	}
	else{
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

    <tr><td colspan="2"><label><span id="errorST" style="color:red;"></span></label></td></tr> 

	<?php

	echo '<tr><td colspan="2">Indique la informaci&oacute;n que a continuación se solicita:</td></tr>';

	echo $form->input(
		'alias',
		array(
			'label'=>'<span class="requerido">* </span>Alias:',
			'type'=>'text',
			'class'=>'alias alfanumericoEspacio',
			'maxlength' => '30',
                        'id'=>'txtAlias',
                        'readonly'=>'readonly'
		)
	);

	if ($form->isFieldError('alias')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('alias',
				array(
					'requerido'=>_m('alias.requerido',array('el alias'))
				)
			).'</td></tr>';
	}       

	echo $form->input(
		'saldo',
		array(
			'label'=>'<span class="requerido">* </span>Saldo Tarjeta de Acceso:',
			'type'=>'text',
            'id'=>'txtSaldo',
            'readonly'=>'readonly'
		)
	);

	echo $form->input(
		'dias',
		array(
			'label'=>'<span class="requerido">* </span>Días de Programaci&oacute;n Disponible:',
			'type'=>'text',
			'maxlength' => '30',
            'id'=>'txtDias',
            'readonly'=>'readonly'
		)
	);

	echo '<tr><td colspan="2"><div id="wait" class="texto_centro" style="display:none;">';
	echo 'Cargando... ' . $html->image('/img/ajax-loader_mini.gif') . '</div>';
    echo '</td></tr>';
    ?>




	<tr><td class="etiqueta">
			<input
				type="radio"
				id="tipopago0"
				name="data[RecargaSimpletv][tipo_pago]"
				value="t"
				<?php echo isset($this->data['RecargaSimpletv']['tipo_pago']) && $this->data['RecargaSimpletv']['tipo_pago']=='t' ? 'checked="checked"' : '' ?>
			/> Renta Mensual
	</td>
	<td class="campo"><?php echo $form->text('renta',array('style'=>'width:140px', 'readonly'=>'readonly', 'id'=>'txtRenta')) ?>
	</td>
	</tr>
	<tr><td class="etiqueta">
			<input
				type="radio"
				id="tipopago1"
				name="data[RecargaSimpletv][tipo_pago]"
				value="p"
				<?php echo isset($this->data['RecargaSimpletv']['tipo_pago']) && $this->data['RecargaSimpletv']['tipo_pago']=='p' ? 'checked="checked"' : '' ?>
 		    /> Pago Parcial
	</td>
    <?php
    if($tipoPago==false){
    ?>	
	      <td class="campo"><?php echo $form->text('monto',array('style'=>'width:140px','class'=>'monto pago', 'id'=>'monto','disabled'=>'true')) ?>
		  </td>
    <?php
    }else{
    ?>	
	      <td class="campo"><?php echo $form->text('monto',array('style'=>'width:140px','class'=>'monto pago', 'id'=>'monto')) ?>
		  </td>
    <?php
    }
    ?>
	</tr>


    <?php
	if ($form->isFieldError('monto')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('monto',
				array(					                                    
                    'requerido'=>_m('seleccion.requerida','el monto'),
					'numerico'=>_m('formato.invalido','el monto'),
					'montoMaximo'=> _m('monto.maximo', 'del numero de servicio'),
					'monto_limite'=>_m('monto.excedido', 'para la recarga'),            
                    'montoLimiteUsuario' => _m('monto.limite.usuario', 'de la recarga'),
					'recargaMinima'=>_m('recarga.minima',$montoMinimo),
                    'recargaMaxima'=>_m('recarga.maxima',$montoMaximo)
				)
			).'</td></tr>';
	}

	if(!empty($errorMtoAfiliado)){
		echo '<tr id="errorMtoAfil"><td>&nbsp;</td><td><span style="color:red;">'.$errorMtoAfiliado.'</span></td></tr>';
	}
			




	echo $form->input(
		'descripcion',
		array(
			'label'=>'<span class="requerido">*</span> Descripci&oacute;n:',
			'type'=>'text',
			'maxlength' => '30',
			'class' => 'alfanumericoEspacio',
			'id'=>'txtDescripcion',
			'readonly'=>'readonly'
		)
	);

	if ($form->isFieldError('descripcion')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('descripcion',
				array(
					'requerido'=>_m('campo.requerido','la descripción'),
					'alfanumerico'=>_m('alfanumerico.invalido','la descripción')
				)
			).'</td></tr>';
	}
	
       
	echo '<tr><td>&nbsp;</td><td class="botonera">';

	echo $html->link(
		'Volver',
		'recarga_simpletv',
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
            'id'=>'recargar'
		)
	);	
	

	echo '</td></tr></table>';

	echo $form->hidden('offset');
	echo $form->end();

	?>
</div>
<?php	
echo $form->create(
	'RecargaSimpletv',
	array(
		'url'=> array(
			'controller'=>'recarga_simpletv',
			'action'=>$this->action
		),
		'id' => 'paginacion'
	)
);
if(!empty($this->data['RecargaSimpletv'])){
	foreach($this->data['RecargaSimpletv'] as $k => $v) {
		echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script type="text/javascript">

/*function acomoda_otro_monto(){
   if($("#tipopago0").is(':checked')) {
   	  $('#monto').attr("readonly","readonly");	
   	  $('#monto').val("");
   }else{
   	  $('#monto').removeAttr("readonly");
   }	  
}*/


function acomoda_otro_monto(){



   if($("#tipopago0").is(':checked')) {
   	  $('#monto').attr('disabled','disabled');	
   	  $('#monto').val("");
   }else{
   	  $('#monto').removeAttr('disabled','enabled');
   	  //$('#monto').removeAttr("readonly");

   }	  
}


function traer_saldo(contrato){
$.ajax({
	type: "GET",
	url: '<?php echo Router::url("/recarga_simpletv/obtenerInfo/"); ?>' + contrato,
	dataType: "json",
	cache:false,
	beforeSend: function() {
		ajaxPendiente = true;
		$('#wait').show();
		//$('#no_deuda').hide();
	},
	success: function(source) {
		if(source){
			ajaxPendiente = false;
			
			$('#wait').hide();
			var flag = false;
			var swError = 0;
			var swMensaje = 0;

			var cadenaMensaje = 'Estimado cliente, ha ocurrido un error inesperado. Por favor intente de nuevo mas tarde.';

			$.each(source, function(index, value){

				if(swError == 1){
	                if(index == 'codeError'){
	                	swMensaje = 1;
	                }    
					swError = 2;
				}
                                                  
                if(swMensaje == 1 && index == 'mensajeError'){
	                    cadenaMensaje = value;
	                    $('#errorST').css('display','inline');
	                    $('#errorST').html(cadenaMensaje);
	                    $('#txtSaldo').val('');	
	                    $('#txtDias').val('');	
	                    $('#txtRenta').val('');	
                        $('#txtDescripcion').val('');
                        $('#txtDescripcion').attr("readonly","readonly");	
                }
                 

				if(index == 'error'){
					if(value == 1){
	                   swError = 1;                   
					}
				}


				if(swError == 0){
					if(index == 'saldo'){
					    $('#txtSaldo').val(value);	
					}

					if(index == 'dias'){
					    $('#txtDias').val(value);	
					}

					if(index == 'renta'){
					    $('#txtRenta').val(value);	
					}
					$('#txtDescripcion').removeAttr("readonly");
					$('#errorST').css('display','none');

				}	
			});
		}else{
            var cadenaMensaje = 'Estimado cliente, ha ocurrido un error inesperado. Por favor intente de nuevo mas tarde.';
            $('#errorST').css('display','inline');
            $('#errorST').html(cadenaMensaje);
            $('#wait').hide();
		}	
	},
	error: function(source){

		$('#errorClave').css('display','inline');
		$('#wait').hide();
	}
});

}


    //formatear monto con decimales
	$('#monto').blur(function (e) {
           
	  	    var montoF = $('#monto').val();
    	    var arreglo_monto = montoF.split(',');
        	var entero = arreglo_monto[0];
	        if(arreglo_monto[1]){
    	        var decimal = arreglo_monto[1];
        	}else{
	            decimal = '00';
    	    }  

        	if (decimal.length == 1){
            	monto = montoF + '0';
            	$('#monto').val(monto);
        	}
        	$('#errorMtoAfil').css('display','none');
	});	


$(function(){
        
        var recarga_minima;
        var recarga_maxima;
        var recarga_afiliacion;
        
        $('.seleccion_afiliado').click(function(){
            if($(this).prop('checked')){  
                var datos = $(this).data('datos').split(".");
                $('#RecargaSimpletvId').val(datos[0]);
                $('#txtAlias').val(datos[1]);
                $('#RecargaSimpletvTipoOperacion').val(datos[2]);
                $('#RecargaSimpletvTipoServicio').val(datos[3]);
                $('#RecargaSimpletvContrato').val(datos[4]);
                $('#RecargaSimpletv').val(datos[4]); 
                $('#RecargaSimpletvMontoMaximo').val(datos[5]); 
                $('#RecargaSimpletvMontoafiliacion').val(datos[5]); 
                $('#montoafiliacion').val(datos[5]);





   	  			$('#monto').attr('disabled','disabled');	
   	  			$('#monto').val("");
                $('#tipopago0').attr('checked','checked');
               

                if(!datos[1].length){
                    $('#txtAlias').removeAttr('readonly');
                }else{
                    $('#txtAlias').attr('readonly',true);
                }

                traer_saldo(datos[4]);
                
                recarga_minima = <?php echo _p('recarga_minima_simple_tv') ?>;
                $('#recarga_minima').val(recarga_minima);

                recarga_maxima = <?php echo _p('recarga_maxima_simple_tv') ?>;
                $('#recarga_maxima').val(recarga_maxima);
            }
        });   


    
	$('input[name="data[RecargaSimpletv][tipo_pago]"]').change(acomoda_otro_monto); 

	$('#RecargaSimpletvAfiliadosForm').validate({
		rules:{
            'data[RecargaSimpletv][alias]':{
                required:true
        	},                     
			'data[RecargaSimpletv][numservicio]':{
				required:true
			},

			'data[RecargaSimpletv][tipo_pago]':{
				required:true
			},
			'data[RecargaSimpletv][monto]':{
				required:true, 
                numberVE:true, 
                montoMaximo: function(e){
                    if($('#RecargaSimpletvTipoOperacion').val()!='') return '#recarga_maxima';
                },    
				montoMinimo: function(e){
                    if($('#RecargaSimpletvTipoOperacion').val()!='') return '#recarga_minima';
                },    

                //montoMaximo2: function(e){
					//if($('#RecargaSimpletvTipoOperacion').val()!='') return '#recarga_maxima';
                //    },
                    
		 		//montoMaximo3:'#monto_limite',
                //montoMaximo4: '#monto_limite_usuario',
			},
			'data[RecargaSimpletv][descripcion]':{
				required:true,alphaNumericSpace:true
			}
		},

		messages:{
            'data[RecargaSimpletv][alias]':{
                required:<?php echo _m('campo.requerido',array('el alias de la afiliación'), true) ?>
            },
			'data[RecargaSimpletv][numservicio]':{
				required:<?php echo _m('seleccion.requerida','el número de servicio o contrato',true) ?>
			},


			'data[RecargaSimpletv][tipo_pago]':{
				required:<?php echo _m('campo.requerido','el monto',true) ?>
			},

			
			'data[RecargaSimpletv][monto]':{
				required:<?php echo _m('campo.requerido','el monto',true) ?>,
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
               //montoMaximo:<?php //echo _m('monto.maximo', 'de la Recarga',true) ?>,

				montoMaximo: function(e){
                    return <?php echo _m('monto.invalido.maximo.simpletv',array(formatea_monto_fromWs(_p('recarga_maxima_simple_tv')),'204'),true) ?>
                },                                


				montoMinimo: function(e){
                    return <?php echo _m('monto.invalido.minimo.simpletv',array(formatea_monto_fromWs(_p('recarga_minima_simple_tv')),'204'),true) ?>
                },    

                
               /* montoMaximo2: function(e){
                    return <?php //echo _m('monto.invalido.maximo.simpletv',array(formatea_monto_fromWs(_p('recarga_minima_simple_tv')),formatea_monto_fromWs(_p('recarga_maxima_simple_tv')),'204'),true) ?>
                }*/
                
			},
			'data[RecargaSimpletv][descripcion]':{
				required:<?php echo _m('campo.requerido','la descripci&oacute;n',true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','la descripci&oacute;n',true) ?>
			}
		}	
	});
});
</script>