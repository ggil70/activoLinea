<?php
/**
 * Formulario de transferencias propias a otros bancos para cuentas no afiliadas
**/
?>
<div>
<h2 class="user">Transferencia Mismo Titular Otro Banco</h2>
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
				'action'=>'otros_noafiliada'
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
	echo $form->hidden('limite_total', array('id'=>'limite_total', 'value'=>$limite_total));
	echo $form->hidden('monto_limite_usuario', array('id'=>'monto_limite_usuario'));
	echo $form->hidden('limite_simf', array('id'=>'limite_simf', 'value'=>$limite_simf));
	echo $form->hidden('altoV', array('id'=>'altoV'));

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
	
	echo '<tr><td colspan="2" class="mensaje">A continuaci&oacute;n proceda a ingresar los datos de la transferencia.</td></tr>';


	echo '
	<tr>
	<td class="etiqueta"><span class="estiloTexto"> </span>Cuenta de Débito</td>
	<td> ' . enmascara($this->data['Transferencia']['cuentaDebitar']) . ' </td>
	</tr>
	';

	echo '<tr><td colspan="2"><span class="estiloTexto"></span>Indique la información que a continuaci&oacute;n se solicita:</td></tr>';

        echo '<tr class="simfD"><td class="etiqueta"><span class="requerido">* </span>Destino:';
	
        echo $form->input(
            'destino',
            array(
                    'before'=>false,                                  
                    'label'=>false,
                    'class'=>'simfDT',
                    'type'=>'select',
                    'options'=>array(                        
			'c'=>'Cuenta',
			't'=>'Teléfono'			
                    )
            )
	);	
        
        if ($form->isFieldError('destino')){
                echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('destino',
                    array(
                        'valoresPermitidos'=>_m('una.seleccion.requerida','el destino'),
                        'requerido'=>_m('campo.requerido',array('el destino'))
                    )
                ).'</td></tr>';
            }
                   
        ?>
        <tr class="account">
            <td class="etiqueta"><span class="requerido">* </span>Número Cuenta:</td>
            <td class="campo">	
                <?php
                    echo $form->input(
                            'cuentaCreditar',
                            array(
                                'before'=>false,
                                'between'=>false,
                                'after'=>false,
                                'label'=>false,
                                'type'=>'text',
                                'class'=>'numerico account',
                                'minlength'=>20,
                                'maxlength'=>20
                            )
                    );

                    if ($form->isFieldError('cuentaCreditar')){
                        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaCreditar',
                                array(
                                        'nroCuenta'=>_m('valor.invalido',array('el número de cuenta')),
                                        'banco'=>_m('valor.invalido',array('el número de cuenta')),
                                        'producto_nodisponible'=>_m('producto.nodisponible')
                                )
                        ).'</td></tr>';
                    }
                ?> 
            </td>
        </tr>
        <tr class="simfT">
	<td class="etiqueta"><span class="requerido">* </span>Número Celular:</td>
            <td class="campo">	
                <?php
                    echo $form->input(
                        'codigo_telefono',
                        array(
                            'before'=>false,
                            'between'=>false,
                            'after'=>false,
                            'label'=>false,
                            'id'=>'codigo',
                            'class'=>'codigoTelefono simfT',
                            'type'=>'select',
                            'options'=>$prefijos
                        )
                    );

                    echo $form->input(
                        'telefono',
                        array(
                            'before'=>false,
                            'between'=>false,
                            'label'=>false,
                            'type'=>'text',
                            'id'=>'telefono',
                            'style'=>'width:150px',
                            'class'=>'numerico simfT',
                            'minlength'=>7,
                            'maxlength'=>7
                        )
                    );
                ?>
            </td>
        </tr>
	<?php        
            if ($form->isFieldError('telefono')){
                echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('celular',
                        array(
                            'requerido'=>_m('campo.requerido','el número de celular'),
                            'numero'=>_m('formato.invalido','el número de celular')					
                        )
                ).'</td></tr>';
            }	
        ?>
        <tr class="simfT">
	<td class="etiqueta"><span class="requerido">* </span>Banco:</td>
            <td class="campo">	
        <?php  
            $opciones = array(
                'type'=>'select',
                'options'=>$bancos,
                'empty'=>'Seleccione',
                'id'=>'banco',
                'label'=>false,
                'div'=>false,
                'before'=>false,
                'between'=>false,
                'after'=>false,
                'style'=>'width:100%'
            );                   

            echo $form->input(
                'banco',
                $opciones
            );
        ?>
            </td>
        </tr>
	<?php
        
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
					'numerico'=>_m('formato.invalido',array('el monto')),
					'requerido'=>_m('campo.requerido',array('el monto')),
					'montoMaximo'=>_m('monto.maximo', 'de la cuenta'),
					'montoLimiteDiario'=>_m('monto.excedido', 'de la transferencia'),
					'montoLimiteUsuario'=>_m('monto.limite.usuario', 'de la transferencia')
					

				)
			).'</td></tr>';
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
				)
			).'</td></tr>';
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
				));
		}
	echo '</td></tr>';

	echo $form->input(
		'alias',
		array(
			'label'=>'<span class="requerido">* </span>Alias:',
			'type'=>'text',
			'class'=>'opciones alfanumericoEspacio requerido',
			'maxlength' => '30',
		)
	);

	if ($form->isFieldError('alias')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('alias',
				array(
					'alfanumerico'=>_m('alfanumerico.invalido',array('el alias')),
					'alias_nodisponible'=>_m('alias.nodisponible')
				)
			).'</td></tr>';
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

	

	 
        

	echo '<tr class="errorlb"><td colspan="2" class="error-message">'. _m('monto.lbtr', array($limite_simf)).'              
        </tr></td>';	               
            
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

	 echo'<tr><td colspan="2"><div class="azul"><strong>Nota:'. _m('nota.lbtr', array($limite_simf)).' </strong></div></td></tr>';     	        
        
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

	var bancos = <?php echo json_encode(array_keys($bancos)) ?>;
	var tipo=null;
	
	$('.errorlb').hide();

	
	
	
        
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

       var montoLb= $('#TransferenciaMonto').val().replace(".", "");
        var montoLb= montoLb.replace(",", ".");
        var limtSIMF=$('#limite_simf').val().replace(".", "");
        var limtSIMF=limtSIMF.replace(",","." );
        if(parseFloat(montoLb) >  parseFloat(limtSIMF)){
            $("#altoV").val('m');            
            $('.simfT').hide();
            $('.account').show();
            
            $(".simfDT").val('c');
            $('.account').show();
            $("#codigo").val('');
            $("#telefono").val('');
            $("#banco").val('');
        }else{
        	$("#altoV").val('s');
           
            
        }
       
        
    });  


   $(".opciones, #TransferenciaMontoMaximo").attr("disabled", $(".seleccion:checked").val()!='s');
        $('.simfT').hide();
   
        
	$("#TransferenciaSimfN").prop("checked", true);
        $("#TransferenciaDestinoC").prop("checked", true);

	// al seleccionar la opcion negativa habilita los campos
	$(".seleccion").click(function(){
            $(".opciones, #TransferenciaMontoMaximo").attr("disabled", !($(this).val()=='s'));

            if( $(this).val()!='s' ) {
                    $("#TransferenciaMontoMaximo").val('');
                    $("#TransferenciaAlias").val('');
                    $("label.error:last").hide();
            }
	});
	
     
    $(".simfDT").change(function(){
            if($(this).val()=='t'){
                tipo=1;
                var montoLb= $('#TransferenciaMonto').val().replace(".", "");
                var montoLb= montoLb.replace(",", ".");
                var limtSIMF=$('#limite_simf').val().replace(".", "");
                var limtSIMF=limtSIMF.replace(",","." );
                if(parseFloat(montoLb) >  parseFloat(limtSIMF)){
                $("#altoV").val('m');
                $('.simfT').hide();
                $('.account').show();
                $('.errorlb').show();
                $(".simfDT").val('c');
                $('.account').show();
                $("#codigo").val('');
	            $("#telefono").val('');
	            $("#banco").val('');
            }else{
            	$('.simfT').show();
                $('.account').hide();
                $('.account').val('');
            }
            }else{
                tipo=2;
                $('.simfT').hide();
                $('.account').show();
                $("#codigo").val('');
                $("#telefono").val('');
                $("#banco").val('');
            }

           
    });


           
	
    
	// para la validacion
	$('#TransferenciaOtrosNoafiliadaForm').validate({

		rules:{
			'data[Transferencia][banco]':{
				required: {
                                    depends:function(){
                                        return ($(".simf:checked").val()=='s' && $("#TransferenciaCuentaCreditar").val()=='');
                                    }
				}
			},
			'data[Transferencia][cuentaCreditar]':{
				required: {
                                    depends:function(){
                                        return ($(".simfDT").val()=='c');
                                    }
				},
				digits:true,
				minlength:20,
				maxlength: 20,
				inarray:<?php echo json_encode(array_values(array_diff(array_keys($bancos),array('0171')))) ?>
			},
            'data[Transferencia][telefono]':{
                            required: {
                                depends:function(){
                                    return ($(".simfDT").val()=='t');
                                }
                            },
                            digits:true                            
			},
			'data[Transferencia][monto]':{
				required:true,
				numberVE:true,
				montoMaximo:'#TransferenciaMontoMaximo',	
				montoMaximo3: '#monto_limite_usuario',
				montoMaximo2:'#monto_limite',
				montoLBRT:{ 

						depends:function(){
						return (($(".simfDT").val()=="t") && ($('#TransferenciaMonto').val() > $('#limite_simf').val()));
					}            
           		}
                      
			},
			'data[Transferencia][descripcion]':{
				required:true, alphaNumericSpace:true
			},
			'data[Transferencia][afiliar]':{
				required:true
			},
                        'data[Transferencia][simf]':{
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
			'data[Transferencia][banco]':{
				required:<?php echo _m('seleccion.requerida',array('el banco'),true) ?>
			},
			'data[Transferencia][cuentaCreditar]':{
				required:<?php echo _m('seleccion.requerida',array('el número de cuenta'),true) ?>,
				digits:<?php echo _m('valor.invalido',array('el número de cuenta'),true) ?>,
				minlength:<?php echo _m('cantidad.igual.a',array('el número de cuenta', '20 dígitos'),true) ?>,
				maxlength:<?php echo _m('valor.invalido',array('el número de cuenta'),true) ?>,
				
			},
			'data[Transferencia][telefono]':{
				required:<?php echo _m('seleccion.requerida',array('el teléfono'),true) ?>,
				digits:<?php echo _m('valor.invalido',array('el teléfono'),true) ?>
								
			},
			'data[Transferencia][monto]':{
				required:<?php echo _m('campo.requerido',array('el monto'),true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto'),true) ?>,
				montoMaximo:<?php echo _m('monto.maximo', 'de la cuenta',true) ?>,
				montoMaximo2:<?php echo _m('monto.excedido', 'de la transferencia' ,true) ?>,
				montoMaximo3: <?php echo _m('monto.limite.usuario', 'de la transferencia' ,true) ?>,
				montoLBRT: <?php echo _m('campo.requerido',array('el monto'),true) ?>
				
			},
			'data[Transferencia][descripcion]':{
				required:<?php echo _m('campo.requerido',array('la descripcion'),true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido',array('la descripcion'),true) ?>
			},
			'data[Transferencia][afiliar]':{
				required:<?php echo _m('una.seleccion.requerida','de las opciones de afiliación',true) ?>
			},
                        'data[Transferencia][simf]':{
                            required:<?php echo _m('una.seleccion.requerida','de las opciones de tipo de transferencia',true) ?>
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