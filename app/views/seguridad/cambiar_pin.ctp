<?php
/**
 * Formulario de cambio de clave de acceso a cajeros automaticos
**/
?>
<div>
<h2 class="user">Cambiar Clave de Tarjeta de D&eacute;bito.</h2>
<div class="line_title_orange"></div>
</div>

<div style="width:800px;margin:0 auto">


<p>Modifique su clave de Tarjeta de D&eacute;bito y presione "Aceptar"</p>

<div style="width:400px;float:left">

<?php

echo $form->create('CambioPin',
	array(
		'url'=> array(
			'controller'=>'seguridad',
			'action'=>'cambiar_pin'
		),
		'id'=>'CambioClaveCambiarClaveForm',
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
		'border'=>'0'
	)
);	


$tarjeta_cambio = array(
	'label'=>'<span class="requerido">* </span>Tarjeta de d&eacute;bito:',
	'type'=>'select',
	'options'=>$tarjetasselect,

	
	
);


if(count($tarjetasselect) > 1 && (empty($tarjetasselect[1]))) $tarjetasselect['empty'] = 'Seleccione';

echo $form->input('tarjeta',$tarjeta_cambio);

if ($form->isFieldError('tarjeta_tdd')){
	echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('tarjeta_tdd',
		array(
			'requerido'=>_m('seleccion.requerida',array('la cuenta de débito'))
		)
	).'</td></tr>';
}


echo $form->input(
	'pin_nuevo',
	array(
		'label'=>'<span class="requerido">* </span>Clave nueva:',
		'type'=>'password',
		'class'=>'input_tip_contrasena',
		'id'=>'cambioPinNuevo',
		'maxlength'=>4,
		'minLength'=>4,
        'between'=>'</td><td class="campo"><div style="position:relative">',
		'after'=>'</div></td></tr>',	
	)
);


echo $form->input(
	'clave_confirmacion',
	array(
		'label'=>'<span class="requerido"> * </span>Confirmar clave:',
		'type'=>'password',
		'class'=>'',
        'maxlength'=>4
	)
);


if (!empty($errors)){
	
		echo '<tr><td>&nbsp;</td><td><p class="error-message">'. _m('pin.noPermitido').'</p></td></tr>';
}
if ($form->isFieldError('pin_nuevo')){
	echo '<tr><td>&nbsp;</td><td>'.$form->error('pin_nuevo',
		array(
			'requerido'=>_m('campo.requerido',array('la clave')),
			'numeric'=>_m('pin.invalido'),
			'consecutivos'=>_m('pin.consecutivo'),
			'minLength'=>_m('longitud.invalida',array('el pin','4','4')),
			'maxLength'=>_m('longitud.invalida',array('el pin','4','4'))
		)
	).'</td></tr>';
}else{
if ($form->isFieldError('clave_confirmacion')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('clave_confirmacion',
			array(
				'requerido'=>_m('campo.requerido',array('la confirmación de clave')),
				'confirmacionIgualQue'=>_m('no.coincide',array('la clave'))
			)).'</td></tr>';
}
}


echo '<tr><td colspan="2">&nbsp;</td></tr>';
	
	echo '<tr><td class="submit" colspan="2">';
		
		echo $html->link(
			'Volver',
			'/consultas/integral',
			array(
				'class'=>'button'
			)
		);
	
		echo '&nbsp;&nbsp;&nbsp;';

		echo $html->link(
			'Aceptar',
			'#',
			array(
				'class'=>'button envio_formulario',
				'div' => false
			)
		);



	echo '</td></tr></table>';

echo $form->end();

?>


</div>

<div style="margin-left:30px;width:340px;float:left;">
<?php echo _m('mensaje.cambio.tdd'); ?>
</div>


</div>

