<?php
/**
 * Correo en html que se envía al usuario cuando quiere recordar su nombre
 * de usuario
**/
?>
Estimado(a) Cliente: <?php echo desencriptar($usuario['nombre']); ?>,
<br/><br/>
<strong>BANCO ACTIVO</strong> se complace en informarle que usted se encuentra registrado en la 
Banca en Linea con el siguiente Usuario: <?php echo desencriptar($usuario['login']); ?>