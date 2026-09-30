<?php
/**
 * Correo html que se envía al ordenante de una transferencia
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
Cuenta Origen: <?php echo enmascara($this->data['Transferencia']['cuentaDebitar']); ?><br/>
Cuenta Destino: <?php echo enmascara($this->data['Transferencia']['cuentaCreditar']); ?><br/>
Banco del Beneficiario: <?php echo $this->data['Transferencia']['banco']; ?> <br/>
Nombre del Beneficiario: <?php echo $this->data['Transferencia']['nombreBeneficiario']; ?><br/>
Monto Transferido: 	<?php echo $this->data['Transferencia']['monto']; ?>
					<?php 
					if ( $this->data['Transferencia']['MONEDA'])
					{ 
						echo ' '. $this->data['Transferencia']['MONEDA'];
					}
					?><br/>
Fecha de Proceso: <?php echo date('d/m/Y H:i:s'); ?><br/>
Concepto: <?php echo $this->data['Transferencia']['CONCEPTO']; ?><br/>
Referencia: <?php echo $this->data['Transferencia']['REFERENCIA']; ?><br/>

