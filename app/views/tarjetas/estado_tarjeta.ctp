<?php
/**
 * Formulario de descarga de estados de cuenta históricos de tarjetas de crédito
**/
?>
<div>
<h2 class="user">Estado de Cuentas Hist&oacute;rico</h2>
<div class="line_title_orange"></div>
</div>


<div class="contenedor_formulario">

<?php

echo $form->create('EstadoCuentaTdc',
	array(
		'url'=> array(
			'controller'=>'tarjetas',
			'action'=>'estado_tarjeta'
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

echo $html->tag(
	'table',
	null,
	array(
		'class'=>'formulario',
		'cellpadding'=>'0',
		'cellspacing'=>'0',
		'border'=>'0',
		'align'=>'center',
		'width'=>'400px'
	)
);

echo '<tr><td colspan="2" class="mensaje">Seleccione el mes del estado de cuenta de la tarjeta de cr&eacute;dito que desea consultar</td></tr>';

echo $form->input(
	'nro_tarjeta',
	array(
		'label'=>'N&uacute;mero Tarjeta:',
		'type'=>'select',
		'options'=>$tarjetas,
		'empty'=>'Seleccione'
	)
);

if ($form->isFieldError('nro_tarjeta')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('nro_tarjeta',array(
			'nro_tarjeta'=>_m('seleccion.requerida','el número de tarjeta')
		)).'</td></tr>';
}

echo $form->hidden('tipo');
echo $form->hidden('nro_tarjeta_padre');

echo $form->input(
	'mes',
	array(
		'label'=>'Mes:',
		'type'=>'select',
		'options'=>$meses,
	)
);

if ($form->isFieldError('mes')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('mes',array(
			'mes'=>_m('seleccion.requerida','el mes')
		)).'</td></tr>';
}

echo '<tr><td colspan="2" >&nbsp;</td></tr>';

echo '<tr><td colspan="2" class="submit">';

echo $html->link(
	'Volver',
	'javascript:void(0)',
	array(
		'class'=>'button',
		'id'=>'volver'
	)
);

echo '&nbsp;&nbsp;';

echo $html->link(
	'Descargar PDF',
	'#',
	array(
		'class'=>'envio_formulario button'
	)
);


echo '</td></tr></table>';

echo $form->end();

?>

</div>

<form class="formulario_enlace" id="formulario_volver" method="post" action="<?php echo Router::url($ruta_regreso) ?>">
<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['EstadoCuentaTdc']['nro_tarjeta']?>" />
<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo $this->data['EstadoCuentaTdc']['nro_tarjeta_padre']?>" />
<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $this->data['EstadoCuentaTdc']['tipo']?>" />
</form>

<script>
$(function(){
	$('#volver').click(function(){
		$('#formulario_volver').submit();	
		return false;
	});
	
	// para la validacion
	$('#EstadoCuentaTdcEstadoTarjetaForm').validate({
		rules:{
			'data[EstadoCuentaTdc][nro_tarjeta]':{
				required:true
			},
			'data[EstadoCuentaTdc][mes]':{
				required:true
			}
		},
		messages:{
			'data[EstadoCuentaTdc][nro_tarjeta]':{
				required:<?php echo _m('seleccion.requerida',array('el número de tarjeta'),true) ?>
			},
			'data[EstadoCuentaTdc][mes]':{
				required:<?php echo _m('seleccion.requerida',array('el mes'),true) ?>
			}
		}		
	});	
});
</script>