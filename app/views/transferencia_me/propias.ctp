<?php
/**
 * Formulario de transferencias a cuentas propias en banco activo
**/
?>
<div>
<h2 class="user">Transferencia Moneda Extranjera Mismo Titular en Activo</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	<?php

	echo $form->create('Transferencia',
            array(
		'url'=> array(
                    'controller'=>'transferencia_me',
                    'action'=>'propias'
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
	
	echo '<tr><td colspan="2">A continuaci&oacute;n proceda a ingresar los datos de la transferencia Moneda Extranjera.</td></tr>';

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

	$input_creditar = array(
            'label'=>'<span class="requerido">* </span>Cuenta de Crédito:',
            'type'=>'select',
            'options'=>$cuentas,
	);
	
	if(count($cuentas) > 1) $input_creditar['empty'] = 'Seleccione';
	
	echo $form->input(
            'cuentaCreditar',
            $input_creditar
	);

	if ($form->isFieldError('cuentaCreditar')){
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('cuentaCreditar',
		array(
                    'nroCuenta'=>_m('formato.invalido',array('la cuenta de crédito')),
                    'comparacion'=>_m('comparacion.cuentas',array('la cuenta destino'))
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
            echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('monto',
		array(
                    'numerico'=>_m('formato.invalido',array('el monto')),
                    'requerido'=>_m('campo.requerido',array('el monto')),
                    'montoMaximo'=> _m('monto.maximo', 'de la cuenta'),
                    'montoLimiteDiario'=>_m('monto.excedido', 'de la transferencia'),
                    'montoLimiteUsuario'=>_m('monto.limite.usuario', 'de la transferencia'),
		)
            ).'</td></tr>';
	}

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

	
	$('#TransferenciaPropiasForm').validate({
		rules:{
			'data[Transferencia][cuentaDebitar]':{
				required:true
			},
			'data[Transferencia][cuentaCreditar]':{
				required:true,
				notEqualTo:'#TransferenciaCuentaDebitar'
			},
			'data[Transferencia][monto]':{
				required:true, numberVE:true
			}
		},
		messages:{
			'data[Transferencia][cuentaDebitar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta de débito'), true) ?>
			},
			'data[Transferencia][cuentaCreditar]':{
				required:<?php echo _m('seleccion.requerida',array('la cuenta de crédito'), true) ?>
			},
			'data[Transferencia][monto]':{
				required:<?php echo _m('campo.requerido',array('el monto'), true) ?>,
				numberVE:<?php echo _m('formato.invalido',array('el monto'), true) ?>
			}
		}
	});
});


        $('#monto').blur(function (e) {
            var monto = $('#monto').val();           

            //Si el monto solo posee un solo decimal, hay que completar los dos decimale
            var arreglo_monto = monto.split(',');
            var entero = arreglo_monto[0];
            var decimal = arreglo_monto[1];

            //validar que los decimales sean 2
            if (decimal.length == 1){
               monto = monto + '0';
               $('#monto').val(monto);
            }
        });

</script>