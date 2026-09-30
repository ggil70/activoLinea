<?php
/**
 * Contenedor de las páginas cuando el usuario ha iniciado sesión, Proceso de selección de empresas
 * auditoras
**/
$usuario = $this->Session->read('usuarios.datos');
$datos_basicos = $this->Session->read('usuarios.nibpf3010');
$ultima_conexion = $this->Session->read('usuarios.lastlogged');
$ms_adv = $this->Session->read('usuarios.timeadvsess');
$ms_fin = $this->Session->read('usuarios.timefinsess');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <?php echo $html->charset() ?>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
  <noscript><meta http-equiv="refresh" content="0;URL='<?php echo Router::url('/pages/nojs') ?>'"/></noscript>  
  <title>Activo en L&iacute;nea</title>
  <meta name="description" content=""/>
  <!-- Mobile viewport optimized: h5bp.com/viewport -->
  <meta name="viewport" content="width=1024">
  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->
	<?php echo $html->meta('icon') ?>
	<?php echo $html->css('bootstrap.css?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'css/bootstrap.css')) ?>
	<?php echo $html->css('style.css?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'css/style.css')) ?>
	<?php echo $html->css('style_print.css?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'css/style_print.css'), null, array('media'=>'print')) ?>
	<?php echo $html->css('jquery.simplemodal.css?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'css/jquery.simplemodal.css')) ?>
	<?php echo $html->script('libs/jquery-1.7.1.min.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery-1.7.1.min.js')) ?>
	<?php echo $html->script('libs/jquery-ui-1.8.22.custom.min.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery-ui-1.8.22.custom.min.js')) ?>
	<?php echo $html->script('plugins.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/plugins.js')) ?>
	<?php echo $html->script('script.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/script.js')) ?>	
	<?php if(!Configure::read('desarrollo')) echo $html->script('noback.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/noback.js')) ?>	
	<?php if(!Configure::read('desarrollo')) echo $html->script('norclick.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/norclick.js')) ?>	
	<?php echo $html->script('libs/jquery.validate/jquery.validate.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery.validate/jquery.validate.js')) ?>
	<?php echo $html->script('libs/jquery.validate/messages_es.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery.validate/messages_es.js'))?>
	<?php echo $html->script('libs/jquery.simplemodal.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery.simplemodal.js')) ?>
	<?php echo $html->script('libs/jquery.validate/additional-methods.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery.validate/additional-methods.js')) ?>
	<?php echo $this->Session->flash('alert') ?>
	<script type="text/javascript">
		var timeout_salir;
		var timeout_message;

		/*function crearTimeoutSesion(){
			timeout_message = setTimeout(
				function(){
					$.modal.close();

					setTimeout(
						function(){
							$('#adv_session').modal({
								onClose:function(){
									$.modal.close();

									$.ajax({
										url:<?php echo json_encode(Router::url('/usuarios/ping')) ?>,
										success:function(){
											if(timeout_salir) {
												clearTimeout(timeout_salir);
											}

											crearTimeoutSesion();
										}
									});	
								}
							})
						},
						1000
					);
				},
				<?php echo $ms_adv; ?>
			);
			
			timeout_salir = setTimeout(
				function(){
					ejecutando = true;
					location = '<?php echo Router::url('/usuarios/fin_sesion'); ?>';
				},
				<?php echo $ms_fin - Configure::read('tiempo.antes.cierre'); ?>
			);
		}*/
		
		<?php if( !Configure::read('desarrollo') ){ ?>
		$(function(){
			crearTimeoutSesion();
			
			excepcionTecla();
			
			$(window).bind('beforeunload', function() {
				if(ejecutando == false){
					return "Vas a abandonar la sesion de forma inadecuada.";
				}
			});
			
			$(window).unload(function(){
				if(ejecutando == false){
					$.ajax({
						async: false,
						type: "GET",
						url: '<?php echo Router::url('/usuarios/closeExplorer/');?>2',
						cache:false
					});
				}
			});
			
			$('.masivoProcess').click( function(){
				clearTimeout(timeout_salir);
				clearTimeout(timeout_message);
			});
		});
		<?php } ?>
	</script>
</head>
<body>
<div class="super-container">
  <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you support IE 6.
       chromium.org/developers/how-tos/chrome-frame-getting-started -->
  <!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
  <div id="top">
  <div id="logo">
	<?php echo $html->image('logo_new.png',array('url'=>'/consultas/integral','alt'=>'Logo Banco Activo')) ?></div>
  <div id="rif" class="offset3"><span>RIF: J-08006622-7</span></div>
  </div>
	<div class="container">
	
	<div class="espacio_vertical_10"></div>
	
	<div class="info_cliente">
	<span class="nombre_cliente"></span><?php 
	if(!empty($usuario['tipo_rif'])) {
		echo $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'] . '<br><span style="width:18px;display:inline-block"></span>';
	}
	
	echo desencriptar($usuario['nombre']);
	if(isset($usuario['nombre_perfil'])){ echo '<br><span style="width:18px;display:inline-block"></span>'; echo $usuario['nombre_perfil']; };

	?><br/>
	<span class="ultima_conexion"></span><?php echo !empty($ultima_conexion) ? "Ultima conexi&oacute;n: " . $ultima_conexion : ""; ?>
	</div>
	
	<a id="salir" class="button" href="<?php echo Router::url('/usuarios/logout') ?>">
	Salir
	<div class="ico-salir"></div>
	</a>

	<div id="header-login">
	<h1>Activo en L&iacute;nea <span><?php echo ($usuario['tipo_rif']) ? 'Empresas' : 'Personas'; ?></span></h1>
	</div>
	
	<div id="box-login2" class="shadows-ie-135 shadows-ie-315">
			<div class="t_shadow"></div>
			<div class="r_shadow"></div>
			<div class="b_shadow"></div>
			<div class="l_shadow"></div>
			<div class="lt_shadow"></div>
			<div class="rt_shadow"></div>
			<div class="rb_shadow"></div>
			<div class="lb_shadow"></div>
			<?php if(isset($error_fatal)) { ?>
			<div class="error_fatal"><?php echo $error_fatal ?></div>
			<?php } ?>
			<?php echo $content_for_layout ?><div style="clear:both"></div>
			</div>
		</div>
		<div id="footer">
			<span>&copy; Copyright <?php echo date('Y') ?> Banco Activo, C.A. Banco Universal. RIF: J-08006622-7. Todos los derechos reservados. La informaci&oacute;n mostrada en esta p&aacute;gina, es de car&aacute;cter Confidencial</span>
		</div>
	</div>
</div>
	
	
	<?php //echo $this->element('sql_dump'); ?>
	
<!-- modal content -->
<!--<div class="basic-modal-content" id="adv_session">
	<h3>Sesi&oacute;n a expirar</h3>
	<p>--><?php //echo _m('session.restante'); ?><!--</p>
</div>-->
</body>
</html>
