<?php
/**
 * Modelo para el manejo de las transaciones de mesa de cambio.
 * 
 * @author Emsys
 */


/**
 * Manejo de las transaciones de mesa de cmabio.
 * 
 */
class TransaccionMesaCambio extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'transacciones_mesa_cambio';

	
	/**
	 * Tipos de transacciones.
	 * 
	 * @var array 
	 */
	public static $tipo_transaccion = array(
            'com'=> 'Compra',
            'ven'=> 'Venta'
	);

	
	/**
	 * Estatus de las transacciones.
	 * 
	 * @var array
	 */
	public static $estatus_transaccion = array(
            'pa'=> 'Por aprobar',
            'pe'=> 'Pendiente',
            'ej'=> 'Procesada',
            'el'=> 'Eliminada',
            'ra'=> 'Rechazada usuario aprobador',            
            'g '=> 'Guardada',
            've'=> 'Vencido'
	);
	
	/**
	 * Indica las tablas con las que tiene una relación foránea.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
            'UsuarioCreador'=>array(
                'className'=>'Usuario',
                'foreignKey'=>'usuario_id'
            )
	);

	
	/**
	 * Indica las tablas que se relacionan con el modelo.
	 * 
	 * @var array 
	 */
	var $hasMany = array(            
            'AprobacionesRealizadas'=>array(
                'className'=>'AprobacionTransaccion',
                'foreignKey'=>'transaccion_id'
            )			
	);
			
	
	/**
	 * Contiene las validaciones que se deben realizar a los campos de los
	 * formularios utilizados en las vistas.
	 * 
	 * @var array
	 */
	var $validate = array(
		'cuenta_origen' => array(
                    'requerido'=> array(
                            'rule'=>'notEmpty'
                    )
		),
		'cuenta_destino' => array(
                    'requerido'=> array(
                            'rule'=>'notEmpty'
                    )    
		),
		'monto' => array(
                    'numerico'=>array(
                            'rule'=>'numerico'
                    ),
                    'requerido'=>array(
                            'rule'=>'notEmpty'
                    )
		)
	);
	
}
?>