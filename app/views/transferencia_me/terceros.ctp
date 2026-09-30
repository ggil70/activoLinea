<?php
/**
 * Formulario inicial de transferencias a cuentas de terceros en banco activo
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
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario_400">
	<?php

	echo $form->create('Transferencia',
            array(
		'url'=> array(
                    'controller'=>'transferencia_me',
                    'action'=>'terceros'
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
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la transferencia.</td></tr>';
	
	$input_debitar = array(
            'label'=>'<span class="requerido">* </span>Cuenta de Débito:',
            'type'=>'select',
            'options'=>$cuentas
	);
	
	if(count($cuentas) > 1) $input_debitar['empty'] = 'Seleccione';
	
	echo $form->input(
            'cuentaDebitar',
            $input_debitar
	);
	
	if ($form->isFieldError('cuentaDebitar')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaDebitar',
		array(
                    'requerido'=>_m('seleccion.requerida',array('la cuenta de débito'))
		)
            ).'</td></tr>';
	}

	echo $form->input(
            'tipoDestinatario',
            array(
                'label'=>'<span class="requerido">* </span>',
                'type'=>'radio',
                'legend'=>false,
                'separator'=>'&nbsp;&nbsp;',
                'options'=> array(
                    'a' => 'Afiliado',
                    'n' => 'No afiliado'
		),
                'before'=>'<tr><td class="etiqueta" style="text-align:right;"><span class="requerido">* </span>'
            )
	);
	
	echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'
		.'<label for="data[Transferencia][tipoDestinatario]" class="error manual">'._m('seleccion.requerida',array('el tipo de beneficiario')).'</label>';
            if ($form->isFieldError('tipoDestinatario')){
		echo $form->error('tipoDestinatario',
                    array(
			'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de beneficiario')),
			'requerido'=>_m('seleccion.requerida',array('el tipo de beneficiario'))
			)			
                    );
		}
                echo '</td></tr>';

                echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
                echo '<tr><td>&nbsp;</td><td class="submit">';

                echo $form->submit(
		'Aceptar',
		array(
                    'class'=>'button'
		)
            );		

            echo '</td></tr></table>';

            echo $form->end();

	?>
</div>

<script>
$(function(){
	$('#TransferenciaTercerosForm').validate({
		rules:{
			'data[Transferencia][cuentaDebitar]':{
				required:true
			},
			'data[Transferencia][tipoDestinatario]':{
				required:true
			}
		},	
		messages:{
			'data[Transferencia][cuentaDebitar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta de débito'),true) ?>
			},
			'data[Transferencia][tipoDestinatario]':{
				required:<?php echo _m('seleccion.requerida',array('el tipo de beneficiario'),true) ?>
			}
		}	
	});
});
</script>