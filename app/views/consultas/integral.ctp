<?php

/**
 * Vista para mostrar el consolidado de productos
 */
?>

<div class="descripcion">La consulta integral presenta de manera consolidada, el estado de los productos y servicios que tiene en Banco Activo. Para ver m&aacute;s informaci&oacute;n haga click sobre el producto.</div>

<?php if (!empty($cuentas) || isset($error_fatal)) { ?>
	<div>
		<h2 class="user">Cuentas Bancarias Moneda Nacional</h2>
		<div class="line_title_orange"></div>
	</div>

	<table class="tabla" cellpadding="0" cellspacing="0" align="center">
		<thead>
			<tr>
				<td>Alias</td>
				<td>N&uacute;mero</td>
				<td>Tipo Moneda</td>
				<td>Saldo Total</td>
				<!-- <td>Saldo Total Petro</td> -->
				<td>Saldo Disponible</td>
				<!--<td>Saldo Disponible Petro</td>-->
			</tr>
		</thead>
		<?php if (!empty($cuentas)) { ?>
			<tbody>
				<?php foreach ($cuentas as $c) { ?>
					<tr>
						<td class="texto_centro"><?php echo $c['ALIAS']; ?></td>
						<td class="texto_centro">
							<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/detalle') ?>">
								<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $c['SUMACN'] ?>" />
								<input type="submit" class="boton_enlace" value="<?php echo enmascara($c['SUMACN']) ?>" />
							</form>
						</td>
						<td class="texto_centro"><?php echo ($c['SUMCCY']) === 'VEB' ? 'BS.' : 'BS.'; ?></td>
						<td class="monto"><?php echo formatea_monto_fromWs($c['SUMBAL']); ?></td>
						<!--<td class="monto"><?php //echo formatea_monto_petro(round($c['SUMBAL'] / $petro_bs, 6)); ?></td>-->
						<td class="monto"><?php echo formatea_monto_fromWs($c['SUMAVB']); ?></td>
						<!--<td class="monto"><?php //echo formatea_monto_petro(round($c['SUMAVB'] / $petro_bs, 6)); ?></td>
					</tr>-->
				<?php } ?>
			</tbody>
		<?php } ?>
		<tfoot>
			<tr>
				<td colspan="3" class="total">Total:</td>
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales['SUMBAL']); ?> </td>
				<!--<td class="monto_total"><?php //echo formatea_monto_petro($totales['SUMBAL_PETRO']); ?> </td>-->
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales['SUMAVB']); ?> </td>
				<!--<td class="monto_total"><?php //echo formatea_monto_petro($totales['SUMAVB_PETRO']); ?> </td>-->

			</tr>
		</tfoot>
	</table>
<?php } ?>

<?php if (!empty($cuentas_moneda_extranjera)) { ?>
	<div>
		<h2 class="user">Cuentas Bancarias Moneda Extranjera</h2>
		<div class="line_title_orange"></div>
	</div>

	<table class="tabla" cellpadding="0" cellspacing="0" align="center">
		<thead>
			<tr>
				<td>Alias</td>
				<td>N&uacute;mero</td>
				<td>Tipo Moneda</td>
				<td>Saldo Total</td>
				<!--<td>Saldo Total Petro</td>-->
				<td>Saldo Disponible</td>
				<!--<td>Saldo Disponible Petro</td>-->
			</tr>
		</thead>
		<?php if (!empty($cuentas_moneda_extranjera)) { ?>
			<tbody>
				<?php foreach ($cuentas_moneda_extranjera as $ce) { ?>
					<tr>
						<td class="texto_centro"><?php echo $ce['ALIAS']; ?></td>
						<td class="texto_centro">
							<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/detalle') ?>">
								<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $ce['SUMACN'] ?>" />
								<input type="submit" class="boton_enlace" value="<?php echo enmascara($ce['SUMACN']) ?>" />
							</form>
						</td>
						<td class="texto_centro"><?php echo $ce['SUMCCY']; ?></td>
						<td class="monto"><?php echo formatea_monto_fromWs($ce['SUMBAL']); ?></td>
						<!--<td class="monto"><?php //echo formatea_monto_petro(round($ce['SUMBAL'] / $petro_usd, 6)); ?></td>-->
						<td class="monto"><?php echo formatea_monto_fromWs($ce['SUMAVB']); ?></td>
						<!--<td class="monto"><?php //echo formatea_monto_petro(round($ce['SUMAVB'] / $petro_usd, 6)); ?></td>-->
					</tr>
				<?php } ?>
			</tbody>
		<?php } ?>
		<tfoot>
			<tr>
				<td colspan="3" class="total">Total:</td>
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_moneda_extranjera['SUMBAL']); ?> </td>
				<!--<td class="monto_total"><?php //echo formatea_monto_petro($totales_moneda_extranjera['SUMBAL_PETRO']); ?> </td>-->
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_moneda_extranjera['SUMAVB']); ?> </td>
				<!--<td class="monto_total"><?php //echo formatea_monto_petro($totales_moneda_extranjera['SUMAVB_PETRO']); ?> </td>-->
			</tr>
		</tfoot>
	</table>
