<?php
/**
 * Modelo para la validación al exportar datos de una tarjeta de crédito.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Validación al exportar datos de una tarjeta de crédito.
 * 
 */
class ExportarTarjeta extends AppModel {
	
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
		'campos' => array(
			'valoresPermitidos'=>array(
				'rule'=>array(
					'multiple',
					array(
						'in'=>array('f','r','d','c','a')
					)
				)
			)
		),
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
				'rule'=>array('inList',array('dmY','Ymd','dmy','ymd')),
				'allowEmpty'=>true
			)
		),
		'formatoMonto' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array(',.','.,',',','.')),
				'allowEmpty'=>true
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