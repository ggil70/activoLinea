<?php
/**
 * Modelo para el manejo de la información de los prefijos telefonicos.
 * 
 * @author MGGY
 */


/**
 * Manejo de la información de los canales.
 * 
 */
class TcpAgencias extends AppModel{
	
	/**
	 * Nombre de la tabla en base de datos.
	 * 
	 * @var string
	 */
	var $useTable = 'agencias';
        
        /**
	 * Listar los prefijos telefonicos.
	 * 	 	 
	 * @return array
	 */
	function listar_agencias(){				
		$agencia =  $this->find(
			'all', 
            array('order' => 'nombre',
                  'conditions' => array('estatus'=>1))
		);

        $arrayAgencias = array(); 
		foreach ($agencia as $key) {
            $arrayAgencia[$key['TcpAgencias']['id']] = $key['TcpAgencias']['nombre'];

		}
		return $arrayAgencia;
	}
	
   
}

?>
