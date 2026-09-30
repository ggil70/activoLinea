<?php
  /**
   * Vista para crear/modificar afiliaciones Pago Activo
   */ 
  ?>

  <div>
    <h2 class="user">Afiliaciones</h2>
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

 

	echo $form->create('Afiliacion',
		array(
			'url'=> '/pago_activo/afiliacion_crear',
			'id'=>'AfiliacionCrearForm',
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
	echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$limite_pago_activo));
    echo $form->hidden('monto_maximo', array('id'=>'monto_maximo', 'value'=>$limite_pago_activo));        
    
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
    echo '<tr><td style="width:300px"></td><td style="width:250px"></td></tr>';

    ?>
    <?php if(isset($this->data['Afiliacion']['accion']) && $this->data['Afiliacion']['accion'] == 'Modificación'){ ?>
        <tr><td colspan="2">A continuaci&oacute;n proceda a modificar los datos de la afiliaci&oacute;n.</td></tr>
    <?php }else{ ?>
        <tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la afiliaci&oacute;n.</td></tr>
    <?php } ?>    
    <tr>
        <td colspan="2">
            <table class="tabla ancholibre" cellpadding="0" cellspacing="0" border="0" width="100%" >
                <thead>
                    <tr>
                        <td width="50%"><span class="requerido">* </span>Tipo de Afiliaci&oacute;n</td>
                        <td><span class="requerido">* </span>Tipo de Operaci&oacute;n</td>
                    </tr>
                </thead>
                <tr>
                    <td>
                        Pago Activo
                    </td>
                    <td id="td_transaccion_wrapper" class="borde">
                        Afiliación
                    </td>
                </tr>
            </table>
        </td>
    </tr>	        
    <?php 
    
    echo '<tr><td colspan="2">'		
    .'<label for="subtipo" class="error manual">'. _m('seleccion.requerida','el tipo de operación') .'</label>';		
    if ($form->isFieldError('subtipo')){
     echo $form->error('subtipo', _m('seleccion.requerida','el tipo de operación'));
 }
 echo '</td></tr>';
 
 $input_canal = array(
    'label'=>'<span class="requerido">* </span>Canal:',
    'type'=>'select',
    'options'=>$canales
);
 
 if(count($canales) > 1) $input_canal['empty'] = 'Seleccione';
 
 echo $form->input(
    'tipo_servicio',
    $input_canal
);
 

 if ($form->isFieldError('tipo_servicio')){
    echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tipo_servicio',
        array(
            'requerido'=>_m('seleccion.requerida','el canal')
        )			
    ).'</td></tr>';
}
?>             
<tr>
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
                'class'=>'codigoTelefono',
                'type'=>'select',
                'options'=>$prefijos_telefonicos,
                'value'=>isset($this->data['Afiliacion']['codigo_telefono']) ? $this->data['Afiliacion']['codigo_telefono'] : substr(desencriptar($usuario['celular']),0,4)
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
                'minlength'=>7,
                'maxlength'=>7,
                'value'=>isset($this->data['Afiliacion']['telefono']) ? $this->data['Afiliacion']['telefono'] : substr(desencriptar($usuario['celular']),4)
            )
        );
        ?>
    </td>
</tr>
<?php

if ($form->isFieldError('telefono')){

    echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('telefono',
        array(
            'requerido'=>_m('campo.requerido','el número de celular'),
            'numero'=>_m('formato.invalido','el número de celular'),
            'producto_nodisponible'=>_m('producto.nodisponible'),					
        )
    ).'</td></tr>';

    
}		        

echo $form->input(
  'email',
  array(
    'label'=>'<span class="requerido">* </span>Correo Electrónico:',
    'type'=>'text',
    'readonly'=>'readonly',
    'maxlength'=>50,
    'value'=>desencriptar($usuario['email_principal'])
)
);

if ($form->isFieldError('email')){
  echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('email',
    array(
        'requerido'=>_m('campo.requerido','el correo electrónico'),
        'correo'=>_m('correo.invalido','el correo electrónico')
    )
).'</td></tr>';
}

$usuario = $session->read('usuarios.datos');

