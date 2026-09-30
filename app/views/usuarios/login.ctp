<?php
/**
 * Formulario de inicio de sesión, donde se solicita el nombre de usuario.
**/
?>
<div class="shadows-ie-135 shadows-ie-315" id="box-login">
<div class="t_shadow"></div>
<div class="r_shadow"></div>
<div class="b_shadow"></div>
<div class="l_shadow"></div>
<div class="lt_shadow"></div>
<div class="rt_shadow"></div>
<div class="rb_shadow"></div>
<div class="lb_shadow"></div>

 


<?php 

 $ssl = 'http://';

// Verificamos si la conexión es HTTPS.
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $ssl = 'https://';
}
  $url_naturales= 'http://10.3.0.155/QA/naturales/usuarios/login/N';
  $url_juridico ='http://10.3.0.155/QA/naturales/usuarios/login/J';
 
?> 
<!-- Modal/Pop-up de alerta -->
<div id="alertModal" class="modal-alert" style="display: none;">
    <div class="modal-content-alert"> 
        <div class="alert-header">
            <div class="alert-image"><?php echo $html->image('warning.png') ?></div>
            <div class="alert-title"><h3>Asegura tu navegación</h3></div>
        </div>
        <hr/>
        <p>Antes de ingresar tus datos, verifica siempre que la dirección <br/>en tu navegador sea: </p>
        <p id="currentUrl" style="font-weight: bold; word-break: break-all;"></p>
        <button id="acceptAlert" class="button">Aceptar</button>
    </div>
</div>

 

<?php 

 $ssl = 'http://';

// Verificamos si la conexión es HTTPS.
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $ssl = 'https://';
}
  $url = $ssl . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
 
?> 
<!-- Modal/Pop-up de alerta -->
<div id="alertModal" class="modal-alert" style="display: none;">
    <div class="modal-content-alert"> 
        <div class="alert-header">
            <div class="alert-image"><?php echo $html->image('warning.png') ?></div>
            <div class="alert-title"><h3>Asegura tu navegación</h3></div>
        </div>
        <hr/>
        <p>Antes de ingresar tus datos, verifica siempre que la dirección <br/>en tu navegador sea: </p>
        <p id="currentUrl" style="font-weight: bold; word-break: break-all;"></p>
        <button id="acceptAlert" class="button">Aceptar</button>
    </div>
