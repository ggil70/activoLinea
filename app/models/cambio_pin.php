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
class CambioPin extends AppModel {
	
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

		//die("aqui");

			'numeric'=>array(
				'rule'=>'numeric'
			),
		
		'pin_nuevo' => array(
			
		'consecutivos'=>array(
				'rule'=>'confirmacionConsecutivos'
			),
			'minLength'=>array(
				'rule'=>array('minLength',4)
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',4)
			),
			'numeric'=>array(
				'rule'=>'numeric'
			),
				'requerido'=>array(
				'rule'=>'notEmpty'
			),
			
		),
		
		'clave_confirmacion' => array(
			'confirmacionIgualQue'=>array(
				'rule'=>'confirmacionIgualQue'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),

		
		),
		
		
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
		
		//die($value.'-->'.$this->data['CambioPin']['pin_nuevo']);
		return $value == $this->data['CambioPin']['pin_nuevo'];
	}
		
	function confirmacionConsecutivos(){
	    
	
	     	
	     	if (
	     		($this->data['CambioPin']['pin_nuevo'][0]==$this->data['CambioPin']['pin_nuevo'][1] && $this->data['CambioPin']['pin_nuevo'][0]==$this->data['CambioPin']['pin_nuevo'][2] )   || ($this->data['CambioPin']['pin_nuevo'][1]==$this->data['CambioPin']['pin_nuevo'][2] && $this->data['CambioPin']['pin_nuevo'][1]==$this->data['CambioPin']['pin_nuevo'][3] )){
	     		return false;
	     	
			
		} else {
			return true;
		}
		
		
	}
}