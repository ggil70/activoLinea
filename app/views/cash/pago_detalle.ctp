<?php
    /**
     *  Vista que muestra el detalle del Pago Nómina/Proveedores
     */
?>

<div>
<h2 class="user"><?php echo $titulo ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style="width:800px;">
<?php

echo $form->create('PagoCash',
	array(
		'url'=> array(
			'controller'=>'cash',
			'action'=>'pago_detalle',
		)
	)
);

?>

<input type="hidden" name="data[accion]">

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
		<td class="resumen_paginacion" colspan="2">A continuación proceda a seleccionar la operación que desea realizar:</td>
	</tr>
	</thead>	
	<tbody>
		<tr>
			<td class="etiqueta">Tipo Operación:</td>
			<td><?php switch($estado['tipo']){
				case 'n':
					echo 'Pago Nómina';
					break;
				case 'p':
					echo 'Pago Proveedores';
					break;
			}?></td>
		</tr>
		<tr>
			<td class="etiqueta">Usuario Registro:</td>
			<td><?php echo desencriptar($usuario['nombre']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Cuenta Débito:</td>
			<td><?php echo $activo->enmascara($this->data['PagoCash']['cuentaDebitar']) ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Fecha Valor:</td>
			<td><?php echo $this->data['PagoCash']['fecha_valor']['day'].'/'.$this->data['PagoCash']['fecha_valor']['month'].'/'.$this->data['PagoCash']['fecha_valor']['year'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Descripción:</td>
			<td><?php echo $this->data['PagoCash']['descripcion'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Número Lote:</td>
			<td><?php echo $this->data['PagoCash']['lote'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Monto Bs.:</td>
			<td><?php echo $this->data['PagoCash']['monto_total'] ?></td>
		</tr>
	</tbody>
</table>

<div class="espacio_vertical_20"></div>
<div class="espacio_vertical_20"></div>


<table class="tabla" cellpadding="0" cellspacing="0" border="0" align="center" style="width:100%;">
<thead>
	<tr>
		<td>Identificación<br/>Beneficiario</td>
		<td style="width:160px;">Nombre <br/>Beneficiario</td>
		<td>Número Cuenta <br/>Beneficiario</td>
		<td>Banco<br/> Beneficiario</td>
		<td style="width:140px;">Monto Bs.</td>
	</tr>
</thead>
<tbody>
<?php
foreach($this->data['Beneficiarios'] as $x){
	$b = desencriptarCampos($x, array('nombre','cedula','numero_cuenta','email','celular'));
?>
<tr>
	<td><?php echo $b['tipo_cedula'].$b['cedula'] ?></td>
	<td><?php echo $b['nombre'] ?></td>
	<td class="texto_centro"><?php echo $b['numero_cuenta'] ?></td>
	<td><?php echo $bancos[substr($b['numero_cuenta'],0,4)] ?></td>
	<td class="monto"><?php echo formatea_monto_fromWs($b['monto']) ?></td>
</tr>
<?php } ?>
<tr>
	<td class="botonera" colspan="5">
		<?php 
		
		echo $html->link(
			'Volver',
			'pagos/'.$estado['tipo'],
			array(
				'class'=>'button',
				'id'=>'volver'
			)
		);
		
		echo '&nbsp;&nbsp;&nbsp;';	
		
		if($this->data['PagoCash']['vencida']){
			echo $html->link(
				'Procesar',
				'javascript:',
				array(
					'id'=>'noprocesar',
					'class'=>'button'
				)
			);
		} else {
			echo $html->link(
				'Procesar',
				'javascript:',
				array(
					'id'=>'procesar',
					'class'=>'button boton_accion'
				)
			);
		}
		
		echo '&nbsp;&nbsp;&nbsp;';
		
		echo $html->link(
			'Modificar',
			'javascript:',
			array(
				'id'=>'modificar',
				'class'=>'button boton_accion'
			)
		);
		
		echo '&nbsp;&nbsp;&nbsp;';
		
		echo $html->link(
			'Eliminar',
			'javascript:',
			array(
				'class'=>'button boton_accion',
				'id'=>'eliminar'						
			)
		);
		
		?>

	</td>
</tr>
</tbody>
</table>
<?php


echo $form->end();

?>
</div>


<div class="basic-modal-content" id="ayuda_noprocesar">
	<h3>Transacción vencida</h3>
	<p>Estimado Cliente, la fecha valor es inválida, por favor verifique e intente de nuevo.</p>
</div>

<script>
$(function(){
	$('#noprocesar').click(function(){
		$("#ayuda_noprocesar").modal({minHeight:100,minWidth:400})
	});
});
</script>