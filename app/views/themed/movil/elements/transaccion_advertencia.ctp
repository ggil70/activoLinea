<div class="subtitulo">
<h2><?php echo $this->data['titulo']; ?></h2>
<div></div>
</div>

<div class="paso_desc_actual">Confirmaci&oacute;n</div>

<div class="contenedor_formulario">

	<table class="confirmacion" cellpadding="0" cellspacing="0" border="0">
	
	<thead>
		<tr>
			<td class="resumen_paginacion" colspan="2">
				<?php echo _m($this->data['mensaje_alerta']); ?>
			</td>
		</tr>
	</thead>

	<tr>
		<td colspan="2" class="botonera">

		<?php 
		echo $html->link(
			'Volver',
			$this->data['url_volver'],
			array(
				'class'=>'button bloqueo_repetir'
			)
		);

		echo '&nbsp;&nbsp;';

		echo $html->link(
			'Continuar',
			$this->data['url_continuar'],
			array(
				'class'=>'button bloqueo_repetir'
			)
		);

		?>

		</td>
	</tr>
	</table>
</div>




