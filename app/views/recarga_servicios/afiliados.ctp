<?php
/**
 * Formulario de recarga prepago numeros afiliados
 **/

?>
<div>
	<h2 class="user">Recarga Servicios Prepago</h2>
	<div class="line_title_orange"></div>
</div>


<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<?php $usuario = $this->Session->read('usuarios.datos');
if (!$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'], array(3, 5)))) { ?>
	<div class="paso_desc">C&oacute;digo de Autorizaci&oacute;n</div>
	<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<?php

	echo $form->create(
		'RecargaServicios',
		array(
			'url' => array(
				'controller' => 'recarga_servicios',
				'action' => 'afiliados'
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
	echo $form->hidden('recarga_minima', array('id' => 'recarga_minima'));
	echo $form->hidden('recarga_maxima', array('id' => 'recarga_maxima'));
	//Monto limite Afiliacion
	echo $form->hidden('monto_maximo', array('id' => 'monto_maximo'));
	//Monto limite acumulado
	echo $form->hidden('monto_limite', array('id' => 'monto_limite', 'value' => $monto_limite));
	//Monto limite para usuarios juridicos con delegacion
	echo $form->hidden('monto_limite_usuario', array('id' => 'monto_limite_usuario', 'value' => $monto_limite_usuario));
	echo $form->hidden('tipoOperacion');
	echo $form->hidden('tipoServicio');
	echo $form->hidden('id');
	// echo $form->hidden('multiploDigitel', array('id' => 'multiploDigitel', 'value' => _p('multiplo.digitel')));
	// echo $form->hidden('multiploMovistar', array('id' => 'multiploMovistar', 'value' => _p('multiplo.movistar')));


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
	echo '<tr><td style="width:180px"></td><td style="width:400px"></td></tr>';

	echo '
	<tr>
	<td class="etiqueta" colspan="2">A continuaci&oacute;n proceda a ingresar los datos del pago.</td>
	</tr>
	';

	echo '
	<tr>
	<td class="etiqueta">Cuenta de D&eacute;bito:</td>
	<td> ' . enmascara($recarga['RecargaServicios']['cuentaDebitar']) . ' </td>
	</tr>
	';

	echo '<tr><td colspan="2">Seleccione el beneficiario de la recarga y complete los datos de la operaci&oacute;n:</td></tr>';


	echo '<tr><td colspan="2">'
		. '<label for="data[RecargaServicios][numservicio]" class="error manual">' . _m('uno.seleccion.requerida', 'de los servicios') . '</label>';
	if ($form->isFieldError('numservicio')) {
		echo $form->error(
			'numservicio',
			array(
				'requerido' => _m('uno.seleccion.requerida', 'de los servicios')
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
					if ($servicios) {
						foreach ($resultados as $a) {
							?>
							<tr>
								<td class="texto_centro ancholibre">
									<input type="radio" class="seleccion_afiliado"
										id="<?php echo 'sel' . $a['Afiliacion']['id'] ?>"
										name="data[RecargaServicios][numservicio]"
										value="<?php echo $a['Afiliacion']['codigo_telefono'] . desencriptar($a['Afiliacion']['numero_cuenta']) ?>"
										<?php if (
											isset($this->data['RecargaServicios']['numservicio'])
											&& 0 == strcmp($this->data['RecargaServicios']['numservicio'], $a['Afiliacion']['codigo_telefono'] . desencriptar($a['Afiliacion']['numero_cuenta']))
										) {
											echo 'checked="checked"';
										}
										?>
								data-datos="<?php echo $a['Afiliacion']['id'] . "." . desencriptar($a['Afiliacion']['alias']) . "." . trim($a['Afiliacion']['tipo_operacion']) . "." . trim($a['Afiliacion']['tipo_servicio']) ?>" />
									<?php
									echo $form->hidden('seleccion', array('id' => 'monto' . $a['Afiliacion']['id'], 'value' => empty($a['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($a['Afiliacion']['monto_maximo'])));
									?>
								</td>
								<td><?php echo ($a['Afiliacion']['alias'] != "") ? desencriptar($a['Afiliacion']['alias']) : 'Alias no asignado' ?>
								</td>
								<td><?php echo desencriptar($a['Afiliacion']['nombre_beneficiario']) ?></td>
								<td><?php echo $a['Afiliacion']['codigo_telefono'] . desencriptar($a['Afiliacion']['numero_cuenta']) ?>
								</td>
							</tr>
							<?php
						}
						?>
						<tr>
							<td colspan="4" class="paginador">
								<?php echo $this->element('paginador2', array('form_id' => 'paginacion', 'total' => $total_registros, 'tamano_paginacion' => 5)); ?>
							</td>
						</tr>
						<?php
					} else {
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

	echo '<tr><td colspan="2">Indique la informaci&oacute;n que a continuaci&oacute;n se solicita:</td></tr>';

	echo $form->input( 
		'alias',
		array(
			'label' => '<span class="requerido">* </span>Alias:',
			'type' => 'text',
			'class' => 'alias alfanumericoEspacio',
			'maxlength' => '30',
			'id' => 'txtAlias',
			'readonly' => 'readonly'
		)
	);

	if ($form->isFieldError('alias')) {
		echo '<tr><td>&nbsp;</td><td>' . $form->error(
			'alias',
			array(
				'requerido' => _m('alias.requerido', array('el alias'))
			)
		) . '</td></tr>';
	}

	// --- Inicio de los campos de monto modificados ---
	// FormHelper de CAKEPHP, con inputDefaults, envuelve cada entrada en <tr><td>...</td></tr>
	// Por lo tanto, necesitamos colocar el ID directamente en la TR o gestionarlo de otra manera.
	// El enfoque anterior con <div id="..." class="monto-input-container"> alrededor de $form->input consistía en crear divs/tablas anidadas,
	// donde el div externo estaba oculto, pero el <tr><td> interno seguía visible debido a la estructura de la tabla.

	// Para ocultar correctamente toda la fila generada por FormHelper, agregaremos una clase específica a la 'tr'
	// generada por inputDefaults y luego controlaremos esa clase.

	// Desactivar temporalmente 'before' y 'after' de inputDefaults para estas entradas específicas.
    $form->inputDefaults(
        'div',
        false,
        'label',
        'etiqueta',
        'before',
        '', // Establecer como cadena vacía para estas entradas específicas
        'between',
        '</td><td class="campo">',
        'after',
        '', // Establecer como cadena vacía para estas entradas específicas
        'error',
        false
    );

    // Ahora, crea manualmente <tr> y <td> para cada entrada y aplica la clase
    echo '<tr id="montoInputGeneralRow" class="monto-input-container">';
    echo '<td class="etiqueta">';
    echo $form->label('monto', '<span class="requerido">*</span> Monto a pagar Bs.:');
    echo '</td><td class="campo">';
    echo $form->text(
        'monto',
        array(
            'class' => 'monto pago'
        )
    );
    echo '</td></tr>';

    echo '<tr id="montoInputMovilnetRow" class="monto-input-container">';
    echo '<td class="etiqueta">';
    echo $form->label('montoFormMovt', '<span class="requerido">*</span> Monto a pagar Bs. (Movilnet):');
    echo '</td><td class="campo">';
    echo $form->select(
        'montoFormMovt',
        $limiteDigMov['montos_movilnet'],
        array(
            'class' => 'monto pago'
        )
    );
    echo '</td></tr>';

    echo '<tr id="montoInputDigitelRow" class="monto-input-container">';
    echo '<td class="etiqueta">';
    echo $form->label('montoFormDig', '<span class="requerido">*</span> Monto a pagar Bs. (Digitel):');
    echo '</td><td class="campo">';
    echo $form->select(
        'montoFormDig',
        $limiteDigMov['montos_digitel'],
        array(
            'class' => 'monto pago'
        )
    );
    echo '</td></tr>';

    echo '<tr id="montoInputMovistarRow" class="monto-input-container">';
    echo '<td class="etiqueta">';
    echo $form->label('montoFormMov', '<span class="requerido">*</span> Monto a pagar Bs. (Movistar):');
    echo '</td><td class="campo">';
    echo $form->select(
        'montoFormMov',
        $limiteDigMov['montos_movistar'],
        array(
            'class' => 'monto pago'
        )
    );
    echo '</td></tr>';

    // Restaurar los valores predeterminados de entrada originales si es necesario para entradas posteriores (aunque 'descripcion' sigue inmediatamente)
    $form->inputDefaults(
        'div',
        false,
        'label',
        'etiqueta',
        'before',
        '<tr><td class="etiqueta">',
        'between',
        '</td><td class="campo">',
        'after',
        '</td></tr>',
        'error',
        false
    );
    // --- Fin de los campos de monto modificados ---

	if ($form->isFieldError('monto')) {
        echo '<tr><td>&nbsp;</td><td>' . $form->error(
            'monto',
            array(
                'requerido' => _m('seleccion.requerida', 'el monto'),
                'numerico' => _m('formato.invalido', 'el monto'),
                'montoMaximo' => _m('monto.maximo', 'del numero de servicio'),
                'monto_limite' => _m('monto.excedido', 'para la recarga'),
                'montoLimiteUsuario' => _m('monto.limite.usuario', 'de la recarga'),
                'recargaMinima' => _m('recarga.minima', $montoMinimo),
                'recargaMaxima' => _m('recarga.maxima', $montoMaximo)
            )
        ) . '</td></tr>';
	}


	echo $form->input(
		'descripcion',
		array(
			'label' => '<span class="requerido">*</span> Descripci&oacute;n:',
			'type' => 'text',
			'maxlength' => '30',
			'class' => 'alfanumericoEspacio'
		)
	);

	if ($form->isFieldError('descripcion')) {
		echo '<tr><td>&nbsp;</td><td>' . $form->error(
			'descripcion',
			array(
				'requerido' => _m('campo.requerido', 'la descripci&oacute;n'),
				'alfanumerico' => _m('alfanumerico.invalido', 'la descripci&oacute;n')
			)
		) . '</td></tr>';
	}

	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

	/*
	echo '<tr id="notaDigitel" style="display:none">
 <td colspan="2">
 Nota: Estimado Cliente, recuerde que el monto debe ser múltiplo de ' . intval(_p('multiplo.digitel')) . ' entre Bs. ' . formatea_monto_fromWs(_p('recarga_minima_digitel')) . ' y ' . formatea_monto_fromWs(_p('recarga_maxima_digitel')) . '
</td>
</tr>';
	*/

	echo '<tr><td>&nbsp;</td><td class="botonera">';

	echo $html->link(
		'Volver',
		'recarga_servicios',
		array(
			'class' => 'button'
		)
	);

	echo '&nbsp;&nbsp;';

	echo $html->link(
		'Aceptar',
		'javascript:',
		array(
			'class' => 'button envio_formulario',
			'id' => 'recargar'
		)
	);


	echo '</td></tr></table>';

	echo $form->hidden('offset');
	echo $form->end();

	?>
</div>
<?php
echo $form->create(
	'RecargaServicios',
	array(
		'url' => array(
			'controller' => 'recarga_servicios',
			'action' => $this->action
		),
		'id' => 'paginacion'
	)
);
if (!empty($this->data['RecargaServicios'])) {
	foreach ($this->data['RecargaServicios'] as $k => $v) {
		echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<style>
/*    .monto-input-container {
        display: none; /* Oculta todos los contenedores de monto inicialmente */
    }
*/
    /* Nuevo estilo para opciones deshabilitadas */
    #RecargaServiciosMontoFormMovt option:disabled,
    #RecargaServiciosMontoFormDig option:disabled,
    #RecargaServiciosMontoFormMov option:disabled {
        background-color: #e0e0e0; /* Un gris claro */
        color: #888; /* Opcional: para que el texto también sea un poco más oscuro */
    }
</style>

<script type="text/javascript">
    $(function () {

        var recarga_minima;
        var recarga_maxima;
        $('#notaDigitel').hide();

        $('.monto-input-container').hide();

        $('#RecargaServiciosMontoFormDig option[value=""]:first').text('Seleccione monto');
        $('#RecargaServiciosMontoFormMov option[value=""]:first').text('Seleccione monto');
        $('#RecargaServiciosMontoFormMovt option[value=""]:first').text('Seleccione monto');
        $('#RecargaServiciosMontoFormDig').val('');
        $('#RecargaServiciosMontoFormMov').val('');
        $('#RecargaServiciosMontoFormMovt').val('');

        // Si la página viene con un afiliado seleccionado (por ejemplo, al regresar del paso 2), restaurar selección y montos
        localStorage.removeItem('servicioRecargaSeleccionado');
        var $radioSeleccionado = $('.seleccion_afiliado:checked');
        if ($radioSeleccionado.length) {
            $radioSeleccionado.trigger('click');
            <?php if (!empty($this->data['RecargaServicios']['montoFormMovt'])) { ?>
                $('#RecargaServiciosMontoFormMovt').val(<?php echo json_encode($this->data['RecargaServicios']['montoFormMovt']); ?>);
            <?php } ?>
            <?php if (!empty($this->data['RecargaServicios']['montoFormDig'])) { ?>
                $('#RecargaServiciosMontoFormDig').val(<?php echo json_encode($this->data['RecargaServicios']['montoFormDig']); ?>);
            <?php } ?>
            <?php if (!empty($this->data['RecargaServicios']['montoFormMov'])) { ?>
                $('#RecargaServiciosMontoFormMov').val(<?php echo json_encode($this->data['RecargaServicios']['montoFormMov']); ?>);
            <?php } ?>
            <?php if (!empty($this->data['RecargaServicios']['monto'])) { ?>
                $('#RecargaServiciosMonto').val(<?php echo json_encode($this->data['RecargaServicios']['monto']); ?>);
            <?php } ?>
        } else {
            // Si es un ingreso limpio a la página, asegurar campos limpios
            $('#txtAlias').val('');
            $('#txtAlias').attr('readonly', true);
            $('#RecargaServiciosId').val('');
            $('#RecargaServiciosTipoOperacion').val('');
            $('#RecargaServiciosTipoServicio').val('');
            $('#RecargaServiciosMonto').val('');
            $('#RecargaServiciosMontoFormDig').val('');
            $('#RecargaServiciosMontoFormMov').val('');
            $('#RecargaServiciosMontoFormMovt').val('');
            $('#RecargaServiciosDescripcion').val('');
            $('.monto-input-container').hide();
        }
        $('.seleccion_afiliado').click(function () {
            if ($(this).prop('checked')) {
                var datos = $(this).data('datos').split(".");
                $('#RecargaServiciosId').val(datos[0]);
                $('#txtAlias').val(datos[1]);
                $('#RecargaServiciosTipoOperacion').val(datos[2]);
                $('#RecargaServiciosTipoServicio').val(datos[3]);
                if (!datos[1].length) {
                    $('#txtAlias').removeAttr('readonly');
                } else {
                    $('#txtAlias').attr('readonly', true);
                }

                $('.monto-input-container').hide();
                var montoMaximoAfiliacion = parseFloat($('#monto' + datos[0]).val().replace(/\./g, '').replace(',', '.'));
                if (datos[2] === 'sd') { // Digitel
                    recarga_minima = <?php echo $limiteDigMov['minimo_digitel_php'] ?>;
                    $('#recarga_minima').val(recarga_minima);
                    recarga_maxima = <?php echo $limiteDigMov['maximo_digitel_php'] ?>;
                    $('#recarga_maxima').val(recarga_maxima);
                    $('#montoInputDigitelRow').show();

                    var $digitelSelect = $('#RecargaServiciosMontoFormDig');
                    $digitelSelect.val('');

                    $digitelSelect.find('option').each(function() {
                        var originalText = $(this).data('original-text');
                        if (originalText === undefined) {
                            $(this).data('original-text', $(this).text());
                        } else {
                            $(this).text(originalText);
                        }
                        $(this).prop('disabled', false);

                        var optionValue = parseFloat($(this).val().replace(/\./g, '').replace(',', '.'));
                        if (isNaN(optionValue) || $(this).val() === '') {
                            $(this).prop('disabled', false);
                        } else if (optionValue > montoMaximoAfiliacion) {
                            $(this).prop('disabled', true);
                            $(this).text($(this).data('original-text') + ' (Excede el monto m\u00e1ximo)');
                        } else {
                            $(this).prop('disabled', false);
                        }
                    });

                    var firstSelectableOptionVal = '';
                    $digitelSelect.find('option:enabled').each(function() {
                        if ($(this).val() !== '') {
                            firstSelectableOptionVal = $(this).val();
                            return false;
                        }
                    });

                    if (firstSelectableOptionVal !== '') {
                        $digitelSelect.val(firstSelectableOptionVal);
                    } else {
                        $digitelSelect.val('');
                    }

                } else if (datos[2] === 'st') { // Movistar
                    recarga_minima = <?php echo $limiteDigMov['minimo_movistar_php'] ?>;
                    $('#recarga_minima').val(recarga_minima);
                    recarga_maxima = <?php echo $limiteDigMov['maximo_movistar_php'] ?>;
                    $('#recarga_maxima').val(recarga_maxima);
                    $('#montoInputMovistarRow').show();

                    var $movistarSelect = $('#RecargaServiciosMontoFormMov');
                    $movistarSelect.val('');

                    $movistarSelect.find('option').each(function() {
                        var originalText = $(this).data('original-text');
                        if (originalText === undefined) {
                            $(this).data('original-text', $(this).text());
                        } else {
                            $(this).text(originalText);
                        }
                        $(this).prop('disabled', false);

                        var optionValue = parseFloat($(this).val().replace(/\./g, '').replace(',', '.'));
                        if (isNaN(optionValue) || $(this).val() === '') {
                            $(this).prop('disabled', false);
                        } else if (optionValue > montoMaximoAfiliacion) {
                            $(this).prop('disabled', true);
                            $(this).text($(this).data('original-text') + ' (Excede el monto m\u00e1ximo)');
                        } else {
                            $(this).prop('disabled', false);
                        }
                    });

                    var firstSelectableOptionVal = '';
                    $movistarSelect.find('option:enabled').each(function() {
                        if ($(this).val() !== '') {
                            firstSelectableOptionVal = $(this).val();
                            return false;
                        }
                    });

                    if (firstSelectableOptionVal !== '') {
                        $movistarSelect.val(firstSelectableOptionVal);
                    } else {
                        $movistarSelect.val('');
                    }

                } else if (datos[2] === 'sm') { // Movilnet
                    recarga_minima = <?php echo $limiteDigMov['minimo_movilnet_php'] ?>;
                    console.log(recarga_minima)
                    $('#recarga_minima').val(recarga_minima);
                    recarga_maxima = <?php echo $limiteDigMov['maximo_movilnet_php'] ?>;
                    console.log(recarga_maxima)
                    montoMaximoAfiliacion = parseFloat(recarga_maxima);
                    console.log(montoMaximoAfiliacion)
                    $('#recarga_maxima').val(recarga_maxima);
                    $('#montoInputMovilnetRow').show();

                    var $movilnetSelect = $('#RecargaServiciosMontoFormMovt');
                    $movilnetSelect.val('');
                    
                    $movilnetSelect.find('option').each(function() {
                        var originalText = $(this).data('original-text');
                        if (originalText === undefined) {
                            $(this).data('original-text', $(this).text());
                        } else {
                            $(this).text(originalText);
                        }
                        $(this).prop('disabled', false);
                        
                        console.log(montoMaximoAfiliacion)
                        var optionValue = parseFloat($(this).val().replace(/\./g, '').replace(',', '.'));
                        if (isNaN(optionValue) || $(this).val() === '') {
                            $(this).prop('disabled', false);
                        } else if (!isNaN(montoMaximoAfiliacion) && montoMaximoAfiliacion > 0 && optionValue > montoMaximoAfiliacion) {
                            $(this).prop('disabled', true);
                            $(this).text($(this).data('original-text') + ' (Excede el monto m\u00e1ximo)');
                        } else {
                            $(this).prop('disabled', false);
                        }
                    });

                    var firstSelectableOptionVal = '';
                    $movilnetSelect.find('option:enabled').each(function() {
                        if ($(this).val() !== '') {
                            firstSelectableOptionVal = $(this).val();
                            return false;
                        }
                    });

                    if (firstSelectableOptionVal !== '') {
                        $movilnetSelect.val(firstSelectableOptionVal);
                    } else {
                        $movilnetSelect.val('');
                    }

                } else { // Otros casos, incluyendo 'si' y otros
                    var recarga_minima_inter = <?php echo json_encode(_p('recarga_minima_inter_pre')); ?>;
                    var recarga_maxima_inter = <?php echo json_encode(_p('recarga_maxima_inter_pre')); ?>;

                    if (recarga_minima_inter) {
                        $('#recarga_minima').val(recarga_minima_inter);
                    } else {
                        $('#recarga_minima').val('');
                    }

                    if (recarga_maxima_inter) {
                        $('#recarga_maxima').val(recarga_maxima_inter);
                    } else {
                        $('#recarga_maxima').val('');
                    }
                    $('#montoInputGeneralRow').show();
                    $('#RecargaServiciosMonto').val('');
                }
            }
        });

        $('#RecargaServiciosAfiliadosForm').validate({
            rules: {
                'data[RecargaServicios][numservicio]': {
                    required: true
                },
                'data[RecargaServicios][montoFormMovt]': {
                    required: function() {
                        return $('#RecargaServiciosTipoOperacion').val() === 'sm';
                    }
                },
                'data[RecargaServicios][montoFormDig]': {
                    required: function() {
                        return $('#RecargaServiciosTipoOperacion').val() === 'sd';
                    }
                },
                'data[RecargaServicios][montoFormMov]': {
                    required: function() {
                        return $('#RecargaServiciosTipoOperacion').val() === 'st';
                    }
                },
                'data[RecargaServicios][monto]': {
                    required: function() {
                        return $('#RecargaServiciosTipoOperacion').val() === 'si';
                    }
                },
                'data[RecargaServicios][descripcion]': {
                    required: true,
                    alphaNumericSpace: true
                }
            },
            messages: {
                'data[RecargaServicios][numservicio]': {
                    required: <?php echo _m('uno.seleccion.requerida', array('de los servicios'), true); ?>
                },
                'data[RecargaServicios][montoFormMovt]': {
                    required: <?php echo _m('seleccion.requerida', array('el monto'), true); ?>
                },
                'data[RecargaServicios][montoFormDig]': {
                    required: <?php echo _m('seleccion.requerida', array('el monto'), true); ?>
                },
                'data[RecargaServicios][montoFormMov]': {
                    required: <?php echo _m('seleccion.requerida', array('el monto'), true); ?>
                },
                'data[RecargaServicios][monto]': {
                    required: <?php echo _m('seleccion.requerida', array('el monto'), true); ?>
                },
                'data[RecargaServicios][descripcion]': {
                    required: <?php echo _m('campo.requerido', array('la descripci&oacute;n'), true); ?>,
                    alphaNumericSpace: <?php echo _m('alfanumerico.invalido', array('la descripci&oacute;n'), true); ?>
                }
            }
        });
    });
</script>