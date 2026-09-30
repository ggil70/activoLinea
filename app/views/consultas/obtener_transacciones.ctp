<?php
    /**
     *  Vista utilizada para obtener las transacciones dependiendo del tipo de operación que se desea consultar
     */

echo $form->input(
	'',
	array(
		'type'=>'select',
		'options'=>$transacciones,
		'empty'=>'Todas las transacciones',
		'id'=>'transaccion',
		'label'=>false,
		'div'=>false,
		'before'=>false,
		'between'=>false,
		'after'=>false,
		'name'=>'data[HistoricoTransacciones][transaccion]'
	)
);
?>