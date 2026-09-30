<?php
    /**
     *  Vista para la selección de los parámetros que se tomarán en cuenta al realizar una exportación de histórico de transaciones
     */
?>
<!--
    
-->

<div>
<h2 class="user">Exportar</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario">

<?php

echo $form->create('HistoricoTransacciones',
	array(
		'url'=> array(
			'controller'=>'consultas',
			'action'=>'historico_exportar'
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
		'align'=>'center'
	)
);

echo $form->hidden('bandera');

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
			'd'=>'Descripción',
			'r'=>'Referencia',
			'm'=>'Monto',
		)
	)
);

echo '<tr><td>&nbsp;</td><td>'
	.'<label for="data[HistoricoTransacciones][campos][]" class="error manual">'. _m('seleccion.requerida','los campos a exportar') .'</label>';
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
			'p'=>'PDF'
		),
		'empty'=>'Seleccione',
		'class'=>'tipoFormato'
	)
);

if ($form->isFieldError('formato')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('formato').'</td></tr>';
}

echo $form->input(
	'configuraciones',
	array(
		'label'=>'<span class="estiloTexto"></span>Configuraciones adicionales:',
		'type'=>'select',
		'multiple'=>'checkbox',
		'options'=>array(
			'e'=>'Incluir encabezado de campos'
		)
	)
);

if ($form->isFieldError('configuraciones')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('configuraciones').'</td></tr>';
}

echo $form->input(
	'formatoFecha',
	array(
		'label'=>'<span class="estiloTexto"/>Formato de fechas:',
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
		echo '<tr><td>&nbsp;</td><td>'.$form->error('formatoFecha').'</td></tr>';
}

echo $form->input(
	'formatoMonto',
	array(
		'label'=>'<span class="estiloTexto"/>Formato de montos:',
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
		echo '<tr><td>&nbsp;</td><td>'.$form->error('divisionCampos').'</td></tr>';
}

echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

echo '<tr><td class="submit" colspan="2">';

echo $html->link(
	'Volver',
	'#',
	array(
		'div'=>false,
		'class'=>'button',
		'id'=>'volver'
	)
);

echo '&nbsp;&nbsp;&nbsp;';

echo $html->link(
	'Aceptar',
	'#',
	array(
		'div'=>false,
		'class'=>'button envio_formulario'
	)
);

echo '</tr></td></table>';

echo $form->end();

?>

</div>

<form class="formulario_enlace" id="formulario_volver" method="post" action="<?php echo Router::url('/consultas/historico_transacciones/v') ?>">
</form>

<script>
$(function(){
	$('td.campo').first().sortable().find('div, div label').css('cursor','pointer');

	deshabilitaDivisionFormato();
	$('.tipoFormato').change(function(){ deshabilitaDivisionFormato(); });
	
	$('#volver').click(function(){
		$('#formulario_volver').submit();	
		return false;
	});
	
	$('#HistoricoTransaccionesHistoricoExportarForm').validate({
		rules:{
			'data[HistoricoTransacciones][campos][]':{
				required:true
			},
			'data[HistoricoTransacciones][formato]':{
				required:true
			},
			'data[HistoricoTransacciones][formatoFecha]':{
				required:{
					depends: function(e){
						return $('#HistoricoTransaccionesCamposF').is(':checked');
					}				
				}
			},
			'data[HistoricoTransacciones][formatoMonto]':{
				required:{
					depends: function(e){
						return $('#HistoricoTransaccionesCamposM').is(':checked');
					}				
				}
			},
			'data[HistoricoTransacciones][divisionCampos]':{
				required:true
			}
		},
		messages:{
			'data[HistoricoTransacciones][campos][]':{
				required:<?php echo _m('seleccion.requerida','los campos a exportar',true) ?>
			},
			'data[HistoricoTransacciones][formato]':{
				required:<?php echo _m('seleccion.requerida','el formato',true) ?>
			},
			'data[HistoricoTransacciones][formatoFecha]':{
				required:<?php echo _m('seleccion.requerida','el formato de fecha',true) ?>
			},
			'data[HistoricoTransacciones][formatoMonto]':{
				required:<?php echo _m('seleccion.requerida','el formato de monto',true) ?>
			},
			'data[HistoricoTransacciones][divisionCampos]':{
				required:<?php echo _m('seleccion.requerida','la división de campos',true) ?>
			}
		}
	});
});
</script>