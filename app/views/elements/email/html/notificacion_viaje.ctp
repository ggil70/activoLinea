<?php
/**
 * Correo html que se envía al cliente que realiza una notificacion de viaje
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

Para <strong>BANCO ACTIVO</strong>  es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado la siguiente transaccion: 
<br/><br/>

Tipo de Transaccion: <?php echo $tipo_accion;?><br/>
Tipo de Operacion: <?php echo $tipo_afiliacion;?><br/>
Fecha de Proceso: <?php echo date('d/m/Y H:i:s'); ?>