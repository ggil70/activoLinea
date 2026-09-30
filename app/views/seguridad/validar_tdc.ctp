<?php
/**
 * Formulario de validación de tarjeta de coordenadas
**/

$filas = Configure::read('filas.tdc');
?>
<div>
<h2 class="user"><?php echo $estado['titulo']; ?></h2>
<div class="line_title_orange"></div>
</div>

<div>
	<?php for($i = 0; $i < count($estado['pasos']); $i++) { 
	$class = ($i==$estado['paso_actual']) ? '_actual' : '';
	?>
	<div class="paso_nro<?php echo $class ?>"><?php echo $i+1 ?></div>
	<div class="paso_desc<?php echo $class ?>"><?php echo $estado['pasos'][$i] ?></div>
	<?php } ?>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<?php

echo $form->create('Tdc',
	array(
		'url'=> array(
				'controller'=>'seguridad',
				'action'=>'validar_tdc'
			)
	)
);

$teclas = range(0,9);
shuffle($teclas);
?>

<table cellpadding="0" cellspacing="0" border="0" class="formulario" width="650px" align="center">
<tr>
	<td class="mensaje">
	Por favor verifique que el n&uacute;mero de serial <span class="error-message"><b>&quot;<?php echo $serial ?>&quot;</b></span> corresponda a la Tarjeta de Coordenadas,
	en caso contrario por favor comun&iacute;quese con nuestro Centro de Atenci&oacute;n Activa al 0500-ACTIVAT (2284828).
	Ingrese las coordenadas que se solicitan a continuaci&oacute;n:
	</td>
</tr>
<tr><td>&nbsp;</td></tr>
<tr><td align="center">
<table cellpadding="0" cellspacing="0" border="0" class="validador_tdc">
<tr>
	<td class="etiqueta" align="left">
		<?php echo $form->label('coordenada1',$filas[$coordenadas[0]['Row']+1].($coordenadas[0]['Column']+1)) ?>
	</td>
        <td class="etiqueta" align="left">
		<?php echo $form->label('coordenada1',$filas[$coordenadas[1]['Row']+1].($coordenadas[1]['Column']+1)) ?>
	</td>
</tr>
<tr>
	<td align="left">
		<?php
		echo $form->input(
			'coordenada1',
			array(
                                'id'=>'coordenada1',
				'a'=>false,
				'div'=>false,
				'label'=>false,
				'before'=>'',
				'between'=>'',
				'after'=>'',
				'error'=>false,
				'type'=>'password',
				'class'=>'coordenada numerico',
				'maxlength'=>3
			)
		);
		?>
	</td>
        <td align="left">
		<?php
		echo $form->input(
			'coordenada2',
			array(
                                'id'=>'coordenada2',
				'a'=>false,
				'label'=>false,
				'div'=>false,
				'before'=>'',
				'between'=>'',
				'after'=>'',
				'error'=>false,
				'type'=>'password',
				'class'=>'coordenada numerico',
				'maxlength'=>3
			)
		);
		?>
	</td>
</tr>
</table>
</td></tr>
<tr>
<td class="texto_centro">
<div><label for="TdcCoordenada1" class="error manual"><?php echo _m('campo.requerido','la coordenada') ?></label></div>
<div><label for="TdcCoordenada2" class="error manual"><?php echo _m('campo.requerido','la coordenada') ?></label></div>
<?php if($form->isFieldError('coordenada1')) {
	echo '<div>'.$form->error('coordenada1',array('requerido'=>_m('campo.requerido', 'la coordenada'), 'coordenada'=>_m('formato.invalido', 'la coordenada'))).'</div>';
} ?>
<?php if($form->isFieldError('coordenada2')) {
	echo '<div>'.$form->error('coordenada2',array('requerido'=>_m('campo.requerido', 'la coordenada'), 'coordenada'=>_m('formato.invalido', 'la coordenada'))).'</div>';
} ?>
</td>
</tr>
<tr>
<td class="submit" align="center">
<?php
echo $form->submit(
	'Aceptar',
	array(
		'class'=>'button bloqueo_repetir',
                'id'=>'aceptar'
	)
);	
?>
</td>
</tr>

</table>

<?php	echo $form->end(); ?>
</div>
<div class="basic-modal-content" id="procesando">
	<h3>Procesando...</h3>
	<p><?php echo(_m('procesando.transaccion','el pago solicitado')); ?></p>
</div>

<script>

$(function(){	
	
        $('#coordenada1').focus();
        
	$('#aceptar').click(function(){
            <?php if (strpos($estado['titulo'], 'Pagos de Impuestos Nacionales/Aduanales') !== false){ ?>
                 $('#procesando').modal({minHeight:120,minWidth:400,close:false});
            <?php } ?>
	});
	        
        // para la validacion
	$('#TdcValidarTdcForm').validate({
		submitHandler: function(form){
			$.each($('tr td div.error-message'),function(){
				if($(this).siblings().size()==0){
					$(this).parent().parent().remove();
				}
			});
			
			if($('#TdcValidarTdcForm').valid()){
				
				if(!bloqueado){ // bloqueado var global
					$('input.bloqueo_repetir').prop('disabled', true);
					bloqueado = true;
					form.submit();
				}
				
			}
		},
		rules:{
			'data[Tdc][coordenada1]':{
				required:true,
				digits:true,
				minlength:3
			},
			'data[Tdc][coordenada2]':{
				required:true,
				digits:true,
				minlength:3
			}
		},
		messages:{
			'data[Tdc][coordenada1]':{
				required:<?php echo _m('campo.requerido','la coordenada',true) ?>,
				digits:<?php echo _m('formato.invalido','la coordenada',true) ?>,
				minlength:<?php echo _m('longitud.exacta2',array('la coordenada','3'),true) ?>
			},
			'data[Tdc][coordenada2]':{
				required:<?php echo _m('campo.requerido','la coordenada',true) ?>,
				digits:<?php echo _m('formato.invalido','la coordenada',true) ?>,
				minlength:<?php echo _m('longitud.exacta2',array('la coordenada','3'),true) ?>
			}
		}
	});

});
</script>

