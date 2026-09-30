<?php
/**
 * Correo html que se envía al usuario luego de que se realiza afiliación/desafiliación movil activo
**/

$datos_basicos = $session->read('usuarios.nibpf3010');

if($usuario['tipo_rif'])
{
	$nombre_usuario = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
}
else
{
	$nombre_usuario = desencriptar($usuario['nombre']);
}
?>

Estimado(a) Cliente: <?php echo $nombre_usuario; ?>,
<br/><br/>

Para<strong> BANCO ACTIVO </strong>es muy importante mantenerte informado de las operaciones realizadas, en esta oportunidad nos complace notificarte que se ha procesado su solicitud de <?php echo $sms; ?> al Servicio de Movil Activo.
<br/><br/>