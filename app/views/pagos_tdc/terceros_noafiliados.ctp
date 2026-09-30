<?php

/**
 * Formulario de pago de tarjetas de terceros no afiliadas en banco activo
 **/
?>
<div>
	<h2 class="user">Pagos Tarjeta de Crédito Tercero en Activo</h2>
	<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<?php $usuario = $this->Session->read('usuarios.datos');
if (!$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5)))) { ?>
	<div class="paso_desc">Código de Autorizacion</div>
	<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<?php

	echo $form->create(
		'Pagostdc',
		array(
			'url' => array(
				'controller' => 'pagos_tdc',
				'action' => 'terceros_noafiliados'
			),
			'inputDefaults' => array(
				'div' => false,
				'label' => 'etiqueta',
				'before' => '<tr><td class="etiqueta">',
				'between' => '</td><td class="campo">',
				'after' => '</td></tr>',
				'error' => false
			)
		)
	);

	echo $form->hidden('tipoDestinatario');
	echo $form->hidden('cuentaDebitar');
	echo $form->hidden('monto_limite', array('id' => 'monto_limite', 'value' => $monto_limite));
	echo $form->hidden('limite_total', array('id' => 'imite_total', 'value' => $limite_total));
	echo $form->hidden('monto_limite_usuario', array('id' => 'monto_limite_usuario'));

	echo $html->tag(
		'table',
		null,
		array(
			'class' => 'formulario',
			'cellpadding' => '0',
			'cellspacing' => '0',
			'border' => '0',
			'align' => 'center',
			'style' => 'table-layout:fixed'
		)
	);

	// La primera fila define la tabla con layout fixed
	echo '<tr><td style="width:180px"></td><td style="width:250px"></td></tr>';

	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos del pago.</td></tr>';
	echo '<tr><td colspan="2"></td></tr>';


	echo '
	<tr>
	<td class="etiqueta"><span class="estiloTexto"></span> Cuenta de D&eacute;bito:</td>
	<td> ' . enmascara($this->data['Pagostdc']['cuentaDebitar']) . ' </td>
	</tr>
	';

	echo '<tr><td colspan="2"></td></tr>';
	echo '<tr><td colspan="2">Indique la informaci&oacute;n que a continuaci&oacute;n se solicita:</td></tr>';

	echo $form->input(
		'numeroTDC',
		array(
			'label' => '<span class="requerido">*</span> Tarjeta del Beneficiario:',
			'type' => 'text',
			'class' => 'tarjetaCredito',
			'maxlength' => '16'
		)
	);

	if ($form->isFieldError('numeroTDC')) {
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
			'numeroTDC',
			array(
				'requerido' => _m('campo.requerido', 'la tarjeta de crédito'),
				'numeric' => _m('formato.invalido', 'la tarjeta de crédito'),
				'longitud' => _m('longitud.invalida', array('la tarjeta cr&eacute;dito', '14', '16')),
				'producto_nodisponible' => _m('producto.nodisponible')
			)
		) . '</td></tr>';
	}

	echo $form->input(
		'emailBeneficiario',
		array(
			'label' => '<span class="requerido">*</span> E-mail del Beneficiario:',
			'type' => 'text',
			'maxlength' => 50
		)
	);

	if ($form->isFieldError('emailBeneficiario')) {
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
			'emailBeneficiario',
			array(
				'requerido' => _m('campo.requerido', 'el email del beneficiario'),
				'correo' => _m('correo.invalido', 'el email del beneficiario')
			)
		) . '</td></tr>';
	}

	// CAMPOS NUEVOS
