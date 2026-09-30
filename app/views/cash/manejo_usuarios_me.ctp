<?php
    /**
     *   Vista para mostrar el listado de usuarios Nómina/Proveedores carga manual
     */
?>

<div>
<h2 class="user"><?php echo $titulo ?></h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario" style="width:790px;">
<?php

echo $form->create('ManejoUsuariosCash',
    array(
    'url'=> array(
        'controller'=>'cash',
	'action'=>'manejo_usuarios_me',
	$tipo
	)
    )
);

echo $form->hidden('id');

?>

<input type="hidden" name="data[accion]">

<p>En esta opci&oacute;n Ud. podr&aacute; crear, modificar y eliminar Beneficiarios</p>


<label for="data[seleccion][]" class="error manual"><?php echo _m('uno.seleccion.requerida','de los registros'); ?></label>

<table class="tabla" width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
    <thead>
	<tr>
            <td class="texto_centro ancholibre"><span class="requerido">*</span></td>
            <td style="width:160px;">Nombre Beneficiario</td>
            <td>Identificación Beneficiario</td>
            <td>Número Cuenta Beneficiario</td>
            <td>Banco Beneficiario</td>
            <td>E-mail Beneficiario</td>
            <td>Número Teléfono Celular</td>
	</tr>
    </thead>
	
    <tbody>
        <?php
	if(count($listado)){
            foreach($listado as $id=>$l) {?>
		<tr>
                    <td class="texto_centro ancholibre">
                        <input type="radio" name="data[seleccion][]" value="<?php echo $l['AfiliacionCash']['id'] ?>"
                            <?php if(
				isset($this->data['seleccion']) &&
				is_array($this->data['seleccion']) &&
				in_array($id, $this->data['seleccion'])
                                ){
                                    echo 'checked="checked"';
				}
                            ?>
                            />
                    </td>
                    <td ><?php echo desencriptar($l['AfiliacionCash']['nombre']) ?></td>
                    <td><?php echo $l['AfiliacionCash']['tipo_cedula'].desencriptar($l['AfiliacionCash']['cedula']) ?></td>
                    <td><?php echo desencriptar($l['AfiliacionCash']['numero_cuenta']) ?></a></td>
                    <td style="width:35%"><?php echo @$bancos[substr(desencriptar($l['AfiliacionCash']['numero_cuenta']),0,4)] ?></td>
                    <td><?php echo desencriptar($l['AfiliacionCash']['email']) ?></td>
                    <td><?php echo desencriptar($l['AfiliacionCash']['celular']) ?></td>
		</tr>
		<?php } ?>
		<tr>
                    <td colspan="7" class="paginador">
                        <?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros)); ?>
                    </td>
		</tr>
		
		<tr><td colspan="7" style="border: 0px;" ><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>
	
		<tr>
                    <td colspan="7" class="botonera">

			<?php
				
                            if($total_registros < 10){
				echo $html->link(
                                    'Crear',
                                    'javascript:',
                                    array(
                                        'class'=>'button',
                                        'id'=>'crear_usuarios'
                                    )
				);
					
				echo '&nbsp;&nbsp;&nbsp;';
				}
				
				echo $html->link(
                                    'Modificar',
                                    'javascript:',
                                    array(
					'class'=>'button',
					'id'=>'modificar_usuarios'						
                                    )
				);
				
				echo '&nbsp;&nbsp;&nbsp;';
				
				echo $html->link(
                                    'Eliminar',
                                    'javascript:',
                                    array(
					'class'=>'button',
					'id'=>'eliminar_usuarios'						
                                    )
				);
                            ?>

                    </td>
		</tr>		
	<?php } else { ?>
	<tr><td colspan="7" class="texto_centro">No se encontraron resultados...</td></tr>
	<tr>
            <td colspan="7" class="botonera">
                <?php 
		echo $html->link(
                    'Crear',
                    'javascript:',
                    array(
                        'class'=>'button',
			'id'=>'crear_usuarios'
                    )
		);
		?>
            </td>
	</tr>	
	<?php } ?>
    </tbody>
</table>
<?php

    echo $form->end();

?>
</div>

<?php	
    echo $form->create(
    'AfiliacionCash',
    array(
        'url'=> array(
            'controller'=>'cash',
            'action'=>$this->action
        ),
        'id' => 'paginacion'
    )
);

echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>

<script>
$(function(){
	$('#crear_usuarios').click(function(){	
		$('input[name="data[accion]"]').val('crear_usuarios');
		$('#ManejoUsuariosCashManejoUsuariosForm').submit();		
	});

	$('#eliminar_usuarios').click(function(){
		if($('#ManejoUsuariosCashManejoUsuariosForm').valid()){		
			$('input[name="data[accion]"]').val('eliminar_usuarios');
			$('#ManejoUsuariosCashManejoUsuariosForm').submit();
		}
	});
	
		$('#modificar_usuarios').click(function(){
		if($('#ManejoUsuariosCashManejoUsuariosForm').valid()){		
			$('input[name="data[accion]"]').val('modificar_usuarios');
			$('#ManejoUsuariosCashManejoUsuariosForm').submit();
		}
	});
	
	$('#ManejoUsuariosCashManejoUsuariosForm').validate({
		onsubmit:false,
		onkeyup:false,
		onclick:false,
		onfocusout:false,
		rules:{
			'data[seleccion][]':{
				required: true
			}
		}
	});	
});
</script>