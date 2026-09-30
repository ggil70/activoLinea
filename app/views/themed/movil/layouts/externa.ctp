<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<?php echo $html->charset() ?>
	<meta name="description" content="">
	<?php if(!isset($page) || $page != 'nojs') { ?>
	<noscript><meta http-equiv="refresh" content="0;URL='<?php echo Router::url('/pages/nojs') ?>'"></noscript>  
	<?php } ?>
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
</head>
<body>

<div id="supercontenedor">

<div id="header">
	<!--div id="fecha"><?php echo strftimeAW('%A, %d de %B de %Y') ?></diV-->
	<div id="logo">
		<?php echo $html->image('Logo-I.png',array('url'=>'/','alt'=>'Logo Banco Activo')) ?>
	</div>
	<div id="rif">RIF: J-08006622-7</div>
</div>

<div id="cuerpo">
	<br/>
	<?php echo $content_for_layout ?>
</div>

<div id="footer">
	&copy; Copyright <?php echo date('Y') ?> Banco Activo, C.A. Banco Universal. RIF: J-08006622-7. Todos los derechos reservados.
</div>

</div>
  
</body>

