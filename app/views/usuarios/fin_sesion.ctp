<?php
/**
 * Mensaje de sesión finalizada por inactividad
**/
?>
<div>
<h2 class="user">Sesión Terminada</h2>
<div class="line_title_orange"></div>
</div>

<div class="espacio_vertical_20"></div>

<div class="contenedor_formulario">
	
<?php

echo $form->create('Usuario',
	array(
		'url'=> array(
			'controller'=>'usuarios',
			'action'=>'fin_sesion'
		)
	)
);

echo _m('session.finalizada') . '<br/>';

echo '<br/>';
echo $html->link(
	'Cerrar explorador',
	'javascript:window.close();'
);
echo '<br/><br/>';
echo $html->link(
	'Ingresar Nuevamente',
	'/'
);

echo $form->end();

?>

</div>

