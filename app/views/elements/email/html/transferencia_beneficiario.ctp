<?php
/**
 * Correo html que se envía al beneficiario de una transferencia
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

?>Estimado(a) Cliente: <?php echo $this->data['Transferencia']['nombreBeneficiario']; ?>,
<br/><br/>

BANCO ACTIVO se complace en informarte que de acuerdo a instrucciones de: <?php echo $nombre_usuario; ?>, se le ha enviado una Transferencia:
<br/><br/>

Cuenta:	<?php echo enmascara($this->data['Transferencia']['cuentaCreditar']); ?><br/>
Banco: 	<?php echo $this->data['Transferencia']['banco']; ?><br/>
Monto: 	<?php echo $this->data['Transferencia']['monto']; ?>
		<?php 
		if ( $this->data['Transferencia']['MONEDA'])
			{ 
				echo ' '. $this->data['Transferencia']['MONEDA'];
			}
		?><br/>
Concepto: <?php echo $this->data['Transferencia']['descripcion']; ?><br/>





