<?php
    /**
     *   Vista para el resultado de la modificación de usuarios Nómina/Proveedores carga manual
     */
?>

<div>
<h2 class="user"><?php echo $titulo ?></h2>
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

<?php if(isset($estado) && isset($estado['cantidad_superada'])){ ?>
	
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td class="resumen_paginacion texto_centro" colspan="2"><div class="error-message"><?php echo _m('cantidad.afiliados.superada'); ?></div></td>
			</tr>
		</thead>
	</table>
	
<?php } else { ?>
	
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td class="resumen_paginacion" colspan="2"><p class="exito">Operación realizada satisfactoriamente</p></td>
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
				<td><?php echo $this->data['AfiliacionCash']['banco'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">E-mail Beneficiario:</td>
				<td><?php echo $this->data['AfiliacionCash']['email'] ?></td>
			</tr>
			<tr>
				<td class="etiqueta">Número Teléfono Celular:</td>
				<td><?php echo $this->data['AfiliacionCash']['codigoCelular'].$this->data['AfiliacionCash']['nroCelular'] ?></td>
			</tr>
			<tr><td colspan="2" class="botonera">
			<?php
			
				echo $html->link(
					'Volver',
					'/cash/manejo_usuarios/'.$this->data['AfiliacionCash']['tipo'],
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
					'descarga_comprobante_afiliacion',
					array(
						'class'=>'button'
					)
				);
			?>
			</td></tr>
		</tbody>
	</table>
<?php } ?>
</div>