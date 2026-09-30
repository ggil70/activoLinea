<?php
/**
 * Modelo para el manejo de la información de cheques.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de la información de cheques.
 * 
 */
class Cheque extends AppModel {
	
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
		'cuenta' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'tipo' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('c','h','r'))
			),
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		
		'chqInicial' => array(
			'numeric'=>array(
				'rule'=>'numeric'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',8)
			),
		),
		
		'chqFinal' => array(
			'numeric'=>array(
				'rule'=>'numeric'
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',8)
			),
		),
		
		'cheque' => array(
			'numeric'=>array(
				'rule'=>'numeric'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',8)
			),
		),
		
		'monto' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		
		'tipo_motivo' => array(
			'requerido'=> array(
				'rule'=>'notEmpty'
			),
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('p','d','m','i','o'))
			),
		),		
		
		'motivo' => array(
			'requerido'=> array(
				'rule'=>'motivo_requerido'
			),
			'maxLength'=>array(
				'rule'=>array('maxLength',30)
			),
		),
		
		'chequeras' => array(
			'requerido'=> array(
				'rule'=>  array('multiple'),
			)
		),
		
	);
	
	
	/**
	 * Método para la validación del motivo de suspensión.
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
		
		if($this->data['Cheque']['tipo_motivo']=='o'){
			return !empty($value);
		} else {
			return true;
		}
	}
	
	
	/**
	 * Retorna los motivos de suspensión de cheques o chequeras.
	 * 
	 * @return array
	 */
	function buscar_motivos(){
		return array(
			'p'=>'Pérdida',
			'd'=>'Discrepancias',
			'm'=>'Monto Erróneo',
			'i'=>'Incumplimiento de Contrato',
			'o'=>'Otras Causas'
		);
	}
}
?>