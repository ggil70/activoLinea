<?php
    /**
     *  Vista utilizada para obtener las operaciones disponibles para las afiliaciones
     */

	$opciones = array(
		'type'=>'select',
		'options'=>$subtipos,
		'empty'=>'Seleccione',
		'id'=>'subtipo',
		'label'=>false,
		'div'=>false,
		'before'=>false,
		'between'=>false,
		'after'=>false,
		'name'=>'data[Afiliacion][tipo_operacion]',
		'onchange'=>'acomoda_tipo_operacion()'
	);

	if(!empty($this->data['Afiliacion']['id'])){
		$opciones['disabled']='disabled';
		$opciones['value']= trim($this->data['Afiliacion']['tipo_operacion']);
		echo $form->hidden('tipo_operacion');
	}

	echo $form->input(
		'tipo_operacion',
		$opciones
	);
?>