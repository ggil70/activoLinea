<?php
    /**
     *  Vista para la modificación de un Pago de Nómina/Proveedores
     */
?>

<div>
<h2 class="user"><?php echo $titulo ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<?php if(empty($beneficiarios)) { 
?>
<div class="contenedor_formulario">
	No tiene beneficiarios para continuar con la transacción. Debe realizar las afiliaciones correspondientes.
	<div class="botonera">
		<?php 
		echo $html->link(
			'Volver',
			'pagos/'.$estado['tipo'],
			array(
				'class'=>'button',
				'id'=>'volver'
			)
		);
		?>
	</div>
</div>
<?php
}
else {
?>

<div class="contenedor_formulario" style="width:800px;">

	<?php

	echo $form->create('PagoCash',
		array(
			'url'=> array(
				'controller'=>'cash',
				'action'=>'pago_modificar'
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
	?>
	<input type="hidden" name="data[accion]">
	<?php
	
	echo $form->hidden('monto_total');
    echo '<input type="hidden" name="altoValor" id="altoValor" value = ' . _p('cash_alto_valor') . '>';  


	echo $html->tag(
		'table',
		null,
		array(
			'class'=>'formulario',
			'cellpadding'=>'0',
			'cellspacing'=>'0',
			'border'=>'0',
			'align'=>'center',
			'style'=>'table-layout:fixed'
		)
	);
	
	// La primera fila define la tabla con layout fixed
	echo '<tr><td style="width:130px"></td><td style="width:250px"></td></tr>';
	
	?>

	<tr><td colspan="2"><?php echo $instruccion; ?></td></tr>

	<tr>
	<td class="etiqueta"><span class="estiloTexto"/>Tipo Operación:</td>
	<td><?php switch($estado['tipo']){
		case 'n':
			echo 'Pago Nómina';
			break;
		case 'p':
			echo 'Pago Proveedor';
			break;
		}?>
	</tr>
	<tr>
		<td class="etiqueta"><span class="estiloTexto"/>Usuario Registro:</td>
		<td><?php echo desencriptar($usuario['nombre']); ?></td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<?php

	echo $form->input(
		'cuentaDebitar',
		array(
			'label'=>'<span class="requerido">* </span>Cuenta Débito:',
			'type'=>'select',
			'options' => $cuentas,
			'empty'=>'Seleccione'
		)
	);

	if ($form->isFieldError('cuentaDebitar')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('cuentaDebitar',
			array(
				'requerido'=>_m('campo.requerido','la cuenta'),
				'numero'=>_m('formato.invalido','la cuenta')
			)
		).'</td></tr>';
	}

	echo $form->input(
		'fecha_valor',
		array(
			'label'=>'<span class="requerido">*</span> Fecha Valor:',
			'type'=>'date',
			'timeFormat'=>null,
			'dateFormat'=>'DMY',
			'between'=>'</td><td>',
			'minYear'=>date('Y'),
			'maxYear'=>date('Y', strtotime('+1 year')),
		)
	);

	if ($form->isFieldError('fecha_valor')){
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('fecha_valor',
			array(
				'fecha_valor'=>_m('seleccion.requerida','una fecha válida')
			)
		).'</td></tr>';
	}

	echo $form->input(
		'descripcion',
		array(
			'label'=>'<span class="requerido">*</span> Descripci&oacute;n:',
			'type'=>'text',
			'width'=>'60',
			'maxlength'=>'30',
			'class'=>'alfanumericoEspacio'
		)
	);

	if ($form->isFieldError('descripcion')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('descripcion',
			array(
				'requerido'=>_m('campo.requerido',array('la descripción')),
				'alfanumerico'=>_m('alfanumerico.invalido',array('la descripción'))
			)
		).'</td></tr>';
	}	
	
	echo $form->input(
		'lote',
		array(
			'label'=>'<span class="requerido">* </span>Número Lote:',
			'type'=>'text',
			'maxlength' => '9',
			'class'=>'numerico',
			'readonly'=>'readonly'
		)
	);

	if ($form->isFieldError('lote')){
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('lote',
			array(
				'requerido'=>_m('campo.requerido',array('el lote')),
				'numerico'=>_m('valor.invalido',array('el número de lote'))
			)
		).'</td></tr>';
	}	
	
	?>
	<tr>
	<td class="etiqueta"><span class="estiloTexto"/>Monto Bs.:</td>
	<td><span id="monto_total"><?php if(isset($this->data['PagoCash']['monto_total'])) echo $this->data['PagoCash']['monto_total']; ?></span></td>
	</tr>
	
	<?php 
	if($form->isFieldError('monto_total')){
		
		$mensajes = array(
			'numerico'=> _m('formato.invalido',array('el monto')),
			'montoLimiteAmbos' => _m('monto.limite.usuario','de la transacción en activo y otros bancos'),
			'montoLimiteIt' => _m('monto.limite.usuario','de la transacción en activo'),
			'montoLimiteE' => _m('monto.limite.usuario','de la transacción a otros bancos')
		);
		
		if($estado['tipo']=='n'){
			$mensajes['nopermitidaIt']=_m('op.nopermitida','de Pagos de Nómina en Activo');
			$mensajes['nopermitidaE']=_m('op.nopermitida','de Pagos de Nómina Otros Bancos');
		} else {
			$mensajes['nopermitidaIt']=_m('op.nopermitida','de Pagos de Proveedores en Activo');
			$mensajes['nopermitidaE']=_m('op.nopermitida','de Pagos de Proveedores Otros Bancos');
		}
	
		echo '<tr><td>&nbsp;</td><td>'.$form->error(
			'monto_total',
			$mensajes
		).'</td></tr>';
	}
	
	echo '<tr><td colspan="2"><span class="requerido">*</span>&nbsp;<span class="requerido2">Campo obligatorio</span></td></tr>'; 
	?>
	
	</table>
	
	<div class="espacio_vertical_20"></div>
	<div class="espacio_vertical_20"></div>
	
	<?php
	if ($form->isFieldError('beneficiario')){
		echo '<div class="error-message">'.$form->error('beneficiario').'</div>';
	}	
	?>
	<label id="error-b" class="error manual"><?php echo _m('uno.seleccion.requerida','de los beneficiarios'); ?></label>
	<table class="tabla" cellpadding="0" cellspacing="0" border="0" align="center" style="width:100%;">
	<thead>
		<tr>
			<td><?php echo $form->checkbox("seleccionar", array('hiddenField' => false));  ?></td>
			<td>Identificación<br/>Beneficiario</td>
			<td style="width:160px;">Nombre<br/>Beneficiario</td>
			<td>Número Cuenta<br/>Beneficiario</td>
			<td>Banco<br/>Beneficiario</td>
			<td style="width:160px;">Monto Bs.</td>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach($beneficiarios as $x){
		$b = desencriptarCampos($x['AfiliacionCash'], array('nombre','cedula','numero_cuenta','email','celular'));
		if(((substr($b['numero_cuenta'],0,4)=='0171') && (substr($b['numero_cuenta'],10,1)==4)) || ((substr($b['numero_cuenta'],0,4)=='0171') && (substr($b['numero_cuenta'],10,1)==6))|| ((substr($b['numero_cuenta'],0,4)=='0171') && (substr($b['numero_cuenta'],10,1)==2)) || ((substr($b['numero_cuenta'],0,4)!='0171'))){ 

            $ctaCliente = substr($b['numero_cuenta'],0,4); 
            $idCuenta = "c" . $b['id'];
			echo $form->hidden($idCuenta, array('id'=>$idCuenta,'value'=>$ctaCliente));
	?>
		<tr>
			<td><?php 
				$options = array('hiddenField' => false);
				
				if(
					isset($estado['modificar_detalle']) &&
					isset($this->data['Beneficiarios'][$b['id']])
				){
					$options['checked'] = true;
					$bene_data = $this->data['Beneficiarios'][$b['id']];
				}
				
				echo $form->checkbox("Beneficiarios.".$b['id'].".seleccionado", $options); 
			?></td>
			<td><?php echo $b['tipo_cedula'].$b['cedula'] ?></td>
			<td><?php echo $b['nombre'] ?></td>
			<td><?php echo $b['numero_cuenta'] ?></td>
			<td><?php echo $bancos[substr($b['numero_cuenta'],0,4)] ?></td>
			<td style="width:22%;">
				<?php 
				$options = array('class'=>'monto monto_beneficiario texto_derecho');
				
				if(isset($bene_data)) $options['value'] = $bene_data['monto'];
				
				echo $form->text("Beneficiarios.".$b['id'].".monto", $options); 

				//Etiqueta de error alto valor
				$idEtiqError = "error_monto_" . $b['id']; 
				echo "<br>";

				$mtoAltoValor = formatea_monto_fromWs(_p('cash_alto_valor'));
				$mensajeError = _m('error.alto.valor') . " " . $mtoAltoValor . " Bs.";


				echo '<div id="' . $idEtiqError . '" style="display: none"><span style="color:red">' . $mensajeError . '</span></div>';
				//FIN Etiqueta de error alto valor
				?>
			</td>
		</tr>
	<?php }} ?>
		
		<tr>
			<td class="botonera" colspan="6">	
				<?php 

				echo $html->link(
					'Volver',
					$estado['url_volver_modificar'],
					array(
						'class'=>'button',
						'id'=>'volver'
					)
				);

				echo '&nbsp;&nbsp;&nbsp;';	
				
				if(isset($estado['modificar_detalle']) && !isset($this->data['accion'])){
					echo $html->link(
						'Guardar',
						'javascript:',
						array(
							'id'=>'modificar',
							'class'=>'button boton_accion'
						)
					);
				} else if(!verificaAfiliados($estado['tipo'])){
					echo $html->link(
						'Guardar',
						'javascript:',
						array(
							'id'=>'guardar',
							'class'=>'button boton_accion'
						)
					);
				}

				echo '&nbsp;&nbsp;&nbsp;';	

				echo $html->link(
					'Procesar',
					'javascript:',
					array(
						'id'=>'procesar',
						'class'=>'button boton_accion'
					)
				);

				?>

			</td>
		</tr>
	</tbody>
	</table>
<?php


echo $form->end();

?>	
</div>

<div class="basic-modal-content" id="ayuda_beneficiario_deshabilitado">
	<h3>Carga Masiva</h3>
	<p>Estimado Cliente, si desea hacer un pago a una mayor cantidad de beneficiarios, por favor hágalo a través del menú de Carga Masiva.</p>
</div>

<script>
function acomodar(){
	if($('input[type="checkbox"][name$="[seleccionado]"]:checked').size()>10){
		$('#ayuda_beneficiario_deshabilitado').modal({minHeight:200,minWidth:200});
		return false;
	} else {
		var i = $(this)
			.attr('name')
			.replace(
				/data\[Beneficiarios\]\[(\d+)\]\[seleccionado\]$/,
				'$1'
			);
		
		if($(this).is(':checked')){
			$('input[name="data[Beneficiarios]['+i+'][monto]"]')
			.removeAttr('disabled');
		} else {
			$('input[name="data[Beneficiarios]['+i+'][monto]"]')
			.attr('disabled','disabled')
			.val('')
			.parent()
			.find('label.error')
			.remove();

			mensajeError = '#error_monto_' + i;
			$(mensajeError).hide();	
		}
	}
}

function calcular_total(){
	var total = 0.0;
	
	var error = false;
	
	$('.monto_beneficiario').each(function(){
		if(!$(this).attr('disabled')){
			var val = $(this).val();
			
			if(val==''){
				// nada
			} else if(/^-?(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d+)?$/.test(val)){
				val = val.replace(/\./g,'').replace(/,/g,'.');
				val = parseFloat(val);
				total += val;
			} else {
				error = true;
			}
		}
	});
	
	if(!error){
		total = total.toFixed(2);
		
		total = total.replace(/\./g,',');
		
		total = number_format(total);
		
		$('#monto_total').text(total);
		$('#PagoCashMontoTotal').val(total);
	}
}

function seleccion(){
	
	var chequeados = 0;
	var total = 0;
	
	$('input[type="checkbox"][name$="[seleccionado]"]').each(function(){
		if($(this).is(':checked')) chequeados++;
		total++;
	});	
	$('#PagoCashSeleccionar').prop('checked',(chequeados==total));
	
}

function calcular_monto(){
	var i = $(this)
		.attr('name')
		.replace(
			/data\[Beneficiarios\]\[(\d+)\]\[monto\]$/,
			'$1'
		);
 
       //Obtener codigo Banco 
       cta = '#c' + i;  
	   valCta = $(cta).val();
	   mensajeError = '#error_monto_' + i; 

       monto = $(this).val();
       monto = monto.replace(".","");
       monto = monto.replace(".","");
       monto = monto.replace(".","");
       monto = monto.replace(".","");
       monto = monto.replace(",",".");
       monto = parseFloat(monto);

       if(valCta != '0171'){
       	  altoValor = $('#altoValor').val();
          altoValor = parseFloat(altoValor);
       	  if(monto > altoValor){
       	  	   $(this).val("");
       	  	   $(mensajeError).show();				
       	  	   calcular_total();
      	  }else{
      	  	  $(mensajeError).hide();				
      	  }
       }

}


$(function(){
	$('input[type="checkbox"][name$="[seleccionado]"]')
	.each(acomodar)
	.change(acomodar)
	.change(seleccion)
	.change(calcular_total);
	
	seleccion();
	$('#PagoCashSeleccionar').change(function(){
		
		var allchk = $(this).is(':checked');
		
		$('input[type="checkbox"][name$="[seleccionado]"]').each(function(){
			$(this).prop('checked',allchk);
		});
		
		$('input[type="checkbox"][name$="[seleccionado]"]').each(acomodar);
		
		if(!allchk) $('.tabla label.error').hide();
	});
	
	$('.monto_beneficiario')
	.keyup(calcular_total)
	.blur(calcular_monto);
	
	calcular_total();
		
	// para la validacion
	$('#PagoCashPagoModificarForm').validate({
		submitHandler: function(form){
			$.each($('tr td div.error-message'),function(){
				if($(this).siblings().size()==0){
					$(this).parent().parent().remove();
				}
			});
			
			if($('input[name^="data[Beneficiarios]"]').is(':checked')){
				$('#error-b').hide();
				form.submit();
			} else {
				$('#error-b').show();
			}
		},
		rules:{
			'data[PagoCash][cuentaDebitar]':{
				required:true, digits:true
			},
			'data[PagoCash][descripcion]':{
				required:true,alphaNumericSpace:true
			},
			'data[PagoCash][lote]':{
				required:true, digits:true
			}
			<?php foreach($beneficiarios as $b){ ?>
			,'data[Beneficiarios][<?php echo $b['AfiliacionCash']['id'] ?>][monto]':{
				required:true,
				numberVE:true
				<?php if(isset($b['AfiliacionCash']['inactiva'])){ ?>
				,inactivaError:true
				<?php } ?>
				<?php if(isset($b['AfiliacionCash']['controlada'])){ ?>
				,controladaError:true
				<?php } ?>
			}
			<?php } ?>
		},
		messages:{
			'data[PagoCash][cuentaDebitar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta'),true) ?>,
				digits:<?php echo _m('valor.invalido',array('la cuenta'),true) ?>
			},
			'data[PagoCash][numero_cuenta]':{
				required:<?php echo _m('campo.requerido', 'la cuenta' , true) ?>,
				digits:<?php echo _m('formato.invalido','la cuenta',true)?>
			},
			'data[PagoCash][descripcion]':{
				required:<?php echo _m('campo.requerido',array('la descripción'),true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido',array('la descripción'),true) ?>
			},
			'data[PagoCash][lote]':{
				required:<?php echo _m('campo.requerido',array('el lote'),true) ?>,
				digits:<?php echo _m('valor.invalido',array('el lote'),true) ?>
			}
			<?php foreach($beneficiarios as $b){ ?>
			,'data[Beneficiarios][<?php echo $b['AfiliacionCash']['id'] ?>][monto]':{
				required:<?php echo _m('campo.requerido','el monto',true) ?>,
				numberVE:<?php echo _m('formato.invalido','el monto',true)?>,
				inactivaError:<?php echo _m('cuenta.inactiva','la cuenta del beneficiario',true) ?>,
				controladaError:<?php echo _m('cuenta.controlada','la cuenta del beneficiario',true) ?>
			}
			<?php } ?>
		}	
	});
	
});
</script>

<?php } ?>