if($usuario['perfil_ib_id']==1){
    
    $input_pagadora = array(
        'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;Cuenta Corriente:',
        'type'=>'select',
        'options'=>$cuentasCorrientes
    );

    if(count($cuentasCorrientes) > 0) $input_pagadora['empty'] = 'Seleccione';	
    if(count($cuentasCorrientes) == 1 && in_array('Estimado Cliente, no posee productos activos',$cuentasCorrientes)==true) $input_pagadora['empty'] = 'Estimado Cliente, no posee productos activos';

    echo $form->input(
        'numero_cuenta',
        $input_pagadora
    );


    if ($form->isFieldError('numero_cuenta')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('numero_cuenta',
            array(
                'requerido'=>_m('cuenta.pagadora', NULL, true)
            )			
        ).'</td></tr>';
    }

    $input_pagadora_ahorro = array(
        'label'=>'&nbsp;&nbsp;&nbsp;&nbsp;Cuenta de Ahorro:',
        'type'=>'select',
        'options'=>$cuentasAhorro
    );

    if(count($cuentasAhorro) > 0) $input_pagadora_ahorro['empty'] = 'Seleccione';
    if(count($cuentasAhorro) == 1 && in_array('Estimado Cliente, no posee productos activos',$cuentasAhorro)==true) $input_pagadora_ahorro['empty'] = 'Estimado Cliente, no posee productos activos';

    echo $form->input(
        'numero_cuenta_ahorro',
        $input_pagadora_ahorro
    );

    if ($form->isFieldError('numero_cuenta_ahorro')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('numero_cuenta_ahorro',
            array(
                'requerido'=>_m('cuenta.pagadora', NULL, true)
            )			
        ).'</td></tr>';
    }            	            
    
    echo $form->input(
        'recibePago',
        array(                    
            'type'=>'radio',
            'legend'=>false,
            'separator'=>'&nbsp;&nbsp;',
            'options'=> array(
                's' => 'Si',
                'n' => 'No'
            ),
            'class'=>'rButton',
            'before'=>'<tr><td class="etiqueta"><span class="requerido">* </span>¿Desea Recibir Pagos?:'
        )
    );

    echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'
    .'<label for="data[Afiliacion][recibePago]" class="error manual">'._m('seleccion.requerida',array('el campo solicitado')).'</label>';
    if ($form->isFieldError('recibePago')){
        echo $form->error('recibePago',
            array(                            
                'requerido'=>_m('seleccion.requerida',array('el campo solicitado'))
            )			
        );
    }
    echo '</td></tr>';        

    $input_receptora = array(             
        'type'=>'select',
        'options'=>$cuentas,             
        'before'=>false,
        'between'=>false,
        'after'=>false,
        'label'=>false,                                                                
        'style'=>'display:none'
    );

    if(count($cuentas) > 0) $input_receptora['empty'] = 'Seleccione';

    echo '<tr id="cuentaReceptora"> 
    <td class="etiqueta"><span class="requerido">* </span>Cuenta Receptora:</td>
    <td display:none class="campo">'.$form->input('numero_cuenta_receptora',$input_receptora).'</td>		
    </tr>';        	

    if ($form->isFieldError('numero_cuenta_receptora')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('numero_cuenta_receptora',
            array(
                'requerido'=>_m('seleccion.requerida',array('la cuenta receptora'))
            )			
        ).'</td></tr>';
    }                                    
    
}else{
    
    echo $form->hidden('monto_maximo', array('id'=>'monto_maximo', 'value'=>'9000,00'));
    
    $input_receptora = array(             
        'type'=>'select',
        'options'=>$cuentas,             
        'before'=>false,
        'between'=>false,
        'after'=>false,
        'label'=>false                
    );

    if(count($cuentas) > 0) $input_receptora['empty'] = 'Seleccione';

    echo '<tr id="cuentaReceptora"> 
    <td class="etiqueta"><span class="requerido">* </span>Cuenta Receptora:</td>
    <td display:none class="campo">'.$form->input('numero_cuenta_receptora',$input_receptora).'</td>		
    </tr>';        	

    if ($form->isFieldError('numero_cuenta_receptora')){
        echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('numero_cuenta_receptora',
            array(
                'requerido'=>_m('seleccion.requerida',array('la cuenta receptora'))
            )			
        ).'</td></tr>';
    }                   
    
}                        

echo '<tr>
<td colspan="2">
<span class="requerido">* </span>
<span class="requerido2">Campo obligatorio</span>
</td>
</tr>';		

