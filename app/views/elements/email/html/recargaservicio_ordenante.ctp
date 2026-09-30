<?php

$nombre_usuario = desencriptar($usuario['nombre'])

?>
Estimado(a) Cliente: <?php echo $nombre_usuario; ?>,
<br/><br/>

Para <strong>BANCO ACTIVO</strong> es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado la siguiente transaccion: 

<br/><br/>
Tipo de Transaccion: <?php echo $tipo_accion; ?><br/>
Tipo de Operacion: <?php echo $tipo_afiliacion; ?><br/>
Cuenta Debito: <?php echo enmascara($this->data['RecargaServicios']['cuentaDebitar']); ?><br/>

<?php
if(isset($this->data['RecargaServicios']['diaProgramacionActual'])){
?>
Numero de Tarjeta de Acceso: <?php echo $this->data['RecargaServicios']['numeroContrato']; ?> <br/>
<?php
}else{
?>	
Numero de Servicio: <?php echo $this->data['RecargaServicios']['numeroContrato']; ?> <br/>
<?php
}
?>	



Monto del Pago: <?php echo $this->data['RecargaServicios']['monto']; ?><br/>

<?php
if(isset($this->data['RecargaServicios']['diaProgramacionActual'])){
?>
Dias Disponibles de Programacion: <?php echo $this->data['RecargaServicios']['diaProgramacionActual']; ?> <br/>
Fecha de Desconexion: <?php echo $this->data['RecargaServicios']['fechaDeconexion']; ?><br/>
<?php
}
?>

Fecha del Proceso: <?php echo date('Y-m-d H:i:s');?><br/>
Concepto: <?php echo  $this->data['RecargaServicios']['descripcion']?><br/>
Referencia: <?php echo  $this->data['RecargaServicios']['REFERENCIA']?><br/>

<?php
if(isset($this->data['RecargaServicios']['diaProgramacionActual'])){
?>
Numero Confirmacion Operadora: <?php echo  $this->data['RecargaServicios']['REFERENCIA_OPERADOR']?><br/>
<?php
}else{
?>	
Numero Control Operador: <?php echo  $this->data['RecargaServicios']['REFERENCIA_OPERADOR']?><br/>
<?php
}
?>	


