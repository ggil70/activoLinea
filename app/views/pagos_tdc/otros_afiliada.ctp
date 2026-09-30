<?php
/**
 * Pago de tarjetas propias en otros bancos afiliadas
**/
?>
<div>
<h2 class="user">Pago Tarjeta Crédito Mismo Titular Otro Banco</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<?php $usuario = $this->Session->read('usuarios.datos'); if( !$activo->otp_validado() && (!isset($usuario['rol_juridico_id']) || !in_array($usuario['rol_juridico_id'],array(3,5))) ) { ?>
<div class="paso_desc">Código de Autorización</div>
<div class="paso_nro">4</div>
<?php } ?>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

	<?php

	echo $form->create('Pagostdc',
		array(
			'url'=> array(
				'controller'=>'pagos_tdc',
				'action'=>'otros_afiliada'
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

	echo $form->hidden('tipoDestinatario');
	echo $form->hidden('cuentaDebitar');
	echo $form->hidden('monto_maximo', array('id'=>'monto_maximo'));
	echo $form->hidden('monto_limite', array('id'=>'monto_limite', 'value'=>$monto_limite));
	echo $form->hidden('monto_limite_usuario', array('id'=>'monto_limite_usuario'));

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
	echo '<tr><td style="width:150px"></td><td style="width:400px"></td></tr>';
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos del pago.</td></tr>';

	echo '
	<tr>
	<td class="etiqueta">Cuenta de D&eacute;bito:</td>
	<td> ' . enmascara($this->data['Pagostdc']['cuentaDebitar']) . ' </td>
	</tr>
	';


	echo '<tr><td colspan="2"></td></tr>';
	echo '<tr><td colspan="2">Seleccione el beneficiario del pago y complete los datos de la operación:</td></tr>';
	
	
	echo '<tr><td colspan="2">'
		.'<label for="data[Pagostdc][numeroTDC]" class="error manual">'. _m('una.seleccion.requerida','de las tarjetas') .'</label>';
		if ($form->isFieldError('numeroTDC')){
			echo $form->error('numeroTDC',
				array(
					'requerido'=>_m('una.seleccion.requerida','de las tarjetas')
				)
			);
		}
	echo '</td></tr>';
	
	?>

	<tr>
	<td colspan="2">

	<input type="hidden" name="data[Pagostdc][numeroTDC]" value="">

	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tabla">
	<thead>
	<tr>
	<td class="ancholibre texto_centro"><span class="requerido">* </span></td>
	<td>Alias</td>
	<td>Banco Beneficiario</td>
	<td>Tarjeta Cr&eacute;dito</td>
	</tr>
	</thead>
	<tbody>
	<?php
	if($afiliados){
		foreach ($resultados as $a){ 
	?>
	<tr>
		<td class="texto_centro ancholibre">
			<input
			type="radio"
			class="seleccion_afiliado"
			id="<?php echo 'sel'.$a['Afiliacion']['id'] ?>"
			name="data[Pagostdc][numeroTDC]"
			value="<?php echo desencriptar($a['Afiliacion']['numero_cuenta']) ?>"
			<?php if(
					isset($this->data['Pagostdc']['numeroTDC'])
					&& 0==strcmp($this->data['Pagostdc']['numeroTDC'],desencriptar($a['Afiliacion']['numero_cuenta']))
				){
				echo 'checked="checked"';
			}
			?>
                        data-alias="<?php echo desencriptar($a['Afiliacion']['alias']) ?>"
			/>
			<?php 
				echo $form->hidden('seleccion', array('id'=>'monto'.$a['Afiliacion']['id'], 'value'=>empty($a['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($a['Afiliacion']['monto_maximo']) ));
			?>
		</td>
		
		<td><?php echo ($a['Afiliacion']['alias']) ? desencriptar($a['Afiliacion']['alias']) : 'Alias no asignado' ?></td>
		<td><?php echo $a['Afiliacion']['banco'] ?></td>
		<td><?php echo desencriptar($a['Afiliacion']['numero_cuenta']) ?></td>

	</tr>
	<?php
		}
	?>
		<tr>
		<td colspan="4" class="paginador">
			<?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros,'tamano_paginacion'=>5)); ?>
		</td>
		</tr>
	<?php
	}
	else{
	?>
	<tr>
		<td colspan="4" class="texto_centro">No se encontraron resultados...</td>
	</tr>
	<?php
	}
	?>
	</tbody>
	</table>
	</td>
	</tr>

	<?php

	echo '<tr><td colspan="2">Indique la informaci&oacute;n que a continuaci&oacute;n se solicita:</td></tr>';

	echo $form->input(
		'alias',
		array(
			'label'=>'<span class="requerido">* </span>Alias:',
			'type'=>'text',
			'class'=>'alias alfanumericoEspacio',
			'maxlength' => '30',
                        'id'=>'txtAlias',
                        'readonly'=>'readonly'
		)
	);

	if ($form->isFieldError('alias')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('monto',
				array(
					'requerido'=>_m('alias.requerido',array('el monto'))
				)
			).'</td></tr>';
	}         
        
	echo $form->input(
		'monto',
		array(
			'label'=>'<span class="requerido">*</span> Monto a pagar:',
			'type'=>'text',
			'class'=>'monto pago'
		)
	);

	if ($form->isFieldError('monto')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('monto',
				array(
					'requerido'=>_m('campo.requerido','el monto'),
					'numerico'=>_m('formato.invalido','el monto'),
					'montoMaximo'	=> _m('monto.maximo', 'de la tarjeta'),
					'montoLimiteDiario'=>_m('monto.excedido', 'para el pago'),
                                        'limiteAltoValor'=>_m('monto.excedido.altoValor', 'para el pago')
				)
			).'</td></tr>';
	}

	echo $form->input(
		'descripcion',
		array(
			'label'=>'<span class="requerido">*</span> Descripci&oacute;n:',
			'type'=>'text',
			'maxlength' => '30',
			'class' => 'alfanumericoEspacio'
		)
	);

	if ($form->isFieldError('descripcion')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('descripcion',
				array(
					'requerido'=>_m('campo.requerido','la descripción'),
					'alfanumerico'=>_m('alfanumerico.invalido','la descripción')
				)
			).'</td></tr>';
	}
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';	        
        
	echo '<tr><td>&nbsp;</td><td class="submit">';

	echo $html->link(
		'Volver',
		'otros/v',
		array(
			'class'=>'button'
		)
	);
	
	echo '&nbsp;&nbsp;';
	
	echo $html->link(
		'Aceptar',
		'javascript:',
		array(
			'class'=>'button envio_formulario',
                        'id'=>'transferir'
		)
	);	

	echo '</td></tr></table>';

	echo $form->hidden('offset');
	echo $form->end();

	?>

</div>

<?php	
echo $form->create(
	'Pagostdc',
	array(
		'url'=> array(
			'controller'=>'pagos_tdc',
			'action'=>$this->action
		),
		'id' => 'paginacion'
	)
);
if(!empty($this->data['Pagostdc'])){
	foreach($this->data['Pagostdc'] as $k => $v) {
		echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script type="text/javascript">
$(function(){

    //Funcion para completar 2 decimales
    $('#PagostdcMonto').blur(function (e) {
    	var monto = $('#PagostdcMonto').val();
        //Si el monto solo posee un solo decimal, hay que completar los dos decimale
        var arreglo_monto = monto.split(',');
        var entero = arreglo_monto[0];
        var decimal = arreglo_monto[1];

        if (decimal.length == 1){
           monto = monto + '0';
           $('#PagostdcMonto').val(monto);
        }
    }); 


        $('.seleccion_afiliado').click(function(){
            if($(this).prop('checked')){
                $('#txtAlias').val($(this).data('alias'));
                if(!$(this).data('alias').length){
                    $('#txtAlias').removeAttr('readonly');
                }else{
                    $('#txtAlias').attr('readonly',true);
                }
            }
        });
        
        $('#transferir').click(function(){
            if(!$('#txtAlias').val().length){
                $('#txtAlias').removeAttr('readonly');
            }
        });    
    
	$('#PagostdcOtrosAfiliadaForm').validate({
		rules:{
                        'data[Pagostdc][alias]':{
                                required:true
                        },                     
			'data[Pagostdc][numeroTDC]':{
				required:true
			},
			'data[Pagostdc][monto]':{
				required:true,
				numberVE:true,
				montoMaximo3: '#monto_limite_usuario',
				montoMaximo2: '#monto_limite',
				montoMaximo: '#monto_maximo'
				
			},
			'data[Pagostdc][descripcion]':{
				required:true, alphaNumericSpace:true
			}
		},
		messages:{
                        'data[Pagostdc][alias]':{
                                required:<?php echo _m('campo.requerido',array('el alias de la afiliación'), true) ?>
                        },                     
			'data[Pagostdc][monto]':{
				required:<?php echo _m('campo.requerido','el monto',true) ?>,
				numberVE:<?php echo _m('formato.invalido','el monto',true) ?>,
				montoMaximo:<?php echo _m('monto.maximo', 'de la tarjeta' ,true) ?>,
				montoMaximo2: <?php echo _m('monto.excedido', 'para el pago' ,true) ?>,
				montoMaximo3: <?php echo _m('monto.limite.usuario', 'para el pago' ,true) ?>
			},
			'data[Pagostdc][descripcion]':{
				required:<?php echo _m('campo.requerido','la descripci&oacute;n',true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','la descripci&oacute;n',true) ?>
			}
		}
	});
});





</script>