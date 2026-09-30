<?php
/**
 * Contenedor por defecto de las páginas externas de la aplicación. Es decir, las que se ven
 * sin que el usuario haya iniciado sesión
**/?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
  <?php echo $html->charset() ?>
  <!-- Use the .htaccess and remove these lines to avoid edge case issues.
       More info: h5bp.com/i/378 -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <?php if(!isset($page) || $page != 'nojs') { ?>
  <noscript><meta http-equiv="refresh" content="0;URL='<?php echo Router::url('/pages/nojs') ?>'"></noscript>  
  <?php } ?>
  <title>Activo en L&iacute;nea</title>
  <meta name="viewport" content="width=1024">
  <meta name="description" content="">
  <!-- Mobile viewport optimized: h5bp.com/viewport -->
  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->
	<?php echo $html->meta('icon') ?>
	<?php echo $html->css('bootstrap.css?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'css/bootstrap.css')) ?>
	<?php echo $html->css('style.css?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'css/style.css')) ?>
	<?php echo $html->script('libs/jquery-1.7.1.min.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery-1.7.1.min.js')) ?>
	<?php echo $html->script('libs/jquery-ui-1.8.22.custom.min.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery-ui-1.8.22.custom.min.js')) ?>
	<?php echo $html->script('plugins.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/plugins.js')) ?>
	<?php echo $html->script('script.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/script.js')) ?>	
	<?php if(!Configure::read('desarrollo')) echo $html->script('noback.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/noback.js')) ?>	
	<?php if(!Configure::read('desarrollo')) echo $html->script('norclick.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/norclick.js')) ?>	
	<?php echo $html->script('libs/jquery.validate/jquery.validate.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery.validate/jquery.validate.js')) ?>
	<?php echo $html->script('libs/jquery.validate/messages_es.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery.validate/messages_es.js'))?>
	<?php echo $html->script('libs/jquery.validate/additional-methods.js?'.filemtime(ROOT.DS.APP_DIR.DS.WEBROOT_DIR.DS.'js/libs/jquery.validate/additional-methods.js')) ?>
	
	<?php echo $this->Session->flash('alert') ?>
</head>
<body>
   <div class="super-container">
  <div id="top">
  <div id="logo">
	<?php echo $html->image('logo_new.png',array('url'=>'/','alt'=>'Logo Banco Activo')) ?></div>
  <div id="rif" class="offset3"><span>RIF: J-08006622-7</span></div>
  </div>
  
	<div class="container">
	<?php if(isset($error_fatal)) { ?>
	<div class="error_fatal"><?php echo $error_fatal ?></div>
	<?php } ?>	

	<?php echo $content_for_layout ?>
	</div>
  <div id="footer">
    <span>&copy; Copyright <?php echo date('Y') ?> Banco Activo, C.A. Banco Universal. RIF: J-08006622-7. Todos los derechos reservados.  La informaci&oacute;n mostrada en esta p&aacute;gina, es de car&aacute;cter Confidencial</span>
  </div>
	</div>
	
	
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>