<?php
/**
 * Listado de productos propios con sus respectivos alias
**/
?>
<div>
<h2 class="user">Asignar Alias de Productos</h2>
<div class="line_title_orange"></div>
</div>

<div class="contenedor_formulario">
<?php
if(count($arrData)){
	echo $form->create('AliasProducto',
		array(
			'url'=> array(
				'controller'=>'otros_servicios',
				'action'=>'alias'
			),
			'inputDefaults'=>array(
				'div'=>false,
				'label'=>false,
				'before'=>false,
				'between'=>false,
				'after'=>false,
				'error'=>false
			)
		)
	);

	echo '<div class="texto_centro">';
	echo '<label for="data[AliasProducto][cuenta]" class="error manual">'. _m('seleccion.requerida', 'un producto') . '</label>';
	if ($form->isFieldError('cuenta')){
		echo '<p>'.$form->error('cuenta',array(
			'requerido'=>_m('seleccion.requerida','un producto')
		)).'</p>';
	}
	echo '</div>';

	echo $form->hidden('cuenta');
	?>
	<div class="espacio_vertical_10"></div>

	<table class="tabla" cellpadding="0" cellspacing="0" align="center">
	<thead>
		<tr>
			<td class="ancholibre texto_centro"><span class="requerido">* </span></td>
			<td>Nombre</td>
			<td>Número</td>
			<td>Alias</td>
		</tr>
	</thead>
	<tbody>
		<?php 
		foreach ($arrData as $arr){
			$arr = array_map('trim', $arr);
		?>
		<tr>
			<td class="texto_centro ancholibre">
				<input type="radio" name="data[AliasProducto][cuenta]" value="<?php echo $arr['SUMACN']; ?>"
					<?php
					if( isset($this->data['AliasProducto']['cuenta']) && 0==strcmp($this->data['AliasProducto']['cuenta'],$arr['SUMACN']) ){
						echo 'checked="checked"';
					}
					?>
				/>
			</td>
			<td class="texto_centro"><?php echo $arr['SUMDSC']; ?></td>
			<td class="texto_centro"><?php echo enmascara($arr['SUMACN']); ?></td>
			<td class="texto_centro"><?php echo _a($userid, $arr['SUMACN']); ?></td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr><td colspan="4" style="border:0;"><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>
		<tr>
			<td class="botonera" colspan="4">
				<?php
				echo $form->submit(
					'Aceptar',
					array(
						'class'=>'button',
						'div'=>false
					)
				);
				?>
			</td>
		</tr>
	</tfoot>
	</table>

<?php
	echo $form->end();
}
else{
	echo _m('error.fatal');
}
?>

</div>

<script type="text/javascript">
$(function(){
	$('#AliasProductoAliasForm').validate({
		rules:{
			'data[AliasProducto][cuenta]':{
				required:true
			}
		}
	});
});
</script>