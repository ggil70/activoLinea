<?php
if( isset($result) && $result ){
	header('Content-Type: image/gif');
	header('Content-Length: '.strlen($result['Sello']['imagen']));
	echo $result['Sello']['imagen'];
	exit();
}
?>