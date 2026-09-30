<?php
/**
 * Formulario de registro, donde se solicita, entre otros, nombre de usuario y contraseña
**/
?>
<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
<?php if($this->Session->check('login.juridico')){ ?>
	<div class="paso_nro">1</div>
	<div class="paso_desc">Autenticar Empresa</div>
	<div class="paso_nro">2</div>
        <div class="paso_desc">Aceptaci&oacute;n de Contrato</div>	
	<div class="paso_nro_actual">3</div>
	<div class="paso_desc_actual">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Tarjeta de Coordenadas</div>
<?php }else{ ?>
        <div class="paso_nro">1</div>
        <div class="paso_desc">Autenticaci&oacute;n del cliente</div>
	<div class="paso_nro">2</div>
        <div class="paso_desc">Aceptaci&oacute;n de Contrato</div>	
	<div class="paso_nro_actual">3</div>
	<div class="paso_desc_actual">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Código de Autorización</div>
        <div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">7</div>
	<div class="paso_desc">Configuraci&oacute;n de L&iacute;mites</div>
<?php } ?>	
</div>

<div class="espacio_vertical_20"></div>

<p>El pr&oacute;ximo paso es crear el usuario que utilizar&aacute; para el ingreso de Internet Banking</p>

<br/>

<div class="row">
	<div class="contenedor_formulario_inicio" style="width: 450px; float: left;">
		
		<?php
		
		echo $form->create('Usuario',
			array(
				'url'=> array(
					'controller'=>'usuarios',
					'action'=>'registro_usuario'
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
		
		echo $html->tag(
			'table',
			null,
			array(
				'class'=>'formulario',
				'cellpadding'=>'0',
				'cellspacing'=>'0',
				'border'=>'0',				
                                'style'=>'table-layout:fixed;'
			)
		);                                
				
		echo $form->input(
			'nomUsuario',
			array(
				'label'=>'<span class="requerido">* </span>Usuario:',
				'type'=>'text',
				'maxlength'=>'12',
				'class'=>'input_tip_usuario alfanumerico',
				'between'=>'</td><td class="campo"><div style="position:relative">',
				'after'=>'</div></td></tr>',	
				
			)
		);
	
		echo $this->element('tip_usuario');

		if ($form->isFieldError('nomUsuario')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('nomUsuario',
				array(
					'nombre_usuario'=>_m('usuario.invalido',array('el usuario')),
					'nombre_usuario2'=>_m('usuario.no.permitido',array('el usuario')),
					'login_unico'=>_m('usuario.no.disponible',array('el usuario')),
					'requerido'=>_m('campo.requerido',array('el usuario')),
					'minLength'=>_m('longitud.invalida',array('el usuario','8','12')),
					'maxLength'=>_m('longitud.invalida',array('el usuario','8','12')),
					'usuario.no.disponible'=>_m('usuario.no.disponible')
			)).'</td></tr>';
		}
		
		// PARA JURIDICO
		if($this->Session->check('login.juridico')){
			
			if( isset($legacy) ){
				
				if(isset($sin_identificacion) && $sin_identificacion){
					echo $form->input(
						'tipoCedulaRif',
						array(
							'after'=>false,
							'label'=>'<span class="requerido">* </span>C&eacute;dula de Identidad:',
							'type'=>'select',
							'class'=>'tipoCedulaRif',
							'options'=>array('V'=>'V','E'=>'E','P'=>'P')
						)
					);

					echo $form->input(
						'nroCedulaRif',
						array(
							'before'=>false,
							'between'=>false,
							'label'=>false,
							'type'=>'text',
							'class'=>'nroCedulaRif',
							'size'=>'27',
							'maxlength'=>9									
						)
					);

					if ($form->isFieldError('tipoCedulaRif')){
						echo '<tr><td>&nbsp;</td><td>'.$form->error('tipoCedulaRif',
							array(
								'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de documento de identificación')),
								'requerido'=>_m('campo.requerido',array('el tipo de documento de identificación'))
						)).'</td></tr>';
					} else if ($form->isFieldError('nroCedulaRif')){
						echo '<tr><td>&nbsp;</td><td>'.$form->error('nroCedulaRif',
							array(
								'requerido'=>_m('campo.requerido',array('documento de identidad ')),
								'longitud'=>_m('valor.invalido',array('documento de identidad '))
							)
						).'</td></tr>';
					}
				}
				else{
					echo '<tr>
						<td class="etiqueta">
							<span class="estiloTexto">Cédula de Identidad: </span>
						</td>
						<td class="campo">' . 
						$html->tag(
							'input',
							null,
							array(
								'type'=>'text',
								'disabled'=>'disabled',
								'value'=>$this->data['Usuario']['tipoCedulaRif'] . '-' . $this->data['Usuario']['nroCedulaRif']
							)
						) . 
						'</td>
					</tr>';
				}
				
				echo '<tr>
					<td class="etiqueta">
						<span class="estiloTexto">Razón Social: </span>
					</td>
					<td class="campo">' . $this->data['Usuario']['razon'] . '</td>
				</tr>';
			} else {
				echo '<tr>
					<td class="etiqueta">
						<span class="estiloTexto">Cédula de Identidad: </span>
					</td>
					<td class="campo">' . 
					$html->tag(
						'input',
						null,
						array(
							'type'=>'text',
							'disabled'=>'disabled',
							'value'=>$this->data['Usuario']['tipoCedulaRif'] . '-' . $this->data['Usuario']['nroCedulaRif']
						)
					) . 
					'</td>
				</tr>';
				
				echo '<tr>
					<td class="etiqueta">
						<span class="estiloTexto">Razón Social: </span>
					</td>
					<td class="campo">' . $this->data['DatosBasicos']['INTLNADB'] . '</td>
				</tr>';
			}
				
			echo $form->input(
				'nombre',
				array(
					'label'=>'<span class="requerido">* </span>Nombre y Apellido:',
					'type'=>'text',
					'class'=>'letrasEspacio',				
					'maxlength'=>'50'
				)
			);

			if ($form->isFieldError('nombre')){
				echo '<tr><td>&nbsp;</td><td>'.$form->error('nombre',
					array(
						'requerido'=>_m('campo.requerido',array('el nombre y apellido')),
						'minLength'=>_m('longitud.invalida',array('nombre y apellido','3','50')),
						'maxLength'=>_m('longitud.invalida',array('nombre y apellido','3','50'))
				)).'</td></tr>';
			}
				
			echo $form->input(
				'cargo',
				array(
					'label'=>'<span class="requerido">* </span>Cargo:',
					'type'=>'text',
					'class'=>'letrasEspacio',
					'maxlength'=>'30'
				)
			);

			if ($form->isFieldError('cargo')){
				echo '<tr><td>&nbsp;</td><td>'.$form->error('cargo',
					array(
						'requerido'=>_m('campo.requerido',array('el cargo')),
						'minLength'=>_m('longitud.invalida',array('el cargo','4','30')),
						'maxLength'=>_m('longitud.invalida',array('el cargo','4','30'))
				)).'</td></tr>';
			}
		}
		
		echo $form->input(
			'emailPri',
			array(
				'label'=>'<span class="requerido">* </span>E-mail Principal:',
				'type'=>'text',
                                'maxlength'=> 50,
                                'readonly'=>'readonly'
			)
		);

		if ($form->isFieldError('emailPri')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('emailPri',
				array(
					'correo'=>_m('actualizar.agencia'),
					'requerido'=>_m('actualizar.agencia')
				)
			).'</td></tr>';
		}	
		
		echo $form->input(
			'emailSec',
			array(
				'label'=>'<span class="estiloTexto">E-mail Secundario:',
				'type'=>'text',
                                'maxlength'=> 50 
			)
		);

		if ($form->isFieldError('emailSec')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('emailSec',
				array(
					'correo'=>_m('correo.invalido',array('el email secundario'))
				)
			).'</td></tr>';
		}
		
		echo $form->input(
		'codigoCelular',
			array(
				'after'=>false,
				'label'=>'<span class="requerido" width="100px">* </span>N&uacute;mero Celular:',
				'type'=>'select',
				'class'=>'codigoCelular',
                                'readonly'=>'readonly',
				'options'=>array(substr($estado['DatosBasicos']['NROCELULAR'],0,4)=>substr($estado['DatosBasicos']['NROCELULAR'],0,4))
			)
		);
		
		echo $form->input(
			'nroCelular',
			array(
				'before'=>false,
				'between'=>false,
				'label'=>false,
				'type'=>'text',
                                'readonly'=>'readonly',
				'class'=>'nroCelular numerico',
				'maxlength'=>7
			)
		);	
		
		if ($this->Form->isFieldError('codigoCelular')){
			echo $form->error('codigoCelular',
				array(
					'requerido' => _m('actualizar.agencia'),
					'valoresPermitidos' => _m('seleccion.requerida',array('el código del celular'))
				)
			);
		}

		if ($form->isFieldError('nroCelular')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('nroCelular',
				array(
					'valor'=>_m('actualizar.agencia'),
					'requerido'=>_m('actualizar.agencia')
				)
			).'</td></tr>';
		}
		
		echo $form->input(
			'clave',
			array(
				'label'=>'<span class="requerido">* </span>Clave:',
				'type'=>'password',
				'class'=>'input_tip_contrasena',
				'maxlength'=>12,
				'between'=>'</td><td class="campo"><div style="position:relative">',
				'after'=>'</div></td></tr>',				
			)
		);
	
		echo $this->element('tip_contrasena');

		if ($form->isFieldError('clave')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('clave',
				array(
					'requerido'=>_m('campo.requerido',array('la clave')),
					'contrasena'=>_m('contrasena.invalida'),
					'contrasena2'=>_m('clave.no.permitida'),
					'minLength'=>_m('longitud.invalida',array('la clave','8','12')),
					'maxLength'=>_m('longitud.invalida',array('la clave','8','12'))
				)
			).'</td></tr>';
		}
		
		echo $form->input(
			'clave_confirmacion',
			array(
				'label'=>'<span class="requerido">* </span>Confirmar clave:',
				'type'=>'password',
				'class'=>'',
				'maxlength'=>12
			)
		);

		if ($form->isFieldError('clave_confirmacion')){
				echo '<tr><td>&nbsp;</td><td>'.$form->error('clave_confirmacion',
					array(
						'confirmacionIgualQue'=>_m('no.coincide',array('la confirmación de la clave')),
						'requerido'=>_m('campo.requerido',array('la confirmación de la clave'))
					)
				).'</td></tr>';
		}

		echo $form->input(
			'tiempoCaduca',
			array(
				'after'=>false,
				'label'=>'<span class="requerido">* </span>Tiempo de Caducidad:',
				'type'=>'select',
				'class'=>'tiempoCaduca',
				'options'=>array('30'=>'30 días','60'=>'60 días','90'=>'90 días','180'=>'180 días'),
				'empty'=>'Seleccione'
			)
		);
		
		if ($form->isFieldError('tiempoCaduca')){
				echo '<tr><td>&nbsp;</td><td>'.$form->error('tiempoCaduca',
					array(
						'requerido'=>_m('seleccion.requerida',array('el tiempo de caducidad')),
						'valoresPermitidos'=>_m('seleccion.invalida',array('el tiempo de caducidad')),
					)
				).'</td></tr>';
		}
		
		
		// PARA JURIDICO
		if($this->Session->check('login.juridico')){
			if(isset($legacy)){
				echo '<tr>
					<td class="etiqueta">
						<span class="estiloTexto">Perfil: </span>
					</td>
					<td class="campo">'.$perfil.'</td>
				</tr>';
			} else {
				echo $form->input(
					'perfil',
					array(
						'after'=>false,
						'label'=>'<span class="requerido">* </span>Perfil:',
						'type'=>'select',
						'class'=>'tiempoCaduca input_tip_perfil',
						'options'=>array('1'=>'Master','2'=>'Master/Único'),
						'empty'=>'Seleccione',
						'between'=>'</td><td class="campo"><div style="position:relative">',
						'after'=>'</div></td></tr>',							
					)
				);

				if ($form->isFieldError('perfil')){
						echo '<tr><td>&nbsp;</td><td>'.$form->error('perfil',
							array(
								'requerido'=>_m('seleccion.requerida',array('perfil')),
								'valoresPermitidos'=>_m('seleccion.invalida',array('perfil')),
							)
						).'</td></tr>';
				}
				
				echo $this->element('tip_perfil');
			}
			?>
		<tr>
                        <td class="etiqueta">
                                <label for="UsuarioCheck"><span class="estiloTexto">Recibe Comunicaciones:</span></label></td><td class="campo">
				<input type="hidden"	name="data[Usuario][recibe]"   id="UsuarioRecibe" value="0">
				<input type="checkbox"  name="data[Usuario][recibe]" id="UsuarioRecibe" value="1">
			</td>		
		</tr>	
		
		<?php
		}
		
		echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';				
		
		echo '<tr><td class="submit" colspan="2">';
                
                echo '<p align="left"><b>NOTA:</b><br/>
			Estimado Cliente, recuerde que su email y número telefónico deben estar actualizados para recibir su clave de autorización y notificaciones.</p>';
			
		echo $html->link(
			'Volver',
			'/',
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
				'div' => false
			)
		);
			
		
		echo '</td></tr></table>';
		
		echo $form->end();
		
		?>
	</div>
		<div class="contenedor_formulario_inicio" style="float:left; width:360px; padding-left:20px; padding-top: 0px">
		<?php
			$margin = ($this->Session->check('login.juridico')) ? 'margin-bottom: 190px;' : 'margin-bottom: 120px;';
			echo $html->tag(
				'table',
				null,
				array(
					'cellpadding'=>'0',
					'cellspacing'=>'0',
					'border'=>'0',
					'width'=>'100%',
					'style'=>$margin,
				)
			);
			echo '<tr><td>';
			echo $form->button('Verificar Usuario', array('class'=>'button', 'id'=>'verificar','style'=>'white-space:nowrap'));
			echo '</td>';
			if($this->Session->check('login.juridico')){
				echo '<td><p class="exito" style="display:none; margin:0px;">&iexcl;Usuario disponible!</p></td>';
			}
			echo '<td><div id="verificacion_usuario" style="padding:5px; margin:0px;"></div></td>';
			
			echo '</tr></table>';
			if($this->Session->check('login.juridico')){
				echo '<div class="espacio_vertical_20"></div>';
				echo '<div class="espacio_vertical_20"></div>';
				echo '<div class="espacio_vertical_20"></div>';
				echo '<div class="espacio_vertical_20"></div>';
			}			
                                                
		?>
	</div>
