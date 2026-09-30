<?php
    /**
     * Vista para mostrar el listado de afiliaciones de pago activo que posee el cliente
     */
?>
<div>
<h2 class="user">Afiliaciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p>En esta opci&oacute;n Ud. podr&aacute; crear, modificar y eliminar afiliaciones de cuentas y tarjetas</p>

<?php
echo $form->create('Afiliacion',
	array(
		'url'=> array(
			'controller'=>'afiliaciones',
			'action'=>'listado_pago_activo'
		),
                'id' => 'AfiliacionListadoPagoActivoForm'
	)
);

?>
<input type="hidden" name="data[accion]">

<table class="formulario" cellpadding="0" cellspacing="0" border="0">	
	<tr>
	<td>
		<label for="data[Afiliacion][id]" class="error manual"><?php echo _m('uno.seleccion.requerida','de los registros'); ?></label>
		<?php
			if ($form->isFieldError('id')){
				echo $form->error('id', _m('uno.seleccion.requerida','de los registros'));
			}
		?>
	</td>
	</tr>
</table>
<table class="tabla" width="100%" cellpadding="0" cellspacing="0" border="0">
	<thead>
	<tr>
		<td colspan="6" class="resumen_paginacion">
			<div style="text-align:left; width:58%; float:left;">
				Seleccione la cuenta y la tarjeta que desea afiliar al servicio:
			</div>
			<div style="text-align:right; width:38%; float:right;">
				<?php echo $this->element('paginador_resumen',array('total'=>$total_registros)) ?>
			</div>
		</td>
	</tr>
        </thead>
        <thead>
	<tr>
		<td class="texto_centro ancholibre"><span class="requerido">*</span></td>
		<td>Cuenta</td>
		<td>Tarjeta de Crédito</td>		
		<td>Contrato</td>		
	</tr>
	</thead>
	
	<tbody>
		<?php 
		if(count($arrData)){
			foreach($arrData as $l) {
		?>
		<tr>
			<td class="texto_centro ancholibre">
				<input type="radio" name="data[Afiliacion][id]" value="<?php echo $nrocliente.'|'.$l['e01PATACC'].'|'.$l['e01PATTDC'] ?>"/>
			</td>
			<td class="texto_centro ancholibre"><?php echo $this->Activo->enmascara('01710000'.$l['e01PATACC']); ?></td>						
			<td class="texto_centro ancholibre"><?php echo $this->Activo->enmascara($l['e01PATTDC']); ?></td>
			<td class="texto_centro ancholibre"><?php echo $html->link(
                                                $l['e01PATACC'],
                                                '/afiliaciones/contrato_pago_activo/'.$l['e01PATACC'].''                                                
                                        ); 
                            ?>
                        </td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan="6" class="paginador">				
                                <?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros)); ?>
			</td>
		</tr>
		
		<tr><td colspan="6" style="border: 0px;" ><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>
	
		<tr>
			<td colspan="6" class="botonera">

				<?php 
				
				echo $html->link(
					'Crear',
					'javascript:',
					array(
						'class'=>'button',
						'id'=>'crear'
					)
				);

				echo '&nbsp;&nbsp;&nbsp;';
				
				echo $html->link(
					'Modificar',
					'javascript:',
					array(
						'id'=>'modificar',
						'class'=>'button'
					)
				);
				
				echo '&nbsp;&nbsp;&nbsp;';
				
				echo $html->link(
					'Eliminar',
					'javascript:',
					array(
						'class'=>'button',
						'id'=>'eliminar'						
					)
				);
				?>

			</td>
		</tr>
		
		<?php
		}
		else{
		?>
			<tr><td colspan="4" class="texto_centro">No se encontraron resultados...</td></tr>
			<tr>
				<td colspan="4" class="botonera">
					<?php 
					echo $html->link(
						'Crear',
						'javascript:',
						array(
							'class'=>'button',
							'id'=>'crear'
						)
					);
					?>
				</td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>
<?php echo $form->end(); ?>
</div>

<?php	
echo $form->create(
	'Afiliacion',
	array(
		'url'=> array(
			'controller'=>'afiliaciones',
			'action'=>$this->action
		),
		'id' => 'paginacion'
	)
);
if(!empty($this->data['Afiliacion'])){
	foreach($this->data['Afiliacion'] as $k => $v) {
		echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script type="text/javascript">

$(function(){

	$('#crear').click(function(){	
		$('input[name="data[accion]"]').val('crear');
		$('#AfiliacionListadoPagoActivoForm').submit();		
	});

	$('#modificar').click(function(){
		if($('#AfiliacionListadoPagoActivoForm').valid()){		
			$('input[name="data[accion]"]').val('editar');
			$('#AfiliacionListadoPagoActivoForm').submit();
		}
	});

	$('#eliminar').click(function(){
		if($('#AfiliacionListadoPagoActivoForm').valid()){		
			$('input[name="data[accion]"]').val('eliminar');
			$('#AfiliacionListadoPagoActivoForm').submit();
		}
	});
	
	$('#AfiliacionListadoPagoActivoForm').validate({
		onsubmit:false,
		onkeyup:false,
		onclick:false,
		onfocusout:false,
		rules:{
			'data[Afiliacion][id]':{
				required: true
			}
		}
	});	
});
</script>
