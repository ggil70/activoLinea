<?php
/**
 * Modelo para la validación en el estado de cuenta de tarjetas de crédito.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Validación en el estado de cuenta de tarjetas de crédito.
 * 
 */
class EstadoCuentaTdc extends AppModel{
	
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
		'nro_tarjeta' => array(
			'nro_tarjeta'=> array(
				'rule'=>'/^\d{13,16}$/i'
			)
		),
		'mes' => array(
			'mes'=> array(
				'rule'=>'/^\d{10}$/i'
			)
		)
	);
}
?>