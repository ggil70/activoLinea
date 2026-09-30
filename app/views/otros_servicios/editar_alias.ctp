<?php
/**
 * Formulario de asignación de alias a un producto propio
**/
?>
<div>
<h2 class="user">Asignar Alias de Productos</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	<?php

	echo $form->create('AliasProducto',
		array(
			'url'=> array(
				'controller'=>'otros_servicios',
				'action'=>'editar_alias'
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
	
	echo $form->hidden('id');
	echo $form->hidden('cuenta');

	echo $html->tag(
		'table',
		null,
		array(
			'class'=>'formulario',
			'cellpadding'=>'0',
			'cellspacing'=>'0',
			'border'=>'0',
			'align'=>'center',
			'style'=>'table-layout:fixed'
		)
	);
	
	// La primera fila define la tabla con layout fixed
	echo '<tr><td style="width:100px"></td><td style="width:250px"></td></tr>';
?>	
<tr>
	<td class="azul">N&uacute;mero:</td>
	<td><?php echo enmascara($this->data['AliasProducto']['cuenta']); ?></td>
</tr>
<?php	
	echo $form->input(
		'alias',
		array(
			'label'=>'Alias:',
			'type'=>'text',
			'class'=>'alfanumericoEspacio',
			'maxlength'=>30
		)
	);

	if ($form->isFieldError('alias')){
		echo '<tr><td>&nbsp;</td><td>'.$form->error('alias', array(
			'requerido'=>_m('campo.requerido','el alias'),
			'alfanumerico'=>_m('alfanumerico.invalido','el alias'),
			'alias_nodisponible'=>_m('alias.nodisponible')
		)).'</td></tr>';
	}

	echo '<tr><td>&nbsp;</td><td class="botonera">';
	
	echo $html->link(
		'Volver',
		'alias',
		array(
			'class'=>'button'
		)
	);
	
	echo '&nbsp;&nbsp;';
	
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
	$('#AliasProductoEditarAliasForm').validate({
		rules:{
			'data[AliasProducto][alias]':{
				alphaNumericSpace:true
			}
		},
		messages:{
			'data[AliasProducto][alias]':{
				alphaNumericSpace:<?php echo _m('alfanumerico.invalido','el alias', true) ?>
			}
		}
	});
});
</script>