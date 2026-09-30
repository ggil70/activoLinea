<?php
/**
 * Fragmento de página que se encarga de generar el teclado virtual numérico
**/

$teclas = range(0,9);
shuffle($teclas);
?>

<table cellpadding="0" cellspacing="0" border="0" class="teclado_virtual_numerico">
<tr>
<td><a class="tecla" valor="<?php echo $teclas[0]?>"><?php echo $teclas[0]?></a></td>
<td><a class="tecla" valor="<?php echo $teclas[1]?>"><?php echo $teclas[1]?></a></td>
<td><a class="tecla" valor="<?php echo $teclas[2]?>"><?php echo $teclas[2]?></a></td></tr>
<tr>
<td><a class="tecla" valor="<?php echo $teclas[3]?>"><?php echo $teclas[3]?></a></td>
<td><a class="tecla" valor="<?php echo $teclas[4]?>"><?php echo $teclas[4]?></a></td>
<td><a class="tecla" valor="<?php echo $teclas[5]?>"><?php echo $teclas[5]?></a></td></tr>
<tr>
<td><a class="tecla" valor="<?php echo $teclas[6]?>"><?php echo $teclas[6]?></a></td>
<td><a class="tecla" valor="<?php echo $teclas[7]?>"><?php echo $teclas[7]?></a></td>
<td><a class="tecla" valor="<?php echo $teclas[8]?>"><?php echo $teclas[8]?></a></td></tr>
<tr>
<td colspan="3" align="center"><a class="tecla" valor="<?php echo $teclas[9]?>"><?php echo $teclas[9]?></a></td></tr>
</table>

<script>
	var campoActualTecladoVirtualNumerico;
	
	$(function(){
		$('table.teclado_virtual_numerico').disableSelection();
		
		$('input.uso_teclado').focus(function(){
			campoActualTecladoVirtualNumerico = $(this);
		})
		.keypress(function(e){
			if(e.keyCode != 46 && e.keyCode != 8 && e.keyCode != 35 && e.keyCode != 36 &&  e.keyCode != 37 && e.keyCode != 38 && e.keyCode != 39 && e.keyCode != 40){
				return false;
			}		
		});
		
		if($('input.uso_teclado').size() > 0){
			campoActualTecladoVirtualNumerico = $('input.uso_teclado').first();
		}

		$('table.teclado_virtual_numerico a.tecla').click(function(){
			if(campoActualTecladoVirtualNumerico == undefined) return;
			
			if(campoActualTecladoVirtualNumerico.attr('maxlength') == campoActualTecladoVirtualNumerico.val().length) return;
			
			var x = $(this);
			var tecla;	
			
			tecla = x.attr('valor');
		
			campoActualTecladoVirtualNumerico.val(
				campoActualTecladoVirtualNumerico.val()
				+tecla
			);
		
			if(campoActualTecladoVirtualNumerico.hasClass('error')) {
				campoActualTecladoVirtualNumerico.valid();
			}
			
		});
		
		$('table.teclado_virtual_numerico a.tecla[valor]').mouseover(function(){
			$('table.teclado_virtual_numerico a.tecla[valor]').text('*');
		})
		.mouseout(function(){
			$('table.teclado_virtual_numerico a.tecla[valor]').each(function(i,e){
					$(e).text($(e).attr('valor'));
			});
		});
	});
</script>
