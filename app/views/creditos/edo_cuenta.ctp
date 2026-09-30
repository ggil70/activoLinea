<?php
    /**
     *  Vista utilizada para la selección de estados de cuenta que se deseen consultar
     */
?>

<div>
<h2 class="user">Estado de Cuenta</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario">

<?php

echo $form->create(
	'DetalleProducto',
	array(
		'url'=> array(
			'controller'=>'creditos',
			'action'=>'exportar_estado'
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

echo $form->hidden('nro_credito');

if(count($opciones)){ // verifica que existan meses que mostrar
	
	echo '<tr><td colspan="2" class="mensaje">Seleccione el mes del estado de cuenta que desea consultar</td></tr>';
	echo '<tr><td class="etiqueta">Crédito:</td><td>'. $this->data['DetalleProducto']['nro_credito'] .'</td></tr>';

	echo $form->input(
		'fechaHasta',
		array(
			'label'=>'Mes:',
			'type'=>'select',
			'options'=>$opciones,
			'empty'=>false
		)
	);
}
else{
	echo '<tr><td colspan="2" class="mensaje">' . _m('edocuenta.nodisponible') . '</td></tr>';
}

echo '<tr><td colspan="2" class="espacio_vertical_10"></td></tr>';
echo '<tr><td colspan="2" class="submit">';
	
	echo $html->link(
		'Volver',
		'#',
		array(
			'class'=>'button',
			'id'=>'volver'
		)
	);

	if(count($opciones)){ // verifica que existan meses que mostrar

		echo '&nbsp;&nbsp;';
		
		echo $html->link(
			'Descargar',
			'#',
			array(
				'class'=>'button envio_formulario'
			)
		);
	}

echo '</td></tr></table>';

echo $form->end();

?>

</div>

<div class="botonera">
<form class="formulario_enlace" id="formulario_volver" method="post" action="<?php echo Router::url('/creditos/movimientos') ?>">
		<input type="hidden" name="data[DetalleProducto][nro_credito]" value="<?php echo $this->data['DetalleProducto']['nro_credito'] ?>" />
</form>
</div>
<script type="text/javascript">
$(function(){
	$('#volver').click(function(){
		$('#formulario_volver').submit();
		return false;
	});
});
</script>