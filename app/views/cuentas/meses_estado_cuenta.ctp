<?php
    /**
     * Vista para manejar los meses disponibles en el historial de estados de cuenta
     */


echo $form->input(
	'DetalleProducto.fecha_edo_cuenta',
	array(
		'options'=>$meses,
		'div'=>false,
		'label'=>false,
		'error'=>false
	)
);
?>