<?php
    /**
     *  Vista para la eliminación de Pago Nómina/Proveedores
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
	
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0" style="width:450px;">
	<thead>
	<tr>
		<td class="resumen_paginacion" colspan="2">Confirme los datos de la operación que desea realizar:</td>
	</tr>
	</thead>
	<tbody>
		<?php foreach($pagos as $i){ ?>
		
		<tr>
			<td class="etiqueta">Tipo Operación:</td>
			<td><?php switch($estado['tipo']){
				case 'm':
					echo 'Pago Nómina Moneda Extranjera';
					break;
				case 'x':
					echo 'Pago Proveedores Moneda Extranjera';
					break;
			}?></td>
		</tr>
		<tr>
			<td class="etiqueta">Usuario Registro:</td>
			<td><?php echo desencriptar($usuario['nombre']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Cuenta Débito:</td>
			<td><?php echo $activo->enmascara($i['PagoCash']['cuentaDebitar']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Fecha Valor:</td>
			<td><?php echo $i['PagoCash']['fecha_valor']['day'].'/'.$i['PagoCash']['fecha_valor']['month'].'/'.$i['PagoCash']['fecha_valor']['year'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Descripción:</td>
			<td><?php echo$i['PagoCash']['descripcion'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Número Lote:</td>
			<td><?php echo$i['PagoCash']['lote'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Monto Bs.:</td>
			<td><?php echo formatea_monto_fromWs($i['PagoCash']['monto_total']) ?></td>
		</tr>
		<tr><td colspan="2" style="border:0 none">&nbsp;</td></tr>
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
		'pago_eliminar_resultado_me',
		array(
			'class'=>'button'
		)
	);
	echo '</td></tr>';

	?>
	</table>
</div>