<?php
/**
 * Modelo para el manejo de los mensajes del sistema.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los alias de los mensajes del sistema.
 * 
 */
class Mensajes extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'mensajes_usuarios';
	
	
	/**
	 * Busca la cantidad de mensajes para un usuario.
	 * 
	 * @param int $usuario
	 * @return int
	 */
	function buscaCantidad($usuario) {	
		return $this->find(
			'count',
			array(
				'conditions' => array(
					'usuario_id' => $usuario,
					'estatus' => 'n'
				),
				'joins' => array(
					array(
						'table' => 'mensajes',
						'alias' => 'M',
						'type' => 'INNER',
						'conditions' => array(
							'M.id = Mensajes.mensaje_id',
							'M.fecha_hora <= ' => date('Y-m-d')
						)
					)
				)
			)
		);
	}
	
	
	/**
	 * Busca los mensajes de un usuario
	 * 
	 * @param int $usuario
	 * @return array
	 */
	function buscaMensajes($usuario){
		if(! $result = $this->find(
			'all',
			array(
				'fields' => array(
					'Mensajes.id','M.fecha_hora', 'M.texto'
				),
				'conditions' => array(
					'usuario_id' => $usuario,
					'estatus'=>array('l','n')
				),
				'joins' => array(
					array(
						'table' => 'mensajes',
						'alias' => 'M',
						'type' => 'INNER',
						'conditions' => array(
							'M.id = Mensajes.mensaje_id',
							'M.fecha_hora <= ' => date('Y-m-d')
						)
					)
				),
				'order' => array(
					'M.fecha_hora DESC', 'M.id desc', 'Mensajes.estatus DESC'
				)
			)
		)){
			$this->log($this->getDataSource()->error);
			return null;
		}
		
		// Actualiza todos los mensajes del usuario como leidos
		$this->updateAll(
			array(
				'estatus' => "'l'"
			),
			array(
				'usuario_id' => $usuario,
				'estatus'=>'n'
			)
		);
		
		return $result;
	}
}
?>