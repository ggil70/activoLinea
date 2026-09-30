<?php
/**
 * Confirmación de pago de tarjetas propias en banco activo
**/
?>
<div class="espacio_vertical_20"></div>
<div>
<div class="line_title_orange"></div>
</div>

<div class="paso_nro">1</div>
<div class="paso_desc">Votaci&oacute;n</div>
<div class="paso_nro">2</div>
<div class="paso_desc">Confirmaci&oacute;n</div>
<div class="paso_nro_actual">3</div>
<div class="paso_desc_actual">Resultado de Operaci&oacute;n</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">

<div class="espacio_vertical_10"></div>

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0" style="width:800px;margin:0 auto">
		<thead>
	<?php 
		if($resultadoVotacion == '01'){
	 ?>	
			<tr>
				<td class="resumen_paginacion">
					<p class="exito">Operación realizada satisfactoriamente.</p>
					<p>Estimado Cliente, su voto se ha realizado con Éxito.</p>
					<p>Gracias por su colaboración.</p>
				</td>
			</tr>
			<tr>
				<td class="botonera" style="text-align:center;">
				<?php 

					echo '&nbsp;&nbsp;';

					echo $html->link(
						'Finalizar',
						'/consultas/index',
						array(
							'class'=>'button'
						)
					);
				?>
				</td>
			</tr>
		<?php } ?>
		</thead>
		
		<?php 
		if($resultadoVotacion != '01'){
		?>
			<thead>
                        <tr>
				<td class="resumen_paginacion">
					<p class="exito">Estimado Cliente, la operación no se pudo completar.</p>
					<p>Por favor comuníquese desde Venezuela a través del Centro de Atención Activa 0500-ACTIVAT (2284828) y desde el Exterior al +58-2122091350.</p>					
				</td>
			</tr>    
			<tr>
				<td colspan="2" class="botonera">
					<form class="formulario_enlace" method="post" action="<?php echo Router::url('/') ?>">
						<input type="submit" class="button" value="Finalizar" />
					</form>
			</td>
			</tr>
			</thead>
		<?php }?>
		
	</table>
</div>