<?php } ?>

<?php if (!empty($tarjetas)) { ?>
	<div style="clear:both;">
		<h2 class="user">Tarjetas de Cr&eacute;dito</h2>
		<div class="line_title_orange"></div>
	</div>

	<table class="tabla" cellpadding="0" cellspacing="0" align="center">
		<thead>
			<tr>
				<td>Alias</td>
				<td>N&uacute;mero</td>
				<td>Saldo Actual Bs.</td>
				<!--<td>Saldo Actual Petro.</td>-->
				<td>Saldo Actual UVC.</td>
				<td>Cr&eacute;dito Disponible Bs.</td>
				<!--<td>Cr&eacute;dito Disponible Petro</td>-->
				<td>Pago M&iacute;nimo Bs.</td>
				<td>Fecha L&iacute;mite Pago</td>
			</tr>
		</thead>
		
		<?php if (!empty($tarjetas)) { 
              
			?>
			<tbody>
                
				<?php 

					foreach ($tarjetas as $t) { 
					?>
						<tr>
							<td class="texto_centro"><?php echo $t['ALIAS']; ?></td>
							<td class="texto_centro">
								<?php if (trim($t['SUMTYP']) == 'TCC') { ?>
									<form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjetas/detalle_emp_corp') ?>">
									<?php } else { ?>
										<form class="formulario_enlace" method="post" action="<?php echo Router::url('/tarjetas/detalle') ?>">
										<?php } ?>
										<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo trim($t['nroTarjeta']) ?>" />
										<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo trim($t['nroTarjeta']) ?>" />
										<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo trim($t['SUMTYP']) ?>" />
										<input type="submit" class="boton_enlace" value="<?php echo enmascara($t['nroTarjeta']) ?>" />
										</form>
							</td>

								<td class="monto"><?php echo formatea_monto_fromWs($t['saldo_actual']); ?></td>

								<!--<td class="monto"><?php //echo formatea_monto_petro(round($t['saldo_actual']/$petro_bs,6)); ?></td>-->

								<td class="monto"><?php echo formatea_monto_fromWs($t['saldo_actual_uvc']); ?></td>
									
								<td class="monto"><?php echo formatea_monto_fromWs($t['creditoBS']); ?></td>
								<!--<td class="monto"><?php //echo formatea_monto_petro(round($t['creditoBS']/$petro_bs,6)); ?></td>-->

						
								<td class="monto"><?php echo formatea_monto_fromWs($t['pago_minimo']); ?></td>


						        <td class="texto_centro"><?php echo formatea_fecha($t['dia_pago'], $t['mes_pago'], $t['anio_pago']); ?></td>
						</tr>



				<?php } ?>
			</tbody>
		<?php } ?>
		<tfoot>
			<tr>
				<td class="total" colspan="3">Total:</td>
				<td class="monto_total">
					<?php echo formatea_monto_fromWs($totales_tarjetas['saldo_actual']) ?> 
				</td>

				<!--<td class="monto"><?php //echo formatea_monto_petro(round($totales_tarjetas['saldo_petro']/$petro_bs,6)); ?>
				</td>-->



				<!-- <td class="monto_total"><?php //echo formatea_monto_petro($totales_tarjetas['saldo_actual_petro']) ?> </td> -->
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_tarjetas['saldo_actualUVC']) ?> </td>
				<!-- <td class="monto_total"><?php //echo formatea_monto_petro($totales_tarjetas['credito_disponible_petro']) ?> </td> -->

				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_tarjetas['credito_disponible']) ?> </td>

				<!--<td class="monto"><?php //echo formatea_monto_petro(round($totales_tarjetas['credito_disponible']/$petro_bs,6)); ?></td>-->				

				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_tarjetas['pago_minimo']) ?> </td>
			
			</tr>
		</tfoot>
	</table>
<?php } ?>


