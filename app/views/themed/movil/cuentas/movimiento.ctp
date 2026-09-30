<div class="subtitulo">
<h2>
<?php
switch($this->data['DetalleProducto']['tipo']){
	case 'CI':
	case 'WD':
		echo "Nota de Débito";
		break;
	case 'WC':
		echo "Nota de Crédito";
		break;

}?>
</h2>
<div></div>
</div>

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td class="etiqueta">Referencia:</td>
		<td><?php echo $movimiento['TINNUM'] ?></td>
	</tr>
	<tr>
	<td class="etiqueta">Fecha:</td>
	<td><?php echo formatea_fecha($movimiento['TINID2'], $movimiento['TINID1'], $movimiento['TINID3']); ?></td>
	</tr>
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
				if($this->data['DetalleProducto']['tipo'] =='PS'){
					echo $descripcion;
				}else{
					echo $movimiento['TINOBI1'];
				}
			?>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="azul">Origen:</td>
	</tr>
	<tr>
		<td class="etiqueta">Cuenta Débito:</td>
		<td><?php echo enmascaraMovil( $movimiento[ 'CTA_ORIGEN' ] )?></td>
	</tr>
	<tr>
		<td class="etiqueta">Nombre o razón social:</td>
		<td><?php echo $movimiento['NOMBRE_ORIGEN']?></td>
	</tr>
	<?php if($this->data['DetalleProducto']['tipo'] =='PS'){ ?>
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
				echo $numero_servicio; 
			?>
		</td>
	</tr>
	<tr>
		<td class="etiqueta">Nombre Beneficiario:</td>
		<td><?php echo desencriptar($data_beneficiario['Afiliacion']['nombre_beneficiario']);?></td>
	</tr>
	<tr>
		<td colspan="2" class="azul">Detalle Pago:</td>
	</tr>
	<tr>
		<td class="etiqueta">Estatus:</td>
		<td><?php if($movimiento['TINNUM']) echo strlen(trim($movimiento['TINIMD']))==0?'PROCESADO':$movimiento['TINIMD']?></td>
	</tr>
	<?php }else{ ?>
	<tr>
		<td colspan="2" class="azul">Beneficiario:</td>
	</tr>
	<tr>
		<td class="etiqueta">Nombre Banco:</td>
		<td><?php echo $movimiento['TINBB1']?></td>
	</tr>
	<tr>
		<td class="etiqueta">
		<?php
		switch($this->data['DetalleProducto']['tipo']){
			case 'CI':
				echo "Número Tarjeta:";
				//$numeroProducto = $movimiento['TINBC4'];
				break;
		/*	case 'WC':
			case 'WD':*/
			default:
				echo "Número Cuenta:";
				//$numeroProducto = enmascaraMovil($movimiento['TINCACC']);
				break;

		}?>
		</td>
		<td><?php echo enmascaraMovil( $movimiento[ 'CTA_DESTINO' ] )?></td>
	</tr>
	<tr>
		<td class="etiqueta">Cédula o RIF:</td>
		<td><?php echo $movimiento['TINBC3']; ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Nombre Beneficiario:</td>
		<td><?php echo $movimiento['NOMBRE_DESTINO']?></td>
	</tr>
	<tr>
		<td colspan="2" class="azul">Detalle pago:</td>
	</tr>
	<tr>
		<td class="etiqueta">Descripción:</td>
		<td><?php echo desencriptar($detalleLogs['Bitacora']['descripcion']); ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Estatus:</td>
		<td><?php if($movimiento['TINNUM']) echo strlen(trim($movimiento['TINIMD']))==0?'PROCESADO':$movimiento['TINIMD']?></td>
	</tr>
	<tr>
		<td class="etiqueta">Motivo:</td>
		<td><?php echo ''; ?></td>
	</tr>
	<?php } ?>
</table>

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
</div>
