<?php
/**
 * Formulario de inicio de sesión donde se solicita la contraseña y se muestra
 * el sello de seguridad
**/
?>

<div class="shadows-ie-135 shadows-ie-135" id="box-login2">
<div class="t_shadow"></div>
<div class="r_shadow"></div>
<div class="b_shadow"></div>
<div class="l_shadow"></div>
<div class="lt_shadow"></div>
<div class="rt_shadow"></div>
<div class="rb_shadow"></div>
<div class="lb_shadow"></div>
	<div class="row">
		<div class="span8">
			<div id="header-login">
				<h1>Activo en L&iacute;nea <span><?php echo isset($tipo_identificacion) ? 'Empresas' : 'Personas'; ?></span></h1>
			</div>
		</div>
		
		<div class="span10" align="center">
			<div>
				<h2 class="user">Usuarios Registrados</h2>
				<div class="line_title_orange"></div>
			</div>
			<div id="box-form">
				<?php echo $form->create('Usuario',array(
					'action' => 'contrasena',
					'inputDefaults'=>array(
						'div'=>false,
						'label'=>false,
						'error'=>false
					)
				)) ?>
                            
                                <?php 
                                if(
                                    ($datos['estatus_registro'] == 'I' && $datos['estatus_acceso'] == 'P') &&
                                    ($datos['perfil_ib_id'] == 2 && $datos['rol_juridico_id'] > 2)
                                ){ ?>
                                       <p>Ingrese la <strong>Clave Temporal</strong> proporcionada por el Master y luego presione Aceptar</p>
                                       <table cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td valign="top">
                                                <label class="etiqueta etiqueta_clave">Clave Temporal:</label>
                                            </td>
                                <?php } else { ?>
                                       <p>Ingrese la clave y luego presione Aceptar</p>
                                       <table cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td valign="top">
                                                <label class="etiqueta etiqueta_clave">Clave:</label>
                                            </td>
                                <?php } ?>
				
				<td valign="top">
				<?php echo $form->input(
					'contrasenaL',
					array(
						'type'=>'password',
						'class'=>'input_contrasena',
						'id'=>'contrasenaL',
						'maxlength'=>isset($datos['legacy']) ? 10 : 12
					)
				) ?>

				<?php

				if ($this->Form->isFieldError('contrasenaL')){

					if(isset($datos['legacy'])) {
						echo $form->error('contrasenaL',
							array(
								'requerido'=>_m('campo.requerido',array('la clave')),
								'minLength'=>_m('longitud.invalida',array('la clave','1','10')),
								'maxLength'=>_m('longitud.invalida',array('la clave','8','10'))
							)
						);
					} else {

						if(count($this->Form->isFieldError('contrasenaL')) < 8){

							$pass = _m('longitud.invalida',array('la clave','8','12'));
							echo $form->error('contrasenaL',$pass);

						}else{

							echo $form->error('contrasenaL',
							array(
								'requerido'=>_m('campo.requerido',array('la clave')),								
								'minLength'=>_m('longitud.invalida',array('la clave','8','12')),
								'maxLength'=>_m('longitud.invalida',array('la clave','8','12'))
							)
						);

						}
						
					}
				}
				?>

				</td>
				</tr>
				</table>
                                
				<div class="clear"></div>
				<div class="espacio_vertical_10"></div>
				<p>
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
				</p>
				<?php echo $form->end() ?>
                                <div class="espacio_vertical_10"></div>
                                <div class="espacio_vertical_10"></div>
                                <div id="alert-div">
                                    <div id="alert-img"><?php //echo $html->image('alerta.jpg') ?></div>
                                    <div id="alert-login">
                                    <br><br>
                                    <!--<p1>Cliente Activo,</p1><br><br><br><p2>Estamos haciendo mejoras en Activo en Línea</p2><p>¡Hemos sustituido el teclado virtual!</p><p3>Visítanos en www.bancoactivo.com y Redes Sociales</p3>
                                -->
                                </div>
                        </div>
			</div>
		</div>
	</div>                        
        <div class="espacio_vertical_10"></div>
</div>
	
<script type="text/javascript">
$(function(){

	<?php if(!isset($datos['legacy']) && !isset($datos['imagen_confirmada'])) { ?>
	// confirmacion de imagen
	
	
	$('input.input_contrasena').prop('disabled',true);
	
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
				$('input.input_contrasena').prop('disabled',false);
                                $('input.input_contrasena').focus();
				$('#mensaje_verificar').hide();
			},
			error: function(msg){
				alert(<?php echo _m('error.fatal',null,true) ?>);
			}
		});
	});
	
	$('.input_contrasena').click(function(){
            if($(this).hasClass("invalid")) {			
                alert(<?php echo _m('confirmar.antiphishing',null,false) ?>);
            }
	});
	
	<?php } ?>	
	
	$('#UsuarioContrasenaForm').validate({
		submitHandler: function(form){
			$('div.error-message').remove();
			
			if($('#contrasena').attr('disabled')) {
				$('input.input_contrasena').val('');
				alert(<?php echo _m('confirmar.antiphishing',null,true) ?>);
			}
			else{
				form.submit();
			}
		},
		rules:{
			'data[Usuario][contrasenaL]':{
				required:true,
				<?php if(isset($datos['legacy'])) { ?>
				minlength:1,
				<?php } else { ?>
				minlength:1,				
				<?php } ?>
			}
		},
		messages:{
			'data[Usuario][contrasenaL]':{
				required:<?php echo _m('campo.requerido',array('la clave'),true) ?>,
				<?php if(isset($datos['legacy'])) { ?>
				minlength:<?php echo _m('longitud.invalida',array('la clave','1','10'),true) ?>,
				<?php } else { ?>
				minlength:<?php echo _m('longitud.invalida',array('la clave','8','12'),true) ?>,				
				<?php } ?>
			}
		}
	});	
});
</script>
	
	