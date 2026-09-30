<?php
    /**
     * Vista para obtener los beneficiarios 
     */

	if(!empty($datos)){
		echo $datos.
		$form->hidden('Afiliacion.nombre_beneficiario',array('value' => $datos));
	}
?>