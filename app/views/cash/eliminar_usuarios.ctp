<?php
    /**
     *  Vista para la eliminación de usuarios Nómina/Proveedores carga manual
     */
?>

<div>
<h2 class="user"><?php echo $titulo ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<?php

	echo $form->create('PagoCash',
		array(
			'url'=> array(
				'controller'=>'cash',
				'action'=>'eliminar_usuarios'
			)
		)
	);
	
	echo $form->hidden('confirmado');
?>

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
		<td class="resumen_paginacion" colspan="2">Confirme los datos de la operación que desea realizar:</td>
	</tr>
	</thead>
	<tbody>
		<?php
		$k=0;
		foreach($afiliaciones as $i){
			if($k++>0){
		?>
		<tr><td colspan="2" style="border:0 none">&nbsp;</td></tr>
		<?php } ?>
		<tr>
			<td class="etiqueta">Nombre Beneficiario:</td>
			<td><?php echo desencriptar($i['AfiliacionCash']['nombre']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Identificación Beneficiario:</td>
			<td><?php  echo $i['AfiliacionCash']['tipo_cedula'].'-'.desencriptar($i['AfiliacionCash']['cedula'])?></td>
		</tr>
		<tr>
			<td class="etiqueta">Número Cuenta Beneficiario:</td>
			<td><?php echo desencriptar($i['AfiliacionCash']['numero_cuenta']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Banco Beneficiario:</td>
			<td><?php echo $bancos[substr(desencriptar($i['AfiliacionCash']['numero_cuenta']),0,4)]  ?></td>
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
		$estado['url_volver'],
		array(
			'class'=>'button',
			'id'=>'volver'
		)
	);
	
	echo '&nbsp;&nbsp;&nbsp;';
	
	
	echo $html->link(
		'Aceptar',
		'javascript:void(0);',
		array(
			'class'=>'button envio_formulario',
			'div' => false
		)
	);
	echo '</td></tr></table>';

	echo $form->end();

	?>
</div>