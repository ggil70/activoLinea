<?php
    /*
     *  Vista que indica que existen errores en la carga masiva de beneficiarios Nómina/Proveedores
     */
?>
<div>
<h2 class="user"><?php echo $titulo; ?></h2>
<div class="line_title_orange"></div>
</div>


<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">


	<table class="tabla" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr><td class="texto_centro"><p><strong>Carga Beneficiario</strong></p>
	</td>
	</tr>
	</thead>
	<tr>
	<td colspan="2" class="resumen_paginacion error-message texto_centro"><center>Estimado Cliente, el archivo no ha sido cargado por presentar errores de forma o datos inválidos.<br>Presione el botón continuar para ver la lista de errores detectados</center></td>
	</tr>

	<tr>
	<td colspan="2" class="botonera">

	<?php 
	
		echo $html->link(
			'Volver',
			'/cash/carga_masiva_beneficiarios/'.$tipo,
			array(
				'class'=>'button',
				'id'=>'volver'
			)
		);
		
		echo '&nbsp;&nbsp;';
		
		echo $html->link(
			'Aceptar',
			'carga_masiva_beneficiarios_error',
			array(
				'class'=>'button'
			)
		);
	?>
	</td>
	</table>
</div>




