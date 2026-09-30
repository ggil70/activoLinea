<?php
/**
 * Correo html que se envía al ordenante de una transferencia
**/
	$nombre_usuario = $dataMail['nombreBeneficiario'];
?>

Estimado(a) Cliente: <?php echo $nombre_usuario; ?>,
<br/><br/>

Para <strong>BANCO ACTIVO</strong>  es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado la siguiente transaccion: 
<br/><br/>

Tipo de Transaccion: <?php echo $dataMail['tipo_accion'];?><br/>
Tipo de Operacion: <?php echo $dataMail['tipo_afiliacion'];?><br/>
Cuenta Origen: <?php echo $dataMail['cuentaOrigen']; ?><br/>
Cuenta Destino: <?php echo $dataMail['nro_tarjeta']; ?><br/>
Monto Recarga: 	<?php echo $dataMail['monto_recarga']; ?><br/>
Fecha de Proceso: <?php echo date('d/m/Y H:i:s'); ?><br/>
Concepto: <?php echo $dataMail['concepto']; ?><br/>


