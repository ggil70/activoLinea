<?php
/**
 * Modelo para la validación de los datos en el proceso de olvido.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Validación de los datos en el proceso de olvido.
 * 
 */
class Olvido extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = false;
	
	
	/**
	 * Método para la validación de la longitud de la cédula de identidad.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function validaLongitud($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		return $this->checkMaxCI($this->data['Olvido']['tipoCedulaRif'], $value);
	}
	
	
	/**
	 * Método para la validación de la longitud del RIF
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function validaLongitudRif($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		return $this->checkMaxCI($this->data['Olvido']['tipoRif'], $value);
	}
	
	
	/**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
	var $validate = array(
		'tipo' => array(
			'requerido'=>array(
				'rule'=>'notEmpty'
			)		
		),
		
		'tipoCedulaRif' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('V','E','P','J','G'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'nroCedulaRif' => array(
			'longitud'=>array(
				'rule'=>'validaLongitud'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'tipoRif' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('J','G','V','E')),

			),
			'requerido'=>array(
				'rule'=>'notEmpty',

			)
		),
		
		'nroRif' => array(
			'longitud'=>array(
				'rule'=>'validaLongitudRif'
			),
			'requerido'=>array(
				'rule'=>'notEmpty',
			)
		),
		'loginUsuario' => array(
			'minLength'=>array(
				'rule'=>array('minLength',8)
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',12),
			),	
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
	);
}

?>