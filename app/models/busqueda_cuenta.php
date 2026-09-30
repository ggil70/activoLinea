<?php
/**
 * Modelo para la validación de busquedas en consultas de cuentas o tarjetas.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Validación de busquedas en consultas de cuentas o tarjetas.
 * 
 */
class BusquedaCuenta extends AppModel {
	
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
		'periodo' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('a','n','u','o')),
			),
			'requerido'=> array(
				'rule'=>'notEmpty',
			),
		),
		
		'referencia' => array(
			'numeric'=>array(
				'rule'=>'numeric',
				'allowEmpty'=>true
			)
		),
		
		'montoDesde' => array(
			'numerico'=> array(
				'rule'=>'numerico'
			),
		),
		
		'montoHasta' => array(
			'numerico'=> array(
				'rule'=>'numerico',
			),
			'comparacionMontos'=>array(
				'rule'=>'comparaMontos'
			),
		),
	);
	
	
	/**
	 * Método para validar los rangos de montos en la busqueda.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function comparaMontos($check){

		if(is_array($check)){
		   $value = array_values($check);
		   $value = $value[0];
		} else {
		   $value = $check;
		}           		

		if($value!="" && $this->data['BusquedaCuenta']['montoDesde']!=""){
		   $hasta = formatea_monto_toWs($value);
		   $desde = formatea_monto_toWs($this->data['BusquedaCuenta']['montoDesde']);		

		   return $hasta >= $desde;
		}
		return true;

	}  
	
}

?>