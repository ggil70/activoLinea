<?php
/**
 * Mensaje de resultado del registro
**/
?>
<div>
<h2 class="user">Registro</h2>
<div class="line_title_orange"></div>
</div>

<div>
	<div class="paso_nro">1</div>
	<div class="paso_desc">Aceptaci&oacute;n de Contrato</div>
	<div class="paso_nro">2</div>
	<div class="paso_desc">Autenticaci&oacute;n del cliente</div>
	<div class="paso_nro">3</div>
	<div class="paso_desc">Registro de Usuario</div>
	<div class="paso_nro">4</div>
	<div class="paso_desc">Código de Autorización</div>
        <div class="paso_nro">5</div>
	<div class="paso_desc">Configuraci&oacute;n Imagen Antiphishing</div>
	<div class="paso_nro">6</div>
	<div class="paso_desc">Configuraci&oacute;n Preguntas Desaf&iacute;o</div>
	<div class="paso_nro">7</div>
	<div class="paso_desc">Configuraci&oacute;n de L&iacute;mites</div>
</div>

<div class="espacio_vertical_20"></div>

	
<div class="contenedor_formulario">
	<p class="exito">Operación realizada satisfactoriamente</p>

	<p>
		<?php echo _m('registro.natural.final'); ?>
	</p>

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td colspan="2" class="botonera">

				<?php 
				echo $html->link(
					'Continuar',
					'/consultas/index',
					array(
						'class'=>'button'
					)
				);
				?>

			</td>
		</tr>
	</table>
</div>
