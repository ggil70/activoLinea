<?php
/**
 * Formulario de solicitud de cédula de identidad en el proceso de olvido
 * de algún dato
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

<div class="contenedor_formulario">

	<?php

	echo $form->create('Olvido',
		array(
			'url'=> array(
				'controller'=>'seguridad',
				'action'=>'olvido_cedula'
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
	
	echo $form->hidden('tipo', array('value'=>$estado['tipo']));

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
	
	echo '<tr><td colspan="2" class="mensaje">A continuación usted puede recuperar la clave sello, usuario ó  las Preguntas y Respuestas de Desafío de ingreso al servicio Activo en Línea</td></tr>';
	
	// PARA CLIENTE JURIDICO
	if( $this->Session->check('login.juridico') ){
		echo $form->input(
			'tipoRif',
			array(
				'after'=>false,
				'label'=>'<span class="requerido">* </span>RIF:',
				'type'=>'select',
				'class'=>'tipoRif',
				'options'=>array('J'=>'J','G'=>'G','V'=>'V','E'=>'E')
			)
		);

		echo $form->input(
			'nroRif',
			array(
				'before'=>false,
				'between'=>false,
				'label'=>false,
				'type'=>'text',
				'class'=>'nroRif',
				'maxlength'=>9
			)
		);
		
		if ($form->isFieldError('tipoRif')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('tipoRif',
					array(
						'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de RIF')),
						'requerido'=>_m('campo.requerido',array('el tipo de RIF')))).'</td></tr>';
		}

		if ($form->isFieldError('nroRif')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('nroRif',
				array(
					'requerido'=>_m('campo.requerido',array('el RIF')),
					'longitud'=>_m('valor.invalido',array('el RIF'))
				)).'</td></tr>';
		}

	}
	// PARA CLIENTE JURIDICO
	
	echo $form->input(
		'tipoCedulaRif',
		array(
			'after'=>false,
			'label'=>'<span class="requerido">* </span>C&eacute;dula de identidad:',
			'type'=>'select',
			'class'=>'tipoCedulaRif',
			'options'=>array('V'=>'V','E'=>'E','P'=>'P')
		)
	);

	echo $form->input(
		'nroCedulaRif',
		array(
			'before'=>false,
			'between'=>false,
			'label'=>false,
			'type'=>'text',
			'class'=>'nroCedulaRif',
			'maxlength'=>15
		)
	);

	if ($form->isFieldError('tipoCedulaRif')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('tipoCedulaRif',
				array(
					'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de documento de identificación')),
					'requerido'=>_m('campo.requerido',array('el tipo de documento de identificación')))).'</td></tr>';
	}
	
	// PARA CLIENTE JURIDICO
	if( $this->Session->check('login.juridico') && $estado['tipo']!='usuario' ){
		echo $form->input(
			'loginUsuario',
			array(
				'label'=>'<span class="requerido">* </span>Usuario (login):',
				'type'=>'text',
				'maxlength'=>12,
				'class'=>'input_login',
				'between'=>'</td><td class="campo">',
				'after'=>'</td>',
			)
		);
		
		if ($form->isFieldError('loginUsuario')){
			echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('loginUsuario',
			array(
				'requerido'	=> _m('campo.requerido',array('el usuario')),
				'minLength'	=> _m('longitud.invalida',array('el usuario', 8,'12')),
				'maxLength'	=> _m('longitud.invalida',array('el usuario', 8,'12')),
				'usuario.no.existe'	=> _m('usuario.no.existe'),
			)).'</td></tr>';
		}
	}
	
	if ($form->isFieldError('nroCedulaRif')){
		echo '<tr><td class="etiqueta">&nbsp;</td><td class="campo">'.$form->error('nroCedulaRif',
				array(
					'requerido'=>_m('campo.requerido',array('la cédula de identidad')),
					'longitud'=>_m('valor.invalido',array('la cédula de identidad')),
					'cedula.no.existe'=>_m('cedula.no.existe')
				)).'</td></tr>';
	}
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td>&nbsp;</td><td class="submit">';
	
	echo $html->link(
		'Volver',
		'/seguridad/olvido_tipo',
		array(
			'class'=>'button'
		)
	);
	
	echo '&nbsp;&nbsp;&nbsp;';

	echo $html->link(
		'Aceptar',
		'#',
		array(
			'div'=>false,
			'class'=>'button envio_formulario'
		)
	);		

	echo '</td></tr></table>';

	echo $form->end();

	?>

</div>

<script>
$(function(){

	// para la validacion
	$('#OlvidoOlvidoCedulaForm').validate({
		rules:{
			'data[Olvido][nroCedulaRif]':{
				required:true,
				digits:{
					depends: function(e){
						return $('#OlvidoTipoCedulaRif').val()=='V';
					}
				},
				alphanumeric:{
					depends: function(e){
						return $('#OlvidoTipoCedulaRif').val()=='P';
					}
				}
			},
			'data[Olvido][nroRif]':{
				required:true, digits:true
			},
			'data[Olvido][loginUsuario]':{
				required:true,
				minlength:8,
				maxlength:12
			}
		},
		messages:{
			'data[Olvido][nroCedulaRif]':{
				required:<?php echo _m('campo.requerido',array('la cédula de identidad'),true) ?>,
				digits:<?php echo _m('formato.invalido',array('la cédula de identidad'),true) ?>,
				alphanumeric:<?php echo _m('formato.invalido',array('el pasaporte'),true) ?>
			},
			'data[Olvido][nroRif]':{
				required:<?php echo _m('campo.requerido',array('el RIF'),true) ?>,
				digits:<?php echo _m('formato.invalido',array('el RIF'),true) ?>
			},
			'data[Olvido][loginUsuario]':{
				required:<?php echo _m('campo.requerido',array('el usuario'),true) ?>,
				minlength:<?php echo _m('longitud.invalida',array('el usuario', 8, 12),true) ?>,
				maxlength:<?php echo _m('longitud.invalida',array('el usuario', 8, 12),true) ?>
			}
		}
	});
	
});
</script>