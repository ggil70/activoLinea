<?php
/**
 * Formulario de postpago numeros afiliados
***/
?>
<div>
<h2 class="user">Pago Servicios Postpago</h2>
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

	echo $form->create('PagoServicios',
		array(
			'url'=> array(
				'controller'=>'pagos_servicios',
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
        echo $form->hidden('monto');
        //Monto limite Afiliacion
	echo $form->hidden('monto_maximo', array('id'=>'monto_maximo'));
        //Monto limite acumulado
	echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$monto_limite));
        //Monto limite para usuarios juridicos con delegacion
        echo $form->hidden('monto_limite_usuario', array('id'=>'monto_limite_usuario', 'value'=>$monto_limite_usuario));
        echo $form->hidden('tipoOperacion');
        echo $form->hidden('tipoServicio');
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
	<td> ' . enmascara($recarga['PagoServicios']['cuentaDebitar']) . ' </td>
	</tr>
	';

	echo '<tr><td colspan="2">Seleccione el beneficiario del pago y complete los datos de la operación:</td></tr>';

	
	echo '<tr><td colspan="2">'
		.'<label for="data[PagoServicios][numservicio]" class="error manual">'. _m('uno.seleccion.requerida','de los servicios') .'</label>';
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
	<td>Numero de Servicio</td>
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
			name="data[PagoServicios][numservicio]"
			value="<?php echo $a['Afiliacion']['codigo_telefono'].desencriptar($a['Afiliacion']['numero_cuenta']) ?>"
			<?php if(
					isset($this->data['PagoServicios']['numservicio'])
					&& 0==strcmp($this->data['PagoServicios']['numservicio'],$a['Afiliacion']['codigo_telefono'].desencriptar($a['Afiliacion']['numero_cuenta']))
				){
				echo 'checked="checked"';
			}
			?>
                        data-datos="<?php echo $a['Afiliacion']['id'].".".desencriptar($a['Afiliacion']['alias']).".".trim($a['Afiliacion']['tipo_operacion']).".".trim($a['Afiliacion']['tipo_servicio']) ?>"                        
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

	<?php
	$mostrar_campos = false;
	if (!empty($servicios)) {
		if (isset($this->data['PagoServicios']['numservicio']) && !empty($this->data['PagoServicios']['numservicio'])) {
			$mostrar_campos = true;
		}
	}
	$estilo_seccion_datos = $mostrar_campos ? '' : 'style="display:none;"';
	?>
	<tbody id="seccion_datos_operacion" <?php echo $estilo_seccion_datos; ?>>
	<?php
	echo '<tr><td colspan="2">Indique la informaci&oacute;n que a continuaci&oacute;n se solicita:</td></tr>';

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
                                     
        echo '<tr><td class="etiqueta"><span class="requerido">*</span> Monto a Pagar Bs.:</td><td>';
	
	echo '<label for="data[PagoServicios][tipo_pago]" class="error manual">'. _m('seleccion.requerida','el monto') .'</label>';
	
	if ($form->isFieldError('monto')){
		echo $form->error('monto', array(
				'numerico'=>_m('formato.invalido',array('el monto')),
				'requerido'=>_m('campo.requerido',array('el monto')),
				'montoMaximo'=> _m('monto.maximo', 'del servicio'),
				'montoLimiteDiario'=>_m('monto.excedido', 'del pago')
			)
		);
	}
	
	if ($form->isFieldError('tipo_pago')){
		echo $form->error('tipo_pago',
			array(
				'requerido'=>_m('seleccion.requerida','el monto'),
				'valoresPermitidos'=>_m('seleccion.invalida','el monto')
			)
		);
	}
	
    ?>

        <div id="wait" style="display:none;" height="60px"><?php echo 'Cargando... ' . $html->image('/img/ajax-loader_mini.gif'); ?></div>
        <div id="montos_pagos">
	<table cellpadding="0" cellspacing="0" border="0">
	<tr>

	<td class="etiqueta">
	<input type="hidden" name="data[PagoServicios][tipo_pago]" value="">
	<input type="hidden" name="data[PagoServicios][referenciab]" value="" id='referenciab'>
	<input type="hidden" name="data[PagoServicios][factura]" value="" id='facturaMovilnet'>

	
	
	<input
		type="radio"
		name="data[PagoServicios][tipo_pago]"
		value="v"
		class="tipo_pago_v"
		<?php echo isset($this->data['PagoServicios']['tipo_pago']) && $this->data['PagoServicios']['tipo_pago']== 'v' ? 'checked="checked"' : '' ?>
	>

	Deuda Vencida
	</td>
	<!--<td class="monto"><span id="tipo_pago_v"></span></td>-->
	 <td class="monto"> <!-- <span id="tipo_pago_t"></span> --> <input type="text" id="tipo_pago_v" name="data[PagoServicios][tipo_pago_v]" value="" readonly="readonly" style="background-color:#FFF; cursor:text; border:none; border-radius:none; box-shadow:none;" /></td>
	</tr>

 
    <!--<tr id='pago_a'>-->
	<tr>
	<td class="etiqueta">
	
	<div id='activar_deuda_actual'> <!-- desactivar el radio cuando sea postpago digitel -->

	<input
		type="radio"
		name="data[PagoServicios][tipo_pago]"
		value="a"
		class="tipo_pago_a"
		<?php echo isset($this->data['PagoServicios']['tipo_pago']) && $this->data['PagoServicios']['tipo_pago']=='a' ? 'checked="checked"' : '' ?>
	>
	Deuda Actual
    </div> <!-- Fin desactivar el radio cuando sea postpago digitel -->
    
    <p id="titulo_deuda_actual" style="display:none">Deuda Actual</p>



	</td>
	<!--<td class="monto"><span id="tipo_pago_a"></span></td>-->
	 <td class="monto"> <!-- <span id="tipo_pago_t"></span> --> <input type="text" id="tipo_pago_a" name="data[PagoServicios][tipo_pago_a]" value="" readonly="readonly" style="background-color:#FFF; cursor:text; border:none; border-radius:none; box-shadow:none;" /></td>
	</tr>

	<tr id='pago_t'>
	<td class="etiqueta">
	<input
		type="radio"
		name="data[PagoServicios][tipo_pago]"
		value="t"
		class="tipo_pago_t"
		<?php echo isset($this->data['PagoServicios']['tipo_pago']) && $this->data['PagoServicios']['tipo_pago']=='t' ? 'checked="checked"' : '' ?>
	>
	Deuda Total
	</td>
	<!--<td class="monto"><span id="tipo_pago_t"></span></td>-->
	 <td class="monto"> <!-- <span id="tipo_pago_t"></span> --> <input type="text" id="tipo_pago_t" name="data[PagoServicios][tipo_pago_t]" value="" readonly="readonly" style="background-color:#FFF; cursor:text; border:none; border-radius:none; box-shadow:none;" /></td>
	</tr>

       

	<tr id="marco_otro_monto">
	<td class="etiqueta">
	<!--<div id="marco_otro_monto">--> 	
	<input
		type="radio"
		name="data[PagoServicios][tipo_pago]"
		value="o"
		class="tipo_pago_o"
		<?php echo isset($this->data['PagoServicios']['tipo_pago']) && $this->data['PagoServicios']['tipo_pago']=='o' ? 'checked="checked"' : '' ?>
	>
	Otro Monto
    
	</td>
	<td>
	<?php echo $form->text('otro_monto',array('style'=>'width:83px','class'=>'monto pago')) ?>
	</td>
	</tr>
	<?php	if ($form->isFieldError('otro_monto')){
				echo '<tr><td colspan = "2">'.$form->error(
					'otro_monto',
					array(
						'requerido'=>_m('campo.requerido','el monto'),
						'numerico'=>_m('formato.invalido','el monto'),
						'montoMaximo'=> _m('monto.maximo', 'de la cuenta'),
						'montoLimiteDiario'=>_m('monto.excedido', 'del pago'),
						'deudatotal'=>_m('maximo.servicio'),
						'mayorCero'=>_m('mayor.cero'),
			)
				).'</div></td></tr>';
	}
    
    //}  //ocultar otro monto digitel
 	?>
    
	</table>
        </div>
        <div id="no_deuda" class="error-message" style="display:none;">
                <?php echo _m('sin.deuda.servicio'); ?>
        </div>
        <div id="no_comunicacion" class="error-message" style="display:none;">
                <?php echo _m('servicio.nocomunicacion'); ?>
        </div>
        <div id="noP" class="error-message" style="display:none;">
                <?php echo _m('digitel.lineanoPagable'); ?>
        </div>
         <div id="noSA" class="error-message" style="display:none;">
                <?php echo _m('inter.noactivo'); ?>
        </div>
        <div id="error_fatal" class="error-message" style="display:none;">
                <?php echo _m('error.fatal'); ?>
        </div>
        <div id="error_servicio" class="error-message" style="display:none;">                
        </div>
        </td></tr>
        <?php	
	
	echo $form->input(
		'descripcion',
		array(
			'label'=>'<span class="requerido">*</span> Descripci&oacute;n:',
			'type'=>'text',
			'maxlength' => '30',
			'class' => 'alfanumericoEspacio',
			'id' => 'descripcion',
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
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	?>
	</tbody>
	<tbody>
	<?php
	        
	echo '<tr><td>&nbsp;</td><td class="botonera">';

	echo $html->link(
		'Volver',
		'pago_servicios',
		array(
			'class'=>'button'
		)
	);
	
	echo '&nbsp;&nbsp;';
	?>
	<span id="contenedor_btn_aceptar" <?php echo $estilo_seccion_datos; ?>>
	<?php
	echo $html->link(
		'Aceptar',
		'javascript:',
		array(
			'class'=>'button envio_formulario',
                        'id'=>'recargar'
		)
	);	
	?>
	</span>
	<?php
	echo '</td></tr></tbody></table>';

	echo $form->hidden('offset');
	echo $form->end();

	?>
</div>
<?php	
echo $form->create(
	'PagoServicios',
	array(
		'url'=> array(
			'controller'=>'pagos_servicios',
			'action'=>$this->action
		),
		'id' => 'paginacion'
	)
);
if(!empty($this->data['PagoServicios'])){
	foreach($this->data['PagoServicios'] as $k => $v) {
		echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script type="text/javascript">
    
function traer_saldos(){

	var valor = $('#PagoServiciosId').val();
	var operacion = $('#PagoServiciosTipoOperacion').val();
	var tipoServicio = $('#PagoServiciosTipoServicio').val();
        
	$('#PagoServiciosNumservicio').focus(function(){
		$('label.error').remove();
		$('div.error-message').remove();
		$('#PagoServiciosOtroMonto').val('');
	});
	
	if(valor == ''){	
		$('#pago_minimo,#saldo_total,#saldo_actual').text('');
		$('#wait').hide();
	}
	else {

		if(operacion=='si'){
			tipoServicio='sfi';
		}

		$.ajax({
			type: "GET",
			url: '<?php echo Router::url("/pagos_servicios/obtenerInfo/"); ?>' + valor +'/'+ operacion +'/'+ tipoServicio,
			dataType: "json", 
			cache:false,
			beforeSend: function() {
				ajaxPendiente = true;
				$('#wait').show();
				$('#no_deuda').hide();
				$('#no_comunicacion').hide();
				$('#noP').hide();
				$('#noSA').hide();
				$('#recargar').show();
			},
			success: function(source) {
				ajaxPendiente = false;

				$('#wait').hide();
				var flag = false;
				var idMensaje = false;	
				var sw_pago_v = 0;	

				if (operacion =="sm" && tipoServicio == "stm"){
					$("#tipo_pago_v").val(source['deuda_vencida'])
					if(source['deuda_vencida'] == "0,00"){
						$('input.tipo_pago_v').prop('disabled',true);
					}
					$("#tipo_pago_a").val(source['deuda_vigente'])
					if(source['deuda_vigente'] == "0,00"){
						$('input.tipo_pago_a').prop('disabled',true);
					}
					$("#tipo_pago_t").val(source['deuda_total']);

					$('#monto_maximo').val(source['monto_maximo']);
					$('#monto_limite').val(source['monto_minimo']);
					$('#facturaMovilnet').val(source['factura']);
				}else{
					$.each(source, function(index, value){
						

						if(index=='referenciab'){
							$('#referenciab').val(value)
						}
						if(index=='noP'){
							flag = false;                                                
							idMensaje = '#noP';
							$('#recargar').hide();
							return false;
						}
						if(index=='noD'){
							flag = false;                                                
							idMensaje = '#no_deuda';
							$('#recargar').hide();
							
							return false;
						}
						if(index=='noSA'){
							flag = false;                                                
							idMensaje = '#noSA';
							$('#recargar').hide();
							
							return false;
						}
						if(index == 'error'){
							flag = false;                                                
							//idMensaje = '#no_comunicacion';
						}else{
							if(value=='NM'){
								$('.' + index).prop("disabled", true);
								value = '0,00';
							}
							else{
								if(operacion=='sd' && index=='tipo_pago_v' && value !='0,00'){
									sw_pago_v = 1;

								}
								$('.' + index).prop("disabled", false);
							}
							$('#' + index).val( value );
							flag = true;
							idMensaje = '#no_deuda';
						}
						
					});
					
					if(flag){
						$('#montos_pagos').show();
						$(idMensaje).hide();
						$('#aceptar').show();
					}
					else{
						$('#montos_pagos').hide();
						$(idMensaje).show();
						$('#aceptar').hide();
					}
					if(sw_pago_v == 1){
						$('#activar_deuda_actual').css("display", "none");
						$('#titulo_deuda_actual').css("display", "inline");

						$('#espacio_post_pago').css("display", "inline");
						
						//$('#pago_t').css("display", "none");
					}else{
						$('#activar_deuda_actual').css("display", "inline");
						$('#titulo_deuda_actual').css("display", "none");
						$('#espacio_post_pago').css("display", "none");

						//$('#pago_t').css("display", "inline");					
					}
				}
				acomoda_otro_monto();
			},
			error: function(source){
				$('#error_fatal').show();
				$('#wait').hide();
			}
		});
	}	
}

function acomoda_otro_monto(){
	var val = $('input[name="data[PagoServicios][tipo_pago]"]:checked').val();
	var operacion = $('#PagoServiciosTipoOperacion').val();

	if(operacion=='sd'){
		//Cuando el tipo de operacion el digitel postpago se esconde el radio buttom
		//y eil input text de otro monto
		//$('input[name="data[PagoServicios][otro_monto]"]').display='none';
        $('#marco_otro_monto').css("display", "none");
        //$('#text_otro_monto').css("display", "none");



	}else{	
        //Se activa el radio buttom y el text de otro monto, que pudo ser 
        //escondido previamente si la ultima operacion era digitel postpago
		$('#marco_otro_monto').css("display", "inline");
		//$('#text_otro_monto').css("display", "inline");

		if(val=='o'){
			$('#PagoServiciosOtroMonto').removeAttr('disabled','enabled');
			$('input[name="data[PagoServicios][monto]"]').val('');

		} else {
			$('#PagoServiciosOtroMonto').attr('disabled','disabled');
			$('input[name="data[PagoServicios][monto]"]').val($('#tipo_pago_'+val).val());
			$('input[name="data[PagoServicios][otro_monto]"]').val('');
		}	
	}
}


$(function(){
	if ($('.seleccion_afiliado:checked').length > 0) {
		$('#seccion_datos_operacion, #contenedor_btn_aceptar').show();
	} else {
		$('#seccion_datos_operacion, #contenedor_btn_aceptar').hide();
	}
	acomoda_otro_monto();

    //Funcion para completar 2 decimales
    $('#PagoServiciosOtroMonto').blur(function (e) {
    	var monto = $('#PagoServiciosOtroMonto').val();
        //Si el monto solo posee un solo decimal, hay que completar los dos decimale
        var arreglo_monto = monto.split(',');
        var entero = arreglo_monto[0];
        var decimal = arreglo_monto[1];

        if (decimal.length == 1){
           monto = monto + '0';
           $('#PagoServiciosOtroMonto').val(monto);
        }
    }); 

	function montoToNumber(valor) {
		if (!valor) return NaN;

		valor = valor.toString().trim();

		// Quitar separador de miles y normalizar decimal
		valor = valor.replace(/\./g, '').replace(',', '.');

		return parseFloat(valor);
	}

	$.validator.addMethod("montoSmStm", function(value, element) {
		var operacion = $('#PagoServiciosTipoOperacion').val();
		var tipoServicio = $('#PagoServiciosTipoServicio').val();

		if (operacion !== 'sm' || tipoServicio !== 'stm') {
			return true;
		}

		if (this.optional(element)) {
			return false;
		}

		// Formato: 0,01 / 10 / 1.234,56
		if (!/^\d{1,3}(\.\d{3})*(,\d{1,2})?$|^\d+(,\d{1,2})?$/.test(value)) {
			return false;
		}

		var monto = montoToNumber(value);
		var minimo = montoToNumber($('#monto_limite').val());
		var maximo = montoToNumber($('#monto_maximo').val());

		if (isNaN(monto)) return false;
		if (!isNaN(minimo) && monto < minimo) return false;
		if (!isNaN(maximo) && monto > maximo) return false;

		return true;
	}, "Monto inválido");





	$('input[name="data[PagoServicios][tipo_pago]"]').change(acomoda_otro_monto);	
        $('.seleccion_afiliado').click(function(){
            if($(this).prop('checked')){   
                $('#seccion_datos_operacion, #contenedor_btn_aceptar').show();
                var datos = $(this).data('datos').split(".");
                $('#PagoServiciosId').val(datos[0]);                
                $('#txtAlias').val(datos[1]);
                $('#PagoServiciosTipoOperacion').val(datos[2]);
                $('#PagoServiciosTipoServicio').val(datos[3]);                
                if(!datos[1].length){
                    $('#txtAlias').removeAttr('readonly');
                }else{
                    $('#txtAlias').attr('readonly',true);
                }
                traer_saldos();
            }
        }); 
        
	$('#PagoServiciosAfiliadosForm').validate({	
	
		rules:{
			'data[PagoServicios][alias]':{
					required:true
			},                     
			'data[PagoServicios][numservicio]':{
				required:true
			},						
			'data[PagoServicios][otro_monto]':{
				required:true, numberVE:true, montoMaximo: '#monto_maximo', montoLimite: '#monto_limite',montoSmStm: true
			},
			
			'data[PagoServicios][tipo_pago]':{
				required:true
			},
			
			'data[PagoServicios][descripcion]':{
				required:true,alphaNumericSpace:true
			}
		},	
		messages:{
                        'data[PagoServicios][alias]':{
                                required:<?php echo _m('campo.requerido',array('el alias de la afiliación'), true) ?>
                        },
			'data[PagoServicios][numservicio]':{
				required:<?php echo _m('seleccion.requerida','el número de servicio o contrato',true) ?>
			},			
			'data[PagoServicios][otro_monto]':{
				required:<?php echo _m('campo.requerido',array('el monto'), true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto'), true) ?>,
				montoMaximo:<?php echo _m('monto.maximo','del servicio' ,true) ?>,
				montoLimite: <?php echo _m('monto.limite.afiliacion', NULL ,true) ?>
			},
			'data[PagoServicios][tipo_pago]':{
				required:<?php echo _m('campo.requerido','el tipo de pago',true) ?>
			},
                        'data[PagoServicios][descripcion]':{
				required:<?php echo _m('campo.requerido','la descripci&oacute;n',true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','la descripci&oacute;n',true) ?>
			}
		}	
	});                                   	
});
</script>