<?php if (!empty($creditos)){ ?>
	<div style="clear:both;">
		<h2 class="user">Cr&eacute;ditos</h2>
		<div class="line_title_orange"></div>
	</div>

	<table class="tabla" cellpadding="0" cellspacing="0" align="center">
		<thead>
			<tr>
				<td>Alias</td>
				<td>N&uacute;mero</td>
				<td width="15%">Saldo Deudor Bs.</td>
				<!--<td width="15%">Saldo Deudor Petro</td>-->
				<td>Saldo Capital Bs.</td>
				<!--<td>Saldo Capital Petro</td>-->
				<td width="17%">Total Pr&oacute;ximo <br> Pago Bs.</td>
				<td>Fecha<br />Pr&oacute;ximo Pago</td>
			</tr>
		</thead>
		<?php if (!empty($creditos)) { ?>
			<tbody>
				<?php foreach ($creditos as $i) { ?>
					<tr>
						<td class="texto_centro"><?php echo $i['ALIAS']; ?></td>
						<td class="texto_centro">
							<form class="formulario_enlace" method="post" action="<?php echo Router::url('/creditos/movimientos') ?>">
								<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo trim($i['SUMACN']) ?>" />
								<input type="submit" class="boton_enlace" value="<?php echo trim($i['SUMACN']) ?>" />
							</form>
						</td>
						<td class="monto"><?php echo formatea_monto_fromWs($i['LNSPOF']) ?></td>
						<!--<td class="monto"><?php //echo formatea_monto_petro(round($i['LNSPOF'] / $petro_bs, 6)) ?></td>-->

						<td class="monto"><?php echo formatea_monto_fromWs($i['LNSPRI']) ?></td>
						<!--<td class="monto"><?php //echo formatea_monto_petro(round($i['LNSPRI'] / $petro_bs, 6)) ?></td>-->
						<td class="monto"><?php echo formatea_monto_fromWs($i['e01DEAMPP']) ?></td>
						<td class="texto_centro"><?php echo formatea_fecha($i['e01DEAMPP2'], $i['e01DEAMPP1'], $i['e01DEAMPP3']) ?></td>
					</tr>
				<?php } ?>
			</tbody>
		<?php } ?>
		<tfoot>
			<tr>
				<td colspan="3" class="total">Total:</td>
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_creditos['LNSPOF']) ?> </td>
				<!--<td class="monto_total"><?php //echo formatea_monto_petro($totales_creditos['LNSPOF_PETRO']) ?> </td>-->
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_creditos['LNSPRI']) ?> </td>
				<!--<td class="monto_total"><?php //echo formatea_monto_petro($totales_creditos['LNSPRI_PETRO']) ?> </td>-->
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_creditos['e01DEAMPP']) ?> </td>
				<!-- <td class="monto_total">&nbsp;</td> -->
			</tr>
		</tfoot>
	</table>
<?php } ?>


<?php if (!empty($montos_uvcc)) { ?>
	<div style="clear:both;">
		<h2 class="user">Cr?ditos en UVCC / UVCP</h2>
		<div class="line_title_orange"></div>
	</div>

	<table class="tabla" cellpadding="0" cellspacing="0" align="center">
		<thead>
			<tr>
				<td width="12%">TIPO DE CREDITO</td>
				<td width="12%">N&uacute;mero</td>
				<td width="15%">Saldo Bs </td>
				<td width="15%">Saldo Deudor Bs </td>
				<td width="15%">Saldo Deudor </br> Valorado Bs </td>
				<td width="15%">Saldo Capital </br> Valorado Bs </td>
				<!--<td width="15%">Saldo Capital (PETRO)</td>-->
			</tr>
		</thead>
		<?php if (!empty($montos_uvcc)) { ?>
			<tbody>
				<?php foreach ($montos_uvcc as $i) { ?>
					<tr>
						<?php if ($i['SUMUSR'] == 'CA01') {

							echo '<td class="texto_centro">Cr&eacute;dito comercial</td>';
							$tipo_credito = 'UVCC';
						} else {
							echo '<td class="texto_centro">Cr&eacute;dito agropecuario</td>';
							$tipo_credito = 'UVCP';
						} ?>
						<td class="texto_centro">
							<form class="formulario_enlace" method="post" action="<?php echo Router::url('/creditos/movimientos') ?>">
								<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo trim($i['SUMACCS3']) ?>" />
								<input type="submit" class="boton_enlace" value="<?php echo trim($i['SUMACCS3']) ?>" />
							</form>
						</td>
						<td class="monto"><?php echo formatea_monto_fromWs($i['SUMCOBS3']) ?></td>
						<td class="monto"><?php echo formatea_monto_fromWs($i['SUMCBS3']) ?></td>
						<td class="monto"><span class="tooltip"><?php echo formatea_monto_fromWs($i['SUMSDBBSR3']); ?><span class="tooltiptext"><?php echo  $tipo_credito . ' ' . formatea_monto_fromWs($i['SUMSDUVCC3']) ?></span></span></td>
						<td class="monto"><span class="tooltip"><?php echo formatea_monto_fromWs($i['SUMCBSR3']); ?><span class="tooltiptext"><?php echo $tipo_credito . ' ' . formatea_monto_fromWs($i['SUMSCUVCC3']) ?></span></span></td>
						<!--<td class="monto"><?php //echo formatea_monto_petro(round($i['SUMCBSR3'] / $petro_bs, 6)) ?></td>-->
					</tr>
				<?php } ?>
			</tbody>
		<?php } ?>
		<tfoot>
			<tr>
				<td colspan="2" class="total">Total:</td>
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_uvcc['SUMCOBS3']) ?> </td>
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_uvcc['SUMCBS3']) ?> </td>
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_uvcc['SUMSDBBSR3']) ?> </td>
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_uvcc['SUMCBSR3']) ?> </td>
				<!--<td class="monto_total"><?php //echo formatea_monto_petro($totales_uvcc['SUMCBSR3_PETRO']) ?> </td>-->
			</tr>
		</tfoot>
	</table>
