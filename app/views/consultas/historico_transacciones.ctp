<?php
    /**
     * Vista para mostrar el listado del histórico de transacciones
     */
?>

<div>
<h2 class="user">Histórico de Transacciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario">

<?php

echo $form->create('HistoricoTransacciones',
	array(
		'url'=> array(
			'controller'=>'consultas',
			'action'=>'historico_transacciones'
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
		'style'=>'width:500px;'
	)
);

if($master){
	echo $form->input(
		'perfiles',
		array(
			'label'=>'<span class="estiloTexto">&nbsp;Perfil:</span>',
			'type'=>'select',
			'options'=>$roles,
			'before'=>'<td class="etiqueta">',
		)
	);
}
?>

<tr><td style="width:50%;"></td><td></td></tr>
<tr><td colspan="2">
	<table class="tabla ancholibre" cellpadding="0" cellspacing="0" border="0" >
		<thead>
			<td style="width:45%;"><span class="requerido">* </span>Tipo Operaci&oacute;n</td>
			<td ><span class="requerido">* </span>Tipo Transacci&oacute;n</td>
		</thead>
		<tr>
			<td class="borde">
				<?php
				echo $form->input(
					'operacion',
					array(
						'type'=>'select',
						'options'=>$modulos,
						'empty'=>'Todas las operaciones',
						'id'=>'operacion',
						'label'=>false,
						'div'=>false,
						'before'=>false,
						'between'=>false,
						'after'=>false
					)
				);						
				?>
			</td>
			<td id="td_transaccion_wrapper" class="borde">
				<?php
				echo $form->input(
					'transaccion',
					array(
						'type'=>'select',
						'options'=>$transacciones,
						'empty'=>'Todas las transacciones',
						'id'=>'transaccion',
						'label'=>false,
						'div'=>false,
						'before'=>false,
						'between'=>false,
						'after'=>false
					)
				);						
				?>
			</td>
		</tr>
	</table>
</td></tr>
<tr><td colspan="2"></td></tr>
	
<?php
echo $form->input(
	'referencia',
	array(
		'label'=>'<span class="estiloTexto">&nbsp;Referencia:</span>',
		'type'=>'text',
		'maxlength'=>'9',
		'class'=>' numerico',
		'size'=>'30'
	)
);

if ($form->isFieldError('referencia')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('referencia', _m('valor.invalido',array('el n&uacute;mero referencia')) ).'</td></tr>';
}

echo $form->input(
	'monto',
	array(
		'label'=>'<span class="estiloTexto">&nbsp;Monto Bs.:</span>',
		'type'=>'text',
		'maxlength'=>'20',
		'class'=>'monto '
	)
);

if ($form->isFieldError('monto')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('monto', _m('formato.invalido',array('el monto'))).'</td></tr>';
}

$arregloP = array(
   'type'=>'radio',
   'name'=>'data[HistoricoTransacciones][tipo]',
   'value'=>'p'
);

if(isset($this->data['HistoricoTransacciones']['tipo']) && $this->data['HistoricoTransacciones']['tipo']=='p') $arregloP['checked'] = true;

echo '<tr><td class="etiqueta" colspan="2">';
echo $html->tag(
	'input',
	null,
	$arregloP
);
echo '&nbsp;Por periodo';
echo '</td></tr>';

echo $form->input(
	'fechaDesde',
	array(
		'label'=>'<span class="estiloTexto"/>Fecha desde:',
		'type'=>'date',
		'timeFormat'=>null,
		'dateFormat'=>'DMY',
		'between'=>'</td><td style="width:250px;">',
		'minYear'=>date( 'Y', strtotime('-1 year') ),
		'maxYear'=>date( 'Y' )
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
		'dateFormat'=>'DMY',
		'between'=>'</td><td style="width:250px;">',
		'minYear'=>date( 'Y', strtotime('-1 year') ),
		'maxYear'=>date( 'Y' )
	)
);

if ($form->isFieldError('fechaHasta')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('fechaHasta').'</td></tr>';
}

$arregloT = array(
   'type'=>'radio',
   'name'=>'data[HistoricoTransacciones][tipo]',
   'value'=>'t'
);
		
if(isset($this->data['HistoricoTransacciones']['tipo']) && $this->data['HistoricoTransacciones']['tipo']=='t') $arregloT['checked'] = true;
			
echo '<tr><td class="etiqueta" colspan="2">';
echo $html->tag(
	'input',
	null,
	$arregloT
);
echo '&nbsp;Todos los meses';
echo '</td></tr>';

echo $form->input(
	'mes_transaccion',
	array(
		'label'=>'<span class="estiloTexto">&nbsp;Mes:</span>',
		'type'=>'select',
		'options'=>$meses,
		'between'=>'</td><td class="campo" id="fecha_edo_cuenta_wrap">',
		'empty'=>'Últimos 12 meses',
		'disabled'=>true
	)
);

echo '<tr><td colspan="2" class="submit">';

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

<?php if(isset($resultados)) { ?>

<table class="tabla" cellpadding="0" cellspacing="0" style="margin:0 auto;" border="0" style="width:100%">
<thead> 
	<tr>
	<td colspan="4" class="resumen_paginacion"><?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?></td>
	</tr>
</thead>
<?php if($total_registros>0) { ?>
<thead> 
<tr>
<td>Fecha/Hora</td>
<td>Referencia</td>
<td>Descripci&oacute;n</td>
<td>Monto Bs.</td>
<?php if($master){ ?>
	<td>Login<br>Usuario</td>
<?php }?>
</tr>
</thead>
<tbody>
<?php foreach($resultados as $i){?>
<tr>
<td class="texto_centro" width="90px"><?php echo date('d/m/Y h:i:s a', strtotime($i['Bitacora']['fecha_hora']))?></td>
<td class="texto_centro">
	<?php 
	$referencia = strval(desencriptar($i['Bitacora']['referencia']));
        
	if($referencia){
                
                $array_tipo = array (
			7 => 'WD', 8 => 'WD', 100021 => 'WD', 100022 => 'WD', 100023 => 'WD', 100024 => 'WD',
			10 => 'CI', 11 => 'CI', 100025 => 'WD', 100026 => 'WD', 100027 => 'WD', 100028 => 'WD',
			100014=>'PS', 100015=>'PS', 100036=>'PI', 100037=>'PI', 100038=>'AE', 100039=>'AE',
                        100058=>'WD', 100059=>'WD'                        
		);
		if($array_tipo[$i['Bitacora']['transaccion_id']] == 'AE'){
                    $cuenta = desencriptar($i['Bitacora']['cuenta_destino']);
                    $referencia = str_pad($referencia,9,'0',STR_PAD_RIGHT);
                }
                else 
                    $cuenta = desencriptar($i['Bitacora']['cuenta_origen']);
		
		if( $i['mostrar_enlace'] && array_key_exists($i['Bitacora']['transaccion_id'], $array_tipo) ){
                    
                    
	?>
		<form class="formulario_enlace" method="post" action="<?php echo Router::url('/cuentas/movimiento') ?>">
			<input type="hidden" name="data[DetalleProducto][nro_cuenta]" value="<?php echo $cuenta ?>" />
			<input type="hidden" name="data[DetalleProducto][referencia]" value="<?php echo $referencia ?>" />
			<input type="hidden" name="data[DetalleProducto][tipo]" value="<?php echo $array_tipo[$i['Bitacora']['transaccion_id']] ?>" />
                        <input type="hidden" name="data[DetalleProducto][monto]" value="<?php echo formatea_monto_fromWs($i['Bitacora']['monto']) ?>" />
			<input type="hidden" name="data[DetalleProducto][flagHT]" value="H" />
			<input type="submit" class="boton_enlace" value="<?php echo $referencia ?>" />
		</form>
	<?php
		}
		else{
			echo $referencia;
		}
	}
	?>
</td>
<td class="descripcion"><?php 


if(empty($i['Bitacora']['descripcion'])){
	if(empty($i['Transaccion']['Modulo']['nombre'])){
		echo $i['Transaccion']['nombre'];	
	} else {
		echo $i['Transaccion']['Modulo']['nombre'].' - '.$i['Transaccion']['nombre'];
	}
} else {


/*Modificacion para mostrar en vista el numero de cuenta de manera enmascarada
Se agrega funciones para trabajar con string
Se realiza una condicion que si encuentra un valor numerico de 20 digitos(numero de cuenta) haga el enmascaramiento si no imprima la descripcion tal cual aparece en base de datos de sistema activo en linea

*/

$des=desencriptar($i['Bitacora']['descripcion']);
$numeros= filter_var($des, FILTER_SANITIZE_NUMBER_INT);

	if(strlen($numeros)==20){
		$detalleProducto=enmascara($numeros);
		echo substr(desencriptar($i['Bitacora']['descripcion']),0,15).'  '.$detalleProducto ;
	}elseif(strlen($numeros)==21){
			 $cuenta=substr($numeros,1,21);
			 $detalleProducto=enmascara($cuenta);
                    echo substr(desencriptar($i['Bitacora']['descripcion']),0,20).'  '.$detalleProducto ;

	}else{


	echo desencriptar($i['Bitacora']['descripcion']);
	}


} ?></td>
<td class="monto"><?php 
if(!empty($i['Bitacora']['monto'])){
	echo formatea_monto_fromWs($i['Bitacora']['monto']);
}
?></td>
<?php if($master){?>
<td>
	<?php echo desencriptar($i['Usuario']['login']); ?>
</td>
<?php }?>
</tr>
<?php } ?>
</tbody>
<tfoot>
<tr>
<td colspan="<?php echo $master ? 5 : 4; ?>" class="paginador">
<?php	
echo $form->create(
	'HistoricoTransacciones',
	array(
		'url'=> array(
			'controller'=>'consultas',
			'action'=>$this->action
		),
		'id' => 'paginacion'
	)
);

echo $form->hidden('HistoricoTransacciones.operacion');
echo $form->hidden('HistoricoTransacciones.transaccion');
echo $form->hidden('HistoricoTransacciones.referencia');
echo $form->hidden('HistoricoTransacciones.monto');
echo $form->hidden('HistoricoTransacciones.mes_transaccion');
echo $form->hidden('HistoricoTransacciones.tipo');
echo $form->hidden('HistoricoTransacciones.fechaDesde.day');
echo $form->hidden('HistoricoTransacciones.fechaDesde.month');
echo $form->hidden('HistoricoTransacciones.fechaDesde.year');
echo $form->hidden('HistoricoTransacciones.fechaHasta.day');
echo $form->hidden('HistoricoTransacciones.fechaHasta.month');
echo $form->hidden('HistoricoTransacciones.fechaHasta.year');

if($master) echo $form->hidden('HistoricoTransacciones.perfiles');

echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros));
echo $form->hidden('offset');
echo $form->end();
?>
</td>
</tr>
</tfoot>
<?php } ?>
</table>

