<?php
    /**
     *  Vista para el resultado de la eliminación de usuarios Nómina/Proveedores carga manual
     */
?>

<div>
<h2 class="user"><?php echo $titulo ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<?php if(!isset($error_fatal)) { ?>
<div class="contenedor_formulario">
<?php

	echo $form->create('AfiliacionCash',
		array(
			'url'=> array(
				'controller'=>'cash',
				'action'=>'afiliacion_eliminar'
			)
		)
	);
	
	echo $form->hidden('confirmado');
?>


<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
		<tr>
			<td class="resumen_paginacion" colspan="2">
				<p class="exito">Operación realizada satisfactoriamente</p>

				<p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF</p>
			</td>
		</tr>
	</thead>
	<tbody>
		<?php $k=0;
		foreach($afiliaciones as $i){
		if($k>0){
		?>
		<tr><td colspan="2" style="border:0 none">&nbsp;</td></tr>
		<?php } ?>
		<tr>
			<td class="etiqueta">Nombre Beneficiario:</td>
			<td><?php echo desencriptar($i['AfiliacionCash']['nombre']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Identificación Beneficiario:</td>
			<td><?php echo $i['AfiliacionCash']['tipo_cedula'].desencriptar($i['AfiliacionCash']['cedula']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Número Cuenta Beneficiario:</td>
			<td><?php echo desencriptar($i['AfiliacionCash']['numero_cuenta']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Banco Beneficiario:</td>
			<td><?php echo $bancos[substr(desencriptar($i['AfiliacionCash']['numero_cuenta']),0,4)] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">E-mail Beneficiario:</td>
			<td><?php echo desencriptar($i['AfiliacionCash']['email']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Número Teléfono Celular:</td>
			<td><?php echo desencriptar($i['AfiliacionCash']['celular']) ?></td>
		</tr>
		<?php } ?>
<?php
	echo '<tr><td colspan="2" class="botonera">';


			echo $html->link(
				'Volver',
				'manejo_usuarios/'.$estado['tipo'],
				array(
					'class'=>'button'
				)
			);

			echo '&nbsp;&nbsp;';

			echo $html->link(
				'Imprimir',
				'javascript:print();void(0);',
				array(
					'class'=>'button'
				)
			);

			echo '&nbsp;&nbsp;';

			echo $html->link(
				'Descargar PDF',
				'descarga_comprobante_eliminacion',
				array(
					'class'=>'button'
				)
			);


	?>
	</tbody>
</table>
</div>

<?php } ?>