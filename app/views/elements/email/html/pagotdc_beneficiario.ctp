<?php
/**
 * Correo html que se envía al beneficiario de un pago de tarjeta de crédito
**/

$datos_basicos = $session->read('usuarios.nibpf3010');

if($usuario['tipo_rif']){
	$nombre_usuario = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
} else {
	$nombre_usuario = desencriptar($usuario['nombre']);
}
?>Estimado(a) Cliente: <?php echo $this->data['Pagostdc']['nombreBeneficiario']; ?>,
<br/><br/>

<strong>BANCO ACTIVO</strong> se complace en informarte que de acuerdo a instrucciones de: <?php echo $nombre_usuario; ?>, se ha enviado un Pago a la Tarjeta de Credito:<br/><br>

Numero de Tarjeta: <?php echo enmascara($this->data['Pagostdc']['numeroTDC']); ?><br/>
Banco del Beneficiario: <?php echo $this->data['Pagostdc']['banco']; ?><br/>
Monto del Pago: <?php echo $this->data['Pagostdc']['monto']; ?><br/>
Concepto: <?php echo $this->data['Pagostdc']['descripcion']; ?>