</div>


 
	<div class="row">
		<div class="span8">
		<div id="header-login">
			<h1>Activo en L&iacute;nea <span><?php echo (isset($juridico)) ? 'Empresas' : 'Personas'; ?></span></h1>
		</div></div>
		<div class="span4">
			<div>
				<h2 class="user">Iniciar Sesi&oacute;n</h2>
				<div class="line_title_orange"></div>
			</div>
			<div id="box-form">
				<?php echo $form->create('Usuario',array(
					'action' => 'login',
					'inputDefaults'=>array(
						'div'=>false,
						'label'=>false,
						'error'=>false
					)
				)); ?>
					<fieldset>
						
						<?php if(isset($juridico)){ ?>
						
						<label>Nombre de Usuario</label>
						<div class="contenedor-login">
							<label class="etiqueta" style="display:inline-block; width:55px;">RIF</label>
							<?php
							echo $form->input(
								'tipoRif',
								array(
									'after'=>false,
									'label'=>false,
									'type'=>'select',
									'class'=>'tipoRif',
									'options'=>array('J'=>'J','G'=>'G','V'=>'V','E'=>'E')
								)
							);

							echo $form->input(
								'nroRif',
								array(
									'before'=>false,
									'between'=>false,
									'label'=>false,
									'type'=>'text',
									'class'=>'nroRif',
									'maxlength'=>9,
									'value'=>false,
									'size'=>'22'
								)
							);
							?>
						</div>
						
						<?php
						if ($this->Form->isFieldError('tipoRif')){
							echo $form->error('tipoRif',
								array(
									'requerido'	=> _m('campo.requerido',array('el tipo de documento')),
									'valoresPermitidos'	=> _m('seleccion.requerida',array('el tipo de documento'))
								)
							);
						}
						
						if ($this->Form->isFieldError('nroRif')){
							echo $form->error('nroRif',
								array(
									'requerido'	=> _m('campo.requerido',array('el número de identificación')),
									'longitud'	=> _m('longitud.invalida',array('el número de identificación','7','9'))
								)
							);
						}
						?>
						<div class="espacio_vertical_10"></div>
						
						<div class="contenedor-login">
							<label class="etiqueta" style="display:inline-block; width:55px;">Usuario</label>
							<?php
								echo $form->input('login', 
									array(
										'type'=>'text',
										'maxlength'=>'12',
										'value'=>false,
										'size'=>'29'
									)
								);
							?>
						</div>
						
						<?php } else { ?>
						
						<label>Nombre de Usuario</label>
						<div class="contenedor-login">
							<?php
								echo $form->input('login', 
									array(
										'type'=>'text',
										'class'=>'input_login',
										'maxlength'=>'12',
										'value'=>false
									)
								);
							?>
						</div>
						
						<?php } ?>
						
						<?php
						if ($this->Form->isFieldError('login')){
							echo $form->error('login',
								array(
									'usuario'	=> _m('usuario.invalido',array('el usuario')),
									'requerido'	=> _m('campo.requerido',array('el usuario')),
									'minLength'	=> _m('longitud.invalida',array('el usuario', (isset($juridico)) ? 2 : 8 ,'12')),
									'maxLength'	=> _m('longitud.invalida',array('el usuario', (isset($juridico)) ? 2 : 8,'12')),
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
	
					</fieldset>
					<div class="botonera_inicio">
				<input type="submit" value="Aceptar" class="button" style="display:inline-block;margin-right:20px">
				</div>
				<?php echo $form->end() ?>
			</div>
			<div class="espacio_vertical_10"></div>
			<div id="links">
			<ul>
			<li>
				<?php 
				$texto_enlace = 'Haz click aqui si olvidaste tu usuario, clave o preguntas y respuestas de desafio.';
				echo $html->link($texto_enlace,'/seguridad/olvido_tipo'); 
				?>
			</li>
			</ul>
			</div>
		</div>
		<div class="span5">
                    <div>
			<h2 class="user">¿No est&aacute;s registrado?</h2>
			<div class="line_title_orange"></div>
                    </div>
                    <div id="box-form">
				<?php
				
				echo $form->create('Usuario',array(
					'url'=> array(
						'controller'=>'usuarios',
						'action'=>'autenticar_cedula'
					),
                                        'inputDefaults'=>array(
                                                'div'=>false,
                                                'label'=>'etiqueta',
                                                'before'=>'<tr><td class="etiqueta">',
                                                'between'=>'</td><td class="campo">',
                                                'after'=>'</td></tr>',
                                                'error'=>false
                                        ),                                    
					'id'=>'AutenticarCedulaForm'
				));
				
				?>
				<fieldset>
					<label>Si est&aacute;s ingresando a Activo en L&iacute;nea por primera vez, o no posees usuario y contraseña</label><br>
					<div align="center">
					<input type="submit" value="Regístrese" class="button_registro">
					</div>
				</fieldset>
				<?php echo $form->end() ?>
			</div>
			<div class="espacio_vertical_10"></div>
			<div id="box-banner">
			<?php echo $activo->banners($banners) ?>
			</div>
			<div id="leyenda">
			                            
			</div>
                 <div id="alert-div">
                    <div id="alert-img"><?php //echo $html->image('alerta.jpg') ?></div>
                        <div id="alert-login">
                          <br><br>
                          <!--<p1>Cliente Activo,</p1><br><br><br><p2>Estamos haciendo mejoras en Activo en Línea</p2><p>¡Hemos sustituido el teclado virtual!</p><p3>Visítanos en www.bancoactivo.com y Redes Sociales</p3>
                          -->
                        </div>
                    </div>
		</div>
                <div class="espacio_vertical_10"></div>
                <div class="espacio_vertical_10"></div>
                <div align="center">Si necesitas ayuda cont&aacute;ctanos desde Venezuela a trav&eacute;s del <br> 0500-ACTIVAT (2264828) o en el Exterior al +582129583333</div>
                <div id="links_politicas" align="center">			
                            <?php 
				$texto_enlace_privacidad = 'Política de Privacidad';                                                         
				echo $html->link($texto_enlace_privacidad,'/files/PolíticasPrivacidadBancoActivo.pdf',array('target'=>'_blank')); 
			    ?>
                            &nbsp;&nbsp;&nbsp;&nbsp; 
                            <?php 
				$texto_enlace_terminos = 'Términos y Condiciones';
                                if(isset($juridico)){
                                    $contrato = '/files/CONTRATO_ACTIVO_EN_LINEA_EMPRESA.pdf';
                                }
                                else{
                                    $contrato = '/files/CONTRATO_ACTIVO_EN_LINEA.pdf';
                                }                              
				echo $html->link($texto_enlace_terminos,$contrato,array('target'=>'_blank')); 
			    ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <?php                                 
                                $link_tips = 'http://www.bancoactivo.com/?p=1318';                                
				$texto_enlace_tips = 'Tips de Seguridad';                                                         
				echo $html->link($texto_enlace_tips,$link_tips,array('target'=>'_blank')); 
			    ?>                       
		</div>
	</div>
</div>


<div class="notificacion">
	


</div>
	
	
<script>
$(function(){
	$('#UsuarioLoginForm').validate({
		onfocusout:false,
		rules:{
			'data[Usuario][nroRif]':{
				required:true,
				minlength:7
			},
			'data[Usuario][login]':{
				required:true,
				minlength:<?php echo (isset($juridico)) ? 1 : 5; ?>,
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
				minlength:<?php echo _m('longitud.invalida',array('el usuario',(isset($juridico)) ? 1 : 8 ,12),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('el usuario',(isset($juridico)) ? 1 : 8,12),true) ?>,
				usuario_legacy:<?php echo _m('usuario.invalido',null,true) ?>
			}
		}
	});
});
 
</script>


<script>
 
	 $(document).ready(function() {
    var currentUrl = window.location.href;
    var cadena = <?php echo json_encode($url_naturales); ?>;
    var cadena2 = <?php echo json_encode($url_juridico); ?>;
    // alert(currentUrl)
    // Comparar con ambas cadenas
    if (currentUrl === cadena || currentUrl === cadena2) {
        $('#currentUrl').text(currentUrl);
        $('#alertModal').show(); // Mostrar el modal
    }

    // Configurar eventos fuera del condicional
    $('#acceptAlert').click(function() {
        $('#alertModal').hide();
    });

    $(window).click(function(event) {
        if ($(event.target).is('#alertModal')) {
            $('#alertModal').hide();
        }
    });
});
 

</script>
 

<style>
/* Estilos para el modal de alerta */
.modal-alert {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.alert-header {
    display: flex;
    flex-direction: column; /* Cambiamos a columna para apilar verticalmente */
    align-items: center; /* Centramos horizontalmente */
    justify-content: center; /* Centramos verticalmente */
    margin-bottom: 15px;
}

.alert-image {
    width: 80px; /* Ajusta según el tamaño de tu imagen */
    margin-bottom: 10px; /* Espacio entre la imagen y el título */
    flex-shrink: 0; /* Evita que la imagen se reduzca */
}

.alert-title h3 {
  color: #d35400;  
    margin: 0; /* Elimina el margen por defecto del h3 */
    font-size: 24px;
    text-align: center; /* Aseguramos que el texto esté centrado */
    

}

.modal-content-alert {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 5px solid rgba(0,0,0,0.3);
    width: 80%;
    max-width: 500px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    text-align: center;
    font-size: 16px;
}

.modal-content-alert .button {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #3C44D1;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
}

.modal-content-alert .button:hover {
    background-color: #5f66df;
}
</style> 
</script> 
</script>


<script>
 
	 $(document).ready(function() {
    var currentUrl = window.location.href;
    var cadena = <?php echo json_encode($url_naturales); ?>;
    var cadena2 = <?php echo json_encode($url_juridico); ?>;

    // Comparar con ambas cadenas
    if (currentUrl === cadena || currentUrl === cadena2) {
        $('#currentUrl').text(currentUrl);
        $('#alertModal').show(); // Mostrar el modal
    }

    // Configurar eventos fuera del condicional
    $('#acceptAlert').click(function() {
        $('#alertModal').hide();
    });

    $(window).click(function(event) {
        if ($(event.target).is('#alertModal')) {
            $('#alertModal').hide();
        }
    });
});
 

</script>

<style>
/* Estilos para el modal de alerta */
.modal-alert {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.alert-header {
    display: flex;
    flex-direction: column; /* Cambiamos a columna para apilar verticalmente */
    align-items: center; /* Centramos horizontalmente */
    justify-content: center; /* Centramos verticalmente */
    margin-bottom: 15px;
}

.alert-image {
    width: 80px; /* Ajusta según el tamaño de tu imagen */
    margin-bottom: 10px; /* Espacio entre la imagen y el título */
    flex-shrink: 0; /* Evita que la imagen se reduzca */
}

.alert-title h3 {
  color: #d35400;  
    margin: 0; /* Elimina el margen por defecto del h3 */
    font-size: 24px;
    text-align: center; /* Aseguramos que el texto esté centrado */
    

}

.modal-content-alert {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 5px solid rgba(0,0,0,0.3);
    width: 80%;
    max-width: 500px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    text-align: center;
    font-size: 16px;
}

.modal-content-alert .button {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: #3C44D1;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
}

.modal-content-alert .button:hover {
    background-color: #5f66df;
}
</style> 
