<?php
/**
 * Formulario de exportación de estado de movimientos de tarjeta de crédito
**/
?>
<div>
<h2 class="user">Exportar</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario">

<?php

echo $form->create('ExportarTarjeta',
	array(
		'url'=> array(
			'controller'=>'tarjetas',
			'action'=>'exportar'
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

echo $form->hidden('nro_tarjeta');
echo $form->hidden('tipo');
echo $form->hidden('nro_tarjeta_padre');

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

echo '<tr><td class="etiqueta"><span class="estiloTexto"></span>Tarjeta:</td><td>' . enmascara($this->data['ExportarTarjeta']['nro_tarjeta']) . '</td></tr>';

echo $form->input(
	'campos',
	array(
		'label'=>'<span class="requerido">* </span>Campos a exportar:',
		'between'=>'
		<table class="aviso">
		<tbody>
		<tr>
		<td class="aviso_izq"></td>
		<td class="aviso_content">Arrastre los campos<br/>de acuerdo al orden<br/>deseado</td>
		<td class="aviso_der"></td>
		</tr>
		</tbody>
		</table>
		</td><td class="campo">',
		'type'=>'select',
		'multiple'=>'checkbox',
		'options'=>array(
			'f'=>'Fecha',
			'r'=>'Referencia',
			'd'=>'Descripción',
			'c'=>'Cargo',
			'a'=>'Abono'
		)
	)
);

echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'
	.'<label for="data[ExportarTarjeta][campos][]" class="error manual">'. _m('seleccion.requerida','los campos a exportar') .'</label>';
	if ($form->isFieldError('campos')){
		echo $form->error('campos',array(
			'valoresPermitidos'=>_m('seleccion.invalida','los campos a exportar')
		));
	}
echo '</td></tr>';

echo $form->input(
	'formato',
	array(
		'label'=>'<span class="requerido">* </span>Formato de archivo:',
		'type'=>'select',
		'options'=>array(
			't'=>'Texto',
			'e'=>'Excel',
			//'s'=>'Swift MT-940',
		),
		'empty'=>'Seleccione',
		'class'=>'tipoFormato'
	)
);

if ($form->isFieldError('formato')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('formato',array(
			'valoresPermitidos'=>_m('seleccion.invalida','el formato'),
			'requerido'=>_m('seleccion.requerida','el formato')
		)).'</td></tr>';
}

echo $form->input(
	'configuraciones',
	array(
		'label'=>'<span class="estiloTexto"></span>Configuraciones adicionales:',
		'type'=>'select',
		'multiple'=>'checkbox',
		'options'=>array(
			//'n'=>'Incluir nro. de tarjeta',
			'e'=>'Incluir encabezado de campos'
		)
	)
);

if ($form->isFieldError('configuraciones')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('configuraciones',array(
			'valoresPermitidos'=>_m('seleccion.invalida','las configuraciones')
		)).'</td></tr>';
}

echo $form->input(
	'formatoFecha',
	array(
		'label'=>'<span class="requerido">* </span>Formato de fechas:',
		'type'=>'select',
		'options'=>array(
			'dmY'=>'DD/MM/AAAA',
			'Ymd'=>'AAAA/MM/DD',
			'dmy'=>'DD/MM/AA',
			'ymd'=>'AA/MM/DD'
		),
		'empty'=>'Seleccione'
	)
);

if ($form->isFieldError('formatoFecha')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('formatoFecha',array(
			'valoresPermitidos'=>_m('seleccion.requerida','el formato de fecha'),
			'requerido'=>_m('seleccion.requerida','el formato de fecha')
		)).'</td></tr>';
}

echo $form->input(
	'formatoMonto',
	array(
		'label'=>'<span class="requerido">* </span>Formato de montos:',
		'type'=>'select',
		'options'=>array(
			'.,'=>'999.999,99',
			','=>'999999,99',
			',.'=>'999,999.99',
			'.'=>'999999.99'
		),
		'empty'=>'Seleccione'
	)
);

if ($form->isFieldError('formatoMonto')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('formatoMonto',array(
			'valoresPermitidos'=>_m('seleccion.requerida','el formato de monto'),
			'requerido'=>_m('seleccion.requerida','el formato de monto')
		)).'</td></tr>';
}

echo $form->input(
	'divisionCampos',
	array(
		'label'=>'<span class="requerido">* </span>División de campos:',
		'type'=>'select',
		'options'=>array(
			'f'=>'Longitud fija',
			';'=>'Punto y coma (;)',
			'tab'=>'Tabulador'
		),
		'empty'=>'Seleccione',
		'class'=>'divisionFormato'
	)
);

if ($form->isFieldError('divisionCampos')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('divisionCampos',array(
			'valoresPermitidos'=>_m('seleccion.requerida','la división de campos'),
			'requerido'=>_m('seleccion.requerida','la división de campos')
		)).'</td></tr>';
}

echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

echo '<tr><td class="submit" colspan="2">';

echo $html->link(
	'Volver',
	'#',
	array(
		'id'=>'volver',
		'class'=>'button'
	)
);

echo '&nbsp;&nbsp';

echo $html->link(
	'Aceptar',
	'#',
	array(
		'div'=>false,
		'class'=>'button envio_formulario'
	)
);

echo '</td></tr></table>';

echo $form->end();

?>

</div>

<?php if(!$this->Session->check('tarjetas.flagBusqueda')){ ?>
<form class="formulario_enlace" id="formulario_volver" method="post" action="<?php echo Router::url('/tarjetas/detalle') ?>">
<input type="hidden" name="data[DetalleProducto][nro_tarjeta]" value="<?php echo $this->data['ExportarTarjeta']['nro_tarjeta']; ?>" />
<input type="hidden" name="data[DetalleProducto][nro_tarjeta_padre]" value="<?php echo $this->data['ExportarTarjeta']['nro_tarjeta_padre']; ?>" />
<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $this->data['ExportarTarjeta']['tipo']; ?>" />
</form>
<?php
}
else{
	$parametros = $this->Session->read('tarjetas.busqueda');
	echo '<form class="formulario_enlace" id="formulario_volver" method="post" action="' . Router::url('/tarjetas/buscar') . '">';
	
	foreach($parametros['BusquedaCuenta'] as $key => $param){
		if(in_array($key,array('fechaDesde','fechaHasta'))){
			echo '<input type="hidden" name="data[BusquedaCuenta][' . $key . '][day]" value="' . $param['day'] . '" />';
			echo '<input type="hidden" name="data[BusquedaCuenta][' . $key . '][month]" value="' . $param['month'] . '" />';
			echo '<input type="hidden" name="data[BusquedaCuenta][' . $key . '][year]" value="' . $param['year'] . '" />';
		} else {
			echo '<input type="hidden" name="data[BusquedaCuenta][' . $key . ']" value="' . $param . '" />';
		}
		
		
	}
	
	echo '</form>';
}
?>

<script>
$(function(){
	$('td.campo').first().sortable().find('div, div label').css('cursor','pointer');
	
	deshabilitaDivisionFormato();
	$('.tipoFormato').change(function(){ deshabilitaDivisionFormato(); });
	
	$('#volver').click(function(){
		$('#formulario_volver').submit();	
		return false;
	});
	
	$('#ExportarTarjetaExportarForm').validate({
		rules:{
			'data[ExportarTarjeta][campos][]':{
				required:true
			},
			'data[ExportarTarjeta][formato]':{
				required:true
			},
			'data[ExportarTarjeta][formatoFecha]':{
				required:{
					depends: function(e){
						return $('#ExportarTarjetaCamposF').is(':checked');
					}				
				}
			},
			'data[ExportarTarjeta][formatoMonto]':{
				required:{
					depends: function(e){
						return $('#ExportarTarjetaCamposA,#ExportarTarjetaCamposC').is(':checked');
					}				
				}
			},
			'data[ExportarTarjeta][divisionCampos]':{
				required:true
			}
		},
		messages:{
			'data[ExportarTarjeta][campos][]':{
				required:<?php echo _m('seleccion.requerida','los campos a exportar',true) ?>
			},
			'data[ExportarTarjeta][formato]':{
				required:<?php echo _m('seleccion.requerida','el formato',true) ?>
			},
			'data[ExportarTarjeta][formatoFecha]':{
				required:<?php echo _m('seleccion.requerida','el formato de fecha',true) ?>
			},
			'data[ExportarTarjeta][formatoMonto]':{
				required:<?php echo _m('seleccion.requerida','el formato de monto',true) ?>
			},
			'data[ExportarTarjeta][divisionCampos]':{
				required:<?php echo _m('seleccion.requerida','la división de campos',true) ?>
			}
		}
	});	
});
</script>