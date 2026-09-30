<?php
    /**
     *  Vista utilizada para mostrar el histórico de movimientos del crédito seleccionado
     */
?>

<div>
<h2 class="user">Histórico</h2>
<div class="line_title_orange"></div>
</div>


<div class="contenedor_formulario">


<?php

echo $form->create('DetalleProducto',
	array(
		'url'=> array(
			'controller'=>'creditos',
			'action'=>'historial'
		),
		'inputDefaults'=>array(
			'div'=>false,
			'label'=>'etiqueta',
			'before'=>'<tr><td class="etiqueta">',
			'between'=>'</td><td class="campo">',
			'after'=>'</td></tr>',
			'error'=>false
		)
	)
);

echo $form->hidden('nro_credito');

echo $html->tag(
	'table',
	null,
	array(
		'class'=>'formulario',
		'cellpadding'=>'0',
		'cellspacing'=>'0',
		'border'=>'0',
		'align'=>'center'
	)
);

echo '<tr><td colspan="2">Seleccione las opciones para su b&uacute;squeda</td></tr>';

echo '<tr><td class="etiqueta"><span class="estiloTexto"/>Crédito:</td><td>' . $this->data['DetalleProducto']['nro_credito'] . '</td></tr>';

echo $form->input(
	'periodo',
	array(
		'label'=>'<span class="requerido">* </span>Período:',
		'type'=>'select',
		'options'=>array(
			'a'=>'Mes actual',
			'n'=>'Mes anterior',
			'c'=>'Año actual',
			'r'=>'Año anterior',
			't'=>'Todos los años',
			'o'=>'Otro'
		),
		'empty'=>'Seleccione'
	)
);

if ($form->isFieldError('periodo')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('periodo',array(
			'requerido'=>_m('seleccion.requerida','el período'),
			'valoresPermitidos'=>_m('seleccion.invalida','el período')
		)).'</td></tr>';
}

echo $form->input(
	'fechaDesde',
	array(
		'label'=>'<span class="estiloTexto"/>Fecha desde:',
		'type'=>'date',
		'timeFormat'=>null,
		'dateFormat'=>'MY',
		'selected'=>time(),
		'between'=>'</td><td>',
		'minYear'=>$anios[0],
		'maxYear'=>$anios[1]
	)
);

if ($form->isFieldError('fechaDesde')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('fechaDesde').'</td></tr>';
}

echo $form->input(
	'fechaHasta',
	array(
		'label'=>'<span class="estiloTexto"/>Fecha hasta:',
		'type'=>'date',
		'timeFormat'=>null,
		'dateFormat'=>'MY',
		'selected'=>time(),
		'between'=>'</td><td style="width:250px;">',
		'minYear'=>$anios[0],
		'maxYear'=>$anios[1]
	)
);

if ($form->isFieldError('fechaHasta')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('fechaHasta').'</td></tr>';
}

echo '<tr><td colspan="2" style="border:0;"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

echo '<tr><td colspan="2" class="submit">';


echo '<input type="button" class="button" value="Volver" id="volver">&nbsp;&nbsp;';

echo $form->submit(
	'Aceptar',
	array(
		'div'=>false,
		'class'=>'button'
	)
);

echo '</td></tr></table>';

echo $form->end();

?>

<form class="formulario_enlace" id="formulario_volver" method="post" action="<?php echo Router::url('/creditos/movimientos') ?>">
	<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo $this->data['DetalleProducto']['nro_credito'] ?>" />
</form>
	
</div>

<script type="text/javascript">
function acomodar(){
	var val = $('#DetalleProductoPeriodo').val();
	
	if(val == 'o'){
		$('select[name^="data[DetalleProducto][fecha"],')
		.removeAttr('disabled');
	} else {
		$('select[name^="data[DetalleProducto][fecha"]')
		.attr('disabled','disabled');
	}
}

$(function(){
	acomodar();
	
	$('#DetalleProductoPeriodo').change(acomodar);
	
	$('#volver').click(function(){
		$('#formulario_volver').submit();	
		return false;
	});
	
	$('#DetalleProductoHistorialForm').validate({
		rules:{
			'data[DetalleProducto][periodo]':{
				required:true
			},
			'data[DetalleProducto][fechaHasta][year]':{
				mesAnioMayorIgualQue: '#DetalleProductoFechaDesdeYear',
				mesAnioMenorIgualQueFijo:'<?php echo date('m/d/Y') ?>'
			}
		},
		messages:{
			'data[DetalleProducto][periodo]':{
				required:<?php echo _m('seleccion.requerida','el período',true) ?>
			},
			'data[DetalleProducto][fechaHasta][year]':{
				mesAnioMayorIgualQue: <?php echo _m('fecha.mayor.igual.que', NULL, true) ?>,
				mesAnioMenorIgualQueFijo:<?php echo _m('fecha.menor.igual.actual',null,true) ?>
			}
		}
	});	
});
</script>