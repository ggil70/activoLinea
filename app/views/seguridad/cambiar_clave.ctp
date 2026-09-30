<?php
/**
 * Formulario de cambio de clave de acceso a la banca en línea
**/
?>
<div>
<h2 class="user">Cambiar Clave Acceso Activo en L&iacute;nea</h2>
<div class="line_title_orange"></div>
</div>

<div style="width:800px;margin:0 auto">

<?php if($this->Session->check('usuarios.clave.vencida')) { ?>
<p class="error-message"><?php echo _m('usuario.clave.vencida') ?></p>
<?php } else { ?>
<p>Modifique su clave personal y presione "Aceptar"</p>
<?php } ?>
<div style="width:400px;float:left">

<?php

echo $form->create('CambioClave',
	array(
		'url'=> array(
			'controller'=>'seguridad',
			'action'=>'cambiar_clave'
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
		'border'=>'0'
	)
);	

echo $form->input(
	'clave_actual',
	array(
		'label'=>'<span class="requerido">* </span>Clave original:',
		'type'=>'password',
		'class'=>'',
        'maxlength'=>12
	)
);

if ($form->isFieldError('clave_actual')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('clave_actual',
			array(
				'requerido'=>_m('campo.requerido',array('la clave')),				
				'minLength'=>_m('longitud.invalida',array('la clave','8','12')),
				'maxLength'=>_m('longitud.invalida',array('la clave','8','12'))
			)
		).'</td></tr>';
}	

echo $form->input(
	'clave_nueva',
	array(
		'label'=>'<span class="requerido">* </span>Clave nueva:',
		'type'=>'password',
		'class'=>'input_tip_contrasena',
        'maxlength'=>12,
		'between'=>'</td><td class="campo"><div style="position:relative">',
		'after'=>'</div></td></tr>',	
	)
);

echo $this->element('tip_contrasena');

if ($form->isFieldError('clave_nueva')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('clave_nueva',
			array(
				'requerido'=>_m('campo.requerido',array('la clave')),
				'contrasena'=>_m('contrasena.invalida'),
				'contrasena2'=>_m('clave.no.permitida'),
				'minLength'=>_m('longitud.invalida',array('la clave','8','12')),
				'maxLength'=>_m('longitud.invalida',array('la clave','8','12'))
			)
		).'</td></tr>';
}

echo $form->input(
	'clave_confirmacion',
	array(
		'label'=>'<span class="requerido">* </span>Confirmar clave:',
		'type'=>'password',
		'class'=>'',
        'maxlength'=>12
	)
);

if ($form->isFieldError('clave_confirmacion')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('clave_confirmacion',
			array(
				'requerido'=>_m('campo.requerido',array('la clave nueva')),
				'confirmacionIgualQue'=>_m('no.coincide',array('la clave'))
			)).'</td></tr>';
}

echo $form->input(
	'tiempoCaduca',
	array(
		'after'=>false,
		'label'=>'<span class="requerido">* </span>Tiempo de Caducidad:',
		'type'=>'select',
		'class'=>'tiempoCaduca',
		'options'=>array('30'=>'30 días','60'=>'60 días','90'=>'90 días','180'=>'180 días'),
		'empty'=>'Seleccione'
	)
);

if ($form->isFieldError('tiempoCaduca')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('tiempoCaduca',
			array(
				'requerido'=>_m('seleccion.requerida',array('el tiempo de caducidad')),
				'valoresPermitidos'=>_m('seleccion.invalida',array('el tiempo de caducidad')),
			)
		).'</td></tr>';
}

echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';


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

<div style="margin-left:40px;width:340px;float:left;">
La clave debe contener s&oacute;lo caracteres alfanum&eacute;ricos y los siguientes especiales: ! " # $ % &amp; / ( ) = ? ¡ <br/> M&iacute;nimo 8, m&aacute;ximo 12 caracteres.
</div>


</div>

<script type="text/javascript">
$(function(){	
	$('#CambioClaveCambiarClaveForm').validate({
		rules:{
			'data[CambioClave][clave_actual]':{
				required:true,
				minlength:8,
				maxlength:12				
			},
			'data[CambioClave][clave_nueva]':{
				required:true,
				minlength:8,
				maxlength:12,
				contrasena:true
			},
			'data[CambioClave][clave_confirmacion]':{
				required:true,
				equalTo: '#CambioClaveClaveNueva'
			},
			'data[CambioClave][tiempoCaduca]':{
				required:true
			}
		},
		messages:{
			'data[CambioClave][clave_actual]':{
				required:<?php echo _m('campo.requerido','la clave actual',true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('la clave actual','8','12'),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('la clave actual','8','12'),true) ?>,
				contrasena:<?php echo _m('contrasena.invalida',null,true) ?>
			},
			'data[CambioClave][clave_nueva]':{
				required:<?php echo _m('campo.requerido',array('la clave'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('la clave','8','12'),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('la clave','8','12'),true) ?>,
				contrasena:<?php echo _m('contrasena.invalida',null,true) ?>
			},
			'data[CambioClave][clave_confirmacion]':{
				required:<?php echo _m('campo.requerido','la confirmación de la clave',true) ?>,
				equalTo: <?php echo _m('no.coincide','la clave',true) ?>
			},
			'data[CambioClave][tiempoCaduca]':{
				required:<?php echo _m('seleccion.requerida',array('el tiempo de caducidad'),true) ?>
			}
		}
	});
});

</script>