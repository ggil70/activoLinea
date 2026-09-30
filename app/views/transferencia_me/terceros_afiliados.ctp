<?php
/**
 * Formulario de transferencias a cuentas afiliadas de terceros en banco activo
**/
?>
<div>
<h2 class="user">Transferencia Moneda Extranjera Tercero en Activo</h2>
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
	echo $form->create('Transferencia',
            array(
                'url'=> array(
                    'controller'=>'transferencia_me',
                    'action'=>'terceros_afiliados'
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
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la transferencia moneda extranjera.</td></tr>';


	echo '
	<tr>
	<td class="etiqueta">Cuenta de Débito:</td>
	<td> ' . enmascara($this->data['Transferencia']['cuentaDebitar']) . ' </td>
	</tr>
	';

	echo '<tr><td colspan="2">Seleccione el beneficiario de la transferencia y complete los datos de la operación:</td></tr>';

	
	echo '<tr><td colspan="2">'
            .'<label for="data[Transferencia][cuentaCreditar]" class="error manual">'._m('seleccion.requerida',array('la cuenta del beneficiario')).'</label>';
            if ($form->isFieldError('cuentaCreditar')){
		echo $form->error('cuentaCreditar',
                    array(
			'nroCuenta'=>_m('formato.invalido',array('la cuenta del beneficiario')),
			'requerido'=>_m('campo.requerido',array('el beneficiario')),
			'banco'=>_m('formato.invalido',array('el número de cuenta')),
			'comparacion'=>_m('comparacion.cuentas',array('la cuenta destino'))
                    )
		);
            }
	echo '</td></tr>';
	?>

	<tr>
	<td colspan="2">

	<input type="hidden" name="data[Transferencia][cuentaCreditar]" value="">

	<table cellpadding="0" cellspacing="0" border="0" class="tabla">
	<thead>
	<tr>
	<td class="ancholibre texto_centro"><span class="requerido">*</span></td>
	<td>Alias</td>
	<td>Nombre Beneficiario</td>
	<td>Número Cuenta</td>
	</tr>
	</thead>
	<tbody>
	<?php
            if($afiliados){
                foreach($resultados as $i){
	?>
                    <tr>
                        <td class="texto_centro ancholibre">
                            <input type="radio"
                            class="seleccion_afiliado_me"
                            id="<?php echo 'sel'.$i['Afiliacion']['id'] ?>"
                            name="data[Transferencia][cuentaCreditar]"
                            value="<?php echo desencriptar($i['Afiliacion']['numero_cuenta']);?>"
                            <?php if(
                                isset($this->data['Transferencia']['cuentaCreditar'])
                                && 0==strcmp($this->data['Transferencia']['cuentaCreditar'],desencriptar($i['Afiliacion']['numero_cuenta']))
                            ){
                            echo 'checked="checked"';
                            }
                            ?>
                                data-alias="<?php echo desencriptar($i['Afiliacion']['alias']) ?>"
                            />
			<?php 
                            echo $form->hidden('seleccion', array('id'=>'monto'.$i['Afiliacion']['id'], 'value'=>empty($i['Afiliacion']['monto_maximo']) ? '' : formatea_monto_fromWs($i['Afiliacion']['monto_maximo']) ));
			?>
                        </td>
			<td><?php echo desencriptar($i['Afiliacion']['alias']) ?></td>
			<td><?php echo desencriptar($i['Afiliacion']['nombre_beneficiario']) ?></td>
			<td><?php echo desencriptar($i['Afiliacion']['numero_cuenta']) ?></td>
                    </tr>
          <?php }?>
                    <tr>
                        <td colspan="4" class="paginador">
                            <?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros,'tamano_paginacion'=>5)); ?>
                        </td>
                    </tr>
            <?php		
            }else {
		?>
			<tr>
                            <td colspan="4" class="texto_centro">No se encontraron resultados...</td>
			</tr>
		<?php } ?>
	</tbody>
	</table>
	</td>
	</tr>
	<?php

	echo '<tr><td colspan="2">Indique la información que a continuación se solicita:</td></tr>';

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

	if ($form->isFieldError('monto')){
            echo '<tr><td>&nbsp;</td><td>'.$form->error('monto',
		array(
                    'requerido'=>_m('alias.requerido',array('el monto'))
		)
            ).'</td></tr>';
	}        
        
  
	echo $form->input(
            'monto',
            array(
                'label'=>'<span class="requerido">* </span>Monto:',
                'type'=>'text',
                'class'=>'monto pago'
            )
	);

	if ($form->isFieldError('monto')){
            echo '<tr><td>&nbsp;</td><td>'.$form->error('monto',
		array(
                    'requerido'=>_m('campo.requerido',array('el monto')),
                    'numerico'=>_m('formato.invalido',array('el monto')),
                    'montoMaximo'	=> _m('monto.maximo', 'de la cuenta'),
                    'montoLimiteDiario'=>_m('monto.excedido', 'de la transferencia')
		)
            ).'</td></tr>';
	}

	echo $form->input(
            'descripcion',
            array(
                'label'=>'<span class="requerido">* </span>Descripci&oacute;n:',
                'type'=>'text',
                'maxlength' => '30',
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

	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td>&nbsp;</td><td class="submit">';
	
	echo $html->link(
            'Volver',
            'terceros/v',
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
    'Transferencia',
    array(
       'url'=> array(
       'controller'=>'transferencia_me',
       'action'=>$this->action
        ),
        'id' => 'paginacion'
    )
);
if(!empty($this->data['Transferencia'])){
    foreach($this->data['Transferencia'] as $k => $v) {
	echo $form->hidden($k);
    }
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>


<script>
$(function(){

    //Funcion para completar 2 decimales
    $('#TransferenciaMonto').blur(function (e) {
    	var monto = $('#TransferenciaMonto').val();
        //Si el monto solo posee un solo decimal, hay que completar los dos decimale
        var arreglo_monto = monto.split(',');
        var entero = arreglo_monto[0];
        var decimal = arreglo_monto[1];

        if (decimal.length == 1){
           monto = monto + '0';
           $('#TransferenciaMonto').val(monto);
        }
    });  


        $('.seleccion_afiliado_me').click(function(){
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
    
	$('#TransferenciaTercerosAfiliadosForm').validate({
		rules:{
                        'data[Transferencia][alias]':{
                                required:true
                        },                    
			'data[Transferencia][cuentaCreditar]':{
				required:true
			},
			'data[Transferencia][monto]':{
				required:true,
				numberVE:true,
				montoMaximo3: '#monto_limite_usuario',
				montoMaximo2: '#monto_limite',
				montoMaximo: '#monto_maximo'			
			},
			'data[Transferencia][descripcion]':{
				required:true,alphaNumericSpace:true
			}
		},	
		messages:{
                        'data[Transferencia][alias]':{
                                required:<?php echo _m('campo.requerido',array('el alias de la afiliación'), true) ?>
                        },
			'data[Transferencia][cuentaCreditar]':{
				required:<?php echo _m('campo.requerido',array('la cuenta del beneficiario'), true) ?>
			},
			'data[Transferencia][monto]':{
				required:<?php echo _m('campo.requerido',array('el monto'), true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto'), true) ?>,
				montoMaximo:<?php echo _m('monto.maximo', 'de la cuenta' ,true) ?>,
				montoMaximo2: <?php echo _m('monto.excedido', 'de la transferencia' ,true) ?>,
				montoMaximo3: <?php echo _m('monto.limite.usuario', 'de la transferencia' ,true) ?>
			},
			'data[Transferencia][descripcion]':{
				required:<?php echo _m('campo.requerido',array('la descripcion'),true) ?>,
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido',array('la descripcion'),true) ?>
			}
		}	
	});
});


</script>
