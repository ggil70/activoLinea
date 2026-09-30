<?php
/**
 * Formulario de transferencias a cuentas no afiliadas de terceros en banco activo
**/
?>
<div>
<h2 class="user">Transferencia Tercero en Activo</h2>
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

	echo $form->create('Transferencia',
		array(
			'url'=> array(
				'controller'=>'transferencias',
				'action'=>'terceros_noafiliados'
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
	echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$monto_limite));
	echo $form->hidden('limite_total', array('id'=>'limite_total', 'value'=>$limite_total));
	echo $form->hidden('monto_limite_usuario', array('id'=>'monto_limite_usuario'));

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
	echo '<tr><td style="width:180px"></td><td style="width:250px"></td></tr>';
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la transferencia.</td></tr>';

	echo '
	<tr>
	<td class="etiqueta"><span class="estiloTexto"></span>Cuenta de Débito</td>
	<td> ' . enmascara($this->data['Transferencia']['cuentaDebitar']) . ' </td>
	</tr>
	';
	echo '<tr><td colspan="2"><span class="estiloTexto"></span>Indique la informaci&oacute;n que a continuaci&oacute;n se solicita:</td></tr>';

	echo $form->input(
		'cuentaCreditar',
		array(
			'label'=>'<span class="requerido">* </span>Número Cuenta:',
			'type'=>'text',
			'maxlength' => '20',
			'class'=>'numerico'
		)
	);

	if ($form->isFieldError('cuentaCreditar')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaCreditar',
				array(
					'banco'=>_m('valor.invalido',array('el número de cuenta')),
					'nroCuenta'=>_m('valor.invalido',array('el número de cuenta')),
					'producto_nodisponible'=>_m('producto.nodisponible'),
					'comparacion'=>_m('comparacion.cuentas',array('la cuenta destino'))
				)).'</td></tr>';
	}

	echo $form->input(
		'emailBeneficiario',
		array(
			'label'=>'<span class="requerido">* </span>E-mail del Beneficiario:',
			'type'=>'text',
			'maxlength'=>50
		)
	);

	if ($form->isFieldError('emailBeneficiario')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('emailBeneficiario',
				array(
					'correo'=>_m('correo.invalido',array('el email del beneficiario')),
					'requerido'=>_m('campo.requerido',array('el email del beneficiario'))
				)).'</td></tr>';
	}

	echo $form->input(
		'monto',
		array(
			'label'=>'<span class="requerido">* </span>Monto Bs.:',
			'type'=>'text',
			'class'=>'monto pago'
		)
	);

	if ($form->isFieldError('monto')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('monto',
				array(
					'numerico'		=> _m('formato.invalido',array('el monto')),
					'requerido'		=> _m('campo.requerido',array('el monto')),
					'montoMaximo'	=> _m('monto.maximo', 'de la cuenta'),
					'montoLimiteDiario'	=> _m('monto.excedido', 'de la transferencia'),
					'montoLimiteUsuario' => _m('monto.limite.usuario', 'de la transferencia'),
				)).'</td></tr>';
	}

	echo $form->input(
		'descripcion',
		array(
			'label'=>'<span class="requerido">* </span>Descripci&oacute;n:',
			'type'=>'text',
			'maxlength' => '30',
			'class'=>'alfanumericoEspacio'
		)
	);

	if ($form->isFieldError('descripcion')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('descripcion',
				array(
					'requerido'=>_m('campo.requerido',array('la descripción')),
					'alfanumerico'=>_m('alfanumerico.invalido',array('la descripción'))
				)).'</td></tr>';
	}
	
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
			'options'=> array(
				's' => 'Sí',
				'n' => 'No'
			)
		)
	);
	
	echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'
		.'<label for="data[Transferencia][afiliar]" class="error manual">'._m('una.seleccion.requerida','de las opciones de afiliación').'</label>';
		if ($form->isFieldError('afiliar')){
			echo $form->error('afiliar',
				array(
					'valoresPermitidos'=>_m('una.seleccion.requerida','de las opciones de afiliación'),
					'requerido'=>_m('seleccion.requerida',array('la afiliación'))
				)
			);
		}
	echo '</td></tr>';

	echo $form->input(
		'alias',
		array(
			'label'=>'<span class="requerido">* </span>Alias:',
			'type'=>'text',
			'class'=>'opciones alfanumericoEspacio',
			'maxlength' => '30',
		)
	);

	if ($form->isFieldError('alias')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('alias',
				array(
					'alfanumerico'=>_m('alfanumerico.invalido',array('el alias')),
					'alias_nodisponible'=>_m('alias.nodisponible')
				)).'</td></tr>';
	}
	
	$usuario = $session->read('usuarios.datos');
	
	if(empty($usuario['rif'])){	
		echo $form->input(
			'monto_maximo',
			array(
				'label'=>'<span class="requerido">* </span>Monto m&aacute;ximo Bs.:',
				'type'=>'text',
				'class'=>'opciones monto pago',
			)
		);

		if ($form->isFieldError('monto_maximo')){
				echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('monto_maximo',
					array(
						'numerico'=>_m('formato.invalido',array('el monto máximo')),
						'requerido'=>_m('campo.requerido',array('el monto máximo')),
						'montoMaximoLimite'=>_m('monto.limite')
					)).'</td></tr>';
		}
	}
        
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

    $('#TransferenciaMonto').blur(function (e) {
    	var monto = $('#TransferenciaMonto').val();
        //Si el monto solo posee un solo decimal, hay que completar los dos decimale
        var arreglo_monto = monto.split(',');
        var entero = arreglo_monto[0];
        var decimal = arreglo_monto[1];

        if (decimal.length == 1){
           monto = monto + '0';
           $('#TransferenciaMonto').val(monto);
        }
        
    });  


	// condicion inicial al cargar el formulario
	$(".opciones, #TransferenciaMontoMaximo").attr("disabled", $(".seleccion:checked").val()!='s');
	
	// al seleccionar la opcion negativa habilita los campos
	$(".seleccion").click(function(){
		$(".opciones, #TransferenciaMontoMaximo").attr("disabled", !($(this).val()=='s'));
		
		if( $(this).val()!='s' ) {
			$("#TransferenciaMontoMaximo").val('');
			$("#TransferenciaAlias").val('');
			$("label.error:last").hide();
		}
	});
	
	// al seleccionar la opcion negativa habilita los campos
	$(".seleccion").click(function(){
		$(".opciones, #TransferenciaMontoMaximo").attr("disabled", !($(this).val()=='s'));
		
		if( $(this).val()!='s' ) {
			$("#TransferenciaMontoMaximo").val('');
			$("#TransferenciaAlias").val('');
			$("label.error:last").hide();
		}
	});
	
	// para la validacion
	$('#TransferenciaTercerosNoafiliadosForm').validate({
		rules:{
			'data[Transferencia][cuentaCreditar]':{
				required:true, digits:true,
				cuentaActivo:true
			},
			'data[Transferencia][nombreBeneficiario]':{
				required:true,alphaNumericSpace:true
			},
			'data[Transferencia][nroCedulaRif]':{
				required:true,
				digits:{
					depends: function(e){
						return $('#TransferenciaTipoCedulaRif').val()=='V';
					}				
				},
				alphanumeric:{
					depends: function(e){
						return $('#TransferenciaTipoCedulaRif').val()=='P';
					}				
				}				
			},
			'data[Transferencia][emailBeneficiario]':{
				required:true, 
                                email:true,
				minlength:3,
				maxlength:50
			},
			'data[Transferencia][monto]':{
				required:true,
				numberVE:true,
				montoMaximo:'#TransferenciaMontoMaximo',
				montoMaximo3: '#monto_limite_usuario',
				montoMaximo2:'#monto_limite'				
			},
			'data[Transferencia][descripcion]':{
				required:true,alphaNumericSpace:true
			},
			'data[Transferencia][afiliar]':{
				required:true
			},
			'data[Transferencia][alias]':{
				required: {
					depends:function(){
						return $(".seleccion:checked").val()=='s';
					}
				},
                                alphaNumericSpace:true
			},
			'data[Transferencia][monto_maximo]':{
				required: {
					depends:function(){
						return $(".seleccion:checked").val()=='s';
					}
				},
				numberVE: {
					depends: function(){
						return $(".seleccion:checked").val()=='s';
					}
				},
				montoMaximo: '#limite_total'
			}
		},
		messages:{
			'data[Transferencia][cuentaCreditar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta del beneficiario'),true) ?>,
				digits:<?php echo _m('valor.invalido',array('el número de cuenta'),true) ?>,
				cuentaActivo:<?php echo _m('valor.invalido',array('el número de cuenta'),true) ?>
			},
			'data[Transferencia][nombreBeneficiario]':{
				required:<?php echo _m('campo.requerido',array('el nombre del beneficiario'),true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido',array('el nombre del beneficiario'),true) ?>
			},
			'data[Transferencia][nroCedulaRif]':{
				required:<?php echo _m('campo.requerido',array('la cédula o rif del beneficiario'),true) ?>,
				digits:<?php echo _m('formato.invalido',array('la cédula o rif del beneficiario'),true) ?>,
				alphanumeric:<?php echo _m('alfanumerico.invalido',array('el pasaporte del beneficiario'),true) ?>				
			},
			'data[Transferencia][emailBeneficiario]':{
				required:<?php echo _m('campo.requerido',array('el email del beneficiario'),true) ?>,
				email:<?php echo _m('correo.invalido',array('el email del beneficiario'),true) ?>
			},
			'data[Transferencia][monto]': {
				required:<?php echo _m('campo.requerido',array('el monto'),true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto'),true) ?>,
				montoMaximo:<?php echo _m('monto.maximo', 'de la cuenta',true) ?>,
				montoMaximo2:<?php echo _m('monto.excedido', 'de la transferencia' ,true) ?>,
				montoMaximo3: <?php echo _m('monto.limite.usuario', 'de la transferencia' ,true) ?>
			},
			'data[Transferencia][descripcion]':{
				required:<?php echo _m('campo.requerido',array('la descripción'),true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido',array('la descripción'),true) ?>
			},
			'data[Transferencia][afiliar]':{
				required:<?php echo _m('una.seleccion.requerida','de las opciones de afiliación',true) ?>
			},
			'data[Transferencia][alias]':{
				required:<?php echo _m('campo.requerido',array('el alias'),true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido',array('el alias'),true) ?>
			},
			'data[Transferencia][monto_maximo]':{
				required:<?php echo _m('campo.requerido',array('el monto máximo'),true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto máximo'),true) ?>,
				montoMaximo: <?php echo _m('monto.limite', NULL ,true) ?>
			}
		}		
	});
	
});



</script>
