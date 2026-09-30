<?php
/**
 * Fragmento de página que se encarga de generar el detalle de una transacción
 * pendiente por aprobar
**/
$tipo = $transaccion['TransaccionPendiente']['tipo'];
if(isset($transaccion['TransaccionPendiente']['moneda'])){
    if($transaccion['TransaccionPendiente']['moneda']=='USD'){
        $moneda = '$';
    }else{
        $moneda = '?';
    }
}else{
    $moneda = 'Bs.';
}
?>




<?php



$refOperadora = "";
$diaSimple = "";
$fechaSimple = "";
$swSimple = 0;


if(!empty($transaccion['TransaccionPendiente']['inf_simpletv'])){
    
	$cadenaSimple = $transaccion['TransaccionPendiente']['inf_simpletv'];
    $arraySimple = explode(';', $cadenaSimple);
	$refOperadora = $arraySimple[0];
	$diaSimple = $arraySimple[1];
	$fechaSimple = $arraySimple[2];
	$swSimple = 2;
}else{
	if($tipo == 'rsv'){
	    $swSimple = 1;	
	}
}





?>






<?php if($tipo[0]=='t' || $tipo[0]=='d' || $tipo[0]=='p' || $tipo[0]=='s' || $tipo[0]=='r' || $tipo[0]=='i' || $tipo[0]=='e' || $tipo[0]=='f') { ?>



	<table class="confirmacion" cellspacing="0" cellpadding="0" border="0">
		<tbody>
			<?php if (isset($transaccion['descripcion_error'])) { ?>
				<tr>
					<td class="etiqueta error-message">ERROR:</td>
					<td class="error-message"><?php echo $transaccion['descripcion_error'] ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td class="etiqueta">Tipo Operación:</td>
				<td><?php echo TransaccionPendiente::$tipo_transaccion[$tipo] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Fecha Registro:</td>
				<td><?php echo date('d/m/Y', strtotime($transaccion['TransaccionPendiente']['fecha_hora_registro'])) ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Usuario Registro:</td>
				<td><?php
				if (empty($transaccion['UsuarioCreador']['login'])) {
					echo desencriptar($transaccion['UsuarioCreador']['ibuser']);
				} else {
					echo desencriptar($transaccion['UsuarioCreador']['login']);
				}
				?></td>
			<tr>
				<td class="etiqueta">Referencia:</td>
				<td><?php echo $transaccion['TransaccionPendiente']['id'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Cuenta Debito:</td>
				<td><?php echo enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen'])) ?></td>
			</tr>
                        <?php if ($tipo[0] == 'r') {?>
			<tr>
                <?php if($swSimple == 0){?>
					<td class="etiqueta">Número Servicio o Cuenta Contrato:</td>
				<?php }else{?>	
					<td class="etiqueta">Número de Tarjeta de Acceso:</td>
                <?php }?>	
				<td><?php echo desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']) ?></td>
			</tr>
			<?php } ?>
			<?php if ($tipo[2] == 'o') {?>
			<tr>
				<td class="etiqueta">Banco del Beneficiario:</td>
				<td><?php echo $transaccion['TransaccionPendiente']['banco'] ?></td>
			</tr>
			<?php } ?>
			<?php if ($tipo[0] != 's' && $tipo[0] != 'r') {?>
			<tr>
				<td class="etiqueta">
					<?php
					switch ($tipo[0]) {
						case 'd':
                                                case 't':
							echo 'Cuenta Beneficiario:';
							break;
						case 'p':
							echo 'Número Tarjeta de Crédito:';
							break;
                                                case 'i':
                                                        echo 'RIF Beneficiario:';
                                                        break;
                                                case 'e':
                                                case 'f':
                                                        echo 'Cuenta Crédito:';
                                                        break;    
					}
					?>
				</td>
				<?php if ($tipo[0] != 's' && $tipo[0] != 'r' && $tipo[0] != 'i') {?>
                                    <td><?php echo enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_destino'])) ?></td>
                                <?php }else if( $tipo[0] == 'i'){ ?>
                                    <td><?php echo desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']) ?></td>
                                <?php } ?>
			</tr>
			<?php } ?>
			<?php if (!in_array($tipo, array('tpm', 'ppm', 'exc', 'exv'))) { ?>
							<?php if ($transaccion['TransaccionPendiente']['tipo'] != 'spc' && $transaccion['TransaccionPendiente']['tipo'] != 'stm' && $transaccion['TransaccionPendiente']['tipo'] != 'sm' && $transaccion['TransaccionPendiente']['tipo'] != 'rcn') { ?>
                            <tr>
                                    <td class="etiqueta">Nombre del Beneficiario:</td>
                                    <td>
                                    <?php echo desencriptar($transaccion['TransaccionPendiente']['nombre_beneficiario']) ?>
                                    </td>
                            </tr>
                        	<?php }?>
                            <?php if (!empty($transaccion['TransaccionPendiente']['alias'])) {?>
                            <tr>
                                    <td class="etiqueta">Alias:</td>
                                    <td><?php echo desencriptar($transaccion['TransaccionPendiente']['alias']) ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($tipo[0] != 'i') {?>
                            <tr>
                                    <td class="etiqueta">Cédula / RIF:</td>
                                    <td><?php echo $transaccion['TransaccionPendiente']['tipo_id_beneficiario'] . '-' . desencriptar($transaccion['TransaccionPendiente']['id_beneficiario']) ?></td>
                            </tr>
                            <?php } ?>
			<?php } ?>
                        <?php if ($tipo[1] != 'p' && $tipo[0] != 's' && $tipo[0] != 'r' && $tipo[0] != 'i' && $tipo[0] != 'e' && $tipo[0] != 'f') { ?>
			<tr>
				<td class="etiqueta">E-mail Beneficiario:</td>
				<td><?php echo desencriptar($transaccion['TransaccionPendiente']['email_beneficiario']) ?></td>
			</tr>
			<?php } ?>                            			              
                        <?php if (in_array($tipo, array('exc', 'exv', 'fxc'))) {                             
                            $valores = explode('-',$transaccion['TransaccionPendiente']['email_beneficiario']);
                            if($tipo=='exc' || $tipo=='fxc'){
                                $tipoMesa = 'Compra';
                            }else{
                                $tipoMesa = 'Venta';
                            }
                        ?>  
                            <tr>
                                <td class="etiqueta">Monto:</td>
                                <td><?php echo $valores[1] ?></td>
                            </tr>
                             <?php if (isset($transaccion['TransaccionPendiente']['resultado']) &&  $transaccion['TransaccionPendiente']['monto_divisaAdjudicado'] != $valores[1] ) {  ?>
                             <tr>
                                <td class="etiqueta">Monto Adjudicado:</td>
                                <td><?php echo $transaccion['TransaccionPendiente']['monto_divisaAdjudicado']?></td>
                            </tr>
                            <?php } ?>  
                           
                            <?php if (in_array($tipo, array('exc', 'exv'))) {  ?>
                                <tr>
                                    <td class="etiqueta">Tasa <?php echo $tipoMesa ?>:</td>
                                    <td><?php echo $valores[2] ?></td>
                                </tr>
                            <?php }else{ ?>
                                <tr>
                                    <td class="etiqueta">Tasa <?php echo $tipoMesa ?>:</td>
                                    <td><?php echo $valores[14] ?></td>
                                </tr>
                            <?php } ?>  

                            <?php if (isset($transaccion['TransaccionPendiente']['total'])) {  ?>
                             <tr>
                                <td class="etiqueta">Total Bs.:</td>
                                <td><?php echo $transaccion['TransaccionPendiente']['total'] ?></td>
                            </tr>
                            <?php }else{ ?>   

                            <tr>
                                <td class="etiqueta">Total Bs.:</td>
                                <td><?php echo $valores[4] ?></td>
                            </tr> 
                             <?php } ?>                                                                                 
			<?php }else{ ?> 
                            <tr>
				<td class="etiqueta">Monto <?php echo$moneda?>:</td>
				<td><?php echo formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']) ?></td>

                            </tr>
                    <?php if($swSimple==2){?>
                    	<tr>
						<td class="etiqueta">D&iacute;as Disponible de Programaci&oacute;n:</td>
						<td><?php echo $diaSimple ?></td>
					    </tr>
                    	<tr>
						<td class="etiqueta">Fecha de Desconexi&oacute;n:</td>
						<td><?php echo $fechaSimple ?></td>
					    </tr>

						<td class="etiqueta">N&uacute;mero Confirmaci&oacute;n Operadora:</td>
						<td><?php echo $refOperadora ?></td>
					    </tr>
                    <?php }?>

                    <?php } ?>

			<?php if ($tipo != 'tpm' && $tipo[0] != 'i') {
				 if ($tipo == 'spc') {?>
				 	<tr>
						<td class="etiqueta">Descripción:</td>
						<td><?php echo "Pago Corpoelec" ?></td>
					</tr>
			<?php }else{?>		
					<tr>
						<td class="etiqueta">Descripción:</td>
						<td><?php echo desencriptar($transaccion['TransaccionPendiente']['descripcion']) ?></td>
					</tr>
			<?php }} ?>
                        <?php if ($tipo[0] == 'i') { 
                            $descripcion = desencriptar($transaccion['TransaccionPendiente']['descripcion']);
                            $detalle = explode('?', $descripcion);
                        ?>
                            <tr>
				<td class="etiqueta">Forma:</td>
				<td><?php echo $detalle[1]; ?></td>
                            </tr>
                            <tr>
                                    <td class="etiqueta">N° Planilla:</td>
                                    <td><?php echo $detalle[2]; ?></td>
                            </tr>
                            <tr>
                                    <td class="etiqueta">Periodo:</td>
                                    <td><?php echo $detalle[3]; ?></td>
                            </tr>
                            <tr>
                                    <td class="etiqueta">Impuesto:</td>
                                    <td><?php echo $detalle[4].' - '.$detalle[5]; ?></td>
                            </tr>
                            <?php 
                            if($detalle[6] != "" && $detalle[7] != ""){        
                            ?>
                                <tr>
                                        <td class="etiqueta">Aduana:</td>
                                        <td><?php echo $detalle[6].' - '.$detalle[7]; ?></td>
                                </tr>
                                <tr>
                                        <td class="etiqueta">Expediente:</td>
                                        <td><?php echo $detalle[8]; ?></td>
                                </tr>
                            <?php 
                            }    
                            ?>
			<?php } ?>                        
		</tbody>                
	</table>        

<?php } else if($tipo[0]=='n' || $tipo[0]=='v' || $tipo[0]=='m' ||  $tipo[0]=='x' ){ ?>

	<table class="confirmacion" cellspacing="0" cellpadding="0" border="0">
		<tbody>
			<?php if (isset($transaccion['descripcion_error'])) { ?>
				<tr>
					<td class="etiqueta error-message">ERROR:</td>
					<td class="error-message"><?php echo $transaccion['descripcion_error'] ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td class="etiqueta">Tipo Operación:</td>
				<td><?php echo TransaccionPendiente::$tipo_transaccion[$tipo] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Usuario Registro:</td>
				<td><?php
				if (empty($transaccion['UsuarioCreador']['login'])) {
					echo desencriptar($transaccion['UsuarioCreador']['ibuser']);
				} else {
					echo desencriptar($transaccion['UsuarioCreador']['login']);
				}
				?></td>
			</tr>
			<tr>
				<td class="etiqueta">Cuenta Débito:</td>
				<td><?php echo enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen'])) ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Fecha Valor:</td>
				<td><?php echo date('d/m/Y', strtotime($transaccion['TransaccionPendiente']['fecha_hora_valor'])) ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Descripción:</td>
				<td><?php echo desencriptar($transaccion['TransaccionPendiente']['descripcion']) ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Número Lote:</td>
				<td><?php echo $transaccion['TransaccionPendiente']['lote'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Monto Bs.:</td>
				<td><?php echo formatea_monto_fromWs($transaccion['TransaccionPendiente']['monto']) ?></td>
			</tr>
		</tbody>
	</table>

	<?php if(count($transaccion['Beneficiarios'])>0) { ?>
	<div class="espacio_vertical_20"></div>
	<div class="espacio_vertical_20"></div>

	<table class="tabla" cellpadding="0" cellspacing="0" border="0" align="center" style="width:100%;">
		<thead>
			<tr>
				<td>Identificación<br/> Beneficiario</td>
				<td style="width:160px;">Nombre <br/>Beneficiario</td>
				<td>Número Cuenta <br/>Beneficiario</td>
				<td>Banco <br/>Beneficiario</td>
				<td style="width:140px;">Monto Bs.</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($transaccion['Beneficiarios'] as $i) { ?>
				<tr>
					<td><?php echo $i['tipo_cedula'] . desencriptar($i['cedula']) ?></td>
					<td><?php echo desencriptar($i['nombre']) ?></td>
					<td style="text-align:center"><?php echo desencriptar($i['numero_cuenta']) ?></td>
					<td><?php echo $bancos[substr(desencriptar($i['numero_cuenta']), 0, 4)] ?></td>
					<td class="monto"><?php echo formatea_monto_fromWs($i['monto']) ?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php } ?>

<?php } else if($tipo[0]=='c'){ ?>
	<table class="confirmacion" cellspacing="0" cellpadding="0" border="0">
		<tbody>
			<?php if (isset($transaccion['descripcion_error'])) { ?>
				<tr>
					<td class="etiqueta error-message">ERROR:</td>
					<td class="error-message"><?php echo $transaccion['descripcion_error'] ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td class="etiqueta">Tipo Operación:</td>
				<td><?php echo TransaccionPendiente::$tipo_transaccion[$tipo] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Fecha Registro:</td>
				<td><?php echo date('d/m/Y', strtotime($transaccion['TransaccionPendiente']['fecha_hora_registro'])) ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Usuario Registro:</td>
				<td><?php
				if (empty($transaccion['UsuarioCreador']['login'])) {
					echo desencriptar($transaccion['UsuarioCreador']['ibuser']);
				} else {
					echo desencriptar($transaccion['UsuarioCreador']['login']);
				}
				?></td>
			</tr>
			<tr>
				<td class="etiqueta">Referencia:</td>
				<td><?php echo $transaccion['TransaccionPendiente']['id'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Número de Cuenta:</td>
				<td><?php echo enmascara(desencriptar($transaccion['TransaccionPendiente']['cuenta_origen'])) ?></td>
			</tr>
			
			<?php if($tipo=='csi'){ ?>
				<tr>
					<td class="etiqueta">&Uacute;ltimos 6 D&iacute;gitos del Cheque:</td>
					<td><?php echo desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']); ?></td>
				</tr>
			<?php 
			}else if($tipo=='csx'){ ?>
				<tr>
					<td class="etiqueta">&Uacute;ltimos 6 D&iacute;gitos del Primer Cheque:</td>
					<td><?php echo desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']); ?></td>
				</tr>
			<?php 
			}
			else{
				list($primer,$segundo) = @explode('|', desencriptar($transaccion['TransaccionPendiente']['cuenta_destino']));
			?>
				<tr>
					<td class="etiqueta">&Uacute;ltimos 6 D&iacute;gitos del Primer Cheque:</td>
					<td><?php echo $primer; ?></td>
				</tr>
				<tr>
					<td class="etiqueta">&Uacute;ltimos 6 D&iacute;gitos del Último Cheque:</td>
					<td><?php echo $segundo; ?></td>
				</tr>
			<?php
			}
			?>
			<tr>
				<td class="etiqueta">Motivo:</td>
				<td><?php echo desencriptar($transaccion['TransaccionPendiente']['descripcion']) ?></td>
			</tr>
		</tbody>                
	</table>
<?php } ?>
