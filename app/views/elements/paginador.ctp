<?php
/**
 * Fragmento de página que se encarga de generar el paginador
**/

if(!isset($procesando)){
    $procesando=false;
}

if($total > Configure::read('Tamano.paginacion')){
$offset= 0;
$last_offset = floor($total / Configure::read('Tamano.paginacion')) * Configure::read('Tamano.paginacion');

if(isset($this->params['form']['offset'])){
	$offset = $this->params['form']['offset'];
}

$id = md5(time().rand());

?>
<input type="hidden" name="offset" value="0">
<span id="<?php echo $id ?>">
<span class="flecha_atras"></span>&nbsp;&nbsp;
<span class="flecha_atras2"></span>&nbsp;&nbsp;
<span class="paginador_rango"><?php 
if($total == 0){
	echo '0-0';
} else {
	echo ($offset+1).'-'.min($total,($offset+Configure::read('Tamano.paginacion')));
}	
?></span>&nbsp;
<span class="paginador_total">de <?php echo $total ?></span>&nbsp;&nbsp;
<span class="flecha_adelante"></span>&nbsp;&nbsp;
<span class="flecha_adelante2"></span>
</span>

<script>
$(function(){
	var procesando = '<?php echo $procesando ?>';
        
	<?php if($offset >= Configure::read('Tamano.paginacion')) { ?>
	$('#<?php echo $id ?> .flecha_atras').click(function(){
                if(procesando && $('#procesandoC')){
                    $('#procesandoC').modal({minHeight:120,minWidth:400,close:false});
                }
 
                $('input[name="offset"]')
		.val('0')
		.parents('form').submit();
	})
	.css('cursor','pointer');
	
	$('#<?php echo $id ?> .flecha_atras2').click(function(){
		if(procesando && $('#procesandoC')){
                    $('#procesandoC').modal({minHeight:120,minWidth:400,close:false});
                }
		$('input[name="offset"]')
		.val(<?php echo $offset - Configure::read('Tamano.paginacion')?>)
		.parents('form').submit();
	})
	.css('cursor','pointer');
	
	<?php } ?>
	
	<?php if( ($offset < $last_offset) && ( ($offset + Configure::read('Tamano.paginacion') ) != $total) ) { ?>
	
	$('#<?php echo $id ?> .flecha_adelante').click(function(){
		if(procesando && $('#procesandoC')){
                    $('#procesandoC').modal({minHeight:120,minWidth:400,close:false});
                }
		$('input[name="offset"]')
		.val(<?php echo $offset + Configure::read('Tamano.paginacion') ?>)
		.parents('form').submit();
	})
	.css('cursor','pointer');
	
	$('#<?php echo $id ?> .flecha_adelante2').click(function(){
		if(procesando && $('#procesandoC')){
                    $('#procesandoC').modal({minHeight:120,minWidth:400,close:false});
                }
		$('input[name="offset"]')
		.val(<?php if( $total % Configure::read('Tamano.paginacion') ) echo $last_offset; else echo $total-Configure::read('Tamano.paginacion'); ?>)
		.parents('form').submit();
	})
	.css('cursor','pointer');
	
	<?php } ?>
});
</script>
<?php } ?>