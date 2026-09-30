<?php
/**
 * Formulario de cambio de estatus de los usuarios
**/
?>
<div>	
	<h2 class="user">Configuraci&oacute;n Usuarios - Cambiar Estatus</h2>
	<div class="line_title_orange"></div>
</div>
<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Cambio Estatus</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confimaci&oacute;n</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado Operaci&oacute;n</div>
<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0" align="center">
		<thead>
			<tr>
				<td colspan="2" class="resumen_paginacion"><p>A trav&eacute;s de esta opci&oacute;n usted podr&aacute; realizar el cambio de estatus del usuario.</p></td>
			</tr>
		</thead>	
		<tr>
			<td class="etiqueta">Identificaci&oacute;n del Usuario (login):</td>
			<td><?php echo $this->data['Usuario']['nomUsuario'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">C&eacute;dula de Identidad:</td>
			<td><?php echo $this->data['Usuario']['tipoCedulaRif'].'-'.$this->data['Usuario']['nroCedulaRif'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Nombre y Apellido:</td>
			<td><?php echo $this->data['Usuario']['nombre'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Cargo:</td>
			<td><?php echo $this->data['Usuario']['cargo'] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Email:</td>
			<td><?php echo $this->data['Usuario']['emailPri']; ?></td>
		</tr>
		<tr>
			<td class="etiqueta">N&uacute;mero de Tel&eacute;fono Celular:</td>
			<td>
			<?php echo $this->data['Usuario']['codigoCelular'].$this->data['Usuario']['nroCelular']; ?>
			</td>
		</tr>
		<tr>
			<td class="etiqueta">Perfil:</td>
			<td><?php echo $perfiles[$this->data['Usuario']['perfil']] ?></td>
		</tr>
		<tr>
			<td class="etiqueta">Estatus Actual:</td>
			<td><?php echo $array_estatus[$this->data['Usuario']['co_estado']] ?></td>
		</tr>
	</table>

	<?php
	echo $form->create('Usuario',
		array(
			'url'=> array(
				'controller'=>'empresa',
				'action'=>'cambio_form'
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
			'width'=>'550'
		)
	);
	
	
	echo '<tr><td colspan="2"></tr>';

	
	echo $form->input(
		'co_estado',
		array(
			'after'=>false,
			'label'=>'<span class="requerido">* </span>Estatus:',
			'type'=>'select',
			'empty'=>'Seleccione una opción',
			'options'=>array('P'=>'Pendiente')
		)
	);
	
	if ($form->isFieldError('co_estado')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('co_estado',
			array(
				'requerido'=>_m('seleccion.requerida',array('el estatus del usuario')),
				'valoresPermitidos'	=> _m('seleccion.requerida',array('el estatus del usuario'))
			)
		).'</td></tr>';
	}
        
	echo '<tr><td colspan="2"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>';
	echo '<div class="espacio_vertical_20"></div>';
	echo '</td></tr>';
	echo "<br>\n";
	echo '<tr><td colspan="2" class="botonera">';
	
	echo $html->link(
		'Volver',
		'/empresa/listado',
		array(
			'class'=>'button',
			'id'=>'volver'
		)
	);
	
	echo '&nbsp;&nbsp;&nbsp;';

	echo $html->link(
		'Aceptar',
		'#',
		array(
			'class'=>'button envio_formulario',
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
	$('#UsuarioCambioFormForm').validate({
		rules:{
            'data[Usuario][co_estado]':{
				required:true
			}
		},
		messages:{
			'data[Usuario][co_estado]':{
				required:<?php echo _m('seleccion.requerida',array('el estatus del usuario'),true) ?>
			}
		}
	});
});
</script>