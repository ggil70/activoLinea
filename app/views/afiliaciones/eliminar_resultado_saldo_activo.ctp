<?php
/**
 *  Vista para el resultado de las afiliaciones de pago activo
 */
?>

<div>
<h2 class="user">Afiliaciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado Operaci&oacute;n</div>
<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
        <?php if(!$this->data['Error']){?>
                <thead>
		<tr>
		<td colspan="2" class="resumen_paginacion">
                    <p class="exito">Operaci&oacute;n realizada satisfactoriamente</p>
                </td>
		</tr>
		</thead>				
		<tr>
			<td class="etiqueta">Tipo de Afiliaci&oacute;n:</td>
			<td>Saldo Activo</td>
		</tr>
		<tr>
			<td class="etiqueta">Cuenta:</td>
			<td><?php echo $this->Activo->enmascara($this->data['Afiliacion']['numero_cuenta']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Tarjeta / Cuenta Cash:</td>
			<td><?php 
			if (substr($this->data['Afiliacion']['numero_tarjeta'],10,1) !=  " ") {
				echo $this->Activo->enmascara($this->data['Afiliacion']['numero_tarjeta']);
			}else{
				echo $this->Activo->enmascara(substr($this->data['Afiliacion']['numero_tarjeta'], 0,9)); 
			}
			?></td>
		</tr>	
	<?php
	}
	else{
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('operacion.fallida'); ?></div></td>
		</tr>
		</thead>
	<?php 
	}
	?>
	<tr>
		<td colspan="2" class="botonera">

		<?php 		
			echo $html->link(
				'Volver',
				'/afiliaciones/listado_saldo_activo',
				array(
					'class'=>'button',
					'id'=>'volver'
				)
			);
		?>
		</td>
	</tr>
	</table>
</div>




