<?php
    /**
     *  Vista utilizada para la búsqueda de movimientos P2P de cuentas
     */
?>

<div>
<h2 class="user">Búsqueda Avanzada Pago Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario">

<?php

echo $form->create('BusquedaCuenta',
	array(
		'url'=> array(
                    'controller'=>'pago_activo',
                    'action'=>'buscar'
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

echo $form->hidden('nro_cuenta');
echo $form->hidden('tipo', array("value"=>"CI"));

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

echo '<tr><td class="etiqueta"><span class="estiloTexto"/>Cuenta:</td><td>' . enmascara($this->data['DetalleProducto']['nro_cuenta']) . '</td></tr>';

echo '<tr><td class="etiqueta" colspan="2">';
echo $html->tag(
	'input',
	null,
	array(
	   'type'=>'radio',
	   'name'=>'data[BusquedaCuenta][tipoBusq]',
	   'value'=>'f',
	   'checked'=>true
	)
);
echo '&nbsp;Por Referencia';
echo '</td></tr>';

echo $form->input(
	'referencia',
	array(
		'label'=>'<span class="requerido">* </span>Referencia:',
		'type'=>'text',
		'class'=>'referencia numerico',
		'maxlength'=>'9'
	)
);

if ($form->isFieldError('referencia')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('referencia',array(
			'numeric'=>_m('formato.invalido','la referencia')
		)).'</td></tr>';
}


echo '<tr><td class="etiqueta" colspan="2">';
echo $html->tag(
	'input',
	null,
	array(
	   'type'=>'radio',
	   'name'=>'data[BusquedaCuenta][tipoBusq]',
	   'value'=>'t'
	)
);
echo '&nbsp;Por combinaci&oacute;n';
echo '</td></tr>';

echo $form->input(
	'periodo',
	array(
		'label'=>'<span class="requerido">* </span>Período:',
		'type'=>'select',
		'options'=>array(
			'a'=>'Mes actual',
			'n'=>'Mes anterior',
			'u'=>'Últimos 6 meses',
			'o'=>'Otro'
		),
		'empty'=>'Seleccione',
		'class'=>'combinacion',
		'disabled'=>true
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
		'dateFormat'=>'DMY',
		'selected'=>time(),
		'between'=>'</td><td style="width:250px;">',
		'minYear'=>date( 'Y', strtotime('-1 year') ),
		'maxYear'=>date( 'Y' )		
	)
);

if ($form->isFieldError('fechaDesde')){
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('fechaDesde').'</td></tr>';
}

echo $form->input(
	'fechaHasta',
	array(
		'label'=>'<span class="estiloTexto"/>Fecha hasta:',
		'type'=>'date',
		'timeFormat'=>null,
		'dateFormat'=>'DMY',
		'selected'=>time(),
		'between'=>'</td><td style="width:250px;">',
		'minYear'=>date( 'Y', strtotime('-1 year') ),
		'maxYear'=>date( 'Y' )
	)
);

if ($form->isFieldError('fechaHasta')){
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('fechaHasta').'</td></tr>';
}

echo '<tr><td class="etiqueta" colspan="2">';
echo $html->tag(
	'input',
	null,
	array(
	   'type'=>'radio',
	   'name'=>'data[BusquedaCuenta][tipoBusq]',
	   'value'=>'m'
	)
);
echo '&nbsp;Por monto';
echo '</td></tr>';

echo $form->input(
	'montoDesde',
	array(
                'label'=>'<span class="requerido">* </span>Monto desde:',		
		'type'=>'text',
		'class'=>'monto combinacion',
		'disabled'=>true,
		'maxlength' => '17',
	)
);

if ($form->isFieldError('montoDesde')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('montoDesde',array(
			'numerico'=>_m('formato.invalido','el monto')
		)).'</td></tr>';
}

echo $form->input(
	'montoHasta',
	array(
		'label'=>'<span class="requerido">* </span>Monto hasta:',
		'type'=>'text',
		'class'=>'monto combinacion',
		'disabled'=>true,
		'maxlength' => '17',
	)
);

if ($form->isFieldError('montoHasta')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('montoHasta',array(
			'numerico'=>_m('formato.invalido','el monto'),
			'comparacionMontos'=>_m('comparacion.montos',array('al monto hasta'))
		)).'</td></tr>';
}

echo '<tr><td colspan="2" style="border:0;"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';

echo '<tr><td colspan="2" class="submit">';

echo '<input type="button" class="button" value="Volver" id="volver">&nbsp;&nbsp;';

echo $form->submit(
	'Aceptar',
	array(
            'div'=>false,
            'class'=>'button',
            'id'=>'accept'
	)
);

echo '</td></tr>';

if(isset($this->data['DetalleProducto']['error']) && $this->data['DetalleProducto']['error']){
    echo '<tr><td colspan="2" class="texto_centro"><label class="error">No se encontraron resultados con la referencia indicada</label></td></tr>';
}

echo '</table>';

echo $form->end();

?>

<form class="formulario_enlace" id="formulario_volver" method="post" action="<?php echo Router::url('/pago_activo/detalle') ?>">
    <input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $this->data['BusquedaCuenta']['nro_cuenta']; ?>" />
</form>
	
</div>

<script type="text/javascript">
function acomodar(){
	$('label.error').remove();
	
	var tipoBusqueda = $('input[name="data[BusquedaCuenta][tipoBusq]"]:checked').val();
	
	switch(tipoBusqueda){
		case 'f':
			// Busqueda por referencia
			$('#BusquedaCuentaReferencia').removeAttr('disabled');
			$('#BusquedaCuentaPeriodo, select[name^="data[BusquedaCuenta][fecha"], #BusquedaCuentaMontoDesde, #BusquedaCuentaMontoHasta').attr('disabled','disabled');
			$('input[name="data[BusquedaCuenta][montoHasta]"]').val('');
			$('input[name="data[BusquedaCuenta][montoDesde]"]').val('');
			break;
		case 't':
			// Busqueda por combinación
			$('#BusquedaCuentaReferencia, #BusquedaCuentaMontoDesde, #BusquedaCuentaMontoHasta').attr('disabled','disabled');
			$('#BusquedaCuentaPeriodo').removeAttr('disabled');
			$('input[name="data[BusquedaCuenta][referencia]"]').val('');
                        $('input[name="data[BusquedaCuenta][montoHasta]"]').val('');
			$('input[name="data[BusquedaCuenta][montoDesde]"]').val('');
		
			var periodo = $('#BusquedaCuentaPeriodo').val();			

			if(periodo == 'o'){
				$('select[name^="data[BusquedaCuenta][fecha"],')
				.removeAttr('disabled');
			} else {
				$('select[name^="data[BusquedaCuenta][fecha"]')
				.attr('disabled','disabled');
			}			
			
			break;
                case 'm':
			// Busqueda por monto
			$('#BusquedaCuentaReferencia, #BusquedaCuentaPeriodo, select[name^="data[BusquedaCuenta][fecha"]').attr('disabled','disabled');
                        $('input[name="data[BusquedaCuenta][referencia]"]').val('');			
                        $('#BusquedaCuentaMontoDesde, #BusquedaCuentaMontoHasta').removeAttr('disabled');
			break;        
	}	
}

$(function(){
	acomodar();
	
	$('#BusquedaCuentaPeriodo').change(acomodar);
	$('input[name="data[BusquedaCuenta][tipoBusq]"]').change(acomodar);
	
	$('#volver').click(function(){
            $('#formulario_volver').submit();	
            return false;
	});	
	
	$('#BusquedaCuentaBuscarForm').validate({
		onkeyup:false,
		rules:{
			'data[BusquedaCuenta][periodo]':{
				required:true
			},
			'data[BusquedaCuenta][referencia]':{
				required: {
					depends:function(e){
						return $('input[name="data[BusquedaCuenta][tipoBusq]"]:checked').val()=='f';
					}
				},
				digits:true
			},
			'data[BusquedaCuenta][montoDesde]':{
                                required: {
					depends:function(e){
						return $('input[name="data[BusquedaCuenta][tipoBusq]"]:checked').val()=='m';
					}
				},
				numberVE:true
			},
			'data[BusquedaCuenta][montoHasta]':{
                                required: {
					depends:function(e){
						return $('input[name="data[BusquedaCuenta][tipoBusq]"]:checked').val()=='m';
					}
				},
				numberVE:true,
				mayorIgual: '#BusquedaCuentaMontoDesde'
			},
			'data[BusquedaCuenta][fechaDesde][year]':{
				'fechaMenorIgualQueFijo':'<?php echo date('m/d/Y') ?>'
			},
			'data[BusquedaCuenta][fechaHasta][year]':{
				'fechaMayorIgualQue':'#BusquedaCuentaFechaDesdeYear',
				'fechaMenorIgualQueFijo':'<?php echo date('m/d/Y') ?>'
			}
		},
		messages:{
			'data[BusquedaCuenta][periodo]':{
				required:<?php echo _m('seleccion.requerida','el período',true) ?>
			},
			'data[BusquedaCuenta][referencia]':{
				required:<?php echo _m('campo.requerido','la referencia',true) ?>,
				digits:<?php echo _m('formato.invalido','la referencia',true) ?>
			},
			'data[BusquedaCuenta][montoDesde]':{
                                required:<?php echo _m('campo.requerido','el monto',true) ?>,
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>
			},
			'data[BusquedaCuenta][montoHasta]':{
                                required:<?php echo _m('campo.requerido','el monto',true) ?>,
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				mayorIgual:<?php echo _m('comparacion.montos','al monto hasta',true) ?>
			},
			'data[BusquedaCuenta][fechaDesde][year]':{
				'fechaMenorIgualQueFijo':<?php echo _m('fecha.menor.igual.actual',null,true) ?>
			},
			'data[BusquedaCuenta][fechaHasta][year]':{
				'fechaMayorIgualQue':<?php echo _m('fecha.mayor.igual.que',null,true) ?>,
				'fechaMenorIgualQueFijo':<?php echo _m('fecha.menor.igual.actual',null,true) ?>
			}
		}
	});	
});
</script>