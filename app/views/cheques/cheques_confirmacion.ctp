<?php
    /**
     *  Vista para la confirmación de la suspensión del cheque o los cheques de una chequera
-->
     */
?>

<div>
<h2 class="user">Suspender Cheques/Chequeras</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">Confirme los datos de la operaci&oacute;n que desea realizar:</td>
	</tr>
	</thead>
	<tr>
	<td class="etiqueta">Número de Cuenta:</td>
	<td><?php echo enmascara($this->data['Cheque']['cuenta']) ?></td>
	</tr>
	<tr>
	<td class="etiqueta">&Uacute;ltimos 6 dígitos del Cheque:</td>
	<td>
	<?php foreach($this->data['Cheque']['cheques'] as $numeroCheque){
	echo  $numeroCheque.' ';
	}?>
	</td>
	</tr>
	<tr>
	<td class="etiqueta">Motivo:</td>
	<td><?php
	if(empty($this->data['Cheque']['motivo'])) { 
		echo $motivos[$this->data['Cheque']['tipo_motivo']];
	} else {
		echo $this->data['Cheque']['motivo'];
	}	?></td>
	</tr>
	<tr>
	<td colspan="2" class="botonera">

	<?php 

	echo $html->link(
		'Volver',
		'/cheques/cheques/v',
		array(
			'class'=>'button'
		)
	);

	echo '&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'/cheques/cheques_resultado',
		array(
			'class'=>'button'
		)
	);

	?>

	</td>
	</table>
</div>
