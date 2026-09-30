<?php
    /**
     * Vista para mostrar el listado de afiliaciones del cliente
     */


?>
<div>
<h2 class="user">Afiliaciones</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<p>En esta opci&oacute;n Ud. podr&aacute; crear, modificar y eliminar afiliaciones de cuentas, tarjetas y servicios</p>

<?php
echo $form->create('Afiliacion',
	array(
		'url'=> array(
			'controller'=>'afiliaciones',
			'action'=>'listado'
		)
	)
);

?>
<input type="hidden" name="data[accion]">

<table class="formulario" cellpadding="0" cellspacing="0" border="0">
	<?php if(count($tipos)>1) { ?>
		<tr>
		<td class="etiqueta">Tipo de afiliación:</td>
		<td class="campo" style="width:300px">
		<?php echo $form->select(
			'tipo_afiliacion',
			$tipos,
			@$this->data['Afiliacion']['tipo_afiliacion'],
			array(
				'id'=>'tipo_afiliacion',
				'empty'=>'Todas',
                                'style'=>'width:100%'
			)
		) ?>
                    
		</td>
		</tr>
	<?php 
	} 
	else{
		echo $form->hidden('x');
	}
	?>
	<tr>
	<td colspan="2">
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
		<td class="texto_centro ancholibre"><span class="requerido">*</span></td>
		<td>Alias</td>
		<td>Tipo Afiliaci&oacute;n</td>
		<td>Tipo Operaci&oacute;n</td>
		<td>Nombre Beneficiario</td>
		<td>N&uacute;mero</td>
	</tr>
	</thead>
	
	<tbody>
		<?php 

		if(count($listado)){
			
			foreach($listado as $l) {
		?>
		<tr>
			<td class="texto_centro ancholibre">
				<input type="radio" name="data[Afiliacion][id]" value="<?php echo $l['Afiliacion']['id'] ?>"
					<?php if(
							isset($this->data['Afiliacion']['id'])
							&& 0==strcmp($this->data['Afiliacion']['id'],$l['Afiliacion']['id'])
						){
						echo 'checked="checked"';
					}
					?>
				/>
			</td>
			<td><?php echo ($l['Afiliacion']['alias']!="") ? desencriptar($l['Afiliacion']['alias']) : '' ?></td>
			<td><?php echo $tipos_listado[$l['Afiliacion']['tipo_afiliacion']] ?></td>
			<td><?php echo $subtipos[$l['Afiliacion']['tipo_afiliacion']][trim($l['Afiliacion']['tipo_operacion'])] ?></td>
			<td> <?php $nombre=desencriptar($l['Afiliacion']['nombre_beneficiario']);
				if (empty($nombre)){
			echo '';
		 }else{
				echo desencriptar($l['Afiliacion']['nombre_beneficiario']);
			
		 }?>
			</td>
			<td><?php                                   
                if ($l['Afiliacion']['tipo_servicio'] == 'stm' || $l['Afiliacion']['tipo_servicio'] == 'stf' || $l['Afiliacion']['tipo_servicio'] == 'rtm' || $l['Afiliacion']['tipo_servicio'] == 'rtf' || $l['Afiliacion']['tipo_servicio'] == 'rim'  ){
                    echo $l['Afiliacion']['codigo_telefono'].desencriptar($l['Afiliacion']['numero_cuenta']);


                }else{

                	if($l['Afiliacion']['tipo_afiliacion'] == 'p' || $l['Afiliacion']['tipo_afiliacion'] =='h'){
                    	$num_tarjeta = desencriptar($l['Afiliacion']['numero_cuenta']); 
                    	$num_tarjeta = substr($num_tarjeta,0,4) . "****" . substr($num_tarjeta,16-4,4);
                    	echo $num_tarjeta;
                    }elseif($l['Afiliacion']['tipo_afiliacion'] == 't' ){
                    	$num_cuenta = desencriptar($l['Afiliacion']['numero_cuenta']); 
                    	$num_cuenta = substr($num_cuenta,0,4) . "****" . substr($num_cuenta,20-4,4);           
                    	echo $num_cuenta;         	
                    }elseif($l['Afiliacion']['tipo_afiliacion'] == 'c' ){
                    	if(str_replace(' ', '', $l['Afiliacion']['tipo_operacion']) == "ce"){
                    		$num_cuenta = desencriptar($l['Afiliacion']['cedula']); 
	                    	echo $l['Afiliacion']['tipo_cedula']."-".$num_cuenta;      
                    	}elseif(str_replace(' ', '', $l['Afiliacion']['tipo_operacion']) == "cea"){
	                    	echo $l['Afiliacion']['numero_contrato'];      
                    	}else{
                    		$num_cuenta = desencriptar($l['Afiliacion']['numero_cuenta']); 
	                    	$num_cuenta = substr($num_cuenta,0,4) . "****" . substr($num_cuenta,20-4,4);           
                    		echo $num_cuenta;  
                    	}
                    }
                }
            ?></td>
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
			<tr><td colspan="6" class="texto_centro">No se encontraron resultados...</td></tr>
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

	$('#tipo_afiliacion').change(function(){	
		$('input[name="data[accion]"]').val('listar');
		$('#AfiliacionListadoForm').submit();
	});

	$('#crear').click(function(){	
		$('input[name="data[accion]"]').val('crear');
		$('#AfiliacionListadoForm').submit();		
	});

	$('#modificar').click(function(){
		if($('#AfiliacionListadoForm').valid()){		
			$('input[name="data[accion]"]').val('editar');
			$('#AfiliacionListadoForm').submit();
		}
	});

	$('#eliminar').click(function(){
		if($('#AfiliacionListadoForm').valid()){		
			$('input[name="data[accion]"]').val('eliminar');
			$('#AfiliacionListadoForm').submit();
		}
	});
	
	$('#AfiliacionListadoForm').validate({
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
