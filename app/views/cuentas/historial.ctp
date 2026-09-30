<?php
    /** 
     *   Vista para el historial de los estados de cuenta de la cuenta seleccionada
     */
?>

<div>
<h2 class="user">Estado de Cuentas Históricos</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario">

<?php

echo $form->create(
	'DetalleProducto',
	array(
		'url'=> array(
			'controller'=>'cuentas',
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

echo $form->hidden('data_exportar', array('id'=>'data_exportar'));

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

echo '<tr><td colspan="2" class="mensaje">Seleccione el mes del estado de cuenta que desea consultar</td></tr>';

echo $form->input(
	'nro_cuenta',
	array(
		'label'=>'N&uacute;mero Cuenta:',
		'type'=>'select',
		'options'=>$cuentas,
		'empty'=>false
	)
);


echo $form->input(
	'fecha_edo_cuenta',
	array(
		'label'=>'Mes:',
		'type'=>'select',
		'options'=>$opciones,
		'between'=>'</td><td class="campo" id="fecha_edo_cuenta_wrap">',
		'empty'=>false
	)
);

echo '<tr><td colspan="2" class="espacio_vertical_10"></td></tr>';
echo '<tr><td colspan="2" class="submit">';

echo $html->link(
	'Volver',
	'#',
	array(
		'class'=>'button',
		'id'=>'volver'
	)
);

echo '&nbsp;&nbsp;';

echo $html->link(
	'Descargar Swift MT-940',
	'#',
	array(
		'class'=>'button',
		'id'=>'mt'
	)
);

echo '&nbsp;&nbsp;';

echo $html->link(
	'Descargar PDF',
	'#',
	array(
		'class'=>'button',
		'id'=>'pdf'
	)
);

echo '&nbsp;&nbsp;';

echo $html->link(
	'Exportar',
	'#',
	array(
		'class'=>'button',
		'id'=>'exportar'
	)
);

echo '</td></tr></table>';

echo $form->end();

?>

</div>

<div class="botonera">
<form class="formulario_enlace" id="formulario_volver" method="post" action="<?php echo Router::url('/cuentas/detalle') ?>">
<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta'] ?>" />
</form>
<form class="formulario_enlace" id="formulario_exportar" method="post" action="<?php echo Router::url('/cuentas/exportar_historico') ?>">
<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['DetalleProducto']['nro_cuenta']?>" />
</form>

</div>
<script>
$(function(){
    $('select[name$="[nro_cuenta]"]').change(function(){
		$.ajax({
			type: "GET",
			url: '<?php echo Router::url("/cuentas/meses_estado_cuenta/"); ?>' + $(this).val(),
			cache:false,
			beforeSend: function() {
				$('#fecha_edo_cuenta_wrap')
					.html('<?php echo 'Cargando... ' . $html->image('/img/ajax-loader_mini.gif'); ?>');
			},
			success: function(msg){
				$('#fecha_edo_cuenta_wrap')
					.html(msg);
			},
			error: function(msg){
				alert(<?php echo _m('error.fatal',null,true) ?>);
			}
		});
    });

	$('#volver').click(function(){
		$('#formulario_volver').submit();
		return false;
	});
	
	$('#exportar').click(function(){
		$('#data_exportar').val('V');
		$('#DetalleProductoHistorialForm').submit();
		return false;
	});
	
	$('#mt').click(function(){
		$('#data_exportar').val('M');
		$('#DetalleProductoHistorialForm').submit();
		return false;
	});
	
	$('#pdf').click(function(){
		$('#data_exportar').val('');
		$('#DetalleProductoHistorialForm').submit();
		return false;
	});
	
	// para la validacion
	$('#DetalleProductoHistorialForm').validate({
		rules:{
			'data[DetalleProducto][fecha_edo_cuenta]':{
				required:true
			}
		},
		messages:{
			'data[DetalleProducto][fecha_edo_cuenta]':{
				required:<?php echo _m('seleccion.requerida',array('la fecha'),true) ?>
			}
		}		
	});
	
});
</script>