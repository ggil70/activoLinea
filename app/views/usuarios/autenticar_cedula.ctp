<?php
/**
 * Formulario de solicitud de cédula, el cual es utilizado en el proceso de
 * registro
**/
?>

<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
<?php if($this->Session->check('login.juridico')){ ?>
	<div class="paso_nro_actual">1</div>
	<div class="paso_desc_actual">Autenticar Empresa</div>
	<div class="paso_nro">2</div>
        <div class="paso_desc">Aceptaci&oacute;n de Contrato</div>	
	<div class="paso_nro">3</div>
	<div class="paso_desc">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Tarjeta de Coordenadas</div>
<?php }else{ ?>
	<div class="paso_nro_actual">1</div>	
        <div class="paso_desc_actual">Autenticaci&oacute;n del cliente</div>
	<div class="paso_nro">2</div>
        <div class="paso_desc">Aceptaci&oacute;n de Contrato</div>	
	<div class="paso_nro">3</div>
	<div class="paso_desc">Registro de Usuario</div>
	<div class="paso_nro">4</div>
        <div class="paso_desc">Código de Autorización</div>
        <div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">7</div>
	<div class="paso_desc">Configuraci&oacute;n de L&iacute;mites</div>
<?php } ?>	
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario_inicio">
	
	<?php
	
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'usuarios',
				'action'=>'autenticar_cedula'
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
			'align'=>'center',
			'width'=>'470px'
		)
	);
	
	?>
	
	<tr><td colspan="2">Ingrese los datos solicitados y luego presione el bot&oacute;n Aceptar.</td></tr>
	
	<div class="espacio_vertical_20"></div>
	
	<?php
	
	if($session->check('login.juridico')){
		echo $form->input(
			'tipoRif',
			array(
				'after'=>false,
				'label'=>'<span class="requerido" width="100px">* </span>R.I.F:',
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
					'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de R.I.F')),
					'requerido'=>_m('campo.requerido',array('el tipo de R.I.F'))
			)).'</td></tr>';
		} else if ($form->isFieldError('nroRif')){
			echo '<tr><td>&nbsp;</td><td>'.$form->error('nroRif',
				array(
					'requerido'=>_m('campo.requerido',array('el número de R.I.F')),
					'longitud'=>_m('formato.invalido','el número de R.I.F')
				)
			).'</td></tr>';
		}
	}
	
	echo $form->input(
		'tipoCedulaRif',
		array(
			'after'=>false,
			'label'=>'<span class="requerido" width="100px">* </span>C&eacute;dula de Identidad:',
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
			'maxlength'=>9
		)
	);		
	
	if ($form->isFieldError('tipoCedulaRif')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('tipoCedulaRif',
			array(
				'valoresPermitidos'=>_m('seleccion.invalida',array('el tipo de documento de identificación')),
				'requerido'=>_m('campo.requerido',array('el tipo de documento de identificación'))
		)).'</td></tr>';
	} else if ($form->isFieldError('nroCedulaRif')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('nroCedulaRif',
			array(
				'requerido'=>_m('campo.requerido',array('documento de identidad ')),
				'longitud'=>_m('formato.invalido','documento de identidad')
			)
		).'</td></tr>';
	}
	
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	
	echo '<tr><td colspan="2">&nbsp;</td></tr>';
	
	echo '<tr><td class="submit" colspan="2">';
		
        echo $html->link(
                'Volver',
                '/',
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

<script>
$(function(){
	
	// para la validacion
	$('#UsuarioAutenticarCedulaForm').validate({
		rules:{
			'data[Usuario][nroRif]':{
				required:true, 
				digits:true
			},
			'data[Usuario][nroCedulaRif]':{
				required:true, 
				digits:{
					depends: function(e){
						return $('#UsuarioTipoCedulaRif').val()=='V';
					}
				},
				alphanumeric:{
					depends: function(e){
						return $('#UsuarioTipoCedulaRif').val()=='P';
					}
				}
			}
		},
		messages:{
			'data[Usuario][nroCedulaRif]':{
				required: <?php echo _m('campo.requerido',array('el documento de identidad'),true) ?>,
				digits: <?php echo _m('formato.invalido','el documento de identidad',true) ?>,
				alphanumeric: <?php echo _m('formato.invalido',array('el documento de identidad'),true) ?>
			},
			'data[Usuario][nroRif]':{
				required: <?php echo _m('campo.requerido',array('el número de R.I.F'),true) ?>,
				digits: <?php echo _m('formato.invalido','el número de R.I.F',true) ?>,
				alphanumeric: <?php echo _m('formato.invalido',array('el número de R.I.F'),true) ?>
			}
		}
	});
	
});
</script>
