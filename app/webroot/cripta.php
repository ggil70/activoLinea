<?php

$cifrado = MCRYPT_RIJNDAEL_128;
$modo = MCRYPT_MODE_ECB;
$iv = mcrypt_create_iv(mcrypt_get_iv_size($cifrado, $modo), MCRYPT_RAND);
//$iv = base64_decode('Jz3t3iGuJIeVWoluzaYnupF8gxQpRKejW+QbEHLG5YI=');
//var_dump(base64_encode($iv));

function encriptar($cadena, $clave = "bcoactivo") {
	if(trim($cadena)){
		global $cifrado,$modo,$iv;
		return base64_encode(mcrypt_encrypt($cifrado, $clave, trim($cadena), $modo, $iv));
	}
}

function desencriptar($cadena, $clave = "bcoactivo") {
	if(trim($cadena)){
		global $cifrado,$modo,$iv;
		return trim(mcrypt_decrypt($cifrado, $clave, base64_decode($cadena), $modo, $iv));
	}
}

import_request_variables("gp", "p_");
?>
<html>
<body>
<form method="post">
<p>Texto a encriptar/desencriptar</p>
<p><textarea name="texto" rows="10" cols="100"><?php if(isset($p_texto)) echo $p_texto;
?></textarea></p>
<?php if(isset($p_texto)) echo '<p>Tama&ntilde;o: ',strlen($p_texto).'</p>' ?>
<p>
<input type="submit" name="accion" value="Encripta">&nbsp;&nbsp;
<input type="submit" name="accion" value="Desencripta">
</p>
</form>
<p>Resultado:</p>
<p>
<textarea  rows="10" cols="100"><?php 


if(isset($p_texto) && isset($p_accion)){
	switch($p_accion){
		case 'Encripta':
			//$resultado = encriptar(trim($p_texto));
			$resultado = encriptar($p_texto);
			break;
		case 'Desencripta':
			//$resultado = desencriptar(trim($p_texto));
			$resultado = desencriptar($p_texto);
			break;
	}
	
	if(isset($resultado)) echo $resultado;
}

?></textarea></p>
<?php if(isset($resultado)) echo '<p>Tama&ntilde;o: ',strlen($resultado).'</p>' ?>

</body>
</html>