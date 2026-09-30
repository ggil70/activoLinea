<?php
/**
 * Modelo para el manejo de las transaciones de pagos y transferencias por la SIMF
 * 
 * @author Emsys
 */


/**
 * Manejo de las transaciones de pagos y transferencias por la SIMF.
 * 
 */
class TransaccionSimf extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'transacciones_simf';

	
	/**
	 * Tipos de transacciones.
	 * 
	 * @var array 
	 */
	public static $tipo_transaccion = array(
            '1'=> 'Emisor',
            '2'=> 'Receptor'
	);

	
	/**
	 * Estatus de las transacciones.
	 * 
	 * @var array
	 */
	public static $estatus_transaccion = array(
            'PE'=> 'En Proceso',            
            'AC'=> 'Procesada',
            'ER'=> 'Rechazada',
            'RE'=> 'Rechazada'            
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
	 * Método para validar las cuentas de origen y destino.
	 * 
	 * @param array|string $check
	 * @return boolean
	 */
	function comparaCuentas($check){
		if(is_array($check)){
                    $value = array_values($check);
                    $value = $value[0];
		} else {
                    $value = $check;
		}	

		return !($value == $this->data['TransaccionSimf']['cuenta_origen']);
	}
	
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
	 * Obtiene las transacciones de un usuario.
	 * 
	 * @param int $usuario
	 * @return array
	 */
	function obtenerTransacciones($usuario){
            		
		// Condiciones para la búsqueda de transacciones
		$valores = array('pa');
		$condiciones = array(
                    'TransaccionSimf.usuario_id'=>$usuario['id'],
                    'TransaccionSimf.fecha_hora_registro >= '=>date('Y-m-d')                    
		);
		
		$transacciones = $this->find(
                    'all',
                    array(
                        'contain'=>array(                               
                            'UsuarioCreador',                                
                        ),
                        'conditions'=>$condiciones,
                        'order'=>'TransaccionSimf.fecha_hora_registro asc'
                    )
		);				
		
		return $transacciones;
	}			
	
        /**
	 * Obtiene la descripcion de un codigo de rechazo simf.
	 * 
	 * @param string $codigo
	 * @return string
	 */
	function obtenerMotivo($codigo){
            		
            $this->MotivoRechazo = ClassRegistry::init('Texto');                   		

            $descripcion = $this->MotivoRechazo->find(
                    'first',
                    array(
                        'conditions' => array(
                            'Texto.codigo'=>$codigo
                        )
                    )
            );
            
            if(isset($descripcion['Texto']['mensaje_usuario']) && !empty($descripcion['Texto']['mensaje_usuario'])){
                $texto = $descripcion['Texto']['mensaje_usuario'];
            }else{
                $texto = '';
            }
            	
            return $texto;
	}
        
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

		),
		'monto' => array(
			'numerico'=>array(
				'rule'=>'numerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		),
		'descripcion' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			),
			'requerido'=>array(
				'rule'=>'notEmpty'
			)
		)
	);			
}
?>