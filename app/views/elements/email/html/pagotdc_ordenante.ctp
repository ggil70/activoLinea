<?php
/**
 * Correo html que se envía al ordenante de un pago de tarjeta de crédito
**/


$datos_basicos = $session->read('usuarios.nibpf3010');

if($usuario['tipo_rif']){
	$nombre_usuario = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
} else {
	$nombre_usuario = desencriptar($usuario['nombre']);
}
?>Estimado(a) Cliente: <?php echo $nombre_usuario; ?>,
<br/><br/>
Para <strong>BANCO ACTIVO</strong> es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado la siguiente transaccion: 

<br/><br/>
Tipo de Transaccion: <?php echo $tipo_accion; ?><br/>
Tipo de Operacion: <?php echo $tipo_afiliacion; ?><br/>
Cuenta Debito: <?php echo enmascara($this->data['Pagostdc']['cuentaDebitar']); ?><br/>
Numero de Tarjeta: <?php echo enmascara($this->data['Pagostdc']['numeroTDC']); ?> <br/>
Banco del Beneficiario: <?php echo $this->data['Pagostdc']['banco']; ?> <br/>
Nombre del Beneficiario: <?php echo $this->data['Pagostdc']['nombreBeneficiario']; ?><br/>
Monto del Pago: <?php echo $this->data['Pagostdc']['monto']; ?><br/>
Fecha del Proceso: <?php echo date('d/m/Y H:i:s');?><br/>
Concepto: <?php echo  $this->data['Pagostdc']['CONCEPTO']?><br/>
Referencia: <?php echo  $this->data['Pagostdc']['REFERENCIA']?><br/>
