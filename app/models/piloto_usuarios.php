<?php
/**
 * Modelo para el manejo de los usuarios pilotos del sistema.
 * 
 * @author Adverweb <http://www.adverweb.com>
 */


/**
 * Manejo de los usuarios pilotos del sistema.
 * 
 */
class PilotoUsuarios extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'piloto_usuarios';
	
	
	/**
	 * Verifica si un usuario esta registrado para acceder a las pruebas pilotos.
	 * 
	 * @param int $numero_documento
	 * @param string $tipo_documento
	 * @param string $tipo_cliente
	 * @return boolean
	 */
	function buscar($numero_documento, $tipo_documento='', $tipo_cliente=''){
		
		// Verifica si el tipo de cliente esta lleno
		switch ($tipo_cliente){
			case 'N':
				$conditions = array(
					"tipo_documento in ('V','E','P')",
					'numero_documento' => $numero_documento
				);
				break;
			
			case 'J':
				$conditions = array(
					"tipo_documento in ('J','G')",
					'numero_documento' => $numero_documento
				);
				break;
			
			default:
				$conditions = array(
					'tipo_documento' => $tipo_documento,
					'numero_documento' => $numero_documento
				);
				break;
		}
		
		// Verifica si encuentra la info
		if( $this->find(
			'first',
				array( 'conditions'=>$conditions ),
				false
			)
		){
			return true;
		}
		else{
			return false;
		}
	}
}
?>