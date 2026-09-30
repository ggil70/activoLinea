<?php
    /**
     * Vista para mostrar el detalle del movimiento seleccionado 
     */
?>

<div>
<h2 class="user">
<?php
switch($this->data['DetalleProducto']['tipo']){
	case 'CI':
	case 'WD':
	case 'CW':
	case 'PS':
	case 'WN':
	case 'DS':
	case 'PI':
        echo "Nota de Débito";
		break;
	case 'WC':
	case 'AE':
	echo "Nota de Crédito";
	break;

}?>
</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario">
<?php if(!isset($error)){ ?>
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td class="etiqueta" style="width:100px">Referencia:</td>
			<td style="width:350px"><?php echo $movimiento['REFERENCIA'] ?></td>
		</tr>
		<?php 
                if($this->data['DetalleProducto']['tipo'] != 'PI'){        
                ?>
		<tr>
			<td class="etiqueta">Fecha Real Transacción:</td>
			<td><?php echo formatea_fecha($movimiento['TINID1'], $movimiento['TINID2'], $movimiento['TINID3']); ?></td>
		</tr>
                <tr>
			<td class="etiqueta">Hora:</td>
			<td><?php echo date_format(new DateTime($detalleLogs['Bitacora']['fecha_hora']),'h:i:s A'); ?></td>
		</tr>
                <?php
                }
                ?>
		<tr>
			<td class="etiqueta">Monto:</td>
			<td><?php echo formatea_monto_fromWs($movimiento['TINAMT']) ?></td>
		</tr>
		<tr>
			<td colspan="2" class="azul">Concepto:</td>
		</tr>
		<tr>
		<td class="etiqueta">Descripción:</td>
				<td>
					<?php 
						$partes = explode(':', $movimiento['TINOBI1']);
						if($partes[0] == "MOVILNET"){
							if(preg_match('/^(0416|0426)/', $partes[1])){
								echo 'RECARGA SERVICIO';
							}else{
								echo 'PAGO SERVICIO';
							}
						}else{
							if($this->data['DetalleProducto']['tipo'] =='PS' || $this->data['DetalleProducto']['tipo'] =='DS'){
								echo 'RECARGA SERVICIO';
							}else{
								echo $movimiento['TINOBI1'];
							}
						}
						
					?>
				</td>
		</tr>
		<?php if($this->data['DetalleProducto']['tipo'] !='AE'){ ?> 
		<tr>
			<td colspan="2" class="azul">Origen:</td>
		</tr>
		<tr>
			<td class="etiqueta">Cuenta Débito:</td>
			<td><?php echo enmascara( $movimiento[ 'CTA_ORIGEN' ] )?></td>
		</tr>
		<tr>
			<td class="etiqueta">Nombre o razón social:</td>
			<td><?php echo $movimiento['NOMBRE_ORIGEN']?></td>
		</tr>
                <?php } ?>
	<?php 
	if($this->data['DetalleProducto']['tipo'] != 'WN'){ 
		if($this->data['DetalleProducto']['tipo'] =='PS' || $this->data['DetalleProducto']['tipo'] =='DS'){ 
	?>
			<tr>
				<td colspan="2" class="azul">Servicio:</td>
			</tr>
			<tr>
				<td class="etiqueta">Nombre Empresa Servicio:</td>
				<td>
					<?php 
						echo $nombre_servicio; 
					?>
				</td>
			</tr>
			<tr>
				<td class="etiqueta">Número de Servicio o Cuenta Contrato:</td>
				<td>
					<?php 
					if($this->data['DetalleProducto']['tipo'] =='PS' || $this->data['DetalleProducto']['tipo'] =='DS'){
							echo preg_replace('/[^0-9]+/', '', $movimiento['TINOBI1']);
						}else{
							echo $movimiento['TINOB1']; 
						}
						
					?>
				</td>
			</tr>			
			<tr>
				<td colspan="2" class="azul">Detalle Pago:</td>
			</tr>
                      <!--  <tr>    
                                <td class="etiqueta">Número Confirmación Operadora:</td>
                                <td><?php //echo $movimiento['TINITM'] ?></td>				
			</tr>-->
			<tr>                                    
				<td class="etiqueta">Estatus:</td>
				<td><?php if($movimiento['TINNUM']) echo strlen(trim($movimiento['TINIMD']))==0?'PROCESADO':$movimiento['TINIMD']?></td>
			</tr>
		<?php }else{ 
		 // COMENTADO A PETICION DE MARCEL 01/08/20013 //		if(!isset($this->data['DetalleProducto']['comision_cheque']) || $this->data['DetalleProducto']['comision_cheque'] != 'I'){
		?>
				<tr>
					<td colspan="2" class="azul">Beneficiario:</td>
				</tr>
				<?php 
                                if($this->data['DetalleProducto']['tipo'] != 'PI' && $this->data['DetalleProducto']['tipo'] !='AE'){        
                                ?>
				<tr>
					<td class="etiqueta">Nombre Banco:</td>
					<td><?php echo $movimiento['TINBB1']?></td>
				</tr>
                                <?php 
                                }        
                                ?>
				<tr>
					<td class="etiqueta">
					<?php
					switch($this->data['DetalleProducto']['tipo']){
						case 'CI':
							echo "Número Tarjeta:";
							//$numeroProducto = $movimiento['TINBC4'];
							break;
					        case 'PI':
                                                    echo "RIF Beneficiario:";
                                                    break;
						default:
							echo "Número Cuenta:";
							//$numeroProducto = enmascara($movimiento['TINCACC']);
							break;

					}?>
					</td>
					<?php 
                                        if($this->data['DetalleProducto']['tipo'] != 'PI'){        
                                        ?>
					<td><?php echo enmascara( $movimiento[ 'CTA_DESTINO' ] )?></td>
					<?php 
                                        }else{        
                                        ?>
                                        <td><?php echo $movimiento['CTA_DESTINO']; ?></td>
                                        <?php 
                                        }       
                                        ?>
				</tr>
				<?php 
                                if($this->data['DetalleProducto']['tipo'] != 'PI'){        
                                ?>
				<tr>
					<td class="etiqueta">Cédula o RIF:</td>
					<td><?php if(strlen(trim($movimiento['TINBC3']))>2) echo $movimiento['TINBC3']; ?></td>
				</tr>
				<tr>
					<td class="etiqueta">Nombre Beneficiario:</td>
					<td><?php echo $movimiento['NOMBRE_DESTINO']?></td>
				</tr>
                                <?php 
                                }      
                                ?>
                                
			<?php // COMENTADO A PETICION DE MARCEL 01/08/20013 //		} ?>
			<?php if($this->data['DetalleProducto']['tipo'] =='AE') { ?>
			<tr>
				<td colspan="2" class="azul">Detalle del Abono:</td>
			</tr>
                        <tr>
				<td class="etiqueta">Estatus:</td>
				<td><?php if($movimiento['TINNUM']) echo strlen(trim($movimiento['TINIMD']))==0?'PROCESADO':$movimiento['TINIMD']?></td>
			</tr>
                        <?php } else { ?>
                        <tr>
				<td colspan="2" class="azul">Detalle pago:</td>
			</tr>
                        <?php 
                        if($this->data['DetalleProducto']['tipo'] == 'PI'){        
                        ?>
			<tr>
				<td class="etiqueta">Descripción:</td>
				<td><?php echo $movimiento['DESCRIPCION']; ?></td>
                            </tr>
                            <tr>
                                    <td class="etiqueta">Impuesto:</td>
                                    <td><?php echo $movimiento['IMPUESTO'].' - '.$movimiento['NOMBREIMPUESTO']; ?></td>
                            </tr>
                            <tr>
                                    <td class="etiqueta">Forma:</td>
                                    <td><?php echo $movimiento['FORMA']; ?></td>
                            </tr>
                            <tr>
                                    <td class="etiqueta">N° Planilla:</td>
                                    <td><?php echo $movimiento['PLANILLA']; ?></td>
                            </tr>
                            <?php 
                            if($movimiento['ADUANA'] != '000000' && $movimiento['ADUANA'] != ''){        
                            ?>
                                <tr>
                                        <td class="etiqueta">Aduana:</td>
                                        <td><?php echo $movimiento['ADUANA'].' - '.$movimiento['NOMBREADUANA']; ?></td>
                                </tr>
                                <tr>
                                        <td class="etiqueta">Expediente:</td>
                                        <td><?php echo $movimiento['EXPEDIENTE']; ?></td>
                                </tr>
                            <?php 
                            }    
                            ?>
                            <tr>
                                    <td class="etiqueta">N° Autorización:</td>
                                    <td><?php echo $movimiento['AUTORIZACION']; ?></td>
                            </tr>
                        <?php 
                        }else{    
                        ?>
                            <tr>
				<td class="etiqueta">Descripción:</td>
				<td><?php echo desencriptar($detalleLogs['Bitacora']['descripcion']); ?></td>
			</tr>
                        <?php 
                        }
                        ?>
			<tr>
				<td class="etiqueta">Estatus:</td>
				<td><?php if($movimiento['TINNUM']) echo strlen(trim($movimiento['TINIMD']))==0?'PROCESADO':$movimiento['TINIMD']?></td>
			</tr>
			<tr>
				<td class="etiqueta">Motivo:</td>
				<td><?php echo ''; ?></td>
			</tr>
		<?php } ?>
		<?php } ?>
	<?php }else{
		foreach($datosTransaccion as $transaccion){
		?>
			<tr>
				<td colspan="2" class="azul">Detalle Pago:</td>
			</tr>
			<tr>
				<td class="etiqueta">Número Lote:</td>
				<td><?php echo $transaccion['TransaccionPendiente']['lote'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Descripción Lote:</td>
				<td><?php echo desencriptar($transaccion['TransaccionPendiente']['descripcion'])?></td>
			</tr>
			<tr>
				<td class="etiqueta">Fecha Valor:</td>
				<td><?php echo date('d/m/Y', strtotime($transaccion['TransaccionPendiente']['fecha_hora_valor'])) ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Cantidad Pagos Realizados:</td>
				<td><?php echo count($transaccion['Beneficiarios']); ?></td>
			</tr>
		<?php }
	}?>
		<tr>
			<td colspan="2" class="botonera">
				<div class="botonera">
					<?php
					if(isset($this->data['DetalleProducto']['flagHT'])){
						$ruta_regreso = '/consultas/historico_transacciones/v';
					}
					else if(isset($flag_buscar) || $flag_busqueda){
						$ruta_regreso = '/cuentas/buscar/v';
					}
					else{
						$ruta_regreso = '/cuentas/detalle/v';
					}
					?>
					<form name="formulario_enlace" class="formulario_enlace" method="post" action="<?php echo Router::url($ruta_regreso) ?>">
						<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']?>" />
						<input type="submit" class="button" value="Volver" />
					</form>
					<form class="formulario_enlace" method="post" onsubmit="print();return false;">
						<input type="submit" class="button" value="Imprimir" />
					</form>		
					<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/descargar_movimiento') ?>">
						<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']?>" />
						<input type="hidden" name="data[DetalleProducto][referencia]" value="<?php echo $this->data['DetalleProducto']['referencia']?>" />
                                                <input type="hidden" name="data[DetalleProducto][monto]" value="<?php echo $this->data['DetalleProducto']['monto']?>" />
						<?php if(isset($this->data['DetalleProducto']['comision_cheque'])) {?>
							<input type="hidden" name="data[DetalleProducto][comision_cheque]" value="<?php echo $this->data['DetalleProducto']['comision_cheque'] ?>" />
						<?php }?>
						<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $this->data['DetalleProducto']['tipo']?>" />
						<input type="submit" class="button" value="Descargar PDF" />
					</form>
				</div>
			</td>
		</tr>
	</table>
<?php }else{ ?>
<br>
		<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td class="resumen_paginacion texto_centro">
						<div class="error-message">
							<?php echo _m('error.fatal'); ?>
						</div>
					</td>
				</tr>
			</thead>
			<tr>
				<td  class="botonera" >
					<a href="<?php echo Router::url('/consultas/integral') ?>"><input type="submit" class="button" value="Volver" /></a>
				</td>
			</tr>
		</table>
<?php } ?>
</div>
