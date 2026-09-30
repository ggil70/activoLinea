<?php
/**
 * Fragmento de página que se encarga de generar el teclado virtual completo
**/

$teclas = range(0,9);
shuffle($teclas);
?>

<table class="teclado_virtual" cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td>
<a class="tecla2">!</a>
<a class="tecla2">"</a>
<a class="tecla2">#</a>
<a class="tecla2">$</a>
<a class="tecla2">%</a>
<a class="tecla2">&amp;</a>
<a class="tecla2">/</a>
<a class="tecla2">(</a>
<a class="tecla2">)</a>
<a class="tecla2">=</a>
<a class="tecla2">?</a>
<a class="tecla2">¡</a>
<br>
<a class="tecla2">q</a>
<a class="tecla2">w</a>
<a class="tecla2">e</a>
<a class="tecla2">r</a>
<a class="tecla2">t</a>
<a class="tecla2">y</a>
<a class="tecla2">u</a>
<a class="tecla2">i</a>
<a class="tecla2">o</a>
<a class="tecla2">p</a>
<a class="tecla3">borrar</a>
<br>
<a class="tecla3">May</a>
<a class="tecla2">a</a>
<a class="tecla2">s</a>
<a class="tecla2">d</a>
<a class="tecla2">f</a>
<a class="tecla2">g</a>
<a class="tecla2">h</a>
<a class="tecla2">j</a>
<a class="tecla2">k</a>
<a class="tecla2">l</a>
<br>
<a class="tecla2">z</a>
<a class="tecla2">x</a>
<a class="tecla2">c</a>
<a class="tecla2">v</a>
<a class="tecla2">b</a>
<a class="tecla2">n</a>
<a class="tecla2">m</a>
<br>
</td>
<td width="100%">&nbsp;</td>
<td>
<a class="tecla2" valor="<?php echo $teclas[0] ?>"><?php echo $teclas[0] ?></a>
<a class="tecla2" valor="<?php echo $teclas[1] ?>"><?php echo $teclas[1] ?></a>
<a class="tecla2" valor="<?php echo $teclas[2] ?>"><?php echo $teclas[2] ?></a>
<br>
<a class="tecla2" valor="<?php echo $teclas[3] ?>"><?php echo $teclas[3] ?></a>
<a class="tecla2" valor="<?php echo $teclas[4] ?>"><?php echo $teclas[4] ?></a>
<a class="tecla2" valor="<?php echo $teclas[5] ?>"><?php echo $teclas[5] ?></a>
<br>
<a class="tecla2" valor="<?php echo $teclas[6] ?>"><?php echo $teclas[6] ?></a>
<a class="tecla2" valor="<?php echo $teclas[7] ?>"><?php echo $teclas[7] ?></a>
<a class="tecla2" valor="<?php echo $teclas[8] ?>"><?php echo $teclas[8] ?></a>
<br>
<a class="tecla2" valor="<?php echo $teclas[9] ?>"><?php echo $teclas[9] ?></a>
</td>
</tr>
</table>

<script>
var campoActualTecladoVirtual;
var mayusculaTecladoVirtual = false;
	
$(function(){
	$('table.teclado_virtual').disableSelection();
	
	$('input.uso_teclado').focus(function(){
		campoActualTecladoVirtual = $(this);
	})
	.keypress(function(){
		return false;		
	})
	.keydown(function(){
		return false;		
	})
	.keyup(function(){
		return false;		
	})
	
	if($('input.uso_teclado').size() > 0){
		campoActualTecladoVirtual = $('input.uso_teclado').first();
	}

	$('table.teclado_virtual a.tecla2').click(function(){
		if(campoActualTecladoVirtual == undefined) return;
		
		if(campoActualTecladoVirtual.attr('maxlength') == campoActualTecladoVirtual.val().length) return;
		
		var x = $(this);
		var tecla;
		
		if(x.attr('valor') != undefined){
			tecla = x.attr('valor');
		} else {
			tecla = x.text();
		}
	
		campoActualTecladoVirtual.val(
			campoActualTecladoVirtual.val()
			+tecla
		);
		
		if(campoActualTecladoVirtual.hasClass('error')) {
			campoActualTecladoVirtual.valid();
		}		
	});

	$('table.teclado_virtual a.tecla3').click(function(){
		if(campoActualTecladoVirtual == undefined) return;
	
		var x = $(this);
		
		if("borrar"==x.text()){
			campoActualTecladoVirtual.val(
				campoActualTecladoVirtual.val().substring(
					0,
					campoActualTecladoVirtual.val().length-1
				)
			);
		
			if(campoActualTecladoVirtual.hasClass('error')) {
				campoActualTecladoVirtual.valid();
			}
		} else if("May"==x.text()){
			mayusculaTecladoVirtual = !mayusculaTecladoVirtual;
			
			$('.tecla2').each(function(){
				if(mayusculaTecladoVirtual){
					$(this).text($(this).text().toUpperCase());
				} else {
					$(this).text($(this).text().toLowerCase());
				}
			});			
		}
		
	});
	
	$('a.tecla2[valor]').mouseover(function(){
		$('a.tecla2[valor]').text('*');
	})
	.mouseout(function(){
		$('a.tecla2[valor]').each(function(i,e){
				$(e).text($(e).attr('valor'));
		});
	});
});
</script>