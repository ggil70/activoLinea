<?php
/**
 * Modelo para el manejo del histórico de las transacciones.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo del histórico de las transacciones.
 * 
 */
class HistoricoTransacciones extends AppModel {
	
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
		'formato' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('t','e','s'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'configuraciones' => array(
			'valoresPermitidos'=>array(
				'rule'=>array(
					'multiple',
					array(
						'in'=>array('n','e')
					)
				),
				'allowEmpty'=>true
			)
		),
		'formatoFecha' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('dmY','Ymd','dmy','ymd'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'formatoMonto' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array(',.','.,',',','.'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)		
		),
		'divisionCampos' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('f',';',',','tab'))
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)		
		)
		
	);
}

?>