</div>
<script>
$(function(){	
	$('input.uso_teclado').keypress(function(){
		return false;
	});
	
	
	// Muestra el mensaje de disponibilidad del usuario
	$('#verificar').click(function(){
		$('#verificacion_usuario').html('');
		
		if($('#UsuarioNomUsuario').valid()){
			$('#verificacion_usuario').html('<?php echo 'Cargando... ' . $html->image('/img/ajax-loader_mini.gif'); ?>');
			
			$.ajax({
				url:'<?php echo Router::url('/usuarios/verificar_usuario/') ?>'+$('#UsuarioNomUsuario').val(),
				cache:false,
				success: function(data) {
					$('#verificacion_usuario').html(data);
				}
			});
		}
	});
	
	
	// para la validacion
	$('#UsuarioRegistroUsuarioForm').validate({
		rules:{
			'data[Usuario][nomUsuario]':{
				required:true,
				minlength:8,
				maxlength:12,
				usuario:true
			},
			<?php if($this->Session->check('login.juridico')){ ?>	
			'data[Usuario][cargo]':{
				required:true,				
				minlength:3,
				maxlength:30
			},
			'data[Usuario][nroCedulaRif]':{
				required:true,				
				digits:{ 
					depends: function(e){
						return ($('#UsuarioTipoCedulaRif').val()=='V')||
							   ($('#UsuarioTipoCedulaRif').val()=='E');
					}
				},
				alphanumeric:{
					depends: function(e){
						return $('#UsuarioTipoCedulaRif').val()=='P';
					}				
				}
			},
			'data[Usuario][nombre]':{
				required:true,
				minlength:3,
				maxlength:50				
			},
			<?php } ?>			
			'data[Usuario][nroCelular]':{
				required:true, celular:true, minlength:7
			},
			'data[Usuario][emailPri]':{
				required:true, email:true
			},
			'data[Usuario][emailSec]':{
				email:true
			},
			'data[Usuario][clave]':{
				required:true,
				minlength:8,
				maxlength:12,
				contrasena:true
			},
			'data[Usuario][clave_confirmacion]':{
				required:true,
				equalTo: '#UsuarioClave'
			},
			'data[Usuario][tiempoCaduca]':{
				required:true
			}
		<?php if($this->Session->check('login.juridico')){ ?>		
			,'data[Usuario][perfil]':{
				required:true
			}
		<?php } ?>
		},
		messages:{
			'data[Usuario][nomUsuario]':{
				required:<?php echo _m('campo.requerido',array('el usuario'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('el usuario','8','12'),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('el usuario','8','12'),true) ?>,
				usuario:<?php echo _m('usuario.invalido',null,true) ?>
			},
			'data[Usuario][nroCelular]':{
				required:<?php echo _m('actualizar.agencia',null,true) ?>,
				celular:<?php echo _m('actualizar.agencia',null,true) ?>
			},
			'data[Usuario][emailPri]':{
				required:<?php echo _m('actualizar.agencia',null,true) ?>,
				email:<?php echo _m('actualizar.agencia',null,true) ?>
			},
			'data[Usuario][emailSec]':{
				email:<?php echo _m('correo.invalido','el email secundario',true) ?>
			},
			'data[Usuario][clave]':{
				required:<?php echo _m('campo.requerido',array('la clave'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('la clave','8','12'),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('la clave','8','12'),true) ?>,
				contrasena:<?php echo _m('contrasena.invalida',null,true) ?>
			},
			'data[Usuario][clave_confirmacion]':{
				required:<?php echo _m('campo.requerido',array('la confirmación de la clave'),true) ?>,
				equalTo:<?php echo _m('no.coincide',array('la clave'),true) ?>
			},
			'data[Usuario][tiempoCaduca]':{
				required:<?php echo _m('seleccion.requerida',array('el tiempo de caducidad'),true) ?>
			}
		<?php if($this->Session->check('login.juridico')){ ?>
			,
			'data[Usuario][cargo]':{
				required:<?php echo _m('campo.requerido',array('el cargo'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('el cargo','3','30'),true) ?>
			},
			'data[Usuario][nroCedulaRif]':{
				required:<?php echo _m('campo.requerido',array('la c&eacute;dula de identidad'),true) ?>
			},
			'data[Usuario][nombre]':{
				required:<?php echo _m('campo.requerido',array('el nombre'),true) ?>
			},
			'data[Usuario][perfil]':{
				required:<?php echo _m('seleccion.requerida',array('el perfil'),true) ?>
			}
		<?php } ?>	
		}
	});	
});
</script>
