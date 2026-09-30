<?php
/**
 * Modelo para el manejo de los roles de los usuarios jurídicos.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los roles de los usuarios jurídicos.
 * 
 */
class RolJuridico extends AppModel {
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'roles_juridicos';
	
	
	/**
	 * Listado de roles diferentes a master y master unico
	 * 
	 * @return array
	 */
	function listadoRoles(){
		
		$result= $this->find(
			'all',
			array( 'conditions'=>array('id not in(1,2)') )
		);
		
		if($result){
			$temp = array();
			foreach ($result as $r){
				$temp[$r['RolJuridico']['id']] = $r['RolJuridico']['nombre'];
			}
			$result = $temp;
		}
		
		return $result;
	}
	
}
?>