<?php
/**
 * Correo html que se envía al usuario luego de que se realiza una afiliación
 * de una cuenta
**/

$datos_basicos = $session->read('usuarios.nibpf3010');

if($usuario['tipo_rif']){
	$nombre_usuario = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
} else {
	$nombre_usuario = desencriptar($usuario['nombre']);
}
?>Estimado(a) Cliente: <?php echo $nombre_usuario; ?>,
<br/><br/>

Para<strong> BANCO ACTIVO </strong>es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado la apertura de cuenta cash:
<br/><br/>
Tipo de Operacion: <?php echo $tipo_accion; ?><br/>
Numero de Cuenta: <?php echo $cta_usd; ?>

<br>