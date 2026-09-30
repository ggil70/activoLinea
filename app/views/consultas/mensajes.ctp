<?php
    /**
     * Vista para mostrar los mensajes del cliente
     */
?>

<div>
<h2 class="user">Mensajes</h2>
<div class="line_title_orange"></div>
</div>

<?php
if ($resultados){
	echo $form->create(
		'Mensajes',
		array(
			'url'=> array(
				'controller'=>'consultas',
				'action'=>'mensajes'
			)
		)
	);
?>

<table class="tabla" cellpadding="0" cellspacing="0" style="width:350px;">
<thead>
	<tr>
		<td colspan="3" class="resumen_paginacion">
			<label for="data[Mensajes][chkdata][]" class="error manual"><?php echo _m('seleccion.requerida','al menos un mensaje') ?></label>
		</td>
	</tr>
	<tr>
		<td class="texto_centro" style="min-width:5px;width:5px;"><span class="requerido">*</span></td>
		<td style="width:12px;">Fecha</td>
		<td>Mensaje</td>
	</tr>
</thead>
<tbody>
<?php foreach ($resultados AS $r){ ?>
<tr>
<td class="texto_centro ancholibre">
	<input name="data[Mensajes][chkdata][]" type="checkbox" value="<?php echo $r['Mensajes']['id']; ?>" />
</td>
<td class="texto_centro"><?php echo date('d/m/Y', strtotime($r['M']['fecha_hora'])) ?></td>
<td><?php echo $r['M']['texto'] ?></td>
</tr>
<?php } ?>
<tr>
<td colspan="3" class="paginador"><?php echo $this->element('paginador',array('total'=>$total_registros)); ?></td>
</tr>

<?php
echo '<tr><td colspan="3" class="botonera">';

echo $form->submit(
	'Borrar el mensaje',
	array(
		'class'=>'button',
		'div'=> false
	)
);

echo '</td></tr>';
?>
</tbody>
</table>
<?php
echo $form->end();
}
else{
	echo '<div class="error-message texto_centro">'. _m('sin.mensajes') .'</div>';
}
?>

<script type="text/javascript">
$(function(){
	$('#MensajesMensajesForm').validate({
		rules:{
			'data[Mensajes][chkdata][]':{
				required:true
			}
		}
	});
});
</script>