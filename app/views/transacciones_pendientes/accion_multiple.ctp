<?php
/**
 * Confirmación de la acción a ejecutar sobre las transacciones seleccionadas
**/
?>
<div>
<h2 class="user">Aprobaciones Pendientes - <?php echo ucwords($estado['accion']) ?></h2>
<div class="line_title_orange"></div>
</div>


<div>
	<?php for($i = 0; $i < count($estado['pasos']); $i++) { 
	$class = ($i==1) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $estado['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario"
	 <?php if (count($todas) == 1 && in_array($tipo_operacion[0],array('n','v'))){ ?>
		style="width:790px;"
	 <?php } else { ?>
		style="width:550px;"
	<?php } ?>
	>

<?php

echo $form->create(
	'TransaccionPendiente',
	array(
		'url'=> array(
			'controller'=>'transacciones_pendientes',
			'action'=>'accion_multiple'
		)
	)
);

?>

<input type="hidden" name="data[accion]">

<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
<thead>
<tr>
	<td class="resumen_paginacion" colspan="2">
	Confirme los datos de la operación que desea realizar:
	</td>
</tr>
</thead>
</table><?php 

if(count($todas)==1){
	echo $this->element(
		'transaccion_detalle',
		array(
			'transaccion'=>$todas[0]
		)
	);
} else {
	echo $this->element('transacciones_multiples', compact('agrupadas'));
}

?>

<div class="espacio_vertical_20"></div>

<div class="botonera">
<?php
echo $html->link(
	'Volver',
	$estado['ruta_regreso'],
	array(
		'class'=>'button',
		'id'=>'volver'
	)
);

echo '&nbsp;&nbsp;&nbsp;';

echo $html->link(
	'Aceptar',
	'accion_multiple_confirmar',
	array(
		'class'=>'button boton_accion',
		'id'=>'aceptar'						
	)
);

?>
</div>


<?php $form->end() ?>

</div>

<div class="basic-modal-content" id="procesandoA">
	<h3>Procesando...</h3>
	<p><?php echo(_m('procesando.transaccion','el pago solicitado')); ?></p>
</div>

<script type="text/javascript">

$(function(){

	$('#aceptar').click(function(){
           var tipo = '<?php echo $todas[0]['TransaccionPendiente']['tipo'][0] ?>';
           var accion = '<?php echo ucwords($estado['accion']) ?>';
           if(tipo == 'i' && accion != "Eliminar" && accion != "Rechazar" ){
               $('#procesandoA').modal({minHeight:120,minWidth:400,close:false});
           }
	});
});
</script>