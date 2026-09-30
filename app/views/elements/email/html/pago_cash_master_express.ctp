<?php
/**
 * Correo html que se envía al beneficiario de una transferencia
**/


$datos_basicos = $session->read('usuarios.nibpf3010');

$nombre_empresa = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];



?>Estimado(a) Cliente: <?php echo $nombre_empresa ?>
<br/><br/>

BANCO ACTIVO se complace en informarle que de acuerdo a sus instrucciones, se ha debitado en su Cuenta Bancaria 

<?php

if($moneda=='USD'){echo 'en Dolares';}
if($moneda=='EUR'){echo 'en Euros';}

 ?>

 No.: <?php echo enmascara($dataPago['cuentaDebitar']); ?>, las cantidades de dinero correspondiente al Lote de <?php echo ($dataPago['tipo']=='n') ? 'Pago de Nomina' : ' Orden de Pago'; ?> identificado con las siglas: <?php echo $dataPago['lote'] ?>.
<br/>