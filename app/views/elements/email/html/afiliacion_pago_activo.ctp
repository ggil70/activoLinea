<?php
/**
 * Correo html que se envía al usuario luego de que se realiza una afiliación de Pago Activo
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

Para<strong> BANCO ACTIVO </strong>es muy importante mantenerte informado de las operaciones realizadas a traves de nuestro servicio Activo en Linea y en esta oportunidad nos complace notificarte que hemos registrado la siguiente Afiliacion:
<br/><br/>

Tipo de Afiliacion: <?php echo $tipo_accion; ?><br/>
                    <?php 
                    if (isset($tipo_afiliacion)){ ?>
Tipo de Operacion: <?php echo $tipo_afiliacion; ?><br/>
<?php }?>

Numero de Celular: <?php echo  $numero_celular; ?> <br/>
Correo Electronico: <?php echo  $email; ?> <br/>
<?php 
    if(empty($usuario['tipo_rif'])){
?>        
    Cuenta Pagadora: <?php echo  $cuentaPagadora; ?> <br/>
<?php     
    } 
?>
Cuenta Receptora: <?php echo  $cuentaReceptora; ?> <br/>
<?php 
    if(!empty($numeroAfiliacion)){
?>        
    Numero de Afiliacion: <?php echo  $numeroAfiliacion; ?> <br/>
<?php     
    } 
?>
<br/>