<?php
/**
 * Resultado de asignación de alias a productos propios
**/
?>
<div>
<h2 class="user">Asignar Alias de Productos</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	<thead>
		<tr>
			<td class="resumen_paginacion">
				<?php if(!$error){ ?>
					<p class="exito"><?php echo _m('titulo.operacion.exito'); ?></p>
					<p>Estimado Cliente, el registro fue modificado exitosamente</p>
				<?php
				} 
				else{
					echo '<div class="error-message">' . _m('operacion.fallida') . '</div>';
				}
				?>
			</td>
		</tr>
		<tr>
			<td class="botonera">

			<?php 		
				echo $html->link(
					'Volver',
					'alias',
					array(
						'class'=>'button',
						'id'=>'volver'
					)
				);
			?>
			</td>
		</tr>
	</thead>
</table>
</div>