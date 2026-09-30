<?php
/**
 * Correo que se envía al ordenante del pago de servicio
**/
$nombre_usuario = desencriptar($usuario['nombre'])

?>Estimado(a) Cliente: <?php echo $nombre_usuario; ?>,
<br/><br/>
Para <strong>BANCO ACTIVO</strong> es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado la siguiente transaccion: 

<br/><br/>
Tipo de Transaccion: <?php echo $tipo_accion; ?><br/>
Tipo de Operacion: <?php echo $tipo_afiliacion; ?><br/>
Cuenta Debito: <?php echo enmascara($this->data['PagoServicios']['cuentaDebitar']); ?><br/>
Numero de Servicio: <?php echo $this->data['PagoServicios']['numeroContrato'] ?> <br/>
Monto del Pago: <?php echo $this->data['PagoServicios']['monto']; ?><br/>
Fecha del Proceso: <?php echo date('Y-m-d H:i:s');?><br/>
Referencia: <?php echo  $this->data['PagoServicios']['REFERENCIA']?><br/>

