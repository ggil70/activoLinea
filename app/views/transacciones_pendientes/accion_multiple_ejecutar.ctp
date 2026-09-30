<?php
/**
 * Mensaje resultado de la acción multiple ejecutada
**/





?>
<div>
<h2 class="user">Aprobaciones Pendientes - <?php echo ucwords($estado['accion']) ?></h2>
<div class="line_title_orange"></div>
</div>


<div>
	<?php for($i = 0; $i < count($estado['pasos']); $i++) { 
	$class = ($i==$estado['paso_final']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $estado['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>


<div class="contenedor_formulario"
	 <?php if (count($todas) == 1 && in_array($tipo_operacion[0],array('n','v')) && !$hubo_error){ ?>
		style="width:790px;"
	 <?php } else { ?>
		style="width:550px;"
	<?php } ?>
	>


<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
<thead>
<tr>
	<td class="resumen_paginacion">
		<?php if($hubo_error && count($todas)>1){ ?>
			Puede descargar el comprobante de las operaciones realizadas presionando la opción Descargar PDF
		<?php } else if(!$hubo_error){ 

			 if($tipo_operacion[0]=='n' || $tipo_operacion[0]=='v' || $tipo_operacion[0]=='x' || $tipo_operacion[0]=='m'){ ?>

			<p class="exito">Operación pendiente por procesar</p>

			 <p> Recuerde verificar el estatus final en la opción Seguimiento de Gestión.</p>
		<p>Puede descargar los comprobantes de las operaciones realizadas presionando la opci&oacute;n Descargar PDF</p>


			<?php } else { ?><p class="exito">Operación realizada satisfactoriamente</p>
			Puede descargar el comprobante de las operaciones realizadas presionando la opción Descargar PDF
				<?php } ?>



		<?php }?>
		
	</td>
</tr>
</thead>
</table>	
<?php
if(count($todas)==1){
	if($hubo_error){
		echo '<p class="error-message">'.$todas[0]['descripcion_error'].'</p>';
	}
	else{
		echo $this->element(
			'transaccion_detalle',
			array(
				'transaccion'=>$todas[0]
			)
		);
	}

        if (($tipo_operacion == 'rcd' || $tipo_operacion == 'scd'  ) && !$hubo_error) { ?>        
        <thead>
            <tr>
                <td colspan="2" class="resumen_paginacion">
                    <p class="exito">Para más información comuníquese al 121 desde su Digitel o al 04129121121</p>
                </td>
            </tr>
        </thead> 
<?php } 
} else {
	echo $this->element('transacciones_multiples', array(
		'agrupadas'=>$agrupadas,
		'mostrar_resultado'=>true
	));
}
?>
                    
<div class="espacio_vertical_20"></div>

<div class="botonera">
<?php

echo $html->link(
	'Volver',
	'index',
	array(
		'class'=>'button',
		'id'=>'volver'
	)
);


if(!$hubo_error || count($todas)>1){
	
	echo '&nbsp;&nbsp;&nbsp;';

	echo $html->link(
		'Imprimir',
		'javascript:print();',
		array(
			'class'=>'button',
			'id'=>'imprimir'
		)
	);

	echo '&nbsp;&nbsp;&nbsp;';

	echo $html->link(
		'Descargar PDF',
		'accion_multiple_descargar',

		array(
			'class'=>'button',
			'id'=>'descargar'						
		)
	);
}

?>
</div>

</div>

