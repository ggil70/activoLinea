<div>
<h2 class="user">Env&iacute;o digital Estado de Cuenta</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro_actual">1</div>
<div class="paso_desc_actual">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">T&eacute;rminos y Condiciones</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>


<?php

echo $form->create('Afiliacion',
	array(
		'url'=> array(
			'controller'=>'afiliaciones',
			'action'=>'envio'
		)
	)
);


?>

<table class="tabla" cellpadding="0" cellspacing="0" border="0" align="center">
<tr><td colspan="4" class="vacio">A través de esta opci&oacute;n podr&aacute; definir la forma de env&iacute;o de sus Estados de Cuenta</td></tr>

	<thead>
		<tr>
			<td colspan="4" class="resumen_paginacion">
				<label for="data[Afiliacion][numero]" class="error manual"><?php echo _m('uno.seleccion.requerida','de los registros'); ?></label>
				<?php
					if ($form->isFieldError('numero')){
						echo $form->error('numero',_m('uno.seleccion.requerida','de los registros'));
					}
				?>
			</td>
		</tr>
	</thead>
	<thead>
	
	<tr>
		<td colspan="2">Productos</td>
		<td>Alias</td>
		<td><span class="requerido">* </span>Digital</td>
	</tr>
	</thead>
	
	<tbody>
	
		<tr>
			<td>Cuenta Corriente</td>
			<td>1234******0087</td>
			<td>Hermano</td>
			<td style="text-align: center;">
				<input type="checkbox" name="data[Afiliacion][numero]" value="1234587893456950087"
					<?php if(
							isset($this->data['Afiliacion']['numero'])
							&& 0==strcmp($this->data['Afiliacion']['numero'],'1234587893456950087')
						){
						echo 'selected="selected"';
					}
					?>
				/>
			</td>
		</tr>
		<tr>
			<td>Visa</td>
			<td>7539******9586</td>
			<td>Sobrina</td>
			<td style="text-align: center;">
				<input type="checkbox" name="data[Afiliacion][numero]" value="75391234569586"
					<?php if(
							isset($this->data['Afiliacion']['numero'])
							&& 0==strcmp($this->data['Afiliacion']['numero'],'75391234569586')
						){
						echo 'selected="selected"';
					}
					?>
				/>
			</td>
		</tr>
		<tr>
			<td>Visa</td>
			<td>8545******0087</td>
			<td>Tia</td>
			<td style="text-align: center;">
				<input type="checkbox" name="data[Afiliacion][numero]" value="85451256950087"
					<?php if(
							isset($this->data['Afiliacion']['numero'])
							&& 0==strcmp($this->data['Afiliacion']['numero'],'85451256950087')
						){
						echo 'selected="selected"';
					}
					?>
				/>
			</td>
		</tr>


		<tr><td colspan="4" style="border: 0px;" ><span class="requerido">* </span><span class="requerido2">Campo obligatorio</span></td></tr>
	
		<tr>
			<td colspan="4" style="border: 0px;" class="botonera">

				<?php 
				echo $form->submit(
					'Aceptar',
					array(
						'class'=>'button',
						'name'=>'crear',
						'div'=>false
					)
				);
				?>

			</td>
		</tr>
	</tbody>
</table>

<p>
	
				<b>Nota:</b>
				Usted recibir&aacute; los Estados de Cuenta a la siguiente direcci&oacute;n de correo el&eacute;ctronico xxxxxx@xxxx.com,
				en caso de no ser la direcci&oacute;n correcta, usted puede realizar la actualizaci&oacute;n por la opci&oacute;n de Configuraci&oacute;n
				de datos ubicada dentro de Administraci&oacute;n Seguridad.
</p>
<?php


echo $form->end();

?>

<script>
$(function(){

	// para la validacion
	$('#AfiliacionEnvioForm').validate({
		rules:{
			'data[Afiliacion][numero]':{
				required: true
			}
		},
		messages:{
			'data[Afiliacion][numero]':{
				required: <?php echo _m('uno.seleccion.requerida','de los registros', true);?>
			}
		}
	});
});
</script>



