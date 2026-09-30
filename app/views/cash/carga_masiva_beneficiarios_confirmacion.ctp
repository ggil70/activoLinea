<?php
    /**
     *  Vista para la confirmación de carga masiva de beneficiarios Nómina/Proveedores
     */
?>
<div>
<h2 class="user"><?php echo $titulo; ?></h2>
<div class="line_title_orange"></div>
</div>
<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>
<div class="espacio_vertical_20"></div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">		
	<thead>
		<tr><td class="resumen_paginacion" colspan="2">A continuaci&oacute;n proceda a confirmar los datos de la afiliación que desea realizar.</td></tr>
	</thead>
	<tbody>
		<tr>
			<td class="etiqueta">Archivo:</td>
			<td><?php echo $estado['archivo']?></td>
		</tr>
		<tr>
			<td class="etiqueta">Tipo Operación:</td>
			<td>Afiliación <?php echo $tipo['tipo']=='n' ? 'Nómina' : 'Proveedores' ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Cantidad de Registros:</td>
			<td><?php echo count($estado['registros']);?></td>
		</tr>
		<tr>
			<td class="etiqueta">Fecha Operación:</td>
			<td><?php echo date('d/m/Y') ?></td>
		</tr>
		<tr>
			<td colspan="2" class="botonera"><?php 
	
				echo $html->link(
					'Volver',
					'/cash/carga_masiva_beneficiarios/',
					array(
						'class'=>'button',
						'id'=>'volver'
					)
				);
				
				echo '&nbsp;&nbsp;';
				
				echo $html->link(
					'Aceptar',
					'carga_masiva_beneficiarios_resultado',
					array(
						'class'=>'button'
					)
				);
			?>
			</td>
		</tr>
	</tbody>
	</table>
</div>




