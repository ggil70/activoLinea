<?php
    /**
     *  Vista que muestra el resultado de carga masiva de beneficiarios Nómina/Proveedores
     */
?>
<div>
<h2 class="user"><?php echo $titulo; ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado Operaci&oacute;n</div>


<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">


<?php if($bandera){ ?>
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td class="resumen_paginacion">
					<p class="exito">Operación realizada satisfactoriamente</p>
					<p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF</p>				
				</td>
			</tr>
		</thead>
	</table>
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td class="etiqueta">Archivo:</td>
				<td><?php echo $data['archivo']?></td>
			</tr>
			<tr>
				<td class="etiqueta">Tipo Operación:</td>
				<td>Afiliación <?php echo $tipo['tipo']=='n' ? 'Nómina' : 'Proveedores' ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Cantidad de Registros:</td>
				<td><?php echo count($data['registros']);?></td>
			</tr>
			<tr>
				<td class="etiqueta">Fecha Operación:</td>
				<td><?php echo date('d/m/Y') ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Total Registros Agregados:</td>
				<td><?php echo $agregados ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Total Registros Modificados:</td>
				<td><?php echo $modificados ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Total Registros Eliminados:</td>
				<td><?php echo $eliminados ?></td>
			</tr>
			<tr>
				<td colspan="2" class="botonera"><?php 
			
				echo $html->link(
					'Volver',
					'/cash/manejo_usuarios/'.$tipo['tipo'],
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
					'/cash/descarga_carga_masiva_beneficiarios',
					array(
						'class'=>'button'
					)
				);
				?>	
				</td>
			</tr>				
		</tbody>
	</table>
<?php }else{
?>
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td class="resumen_paginacion texto_centro"><div class="error-message"><?php echo _m('operacion.fallida'); ?></div></td>
			</tr>
			<tr>
				<td class ="botonera">
					<?php 
					
						echo $html->link(
							'Volver',
							'/cash/carga_masiva_beneficiarios',
							array(
								'class'=>'button'
							)
						);
					
					?>
				</td>
			</tr>
		</thead>
	</table>

<?php }?>

</div>