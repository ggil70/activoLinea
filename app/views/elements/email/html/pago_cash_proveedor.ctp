<?php
/**
 * Correo html que se envía al beneficiario de una transferencia
**/

$datos_basicos = $session->read('usuarios.nibpf3010');

$nombre_empresa = $datos_basicos['NIBPF3010Return']['NIBPF3010']['INTLNADB'];
?>
Estimado(a) Cliente: <?php echo desencriptar($beneficiario['AfiliacionCash']['nombre']) ?>
<br><br>
BANCO ACTIVO se complace en informarle que de acuerdo a instrucciones de la Empresa: <?php echo $nombre_empresa ?>, hemos registrado para su procesamiento la siguiente operacion:
<br>

<br><br>

<span style="font-weight:bold">Cuenta del Beneficiario:</span> <?php echo enmascara(desencriptar($beneficiario['AfiliacionCash']['numero_cuenta'])) ?><br>
<span style="font-weight:bold">Numero o Referencia :</span> <?php echo $dataPago['lote'] ?><br>
<?php if($beneficiario['AfiliacionCash']['moneda'] ==NULL){


	$moneda='Bs';
}elseif($beneficiario['AfiliacionCash']['moneda'] =='VEB'){

	$moneda='Bs';

}else{                   

	$moneda=$beneficiario['AfiliacionCash']['moneda'];
} ?>

<span style="font-weight:bold">Monto Operacion:</span> <?php echo $beneficiario['AfiliacionCash']['monto']." ".$moneda ?><br>
<span style="font-weight:bold">Descripcion del Pago:</span> <?php echo (isset($beneficiario['AfiliacionCash']['concepto']) && !empty($beneficiario['AfiliacionCash']['concepto'])) ? desencriptar($beneficiario['AfiliacionCash']['concepto']) : 'Orden de Pago' ; ?><br>





    