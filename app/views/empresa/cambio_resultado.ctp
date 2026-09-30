<?php
/**
 * Resultado del cambio de datos
**/
?>
<div>
<h2 class="user">Configuraci&oacute;n Usuarios - Cambiar Estatus</h2>
<div class="line_title_orange"></div>
</div>
<div class="paso_nro">1</div>
<div class="paso_desc">Cambio Estatus</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confimaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<?php 
if (!$operacion_exitosa){ 
	
	echo '<div class="error-message">'. _m('operacion.fallida') . '</div>';
	
	echo '<div class="botonera">';
	
		echo $html->link(
			'Volver',
			'/empresa/listado',
			array(
				'class'=>'button'
			)
		);
		
	echo '</div>';
}
else{
?>
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0" align="center">
	<thead>
		<tr>
			<td colspan="2" class="resumen_paginacion"><p class="exito">Operaci&oacute;n realizada satisfactoriamente</p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF </td>
		</tr>
	</thead>
		<tr>
			<td class="etiqueta">Identificaci&oacute;n del Usuario (login):</td>
			<td><?php echo $usuarioEmpresa['Usuario']['nomUsuario'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">RIF de la Empresa:</td>
			<td><?php echo $usuario['tipo_rif'].'-'.desencriptar($usuario['rif'])?></td>
		</tr>
		<tr>
			<td class="etiqueta">C&eacute;dula de Identidad:</td>
			<td><?php echo $usuarioEmpresa['Usuario']['tipoCedulaRif'].'-'.$usuarioEmpresa['Usuario']['nroCedulaRif'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Nombre y Apellido:</td>
			<td><?php echo $usuarioEmpresa['Usuario']['nombre'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Cargo:</td>
			<td><?php echo $usuarioEmpresa['Usuario']['cargo'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Email:</td>
			<td><?php echo $usuarioEmpresa['Usuario']['emailPri']; ?></td>
		</tr>
		<tr>
			<td class="etiqueta">N&uacute;mero de Tel&eacute;fono Celular:</td>
			<td>
			<?php echo $usuarioEmpresa['Usuario']['codigoCelular'].$usuarioEmpresa['Usuario']['nroCelular']; ?>
			</td>
		</tr>
		<tr>
			<td class="etiqueta">Perfil:</td>
			<td><?php echo $perfiles[$usuarioEmpresa['Usuario']['perfil']] ?></td>
		</tr>
	<tr>
		<td class="etiqueta">Estatus:</td>
		<td><?php echo $estatus[$usuarioEmpresa['Usuario']['co_estado']]?></td>
	</tr>
	
	
	<?php
	echo '<tr><td colspan="2" class="botonera" >';

		echo $html->link(
			'Imprimir',
			'javascript:print();void(0)',
			array(
				'class'=>'button'
			)
		);

		echo '&nbsp;&nbsp;';

		echo $html->link(
			'Descargar PDF',
			'/empresa/descargar_pdf_juridico_estatus',
			array(
				'class'=>'button'
			)
		);

	echo '</td></tr></table>';
	?>
</div>
<?php } ?>