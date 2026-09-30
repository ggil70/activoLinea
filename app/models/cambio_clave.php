<?php
/**
 * Modelo para la validación de cambio de claves.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Validación de cambio de claves.
 * 
 */
class CambioClave extends AppModel {
	
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
		'clave_actual' => array(
			'minLength'=>array(
				'rule'=>array('minLength',8)
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',12)
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)		
		),
		
		'clave_nueva' => array(
			'minLength'=>array(
				'rule'=>array('minLength',8)
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',12)
			),
			'contrasena'=>array(
				'rule'=>'contrasena'
			),	
			'contrasena2'=>array(
				'rule'=>'contrasena2',
			),
			'requerido'=>array(
				'rule'=>'notEmpty',
			)
		),
		
		'clave_confirmacion' => array(
			'confirmacionIgualQue'=>array(
				'rule'=>'confirmacionIgualQue'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'tiempoCaduca' => array(
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('30','60','90','180')),

			),
		)
	);
	
	
	/**
	 * Método para verificar la clave nueva y confirmada.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function confirmacionIgualQue($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		return $value == $this->data['CambioClave']['clave_nueva'];
	}
}