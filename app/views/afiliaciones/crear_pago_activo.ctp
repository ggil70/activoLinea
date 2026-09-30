<?php
  /**
   * Vista para la crear/modificar afiliaciones pago activo
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
			'url'=> '/afiliaciones/crear_pago_activo',
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
	echo '<tr><td style="width:225px"></td><td style="width:250px"></td></tr>';

	?>
	<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la afiliaci&oacute;n.</td></tr>
	<tr>
		<td colspan="2">
			<table class="tabla ancholibre" cellpadding="0" cellspacing="0" border="0" width="100%" >
				<thead>
					<tr>
						<td width="50%"><span class="requerido">* </span>Cuenta</td>
						<td><span class="requerido">* </span>Tarjeta</td>
					</tr>
				</thead>
				<tr>
                                <?php if(count($cuentas)){ ?>
					<td class="texto_centro ancholibre">
						<?php
                                                if(is_array($cuentas)){
                                                    $opciones = array(
                                                            'type'=>'select',
                                                            'options'=>$cuentas,                                                            
                                                            'id'=>'cuenta',
                                                            'label'=>false,
                                                            'div'=>false,
                                                            'before'=>false,
                                                            'between'=>false,
                                                            'after'=>false,
                                                            'style'=>'width:100%'
                                                    );

                                                    if(!empty($this->data['Afiliacion']['id'])){
                                                            $opciones['disabled']='disabled';
                                                            $opciones['value']= $this->data['Afiliacion']['numero_cuenta'];							
                                                    }

                                                    echo $form->input(
                                                            'numero_cuenta',
                                                            $opciones
                                                    );
                                                }else{
                                                    echo $this->Activo->enmascara('01710002'.$cuentas);
                                                    echo $form->hidden('numero_cuenta');
                                               }
                                               ?>
					</td>
					<td id="td_transaccion_wrapper" class="borde">
						<?php						
						$opciones = array(
							'type'=>'select',
							'options'=>$tarjetas,							
							'id'=>'tarjeta',
                                                        'selected'=>$this->data['Afiliacion']['numero_tarjeta'],
							'label'=>false,
							'div'=>false,
							'before'=>false,
							'between'=>false,
							'after'=>false,
							'style'=>'width:100%'
						);
									
						if(!empty($this->data['Afiliacion']['id'])){							
							$opciones['value']= $this->data['Afiliacion']['numero_tarjeta'];							
						}

						echo $form->input(
							'numero_tarjeta',
							$opciones
						);
						?>
					</td>
                                <?php }else { ?>    
                                        <td colspan="2">
                                            Estimado Cliente, usted tiene sus cuentas y tarjetas afiliadas a Saldo Activo
                                        </td>
                                <?php }?>        
				</tr>
			</table>
		</td>
	</tr>
	
	<?php
	echo '<tr><td colspan="2">'
		.'<label for="cuenta" class="error manual">'. _m('seleccion.requerida','el numero de cuenta') .'</label></br>'
		.'<label for="tarjeta" class="error manual">'. _m('seleccion.requerida','el numero de tarjeta') .'</label>';
		if ($form->isFieldError('cuenta')){
			echo $form->error('cuenta', _m('seleccion.requerida','el número de cuenta'));
		}
		if ($form->isFieldError('tarjetas')){
			echo $form->error('tarjeta', _m('seleccion.requerida','el número de tarjeta'));
		}
	echo '</td></tr>';																
			
	echo '<tr>
		<td colspan="2">
		<span class="requerido">* </span>
		<span class="requerido2">Campo obligatorio</span>
		</td>
		</tr>';		
		
	echo '<tr><td class="botonera" colspan="2">';

	echo $html->link(
		'Volver',
		'/afiliaciones/listado_pago_activo/v',
		array(
			'class'=>'button'
		)
	);

	echo '&nbsp;&nbsp;&nbsp;';
        if(count($cuentas)){
            echo $html->link(
                    'Aceptar',
                    'javascript:',
                    array(
                            'class'=>'button envio_formulario',
                            'id'=>'aceptar',
                            'div' => false
                    )
            );
        }
	echo '</td></tr></table>';	
	echo $form->end();
	?>
</div>


<script type="text/javascript">
var ajaxPendiente = false;
var servicioValido = true;
var existeProducto = true;
var loadpage = false;

$(function(){	
	
	// para la validacion
	$('#AfiliacionCrearForm').validate({
		submitHandler:function(form){
			$.each($('tr td div.error-message'),function(){
				if($(this).siblings().size()==0){
					$(this).parent().parent().remove();
				}
			});
			
			if(!ajaxPendiente){
				form.submit();
			}
		},
                rules:{
			'data[Afiliacion][numero_cuenta]':{
				required:true
			},
			'data[Afiliacion][numero_tarjeta]':{
				required:true
			}		
		},
		messages:{				
			'data[Afiliacion][numero_cuenta]':{
				required:<?php echo _m('campo.requerido', 'el campo solicitado',array('el banco'), true) ?>				
			},
			'data[Afiliacion][numero_tarjeta]':{
				required:<?php echo _m('campo.requerido','el nombre', true) ?>				
			}		
		}	
	});	
	
	existeProducto = false;
	 
	loadpage = true;
	
 });
</script>
