<?php
/**
 * Formulario de cambio de datos de contacto de la banca en línea
**/
?>
<div>
<h2 class="user">Cambiar Datos Contacto</h2>
<div class="line_title_orange"></div>
</div>
<div class="paso_nro">1</div>
<div class="paso_desc">Tarjeta de Coordenadas</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro">4</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>


<div class="contenedor_formulario" style="width:550px;">
	
	<?php
	
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'seguridad',
				'action'=>'cambiar_datos'
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
                        'style'=>'table-layout:fixed;'
		)
	);
        
        // La primera fila define la tabla con layout fixed
        echo '<tr><td style="width:150px"></td><td style="width:150px"></td></tr>';
	
	echo '<thead>
	<tr>
	<td colspan="2" class="resumen_paginacion">A trav&eacute;s de esta opci&oacute;n usted podr&aacute; realizar la actualizaci&oacute;n de los datos que se indican a continuaci&oacute;n</td>
	</tr>
	</thead>';
	
	echo $form->input(
		'emailPri',
		array(
			'label'=>'<span class="requerido">* </span>E-mail Principal:',
			'type'=>'text',
                        'maxlength'=> 50
		)
	);

	if ($form->isFieldError('emailPri')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('emailPri',
			array(
				'correo'=>_m('correo.invalido',array('el email principal')),
				'requerido'=>_m('campo.requerido',array('el email principal'))
			)
		).'</td></tr>';
	}
	
	
	
	echo $form->input(
		'emailSec',
		array(
			'label'=>'<span class="estiloTexto"></span>E-mail Secundario:',
			'type'=>'text',
                        'maxlength'=> 50 
			
		)
	);

	if ($form->isFieldError('emailSec')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('emailSec',
			array(
				'correo'=>_m('correo.invalido',array('el email secundario'))
			)
		).'</td></tr>';
	}
	
	echo $form->input(
			'codigoCelular',
			array(
				'after'=>false,
				'label'=>'<span class="requerido" width="100px">* </span>N&uacute;mero Celular:',
				'type'=>'select',
				'class'=>'codigoCelular',
				'options'=>array('0414'=>'0414','0424'=>'0424','0416'=>'0416','0426'=>'0426','0412'=>'0412','0422'=>'0422')
			)
		);

		echo $form->input(
			'nroCelular',
			array(
				'before'=>false,
				'between'=>false,
				'label'=>false,
				'type'=>'text',
				'class'=>'nroCelular numerico',
				'maxlength'=>7
			)
		);	
		
	if ($this->Form->isFieldError('codigoCelular')){
		echo $form->error('codigoCelular',
			array(
				'requerido'	=> _m('campo.requerido',array('el código del celular')),
				'valoresPermitidos'	=> _m('seleccion.requerida',array('el código del celular'))
			)
		);
	}

	if ($form->isFieldError('nroCelular')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('nroCelular',
			array(
				'valor'=>_m('formato.invalido',array('el número celular')),
				'requerido'=>_m('campo.requerido',array('el número celular'))
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



<script>
$(function(){
	
	// para la validacion
	$('#UsuarioCambiarDatosForm').validate({
		rules:{
			'data[Usuario][nroCelular]':{
				required:true, celular:true
			},
			'data[Usuario][emailPri]':{
				required:true, email:true
			},
			'data[Usuario][emailSec]':{
				email:true
			}
			
		},
		messages:{
			'data[Usuario][nroCelular]':{
				required:<?php echo _m('campo.requerido',array('el número de celular'),true) ?>,
				celular:<?php echo _m('telefono.invalido',array('el número de celular'),true) ?>
			},
			'data[Usuario][emailPri]':{
				required:<?php echo _m('campo.requerido',array('el email principal'),true) ?>,
				email:<?php echo _m('correo.invalido',array('el email principal'),true) ?>
			},
			'data[Usuario][emailSec]':{
				email:<?php echo _m('correo.invalido',array('el email secundario'),true) ?>
			}
			
		}
	});
	
});
</script>
