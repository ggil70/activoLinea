<?php
    /**
     *  Vista para la confirmación de modificación de usuarios Nómina/Proveedores carga manual
     */
?>

<div>
<h2 class="user"><?php echo $titulo ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<?php

	echo $form->create('AfiliacionCash',
		array(
			'url'=> array(
				'controller'=>'cash',
				'action'=>'modificar_usuarios_confirmar'
			),
			'inputDefaults'=>array(
				'div'=>false,
				'label'=>'etiqueta',
				'before'=>'<tr><td class="etiqueta">',
				'between'=>'</td><td class="campo">',
				'after'=>'</td></tr>',
				'error'=>false
			)
		)
	);
	
	echo $form->hidden('id');
	echo $form->hidden('confirmado');
	
?>
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
		<td class="resumen_paginacion" colspan="2">Confirme los datos de la operación que desea realizar:</td>
	</tr>
	</thead>	
	<tbody>
		<tr>
			<td class="etiqueta">Nombre Beneficiario:</td>
			<td><?php echo $this->data['AfiliacionCash']['nombre'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Identificación Beneficiario:</td>
			<td><?php echo $this->data['AfiliacionCash']['tipo_cedula'].$this->data['AfiliacionCash']['cedula'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Número Cuenta Beneficiario:</td>
			<td><?php echo $this->data['AfiliacionCash']['numero_cuenta'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Banco Beneficiario:</td>
			<td><?php echo $bancos[substr($this->data['AfiliacionCash']['numero_cuenta'],0,4)] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">E-mail Beneficiario:</td>
			<td><?php echo $this->data['AfiliacionCash']['email'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Número Teléfono Celular:</td>
			<td><?php echo $this->data['AfiliacionCash']['codigoCelular'].$this->data['AfiliacionCash']['nroCelular'] ?></td>
		</tr>
<?php
	echo '<tr><td colspan="2" class="botonera">';

	echo $html->link(
		'Volver',
		'/cash/modificar_usuarios/v',
		array(
			'class'=>'button',
			'id'=>'volver'
		)
	);
	
	echo '&nbsp;&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'javascript:',
		array(
			'class'=>'button boton_accion'
		)
	);

	echo '</td></tr></tbody></table>';

	echo $form->end();

	?>