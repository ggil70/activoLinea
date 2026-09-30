<?php
    /**
     *    Vista para la mostrar el listado de errores encontrados en la carga masiva de beneficiarios Nómina/Proveedores
     */
?>
<div>
<h2 class="user"><?php echo $titulo; ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class=" paso_nro_actual">2</div>
<div class="paso_desc_actual">Listado de Errores</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<table class="tabla" cellpadding="0" cellspacing="0" border="0">	
	<thead>
	<tr>
	<td colspan="4" class="resumen_paginacion">
	A continuación la lista de errrores encontrados en el archivo: <strong><?php echo $manejoErrores['archivo']; ?></strong>
	</td>
	</tr>
	<tr>
	<td>Registro</td>
	<td>Identificación</td>
	<td>Nombre</td>
	<td>Error</td>
	</thead>
	<tbody>
	<?php foreach($manejoErrores['errores'] as $i){ ?>	
	<tr>
		<td><?php echo $i['registro'] ?></td>
		<td><?php echo $i['identificacion'] ?></td>
		<td><?php echo $i['nombre'] ?></td>
		<td><?php echo $i['error'] ?></td>
	</tr>			
	<?php }	?>
	<tr>
	<td colspan="4" class="botonera"><?php 

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
				'Imprimir',
				'javascript:print();void(0);',
				array(
					'class'=>'button'
				)
			);
			
			echo '&nbsp;&nbsp;';

			echo $html->link(
				'Descargar PDF',
				'descarga_carga_masiva_error',
				array(
					'class'=>'button'
				)
			);
			
		?>
	</td>
	</tbody>
	</table>
    
        <div>
        <p align="left"><b>NOTA:</b><br/>
            Estimado Cliente, por favor realice la corrección de los errores que presenta el archivo y vuelva a subir el mismo.
        </p>
        </div>
</div>