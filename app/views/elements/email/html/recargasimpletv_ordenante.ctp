<?php

$nombre_usuario = desencriptar($usuario['nombre'])

?>
Estimado(a) Cliente: <?php echo $nombre_usuario; ?>,
<br/><br/>

Para <strong>BANCO ACTIVO</strong> es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado la siguiente transaccion: 

<br/><br/>
Tipo de Transaccion: <?php echo $tipo_accion; ?><br/>
Tipo de Operacion: <?php echo $tipo_afiliacion; ?><br/>
Cuenta Debito: <?php echo enmascara($this->data['RecargaSimpletv']['cuentaDebitar']); ?><br/>
Numero de Tarjeta de Acceso: <?php echo $this->data['RecargaSimpletv']['numeroContrato']; ?> <br/>
Monto del Pago: <?php echo $this->data['RecargaSimpletv']['montoFinal']; ?><br/>
Dia de Programacion: <?php echo $this->data['RecargaSimpletv']['DIAPROGRAMACION']; ?><br/>
Fecha de Desconexion: <?php echo $this->data['RecargaSimpletv']['FECHADECONEXION']; ?><br/>
Fecha del Proceso: <?php echo date('Y-m-d H:i:s');?><br/>
Concepto: <?php echo  $this->data['RecargaSimpletv']['descripcion']?><br/>
Referencia: <?php echo  $this->data['RecargaSimpletv']['REFERENCIA']?><br/>
Numero Confirmacion Operadora: <?php echo  $this->data['RecargaSimpletv']['REFERENCIA_OPERADOR']?><br/>

