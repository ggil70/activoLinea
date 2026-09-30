<?php
/**
 * Formulario de configuración de usuarios jurídicos
**/
?>
<div>
<h2 class="user">Configuraci&oacute;n Usuarios</h2>
<div class="line_title_orange"></div>
</div>
<?php
echo $form->create('Usuario',
	array(
		'url'=> array(
			'controller'=>'empresa',
			'action'=>'listado'
		)
	)
);
?>
<table class="formulario" cellpadding="0" cellspacing="0" border="0">
	<tr>	
	<td class="etiqueta"></td>
	</tr>
	<tr>
	<td >
	<?php
		if($rolJuridico <= 2) {?>
			<label for="data[Usuario][id]" class="error manual"><?php echo _m('no.editar', $accion); ?></label>
				<?php
					if ($form->isFieldError('id')){
						echo $form->error('id', _m('no.editar', $accion));
					}
		}else{?>
			<label for="data[Usuario][id]" class="error manual"><?php echo _m('uno.seleccion.requerida','de los usuarios'); ?></label>
			<?php
				if ($form->isFieldError('id')){
					echo $form->error('id', _m('uno.seleccion.requerida','de los usuarios'));
				}
		}
		?>
	</td>
	</tr>
</table>

<table class="tabla" cellpadding="0" cellspacing="0" style="margin: 0 auto" border="0" width="90%">
<thead>
<tr>
    <td class="ancholibre tcheckbox">&nbsp;</td>
    <td>Identificaci&oacute;n<br/>Usuario (login)</td>
    <td >Nombre y Apellido</td>
    <td>Nombre del Perfil Asignado</td>
    <td>Fecha/Hora<br/>Creaci&oacute;n</td>    
    <td>Fecha/Hora<br/>&Uacute;ltima Modificaci&oacute;n</td>
    <td>Estatus de Acceso</td>
</tr>
</thead>

<?php 
foreach($listado as $l){
?>
<tr>
    <td class="ancholibre tcheckbox">	
		<?php if($l['rj']['id'] && $l['rj']['id']>2 && isset($l['usuarioCanal']['estatus_acceso']) && $l['usuarioCanal']['estatus_acceso']!='I1'){  ?>
		<input type="radio" name="data[Usuario][id]" value="<?php echo $l['Usuario']['id'] ?>">
		<?php } ?>
	</td>
    <td class="texto_centro"><?php echo ($l['Usuario']['login'] ?  desencriptar($l['Usuario']['login']) : desencriptar($l['Usuario']['ibuser']) ); ?></td>
    <td class="texto_centro"><?php echo desencriptar($l['Usuario']['nombre'])?></td>
    <td class="texto_centro"><?php echo $l['rj']['nombre']?></td>
    <td class="texto_centro"  width = "18%"><?php if(!empty($l['Usuario']['fecha_hora_creacion'])) echo date('d/m/Y h:i:s a', strtotime($l['Usuario']['fecha_hora_creacion']))?></td>
    <td class="texto_centro"  width = "18%"><?php if(!empty($l['Usuario']['fecha_hora_modificacion'])) echo date('d/m/Y h:i:s a', strtotime($l['Usuario']['fecha_hora_modificacion']))?></td>
    <td class="texto_centro"><?php echo $estatus[$l['usuarioCanal']['estatus_acceso']] ?></td>
</tr>		
<?php } ?>

<tr>
    <td colspan="7" class="paginador">
          <?php echo $this->element('paginador2',array('form_id'=>'paginacion','total'=>$total_registros)); ?>
    </td>
</tr>
<tr>
<td colspan="7" class="botonera">

<?php

    echo $html->link(
        'Agregar',
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
            'class'=>'button',
            'id'=>'editar'
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
	
	echo '&nbsp;&nbsp;&nbsp;';
	echo $html->link(
        'Cambiar Estatus',		
		'javascript:',		
        array(
            'class'=>'button',
            'id'=>'cambio_estatus'
        )
    );
	
	echo '&nbsp;&nbsp;&nbsp;';
	echo $html->link(
        'Consultar Usuario',
        'javascript:',
        array(
            'class'=>'button',
            'id'=>'ver'
        )
    );
	
	echo $form->input(
        'accion',
		array(
			'label'=>false,
			'before'=>'<br>',
			'type'=>'hidden',
			'value'=>'listado'				
		)
    );

    echo $form->end();	
?>

</td>
</tr>

</table>
<?php	
echo $form->create(
	'Usuario',
	array(
		'url'=> array(
			'controller'=>'empresa',
			'action'=>$this->action
		),
		'id' => 'paginacion'
	)
);
if(!empty($this->data['Usuario'])){
	foreach($this->data['Usuario'] as $k => $v) {
		echo $form->hidden($k);
	}
}
echo $form->hidden('offset');
echo $form->hidden('paginando');
echo $form->end();
?>
<script>


$(function(){

	$('#crear').click(function(){               
		$('input[name="data[Usuario][accion]"]').val('crear');		
		$('#UsuarioListadoForm').submit();
	});

	$('#editar').click(function(){
		if($('#UsuarioListadoForm').valid()){		
			$('input[name="data[Usuario][accion]"]').val('editar');
			$('#UsuarioListadoForm').submit();
		}
	});
	
	$('#ver').click(function(){
		if($('#UsuarioListadoForm').valid()){		
			$('input[name="data[Usuario][accion]"]').val('ver');
			$('#UsuarioListadoForm').submit();
		}
	});

	$('#eliminar').click(function(){
		if($('#UsuarioListadoForm').valid()){		
			$('input[name="data[Usuario][accion]"]').val('eliminar');
			$('#UsuarioListadoForm').submit();
		}
	});
	
	$('#cambio_estatus').click(function(){
		if($('#UsuarioListadoForm').valid()){		
			$('input[name="data[Usuario][accion]"]').val('cambio_estatus');
			$('#UsuarioListadoForm').submit();
		}
	});
	
	$('#UsuarioListadoForm').validate({
		onsubmit:false,
		onkeyup:false,
		onclick:false,
		onfocusout:false,
		
		rules:{
			'data[Usuario][id]':{
				required: true
			}
		}
	});
});
</script>