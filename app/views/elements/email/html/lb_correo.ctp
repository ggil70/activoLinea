<?php
/**
 * Correo html que se envía al ordenante de un pago activo
**/

$usuario = $this->Session->read('usuarios.datos');

if($usuario['tipo_rif']){
	$nombre_usuario = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];

} else {
	$nombre_usuario = desencriptar($usuario['nombre']);
}
?>


Notificacion de seguridad
<br/><br/>

Se notifica que el usuario <strong> <?php echo $nombre_usuario;?></strong> de cedula <strong> <?php echo $cedCliente;?> </strong> intento realizar la siguiente operacion:
<br/><br/>

Tipo de Operacion: <?php echo $tipo_accion;?><br/>
C&eacute;dula Destino:  <?php echo $cedBene;?><br/>
Banco:  <?php echo $banco;?><br/>
Fecha de Proceso: <?php echo date('d/m/Y H:i:s'); ?><br/>
IP: <?php echo $ip;?>
Monto: <?php echo $monto; ?><br/>