<?php } ?>

<?php if (!empty($inversiones)) { ?>
	<div style="clear:both;">
		<h2 class="user">Inversiones</h2>
		<div class="line_title_orange"></div>
	</div>

	<table class="tabla" cellpadding="0" cellspacing="0" align="center">
		<thead>
			<tr>
				<td>Alias</td>
				<td>N&uacute;mero</td>
				<td>Monto Invertido Bs.</td>
				<td>Fecha Vencimiento</td>
				<td>Intereses Ganados Bs.</td>
			</tr>
		</thead>
		<?php if (!empty($inversiones)) { ?>
			<tbody>
				<?php
				foreach ($inversiones as $c) {
				?>
					<tr>
						<td class="texto_centro"><?php echo $c['ALIAS']; ?></td>
						<td class="texto_centro">
							<form class="formulario_enlace" method="post" action="<?php echo Router::url('/inversiones/detalle'); ?>">
								<input type="hidden" name="data[DetalleProducto][nro_inversion]" value="<?php echo $c['SUMACN']; ?>" />
								<input type="submit" class="boton_enlace" value="<?php echo $c['SUMACN']; ?>" />
							</form>
						</td>
						<td class="monto"><?php echo formatea_monto_fromWs($c['SUMBAL']); ?></td>
						<td class="texto_centro"><?php echo formatea_fecha($c['CDSMATDTE1'], $c['CDSMATDTE2'], $c['CDSMATDTE3']); ?></td>
						<td class="monto"><?php echo formatea_monto_fromWs($c['SUMAVB']); ?></td>
					</tr>
				<?php
				}
				?>
			</tbody>
		<?php } ?>
		<tfoot>
			<tr>
				<td class="total" colspan="3">Total Bs.:</td>
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_inversiones['SUMBAL']); ?> </td>
				<td class="monto_total">&nbsp;</td>
				<td class="monto_total"><?php echo formatea_monto_fromWs($totales_inversiones['SUMAVB']); ?> </td>
			</tr>
		</tfoot>
	</table>
<?php } ?>

<?php if (!empty($lineasCredito)) { ?>
	<div style="clear:both;">
		<h2 class="user">L?neas de Cr?dito</h2>
		<div class="line_title_orange"></div>
	</div>

	<table class="tabla" cellpadding="0" cellspacing="0" align="center">
		<thead>
			<tr>
				<td>Descripci?n</td>
				<td>N&uacute;mero</td>
				<td>Monto Aprobado Bs.</td>
				<td>Saldo Disponible Bs.</td>
				<td>Fecha de vencimiento</td>
			</tr>
		</thead>
		<?php
		if (!empty($lineasCredito)) {
			foreach ($lineasCredito as $lc) {
		?>
				<tbody>
					<tr>
						<td class="texto_centro">&nbsp;</td>
						<td class="texto_centro"><?php echo $lc['LN61LNENUM'] ?></td>
						<td class="monto"><?php echo formatea_monto_fromWs($lc['LN61LNEAMN']); ?></td>
						<td class="monto"><?php echo formatea_monto_fromWs($lc['LN61AVAILA']); ?></td>
						<td class="texto_centro"><?php echo formatea_fecha($lc['LN61LNEMT1'], $lc['LN61LNEMT2'], $lc['LN61LNEMT3']); ?></td>
					</tr>
				</tbody>
		<?php
			}
		}
		?>
	</table>
<?php } ?>
<br>
<br>
<!--<div class="azul"><strong><?php //echo _m('mensaje.petro.bcv'); ?></strong></div>-->
<br>
<div style="color:#FE7F00"><strong><?php echo _m('movimiento.mensaje'); ?></strong></div>