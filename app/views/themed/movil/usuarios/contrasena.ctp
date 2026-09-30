<div class="subtitulo">
	<h2>Imagen Antiphishing</h2>
	<div></div>
</div>
		
<center>	
<?php
if( !empty($datos['sello_id']) && $datos['estatus_registro']!='I' ){
	echo '<img width="100" src="'.Router::url('/ws/buscar_imagen/'). desencriptar($datos['sello_id']) .'"/>'; 
} else {
?>
	<div class="sello_externo">
		<div class="sello_interno">
			<?php 
			if(
				( empty($datos['sello_1']) && empty($datos['sello_2']) && empty($datos['sello_3']) ) ||
				$datos['estatus_registro'] == 'I'
			){
				echo 'NO DEFINIDA';
			} else { ?>
			<?php echo desencriptar($datos['sello_1']) ?><br/>
			<?php echo desencriptar($datos['sello_2']) ?><br/>
			<?php echo desencriptar($datos['sello_3']) ?><br/>
			<?php } ?>
		</div>
	</div>
<?php
}
?>
</center>
<div class="espacio_vertical_10"></div>

<p>Estimado cliente, en caso de no reconocer la imagen desplegada evite ingresar la Clave y por favor comuníquese al Centro de Atención Activa a través del 0500-ACTIVAT(2284828)</p>
<br/>
<p id="mensaje_verificar">
	<input type="checkbox" value="0" id="activar" /> Haga click si esta es su imagen o sello personal.
	
</p>
<div class="espacio_vertical_20"></div>

<div class="subtitulo">
	<h2>Usuarios Registrados</h2>
	<div></div>
</div>

<?php echo $form->create('Usuario',array(
	'action' => 'contrasena',
	'inputDefaults'=>array(
		'div'=>'campo',
		'label'=>false,
		'error'=>false,
		'before'=>false,
		'between'=>false,
		'after'=>false,
	)
)) ?>

<p>Ingrese la clave y luego presione Aceptar</p>


<?php echo $form->input(
	'contrasena',
	array(
		'type'=>'password',
		'id'=>'contrasena',
		'maxlength'=>isset($datos['legacy']) ? 10 : 12,
		'class'=>'uso_teclado',
	)
) ?>

<?php
if ($this->Form->isFieldError('contrasena')){

	if(isset($datos['legacy'])) {
		echo $form->error('contrasena',
			array(
				'requerido'=>_m('campo.requerido',array('la clave')),
				'minLength'=>_m('longitud.invalida',array('la clave','8','10')),
				'maxLength'=>_m('longitud.invalida',array('la clave','8','10'))
			)
		);
	} else {
		echo $form->error('contrasena',
			array(
				'requerido'=>_m('campo.requerido',array('la clave')),
				'contrasena'=>_m('contrasena.invalida'),
				'minLength'=>_m('longitud.invalida',array('la clave','8','12')),
				'maxLength'=>_m('longitud.invalida',array('la clave','8','12'))
			)
		);

	}
}
?>

<div class="botonera">
<?php	
echo $html->link(
	'Volver',
	$datos['url_volver'],
	array(
		'class'=>'button'
	)
);

echo '&nbsp;&nbsp;';

echo $html->link(
	'Aceptar',
	'javascript:void(0)',
	array(
		'class'=>'button envio_formulario'
	)
);
?>
</div>
<?php echo $form->end() ?>


<script type="text/javascript">
$(function(){
	
	// confirmacion de imagen
	$('input.uso_teclado').prop('disabled',true);
	
	$('#activar').click(function(){
		$.ajax({
			type: "POST",
			url: '<?php echo Router::url("/usuarios/confirmar_imagen"); ?>',
			data: {
				usuario_id: <?php echo json_encode(encriptar($datos['id'])) ?>
				<?php if(!empty($datos['sello_id'])) { ?>
				,sello_id: <?php echo json_encode($datos['sello_id']) ?>
				<?php } ?>
			},
			cache:false,
			beforeSend: function() {
				//$('#wait').show();
			},
			success: function(msg){
				//$('#wait').hide();
				$('input.uso_teclado').prop('disabled',false);
				$('#mensaje_verificar').hide();
			},
			error: function(msg){
				alert(<?php echo _m('error.fatal',null,true) ?>);
			}
		});
	});
	
	
	$('#UsuarioContrasenaForm').validate({
		rules:{
			'data[Usuario][contrasena]':{
				required:true,
				minlength:8,
				contrasena:true
			}
		},
		messages:{
			'data[Usuario][contrasena]':{
				required:<?php echo _m('campo.requerido',array('la clave'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('la clave','8','12'),true) ?>,
				contrasena:<?php echo _m('contrasena.invalida',null,true) ?>
			}
		}
	});	
});
</script>