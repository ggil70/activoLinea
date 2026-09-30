<?php
/**
 * Modelo para el manejo de las solicitudes de tarjetas de coordenadas.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de las solicitudes de tarjetas de coordenadas.
 * 
 */
class SolicitudTdc extends AppModel {
	
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
		'respuesta' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		
		'tipo_motivo' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			),
		),
		
		'motivo' => array(
			'requerido'=> array(
				'rule'=>'motivo_requerido'
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',30)
			),
		)
	);
	
	
	/**
	 * Método que valida el motivo de anulación.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function motivo_requerido($check){
		if(is_array($check)){
			$value = array_values($check);
			$value = $value[0];
		} else {
			$value = $check;
		}
		
		if($this->data['SolicitudTdc']['tipo_motivo']=='o'){
			return !empty($value);
		} else {
			return true;
		}
	}
	
	
	/**
	 * Listado de los motivos de anulación.
	 * 
	 * @return array
	 */
	function obtenerMotivosAnulacion(){
		return array(
				'r'=>'Robo',
				'e'=>'Extravío',
				'd'=>'Deterioro',
				's'=>'Solicitud Voluntaria',
				'o'=>'Otro'
			);
	}
	
}

?>