<?php echo $form->create('Usuario',array(
	'action' => 'login',
	'inputDefaults'=>array(
		'div'=>'campo',
		'label'=>false,
		'error'=>false,
		'before'=>false,
		'between'=>false,
		'after'=>false,
	)
)); ?>
		
<p>Si Ud. ya está registrado a este servicio ingrese su Usuario y presione Aceptar</p>

<?php
	echo $form->input(
		'login', 
		array(
			'type'=>'text',
			'class'=>'input_login',
			'maxlength'=>'12',
			'value'=>false
		)
	);
?>

<?php
if ($this->Form->isFieldError('login')){
	echo $form->error(
		'login',
		array(
			'usuario'	=> _m('usuario.invalido',array('el usuario')),
			'requerido'	=> _m('campo.requerido',array('el usuario')),
			'minLength'	=> _m('longitud.invalida',array('el usuario','8','12')),
			'maxLength'	=> _m('longitud.invalida',array('el usuario','8','12')),
			'usuario.error'		=> _m('error.fatal'),
			'usuario.notfound'	=> _m('usuario.login.notfound'),
			'usuario.callcenter'=> _m('autenticacion.b.2.22'),
			'usuario.oficina.comercial'=> _m('registro.bloqueado'),
			'usuario.desconexion.incorrecta'=> _m('desconexion.incorrecta'),
			'registro.pendiente'=> _m('registro.pendiente')
		)
	);
}
?>						

<div class="botonera">
<input type="submit" value="Aceptar" class="button">
</div>

<?php echo $form->end() ?>
			
	
<script>
$(function(){
	$('#UsuarioLoginForm').validate({
		onfocusout:false,
		rules:{
			'data[Usuario][nroRif]':{
				required:true,
				minlength:7,
				maxlength:9
			},
			'data[Usuario][login]':{
				required:true,
				minlength:5,
				maxlength:12,
				usuario_legacy:true
			}
		},
		messages:{
			'data[Usuario][nroRif]':{
				required:<?php echo _m('campo.requerido',array('el número de identificación'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('el número de identificación',7,9),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('el número de identificación',7,9),true) ?>
			},
			'data[Usuario][login]':{
				required:<?php echo _m('campo.requerido',array('el usuario'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('el usuario',8,12),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('el usuario',8,12),true) ?>,
				usuario_legacy:<?php echo _m('usuario.invalido',null,true) ?>
			}
		}
	});
});
</script>