<?php
/**
 * Modelo para el manejo de los alias de los productos.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los alias de los productos.
 * 
 */
class AliasProducto extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'alias_producto';
	
	
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
		'alias' => array(
			'alfanumerico'=>array(
				'rule'=>'alfanumerico'
			)
		)
	);
	
	
	/**
	 * Busca la información del alias de un producto.
	 * 
	 * @param int $usuario_id
	 * @return array
	 */
	function buscaInfo($usuario_id) {	
		return $this->find(
			'first',
			array(
				'conditions' => array(
					'AliasProducto.usuario_id'=>$usuario_id,
					'AliasProducto.cuenta'=>encriptar($this->data['AliasProducto']['cuenta']),
				)
			)
		);
	}

	
	/**
	 * Verifica el alias de un producto del usuario.
	 * 
	 * @param int $usuario_id
	 * @return array
	 */
	function checkAlias($usuario_id) {
		return $this->find(
			'first',
			array(
				'conditions' => array(
					'AliasProducto.usuario_id'=>$usuario_id,
					'AliasProducto.alias'=>encriptar($this->data['AliasProducto']['alias']),
					'AliasProducto.cuenta <>'=>encriptar($this->data['AliasProducto']['cuenta']),
					'AliasProducto.alias IS NOT NULL',
				)
			)
		);
	}
}
?>