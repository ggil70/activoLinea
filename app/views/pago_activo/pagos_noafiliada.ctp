<?php
/**
 * Formulario de transferencias a cuentas no afiliadas de terceros en otros bancos
**/
?>
<div>
<h2 class="user">Pago Activo</h2>
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

	<?php

	echo $form->create('PagoActivo',
		array(
			'url'=> '/pago_activo/pagos_noafiliada',
			'id'=>'PagoActivoPagoForm',
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

        echo $form->hidden('tipoPersona');
	echo $form->hidden('tipoDestinatario');
	echo $form->hidden('cuentaDebitar');
	echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$limite_pago_activo));	


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
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos del pago.</td></tr>';

	echo '
	<tr>
	<td class="etiqueta"><span class="estiloTexto"></span>Cuenta de D&eacute;bito</td>
	<td> ' . enmascara($this->data['PagoActivo']['cuentaDebitar']) . ' </td>
	</tr>
	';

	echo '<tr><td colspan="2">&nbsp;</td></tr>';
	echo '<tr><td colspan="2"><span class="estiloTexto"></span>Indique la informaci&oacute;n que a continuaci&oacute;n se solicita:</td></tr>';

	echo '<tr><td class="etiqueta"><span class="requerido">* </span>Número Celular:</td>';	
        echo '<td class="campo">';                

                 echo $form->input(
                        'codigo_telefono',
                        array(
                                'before'=>false,
                                'between'=>false,
                                'after'=>false,
                                'label'=>false,                                
                                'class'=>'codigoTelefono',
                                'type'=>'select',
                                'options'=>$prefijos_telefonicos                                
                        )
                );

                echo $form->input(
                        'telefono',
                        array(
                                'before'=>false,
                                'between'=>false,
                                'label'=>false,
                                'type'=>'text',
                                'style'=>'width:150px',
                                'class'=>'numerico',
                                'maxlength'=>7
                        )
                );  
                
        echo '</td>';
        echo '</tr>';
        
        if ($form->isFieldError('telefono')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('celular',
                    array(
                        'requerido'=>_m('campo.requerido','el número de celular'),
                        'numero'=>_m('formato.invalido','el número de celular')					
                    )
            ).'</td></tr>';
	}
        
	$input_banco = array(
            'label'=>'<span class="requerido">* </span>Banco Receptor:',
            'type'=>'select',
            'options'=>$bancos
	);
	
	if(count($bancos) > 1) $input_banco['empty'] = 'Seleccione';
	
	echo $form->input(
            'banco_receptor',
            $input_banco
	);
	
	if ($form->isFieldError('banco_receptor')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('banco_receptor',
                array(
                    'requerido'=>_m('seleccion.requerida','el banco receptor')
                )			
            ).'</td></tr>';
	}
        
        if($this->data['PagoActivo']['tipoPersona']=='0'){
            $nacionalidad = array('V'=>'V','E'=>'E','P'=>'P');
        }else{
            $nacionalidad = array('V'=>'V','J'=>'J','G'=>'G');
        }
        
	echo $form->input(
		'tipoCedulaRif',
		array(
			'after'=>false,
			'label'=>'<span class="requerido">* </span>C&eacute;dula / RIF del Beneficiario:',
			'type'=>'select',
			'class'=>'tipoRif',
			'options'=>$nacionalidad
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
                        'maxlength'=>15
		)
	);

	if ($form->isFieldError('tipoCedulaRif')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tipoCedulaRif',
				array(
					'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de documento de identidad')),
					'requerido'=>_m('campo.requerido',array('el tipo de documento de identidad'))
				)).'</td></tr>';
	} else if ($form->isFieldError('nroCedulaRif')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nroCedulaRif',
				array(
					'requerido'=>_m('campo.requerido',array('la cédula o rif del beneficiario')),
					'longitud'=>_m('valor.invalido',array('la cédula o rif del beneficiario')),
                                        'destinatario'=>_m('identificacion.igualcliente',array('la cédula o rif del beneficiario'))
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
					'numerico'=>_m('formato.invalido',array('el monto')),
					'requerido'=>_m('campo.requerido',array('el monto')),
					'montoMaximo'=> _m('monto.maximo', 'del pago'),
                                        'montoLimiteDiario'=>_m('monto.excedido', 'del pago') 
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
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';               
        
	echo '<tr><td colspan="2"></td></tr>';               
        
	echo '<tr><td>&nbsp;</td><td class="submit">';
        
        echo $html->link(
            'Volver',
            'pagos/v',
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
                'id'=>'pagar'
            )
	);		

	echo '</td></tr></table>';  

	echo $form->end();

	?>
</div>


<script>
$(function(){


   $('#PagoActivoMonto').blur(function (e) {
    	var monto = $('#PagoActivoMonto').val();
        //Si el monto solo posee un solo decimal, hay que completar los dos decimale
        var arreglo_monto = monto.split(',');
        var entero = arreglo_monto[0];
        var decimal = arreglo_monto[1];

        if (decimal.length == 1){
           monto = monto + '0';
           $('#PagoActivoMonto').val(monto);
        }
        
    }); 


	
	// para la validacion
	$('#PagoActivoPagoForm').validate({
		rules:{                        
			'data[PagoActivo][telefono]':{
				required:true, 
                                digits:true
			},
			'data[PagoActivo][banco_receptor]':{
				required:true
			},
			'data[PagoActivo][cuentaCreditar]':{
				required:true, digits:true, minlength:20, maxlength:20,
				inarray:<?php echo json_encode(array_values(array_diff(array_keys($bancos),array('0171')))) ?>
			},
			'data[PagoActivo][nombreBeneficiario]':{
				required:true,alphaNumericSpace:true
			},
			'data[PagoActivo][nroCedulaRif]':{
				required:true,
				digits:{
					depends: function(e){
						return $('#PagoActivoTipoCedulaRif').val()=='V';
					}				
				},
				alphanumeric:{
					depends: function(e){
						return $('#PagoActivoTipoCedulaRif').val()=='P';
					}				
				}
			},
			'data[PagoActivo][monto]':{
				required:true,
				numberVE:true,
				montoMaximo2: '#monto_limite'                               
			},
			'data[PagoActivo][descripcion]':{
				required:true,alphaNumericSpace:true
			}
		},
		messages:{                        
                        'data[PagoActivo][telefono]':{
				required:<?php echo _m('seleccion.requerida',array('el número de celular'),true) ?>,
				digits:<?php echo _m('formato.invalido','el campo solicitado', true)?>				
			},
			'data[PagoActivo][banco_receptor]':{
				required:<?php echo _m('seleccion.requerida',array('el banco'),true) ?>
			},
			'data[PagoActivo][cuentaCreditar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta del beneficiario'),true) ?>,
				digits:<?php echo _m('valor.invalido',array('el número de cuenta'),true) ?>,
				inarray:<?php echo _m('valor.invalido',array('el número de cuenta'),true) ?>,
				minlength:<?php echo _m('cantidad.igual.a',array('el número de cuenta', '20 dígitos'),true) ?>,
				maxlength:<?php echo _m('valor.invalido',array('el número de cuenta'),true) ?>
			},
			'data[PagoActivo][nombreBeneficiario]':{
				required:<?php echo _m('campo.requerido',array('el nombre del beneficiario'),true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido',array('el nombre del beneficiario'),true) ?>
			},
			'data[PagoActivo][nroCedulaRif]':{
				required:<?php echo _m('campo.requerido',array('la cédula o rif del beneficiario'),true) ?>,
				digits:<?php echo _m('formato.invalido',array('la cédula o rif del beneficiario'),true) ?>,
				alphanumeric:<?php echo _m('alfanumerico.invalido',array('el pasaporte del beneficiario'),true) ?>
			},			
			'data[PagoActivo][monto]':{
				required:<?php echo _m('campo.requerido',array('el monto'),true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto'),true) ?>,
				montoMaximo2: <?php echo _m('monto.limite.config', 'del pago' ,true) ?>,
                                montoMaximo:<?php echo _m('monto.excedido', 'del pago' ,true) ?>
			},
			'data[PagoActivo][descripcion]':{
				required:<?php echo _m('campo.requerido',array('la descripcion'),true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido',array('la descripcion'),true) ?>
			}
		}		
	});
	
});




</script>

