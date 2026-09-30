<?php
    /**
     *  Vista para mostrar el listado de Pago de Nómina/Proveedores
     */
?>

<div>
<h2 class="user"><?php echo $titulo ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style="width:750px;">
<?php

echo $form->create('PagoCash',
	array(
		'url'=> array(
			'controller'=>'cash',
			'action'=>'pagos',
			$tipo
		)
	)
);

echo $form->hidden('id');

?>

<input type="hidden" name="data[accion]">

<?php if(isset($pago_guardado)){ ?>
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0" align="center" style="width:100%;">
	<thead>
		<tr>
			<td colspan="2" class="resumen_paginacion">
				<?php if($pago_guardado){ ?>
				<p class="exito">Operación guardada satisfactoriamente</p>
				<p>Su pago ha sido guardado con &eacute;xito</p>
				<?php } else { ?>
				<p><div class="error-message">Error inesperado al tratar de guardar el pago</div></p>
				<?php } ?>
			</td>
		</tr>
	</thead>		
</table>
<?php } ?>

<p>A continuación se presentan los <?php echo $encabezado_vista ?> que han sido guardados por usted.</p>

<label for="data[seleccion][]" class="error manual"><?php echo _m('uno.seleccion.requerida','de los registros'); ?></label>

<table class="tabla" cellpadding="0" cellspacing="0" border="0" align="center" style="width:100%;">

<thead>
<tr>
<td class="texto_centro ancholibre"><span class="requerido">*</span></td>
<td>Fecha Valor</td>
<td>Lote</td>
<td>Resumen</td>
<td>Monto Bs.</td>
<td>Usuario Registro</td>
</tr>
</thead>

<tbody>
<?php 
if(count($listado)){
	foreach($listado as $p){
?>
		<tr>
			<td class="texto_centro ancholibre">
				<input type="checkbox" name="data[seleccion][]" value="<?php echo $p['TransaccionPendiente']['id'] ?>" />
			</td>
			<td class="texto_centro"><?php echo date('d/m/Y', strtotime($p['TransaccionPendiente']['fecha_hora_valor'])) ?></td>
			<td class="texto_centro"><a href="javascript:;" class="detalle" id="det<?php echo $p['TransaccionPendiente']['id'] ?>"><?php echo $p['TransaccionPendiente']['lote'] ?></a></td>
			<td class="descripcion">
				<table cellpadding="0" cellspacing="1" align="left" class="separacion">
					<tr><td class="vacio">Cuenta D&eacute;bito: <?php echo $activo->enmascara(desencriptar($p['TransaccionPendiente']['cuenta_origen']))?></td></tr>
					<tr><td class="vacio">Descripci&oacute;n: <?php echo desencriptar($p['TransaccionPendiente']['descripcion'])?></td></tr>
					<tr><td class="vacio">N&uacute;mero de Registros: <?php echo count($p['Beneficiarios']) ?></td></tr>
				</table>
			</td>
			<td class="monto"><?php echo formatea_monto_fromWs($p['TransaccionPendiente']['monto']) ?> </td>
			<td class="texto_centro"><?php echo desencriptar($p['UsuarioCreador']['login']) ?></td>
		</tr>

<?php } ?>

	<tr>
		<td colspan="6" class="paginador">
			<?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros,'tamano_paginacion'=>10)); ?>
		</td>
	</tr>

	<tr><td colspan="6" style="border: 0px;" ><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>
	
	<tr>
		<td colspan="6" class="botonera">

			<?php 
			
			echo $html->link(
				'Crear',
				'javascript:',
				array(
					'class'=>'button boton_accion',
					'id'=>'crear'
				)
			);
			
			echo '&nbsp;&nbsp;&nbsp;';
			
			echo $html->link(
				'Eliminar',
				'javascript:',
				array(
					'class'=>'button',
					'id'=>'eliminar'						
				)
			);
			?>

		</td>
	</tr>

<?php 
} 
else {
?>
	<tr><td colspan="6" class="texto_centro">No se encontraron resultados...</td></tr>
	<tr>
		<td colspan="6" class="botonera">

			<?php 
			echo $html->link(
				'Crear',
				'javascript:',
				array(
					'class'=>'button boton_accion',
					'id'=>'crear'
				)
			);
			?>

		</td>
	</tr>
<?php } ?>
	
</tbody>
</table>

<?php 

echo '<p align="left"><b>IMPORTANTE:</b><br/>
			Los lotes que se muestran en esta consulta se encuentran pendientes de confirmación para su procesamiento. Para ver el detalle del pago haga click en el número de lote. Esta opción le permite realizar un ajuste al lote antes de procesarlo.
</p>';

echo $form->end(); 

?>

</div>

<?php	
echo $form->create(
	'PagoCash',
	array(
		'url'=> array(
			'controller'=>'cash',
			'action'=>$this->action,
			$tipo
		),
		'id' => 'paginacion'
	)
);
if(!empty($this->data['PagoCash'])){
	foreach($this->data['PagoCash'] as $k => $v) {
		echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script type="text/javascript">

$(function(){
	
	$('.detalle').click(function(){
		var id = $(this)
		.attr('id')
		.replace(
			/det(\d+)$/,
			'$1'
		);
		
		$('input[name="data[PagoCash][id]"]').val(id);
		$('input[name="data[accion]"]').val('detalle');
		$('#PagoCashPagosForm').submit();
	});
	
	$('#eliminar').click(function(){
		if($('#PagoCashPagosForm').valid()){		
			$('input[name="data[accion]"]').val('eliminar');
			$('#PagoCashPagosForm').submit();
		}
	});
	
	$('#PagoCashPagosForm').validate({
		onsubmit:false,
		onkeyup:false,
		onclick:false,
		onfocusout:false,
		rules:{
			'data[seleccion][]':{
				required: true
			}
		}
	});	
});
</script>