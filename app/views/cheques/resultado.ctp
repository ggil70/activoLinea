<?php
    /**
     *  Vista para el resultado de la suspensión cheque o rango de cheques
     */
?>

<div>
<h2 class="user">Suspensi&oacute;n de Cheques</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n de los Datos</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>
<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style= "width:500px">
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0" style="width:550px;margin:0 auto">
	<?php if($error == 0){ ?>
	<thead>
		<tr>
			<td class="resumen_paginacion"  colspan="2">
				<?php if($pendiente){ ?>
					<p class="exito">Operación pendiente por ejecutar.</p>
				<?php } else { ?>
					<p class="exito">Operación realizada satisfactoriamente</p>
					<p>Estimado Cliente, cheque suspendido exitosamente</p>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td class="etiqueta" style="width:50%">Número de Cuenta:</td>
			<td><?php echo enmascara($this->data['Cheque']['cuenta']) ?></td>
		</tr>
		<?php if($flagCheques){ ?>
			<tr>
				<td class="etiqueta">&Uacute;ltimos 6 dígitos del Primer Cheque:</td>
				<td><?php echo str_pad($this->data['Cheque']['chqInicial'],8,'0',STR_PAD_LEFT); ?></td>
			</tr>
			<tr>
				<td class="etiqueta">&Uacute;ltimos 6 dígitos del &Uacute;ltimo Cheque:</td>
				<td><?php echo str_pad($this->data['Cheque']['chqFinal'],8,'0',STR_PAD_LEFT); ?></td>
			</tr>
		<?php } else {?>
			<tr>
				<td class="etiqueta">Últimos 6 dígitos del Cheque:</td>
				<td><?php echo str_pad($this->data['Cheque']['cheque'],8,'0',STR_PAD_LEFT); ?></td>
			</tr>
		<?php }?>
		<tr>
			<td class="etiqueta">Motivo:</td>
			<td><?php
				if(empty($this->data['Cheque']['motivo'])) { 
					echo $motivos[$this->data['Cheque']['tipo_motivo']];
				} else {
					echo $this->data['Cheque']['motivo'];
				}	?>
			</td>
		</tr>
		<?php if(!empty($referencia)){ ?>
			<tr>
				<td class="etiqueta">Número de Referencia:</td>
				<td><?php echo $referencia ?></td>
			</tr>
		<?php } ?>
	</thead>
	<?php } else if($error == 2) { ?>
	<thead>
		<tr>
			<td class="resumen_paginacion texto_centro" ><div class="error-message"><?php echo _m('no.pago', 'número de cheque'); ?></div></td>
		</tr>
	</thead>
	<?php } else if($error == 1) { ?>
	<thead>
		<tr>
			<td class="resumen_paginacion texto_centro" ><div class="error-message"><?php echo _m('valor.invalido', 'el número de cheque'); ?></div></td>
		</tr>
	</thead>
	<?php
	}
	else{ ?>
		<thead>
			<tr>
				<td class="resumen_paginacion texto_centro" ><div class="error-message"><?php echo _m('operacion.fallida'); ?></div></td>
			</tr>
		</thead>
	<?php
	}
	
	if($error != 0){
	?>
		<tr>
			<td class="botonera" style="padding-right: -50px;">
			<?php 

				echo '&nbsp;&nbsp;';

				echo $html->link(
					'Volver',
					'/cheques',
					array(
						'class'=>'button'
					)
				);
			?>
			</td>
		</tr>
	<?php } ?>
	</table>
</div>




