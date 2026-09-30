<?php
/**
 * Formulario de recarga simple tv prepago confirmacion de datos
**/
?>
<div>
<h2 class="user">Recarga Simpletv Prepago</h2>
<div class="line_title_orange"></div>
</div>
<div>
	<?php for($i = 0; $i < count($this->data['pasos']); $i++) { 
	$class = ($i==1) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $this->data['pasos'][$i] ?></div>
	<?php } ?>
</div>
<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<?php
		if($error == 0){
	?>
	<thead>
	<tr>
		<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
		<td class="etiqueta">Cuenta de D&eacute;bito:</td>
		<td><?php echo enmascara($this->data['RecargaSimpletv']['cuentaDebitar']) ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Tipo de Operación:</td>
		<td><?php echo $this->data['RecargaSimpletv']['operacion'] ?></td>
	</tr>
		<?php if(!empty($servicios['r'][$this->data['RecargaSimpletv']['tipoServicio']])){ ?>
        <tr>
		<td class="etiqueta">Tipo de Servicio:</td>
		<td><?php echo $servicios['r'][$this->data['RecargaSimpletv']['tipoServicio']] ?></td>
	</tr>
<?php } ?>
	<tr>
		<td class="etiqueta">N&uacute;mero de Tarjeta de Acceso:</td>
		<td><?php echo $this->data['RecargaSimpletv']['numeroContrato'] ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Nombre del Beneficiario:</td>
		<td><?php echo $this->data['RecargaSimpletv']['nombreBeneficiario'] ?></td>
	</tr>
	<tr>
		<td class="etiqueta">Monto:</td>
		<td><?php echo $this->data['RecargaSimpletv']['montoFinal'] ?></td>

		
	</tr>

	<tr>
		<td class="etiqueta">Descripción:</td>
		<td><?php echo $this->data['RecargaSimpletv']['descripcion'] ?></td>

		
	</tr>


	<tr>
		<td colspan="2" class="botonera">

			<?php 			
                echo $html->link(
                  'Volver',
                  '/recarga_simpletv/recarga_simpletv',
                  array(
                    'class'=>'button bloqueo_repetir'
                  )
                );

                echo '&nbsp;&nbsp;';
                echo $html->link(
                    'Aceptar',
                    '/recarga_simpletv/recarga_simpletv_resultado',
                    array(
                        'class'=>'button bloqueo_repetir'
                    )
                );
			?>

		</td>
	</tr>
	<?php 
	}else if($error == 9996){
?>
	<thead>
	<tr>
	<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('servicio.nocomunicacion'); ?></div></td>
	</tr>
	</thead>
<?php 
}
	else{
	?>
	<thead>
	<tr>
		<td class="resumen_paginacion texto_centro">
			<div class="error-message"><?php echo _m('error.fatal'); ?></div>
		</td>
	</tr>
	</thead>
	<?php 
	}if($error != ''){
	?>
		<tr>
			<td colspan="2" class="botonera"  align="right">
				<?php
					echo $html->link(
						'Volver',
						'/recarga_simpletv/afiliados',
						array(
							'class'=>'button bloqueo_repetir'
						)
					);
				?>
			</td>
		</tr>
	<?php } ?>
	</table>
</div>