<?php
/**
 * Modelo para el manejo del proceso de Auditoría Externa.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo del proceso de Auditoría Externa
 * 
 */
class AuditoresExternos extends AppModel {

	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = false;
	
	
	/**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
	var $validate = array(
		'empresa_seleccion' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			),
		)
	);
	
}

?>