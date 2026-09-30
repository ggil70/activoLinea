<?php
/**
 * Modelo para el manejo del detalle de los pagos a proveedores en carga masiva.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo del detalle de los pagos a proveedores en carga masiva.
 * 
 */
class DetallePagoProveedor extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'detalle_pago_proveedor';
	
	
	/**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
	var $validate = array(
		'monto' => array(
			'numerico'=>array(
				'rule'=>'montoEstricto'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'tipo_detalle' => array(
			'valoresPermitidos'=>array(
				'rule'=>array('inList',array('FA','GI','CU','ND','NC','OT'))
			),
			'requerido'=> array(
				'rule'=>'notEmpty'
			)
		),
		'referencia' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
	);
	
	/**
	 * Tipos de detalles de pagos de proveedores
	 * 
	 * @var array 
	 */
	public static $tipos_detalle = array(
		'FA'=>'Factura',
		'GI'=>'Giro',
		'CU'=>'Cuota',
		'ND'=>'Nota de Débito',
		'NC'=>'Nota de Crédito',
		'OT'=>'Otro'
	);
}
?>