echo '<tr id="nota">
<td colspan="2">                            
</br><span class="requerido2">Nota: El número de celular que registre es utilizado para recibir
notificaciones de Pago Activo (envío y recepción de pagos). El resto de las operaciones que
ejecute a través de Activo en Línea le será notificado a través del número telefónico registrado
en la Ficha del Cliente. Si desea modificar sus datos en la ficha del cliente deberá dirigirse a
una agencia bancaria.</span>
</td>
</tr>';		

echo '<tr><td class="botonera" colspan="2">';

echo $html->link(
  'Volver',
  '/pago_activo/listado_pago_activo/v',
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
       
        perfil = <?php echo $usuario['perfil_ib_id'] ?>;
        
        if($('#AfiliacionNumeroCuentaReceptora').val()==='' && perfil=='1'){
            $('#AfiliacionNumeroCuentaReceptora').hide();
            $('#cuentaReceptora').hide();
            <?php if(isset($this->data['Afiliacion']['accion']) && $this->data['Afiliacion']['accion'] == 'Modificación'){ ?>            
                $( "#AfiliacionRecibePagoN" ).prop( "checked", true );
            <?php } ?>    
        }else{
            $('#AfiliacionNumeroCuentaReceptora').show();
            $('#cuentaReceptora').show();
            if(perfil=='1'){
                $( "#AfiliacionRecibePagoS" ).prop( "checked", true );
            }            
        }        
        
        $( ".rButton" ).change(function() {            
           switch($(this).val()) {
               case 's':                     
               $('#cuentaReceptora').show();
               $('#AfiliacionNumeroCuentaReceptora').show();
               break;
               case 'n':                     
               $('#cuentaReceptora').hide();
               $('#AfiliacionNumeroCuentaReceptora').hide();
               $('#AfiliacionNumeroCuentaReceptora').val('');
               break;
           }
       });
        
	// para la validacion
	$('#AfiliacionCrearForm').validate({		
		rules:{	                        			
            'data[Afiliacion][tipo_servicio]':{
                required:true                                
            },
            'data[Afiliacion][telefono]':{
                required:true, 
                digits:true,
                
            },
            'data[Afiliacion][email]':{
                required:true,
                email:true
            },
            'data[Afiliacion][numero_cuenta]':{
                required: {
                    depends: function(element) {
                        return ($('#AfiliacionNumeroCuentaAhorro').val() == '');
                    }
                }										                               
            },
            'data[Afiliacion][numero_cuenta_ahorro]':{
                required: {
                    depends: function(element) {
                        return ($('#AfiliacionNumeroCuenta').val() == '');
                    }
                }										                               
            },
            'data[Afiliacion][numero_cuenta_receptora]':{
                required:true,
                digits:true						                               
            },
            'data[Afiliacion][recibePago]':{
                required:true                                
            }
        },
        messages:{                        
            'data[Afiliacion][tipo_servicio]':{
                required:<?php echo _m('campo.requerido', 'el campo solicitado', true) ?>
            },
            'data[Afiliacion][telefono]':{
                required:<?php echo _m('campo.requerido', 'el campo solicitado', true) ?>,
                digits:<?php echo _m('formato.invalido','el campo solicitado', true)?>,
                

            },
            'data[Afiliacion][email]':{
                required:<?php echo _m('campo.requerido','el email', true) ?>,
                email:<?php echo _m('correo.invalido','el email', true) ?>
            },
            'data[Afiliacion][numero_cuenta]':{
                required:<?php echo _m('cuenta.pagadora', NULL, true) ?>,
                digits:<?php echo _m('formato.invalido','el campo solicitado', true)?>				
            },
            'data[Afiliacion][numero_cuenta_ahorro]':{
                required:<?php echo _m('cuenta.pagadora', NULL, true) ?>,
                digits:<?php echo _m('formato.invalido','el campo solicitado', true)?>				
            },
            'data[Afiliacion][numero_cuenta_receptora]':{
                required:<?php echo _m('campo.requerido', 'el campo solicitado', true) ?>,
                digits:<?php echo _m('formato.invalido','el campo solicitado', true)?>								
            },			
            'data[Afiliacion][recibePago]':{
                required:<?php echo _m('campo.requerido','el campo solicitado',true) ?>				
            }
            
        }	
    });	
	
	loadpage = true;
	
});


</script>