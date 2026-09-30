<?php
/**
 * Modelo para el manejo del detalle de productos.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo del detalle de productos.
 * 
 */
class DetalleProducto extends AppModel{
	
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
		'nro_credito' => array(
			'nro_credito'=> array(
				'rule'=>'/^\d{12}$/i'
			)
		),
		'nro_cuenta' => array(
			'nro_cuenta'=> array(
				'rule'=>'/^\d{20}$/i'
			)
		),
		'nro_cuenta_pm' => array(
			'nro_cuenta_pm'=> array(
				'rule'=>'/^\d{10}$/i'
			)
		),
		'nro_tarjeta' => array(
			'nro_tarjeta'=> array(
				'rule'=>'/^\d{13,16}$/i'
			)
		),
		'referencia' => array(
			'referencia'=> array(
				'rule'=>'/^\d{0,10}$/i'
			)
		),
		'tipo' => array(
			'tipo'=> array(
				'rule'=>'/^WD|WC|AE|CI||WN|PS$/i'
			)
		),
		'nro_inversion' => array(
			'nro_inversion'=> array(
				'rule'=>'/^\d{12}$/i'
			)
		)
	);
}