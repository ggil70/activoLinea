<?php
/**
 * Formulario de cambio de contraseña, el cual se usa en el proceso de olvido de contraseña
**/
?>
<div>
<h2 class="user"><?php echo $estado['titulo'] ?></h2>
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

<div style="width:800px;margin:0 auto">
	
<?php
	if(isset($estado['mensaje'])){
		echo '<p>' . _m($estado['mensaje']) . '</p>';
	}
?>
	
<p>Modifique su clave personal y presione "Aceptar"</p>

<div style="width:400px;float:left">

<?php

echo $form->create('CambioClave',
	array(
		'url'=> array(
			'controller'=>'seguridad',
			'action'=>'olvido_clave'
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
			'requerido'=>_m('campo.requerido',array('la clave nueva')),
			'contrasena'=>_m('contrasena.invalida',array('la clave nueva')),
			'contrasena2'=>_m('clave.no.permitida'),
			'minLength'=>_m('longitud.invalida',array('la clave nueva','8','12')),
			'maxLength'=>_m('longitud.invalida',array('la clave nueva','8','12'))
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
				'requerido'=>_m('campo.requerido',array('la confirmación de la clave')),
				'confirmacionIgualQue'=>_m('no.coincide',array('la clave'))
			)).'</td></tr>';
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

<div style="margin-left:20px;width:360px;float:left">
<?php echo _m('olvido.clave');?>

</div>

</div>

<script>

$(function(){
	$('#CambioClaveOlvidoClaveForm').validate({
		rules:{
			'data[CambioClave][clave_nueva]':{
				required:true,
				minlength:8,
				maxlength:12,
				contrasena:true
			},
			'data[CambioClave][clave_confirmacion]':{
				required:true,
				equalTo: '#CambioClaveClaveNueva'
			}
		},
		messages:{
			'data[CambioClave][clave_nueva]':{
				required:<?php echo _m('campo.requerido', array('la clave'), true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('la clave','8','12'),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('la clave','8','12'),true) ?>,
				contrasena:<?php echo _m('contrasena.invalida',null,true) ?>
			},
			'data[CambioClave][clave_confirmacion]':{
				required:<?php echo _m('campo.requerido', array('la confirmación de la clave'), true) ?>,
				equalTo: <?php echo _m('no.coincide',array('la clave'),true) ?>
			}
		}
	});
});

</script>