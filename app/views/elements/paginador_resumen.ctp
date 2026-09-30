<?php
/**
 * Fragmento de página que se encarga de generar el resumen del paginador
**/

$offset= 0;

if(isset($this->params['form']['offset'])){
	$offset = $this->params['form']['offset'];
} else if(count($this->data)){
	
	$data = array_values($this->data);

	if(isset($data[0]['offset'])){
		$offset = $data[0]['offset'];
	}

}

echo $total_registros.' registros encontrados. ';

if($total > 0) {
	echo 'Mostrando '.($offset+1).' al '.min($total,$offset + Configure::read('Tamano.paginacion'));
}

?>