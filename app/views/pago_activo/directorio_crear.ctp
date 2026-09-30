<?php
  /**
   * Vista para crear/modificar el directorio Pago Activo
   */ 
 ?>

<div>
<h2 class="user">Directorio</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<?php

	echo $form->create('Directorio',
		array(
			'url'=> '/pago_activo/directorio_crear',
			'id'=>'DirectorioCrearForm',
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
                    'style'=>'table-layout:fixed;'
		)
	);
	
	// La primera fila define la tabla con layout fixed
	echo '<tr><td style="width:200px"></td><td style="width:150px"></td></tr>';

	?>
	<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la afiliaci&oacute;n.</td></tr>	      
        
        <?php
        
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
                                'maxlength'=>7,
                                'minlength'=>7
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
        
        echo $form->input(
		'tipoCliente',
		array(
			'after'=>false,
			'label'=>'<span class="requerido">* </span>Tipo de Cliente:',
			'type'=>'select',
                        'empty'=>'Seleccione',
			'options'=>array('0'=>'Natural','1'=>'Jurídico')
		)
	);
        
        if ($form->isFieldError('tipoCliente')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tipoCliente',
                array(
                    'requerido'=>_m('seleccion.requerida','el tipo de cliente'),
                    'valoresPermitidos'=>_m('formato.invalido','el campo solicitado', true),
                )			
            ).'</td></tr>';
	}
        
	$input_banco = array(
            'label'=>'<span class="requerido">* </span>Banco Receptor:',
            'type'=>'select'            
	);
	
	$input_banco['empty'] = 'Seleccione';
	
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
        
	echo $form->input(
		'tipoCedulaRif',
		array(
			'after'=>false,
			'label'=>'<span class="requerido">* </span>C&eacute;dula / RIF del Beneficiario:',
			'type'=>'select',
			'class'=>'tipoRif',
			'options'=>'Seleccione'
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
					'longitud'=>_m('valor.invalido',array('la cédula o rif del beneficiario'))                                        
				)).'</td></tr>';
	}	

	echo $form->input(
		'nombreBeneficiario',
		array(
			'label'=>'<span class="requerido">* </span>Nombre Beneficiario:',
			'type'=>'text',
			'maxlength'=>30,
			'class'=>'alfanumericoEspacio'
		)
	);

	if ($form->isFieldError('nombreBeneficiario')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nombreBeneficiario',
				array(
					'alfanumerico'=>_m('alfanumerico.invalido',array('el nombre del beneficiario')),
					'requerido'=>_m('campo.requerido',array('el nombre del beneficiario'))
				)).'</td></tr>';
	}
	
	echo '<tr>
		<td colspan="2">
		<span class="requerido">* </span>
		<span class="requerido2">Campo obligatorio</span>
		</td>
		</tr>';			
        
	echo '<tr><td class="botonera" colspan="2">';

	echo $html->link(
		'Volver',
		'/pago_activo/consulta_directorio/v',
		array(
                    'class'=>'button'
		)
	);

	echo '&nbsp;&nbsp;&nbsp;';

	echo $html->link(
		'Aceptar',
		'javascript:',
		array(
                    'class'=>'button envio_formulario',
                    'id'=>'aceptar',
                    'div' => false
		)
	);

	echo '</td></tr></table>';		
        
	echo $form->end();
	?>
</div>


<script type="text/javascript">
var ajaxPendiente = false;
var loadpage = false;

$(function(){		
	
        $('#DirectorioTipoCliente').val('');
        
        function blanqueoCampos(){
            $('#DirectorioTipoCedulaRif').empty();            
            $('#DirectorioNroCedulaRif').val('');
            $('#DirectorioBancoReceptor').empty();
             $('#DirectorioBancoReceptor').append(
                $('<option>',{'value': '','text': 'Seleccione'})              
            );  
        }
        
        function acomoda_tipo_identificacion(){
                
            var tipoCliente = $('#DirectorioTipoCliente').val();            	
            
            switch(tipoCliente){		
                case '0': 
                    
                    <?php foreach ($bancos as $key => $valor) { ?>
                        $('#DirectorioBancoReceptor').append(
                            $('<option>',{
                                'value': '<?php echo $key ?>',
                                'text': '<?php echo $valor ?>'
                            })
                        );
                    <?php                        
                    } 
                    ?>      
                                        
                    $('#DirectorioTipoCedulaRif').append(
                        $('<option>',{'value': 'V','text': 'V'}),
                        $('<option>',{'value': 'E','text': 'E'}),
                        $('<option>',{'value': 'P','text': 'P'})
                    );                                                                         
                
                    break;
                case '1':
                    
                    <?php foreach ($bancosJ as $key => $valor) { ?>
                        $('#DirectorioBancoReceptor').append(
                            $('<option>',{
                                'value': '<?php echo $key ?>',
                                'text': '<?php echo $valor ?>'
                            })
                        );
                    <?php                        
                    } 
                    ?>  
                                            
                    $('#DirectorioTipoCedulaRif').append(
                        $('<option>',{'value': 'V','text': 'V'}),
                        $('<option>',{'value': 'J','text': 'J'}),
                        $('<option>',{'value': 'G','text': 'G'})
                    );                                         
            
                    break;	

            }
            
        }

        $('#DirectorioTipoCliente').change(function(){
            blanqueoCampos();
            acomoda_tipo_identificacion();          
        });
        
	// para la validacion
	$('#DirectorioCrearForm').validate({		
		rules:{                        
			'data[Directorio][telefono]':{
				required:true, 
                                digits:true
			},
			'data[Directorio][banco_receptor]':{
				required:true
			},
			'data[Directorio][nombreBeneficiario]':{
				required:true,alphaNumericSpace:true
			},
			'data[Directorio][tipoCliente]':{
				required:true
			},
			'data[Directorio][nroCedulaRif]':{
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
			}
		},
		messages:{                        
                        'data[Directorio][telefono]':{
				required:<?php echo _m('seleccion.requerida',array('el número de celular'),true) ?>,
				digits:<?php echo _m('formato.invalido','el campo solicitado', true)?>				
			},
			'data[Directorio][banco_receptor]':{
				required:<?php echo _m('seleccion.requerida',array('el banco'),true) ?>
			},
			'data[Directorio][nombreBeneficiario]':{
				required:<?php echo _m('campo.requerido',array('el nombre del beneficiario'),true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido',array('el nombre del beneficiario'),true) ?>
			},
                        'data[Directorio][tipoCliente]':{
				required:<?php echo _m('seleccion.requerida',array('el tipo de cliente'),true) ?>				
			},        
			'data[Directorio][nroCedulaRif]':{
				required:<?php echo _m('campo.requerido',array('la cédula o rif del beneficiario'),true) ?>,
				digits:<?php echo _m('formato.invalido',array('la cédula o rif del beneficiario'),true) ?>,
				alphanumeric:<?php echo _m('alfanumerico.invalido',array('el pasaporte del beneficiario'),true) ?>
			}
		}		
	});	
	
	loadpage = true;
	
 });
	
 
</script>