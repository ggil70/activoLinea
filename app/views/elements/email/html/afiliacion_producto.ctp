<?php
/**
 * Correo html que se envía al usuario luego de que se realiza una afiliación
 * de una cuenta
**/

$datos_basicos = $session->read('usuarios.nibpf3010');

if($usuario['tipo_rif']){
	$nombre_usuario = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
} else {
	$nombre_usuario = desencriptar($usuario['nombre']);
}
?>Estimado(a) Cliente: <?php echo $nombre_usuario; ?>,
<br/><br/>

Para<strong> BANCO ACTIVO </strong>es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado la siguiente Afiliacion:
<br/><br/>
Tipo de Afiliacion: <?php echo $tipo_accion; ?><br/>
<?php if (isset($tipo_afiliacion)){ ?>
Tipo de Operacion: <?php echo $tipo_afiliacion; ?><br/>
<?php }?>
<?php if (isset($tipo_afiliacion_s) && ($tipo_afiliacion_s=='s' || $tipo_afiliacion_s=='r')){ 
     if($tipo_operacion == 'sv'){
?>
         Numero de Tarjeta de Acceso: <?php echo  $numero_servicio; ?> <br/>
<?php } else {           
?>
     	 Numero de Servicio: <?php echo  $numero_servicio; ?> <br/>
<?php        } 
     }
elseif (isset($tipo_afiliacion_s) && $tipo_afiliacion_s == 'i'){?>
	RIF del Beneficiario: <?php echo  $numero_servicio; ?> <br/>
<?php } elseif (isset($nombreBeneficiario)){?>
	Nombre del Beneficiario: <?php echo  $nombreBeneficiario; ?> <br/>
<?php }?>

<br/>