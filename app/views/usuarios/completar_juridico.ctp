<?php
/**
 * Formulario de registro para usuarios jurídicos
**/
?>
<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
<?php for($i = 0; $i < count($pasos); $i++) { 
	$class = ($i==$paso_actual) ? '_actual' : '';
?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $pasos[$i] ?></div>
<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<p>El pr&oacute;ximo paso es modificar su clave de acceso que utilizar&aacute; para el ingreso de Internet Banking</p>

<br/>

<div class="row">
	<div class="contenedor_formulario_inicio" style="width: 450px; float: left;">
		
		<?php
		
		echo $form->create('Usuario',
			array(
				'url'=> array(
					'controller'=>'usuarios',
					'action'=>'completar_juridico'
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
				'width'=>'100%',
			)
		);
		
		if(isset($this->data['legacy'])){
			echo $form->input(
				'nomUsuario',
				array(
					'label'=>'<span class="requerido">* </span>Usuario:',
					'type'=>'text',
					'maxlength'=>'12',
					'between'=>'</td><td class="campo"><div style="position:relative">',
					'after'=>'</div></td></tr>',	
					'class'=>'alfanumerico',
				)
			);
	
			echo $this->element('tip_usuario');

			if ($form->isFieldError('nomUsuario')){
				echo '<tr><td>&nbsp;</td><td>'.$form->error('nomUsuario',
					array(
						'nombre_usuario'=>_m('usuario.invalido',array('el usuario')),
						'nombre_usuario2'=>_m('usuario.no.autorizado',array('el usuario')),
						'login_unico'=>_m('usuario.no.disponible',array('el usuario')),
						'requerido'=>_m('campo.requerido',array('el usuario')),
						'minLength'=>_m('longitud.invalida',array('el usuario','8','12')),
						'maxLength'=>_m('longitud.invalida',array('el usuario','8','12')),
						'usuario.no.disponible'=>_m('usuario.no.disponible')
				)).'</td></tr>';
			}
			
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
			echo '<tr><td class="etiqueta"><span class="estiloTexto"/>Usuario:</td><td class="campo">'. $this->data['Usuario']['nomUsuario'] .'</td></tr>';
			
			echo '<tr><td class="etiqueta"><span class="estiloTexto"/>C&eacute;dula de Identidad:</td><td class="campo">'. $this->data['Usuario']['tipoCedulaRif'] .'-'. $this->data['Usuario']['nroCedulaRif'] .'</td></tr>';
		}
		
		echo '<tr><td class="etiqueta"><span class="estiloTexto"/>Nombre y Apellido:</td><td class="campo">'. $this->data['Usuario']['nombre'] .'</td></tr>';
		
		echo '<tr><td class="etiqueta"><span class="estiloTexto"/>Cargo:</td><td class="campo">'. $this->data['Usuario']['cargo'] .'</td></tr>';
		
		echo '<tr><td class="etiqueta"><span class="estiloTexto"/>E-mail Principal:</td><td class="campo">'. $this->data['Usuario']['emailPri'] .'</td></tr>';
		
		if(!isset($this->data['legacy'])){
			echo '<tr><td class="etiqueta"><span class="estiloTexto"/>N&uacute;mero Celular:</td><td class="campo">'. $this->data['Usuario']['codigoCelular'].'-'.$this->data['Usuario']['nroCelular'] .'</td></tr>';
		}
		else{
			echo $form->input(
				'codigoCelular',
				array(
					'after'=>false,
					'label'=>'<span class="requerido" width="100px">* </span>N&uacute;mero Celular:',
					'type'=>'select',
					'class'=>'codigoCelular',
					'options'=>array('0414'=>'0414','0424'=>'0424','0416'=>'0416','0426'=>'0426','0412'=>'0412','0422'=>'0422')
				)
			);

			echo $form->input(
				'nroCelular',
				array(
					'before'=>false,
					'between'=>false,
					'label'=>false,
					'type'=>'text',
					'class'=>'nroCelular numerico',
					'maxlength'=>7
				)
			);	

			if ($this->Form->isFieldError('codigoCelular')){
				echo $form->error('codigoCelular',
					array(
						'requerido'	=> _m('campo.requerido',array('el código del celular')),
						'valoresPermitidos'	=> _m('seleccion.requerida',array('el código del celular'))
					)
				);
			}

			if ($form->isFieldError('nroCelular')){
				echo '<tr><td>&nbsp;</td><td>'.$form->error('nroCelular',
					array(
						'valor'=>_m('formato.invalido',array('el número celular')),
						'requerido'=>_m('campo.requerido',array('el número celular'))
					)
				).'</td></tr>';
			}
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
		
		echo '<tr><td class="etiqueta"><span class="estiloTexto"/>Perfil:</td><td class="campo">'. $perfiles[$this->data['Usuario']['perfil']] .'</td></tr>';
		
		?>
		<tr>
			<td class="etiqueta"><label for="UsuarioCheck"><span class="estiloTexto">Recibe Comunicaciones:</span></label></td>
			<td class="campo">
				<input type="hidden" name="data[Usuario][recibe]"   id="UsuarioRecibe" value="0">
				<input type="checkbox" name="data[Usuario][recibe]" id="UsuarioRecibe" value="1">
			</td>
		</tr>
		<?php
		
		echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
		
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		
		echo '<tr><td class="submit" colspan="2">';
			
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
			if(isset($this->data['legacy'])){
				echo $html->tag(
					'table',
					null,
					array(
						'cellpadding'=>'0',
						'cellspacing'=>'0',
						'border'=>'0',
						'width'=>'100%',
						'style'=>'margin-bottom: 170px;',
					)
				);
				echo '<tr><td>';
					echo $form->button('Verificar Usuario', array('class'=>'button', 'id'=>'verificar','style'=>'white-space:nowrap'));
				echo '</td>';
				echo '<td><p class="exito" style="display:none; margin:0px;">&iexcl;Usuario disponible!</p></td>';
				echo '<td><div id="verificacion_usuario" style="padding:5px; margin:0px;"></div></td>';

				echo '</tr></table>';
			}
			else{
				echo '<div class="espacio_vertical_20"></div>';
				echo '<div class="espacio_vertical_20"></div>';
				echo '<div class="espacio_vertical_20"></div>';
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
	$('#UsuarioCompletarJuridicoForm').validate({
		rules:{
			'data[Usuario][nomUsuario]':{
				required:true,
				minlength:8,
				maxlength:12,
				usuario:true
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
			'data[Usuario][nroCelular]':{
				required:true, celular:true, minlength:7		
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
		},
		messages:{
			'data[Usuario][nomUsuario]':{
				required:<?php echo _m('campo.requerido',array('el usuario'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('el usuario','8','12'),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('el usuario','8','12'),true) ?>,
				usuario:<?php echo _m('usuario.invalido',null,true) ?>
			},
			'data[Usuario][nroCedulaRif]':{
				required:<?php echo _m('campo.requerido',array('la c&eacute;dula de identidad'),true) ?>
			},
			'data[Usuario][nroCelular]':{
				required:<?php echo _m('campo.requerido',array('el número de celular del usuario'),true) ?>,
				celular:<?php echo _m('telefono.invalido',array('el número de celular del usuario'),true) ?>
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
		}
	});	
});
</script>
