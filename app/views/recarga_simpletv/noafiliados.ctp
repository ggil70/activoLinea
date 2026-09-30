<?php
/**
 * Formulario de recarga prepago numeros no afiliados
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
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<?php

	echo $form->create('RecargaServicios',
		array(
			'url'=> array(
				'controller'=>'recarga_servicios',
				'action'=>'noafiliados'
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
	
	echo $form->hidden('cuentaDebitar');        
        //Limite para realizar la transaccion segun el acumulado
	echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$monto_limite));
        //Limite general definido por el cliente
        echo $form->hidden('limite_total', array('id'=>'limite_total', 'value'=>$limite_total));
        echo $form->hidden('monto_limite_usuario', array('id'=>'monto_limite_usuario', 'value'=>$monto_limite_usuario));
        echo $form->hidden('recarga_minima', array('id'=>'recarga_minima'));
        echo $form->hidden('recarga_maxima', array('id'=>'recarga_maxima'));
        echo $form->hidden('multiplo', array('id'=>'multiplo', 'value'=>_p('multiplo.digitel')));


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
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la recarga.</td></tr>';
	echo '<tr><td colspan="2"></td></tr>';	

        echo '
	<tr>
	<td class="etiqueta">Cuenta de Débito:</td>
	<td> ' . enmascara($recarga['RecargaServicios']['cuentaDebitar']) . ' </td>
	</tr>
	';
        
	echo '<tr><td colspan="2"></td></tr>';
	echo '<tr><td colspan="2">Indique la informaci&oacute;n que a continuaci&oacute;n se solicita:</td></tr>';

	$input_tipo_operacion = array(
		'label'=>'<span class="requerido">* </span>Tipo de Operación:',
		'type'=>'select',
		'style'=>'width:250px',
		'options'=>$tipo_operacion,
		'empty'=>'Seleccione',
	);
		
	echo $form->input(
		'tipoOperacion',
		$input_tipo_operacion
	);
	
	if ($form->isFieldError('tipoOperacion')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('tipoOperacion',
				array(
					'requerido'=>_m('seleccion.requerida',array('el tipo de operacion'))
				)			
			).'</td></tr>';
					
	}
        ?>
        <tr>
	<td class="etiqueta labelBeneficiario"><span class="requerido">* </span>Número de Servicio:</td>
            <td class="campo">	
                <?php

                 echo $form->input(
                        'codigo_telefono',
                        array(
                                'before'=>false,
                                'between'=>false,
                                'after'=>false,
                                'label'=>false,
                                'class'=>'codigoTelefono',
                                'type'=>'select',                                
                                'options'=>''
                        )
                );

                echo $form->input(
                        'numservicio',
                        array(
                                'before'=>false,
                                'between'=>false,
                                'label'=>false,
                                'style'=>'width:150px',
                                'type'=>'text',
                                'minlength' => '7',
                                'maxlength' => '7',
                                'class'=>'numerico'
                        )
                );
                ?>
            </td>
        </tr>
        <?php
        
        if ($form->isFieldError('numservicio')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('numservicio',
				array(
					'requerido'=>_m('campo.requerido','el número de servicio'),
					'numero'=>_m('formato.invalido','el número de servicio'),					
					'formato_servicio'=>_m('formato.servicio'),
				)
			).'</td></tr>';
	}
        
        echo $form->input(
		'nombreBeneficiario',
		array(
			'label'=>'<span class="requerido">*</span> Nombre del Beneficiario:',
			'type'=>'text',
                        'maxlength'=>30,
			'class'=>'alfanumericoEspacio'
		)
	);                

	if ($form->isFieldError('nombreBeneficiario')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('nombreBeneficiario',
				array(
					'requerido'=> ($this->data['Afiliacion']['tipo_afiliacion']=='r' || $this->data['Afiliacion']['tipo_afiliacion']=='s') ? _m('error.fatal') : _m('campo.requerido','el nombre'),
					'alfanumerico'=>_m('alfanumerico.invalido','el nombre')
				)
			).'</td></tr>';
	}
        
	echo $form->input(
		'monto',
		array(
			'label'=>'<span class="requerido">*</span> Monto Bs.:',
			'type'=>'text',
			'class'=>'monto pago'
		)
	);

	if ($form->isFieldError('monto')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('monto',
				array(
					'requerido'=>_m('campo.requerido','el monto'),
					'numerico'=>_m('formato.invalido','el monto'),
					'montoMaximo'=> _m('monto.maximo', 'del servicio'),
					'montoLimiteDiario'=> _m('monto.excedido', 'para la recarga'),
                                        'montoLimiteUsuario' => _m('monto.limite.usuario', 'de la recarga'),
                                        'recargaMinima'=>_m('recarga.minima',$montoMinimo),
					'recargaMaxima'=>_m('recarga.maxima',$montoMaximo)
				)
			).'</td></tr>';
	}

	echo $form->input(
		'descripcion',
		array(
			'label'=>'<span class="requerido">*</span> Descripci&oacute;n:',
			'type'=>'text',
			'maxlength' => '30',
			'class' => 'alfanumericoEspacio',
		)
	);

	if ($form->isFieldError('descripcion')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('descripcion',
				array(
					'requerido'=>_m('campo.requerido','la descripción'),
					'alfanumerico'=>_m('alfanumerico.invalido','la descripción')
				)
			).'</td></tr>';
	}

	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
        
	echo '<tr><td>&nbsp;</td><td class="botonera">';
        
        echo $html->link(
		'Volver',
		'recarga_servicios',
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
        
	echo $form->end();

	?>
</div>

<script>

//Carga los prefijos telefonicos segun el tipo de operacion
function acomoda_prefijos(){
				
	var subtipo = $('#RecargaServiciosTipoOperacion').val();        
        var recarga_minima;
        var recarga_maxima;
	
        switch(subtipo){
		case 'sd':
                        $('#RecargaServiciosCodigoTelefono').empty().append();
			<?php foreach ($prefijos_telefonicos as $key) { 
                           if ($key['PrefijosTelefonicos']['tipo_operacion']=='sd'){                             
                        ?>
                            $('#RecargaServiciosCodigoTelefono').append(
                                    $('<option>',{
                                            'value': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>',
                                            'text': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>'
                                    })
                            );
			<?php 
                           }
                        } 
                        ?>
                        recarga_minima = <?php echo _p('recarga_minima_digitel') ?>;
                        $('#recarga_minima').val(recarga_minima);
                        recarga_maxima = <?php echo _p('recarga_maxima_digitel') ?>;
                        $('#recarga_maxima').val(recarga_maxima);
			break;
                case 'sm':
                        $('#RecargaServiciosCodigoTelefono').empty().append();
			<?php foreach ($prefijos_telefonicos as $key) { 
                           if ($key['PrefijosTelefonicos']['tipo_operacion']=='sm'){                             
                        ?>
                            $('#RecargaServiciosCodigoTelefono').append(
                                    $('<option>',{
                                            'value': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>',
                                            'text': '<?php echo $key['PrefijosTelefonicos']['codigo_telefono'] ?>'
                                    })
                            );
			<?php 
                           }
                        } 
                        ?>
                        recarga_minima = <?php echo _p('recarga_minima') ?>;
                        $('#recarga_minima').val(recarga_minima);
                        recarga_maxima = <?php echo _p('recarga_maxima') ?>;
                        $('#recarga_maxima').val(recarga_maxima);
			break;
                default:
                        $('#RecargaServiciosCodigoTelefono').empty().append();
                        recarga_minima = <?php echo _p('recarga_minima') ?>;
                        $('#recarga_minima').val(recarga_minima);
                        recarga_maxima = <?php echo _p('recarga_maxima') ?>;
                        $('#recarga_maxima').val(recarga_maxima);
	}		
}

$(function(){
        
	// Condicion inicial al cargar el formulario
	$(".opciones").attr("disabled", $(".seleccion:checked").val()!='s');
			
        //acomoda_tipo_operacion();
	$('#RecargaServiciosTipoOperacion').change(acomoda_prefijos);
        acomoda_prefijos()
	
        // Validacion
	$('#RecargaServiciosNoafiliadosForm').validate({
		rules:{ 
			'data[RecargaServicios][cuentaDebitar]':{
				required:true
			},
                        'data[RecargaServicios][tipoOperacion]':{
				required:true
			},
                        'data[RecargaServicios][numservicio]':{
				required:true,
                                digits:true,
                                digitel:{
					depends: function(e){
						return ($('#RecargaServiciosTipoOperacion').val()=='sd');
					}			
				}
			},
                        'data[RecargaServicios][codigo_telefono]':{
				required:true
			},
                        'data[RecargaServicios][nombreBeneficiario]':{
				required:true
			},                        
			'data[RecargaServicios][monto]':{
				required:true, 
                                numberVE:true,
                                montoMaximo: '#RecargaServiciosMontoMaximo',                              
				montoMinimo: function(e){
                                        if($('#RecargaServiciosTipoOperacion').val()!='') return '#recarga_minima';
                                },
                                montoMaximo2: function(e){
					if($('#RecargaServiciosTipoOperacion').val()!='') return '#recarga_maxima';
                                },
                                montoMaximo3: '#monto_limite',
                                montoMaximo4: '#monto_limite_usuario',
                                montoMultiplo: function(e){
                                        if($('#RecargaServiciosTipoOperacion').val()=='sd') return '#multiplo';
                                }
			},
			'data[RecargaServicios][descripcion]':{
				required:true,alphaNumericSpace:true
			}
		},
		messages:{
			'data[RecargaServicios][cuentaDebitar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta de débito'),true) ?>
			},
			'data[RecargaServicios][tipoOperacion]':{
				required:<?php echo _m('seleccion.requerida',array('el tipo de operación'),true) ?>
			},
			'data[RecargaServicios][numservicio]':{
				required:<?php echo _m('campo.requerido','el número de servicio',true) ?>,
                                digits:<?php echo _m('formato.invalido','el campo solicitado', true)?>,
                                digitel:<?php echo _m('formato.invalido','el campo solicitado',true) ?>				
			},
                        'data[RecargaServicios][codigo_telefono]':{
				required:<?php echo _m('seleccion.requerida','el codigo telefónico',true) ?>
			},
                        'data[RecargaServicios][nombreBeneficiario]':{
				required:<?php echo _m('campo.requerido','el nombre',true) ?>
			},        
			'data[RecargaServicios][monto]':{
				required:<?php echo _m('campo.requerido','el monto',true) ?>,
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				montoMaximo:<?php echo _m('monto.maximo', 'del numero de servicio',true) ?>,
				montoMinimo: function(e){
                                    if($('#RecargaServiciosTipoOperacion').val()==='sd'){
                                        return <?php echo _m('monto.recarga.invalido',array(_p('multiplo.digitel'),formatea_monto_fromWs(_p('recarga_minima_digitel')),formatea_monto_fromWs(_p('recarga_maxima_digitel')),'204'),true) ?>
                                    }else{
                                        return <?php echo _m('recarga.minima',  formatea_monto_fromWs(_p('recarga_minima')) ,true) ?>
                                    }
                                },
				montoMaximo2: function(e){
                                    if($('#RecargaServiciosTipoOperacion').val()==='sd'){
                                        return <?php echo _m('monto.recarga.invalido',array(_p('multiplo.digitel'),formatea_monto_fromWs(_p('recarga_minima_digitel')),formatea_monto_fromWs(_p('recarga_maxima_digitel')),'204'),true) ?>
                                    }else{
                                        return <?php echo _m('recarga.maxima',  formatea_monto_fromWs(_p('recarga_maxima')) ,true) ?>
                                    }
                                },
				montoMaximo3:<?php echo _m('monto.excedido', 'de la recarga' ,true) ?>,
                                montoMaximo4:<?php echo _m('monto.limite.usuario', 'de la recarga' ,true) ?>,
                                montoMultiplo:<?php echo _m('monto.recarga.invalido',array(_p('multiplo.digitel'),formatea_monto_fromWs(_p('recarga_minima_digitel')),formatea_monto_fromWs(_p('recarga_maxima_digitel')),'204'),true) ?>
			},
			'data[RecargaServicios][descripcion]':{
				required:<?php echo _m('campo.requerido','la descripci&oacute;n',true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','la descripci&oacute;n',true) ?>
			}
		}	
	});
	
});
</script>
