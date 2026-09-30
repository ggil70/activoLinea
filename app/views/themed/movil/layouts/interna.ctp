<?php 
$usuario = $this->Session->read('usuarios.datos');
$datos_basicos = $this->Session->read('usuarios.nibpf3010');
$ultima_conexion = $this->Session->read('usuarios.lastlogged');
$ms_adv = $this->Session->read('usuarios.timeadvsess');
$ms_fin = $this->Session->read('usuarios.timefinsess');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<?php echo $html->charset() ?>
	<meta name="description" content="">
	<noscript><meta http-equiv="refresh" content="0;URL='<?php echo Router::url('/pages/nojs') ?>'"/></noscript>  
	<title>Activo en L&iacute;nea</title>
	<?php echo $html->meta('icon') ?>
	<?php echo $html->css('style.css?'.filemtime(ROOT.DS.APP_DIR.DS.'views/themed/movil/webroot/css/style.css')) ?>
	<?php echo $html->script('libs/jquery-1.7.1.min.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery-1.7.1.min.js')) ?>
	<?php echo $html->script('libs/jquery-ui-1.8.22.custom.min.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery-ui-1.8.22.custom.min.js')) ?>
	<?php echo $html->script('plugins.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/plugins.js')) ?>
	<?php if(!Configure::read('desarrollo')) echo $html->script('noback.js?'.filemtime(ROOT.DS.APP_DIR.DS.'views/themed/movil/webroot/js/noback.js')) ?>	
	<?php if(!Configure::read('desarrollo')) echo $html->script('norclick.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/norclick.js')) ?>	
	<?php echo $html->script('script.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/script.js')) ?>
	<?php echo $html->script('movil.js?'.filemtime(ROOT.DS.APP_DIR.DS.'views/themed/movil/webroot/js/movil.js')) ?>
	<?php echo $html->script('libs/jquery.validate/jquery.validate.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery.validate/jquery.validate.js')) ?>
	<?php echo $html->script('libs/jquery.validate/messages_es.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery.validate/messages_es.js'))?>
	<?php echo $html->script('libs/jquery.validate/additional-methods.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery.validate/additional-methods.js')) ?>
	<script type="text/javascript">
		var timeout_salir;

		function crearTimeoutSesion(){
			setTimeout(
				function(){
					
					setTimeout(
						function(){
						
							alert(<?php echo _m('session.restante',null,true) ?>);
									
							$.ajax({
								url:<?php echo json_encode(Router::url('/usuarios/ping')) ?>,
								success:function(){
									if(timeout_salir) {
										clearTimeout(timeout_salir);
									}
									
									crearTimeoutSesion();
								}
							});
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
		}
		
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
		});
		<?php } ?>
	</script>	
	
</head>
<body>

<div id="supercontenedor">

<div id="header">
	<!--div id="fecha"><?php echo strftimeAW('%A, %d de %B de %Y') ?></diV-->
	<div id="logo">
	<?php echo $html->image('Logo-I.png',array('url'=>'/consultas/integral','alt'=>'Logo Banco Activo')) ?>
	</div>
	<div id="rif">RIF: J-08006622-7</div>
</div>

<div id="cuerpo">	
	<?php 
	echo $html->image('salir.png',array('url'=>'/usuarios/logout', 'id'=>'salir'));
	echo $html->image('home.png',array('url'=>'/consultas/integral', 'id'=>'home'));
	?>
	
	
	<div class="titulo">
		<h1>Activo en L&iacute;nea <span><?php echo $this->Session->check('login.juridico') ? 'Empresas' : 'Personas'; ?></span></h1>
	</div>
	<div class="info_cliente">
	<span class="nombre_cliente"></span><?php 
	if(!empty($usuario['tipo_rif'])) {
		echo $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'] . '<br><span style="width:18px;display:inline-block"></span>';
	}

	echo desencriptar($usuario['nombre']); ?><br/>
	<span class="ultima_conexion"></span><?php echo !empty($ultima_conexion) ? "Ultima conexi&oacute;n: " . $ultima_conexion : ""; ?>
	</div>
	
	<div class="clear"></div>
	<?php echo $content_for_layout ?>
</div>

<div id="footer">
	&copy; Copyright <?php echo date('Y') ?> Banco Activo, C.A. Banco Universal. RIF: J-08006622-7. Todos los derechos reservados.
</div>
 
</div>
 
</body>

