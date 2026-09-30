<?php
/**
 * Formulario para solicitar y validar un OTP
**/
?>

<div>
<h2 class="user"><?php echo $estado['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($estado['pasos']); $i++) { 
	$class = ($i==$estado['paso_actual']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $estado['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	
	<?php
	
	echo $form->create('Otp',
	array(
		'url'=> array(
                        'controller'=>'seguridad',
                        'action'=>'valida_otp'
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
		)
	);
	
	?>
	
        <div class="espacio_vertical_20"></div>
        
	<tr><td colspan="2" class="mensaje">Ingrese los datos solicitados y luego presione el bot&oacute;n Aceptar.</td></tr>
	
	<tr><td colspan="2">&nbsp;</td></tr>		
	
	<?php
	
	echo $form->input(
		'nroOtp',
		array(
			'label'=>'Código de Autorización:',
			'type'=>'text',
			'maxlength'=>'12',
			'class'=>'numerico'
		)
	);

	if ($form->isFieldError('nroOtp')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('nroTarjeta',
			array(
				'otp'=>_m('formato.invalido', array('el Código de Autorización')),
				'requerido'=>_m('campo.requerido', array('el Código de Autorización'))
			)
		).'</td></tr>';
	}
	
	echo '<tr><td colspan="2">&nbsp;</td></tr>';
	
	echo '<tr><td class="submit" colspan="2">';	
	
	echo $form->submit(
            'Aceptar',
            array(
                    'class'=>'button bloqueo_repetir',
                    'id'=>'aceptar'
            )
        );
	
	echo '</td></tr></table>';
		
	echo $form->end();
	
	?>
</div>

<script>
$(function(){
	// para la validacion
	$('#OtpValidaOtpForm').validate({
		submitHandler: function(form){						
			if($('#OtpValidaOtpForm').valid()){
				
				if(!bloqueado){ // bloqueado var global
					$('input.bloqueo_repetir').prop('disabled', true);
					bloqueado = true;
					form.submit();
				}
				
			}
		},
                rules:{
			'data[Otp][nroOtp]':{
				required:true,
				digits:true,
				minlength:6,
				maxlength:12
			}
		},
		messages:{
			'data[Otp][nroOtp]':{
				required: <?php echo _m('campo.requerido', array('el Código de Autorización'), true) ?>,
				digits: <?php echo _m('formato.invalido', array('el Código de Autorización'), true) ?>,
				minlength: <?php echo _m('longitud.exacta',array(6,'el Código de Autorización'), true) ?>,
				maxlength: <?php echo _m('longitud.exacta',array(12,'el Código de Autorización'), true) ?>
			}
		}
	});
});
</script>
