<div class="subtitulo">
<h2>Sesión Terminada</h2>
<div></div>
</div>

<div class="contenedor_formulario">

	<?php
	echo '<p>'._m('session.finalizada') . '</p>';
	echo '<br/>';
	?>

	<div class="botonera">

		<?php
		echo $html->link(
			'Ingresar Nuevamente',
			'/',
			array(
				'class'=>'button'
			)
		);
		?>

	</div>

</div>