<?php if($total_registros > 0) { ?>

<div class="botonera" style="width:100%">

<form class="formulario_enlace" id="formulario_exportar" method="post" action="<?php echo Router::url('/consultas/historico_exportar') ?>">

<input type="submit" class="button" value="Exportar" />
</form>


</form>

</div>

<?php } ?>
</div>

<?php } ?>


<script type="text/javascript">
	
function acomodar(){
	$('label.error').remove();
	
	var tipoBusqueda = $('input[name="data[HistoricoTransacciones][tipo]"]:checked').val();
	if(tipoBusqueda == 'p' || tipoBusqueda == 't'){
		$('input[name="data[HistoricoTransacciones][referencia]"]').val('');
		$('input[name="data[HistoricoTransacciones][monto]"]').val('');
	}	
		
	$('select[name^="data[HistoricoTransacciones][fechaDesde]"],select[name^="data[HistoricoTransacciones][fechaHasta]"]')
	.attr(
		'disabled',
		tipoBusqueda!='p'
	);
			
	$('select[name="data[HistoricoTransacciones][mes_transaccion]"]')
	.attr(
		'disabled',
		tipoBusqueda!='t'
	);
}

$(function(){
	
	acomodar();

	$('input[name="data[HistoricoTransacciones][tipo]"]').change(acomodar);
	
	// para la validacion
	$('#HistoricoTransaccionesHistoricoTransaccionesForm').validate({
		rules:{
			'data[HistoricoTransacciones][referencia]':{
				digits:true
			},
			'data[HistoricoTransacciones][monto]':{
				numberVE:true
			},
			'data[HistoricoTransacciones][fechaDesde][year]':{
				fechaMenorIgualQueFijo: '<?php echo date('m/d/Y'); ?>',
				fechaMayorIgualQueFijo: '<?php echo date('m/d/Y', strtotime('-1 year') ); ?>'
			},
			'data[HistoricoTransacciones][fechaHasta][year]':{
				fechaMayorIgualQue: '#HistoricoTransaccionesFechaDesdeYear',
				fechaMenorIgualQueFijo: '<?php echo date('m/d/Y'); ?>'
			}
		},
		messages:{
			'data[HistoricoTransacciones][referencia]':{
				digits:<?php echo _m('valor.invalido',array('el n&uacute;mero referencia'),true) ?>
			},
			'data[HistoricoTransacciones][monto]':{
				numberVE:<?php echo _m('formato.invalido',array('el monto'),true) ?>
			},
			'data[HistoricoTransacciones][fechaDesde][year]':{
				fechaMenorIgualQueFijo: <?php echo _m('fecha.menor.igual.actual',null,true) ?>,
				fechaMayorIgualQueFijo: <?php echo _m('fecha.mayor.igual.fija','a un año',true) ?>
			},
			'data[HistoricoTransacciones][fechaHasta][year]':{
				fechaMayorIgualQue: <?php echo _m('fecha.mayor.igual.que', NULL, true) ?>,
				fechaMenorIgualQueFijo: <?php echo _m('fecha.menor.igual.actual',null,true) ?>
			}
		}		
	});
	
	
    $('#operacion').bind('change', function(){
		if($(this).val()){
			$.ajax({
				type: "GET",
				url: '<?php echo Router::url("/consultas/obtener_transacciones/"); ?>' + $(this).val(),
				cache:false,
				beforeSend: function() {
					$('#td_transaccion_wrapper').html('<?php echo 'Cargando... ' . $html->image('/img/ajax-loader_mini.gif'); ?>');
				},
				success: function(msg){
					$('#td_transaccion_wrapper').html(msg);
				},
			error: function(msg){
				alert(<?php echo _m('error.fatal',null,true) ?>);
			}
			});
		}
		else{
			$("#transaccion").find("option").remove(); 
			$("#transaccion").html('<option value="">Todas las transacciones</option>'); 
		}
    });

});

</script>