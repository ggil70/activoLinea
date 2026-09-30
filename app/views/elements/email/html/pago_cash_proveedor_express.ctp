<?php
/**
 * Correo html que se envía al beneficiario de una transferencia
**/

$datos_basicos = $session->read('usuarios.nibpf3010');

$nombre_empresa = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
?>
Estimado(a) Cliente: <?php echo $beneficiario['nombre'] ?>
<br><br>
BANCO ACTIVO se complace en informarle que de acuerdo a instrucciones de la Empresa: <?php echo $nombre_empresa ?>, hemos registrado para su procesamiento la siguiente operacion:
<br>

<br><br>

<span style="font-weight:bold">Cuenta del Beneficiario:</span> <?php echo enmascara($beneficiario['numero_cuenta']) ?><br>
<span style="font-weight:bold">Numero o Referencia :</span> <?php echo $dataPago['lote'] ?><br>

<span style="font-weight:bold">Monto Operacion:</span> <?php echo $beneficiario['monto']." "."Bs" ?><br>
<span style="font-weight:bold">Descripcion del Pago:</span> <?php echo (isset($beneficiario['concepto']) && !empty($beneficiario['concepto'])) ? desencriptar($beneficiario['concepto']) : 'Orden de Pago' ; ?><br>





    