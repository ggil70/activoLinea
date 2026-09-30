<?php
/**
 * Modelo para el manejo de las aprobaciones pendientes.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de las aprobaciones pendientes.
 * 
 */
class AprobacionTransaccion extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'aprobaciones_transacciones';

	
	/**
	 * Indica las tablas con las que tiene una relación foránea.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
		'TransaccionPendiente'=>array(
			'className'=>'TransaccionPendiente',
			'foreignKey'=>'transaccion_id'
		),
		'Usuario'=>array(
			'className'=>'Usuario',
			'foreignKey'=>'usuario_id'
		)
	);
	
}

?>