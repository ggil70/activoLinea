<?php
/**
 * Formulario de modificación y creación de usuarios
**/
?>
<div>
<h2 class="user">Configuraci&oacute;n Usuarios - <?php echo $operacion['operacion'] ?></h2>
<div class="line_title_orange"></div>
</div>

<?php 
for($i = 1; $i <= count($operacion['pasos']); $i++) { 
	$class = $i==1 ? '_actual' : '';
?>
<div class="paso_nro<?php echo $class ?>"><?php echo $i ?></div>
<div class="paso_desc<?php echo $class ?>"><?php echo $operacion['pasos'][$i] ?></div>
<?php } ?>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style=" width:90%">
<p>A trav&eacute;s de esta opci&oacute;n usted podr&aacute; realizar la <?php
if($operacion['operacion']=='Agregar'){
	echo 'inserción';
} else {
	echo 'actualizaci&oacute;n';
}
?> de los datos que se indican a continuaci&oacute;n</p>
<?php

echo $form->create('Usuario',
	array(
		'url'=> array(
			'controller'=>'empresa',
			'action'=>'usuario'
		),
		'inputDefaults'=>array(
			'div'=>false,
			'label'=>'etiqueta',
			'before'=>'<tr><td class="etiqueta">',
			'between'=>'</td><td class="campo" colspan="2">',
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
		'align'=>'center',
                'style'=>'table-layout:fixed;'
	)
);

// La primera fila define la tabla con layout fixed
echo '<tr><td style="width:200px"></td><td style="width:250px"></td></tr>';

if($operacion['operacion']=='Modificar'){
	echo '<tr>'.
		'<td class="etiqueta">Identificación del usuario (login) :</td>'.
		'<td class="campo">'. $this->data['Usuario']['nomUsuario'] .'</td>'.
	'</tr>';
}
else{
	echo $form->input(
		'nomUsuario',
		array(
			'label'=>'<span class="requerido">* </span>Identificación del usuario (login) :',
			'type'=>'text',
			'maxlength'=>12,
			'class'=>'alfanumerico',
			'between'=>'</td><td class="campo">',
			'after'=>'</td>',
		)
	);

	echo '<td style="vertical-align:top">'.$form->button('Verificar Usuario', array('class'=>'button', 'id'=>'verificar','style'=>'white-space:nowrap')).'</td>';

	echo '<td rowspan="8" style="vertical-align:top" width="100%"><div id="verificacion_usuario"></div></td>';

	echo '</tr>';
}

if ($form->isFieldError('nomUsuario')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('nomUsuario',
		array(
			'nombre_usuario'=>_m('usuario.invalido',array('la Identificación del usuario')),
			'nombre_usuario2'=>_m('usuario.no.autorizado',array('la Identificación del usuario')),
			'login_unico'=>_m('usuario.no.disponible',array('la Identificación del usuario')),
			'requerido'=>_m('campo.requerido',array('la Identificación del usuario')),
			'minLength'=>_m('longitud.invalida',array('la Identificación del usuario','8','12')),
			'maxLength'=>_m('longitud.invalida',array('la Identificación del usuario','8','12')),
			'usuario.no.disponible'=>_m('usuario.no.disponible')
		)
	).'</td></tr>';
}

echo $form->input(
	'tipoCedulaRif',
	array(
		'after'=>false,
		'label'=>'<span class="requerido">* </span>C&eacute;dula de Identidad:',
		'type'=>'select',
		'class'=>'tipoCedulaRif',
		'options'=>array('V'=>'V','E'=>'E','P'=>'P'),
		'disabled'=>$operacion['operacion']=='Modificar'
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
		'maxlength'=>9,
		'disabled'=>$operacion['operacion']=='Modificar'
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

echo $form->input(
	'nombre',
	array(
		'label'=>'<span class="requerido">* </span>Nombre y Apellido:',
		'type'=>'text',
		'class'=>'alfanumericoEspacio',
		'maxlength'=>50
	)
);

if ($form->isFieldError('nombre')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('nombre',
		array(
			'requerido'=>_m('campo.requerido',array('el nombre y apellido')),
			'minLength'=>_m('longitud.invalida',array('nombre y apellido','4','30')),
			'maxLength'=>_m('longitud.invalida',array('nombre y apellido','4','30'))
		)
	).'</td></tr>';
}

echo $form->input(
	'cargo',
	array(
		'label'=>'<span class="requerido">* </span>Cargo:',
		'type'=>'text',
		'class'=>'alfanumericoEspacio',
		'maxlength'=>30                       
	)
);

if ($form->isFieldError('cargo')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('cargo',
		array(
			'requerido'=>_m('campo.requerido',array('el cargo')),
			'minLength'=>_m('longitud.invalida',array('el cargo','3','30')),
			'maxLength'=>_m('longitud.invalida',array('el cargo','3','30'))
		)
	).'</td></tr>';
}       

echo $form->input(
	'emailPri',
	array(
		'label'=>'<span class="requerido">* </span>E-mail:',
		'type'=>'text',
                'maxLength'=>50
	)
);

if ($form->isFieldError('emailPri')){
	echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('emailPri',
		array(
			'correo'=>_m('correo.invalido',array('el email')),
			'requerido'=>_m('campo.requerido',array('el email'))
		)
	).'</td></tr>';
}

echo $form->input(
	'codigoCelular',
	array(
		'after'=>false,
		'label'=>'<span class="requerido">* </span>N&uacute;mero Celular:',
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

echo $form->input(
	'perfil',
	array(
		'label'=>'<span class="requerido">* </span>Perfil:',				
		'type'=>'select',
		'options'=>$perfiles,
		'empty'=>'Seleccione'
	)	
);


if ($form->isFieldError('perfil')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('perfil',
		array(
			'requerido'=>_m('seleccion.requerida',array('el perfil del usuario'))
		)
	).'</td></tr>';
}

echo '<tr><td colspan="4"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

echo '<tr><td colspan="2" class="botonera">';

	echo $html->link(
		'Volver',
		'/empresa/listado',
		array(
			'class'=>'button',
			'id'=>'volver'
		)
	);

	echo '&nbsp;&nbsp;&nbsp;';

	echo $html->link(
		'Aceptar',
		'#',
		array(
			'class'=>'button envio_formulario'
		)		
	);

echo '</td><td colspan="2"></td></tr></table>';

echo $form->end();

?>

</div>

<script type="text/javascript">
	
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

		return false;
	});		

	// para la validacion
	$('#UsuarioUsuarioForm').validate({
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
			'data[Usuario][nombre]':{
				required:true,
				minlength:3,
				maxlength:50				
			},
			'data[Usuario][cargo]':{
				required:true,
				minlength:3,
				maxlength:30
			},
			'data[Usuario][emailPri]':{
				required:true, 
				email:true,
				minlength:3,
				maxlength:50
			},
			'data[Usuario][nroCelular]':{
				required:true, celular:true, minlength:7		
			},
			'data[Usuario][perfil]':{
				required:true
			}
		},
		messages:{					
			'data[Usuario][nomUsuario]':{
				required:<?php echo _m('campo.requerido',array('el identificador del usuario'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('el identificador del usuario','8','12'),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('el identificador del usuario','8','12'),true) ?>,
				usuario:<?php echo _m('usuario.invalido',null,true) ?>
			},
			'data[Usuario][nroCedulaRif]':{
				required:<?php echo _m('campo.requerido',array('la c&eacute;dula de identidad'),true) ?>
			},
			'data[Usuario][nombre]':{
				required:<?php echo _m('campo.requerido',array('el nombre'),true) ?>
			},
			'data[Usuario][cargo]':{
				required:<?php echo _m('campo.requerido',array('el cargo'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('el cargo','3','30'),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('el cargo','3','30'),true) ?>
			},
			'data[Usuario][emailPri]':{
				required:<?php echo _m('campo.requerido',array('el email'),true) ?>,
				email:<?php echo _m('correo.invalido',array('el email'),true) ?>
			},
			'data[Usuario][nroCelular]':{
				required:<?php echo _m('campo.requerido',array('el número de celular del usuario'),true) ?>,
				celular:<?php echo _m('telefono.invalido',array('el número de celular del usuario'),true) ?>
			},
			'data[Usuario][perfil]':{
				required:<?php echo _m('seleccion.requerida',array('el perfil'),true) ?>
			}	
		}
	});	
});
</script>