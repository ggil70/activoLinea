<?php
/**
 * Modelo para el manejo de las transacciones de usuarios jurídicos.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de las transacciones de usuarios jurídicos.
 * 
 */
class TransaccionJuridico extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'transacciones_usuarios_juridicos';
	
	/**
	 * Indica las tablas con las que tiene una relación foránea.
	 * 
	 * @var array 
	 */
	var $belongsTo = array(
		'Usuario'=>array(
			'className'=>'Usuario',
			'foreignKey'=>'usuario_id'
		)
	);	
	
	
	/**
	 * Obtiene las transacciones de un usuario
	 * 
	 * @param int $usuario
	 * @return array
	 */
	public function obtenerTransUsuario($usuario) {
		$arrData = array();
		if( $result = $this->find(
			'all',
			array(
				'joins' => array(
					array(
						'table' => 'transacciones_perfiles_ib',
						'alias' => 'TP',
						'type' => 'INNER',
						'conditions' => array(
							'TP.transaccion_id = TransaccionJuridico.transaccion_id'
						)
					)
				),
				'contain'=>'Usuario',
				'conditions'=> array( 'TransaccionJuridico.usuario_id' => $usuario, 'TP.perfil_ib_id'=>2)
			)
		)){
			
			// Asignacion de las transacciones
			foreach ($result AS $i){
				// El usuario aprobador es especial, ya que a pesar de que tenga los permisos
				// asignados, sólo se utilizan cuando el mismo está realizando una aprobación
				if(
					$i['Usuario']['rol_juridico_id']==4 &&
					in_array($i['TransaccionJuridico']['transaccion_id'], array(7,8,10,11,13,14,19,22,39,100016,40,100017,41,42))
				){
					// No agregar esta transacción para que no la vea en el menú
					continue;
				}
			
				$arrData[] = $i['TransaccionJuridico']['transaccion_id'];
			}
			
			
			// Para las transacciones pilotos de los usuarios juridicos
			$this->TransaccionPerfil = ClassRegistry::init('TransaccionPerfil');
			if( $result = $this->TransaccionPerfil->find(
				'all',
				array(
					'fields' => array('TransaccionPerfil.transaccion_id'),
					'joins' => array(
						array(
							'table' => 'usuarios_perfil_piloto',
							'alias' => 'UP',
							'type' => 'INNER',
							'conditions' => array(
								'UP.perfil_piloto = TransaccionPerfil.perfil_piloto'
							)
						)
					),
					'conditions'=> array('TransaccionPerfil.perfil_ib_id'=>2, 'UP.usuario_id' => $usuario)
				)
			)){
				foreach ($result AS $i){
					$arrData[] = $i['TransaccionPerfil']['transaccion_id'];
				}
			}
			
		}
		else{
			$this->log('Fallo transaccion/perfiles - No se encontraron transacciones del usuario ' . $usuario . ' - ' . $this->getDataSource()->error);
		}
		
		return $arrData;
	}
}
?>