echo $form->input(
		'nombreBeneficiario',
		array(
			'label'=>'<span class="requerido">* </span>Nombre del Beneficiario:',
			'type'=>'text',
			'maxlength'=>30,
			'class'=>'alfanumericoEspacio'
		)
	);

	if ($form->isFieldError('nombreBeneficiario')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nombreBeneficiario',
				array(
					'requerido'=>_m('campo.requerido','el nombre del beneficiario'),
					'alfanumerico'=>_m('alfanumerico.invalido','el nombre del beneficiario')
				)
			).'</td></tr>';
	}

	echo $form->input(
		'tipoCedulaRif',
		array(
			'after'=>false,
			'label'=>'<span class="requerido">* </span>C&eacute;dula / RIF del Beneficiario:',
			'type'=>'select',
			'class'=>'tipoRif',
			'options'=>array('V'=>'V','E'=>'E','P'=>'P','J'=>'J','G'=>'G')
		)
	);

	echo $form->input(
		'nroCedulaRif',
		array(
			'before'=>false,
			'between'=>false,
			'label'=>false,
			'type'=>'text',
			'class'=>'nroRif',
			'maxlength' =>'9'
		)
	);

	if ($form->isFieldError('tipoCedulaRif')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tipoCedulaRif',
				array(
					'requerido'=>_m('campo.requerido','el tipo de cédula/rif'),
					'valoresPermitidos'=>_m('seleccion.invalida','el tipo de cédula/rif')
				)
			).'</td></tr>';
	} else if ($form->isFieldError('nroCedulaRif')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nroCedulaRif',
				array(
					'requerido'=>_m('campo.requerido','la cédula/rif'),
					'longitud'=>_m('valor.invalido',array('la cédula/rif')),
                                        'destinatario'=>_m('identificacion.igualcliente',array('la cédula/rif del beneficiario'))
				)
			).'</td></tr>';
	}

	// END CAMPOS NUEVOS

	echo $form->input(
		'monto',
		array(
			'label' => '<span class="requerido">*</span> Monto Bs.:',
			'type' => 'text',
			'class' => 'monto pago'
		)
	);

	if ($form->isFieldError('monto')) {
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
			'monto',
			array(
				'requerido' => _m('campo.requerido', 'el monto'),
				'numerico' => _m('formato.invalido', 'el monto'),
				'montoMaximo'	=> _m('monto.maximo', 'de la tarjeta'),
				'montoLimiteDiario'	=> _m('monto.excedido', 'para el pago')
			)
		) . '</td></tr>';
	}

	echo $form->input(
		'descripcion',
		array(
			'label' => '<span class="requerido">*</span> Descripci&oacute;n:',
			'type' => 'text',
			'maxlength' => '30',
			'class' => 'alfanumericoEspacio',
		)
	);

	if ($form->isFieldError('descripcion')) {
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
			'descripcion',
			array(
				'requerido' => _m('campo.requerido', 'la descripción'),
				'alfanumerico' => _m('alfanumerico.invalido', 'la descripción')
			)
		) . '</td></tr>';
	}

	echo '<tr><td class="etiqueta"><span class="estiloTexto"></span> Desea afiliar al Beneficiario?:';

	echo $form->input(
		'afiliar',
		array(
			'before' => false,
			'label' => false,
			'type' => 'radio',
			'legend' => false,
			'separator' => '&nbsp;&nbsp;',
			'class' => 'seleccion',
			'options' => array(
				's' => 'Sí',
				'n' => 'No'
			)
		)
	);

	echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'
		. '<label for="data[Pagostdc][afiliar]" class="error manual">' . _m('una.seleccion.requerida', 'de las opciones de afiliación') . '</label>';
	if ($form->isFieldError('afiliar')) {
		echo $form->error(
			'afiliar',
			array(
				'requerido' => _m('una.seleccion.requerida', 'de las opciones'),
				'valoresPermitidos' => _m('formato.invalido', 'la selección')
			)
		);
	}
	echo '</td></tr>';




	echo $form->input(
		'alias',
		array(
			'label' => '<span class="requerido">* </span>Alias:',
			'type' => 'text',
			'class' => 'opciones alfanumericoEspacio',
			'maxlength' => '30',
		)
	);

	if ($form->isFieldError('alias')) {
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
			'alias',
			array(
				'alfanumerico' => _m('alfanumerico.invalido', 'el alias'),
				'alias_nodisponible' => _m('alias.nodisponible')
			)
		) . '</td></tr>';
	}

	$usuario = $session->read('usuarios.datos');

	if (empty($usuario['rif'])) {
		echo $form->input(
			'monto_maximo',
			array(
				'label' => '<span class="requerido">*</span> Monto m&aacute;ximo Bs.:',
				'type' => 'text',
				'class' => 'opciones monto pago',
			)
		);

		if ($form->isFieldError('monto_maximo')) {
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">' . $form->error(
				'monto_maximo',
				array(
					'numerico' => _m('formato.invalido', 'el monto'),
					'montoMaximoLimite' => _m('monto.limite')
				)
			) . '</td></tr>';
		}
	}

		//  INICIO: Input para servicio de pago CREDICARD
		echo $form->input(
			'anioTransaccion',
			array(
				'label' => '<span class="requerido">*</span> anioTransaccion',
				'type' => 'hidden',
				'maxlength' => '30',
				'class' => 'anioTransaccion',
				'value' => $soapServicioPago['anioTransaccion']
			)
		);
		echo $form->input(
			'diaTransaccion',
			array(
				'label' => '<span class="requerido">*</span> diaTransaccion',
				'type' => 'hidden',
				'maxlength' => '30',
				'class' => 'diaTransaccion',
				'value' => $soapServicioPago['diaTransaccion']
			)
		);
		echo $form->input(
			'mesTransaccion',
			array(
				'label' => '<span class="requerido">*</span> mesTransaccion',
				'type' => 'hidden',
				'maxlength' => '30',
				'class' => 'mesTransaccion',
				'value' => $soapServicioPago['mesTransaccion']
			)
		);
	
		echo $form->input(
			'numTransaccion',
			array(
				'label' => '<span class="requerido">*</span> numTransaccion',
				'type' => 'hidden',
				'maxlength' => '30',
				'class' => 'numTransaccion',
				'value' => $soapServicioPago['numTransaccion']
			)
		);
	
		echo $form->input(
			'codBCV',
			array(
				'label' => '<span class="requerido">*</span> codBCV',
				'type' => 'hidden',
				'maxlength' => '30',
				'class' => 'codBCV',
				'value' => $soapServicioPago['codBCV']
			)
		);
		// FIN: Input para servicio de pago CREDICARD

	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

	echo '<tr><td>&nbsp;</td><td class="submit">';

	echo $form->submit(
		'Aceptar',
		array(
			'class' => 'button'
		)
	);

	echo '</td></tr></table>';

	echo $form->end();

	?>
