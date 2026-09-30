<div>
<h2 class="user">Env&iacute;o digital Estado de Cuenta</h2>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Solicitud de Informaci&oacute;n</div>
<div class="paso_nro_actual">2</div>
<div class="paso_desc_actual">T&eacute;rminos y Condiciones</div>
<div class="paso_nro">3</div>
<div class="paso_desc">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<?php

echo $form->create('Afiliacion',
	array(
		'url'=> array(
			'controller'=>'afiliaciones',
			'action'=>'envio_terminos'
		)
	)
);

echo $form->hidden('tag',array('value'=>'ok'));

?>
<table class="formulario" width="75%" cellpadding="0" cellspacing="0" border="0" align="center">
	<tr>
		<td class="texto_centro">
			<b>
				T&eacute;rminos y Condiciones para la configuraci&oacute;n de las instrucciones de env&iacute;o digital de los
				Estados de Cuenta de los productos de Banco Activo.
			</b>
		</td>
	</tr>
	<tr>
		<td class="texto_justificado">
			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris sit amet mi lacus, id pulvinar odio. Nulla consequat ligula mi, id lacinia est. Nulla odio purus, pharetra vitae fermentum vitae, eleifend ac metus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Cras faucibus odio eu augue tempus condimentum cursus eu ipsum. Nulla accumsan ante vitae enim volutpat suscipit. Vivamus porta vehicula orci at fermentum. Donec in bibendum nulla. Vivamus sagittis dictum augue nec convallis.
			<br/><br/>
			Aliquam eu lectus lorem. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Integer quis nulla sit amet risus molestie fermentum. Ut sed eros est, id tristique nulla. Vivamus vel felis sed justo euismod semper. Proin ultrices aliquam mi quis tristique. Nunc volutpat auctor nisi tempor euismod. Cras at ligula felis, et adipiscing lectus. Aliquam quis orci non metus gravida ullamcorper. Nam tincidunt tortor quis risus fringilla porttitor. Nunc eu sapien odio, vitae iaculis nisl. Vivamus ipsum urna, laoreet sed venenatis id, sagittis eget est.
			<br/><br/>
			Nulla consequat, ipsum eu vulputate ultrices, nibh odio convallis quam, eget posuere turpis massa laoreet arcu. Mauris lacinia cursus felis et pharetra. Nunc at rutrum ipsum. Praesent et urna vitae neque pellentesque vestibulum at in lorem. Cras nec nunc metus, ut egestas libero. Cras imperdiet blandit vehicula. Mauris mi risus, elementum nec elementum porttitor, viverra venenatis orci. Morbi ac erat mauris. Donec lorem massa, vestibulum nec venenatis sit amet, consectetur a tortor. Sed malesuada iaculis euismod. Pellentesque non mi nec nulla laoreet ultrices. Integer ligula erat, tincidunt at tristique a, accumsan a metus.
		</td>
	</tr>
	<tr><td></td></tr>
	<tr>
		<td colspan="4" style="border: 0px;" class="botonera">

			<?php 
			echo $form->submit(
				'Aceptar',
				array(
					'class'=>'button',
					'name'=>'crear',
					'div'=>'false'
				)
			);
			?>

		</td>
	</tr>
</table>
<?php


echo $form->end();

?>
