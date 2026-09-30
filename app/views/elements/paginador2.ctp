<?php
/**
 * Fragmento de página que se encarga de generar el paginador
**/

if(!isset($tamano_paginacion)){
	$tamano_paginacion=Configure::read('Tamano.paginacion');
}

if($total > $tamano_paginacion){
$offset= 0;
$last_offset = floor($total / $tamano_paginacion) * $tamano_paginacion;

if(count($this->data)){
	
	$data = array_values($this->data);

	if(isset($data[0]['offset'])){
		$offset = $data[0]['offset'];
	}

}


$id = md5(time().rand());

?>
<span id="<?php echo $id ?>">
<span class="flecha_atras"></span>&nbsp;&nbsp;
<span class="flecha_atras2"></span>&nbsp;&nbsp;
<span class="paginador_rango"><?php 
if($total == 0){
	echo '0-0';
} else {
	echo ($offset+1).'-'.min($total,($offset+$tamano_paginacion));
}	
?></span>&nbsp;
<span class="paginador_total">de <?php echo $total ?></span>&nbsp;&nbsp;
<span class="flecha_adelante"></span>&nbsp;&nbsp;
<span class="flecha_adelante2"></span>
</span>

<script>
$(function(){
	
	<?php if($offset >= $tamano_paginacion) { ?>
	$('#<?php echo $id ?> .flecha_atras').click(function(){
		$('#<?php echo $form_id ?> input[name$="[offset]"]')
		.val('0');
		
		$('#<?php echo $form_id ?>').submit();
	})
	.css('cursor','pointer');
	
	$('#<?php echo $id ?> .flecha_atras2').click(function(){
		$('#<?php echo $form_id ?> input[name$="[offset]"]')
		.val(<?php echo $offset - $tamano_paginacion ?>);
		
		$('#<?php echo $form_id ?>').submit();
	})
	.css('cursor','pointer');
	
	<?php } ?>
	
	<?php if( ($offset < $last_offset) && ( ($offset + $tamano_paginacion ) != $total) ) { ?>
	
	$('#<?php echo $id ?> .flecha_adelante').click(function(){
		$('#<?php echo $form_id ?> input[name$="[offset]"]')
		.val(<?php echo $offset + $tamano_paginacion ?>);
		
		$('#<?php echo $form_id ?>').submit();
	})
	.css('cursor','pointer');
	
	$('#<?php echo $id ?> .flecha_adelante2').click(function(){
		$('#<?php echo $form_id ?> input[name$="[offset]"]')
		.val(<?php if( $total % $tamano_paginacion ) echo $last_offset; else echo $total-$tamano_paginacion; ?>);
		
		$('#<?php echo $form_id ?>').submit();
	})
	.css('cursor','pointer');
	
	<?php } ?>
});
</script>
<?php } ?>