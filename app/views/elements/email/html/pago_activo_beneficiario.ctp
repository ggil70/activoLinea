<?php
/**
 * Correo html que se envía al ordenante de un pago activo
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
?>Estimado(a) Cliente: <?php echo $this->data['PagoActivo']['nombreBeneficiario']; ?>,
<br/><br/>
BANCO ACTIVO se complace en informarte que de acuerdo a instrucciones de: <?php echo $nombre_usuario; ?>, se le ha enviado un Pago:
<br/><br/>
Cuenta: <?php echo enmascara($this->data['PagoActivo']['codigo_telefono'].$this->data['PagoActivo']['telefono']); ?><br/>
Banco: <?php echo $this->data['PagoActivo']['banco']; ?><br/>
Monto: <?php echo $this->data['PagoActivo']['monto']; ?><br/>
Concepto: <?php echo $this->data['PagoActivo']['descripcion']; ?><br/>
