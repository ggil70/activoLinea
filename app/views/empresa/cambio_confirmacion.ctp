<?php
/**
 * Confirmación de cambio de estatus de los usuarios
**/
?>
<div>
	<h2 class="user">Configuraci&oacute;n Usuarios - Cambiar Estatus</h2>
	<div class="line_title_orange"></div>
</div>
<div class="paso_nro">1</div>
<div class="paso_desc">Cambio Estatus</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confimaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>


<div class="contenedor_formulario">
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0" align="center">
	<thead>
		<tr>
			<td colspan="2" class="resumen_paginacion"><p>Confirme los datos de la operaci&oacute;n que desea realizar</p></td>
		</tr>
	</thead>
		<tr>
			<td class="etiqueta">Identificaci&oacute;n del Usuario (login):</td>
			<td><?php echo $this->data['Usuario']['nomUsuario'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">C&eacute;dula de Identidad:</td>
			<td><?php echo $this->data['Usuario']['tipoCedulaRif'].'-'.$this->data['Usuario']['nroCedulaRif'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Nombre y Apellido:</td>
			<td><?php echo $this->data['Usuario']['nombre'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Cargo:</td>
			<td><?php echo $this->data['Usuario']['cargo'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Email:</td>
			<td><?php echo $this->data['Usuario']['emailPri']; ?></td>
		</tr>
		<tr>
			<td class="etiqueta">N&uacute;mero de Tel&eacute;fono Celular:</td>
			<td>
			<?php echo $this->data['Usuario']['codigoCelular'].$this->data['Usuario']['nroCelular']; ?>
			</td>
		</tr>
		<tr>
			<td class="etiqueta">Perfil:</td>
			<td><?php echo $perfiles[$this->data['Usuario']['perfil']] ?></td>
		</tr>
	<tr>
		<td class="etiqueta">Estatus:</td>
		<td><?php echo $estatus[$this->data['Usuario']['co_estado']]?></td>
	</tr>
	<?php

	echo '<tr><td colspan="2" class="botonera" >';

	echo $html->link(
		'Volver',
		'cambio_form',
		array(
			'class'=>'button',
			'id'=>'volver'
		)
	);
	
	echo '&nbsp;&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'cambio_resultado',
		array(
			'class'=>'button',
			'id'=>'aceptar'
		)
	);
	echo '</td></tr></table>';
	
	echo $form->end();

?>