</div>

<script>
	$(function() {

		//Funcion para completar 2 decimales
		$('#PagostdcMonto').blur(function(e) {
			var monto = $('#PagostdcMonto').val();
			//Si el monto solo posee un solo decimal, hay que completar los dos decimale
			var arreglo_monto = monto.split(',');
			var entero = arreglo_monto[0];
			var decimal = arreglo_monto[1];

			if (decimal.length == 1) {
				monto = monto + '0';
				$('#PagostdcMonto').val(monto);
			}
		});


		// condicion inicial al cargar el formulario
		$(".opciones").attr("disabled", $(".seleccion:checked").val() != 's');

		// al seleccionar la opcion negativa habilita los campos
		$(".seleccion").click(function() {
			$(".opciones").attr("disabled", !($(this).val() == 's'));

			if ($(this).val() != 's') {
				$("#PagostdcMontoMaximo").val('');
				$("#PagostdcAlias").val('');
				$("label.error:last").hide();
			}
		});

		// para la validacion
		$('#PagostdcTercerosNoafiliadosForm').validate({
			rules: {
				'data[Pagostdc][numeroTDC]': {
					required: true,
					digits: true,
					minlength: 14,
					maxlength: 16
				},
				'data[Pagostdc][emailBeneficiario]': {
					required: true,
					email: true
				},
				'data[Pagostdc][monto]': {
					required: true,
					numberVE: true,
					montoMaximo: '#PagostdcMontoMaximo',
					montoMaximo3: '#monto_limite_usuario',
					montoMaximo2: '#monto_limite'
				},
				'data[Pagostdc][descripcion]': {
					required: true,
					alphaNumericSpace: true
				},
				'data[Pagostdc][afiliar]': {
					required: true
				},
				'data[Pagostdc][alias]': {
					required: {
						depends: function(e) {
							return $(".seleccion:checked").val() == 's';
						}
					},
					alphaNumericSpace: true
				},
				'data[Pagostdc][monto_maximo]': {
					required: {
						depends: function(e) {
							return $(".seleccion:checked").val() == 's';
						}
					},
					numberVE: {
						depends: function() {
							return $(".seleccion:checked").val() == 's';
						}
					},
					montoMaximo: '#limite_total'
				}
			},
			messages: {
				'data[Pagostdc][numeroTDC]': {
					required: <?php echo _m('campo.requerido', 'la tarjeta cr&eacute;dito', true) ?>,
					digits: <?php echo _m('formato.invalido', 'la tarjeta cr&eacute;dito', true) ?>,
					minlength: <?php echo _m('longitud.invalida', array('la tarjeta cr&eacute;dito', '14', '16'), true) ?>,
					maxlength: <?php echo _m('longitud.invalida', array('la tarjeta cr&eacute;dito', '14', '16'), true) ?>
				},
				'data[Pagostdc][emailBeneficiario]': {
					required: <?php echo _m('campo.requerido', 'el email del beneficiario', true) ?>,
					email: <?php echo _m('correo.invalido', 'el email del beneficiario', true) ?>
				},
				'data[Pagostdc][monto]': {
					required: <?php echo _m('campo.requerido', 'el monto', true) ?>,
					numberVE: <?php echo _m('formato.invalido', 'el monto', true) ?>,
					montoMaximo: <?php echo _m('monto.maximo', 'de la tarjeta', true) ?>,
					montoMaximo2: <?php echo _m('monto.excedido', 'para el pago', true) ?>,
					montoMaximo3: <?php echo _m('monto.limite.usuario', 'para el pago', true) ?>
				},
				'data[Pagostdc][descripcion]': {
					required: <?php echo _m('campo.requerido', 'la descripci&oacute;n', true) ?>,
					alphaNumericSpace: <?php echo _m('alfanumerico.invalido', 'la descripci&oacute;n', true) ?>
				},
				'data[Pagostdc][alias]': {
					required: <?php echo _m('campo.requerido', 'el alias', true) ?>,
					alphaNumericSpace: <?php echo _m('alfanumerico.invalido', 'el alias', true) ?>
				},
				'data[Pagostdc][monto_maximo]': {
					required: <?php echo _m('campo.requerido', 'el monto m&aacute;ximo', true) ?>,
					numberVE: <?php echo _m('formato.invalido', 'el monto m&aacute;ximo', true) ?>,
					montoMaximo: <?php echo _m('monto.limite', NULL, true) ?>
				}
			}
		});

	});
</script>