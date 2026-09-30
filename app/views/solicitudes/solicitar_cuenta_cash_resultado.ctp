<?php
/**
 * Resultado de transferencias a cuentas de terceros en otros bancos
**/
?>
<div>
<h2 class="user">Solicitar Cuenta Cash</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">2</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro_actual">3</div>
<?php } ?>
<div class="paso_desc_actual">Resultado de Operación</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<?php if(!$this->data['Error']){?>
	<thead>
		<tr>
		<td colspan="2" class="resumen_paginacion">
                    <p class="exito">Operación realizada satisfactoriamente</p>
                </td>
		</tr>
		<tr>
			<td class="etiqueta">Nombre del Cliente:</td>
			<td><?php echo desencriptar($usuario['nombre']); ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Tipo de Cuenta:</td>
			<td><?php echo "CUENTA CORRIENTE CASH USD"; ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Cuenta:</td>
			<td><?php echo $cta_usd ?></td>
		</tr>
	<?php
	}
	else{
	?>
		<thead>
		<tr>
		<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo $descripcion; ?></div></td>
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
				'/consultas/integral',
				array(
					'class'=>'button',
					'id'=>'volver'
				)
			);
		?>
		</td>
	</tr>

	</thead>
</table>