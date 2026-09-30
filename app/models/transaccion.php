<?php
/**
 * Modelo para el manejo de las transacciones del sistema.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de las transacciones del sistema.
 * 
 */
class Transaccion extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'transacciones';
	
	
	/**
	 * Indica las tablas con las que tiene una relación foránea.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
		'Modulo' => array( 
			'className' => 'Modulo',
			'foreignKey' => 'modulo_id'
		)
	);
	
	
	/**
	 * Obtiene las transacciones de por módulo de un usuario
	 * 
	 * @param array $usuario Datos generales del usuario
	 * @param int $modulo ID del módulo
	 * @return array Listado con las transacciones
	 */
	function obtenerTransaccionesPorModulo($usuario, $modulo){
		
		// Condiciones generales de busqueda
		$condiciones = array(
			'modulo_id'=>$modulo,
			'tp.transaccion_id not' => array(7,8,10,11) // Transacciones de transferencias y pagos anteriores
		);

		// Verifica si es un usuario juridico o natural
		if( !empty($usuario['tipo_rif']) ){
			
			// Condiciones para usuarios juridicos
			if($usuario['rol_juridico_id']>1){ 
				$condiciones['tp.usuario_id'] = $usuario['id'];
			}
			else{
				$condiciones[] = "tp.usuario_id in (select id from usuarios where rif = '". $usuario['rif'] ."' and tipo_rif = '". $usuario['tipo_rif']. "')";
			}
			
			// tabla a la cual hacer inner join
			$tabla_inner = 'transacciones_usuarios_juridicos';

		}
		else{
			
			// Condiciones para usuarios juridicos
			$condiciones['tp.perfil_ib_id']= $usuario['perfil_ib_id'];
			$condiciones[] = '(
				tp.perfil_piloto is null or 
				tp.perfil_piloto in (SELECT perfil_piloto FROM usuarios_perfil_piloto WHERE usuario_id = '.$usuario['id'].')
			)';
			
			// tabla a la cual hacer inner join
			$tabla_inner = 'transacciones_perfiles_ib';
				
		}
		
		// Busqueda de las transacciones
		$transacciones = $this->find(
			'list',
			array(
				'fields'=>array('id','nombre'),
				'joins' => array(
					array(
						'table' => $tabla_inner,
						'alias' => 'tp',
						'type' => 'INNER',
						'conditions' => array(
							'tp.transaccion_id = Transaccion.id'
						)
					)
				),
				'conditions'=>$condiciones,
				'order'=>'posicion asc'
			)
		);
		
		
		return $transacciones;
	}
}

?>