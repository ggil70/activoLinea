<?php
/**
 * Modelo para la validación de las tarjetas de coordenadas.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Validación de las tarjetas de coordenadas.
 * 
 */
class Tdc extends AppModel {
	
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
		'coordenada1' => array(
			'coordenada'=>array(
				'rule'=>'/^\d{3}$/i'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'coordenada2' => array(
			'coordenada'=>array(
				'rule'=>'/^\d{3}$/i'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		)
	);
}
?>