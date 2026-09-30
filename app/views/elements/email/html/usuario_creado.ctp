<?php
/**
 * Correo html que se envía al usuario cuando se acaba de registrar
**/
?>Estimado(a) Cliente: <?php echo $usuarioEmpresa['Usuario']['nombre']; ?>,
<br/><br/>
BANCO ACTIVO se complace en informarle que usted se encuentra registrado en la 
Banca en Linea con los siguientes datos de acceso: 
<br/><br/>
<b>Rif:</b> <?php echo $usuario['tipo_rif'].'-'.desencriptar($usuario['rif']); ?><br/>
<b>Clave:</b> <?php echo $clave_acceso; ?>
<br/><br/>
Para completar el registro debe acceder al sistema de Banca en Linea con los
datos de acceso suministrados.
